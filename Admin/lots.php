<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Get auction and bidder IDs from the URL
$auctionNumber = $_GET['auctionNumber'] ?? '';
$bidderId = $_GET['bidderId'] ?? '';

if (!$auctionNumber || !$bidderId) {
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

$isAuctionOpen = ($auctionDetails['is_open'] == 1);

// Fetch bidder's name
$bidderQuery = "SELECT firstname, lastname FROM bidders WHERE id_number = ?";
$stmt = $conn->prepare($bidderQuery);
$stmt->bind_param("s", $bidderId);
$stmt->execute();
$bidderResult = $stmt->get_result();
$bidderDetails = $bidderResult->fetch_assoc();

if (!$bidderDetails) {
    echo "Bidder not found.";
    exit;
}

// Display bidder's name
$bidderName = $bidderDetails['firstname'] . ' ' . $bidderDetails['lastname'];

// Fetch lots for the selected bidder in the selected auction
$lotsQuery = "SELECT ll.id, ll.lot_number, ll.title_desc, ll.payment_status, ll.sold_for_amt, ll.start_bid_amt 
              FROM lotlist ll
              WHERE ll.auction_number = ? AND ll.id_number = ?";
$stmt = $conn->prepare($lotsQuery);
$stmt->bind_param("ss", $auctionNumber, $bidderId);
$stmt->execute();
$lotsResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Lots for Bidder - <?php echo htmlspecialchars($auctionDetails['auction_name']); ?></title>
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
            <h1 class="h3 mb-0 text-gray-800">Lots for: <?php echo htmlspecialchars($bidderName); ?></h1>
            <?php if ($isAuctionOpen): ?>
              <button id="saveChanges" class="btn btn-success">Save Changes</button>
            <?php endif; ?>
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
      <td><?php echo htmlspecialchars($row['sold_for_amt']); ?></td>
      <td>
  <?php if ($isAuctionOpen): ?>
    <div class="status-dropdown" data-id="<?php echo $row['id']; ?>">
      <?php if ($row['payment_status']): ?>
        <span class="status-text text-success"><?php echo $row['payment_status'] ? 'Paid' : 'Unpaid'; ?></span>
      <?php else: ?>
        <span class="status-text text-danger"><?php echo $row['payment_status'] ? 'Paid' : 'Unpaid'; ?></span>
      <?php endif; ?>
      <div class="status-options">
        <a href="#" class="status-option" data-status="paid" style="display: <?php echo $row['payment_status'] ? 'none' : 'block'; ?>"></i> Paid</a>
        <a href="#" class="status-option" data-status="unpaid" style="display: <?php echo $row['payment_status'] ? 'block' : 'none'; ?>"></i> Unpaid</a>
      </div>
    </div>
  <?php else: ?>
    <span class="status-text <?php echo $row['payment_status'] ? 'text-success' : 'text-danger'; ?>"><?php echo $row['payment_status'] ? 'Paid' : 'Unpaid'; ?></span>
  <?php endif; ?>
</td>
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
      $(this).find('.status-options').hide();
      $('#dataTableHover').DataTable();
      
      // Show/hide status options on hover
      $('.status-dropdown').hover(function () {
        $(this).find('.status-options').show();
      }, function () {
        $(this).find('.status-options').hide();
      });

      // Change status text on click
      $('.status-option').click(function (e) {
        e.preventDefault();
        var status = $(this).data('status');
        var id = $(this).closest('.status-dropdown').data('id');
        var statusText = (status === 'paid') ? 'Paid' : 'Unpaid';
        $(this).closest('.status-dropdown').find('.status-text').text(statusText);
      });

      // Save changes button click handler
      $('#saveChanges').click(function () {
        var updates = [];
        $('.status-dropdown').each(function () {
          var lotId = $(this).data('id');
          var statusText = $(this).find('.status-text').text();
          var paymentStatus = (statusText === 'Paid') ? 1 : 0;
          updates.push({ lotId: lotId, paymentStatus: paymentStatus });
        });

        // AJAX request to save all changes
        $.ajax({
          url: 'updatePaymentStatus.php', // Replace with the correct URL to your PHP script
          type: 'POST',
          data: { updates: updates },
          success: function(response) {
            // Handle response here
            alert('Changes saved successfully');
          },
          error: function() {
            alert('Error saving changes');
          }
        });
      });
    });
  </script>
</body>
</html>
