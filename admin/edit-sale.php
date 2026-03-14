<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

include 'config/db.php';

$msg = "";
$msg_success = "";

// گرفتن شناسه مشتری از URL
if(!isset($_GET['id']) || empty($_GET['id'])){
    header("Location: customers.php");
    exit();
}

$id = intval($_GET['id']);

// دریافت اطلاعات مشتری برای نمایش در فرم
$stmt = $conn->prepare("SELECT * FROM tblcustomers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    echo "<div class='alert alert-danger text-center'>❌ مشتری پیدا نشد!</div>";
    exit();
}

$customer = $result->fetch_assoc();
$stmt->close();

$sale_id = intval($_GET['id']);
$msg = "";
$msg_success = "";

// دریافت اطلاعات فروش
$stmt = $conn->prepare("SELECT * FROM tblSales WHERE id = ?");
$stmt->bind_param("i", $sale_id);
$stmt->execute();
$sale = $stmt->get_result()->fetch_assoc();
$stmt->close();

// پردازش فرم ویرایش
if(isset($_POST['update_sale'])){
    $plot_id = $_POST['plot_id'];
    $customer_id = $_POST['customer_id'];
    $sale_type = $_POST['sale_type'];
    $sale_date = $_POST['sale_date'];
    $total_amount = $_POST['total_amount'];
    $sale_status = $_POST['sale_status'];
    $note = $_POST['note'];

    // آپلود فایل قرارداد جدید
    $contract_file = $sale['contract_file'];
    if(!empty($_FILES['contract_file']['name'])){
        $contract_file = time() . "_" . basename($_FILES['contract_file']['name']);
        move_uploaded_file($_FILES['contract_file']['tmp_name'], "assets/img/contracts/".$contract_file);
    }

    // به‌روزرسانی فروش
    $updateStmt = $conn->prepare("UPDATE tblSales SET plot_id=?, customer_id=?, sale_type=?, sale_date=?, total_amount=?, sale_status=?, contract_file=?, note=? WHERE id=?");
    $updateStmt->bind_param("isssdsisi", $plot_id, $customer_id, $sale_type, $sale_date, $total_amount, $sale_status, $contract_file, $note, $sale_id);

    if($updateStmt->execute()){
        // پس از موفقیت، کاربر را به صفحه plots.php منتقل می‌کند
            header("Location: sales.php?updated=1");
            exit();
    } else {
        $msg = "⚠️ خطا در به‌روزرسانی: " . $updateStmt->error;
    }

    $updateStmt->close();
}

// لیست مشتریان
$customers = $conn->query("SELECT id, fullName, fatherName, tazkira FROM tblCustomers ORDER BY fullName ASC");

// لیست پلا‌ت‌ها (فقط Available + پلا‌ت فعلی فروش)
$plots = $conn->query("SELECT id, plot_code, status FROM tblPlots WHERE status='Available' OR id={$sale['plot_id']} ORDER BY plot_code ASC");

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
    <title>KCC | Edit Sales</title>
    <!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <!-- Icons -->
    <link href="icons/font-awesome.min.css" rel="stylesheet">
    <link href="icons/simple-line-icons.css" rel="stylesheet">
    <!-- Main styles for this application -->
    <link href="dest/style.css" rel="stylesheet">
        <style>
        body {
            background-color: #f7f7f7;
        }
        .container {
            max-width: 800px;
        }
        .card {
            border-radius: 10px;
        }
        img.preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 6px;
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
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">✏️ ویرایش مشتری</h5>
                            </div>
                            <div class="card-body">


                                    <form method="POST" style="margin: 10px" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label>نمره *</label>
                                            <select name="plot_id" class="form-control" required>
                                                <?php while($p = $plots->fetch_assoc()): ?>
                                                    <option value="<?php echo $p['id']; ?>" <?php if($p['id']==$sale['plot_id']) echo "selected"; ?>>
                                                        <?php echo $p['plot_code']." (".$p['status'].")"; ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>مشتری *</label>
                                            <select name="customer_id" class="form-control" required>
                                                <?php while($c = $customers->fetch_assoc()): ?>
                                                    <option value="<?php echo $c['id']; ?>" <?php if($c['id']==$sale['customer_id']) echo "selected"; ?>>
                                                        <?php echo $c['fullName']." | ".$c['fatherName']." | ".$c['tazkira']; ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>

                                        <div class="form-row">
                                            <div class="col-md-4 form-group">
                                                <label>نوع فروش</label>
                                                <select name="sale_type" class="form-control">
                                                    <option value="Full" <?php if($sale['sale_type']=='Full') echo 'selected'; ?>>Full</option>
                                                    <option value="Reserve" <?php if($sale['sale_type']=='Reserve') echo 'selected'; ?>>Reserve</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label>تاریخ فروش</label>
                                                <input type="datetime-local" name="sale_date" class="form-control" value="<?php echo date('Y-m-d\TH:i', strtotime($sale['sale_date'])); ?>" required>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label>مبلغ کل</label>
                                                <input type="number" step="0.01" name="total_amount" class="form-control" value="<?php echo $sale['total_amount']; ?>" required>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>وضعیت فروش</label>
                                            <select name="sale_status" class="form-control">
                                                <option value="Confirmed" <?php if($sale['sale_status']=='Confirmed') echo 'selected'; ?>>Confirmed</option>
                                                <option value="Partial" <?php if($sale['sale_status']=='Partial') echo 'selected'; ?>>Partial</option>
                                                <option value="Cancelled" <?php if($sale['sale_status']=='Cancelled') echo 'selected'; ?>>Cancelled</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>فایل قرارداد (در صورت نیاز به جایگزینی)</label>
                                            <input type="file" name="contract_file" class="form-control-file">
                                            <?php if(!empty($sale['contract_file'])): ?>
                                                <a href="assets/img/contracts/<?php echo $sale['contract_file']; ?>" target="_blank">مشاهده فایل قرارداد فعلی</a>
                                            <?php endif; ?>
                                        </div>

                                        <div class="form-group">
                                            <label>توضیحات</label>
                                            <textarea name="note" class="form-control" rows="3"><?php echo $sale['note']; ?></textarea>
                                        </div>

                                        <button type="submit" name="update_sale" class="btn btn-primary">✅ ذخیره تغییرات</button>
                                        <a href="sales.php" class="btn btn-secondary">❌ بازگشت</a>
                                    </form>

                            </div>
                        </div>
                    </div>


                </div>
                <!--/row-->
            </div>

        </div>
        <!--/.container-fluid-->
    </main>


    <?php require 'includes/footer.php'; ?>

    
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

</body>

</html>
