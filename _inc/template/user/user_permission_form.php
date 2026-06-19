<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><b> Account Information</b></h3>
    </div><!-- /.card-header -->

    <div class="card-body">
                <form id="user-permission-form" action="user.php" method="post">
                    <input type="hidden" id="action_type" name="action_type" value="UPDATEUSERPERMISSION">
                    <input type="hidden" id="User_ID" name="User_ID" value="<?php echo $User_ID; ?>">
                    
                                <?php $dbpermissions = get_userpermissions($User_ID) ? get_userpermissions($User_ID) : array(); ?>
                                <?php
                                $the_permission = array();
                                foreach ($dbpermissions as $permission) {
                                    $the_permission[$permission->Permission_ID] = $permission->Active;
                                }
                                ?>

                                <div class="form-group">
                                    <div class="row">

                                        <?php
                                        $data = array(
                                            "has_submodules" => 0
                                        );
                                        $modules = get_modules($data);
                                        foreach ($modules as $module): ?>
                                            <div class="col-sm-4">
                                                <h5>
                                                    <input type="checkbox"
                                                        id="<?php echo strtolower($module->Module_ID); ?>_action"
                                                        onclick="$('.<?php echo strtolower($module->Module_ID); ?>').prop('checked', this.checked);">
                                                    <label for="<?php echo strtolower($module->Module_ID); ?>_action">
                                                        <strong>
                                                            <?php echo strtoupper($module->Module); ?>
                                                        </strong>
                                                    </label>
                                                </h5>
                                                <?php
                                                $data = array(
                                                    "filter_submodule" => $module->Module_ID,
                                                    "sort" => 'Permission'
                                                );
                                                $permissions = get_permissions($data) ?>
                                                <div>
                                                    <div filter-list="search_<?php echo strtolower($module->Module_ID); ?>"
                                                        class="">
                                                        <?php foreach ($permissions as $permission): ?>
                                                            <div class="form-check">
                                                                <input type="checkbox"
                                                                    class="<?php echo strtolower($module->Module_ID); ?> form-check-input"
                                                                    id="<?php echo $permission->Permission_ID; ?>" value="true"
                                                                    name="access[<?php echo $permission->Permission_ID; ?>]"
                                                                    <?php echo isset($the_permission[$permission->Permission_ID]) && $the_permission[$permission->Permission_ID] == 1 ? ' checked' : null; ?>>
                                                                <label class="form-check-label"
                                                                    for="<?php echo $permission->Permission_ID; ?>">
                                                                    <?php echo ucfirst($permission->Permission); ?>
                                                                </label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>

                                        <?php
                                        $submodules = get_submodules();
                                        ?>
                                        <?php foreach ($submodules as $submodule): ?>
                                            <div class="col-sm-4">
                                                <h5>
                                                    <input type="checkbox"
                                                        id="<?php echo strtolower($submodule->Sub_Module_ID); ?>_action"
                                                        onclick="$('.<?php echo strtolower($submodule->Sub_Module_ID); ?>').prop('checked', this.checked);">
                                                    <label
                                                        for="<?php echo strtolower($submodule->Sub_Module_ID); ?>_action">
                                                        <strong>
                                                            <?php echo strtoupper($submodule->Sub_Module); ?>
                                                        </strong>
                                                    </label>
                                                </h5>
                                                <?php
                                                $data = array(
                                                    "filter_submodule" => $submodule->Sub_Module_ID,
                                                    "sort" => 'Permission'
                                                );
                                                $permissions = get_permissions($data) ?>
                                                <div class="well well-sm permission-well">
                                                    <div filter-list="search_<?php echo strtolower($submodule->Sub_Module_ID); ?>"
                                                        class="mb-2">
                                                        <?php foreach ($permissions as $permission): ?>
                                                            <div class="form-check">
                                                                <input type="checkbox"
                                                                    class="<?php echo strtolower($submodule->Sub_Module_ID); ?> form-check-input"
                                                                    id="<?php echo $permission->Permission_ID; ?>" value="true"
                                                                    name="access[<?php echo $permission->Permission_ID; ?>]"
                                                                    <?php echo isset($the_permission[$permission->Permission_ID]) && $the_permission[$permission->Permission_ID] == 1 ? ' checked' : null; ?>>
                                                                <label class="form-check-label"
                                                                    for="<?php echo $permission->Permission_ID; ?>">
                                                                    <?php echo ucfirst($permission->Permission); ?>
                                                                </label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                          

                    <div class="row">
                        <div class="col-sm-12">
                            <button type="submit" class="btn btn-block btn-info user-permission-update"
                                data-form="#user-permission-form" name="btn_edit_role"
                                data-loading-text="Updating..."><b>Update</b></button>
                        </div>
                    </div>
                </form>

    </div><!-- /.card-body -->
</div>
<!-- /.card -->