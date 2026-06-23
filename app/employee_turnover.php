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
$document->addScript('../assets/app/js/Controller/TurnoverController.js?v=1');

// Set Document Title
$document->setTitle("Employee Turnover Form");

// Include Header and Footer
include realpath(__DIR__ . '/../') . '/_inc/template/partial/header.php';
include realpath(__DIR__ . '/../') . '/_inc/template/partial/sidebar.php';

// LOAD MODEL 
$dictModel = registry()->get('loader')->model('dictionary');

// LOAD DICTIONARIES
$departments = $dictModel->getDepartments();
$designations = $dictModel->getDesignations();
$locations = registry()->get('loader')->model('location')->getLocations();
$categories = registry()->get('loader')->model('employee_category')->getEmployeeCategories();
$resTypes = registry()->get('loader')->model('resignation_type')->getResignationTypes();
$reasons = registry()->get('loader')->model('reason_of_turnover')->getReasonOfTurnovers();
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

    .error-message {
        color: red;
        font-size: 14px;
        display: block;
    }
</style>

<!-- Content Wrapper Start -->
<div class="content-wrapper">

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <!-- <h1 class="m-0">Employee Turnover Form</h1> -->
                </div>
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Content Start -->
    <section class="content">
        <div class="row">
            <div class="col-sm-8 offset-sm-2">
                <div class="card">

                    <div class="card-header">
                        <h3 class="card-title">
                            <b>Employee Turnover Form</b> <small>press (Alt + S) to submit the form</small>
                        </h3>
                    </div>
                    <form id="create-form" action="employee_turnover.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action_type" value="CREATE">
                        
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="Employee_ID" style="font-size:14px">Employee ID: <span style="color: red;">*</span></label>
                                    <input type="text" name="Employee_ID" value="" class="form-control form-control-sm" id="Employee_ID" placeholder="Employee ID" tabindex="1" autofocus autocomplete="off">
                                    <div class="error-message" id="employee-id"></div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="Employee_Name" style="font-size:14px">Employee Name: <span style="color: red;">*</span></label>
                                    <input type="text" name="Employee_Name" value="" class="form-control form-control-sm" id="Employee_Name" placeholder="Employee Name" tabindex="2" required autocomplete="off">
                                    <div class="error-message" id="employee-name"></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="Gender" style="font-size:14px">Gender: <span style="color: red;">*</span></label>
                                    <select name="Gender" id="Gender" class="form-control form-control-sm tom-select" tabindex="3">
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="Date_of_Birth" style="font-size:14px">Date of Birth: <span style="color: red;">*</span></label>
                                    <input type="text" name="Date_of_Birth" value="" class="form-control form-control-sm" id="Date_of_Birth" placeholder="DD-MM-YYYY" tabindex="4" oninput="formatDate(this)" autocomplete="off">
                                    <div class="error-message" id="employee-dob"></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <label for="Department" style="font-size:14px">Department: <span style="color: red;">*</span></label>
                                    <select name="Department" id="Department" class="form-control form-control-sm tom-select" tabindex="5">
                                        <option value="">Select Department</option>
                                        <?php foreach ($departments as $dept): ?>
                                            <option value="<?php echo $dept->Department_ID; ?>"><?php echo htmlspecialchars((string)$dept->Department); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-12 form-group">
                                    <label for="Designation" style="font-size:14px">Designation: <span style="color: red;">*</span></label>
                                    <select name="Designation" id="Designation" class="form-control form-control-sm tom-select" tabindex="6">
                                        <option value="">Select Designation</option>
                                        <?php foreach ($designations as $desig): ?>
                                            <option value="<?php echo $desig->Designation_ID; ?>"><?php echo htmlspecialchars((string)$desig->Designation); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <label for="Location" style="font-size:14px">Location: <span style="color: red;">*</span></label>
                                    <select name="Location" id="Location" class="form-control form-control-sm tom-select" tabindex="7">
                                        <option value="">Select Location</option>
                                        <?php foreach ($locations as $loc): ?>
                                            <option value="<?php echo $loc->Location_ID; ?>"><?php echo htmlspecialchars((string)$loc->Location); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="DOJ" style="font-size:14px">Date of Joining: <span style="color: red;">*</span></label>
                                    <input type="text" name="DOJ" value="" class="form-control form-control-sm" id="DOJ" placeholder="DD-MM-YYYY" tabindex="8" oninput="formatDate(this)" autocomplete="off">
                                    <div class="error-message" id="employee-doj"></div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="Date_of_Leaving" style="font-size:14px">Date of Leaving: <span style="color: red;">*</span></label>
                                    <input type="text" name="Date_of_Leaving" value="" class="form-control form-control-sm" id="Date_of_Leaving" placeholder="DD-MM-YYYY" tabindex="9" oninput="formatDate(this)" autocomplete="off">
                                    <div class="error-message" id="employee-dol"></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <label for="Employee_Category" style="font-size:14px">Employee Category: <span style="color: red;">*</span></label>
                                    <select name="Employee_Category" id="Employee_Category" class="form-control form-control-sm tom-select" tabindex="10">
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat->Category_ID; ?>"><?php echo htmlspecialchars((string)$cat->Employee_Category); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="Resignation_Type" style="font-size:14px">Resignation Type: <span style="color: red;">*</span></label>
                                    <select name="Resignation_Type" id="Resignation_Type" class="form-control form-control-sm tom-select" tabindex="11">
                                        <option value="">Select Type</option>
                                        <?php foreach ($resTypes as $rt): ?>
                                            <option value="<?php echo $rt->Resignation_Type_ID; ?>"><?php echo htmlspecialchars((string)$rt->Resignation_Type); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="Reason_of_Turnover" style="font-size:14px">Reason of Turnover: <span style="color: red;">*</span></label>
                                    <select name="Reason_of_Turnover" id="Reason_of_Turnover" class="form-control form-control-sm tom-select" tabindex="12" placeholder="Select Reason">
                                        <option value="">Select Reason</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="Remarks" style="font-size:14px">Remarks:</label>
                                <textarea name="Remarks" id="Remarks" class="form-control form-control-sm" oninput="validateCharacters(this, 500);" rows="3" tabindex="13" placeholder="Enter any remarks here..."></textarea>
                            </div>

                            <div class="form-group">
                                <label for="Scan" style="font-size:14px">Upload PDF: <small class="text-muted">(Optional)</small></label>
                                <div class="custom-file">
                                    <input type="file" name="Scan" class="custom-file-input" id="Scan" accept=".pdf" tabindex="14">
                                    <label class="custom-file-label" for="Scan">Choose file</label>
                                </div>
                                <div class="error-message" id="scan-error"></div>
                            </div>

                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary" id="create-submit" data-datatable="#list" name="create-submit" data-form="#create-form" data-loading-text="Saving..." tabindex="15">Save</button>
                            <button type="reset" id="reset" name="reset" class="btn btn-danger" tabindex="16">Reset</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

    </section>
    <!-- Content End -->
</div>
<!-- Content Wrapper End -->
<?php include realpath(__DIR__ . '/../') . '/_inc/template/partial/footer.php'; ?>

<script>
// Update custom file input label with selected filename
document.getElementById('Scan').addEventListener('change', function() {
    var fileName = this.files[0] ? this.files[0].name : 'Choose file';
    this.nextElementSibling.textContent = fileName;
});
</script>
