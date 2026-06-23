<form id="form-delete" action="Resignation_Type.php" method="post">
    <input type="hidden" name="action_type" value="DELETE">
    <input type="hidden" name="Resignation_Type_ID" value="<?php echo $Resignation_Type_ID; ?>">
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-warning">
                Are you sure you want to delete <strong><?php echo $Resignation_Type->Resignation_Type; ?></strong>?
            </div>
            <div class="form-group">
                <label>Shift Data To (Optional if not assigned) <span class="text-danger">*</span></label>
                <select name="Shift_Resignation_Type_ID" class="form-control">
                    <option value="">Select Resignation Type</option>
                    <?php foreach($Resignation_Types as $loc): ?>
                        <?php if($loc->Resignation_Type_ID != $Resignation_Type_ID && $loc->Active == 1): ?>
                            <option value="<?php echo $loc->Resignation_Type_ID; ?>"><?php echo $loc->Resignation_Type; ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">Any existing employees with this Resignation Type will be shifted to the selected Resignation_Type before deletion.</small>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-12 text-right">
            <button type="button" class="btn btn-danger" id="delete-submit">Delete & Shift Data</button>
        </div>
    </div>
</form>
