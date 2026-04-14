<?php
require_once __DIR__ . '/auth.php';

if (!function_exists('hms_email_project_root')) {
    function hms_email_project_root()
    {
        return dirname(__DIR__);
    }

    function hms_email_app_name()
    {
        return defined('SMTP_FROM_NAME') && trim((string) SMTP_FROM_NAME) !== ''
            ? trim((string) SMTP_FROM_NAME)
            : 'Hostel Management System';
    }

    function hms_email_escape($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    function hms_email_money($amount)
    {
        $amount = (float) $amount;
        if ((int) $amount == $amount) {
            return (string) (int) $amount;
        }

        return number_format($amount, 2);
    }

    function hms_email_normalize_name($value, $fallback)
    {
        $value = trim((string) $value);
        return $value !== '' ? $value : $fallback;
    }

    function hms_email_build_layout($title, $intro, $contentHtml, $footerNote = '')
    {
        $brand = hms_email_escape(hms_email_app_name());
        $title = hms_email_escape($title);
        $intro = nl2br(hms_email_escape($intro));
        $footerNote = trim((string) $footerNote);
        $footerHtml = $footerNote !== ''
            ? '<p style="margin:20px 0 0;color:#6b7280;font-size:13px;line-height:1.7;">' . nl2br(hms_email_escape($footerNote)) . '</p>'
            : '';

        return '<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $title . '</title>
</head>
<body style="margin:0;padding:0;background:#eef3f8;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#eef3f8;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;background:#ffffff;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="padding:28px 32px;background:#16324f;color:#ffffff;">
                            <div style="font-size:12px;letter-spacing:1px;text-transform:uppercase;opacity:0.9;">' . $brand . '</div>
                            <h1 style="margin:12px 0 0;font-size:28px;line-height:1.2;">' . $title . '</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 20px;font-size:15px;line-height:1.7;color:#475569;">' . $intro . '</p>
                            ' . $contentHtml . '
                            ' . $footerHtml . '
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 32px;background:#f8fafc;color:#64748b;font-size:12px;line-height:1.6;">
                            This is an automated message from ' . $brand . '. Please do not reply to this email unless your hostel team has asked you to.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }

    function hms_email_build_details_table(array $details)
    {
        $rows = '';
        foreach ($details as $label => $value) {
            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }

            $rows .= '<tr>
                <td style="padding:12px 14px;border-bottom:1px solid #e5e7eb;background:#f8fafc;font-weight:bold;width:180px;">' . hms_email_escape($label) . '</td>
                <td style="padding:12px 14px;border-bottom:1px solid #e5e7eb;">' . hms_email_escape($value) . '</td>
            </tr>';
        }

        if ($rows === '') {
            return '';
        }

        return '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;border-collapse:separate;border-spacing:0;margin:20px 0;">
            ' . $rows . '
        </table>';
    }

    function hms_email_build_text_message($title, array $lines, $footerNote = '')
    {
        $text = trim((string) $title) . "\n\n";
        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line !== '') {
                $text .= $line . "\n";
            }
        }

        if (trim((string) $footerNote) !== '') {
            $text .= "\n" . trim((string) $footerNote) . "\n";
        }

        return trim($text);
    }

    function hms_bootstrap_email_schema($mysqli)
    {
        static $bootstrapped = false;

        if ($bootstrapped || !($mysqli instanceof mysqli) || $mysqli->connect_errno) {
            return;
        }

        $bootstrapped = true;
        $mysqli->query(
            "CREATE TABLE IF NOT EXISTS `email_logs` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) DEFAULT NULL,
                `email_to` varchar(255) NOT NULL,
                `subject` varchar(255) NOT NULL,
                `type` varchar(50) NOT NULL DEFAULT 'general',
                `status` enum('sent','failed') NOT NULL DEFAULT 'sent',
                `error_message` text DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_email_logs_user_id` (`user_id`),
                KEY `idx_email_logs_status` (`status`),
                KEY `idx_email_logs_type` (`type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

    function hms_log_email_result($to, $subject, $type, $status, $errorMessage = null, $userId = null)
    {
        global $mysqli;

        if (!isset($mysqli) || !($mysqli instanceof mysqli) || $mysqli->connect_errno) {
            error_log('Email log unavailable: ' . trim((string) $status) . ' -> ' . trim((string) $to) . ' | ' . trim((string) $subject));
            return false;
        }

        hms_bootstrap_email_schema($mysqli);

        $emailTo = substr((string) $to, 0, 255);
        $emailSubject = substr((string) $subject, 0, 255);
        $emailType = substr((string) $type, 0, 50);
        $emailStatus = $status === 'sent' ? 'sent' : 'failed';
        $errorMessage = $errorMessage !== null ? (string) $errorMessage : null;
        $resolvedUserId = $userId !== null ? (int) $userId : null;

        $stmt = $mysqli->prepare('INSERT INTO email_logs(user_id, email_to, subject, type, status, error_message) VALUES(?,?,?,?,?,?)');
        if (!$stmt) {
            error_log('Failed to prepare email log insert.');
            return false;
        }

        $stmt->bind_param('isssss', $resolvedUserId, $emailTo, $emailSubject, $emailType, $emailStatus, $errorMessage);
        $result = $stmt->execute();
        $stmt->close();

        return (bool) $result;
    }

    function hms_email_get_user_id_by_email($email)
    {
        global $mysqli;

        if (!isset($mysqli) || !($mysqli instanceof mysqli) || $mysqli->connect_errno) {
            return null;
        }

        if (!hms_table_exists($mysqli, 'users')) {
            return null;
        }

        $stmt = $mysqli->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->bind_result($userId);
        $rowFound = $stmt->fetch();
        $stmt->close();

        return $rowFound ? (int) $userId : null;
    }

    function hms_email_mailer_candidates()
    {
        $root = hms_email_project_root();

        return array(
            array(
                'autoload' => $root . '/vendor/autoload.php',
            ),
            array(
                'src' => $root . '/vendor/phpmailer/phpmailer/src',
            ),
            array(
                'src' => $root . '/third_party/PHPMailer/src',
            ),
            array(
                'src' => $root . '/includes/PHPMailer/src',
            ),
        );
    }

    function hms_email_load_phpmailer()
    {
        if (class_exists('\\PHPMailer\\PHPMailer\\PHPMailer')) {
            return true;
        }

        foreach (hms_email_mailer_candidates() as $candidate) {
            if (!empty($candidate['autoload']) && file_exists($candidate['autoload'])) {
                require_once $candidate['autoload'];
                if (class_exists('\\PHPMailer\\PHPMailer\\PHPMailer')) {
                    return true;
                }
            }

            if (!empty($candidate['src'])) {
                $exceptionFile = $candidate['src'] . '/Exception.php';
                $phpMailerFile = $candidate['src'] . '/PHPMailer.php';
                $smtpFile = $candidate['src'] . '/SMTP.php';

                if (file_exists($exceptionFile) && file_exists($phpMailerFile) && file_exists($smtpFile)) {
                    require_once $exceptionFile;
                    require_once $phpMailerFile;
                    require_once $smtpFile;

                    if (class_exists('\\PHPMailer\\PHPMailer\\PHPMailer')) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    function hms_email_configured()
    {
        $required = array(
            'SMTP_HOST',
            'SMTP_PORT',
            'SMTP_FROM_EMAIL',
            'SMTP_FROM_NAME',
        );

        foreach ($required as $constantName) {
            if (!defined($constantName) || trim((string) constant($constantName)) === '') {
                return false;
            }
        }

        return true;
    }

    function sendEmail($to, $toName, $subject, $bodyHtml, $bodyText = '', $userId = null, $type = 'general')
    {
        $to = trim((string) $to);
        $toName = trim((string) $toName);
        $subject = trim((string) $subject);
        $type = trim((string) $type) !== '' ? trim((string) $type) : 'general';
        $userId = $userId !== null ? (int) $userId : hms_email_get_user_id_by_email($to);

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            hms_log_email_result($to, $subject, $type, 'failed', 'Invalid recipient email address.', $userId);
            return false;
        }

        if (!hms_email_configured()) {
            hms_log_email_result($to, $subject, $type, 'failed', 'SMTP configuration is incomplete.', $userId);
            return false;
        }

        if (!hms_email_load_phpmailer()) {
            hms_log_email_result($to, $subject, $type, 'failed', 'PHPMailer library was not found.', $userId);
            return false;
        }

        $textBody = trim((string) $bodyText) !== '' ? (string) $bodyText : trim(strip_tags((string) $bodyHtml));

        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->Port = (int) SMTP_PORT;
            $mail->SMTPAuth = trim((string) SMTP_USERNAME) !== '' || trim((string) SMTP_PASSWORD) !== '';
            $mail->Username = defined('SMTP_USERNAME') ? (string) SMTP_USERNAME : '';
            $mail->Password = defined('SMTP_PASSWORD') ? (string) SMTP_PASSWORD : '';
            $mail->CharSet = 'UTF-8';
            $mail->Timeout = 15;

            $encryption = defined('SMTP_ENCRYPTION') ? strtolower(trim((string) SMTP_ENCRYPTION)) : '';
            if ($encryption === 'tls') {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($encryption === 'ssl') {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            }

            $mail->setFrom((string) SMTP_FROM_EMAIL, (string) SMTP_FROM_NAME);
            $mail->addAddress($to, $toName !== '' ? $toName : $to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = (string) $bodyHtml;
            $mail->AltBody = $textBody;
            $mail->send();

            hms_log_email_result($to, $subject, $type, 'sent', null, $userId);
            return true;
        } catch (\Exception $exception) {
            hms_log_email_result($to, $subject, $type, 'failed', $exception->getMessage(), $userId);
            return false;
        }
    }

    function hms_render_email_template($templateName, array $data)
    {
        $templatePath = hms_email_project_root() . '/templates/emails/' . basename($templateName) . '.php';
        if (!file_exists($templatePath)) {
            return false;
        }

        $emailData = $data;
        $renderedTemplate = require $templatePath;

        return is_array($renderedTemplate) ? $renderedTemplate : false;
    }

    function hms_send_templated_email($templateName, $to, $toName, array $data, $userId, $type)
    {
        $template = hms_render_email_template($templateName, $data);
        if (!$template) {
            hms_log_email_result($to, '', $type, 'failed', 'Email template could not be rendered.', $userId);
            return false;
        }

        return sendEmail(
            $to,
            $toName,
            isset($template['subject']) ? $template['subject'] : hms_email_app_name(),
            isset($template['html']) ? $template['html'] : '',
            isset($template['text']) ? $template['text'] : '',
            $userId,
            $type
        );
    }

    function sendBookingSubmittedEmail($studentEmail, $studentName, array $bookingData, $userId = null)
    {
        $data = array(
            'student_name' => hms_email_normalize_name($studentName, 'Student'),
            'booking' => $bookingData,
        );

        return hms_send_templated_email('booking_submitted', $studentEmail, $studentName, $data, $userId, 'booking_submitted');
    }

    function sendBookingApprovedEmail($studentEmail, $studentName, array $bookingData, $userId = null)
    {
        $data = array(
            'student_name' => hms_email_normalize_name($studentName, 'Student'),
            'booking' => $bookingData,
        );

        return hms_send_templated_email('booking_approved', $studentEmail, $studentName, $data, $userId, 'booking_approved');
    }

    function sendBookingRejectedEmail($studentEmail, $studentName, array $bookingData, $reason = '', $userId = null)
    {
        $data = array(
            'student_name' => hms_email_normalize_name($studentName, 'Student'),
            'booking' => $bookingData,
            'reason' => trim((string) $reason),
        );

        return hms_send_templated_email('booking_rejected', $studentEmail, $studentName, $data, $userId, 'booking_rejected');
    }

    function sendRoomAssignedEmail($studentEmail, $studentName, array $roomData, $userId = null)
    {
        $data = array(
            'student_name' => hms_email_normalize_name($studentName, 'Student'),
            'room' => $roomData,
        );

        return hms_send_templated_email('room_assigned', $studentEmail, $studentName, $data, $userId, 'room_assigned');
    }

    function sendPaymentConfirmedEmail($studentEmail, $studentName, array $paymentData, $userId = null)
    {
        $data = array(
            'student_name' => hms_email_normalize_name($studentName, 'Student'),
            'payment' => $paymentData,
        );

        return hms_send_templated_email('payment_confirmed', $studentEmail, $studentName, $data, $userId, 'payment_confirmed');
    }

    function sendPaymentReminderEmail($studentEmail, $studentName, array $paymentData, $userId = null)
    {
        $data = array(
            'student_name' => hms_email_normalize_name($studentName, 'Student'),
            'payment' => $paymentData,
        );

        return hms_send_templated_email('payment_reminder', $studentEmail, $studentName, $data, $userId, 'payment_reminder');
    }

    function sendAdminNewBookingAlert($adminEmail, array $bookingData)
    {
        $data = array(
            'booking' => $bookingData,
        );

        return hms_send_templated_email('admin_new_booking', $adminEmail, 'Administrator', $data, null, 'admin_new_booking');
    }

    function sendAdminPaymentReceivedAlert($adminEmail, array $paymentData)
    {
        $data = array(
            'payment' => $paymentData,
        );

        return hms_send_templated_email('admin_payment_received', $adminEmail, 'Administrator', $data, null, 'admin_payment_received');
    }

    function hms_get_admin_notification_recipients()
    {
        global $mysqli;

        $recipients = array();
        if (!isset($mysqli) || !($mysqli instanceof mysqli) || $mysqli->connect_errno) {
            return $recipients;
        }

        if (hms_table_exists($mysqli, 'users')) {
            $result = $mysqli->query("SELECT email, full_name FROM users WHERE role = 'admin'");
            if ($result instanceof mysqli_result) {
                while ($row = $result->fetch_assoc()) {
                    $email = trim((string) $row['email']);
                    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $recipients[$email] = trim((string) $row['full_name']) !== '' ? trim((string) $row['full_name']) : 'Administrator';
                    }
                }
                $result->free();
            }
        }

        if (!$recipients && hms_table_exists($mysqli, 'admin')) {
            $result = $mysqli->query("SELECT email, username FROM admin");
            if ($result instanceof mysqli_result) {
                while ($row = $result->fetch_assoc()) {
                    $email = trim((string) $row['email']);
                    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $recipients[$email] = trim((string) $row['username']) !== '' ? trim((string) $row['username']) : 'Administrator';
                    }
                }
                $result->free();
            }
        }

        return $recipients;
    }

    function hms_send_admin_new_booking_alerts(array $bookingData)
    {
        $sent = false;
        foreach (hms_get_admin_notification_recipients() as $adminEmail => $adminName) {
            $sent = sendAdminNewBookingAlert($adminEmail, $bookingData) || $sent;
        }

        return $sent;
    }

    function hms_send_booking_notifications_after_submission($studentEmail, $studentName, array $bookingData, $userId = null)
    {
        // Email delivery is always secondary. Failures are logged inside sendEmail().
        sendBookingSubmittedEmail($studentEmail, $studentName, $bookingData, $userId);
        sendRoomAssignedEmail($studentEmail, $studentName, $bookingData, $userId);
        hms_send_admin_new_booking_alerts($bookingData);
    }
}

if (isset($mysqli) && $mysqli instanceof mysqli) {
    hms_bootstrap_email_schema($mysqli);
}
