<?php
ob_start();
include("../_init.php");

// Redirect, If user is not logged in
if (!is_loggedin()) {
    redirect(root_url() . '/index.php');
}

// Redirect, If User has not Read Permission
if (user_group_id() != 1 && !has_permission(1, 'read_demographic')) {
    redirect(root_url() . '/' . APPDIRNAME . '/dashboard.php');
}

// Add Script and Style
$document->addScript('../assets/app/js/Controller/IndexController.js?v=1');

// Set Document Title
$document->setTitle("Indexing");
// ADD BODY CLASS
// $document->setBodyClass('sidebar-collapse');

// Include Header and Footer
include realpath(__DIR__ . '/../') . '/_inc/template/partial/header.php';
include realpath(__DIR__ . '/../') . '/_inc/template/partial/sidebar.php';

// LOAD MODEL 
$fieldmodel = registry()->get('loader')->model('field');
$imodel = registry()->get('loader')->model('indexing');

$pdfrow = $imodel->getpdfforindexing(); ?>
<style>
    body {
        user-select: none;
    }

    @media print {
        body {
            display: none;
        }
    }

    input[type="radio"],
    input[type="checkbox"] {
        transform: scale(1.3);
    }

    .instruction-box {
        height: auto;
        max-height: 300px;
        overflow-y: scroll;
        color: black;
        transition: color 0.3s;
    }

    .selectedpdf {
        font-weight: bold;
        color: black;
        background-color: #D6EAF8;
        padding: 5px;
    }

    #pdf_screen {
        height: 650px;
    }

    .error-message {
        color: red;
        font-size: 14px;
        display: block;
        /* margin-top: 5px; */
    }

    .autocomplete-items {
        position: absolute;
        border: 1px solid #d4d4d4;
        border-bottom: none;
        border-top: none;
        z-index: 99;
        top: 100%;
        left: 0;
        right: 0;
    }

    .autocomplete-items div {
        padding: 5px;
        cursor: pointer;
        background-color: #fff;
        border-bottom: 1px solid #d4d4d4;
    }

    .autocomplete-items div:hover {
        /*when hovering an item:*/
        background-color: #e9e9e9;
    }

    .autocomplete-active {
        /*when navigating through the items using the arrow keys:*/
        background-color: DodgerBlue !important;
        color: #ffffff;
    }
</style>

<!-- Content Wrapper Start -->
<div class="content-wrapper">

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <!-- <h1 class="m-0">Demographic</h1> -->
                </div>
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <?php if (!$pdfrow): ?>
        <div class="alert alert-info col-sm-6 offset-sm-3">
            <h5><i class="icon fas fa-info"></i> Alert!</h5>
            No PDF Available for Indexing.
        </div>
    <?php endif; ?>
    <?php if($pdfrow) : ?>
    <!-- Content Start -->
    <section class="content">
        <div class="row">
            <div class="col-sm-4">
                <div class="card">

                    <div class="card-header">
                        <h3 class="card-title">
                            <b>Entry Field(s)</b> <small>press (Alt + S) to submit the form</small>
                        </h3>
                    </div>
                    <form id="create-form" action="indexing.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action_type" value="CREATE">
                        <input type="hidden" name="Scan" value="<?php echo $pdfrow->Scan ?>">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="Employee_ID">Employee ID: <span style="color: red;">*</span></label> <button
                                    class="btn btn-sm btn-secondary p-1 float-right" id="missing-id">Fill Missing ID</button>
                                <input type="text" name="Employee_ID" value="<?php echo $pdfrow->Employee_ID ? $pdfrow->Employee_ID : "" ?>" class="form-control" id="Employee_ID"
                                    placeholder="Write Employee ID" tabindex="1" autofocus autocomplete="off">
                                <div class="error-message" id="employee-id"></div>
                            </div>
                            <div class="form-group">
                                <label for="Employee_Name">Employee Name: <span style="color: red;">*</span></label>
                                <input type="text" name="Employee_Name" value="<?php echo $pdfrow->Name ? $pdfrow->Name : "" ?>" class="form-control" id="Employee_Name"
                                    placeholder="Write Employee Name" tabindex="2" required autocomplete="off">
                                <div class="error-message" id="employee-name"></div>
                            </div>
                            <div class="form-group search-box">
                                <label for="Department">Department:</label>
                                <div class="input-group autocomplete">
                                    <input type="text" placeholder="Write Employee Department" tabindex="3"
                                        class="form-control" name="Department" value="<?php echo $pdfrow->Department ? get_the_department($pdfrow->Department, "Department") : "" ?>" id="Department" autocomplete="off">
                                </div>
                            </div>
                            <div class="form-group search-box">
                                <label for="Designation">Designation:</label>
                                <div class="input-group autocomplete">
                                    <input type="text" placeholder="Write Employee Designation" tabindex="4"
                                        class="form-control" name="Designation" value="<?php echo $pdfrow->Designation ? get_the_designation($pdfrow->Designation, "Designation") : "" ?>" id="Designation" autocomplete="off">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="DOJ">Date of Joining:</label>
                                <input type="text" name="DOJ" value="<?php echo $pdfrow->Date_of_Joining ? date_normalizer($pdfrow->Date_of_Joining, "d-m-Y") : "" ?>" class="form-control" id="DOJ" placeholder="DD-MM-YYYY"
                                    tabindex="5" oninput="formatDate(this)" required autocomplete="off">
                                <div class="error-message" id="employee-doj"></div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary" id="create-submit" data-datatable="#list"
                                name="create-submit" data-form="#create-form" data-loading-text="Saving..."
                                tabindex="6">Save &
                                Next</button>
                            <button type="reset" id="reset" name="reset" class="btn btn-danger">Reset</button>
                        </div>
                    </form>
                </div>

            </div>
            <div class="col-sm-8">
                <div class="card" id="myDiv">
                    <div class="card-header">
                        <h3 class="card-title">
                            <b><?php echo $pdfrow->Scan ?></b>
                        </h3>
                        <div class="card-tools pull-right">
                            <a href="javascript:void(0)" onclick="$('#pdf_screen').toggleClass('fullscreen');"
                                class="btn btn-sm btn-icon float-right" data-card-widget="maximize">
                                <i class="fas fa-expand"></i>
                            </a>
                        </div>
                    </div>

                    <?php
                    $file = parameter("file_path") . $pdfrow->Scan;
                    $proxyUrl = root_url() . "/app/proxy.php?file=" . urlencode($file); ?>
                    <div class="card-body m-0 p-0" id="pdf_screen">
                        <iframe id="pdf-js-viewer"
                            src="../_inc/vendor/pdfjs/web/pdf.html?file=<?php echo urlencode($proxyUrl); ?>"
                            title="webviewer" frameborder="0" name="myiframename" width="100%" height="100%"
                            allowfullscreen webkitallowfullscreen></iframe>
                    </div>
                </div>
                <!--end::Portlet-->
            </div>
        </div>

    </section>
    <!-- Content End -->
    <?php endif; ?>
</div>
<!-- Content Wrapper End -->
<?php include realpath(__DIR__ . '/../') . '/_inc/template/partial/footer.php'; ?>
