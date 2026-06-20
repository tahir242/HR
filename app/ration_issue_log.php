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
$document->setTitle("Ration Issue Log");
//ADD BODY CLASS
$document->setBodyClass('');

//Add Script and Style
$document->addScript('../assets/app/js/Controller/RationIssueLogController.js?v=1');

//Include Header and Footer
include realpath(__DIR__ . '/../') . '/_inc/template/partial/header.php';
include realpath(__DIR__ . '/../') . '/_inc/template/partial/sidebar.php';

// Get filter data

$dept_query = "SELECT DISTINCT D.[Department_ID], D.[Department] FROM [Department] D 
               INNER JOIN [Employee] E ON D.[Department_ID] = E.[Department]
               INNER JOIN [Ration_Issue_Log] RIL ON E.[Employee_ID] = RIL.[Employee_ID]
               ORDER BY D.[Department]";
$departments = db()->get_results($dept_query);

$desig_query = "SELECT DISTINCT DS.[Designation_ID], DS.[Designation] FROM [Designation] DS 
                INNER JOIN [Employee] E ON DS.[Designation_ID] = E.[Designation]
                INNER JOIN [Ration_Issue_Log] RIL ON E.[Employee_ID] = RIL.[Employee_ID]
                ORDER BY DS.[Designation]";
$designations = db()->get_results($desig_query);

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
                    <div class="float-right">
                        <button class="btn btn-sm btn-success" onclick="exportToExcel()">
                            <i class="fa fa-file-excel"></i> Export to Excel
                        </button>
                    </div>
                </div>
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Content Start -->
    <section class="content">

        <!-- Filter Card -->
        <div class="card card-info card-outline mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-filter"></i> Filters</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body pb-0">
                <!-- Row 1: 4 filters -->
                <div class="row">
                    <div class="col-sm-3 form-group">
                        <label for="filter_employee">Employee ID</label>
                        <input type="text" class="form-control form-control-sm" id="filter_employee" placeholder="e.g. 1001">
                    </div>
                    <div class="col-sm-3 form-group">
                        <label for="filter_year">Year</label>
                        <select class="form-control form-control-sm" id="filter_year">
                            <option value="">All Years</option>
                            <option value="2026">2026</option>
                            <option value="2025">2025</option>
                        </select>
                    </div>
                    <div class="col-sm-3 form-group">
                        <label for="filter_department">Department</label>
                        <select class="form-control form-control-sm" id="filter_department">
                            <option value="">All Departments</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept->Department_ID; ?>"><?php echo $dept->Department; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-3 form-group">
                        <label for="filter_designation">Designation</label>
                        <select class="form-control form-control-sm" id="filter_designation">
                            <option value="">All Designations</option>
                            <?php foreach ($designations as $desig): ?>
                                <option value="<?php echo $desig->Designation_ID; ?>"><?php echo $desig->Designation; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <!-- Row 2: 2 filters + 2 block buttons -->
                <div class="row">
                    <div class="col-sm-3 form-group">
                        <label for="filter_status">Status</label>
                        <select class="form-control form-control-sm" id="filter_status">
                            <option value="">All Status</option>
                            <option value="Eligible">Eligible (Not Received)</option>
                            <option value="Issued">Issued (Received)</option>
                        </select>
                    </div>
                    <div class="col-sm-3 form-group">
                        <label for="filter_issue_type">Issue Type</label>
                        <select class="form-control form-control-sm" id="filter_issue_type">
                            <option value="">All Types</option>
                            <option value="Oil">Oil</option>
                            <option value="Ghee">Ghee</option>
                            <option value="Both">Both</option>
                        </select>
                    </div>
                    <div class="col-sm-3 form-group">
                        <label>&nbsp;</label>
                        <a type="button" href="javascript:void(0);" id="apply_filters" class="btn btn-info btn-sm btn-block" title="Apply Filters">
                            <i class="fa fa-search"></i> Apply Filters
                        </a>
                    </div>
                    <div class="col-sm-3 form-group">
                        <label>&nbsp;</label>
                        <a type="button" href="javascript:void(0);" id="reset_filters" class="btn btn-sm btn-default btn-block" title="Reset">
                            <i class="fa fa-sync"></i> Reset
                        </a>
                    </div>
                </div>
            </div>
        </div>



            <!-- Summary Cards -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted">Total Records</h6>
                            <h3 class="mb-0" id="total_records">0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted text-success">Issued (Received)</h6>
                            <h3 class="mb-0 text-success" id="total_issued">0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted text-warning">Eligible (Not Received)</h6>
                            <h3 class="mb-0 text-warning" id="total_eligible">0</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <div class="card">
                <div class="card-body">
                    <!-- Form List Start -->
                    <table id="issue_log_table" class="table table-hover table-sm" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>CNIC</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>Year</th>
                                <th>Status</th>
                                <th>Issue Type</th>
                                <th>Issue Date/Time</th>
                                <th>Issue By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via AJAX -->
                        </tbody>
                    </table>
                    <!-- Form List End -->
                </div>
            </div>
    </section>
    <!-- Content End -->

</div>
<!-- Content Wrapper End -->
<?php include realpath(__DIR__ . '/../') . '/_inc/template/partial/footer.php'; ?>
