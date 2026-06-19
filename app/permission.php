<?php
ob_start();
include("../_init.php");

// Redirect, If user is not logged in
if (!is_loggedin()) {
    redirect(root_url() . '/index.php');
}

// Redirect, If User has not Read Permission
if (user_group_id() != 1 && !has_permission(1, 'read_permission')) {
    redirect(root_url() . '/'.APPDIRNAME.'/dashboard.php');
}

// Set Document Title
$document->setTitle("Permission");
// ADD BODY CLASS
$document->setBodyClass('');

// Add Script and Style
$document->addScript('../assets/app/js/Controller/PermissionController.js?v=1');

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
                    <h1 class="m-0">Permission</h1>
                </div>
                <div class="col-sm-6">
                <?php if (user_group_id() == 1 || has_permission(1, 'create_permission')) : ?>
                    <div class="float-right">
                        <button class="btn btn-sm btn-info create-new" style="display: inline-block;"><span class="fa fa-plus"></span> Create New</button>
                    </div>
                <?php endif; ?>
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
                            <b>Permission(s) List</b>
                        </h3>
                        <!--card Tools End-->
                        <div class="card-tools pull-right">
                            <input type="text" class="form-control" style="display: inline-block;" id="search-input" placeholder="Search.. (Any Parameter)">
                        </div>
                    </div>
                    <div class="card-body">
                        <?php
                            $hide_colums = "";
                            if (user_group_id() != 1 && !has_permission(1, 'modify_permission')) {
                                $hide_colums .= "6,";
                            }
                        ?>
                        <!-- List Start -->
                        <table id="list" class="table dataTable table-hover table-sm" data-hide-colums="<?php echo $hide_colums ?>"
                            style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>S. No.</th>
                                    <th>Sub Module</th>
                                    <th>Permission ID</th>
                                    <th>Permission</th>
                                    <th>User(s)</th>
                                    <th>Active</th>
                                    <th>Edit</th>
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
