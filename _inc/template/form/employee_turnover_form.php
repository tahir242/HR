<?php
// Shared employee turnover form markup. Configure with $turnoverForm before including.
$turnoverForm = array_replace_recursive([
    'render_header' => true,
    'render_form' => true,
    'render_footer' => true,
    'body_class' => 'card-body',
    'form_id' => 'create-form',
    'form_action' => 'employee_turnover.php',
    'action_type' => 'CREATE',
    'card_title' => 'Employee Turnover Form',
    'card_subtitle' => 'press (Alt + S) to submit the form',
    'card_tools_html' => '',
    'field_prefix' => '',
    'error_prefix' => '',
    'select_class' => 'form-control form-control-sm tom-select',
    'employee_id_readonly' => false,
    'show_missing_id' => false,
    'include_file_upload' => true,
    'hidden_inputs' => [],
    'submit_id' => 'create-submit',
    'submit_name' => 'create-submit',
    'submit_label' => 'Save',
    'submit_tabindex' => 15,
    'reset_tabindex' => 16,
    'values' => [],
], isset($turnoverForm) && is_array($turnoverForm) ? $turnoverForm : []);

$tfh = function ($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
};
$tfv = function ($key) use ($turnoverForm) {
    return $turnoverForm['values'][$key] ?? '';
};
$tfid = function ($key) use ($turnoverForm) {
    return $turnoverForm['field_prefix'] . $key;
};
$tferr = function ($key) use ($turnoverForm) {
    return $turnoverForm['error_prefix'] . $key;
};
$tfselected = function ($key, $value) use ($tfv) {
    return (string)$tfv($key) === (string)$value ? 'selected' : '';
};
$tfrequired = '<span style="color: red;">*</span>';
?>

<?php if ($turnoverForm['render_header']): ?>
    <div class="card-header">
        <h3 class="card-title">
            <b><?php echo $tfh($turnoverForm['card_title']); ?></b>
            <?php if ($turnoverForm['card_subtitle']): ?>
                <small><?php echo $tfh($turnoverForm['card_subtitle']); ?></small>
            <?php endif; ?>
        </h3>
        <?php if ($turnoverForm['card_tools_html']): ?>
            <?php echo $turnoverForm['card_tools_html']; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($turnoverForm['render_form']): ?>
    <form id="<?php echo $tfh($turnoverForm['form_id']); ?>" action="<?php echo $tfh($turnoverForm['form_action']); ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action_type" value="<?php echo $tfh($turnoverForm['action_type']); ?>">
        <?php foreach ($turnoverForm['hidden_inputs'] as $name => $value): ?>
            <input type="hidden" name="<?php echo $tfh($name); ?>" value="<?php echo $tfh($value); ?>">
        <?php endforeach; ?>
<?php endif; ?>

<div class="<?php echo $tfh($turnoverForm['body_class']); ?>">
    <div class="row">
        <div class="col-md-6 form-group">
            <label for="<?php echo $tfh($tfid('Employee_ID')); ?>" style="font-size:14px">Employee ID: <?php echo $tfrequired; ?></label>
            <?php if ($turnoverForm['show_missing_id']): ?>
                <button type="button" class="btn btn-sm btn-secondary p-0 px-1 float-right" id="missing-id" style="font-size: 10px;">Fill Missing</button>
            <?php endif; ?>
            <input type="text" name="Employee_ID" value="<?php echo $tfh($tfv('Employee_ID')); ?>" class="form-control form-control-sm" id="<?php echo $tfh($tfid('Employee_ID')); ?>" placeholder="Employee ID" tabindex="1" <?php echo $turnoverForm['employee_id_readonly'] ? 'readonly' : 'autofocus'; ?> autocomplete="off">
            <div class="error-message" id="<?php echo $tfh($tferr('employee-id')); ?>"></div>
        </div>
        <div class="col-md-6 form-group">
            <label for="<?php echo $tfh($tfid('Employee_Name')); ?>" style="font-size:14px">Employee Name: <?php echo $tfrequired; ?></label>
            <input type="text" name="Employee_Name" value="<?php echo $tfh($tfv('Employee_Name')); ?>" class="form-control form-control-sm" id="<?php echo $tfh($tfid('Employee_Name')); ?>" placeholder="Employee Name" tabindex="2" required autocomplete="off">
            <div class="error-message" id="<?php echo $tfh($tferr('employee-name')); ?>"></div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            <label for="<?php echo $tfh($tfid('Gender')); ?>" style="font-size:14px">Gender: <?php echo $tfrequired; ?></label>
            <select name="Gender" id="<?php echo $tfh($tfid('Gender')); ?>" class="<?php echo $tfh($turnoverForm['select_class']); ?>" tabindex="3">
                <option value="">Select Gender</option>
                <option value="Male" <?php echo $tfselected('Gender', 'Male'); ?>>Male</option>
                <option value="Female" <?php echo $tfselected('Gender', 'Female'); ?>>Female</option>
            </select>
        </div>
        <div class="col-md-6 form-group">
            <label for="<?php echo $tfh($tfid('Date_of_Birth')); ?>" style="font-size:14px">Date of Birth: <?php echo $tfrequired; ?></label>
            <input type="text" name="Date_of_Birth" value="<?php echo $tfh($tfv('Date_of_Birth')); ?>" class="form-control form-control-sm" id="<?php echo $tfh($tfid('Date_of_Birth')); ?>" placeholder="DD-MM-YYYY" tabindex="4" oninput="formatDate(this)" autocomplete="off">
            <div class="error-message" id="<?php echo $tfh($tferr('employee-dob')); ?>"></div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 form-group">
            <label for="<?php echo $tfh($tfid('Department')); ?>" style="font-size:14px">Department: <?php echo $tfrequired; ?></label>
            <select name="Department" id="<?php echo $tfh($tfid('Department')); ?>" class="<?php echo $tfh($turnoverForm['select_class']); ?>" tabindex="5">
                <option value="">Select Department</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?php echo $tfh($dept->Department_ID); ?>" <?php echo $tfselected('Department', $dept->Department_ID); ?>><?php echo $tfh($dept->Department); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-12 form-group">
            <label for="<?php echo $tfh($tfid('Designation')); ?>" style="font-size:14px">Designation: <?php echo $tfrequired; ?></label>
            <select name="Designation" id="<?php echo $tfh($tfid('Designation')); ?>" class="<?php echo $tfh($turnoverForm['select_class']); ?>" tabindex="6">
                <option value="">Select Designation</option>
                <?php foreach ($designations as $desig): ?>
                    <option value="<?php echo $tfh($desig->Designation_ID); ?>" <?php echo $tfselected('Designation', $desig->Designation_ID); ?>><?php echo $tfh($desig->Designation); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 form-group">
            <label for="<?php echo $tfh($tfid('Location')); ?>" style="font-size:14px">Location: <?php echo $tfrequired; ?></label>
            <select name="Location" id="<?php echo $tfh($tfid('Location')); ?>" class="<?php echo $tfh($turnoverForm['select_class']); ?>" tabindex="7">
                <option value="">Select Location</option>
                <?php foreach ($locations as $loc): ?>
                    <option value="<?php echo $tfh($loc->Location_ID); ?>" <?php echo $tfselected('Location', $loc->Location_ID); ?>><?php echo $tfh($loc->Location); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            <label for="<?php echo $tfh($tfid('DOJ')); ?>" style="font-size:14px">Date of Joining: <?php echo $tfrequired; ?></label>
            <input type="text" name="DOJ" value="<?php echo $tfh($tfv('DOJ')); ?>" class="form-control form-control-sm" id="<?php echo $tfh($tfid('DOJ')); ?>" placeholder="DD-MM-YYYY" tabindex="8" oninput="formatDate(this)" autocomplete="off">
            <div class="error-message" id="<?php echo $tfh($tferr('employee-doj')); ?>"></div>
        </div>
        <div class="col-md-6 form-group">
            <label for="<?php echo $tfh($tfid('Date_of_Leaving')); ?>" style="font-size:14px">Date of Leaving: <?php echo $tfrequired; ?></label>
            <input type="text" name="Date_of_Leaving" value="<?php echo $tfh($tfv('Date_of_Leaving')); ?>" class="form-control form-control-sm" id="<?php echo $tfh($tfid('Date_of_Leaving')); ?>" placeholder="DD-MM-YYYY" tabindex="9" oninput="formatDate(this)" autocomplete="off">
            <div class="error-message" id="<?php echo $tfh($tferr('employee-dol')); ?>"></div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 form-group">
            <label for="<?php echo $tfh($tfid('Employee_Category')); ?>" style="font-size:14px">Employee Category: <?php echo $tfrequired; ?></label>
            <select name="Employee_Category" id="<?php echo $tfh($tfid('Employee_Category')); ?>" class="<?php echo $tfh($turnoverForm['select_class']); ?>" tabindex="10">
                <option value="">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $tfh($cat->Category_ID); ?>" <?php echo $tfselected('Employee_Category', $cat->Category_ID); ?>><?php echo $tfh($cat->Employee_Category); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            <label for="<?php echo $tfh($tfid('Resignation_Type')); ?>" style="font-size:14px">Resignation Type: <?php echo $tfrequired; ?></label>
            <select name="Resignation_Type" id="<?php echo $tfh($tfid('Resignation_Type')); ?>" class="<?php echo $tfh($turnoverForm['select_class']); ?>" tabindex="11">
                <option value="">Select Type</option>
                <?php foreach ($resTypes as $rt): ?>
                    <option value="<?php echo $tfh($rt->Resignation_Type_ID); ?>" <?php echo $tfselected('Resignation_Type', $rt->Resignation_Type_ID); ?>><?php echo $tfh($rt->Resignation_Type); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6 form-group">
            <label for="<?php echo $tfh($tfid('Reason_of_Turnover')); ?>" style="font-size:14px">Reason of Turnover: <?php echo $tfrequired; ?></label>
            <select name="Reason_of_Turnover" id="<?php echo $tfh($tfid('Reason_of_Turnover')); ?>" class="<?php echo $tfh($turnoverForm['select_class']); ?>" tabindex="12" placeholder="Select Reason" data-selected-value="<?php echo $tfh($tfv('Reason_of_Turnover')); ?>">
                <option value="">Select Reason</option>
                <?php foreach ($reasons as $reason): ?>
                    <?php if ($tfv('Resignation_Type') && (string)$reason->Resignation_Type_ID === (string)$tfv('Resignation_Type')): ?>
                        <option value="<?php echo $tfh($reason->Reason_ID); ?>" <?php echo $tfselected('Reason_of_Turnover', $reason->Reason_ID); ?>><?php echo $tfh($reason->Reason); ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="<?php echo $tfh($tfid('Remarks')); ?>" style="font-size:14px">Remarks:</label>
        <textarea name="Remarks" id="<?php echo $tfh($tfid('Remarks')); ?>" class="form-control form-control-sm" oninput="validateCharacters(this, 500);" rows="3" tabindex="13" placeholder="Enter any remarks here..."><?php echo $tfh($tfv('Remarks')); ?></textarea>
    </div>

    <?php if ($turnoverForm['include_file_upload']): ?>
        <div class="form-group">
            <label for="<?php echo $tfh($tfid('Scan')); ?>" style="font-size:14px">Upload PDF: <small class="text-muted">(Optional)</small></label>
            <div class="custom-file">
                <input type="file" name="Scan" class="custom-file-input" id="<?php echo $tfh($tfid('Scan')); ?>" accept=".pdf" tabindex="14">
                <label class="custom-file-label" for="<?php echo $tfh($tfid('Scan')); ?>">Choose file</label>
            </div>
            <div class="error-message" id="<?php echo $tfh($tferr('scan-error')); ?>"></div>
        </div>
    <?php endif; ?>
</div>

<?php if ($turnoverForm['render_footer']): ?>
    <div class="card-footer">
        <button type="submit" class="btn btn-primary" id="<?php echo $tfh($turnoverForm['submit_id']); ?>" data-datatable="#list" name="<?php echo $tfh($turnoverForm['submit_name']); ?>" data-form="#<?php echo $tfh($turnoverForm['form_id']); ?>" data-loading-text="Saving..." tabindex="<?php echo $tfh($turnoverForm['submit_tabindex']); ?>"><?php echo $tfh($turnoverForm['submit_label']); ?></button>
        <button type="reset" id="reset" name="reset" class="btn btn-danger" tabindex="<?php echo $tfh($turnoverForm['reset_tabindex']); ?>">Reset</button>
    </div>
<?php endif; ?>

<?php if ($turnoverForm['render_form']): ?>
    </form>
<?php endif; ?>
