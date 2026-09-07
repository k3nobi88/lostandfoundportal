<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

/**
 * Send an email via Gmail SMTP.
 *
 * @param string $toEmail    Recipient email address
 * @param string $toName     Recipient name
 * @param string $subject    Email subject
 * @param string $htmlBody   HTML content of the email
 * @return bool              true on success, false on failure
 */
function sendMail($toEmail, $toName, $subject, $htmlBody) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ]
        ];

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer error: ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Reusable HTML email template that matches your site's dark style.
 * Wrap your content in this for consistent-looking emails.
 */
function mailTemplate($title, $bodyHtml) {
    return '
    <!DOCTYPE html>
    <html>
    <body style="margin:0;padding:0;background:#0d0d0d;font-family:Arial,sans-serif;">
      <table width="100%" cellpadding="0" cellspacing="0" style="background:#0d0d0d;padding:40px 20px;">
        <tr><td align="center">
          <table width="560" cellpadding="0" cellspacing="0" style="background:#111;border-radius:16px;border:1px solid #1f1f1f;overflow:hidden;">

            <tr>
              <td style="background:#cc0000;padding:24px 32px;">
                <p style="margin:0;font-size:20px;font-weight:900;color:white;letter-spacing:1px;">
                  LOST &amp; <span style="color:#fff;">FOUND</span>
                </p>
              </td>
            </tr>

            <tr>
              <td style="padding:32px;">
                <h2 style="color:white;margin:0 0 16px;font-size:20px;">' . $title . '</h2>
                ' . $bodyHtml . '
              </td>
            </tr>

            <tr>
              <td style="padding:20px 32px;border-top:1px solid #1f1f1f;">
                <p style="margin:0;font-size:12px;color:#444;">
                  Lost &amp; Found Portal &nbsp;·&nbsp; Malaysia<br>
                  This is an automated message. Do not reply to this email.
                </p>
              </td>
            </tr>

          </table>
        </td></tr>
      </table>
    </body>
    </html>';
}