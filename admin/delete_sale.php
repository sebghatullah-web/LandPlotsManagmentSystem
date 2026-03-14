<?php
session_start();
include 'config/db.php';

if(isset($_POST['id'])){
    $sale_id = intval($_POST['id']);

    // ابتدا اطلاعات فروش برای گرفتن plot_id
    $stmt = $conn->prepare("SELECT plot_id FROM tblSales WHERE id = ?");
    $stmt->bind_param("i", $sale_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
        $sale = $result->fetch_assoc();
        $plot_id = $sale['plot_id'];

        // حذف فروش
        $delStmt = $conn->prepare("DELETE FROM tblSales WHERE id = ?");
        $delStmt->bind_param("i", $sale_id);

        if($delStmt->execute()){
            // به‌روزرسانی وضعیت پلا‌ت به "Available"
            $updatePlot = $conn->prepare("UPDATE tblPlots SET status='Available', owner_customer_id=NULL WHERE id=?");
            $updatePlot->bind_param("i", $plot_id);
            $updatePlot->execute();
            $updatePlot->close();

            echo "success";
        } else {
            echo "error";
        }

        $delStmt->close();
    } else {
        echo "not_found";
    }

    $stmt->close();
} else {
    echo "invalid";
}
?>
