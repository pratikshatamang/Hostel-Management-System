<?php
$dbuser = "root";
$dbpass = "";
$host = "localhost";
$db = "hostel";
$mysqli = new mysqli($host, $dbuser, $dbpass, $db,3306);

if (!defined('KHALTI_ENVIRONMENT')) {
    define('KHALTI_ENVIRONMENT', 'sandbox');
}

if (!defined('KHALTI_PUBLIC_KEY')) {
    define('KHALTI_PUBLIC_KEY', '25df58e4b6064975ae40c03a799fec58');
}

if (!defined('KHALTI_SECRET_KEY')) {
    define('KHALTI_SECRET_KEY', '302c4b9c78dd4c128419a067de2eb254');
}

if (!defined('KHALTI_SANDBOX_BASE_URL')) {
    define('KHALTI_SANDBOX_BASE_URL', 'https://dev.khalti.com/api/v2/');
}

if (!defined('KHALTI_PRODUCTION_BASE_URL')) {
    define('KHALTI_PRODUCTION_BASE_URL', 'https://khalti.com/api/v2/');
}

// Email notification SMTP configuration.
if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', 'smtp.example.com');
}

if (!defined('SMTP_PORT')) {
    define('SMTP_PORT', 587);
}

if (!defined('SMTP_USERNAME')) {
    define('SMTP_USERNAME', 'your-smtp-username');
}

if (!defined('SMTP_PASSWORD')) {
    define('SMTP_PASSWORD', 'your-smtp-password');
}

if (!defined('SMTP_FROM_EMAIL')) {
    define('SMTP_FROM_EMAIL', 'no-reply@example.com');
}

if (!defined('SMTP_FROM_NAME')) {
    define('SMTP_FROM_NAME', 'Hostel Management System');
}

if (!defined('SMTP_ENCRYPTION')) {
    define('SMTP_ENCRYPTION', 'tls');
}
?>
