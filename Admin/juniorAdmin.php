<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../Includes/dbcon.php'; // Include your database connection file here

$statusMsg = ''; // Initialize status message

if (isset($_POST['save'])) {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $emailAddress = $_POST['emailAddress'];
    $password = md5($_POST['password']); // Hash the password using MD5

    // Insert new junior admin details into the database
    $query = "INSERT INTO junior (firstName, lastName, emailAddress, password) VALUES ('$firstName', '$lastName', '$emailAddress', '$password')";

    if (mysqli_query($conn, $query)) {
        $statusMsg = "<div class='alert alert-success'>Junior admin added successfully.</div>";
    } else {
        $statusMsg = "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Junior Admin</title>
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/ruang-admin.min.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- TopBar -->
                <?php include "Includes/topbar.php";?>
                <!-- Topbar -->

                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Add New Junior Admin</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="./">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Add New Junior Admin</li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <!-- Form Basic -->
                            <div class="card mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Add New Junior Admin</h6>
                                </div>
                                <div class="card-body">
                                    <?php echo $statusMsg; // Display status message ?>
                                    <form method="post" onsubmit="return validateForm()">
                                        <div class="form-group row mb-3">
                                            <div class="col-xl-6">
                                                <label class="form-control-label">First Name<span class="text-danger ml-2">*</span></label>
                                                <input type="text" class="form-control" name="firstName" required>
                                            </div>
                                            <div class="col-xl-6">
                                                <label class="form-control-label">Last Name<span class="text-danger ml-2">*</span></label>
                                                <input type="text" class="form-control" name="lastName" required>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-3">
                                            <div class="col-xl-6">
                                                <label class="form-control-label">Email Address<span class="text-danger ml-2">*</span></label>
                                                <input type="email" class="form-control" name="emailAddress" required>
                                            </div>
                                            <div class="col-xl-6">
                                                <label class="form-control-label">Password<span class="text-danger ml-2">*</span></label>
                                                <input type="password" class="form-control" id="password" name="password" required>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-3">
                                            <div class="col-xl-6">
                                                <label class="form-control-label">Confirm Password<span class="text-danger ml-2">*</span></label>
                                                <input type="password" class="form-control" id="confirmPassword" required>
                                            </div>
                                        </div>
                                        <button type="submit" name="save" class="btn btn-primary">Add Junior Admin</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!---Container Fluid-->
            </div>
            <!-- Footer -->
            <?php include "Includes/footer.php";?>
            <!-- Footer -->
        </div>
    </div>

    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/ruang-admin.min.js"></script>
    <script>
        function validateForm() {
            var password = document.getElementById("password").value;
            var confirmPassword = document.getElementById("confirmPassword").value;

            if (password != confirmPassword) {
                alert("Passwords do not match.");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
