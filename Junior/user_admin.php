<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Update Bidder
if(isset($_POST['update'])){
    $idNumber = $_POST['idNumber'];
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $cellphoneNumber = $_POST['cellphoneNumber'];
    $email = $_POST['email'];

    $query = "UPDATE bidders SET 
                firstName='$firstName', 
                lastName='$lastName', 
                cellphone_number='$cellphoneNumber', 
                email='$email' 
              WHERE id_number='$idNumber'";

    $result = mysqli_query($conn, $query);

    if ($result) {
        $statusMsg = "<div class='alert alert-success' style='margin-right:700px;'>Bidder information updated successfully!</div>";
    } else {
        $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>Failed to update bidder information. Please try again.</div>";
    }
}

// Fetch active auction details
$auctionQuery = "SELECT * FROM auctiondetails WHERE ended_auction = 0";
$auctionResult = mysqli_query($conn, $auctionQuery);
$auctionDetails = mysqli_fetch_assoc($auctionResult);

// Check if an active auction is found
if (!$auctionDetails) {
    echo "No active auction found.";
    exit;
}

$auctionCode = $auctionDetails['auction_code'];

// Fetch bidders for the active auction
$query = "SELECT b.id_number, b.firstname, b.lastname, b.cellphone_number, b.email
          FROM bidders b
          JOIN biddersauction ba ON b.id_number = ba.id_number
          JOIN auctiondetails ad ON ba.auction_number = ad.auction_number
          WHERE ad.auction_code = '$auctionCode'";

$result = mysqli_query($conn, $query);
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
    <?php include 'includes/title.php';?>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="css/ruang-admin.min.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar -->
        
        <!-- Sidebar -->
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- TopBar -->
                <?php include "Includes/topbar.php";?>
                <!-- Topbar -->

                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Edit Bidders</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="./"><i class="fas fa-home"></i> Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit Bidders</li>
                        </ol>
                    </div>
                    

                    <!-- Add your HTML content here for editing bidders -->
                    <div class="row">
                        <div class="col-lg-12">
                            <?php echo isset($statusMsg) ? $statusMsg : ''; ?>
                            <div class="card mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-edit"></i> Edit Bidder Information</h6>
                                </div>
                                <div class="card-body">
                                    <form method="post">
                                        
                                        <div class="form-group row">
    <label for="idNumber" class="col-sm-2 col-form-label"><i class="fas fa-id-card"></i> ID Number</label>
    <div class="col-sm-10">
        <input type="text" class="form-control" id="idNumber" name="idNumber" required>
    </div>
</div>
<div class="form-group row">
    <label for="firstName" class="col-sm-2 col-form-label"><i class="fas fa-user"></i> First Name</label>
    <div class="col-sm-10">
        <input type="text" class="form-control" id="firstName" name="firstName" required>
    </div>
</div>
<div class="form-group row">
    <label for="lastName" class="col-sm-2 col-form-label"><i class="fas fa-user"></i> Last Name</label>
    <div class="col-sm-10">
        <input type="text" class="form-control" id="lastName" name="lastName" required>
    </div>
</div>
<div class="form-group row">
    <label for="cellphoneNumber" class="col-sm-2 col-form-label"><i class="fas fa-phone"></i> Cellphone Number</label>
    <div class="col-sm-10">
        <input type="text" class="form-control" id="cellphoneNumber" name="cellphoneNumber" required>
    </div>
</div>
<div class="form-group row">
    <label for="email" class="col-sm-2 col-form-label"><i class="fas fa-envelope"></i> Email</label>
    <div class="col-sm-10">
        <input type="email" class="form-control" id="email" name="email" required>
    </div>
</div>

                                        <div class="form-group row">
                                            <div class="col-sm-10 offset-sm-2">
                                                <button type="submit" class="btn btn-primary" name="update"><i class="fas fa-save"></i> Update Bidder</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Display bidders for the active auction -->
                    <div class="row">
    <div class="col-lg-12">
        <div class="card mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-list"></i> Bidders for Auction <?php echo $auctionCode; ?></h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th><i class="fas fa-id-card"></i> ID Number</th>
                                <th><i class="fas fa-user"></i> First Name</th>
                                <th><i class="fas fa-user"></i> Last Name</th>
                                <th><i class="fas fa-phone"></i> Cellphone Number</th>
                                <th><i class="fas fa-envelope"></i> Email</th>
                                <th><i class="fas fa-cogs"></i> Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td><?php echo $row['id_number']; ?></td>
                                    <td><?php echo $row['firstname']; ?></td>
                                    <td><?php echo $row['lastname']; ?></td>
                                    <td><?php echo $row['cellphone_number']; ?></td>
                                    <td><?php echo $row['email']; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary edit-btn" data-id="<?php echo $row['id_number']; ?>"><i class="fas fa-edit"></i> Edit</button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
                    <!-- End of Display bidders for the active auction -->

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
    <!-- Page level plugins -->
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
    


    <script>
    // Script to handle editing bidder information without redirecting
    $(document).ready(function () {
        $('.edit-btn').click(function () {
            var idNumber = $(this).data('id');
            // Fetch bidder information using AJAX
            $.ajax({
                url: 'fetch_bidder_info.php',
                method: 'POST',
                data: {idNumber: idNumber},
                dataType: 'json',
                success: function (response) {
                    // Populate form fields with fetched data
                    $('#idNumber').val(response.idNumber);
                    $('#firstName').val(response.firstName);
                    $('#lastName').val(response.lastName);
                    $('#cellphoneNumber').val(response.cellphoneNumber);
                    $('#email').val(response.email);
                }
            });
        });
    });
</script>

    <!-- Page level custom scripts -->
    <script>
        $(document).ready(function () {
            $('#dataTable').DataTable();
        });
    </script>
</body>
</html>
