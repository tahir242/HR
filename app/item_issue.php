<?php
ob_start();
include("../_init.php");

// Redirect, If user is not logged in
if (!is_loggedin()) {
    redirect(root_url() . '/index.php');
}

// Redirect, If User has not Read Permission
if (user_role_id() != 1 && !has_permission(1, 'read_item')) {
    redirect(root_url() . '/' . APPDIRNAME . '/home.php');
}

//Set Document Title
$document->setTitle("Item Issue");
//ADD BODY CLASS
$document->setBodyClass('');

//Add Script and Style
$document->addScript('../assets/app/js/Controller/ItemIssueController.js?v=1');

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
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Content Start -->
    <section class="content">

        <div class="row">
            <div class="col-sm-2 mb-2">
                <input type="text" class="form-control" id="searchInput" placeholder="Filter.. (With Employee ID)">
            </div>
            <div class="col-sm-2 mb-2">
                <select class="form-control" name="Item_ID" id="Item_ID">
                    <option value="">Select Item</option>
                    <?php
                    $results = get_items();
                    foreach ($results as $result): ?>
                        <option value="<?php echo $result->Item_ID ?>" data-unit="<?php echo $result->Unit ?>">
                            <?php echo $result->Item_Name ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-2 mb-2">
                <select class="form-control" name="Working_Year" id="Working_Year">
                    <option value="2026">2026</option>
                    <option value="2025">2025</option>
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
                        <?php
                        $hide_colums = "";
                        ?>
                        <!-- Form List Start -->
                        <table id="list" class="table dataTable table-hover table-sm" data-hide-colums="<?php echo $hide_colums ?>" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>S. No.</th>
                                    <th>Receive Date</th>
                                    <th>Employee ID</th>
                                    <th>Item</th>
                                    <th>Issue Qty</th>
                                    <th>Unit</th>
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
