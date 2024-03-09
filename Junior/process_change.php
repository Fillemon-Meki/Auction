<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../Includes/dbcon.php';

// Retrieve POST data
$bidderId = $_POST['bidderId'] ?? '';
$auctionNumber = $_POST['auctionNumber'] ?? '';
$amount = $_POST['amount'] ?? '';
$createdAt = $_POST['created_at'] ?? '';

// Check if all required data is provided
if (!$bidderId || !$auctionNumber || !$amount || !$createdAt) {
    echo json_encode(array("success" => false, "error" => "Incomplete data provided."));
    exit;
}

// Insert change issuance transaction into transactions table
$insertQuery = "INSERT INTO transactions (bid_number, auction_number, amount, type, payment_method, proof_of_payment, description, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insertQuery);
$zero = 0;
$paymentMethod = 0; // Assuming payment method for change issuance is 0
$proofOfPayment = null; // Assuming no proof of payment is required for change issuance
$description = 'Change issued'; // Description for change issuance
$type = 2; // Transaction type for change issuance
$stmt->bind_param("iiidisss", $bidderId, $auctionNumber, $amount, $type, $paymentMethod, $proofOfPayment, $description, $createdAt);
if ($stmt->execute()) {
    echo json_encode(array("success" => true, "message" => "Change issued successfully."));
} else {
    echo json_encode(array("success" => false, "error" => "Failed to issue change."));
}

$stmt->close();
$conn->close();
?>
