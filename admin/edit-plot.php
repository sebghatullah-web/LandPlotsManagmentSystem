<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

include 'config/db.php';

if(!isset($_GET['id'])){
    die("خطا: نمره انتخاب نشده ❌");
}

$id = intval($_GET['id']);

// اول باید اطلاعات نمره را بگیریم ✅
$q = $conn->query("SELECT * FROM tblPlots WHERE id = $id");
$plot = $q->fetch_assoc();

if(!$plot){
    die("⚠️ نمره یافت نشد!");
}

// گرفتن لیست پروژه‌ها
$projects = $conn->query("SELECT id, project_name FROM tblProjects ORDER BY project_name ASC");
// گرفتن لیست مشتری‌ها
$customers = $conn->query("SELECT id, fullname FROM tblCustomers ORDER BY fullname ASC");



// اگر فرم به صورت POST ارسال شد → Update
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    
    $code = $_POST['plot_code'];
    $project = intval($_POST['project_id']);
    $type = $_POST['plot_type'];
    $area = floatval($_POST['area']);
    $length = floatval($_POST['length']);
    $width = floatval($_POST['width']);
    $bound = $_POST['boundaries'];
    $price = floatval($_POST['price']);
    $status = $_POST['status'];
    $owner  = ($_POST['owner_customer_id'] != "") ? intval($_POST['owner_customer_id']) : "NULL";

    $mapUpload = "";
    if(!empty($_FILES['map_file']['name'])){
        $file = "assets/maps/" . time() . "_" . basename($_FILES["map_file"]["name"]);
        move_uploaded_file($_FILES["map_file"]["tmp_name"], $file);
        $mapUpload = ", map_file='$file'";
    }

    
    // ✅ چک تکراری بودن کد نمره (مهم)
    $check = $conn->query("SELECT id FROM tblPlots WHERE plot_code='$code' AND id!=$id");
    if($check->num_rows > 0){
        die("<script>alert('⚠️ کد نمره تکراری است، لطفاً کد دیگری وارد کنید!'); history.back();</script>");
    }


    $sql = "UPDATE tblPlots SET 
            plot_code='$code',
            project_id=$project,
            plot_type='$type',
            area=$area,
            length=$length,
            width=$width,
            boundaries='$bound',
            price=$price,
            status='$status',
            owner_customer_id=$owner
            $mapUpload
            WHERE id=$id";

    if($conn->query($sql)){
        header("Location: plots.php?updated=1");
        exit();
    } else {
        echo "❌ خطا در ذخیره: " . $conn->error;
    }
}
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
    <title>KCC | Edit Plots</title>
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
                                <h5 class="mb-0">✏️ ویرایش نمره</h5>
                            </div>
                            <div class="card-body">

                                <form method="POST" style="margin: 10px" enctype="multipart/form-data">

                                    <input type="hidden" name="id" value="<?= $plot['id'] ?>">

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label>کد نمره</label>
                                            <input type="text" name="plot_code" class="form-control" value="<?= htmlspecialchars($plot['plot_code']) ?>" required>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label>پروژه</label>
                                            <select name="project_id" class="form-control" required>
                                                <?php while($p = $projects->fetch_assoc()): ?>
                                                    <option value="<?= $p['id'] ?>" <?= $plot['project_id']==$p['id']?"selected":""; ?>>
                                                        <?= htmlspecialchars($p['project_name']) ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label>نوع نمره</label>
                                            <input type="text" name="plot_type" class="form-control" value="<?= htmlspecialchars($plot['plot_type']) ?>">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label>مساحت (m²)</label>
                                            <input type="number" step="0.01" name="area" class="form-control" value="<?= $plot['area'] ?>">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label>طول</label>
                                            <input type="number" step="0.01" name="length" class="form-control" value="<?= $plot['length'] ?>">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label>عرض</label>
                                            <input type="number" step="0.01" name="width" class="form-control" value="<?= $plot['width'] ?>">
                                        </div>

                                        <div class="col-md-12 mb-3">
                                            <label>حدود اربعه</label>
                                            <textarea name="boundaries" class="form-control" rows="3"><?= htmlspecialchars($plot['boundaries']) ?></textarea>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label>قیمت</label>
                                            <input type="number" step="0.01" name="price" class="form-control" value="<?= $plot['price'] ?>">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label>وضعیت</label>
                                            <select name="status" class="form-control">
                                                <option value="Available" <?= $plot['status']=="Available"?"selected":""; ?>>خالی</option>
                                                <option value="Reserved" <?= $plot['status']=="Reserved"?"selected":""; ?>>رزرو</option>
                                                <option value="Sold" <?= $plot['status']=="Sold"?"selected":""; ?>>فروش شده</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label>مالک (اختیاری)</label>
                                            <select name="owner_customer_id" class="form-control">
                                                <option value="">— بدون مالک —</option>
                                                <?php while($c = $customers->fetch_assoc()): ?>
                                                    <option value="<?= $c['id'] ?>" <?= $plot['owner_customer_id']==$c['id']?"selected":""; ?>>
                                                        <?= htmlspecialchars($c['fullname']) ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label>فایل نقشه (در صورت نیاز به تغییر)</label>
                                            <input type="file" name="map_file" class="form-control">
                                        </div>

                                    </div>

                                    <button class="btn btn-success">✅ ثبت تغییرات</button>
                                    <a href="plots.php" class="btn btn-secondary">بازگشت</a>

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
