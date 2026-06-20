<!--begin::Form-->
<form id="edit-form" class="form-horizontal" action="item_receive.php" method="post" enctype="multipart/form-data">
   <input type="hidden" id="action_type" name="action_type" value="UPDATE">
   <input type="hidden" id="Transaction_ID" name="Transaction_ID" value="<?php echo $row->Transaction_ID; ?>">

   <div class="mb-3 row">
      <label for="Item_ID" class="col-sm-3 col-form-label">Item <span class="text-danger">*</span></label>
      <div class="col-sm-9">
         <select class="form-control" name="Item_ID" id="Item_ID">
            <option value="">Select Item</option>
            <?php
            $results = get_items();
            foreach ($results as $result): ?>
               <option value="<?php echo $result->Item_ID ?>" data-unit="<?php echo $result->Unit ?>" <?php echo $row->Item_ID == $result->Item_ID ? "selected" : ""; ?>>
                  <?php echo $result->Item_Name ?>
               </option>
            <?php endforeach; ?>
         </select>
      </div>
   </div>

   <div class="mb-3 row">
      <label for="Received_Qty" class="col-sm-3 col-form-label">Received Qty <span class="text-danger">*</span></label>
      <div class="col-sm-9">
         <input type="text" class="form-control" id="Received_Qty" name="Received_Qty" placeholder=""
            oninput="digitsOnly(this, 4);" value="<?php echo $row->Received_Qty ?>" autocomplete="off" pattern="[0-9]*" inputmode="numeric">
      </div>
   </div>

   <div class="form-group row">
      <div class="col-sm-10 offset-sm-3">
         <button type="submit" class="btn btn-primary" id="edit-submit" data-datatable="#list" name="edit-submit"
            data-form="#edit-form" data-loading-text="Updating...">
            <span class="fas fa-pencil-alt"></span> Update
         </button>
      </div>
   </div>

</form>
<!--end::Form-->