<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Get the auction details where ended_auction is 0
$auctionQuery = "SELECT * FROM auctiondetails WHERE ended_auction = 0";
$stmt = $conn->prepare($auctionQuery);
$stmt->execute();
$auctionResult = $stmt->get_result();
$auctionDetails = $auctionResult->fetch_assoc();

// Check if an active auction is found
if (!$auctionDetails) {
    echo "No active auction found.";
    exit;
}

// Get the auction code of the active auction
$auctionCode = $auctionDetails['auction_number'];

$statusMsg = '';

// Check if the form is submitted
if (isset($_POST['save'])) {
    // Retrieve form data for each lot
    $lotNumbers = $_POST['lotNumber'];
    $titleDescriptions = $_POST['titleDescription'];
    $startBidAmounts = $_POST['startBidAmount'];

    // Loop through each lot and insert into the database
    foreach ($lotNumbers as $key => $lotNumber) {
        // Retrieve lot details
        $lotNumber = $lotNumbers[$key];
        $titleDescription = $titleDescriptions[$key];
        $startBidAmount = $startBidAmounts[$key];

        // Insert lot details into the database for the active auction
        $query = mysqli_query($conn, "INSERT INTO lotlist (auction_number, lot_number, title_desc, start_bid_amt) 
                                 VALUES ('$auctionCode', '$lotNumber', '$titleDescription', '$startBidAmount')");

        if ($query) {
            $statusMsg .= "<div class='alert alert-success' style='margin-right:700px;'>Lot $lotNumber Added Successfully!</div>";
        } else {
            $statusMsg .= "<div class='alert alert-danger' style='margin-right:700px;'>Error for Lot $lotNumber: " . mysqli_error($conn) . "</div>";
        }
    }
}

// Check if the file is uploaded
if(isset($_POST['upload'])){
    $statusMsg = '';

    // File upload path
    $targetDir = "uploads/";
    $fileName = basename($_FILES["lotFile"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath,PATHINFO_EXTENSION);

    // Allow certain file formats
    $allowTypes = array('csv', 'xlsx', 'xls');
    if(in_array($fileType, $allowTypes)){
        // Upload file to server
        if(move_uploaded_file($_FILES["lotFile"]["tmp_name"], $targetFilePath)){
            // Read the uploaded file
            if (($handle = fopen($targetFilePath, "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $lotNumber = $data[0];
                    $titleDescription = $data[1];
                    $startBidAmount = $data[2];

                    // Insert lot details into the database for the active auction
                    $query = mysqli_query($conn, "INSERT INTO lotlist (auction_number, lot_number, title_desc, start_bid_amt) 
                                                VALUES ('$auctionCode', '$lotNumber', '$titleDescription', '$startBidAmount')");

                    if ($query) {
                        $statusMsg .= "<div class='alert alert-success' style='margin-right:700px;'>Lot $lotNumber Added Successfully!</div>";
                    } else {
                        $statusMsg .= "<div class='alert alert-danger' style='margin-right:700px;'>Error for Lot $lotNumber: " . mysqli_error($conn) . "</div>";
                    }
                }
                fclose($handle);
                // Delete the uploaded file after reading
                unlink($targetFilePath);
            } else {
                $statusMsg .= "<div class='alert alert-danger' style='margin-right:700px;'>Failed to open uploaded file.</div>";
            }
        } else {
            $statusMsg .= "<div class='alert alert-danger' style='margin-right:700px;'>Failed to upload file.</div>";
        }
    } else {
        $statusMsg .= "<div class='alert alert-danger' style='margin-right:700px;'>Sorry, only CSV, XLSX, & XLS files are allowed to upload.</div>";
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
    <title>Add Lots</title>
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
                        <!-- <h1 class="h3 mb-0 text-gray-800">Add Lots</h1> -->
                        <a href="lotlists.php" class="btn btn-info">View Lots</a>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="./">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Add Lots</li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <!-- Form Basic -->
                            <div class="card mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Add Lots</h6>

                                </div>
                                <div class="card-body">
                                    <?php echo isset($statusMsg) ? $statusMsg : ''; ?>
                                    <!-- Add a form for uploading the stylesheet -->
                                    <form method="post" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label for="lotFile">Upload Lots Stylesheet</label>
                                            <input type="file" class="form-control-file" id="lotFile" name="lotFile" accept=".csv,.xlsx,.xls">
                                        </div>
                                        <button type="submit" name="upload" class="btn btn-primary">Upload Stylesheet</button>
                                    </form>

                                    <form method="post">
                                        <div id="lots-container">
                                            <div class="lot-row">
                                                <div class="form-group row mb-3">
                                                    <div class="col-xl-4">
                                                        <label class="form-control-label">Lot Number<span
                                                                class="text-danger ml-2">*</span></label>
                                                        <input type="text" class="form-control" name="lotNumber[]"
                                                            required>
                                                    </div>
                                                    <div class="col-xl-4">
                                                        <label class="form-control-label">Title/Description<span
                                                                class="text-danger ml-2">*</span></label>
                                                        <input type="text" class="form-control"
                                                            name="titleDescription[]" required>
                                                    </div>
                                                    <div class="col-xl-4">
                                                        <label class="form-control-label">Starting Bid Amount<span
                                                                class="text-danger ml-2"></span></label>
                                                        <input type="text" class="form-control"
                                                            name="startBidAmount[]">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Add more lots button -->
                                        <button type="button" id="add-lot-btn"
                                            class="btn btn-success">Add Another Lot</button>

                                        <!-- Submit button -->
                                        <button type="submit" name="save"
                                            class="btn btn-primary">Add Lots</button>
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
        document.getElementById('add-lot-btn').addEventListener('click', function () {
            var lotsContainer = document.getElementById('lots-container');
            var newLotRow = lotsContainer.children[0].cloneNode(true);
            lotsContainer.appendChild(newLotRow);
        });
    </script>
</body>
</html>
