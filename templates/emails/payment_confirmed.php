<?php
$studentName = isset($emailData['student_name']) ? $emailData['student_name'] : 'Student';
$payment = isset($emailData['payment']) && is_array($emailData['payment']) ? $emailData['payment'] : array();
$subject = 'Payment Confirmed';

$details = array(
    'Student' => $studentName,
    'Amount' => isset($payment['amount']) ? 'Rs ' . hms_email_money($payment['amount']) : '',
    'Room Number' => isset($payment['roomno']) ? $payment['roomno'] : '',
    'Payment Date' => isset($payment['payment_date']) ? $payment['payment_date'] : '',
    'Reference' => isset($payment['reference']) ? $payment['reference'] : '',
);

$html = hms_email_build_layout(
    'Payment Confirmed',
    'Hello ' . $studentName . ', your hostel payment has been confirmed.',
    '<p style="margin:0 0 14px;font-size:15px;line-height:1.7;">Thank you. The hostel office has recorded your payment.</p>' .
    hms_email_build_details_table($details),
    'Keep this email as proof of payment confirmation.'
);

$text = hms_email_build_text_message(
    'Payment Confirmed',
    array(
        'Hello ' . $studentName . ',',
        'Your hostel payment has been confirmed.',
        'Amount: ' . (isset($payment['amount']) ? 'Rs ' . hms_email_money($payment['amount']) : ''),
        'Reference: ' . (isset($payment['reference']) ? $payment['reference'] : ''),
    ),
    'Keep this email as proof of payment confirmation.'
);

return array(
    'subject' => $subject,
    'html' => $html,
    'text' => $text,
);
