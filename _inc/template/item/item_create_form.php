<!--begin::Form-->
<form id="create-form" class="form-horizontal" action="item.php" method="post" enctype="multipart/form-data">
   <input type="hidden" name="action_type" value="CREATE">

   <div class="mb-3 row">
      <label for="Item_Name" class="col-sm-3 col-form-label">Item Name <span class="text-danger">*</span></label>
      <div class="col-sm-9">
          <input type="text" class="form-control" id="Item_Name" name="Item_Name" autocomplete="off" oninput="validateCharacters(this, 200)">
      </div>
   </div>

   <div class="mb-3 row">
      <label for="Unit" class="col-sm-3 col-form-label">Unit <span class="text-danger">*</span></label>
      <div class="col-sm-9">
         <input type="text" class="form-control" id="Unit" name="Unit" placeholder="Kg / ltr etc." oninput="validateCharacters(this, 5)" autocomplete="off">
      </div>
   </div>

   <div class="mb-3 row">
      <label for="Packing_Unit" class="col-sm-3 col-form-label">Packing Unit <span class="text-danger">*</span></label>
      <div class="col-sm-9">
         <input type="text" class="form-control" id="Packing_Unit" name="Packing_Unit" placeholder="" oninput="digitsOnly(this, 2);" autocomplete="off">
      </div>
   </div>

   <div class="mb-3 row">
      <label for="Issue_Qty" class="col-sm-3 col-form-label">Issue Quantity <span class="text-danger">*</span></label>
      <div class="col-sm-9">
         <input type="text" class="form-control" id="Issue_Qty" name="Issue_Qty" placeholder="" oninput="digitsOnly(this, 2);" autocomplete="off">
      </div>
   </div>

   <div class="form-group row">
      <div class="col-sm-10 offset-sm-3">
         <button type="submit" class="btn btn-success" id="create-submit"
            data-datatable="#list" name="create-submit"
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