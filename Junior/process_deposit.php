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

// Fetch bidder number from biddersauction table
$bidderNumberQuery = "SELECT bid_number FROM biddersauction WHERE id_number = ?";
$stmt = $conn->prepare($bidderNumberQuery);
$stmt->bind_param("s", $bidderId);
$stmt->execute();
$bidderNumberResult = $stmt->get_result();
$bidderNumberRow = $bidderNumberResult->fetch_assoc();
$bidderNumber = $bidderNumberRow ? $bidderNumberRow['bid_number'] : '';

// Retrieve form data
$depositAmount = $_POST['depositAmount'] ?? '';
$description = $_POST['description'] ?? '';
$depositFor = $_POST['depositFor'] ?? '';
$paymentMethod = $_POST['paymentMethod'] ?? '';

// Check if all required fields are filled
if (!$depositAmount || !$description || !$depositFor ) {
    $response = array('success' => false, 'error' => 'All fields are required.');
    echo json_encode($response);
    exit;
}

// Initialize variables for file upload
$fileName = '';
$fileTmpName = '';
$fileDestination = '';

// File upload handling only if proof of payment is provided
if ($depositFor === 'lots' && isset($_FILES['proofOfPayment']) && $_FILES['proofOfPayment']['error'] === UPLOAD_ERR_OK) {
    $fileName = $_FILES['proofOfPayment']['name'];
    $fileTmpName = $_FILES['proofOfPayment']['tmp_name'];
    $fileDestination = 'proofs' . $fileName;
    move_uploaded_file($fileTmpName, $fileDestination); // Move file to your uploads directory
}

// Get current date and time
$createdAt = date('Y-m-d H:i:s');

// Determine type based on depositFor value
$type = ($depositFor == 'lots') ? 1 : 0;

// Determine payment method value
$paymentMethodValue = $paymentMethod === 'cash' ? 0 : ($paymentMethod === 'eft' ? 1 : '');

// Retrieve the auction number where ended_auction is 0
$auctionQuery = "SELECT auction_number FROM auctiondetails WHERE ended_auction = 0 LIMIT 1";
$auctionResult = $conn->query($auctionQuery);
if ($auctionResult->num_rows > 0) {
    $auctionRow = $auctionResult->fetch_assoc();
    $auctionNumber = $auctionRow['auction_number'];

    // Prepare SQL statement to insert data into transactions table
    $insertQuery = "INSERT INTO transactions (bid_number, auction_number, amount, type, payment_method, proof_of_payment, description, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("iidiisss", $bidderNumber, $auctionNumber, $depositAmount, $type, $paymentMethodValue, $fileName, $description, $createdAt);
    
    if ($stmt->execute()) {
        $response = array('success' => true, 'message' => 'Deposit submitted successfully.');
        echo json_encode($response);
    } else {
        $response = array('success' => false, 'error' => 'Error: ' . $conn->error);
        echo json_encode($response);
    }

    // Close statement and connection
    $stmt->close();
} else {
    $response = array('success' => false, 'error' => 'No active auction found.');
    echo json_encode($response);
}

?>
