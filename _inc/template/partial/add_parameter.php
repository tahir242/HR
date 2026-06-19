<!--begin::Form-->
<form id="create-form" action="parameter.php" method="post" enctype="multipart/form-data">

   <div class="form-group row">
      <input type="hidden" name="action_type" value="CREATE">
      <label class="col-lg-3 col-form-label">Parameter: *</label>
      <div class="col-lg-9">
         <input type="text" class="form-control" id="Parameter" name="Parameter" value="" autocomplete="off">
         <span class="form-text text-muted">Write unique text and/or number combination, This is one time You can not
            change this later or at any stage.</span>
      </div>
   </div>
   <div class="form-group row">
      <label class="col-lg-3 col-form-label">Value: *</label>
      <div class="col-lg-9">
         <input type="text" class="form-control" id="Value" name="Value" autocomplete="off">
      </div>
   </div>

   <div class="form-group row">
      <label class="col-3 col-form-label">Active: *</label>
      <div class="col-9">
         <div class="radio-inline">
            <label class="radio">
               <input type="radio" name="Active" value="1" checked> Yes
               <span></span>
            </label>
            <label class="radio">
               <input type="radio" name="Active" value="0"> No
               <span></span>
            </label>
         </div>
      </div>
   </div>

   <div class="row">
      <div class="col-lg-3"></div>
      <div class="col-lg-9">
         <button type="submit" class="btn btn-primary" id="create-submit" data-datatable="#list" name="create-submit"
            data-form="#create-form" data-loading-text="Saving...">Save</button>
         <button type="reset" id="reset" name="reset" class="btn btn-danger">Reset</button>
      </div>
   </div>
</form>
<!--end::Form-->