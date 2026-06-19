<form class="form-horizontal" id="edit-form" action="role.php" method="post">
  
  <input type="hidden" id="action_type" name="action_type" value="UPDATE">
  <input type="hidden" id="Role_ID" name="Role_ID" value="<?php echo $role->Role_ID; ?>">
  
      <div class="mb-3 row">
         <label for="Role" class="col-sm-3 col-form-label">Role <span class="text-danger">*</span></label>
         <div class="col-sm-9">
            <input type="text" class="form-control" id="Role" name="Role" value="<?php echo $role->Role; ?>" autocomplete="off">
         </div>
      </div>
      <fieldset class="mb-2">
         <div class="row">
            <label class="col-form-label col-sm-3 pt-0">Active <span class="text-danger">*</span></label>
            <div class="col-sm-9">
            <div class="radio-inline">
            <label class="radio">
               <input type="radio" name="Active" value="1" <?php echo $role->Active == 1 ? "checked" : ""; ?>> Yes
               <span></span>
            </label>
            <label class="radio">
               <input type="radio" name="Active" value="0" <?php echo $role->Active == 0 ? "checked" : ""; ?>> No
               <span></span>
            </label>
         </div>
            </div>
         </div>
      </fieldset>
    <hr>
    <div class="p-0 m-0">
        <h5 class="float-left">
        <b>Permission(s)</b>
        </h5>      
        <button data-form="#edit-form" data-datatable="#list" class="btn btn-sm btn-info float-right role-update" data-loading-text="Updating..."> <span class="fas fa-pencil-alt"></span> Update </button>
    </div>
    <div class="clearfix"></div>
    <hr class="mb-0 mt-3 mb-3" />
    <?php $dbpermissions = get_rolepermissions($role->Role_ID) ? get_rolepermissions($role->Role_ID) : array(); ?>
    <?php
    $the_permission = array();
    foreach($dbpermissions AS $permission){
      $the_permission[$permission->Permission_ID] = $permission->Active;
    }
    ?>

    <div class="form-group permission-list">
      <div class="row">
      <?php 
        $data = array(
         "has_submodules" => 0
        );
        $modules = get_modules($data); 
      foreach ($modules AS $module) : ?>
      <div class="col-sm-3">
        <h5>
          <input type="checkbox" id="<?php echo strtolower($module->Module_ID); ?>_action" onclick="$('.<?php echo strtolower($module->Module_ID); ?>').prop('checked', this.checked);">
          <label for="<?php echo strtolower($module->Module_ID); ?>_action">
            <?php echo strtoupper($module->Module); ?>
          </label>
        </h5>
        <?php 
        $data = array(
          "filter_submodule" => $module->Module_ID,
          "sort" => 'Permission'
        );
        $permissions = get_permissions($data) ?>
        <div class="well well-sm permission-well">
          <div filter-list="search_<?php echo strtolower($module->Module_ID); ?>" class="mb-4">
            <?php foreach ($permissions as $permission) : ?>
              <div class="form-check">
                <input type="checkbox" class="<?php echo strtolower($module->Module_ID); ?> form-check-input" id="<?php echo $permission->Permission_ID; ?>" value="true" name="access[<?php echo $permission->Permission_ID; ?>]"<?php echo isset($the_permission[$permission->Permission_ID]) && $the_permission[$permission->Permission_ID] == 1 ? ' checked' : null; ?>>
                <label  class="form-check-label" for="<?php echo $permission->Permission_ID; ?>"><?php echo ucfirst($permission->Permission); ?></label>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>



      <?php 
      $submodules = get_submodules();
      ?>
      <?php foreach ($submodules as $submodule) : ?>
      <div class="col-sm-3">
        <h5>
          <input type="checkbox" id="<?php echo strtolower($submodule->Sub_Module_ID); ?>_action" onclick="$('.<?php echo strtolower($submodule->Sub_Module_ID); ?>').prop('checked', this.checked);">
          <label for="<?php echo strtolower($submodule->Sub_Module_ID); ?>_action">
            <?php echo strtoupper($submodule->Sub_Module); ?>
          </label>
        </h5>
        <?php 
        $data = array(
          "filter_submodule" => $submodule->Sub_Module_ID,
          "sort" => 'Permission'
        );
        $permissions = get_permissions($data) ?>
        <div class="well well-sm permission-well">
          <div filter-list="search_<?php echo strtolower($submodule->Sub_Module_ID); ?>" class="mb-4">
            <?php foreach ($permissions as $permission) : ?>
              <div class="form-check">
                <input type="checkbox" class="<?php echo strtolower($submodule->Sub_Module_ID); ?> form-check-input" id="<?php echo $permission->Permission_ID; ?>" value="true" name="access[<?php echo $permission->Permission_ID; ?>]"<?php echo isset($the_permission[$permission->Permission_ID]) && $the_permission[$permission->Permission_ID] == 1 ? ' checked' : null; ?>>
                <label  class="form-check-label" for="<?php echo $permission->Permission_ID; ?>"><?php echo ucfirst($permission->Permission); ?></label>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      </div>
    </div>
  </div>
  <hr class="mb-0 mt-2 mb-3" />
  <div class="card-footer">
    <div class="form-group">
      <div class="col-sm-12 text-center">
        <button data-form="#edit-form" data-datatable="#list" class="btn btn-block btn-info role-update" data-loading-text="Updating...">
          <span class="fas fa-pencil-alt"></span> Update
        </button>
      </div>
    </div>
  </div>
</form>