<?php
session_start();
error_reporting(E_ALL);       // تمام انواع خطاها
ini_set('display_errors', 1); // نمایش خطاها
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

include 'config/db.php';

$username = $_SESSION['username'];
$role = $_SESSION['role'];

// پیام‌ها
$msg = "";
$msg_success = "";

if(isset($_POST['add_payment'])){

    $sale_id       = intval($_POST['sale_id']);
    $amount        = floatval($_POST['amount']);
    $payment_date  = $_POST['payment_date'];
    $method        = $_POST['payment_method'] ?? '';
    $note          = $_POST['note'] ?? '';

    // گرفتن اطلاعات فروش
    $stmt = $conn->prepare("SELECT sale_type, remaining_amount FROM tblSales WHERE id = ?");
    $stmt->bind_param("i", $sale_id);
    $stmt->execute();
    $sale = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if(!$sale){
        $msg = "❌ فروش مورد نظر یافت نشد!";
    } elseif($sale['sale_type'] == 'Full' || $sale['remaining_amount'] <= 0) {
        $msg = "❌ این فروش به‌صورت کامل پرداخت شده است!";
    } elseif($amount > $sale['remaining_amount']) {
        $msg = "❌ مبلغ پرداخت بیشتر از باقی‌داری است!";
    } else {
        // آپلود رسید در صورت وجود
        $receipt_file = "";
        if(!empty($_FILES['receipt_file']['name'])){
            $target_dir = "assets/img/receipts/";
            if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $receipt_file = time() . "_" . basename($_FILES['receipt_file']['name']);
            move_uploaded_file($_FILES['receipt_file']['tmp_name'], $target_dir . $receipt_file);
        }

        // ثبت پرداخت
        $stmt = $conn->prepare("INSERT INTO tblPayments (sale_id, payment_date, amount, payment_method, receipt_file, note, created_at)
                                VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("isdsss", $sale_id, $payment_date, $amount, $method, $receipt_file, $note);

        if($stmt->execute()){
            // محاسبه باقی‌داری جدید
            $new_remaining = $sale['remaining_amount'] - $amount;

            // بروزرسانی وضعیت فروش
            $status = ($new_remaining <= 0) ? 'Confirmed' : 'Partial';
            $sale_type = ($new_remaining <= 0) ? 'Full' : 'Reserve';

            // گرفتن مقدار پرداخت‌های قبلی تا جمع کل پرداخت‌ها درست شود
            $stmtTotal = $conn->prepare("SELECT payment_amount FROM tblSales WHERE id=?");
            $stmtTotal->bind_param("i", $sale_id);
            $stmtTotal->execute();
            $totalPaid = $stmtTotal->get_result()->fetch_assoc()['payment_amount'] ?? 0;
            $stmtTotal->close();

            $payment_amount = $totalPaid + $new_remaining;

            // بروزرسانی remaining_amount و payment_amount و وضعیت
            $update = $conn->prepare("UPDATE tblSales SET remaining_amount=?, payment_amount=?, sale_status=?, sale_type=? WHERE id=?");
            $update->bind_param("ddssi", $new_remaining, $payment_amount, $status, $sale_type, $sale_id);
            $update->execute();
            $update->close();

            $msg_success = "✅ پرداخت با موفقیت ثبت شد!";

        } else {
            $msg = "⚠️ خطا در ثبت پرداخت: " . $stmt->error;
        }

        $stmt->close();
    }
}

// گرفتن لیست پرداخت‌ها همراه با مشتری و فروش
$sql = "SELECT p.id AS payment_id, p.payment_date, p.amount, p.payment_method, p.receipt_file, p.note,
               s.sale_code, s.remaining_amount, c.fullName AS customer_name, pl.plot_code
        FROM tblPayments p
        JOIN tblSales s ON p.sale_id = s.id
        JOIN tblCustomers c ON s.customer_id = c.id
        JOIN tblPlots pl ON s.plot_id = pl.id
        ORDER BY p.id DESC";

$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="IR-fa" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="CoreUI Bootstrap 4 Admin Template">
    <meta name="author" content="Lukasz Holeczek">
    <meta name="keyword" content="CoreUI Bootstrap 4 Admin Template">
    <!-- <link rel="shortcut icon" href="assets/ico/favicon.png"> -->
    <title>KCC | Payments</title>
    <!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <!-- Icons -->
    <link href="icons/font-awesome.min.css" rel="stylesheet">
    <link href="icons/simple-line-icons.css" rel="stylesheet">
    <!-- Main styles for this application -->
    <link href="dest/style.css" rel="stylesheet">
    <style>
        table th, table td {
            vertical-align: middle !important;
        }

    </style>
</head>


<body class="navbar-fixed sidebar-nav fixed-nav">

    <?php require 'includes/header.php'; ?>
    <?php require 'includes/sidebar.php'; ?>
    <!-- Main content -->
    <main class="main">

        <!-- Breadcrumb -->
        <ol class="breadcrumb">
            <li class="breadcrumb-item">خانه</li>
            <li class="breadcrumb-item"><a href="#">مدیریت</a>
            </li>
            <li class="breadcrumb-item active">داشبرد</li>

            <!-- Breadcrumb Menu-->
            <li class="breadcrumb-menu">
                <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                    <a class="btn btn-secondary" href="#"><i class="icon-speech"></i></a>
                    <a class="btn btn-secondary" href="./"><i class="icon-graph"></i> &nbsp;داشبرد</a>
                    <a class="btn btn-secondary" href="#"><i class="icon-settings"></i> &nbsp;تنظیمات</a>
                </div>
            </li>
        </ol>

        <div class="container-fluid">

            <div class="animated fadeIn">  
                <div class="row">

                    <div class="container mt-4">
                        <h3 class="mb-3 text-primary">لیست پرداخت ها</h3>

                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addPaymentModal">
                            <i class="fa fa-plus"></i> افزودن پرداخت جدید
                        </button>

                        <?php if(isset($_GET['msg']) && $_GET['msg'] == "success"){ ?>
                            <div class="alert alert-success text-center">
                                فروش جدید با موفقیت ثبت شد ✅
                            </div>
                        <?php } ?>

                         <?php if(isset($_GET['updated'])): ?>
                            <div class="alert alert-success text-right">
                                ✅ تغییرات با موفقیت ذخیره شد!
                            </div>
                        <?php endif; ?>

                        <?php if(!empty($msg)): ?>
                            <div class="alert alert-danger text-center"><?php echo $msg; ?></div>
                        <?php endif; ?>

                        <?php if(!empty($msg_show)): ?>
                            <div class="alert alert-danger text-center"><?php echo $msg_show; ?></div>
                        <?php endif; ?>



                        <div id="alertArea"></div>

                        <table id="paymentsTable" class="table table-bordered table-striped text-center">
                            <thead class="thead-dark">
                                <tr>
                                    <th>نمبر مسلسل</th>
                                    <th>کد فروش</th>
                                    <th>مشتری</th>
                                    <th>پلا‌ت</th>
                                    <th>تاریخ پرداخت</th>
                                    <th>مبلغ پرداخت</th>
                                    <th>روش پرداخت</th>
                                    <th>رسید</th>
                                    <th>توضیحات</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            if($result->num_rows > 0){
                                while($row = $result->fetch_assoc()){
                                    echo "<tr>";
                                    echo "<td>".$row['payment_id']."</td>";
                                    echo "<td>".$row['sale_code']."</td>";
                                    echo "<td>".$row['customer_name']."</td>";
                                    echo "<td>".$row['plot_code']."</td>";
                                    echo "<td>".$row['payment_date']."</td>";
                                    echo "<td>".number_format($row['amount'],2)."</td>";
                                    echo "<td>".$row['payment_method']."</td>";
                                    echo "<td>";
                                    if(!empty($row['receipt_file'])){
                                        echo "<a href='assets/img/receipts/".$row['receipt_file']."' target='_blank'>مشاهده</a>";
                                    } else { echo "-"; }
                                    echo "</td>";
                                    echo "<td>".$row['note']."</td>";
                                    echo "<td>
                                            <button class='btn btn-primary btn-sm' onclick=\"window.location.href='edit_payment.php?id={$row['payment_id']}'\"><i class='fa fa-edit'></i></button>
                                            <button class='btn btn-danger btn-sm deletePayment' data-id='".$row['payment_id']."'><i class='fa fa-trash'></i></button>
                                        </td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                            </tbody>
                        </table>

                    </div>


                </div>
                <!--/row-->
            </div>

        </div>
        <!--/.container-fluid-->
    </main>


    <?php require 'includes/footer.php'; ?>

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" role="dialog" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">➕ افزودن پرداخت جدید</h5>
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>

      <form action="" method="post" enctype="multipart/form-data">
        <div class="modal-body">

          <div class="form-group">
            <label for="sale_id">انتخاب فروش:</label>
            <select name="sale_id" id="sale_id" class="form-control select2" required>
              <option value="">انتخاب کنید...</option>
              <?php
              // فقط فروش‌هایی که هنوز پرداخت کامل نشده‌اند نمایش داده شود
              $sales = $conn->query("SELECT s.id, s.sale_code, c.fullName AS customer_name, s.remaining_amount 
                                     FROM tblSales s 
                                     JOIN tblCustomers c ON s.customer_id = c.id 
                                     WHERE s.sale_type != 'Full' AND s.remaining_amount > 0
                                     ORDER BY s.id DESC");
              while($s = $sales->fetch_assoc()){
                  echo "<option value='{$s['id']}'>کد: {$s['sale_code']} - مشتری: {$s['customer_name']} - باقی‌داری: ".number_format($s['remaining_amount'],2)." </option>";
              }
              ?>
            </select>
          </div>

          <div class="form-group">
            <label for="payment_date">تاریخ پرداخت:</label>
            <input type="datetime-local" name="payment_date" id="payment_date" class="form-control" required>
          </div>

          <div class="form-group">
            <label for="amount">مبلغ پرداخت:</label>
            <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
          </div>

          <div class="form-group">
            <label for="payment_method">روش پرداخت:</label>
            <select name="payment_method" id="payment_method" class="form-control" required>
                <option value="">انتخاب کنید...</option>
                <option value="دفتر مالی">دفتر مالی</option>
                <option value="بانک">بانک</option>
            </select>
          </div>


          <div class="form-group">
            <label for="receipt_file">فایل رسید (در صورت وجود):</label>
            <input type="file" name="receipt_file" id="receipt_file" class="form-control-file">
          </div>

          <div class="form-group">
            <label for="note">توضیحات:</label>
            <textarea name="note" id="note" class="form-control" rows="3"></textarea>
          </div>

        </div>
        <div class="modal-footer">
          <button type="submit" name="add_payment" class="btn btn-success">ثبت پرداخت</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
        </div>
      </form>
    </div>
  </div>
</div>

    
    <!-- Bootstrap and necessary plugins -->
    <script src="assets/js/libs/jquery.min.js"></script>
    <script src="assets/js/libs/tether.min.js"></script>
    <script src="assets/js/libs/bootstrap.min.js"></script>
    <script src="assets/js/libs/pace.min.js"></script>

    <!-- Plugins and scripts required by all views -->
    <script src="assets/js/libs/Chart.min.js"></script>

    <!-- CoreUI main scripts -->

    <script src="assets/js/app.js"></script>


    <script src="assets/js/views/main.js"></script>

    <!-- DataTable JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>

    <script>
    $(document).ready(function(){
        $('#paymentsTable').DataTable({
            "order": [],
            "language": {
                "search": "جستجو:",
                "lengthMenu": "نمایش _MENU_ ردیف",
                "info": "نمایش _START_ تا _END_ از _TOTAL_ ردیف",
                "infoEmpty": "هیچ موردی موجود نیست",
                "zeroRecords": "نتیجه‌ای یافت نشد",
                "paginate": {
                    "next": "بعدی",
                    "previous": "قبلی"
                }
            }
        });
    });
    </script>

<!-- jQuery باید قبل از Select2 باشد -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    $(".select2").select2({
        width: '100%',
        placeholder: " انتخاب کنید...",
        allowClear: true
    });
});
</script>

    <script>
    // Auto hide alert after 3 seconds
    setTimeout(function() {
        let alertBox = document.querySelector('.alert');
        if (alertBox) {
            alertBox.style.transition = "0.7s";
            alertBox.style.opacity = "0";

            setTimeout(() => {
                alertBox.remove();

                // Remove URL parameter without refreshing
                const url = new URL(window.location.href);
                url.searchParams.delete('msg');
                window.history.replaceState({}, document.title, url);
            }, 800);
        }
    }, 3000);
    </script>

    <!-- Delete Payment Modal -->
<script>
$(document).on("click", ".deletePayment", function(){
    var projectID = $(this).data("id");

    if(confirm("آیا مطمئن هستید که می‌خواهید این پروژه را حذف کنید؟")){
        $.ajax({
            url: "delete_payment.php",
            type: "POST",
            data: { id: projectID },
            success: function(response){
                if(response.trim() === "success"){
                    // حذف سطر بدون رفرش کل صفحه ✅
                    $("button[data-id='" + projectID + "']").closest("tr").fadeOut(600);
                } else {
                    alert("خطا در حذف پروژه ❌");
                }
            }
        });
    }
});
</script>

</body>

</html>
