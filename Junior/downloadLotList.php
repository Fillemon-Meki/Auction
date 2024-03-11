<?php
// Start output buffering
ob_start();

// Include the TCPDF library
require_once('tcpdf/tcpdf.php');

// Include the database connection
include '../Includes/dbcon.php';

// Function to fetch auction details (including name and date)
function getAuctionDetails() {
    global $conn;
    $query = "SELECT auction_number FROM auctiondetails WHERE ended_auction = 0";
    $result = $conn->query($query);
    $details = $result->fetch_assoc();
    return $details['auction_number'];
}

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

// Function to generate PDF
function generatePDF($auctionNumber) {
    // Fetch auction details
    $auctionDetails = getAuctionDetails();

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
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->Write(0, "Lot list for Auction " . $auctionNumber . "\n\n", '', 0, 'L', true, 0, false, false, 0);

    // Fetch lot list from the database
    $lotList = getLotList($auctionNumber);

    // Table header
    $pdf->SetFont('helvetica', 'B', 12);
    $html = '<table border="1" cellpadding="5">';
    $html .= '<tr>';
    $html .= '<th style="width: 100px;">Lot Number</th>';
    $html .= '<th style="width: 200px;">Description</th>';
    $html .= '<th>Starting Amount</th>';
    $html .= '<th>Sold For Amount</th>';
    $html .= '</tr>';

    // Table body
    foreach ($lotList as $lot) {
        $html .= '<tr>';
        $html .= '<td>' . $lot['lot_number'] . '</td>';
        $html .= '<td>' . $lot['title_desc'] . '</td>';
        $html .= '<td>' . $lot['start_bid_amt'] . '</td>';
        $html .= '<td>' . $lot['sold_for_amt'] . '</td>';
        $html .= '</tr>';
    }

    $html .= '</table>';

    // Write the HTML content to the PDF
    $pdf->writeHTML($html, true, false, true, false, '');

    // Close and output PDF
    $pdf->Output('lot_list_' . $auctionNumber . '.pdf', 'D');
}

// Generate and download the PDF
generatePDF(getAuctionDetails());

// Flush output buffer
ob_end_flush();
?>
