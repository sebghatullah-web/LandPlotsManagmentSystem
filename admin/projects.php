<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

include 'config/db.php';

$username = $_SESSION['username'];
$role = $_SESSION['role'];

// ✅ اگر فورم مودال ارسال شد → ذخیره در دیتابیس
if(isset($_POST['project_name'])){
    $name = $_POST['project_name'];
    $location = $_POST['location'];
    $description = $_POST['description'];

    $sql_insert = "INSERT INTO tblProjects (project_name, location, description)
                   VALUES ('$name', '$location', '$description')";
    
    if($conn->query($sql_insert)){
        header("Location: projects.php?msg=success");
        exit();
    } else {
        echo "خطا در ثبت: " . $conn->error;
    }
}

// ✅ نمایش لیست پروژه‌ها
$sql = "SELECT * FROM tblProjects ORDER BY id DESC";
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
    <title>KCC | Projects</title>
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
                        <h3 class="mb-3 text-primary">لیست پروژه‌ها</h3>

                        <button class="btn btn-success btn-sm float-right" data-toggle="modal" data-target="#addProjectModal">
                            <i class="fa fa-plus"></i> افزودن پروژه
                        </button>

                        <?php if(isset($_GET['msg']) && $_GET['msg'] == "success"){ ?>
                        <div class="alert alert-success text-center">
                            پروژه با موفقیت ثبت شد ✅
                        </div>
                        <?php } ?>
                        
                        <div id="alertArea"></div>

                        <table id="projectsTable" class="table table-bordered table-striped text-center">
                            <thead class="table-dark">
                                <tr>
                                    <th>شماره</th>
                                    <th>نام پروژه</th>
                                    <th>موقعیت</th>
                                    <th>توضیحات</th>
                                    <th>تاریخ ایجاد</th>
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
                                        echo "<td>".$row['project_name']."</td>";
                                        echo "<td>".$row['location']."</td>";
                                        echo "<td>".$row['description']."</td>";
                                        echo "<td>".$row['created_at']."</td>";
                                        echo "<td>

                                                <button class='btn btn-primary btn-sm editbtn' data-id='".$row['id']."'><i class='fa fa-edit'></i></button>
                                                <button class='btn btn-danger btn-sm deleteProject' data-id='".$row['id']."'><i class='fa fa-trash'></i></button>

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
        $('#projectsTable').DataTable({
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


    <!-- Add Project Modal -->
<div class="modal fade" id="addProjectModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">افزودن پروژه جدید</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <form action="projects.php" method="POST">
        <div class="modal-body">

          <div class="form-group">
            <label>نام پروژه</label>
            <input type="text" name="project_name" class="form-control" required>
          </div>

          <div class="form-group">
            <label>موقعیت</label>
            <input type="text" name="location" class="form-control">
          </div>

          <div class="form-group">
            <label>توضیحات</label>
            <textarea name="description" class="form-control"></textarea>
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

<!-- Delete Project Modal -->
<script>
$(document).on("click", ".deleteProject", function(){
    var projectID = $(this).data("id");

    if(confirm("آیا مطمئن هستید که می‌خواهید این پروژه را حذف کنید؟")){
        $.ajax({
            url: "delete_project.php",
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

<!-- Edit Project Modal -->
<div class="modal fade" id="editProjectModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header bg-warning text-white">
        <h5 class="modal-title">ویرایش پروژه</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <form id="editProjectForm">
        <input type="hidden" name="id" id="edit_id">

        <div class="modal-body">

          <div class="form-group">
            <label>نام پروژه</label>
            <input type="text" name="project_name" id="edit_name" class="form-control" required>
          </div>

          <div class="form-group">
            <label>موقعیت</label>
            <input type="text" name="location" id="edit_location" class="form-control">
          </div>

          <div class="form-group">
            <label>توضیحات</label>
            <textarea name="description" id="edit_description" class="form-control"></textarea>
          </div>

          <div class="form-group">
            <label>تاریخ ایجاد</label>
            <input type="datetime-local" name="created_at" id="edit_created_at" class="form-control">
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
          <button type="submit" class="btn btn-warning">ذخیره تغییرات</button>
        </div>

      </form>

    </div>
  </div>
</div>

<script>
// ✅ Load project data into modal
$(document).on("click", ".editbtn", function(){
    var id = $(this).data("id");

    $.post("get_project.php", {id:id}, function(data){
        var project = JSON.parse(data);

        $("#edit_id").val(project.id);
        $("#edit_name").val(project.project_name);
        $("#edit_location").val(project.location);
        $("#edit_description").val(project.description);

        let formatted = project.created_at.replace(" ", "T");
        $("#edit_created_at").val(formatted);

        $("#editProjectModal").modal("show");
    });
});

// ✅ Submit update AJAX
$("#editProjectForm").submit(function(e){
    e.preventDefault();

    $.post("update_project.php", $(this).serialize(), function(response){
        if(response == "success"){
            $("#editProjectModal").modal("hide");

            $("#projectsTable").load(location.href+" #projectsTable>*","");
        } else {
            alert("خطا در ویرایش!");
        }
    });
});

// ✅ Submit update AJAX
$("#editProjectForm").submit(function(e){
    e.preventDefault();

    $.post("update_project.php", $(this).serialize(), function(response){
        if(response == "success"){
            $("#editProjectModal").modal("hide");

            // Refresh table instantly
            $("#projectsTable").load(location.href+" #projectsTable>*","");

            // ✅ Show success alert
            let successAlert = `
                <div class="alert alert-success text-center mt-2" id="updateMsg">
                    ویرایش با موفقیت انجام شد ✅
                </div>`;
            $("#alertArea").html(successAlert);

            // ✅ Auto hide message after 3 seconds
            setTimeout(() => {
                $("#updateMsg").fadeOut();
            }, 3000);

        } else {
            alert("خطا در ویرایش!");
        }
    });
});

</script>


</body>

</html>
