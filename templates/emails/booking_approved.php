<?php
$studentName = isset($emailData['student_name']) ? $emailData['student_name'] : 'Student';
$booking = isset($emailData['booking']) && is_array($emailData['booking']) ? $emailData['booking'] : array();
$roomNo = isset($booking['roomno']) ? $booking['roomno'] : '';
$subject = 'Booking Approved: Room ' . $roomNo;

$details = array(
    'Student' => $studentName,
    'Approved Room' => $roomNo,
    'Stay From' => isset($booking['stayfrom']) ? $booking['stayfrom'] : '',
    'Duration' => isset($booking['duration']) ? $booking['duration'] . ' month(s)' : '',
    'Monthly Fee' => isset($booking['feespm']) ? 'Rs ' . hms_email_money($booking['feespm']) : '',
);

$html = hms_email_build_layout(
    'Booking Approved',
    'Hello ' . $studentName . ', your hostel booking has been approved.',
    '<p style="margin:0 0 14px;font-size:15px;line-height:1.7;">Your booking has been approved by the hostel administration. Please review the confirmed details below.</p>' .
    hms_email_build_details_table($details),
    'Please bring any required documents or payment confirmation on your reporting date.'
);

$text = hms_email_build_text_message(
    'Booking Approved',
    array(
        'Hello ' . $studentName . ',',
        'Your hostel booking has been approved.',
        'Room Number: ' . $roomNo,
        'Stay From: ' . (isset($booking['stayfrom']) ? $booking['stayfrom'] : ''),
    ),
    'Please contact the hostel team if you need any clarification.'
);

return array(
    'subject' => $subject,
    'html' => $html,
    'text' => $text,
);
