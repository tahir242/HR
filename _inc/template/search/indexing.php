<?php
$dictModel = registry()->get('loader')->model('dictionary');
$departments = $dictModel->getDepartments();
$designations = $dictModel->getDesignations();
$locations = registry()->get('loader')->model('location')->getLocations();
$categories = registry()->get('loader')->model('employee_category')->getEmployeeCategories();
$resTypes = registry()->get('loader')->model('resignation_type')->getResignationTypes();
$reasons = registry()->get('loader')->model('reason_of_turnover')->getReasonOfTurnovers();

$turnoverForm = [
    'card_title' => 'Update Demographic',
    'card_subtitle' => '',
    'card_tools_html' => '<div class="card-tools pull-right"><a href="JavaScript:void(0);" class="btn btn-primary btn-xs search-button"><i class="fa-solid fa-arrows-rotate"></i> Searching Field(s)</a></div>',
    'form_action' => 'indexing.php',
    'hidden_inputs' => [
        'Scan' => $scan,
    ],
    'show_missing_id' => true,
    'include_file_upload' => false,
    'submit_id' => 'update-submit',
    'submit_name' => 'update-submit',
    'submit_label' => 'Update',
    'submit_tabindex' => 15,
    'reset_tabindex' => 16,
    'values' => [
        'Employee_ID' => $emp->Employee_ID ?? '',
        'Employee_Name' => $emp->Name ?? '',
        'Gender' => $emp->Gender ?? '',
        'Date_of_Birth' => !empty($emp->Date_of_Birth) ? date_normalizer($emp->Date_of_Birth, "d-m-Y") : '',
        'Department' => $emp->Department ?? '',
        'Designation' => $emp->Designation ?? '',
        'Location' => $emp->Location ?? '',
        'DOJ' => !empty($emp->Date_of_Joining) ? date_normalizer($emp->Date_of_Joining, "d-m-Y") : '',
        'Date_of_Leaving' => !empty($emp->Date_of_Leaving) ? date_normalizer($emp->Date_of_Leaving, "d-m-Y") : '',
        'Employee_Category' => $emp->Employee_Category ?? '',
        'Resignation_Type' => $emp->Resignation_Type ?? '',
        'Reason_of_Turnover' => $emp->Reason_of_Turnover ?? '',
        'Remarks' => $emp->Remarks ?? '',
    ],
];
?>
<div class="card">
    <?php include __DIR__ . '/../form/employee_turnover_form.php'; ?>
</div>
