<?php
$Designation_ID = $request->get['Designation_ID'];
$designation = $model->getDesignation($Designation_ID);

$designations = $model->getDesignations(); 
?>
<form id="form-delete" action="designation.php" method="post">
    <input type="hidden" name="action_type" value="DELETE">
    <input type="hidden" name="Designation_ID" value="<?php echo $Designation_ID; ?>">
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-warning">
                Are you sure you want to delete <strong><?php echo $designation->Designation; ?></strong>?
            </div>
            <div class="form-group">
                <label>Shift Data To (Optional if not assigned) <span class="text-danger">*</span></label>
                <select name="Shift_Designation_ID" class="form-control">
                    <option value="">Select Designation</option>
                    <?php foreach($designations as $desg): ?>
                        <?php if($desg->Designation_ID != $Designation_ID && $desg->Active == 1): ?>
                            <option value="<?php echo $desg->Designation_ID; ?>"><?php echo $desg->Designation; ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">Any existing employees with this designation will be shifted to the selected designation before deletion.</small>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-12 text-right">
            <button type="button" class="btn btn-danger" id="delete-submit">Delete & Shift Data</button>
        </div>
    </div>
</form>
