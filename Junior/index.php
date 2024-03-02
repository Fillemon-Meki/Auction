<?php
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Get the logged-in junior admin's ID from the session
if(isset($_SESSION['userId'])) {
    // Get the user's full name based on the userId
    $query = "SELECT * FROM junior WHERE Id = ".$_SESSION['userId']."";
    $rs = $conn->query($query);

    // Check if the query was successful
    if($rs) {
      $num = $rs->num_rows;
      
      // Check if any rows were returned
      if($num > 0) {
        $rows = $rs->fetch_assoc();
        $loggedInJuniorAdminId = $rows['Id'];
      } else {
        $loggedInJuniorAdminId = "Unknown"; // Provide a default name if no user found
      }
    } else {
      // Handle query error
      $loggedInJuniorAdminId = "Unknown"; // Provide a default name
    }
  } else {
    // Handle session variable not set error
    $loggedInJuniorAdminId = "Unknown"; // Provide a default name
  }

// Query to fetch assigned roles for the logged-in junior admin from the junior_admin_roles table
$sql = "SELECT roles.role_name 
        FROM junior_admin_roles 
        JOIN roles ON junior_admin_roles.role_id = roles.role_id 
        WHERE junior_admin_roles.junior_admin_id = $loggedInJuniorAdminId";

$result = mysqli_query($conn, $sql);

$assignedRoles = array();

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $assignedRoles[] = $row['role_name'];
    }
}

// Assume $allRoles is an array containing all the roles available in the database
$allRoles = array("Manage Lots", "Generate Reports", "User Admin", "Printing", "Cashier", "New Auction");

// Mapping from role names to Font Awesome icon classes
$roleIconClasses = array(
    "Manage Lots" => "fas fa-clipboard-list",
    "Generate Reports" => "fas fa-chart-line",
    "User Admin" => "fas fa-user-cog",
    "Printing" => "fas fa-print",
    "Cashier" => "fas fa-cash-register",
    "New Auction" => "fas fa-gavel"
);

// Function to check if a role is assigned to the logged-in junior admin
function isRoleAssigned($role, $assignedRoles) {
    return in_array($role, $assignedRoles);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Junior Admin Dashboard</title>
    <link rel="stylesheet" href="../vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../vendor/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="css/ruang-admin.min.css">
    <style>
    body {
        background-color: #f8f9fc;
    }
    .container {
        padding-top: 20px;
    }
    .card {
        padding: 20px;
        margin-bottom: 20px;
        background-color: #fff;
        border-radius: 5px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease-in-out;
    }
    .card:hover {
        transform: translateY(-5px);
    }
    .card a {
        text-decoration: none;
        color: inherit;
    }
    .assigned-role {
        background-color: #28a745 !important; /* Green color for assigned roles */
        color: #fff !important; /* White text for assigned roles */
    }
    .card-title i {
        margin-right: 10px;
    }
    .logo {
        text-align: center; /* Center the content horizontally */
        margin-bottom: 20px; /* Add some space below the logo */
    }
    .logo img {
        max-width: 60%; /* Ensure the image doesn't exceed the container's width */
        height: auto; /* Maintain aspect ratio */
    }
    .slogan {
        text-align: center;
        font-size: 18px;
        margin-top: 20px;
    }
</style>

</head>
<body> 
    <?php include "Includes/topbar.php";?>

    <div class="container"> 
        <h2 class="mb-4"><strong>Auction Era, Go Paperless – Your Eco-Friendly Auction Solution in Namibia!</strong></h2>
    
    <div class="logo">
        <img src="img/6.png" alt="Logo"> <!-- Corrected the image extension to .png as mentioned -->
    </div>
        
        
        <div class="row">
            <?php foreach ($assignedRoles as $role): ?>
                <div class="col-lg-4">
                    <div class="card clickable assigned-role">
                        <a href="<?php echo strtolower(str_replace(' ', '_', $role)) . '.php' ?>">
                            <div class="card-body">
                                <h3 class="card-title"><i class="<?php echo $roleIconClasses[$role]; ?>"></i><strong><?php echo $role; ?></strong></h3> <!-- Role name is now bold -->
                            </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php include "Includes/footer.php";?>
 <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
</body>
</html>
