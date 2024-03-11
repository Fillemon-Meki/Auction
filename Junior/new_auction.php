<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../Includes/dbcon.php';
include '../Includes/session.php';

$statusMsg = ''; // Initialize status message

if (isset($_POST['save'])) {
    $auctionName = $_POST['auctionName'];
    $depositAmount = $_POST['depositAmount'];
    $region = $_POST['region'];
    $town = $_POST['town'];
    $dateOfAuction = $_POST['dateOfAuction'];
    $startTime = $_POST['startTime'];
    $endTime = $_POST['endTime'];
    


    // Generate a unique auction code with "AUC-" prefix
    $auctionCode = uniqid("AUC-");

    // Set the URLs for the QR code columns
    $registrationURL = "https://yourdomain.com/registration.php?auction_number=$auctionCode";
    $lotsURL = "https://yourdomain.com/lots.php?auction_number=$auctionCode";
    $selfRegistrationURL = "https://yourdomain.com/self_registration.php?auction_number=$auctionCode";

    // Save auction details, generated auction code, and QR code URLs to the database
    $query = mysqli_query($conn, "INSERT INTO auctiondetails (auction_code, auction_name, deposit_amount, region, town, date_of_auction, start_date_time, end_date_time, qr_1, qr_2, qr_3)
                             VALUES ('$auctionCode', '$auctionName', '$depositAmount', '$region', '$town', '$dateOfAuction', '$startTime', '$endTime', '$registrationURL', '$lotsURL', '$selfRegistrationURL')");

    if ($query) {
        $statusMsg = "<div class='alert alert-success' style='margin-right:700px;'>Auction Created Successfully! Auction Code: $auctionCode</div>";
    } else {
        $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>Error: " . mysqli_error($conn) . "</div>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link href="img/logo/attnlg.jpg" rel="icon">
    <title>Create New Auction</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
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
                        <h1 class="h3 mb-0 text-gray-800">Create New Auction</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="./">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Create New Auction</li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <!-- Form Basic -->
                            <div class="card mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Create New Auction</h6>
                                </div>
                                <div class="card-body">
                                    <?php echo $statusMsg; // Display status message ?>
                                    <form method="post">
                                        <div class="form-group row mb-3">
                                            <div class="col-xl-6">
                                                <label class="form-control-label">Auction Name<span class="text-danger ml-2">*</span></label>
                                                <input type="text" class="form-control" name="auctionName" required>
                                            </div>
                                            <div class="col-xl-6">
                                                <label class="form-control-label">Deposit Amount<span class="text-danger ml-2">*</span></label>
                                                <input type="text" class="form-control" name="depositAmount" required>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-3">
                                            <div class="col-xl-6">
                                                <label class="form-control-label">Region<span class="text-danger ml-2">*</span></label>
                                                <input type="text" class="form-control" name="region" required>
                                            </div>
                                            <div class="col-xl-6">
                                                <label class="form-control-label">Town<span class="text-danger ml-2">*</span></label>
                                                <input type="text" class="form-control" name="town" required>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-3">
                                            <div class="col-xl-6">
                                                <label class="form-control-label">Start Time<span class="text-danger ml-2">*</span></label>
                                                <input type="time" class="form-control" name="startTime" required>
                                            </div>
                                            <div class="col-xl-6">
                                                <label class="form-control-label">End Time</label>
                                                <input type="time" class="form-control" name="endTime">
                                            </div>
                                            
                                        </div>
                                        <div class="form-group row mb-3">
                                            <div class="col-xl-6">
                                                <label class="form-control-label">Date of Auction<span class="text-danger ml-2">*</span></label>
                                                <input type="date" class="form-control" name="dateOfAuction" required>
                                            </div>
                                            
                                        </div>
                                       
                                        <button type="submit" name="save" class="btn btn-primary">Create Auction</button>
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
</body>
</html>
