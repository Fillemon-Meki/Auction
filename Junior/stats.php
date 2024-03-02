<?php
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Fetch data for the earnings chart
$queryEarnings = "SELECT ad.auction_number, SUM(ll.sold_for_amt) AS total_earnings
                  FROM auctiondetails ad
                  LEFT JOIN lotlist ll ON ad.auction_number = ll.auction_number
                  WHERE ll.payment_status = 1
                  GROUP BY ad.auction_number";
$resultEarnings = $conn->query($queryEarnings);

$labelsEarnings = array();
$earningsData = array();

while ($row = $resultEarnings->fetch_assoc()) {
    $labelsEarnings[] = $row['auction_number'];
    $earningsData[] = $row['total_earnings'];
}

// Fetch data for the bidders chart
$queryBidders = "SELECT auction_number, COUNT(*) AS num_bidders FROM biddersauction GROUP BY auction_number";
$resultBidders = $conn->query($queryBidders);

$labelsBidders = array();
$biddersData = array();

while ($row = $resultBidders->fetch_assoc()) {
    $labelsBidders[] = $row['auction_number'];
    $biddersData[] = $row['num_bidders'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Auction Selection</title>
  <link href="../vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
</head>
<body id="page-top">
  <div id="wrapper">
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <?php include "Includes/topbar.php";?>
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Home</h1>
            <a href="createAuction.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-plus fa-sm text-white-50"></i> Add New Auction</a>
          </div>

          <!-- Line Chart for Total Earnings -->
          <div class="row">
            <div class="col-lg-6">
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Total Earnings Comparison</h6>
                </div>
                <div class="card-body">
                  <canvas id="earningsChart"></canvas>
                </div>
              </div>
            </div>
            
            <!-- Line Chart for Number of Bidders -->
            <div class="col-lg-6">
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Number of Bidders Comparison</h6>
                </div>
                <div class="card-body">
                  <canvas id="biddersChart"></canvas>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Upcoming Auctions</h6>
                </div>
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                    <thead class="thead-light">
                      <tr>
                        <th><i class="fas fa-hashtag"></i> Auction Number</th>
                        <th><i class="fas fa-file-signature"></i> Auction Name</th>
                        <th><i class="fas fa-calendar-day"></i> Date of Auction</th>
                        <th><i class="far fa-clock"></i> Start Time</th>
                        <th><i class="far fa-clock"></i> End Time</th>
                        <th><i class="fas fa-map-marker-alt"></i> Region</th>
                        <th><i class="fas fa-city"></i> Town</th>
                        <th><i class="fas fa-info-circle"></i> Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $query = "SELECT * FROM auctiondetails";
                      $result = $conn->query($query);
                      while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['auction_number']}</td>
                                <td>{$row['auction_name']}</td>
                                <td>{$row['date_of_auction']}</td>
                                <td>{$row['start_date_time']}</td>
                                <td>{$row['end_date_time']}</td>
                                <td>{$row['region']}</td>
                                <td>{$row['town']}</td>
                                <td><a href='dashboard.php?auctionNumber={$row['auction_number']}' class='btn btn-primary'>View Details</a></td>
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
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
  <script>
  $(document).ready(function () {
    $('#dataTableHover').DataTable();
  });

  // Render the earnings chart as a bar chart
  var earningsCtx = document.getElementById('earningsChart').getContext('2d');
  var earningsChart = new Chart(earningsCtx, {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($labelsEarnings); ?>,
      datasets: [{
        label: 'Total Earnings',
        data: <?php echo json_encode($earningsData); ?>,
        backgroundColor: 'rgba(54, 162, 235, 0.2)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 1
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });

  // Render the bidders chart as a bar chart
  var biddersCtx = document.getElementById('biddersChart').getContext('2d');
  var biddersChart = new Chart(biddersCtx, {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($labelsBidders); ?>,
      datasets: [{
        label: 'Number of Bidders',
        data: <?php echo json_encode($biddersData); ?>,
        backgroundColor: 'rgba(255, 99, 132, 0.2)',
        borderColor: 'rgba(255, 99, 132, 1)',
        borderWidth: 1
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
</script>

</body>
</html>
