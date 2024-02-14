<?php
require_once('tcpdf/tcpdf.php');

// Include database connection
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
$bidderNumber = $bidderNumberRow ? '#' . $bidderNumberRow['bid_number'] : '';

// Create new PDF instance
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Fillemon');
$pdf->SetTitle('Bidder Number PDF');
$pdf->SetSubject('Bidder Number');
$pdf->SetKeywords('Bidder, Number');

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', 'B', 250);

// Add content to the PDF
$pdf->Cell(0, 10, $bidderNumber, 0, 1);

// Close and output PDF
$pdf->Output('bidder_number_' . $bidderId . '.pdf', 'D');
?>
