<?php
include 'config/db.php';

$id = $_POST['id'];
$name = $_POST['project_name'];
$location = $_POST['location'];
$description = $_POST['description'];
$created_at = $_POST['created_at'];

$sql = "UPDATE tblProjects SET 
        project_name='$name',
        location='$location',
        description='$description',
        created_at='$created_at'
        WHERE id = $id";

echo ($conn->query($sql)) ? "success" : "error";
?>
