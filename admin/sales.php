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

if(isset($_POST['add_sale'])){

    // گرفتن و نوع‌دهی ورودی‌ها
    $sale_code      = trim($_POST['sale_code']);
    $plot_id        = isset($_POST['plot_id']) ? (int) $_POST['plot_id'] : 0;
    $customer_id    = isset($_POST['customer_id']) ? (int) $_POST['customer_id'] : 0;
    $sale_type      = $_POST['sale_type'] ?? 'Reserve';
    $sale_date      = $_POST['sale_date'] ?? date('Y-m-d H:i:s');
    $total_amount   = is_numeric($_POST['total_amount']) ? (float) $_POST['total_amount'] : 0.0;
    $payment_amount = is_numeric($_POST['payment_amount']) ? (float) $_POST['payment_amount'] : 0.0;
    $note           = $_POST['note'] ?? '';

    // محاسبه باقی‌داری
    $remaining_amount = $total_amount - $payment_amount;

    // بررسی تکراری بودن sale_code
    $stmt = $conn->prepare("SELECT id FROM tblSales WHERE sale_code = ?");
    $stmt->bind_param("s", $sale_code);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows > 0){
        $msg = "❌ کد فروش قبلاً ثبت شده است!";
    } else {

        // آپلود فایل قرارداد
        $contract_file = "";
        if(!empty($_FILES['contract_file']['name'])){
            $target_dir = "assets/img/contracts/";
            if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $contract_file = time() . "_" . basename($_FILES['contract_file']['name']);
            move_uploaded_file($_FILES['contract_file']['tmp_name'], $target_dir . $contract_file);
        }

        // درج در دیتابیس (Prepared statement)
        $stmtInsert = $conn->prepare("
            INSERT INTO tblSales 
            (sale_code, plot_id, customer_id, sale_type, sale_date, total_amount, payment_amount, remaining_amount, note, contract_file, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        // bind_param: s = string, i = int, d = double
        $stmtInsert->bind_param(
            "siissddsss",
            $sale_code,
            $plot_id,
            $customer_id,
            $sale_type,
            $sale_date,
            $total_amount,
            $payment_amount,
            $remaining_amount,
            $note,
            $contract_file
        );

        if($stmtInsert->execute()){
            // تغییر وضعیت پلات به Sold (ایمن با cast)
            $conn->query("UPDATE tblPlots SET status='Sold' WHERE id=" . (int)$plot_id);

            // به‌جای redirect، پیام موفقیت می‌دهیم تا همان صفحه بماند
            $msg_success = "✅ فروش با موفقیت ثبت شد!";
            // اگر خواستی بلافاصله جدول/لیست را نمایش دهی، میتوانی اینجا یک fetch تازه هم انجام دهی
        } else {
            $msg = "⚠️ خطا در ذخیره اطلاعات فروش: " . $stmtInsert->error;
        }

        $stmtInsert->close();
    }
    $stmt->close();
}


// واکشی لیست فروشات
$sql = "SELECT s.*, 
        c.fullName AS customer_name,
        p.plot_code AS plot_code
        FROM tblSales s
        JOIN tblCustomers c ON s.customer_id = c.id
        JOIN tblPlots p ON s.plot_id = p.id
        ORDER BY s.id DESC";
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
    <title>KCC | Sales</title>
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
                        <h3 class="mb-3 text-primary">لیست فروشات</h3>

                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addSaleModal">
                            <i class="fa fa-plus"></i> افزودن فروش جدید
                        </button>

                        <?php if(!empty($msg_success)): ?>
                            <div class="alert alert-danger text-center"><?php echo $msg_success; ?></div>
                        <?php endif; ?>

                         <?php if(isset($_GET['updated'])): ?>
                            <div class="alert alert-success text-right">
                                ✅ تغییرات با موفقیت ذخیره شد!
                            </div>
                        <?php endif; ?>

                        <?php if(!empty($msg)): ?>
                            <div class="alert alert-danger text-center"><?php echo $msg; ?></div>
                        <?php endif; ?>



                        <div id="alertArea"></div>

                        <table id="salesTable" class="table table-bordered table-striped text-center">
                            <thead class="table-dark">
                                <tr>
                                <th>#</th>
                                <th>کد فروش</th>
                                <th>پلات</th>
                                <th>مشتری</th>
                                <th>نوع فروش</th>
                                <th>مبلغ کل</th>
                                <th>تاریخ فروش</th>
                                <th>وضعیت</th>
                                <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if($result->num_rows > 0){
                                    while($row = $result->fetch_assoc()){
                                        echo "<tr>";
                                        echo "<td>{$row['id']}</td>";
                                        echo "<td>{$row['sale_code']}</td>";
                                        echo "<td>{$row['plot_code']}</td>";
                                        echo "<td>{$row['customer_name']}</td>";
                                        echo "<td>{$row['sale_type']}</td>";
                                        echo "<td>".number_format($row['total_amount'])."</td>";
                                        echo "<td>".date('Y-m-d', strtotime($row['sale_date']))."</td>";
                                        echo "<td>{$row['sale_status']}</td>";
                                        echo "<td>
                                                <button class='btn btn-primary btn-sm' onclick=\"window.location.href='edit-sale.php?id={$row['id']}'\"><i class='fa fa-edit'></i></button>
                                                <button class='btn btn-danger btn-sm deleteSales' data-id='".$row['id']."'><i class='fa fa-trash'></i></button>
                                            </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='9' class='text-danger'>هیچ فروش ثبت نشده است!</td></tr>";
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



<!-- ✅ Add Sale Modal -->
<div class="modal fade" id="addSaleModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">➕ افزودن فروش جدید</h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>

      <form method="POST" enctype="multipart/form-data">
        <div class="modal-body">

          <div class="form-row">
            <div class="col-md-6 form-group">
              <label>کد فروش *</label>
              <input type="text" name="sale_code" class="form-control" required>
            </div>

            <div class="col-md-6 form-group">
              <label>نوع فروش *</label>
              <select name="sale_type" class="form-control" required>
                <option value="Full">نقدی (Full)</option>
                <option value="Reserve">رزرف (Reserve)</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="col-md-6 form-group">
              <label>نمره *</label>
              <select name="plot_id" class="form-control select2" required>
                <option value="">-- انتخاب نمره --</option>
                <?php
                  $plots = $conn->query("SELECT id, plot_code, plot_type, price FROM tblPlots WHERE status='Available'");
                  while($p = $plots->fetch_assoc()){
                    echo "<option value='{$p['id']}'>{$p['plot_code']} | {$p['plot_type']} | {$p['price']}</option>";
                  }
                ?>
              </select>
            </div>

            <div class="col-md-6 form-group">
              <label>مشتری *</label>
              <select name="customer_id" class="form-control select2" required>
                <option value="">-- انتخاب مشتری --</option>
                <?php
                  $customers = $conn->query("SELECT id, fullName, fatherName, tazkira FROM tblCustomers ORDER BY fullName ASC");
                  while($c = $customers->fetch_assoc()){
                    echo "<option value='{$c['id']}'>{$c['fullName']} | {$c['fatherName']} | {$c['tazkira']}</option>";
                  }
                ?>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="col-md-4 form-group">
              <label>تاریخ فروش *</label>
              <input type="datetime-local" name="sale_date" class="form-control" required>
            </div>

            <div class="col-md-4 form-group">
              <label>قیمت کل *</label>
              <input type="number" step="0.01" name="total_amount" class="form-control" required>
            </div>

            <div class="col-md-4 form-group">
              <label>مقدار پرداخت *</label>
              <input type="number" step="0.01" name="payment_amount" class="form-control" required>
            </div>
          </div>

          <div class="form-group">
            <label>توضیحات</label>
            <textarea name="note" class="form-control" rows="2"></textarea>
          </div>

          <div class="form-group">
            <label>فایل قرارداد</label>
            <input type="file" name="contract_file" class="form-control-file" accept=".pdf,.jpg,.png,.doc,.docx">
          </div>

        </div>

        <div class="modal-footer">
            <button type="submit" name="add_sale" class="btn btn-success">✅ ثبت فروش</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">❌ بستن</button>
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
        $('#salesTable').DataTable({
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

    <!-- Delete Customer Modal -->
<script>
$(document).on("click", ".deleteSales", function(){
    var projectID = $(this).data("id");

    if(confirm("آیا مطمئن هستید که می‌خواهید این پروژه را حذف کنید؟")){
        $.ajax({
            url: "delete_sale.php",
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
