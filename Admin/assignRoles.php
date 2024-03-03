<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../Includes/dbcon.php';

// Fetch junior admins
$juniorAdminsQuery = "SELECT * FROM junior";
$juniorAdminsResult = mysqli_query($conn, $juniorAdminsQuery);

// Fetch roles
$rolesQuery = "SELECT * FROM roles";
$rolesResult = mysqli_query($conn, $rolesQuery);

// Fetch assigned roles for each junior admin
$assignedRolesQuery = "SELECT junior_admin_id, role_id FROM junior_admin_roles";
$assignedRolesResult = mysqli_query($conn, $assignedRolesQuery);
$assignedRoles = array();
while ($row = mysqli_fetch_assoc($assignedRolesResult)) {
    $juniorAdminId = $row['junior_admin_id'];
    $roleId = $row['role_id'];
    if (!isset($assignedRoles[$juniorAdminId])) {
        $assignedRoles[$juniorAdminId] = array();
    }
    $assignedRoles[$juniorAdminId][] = $roleId;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Assign Roles</title>
    <link href="../vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/ruang-admin.min.css" rel="stylesheet">
    <style>
        .card-header {
            background-color: #fff;
            color: white;
        }

        .role-checkbox {
            display: none;
        }

        .role-label {
            margin-right: 10px;
        }

        .btn {
            margin-top: 10px;
        }
    </style>
</head>
<body id="page-top">
    <div id="wrapper">
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include "Includes/topbar.php";?>
                <div class="container-fluid" id="container-wrapper">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Assign Roles</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="./">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Assign Roles</li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Junior Admins</h6>
                                </div>
                                <div class="table-responsive p-3">
                                    <table class="table align-items-center table-flush table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th><i class="fas fa-id-badge"></i> ID</th>
                                                <th><i class="fas fa-user"></i> Name</th>
                                                <th><i class="fas fa-envelope"></i> Email</th>
                                                <th><i class="fas fa-cogs"></i> </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = mysqli_fetch_assoc($juniorAdminsResult)) : ?>
                                                <tr class="admin-row">
                                                    <td><?= $row['Id'] ?></td>
                                                    <td><?= $row['firstName'] . ' ' . $row['lastName'] ?></td>
                                                    <td><?= $row['emailAddress'] ?></td>
                                                    <td>
                                                        <form class="role-form" method="post">
                                                            <?php
                                                            // Loop through roles to create checkboxes
                                                            mysqli_data_seek($rolesResult, 0); // Reset roles result pointer
                                                            while ($role = mysqli_fetch_assoc($rolesResult)) {
                                                                $checked = '';
                                                                // Check if the role is already assigned to the junior admin
                                                                if (isset($assignedRoles[$row['Id']]) && in_array($role['role_id'], $assignedRoles[$row['Id']])) {
                                                                    $checked = 'checked';
                                                                }
                                                                echo "<input type='checkbox' class='role-checkbox' id='role{$role['role_id']}' name='roles[{$row['Id']}][]' value='{$role['role_id']}' $checked><label for='role{$role['role_id']}' class='role-label'>{$role['role_name']}</label>";
                                                            }
                                                            ?>
                                                            <button type="submit" class="btn btn-primary"><i class="fas fa-user-tag"></i> Save </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php include "Includes/footer.php";?>
            </div>
        </div>
    </div>

    <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
  <script>
    $(document).ready(function() {
        var uncheckedRoles = {}; // Object to store unchecked roles

        // Hide the role form initially
        $('.role-form').hide();

        // Toggle the role form on admin row hover
        $('.admin-row').hover(function() {
            $(this).find('.role-form').toggle();
            $(this).find('.role-checkbox').toggle();
        });

        // Handle checkbox change event
        $('.role-checkbox').change(function() {
            var juniorId = $(this).closest('.admin-row').find('td:first').text();
            var roleId = $(this).val();

            if ($(this).is(':checked')) {
                // If checkbox is checked, remove it from uncheckedRoles object
                delete uncheckedRoles[juniorId + '-' + roleId];
            } else {
                // If checkbox is unchecked, add it to uncheckedRoles object
                uncheckedRoles[juniorId + '-' + roleId] = true;
            }
        });

        // Handle form submission
        $('.role-form').submit(function(event) {
            event.preventDefault(); // Prevent the default form submission

            // Serialize form data
            var formData = $(this).serialize();

            // Add information about unchecked roles to formData
            formData += '&' + $.param({ 'uncheckedRoles': uncheckedRoles });

            // Submit form data using AJAX
            $.ajax({
                type: 'POST',
                url: 'updateJuniorRoles.php', // Update the URL to the correct PHP file
                data: formData,
                success: function(response) {
                    // Display success message or handle response
                    alert(response); // You can replace this with any action you want to take after successful submission

                    // Clear uncheckedRoles object
                    uncheckedRoles = {};
                },
                error: function(xhr, status, error) {
                    // Handle errors
                    console.error(xhr.responseText);
                }
            });
        });
    });
</script>
</body>
</html>
