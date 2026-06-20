<?php
ob_start();
include("../_init.php");

// Redirect, If user is not logged in
if (!is_loggedin()) {
    redirect(root_url() . '/index.php');
}

// Redirect, If User has not Read Permission
if (user_role_id() != 1 && !has_permission(1, 'read_search')) {
    redirect(root_url() . '/' . APPDIRNAME . '/dashboard.php');
}

// Add Script and Style
$document->addScript('../assets/app/js/Controller/SearchController.js?v=1');

// Set Document Title
$document->setTitle("Searching");
// ADD BODY CLASS
$document->setBodyClass('');

// Include Header and Footer
include realpath(__DIR__ . '/../') . '/_inc/template/partial/header.php';
include realpath(__DIR__ . '/../') . '/_inc/template/partial/sidebar.php';

?>
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

    /* #pdf_screen {
        height: 100%;
    } */

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

    <!-- Content Start -->
    <section class="content">

        <div class="row">
            <div class="col-sm-3">
                <div id="rawHtml1"></div>
                <div class="card sticky-top" id="search-field">
                    <div class="card-header">
                        <h3 class="card-title">
                            <b>Search Fields</b>
                        </h3>
                    </div>
                    <form id="create-form" action="search.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action_type" value="SEARCH">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="Employee_ID">Employee ID: </label>
                                <input type="text" name="Employee_ID" class="form-control" id="Employee_ID1"
                                    placeholder="Write Employee ID" autofocus>
                                <div class="error-message" id="employee-id"></div>
                            </div>
                            <div class="form-group">
                                <label for="Employee_Name">Employee Name: </label>
                                <input type="text" name="Employee_Name" class="form-control" id="Employee_Name1"
                                    placeholder="Write Employee Name">
                                <div class="error-message" id="employee-name"></div>
                            </div>
                            <div class="form-group search-box">
                                <label for="Department">Department:</label>
                                <select class="form-control select2" name="Department" id="Department1">
                                    <option value="">Select Department</option>
                                    <?php $results = get_departments(); ?>
                                    <?php if ($results) {
                                        foreach ($results as $department) {
                                            echo "<option value=\"" . $department->Department_ID . "\">" . $department->Department . "</option>";
                                        }
                                    } ?>
                                </select>
                            </div>
                            <div class="form-group search-box">
                                <label for="Designation">Designation:</label>
                                <select class="form-control select2" name="Designation" id="Designation1">
                                    <option value="">Select Designation</option>
                                    <?php $results = get_designations(); ?>
                                    <?php if ($results) {
                                        foreach ($results as $designation) {
                                            echo "<option value=\"" . $designation->Designation_ID . "\">" . $designation->Designation . "</option>";
                                        }
                                    } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="DOJ">Date of Joining:</label>
                                <input type="text" name="DOJ" class="form-control" id="DOJ1" placeholder="DD-MM-YYYY"
                                    tabindex="5" oninput="formatDate(this)">
                                <div class="error-message" id="employee-doj"></div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary" id="create-submit" data-datatable="#list"
                                name="create-submit" data-form="#create-form" data-loading-text="Saving..."
                                tabindex="6">Search</button>
                            <button id="reset" name="reset" class="btn btn-danger"> Clear Filter</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-sm-9">
                <div id="rawHtml"></div>
            </div>
        </div>

    </section>
    <!-- Content End -->

</div>
<!-- Content Wrapper End -->
<?php include realpath(__DIR__ . '/../') . '/_inc/template/partial/footer.php'; ?>
