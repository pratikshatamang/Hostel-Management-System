<?php
$payment = isset($emailData['payment']) && is_array($emailData['payment']) ? $emailData['payment'] : array();
$subject = 'Admin Alert: Payment Received';

$details = array(
    'Student' => isset($payment['student_name']) ? $payment['student_name'] : '',
    'Email' => isset($payment['email']) ? $payment['email'] : '',
    'Amount' => isset($payment['amount']) ? 'Rs ' . hms_email_money($payment['amount']) : '',
    'Reference' => isset($payment['reference']) ? $payment['reference'] : '',
    'Payment Date' => isset($payment['payment_date']) ? $payment['payment_date'] : '',
);

$html = hms_email_build_layout(
    'Payment Received Alert',
    'A hostel payment has been recorded in the system.',
    '<p style="margin:0 0 14px;font-size:15px;line-height:1.7;">Review the payment summary below.</p>' .
    hms_email_build_details_table($details),
    'This email is ready for future payment-module integration.'
);

$text = hms_email_build_text_message(
    'Payment Received Alert',
    array(
        'A hostel payment has been recorded.',
        'Student: ' . (isset($payment['student_name']) ? $payment['student_name'] : ''),
        'Amount: ' . (isset($payment['amount']) ? 'Rs ' . hms_email_money($payment['amount']) : ''),
        'Reference: ' . (isset($payment['reference']) ? $payment['reference'] : ''),
    ),
    'This alert is ready for future payment-module integration.'
);

return array(
    'subject' => $subject,
    'html' => $html,
    'text' => $text,
);
