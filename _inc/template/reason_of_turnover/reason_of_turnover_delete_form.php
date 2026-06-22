<?php
$Reason_ID = $request->get['Reason_ID'];
$reason = $model->getReasonOfTurnover($Reason_ID);

$reasons = $model->getReasonOfTurnovers(); 
?>
<form id="form-delete" action="reason_of_turnover.php" method="post">
    <input type="hidden" name="action_type" value="DELETE">
    <input type="hidden" name="Reason_ID" value="<?php echo $Reason_ID; ?>">
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-warning">
                Are you sure you want to delete <strong><?php echo $reason->Reason; ?></strong>?
            </div>
            <div class="form-group">
                <label>Shift Data To (Optional if not assigned) <span class="text-danger">*</span></label>
                <select name="Shift_Reason_ID" class="form-control">
                    <option value="">Select Reason</option>
                    <?php foreach($reasons as $r): ?>
                        <?php if($r->Reason_ID != $Reason_ID && $r->Active == 1): ?>
                            <option value="<?php echo $r->Reason_ID; ?>"><?php echo $r->Reason; ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">Any existing employees with this reason will be shifted to the selected reason before deletion.</small>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-12 text-right">
            <button type="button" class="btn btn-danger" id="delete-submit">Delete & Shift Data</button>
        </div>
    </div>
</form>
