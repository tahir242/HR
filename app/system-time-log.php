<?php
ob_start();
include("../_init.php");

// Redirect, If user is not logged in
if (!is_loggedin()) {
    redirect(root_url() . '/index.php');
}

// Redirect, If User has not Read Permission
if (user_role_id() != 1 && !has_permission(1, 'read_time_log')) {
    redirect(root_url() . '/'.APPDIRNAME.'/dashboard.php');
}

// Set Document Title
$document->setTitle("System Time Log");
// ADD BODY CLASS
$document->setBodyClass('');

// Add Script and Style
$document->addScript('../assets/app/js/Controller/SystemLogController.js?v=1');

// Include Header and Footer
include realpath(__DIR__ . '/../') . '/_inc/template/partial/header.php';
include realpath(__DIR__ . '/../') . '/_inc/template/partial/sidebar.php';

?>

<!-- Content Wrapper Start -->
<div class="content-wrapper">

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    
                </div>
                <div class="col-sm-6">
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
                    <div class="card-header m-1 p-1">
                        <h3 class="card-title">
                            System Process Time
                        </h3>
                        <!--card Tools End-->
                        <div class="card-tools pull-right">
                            <button id="refreshButton" class="btn btn-sm">Start Refresh</button>
                        </div>
                    </div>
                    <div class="card-body m-1 p-1">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                            <label>User:</label>
                                            <select class="form-control" id="u" name="u">
                                                <option value="All">All</option>
                                                <?php 
                                                $users1 = get_users($response->hostID);
                                                if($users1) : ?>
                                                <?php foreach($users1 AS $user1) : ?>
                                                <option value="<?php echo $user1->UserID ?>"
                                                    <?php echo isset($request->get['u']) && $request->get['u'] == $user1->UserID ? "selected" : "" ?>>
                                                    <?php echo $user1->Fullname ?></option>
                                                <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                            <label>Type:</label>
                                            <select class="form-control" id="t" name="t">
                                                <?php $results = system_log_dictionary(); ?>
                                                <option value="">All</option>
                                                <?php foreach($results AS $key => $value) : ?>
                                                <option value="<?php echo $key ?>" <?php echo isset($request->get['t']) && $request->get['t'] == $key ? "selected" : "" ?>><?php echo $value ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                <div class="form-group">
                                    <br>
                                    <button type="submit" id="apply-filter" class="btn btn-primary">Apply</button>
                                </div>
                                </div>
                            </div>
                        
                        
                        <?php
                            $hide_colums = "";
                        ?>
                        <!-- List Start -->
                        <table id="list" class="table dataTable table-hover table-sm" data-hide-colums="<?php echo $hide_colums ?>"
                            style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>S. No.</th>
                                    <th>Date Time</th>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th>Source</th>
                                    <th>Second</th>
                                </tr>
                            </thead>
                        </table>
                        <!-- List End -->
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Content End -->

</div>
<!-- Content Wrapper End -->
<?php include realpath(__DIR__ . '/../') . '/_inc/template/partial/footer.php'; ?>
