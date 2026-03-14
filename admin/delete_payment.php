<?php
session_start();
include 'config/db.php';

if(isset($_POST['id'])){
    $payment_id = (int)$_POST['id'];

    // گرفتن اطلاعات پرداخت
    $stmt = $conn->prepare("SELECT sale_id, amount FROM tblPayments WHERE id=?");
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    $payment = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if($payment){
        $sale_id = $payment['sale_id'];
        $amount_to_reduce = $payment['amount'];

        // حذف پرداخت
        $stmtDelete = $conn->prepare("DELETE FROM tblPayments WHERE id=?");
        $stmtDelete->bind_param("i", $payment_id);
        if($stmtDelete->execute()){
            $stmtDelete->close();

            // به‌روزرسانی tblSales
            // ابتدا گرفتن total_amount و payment_amount
            $stmtSale = $conn->prepare("SELECT total_amount, payment_amount FROM tblSales WHERE id=?");
            $stmtSale->bind_param("i", $sale_id);
            $stmtSale->execute();
            $sale = $stmtSale->get_result()->fetch_assoc();
            $stmtSale->close();

            if($sale){
                $new_payment_amount = $sale['payment_amount'] - $amount_to_reduce;
                $new_remaining = $sale['total_amount'] - $new_payment_amount;

                // تعیین نوع و وضعیت فروش
                $new_sale_type = ($new_remaining <= 0) ? 'Full' : 'Reserve';
                $new_sale_status = ($new_remaining <= 0) ? 'Confirmed' : 'Partial';

                $stmtUpdate = $conn->prepare("UPDATE tblSales SET payment_amount=?, remaining_amount=?, sale_type=?, sale_status=? WHERE id=?");
                $stmtUpdate->bind_param("ddssi", $new_payment_amount, $new_remaining, $new_sale_type, $new_sale_status, $sale_id);
                $stmtUpdate->execute();
                $stmtUpdate->close();

                echo "success";
            } else {
                echo "error_sale_not_found";
            }

        } else {
            echo "error_delete_payment";
        }

    } else {
        echo "error_payment_not_found";
    }
}
?>
