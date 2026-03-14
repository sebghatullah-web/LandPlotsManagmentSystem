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

$msg = "";       // پیام خطا
$msg_success = ""; // پیام موفقیت


// پردازش فرم افزودن مشتری
if(isset($_POST['add_customer'])) {

    $fullName = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $fatherName = isset($_POST['father_name']) ? trim($_POST['father_name']) : '';
    $tazkira = isset($_POST['tazkira']) ? trim($_POST['tazkira']) : '';
    $dob = $_POST['dob'] ?? null;
    $job = $_POST['job'] ?? '';
    $address = $_POST['address'] ?? '';
    $phone = $_POST['phone'] ?? '';

    // بررسی تکراری بودن شماره تذکره
    $stmt = $conn->prepare("SELECT id FROM tblcustomers WHERE tazkira = ?");
    $stmt->bind_param("s", $tazkira);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows > 0){
        $msg = "❌ این شماره تذکره قبلاً ثبت شده است!";
    } else {
        // آپلود عکس مشتری
        $photo = "";
        if(!empty($_FILES['profile_photo']['name'])){
            $photo = time() . "_" . basename($_FILES['profile_photo']['name']);
            move_uploaded_file($_FILES['profile_photo']['tmp_name'], "assets/img/".$photo);
        }

        // آپلود عکس تذکره
        $id_card_photo = "";
        if(!empty($_FILES['id_card_photo']['name'])){
            $id_card_photo = time() . "_" . basename($_FILES['id_card_photo']['name']);
            move_uploaded_file($_FILES['id_card_photo']['tmp_name'], "assets/img/".$id_card_photo);
        }

        // Insert به پایگاه داده
        $stmtInsert = $conn->prepare("INSERT INTO tblcustomers 
            (fullName, fatherName, tazkira, dob, job, address, phone, photo, id_card_photo) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtInsert->bind_param("sssssssss", $fullName, $fatherName, $tazkira, $dob, $job, $address, $phone, $photo, $id_card_photo);

        if($stmtInsert->execute()){
             // redirect to avoid repost on refresh
            header("Location: ".$_SERVER['PHP_SELF']."?msg=success");
            exit();
        } else {
            $msg = "⚠️ خطا در ذخیره‌سازی اطلاعات: " . $stmtInsert->error;
        }

        $stmtInsert->close();
    }

    $stmt->close();
}

// ✅ نمایش لیست پروژه‌ها
$sql = "SELECT * FROM tblcustomers ORDER BY id DESC";
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
    <title>KCC | Customers</title>
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
                        <h3 class="mb-3 text-primary">لیست مشتریان</h3>

                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addCustomerModal">
                            <i class="fa fa-plus"></i> افزودن مشتری
                        </button>

                        <?php if(isset($_GET['msg']) && $_GET['msg'] == "success"){ ?>
                            <div class="alert alert-success text-center">
                                مشتری با موفقیت ثبت شد ✅
                            </div>
                        <?php } ?>

                         <?php if(isset($_GET['updated'])): ?>
                            <div class="alert alert-success text-right">
                                ✅ تغییرات با موفقیت ذخیره شد!
                            </div>
                        <?php endif; ?>



                        <div id="alertArea"></div>

                        <table id="customersTable" class="table table-bordered table-striped text-center">
                            <thead class="table-dark">
                                <tr>
                                    <th>نمبر مسلسل</th>
                                    <th>اسم مشتری</th>
                                    <th>ولد/بنت</th>
                                    <th>نمبر تذکره</th>
                                    <th>شماره تماس</th>
                                    <th>عکس مشتری</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php 
                                $i = 1;
                                if($result->num_rows > 0){
                                    while($row = $result->fetch_assoc()){
                                        echo "<tr>";
                                        echo "<td>".$row['id']."</td>";
                                        echo "<td>".$row['fullName']."</td>";
                                        echo "<td>".$row['fatherName']."</td>";
                                        echo "<td>".$row['tazkira']."</td>";
                                        echo "<td>".$row['phone']."</td>";
                                        echo "<td><img src='assets/img/".$row['photo']."' width='60' height='60' style='object-fit: cover; border-radius: 6px;'></td>";
                                        echo "<td>
                                                <button class='btn btn-primary btn-sm' onclick=\"window.location.href='edit-customer.php?id={$row['id']}'\"><i class='fa fa-edit'></i></button>
                                                <button class='btn btn-danger btn-sm deleteCustomer' data-id='".$row['id']."'><i class='fa fa-trash'></i></button>

                                            </td>";

                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr>
                                            <td colspan='6' class='text-danger'>هیچ پروژه‌ای ثبت نشده است!</td>
                                        </tr>";
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



<!-- ✅ Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">➕ افزودن مشتری جدید</h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>

      <form method="POST" enctype="multipart/form-data" id="addCustomerForm">
        <div class="modal-body">

          <div class="form-row">
            <div class="col-md-6 form-group">
              <label>اسم مشتری *</label>
              <input type="text" name="full_name" class="form-control" required>
            </div>

            <div class="col-md-6 form-group">
              <label>ولد/بنت *</label>
              <input type="text" name="father_name" class="form-control" required>
            </div>
          </div>

          <div class="form-row">
            <div class="col-md-6 form-group">
              <label>شماره تذکره / پاسپورت *</label>
              <input type="text" name="tazkira" class="form-control" required>
            </div>

            <div class="col-md-6 form-group">
              <label>شماره تماس</label>
              <input type="text" name="phone" class="form-control">
            </div>
          </div>

          <div class="form-row">
            <div class="col-md-6 form-group">
              <label>تاریخ تولد</label>
              <input type="date" name="dob" class="form-control">
            </div>

            <div class="col-md-6 form-group">
              <label>وظیفه</label>
              <input type="text" name="job" class="form-control">
            </div>
          </div>

          <div class="form-group">
            <label>آدرس</label>
            <textarea name="address" class="form-control" rows="2"></textarea>
          </div>

          <div class="form-row">
            <div class="col-md-6 form-group">
              <label>عکس مشتری</label>
              <input type="file" name="profile_photo" class="form-control-file" accept="image/*">
            </div>

            <div class="col-md-6 form-group">
              <label>عکس تذکره / پاسپورت</label>
              <input type="file" name="id_card_photo" class="form-control-file" accept="image/*">
            </div>
          </div>

        </div>

        <div class="modal-footer">
            <button type="submit" name="add_customer" class="btn btn-success">✅ ثبت مشتری</button>
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
        $('#customersTable').DataTable({
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
$(document).on("click", ".deleteCustomer", function(){
    var projectID = $(this).data("id");

    if(confirm("آیا مطمئن هستید که می‌خواهید این پروژه را حذف کنید؟")){
        $.ajax({
            url: "delete_customer.php",
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
