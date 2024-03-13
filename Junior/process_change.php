<?php
// Assuming database connection and other configurations are included

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $depositAmount = $_POST['depositAmount'];
    $description = $_POST['description'];
    // Add other form fields as needed

    // Perform database operation to insert a new transaction with type 3
    $insertQuery = "INSERT INTO transactions (bid_number, amount, type, description, created_at) VALUES (?, ?, 3, ?, NOW())";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("ids", $bidderNumber, $depositAmount, $description);

    if ($stmt->execute()) {
        // Return success response
        echo json_encode(array("success" => true, "message" => "Transaction recorded successfully."));
        exit;
    } else {
        // Return error response
        echo json_encode(array("success" => false, "error" => "Failed to record transaction."));
        exit;
    }
} else {
    // Return error response if form is not submitted
    echo json_encode(array("success" => false, "error" => "Form submission error."));
    exit;
}
?>
