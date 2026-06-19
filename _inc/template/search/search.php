<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    Found <b><?php echo count($results) ?></b> Employee(s).
                </h3>
            </div>
            <div class="card-body table-responsive p-0" style="height: 600px;">
                <?php if ($results): ?>
                    <table class="table table-sm table-hover table-valign-middle table-bordered table-head-fixed">
                        <thead>
                            <tr>
                                <td style="width: 10%; text-align: center;">Emp. ID</td>
                                <td>Name</td>
                                <td>Department</td>
                                <td>Designation</td>
                                <td style="width: 10%; text-align: center;">DOJ</td>
                                <td style="width: 10%; text-align: center;">PDF</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($results AS $result): ?>
                            <tr>
                                <td style="text-align: center;"><?php echo $result->Employee_ID ?></td>
                                <td><?php echo $result->Name ?></td>
                                <td><?php echo get_the_department($result->Department, "Department") ?></td>
                                <td><?php echo get_the_designation($result->Designation, "Designation") ?></td>
                                <td style="text-align: center;"><?php echo date_normalizer($result->Date_of_Joining, "d-m-Y") ?></td>
                                <td style="text-align: right;"><button class="btn btn-outline-danger btn-sm view-pdf" data-file="<?php echo $result->Scan ?>"><i class="fa-solid fa-file-pdf"></i> View</button></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="d-flex justify-content-center align-items-center" style="height: 56vh;">
                        <div
                            style="border: 2px solid #ccc; padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                            <h1 style="margin: 0; color: #555;">No Result Found</h1>
                            <?php foreach($log as $key => $value): ?>
                                <p><?php echo $key . " : " . $value ?></p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>