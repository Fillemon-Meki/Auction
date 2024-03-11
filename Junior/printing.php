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

// Check if an active auction is found
if (!$auctionDetails) {
    echo "No active auction found.";
    exit;
}

$auctionNumber = $auctionDetails['auction_number'];

// Fetch bidders for the selected auction
$biddersQuery = "SELECT b.id_number, b.firstname, b.lastname, ba.bid_number
                 FROM bidders b
                 INNER JOIN biddersauction ba ON b.id_number = ba.id_number 
                 WHERE ba.auction_number = ?";
$stmt = $conn->prepare($biddersQuery);
$stmt->bind_param("s", $auctionNumber);
$stmt->execute();
$biddersResult = $stmt->get_result();
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
    
    <div id="content-wrapper" class="d-flex flex-column">
      <?php include "Includes/topbar.php"; ?>
      <div id="content">
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
          <a href="downloadLotList.php" class="btn btn-info"><i class='fas fa-print'></i> Print Lots</a>
          <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="./">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Printing</li>
                        </ol>
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
    <th><i class="fas fa-cogs"></i> Action</th>
  </tr>
</thead>
                    <tbody>
                    <?php
while ($row = $biddersResult->fetch_assoc()) {
    $bidNumber = isset($row['bid_number']) ? $row['bid_number'] : null;
    $idNumber = isset($row['id_number']) ? $row['id_number'] : null;

    echo "<tr>
    <td>{$bidNumber}</td>
    <td>{$row['firstname']}</td>
    <td>{$row['lastname']}</td>
    <td><a href='generate_pdf.php?auctionNumber={$auctionNumber}&bidderId={$idNumber}' class='btn btn-primary'> <i class='fas fa-print'></i> Print #</a></td>

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
        <?php if ($auctionDetails['ended_auction'] == 0): ?>
          
        <?php endif; ?>
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
