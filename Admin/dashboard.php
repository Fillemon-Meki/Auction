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

// Fetch total earnings
$totalEarningsQuery = "SELECT SUM(sold_for_amt) AS totalEarnings FROM lotlist WHERE auction_number = ? AND payment_status = 1";
$stmt = $conn->prepare($totalEarningsQuery);
$stmt->bind_param("s", $auctionNumber);
$stmt->execute();
$totalEarningsResult = $stmt->get_result();
$totalEarningsData = $totalEarningsResult->fetch_assoc();
$totalEarnings = $totalEarningsData['totalEarnings'] ?? 0; // Handle case when there are no earnings

// Fetch total number of bidders
$totalBiddersQuery = "SELECT COUNT(*) as totalBidders FROM biddersauction WHERE auction_number = ?";
$stmt = $conn->prepare($totalBiddersQuery);
$stmt->bind_param("s", $auctionNumber);
$stmt->execute();
$biddersResult = $stmt->get_result();
$biddersData = $biddersResult->fetch_assoc();

$biddersQuery = "SELECT COUNT(*) as activeBidders 
                 FROM bidders b
                 INNER JOIN biddersauction ba ON b.id_number = ba.id_number 
                 WHERE ba.auction_number = ? AND ba.deposit_status = 1";
$stmt = $conn->prepare($biddersQuery);
$stmt->bind_param("s", $auctionNumber);
$stmt->execute();
$biddersResult = $stmt->get_result();
$ActiveBiddersData = $biddersResult->fetch_assoc();

$inactiveBiddersQuery = "SELECT COUNT(*) as inactiveBidders 
                         FROM bidders b
                         INNER JOIN biddersauction ba ON b.id_number = ba.id_number 
                         WHERE ba.auction_number = ? AND ba.deposit_status <> 1";
$stmt = $conn->prepare($inactiveBiddersQuery);
$stmt->bind_param("s", $auctionNumber);
$stmt->execute();
$inactiveBiddersResult = $stmt->get_result();
$inactiveBiddersData = $inactiveBiddersResult->fetch_assoc();


// Add more queries here for additional statistics
// Example: Fetch total number of lots
$totalLotsQuery = "SELECT COUNT(*) as totalLots FROM lotlist WHERE auction_number = ?";
$stmt = $conn->prepare($totalLotsQuery);
$stmt->bind_param("s", $auctionNumber);
$stmt->execute();
$lotsResult = $stmt->get_result();
$lotsData = $lotsResult->fetch_assoc();

// Fetch count of lots that are not sold, including lots with sold_for_amt that is NULL
$unsoldLotsQuery = "SELECT COUNT(*) as unsoldLots FROM lotlist WHERE auction_number = ? AND (sold_for_amt = 0.00 OR sold_for_amt IS NULL)";
$stmt = $conn->prepare($unsoldLotsQuery);
$stmt->bind_param("s", $auctionNumber);
$stmt->execute();
$unsoldLotsResult = $stmt->get_result();
$unsoldLotsData = $unsoldLotsResult->fetch_assoc();


$soldLotsQuery = "SELECT COUNT(*) as soldLots FROM lotlist WHERE auction_number = ? AND sold_for_amt > 0.00";
$stmt = $conn->prepare($soldLotsQuery);
$stmt->bind_param("s", $auctionNumber);
$stmt->execute();
$soldLotsResult = $stmt->get_result();
$soldLotsData = $soldLotsResult->fetch_assoc();

// Fetch count of active bidders who bought something
$activeBiddersWithPurchasesQuery = "SELECT COUNT(DISTINCT b.id_number) as activeBiddersWithPurchases 
                                    FROM bidders b
                                    INNER JOIN biddersauction ba ON b.id_number = ba.id_number 
                                    INNER JOIN lotlist l ON b.id_number = l.id_number AND ba.auction_number = l.auction_number
                                    WHERE ba.auction_number = ? AND ba.deposit_status = 1 AND l.sold_for_amt > 0.00";
$stmt = $conn->prepare($activeBiddersWithPurchasesQuery);
$stmt->bind_param("s", $auctionNumber);
$stmt->execute();
$activeBiddersWithPurchasesResult = $stmt->get_result();
$activeBiddersWithPurchasesData = $activeBiddersWithPurchasesResult->fetch_assoc();

// Fetch count of active bidders who did not buy anything
$activeBiddersWithoutPurchasesQuery = "SELECT COUNT(DISTINCT b.id_number) as activeBiddersWithoutPurchases 
                                       FROM bidders b
                                       INNER JOIN biddersauction ba ON b.id_number = ba.id_number 
                                       LEFT JOIN lotlist l ON b.id_number = l.id_number AND ba.auction_number = l.auction_number
                                       WHERE ba.auction_number = ? AND ba.deposit_status = 1 AND (l.sold_for_amt IS NULL OR l.sold_for_amt = 0.00)";
$stmt = $conn->prepare($activeBiddersWithoutPurchasesQuery);
$stmt->bind_param("s", $auctionNumber);
$stmt->execute();
$activeBiddersWithoutPurchasesResult = $stmt->get_result();
$activeBiddersWithoutPurchasesData = $activeBiddersWithoutPurchasesResult->fetch_assoc();

// Fetch count of sold lots that are paid for
$soldPaidLotsQuery = "SELECT COUNT(*) as soldPaidLots FROM lotlist WHERE auction_number = ? AND sold_for_amt > 0.00 AND payment_status = 1";
$stmt = $conn->prepare($soldPaidLotsQuery);
$stmt->bind_param("s", $auctionNumber);
$stmt->execute();
$soldPaidLotsResult = $stmt->get_result();
$soldPaidLotsData = $soldPaidLotsResult->fetch_assoc();

// Fetch count of sold lots that are not paid for
$soldNotPaidLotsQuery = "SELECT COUNT(*) as soldNotPaidLots FROM lotlist WHERE auction_number = ? AND sold_for_amt > 0.00 AND payment_status = 0";
$stmt = $conn->prepare($soldNotPaidLotsQuery);
$stmt->bind_param("s", $auctionNumber);
$stmt->execute();
$soldNotPaidLotsResult = $stmt->get_result();
$soldNotPaidLotsData = $soldNotPaidLotsResult->fetch_assoc();

// Calculate total sold lots
$totalSoldLots = $soldLotsData['soldLots'] + $soldPaidLotsData['soldPaidLots'] + $soldNotPaidLotsData['soldNotPaidLots'];

?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Auction Dashboard -
    <?php echo htmlspecialchars($auctionDetails['auction_name']); ?>
  </title>
  <link href="../vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
  <style>
    /* Add your custom CSS styles here */
    .stat-card {
      border: 1px solid #ddd;
      border-radius: 5px;
      padding: 15px;
      margin-bottom: 15px;
      background-color: #fff;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
      /* Add animation properties */
      animation: fadeInUp 0.5s ease forwards;
      opacity: 0;
      transform: translateY(20px);
      /* Add transition for hover effect */
      transition: transform 0.3s ease;
    }

    .stat-card:hover {
      transform: translateY(15px);
    }

    .stat-label {
      font-size: 20px;
      color: #ffffff;
    }

    .stat-value {
      font-size: 24px;
      font-weight: bold;
      color: #ffffff;
    }

    /* Define the animation keyframes */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>



</head>

<body>
  <div id="wrapper">
    <?php include "Includes/sidebar.php"; ?>

    <div id="content-wrapper">
      <?php include "Includes/topbar.php"; ?>

      <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">
          <?php echo htmlspecialchars($auctionDetails['auction_name']); ?>
        </h1>
        <h1 class="h3 mb-4 text-gray-800">Total Earnings - N$
          <?php echo number_format($totalEarnings, 2); ?>
        </h1>

        <!-- Dashboard Content Here -->
        <div class="row">

          <!-- Total Bidders -->
          <div class="col-xl-2 col-md-4 mb-4">
            <div class="stat-card bg-primary text-white">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                  <div class="stat-label"><i class="fas fa-users"></i> Total Bidders</div>
                  <div class="stat-value">
                    <?php echo $biddersData['totalBidders']; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Active Bidders -->
          <div class="col-xl-2 col-md-4 mb-4">
            <div class="stat-card bg-success text-white">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                  <div class="stat-label"><i class="fas fa-user-check"></i> Active Bidders</div>
                  <div class="stat-value">
                    <?php echo $ActiveBiddersData['activeBidders']; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Inactive Bidders -->
          <div class="col-xl-2 col-md-4 mb-4">
            <div class="stat-card bg-danger text-white">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                  <div class="stat-label"><i class="fas fa-user-times"></i> Inactive Bidders</div>
                  <div class="stat-value">
                    <?php echo $inactiveBiddersData['inactiveBidders']; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Total Lots -->
          <div class="col-xl-2 col-md-4 mb-4">
            <div class="stat-card bg-info text-white">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                  <div class="stat-label"><i class="fas fa-boxes"></i> Total Lots</div>
                  <div class="stat-value">
                    <?php echo $lotsData['totalLots']; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Sold Lots -->
          <div class="col-xl-2 col-md-4 mb-4">
            <div class="stat-card bg-warning text-white">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                  <div class="stat-label"><i class="fas fa-gavel"></i> Sold Lots</div>
                  <div class="stat-value">
                    <?php echo $soldLotsData['soldLots']; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Unsold Lots -->
          <div class="col-xl-2 col-md-4 mb-4">
            <div class="stat-card bg-secondary text-white">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                  <div class="stat-label"><i class="fas fa-times-circle"></i> Unsold Lots</div>
                  <div class="stat-value">
                    <?php echo $unsoldLotsData['unsoldLots']; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>


          <!-- Pie chart for bidders -->
          <div class="col-md-6 mb-4">
            <div class="card">
              <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Bidders Distribution</h6>
              </div>
              <div class="card-body">
                <canvas id="biddersChart" width="300" height="300"></canvas>
              </div>
            </div>
          </div>

          <!-- Pie chart for sold and unsold lots -->
          <div class="col-md-6 mb-4">
            <div class="card">
              <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Lots Distribution</h6>
              </div>
              <div class="card-body">
                <canvas id="lotsChart" width="300" height="300"></canvas>
              </div>
            </div>
          </div>



          <!-- Additional content can be added here -->

        </div>
        <?php include "Includes/footer.php"; ?>
      </div>
    </div>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
      // Get data from PHP variables
      // Get data from PHP variables
      var activeBiddersWithPurchases = <?php echo $activeBiddersWithPurchasesData['activeBiddersWithPurchases']; ?>;
      var activeBiddersWithoutPurchases = <?php echo $activeBiddersWithoutPurchasesData['activeBiddersWithoutPurchases']; ?>;
      var inactiveBidders = <?php echo $inactiveBiddersData['inactiveBidders']; ?>;

      // Render the pie chart
      var ctx = document.getElementById('biddersChart').getContext('2d');
      var myChart = new Chart(ctx, {
        type: 'pie',
        data: {
          labels: ['Active with Purchases', 'Active without Purchases', 'Inactive Bidders'],
          datasets: [{
            label: 'Number of Bidders',
            data: [activeBiddersWithPurchases, activeBiddersWithoutPurchases, inactiveBidders],
            backgroundColor: [
              'rgba(255, 99, 132, 0.2)',
              'rgba(54, 162, 235, 0.2)',
              'rgba(255, 206, 86, 0.2)',
            ],
            borderColor: [
              'rgba(255, 99, 132, 1)',
              'rgba(54, 162, 235, 1)',
              'rgba(255, 206, 86, 1)',
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: false,
          legend: {
            position: 'right'
          },
          plugins: {
            tooltip: {
              callbacks: {
                label: function (context) {
                  var label = context.label || '';
                  var value = context.parsed || 0;
                  var percent = value / (activeBiddersWithPurchases + activeBiddersWithoutPurchases + inactiveBidders) * 100;
                  return label + ': ' + percent.toFixed(2) + '%';
                }
              }
            }
          }
        }
      });

    </script>
    <script>
      // Get data from PHP variables
      // Get data from PHP variables
      var soldLots = <?php echo $soldLotsData['soldLots']; ?>;
      var soldPaidLots = <?php echo $soldPaidLotsData['soldPaidLots']; ?>;
      var soldNotPaidLots = <?php echo $soldNotPaidLotsData['soldNotPaidLots']; ?>;
      var unsoldLots = <?php echo $unsoldLotsData['unsoldLots']; ?>;

      // Render the pie chart for sold and unsold lots
      // Render the pie chart for sold and unsold lots
      // Render the pie chart for sold and unsold lots
      var ctxLots = document.getElementById('lotsChart').getContext('2d');
      var lotsChart = new Chart(ctxLots, {
        type: 'pie',
        data: {
          labels: ['Sold and Paid For', 'Sold but Not Paid For', 'Unsold Lots'],
          datasets: [{
            label: 'Number of Lots',
            data: [soldPaidLots, soldNotPaidLots, unsoldLots],
            backgroundColor: [
              'rgba(75, 192, 192, 0.2)', // Sold and Paid For
              'rgba(255, 159, 64, 0.2)',   // Sold but Not Paid For
              'rgba(255, 99, 132, 0.2)'   // Unsold Lots
            ],
            borderColor: [
              'rgba(75, 192, 192, 1)',
              'rgba(255, 159, 64, 1)',
              'rgba(255, 99, 132, 1)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: false,
          legend: {
            position: 'right'
          },
          plugins: {
            tooltip: {
              callbacks: {
                label: function (context) {
                  var label = context.label || '';
                  var value = context.parsed || 0;
                  var percent = value / (soldPaidLots + soldNotPaidLots + unsoldLots) * 100;
                  return label + ': ' + percent.toFixed(2) + '%';
                }
              }
            }
          }
        }
      });


    </script>



</body>

</html>