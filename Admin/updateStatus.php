<?php
include '../Includes/dbcon.php';

if(isset($_POST['updates'])) {
    foreach($_POST['updates'] as $update) {
        $id = $update['id'];
        $status = $update['status'];

        $query = "UPDATE biddersauction SET deposit_status = ? WHERE id_number = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $status, $id); // Use "ii" for two integers
        $stmt->execute();
    }
    echo "Statuses updated successfully.";
} else {
    echo "No updates received.";
}
?>
