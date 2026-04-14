<?php
$studentName = isset($emailData['student_name']) ? $emailData['student_name'] : 'Student';
$booking = isset($emailData['booking']) && is_array($emailData['booking']) ? $emailData['booking'] : array();
$roomNo = isset($booking['roomno']) ? $booking['roomno'] : '';
$duration = isset($booking['duration']) ? $booking['duration'] : '';
$subject = 'Booking Submitted: Room ' . $roomNo;

$details = array(
    'Student' => $studentName,
    'Room Number' => $roomNo,
    'Stay From' => isset($booking['stayfrom']) ? $booking['stayfrom'] : '',
    'Duration' => $duration !== '' ? $duration . ' month(s)' : '',
    'Monthly Fee' => isset($booking['feespm']) ? 'Rs ' . hms_email_money($booking['feespm']) : '',
    'Food Preference' => isset($booking['foodstatus']) ? ((int) $booking['foodstatus'] === 1 ? 'Included' : 'Not Included') : '',
    'Registration No' => isset($booking['regno']) ? $booking['regno'] : '',
);

$html = hms_email_build_layout(
    'Booking Submitted',
    'Hello ' . $studentName . ', your hostel booking has been saved successfully. Your booking summary is below for reference.',
    '<p style="margin:0 0 14px;font-size:15px;line-height:1.7;">We have recorded your hostel booking in the system. Keep this email for your records.</p>' .
    hms_email_build_details_table($details),
    'If any of the booking details are incorrect, please contact the hostel administrator.'
);

$text = hms_email_build_text_message(
    'Booking Submitted',
    array(
        'Hello ' . $studentName . ',',
        'Your hostel booking has been saved successfully.',
        'Room Number: ' . $roomNo,
        'Stay From: ' . (isset($booking['stayfrom']) ? $booking['stayfrom'] : ''),
        'Duration: ' . ($duration !== '' ? $duration . ' month(s)' : ''),
        'Monthly Fee: ' . (isset($booking['feespm']) ? 'Rs ' . hms_email_money($booking['feespm']) : ''),
    ),
    'If you need help, please contact the hostel administrator.'
);

return array(
    'subject' => $subject,
    'html' => $html,
    'text' => $text,
);
