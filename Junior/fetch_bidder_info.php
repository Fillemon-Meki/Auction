<?php
// Include necessary files and initialize database connection
include '../Includes/dbcon.php';

// Check if the request is made using POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the 'idNumber' parameter is set in the POST data
    if(isset($_POST['idNumber'])) {
        // Sanitize input to prevent SQL injection
        $idNumber = mysqli_real_escape_string($conn, $_POST['idNumber']);

        // Prepare and execute query to fetch bidder information based on the provided ID number
        $query = "SELECT id_number, firstname, lastname, cellphone_number, email FROM bidders WHERE id_number = '$idNumber'";
        $result = mysqli_query($conn, $query);

        // Check if query was successful and if any rows were returned
        if ($result && mysqli_num_rows($result) > 0) {
            // Fetch the row as an associative array
            $row = mysqli_fetch_assoc($result);
            
            // Create an array with the fetched bidder information
            $bidderInfo = array(
                'idNumber' => $row['id_number'],
                'firstName' => $row['firstname'],
                'lastName' => $row['lastname'],
                'cellphoneNumber' => $row['cellphone_number'],
                'email' => $row['email']
            );

            // Encode the array as JSON and output it
            echo json_encode($bidderInfo);
        } else {
            // If no bidder with the provided ID number was found, return an empty JSON object
            echo json_encode(array());
        }
    } else {
        // If 'idNumber' parameter is not set in the POST data, return an empty JSON object
        echo json_encode(array());
    }
} else {
    // If the request method is not POST, return an empty JSON object
    echo json_encode(array());
}
?>
