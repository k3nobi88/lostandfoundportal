<?php

$host = "localhost";
$user = "root";
$pass = "";
$db = "lostfound_db";

$conn = mysqli_connect($host, $user, $pass, $db);

if(!$conn){
    die("Database connection failed: " . mysqli_connect_error());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
*/

/* Safe output (prevents XSS) */
if (!function_exists('e')) {
    function e($text){
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}



/*
|--------------------------------------------------------------------------
| ToyyibPay Configuration
|--------------------------------------------------------------------------
*/

// SANDBOX (for testing) — use dev.toyyibpay.com credentials
define('TOYYIBPAY_SECRET_KEY',   'ln1x0pzd-rocn-8ugn-fkq1-izbsrachmin2');
define('TOYYIBPAY_CATEGORY_CODE','d1c6c9i2');
define('TOYYIBPAY_BASE_URL',     'https://dev.toyyibpay.com');

// Your site's public URL (use your ngrok URL while testing on localhost)
// e.g. 'https://abc123.ngrok-free.app/lostandfoundportal'
define('SITE_URL', 'https://scruffy-navigate-daylong.ngrok-free.dev/lostandfoundportal');

// When you go live, change the above three to:
// define('TOYYIBPAY_SECRET_KEY',   'your_real_secret_key');
// define('TOYYIBPAY_CATEGORY_CODE','your_real_category_code');
// define('TOYYIBPAY_BASE_URL',     'https://toyyibpay.com');
// define('SITE_URL',               'https://yourdomain.com');



/*
|--------------------------------------------------------------------------
| Email Configuration (PHPMailer + Gmail SMTP)
|--------------------------------------------------------------------------
*/
define('MAIL_HOST',     'smtp.gmail.com');
define('MAIL_PORT',     587);
define('MAIL_USERNAME', 'neroipan@gmail.com');       // your Gmail address
define('MAIL_PASSWORD', ':yooo iidd ylnc yvtk');        // your App Password (with spaces is fine)
define('MAIL_FROM',     'neroipan@gmail.com');
define('MAIL_FROM_NAME','Lost & Found Portal');


?>