<?php
$booking = isset($emailData['booking']) && is_array($emailData['booking']) ? $emailData['booking'] : array();
$studentName = trim((isset($booking['firstName']) ? $booking['firstName'] : '') . ' ' . (isset($booking['middleName']) ? $booking['middleName'] : '') . ' ' . (isset($booking['lastName']) ? $booking['lastName'] : ''));
$subject = 'Admin Alert: New Hostel Booking';

$details = array(
    'Student' => $studentName,
    'Email' => isset($booking['emailid']) ? $booking['emailid'] : '',
    'Registration No' => isset($booking['regno']) ? $booking['regno'] : '',
    'Room Number' => isset($booking['roomno']) ? $booking['roomno'] : '',
    'Stay From' => isset($booking['stayfrom']) ? $booking['stayfrom'] : '',
    'Duration' => isset($booking['duration']) ? $booking['duration'] . ' month(s)' : '',
);

$html = hms_email_build_layout(
    'New Booking Alert',
    'A new hostel booking has been submitted in the system.',
    '<p style="margin:0 0 14px;font-size:15px;line-height:1.7;">Booking details are included below for quick review.</p>' .
    hms_email_build_details_table($details),
    'This alert was generated automatically after a successful booking save.'
);

$text = hms_email_build_text_message(
    'New Booking Alert',
    array(
        'A new hostel booking has been submitted.',
        'Student: ' . $studentName,
        'Email: ' . (isset($booking['emailid']) ? $booking['emailid'] : ''),
        'Room Number: ' . (isset($booking['roomno']) ? $booking['roomno'] : ''),
    ),
    'Please review the booking in the admin panel.'
);

return array(
    'subject' => $subject,
    'html' => $html,
    'text' => $text,
);
