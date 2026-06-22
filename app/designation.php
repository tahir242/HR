<?php
ob_start();
include("../_init.php");

// Redirect, If user is not logged in
if (!is_loggedin()) {
    redirect(root_url() . '/index.php');
}

// Redirect, If User has not Read Permission
if (user_role_id() != 1 && !has_permission(1, 'read_designation')) {
    redirect(root_url() . '/' . APPDIRNAME . '/home.php');
}

//Set Document Title
$document->setTitle("Designation(s)");
//ADD BODY CLASS
$document->setBodyClass('');

//Add Script and Style
$document->addScript('../assets/app/js/Controller/DesignationController.js?v=1');

//Include Header and Footer
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
                    <h1 class="m-0"><?php echo $title ?></h1>
                </div>
                <div class="col-sm-6">
                    <?php if (user_role_id() == 1 || has_permission(1, 'create_designation')): ?>
                        <div class="float-right">
                            <button class="btn btn-info btn-sm create-new" style="display: inline-block;"
                                data-bs-toggle="tooltip" data-bs-title="Create New"><span class="fa fa-plus"></span> Create
                                New</button>
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
            <div class="col-sm-4 mb-2">
                <input type="text" class="form-control" id="search-input" placeholder="Search.. (With Any Parameter)">
            </div>
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <?php
                            $hide_colums = "";
                            if (user_role_id() != 1 && !has_permission(1, 'modify_designation')) {
                                $hide_colums .= "3,";
                            }
                        ?>
                        <!-- Form List Start -->
                        <table id="list" class="table dataTable table-hover table-sm" data-hide-colums="<?php echo $hide_colums ?>" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Designation</th>
                                    <th>Status</th>
                                    <th>Edit</th>
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

</div>
<!-- Content Wrapper End -->
<?php include realpath(__DIR__ . '/../') . '/_inc/template/partial/footer.php'; ?>
