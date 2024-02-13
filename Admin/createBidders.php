<?php
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

//------------------------SAVE--------------------------------------------------

if(isset($_POST['save'])){
    
  $idNumber = $_POST['idNumber'];
  $surname = $_POST['surname'];
  $firstname = $_POST['firstname'];
  $bidderNumber = $_POST['bidderNumber'];
  $cellphone = $_POST['cellphone'];
  $depositAmount = $_POST['depositAmount'];
  $dateOfRegister = date("Y-m-d");

  $query = mysqli_query($conn, "SELECT * FROM bidders WHERE id_number = '$idNumber'");
  $ret = mysqli_fetch_array($query);

  if($ret > 0){ 
      $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>Record with this ID Number Already Exists!</div>";
  } else {
      $query = mysqli_query($conn, "INSERT INTO bidders(id_number, surname, firstname, bidder_number, cellphone, deposit_amount, date_of_register) 
      VALUES ('$idNumber', '$surname', '$firstname', '$bidderNumber', '$cellphone', '$depositAmount', '$dateOfRegister')");

      if ($query) {
          $statusMsg = "<div class='alert alert-success' style='margin-right:700px;'>Record Created Successfully!</div>";
      } else {
          $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error Occurred!</div>";
      }
  }
}


//---------------------------------------EDIT-------------------------------------------------------------

//--------------------EDIT------------------------------------------------------------

if (isset($_GET['id_number']) && isset($_GET['action']) && $_GET['action'] == "edit") {
  $idNumber = $_GET['id_number'];

  $query = mysqli_query($conn, "SELECT * FROM bidders WHERE id_number = '$idNumber'");
  $row = mysqli_fetch_array($query);

  //------------UPDATE-----------------------------

  if(isset($_POST['update'])){
      $idNumber = $_POST['idNumber'];
      $surname = $_POST['surname'];
      $firstname = $_POST['firstname'];
      $bidderNumber = $_POST['bidderNumber'];
      $cellphone = $_POST['cellphone'];
      $depositAmount = $_POST['depositAmount'];

      $query = mysqli_query($conn, "UPDATE bidders SET
          surname='$surname',
          firstname='$firstname',
          bidder_number='$bidderNumber',
          cellphone='$cellphone',
          deposit_amount='$depositAmount'
          WHERE id_number='$idNumber'");

      if ($query) {
          echo "<script type = \"text/javascript\">
          window.location = (\"your_page.php\")
          </script>";
      } else {
          $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error Occurred!</div>";
      }
  }
}

//--------------------------------DELETE------------------------------------------------------------------

if (isset($_GET['Id']) && isset($_GET['action']) && $_GET['action'] == "delete") {
    $Id= $_GET['Id'];
    $query = mysqli_query($conn,"DELETE FROM bidders WHERE id_number='$Id'");

    if ($query == TRUE) {
        echo "<script type = \"text/javascript\">
        window.location = (\"createStudents.php\")
        </script>";
    } else {
        $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error Occurred!</div>"; 
    }
}

?>

<!DOCTYPE html>
<html lang="en">


<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <link href="img/logo/attnlg.jpg" rel="icon">
<?php include 'includes/title.php';?>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">



   <script>
    function classArmDropdown(str) {
    if (str == "") {
        document.getElementById("txtHint").innerHTML = "";
        return;
    } else { 
        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("txtHint").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET","ajaxClassArms2.php?cid="+str,true);
        xmlhttp.send();
    }
}
</script>
</head>

<body id="page-top">
  <div id="wrapper">
    <!-- Sidebar -->
      <?php include "Includes/sidebar.php";?>
    <!-- Sidebar -->
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <!-- TopBar -->
       <?php include "Includes/topbar.php";?>
        <!-- Topbar -->


  <div class="container-fluid" id="container-wrapper">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Create Students</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Create Students</li>
            </ol>
    </div>

    <div class="row">
      <div class="col-lg-12">
        <!-- Form Basic -->
        <div class="card mb-4">
          <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Create Students</h6>
            <?php echo $statusMsg; ?>
          </div>
          <div class="card-body">
            <form method="post">
              <div class="form-group row mb-3">
                <div class="col-xl-6">
                  <label class="form-control-label">First Name<span class="text-danger ml-2">*</span></label>
                  <input type="text" class="form-control" name="firstname" value="<?php echo $row['firstname'];?>" id="exampleInputFirstName">
                </div>
                <div class="col-xl-6">
                  <label class="form-control-label">Last Name<span class="text-danger ml-2">*</span></label>
                  <input type="text" class="form-control" name="surname" value="<?php echo $row['surname'];?>" id="exampleInputFirstName">
                </div>
              </div>
              <!-- ... (unchanged) ... -->
              <button type="submit" name="save" class="btn btn-primary">Save</button>
            </form>
          </div>
        </div>
        
        <!-- Input Group -->
        <div class="row">
          <div class="col-lg-12">
            <div class="card mb-4">
              <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">All Students</h6>
              </div>
              <div class="table-responsive p-3">
                <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                  <!-- ... (unchanged) ... -->
                  <tbody>
                    <?php
                      $query = "SELECT * FROM bidders";
                      $rs = $conn->query($query);
                      $num = $rs->num_rows;
                      $sn=0;
                      $status="";
                      if($num > 0) { 
                        while ($rows = $rs->fetch_assoc()) {
                          $sn = $sn + 1;
                          echo "
                            <tr>
                              <td>".$sn."</td>
                              <td>".$rows['firstname']."</td>
                              <td>".$rows['surname']."</td>
                              <!-- ... (unchanged) ... -->
                            </tr>";
                        }
                      } else {
                        echo "<div class='alert alert-danger' role='alert'>
                          No Record Found!
                          </div>";
                      }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--Row-->
  </div>
  <!---Container Fluid-->
</div>
<!-- ... (unchanged) ... -->
</body>

</html>
