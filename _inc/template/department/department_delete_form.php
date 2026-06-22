<?php
$Department_ID = $request->get['Department_ID'];
$department = $model->getDepartment($Department_ID);

$departments = $model->getDepartments(); 
?>
<form id="form-delete" action="department.php" method="post">
    <input type="hidden" name="action_type" value="DELETE">
    <input type="hidden" name="Department_ID" value="<?php echo $Department_ID; ?>">
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-warning">
                Are you sure you want to delete <strong><?php echo $department->Department; ?></strong>?
            </div>
            <div class="form-group">
                <label>Shift Data To (Optional if not assigned) <span class="text-danger">*</span></label>
                <select name="Shift_Department_ID" class="form-control">
                    <option value="">Select Department</option>
                    <?php foreach($departments as $dept): ?>
                        <?php if($dept->Department_ID != $Department_ID && $dept->Active == 1): ?>
                            <option value="<?php echo $dept->Department_ID; ?>"><?php echo $dept->Department; ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">Any existing employees with this department will be shifted to the selected department before deletion.</small>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-12 text-right">
            <button type="button" class="btn btn-danger" id="delete-submit">Delete & Shift Data</button>
        </div>
    </div>
</form>
