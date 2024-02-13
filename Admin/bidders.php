<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../Includes/dbcon.php';
include '../Includes/session.php';

$auctionNumber = $_GET['auctionNumber'] ?? '';

if (!$auctionNumber) {
    header('Location: errorPage.php');
    exit;
}

// Fetch auction details
$auctionQuery = "SELECT * FROM auctiondetails WHERE auction_number = ?";
$stmt = $conn->prepare($auctionQuery);
$stmt->bind_param("s", $auctionNumber);
$stmt->execute();
$auctionResult = $stmt->get_result();
$auctionDetails = $auctionResult->fetch_assoc();

if (!$auctionDetails) {
    echo "Auction not found.";
    exit;
}

// Fetch registered bidders with Active status, count their bought lots, and include bid_number
$biddersQuery = "SELECT b.firstname, b.lastname, b.id_number, ba.bid_number, COUNT(*) AS lots_bought
                FROM bidders b 
                INNER JOIN biddersauction ba ON b.id_number = ba.id_number 
                INNER JOIN lotlist ll ON b.id_number = ll.id_number
                WHERE ba.auction_number = ? AND ba.deposit_status = 1
                GROUP BY b.id_number, ba.bid_number"; // Include ba.bid_number in GROUP BY to ensure it's fetched
$stmt = $conn->prepare($biddersQuery);
$stmt->bind_param("s", $auctionNumber);
$stmt->execute();
$resultLotsBought = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Registered Bidders - <?php echo htmlspecialchars($auctionDetails['auction_name']); ?></title>
  <link href="../vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
</head>
<body id="page-top">
  <div id="wrapper">
  <?php include "Includes/sidebar.php"; ?>
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
                  <h6 class="m-0 font-weight-bold text-primary">All Bidders That Bought Lots</h6>
                </div>
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                  <thead class="thead-light">
  <tr>
    <th><i class="fas fa-user"></i> Bidder Number</th>
    <th><i class="fas fa-user"></i> First Name</th>
    <th><i class="fas fa-user"></i> Last Name</th>
    <th><i class="fas fa-box"></i> Number of Lots Bought</th>
    <th><i class="fas fa-eye"></i> View Lots</th>
  </tr>
</thead>
                    <tbody>
                      <?php
                      while ($row = $resultLotsBought->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['bid_number']}</td>
                                <td>{$row['firstname']}</td>
                                <td>{$row['lastname']}</td>
                                <td>{$row['lots_bought']}</td>
                                <td><a href='lots.php?auctionNumber={$auctionNumber}&bidderId={$row['id_number']}' class='btn btn-primary'><i class='fas fa-eye'></i> View</a></td>
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
      $('#dataTableHover').DataTable();
    });
  </script>
</body>
</html>
