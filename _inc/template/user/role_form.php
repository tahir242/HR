<div class="card card-info card-outline">
    <div class="card-header">
        <h3 class="card-title"><b> Assign & Change Role</b></h3>
    </div><!-- /.card-header -->

    <div class="card-body">

        <div class="card card-info">
            <div class="card-body p-5">

                <form class="form-horizontal" id="role_change_form" action="user.php" method="post"
                    enctype="multipart/form-data">
                    <input type="hidden" name="action_type" value="CHANGEUSERROLE">
                    <input type="hidden" name="User_ID" value="<?php echo $User_ID ?>">

                    <div class="form-group row">
                        <label for="Role_ID" class="col-sm-2 control-label">Select Role <i class="required"
                                style="color: red;">*</i></label>
                        <div class="col-sm-6">
                            <div>
                                <select id="Role_ID" class="form-control" name="Role_ID" required>
                                    <option value="">Select</option>
                                    <?php
                                    $roles = get_roles();
                                    foreach ($roles as $role): ?>
                                        <option value="<?php echo $role["Role_ID"] ?>" <?php echo isset($userrole->Role_ID) && $role["Role_ID"] == $userrole->Role_ID ? "selected" : "" ?>>
                                            <?php echo $role["Role"] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-2">
                        </div>
                        <div class="col-sm-4">
                            <button class="btn btn-info" id="assignRole" data-form="#role_change_form">Assign
                                Role</button>
                        </div>
                    </div>
                </form>

            </div>
            <!-- /.card-body -->
        </div>

    </div><!-- /.card-body -->
</div>
<!-- /.card -->