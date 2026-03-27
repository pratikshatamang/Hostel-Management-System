<?php
include('includes/config.php'); // this has $mysqli

if(!empty($_POST["roomid"])) {
    $id = $_POST['roomid'];

    $stmt = $mysqli->prepare("SELECT seater FROM rooms WHERE room_no = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($seater);
    $stmt->fetch();
    echo $seater;
}

if(!empty($_POST["rid"])) {
    $id = $_POST['rid'];

    $stmt = $mysqli->prepare("SELECT fees FROM rooms WHERE room_no = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($fees);
    $stmt->fetch();
    echo $fees;
}
?>
