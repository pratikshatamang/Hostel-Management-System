<?php
include('includes/config.php');

if (!empty($_POST["roomid"])) {
    $id = (int) $_POST['roomid'];
    $stmt = $mysqli->prepare("SELECT seater FROM rooms WHERE room_no = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($seater);
    $stmt->fetch();
    $stmt->close();

    echo htmlentities($seater);
    exit();
}

if (!empty($_POST["rid"])) {
    $id = (int) $_POST['rid'];
    $stmt = $mysqli->prepare("SELECT fees FROM rooms WHERE room_no = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($fees);
    $stmt->fetch();
    $stmt->close();

    echo htmlentities($fees);
    exit();
}
?>
