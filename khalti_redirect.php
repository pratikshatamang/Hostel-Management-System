<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/checklogin.php';
check_login();

$paymentUrl = '';
if (!empty($_SESSION['pending_booking']) && is_array($_SESSION['pending_booking']) && !empty($_SESSION['pending_booking']['khalti_payment_url'])) {
    $paymentUrl = trim((string) $_SESSION['pending_booking']['khalti_payment_url']);
}

if ($paymentUrl === '') {
    header('Location: payment.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Redirecting to Khalti</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/user-ui.css">
</head>
<body>
    <div class="container" style="max-width:720px; margin:80px auto;">
        <div class="panel panel-default" style="padding:32px; border-radius:16px;">
            <h2 style="margin-top:0;">Redirecting to Khalti Sandbox</h2>
            <p>Please wait while we open the secure Khalti checkout page.</p>
            <p>If you are not redirected automatically, use the button below.</p>
            <p>
                <a id="manual-khalti-link" class="btn btn-primary" href="<?php echo htmlspecialchars($paymentUrl, ENT_QUOTES, 'UTF-8'); ?>" rel="noopener">Open Khalti Checkout</a>
                <a class="btn btn-default" href="payment.php">Back to Payment</a>
            </p>
        </div>
    </div>
    <script>
    (function () {
        var paymentUrl = <?php echo json_encode($paymentUrl); ?>;
        if (paymentUrl) {
            window.location.replace(paymentUrl);
        }
    })();
    </script>
</body>
</html>
