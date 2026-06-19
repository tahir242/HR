<!--begin::Form-->
<form id="edit-form" action="module.php" method="post" enctype="multipart/form-data">
   <input type="hidden" id="action_type" name="action_type" value="UPDATE">
   <input type="hidden" id="Module_URN" name="Module_URN" value="<?php echo $module->Module_URN; ?>">

   <div class="form-group row">
      <label class="col-lg-3 col-form-label">Module ID: *</label>
      <div class="col-lg-9">
         <input type="text" class="form-control-plaintext" readonly id="Module_ID" name="Module_ID"
            value="<?php echo $module->Module_ID ?>" autocomplete="off">
         <span class="form-text text-muted">Write unique text and/or number combination, This is one time You can not
            change this later or at any stage.</span>
      </div>
   </div>
   <div class="form-group row">
      <label class="col-lg-3 col-form-label">Module: *</label>
      <div class="col-lg-9">
         <input type="text" class="form-control" id="Module" name="Module" value="<?php echo $module->Module; ?>"
            autocomplete="off">
      </div>
   </div>

   <div class="form-group row">
      <label class="col-lg-3 col-form-label">Icon:</label>
      <div class="col-lg-9">
         <input type="text" class="form-control" id="icon" name="Icon" value="<?php echo $module->Icon; ?>"
            autocomplete="off">
         <span class="form-text text-muted">Icon for Navigation Bar</span>
      </div>
   </div>
   <div class="form-group row">
      <label class="col-lg-3 col-form-label">Sub Menu: *</label>
      <div class="col-lg-9">
         <select class="form-control kt-select2" name="Has_Sub_Menu" id="Has_Sub_Menu">
            <option value="1" <?php echo $module->Has_Sub_Menu == 1 ? "selected" : ""; ?>>Yes</option>
            <option value="0" <?php echo $module->Has_Sub_Menu == 0 ? "selected" : ""; ?>>No</option>
         </select>
      </div>
   </div>
   <div class="form-group row" id="hideSubMenu">
      <label class="col-lg-3 col-form-label">URL: *</label>
      <div class="col-lg-9">
         <input type="text" class="form-control" id="Url" name="Url" value="<?php echo $module->Url; ?>"
            autocomplete="off">
      </div>
   </div>
   <div class="form-group row">
      <label class="col-3 col-form-label">Active: *</label>
      <div class="col-9">
         <div class="radio-inline">
            <label class="radio">
               <input type="radio" name="Active" value="1" <?php echo $module->Active == 1 ? "checked" : ""; ?>> Yes
               <span></span>
            </label>
            <label class="radio">
               <input type="radio" name="Active" value="0" <?php echo $module->Active == 0 ? "checked" : ""; ?>> No
               <span></span>
            </label>
         </div>
      </div>
   </div>

   <div class="row">
      <div class="col-lg-3"></div>
      <div class="col-lg-9">
         <button type="submit" class="btn btn-primary" id="edit-submit" data-datatable="#list"
            name="edit-submit" data-form="#edit-form" data-loading-text="Updating...">Update</button>
         <button type="reset" id="reset" name="reset" class="btn btn-danger">Reset</button>
      </div>
   </div>

</form>
<!--end::Form-->