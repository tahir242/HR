<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <b>Update Demographic</b>
        </h3>
        <div class="card-tools pull-right">
            <a href="JavaScript:void(0);" class="btn btn-primary btn-xs search-button"><i
                    class="fa-solid fa-arrows-rotate"></i> Searching Field(s)</a>
        </div>
    </div>
    <form id="create-form" action="indexing.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action_type" value="CREATE">
        <input type="hidden" name="Scan" value="<?php echo $scan ?>">
        <div class="card-body">
            <div class="form-group">
                <label for="Employee_ID">Employee ID: <span style="color: red;">*</span></label> <button
                    class="btn btn-sm btn-secondary p-1 float-right" id="missing-id">Missing ID</button>
                <input type="text" name="Employee_ID" value="<?php echo $emp->Employee_ID ?>" class="form-control"
                    id="Employee_ID" placeholder="Write Employee ID" tabindex="1" autofocus autocomplete="off">
                <div class="error-message" id="employee-id"></div>
            </div>
            <div class="form-group">
                <label for="Employee_Name">Employee Name: <span style="color: red;">*</span></label>
                <input type="text" name="Employee_Name" value="<?php echo $emp->Name ?>" class="form-control"
                    id="Employee_Name" placeholder="Write Employee Name" tabindex="2" required autocomplete="off">
                <div class="error-message" id="employee-name"></div>
            </div>
            <div class="form-group search-box">
                <label for="Department">Department:</label>
                <div class="input-group autocomplete">
                    <input type="text" placeholder="Write Employee Department"
                        value="<?php echo get_the_department($emp->Department, "Department") ?>" tabindex="3"
                        class="form-control" name="Department" id="Department">
                </div>
            </div>
            <div class="form-group search-box">
                <label for="Designation">Designation:</label>
                <div class="input-group autocomplete">
                    <input type="text" placeholder="Write Employee Designation"
                        value="<?php echo get_the_designation($emp->Designation, "Designation") ?>" tabindex="4"
                        class="form-control" name="Designation" id="Designation">
                </div>
            </div>
            <div class="form-group">
                <label for="DOJ">Date of Joining:</label>
                <input type="text" name="DOJ" class="form-control" id="DOJ"
                    value="<?php echo date_normalizer($emp->Date_of_Joining, "d-m-Y") ?>" placeholder="DD-MM-YYYY"
                    tabindex="5" oninput="formatDate(this)" required>
                <div class="error-message" id="employee-doj"></div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary" id="update-submit" data-datatable="#list" name="update-submit"
                data-form="#create-form" data-loading-text="Saving..." tabindex="6">Update</button>
            <button type="reset" id="reset" name="reset" class="btn btn-danger">Reset</button>
        </div>
    </form>
</div>