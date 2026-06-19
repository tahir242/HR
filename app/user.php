<?php
ob_start();
include("../_init.php");

// Redirect, If user is not logged in
if (!is_loggedin()) {
    redirect(root_url() . '/index.php');
}

// Redirect, If User has not Read Permission
if (user_group_id() != 1 && !has_permission(1, 'read_user')) {
    redirect(root_url() . '/'.APPDIRNAME.'/dashboard.php');
}

// Add Script and Style
$document->addScript('../assets/app/js/Controller/UserController.js?v=4');

// Set Document Title
$document->setTitle("Users");
// ADD BODY CLASS
$document->setBodyClass('');

// Include Header and Footer
include realpath(__DIR__ . '/../') . '/_inc/template/partial/header.php';
include realpath(__DIR__ . '/../') . '/_inc/template/partial/sidebar.php';


?>

<script>
    var hostID = <?php echo $response->hostID; ?>
</script>

<!-- Content Wrapper Start -->
<div class="content-wrapper">

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0">Users</h1>
                </div>
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Content Start -->
    <section class="content">

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <b>User List</b>
                        </h3>
                        <!--card Tools End-->
                        <div class="card-tools pull-right">
                            <input type="text" class="form-control" id="searchInput" placeholder="Search.. (Any Parameter)">
                        </div>
                    </div>
                    <div class="card-body">
                        <?php
                            $hide_colums = "";
                            if (user_group_id() != 1 && !has_permission(1, 'view_profile')) {
                                $hide_colums .= "8,";
                            }
                        ?>
                            <!-- Form List Start -->
                            <table id="list" class="table dataTable table-hover table-sm" data-hide-colums="<?php echo $hide_colums ?>"
                                style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>User ID</th>
                                        <th>Employee ID</th>
                                        <th>Username</th>
                                        <th>Fullname</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                        <th>Created Date</th>
                                        <th>Status</th>
                                        <th>Profile</th>
                                    </tr>
                                </thead>
                            </table>
                            <!-- Form List End -->
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Content End -->

<?php

$results = get_users($response->hostID);
foreach($results AS $result){

    $query = "SELECT * FROM Users WHERE UserID = ?";
    $stmt = $dblite->prepare($query);
    $stmt->execute([$result->UserID]);
    $row = $stmt->fetch(PDO::FETCH_OBJ);

    if($row){
        $updateQuery = "UPDATE Users SET EmpID = ?, EmpInitial = ?, Username = ?, Fullname = ?, Email = ?, Mobile = ?, Gender = ?, DOB = ?, Photo = ?, Active = ?  WHERE UserID = ?";
        $updateStmt = $dblite->prepare($updateQuery);
        $updateStmt->execute(array($result->EmpID, $result->EmpInitial, $result->Username, $result->Fullname, $result->Email, $result->Mobile, $result->Gender, date_normalizer($result->DOB, "Y-m-d"), $result->Photo, $result->Active, $row->UserID));
    }else{
        $insertQuery = "INSERT INTO Users (UserID, EmpID, EmpInitial, Username, Fullname, Email, Mobile, Gender, DOB, Photo, Active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $dblite->prepare($insertQuery);
        $insertStmt->execute(array($result->UserID, $result->EmpID, $result->EmpInitial, $result->Username, $result->Fullname, $result->Email, $result->Mobile, $result->Gender, date_normalizer($result->DOB, "Y-m-d"), $result->Photo, $result->Active));
    }

}

?>


</div>
<!-- Content Wrapper End -->
<?php include realpath(__DIR__ . '/../') . '/_inc/template/partial/footer.php'; ?>
