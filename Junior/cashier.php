<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Fetch active auctions
$activeAuctionsQuery = "SELECT auction_number FROM auctiondetails WHERE ended_auction = 0";
$activeAuctionsResult = $conn->query($activeAuctionsQuery);

// Initialize an array to store auction numbers
$activeAuctionNumbers = [];

// Fetch auction numbers from the result set
while ($row = $activeAuctionsResult->fetch_assoc()) {
    $activeAuctionNumbers[] = $row['auction_number'];
}

// Check if there are active auctions
if (empty($activeAuctionNumbers)) {
    echo "No active auctions found.";
    exit;
}

// Fetch registered bidders for all active auctions
$biddersQuery = "SELECT b.id_number, b.firstname, b.lastname, ba.bid_number, ba.deposit_status 
                 FROM bidders b
                 INNER JOIN biddersauction ba ON b.id_number = ba.id_number 
                 WHERE ba.auction_number IN (" . implode(',', array_fill(0, count($activeAuctionNumbers), '?')) . ")";
$stmt = $conn->prepare($biddersQuery);
// Bind auction numbers as parameters
foreach ($activeAuctionNumbers as $index => $auctionNumber) {
    $stmt->bind_param("s", $auctionNumber);
}
$stmt->execute();
$biddersResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Registered Bidders - All Active Auctions</title>
  <link href="../vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
</head>
<body id="page-top">
  <div id="wrapper">
    
    <div id="content-wrapper" class="d-flex flex-column">
      <?php include "Includes/topbar.php"; ?>
      <div id="content">
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Bidders</h1>
           
          </div>

          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">All Registered Bidders</h6>
                </div>
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                  <thead class="thead-light">
  <tr>
    <th><i class="fas fa-user"></i> Bidder Number</th>
    <th><i class="fas fa-user"></i> First Name</th>
    <th><i class="fas fa-user"></i> Last Name</th>
    <th><i class="fas fa-info-circle"></i> Status</th>
  </tr>
</thead>
                    <tbody>
                    <?php
while ($row = $biddersResult->fetch_assoc()) {
    // Check if the array keys exist before accessing them
    $depositStatus = isset($row['deposit_status']) ? $row['deposit_status'] : null;
    $bidNumber = isset($row['bid_number']) ? $row['bid_number'] : null;
    $idNumber = isset($row['id_number']) ? $row['id_number'] : null;

    $statusText = ($depositStatus == 1) ? 'Active' : 'Inactive';

    echo "<tr class='clickable-row' data-href='bidderDetails.php?bidderId={$idNumber}'>
    <td>{$bidNumber}</td>
    <td>{$row['firstname']}</td>
    <td>{$row['lastname']}</td>
    <td>
        <div class='status-dropdown' data-id='{$idNumber}'>
            <i class='fas fa-circle status-icon' style='color: ", ($depositStatus == 1) ? 'green' : 'red', "'></i>
            <span class='status-text'>{$statusText}</span>
        </div>
    </td>
  </tr>";
}
?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php include "Includes/footer.php";?>
    </div>
  </div>

  <script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script>
  $(document).ready(function () {
    var table = $('#dataTableHover').DataTable(); // Initialize DataTables

    // Function to make rows clickable
    function makeRowsClickable() {
      $(".clickable-row").off('click').on('click', function() { // Detach previous click events to prevent multiple bindings and then reattach
        window.location = $(this).data("href");
      });
    }

    makeRowsClickable(); // Call it once to make rows clickable initially

    // Re-apply click event each time the table is drawn (e.g., pagination, sorting)
    table.on('draw', function(){
      makeRowsClickable();
    });
  });
</script>