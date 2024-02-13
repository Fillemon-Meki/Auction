<?php
// Include the BaconQrCode library
require 'path/to/BaconQrCode/vendor/autoload.php';

use BaconQrCode\Encoder\QrCode;
use BaconQrCode\Common\ErrorCorrectionLevel;

// Function to generate QR code URL using BaconQrCode library
function generateQRCode($data, $size = 300) {
    // Create a QR code instance
    $qrCode = QrCode::create($data)
        ->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH)
        ->setSize($size);

    // Generate QR code image
    $image = $qrCode->writeString();

    return $image;
}

// Assuming you get the auctionNumber from the GET request or session
$auctionNumber = $_GET['auctionNumber'] ?? '';

$host = "localhost";
$user = "root";
$pass = "";
$db = "auction_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo "Seems like you have not configured the database. Failed To Connect to database:" . $conn->connect_error;
}

// Fetch auction details based on the selected auction number
$sql = "SELECT qr_1, qr_2, qr_3 FROM auctiondetails WHERE auction_code = '$auctionNumber'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        // Generate QR codes for each QR link in the auction details
        foreach (['qr_1', 'qr_2', 'qr_3'] as $qrField) {
            $qrData = $row[$qrField];
            if ($qrData) {
                // Generate QR code using BaconQrCode library
                $qrCodeImage = generateQRCode($qrData);
                // Output QR code image with auction data
                echo "<img src='data:image/png;base64," . base64_encode($qrCodeImage) . "' alt='QR Code for $qrField'>";
            }
        }
    }
} else {
    echo "No auction found with the provided auction number.";
}

$conn->close();
?>
