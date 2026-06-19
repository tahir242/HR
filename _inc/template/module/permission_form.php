<!--begin::Form-->
<form id="edit-form" action="permission.php" method="post" enctype="multipart/form-data">
   <input type="hidden" id="action_type" name="action_type" value="UPDATE">
   <input type="hidden" id="Permission_URN" name="Permission_URN" value="<?php echo $permission->Permission_URN; ?>">
   
            <div class="form-group row">
               <label class="col-lg-4 col-form-label">Module: *</label>
               <div class="col-lg-8">
                  <select class="form-control" name="Sub_Module_ID" id="Sub_Module_ID">
                     <option value="">Select Module</option>
                     <?php 
                     $data = array(
                        "has_submodules" => 0
                     );
                     $modules = get_modules($data); 
                     foreach ($modules AS $module) : ?>
                     <option value="<?= $module->Module_ID ?>" <?php echo $permission->Sub_Module_ID == $module->Module_ID ? "selected" : ""; ?>><?php echo $module->Module ?></option>
                     <?php endforeach; ?>
                     <?php
                     $modules = get_submodules(); 
                     foreach ($modules AS $module) : ?>
                        <option value="<?php echo $module->Sub_Module_ID ?>" <?php echo $permission->Sub_Module_ID == $module->Sub_Module_ID ? "selected" : ""; ?>><?php echo $module->Sub_Module ?></option>
                     <?php endforeach; ?>
                  </select>
               </div>
            </div>
            <div class="form-group row">
               <label class="col-lg-4 col-form-label">Permission ID: *</label>
               <div class="col-lg-8">
                  <input type="text" class="form-control-plaintext" readonly id="Permission_ID" name="Permission_ID" value="<?php echo $permission->Permission_ID ?>" autocomplete="off">
                  <span class="form-text text-muted">Write unique text and/or number combination, This is one time You can not change this later or at any stage.</span>
               </div>
            </div>
            <div class="form-group row">
               <label class="col-lg-4 col-form-label">Permission: *</label>
               <div class="col-lg-8">
                  <input type="text" class="form-control" id="Permission" name="Permission" value="<?php echo $permission->Permission; ?>" autocomplete="off">
               </div>
            </div>
            <div class="form-group row">
               <label class="col-4 col-form-label">Active: *</label>
               <div class="col-8">

                     <label class="radio">
                     <input type="radio" name="Active" value="1" <?php echo $permission->Active == 1 ? "checked" : ""; ?>> Yes
                     <span></span>
                     </label>
                     <label class="radio">
                     <input type="radio" name="Active" value="0" <?php echo $permission->Active == 0 ? "checked" : ""; ?>> No
                     <span></span>
                     </label>
               </div>
            </div>
         

         <div class="row">
            <div class="col-lg-4"></div>
            <div class="col-lg-8">
               <button type="submit" class="btn btn-primary" id="edit-submit" data-datatable="#list" name="edit-submit" data-form="#edit-form" data-loading-text="Updating...">Update</button>
               <button type="reset" id="reset" name="reset" class="btn btn-danger">Reset</button>
            </div>
         </div>
      
</form>
<!--end::Form-->