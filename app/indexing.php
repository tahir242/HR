<?php
ob_start();
include("../_init.php");

// Redirect, If user is not logged in
if (!is_loggedin()) {
    redirect(root_url() . '/index.php');
}

// Redirect, If User has not Read Permission
if (user_role_id() != 1 && !has_permission(1, 'read_demographic')) {
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
$imodel = registry()->get('loader')->model('indexing');
$dictModel = registry()->get('loader')->model('dictionary');

$pdfrow = $imodel->getpdfforindexing();

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
        height: 750px;
    }

    .error-message {
        color: red;
        font-size: 14px;
        display: block;
        /* margin-top: 5px; */
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
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="Employee_ID" style="font-size:14px">Employee ID: <span style="color: red;">*</span></label>
                                    <button type="button" class="btn btn-sm btn-secondary p-0 px-1 float-right" id="missing-id" style="font-size: 10px;">Fill Missing</button>
                                    <input type="text" name="Employee_ID" value="<?php echo htmlspecialchars((string)$pdfrow->Employee_ID); ?>" class="form-control form-control-sm" id="Employee_ID" placeholder="Employee ID" tabindex="1" autofocus autocomplete="off">
                                    <div class="error-message" id="employee-id"></div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="Employee_Name" style="font-size:14px">Employee Name: <span style="color: red;">*</span></label>
                                    <input type="text" name="Employee_Name" value="<?php echo htmlspecialchars((string)$pdfrow->Name); ?>" class="form-control form-control-sm" id="Employee_Name" placeholder="Employee Name" tabindex="2" required autocomplete="off">
                                    <div class="error-message" id="employee-name"></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="Gender" style="font-size:14px">Gender:</label>
                                    <select name="Gender" id="Gender" class="form-control form-control-sm tom-select" tabindex="3">
                                        <option value="">Select Gender</option>
                                        <option value="Male" <?php echo $pdfrow->Gender == 'Male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo $pdfrow->Gender == 'Female' ? 'selected' : ''; ?>>Female</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="Date_of_Birth" style="font-size:14px">Date of Birth:</label>
                                    <input type="text" name="Date_of_Birth" value="<?php echo $pdfrow->Date_of_Birth ? date_normalizer($pdfrow->Date_of_Birth, "d-m-Y") : ""; ?>" class="form-control form-control-sm" id="Date_of_Birth" placeholder="DD-MM-YYYY" tabindex="4" oninput="formatDate(this)" autocomplete="off">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <label for="Department" style="font-size:14px">Department:</label>
                                    <select name="Department" id="Department" class="form-control form-control-sm tom-select" tabindex="5">
                                        <option value="">Select Department</option>
                                        <?php foreach ($departments as $dept): ?>
                                            <option value="<?php echo $dept->Department_ID; ?>" <?php echo $pdfrow->Department == $dept->Department_ID ? 'selected' : ''; ?>><?php echo htmlspecialchars((string)$dept->Department); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-12 form-group">
                                    <label for="Designation" style="font-size:14px">Designation:</label>
                                    <select name="Designation" id="Designation" class="form-control form-control-sm tom-select" tabindex="6">
                                        <option value="">Select Designation</option>
                                        <?php foreach ($designations as $desig): ?>
                                            <option value="<?php echo $desig->Designation_ID; ?>" <?php echo $pdfrow->Designation == $desig->Designation_ID ? 'selected' : ''; ?>><?php echo htmlspecialchars((string)$desig->Designation); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <label for="Location" style="font-size:14px">Location:</label>
                                    <select name="Location" id="Location" class="form-control form-control-sm tom-select" tabindex="7">
                                        <option value="">Select Location</option>
                                        <?php foreach ($locations as $loc): ?>
                                            <option value="<?php echo $loc->Location_ID; ?>" <?php echo $pdfrow->Location == $loc->Location_ID ? 'selected' : ''; ?>><?php echo htmlspecialchars((string)$loc->Location); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="DOJ" style="font-size:14px">Date of Joining:</label>
                                    <input type="text" name="DOJ" value="<?php echo $pdfrow->Date_of_Joining ? date_normalizer($pdfrow->Date_of_Joining, "d-m-Y") : ""; ?>" class="form-control form-control-sm" id="DOJ" placeholder="DD-MM-YYYY" tabindex="8" oninput="formatDate(this)" autocomplete="off">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="Date_of_Leaving" style="font-size:14px">Date of Leaving:</label>
                                    <input type="text" name="Date_of_Leaving" value="<?php echo $pdfrow->Date_of_Leaving ? date_normalizer($pdfrow->Date_of_Leaving, "d-m-Y") : ""; ?>" class="form-control form-control-sm" id="Date_of_Leaving" placeholder="DD-MM-YYYY" tabindex="9" oninput="formatDate(this)" autocomplete="off">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <label for="Employee_Category" style="font-size:14px">Employee Category:</label>
                                    <select name="Employee_Category" id="Employee_Category" class="form-control form-control-sm tom-select" tabindex="10">
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat->Category_ID; ?>" <?php echo $pdfrow->Employee_Category == $cat->Category_ID ? 'selected' : ''; ?>><?php echo htmlspecialchars((string)$cat->Employee_Category); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="Resignation_Type" style="font-size:14px">Resignation Type:</label>
                                    <select name="Resignation_Type" id="Resignation_Type" class="form-control form-control-sm tom-select" tabindex="11">
                                        <option value="">Select Type</option>
                                        <?php foreach ($resTypes as $rt): ?>
                                            <option value="<?php echo $rt->Resignation_Type_ID; ?>" <?php echo $pdfrow->Resignation_Type == $rt->Resignation_Type_ID ? 'selected' : ''; ?>><?php echo htmlspecialchars((string)$rt->Resignation_Type); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="Reason_of_Turnover" style="font-size:14px">Reason of Turnover:</label>
                                    <select name="Reason_of_Turnover" id="Reason_of_Turnover" class="form-control form-control-sm tom-select" tabindex="12" placeholder="Select Reason">
                                        <option value="">Select Reason</option>
                                        <?php 
                                        $filtered_reasons = [];
                                        if($pdfrow->Resignation_Type) {
                                            foreach($reasons as $reason) {
                                                if($reason->Resignation_Type_ID == $pdfrow->Resignation_Type) {
                                                    $filtered_reasons[] = $reason;
                                                }
                                            }
                                        }
                                        foreach ($filtered_reasons as $reason): ?>
                                            <option value="<?php echo $reason->Reason_ID; ?>" <?php echo $pdfrow->Reason_of_Turnover == $reason->Reason_ID ? 'selected' : ''; ?>><?php echo htmlspecialchars((string)$reason->Reason); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="Remarks" style="font-size:14px">Remarks:</label>
                                <textarea name="Remarks" id="Remarks" class="form-control form-control-sm" oninput="validateCharacters(this, 500);" rows="3" tabindex="13" placeholder="Enter any remarks here..."><?php echo htmlspecialchars((string)$pdfrow->Remarks); ?></textarea>
                            </div>

                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary" id="create-submit" data-datatable="#list" name="create-submit" data-form="#create-form" data-loading-text="Saving..." tabindex="14">Save & Next</button>
                            <button type="reset" id="reset" name="reset" class="btn btn-danger" tabindex="15">Reset</button>
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


