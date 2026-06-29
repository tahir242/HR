<?php
ob_start();
include realpath(__DIR__ . '/../') . '/_init.php';

// Redirect, If user is not logged in
if (!is_loggedin()) {
    redirect(root_url() . '/index.php');
}

// Redirect, If User has not Read Permission
if (user_role_id() != 1 && !has_permission(1, 'read_turnover_dashboard')) {
    redirect(root_url() . '/' . APPDIRNAME . '/dashboard.php');
}

// Set Document Title
$title = "Employee Turnover Dashboard";
$document->setTitle($title);

// Include ApexCharts and SheetJS via CDN
$document->addScript('https://cdn.jsdelivr.net/npm/apexcharts');
$document->addScript('https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js');
$document->addScript('../assets/app/js/Controller/TurnoverDashboardController.js?v=1');

// Include Header and Footer
include realpath(__DIR__ . '/../') . '/_inc/template/partial/header.php';
include realpath(__DIR__ . '/../') . '/_inc/template/partial/sidebar.php';

// LOAD DICTIONARIES for filter dropdowns
$dictModel = registry()->get('loader')->model('dictionary');
$departments = $dictModel->getDepartments();
$designations = $dictModel->getDesignations();
$categories = registry()->get('loader')->model('employee_category')->getEmployeeCategories();
$resTypes = registry()->get('loader')->model('resignation_type')->getResignationTypes();

// Get available years
$yearsQuery = "SELECT DISTINCT YEAR(Date_of_Leaving) as yr FROM [HR].[dbo].[Employee_PDF] WHERE Active = 'Y' AND Date_of_Leaving IS NOT NULL ORDER BY yr DESC";
$years = $db->get_results($yearsQuery, []);
$currentYear = date('Y');
?>

<style>
/* ── Dashboard Custom Styles ── */
.dashboard-wrapper {
    background: #f0f2f5;
    min-height: 100vh;
}

/* KPI Cards */
.kpi-card {
    background: linear-gradient(135deg, #1B2838 0%, #2C3E50 100%);
    border-radius: 12px;
    padding: 20px 18px;
    color: #fff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border-left: 4px solid #00897B;
}
.kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.25);
}
.kpi-card .kpi-icon {
    position: absolute;
    top: 12px;
    right: 15px;
    font-size: 28px;
    opacity: 0.2;
    color: #00897B;
}
.kpi-card .kpi-value {
    font-size: 28px;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 4px;
}
.kpi-card .kpi-label {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1px;
    opacity: 0.75;
}
.kpi-card .kpi-sublabel {
    font-size: 11px;
    opacity: 0.55;
    margin-top: 2px;
}

/* Chart Cards */
.chart-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    margin-bottom: 20px;
    border: 1px solid #e8ecef;
    transition: box-shadow 0.2s ease;
}
.chart-card:hover {
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
}
.chart-card .chart-card-header {
    padding: 14px 18px 10px;
    border-bottom: 1px solid #f0f2f5;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.chart-card .chart-card-header h6 {
    font-size: 14px;
    font-weight: 700;
    color: #1B2838;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.chart-card .chart-card-header .filter-indicator {
    display: none;
    font-size: 11px;
    color: #00897B;
    font-weight: 600;
}
.chart-card .chart-card-header .filter-indicator.active {
    display: inline-block;
}
.chart-card .chart-card-body {
    padding: 10px 15px 15px;
}

/* Filter Bar */
.filter-bar {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    padding: 15px 20px;
    margin-bottom: 20px;
    border: 1px solid #e8ecef;
}
.filter-bar label {
    font-size: 12px;
    font-weight: 600;
    color: #555;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}
.filter-bar .form-control-sm {
    font-size: 13px;
    border-radius: 6px;
    border: 1px solid #ddd;
}
.filter-bar .form-control-sm:focus {
    border-color: #00897B;
    box-shadow: 0 0 0 0.15rem rgba(0,137,123,0.25);
}
.filter-bar .btn {
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    padding: 6px 16px;
}

/* Active Filter Badges */
#activeFilterBadges {
    padding: 8px 0 0;
    display: none;
}
#activeFilterBadges .badge {
    background: #00897B;
    border-radius: 20px;
    font-weight: 500;
    transition: background 0.2s;
    user-select: none;
}
#activeFilterBadges .badge:hover {
    background: #00695C;
}

/* Heatmap Tables */
.heatmap-table {
    font-size: 12px;
    border-collapse: collapse;
    width: 100%;
}
.heatmap-table thead th {
    background: #1B2838;
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 8px 6px;
    text-align: center;
    white-space: nowrap;
    position: sticky;
    top: 0;
    z-index: 1;
}
.heatmap-table thead th:first-child {
    text-align: left;
    min-width: 140px;
}
.heatmap-table tbody td {
    padding: 6px 6px;
    text-align: center;
    font-weight: 500;
    border: 1px solid #eee;
    transition: background 0.15s;
}
.heatmap-table tbody td:first-child {
    text-align: left;
    font-weight: 600;
    color: #333;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 180px;
}
.heatmap-table tbody tr:hover td {
    opacity: 0.9;
}
.heatmap-table tfoot td {
    background: #f8f9fa;
    font-weight: 700;
    padding: 8px 6px;
    text-align: center;
    border-top: 2px solid #1B2838;
    color: #E53935;
}
.heatmap-table tfoot td:first-child {
    text-align: right;
    text-transform: uppercase;
    color: #333;
}
.heatmap-table .ytd-col {
    background: #f0f2f5 !important;
    font-weight: 700;
    color: #E53935;
}

/* Export Buttons */
.export-bar {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    padding: 15px 20px;
    margin-bottom: 20px;
    border: 1px solid #e8ecef;
    text-align: right;
}
.export-bar .btn {
    border-radius: 6px;
    font-weight: 600;
    padding: 8px 20px;
}

/* Loading Skeleton */
.skeleton-pulse {
    animation: skeleton-pulse 1.5s ease-in-out infinite;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    border-radius: 4px;
}
@keyframes skeleton-pulse {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Cross-filter active state */
.cross-filter-active {
    box-shadow: 0 0 0 3px #00897B, 0 4px 20px rgba(0,137,123,0.3) !important;
}

/* No data message */
.no-data-message {
    text-align: center;
    padding: 40px 20px;
    color: #999;
    font-size: 14px;
}
.no-data-message i {
    font-size: 40px;
    display: block;
    margin-bottom: 10px;
    opacity: 0.3;
}

/* ── Print Styles ── */
@media print {
    body {
        background-color: #fff !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .content-wrapper {
        margin-left: 0 !important;
        padding: 0 !important;
        background-color: #fff !important;
    }
    .main-footer, .main-header, .main-sidebar, .d-print-none {
        display: none !important;
    }
    .kpi-card {
        background: #1B2838 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        box-shadow: none !important;
        border: 1px solid #ccc;
    }
    .chart-card {
        box-shadow: none !important;
        border: 1px solid #ccc !important;
        page-break-inside: avoid;
        margin-bottom: 15px !important;
    }
    .filter-bar, .export-bar {
        display: none !important;
    }
    #activeFilterBadges {
        display: none !important;
    }
    .heatmap-table thead th {
        background: #1B2838 !important;
        color: #fff !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .col-lg-6, .col-lg-4, .col-lg-8 {
        page-break-inside: avoid;
    }
    h1 { font-size: 22px; text-align: center; margin-bottom: 15px; }
}
</style>

<!-- Content Wrapper Start -->
<div class="content-wrapper dashboard-wrapper">

    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-8">
                    <h1 class="m-0" style="font-size: 22px; font-weight: 700; color: #1B2838;">
                        <i class="fas fa-chart-pie mr-2" style="color: #00897B;"></i><?php echo $title ?>
                    </h1>
                </div>
                <div class="col-sm-4 text-right d-print-none">
                    <button class="btn btn-outline-success btn-sm mr-1" id="exportExcel">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                    <button class="btn btn-outline-primary btn-sm" id="printReport">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Start -->
    <section class="content">
        <div class="container-fluid">

            <!-- ═══════════ FILTER BAR ═══════════ -->
            <div class="filter-bar d-print-none">
                <div class="row align-items-end">
                    <div class="col-lg-2 col-md-3 col-sm-6 mb-2">
                        <label for="filterYear">Year</label>
                        <select class="form-control form-control-sm" id="filterYear">
                            <?php foreach ($years as $y): ?>
                                <option value="<?php echo $y->yr; ?>" <?php echo ($y->yr == $currentYear) ? 'selected' : ''; ?>>
                                    <?php echo $y->yr; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6 mb-2">
                        <label for="filterMonth">Month</label>
                        <select class="form-control form-control-sm" id="filterMonth">
                            <option value="">All Months</option>
                            <option value="1">January</option>
                            <option value="2">February</option>
                            <option value="3">March</option>
                            <option value="4">April</option>
                            <option value="5">May</option>
                            <option value="6">June</option>
                            <option value="7">July</option>
                            <option value="8">August</option>
                            <option value="9">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6 mb-2">
                        <label for="filterDepartment">Department</label>
                        <select class="form-control form-control-sm" id="filterDepartment">
                            <option value="">All Departments</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept->Department_ID; ?>"><?php echo htmlspecialchars((string)$dept->Department); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6 mb-2">
                        <label for="filterDesignation">Designation</label>
                        <select class="form-control form-control-sm" id="filterDesignation">
                            <option value="">All Designations</option>
                            <?php foreach ($designations as $desig): ?>
                                <option value="<?php echo $desig->Designation_ID; ?>"><?php echo htmlspecialchars((string)$desig->Designation); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-1 col-md-3 col-sm-6 mb-2">
                        <label for="filterGender">Gender</label>
                        <select class="form-control form-control-sm" id="filterGender">
                            <option value="">All</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6 mb-2">
                        <label for="filterCategory">Employee Category</label>
                        <select class="form-control form-control-sm" id="filterCategory">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat->Category_ID; ?>"><?php echo htmlspecialchars((string)$cat->Employee_Category); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-1 col-md-3 col-sm-6 mb-2">
                        <label>&nbsp;</label>
                        <div class="d-flex">
                            <button class="btn btn-success btn-sm flex-fill mr-1" id="applyFilters" title="Apply Filters">
                                <i class="fas fa-filter"></i> Apply
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" id="resetFilters" title="Reset Filters">
                                <i class="fas fa-undo"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Active Filter Badges -->
                <div id="activeFilterBadges"></div>
            </div>

            <!-- ═══════════ KPI CARDS ═══════════ -->
            <div class="row mb-3">
                <div class="col-lg col-md-4 col-sm-6 mb-2">
                    <div class="kpi-card">
                        <i class="fas fa-users-slash kpi-icon"></i>
                        <div class="kpi-value" id="kpiTotal">0</div>
                        <div class="kpi-label">Total Turnover</div>
                        <div class="kpi-sublabel">Total Separations</div>
                    </div>
                </div>
                <div class="col-lg col-md-4 col-sm-6 mb-2">
                    <div class="kpi-card" style="border-left-color: #E53935;">
                        <i class="fas fa-calendar-day kpi-icon" style="color: #E53935;"></i>
                        <div class="kpi-value" id="kpiMonthly">0</div>
                        <div class="kpi-label">Current Month</div>
                        <div class="kpi-sublabel" id="kpiMonthLabel"><?php echo date('F Y'); ?></div>
                    </div>
                </div>
                <div class="col-lg col-md-4 col-sm-6 mb-2">
                    <div class="kpi-card" style="border-left-color: #FF8F00;">
                        <i class="fas fa-chart-line kpi-icon" style="color: #FF8F00;"></i>
                        <div class="kpi-value" id="kpiYearly">0</div>
                        <div class="kpi-label">Year To Date</div>
                        <div class="kpi-sublabel" id="kpiYearLabel">YTD <?php echo date('Y'); ?></div>
                    </div>
                </div>
                <div class="col-lg col-md-4 col-sm-6 mb-2">
                    <div class="kpi-card" style="border-left-color: #6A1B9A;">
                        <i class="fas fa-business-time kpi-icon" style="color: #6A1B9A;"></i>
                        <div class="kpi-value" id="kpiTenure">0</div>
                        <div class="kpi-label">Avg Tenure</div>
                        <div class="kpi-sublabel">Years of Service</div>
                    </div>
                </div>
                <div class="col-lg col-md-4 col-sm-6 mb-2">
                    <div class="kpi-card" style="border-left-color: #0097A7;">
                        <i class="fas fa-venus-mars kpi-icon" style="color: #0097A7;"></i>
                        <div>
                            <span class="kpi-value" style="font-size: 18px;"><i class="fas fa-mars" style="color: #42A5F5; font-size: 14px;"></i> <span id="kpiMalePercent">0%</span></span>
                            <span class="kpi-value" style="font-size: 18px; margin-left: 8px;"><i class="fas fa-venus" style="color: #EC407A; font-size: 14px;"></i> <span id="kpiFemalePercent">0%</span></span>
                        </div>
                        <div class="kpi-label">Gender Split</div>
                        <div class="kpi-sublabel">
                            M: <span id="kpiMaleCount">0</span> | F: <span id="kpiFemaleCount">0</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══════════ ROW 1: Monthly Trend + Voluntary/Involuntary ═══════════ -->
            <div class="row">
                <div class="col-lg-8 mb-3">
                    <div class="chart-card">
                        <div class="chart-card-header">
                            <h6><i class="fas fa-chart-area mr-1" style="color: #0D47A1;"></i> Monthly Turnover Trend</h6>
                            <span class="filter-indicator" id="indicatorMonthly"><i class="fas fa-filter"></i> Filtered</span>
                        </div>
                        <div class="chart-card-body">
                            <div id="chartMonthlyTrend" style="min-height: 340px;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-3">
                    <div class="chart-card">
                        <div class="chart-card-header">
                            <h6><i class="fas fa-chart-pie mr-1" style="color: #00897B;"></i> Voluntary vs Involuntary</h6>
                            <span class="filter-indicator" id="indicatorVolInvol"><i class="fas fa-filter"></i> Filtered</span>
                        </div>
                        <div class="chart-card-body">
                            <div id="chartVolInvol" style="min-height: 340px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══════════ ROW 2: Department + Designation ═══════════ -->
            <div class="row">
                <div class="col-lg-6 mb-3">
                    <div class="chart-card">
                        <div class="chart-card-header">
                            <h6><i class="fas fa-building mr-1" style="color: #E53935;"></i> Turnover by Department</h6>
                            <span class="filter-indicator" id="indicatorDept"><i class="fas fa-filter"></i> Filtered</span>
                        </div>
                        <div class="chart-card-body">
                            <div id="chartDepartment" style="min-height: 300px;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="chart-card">
                        <div class="chart-card-header">
                            <h6><i class="fas fa-user-tie mr-1" style="color: #FF8F00;"></i> Turnover by Designation</h6>
                            <span class="filter-indicator" id="indicatorDesig"><i class="fas fa-filter"></i> Filtered</span>
                        </div>
                        <div class="chart-card-body">
                            <div id="chartDesignation" style="min-height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══════════ ROW 3: Reasons + Tenure ═══════════ -->
            <div class="row">
                <div class="col-lg-6 mb-3">
                    <div class="chart-card">
                        <div class="chart-card-header">
                            <h6><i class="fas fa-sign-out-alt mr-1" style="color: #6A1B9A;"></i> Reasons for Leaving</h6>
                            <span class="filter-indicator" id="indicatorReasons"><i class="fas fa-filter"></i> Filtered</span>
                        </div>
                        <div class="chart-card-body">
                            <div id="chartReasons" style="min-height: 300px;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="chart-card">
                        <div class="chart-card-header">
                            <h6><i class="fas fa-hourglass-half mr-1" style="color: #2E7D32;"></i> Tenure of Service</h6>
                        </div>
                        <div class="chart-card-body">
                            <div id="chartTenure" style="min-height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══════════ ROW 4: Category + Year Comparison ═══════════ -->
            <div class="row">
                <div class="col-lg-4 mb-3">
                    <div class="chart-card">
                        <div class="chart-card-header">
                            <h6><i class="fas fa-layer-group mr-1" style="color: #0097A7;"></i> Employee Category</h6>
                            <span class="filter-indicator" id="indicatorCategory"><i class="fas fa-filter"></i> Filtered</span>
                        </div>
                        <div class="chart-card-body">
                            <div id="chartCategory" style="min-height: 320px;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8 mb-3">
                    <div class="chart-card">
                        <div class="chart-card-header">
                            <h6><i class="fas fa-chart-bar mr-1" style="color: #283593;"></i> Year-over-Year Comparison</h6>
                        </div>
                        <div class="chart-card-body">
                            <div id="chartYearComparison" style="min-height: 320px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══════════ ROW 5: Department × Month Matrix ═══════════ -->
            <div class="row">
                <div class="col-12 mb-3">
                    <div class="chart-card">
                        <div class="chart-card-header">
                            <h6><i class="fas fa-th mr-1" style="color: #1B2838;"></i> Department × Monthly Turnover Matrix</h6>
                        </div>
                        <div class="chart-card-body p-0">
                            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                <div id="deptMonthMatrix">
                                    <div class="no-data-message">
                                        <i class="fas fa-table"></i>
                                        Data will appear after loading
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══════════ ROW 6: Reason × Department Matrix ═══════════ -->
            <div class="row">
                <div class="col-12 mb-3">
                    <div class="chart-card">
                        <div class="chart-card-header">
                            <h6><i class="fas fa-project-diagram mr-1" style="color: #6A1B9A;"></i> Reasons by Department Breakdown</h6>
                        </div>
                        <div class="chart-card-body p-0">
                            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                <div id="reasonDeptMatrix">
                                    <div class="no-data-message">
                                        <i class="fas fa-table"></i>
                                        Data will appear after loading
                                    </div>
                                </div>
                            </div>
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
