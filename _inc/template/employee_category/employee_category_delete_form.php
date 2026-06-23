<form id="form-delete" action="Employee_Category.php" method="post">
    <input type="hidden" name="action_type" value="DELETE">
    <input type="hidden" name="Category_ID" value="<?php echo $Category_ID; ?>">
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-warning">
                Are you sure you want to delete <strong><?php echo $Employee_Category->Employee_Category; ?></strong>?
            </div>
            <div class="form-group">
                <label>Shift Data To (Optional if not assigned) <span class="text-danger">*</span></label>
                <select name="Shift_Category_ID" class="form-control">
                    <option value="">Select Employee Category</option>
                    <?php foreach($Employee_Categorys as $loc): ?>
                        <?php if($loc->Category_ID != $Category_ID && $loc->Active == 1): ?>
                            <option value="<?php echo $loc->Category_ID; ?>"><?php echo $loc->Employee_Category; ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">Any existing employees with this Employee_Category will be shifted to the selected Employee_Category before deletion.</small>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-12 text-right">
            <button type="button" class="btn btn-danger" id="delete-submit">Delete & Shift Data</button>
        </div>
    </div>
</form>


