<?php
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id  = $_SESSION['user_id'];
$claim_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if(!$claim_id) die("Invalid claim.");

/* ========================= LOAD CLAIM ========================= */
$stmt = $conn->prepare("
    SELECT
        claims.*,
        listings.title,
        listings.description,
        listings.location,
        listings.image,
        listings.user_id AS owner_id,
        u1.username AS owner_name,
        u2.username AS claimant_name
    FROM claims
    JOIN listings ON claims.listing_id = listings.id
    JOIN users u1 ON listings.user_id   = u1.id
    JOIN users u2 ON claims.claimant_id = u2.id
    WHERE claims.id = ?
");
$stmt->bind_param('i', $claim_id);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows == 0) die("Claim not found.");
$claim = $result->fetch_assoc();
$stmt->close();

/* ========================= ACCESS CONTROL ========================= */
$roleRes  = $conn->prepare("SELECT role FROM users WHERE id = ?");
$roleRes->bind_param('i', $user_id);
$roleRes->execute();
$roleRow  = $roleRes->get_result()->fetch_assoc();
$roleRes->close();
$is_admin = ($roleRow && $roleRow['role'] === 'admin');

if($claim['claimant_id'] != $user_id && $claim['owner_id'] != $user_id && !$is_admin){
    die("Access denied.");
}

$isOwner    = ($user_id == $claim['owner_id']);
$isClaimant = ($user_id == $claim['claimant_id']);

/* ========================= OWNER ACTIONS (still server-side) ========================= */
if(isset($_POST['accept']) && $isOwner){
    $stmt = $conn->prepare("UPDATE claims SET status='approved' WHERE id=?");
    $stmt->bind_param('i', $claim_id);
    $stmt->execute(); $stmt->close();

    $stmt = $conn->prepare("UPDATE listings SET status='removed' WHERE id=?");
    $stmt->bind_param('i', $claim['listing_id']);
    $stmt->execute(); $stmt->close();

    $stmt = $conn->prepare("UPDATE claims SET status='rejected' WHERE listing_id=? AND id != ? AND status='pending'");
    $stmt->bind_param('ii', $claim['listing_id'], $claim_id);
    $stmt->execute(); $stmt->close();

    header("Location: claim_detail.php?id=$claim_id");
    exit();
}

if(isset($_POST['reject']) && $isOwner){
    $stmt = $conn->prepare("UPDATE claims SET status='rejected' WHERE id=?");
    $stmt->bind_param('i', $claim_id);
    $stmt->execute(); $stmt->close();

    header("Location: claim_detail.php?id=$claim_id");
    exit();
}

/* ========================= LOAD INITIAL MESSAGES ========================= */
$stmt = $conn->prepare("
    SELECT messages.id, messages.sender_id, messages.message, messages.created_at,
           users.username
    FROM messages
    JOIN users ON messages.sender_id = users.id
    WHERE claim_id = ?
    ORDER BY created_at ASC
");
$stmt->bind_param('i', $claim_id);
$stmt->execute();
$msg_result = $stmt->get_result();
$stmt->close();

$messages    = [];
$last_msg_id = 0;
while($msg = $msg_result->fetch_assoc()){
    $messages[] = $msg;
    $last_msg_id = max($last_msg_id, (int)$msg['id']);
}

$status   = $claim['status'];
$chatOpen = ($status === 'pending' || $status === 'approved');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Claim Chat – <?php echo htmlspecialchars($claim['title']); ?></title>
    <link rel="stylesheet" href="style.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="chat-page-wrap">

    <!-- LEFT: Item info + claim details -->
    <div class="chat-sidebar">

        <div class="chat-item-img">
            <?php if(!empty($claim['image'])): ?>
                <img src="<?php echo $claim['image']; ?>" alt="<?php echo htmlspecialchars($claim['title']); ?>">
            <?php else: ?>
                <img src="images/default.jpg" alt="No image">
            <?php endif; ?>
        </div>

        <div class="chat-sidebar-body">

            <p class="chat-sidebar-label">Item</p>
            <h3 class="chat-sidebar-title"><?php echo htmlspecialchars($claim['title']); ?></h3>

            <p class="chat-sidebar-label" style="margin-top:14px;">Location</p>
            <p class="chat-sidebar-value">📍 <?php echo htmlspecialchars($claim['location']); ?></p>

            <p class="chat-sidebar-label" style="margin-top:14px;">Claim Status</p>
            <span class="claim-status-badge" id="claimStatusBadge">
            <?php
                if($status == 'pending')   echo '<span class="claim-status status-pending">🟡 Pending</span>';
                if($status == 'approved')  echo '<span class="claim-status status-approved">🟢 Approved</span>';
                if($status == 'rejected')  echo '<span class="claim-status status-rejected">🔴 Rejected</span>';
                if($status == 'collected') echo '<span class="claim-status status-collected">✅ Collected</span>';
            ?>
            </span>

            <div class="chat-sidebar-divider"></div>

            <p class="chat-sidebar-label">Reporter</p>
            <p class="chat-sidebar-value">👤 <?php echo htmlspecialchars($claim['owner_name']); ?></p>

            <p class="chat-sidebar-label" style="margin-top:10px;">Claimant</p>
            <p class="chat-sidebar-value">👤 <?php echo htmlspecialchars($claim['claimant_name']); ?></p>

            <?php if($isOwner && $status == 'pending'): ?>
                <div class="chat-sidebar-divider"></div>
                <p class="chat-sidebar-label">Owner Actions</p>
                <form method="POST" style="margin-bottom:8px;">
                    <button name="accept" class="claim-action-btn btn-approve" style="width:100%;"
                        onclick="return confirm('Accept this claim? All other pending claims on this item will be rejected.')">
                        ✅ Accept Claim
                    </button>
                </form>
                <form method="POST">
                    <button name="reject" class="claim-action-btn btn-reject" style="width:100%;"
                        onclick="return confirm('Reject this claim?')">
                        ❌ Reject Claim
                    </button>
                </form>
            <?php endif; ?>

            <!-- Live indicator -->
            <div style="margin-top:16px; display:flex; align-items:center; gap:8px;">
                <span id="liveIndicator" style="width:8px; height:8px; border-radius:50%; background:#22c55e; display:inline-block;"></span>
                <span style="font-size:12px; color:#666;" id="liveLabel">Live</span>
            </div>

            <a href="<?php echo $isOwner ? 'incoming_claims.php' : 'myclaims.php'; ?>" class="chat-back-link">
                ← Back to Claims
            </a>

        </div>
    </div>

    <!-- RIGHT: Chat -->
    <div class="chat-main">

        <div class="chat-header">
            <div>
                <h2>Claim Chat</h2>
                <p>Between <?php echo htmlspecialchars($claim['owner_name']); ?> and <?php echo htmlspecialchars($claim['claimant_name']); ?></p>
            </div>
        </div>

        <!-- Messages box -->
        <div class="chat-box" id="chatBox">

            <?php if(count($messages) === 0): ?>
                <div class="chat-empty" id="chatEmpty">
                    💬 No messages yet. Start the conversation.
                </div>
            <?php endif; ?>

            <?php foreach($messages as $msg): ?>
                <?php $isMe = ($msg['sender_id'] == $user_id); ?>
                <div class="msg-wrapper <?php echo $isMe ? 'right' : 'left'; ?>">
                    <div class="msg <?php echo $isMe ? 'me' : 'other'; ?>">
                        <div class="msg-user"><?php echo htmlspecialchars($msg['username']); ?></div>
                        <div class="msg-text"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                        <div class="msg-time"><?php echo date('d M Y, g:i A', strtotime($msg['created_at'])); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>

        <!-- Input -->
        <?php if($chatOpen): ?>
            <div class="chat-input-form">
                <textarea id="msgInput" placeholder="Type your message..." rows="1"
                    onkeydown="handleEnter(event)"></textarea>
                <button id="sendBtn" onclick="sendMessage()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="22" y1="2" x2="11" y2="13"/>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                </button>
            </div>
        <?php else: ?>
            <div class="chat-closed-bar">
                🚫 Chat closed — this claim has been <?php echo $status; ?>.
            </div>
        <?php endif; ?>

    </div>
</div>

<?php include 'footer.php'; ?>

<script>
const CLAIM_ID   = <?php echo $claim_id; ?>;
const MY_USER_ID = <?php echo $user_id; ?>;
const CHAT_OPEN  = <?php echo $chatOpen ? 'true' : 'false'; ?>;

let lastMsgId   = <?php echo $last_msg_id; ?>;
let pollTimer   = null;
let sending     = false;

/* ---------- Scroll to bottom ---------- */
function scrollBottom() {
    const box = document.getElementById('chatBox');
    if (box) box.scrollTop = box.scrollHeight;
}
scrollBottom();

/* ---------- Render a single message bubble ---------- */
function renderMessage(msg) {
    const isMe = msg.is_me;

    // Remove "no messages" placeholder if it's there
    const empty = document.getElementById('chatEmpty');
    if (empty) empty.remove();

    const dt    = new Date(msg.created_at.replace(' ', 'T'));
    const timeStr = dt.toLocaleDateString('en-MY', { day:'2-digit', month:'short', year:'numeric' })
                  + ', ' + dt.toLocaleTimeString('en-MY', { hour:'numeric', minute:'2-digit' });

    const wrap = document.createElement('div');
    wrap.className = 'msg-wrapper ' + (isMe ? 'right' : 'left');
    wrap.innerHTML = `
        <div class="msg ${isMe ? 'me' : 'other'}">
            <div class="msg-user">${escapeHtml(msg.username)}</div>
            <div class="msg-text">${escapeHtml(msg.message).replace(/\n/g, '<br>')}</div>
            <div class="msg-time">${timeStr}</div>
        </div>`;

    document.getElementById('chatBox').appendChild(wrap);
    scrollBottom();
}

/* ---------- Escape HTML to prevent XSS ---------- */
function escapeHtml(str) {
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

/* ---------- Poll for new messages ---------- */
function poll() {
    fetch(`chat_poll.php?claim_id=${CLAIM_ID}&after=${lastMsgId}`)
        .then(r => r.json())
        .then(data => {
            if (data.messages && data.messages.length > 0) {
                data.messages.forEach(msg => {
                    renderMessage(msg);
                    if (msg.id > lastMsgId) lastMsgId = msg.id;
                });
            }
            setLive(true);
        })
        .catch(() => setLive(false));
}

/* ---------- Live indicator ---------- */
function setLive(online) {
    const dot   = document.getElementById('liveIndicator');
    const label = document.getElementById('liveLabel');
    if (!dot) return;
    dot.style.background = online ? '#22c55e' : '#ef4444';
    label.textContent    = online ? 'Live' : 'Reconnecting...';
}

/* ---------- Start / stop polling ---------- */
if (CHAT_OPEN) {
    pollTimer = setInterval(poll, 3000);
    // Pause when tab is hidden (saves resources)
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(pollTimer);
        } else {
            poll(); // immediate catch-up
            pollTimer = setInterval(poll, 3000);
        }
    });
}

/* ---------- Send message via AJAX ---------- */
function sendMessage() {
    if (sending) return;
    const input = document.getElementById('msgInput');
    const text  = input.value.trim();
    if (!text) return;

    sending = true;
    const btn = document.getElementById('sendBtn');
    btn.disabled = true;
    input.disabled = true;

    fetch('chat_send.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body:    `claim_id=${CLAIM_ID}&message=${encodeURIComponent(text)}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            lastMsgId = data.message_id;
            // Optimistic render — show the message immediately without waiting for poll
            renderMessage({
                id:         data.message_id,
                sender_id:  MY_USER_ID,
                username:   '<?php echo addslashes($_SESSION["username"]); ?>',
                message:    text,
                created_at: new Date().toISOString().replace('T', ' ').substring(0, 19),
                is_me:      true
            });
        } else {
            alert(data.error || 'Failed to send message.');
        }
    })
    .catch(() => alert('Network error. Please try again.'))
    .finally(() => {
        sending       = false;
        btn.disabled  = false;
        input.disabled = false;
        input.focus();
    });
}

/* ---------- Press Enter to send (Shift+Enter for new line) ---------- */
function handleEnter(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
}
</script>

</body>
</html>