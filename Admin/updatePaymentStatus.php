<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Check if updates data is received
if (isset($_POST['updates'])) {
    $updates = $_POST['updates'];
    foreach ($updates as $update) {
        $lotId = $update['lotId'];
        $paymentStatus = $update['paymentStatus'];

        // Update payment status for each lot
        $updateQuery = "UPDATE lotlist SET payment_status = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ss", $paymentStatus, $lotId);
        $stmt->execute();
    }
    echo "Payment status updated successfully";
} else {
    echo "No updates received";
}
?>