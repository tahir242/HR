<?php
ob_start();
include realpath(__DIR__ . '/../') . '/_init.php';

if (!is_loggedin()) {
    redirect(root_url() . '/index.php');
}

$document->setTitle("Department & Designation Turnover");
$document->addScript('../assets/app/js/Controller/TurnoverDeptDesigController.js?v=10');
// Include ApexCharts
$document->addScript('https://cdn.jsdelivr.net/npm/apexcharts');

include realpath(__DIR__ . '/../') . '/_inc/template/partial/header.php';
include realpath(__DIR__ . '/../') . '/_inc/template/partial/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0"><?php echo $title ?></h1>
                </div>
                <div class="col-sm-6 text-right" id="printBtnContainer" style="display: none;">
                    <button class="btn btn-primary" onclick="window.print();"><i class="fas fa-print"></i> Print Report</button>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <!-- Filter Form -->
        <div class="card d-print-none mb-3">
            <div class="card-body p-3">
                <form id="filterForm">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label for="fromDate" style="font-size: 13px;">From Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm" id="fromDate" name="fromDate" required max="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label for="toDate" style="font-size: 13px;">To Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm" id="toDate" name="toDate" required max="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-search"></i> Fetch</button>
                            <button type="button" class="btn btn-default btn-sm" id="resetBtn">Reset</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Report Container -->
        <div id="reportContainer" style="display: none;">
            
            <!-- Summary Paragraph -->
            <div class="card bg-light mb-3" style="page-break-after: avoid; page-break-inside: avoid;">
                <div class="card-body p-3">
                    <h5 class="text-info font-weight-bold mb-2"><i class="fas fa-info-circle"></i> Executive Summary</h5>
                    <p id="summaryText" class="mb-0 text-dark" style="font-size: 14px; line-height: 1.4;"></p>
                </div>
            </div>

            <!-- Dynamic Grid -->
            <div id="departmentsGrid"></div>

        </div>
    </section>
</div>

<!-- Print Styles -->
<style>
@media print {
    body { background-color: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .content-wrapper { margin-left: 0 !important; padding: 0 !important; background-color: #fff; }
    .main-footer, .main-header, .d-print-none { display: none !important; }
    
    /* Keep borders so the equal-height cards don't look broken */
    .card { border: 1px solid #ccc !important; box-shadow: none !important; margin-bottom: 15px !important; }
    .card-header { border-bottom: 1px solid #ccc !important; background-color: #f8f9fa !important; padding: 5px 10px !important; }
    .card-title { font-size: 14px !important; color: #0056b3 !important; }
    .table-bordered th, .table-bordered td { border: 1px solid #ccc !important; }
    h1 { font-size: 24px; text-align: center; margin-bottom: 20px; }
    
    /* Ensure rows don't break awkwardly across pages */
    #departmentsGrid .row { page-break-inside: avoid; }
}
</style>

<?php include realpath(__DIR__ . '/../') . '/_inc/template/partial/footer.php'; ?>
