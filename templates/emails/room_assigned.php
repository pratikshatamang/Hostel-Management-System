<?php
$studentName = isset($emailData['student_name']) ? $emailData['student_name'] : 'Student';
$room = isset($emailData['room']) && is_array($emailData['room']) ? $emailData['room'] : array();
$roomNo = isset($room['roomno']) ? $room['roomno'] : (isset($room['room_no']) ? $room['room_no'] : '');
$subject = 'Room Assigned: Room ' . $roomNo;

$details = array(
    'Student' => $studentName,
    'Assigned Room' => $roomNo,
    'Seater' => isset($room['seater']) ? $room['seater'] : '',
    'Stay From' => isset($room['stayfrom']) ? $room['stayfrom'] : '',
    'Duration' => isset($room['duration']) ? $room['duration'] . ' month(s)' : '',
    'Monthly Fee' => isset($room['feespm']) ? 'Rs ' . hms_email_money($room['feespm']) : (isset($room['fees']) ? 'Rs ' . hms_email_money($room['fees']) : ''),
);

$html = hms_email_build_layout(
    'Room Assigned',
    'Hello ' . $studentName . ', your room assignment has been confirmed.',
    '<p style="margin:0 0 14px;font-size:15px;line-height:1.7;">The following room details are now linked with your hostel booking.</p>' .
    hms_email_build_details_table($details),
    'Please keep this email with your hostel records.'
);

$text = hms_email_build_text_message(
    'Room Assigned',
    array(
        'Hello ' . $studentName . ',',
        'Your room assignment has been confirmed.',
        'Assigned Room: ' . $roomNo,
        'Stay From: ' . (isset($room['stayfrom']) ? $room['stayfrom'] : ''),
    ),
    'Please keep this email with your hostel records.'
);

return array(
    'subject' => $subject,
    'html' => $html,
    'text' => $text,
);
