<?php
ob_start();
include("../_init.php");

// Redirect, If user is not logged in
if (!is_loggedin()) {
    redirect(root_url() . '/index.php');
}

// Redirect, If User has not Read Permission
if (user_role_id() != 1 && !has_permission(1, 'read_item')) {
    redirect(root_url() . '/' . APPDIRNAME . '/home.php');
}

//Set Document Title
$document->setTitle("Ration Inventory");
//ADD BODY CLASS
$document->setBodyClass('');

//Include Header and Footer
include realpath(__DIR__ . '/../') . '/_inc/template/partial/header.php';
include realpath(__DIR__ . '/../') . '/_inc/template/partial/sidebar.php';

?>

<!-- Content Wrapper Start -->
<div class="content-wrapper">

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0"><?php echo $title ?></h1>
                </div>
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Content Start -->
    <section class="content">

        <div class="row">
            <div class="col-sm-2 mb-2">
                <select class="form-control" name="y" id="y">
                    <option value="2026" <?php if (isset($request->get['y']) && $request->get['y'] == '2026') echo 'selected'; ?>>2026</option>
                    <option value="2025" <?php if (isset($request->get['y']) && $request->get['y'] == '2025') echo 'selected'; ?>>2025</option>
                </select>
            </div>
            <div class="col-sm-2 mb-2">
                <a type="button" href="javascript:void(0);" id="apply-filter" class="btn btn-info" title="Filter">
                    <i class="fa fa-filter"></i> Filter
                </a>
            </div>
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <table class="table dataTable table-hover table-sm" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th class="text-center">Unit</th>
                                <th class="text-center">Received</th>
                                <th class="text-center">Issued</th>
                                <th class="text-center">Balance</th>
                                <th class="text-center">Box/Pack</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT * FROM [Ration_Item]";
                            $results = db()->get_results($query, []);
                            $workingYear = (isset($request->get['y']) && $request->get['y'] != '') ? $request->get['y'] : current_year();
                            ?>
                            <?php if ($results): ?>
                                <?php foreach ($results as $result): ?>
                                    <?php
                                    $received = 0;
                                    $issued = 0;
                                    $balance = 0;
                                    $pack = 0;
                                    $received_query = "SELECT SUM([Received_Qty]) as [Received] FROM [Ration_Transaction] WHERE [Year] = ? AND [Item_ID] = ? AND Received_Qty <> 0";
                                    $received_result = db()->get_row($received_query, [$workingYear, $result->Item_ID]);
                                    if ($received_result) {
                                        $received = $received_result->Received;
                                    }
                                    $issued_query = "SELECT SUM([Issued_Qty]) as [Issued] FROM [Ration_Transaction] WHERE [Year] = ? AND [Item_ID] = ? AND Issued_Qty <> 0";
                                    $issued_result = db()->get_row($issued_query, [$workingYear, $result->Item_ID]);
                                    if ($issued_result) {
                                        $issued = $issued_result->Issued;
                                    }
                                    $balance = $received - $issued;
                                    $pack = $balance / $result->Packing_Unit;
                                    ?>
                                    <tr>
                                        <td data-title="Item Name"><?php echo $result->Item_Name ?></td>
                                        <td data-title="Unit" class="text-center"><?php echo $result->Unit ?></td>
                                        <td data-title="Received" class="text-center"><?php echo $received ?></td>
                                        <td data-title="Issued" class="text-center"><?php echo $issued ?></td>
                                        <td data-title="Balance" class="text-center"><?php echo $balance ?></td>
                                        <td data-title="Box/Pack" class="text-center"><?php echo $pack ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    </div><!-- /.card-body -->
                </div><!-- /.card -->
            </div><!-- /.col-sm-12 -->
        </div><!-- /.row -->
    </section>
    <!-- Content End -->

</div>
<!-- Content Wrapper End -->
<?php include realpath(__DIR__ . '/../') . '/_inc/template/partial/footer.php'; ?>

