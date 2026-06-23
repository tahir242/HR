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
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label style="font-size:14px">Employee ID: <span style="color:red;">*</span></label>
                            <input type="text" name="Employee_ID" class="form-control form-control-sm" id="edit_Employee_ID" readonly>
                        </div>
                        <div class="col-md-6 form-group">
                            <label style="font-size:14px">Employee Name: <span style="color:red;">*</span></label>
                            <input type="text" name="Employee_Name" class="form-control form-control-sm" id="edit_Employee_Name">
                            <div class="error-message" id="edit-employee-name"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label style="font-size:14px">Gender: <span style="color:red;">*</span></label>
                            <select name="Gender" id="edit_Gender" class="form-control form-control-sm">
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label style="font-size:14px">Date of Birth: <span style="color:red;">*</span></label>
                            <input type="text" name="Date_of_Birth" class="form-control form-control-sm" id="edit_Date_of_Birth" placeholder="DD-MM-YYYY" oninput="formatDate(this)">
                            <div class="error-message" id="edit-employee-dob"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label style="font-size:14px">Department: <span style="color:red;">*</span></label>
                            <select name="Department" id="edit_Department" class="form-control form-control-sm">
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept->Department_ID; ?>"><?php echo htmlspecialchars((string)$dept->Department); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label style="font-size:14px">Designation: <span style="color:red;">*</span></label>
                            <select name="Designation" id="edit_Designation" class="form-control form-control-sm">
                                <option value="">Select Designation</option>
                                <?php foreach ($designations as $desig): ?>
                                    <option value="<?php echo $desig->Designation_ID; ?>"><?php echo htmlspecialchars((string)$desig->Designation); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label style="font-size:14px">Location: <span style="color:red;">*</span></label>
                            <select name="Location" id="edit_Location" class="form-control form-control-sm">
                                <option value="">Select Location</option>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?php echo $loc->Location_ID; ?>"><?php echo htmlspecialchars((string)$loc->Location); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label style="font-size:14px">Date of Joining: <span style="color:red;">*</span></label>
                            <input type="text" name="DOJ" class="form-control form-control-sm" id="edit_DOJ" placeholder="DD-MM-YYYY" oninput="formatDate(this)">
                            <div class="error-message" id="edit-employee-doj"></div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label style="font-size:14px">Date of Leaving: <span style="color:red;">*</span></label>
                            <input type="text" name="Date_of_Leaving" class="form-control form-control-sm" id="edit_Date_of_Leaving" placeholder="DD-MM-YYYY" oninput="formatDate(this)">
                            <div class="error-message" id="edit-employee-dol"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label style="font-size:14px">Employee Category: <span style="color:red;">*</span></label>
                            <select name="Employee_Category" id="edit_Employee_Category" class="form-control form-control-sm">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat->Category_ID; ?>"><?php echo htmlspecialchars((string)$cat->Employee_Category); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label style="font-size:14px">Resignation Type: <span style="color:red;">*</span></label>
                            <select name="Resignation_Type" id="edit_Resignation_Type" class="form-control form-control-sm">
                                <option value="">Select Type</option>
                                <?php foreach ($resTypes as $rt): ?>
                                    <option value="<?php echo $rt->Resignation_Type_ID; ?>"><?php echo htmlspecialchars((string)$rt->Resignation_Type); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label style="font-size:14px">Reason of Turnover: <span style="color:red;">*</span></label>
                            <select name="Reason_of_Turnover" id="edit_Reason_of_Turnover" class="form-control form-control-sm">
                                <option value="">Select Reason</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label style="font-size:14px">Remarks:</label>
                        <textarea name="Remarks" id="edit_Remarks" class="form-control form-control-sm" rows="3"></textarea>
                    </div>
                </div>
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
