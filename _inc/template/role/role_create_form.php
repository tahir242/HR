<form id="create-form" class="form-horizontal" action="role.php" method="post"
   enctype="multipart/form-data">
   <input type="hidden" name="action_type" value="CREATE">
   <div class="mb-3 row">
      <label for="Role" class="col-sm-3 col-form-label">Role <span class="text-danger">*</span></label>
      <div class="col-sm-9">
         <input type="text" class="form-control" id="Role" name="Role" autocomplete="off">
      </div>
   </div>
   <fieldset class="mb-2">
      <div class="row">
         <label class="col-form-label col-sm-3">Active <span class="text-danger">*</span></label>
         <div class="col-sm-9">
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
   </fieldset>
   <div class="form-group row">
      <div class="col-sm-10 offset-sm-3">
         <button class="btn btn-primary" id="create-submit" type="submit" data-datatable="#list"
            name="create-submit" data-form="#create-form" data-loading-text="Saving...">
            <span class="fa fa-fw fa-save"></span>
            Save
         </button>
         <button type="reset" class="btn btn-danger" id="reset" name="reset">
            <span class="far fa-circle"></span>
            Reset
         </button>
      </div>
   </div>
</form>