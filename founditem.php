<?php
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$success = false;
$error   = '';

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $user_id     = $_SESSION['user_id'];
    $title       = mysqli_real_escape_string($conn, trim($_POST['title']));
    $category = mysqli_real_escape_string($conn, $_POST['category']);
	$allowed_categories = ['item', 'pet', 'vehicle', 'person'];
	if(!in_array($category, $allowed_categories)){
		$category = 'item';
	}
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $location    = mysqli_real_escape_string($conn, trim($_POST['location']));
    $date_found = mysqli_real_escape_string($conn, $_POST['date_found']);
	if(empty($date_found) || strtotime($date_found) === false || strtotime($date_found) > time()){
		$error = "Please enter a valid date that is not in the future.";
	}
    $reward      = mysqli_real_escape_string($conn, trim($_POST['reward'] ?? ''));
	$lat = mysqli_real_escape_string($conn, $_POST['lat'] ?? '');
	$lng = mysqli_real_escape_string($conn, $_POST['lng'] ?? '');

    // Build extra details based on category
    $extra = [];

    if($category == 'vehicle'){
        $extra[] = "Vehicle Type: "  . $_POST['vehicle_type'];
        $extra[] = "Plate Number: "  . $_POST['plate_number'];
        $extra[] = "Vehicle Color: " . $_POST['vehicle_color'];
    }

    if($category == 'pet'){
        $extra[] = "Pet Type: "  . $_POST['pet_type'];
        $extra[] = "Pet Name: "  . $_POST['pet_name'];
        $extra[] = "Breed: "     . $_POST['pet_breed'];
        $extra[] = "Color: "     . $_POST['pet_color'];
    }

    if($category == 'person'){
        $extra[] = "Name: "                    . $_POST['person_name'];
        $extra[] = "Estimated Age: "           . $_POST['person_age'];
        $extra[] = "Wearing: "                 . $_POST['person_wearing'];
        $extra[] = "Distinguishing features: " . $_POST['person_features'];
        $extra[] = "Condition when found: "    . $_POST['person_condition'];
    }

    if(!empty($extra)){
        $description = implode("\n", $extra) . "\n\n" . $description;
    }

    $description = mysqli_real_escape_string($conn, $description);

    // Multi-image upload
    $firstImage    = "";
    $uploadedImages = [];
    $maxFileSize   = 5 * 1024 * 1024;
    $allowed       = ['jpg','jpeg','png','gif','webp'];

    if(isset($_FILES['images']) && count($_FILES['images']['name']) > 0){
        $fileCount = min(5, count($_FILES['images']['name']));
        for($i = 0; $i < $fileCount; $i++){
            if($_FILES['images']['error'][$i] != 0) continue;
            if($_FILES['images']['size'][$i] > $maxFileSize){
                $error = "One or more images exceed 5MB."; break;
            }
            $ext = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
            if(!in_array($ext, $allowed)){
                $error = "Invalid image format. Use JPG, PNG, GIF or WEBP."; break;
            }
            $filename         = time() . '_' . $i . '_' . basename($_FILES['images']['name'][$i]);
            $path             = "uploads/" . $filename;
            if(move_uploaded_file($_FILES['images']['tmp_name'][$i], $path)){
                $uploadedImages[] = $path;
                if($i === 0) $firstImage = $path;
            }
        }
    }
    $image = $firstImage; // first image stays in listings.image for backwards compat

    if(empty($error)){
        $sql = "INSERT INTO listings
			(user_id, title, description, category, location, date_lost, reward, image, listing_type, lat, lng)
			VALUES
			('$user_id', '$title', '$description', '$category', '$location', '$date_found', '$reward', '$image', 'found', '$lat', '$lng')";


        if(mysqli_query($conn, $sql)){
            $newListingId = $conn->insert_id;
            // Save additional images to listing_images
            if(count($uploadedImages) > 0){
                foreach($uploadedImages as $idx => $imgPath){
                    $imgStmt = $conn->prepare("INSERT INTO listing_images (listing_id, image, sort_order) VALUES (?,?,?)");
                    $imgStmt->bind_param('isi', $newListingId, $imgPath, $idx);
                    $imgStmt->execute();
                    $imgStmt->close();
                }
            }
            $success = true;
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Report Found Item – Lost & Found</title>
    <link rel="stylesheet" href="style.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<?php include 'navbar.php'; ?>

<?php if($success): ?>

    <!-- SUCCESS STATE -->
    <div class="report-success">
        <div class="report-success-icon">✅</div>
        <h2>Report Submitted!</h2>
        <p>Thank you for being a community hero! Your found item report has been published. The owner will be able to submit a claim.</p>
        <div class="report-success-btns">
            <a href="browse.php" class="btn">Browse Listings</a>
            <a href="founditem.php" class="btn btn-outline">Submit Another</a>
        </div>
    </div>

<?php else: ?>

    <div class="report-page">

        <!-- LEFT: Form -->
        <div class="report-left">

            <div class="report-header">
                <span class="report-type-tag found-tag">🟢 Found Item</span>
                <h1>Report a Found Item</h1>
                <p>Help reunite this item with its owner. Fill in as much detail as possible.</p>
            </div>

            <?php if($error): ?>
                <div class="auth-error" style="margin-bottom:20px;">⚠️ <?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" class="report-form" enctype="multipart/form-data" id="reportForm">

                <!-- CATEGORY SELECTOR -->
                <div class="category-selector">
                    <p class="report-field-label">What did you find?</p>
                    <div class="cat-btn-group">
                        <label class="cat-btn active">
                            <input type="radio" name="category" value="item" checked onchange="toggleFields('item')">
                            🎒 Item
                        </label>
                        <label class="cat-btn">
                            <input type="radio" name="category" value="pet" onchange="toggleFields('pet')">
                            🐾 Pet
                        </label>
                        <label class="cat-btn">
                            <input type="radio" name="category" value="vehicle" onchange="toggleFields('vehicle')">
                            🚗 Vehicle
                        </label>
                        <label class="cat-btn">
                            <input type="radio" name="category" value="person" onchange="toggleFields('person')">
                            👤 Person
                        </label>
                    </div>
                </div>

                <!-- BASIC FIELDS -->
                <div class="report-section">
                    <p class="report-section-title">Basic Information</p>

                    <div class="report-field">
                        <label>Title / Name <span class="required">*</span></label>
                        <input type="text" name="title" placeholder="e.g. Black wallet found near Padungan, Stray cat near Tabuan Jaya..." required>
                    </div>

                    <div class="report-field">
						<label>Location Found <span class="required">*</span></label>
						<div class="location-search-wrap">
							<input type="text" id="locationSearch" placeholder="Type an address or place name..." autocomplete="off">
							<button type="button" class="location-search-btn" onclick="searchLocation()">🔍 Search</button>
						</div>
						<input type="hidden" name="location" id="locationText">
						<input type="hidden" name="lat" id="latInput">
						<input type="hidden" name="lng" id="lngInput">
						<p class="location-hint" id="locationHint">Search for a location or click on the map to drop a pin</p>
					</div>

					<div class="map-picker-wrap">
						<div id="mapPicker"></div>
					</div>

					<div class="report-field" style="margin-top:16px;">
						<label>Date Found <span class="required">*</span></label>
						<input type="date" name="date_found" max="<?php echo date('Y-m-d'); ?>" required>
					</div>

                </div>

                <!-- ITEM FIELDS -->
                <div id="fields-item" class="report-section cat-fields">
                    <p class="report-section-title">Item Details</p>
                    <div class="report-field">
                        <label>Item Description <span class="required">*</span></label>
                        <textarea name="description" placeholder="Color, brand, size, condition, any contents or identifying features..." required rows="4"></textarea>
                    </div>
                </div>

                <!-- PET FIELDS -->
                <div id="fields-pet" class="report-section cat-fields" style="display:none;">
                    <p class="report-section-title">Pet Details</p>
                    <div class="report-row">
                        <div class="report-field">
                            <label>Pet Type</label>
                            <select name="pet_type">
                                <option value="">Select type</option>
                                <option value="Dog">Dog</option>
                                <option value="Cat">Cat</option>
                                <option value="Bird">Bird</option>
                                <option value="Rabbit">Rabbit</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="report-field">
                            <label>Pet Name (if tagged)</label>
                            <input type="text" name="pet_name" placeholder="Name on tag if any">
                        </div>
                    </div>
                    <div class="report-row">
                        <div class="report-field">
                            <label>Breed (if known)</label>
                            <input type="text" name="pet_breed" placeholder="e.g. Persian, Labrador...">
                        </div>
                        <div class="report-field">
                            <label>Color / Markings</label>
                            <input type="text" name="pet_color" placeholder="e.g. Orange tabby with white paws...">
                        </div>
                    </div>
                    <div class="report-field">
                        <label>Additional Description <span class="required">*</span></label>
                        <textarea name="description" placeholder="Collar, tags, condition when found, behavior, any injuries..." required rows="4"></textarea>
                    </div>
                </div>

                <!-- VEHICLE FIELDS -->
                <div id="fields-vehicle" class="report-section cat-fields" style="display:none;">
                    <p class="report-section-title">Vehicle Details</p>
                    <div class="report-row">
                        <div class="report-field">
                            <label>Vehicle Type</label>
                            <select name="vehicle_type">
                                <option value="">Select type</option>
                                <option value="Car">Car</option>
                                <option value="Motorcycle">Motorcycle</option>
                                <option value="Bicycle">Bicycle</option>
                                <option value="Truck">Truck</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="report-field">
                            <label>Plate Number (if visible)</label>
                            <input type="text" name="plate_number" placeholder="e.g. QA 1234 B">
                        </div>
                    </div>
                    <div class="report-field">
                        <label>Vehicle Color</label>
                        <input type="text" name="vehicle_color" placeholder="e.g. White, Red Metallic...">
                    </div>
                    <div class="report-field">
                        <label>Additional Description <span class="required">*</span></label>
                        <textarea name="description" placeholder="Make, model, condition, where it was found parked or abandoned..." required rows="4"></textarea>
                    </div>
                </div>

                <!-- PERSON FIELDS -->
                <div id="fields-person" class="report-section cat-fields" style="display:none;">
                    <p class="report-section-title">Person Details</p>
                    <div class="report-row">
                        <div class="report-field">
                            <label>Name (if known)</label>
                            <input type="text" name="person_name" placeholder="Name if they told you or from ID">
                        </div>
                        <div class="report-field">
                            <label>Estimated Age</label>
                            <input type="number" name="person_age" placeholder="Approximate age">
                        </div>
                    </div>
                    <div class="report-field">
                        <label>Wearing / Appearance</label>
                        <input type="text" name="person_wearing" placeholder="e.g. Blue shirt, black pants, white sneakers...">
                    </div>
                    <div class="report-field">
                        <label>Distinguishing Features</label>
                        <input type="text" name="person_features" placeholder="e.g. Glasses, birthmark, uses wheelchair...">
                    </div>
                    <div class="report-field">
                        <label>Condition When Found</label>
                        <input type="text" name="person_condition" placeholder="e.g. Confused, injured, unresponsive...">
                    </div>
                    <div class="report-field">
                        <label>Additional Description <span class="required">*</span></label>
                        <textarea name="description" placeholder="Any other details that could help identify this person..." required rows="3"></textarea>
                    </div>
                </div>

                <!-- REWARD & IMAGE -->
                <div class="report-section">
                    <p class="report-section-title">Additional Info</p>

                    <div class="report-row">
                        <div class="report-field">
                            <label>Reward Expected (RM)</label>
                            <input type="number" name="reward" placeholder="Leave blank if not expecting reward" min="0">
                        </div>
						
                        <div class="report-field">
							<label>Photos <span class="required">*</span></label>
							<div class="file-upload-wrap">
								<label class="file-upload-btn" for="imageInput">📷 Choose Photos</label>
								<input type="file" name="images[]" id="imageInput" accept="image/*" multiple onchange="previewImages(this)">
								<p class="file-hint">Select up to 5 images · JPG, PNG, WEBP or GIF · Max 5MB each</p>
							</div>
						</div>

						<div id="multiPreview" class="multi-preview-wrap"></div>
                    </div>

                    <!-- Image preview -->
                    <div id="imagePreview" style="display:none; margin-top:16px; aspect-ratio:4/3; overflow:hidden;">
						<img id="previewImg" src="" alt="Preview">
					</div>

                </div>

                <button type="button" class="auth-submit-btn" style="margin-top:10px;" onclick="submitForm()">
					📢 Submit Found Item Report
				</button>

			</form> <!-- 1. Close form first -->
        </div> <!-- 2. Then close report-left -->

        <!-- RIGHT: Tips -->
        <div class="report-right">

            <div class="report-tips">
                <h3>📝 Tips for a Good Report</h3>
                <ul>
                    <li>Upload a clear photo of the item you found</li>
                    <li>Be specific about where exactly you found it</li>
                    <li>Don't include information only the owner would know — use that to verify claims</li>
                    <li>Mention the condition of the item when found</li>
                    <li>Respond to claims promptly</li>
                </ul>
            </div>

            <div class="report-tips" style="margin-top:20px;">
                <h3>⚠️ Important</h3>
                <ul>
                    <li>Keep the item safe until the owner collects it</li>
                    <li>Arrange handovers in safe, public locations</li>
                    <li>Ask the claimant to verify ownership before handing over</li>
                    <li>For found persons or emergencies, contact authorities first</li>
                </ul>
            </div>

        </div>

    </div>

<?php endif; ?>

<?php include 'footer.php'; ?>

<script>
// ====== CATEGORY TOGGLE ======
function toggleFields(cat) {
    document.querySelectorAll('.cat-fields').forEach(el => el.style.display = 'none');
    document.getElementById('fields-' + cat).style.display = 'block';
    document.querySelectorAll('.cat-btn').forEach(btn => btn.classList.remove('active'));
    event.currentTarget.closest('.cat-btn').classList.add('active');
}

// ====== MULTI IMAGE PREVIEW ======
function previewImages(input) {
    const wrap = document.getElementById('multiPreview');
    wrap.innerHTML = ''; // Clear out any previous selections
    
    if (input.files) {
        // Limit processing to maximum 5 files
        const files = Array.from(input.files).slice(0, 5);
        
        files.forEach((file, i) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'multi-preview-thumb';
                
                // If it is the first image index (0), label it as the Main Cover display
                div.innerHTML = `
                    <img src="${e.target.result}" alt="Preview ${i + 1}">
                    ${i === 0 ? '<span class="thumb-label">Cover</span>' : ''}
                `;
                wrap.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }
}

// ====== MAP PICKER ======
let map, marker, geocoder;

function initMap() {
    const defaultCenter = { lat: 1.5533, lng: 110.3592 }; // Kuching, Sarawak

    map = new google.maps.Map(document.getElementById('mapPicker'), {
        center: defaultCenter,
        zoom: 13,
        styles: [
            { elementType: 'geometry',        stylers: [{ color: '#1a1a1a' }] },
            { elementType: 'labels.text.fill',stylers: [{ color: '#757575' }] },
            { elementType: 'labels.text.stroke',stylers:[{ color: '#212121' }] },
            { featureType: 'road',            elementType: 'geometry',        stylers: [{ color: '#2c2c2c' }] },
            { featureType: 'road',            elementType: 'labels.text.fill',stylers: [{ color: '#8a8a8a' }] },
            { featureType: 'water',           elementType: 'geometry',        stylers: [{ color: '#000000' }] },
            { featureType: 'poi',             elementType: 'geometry',        stylers: [{ color: '#1a1a1a' }] },
            { featureType: 'transit',         elementType: 'geometry',        stylers: [{ color: '#1a1a1a' }] },
        ]
    });

    geocoder = new google.maps.Geocoder();

    // Click on map to drop pin
    map.addListener('click', function(e) {
        placeMarker(e.latLng);
        reverseGeocode(e.latLng);
    });
}

function placeMarker(latLng) {
    if(marker) marker.setMap(null);

    marker = new google.maps.Marker({
        position: latLng,
        map: map,
        animation: google.maps.Animation.DROP,
        icon: {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 10,
            fillColor: '#ff0000',
            fillOpacity: 1,
            strokeColor: '#ffffff',
            strokeWeight: 2
        }
    });

    document.getElementById('latInput').value = latLng.lat();
    document.getElementById('lngInput').value = latLng.lng();
}

function reverseGeocode(latLng) {
    geocoder.geocode({ location: latLng }, function(results, status) {
        if(status === 'OK' && results[0]){
            const address = results[0].formatted_address;
            document.getElementById('locationSearch').value = address;
            document.getElementById('locationText').value   = address;
            document.getElementById('locationHint').textContent = '📍 ' + address;
            document.getElementById('locationHint').style.color = '#22c55e';
        }
    });
}

function searchLocation() {
    const query = document.getElementById('locationSearch').value.trim();
    if(!query) return;

    geocoder.geocode({ address: query }, function(results, status) {
        if(status === 'OK'){
            const loc = results[0].geometry.location;
            map.setCenter(loc);
            map.setZoom(16);
            placeMarker(loc);

            const address = results[0].formatted_address;
            document.getElementById('locationText').value   = address;
            document.getElementById('locationHint').textContent = '📍 ' + address;
            document.getElementById('locationHint').style.color = '#22c55e';
        } else {
            document.getElementById('locationHint').textContent = '⚠️ Location not found. Try a different search.';
            document.getElementById('locationHint').style.color = '#ff4444';
        }
    });
}

function submitForm() {
    const location = document.getElementById('locationText').value.trim();
    const hint     = document.getElementById('locationHint');

    if(!location){
        hint.textContent = '⚠️ Please search for a location or click on the map to drop a pin.';
        hint.style.color = '#ff4444';
        document.getElementById('locationSearch').scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    document.getElementById('reportForm').submit();
}

// Allow pressing Enter to search
document.addEventListener('DOMContentLoaded', function(){
    document.getElementById('locationSearch').addEventListener('keydown', function(e){
        if(e.key === 'Enter'){ e.preventDefault(); searchLocation(); }
    });
});
</script>

<!-- Load Google Maps AFTER the script above -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyChzn_JIEytG6xbhuf3Wu1UGO-3de_4m0A&callback=initMap" async defer></script>

</body>
</html>