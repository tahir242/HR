<!--begin::Form-->
<form id="create-form" class="form-horizontal" action="department.php" method="post" enctype="multipart/form-data">
   <input type="hidden" name="action_type" value="CREATE">

   <div class="mb-3 row">
      <label for="Department" class="col-sm-3 col-form-label">Department <span class="text-danger">*</span></label>
      <div class="col-sm-9">
          <input type="text" class="form-control" id="Department" name="Department" autocomplete="off" oninput="validateCharacters(this, 200)">
      </div>
   </div>

   <div class="mb-3 row">
      <label for="Active" class="col-sm-3 col-form-label">Status <span class="text-danger">*</span></label>
      <div class="col-sm-9">
         <select class="form-control" id="Active" name="Active">
            <option value="1">Active</option>
            <option value="0">Inactive</option>
         </select>
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
