<!--begin::Form-->
<form id="create-form" class="form-horizontal" action="item_receive.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="action_type" value="CREATE">

    <div class="mb-3 row">
        <label for="Item_ID" class="col-sm-3 col-form-label">Item <span class="text-danger">*</span></label>
        <div class="col-sm-9">
            <select class="form-control" name="Item_ID" id="Item_ID">
                <option value="" hidden>Select Item</option>
                <?php
                $results = get_items();
                foreach ($results as $result): ?>
                    <option value="<?php echo $result->Item_ID ?>" data-unit="<?php echo $result->Unit ?>">
                        <?php echo $result->Item_Name ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="mb-3 row">
        <label for="Received_Qty" class="col-sm-3 col-form-label">Received Qty <span class="text-danger">*</span></label>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="Received_Qty" name="Received_Qty" placeholder="" oninput="digitsOnly(this, 4);" autocomplete="off" pattern="[0-9]*" inputmode="numeric">
        </div>
    </div>

    <div class="form-group row">
        <div class="col-sm-10 offset-sm-3">
            <button type="submit" class="btn btn-success" id="create-submit" data-datatable="#list" name="create-submit"
                data-form="#create-form" data-loading-text="Saving...">
                <span class="fa fa-fw fa-save"></span> Save
            </button>
            <button type="reset" class="btn btn-danger" id="reset" name="reset">
                <span class="far fa-circle"></span> Reset
            </button>
        </div>
    </div>
</form>
<!--end::Form-->