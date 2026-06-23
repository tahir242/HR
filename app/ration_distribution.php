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

//Add Script and Style
$document->addScript('../assets/apexchart/apexcharts.min.js?v=1');
$document->addStyle('../assets/apexchart/apexcharts.css?v=1');
$document->addScript('../assets/app/js/Controller/ReportController.js?v=1');

//Set Document Title
$document->setTitle("Ration Distribution Report");
//ADD BODY CLASS
$document->setBodyClass('');

//Include Header and Footer
include realpath(__DIR__ . '/../') . '/_inc/template/partial/header.php';
include realpath(__DIR__ . '/../') . '/_inc/template/partial/sidebar.php';

?>

<style>
    .report-card {
        margin-bottom: 20px;
    }

    .report-card .card-header {
        background-color: #f8f9fa;
        font-weight: 600;
        padding: 12px 16px;
    }

    @media (max-width: 576px) {
        .app-content-header h3 {
            font-size: 1.25rem;
        }

        .info-box-text {
            font-size: 0.85rem;
        }

        .info-box-number {
            font-size: 1.25rem;
        }
    }

    @media print {

        .app-sidebar,
        .app-header,
        .float-sm-end,
        form {
            display: none !important;
        }

        .app-main {
            margin-left: 0 !important;
        }

        .report-card {
            break-inside: avoid;
            page-break-inside: avoid;
        }
    }

    .pending-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .pending-item {
        padding: 8px 12px;
        border-bottom: 1px solid #e9ecef;
    }

    .pending-item:last-child {
        border-bottom: none;
    }
</style>

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

    <?php

    $workingYear = (isset($request->get['y']) && $request->get['y'] != '') ? $request->get['y'] : current_year();

    $query = "
            SELECT 
                COUNT(1) AS Total, 
                COUNT(CASE WHEN [Status] = 'Eligible' THEN 1 END) AS TotalEligible,
                COUNT(CASE WHEN [Status] = 'Issued' THEN 1 END) AS TotalIssued
            FROM [Ration_Issue_Log] RIL
            WHERE [Ration_Year] = ?";
    $row = $db->get_row($query, [$workingYear]);

    $total = $row ? $row->Total : 0;
    $totalEligible = $row ? $row->TotalEligible : 0;
    $totalIssued = $row ? $row->TotalIssued : 0;

    // Location-wise distribution query
    $locationQuery = "
        SELECT 
            ISNULL(L.[Location], 'Unknown') AS Location,
            COUNT(CASE WHEN RIL.[Status] = 'Issued' THEN 1 END) AS [Distributed],
            COUNT(CASE WHEN RIL.[Status] = 'Eligible' THEN 1 END) AS [NotDistributed]
        FROM [Ration_Issue_Log] RIL
        LEFT JOIN [HR].[dbo].[Employee] E ON RIL.[Employee_ID] = E.[Employee_ID]
        LEFT JOIN [HR].[dbo].[Location] L ON E.[Location] = L.[Location_ID]
        WHERE RIL.[Ration_Year] = ?
        GROUP BY L.[Location]
        ORDER BY L.[Location]";
    $locationData = $db->get_results($locationQuery, [$workingYear]);

    $locationLabels = [];
    $locationDistributed = [];
    $locationNotDistributed = [];

    if ($locationData) {
        foreach ($locationData as $loc) {
            $locationLabels[] = $loc->Location;
            $locationDistributed[] = (int)$loc->Distributed;
            $locationNotDistributed[] = (int)$loc->NotDistributed;
        }
    }

    ?>

    <script>
        var pieSerials = [<?php echo $totalEligible; ?>, <?php echo $totalIssued; ?>];
        var pieLabels = ['Not Distributed', 'Distributed'];
        var pieColors = ['#dc3545', '#218838'];
        var workingYear = <?php echo $workingYear; ?>;
        var isAdmin = <?php echo user_role_id() == 1 ? 'true' : 'false'; ?>;
        
        // Location-wise distribution data
        var locationLabels = <?php echo json_encode($locationLabels); ?>;
        var locationDistributed = <?php echo json_encode($locationDistributed); ?>;
        var locationNotDistributed = <?php echo json_encode($locationNotDistributed); ?>;
    </script>

    <!-- Content Start -->
    <section class="content">

        <div class="row">
            <div class="col-sm-2 mb-2">
                <select class="form-control" name="y" id="y">
                    <option value="2026" <?php if (isset($request->get['y']) && $request->get['y'] == '2026') echo 'selected'; ?>>2026</option>
                    <option value="2025" <?php if (isset($request->get['y']) && $request->get['y'] == '2025') echo 'selected'; ?>>2025</option>
                </select>
            </div>
            <div class="col-sm-1 mb-2">
                <a type="button" href="?y=<?php echo $workingYear; ?>" id="apply-filter" class="btn btn-info" title="Filter">
                    <i class="fa fa-filter"></i> Filter
                </a>
            </div>
            <div class="col-sm-9 mb-2">
                <button class="btn float-right btn-warning" onclick="refreshData()">
                    <i class="fa fa-sync"></i> Refresh
                </button>
            </div>
        </div>

            <div class="row">
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary elevation-1"><i class="fa fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Eligible Employee</span>
                            <span class="info-box-number">
                                <?php echo number_format($total, 0, ",", ","); ?>
                            </span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>
                <!-- /.col -->
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-success elevation-1"><i class="fa fa-thumbs-up"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Distributed</span>
                            <span class="info-box-number"><?php echo number_format($totalIssued, 0, ",", ","); ?></span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>
                <!-- /.col -->
                <!-- fix for small devices only -->
                <!-- <div class="clearfix hidden-md-up"></div> -->
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-danger elevation-1"><i class="fa fa-shopping-cart"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Not Distributed</span>
                            <span
                                class="info-box-number"><?php echo number_format($totalEligible, 0, ",", ","); ?></span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="report-card">
                        <div class="card">
                            <div class="card-header">
                                <i class="fa fa-chart-pie"></i> Distribution Overview
                            </div>
                            <div class="card-body p-2">
                                <div id="chart"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-4">
                    <div class="report-card">
                        <div class="card">
                            <div class="card-header">
                                <i class="fa fa-chart-bar"></i> Ration Type Breakdown (Oil / Ghee)
                            </div>
                            <div class="card-body p-2">
                                <div id="issueTypeChart"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-12 col-lg-4">
                    <div class="report-card">
                        <div class="card">
                            <div class="card-header">
                                <i class="fa fa-tasks"></i> Pending Distribution
                                <span class="badge bg-danger float-end"
                                    id="pendingCount"><?php echo $totalEligible; ?></span>
                            </div>
                            <div class="card-body p-0">
                                <div class="pending-list" id="pendingList">
                                    <div class="text-center p-3 text-muted">
                                        <i class="fa fa-hourglass-half"></i> Loading...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-lg-6">
                    <div class="report-card">
                        <div class="card">
                            <div class="card-header">
                                <i class="fa fa-chart-line"></i> Daily Distribution Trend
                            </div>
                            <div class="card-body p-2">
                                <div id="trendChart"></div>
                            </div>
                        </div>
                    </div>
                    <div class="report-card">
                        <div class="card">
                            <div class="card-header">
                                <i class="fa fa-map-marker-alt"></i> Location-wise Distribution
                            </div>
                            <div class="card-body p-2">
                                <div id="locationChart"></div>
                            </div>
                        </div>
                    </div>
                    <div class="report-card">
                            <div class="card">
                                <div class="card-header">
                                    <i class="fa fa-id-badge"></i> User Performance Stats
                                </div>
                                <div class="card-body p-2">
                                    <div id="userStatsChart"></div>
                                </div>
                            </div>
                        </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="report-card">
                        <div class="card">
                            <div class="card-header">
                                <i class="fa fa-building"></i> Department-wise Distribution
                            </div>
                            <div class="card-body p-2">
                                <div id="departmentChart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

    </section>
    <!-- Content End -->



</div>
<!-- Content Wrapper End -->
<?php include realpath(__DIR__ . '/../') . '/_inc/template/partial/footer.php'; ?>

