<!--begin::Form-->
<form id="edit-form" class="form-horizontal" action="item.php" method="post" enctype="multipart/form-data">
   <input type="hidden" id="action_type" name="action_type" value="UPDATE">
   <input type="hidden" id="Item_ID" name="Item_ID" value="<?php echo $row->Item_ID; ?>">

   <div class="mb-3 row">
      <label for="Item_Name" class="col-sm-3 col-form-label">Item Name <span class="text-danger">*</span></label>
      <div class="col-sm-9">
          <input type="text" class="form-control" id="Item_Name" name="Item_Name" value="<?php echo $row->Item_Name ?>" autocomplete="off" oninput="validateCharacters(this, 200)">
      </div>
   </div>

   <div class="mb-3 row">
      <label for="Unit" class="col-sm-3 col-form-label">Unit <span class="text-danger">*</span></label>
      <div class="col-sm-9">
         <input type="text" class="form-control" id="Unit" name="Unit" value="<?php echo $row->Unit ?>" placeholder="Kg / ltr etc." autocomplete="off" oninput="validateCharacters(this, 5)">
      </div>
   </div>

   <div class="mb-3 row">
      <label for="Packing_Unit" class="col-sm-3 col-form-label">Packing Unit <span class="text-danger">*</span></label>
      <div class="col-sm-9">
         <input type="text" class="form-control" id="Packing_Unit" value="<?php echo $row->Packing_Unit ?>" oninput="digitsOnly(this, 2);" name="Packing_Unit" placeholder="" autocomplete="off">
      </div>
   </div>

   <div class="mb-3 row">
      <label for="Issue_Qty" class="col-sm-3 col-form-label">Issue Quantity <span class="text-danger">*</span></label>
      <div class="col-sm-9">
         <input type="text" class="form-control" id="Issue_Qty" value="<?php echo $row->Issue_Qty ?>" oninput="digitsOnly(this, 2);" name="Issue_Qty" placeholder="" autocomplete="off">
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