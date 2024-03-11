<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Fetch auction details where ended_auction is 0
$auctionQuery = "SELECT * FROM auctiondetails WHERE ended_auction = 0";
$stmt = $conn->prepare($auctionQuery);
$stmt->execute();
$auctionResult = $stmt->get_result();
$auctionDetails = $auctionResult->fetch_assoc();

if (!$auctionDetails) {
    echo "No active auction found.";
    exit;
}

$auctionNumber = $auctionDetails['auction_number'];

// Fetch lots for the selected auction
$lotsQuery = "SELECT ll.id, ll.lot_number, ll.title_desc, ll.payment_status, ll.sold_for_amt, ll.start_bid_amt 
              FROM lotlist ll
              WHERE ll.auction_number = ?";
$stmt = $conn->prepare($lotsQuery);
$stmt->bind_param("s", $auctionNumber);
$stmt->execute();
$lotsResult = $stmt->get_result();
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
    <title>View Lots</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="css/ruang-admin.min.css" rel="stylesheet">
</head>
<body id="page-top">
  <div id="wrapper">
   
    <div id="content-wrapper" class="d-flex flex-column">
      <?php include "Includes/topbar.php"; ?>
      <div id="content">
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <!-- <h1 class="h3 mb-0 text-gray-800">Lots for Auction: <?php echo htmlspecialchars($auctionDetails['auction_name']); ?></h1> -->
            <a href="manage_lots.php" class="btn btn-info">Add Lots</a>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="./">Home</a></li>
                            <li class="breadcrumb-item"><a href="manage_lots.php">Add Lots</a></li>
                            <li class="breadcrumb-item active" aria-current="page">View Lots</li>
                        </ol>
          </div>
          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">All Lots</h6>
                </div>
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                    <thead class="thead-light">
                    <tr>
    <th><i class="fas fa-sort-numeric-up"></i> Lot Number</th>
    <th><i class="fas fa-file-alt"></i> Title/Description</th>
    <th><i class="fas fa-money-bill-wave"></i> Starting Amount</th>
    <th><i class="fas fa-money-bill"></i> Sold For Amount</th>
    <th><i class="fas fa-money-check-alt"></i> Payment Status</th>
  </tr>
                    </thead>
                    <tbody>
                      <?php while ($row = $lotsResult->fetch_assoc()): ?>
                        <tr data-lot-id="<?php echo $row['id']; ?>">
                          <td><?php echo htmlspecialchars($row['lot_number']); ?></td>
                          <td><?php echo htmlspecialchars($row['title_desc']); ?></td>
                          <td><?php echo htmlspecialchars($row['start_bid_amt']); ?></td>
                          <td><?php echo ($row['sold_for_amt'] == null || $row['sold_for_amt'] == 0.00) ? '-----' : htmlspecialchars($row['sold_for_amt']); ?></td>
                          <td class="<?php echo $row['payment_status'] ? 'text-success' : 'text-danger'; ?>"><?php echo ($row['sold_for_amt'] == null || $row['sold_for_amt'] == 0.00) ? '-----' : ($row['payment_status'] ? 'Paid' : 'Unpaid'); ?></td>
                        </tr>
                      <?php endwhile; ?>
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
  <!-- DataTables -->
  <script>
    $(document).ready(function () {
      $('#dataTableHover').DataTable();
    });
  </script>
</body>
</html>
