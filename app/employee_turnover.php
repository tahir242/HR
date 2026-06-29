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

                    <?php
                    $turnoverForm = [
                        'card_title' => 'Employee Turnover Form',
                        'card_subtitle' => 'press (Alt + S) to submit the form',
                        'form_action' => 'employee_turnover.php',
                        'submit_label' => 'Save',
                        'include_file_upload' => true,
                    ];
                    include realpath(__DIR__ . '/../') . '/_inc/template/form/employee_turnover_form.php';
                    ?>
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
