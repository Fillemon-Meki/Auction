<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../Includes/dbcon.php';

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

// Fetch transactions made by the bidder for open auctions
$transactionsQuery = "SELECT t.*, ad.auction_name
                      FROM transactions t
                      INNER JOIN auctiondetails ad ON t.auction_number = ad.auction_number
                      WHERE t.bid_number = ? AND ad.ended_auction = 0";
$stmt = $conn->prepare($transactionsQuery);
$stmt->bind_param("i", $bidderNumber);
$stmt->execute();
$transactionsResult = $stmt->get_result();

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $depositAmount = $_POST['depositAmount'];
    $description = $_POST['description'];
    $depositFor = $_POST['depositFor'];
    $paymentMethod = isset($_POST['paymentMethod']) ? $_POST['paymentMethod'] : '';
    $proofOfPayment = isset($_POST['proofOfPayment']) ? $_POST['proofOfPayment'] : '';

    // Get current date and time
    $createdAt = date('Y-m-d H:i:s');

    // Fetch bidder number based on bidder ID
    $bidderNumberQuery = "SELECT bid_number FROM biddersauction WHERE id_number = ?";
    $stmt = $conn->prepare($bidderNumberQuery);
    $stmt->bind_param("s", $bidderId);
    $stmt->execute();
    $bidderNumberResult = $stmt->get_result();
    $bidderNumberRow = $bidderNumberResult->fetch_assoc();
    $bidderNumber = $bidderNumberRow ? $bidderNumberRow['bid_number'] : '';

    // Check if bidder number is retrieved successfully
    if (!$bidderNumber) {
        echo "Error: Bidder number not found.";
        exit;
    }

    // Determine type based on depositFor value
    $type = ($depositFor == 'lots') ? 1 : 0;

    // Retrieve the auction number where ended_auction is 0
    $auctionQuery = "SELECT auction_number FROM auctiondetails WHERE ended_auction = 0 LIMIT 1";
    $auctionResult = $conn->query($auctionQuery);
    if ($auctionResult->num_rows > 0) {
        $auctionRow = $auctionResult->fetch_assoc();
        $auctionNumber = $auctionRow['auction_number'];

        // Prepare SQL statement to insert data into transactions table
        $insertQuery = "INSERT INTO transactions (bid_number, auction_number, amount, type, payment_method, proof_of_payment, description, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        // Prepare and execute the SQL statement
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("iidiisss", $bidderNumber, $auctionNumber, $depositAmount, $type, $paymentMethod, $proofOfPayment, $description, $createdAt);
        
        if ($stmt->execute()) {
            $successMessage = "Deposit submitted successfully.";
        } else {
            $errorMessage = "Error: " . $conn->error;
        }

        // Close statement and connection
        $stmt->close();
    } else {
        $errorMessage = "No active auction found.";
    }
}

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
  
    <div id="content-wrapper" class="d-flex flex-column">
    <?php include "Includes/topbar.php"; ?>
      <div id="content">
        <div class="container-fluid" id="container-wrapper">
          <h1 class="h3 mb-4 text-gray-800">Bidder Details</h1>
          
          <?php if(isset($successMessage)): ?>
          <div class="alert alert-success" role="alert">
            <?php echo $successMessage; ?>
          </div>
          <?php endif; ?>
          
          <?php if(isset($errorMessage)): ?>
          <div class="alert alert-danger" role="alert">
            <?php echo $errorMessage; ?>
          </div>
          <?php endif; ?>
          
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
    
    <!-- Add transaction type dropdown -->
    <div class="form-group">
        <label for="transactionType">Transaction Type:</label>
        <select class="form-control" id="transactionType" name="transactionType">
            <option value="deposit">Deposit</option>
            <option value="withdrawal">Withdrawal</option>
        </select>
    </div>
    
    <!-- Add deposit fields -->
    <form action="" method="post" id="depositForm">
        <div class="form-group">
            <label for="depositAmount">Enter Deposit Amount:</label>
            <input type="number" class="form-control" id="depositAmount" name="depositAmount" required>
        </div>
    
        <div class="form-group">
            <label for="description">Description:</label>
            <input type="text" class="form-control" id="description" name="description" required>
        </div>
    
        <div class="form-group">
            <label for="depositFor">Deposit For:</label>
            <select class="form-control" id="depositFor" name="depositFor" required>
                <option value="">Select Deposit For</option>
                <option value="lots">Lots</option>
                <option value="registration_fees">Registration Fees</option>
            </select>
        </div>
    
        <div id="lotsOptions" style="display: none;">
            <div class="form-group">
                <label for="paymentMethod">Payment Method:</label>
                <select class="form-control" id="paymentMethod" name="paymentMethod">
                    <option value="">Select Payment Method</option>
                    <option value="cash">Cash</option>
                    <option value="eft">EFT</option>
                </select>
            </div>
    
            <div id="proofOfPaymentField" style="display: none;">
                <div class="form-group">
                    <label for="proofOfPayment">Upload Proof of Payment:</label>
                    <input type="file" class="form-control-file" id="proofOfPayment" name="proofOfPayment">
                </div>
            </div>
        </div>
        <input type="hidden" name="bidderId" value="<?php echo $bidderId; ?>">
        <button type="submit" class="btn btn-primary">Submit Deposit</button>
    </form>
</div>

              </div>
            </div>
            <div class="col-lg-6">
              <div class="card mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-bar"></i> Bidder Transactions</h6>
                </div>
                <div class="card-body">
                  <table class="table table-bordered">
                    <thead>
                      <tr>
                        <th>Transaction ID</th>
                        <th>Auction Name</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Date</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      while ($row = $transactionsResult->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['auction_name']}</td>
                                <td>{$row['amount']}</td>
                                <td>{$row['type']}</td>
                                <td>{$row['description']}</td>
                                <td>{$row['created_at']}</td>
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
  <script>
    // Display deposit fields by default
    document.getElementById("depositForm").style.display = "block";

    document.getElementById("transactionType").addEventListener("change", function() {
        var depositForm = document.getElementById("depositForm");

        if (this.value === "deposit") {
            depositForm.style.display = "block";
        } else {
            depositForm.style.display = "none";
        }
    });

    document.getElementById("depositFor").addEventListener("change", function() {
        var lotsOptions = document.getElementById("lotsOptions");
        var paymentMethod = document.getElementById("paymentMethod");
        var proofOfPaymentField = document.getElementById("proofOfPaymentField");

        if (this.value === "lots") {
            lotsOptions.style.display = "block";
            paymentMethod.required = true;
        } else {
            lotsOptions.style.display = "none";
            paymentMethod.required = false;
            paymentMethod.value = "";
            proofOfPaymentField.style.display = "none";
        }
    });

    document.getElementById("paymentMethod").addEventListener("change", function() {
        var proofOfPaymentField = document.getElementById("proofOfPaymentField");

        if (this.value === "eft") {
            proofOfPaymentField.style.display = "block";
        } else {
            proofOfPaymentField.style.display = "none";
        }
    });
</script>
</body>
</html>
