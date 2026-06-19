<!--begin::Form-->
<form id="edit-form" action="sub-module.php" method="post" enctype="multipart/form-data">
   <input type="hidden" id="action_type" name="action_type" value="UPDATE">
   <input type="hidden" id="Sub_Module_URN" name="Sub_Module_URN" value="<?php echo $submodule->Sub_Module_URN; ?>">

   <div class="form-group row">
      <label class="col-lg-4 col-form-label">Module: *</label>
      <div class="col-lg-8">
         <select class="form-control" name="Module_ID" name="Module_ID">
            <option value="">Select Module</option>
            <?php
            $data = array(
               "has_submodules" => 1
            );
            $modules = get_modules($data);
            foreach ($modules as $row): ?>
               <option value="<?= $row->Module_ID ?>" <?php echo $submodule->Module_ID == $row->Module_ID ? "selected" : ""; ?>>
                  <?= $row->Module ?>
               </option>
            <?php endforeach; ?>
         </select>
      </div>
   </div>
   <div class="form-group row">
      <label class="col-lg-4 col-form-label">Sub Module ID: *</label>
      <div class="col-lg-8">
         <input type="text" class="form-control-plaintext" readonly id="Sub_Module_ID" name="Sub_Module_ID"
            value="<?= $submodule->Sub_Module_ID ?>" autocomplete="off">
         <span class="form-text text-muted">Write unique text and/or number combination, This is one time You can not
            change this later or at any stage.</span>
      </div>
   </div>
   <div class="form-group row">
      <label class="col-lg-4 col-form-label">Sub Module: *</label>
      <div class="col-lg-8">
         <input type="text" class="form-control" id="Sub_Module" name="Sub_Module"
            value="<?php echo $submodule->Sub_Module; ?>" autocomplete="off">
      </div>
   </div>
   <div class="form-group row">
      <label class="col-lg-4 col-form-label">URL: *</label>
      <div class="col-lg-8">
         <input type="text" class="form-control" id="Url" name="Url" value="<?php echo $submodule->Url; ?>"
            autocomplete="off">
      </div>
   </div>
   <div class="form-group row">
      <label class="col-4 col-form-label">Show in Menu: *</label>
      <div class="col-8">

         <label class="radio">
            <input type="radio" name="Show_In_Menu" value="1" <?php echo $submodule->Show_In_Menu == 1 ? "checked" : ""; ?>> Yes
            <span></span>
         </label>
         <label class="radio">
            <input type="radio" name="Show_In_Menu" value="0" <?php echo $submodule->Show_In_Menu == 0 ? "checked" : ""; ?>> No
            <span></span>
         </label>

      </div>
   </div>
   <div class="form-group row">
      <label class="col-4 col-form-label">Active: *</label>
      <div class="col-8">
         <label class="radio">
            <input type="radio" name="Active" value="1" <?php echo $submodule->Active == 1 ? "checked" : ""; ?>> Yes
            <span></span>
         </label>
         <label class="radio">
            <input type="radio" name="Active" value="0" <?php echo $submodule->Active == 0 ? "checked" : ""; ?>> No
            <span></span>
         </label>
      </div>
   </div>


   <div class="row">
      <div class="col-lg-4"></div>
      <div class="col-lg-8">
         <button type="submit" class="btn btn-primary" id="edit-submit" data-datatable="#list" name="edit-submit"
            data-form="#edit-form" data-loading-text="Updating...">Update</button>
         <button type="reset" id="reset" name="reset" class="btn btn-danger">Reset</button>
      </div>
   </div>

</form>
<!--end::Form-->