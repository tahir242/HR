<div class="card card-info card-outline">
    <div class="card-header">
        <h3 class="card-title"><b> Change Password</b></h3>
    </div><!-- /.card-header -->

    <div class="card-body">

        <div class="card card-info">
            <div class="card-body p-5">

                <form class="form-horizontal" id="password_change_form" action="change_password.php" method="post"
                    enctype="multipart/form-data">
                    <input type="hidden" name="action_type" value="CHANGEUSERPASSWORD">
                    <input type="hidden" name="UserID" value="<?php echo $User_ID ?>">


                    <div class="form-group row">
                        <label class="col-sm-3 form-label">Current Password</label>
                        <div class="col-sm-9">
                            <input type="password" name="old" id="old" class="form-control" value=""
                                placeholder="Current password">
                            <!-- <a href="" class="kt-link kt-font-sm kt-font-bold kt-margin-t-5">Forgot password ?</a> -->
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 form-label">New Password</label>
                        <div class="col-sm-9">
                            <input type="password" name="new1" id="new1" class="form-control" value=""
                                placeholder="New password">
                        </div>
                    </div>
                    <div class="form-group form-group-last row">
                        <label class="col-sm-3 form-label">Verify Password</label>
                        <div class="col-sm-9">
                            <input type="password" name="new2" id="new2" class="form-control" value=""
                                placeholder="Verify password">
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-lg-3 col-xl-3">
                        </div>
                        <div class="col-lg-9 col-xl-9">
                            <button type="submit" class="btn btn-info" id="password_change_submit"
                                name="password_change_submit" data-form="#password_change_form"
                                data-loading-text="Wait...">Change
                                Password</button>&nbsp;
                            <button type="reset" id="reset" class="btn btn-secondary">Cancel</button>
                        </div>
                    </div>


                </form>

            </div>
            <!-- /.card-body -->
        </div>

    </div><!-- /.card-body -->
</div>
<!-- /.card -->