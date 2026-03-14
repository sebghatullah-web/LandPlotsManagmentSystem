<?php
include 'config/db.php';

$id = $_POST['id'];

$sql = "SELECT * FROM tblProjects WHERE id = $id";
$result = $conn->query($sql);

$data = $result->fetch_assoc();

echo json_encode($data);
?>
