<?php
$studentName = isset($emailData['student_name']) ? $emailData['student_name'] : 'Student';
$payment = isset($emailData['payment']) && is_array($emailData['payment']) ? $emailData['payment'] : array();
$subject = 'Payment Reminder';

$details = array(
    'Student' => $studentName,
    'Amount Due' => isset($payment['amount']) ? 'Rs ' . hms_email_money($payment['amount']) : '',
    'Due Date' => isset($payment['due_date']) ? $payment['due_date'] : '',
    'Room Number' => isset($payment['roomno']) ? $payment['roomno'] : '',
    'Reference' => isset($payment['reference']) ? $payment['reference'] : '',
);

$html = hms_email_build_layout(
    'Payment Reminder',
    'Hello ' . $studentName . ', this is a reminder for your upcoming hostel payment.',
    '<p style="margin:0 0 14px;font-size:15px;line-height:1.7;">Please review the payment details below and complete the payment before the due date.</p>' .
    hms_email_build_details_table($details),
    'If you have already paid, you can ignore this reminder.'
);

$text = hms_email_build_text_message(
    'Payment Reminder',
    array(
        'Hello ' . $studentName . ',',
        'This is a reminder for your upcoming hostel payment.',
        'Amount Due: ' . (isset($payment['amount']) ? 'Rs ' . hms_email_money($payment['amount']) : ''),
        'Due Date: ' . (isset($payment['due_date']) ? $payment['due_date'] : ''),
    ),
    'If you have already paid, please ignore this reminder.'
);

return array(
    'subject' => $subject,
    'html' => $html,
    'text' => $text,
);
