<!--begin::Form-->
<form id="edit-form" class="form-horizontal" action="location.php" method="post" enctype="multipart/form-data">
   <input type="hidden" name="action_type" value="UPDATE">
   <input type="hidden" name="Location_ID" value="<?php echo $row->Location_ID; ?>">

   <div class="mb-3 row">
      <label for="Location" class="col-sm-3 col-form-label">Location <span class="text-danger">*</span></label>
      <div class="col-sm-9">
          <input type="text" class="form-control" id="Location" name="Location" value="<?php echo htmlspecialchars($row->Location); ?>" autocomplete="off" oninput="validateCharacters(this, 200)">
      </div>
   </div>

   <div class="mb-3 row">
      <label for="Active" class="col-sm-3 col-form-label">Status <span class="text-danger">*</span></label>
      <div class="col-sm-9">
         <select class="form-control" id="Active" name="Active">
            <option value="1" <?php echo $row->Active == 1 ? 'selected' : ''; ?>>Active</option>
            <option value="0" <?php echo $row->Active == 0 ? 'selected' : ''; ?>>Inactive</option>
         </select>
      </div>
   </div>

   <div class="form-group row">
      <div class="col-sm-10 offset-sm-3">
         <button type="submit" class="btn btn-success" id="edit-submit"
            data-datatable="#list" name="edit-submit"
            data-form="#edit-form" data-loading-text="Saving...">
            <span class="fa fa-fw fa-save"></span> Update
         </button>
      </div>
   </div>
</form>
<!--end::Form-->
