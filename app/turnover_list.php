<?php
ob_start();
include("../_init.php");

// Redirect, If user is not logged in
if (!is_loggedin()) {
    redirect(root_url() . '/index.php');
}

// Redirect, If User has not Read Permission
if (user_role_id() != 1 && !has_permission(1, 'read_turnover_form')) {
    redirect(root_url() . '/' . APPDIRNAME . '/dashboard.php');
}

// Add Script and Style
$document->addScript('../assets/app/js/Controller/TurnoverListController.js?v=1');

// Set Document Title
$document->setTitle("Employee Turnover List");

// Include Header and Footer
include realpath(__DIR__ . '/../') . '/_inc/template/partial/header.php';
include realpath(__DIR__ . '/../') . '/_inc/template/partial/sidebar.php';

// LOAD DICTIONARIES
$dictModel = registry()->get('loader')->model('dictionary');
$departments = $dictModel->getDepartments();
$designations = $dictModel->getDesignations();
$locations = registry()->get('loader')->model('location')->getLocations();
$categories = registry()->get('loader')->model('employee_category')->getEmployeeCategories();
$resTypes = registry()->get('loader')->model('resignation_type')->getResignationTypes();
$reasons = registry()->get('loader')->model('reason_of_turnover')->getReasonOfTurnovers();
?>

<style>
    .modal-xl { max-width: 900px; }
    .error-message {
        color: red;
        font-size: 14px;
        display: block;
    }
</style>

<!-- Content Wrapper Start -->
<div class="content-wrapper">

    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <!-- <h1 class="m-0">Employee Turnover List</h1> -->
                </div>
            </div>
        </div>
    </div>

    <!-- Content Start -->
    <section class="content">
        <div class="row mb-2">
            <div class="col-sm-3">
                <input type="text" class="form-control" id="searchInput" placeholder="Search (ID, Name, Dept, Desig)">
            </div>
            <div class="col-sm-2">
                <a href="javascript:void(0);" id="apply-filter" class="btn btn-info" title="Filter">
                    <i class="fa fa-filter"></i> Filter
                </a>
                <a href="employee_turnover.php" class="btn btn-success" title="Add New">
                    <i class="fa fa-plus"></i> Add New
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <table id="list" class="table dataTable table-valign-middle table-hover table-sm" data-hide-colums="" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Designation</th>
                                    <th>Location</th>
                                    <th>Date of Joining</th>
                                    <th>Date of Leaving</th>
                                    <th>Resignation Type</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white"><i class="fas fa-eye"></i> View Employee Turnover</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="viewModalBody">
                <!-- Filled by JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="viewOpenPdf" style="display:none;"><i class="fas fa-file-pdf"></i> Open PDF</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white"><i class="fas fa-edit"></i> Edit Employee Turnover</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="edit-form" enctype="multipart/form-data">
                <input type="hidden" name="action_type" value="UPDATE">
                <input type="hidden" name="Scan" id="edit_Scan">
                <?php
                $turnoverForm = [
                    'render_header' => false,
                    'render_form' => false,
                    'render_footer' => false,
                    'body_class' => 'modal-body',
                    'field_prefix' => 'edit_',
                    'error_prefix' => 'edit-',
                    'select_class' => 'form-control form-control-sm',
                    'employee_id_readonly' => true,
                    'include_file_upload' => false,
                ];
                include realpath(__DIR__ . '/../') . '/_inc/template/form/employee_turnover_form.php';
                ?>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="update-submit">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload PDF Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-upload"></i> Upload PDF</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="upload-form" enctype="multipart/form-data">
                <input type="hidden" name="action_type" value="UPLOAD_SCAN">
                <input type="hidden" name="Employee_ID" id="upload_Employee_ID">
                <div class="modal-body">
                    <div class="form-group">
                        <label style="font-size:14px">Select PDF File: <span style="color:red;">*</span></label>
                        <div class="custom-file">
                            <input type="file" name="Scan" class="custom-file-input" id="upload_Scan" accept=".pdf" required>
                            <label class="custom-file-label" for="upload_Scan" id="upload_Scan_label">Choose file</label>
                        </div>
                        <div class="error-message" id="upload-scan-error"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" id="upload-submit">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include realpath(__DIR__ . '/../') . '/_inc/template/partial/footer.php'; ?>

<script>
// Update custom file input label
document.getElementById('upload_Scan').addEventListener('change', function() {
    var fileName = this.files[0] ? this.files[0].name : 'Choose file';
    document.getElementById('upload_Scan_label').textContent = fileName;
});
</script>
