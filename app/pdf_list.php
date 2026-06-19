<?php
ob_start();
include("../_init.php");

// Redirect, If user is not logged in
if (!is_loggedin()) {
    redirect(root_url() . '/index.php');
}

// Redirect, If User has not Read Permission
if (user_group_id() != 1 && !has_permission(1, 'read_pdf')) {
    redirect(root_url() . '/' . APPDIRNAME . '/dashboard.php');
}

// Add Script and Style
$document->addScript('../assets/app/js/Controller/PDFController.js?v=6');

// Set Document Title
$document->setTitle("PDF's List");

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
                    <h1 class="m-0 d-none"><?php echo $title; ?></h1>
                </div>
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Content Start -->
    <section class="content">

        <div class="row">
            <div class="col-sm-2 mb-2">
                <input type="text" class="form-control" id="searchInput" placeholder="Search.. (Any Parameter)">
            </div>
            <div class="col-sm-2 mb-2">
                <select class="form-control" id="filter" name="filter">
                    <option value="">All</option>
                    <option value="Uploaded">Uploaded</option>
                    <option value="Indexing">Indexing</option>
                    <option value="Indexed">Indexed</option> 
                </select>
            </div>
            <div class="col-sm-2 mb-2">
                <a type="button" href="javascript:void(0);" id="apply-filter" class="btn btn-info" title="Filter Forms">
                    <i class="fa fa-filter"></i> Filter
                </a>
            </div>
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Form List Start -->
                        <table id="list" class="table dataTable table-valign-middle table-hover table-sm" data-hide-colums=""
                            style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Designation</th>
                                    <th>Date of Joining</th>
                                    <th>Scan</th>
                                    <th>Status</th>
                                    <th>Open</th>
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
