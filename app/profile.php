<?php
ob_start();
include("../_init.php");

// Redirect, If user is not logged in
if (!is_loggedin()) {
    redirect(root_url() . '/index.php');
}

// Set Document Title
$document->setTitle("User Profile");
// ADD BODY CLASS
$document->setBodyClass('');

// Add Script and Style
$document->addScript('../assets/app/js/Controller/UserProfileController.js?v=4');

// Include Header and Footer
include realpath(__DIR__ . '/../') . '/_inc/template/partial/header.php';
include realpath(__DIR__ . '/../') . '/_inc/template/partial/sidebar.php';

// FETCH USER INFO
if(isset($request->post['UserID']) && $request->post['UserID'] !== ""){

    $UserID = $request->post['UserID'];
 
    $url  = SSOURL . "/host_user.php";
    $data = ["UserID" => $UserID, "auth"   => APPID];
 
   $response = json_decode(make_request($url, $data));
    // print_r($response);
    // exit();
 }else{
    redirect(root_url() . '/'.APPDIRNAME.'/user.php');
 }

?>
<script> var userid = "<?php echo $response->data->UserID ?>"; </script>
<!-- Content Wrapper Start -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><?= $title ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="user.php">User</a></li>
                        <li class="breadcrumb-item active"><?= $title ?></li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">

                    <!-- Profile Image -->
                    <div class="card card-info card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <img class="profile-user-img img-fluid img-circle"
                                    src="<?php echo $response->data->Photo ? SSOURL . "/storage/users/" . $response->data->Photo : "../assets/app/img/avatar6.png" ?>"
                                    alt="User profile picture">
                            </div>

                            <h3 class="profile-username text-center"><?php echo $response->data->Fullname ?></h3>
                            <p class="text-muted text-center">@<?php echo $response->data->Username ?><br><?php echo $response->data->EmpID ?></p>
                            <hr>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->

                    <!-- About Me Box -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">About</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body p-0" style="display: block;">

                            <ul class="nav nav-pills flex-column mb-3 mt-2">
                                <li class="nav-item">
                                    <a href="javascript:void(0);" class="nav-link action" data-action="USERACCOUNTINFORMATION">
                                        <b><i class="fas fa-user mr-1"></i> Account Information</b>
                                    </a>
                                </li>
                                <?php if (user_group_id() == 1 || has_permission(1, 'change_password')) : ?>
                                <li class="nav-item">
                                    <a href="javascript:void(0);" class="nav-link action" data-action="CHANGEPASSWORDFORM">
                                        <b><i class="fas fa-key mr-1"></i> Change Password</b>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if (user_group_id() == 1 || has_permission(1, 'change_role')) : ?>
                                <li class="nav-item">
                                    <a href="javascript:void(0);" class="nav-link action" data-action="CHANGEROLEFORM" id="sr">
                                        <b><i class="fas fa-user-group mr-1"></i> Role</b>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if (user_group_id() == 1 || has_permission(1, 'change_permission')) : ?>
                                <li class="nav-item">
                                    <a href="javascript:void(0);" class="nav-link action" data-action="CHANGEUSERPERMISSION" id="up">
                                        <b><i class="fa-solid fa-address-card mr-1"></i> Permission(s)</b>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>

                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
                <div class="col-md-9">
                    <div id="rawHtml"></div>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->

</div>
<!-- Content Wrapper End -->
<?php include realpath(__DIR__ . '/../') . '/_inc/template/partial/footer.php'; ?>
