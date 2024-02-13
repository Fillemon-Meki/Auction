<?php
// Start output buffering
ob_start();

// Include the TCPDF library
require_once('tcpdf/tcpdf.php');

// Include the database connection
include '../Includes/dbcon.php';

// Function to fetch auction details (including name and date)


// Function to fetch lot list for a specific auction from the database
function getLotList($auctionNumber) {
    global $conn;
    $query = "SELECT lot_number, title_desc, start_bid_amt, sold_for_amt FROM lotlist WHERE auction_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $auctionNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $lotList = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $lotList;
}

// Function to fetch auction details (including name and date)
function getAuctionDetails($auctionNumber) {
    global $conn;
    $query = "SELECT auction_name, date_of_auction,town FROM auctiondetails WHERE auction_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $auctionNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $details = $result->fetch_assoc();
    $stmt->close();
    return $details;
}

// Function to generate PDF
function generatePDF($auctionNumber) {
    // Fetch auction details
    $auctionDetails = getAuctionDetails($auctionNumber);

    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Fillemon');
    $pdf->SetTitle('Lot List for Auction ' . $auctionNumber);
    $pdf->SetSubject('Lot List');
    $pdf->SetKeywords('Auction, Lot List');

    // Set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // Set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // Add a page
    $pdf->AddPage();

    // Set auction name, date, and town on top of the page
   // Set auction name, date, and town on top of the page
$pdf->SetFont('helvetica', 'B', 20); // Change font size to 20
$pdf->Write(0, "Lot list for " . $auctionDetails['auction_name'] ."\n". "\n", '', 0, 'L', true, 0, false, false, 0);

// Reset font size for the date and town to a smaller size if needed
$pdf->SetFont('helvetica', 'B', 15); // Reset to smaller font for the rest of the text if desired
$pdf->Write(0, "Auction Date: " . $auctionDetails['date_of_auction'] . "                                            Town: " . $auctionDetails['town'], '', 0, 'L', true, 0, false, false, 0);


    // Fetch lot list from the database
    $lotList = getLotList($auctionNumber);

    // Table header
// Table header with explicit column widths
$pdf->SetFont('helvetica', 'B', 12);
$html = '<table border="1" cellpadding="5">';
$html .= '<tr>';
$html .= '<th style="width: 100px;">Lot Number</th>'; // Smaller width for Lot Number
$html .= '<th style="width: 200px;">Description</th>';
$html .= '<th>Starting Amount</th>';
$html .= '<th>Sold For Amount</th>';
$html .= '</tr>';

// Table body
// Table body
foreach ($lotList as $lot) {
    $html .= '<tr>';
    $html .= '<td>' . $lot['lot_number'] . '</td>';
    $html .= '<td>' . $lot['title_desc'] . '</td>';

    // Check if starting price is not 0.00, null, or empty
    if ($lot['start_bid_amt'] !== null && $lot['start_bid_amt'] !== '' && $lot['start_bid_amt'] != '0.00') {
        $html .= '<td>' . $lot['start_bid_amt'] . '</td>';
    } else {
        $html .= '<td></td>'; // If starting price is 0.00, null, or empty, display nothing
    }

    $html .= '<td></td>'; // Keep Sold For Amount column empty as per previous modification
    $html .= '</tr>';
}


$html .= '</table>';


    // Write the HTML content to the PDF
    $pdf->writeHTML($html, true, false, true, false, '');

    // Close and output PDF
    $pdf->Output('lot_list_' . $auctionNumber . '.pdf', 'D');
}


// Assuming you get the auctionNumber from the GET request or session
$auctionNumber = $_GET['auctionNumber'] ?? '';

// Generate and download the PDF
generatePDF($auctionNumber);

// Flush output buffer
ob_end_flush();
?>
