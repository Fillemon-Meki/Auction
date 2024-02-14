<?php 
include 'Includes/dbcon.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="img/logo/attnlg.jpg">
    <title>AUCTION ERA - Login</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="css/ruang-admin.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('img/OIG4.jpg');
            background-size: cover;
            background-position: center;
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container-login {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .login-form {
            text-align: center;
        }

        .login-form img {
            width: 100px;
            height: 100px;
        }

        .login-form h1 {
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }

        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }

        .alert-danger {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <!-- Login Content -->
    <div class="container-login">
        <div class="login-form">
            <h5>AUCTION ERA</h5>
            <div class="text-center">
                <img src="img/logo/attnlg.png" alt="Logo">
                <br><br>
                <h1 class="h4 text-gray-900 mb-4">Login Panel</h1>
            </div>
            <form class="user" method="post" action="">
                <div class="form-group">
                    <select required name="userType" class="form-control mb-3">
                        <option value="">--Select User Roles--</option>
                        <option value="Administrator">Administrator</option>
                        <option value="JuniorAdmin">Junior Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" required name="username" placeholder="Enter Email Address">
                </div>
                <div class="form-group">
                    <input type="password" name="password" required class="form-control" placeholder="Enter Password">
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-success btn-block" value="Login" name="login" />
                </div>
            </form>


                                    <?php

  if(isset($_POST['login'])){

    $userType = $_POST['userType'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $password = md5($password);

    if($userType == "Administrator"){

      $query = "SELECT * FROM tbladmin WHERE emailAddress = '$username' AND password = '$password'";
      $rs = $conn->query($query);
      $num = $rs->num_rows;
      $rows = $rs->fetch_assoc();

      if($num > 0){

        $_SESSION['userId'] = $rows['Id'];
        $_SESSION['firstName'] = $rows['firstName'];
        $_SESSION['lastName'] = $rows['lastName'];
        $_SESSION['emailAddress'] = $rows['emailAddress'];

        echo "<script type = \"text/javascript\">
        window.location = (\"Admin/index.php\")
        </script>";
      }

      else{

        echo "<div class='alert alert-danger' role='alert'>
        Invalid Username/Password!
        </div>";

      }
    }
    elseif($userType == "JuniorAdmin"){

        $query = "SELECT * FROM junior WHERE emailAddress = '$username' AND password = '$password'";
        $rs = $conn->query($query);
        $num = $rs->num_rows;
        $rows = $rs->fetch_assoc();
  
        if($num > 0){
  
          $_SESSION['userId'] = $rows['Id'];
          $_SESSION['firstName'] = $rows['firstName'];
          $_SESSION['lastName'] = $rows['lastName'];
          $_SESSION['emailAddress'] = $rows['emailAddress'];
  
          echo "<script type = \"text/javascript\">
          window.location = (\"Junior/index.php\")
          </script>";
        }
  
        else{
  
          echo "<div class='alert alert-danger' role='alert'>
          Invalid Username/Password!
          </div>";
  
        }
      }
    
    else{

        echo "<div class='alert alert-danger' role='alert'>
        Invalid Username/Password!
        </div>";

    }
}
?>

                    


                                    <div class="text-center">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Login Content -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/ruang-admin.min.js"></script>
</body>

</html>