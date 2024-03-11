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
// Fetch deposit amount for the active auction
$activeAuctionDepositQuery = "SELECT deposit_amount FROM auctiondetails WHERE ended_auction = 0 LIMIT 1";
$activeAuctionDepositResult = $conn->query($activeAuctionDepositQuery);
$activeAuctionDepositRow = $activeAuctionDepositResult->fetch_assoc();
$activeAuctionDeposit = $activeAuctionDepositRow ? $activeAuctionDepositRow['deposit_amount'] : 0;

// Calculate total registration fees paid by the bidder
$totalRegistrationFeesQuery = "SELECT SUM(amount) AS total_registration_fees FROM transactions WHERE bid_number = ? AND type = 0";
$stmt = $conn->prepare($totalRegistrationFeesQuery);
$stmt->bind_param("i", $bidderNumber);
$stmt->execute();
$totalRegistrationFeesResult = $stmt->get_result();
$totalRegistrationFeesRow = $totalRegistrationFeesResult->fetch_assoc();
$totalRegistrationFees = $totalRegistrationFeesRow ? $totalRegistrationFeesRow['total_registration_fees'] : 0;

// Calculate registration change
$registrationChange = $totalRegistrationFees - $activeAuctionDeposit;

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
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
          <h1 class="h3 mb-4 text-gray-800">Bidder Details</h1>
          <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="./">Home</a></li>
                            <li class="breadcrumb-item"><a href="cashier.php">Bidders</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Bidder Details</li>
                        </ol>
        </div>
          <!-- Success and Error Messages -->
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
          
          <!-- Bidder Information Card -->
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
                  <p><strong>Registration Change:</strong> <?php echo $registrationChange; ?> <button type="button" class="btn btn-primary" id="issueChangeBtn">Issue Change</button></p>
                  
                  <!-- Transaction Type Dropdown -->
                  <div class="form-group">
                      <label for="transactionType">Transaction Type:</label>
                      <select class="form-control" id="transactionType" name="transactionType">
                          <option value="deposit">Deposit</option>
                          <option value="withdrawal">Withdrawal</option>
                      </select>
                  </div>
                  
                  <!-- Deposit Form -->
                  <form action="" method="post" id="depositForm" enctype="multipart/form-data">
                      <!-- Deposit Amount -->
                      <div class="form-group">
                          <label for="depositAmount">Enter Deposit Amount:</label>
                          <input type="number" class="form-control" id="depositAmount" name="depositAmount" required>
                      </div>
                  
                      <!-- Description -->
                      <div class="form-group">
                          <label for="description">Description:</label>
                          <input type="text" class="form-control" id="description" name="description" required>
                      </div>
                  
                      <!-- Deposit For (Lots/Registration Fees) -->
                      <div class="form-group">
                          <label for="depositFor">Deposit For:</label>
                          <select class="form-control" id="depositFor" name="depositFor" required>
                              <option value="">Select Deposit For</option>
                              <option value="lots">Lots</option>
                              <option value="registration_fees">Registration Fees</option>
                          </select>
                      </div>
                  
                      <!-- Payment Method -->
                      <div id="lotsOptions" style="display: none;">
                          <div class="form-group">
                              <label for="paymentMethod">Payment Method:</label>
                              <select class="form-control" id="paymentMethod" name="paymentMethod"> <!-- Ensure name="paymentMethod" is set -->
                                  <option value="">Select Payment Method</option>
                                  <option value="cash">Cash</option>
                                  <option value="eft">EFT</option>
                              </select>
                          </div>
                  
                          <!-- Proof of Payment Upload Field -->
                          <div id="proofOfPaymentField" style="display: none;">
                              <div class="form-group">
                                  <label for="proofOfPayment">Upload Proof of Payment:</label>
                                  <input type="file" class="form-control-file" id="proofOfPayment" name="proofOfPayment">
                              </div>
                          </div>
                      </div>
                      <input type="hidden" name="bidderId" value="<?php echo $bidderId; ?>">
                      <button type="button" class="btn btn-primary" onclick="submitForm()">Submit Deposit</button>
                  </form>
                </div>
              </div>
            </div>
            <!-- Bidder Transactions Card -->
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

          <
          
        </div>
      </div>
      <?php include "Includes/footer.php";?>
    </div>
  </div>
  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <script>
    document.getElementById("issueChangeBtn").addEventListener("click", function() {
      console.log("Button clicked"); // Check if the button click event is triggered
      if (<?php echo $registrationChange; ?> > 0) {
        console.log("Registration change is above 0"); // Check if the registration change is above 0
        // Make AJAX request to process_change.php
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "process_change.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
          if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
              var response = JSON.parse(xhr.responseText);
              console.log(response); // Log the response from the server
              if (response.success) {
                alert(response.message);
                // Optionally, you can reload the page or update UI as needed
                location.reload();
              } else {
                alert(response.error);
              }
            } else {
              alert("An error occurred. Please try again. Status: " + xhr.status);
            }
          }
        };
        // Construct the data to be sent in the request
        var data = "bidderId=<?php echo $bidderId; ?>&auctionNumber=<?php echo $activeAuctionNumber; ?>&amount=<?php echo $registrationChange; ?>&created_at=<?php echo date('Y-m-d H:i:s'); ?>";
        // Send the request with the data
        xhr.send(data);
        console.log("AJAX request sent"); // Check if the AJAX request is sent
      } else {
        alert("Registration change is not above 0. No change issued.");
      }
    });
  </script>

  <script>
    // Display deposit fields by default
    document.getElementById("depositForm").style.display = "block";



    // Function to handle form submission
    function submitForm() {
        // Retrieve form data
        var depositAmount = document.getElementById('depositAmount').value;
        var description = document.getElementById('description').value;
        var depositFor = document.getElementById('depositFor').value;
        var paymentMethod = document.getElementById('paymentMethod').value;
        var proofOfPayment = document.getElementById('proofOfPayment').files[0]; // Retrieve uploaded file

        // Create form data object
        var formData = new FormData();
        formData.append('depositAmount', depositAmount);
        formData.append('description', description);
        formData.append('depositFor', depositFor);
        formData.append('paymentMethod', paymentMethod);
        formData.append('proofOfPayment', proofOfPayment);

        // Send form data to server using fetch API
        fetch('process_deposit.php?bidderId=<?php echo $bidderId; ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Display success or error message
            if (data.success) {
                alert(data.message); // You can replace this with a more user-friendly notification
                location.reload(); // Refresh the page after successful submission
            } else {
                alert(data.error); // Display error message to the user
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.'); // Display generic error message
        });
    }

  </script>
  
</body>
</html>
