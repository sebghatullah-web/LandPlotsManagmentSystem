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

// ✅ پردازش فرم ویرایش مشتری
if(isset($_POST['update_customer'])){

    $fullName   = trim($_POST['full_name']);
    $fatherName = trim($_POST['father_name']);
    $tazkira    = trim($_POST['tazkira']);
    $dob        = $_POST['dob'] ?? null;
    $job        = $_POST['job'] ?? '';
    $address    = $_POST['address'] ?? '';
    $phone      = $_POST['phone'] ?? '';

    // بررسی تکراری بودن شماره تذکره برای مشتریان دیگر
    $checkStmt = $conn->prepare("SELECT id FROM tblcustomers WHERE tazkira = ? AND id != ?");
    $checkStmt->bind_param("si", $tazkira, $id);
    $checkStmt->execute();
    $checkStmt->store_result();

    if($checkStmt->num_rows > 0){
        $msg = "❌ این شماره تذکره قبلاً برای مشتری دیگری ثبت شده است!";
    } else {

        // مدیریت عکس مشتری
        $photo = $customer['photo'];
        if(!empty($_FILES['profile_photo']['name'])){
            $photo = time() . "_" . basename($_FILES['profile_photo']['name']);
            move_uploaded_file($_FILES['profile_photo']['tmp_name'], "assets/img/".$photo);
        }

        // مدیریت عکس تذکره
        $id_card_photo = $customer['id_card_photo'];
        if(!empty($_FILES['id_card_photo']['name'])){
            $id_card_photo = time() . "_" . basename($_FILES['id_card_photo']['name']);
            move_uploaded_file($_FILES['id_card_photo']['tmp_name'], "assets/img/".$id_card_photo);
        }

        // به‌روزرسانی اطلاعات در دیتابیس
        $updateStmt = $conn->prepare("UPDATE tblcustomers 
            SET fullName=?, fatherName=?, tazkira=?, dob=?, job=?, address=?, phone=?, photo=?, id_card_photo=? 
            WHERE id=?");
        $updateStmt->bind_param("sssssssssi", 
            $fullName, $fatherName, $tazkira, $dob, $job, $address, $phone, $photo, $id_card_photo, $id
        );

        if($updateStmt->execute()){
            // پس از موفقیت، کاربر را به صفحه plots.php منتقل می‌کند
            header("Location: customers.php?updated=1");
            exit();
        } else {
            $msg = "⚠️ خطا در به‌روزرسانی اطلاعات: " . $updateStmt->error;
        }

        $updateStmt->close();
    }

    $checkStmt->close();
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
    <title>KCC | Edit Customers</title>
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
                                    <div class="form-row">
                                        <div class="col-md-6 form-group">
                                            <label>اسم مشتری *</label>
                                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($customer['fullName']); ?>" class="form-control" required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label>ولد/بنت *</label>
                                            <input type="text" name="father_name" value="<?php echo htmlspecialchars($customer['fatherName']); ?>" class="form-control" required>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="col-md-6 form-group">
                                            <label>شماره تذکره / پاسپورت *</label>
                                            <input type="text" name="tazkira" value="<?php echo htmlspecialchars($customer['tazkira']); ?>" class="form-control" required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label>شماره تماس</label>
                                            <input type="text" name="phone" value="<?php echo htmlspecialchars($customer['phone']); ?>" class="form-control">
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="col-md-6 form-group">
                                            <label>تاریخ تولد</label>
                                            <input type="date" name="dob" value="<?php echo $customer['dob']; ?>" class="form-control">
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label>وظیفه</label>
                                            <input type="text" name="job" value="<?php echo htmlspecialchars($customer['job']); ?>" class="form-control">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>آدرس</label>
                                        <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($customer['address']); ?></textarea>
                                    </div>

                                    <div class="form-row">
                                        <div class="col-md-6 form-group">
                                            <label>عکس مشتری</label><br>
                                            <?php if(!empty($customer['photo'])): ?>
                                                <img src="assets/img/<?php echo $customer['photo']; ?>" class="preview mb-2">
                                            <?php endif; ?>
                                            <input type="file" name="profile_photo" class="form-control-file" accept="image/*">
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label>عکس تذکره / پاسپورت</label><br>
                                            <?php if(!empty($customer['id_card_photo'])): ?>
                                                <img src="assets/img/<?php echo $customer['id_card_photo']; ?>" class="preview mb-2">
                                            <?php endif; ?>
                                            <input type="file" name="id_card_photo" class="form-control-file" accept="image/*">
                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" name="update_customer" class="btn btn-success px-4">💾 ذخیره تغییرات</button>
                                        <a href="customers.php" class="btn btn-secondary px-4">بازگشت</a>
                                    </div>
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
