<?php
require_once("includes/config.php");
require_once("includes/booking.php");

$email = 'srijana1@gmail.com'; // based on screenshot
$userPrefs = hms_get_user_room_preferences($mysqli, $email);
var_dump($userPrefs);

$hasPreferences = $userPrefs !== null && hms_has_room_preferences($userPrefs);
var_dump($hasPreferences);
?>
