<?php
$studentName = isset($emailData['student_name']) ? $emailData['student_name'] : 'Student';
$booking = isset($emailData['booking']) && is_array($emailData['booking']) ? $emailData['booking'] : array();
$reason = isset($emailData['reason']) ? trim((string) $emailData['reason']) : '';
$subject = 'Booking Update: Request Not Approved';

$details = array(
    'Student' => $studentName,
    'Requested Room' => isset($booking['roomno']) ? $booking['roomno'] : '',
    'Stay From' => isset($booking['stayfrom']) ? $booking['stayfrom'] : '',
    'Reason' => $reason,
);

$html = hms_email_build_layout(
    'Booking Rejected',
    'Hello ' . $studentName . ', we could not approve your hostel booking request.',
    '<p style="margin:0 0 14px;font-size:15px;line-height:1.7;">Please review the update below and contact the hostel office if you would like help submitting another booking.</p>' .
    hms_email_build_details_table($details),
    'You can submit a fresh booking request when an appropriate room becomes available.'
);

$text = hms_email_build_text_message(
    'Booking Rejected',
    array(
        'Hello ' . $studentName . ',',
        'We could not approve your hostel booking request.',
        'Requested Room: ' . (isset($booking['roomno']) ? $booking['roomno'] : ''),
        'Reason: ' . ($reason !== '' ? $reason : 'Not provided'),
    ),
    'Please contact the hostel office for further assistance.'
);

return array(
    'subject' => $subject,
    'html' => $html,
    'text' => $text,
);
