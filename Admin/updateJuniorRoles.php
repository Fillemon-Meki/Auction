<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if roles data is received
    if (isset($_POST['roles']) && !empty($_POST['roles'])) {
        // Prepare statement to insert data into the junior_admin_roles table
        $insertStmt = $conn->prepare("INSERT INTO junior_admin_roles (junior_admin_id, role_id) VALUES (?, ?)");

        foreach ($_POST['roles'] as $juniorAdminId => $roles) {
            foreach ($roles as $roleId) {
                // Check if the record already exists
                $checkStmt = $conn->prepare("SELECT COUNT(*) FROM junior_admin_roles WHERE junior_admin_id = ? AND role_id = ?");
                $checkStmt->bind_param("ii", $juniorAdminId, $roleId);
                $checkStmt->execute();
                $checkStmt->bind_result($count);
                $checkStmt->fetch();
                $checkStmt->close();

                // If the record doesn't exist, insert it
                if ($count == 0) {
                    // Bind parameters and execute the statement
                    $insertStmt->bind_param("ii", $juniorAdminId, $roleId);
                    $insertStmt->execute();
                }
            }
        }

        // Close statement
        $insertStmt->close();

        // Display success message
        $successMessage = "Roles assigned successfully!";
    } else {
        // Display error message if no roles are selected
        $errorMessage = "No roles selected!";
    }

    // Check if there are unchecked roles to delete
    if (isset($_POST['uncheckedRoles']) && !empty($_POST['uncheckedRoles'])) {
        // Prepare statement to delete unchecked roles from the junior_admin_roles table
        $deleteStmt = $conn->prepare("DELETE FROM junior_admin_roles WHERE junior_admin_id = ? AND role_id = ?");

        foreach ($_POST['uncheckedRoles'] as $uncheckedRole => $_) {
            list($juniorAdminId, $roleId) = explode('-', $uncheckedRole);
            // Bind parameters and execute the statement
            $deleteStmt->bind_param("ii", $juniorAdminId, $roleId);
            $deleteStmt->execute();
        }

        // Close statement
        $deleteStmt->close();
    }

    // Determine the response message
    if (isset($successMessage)) {
        echo $successMessage;
    } elseif (isset($errorMessage)) {
        echo $errorMessage;
    }
} else {
    // Display error message if the request method is not POST
    echo "Invalid request!";
}
?>
