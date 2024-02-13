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

// Check if the auction is open
$isAuctionOpen = ($auctionDetails['is_open'] == 1);

// Fetch registered bidders
$biddersQuery = "SELECT b.id_number, b.firstname, b.lastname, ba.bid_number, ba.deposit_status 
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
    <th><i class="fas fa-cogs"></i> Action</th>
  </tr>
</thead>
                    <tbody>
                    <?php
while ($row = $biddersResult->fetch_assoc()) {
    // Check if the array keys exist before accessing them
    $depositStatus = isset($row['deposit_status']) ? $row['deposit_status'] : null;
    $bidNumber = isset($row['bid_number']) ? $row['bid_number'] : null;
    $idNumber = isset($row['id_number']) ? $row['id_number'] : null;

    // Check if the auction is closed and set the disable attribute accordingly
    $disabled = ($isAuctionOpen) ? '' : 'disabled';

    $statusText = ($depositStatus == 1) ? 'Active' : 'Inactive';
    $inverseStatusText = ($depositStatus == 1) ? 'Inactive' : 'Active';

    echo "<tr>
    <td>{$bidNumber}</td>
    <td>{$row['firstname']}</td>
    <td>{$row['lastname']}</td>
    <td>
        <div class='status-dropdown' data-id='{$idNumber}'>
            <i class='fas fa-circle status-icon' style='color: ", ($depositStatus == 1) ? 'green' : 'red', "'></i>
            <span class='status-text'>{$statusText}</span>
            <div class='status-options'>
                <a href='#' class='status-option' data-status='active' style='display: ", ($depositStatus == 0) ? 'block' : 'none', "' {$disabled}>Active</a>
                <a href='#' class='status-option' data-status='inactive' style='display: ", ($depositStatus == 1) ? 'block' : 'none', "' {$disabled}>Inactive</a>
            </div>
        </div>
    </td>
    <td><a href='bidderDetails.php?auctionNumber={$auctionNumber}&bidderId={$idNumber}' class='btn btn-primary'><i class='fas fa-eye'></i> View</a></td>
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
        <?php if ($isAuctionOpen): ?>
          <div class="container-fluid">
            <button id="saveChanges" class="btn btn-success">Save Changes</button>
          </div>
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
      $(this).find('.status-options').hide();
      <?php if ($isAuctionOpen): ?>
        // Add hover functionality when the auction is open
        $('.status-dropdown').hover(function () {
          $(this).find('.status-options').show();
        }, function () {
          $(this).find('.status-options').hide();
        });
      <?php endif; ?>

      // Change status text on click
      $('.status-option').click(function (e) {
        e.preventDefault();
        var status = $(this).data('status');
        var id = $(this).closest('.status-dropdown').data('id');
        var statusText = (status === 'active') ? 'Active' : 'Inactive';

        $(this).closest('.status-dropdown').find('.status-text').text(statusText);
      });

      // Save changes button
      $('#saveChanges').click(function () {
        var updates = [];
        $('.status-dropdown').each(function () {
          var id = $(this).data('id');
          var statusText = $(this).find('.status-text').text();
          var status = (statusText === 'Active') ? 1 : 0;
          updates.push({ id: id, status: status });
        });

        // AJAX request to PHP script
        $.ajax({
          url: 'updateStatus.php', // Replace with the correct URL to your PHP script
          type: 'POST',
          data: { updates: updates },
          success: function(response) {
            // Handle response here
            alert('Statuses updated successfully');
          },
          error: function() {
            alert('Error updating statuses');
          }
        });
      });
    });
  </script>
</body>
</html>
