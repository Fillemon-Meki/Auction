<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Get bidder ID from the URL
$bidderId = $_GET['bidderId'] ?? '';

if (!$bidderId) {
    header('Location: errorPage.php');
    exit;
}

// Fetch bidder details
$bidderQuery = "SELECT * FROM bidders WHERE id_number = ?";
$stmt = $conn->prepare($bidderQuery);
$stmt->bind_param("s", $bidderId);
$stmt->execute();
$bidderResult = $stmt->get_result();
$bidderDetails = $bidderResult->fetch_assoc();

if (!$bidderDetails) {
    echo "Bidder not found.";
    exit;
}

// Fetch bidder number from biddersauction table
$bidderNumberQuery = "SELECT bid_number, deposit_status FROM biddersauction WHERE id_number = ?";
$stmt = $conn->prepare($bidderNumberQuery);
$stmt->bind_param("s", $bidderId);
$stmt->execute();
$bidderNumberResult = $stmt->get_result();
$bidderNumberRow = $bidderNumberResult->fetch_assoc();
$bidderNumber = $bidderNumberRow ? $bidderNumberRow['bid_number'] : 'Not Available';

// Determine bidder status
$bidderStatus = $bidderNumberRow['deposit_status'] == 1 ? 'Active' : 'Inactive';

// Fetch statistics (e.g., total number of lots purchased, total amount spent, etc.)
$statisticsQuery = "SELECT COUNT(*) AS total_lots_purchased, SUM(sold_for_amt) AS total_amount_spent
                    FROM lotlist
                    WHERE id_number = ?";
$stmt = $conn->prepare($statisticsQuery);
$stmt->bind_param("s", $bidderId);
$stmt->execute();
$statisticsResult = $stmt->get_result();
$statistics = $statisticsResult->fetch_assoc();

// Fetch lots bought by the selected bidder
$lotsBoughtQuery = "SELECT * FROM lotlist WHERE id_number = ?";
$stmt = $conn->prepare($lotsBoughtQuery);
$stmt->bind_param("s", $bidderId);
$stmt->execute();
$lotsBoughtResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Bidder Details</title>
  <link href="../vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
  <style>
    .status-active {
      color: green;
    }
    .status-inactive {
      color: red;
    }
  </style>
</head>
<body id="page-top">
  <div id="wrapper">
  <?php include "Includes/sidebar.php"; ?>
    <div id="content-wrapper" class="d-flex flex-column">
    <?php include "Includes/topbar.php"; ?>
      <div id="content">
        <div class="container-fluid" id="container-wrapper">
          <h1 class="h3 mb-4 text-gray-800">Bidder Details</h1>
          
          <div class="row">
            <div class="col-lg-6">
              <div class="card mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user"></i> Bidder Information</h6>
                </div>
                <div class="card-body">
                  <p><strong>First Name:</strong> <?php echo htmlspecialchars($bidderDetails['firstname']); ?></p>
                  <p><strong>Last Name:</strong> <?php echo htmlspecialchars($bidderDetails['lastname']); ?></p>
                  <p><strong>ID Number:</strong> <?php echo htmlspecialchars($bidderDetails['id_number']); ?></p>
                  <p><strong>Bidder Number:</strong> <?php echo htmlspecialchars($bidderNumber); ?></p>
                  <p><strong>Status:</strong> <span class="status-<?php echo strtolower($bidderStatus); ?>"><?php echo $bidderStatus; ?></span></p>
                  <!-- Add other bidder details here -->
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="card mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-bar"></i> Bidder Statistics</h6>
                </div>
         
<div class="card-body">
    <p><strong>Total Lots Purchased:</strong> <?php echo htmlspecialchars($statistics['total_lots_purchased']); ?></p>
    <?php if ($statistics['total_amount_spent'] !== null) { ?>
        <p><strong>Total Amount Spent:</strong> <?php echo htmlspecialchars($statistics['total_amount_spent']); ?></p>
    <?php } else { ?>
        <p><strong>Total Amount Spent:</strong> 0.00</p>
    <?php } ?>
    <!-- Add other bidder statistics here -->
</div>


              </div>
            </div>
          </div>

          <!-- Table for lots bought by the bidder -->
          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Lots Bought by Bidder</h6>
                </div>
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                    <thead class="thead-light">
                      <tr>
                        <th><i class="fas fa-hashtag"></i> Lot Number</th>
                        <th><i class="fas fa-box"></i> Description</th>
                        <th><i class="fas fa-dollar-sign"></i> Sold For Amount</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      while ($row = $lotsBoughtResult->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['lot_number']}</td>
                                <td>{$row['title_desc']}</td>
                                <td>{$row['sold_for_amt']}</td>
                              </tr>";
                      }
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <!-- Add a button to download bidder number PDF -->
<a href="generate_pdf.php?bidderId=<?php echo $bidderId; ?>" class="btn btn-primary" target="_blank">Download Bidder Number</a>

        </div>
      </div>
      <?php include "Includes/footer.php";?>
    </div>
  </div>
  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
