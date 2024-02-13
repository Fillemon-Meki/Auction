<?php
// Assuming you get the auctionNumber from the GET request or session
$auctionNumber = $_GET['auctionNumber'] ?? '';

// Function to append auctionNumber to a URL
function appendauctionNumberToUrl($url, $auctionNumber) {
    if ($auctionNumber) {
        return $url . (strpos($url, '?') !== false ? '&' : '?') . 'auctionNumber=' . urlencode($auctionNumber);
    }
    return $url;
}
?>

<ul class="navbar-nav sidebar sidebar-light accordion" id="accordionSidebar">
    <a class="sidebar-brand d-flex align-items-center bg-gradient-primary justify-content-center" href="index.php">
        <div class="sidebar-brand-icon">
            <img src="img/logo/attnlg.png">
        </div>
        <div class="sidebar-brand-text mx-3">AUCTION ERA</div>
    </a>
    <hr class="sidebar-divider my-0">
    <li class="nav-item active">
        <a class="nav-link" href="<?php echo appendauctionNumberToUrl('dashboard.php', $auctionNumber); ?>">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>
    <hr class="sidebar-divider">
    
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseBidders" aria-expanded="true" aria-controls="collapseBidders">
            <i class="fas fa-users"></i>
            <span>BIDDERS</span>
        </a>
        <div id="collapseBidders" class="collapse" aria-labelledby="headingBidders" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="<?php echo appendauctionNumberToUrl('registeredBidders.php', $auctionNumber); ?>">Registered Bidders</a>
                <a class="collapse-item" href="<?php echo appendauctionNumberToUrl('bidders.php', $auctionNumber); ?>">Bids</a>
            </div>
        </div>
    </li>

    <hr class="sidebar-divider">
    
    <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAuction" aria-expanded="true" aria-controls="collapseAuction">
        <i class="fas fa-gavel"></i>
        <span>MANAGE AUCTION</span>
    </a>
    <div id="collapseAuction" class="collapse" aria-labelledby="headingAuction" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
           
            <a class="collapse-item" href="<?php echo appendauctionNumberToUrl('lotlists.php', $auctionNumber); ?>">Lots</a>
            <a class="collapse-item" href="<?php echo appendauctionNumberToUrl('qrcodes.php', $auctionNumber); ?>">QR Codes</a>
            <a class="collapse-item" href="<?php echo appendauctionNumberToUrl('downloadLotList.php', $auctionNumber); ?>">Download Lot List</a>
        </div>
    </div>
</li>


    <!-- Other single clickable links -->
    <hr class="sidebar-divider">
    <!-- Add your other sections here -->

</ul>
