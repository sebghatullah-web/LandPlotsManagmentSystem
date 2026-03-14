<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

include 'config/db.php';

$username = $_SESSION['username'];
$role = $_SESSION['role'];

// --- پردازش فرم افزودن Plot (ارسال POST از مودال) ---
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_plot'])) {

    // گرفتن و پاک‌سازی ورودی‌ها
    $plot_code = $conn->real_escape_string(trim($_POST['plot_code']));
    $project_id = intval($_POST['project_id']);
    $plot_type = $conn->real_escape_string(trim($_POST['plot_type']));
    $area = is_numeric($_POST['area']) ? floatval($_POST['area']) : null;
    $length = is_numeric($_POST['length']) ? floatval($_POST['length']) : null;
    $width = is_numeric($_POST['width']) ? floatval($_POST['width']) : null;
    $boundaries = $conn->real_escape_string(trim($_POST['boundaries']));
    $price = is_numeric($_POST['price']) ? number_format((float)$_POST['price'], 2, '.', '') : null;
    $status = in_array($_POST['status'], ['Available','Reserved','Sold']) ? $_POST['status'] : 'Available';
    $owner_customer_id = ($_POST['owner_customer_id'] === "" ? null : intval($_POST['owner_customer_id']));
    // created_at از دیتابیس بصورت DEFAULT CURRENT_TIMESTAMP گرفته می‌شود مگر بخواهید وارد کنید.

    // ساده‌ترین اعتبارسنجی
    $errors = [];
    if($plot_code === '') $errors[] = "کد قطعه الزامی است.";
    if($project_id <= 0) $errors[] = "پروژه را انتخاب کنید.";

    // آپلود فایل نقشه (اختیاری)
    $map_file_db = null;
    if(isset($_FILES['map_file']) && $_FILES['map_file']['error'] !== UPLOAD_ERR_NO_FILE){
        $uploadDir = __DIR__ . '/uploads/maps/';
        if(!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $f = $_FILES['map_file'];
        if($f['error'] === UPLOAD_ERR_OK){
            $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
            $allowed = ['jpg','jpeg','png','pdf','svg'];
            if(!in_array(strtolower($ext), $allowed)){
                $errors[] = "فرمت نقشه معتبر نیست. (jpg, png, pdf, svg)";
            } else {
                $newName = 'map_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $target = $uploadDir . $newName;
                if(move_uploaded_file($f['tmp_name'], $target)){
                    // ذخیره مسیر نسبت به روت پروژه (برای مثال)
                    $map_file_db = 'assets/maps/' . $newName;
                } else {
                    $errors[] = "خطا در آپلود فایل نقشه.";
                }
            }
        } else {
            $errors[] = "خطا در آپلود فایل نقشه.";
        }
    }

    if(empty($errors)){
        // Prepared statement برای امنیت
        $stmt = $conn->prepare("INSERT INTO tblPlots 
        (plot_code, project_id, plot_type, area, length, width, boundaries, price, status, map_file, owner_customer_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "sissdddsssi",
            $plot_code,
            $project_id,
            $plot_type,
            $area,
            $length,
            $width,
            $boundaries,
            $price,
            $status,
            $map_file,
            $owner_customer_id
        );


        // NOTE: Some PHP versions don't accept spaces in types; to avoid complexity, we'll use this fallback:
        $stmt->close(); // close prepared (we will do simpler safe insert with real_escape earlier)
        // fallback insert (safe enough since inputs escaped and we used set_charset)
        $proj = $project_id;
        $oc = ($owner_customer_id === null) ? "NULL" : $owner_customer_id;
        $map_sql = ($map_file_db === null) ? "NULL" : ("'" . $conn->real_escape_string($map_file_db) . "'");
        $sql = "INSERT INTO tblPlots 
            (plot_code, project_id, plot_type, area, length, width, boundaries, price, status, map_file, owner_customer_id)
            VALUES (
                '".$conn->real_escape_string($plot_code)."',
                $proj,
                '".$conn->real_escape_string($plot_type)."',
                ".($area === null ? "NULL" : $area).",
                ".($length === null ? "NULL" : $length).",
                ".($width === null ? "NULL" : $width).",
                '".$conn->real_escape_string($boundaries)."',
                ".($price === null ? "NULL" : $price).",
                '".$conn->real_escape_string($status)."',
                $map_sql,
                ".($oc === "NULL" ? "NULL" : $oc)."
            )";

        if($conn->query($sql)){
            // redirect to avoid repost on refresh
            header("Location: ".$_SERVER['PHP_SELF']."?msg=success");
            exit();
        } else {
            $errors[] = "خطا در ذخیره: " . $conn->error;
        }
    }
}

// ✅ نمایش لیست پروژه‌ها
$sql = "SELECT * FROM tblplots ORDER BY id DESC";
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
    <title>KCC | Plots</title>
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
                        <h3 class="mb-3 text-primary">لیست نمرات</h3>

                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addPlotModal">
                            <i class="fa fa-plus"></i> افزودن نمره
                        </button>

                        <?php if(isset($_GET['msg']) && $_GET['msg'] == "success"){ ?>
                            <div class="alert alert-success text-center">
                                پروژه با موفقیت ثبت شد ✅
                            </div>
                        <?php } ?>

                         <?php if(isset($_GET['updated'])): ?>
                            <div class="alert alert-success text-right">
                                ✅ تغییرات با موفقیت ذخیره شد!
                            </div>
                        <?php endif; ?>

                        <div id="alertArea"></div>

                        <table id="plotTable" class="table table-bordered table-striped text-center">
                            <thead class="table-dark">
                                <tr>
                                    <th>شماره</th>
                                    <th>کود نمره</th>
                                    <th>نوعیت نمره</th>
                                    <th>قیمت نمره</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php 
                                $i = 1;
                                if($result->num_rows > 0){
                                    while($row = $result->fetch_assoc()){
                                        echo "<tr>";
                                        echo "<td>".$i++."</td>";
                                        echo "<td>".$row['plot_code']."</td>";
                                        echo "<td>".$row['plot_type']."</td>";
                                        echo "<td>".$row['price']."</td>";
                                        echo "<td>
                                                <button class='btn btn-primary btn-sm' onclick=\"window.location.href='edit-plot.php?id={$row['id']}'\"><i class='fa fa-edit'></i></button>
                                                <button class='btn btn-danger btn-sm deletePlots' data-id='".$row['id']."'><i class='fa fa-trash'></i></button>

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

    <!-- Add Plot Modal -->
<div class="modal fade" id="addPlotModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content" dir="rtl">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">افزودن نمره جدید</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
      </div>

      <form action="<?= $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="add_plot" value="1">
        <div class="modal-body">

          <div class="row">
            <div class="col-md-6 form-group">
              <label>کد نمره (Plot Code) <span class="text-danger">*</span></label>
              <input type="text" name="plot_code" class="form-control" required>
            </div>

            <div class="col-md-6 form-group">
              <label>پروژه <span class="text-danger">*</span></label>
              <select name="project_id" class="form-control select2" required>
                <option value="">انتخاب پروژه</option>
                <?php
                // بارگذاری پروژه‌ها برای select
                $q = $conn->query("SELECT id, project_name FROM tblProjects ORDER BY project_name ASC");
                while($p = $q->fetch_assoc()){
                    echo "<option value=\"{$p['id']}\">".htmlspecialchars($p['project_name'])."</option>";
                }
                ?>
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4 form-group">
              <label>نوع نمره</label>
              <input type="text" name="plot_type" class="form-control" placeholder="مثال: دو بسوه یی">
            </div>
            <div class="col-md-2 form-group">
              <label>مساحت (m²)</label>
              <input type="number" step="0.01" name="area" class="form-control">
            </div>
            <div class="col-md-2 form-group">
              <label>طول</label>
              <input type="number" step="0.01" name="length" class="form-control">
            </div>
            <div class="col-md-2 form-group">
              <label>عرض</label>
              <input type="number" step="0.01" name="width" class="form-control">
            </div>
            <div class="col-md-2 form-group">
              <label>قیمت</label>
              <input type="number" step="0.01" name="price" class="form-control">
            </div>
          </div>

          <div class="form-group">
            <label>چهار سمت  / Boundaries</label>
            <textarea name="boundaries" class="form-control" rows="2"></textarea>
          </div>

          <div class="row">
            <div class="col-md-6 form-group">
              <label>وضعیت</label>
              <select name="status" class="form-control">
                <option value="Available">Available</option>
                <option value="Reserved">Reserved</option>
                <option value="Sold">Sold</option>
              </select>
            </div>

            <div class="col-md-6 form-group">
                <label>مالک (اختیاری)</label>
                <select name="owner_customer_id" class="form-control select2">
                    <option value="">— مالک ندارد —</option>
                    <?php
                    $cq = $conn->query("SELECT id, fullname FROM tblCustomers ORDER BY fullname ASC");
                    while($c = $cq->fetch_assoc()){
                        echo "<option value=\"{$c['id']}\">".htmlspecialchars($c['fullname'])."</option>";
                    }
                    ?>
                </select>
            </div>


          <div class="form-group">
            <label>فایل نقشه (jpg, png, pdf, svg) — اختیاری</label>
            <input type="file" name="map_file" class="form-control-file">
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
          <button type="submit" class="btn btn-primary">ذخیره</button>
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
        $('#plotTable').DataTable({
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

<!-- Delete Plots Modal -->
<script>
$(document).on("click", ".deletePlots", function(){
    var projectID = $(this).data("id");

    if(confirm("آیا مطمئن هستید که می‌خواهید این پروژه را حذف کنید؟")){
        $.ajax({
            url: "delete_plots.php",
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
