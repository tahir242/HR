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

                    <?php
                    $turnoverForm = [
                        'card_title' => 'Entry Field(s)',
                        'card_subtitle' => 'press (Alt + S) to submit the form',
                        'form_action' => 'indexing.php',
                        'hidden_inputs' => [
                            'Scan' => $pdfrow->Scan,
                        ],
                        'show_missing_id' => true,
                        'include_file_upload' => false,
                        'submit_label' => 'Save & Next',
                        'submit_tabindex' => 14,
                        'reset_tabindex' => 15,
                        'values' => [
                            'Employee_ID' => $pdfrow->Employee_ID,
                            'Employee_Name' => $pdfrow->Name,
                            'Gender' => $pdfrow->Gender,
                            'Date_of_Birth' => $pdfrow->Date_of_Birth ? date_normalizer($pdfrow->Date_of_Birth, "d-m-Y") : "",
                            'Department' => $pdfrow->Department,
                            'Designation' => $pdfrow->Designation,
                            'Location' => $pdfrow->Location,
                            'DOJ' => $pdfrow->Date_of_Joining ? date_normalizer($pdfrow->Date_of_Joining, "d-m-Y") : "",
                            'Date_of_Leaving' => $pdfrow->Date_of_Leaving ? date_normalizer($pdfrow->Date_of_Leaving, "d-m-Y") : "",
                            'Employee_Category' => $pdfrow->Employee_Category,
                            'Resignation_Type' => $pdfrow->Resignation_Type,
                            'Reason_of_Turnover' => $pdfrow->Reason_of_Turnover,
                            'Remarks' => $pdfrow->Remarks,
                        ],
                    ];
                    include realpath(__DIR__ . '/../') . '/_inc/template/form/employee_turnover_form.php';
                    ?>
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


