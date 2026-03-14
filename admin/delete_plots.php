<?php
include 'config/db.php';

if(isset($_POST['id'])){
    $id = intval($_POST['id']);
    $sql = "DELETE FROM tblPlots WHERE id = $id";

    if($conn->query($sql)){
        echo "success";
    } else {
        echo "error";
    }
}
?>
