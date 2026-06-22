<?php
$Location_ID = $request->get['Location_ID'];
$location = $model->getLocation($Location_ID);

$locations = $model->getLocations(); 
?>
<form id="form-delete" action="location.php" method="post">
    <input type="hidden" name="action_type" value="DELETE">
    <input type="hidden" name="Location_ID" value="<?php echo $Location_ID; ?>">
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-warning">
                Are you sure you want to delete <strong><?php echo $location->Location; ?></strong>?
            </div>
            <div class="form-group">
                <label>Shift Data To (Optional if not assigned) <span class="text-danger">*</span></label>
                <select name="Shift_Location_ID" class="form-control">
                    <option value="">Select Location</option>
                    <?php foreach($locations as $loc): ?>
                        <?php if($loc->Location_ID != $Location_ID && $loc->Active == 1): ?>
                            <option value="<?php echo $loc->Location_ID; ?>"><?php echo $loc->Location; ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">Any existing employees with this location will be shifted to the selected location before deletion.</small>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-12 text-right">
            <button type="button" class="btn btn-danger" id="delete-submit">Delete & Shift Data</button>
        </div>
    </div>
</form>
