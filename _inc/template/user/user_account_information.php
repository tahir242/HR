<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><b> Account Information</b></h3>
    </div><!-- /.card-header -->

    <div class="card-body">

        <div class="card card-info">
            <div class="card-body p-0">
                <table class="table">
                    <tbody>
                        <tr>
                            <td>Employee ID</td>
                            <td>:</td>
                            <td><?php echo $response->data->EmpID ?></td>
                        </tr>
                        <tr>
                            <td>Username</td>
                            <td>:</td>
                            <td>@<?php echo $response->data->Username ?></td>
                        </tr>
                        <tr>
                            <td>Contact No.</td>
                            <td>:</td>
                            <td><?php echo $response->data->Mobile ?></td>
                        </tr>
                        <tr>
                            <td>Email Address</td>
                            <td>:</td>
                            <td><?php echo $response->data->Email ?></td>
                        </tr>
                        <tr>
                            <td>Gender</td>
                            <td>:</td>
                            <td><?php echo $response->data->Gender ?></td>
                        </tr>
                        <tr>
                            <td>Date of Birth</td>
                            <td>:</td>
                            <td><?php echo date_normalizer($response->data->DOB->date, "D d M, Y"); ?></td>
                        </tr>
                        <tr>
                            <td>Active</td>
                            <td>:</td>
                            <td><?php echo $response->data->Active == 1 ? "Yes" : "No"  ?></td>
                        </tr>
                        <tr>
                            <td>User Since</td>
                            <td>:</td>
                            <td><?php echo date_normalizer($response->data->CreatedDtTm->date, "D d M, Y"); ?></td>
                        </tr>
                        <tr>
                            <td>Last Login</td>
                            <td>:</td>
                            <td><?php echo $response->data->LastLogin->date != "" ? date_normalizer($response->data->LastLogin->date, "D d M, Y h:i:s") : ""; ?></td>
                        </tr>
                    </tbody>
                </table>

            </div>
            <!-- /.card-body -->
        </div>

    </div><!-- /.card-body -->
</div>
<!-- /.card -->