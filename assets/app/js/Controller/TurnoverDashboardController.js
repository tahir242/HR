/**
 * TurnoverDashboardController.js
 * Interactive Employee Turnover Dashboard — Power BI-like cross-filtering
 * 
 * Dependencies: jQuery 3, ApexCharts, Axios, SweetAlert2, SheetJS (XLSX)
 */

function docReady(fn) {
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(fn, 1);
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}

docReady(function () {

    /* ===================================================================
     *  CONSTANTS
     * =================================================================== */
    var COLORS = {
        primary:  '#0D47A1',
        teal:     '#00897B',
        coral:    '#E53935',
        amber:    '#FF8F00',
        purple:   '#6A1B9A',
        green:    '#2E7D32',
        cyan:     '#0097A7',
        indigo:   '#283593',
        palette: [
            '#0D47A1', '#00897B', '#E53935', '#FF8F00',
            '#6A1B9A', '#2E7D32', '#0097A7', '#F57C00',
            '#1565C0', '#00695C', '#C62828', '#EF6C00',
            '#4A148C', '#1B5E20'
        ]
    };

    var MONTH_NAMES  = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    var MONTH_FULL   = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    var TENURE_LABELS = ['<1 Year', '1-3 Years', '3-5 Years', '5-10 Years', '10+ Years'];

    /* ===================================================================
     *  STATE
     * =================================================================== */
    var rawData        = [];
    var filteredData   = [];
    var activeFilters  = {};
    var crossFilters   = {};
    var chartInstances = {};
    var allYearsData   = [];

    /* ===================================================================
     *  DATA FETCHING
     * =================================================================== */
    function fetchData(year) {
        window.swal.fire({
            title: 'Loading Dashboard...',
            text: 'Fetching turnover data',
            allowOutsideClick: false,
            didOpen: function () { window.swal.showLoading(); }
        });

        var formData = new FormData();
        formData.append('action_type', 'GET_DASHBOARD_DATA');
        formData.append('year', year || new Date().getFullYear());

        axios.post(window.baseUrl + '/_inc/report_turnover_dashboard.php', formData)
            .then(function (response) {
                window.swal.close();
                if (response.data.valid) {
                    rawData = response.data.records || [];
                    fetchAllYearsData();
                    applyFiltersAndRender();
                } else {
                    window.swal.fire({ title: 'Error!', text: response.data.errorMsg || 'Unknown error.', icon: 'error' });
                }
            })
            .catch(function (error) {
                var msg = 'Failed to load dashboard data.';
                if (error.response && error.response.data && error.response.data.errorMsg) {
                    msg = error.response.data.errorMsg;
                }
                window.swal.fire({ title: 'Error!', text: msg, icon: 'error' });
            });
    }

    function fetchAllYearsData() {
        var formData = new FormData();
        formData.append('action_type', 'GET_DASHBOARD_DATA');
        formData.append('year', 'all');

        axios.post(window.baseUrl + '/_inc/report_turnover_dashboard.php', formData)
            .then(function (response) {
                if (response.data.valid) {
                    allYearsData = response.data.records || [];
                    // Re-render only the year comparison chart once data arrives
                    renderYearComparison(computeYearComparison());
                }
            })
            .catch(function () {
                allYearsData = [];
            });
    }

    /* ===================================================================
     *  FILTER APPLICATION
     * =================================================================== */
    function applyFiltersAndRender() {
        var allFilters = Object.assign({}, activeFilters, crossFilters);

        filteredData = rawData.filter(function (record) {
            if (allFilters.month && parseInt(record.Leave_Month) !== parseInt(allFilters.month)) return false;
            if (allFilters.department && record.Department !== allFilters.department) return false;
            if (allFilters.designation && record.Designation !== allFilters.designation) return false;
            if (allFilters.gender && record.Gender !== allFilters.gender) return false;
            if (allFilters.category && record.Employee_Category !== allFilters.category) return false;
            if (allFilters.resignationType && record.Resignation_Type !== allFilters.resignationType) return false;
            if (allFilters.reason && record.Reason !== allFilters.reason) return false;
            return true;
        });

        renderAll();
        updateFilterBadges();
    }

    /* ===================================================================
     *  AGGREGATION / COMPUTE FUNCTIONS
     * =================================================================== */

    // ── KPIs ────────────────────────────────────────────────────────────
    function computeKPIs() {
        var total = filteredData.length;

        var selectedYear = parseInt($('#filterYear').val()) || new Date().getFullYear();
        var currentMonth = new Date().getMonth() + 1;

        var monthly = filteredData.filter(function (r) {
            return parseInt(r.Leave_Month) === currentMonth && parseInt(r.Leave_Year) === selectedYear;
        }).length;

        var yearly = total; // data is already scoped by year from API

        var tenureSum = 0;
        var tenureCount = 0;
        var maleCount = 0;
        var femaleCount = 0;

        filteredData.forEach(function (r) {
            var t = parseFloat(r.Tenure_Months);
            if (!isNaN(t)) { tenureSum += t; tenureCount++; }
            if (r.Gender === 'Male') maleCount++;
            if (r.Gender === 'Female') femaleCount++;
        });

        var avgTenure = tenureCount > 0 ? (tenureSum / tenureCount / 12).toFixed(1) : '0.0';
        var malePercent  = total > 0 ? Math.round(maleCount / total * 100) : 0;
        var femalePercent = total > 0 ? Math.round(femaleCount / total * 100) : 0;

        return {
            total: total,
            monthly: monthly,
            yearly: yearly,
            avgTenure: avgTenure,
            maleCount: maleCount,
            femaleCount: femaleCount,
            malePercent: malePercent,
            femalePercent: femalePercent
        };
    }

    // ── Monthly Trend ───────────────────────────────────────────────────
    function computeMonthlyTrend() {
        var counts = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        filteredData.forEach(function (r) {
            var m = parseInt(r.Leave_Month);
            if (m >= 1 && m <= 12) counts[m - 1]++;
        });

        var cumulative = [];
        var running = 0;
        counts.forEach(function (c) {
            running += c;
            cumulative.push(running);
        });

        return { months: MONTH_NAMES.slice(), counts: counts, cumulative: cumulative };
    }

    // ── Department Breakdown ────────────────────────────────────────────
    function computeDeptBreakdown() {
        var map = {};
        filteredData.forEach(function (r) {
            var d = r.Department || 'Unknown';
            map[d] = (map[d] || 0) + 1;
        });

        var sorted = Object.keys(map).map(function (k) { return { label: k, count: map[k] }; })
            .sort(function (a, b) { return b.count - a.count; })
            .slice(0, 15);

        var total = filteredData.length || 1;
        return {
            labels:      sorted.map(function (s) { return s.label; }),
            counts:      sorted.map(function (s) { return s.count; }),
            percentages: sorted.map(function (s) { return Math.round(s.count / total * 100); })
        };
    }

    // ── Designation Breakdown ───────────────────────────────────────────
    function computeDesigBreakdown() {
        var map = {};
        filteredData.forEach(function (r) {
            var d = r.Designation || 'Unknown';
            map[d] = (map[d] || 0) + 1;
        });

        var sorted = Object.keys(map).map(function (k) { return { label: k, count: map[k] }; })
            .sort(function (a, b) { return b.count - a.count; })
            .slice(0, 15);

        var total = filteredData.length || 1;
        return {
            labels:      sorted.map(function (s) { return s.label; }),
            counts:      sorted.map(function (s) { return s.count; }),
            percentages: sorted.map(function (s) { return Math.round(s.count / total * 100); })
        };
    }

    // ── Reasons ─────────────────────────────────────────────────────────
    function computeReasons() {
        var map = {};
        var typeMap = {};
        filteredData.forEach(function (r) {
            var reason = r.Reason || 'Unknown';
            map[reason] = (map[reason] || 0) + 1;
            if (!typeMap[reason]) typeMap[reason] = r.Resignation_Type || 'Unknown';
        });

        var sorted = Object.keys(map).map(function (k) { return { label: k, count: map[k], type: typeMap[k] }; })
            .sort(function (a, b) { return b.count - a.count; })
            .slice(0, 10);

        return {
            labels: sorted.map(function (s) { return s.label; }),
            counts: sorted.map(function (s) { return s.count; }),
            types:  sorted.map(function (s) { return s.type; })
        };
    }

    // ── Voluntary vs Involuntary ────────────────────────────────────────
    function computeVolInvol() {
        var voluntary   = 0;
        var involuntary = 0;
        var unknown     = 0;

        filteredData.forEach(function (r) {
            var t = (r.Resignation_Type || '').toLowerCase();
            if (t.indexOf('voluntary') !== -1 && t.indexOf('involuntary') === -1) {
                voluntary++;
            } else if (t.indexOf('involuntary') !== -1) {
                involuntary++;
            } else {
                unknown++;
            }
        });

        return { voluntary: voluntary, involuntary: involuntary, unknown: unknown };
    }

    // ── Tenure Buckets ──────────────────────────────────────────────────
    function computeTenureBuckets() {
        var buckets = [0, 0, 0, 0, 0];
        filteredData.forEach(function (r) {
            var m = parseFloat(r.Tenure_Months);
            if (isNaN(m)) return;
            if (m < 12)       buckets[0]++;
            else if (m < 36)  buckets[1]++;
            else if (m < 60)  buckets[2]++;
            else if (m < 120) buckets[3]++;
            else              buckets[4]++;
        });

        return { labels: TENURE_LABELS.slice(), counts: buckets };
    }

    // ── Category Breakdown ──────────────────────────────────────────────
    function computeCategoryBreakdown() {
        var map = {};
        filteredData.forEach(function (r) {
            var c = r.Employee_Category || 'Unknown';
            map[c] = (map[c] || 0) + 1;
        });

        var labels = Object.keys(map);
        var counts = labels.map(function (l) { return map[l]; });
        return { labels: labels, counts: counts };
    }

    // ── Year-over-Year Comparison ───────────────────────────────────────
    function computeYearComparison() {
        var source = allYearsData.length ? allYearsData : rawData;
        var map = {};
        source.forEach(function (r) {
            var y = parseInt(r.Leave_Year);
            if (!isNaN(y)) map[y] = (map[y] || 0) + 1;
        });

        var years = Object.keys(map).map(Number).sort(function (a, b) { return a - b; });
        // Take last 5 years
        if (years.length > 5) years = years.slice(years.length - 5);

        return {
            labels: years.map(String),
            counts: years.map(function (y) { return map[y] || 0; })
        };
    }

    // ── Department × Month Matrix ───────────────────────────────────────
    function computeDeptMonthMatrix() {
        var deptSet = {};
        var matrix  = {};
        var totals  = {};
        var monthTotals = {};

        filteredData.forEach(function (r) {
            var dept = r.Department || 'Unknown';
            var m    = parseInt(r.Leave_Month);
            if (isNaN(m) || m < 1 || m > 12) return;

            deptSet[dept] = true;
            if (!matrix[dept]) matrix[dept] = {};
            matrix[dept][m] = (matrix[dept][m] || 0) + 1;
            totals[dept] = (totals[dept] || 0) + 1;
            monthTotals[m] = (monthTotals[m] || 0) + 1;
        });

        var departments = Object.keys(deptSet).sort(function (a, b) {
            return (totals[b] || 0) - (totals[a] || 0);
        });

        return {
            departments: departments,
            months: MONTH_NAMES.slice(),
            matrix: matrix,
            totals: totals,
            monthTotals: monthTotals
        };
    }

    // ── Reason × Department Matrix ──────────────────────────────────────
    function computeReasonDeptMatrix() {
        var reasonSet = {};
        var deptSet   = {};
        var matrix    = {};
        var rowTotals = {};
        var colTotals = {};

        filteredData.forEach(function (r) {
            var reason = r.Reason || 'Unknown';
            var dept   = r.Department || 'Unknown';

            reasonSet[reason] = true;
            deptSet[dept] = true;

            if (!matrix[reason]) matrix[reason] = {};
            matrix[reason][dept] = (matrix[reason][dept] || 0) + 1;
            rowTotals[reason] = (rowTotals[reason] || 0) + 1;
            colTotals[dept]   = (colTotals[dept] || 0) + 1;
        });

        var reasons = Object.keys(reasonSet).sort(function (a, b) {
            return (rowTotals[b] || 0) - (rowTotals[a] || 0);
        });

        var departments = Object.keys(deptSet).sort(function (a, b) {
            return (colTotals[b] || 0) - (colTotals[a] || 0);
        });

        return {
            reasons: reasons,
            departments: departments,
            matrix: matrix,
            rowTotals: rowTotals,
            colTotals: colTotals
        };
    }

    /* ===================================================================
     *  RENDERING — renderAll orchestrator
     * =================================================================== */
    function renderAll() {
        var kpis = computeKPIs();
        renderKPICards(kpis);
        renderMonthlyTrend(computeMonthlyTrend());
        renderVolInvolDonut(computeVolInvol());
        renderDeptBar(computeDeptBreakdown());
        renderDesigBar(computeDesigBreakdown());
        renderReasonsBar(computeReasons());
        renderTenureBar(computeTenureBuckets());
        renderCategoryDonut(computeCategoryBreakdown());
        renderYearComparison(computeYearComparison());
        renderDeptMonthTable(computeDeptMonthMatrix());
        renderReasonDeptTable(computeReasonDeptMatrix());
    }

    /* ===================================================================
     *  KPI CARDS
     * =================================================================== */
    function renderKPICards(kpis) {
        var selectedYear = $('#filterYear').val() || new Date().getFullYear();
        var currentMonth = new Date().getMonth(); // 0-based

        animateValue(document.getElementById('kpiTotal'), 0, kpis.total, 600);
        animateValue(document.getElementById('kpiMonthly'), 0, kpis.monthly, 600);
        animateValue(document.getElementById('kpiYearly'), 0, kpis.yearly, 600);

        var kpiTenureEl = document.getElementById('kpiTenure');
        if (kpiTenureEl) kpiTenureEl.textContent = kpis.avgTenure + ' yrs';

        var kpiMonthLabel = document.getElementById('kpiMonthLabel');
        if (kpiMonthLabel) kpiMonthLabel.textContent = MONTH_FULL[currentMonth] || '';

        var kpiYearLabel = document.getElementById('kpiYearLabel');
        if (kpiYearLabel) kpiYearLabel.textContent = selectedYear;

        var kpiMalePercent = document.getElementById('kpiMalePercent');
        if (kpiMalePercent) kpiMalePercent.textContent = kpis.malePercent + '%';

        var kpiFemalePercent = document.getElementById('kpiFemalePercent');
        if (kpiFemalePercent) kpiFemalePercent.textContent = kpis.femalePercent + '%';

        var kpiMaleCount = document.getElementById('kpiMaleCount');
        if (kpiMaleCount) kpiMaleCount.textContent = formatNumber(kpis.maleCount);

        var kpiFemaleCount = document.getElementById('kpiFemaleCount');
        if (kpiFemaleCount) kpiFemaleCount.textContent = formatNumber(kpis.femaleCount);
    }

    /* ===================================================================
     *  CHART — Monthly Trend (column + line)
     * =================================================================== */
    function renderMonthlyTrend(data) {
        if (chartInstances.monthlyTrend) chartInstances.monthlyTrend.destroy();

        var el = document.querySelector('#chartMonthlyTrend');
        if (!el) return;

        var options = {
            series: [
                { name: 'Monthly Turnover', type: 'column', data: data.counts },
                { name: 'Cumulative YTD',   type: 'line',   data: data.cumulative }
            ],
            chart: {
                height: 350,
                type: 'line',
                toolbar: { show: true, tools: { download: true, selection: false, zoom: false, zoomin: false, zoomout: false, pan: false, reset: false } },
                fontFamily: 'inherit',
                events: {
                    dataPointSelection: function (event, chartContext, config) {
                        if (config.seriesIndex === 0) { // only on column clicks
                            var monthIndex = config.dataPointIndex;
                            toggleCrossFilter('month', monthIndex + 1);
                        }
                    }
                }
            },
            stroke: { width: [0, 3], curve: 'smooth' },
            colors: [COLORS.primary, COLORS.coral],
            fill: { type: ['solid', 'solid'], opacity: [0.85, 1] },
            plotOptions: {
                bar: { borderRadius: 3, columnWidth: '55%' }
            },
            dataLabels: {
                enabled: true,
                enabledOnSeries: [0, 1],
                style: { fontSize: '11px', fontWeight: 600 },
                background: { enabled: true, foreColor: '#000', borderRadius: 2, padding: 4, borderWidth: 0 }
            },
            xaxis: {
                categories: data.months,
                labels: { style: { fontSize: '12px', fontWeight: 500 } }
            },
            yaxis: [
                { title: { text: 'Monthly Count' }, min: 0 },
                { opposite: true, title: { text: 'Cumulative (YTD)' }, min: 0 }
            ],
            grid: {
                borderColor: '#e7e7e7',
                row: { colors: ['#f3f3f3', 'transparent'], opacity: 0.5 }
            },
            legend: { position: 'top', horizontalAlign: 'center' },
            tooltip: { shared: true, intersect: false }
        };

        var chart = new ApexCharts(el, options);
        chart.render();
        chartInstances.monthlyTrend = chart;
    }

    /* ===================================================================
     *  CHART — Voluntary vs Involuntary Donut
     * =================================================================== */
    function renderVolInvolDonut(data) {
        if (chartInstances.volInvol) chartInstances.volInvol.destroy();

        var el = document.querySelector('#chartVolInvol');
        if (!el) return;

        var labels = [];
        var series = [];
        var colors = [];

        if (data.voluntary > 0)   { labels.push('Voluntary');   series.push(data.voluntary);   colors.push(COLORS.teal); }
        if (data.involuntary > 0) { labels.push('Involuntary'); series.push(data.involuntary); colors.push(COLORS.coral); }
        if (data.unknown > 0)     { labels.push('Unknown');     series.push(data.unknown);     colors.push('#9E9E9E'); }

        if (series.length === 0) {
            labels = ['No Data']; series = [1]; colors = ['#E0E0E0'];
        }

        var totalVal = data.voluntary + data.involuntary + data.unknown;

        var options = {
            series: series,
            labels: labels,
            chart: {
                type: 'donut',
                height: 350,
                toolbar: { show: false },
                fontFamily: 'inherit',
                events: {
                    dataPointSelection: function (event, chartContext, config) {
                        var label = config.w.config.labels[config.dataPointIndex];
                        if (label && label !== 'No Data') {
                            toggleCrossFilter('resignationType', label);
                        }
                    }
                }
            },
            colors: colors,
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            name: { show: true, fontSize: '14px', fontWeight: 600, offsetY: -10 },
                            value: { show: true, fontSize: '24px', fontWeight: 700, offsetY: 6, formatter: function (val) { return formatNumber(parseInt(val)); } },
                            total: {
                                show: true,
                                label: 'Total',
                                fontSize: '13px',
                                fontWeight: 400,
                                color: '#666',
                                formatter: function () { return formatNumber(totalVal); }
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            legend: {
                position: 'bottom',
                fontSize: '13px',
                formatter: function (seriesName, opts) {
                    var count = opts.w.globals.series[opts.seriesIndex];
                    var pct = totalVal > 0 ? Math.round(count / totalVal * 100) : 0;
                    return seriesName + ': ' + formatNumber(count) + ' (' + pct + '%)';
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        var pct = totalVal > 0 ? Math.round(val / totalVal * 100) : 0;
                        return formatNumber(val) + ' (' + pct + '%)';
                    }
                }
            },
            stroke: { width: 2 },
            responsive: [{ breakpoint: 480, options: { chart: { height: 300 }, legend: { position: 'bottom' } } }]
        };

        var chart = new ApexCharts(el, options);
        chart.render();
        chartInstances.volInvol = chart;
    }

    /* ===================================================================
     *  CHART — Department Horizontal Bar
     * =================================================================== */
    function renderDeptBar(data) {
        if (chartInstances.department) chartInstances.department.destroy();

        var el = document.querySelector('#chartDepartment');
        if (!el) return;

        var dynamicHeight = Math.max(250, data.labels.length * 32);

        var options = {
            series: [{ name: 'Turnover', data: data.counts }],
            chart: {
                type: 'bar',
                height: dynamicHeight,
                toolbar: { show: true, tools: { download: true, selection: false, zoom: false, zoomin: false, zoomout: false, pan: false, reset: false } },
                fontFamily: 'inherit',
                events: {
                    dataPointSelection: function (event, chartContext, config) {
                        var idx = config.dataPointIndex;
                        toggleCrossFilter('department', data.labels[idx]);
                    }
                }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 3,
                    barHeight: '70%',
                    distributed: false,
                    dataLabels: { position: 'right' }
                }
            },
            colors: [COLORS.primary],
            fill: {
                type: 'gradient',
                gradient: { shade: 'light', type: 'horizontal', shadeIntensity: 0.2, opacityFrom: 0.85, opacityTo: 1, stops: [0, 100] }
            },
            dataLabels: {
                enabled: true,
                textAnchor: 'start',
                offsetX: 5,
                style: { fontSize: '11px', fontWeight: 600, colors: ['#333'] },
                formatter: function (val, opt) {
                    return val + ' (' + data.percentages[opt.dataPointIndex] + '%)';
                }
            },
            xaxis: {
                categories: data.labels,
                labels: { style: { fontSize: '11px' } }
            },
            yaxis: { labels: { style: { fontSize: '12px', fontWeight: 500 }, maxWidth: 200 } },
            grid: { borderColor: '#f1f1f1', xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } } },
            tooltip: {
                y: { formatter: function (val, opt) { return val + ' (' + data.percentages[opt.dataPointIndex] + '%)'; } }
            }
        };

        var chart = new ApexCharts(el, options);
        chart.render();
        chartInstances.department = chart;
    }

    /* ===================================================================
     *  CHART — Designation Horizontal Bar
     * =================================================================== */
    function renderDesigBar(data) {
        if (chartInstances.designation) chartInstances.designation.destroy();

        var el = document.querySelector('#chartDesignation');
        if (!el) return;

        var dynamicHeight = Math.max(250, data.labels.length * 32);

        var options = {
            series: [{ name: 'Turnover', data: data.counts }],
            chart: {
                type: 'bar',
                height: dynamicHeight,
                toolbar: { show: true, tools: { download: true, selection: false, zoom: false, zoomin: false, zoomout: false, pan: false, reset: false } },
                fontFamily: 'inherit',
                events: {
                    dataPointSelection: function (event, chartContext, config) {
                        var idx = config.dataPointIndex;
                        toggleCrossFilter('designation', data.labels[idx]);
                    }
                }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 3,
                    barHeight: '70%',
                    distributed: false,
                    dataLabels: { position: 'right' }
                }
            },
            colors: [COLORS.teal],
            fill: {
                type: 'gradient',
                gradient: { shade: 'light', type: 'horizontal', shadeIntensity: 0.2, opacityFrom: 0.85, opacityTo: 1, stops: [0, 100] }
            },
            dataLabels: {
                enabled: true,
                textAnchor: 'start',
                offsetX: 5,
                style: { fontSize: '11px', fontWeight: 600, colors: ['#333'] },
                formatter: function (val, opt) {
                    return val + ' (' + data.percentages[opt.dataPointIndex] + '%)';
                }
            },
            xaxis: {
                categories: data.labels,
                labels: { style: { fontSize: '11px' } }
            },
            yaxis: { labels: { style: { fontSize: '12px', fontWeight: 500 }, maxWidth: 200 } },
            grid: { borderColor: '#f1f1f1', xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } } },
            tooltip: {
                y: { formatter: function (val, opt) { return val + ' (' + data.percentages[opt.dataPointIndex] + '%)'; } }
            }
        };

        var chart = new ApexCharts(el, options);
        chart.render();
        chartInstances.designation = chart;
    }

    /* ===================================================================
     *  CHART — Reasons Horizontal Bar
     * =================================================================== */
    function renderReasonsBar(data) {
        if (chartInstances.reasons) chartInstances.reasons.destroy();

        var el = document.querySelector('#chartReasons');
        if (!el) return;

        var dynamicHeight = Math.max(250, data.labels.length * 32);

        // Color each bar based on its resignation type
        var barColors = data.types.map(function (t) {
            var lower = (t || '').toLowerCase();
            if (lower.indexOf('involuntary') !== -1) return COLORS.coral;
            if (lower.indexOf('voluntary') !== -1) return COLORS.teal;
            return '#9E9E9E';
        });

        var options = {
            series: [{ name: 'Count', data: data.counts }],
            chart: {
                type: 'bar',
                height: dynamicHeight,
                toolbar: { show: true, tools: { download: true, selection: false, zoom: false, zoomin: false, zoomout: false, pan: false, reset: false } },
                fontFamily: 'inherit',
                events: {
                    dataPointSelection: function (event, chartContext, config) {
                        var idx = config.dataPointIndex;
                        toggleCrossFilter('reason', data.labels[idx]);
                    }
                }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 3,
                    barHeight: '70%',
                    distributed: true,
                    dataLabels: { position: 'right' }
                }
            },
            colors: barColors,
            dataLabels: {
                enabled: true,
                textAnchor: 'start',
                offsetX: 5,
                style: { fontSize: '11px', fontWeight: 600, colors: ['#333'] },
                formatter: function (val) { return val; }
            },
            xaxis: {
                categories: data.labels,
                labels: { style: { fontSize: '11px' } }
            },
            yaxis: { labels: { style: { fontSize: '12px', fontWeight: 500 }, maxWidth: 220 } },
            grid: { borderColor: '#f1f1f1', xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } } },
            legend: { show: false },
            tooltip: {
                y: { formatter: function (val) { return formatNumber(val); } }
            }
        };

        var chart = new ApexCharts(el, options);
        chart.render();
        chartInstances.reasons = chart;
    }

    /* ===================================================================
     *  CHART — Tenure Distribution Horizontal Bar
     * =================================================================== */
    function renderTenureBar(data) {
        if (chartInstances.tenure) chartInstances.tenure.destroy();

        var el = document.querySelector('#chartTenure');
        if (!el) return;

        var tenureColors = ['#A5D6A7', '#66BB6A', '#43A047', '#2E7D32', '#1B5E20'];

        var options = {
            series: [{ name: 'Employees', data: data.counts }],
            chart: {
                type: 'bar',
                height: Math.max(250, data.labels.length * 48),
                toolbar: { show: true, tools: { download: true, selection: false, zoom: false, zoomin: false, zoomout: false, pan: false, reset: false } },
                fontFamily: 'inherit'
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 4,
                    barHeight: '65%',
                    distributed: true,
                    dataLabels: { position: 'center' }
                }
            },
            colors: tenureColors,
            dataLabels: {
                enabled: true,
                style: { fontSize: '12px', fontWeight: 700, colors: ['#fff'] },
                formatter: function (val) { return val; },
                dropShadow: { enabled: false }
            },
            xaxis: {
                categories: data.labels,
                labels: { style: { fontSize: '11px' } }
            },
            yaxis: { labels: { style: { fontSize: '13px', fontWeight: 500 } } },
            grid: { borderColor: '#f1f1f1' },
            legend: { show: false },
            tooltip: {
                y: { formatter: function (val) { return formatNumber(val) + ' employees'; } }
            }
        };

        var chart = new ApexCharts(el, options);
        chart.render();
        chartInstances.tenure = chart;
    }

    /* ===================================================================
     *  CHART — Employee Category Donut
     * =================================================================== */
    function renderCategoryDonut(data) {
        if (chartInstances.category) chartInstances.category.destroy();

        var el = document.querySelector('#chartCategory');
        if (!el) return;

        var categoryColors = [COLORS.primary, COLORS.teal, COLORS.amber, COLORS.purple, COLORS.coral, COLORS.green, COLORS.cyan, COLORS.indigo];
        var usedColors = categoryColors.slice(0, Math.max(data.labels.length, 1));

        var totalVal = 0;
        data.counts.forEach(function (c) { totalVal += c; });

        var seriesData = data.counts.length ? data.counts : [1];
        var labelData  = data.labels.length ? data.labels : ['No Data'];
        if (!data.counts.length) usedColors = ['#E0E0E0'];

        var options = {
            series: seriesData,
            labels: labelData,
            chart: {
                type: 'donut',
                height: 350,
                toolbar: { show: false },
                fontFamily: 'inherit',
                events: {
                    dataPointSelection: function (event, chartContext, config) {
                        var label = config.w.config.labels[config.dataPointIndex];
                        if (label && label !== 'No Data') {
                            toggleCrossFilter('category', label);
                        }
                    }
                }
            },
            colors: usedColors,
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            name: { show: true, fontSize: '14px', fontWeight: 600, offsetY: -10 },
                            value: { show: true, fontSize: '24px', fontWeight: 700, offsetY: 6, formatter: function (val) { return formatNumber(parseInt(val)); } },
                            total: {
                                show: true,
                                label: 'Total',
                                fontSize: '13px',
                                fontWeight: 400,
                                color: '#666',
                                formatter: function () { return formatNumber(totalVal); }
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            legend: {
                position: 'bottom',
                fontSize: '13px',
                formatter: function (seriesName, opts) {
                    var count = opts.w.globals.series[opts.seriesIndex];
                    var pct = totalVal > 0 ? Math.round(count / totalVal * 100) : 0;
                    return seriesName + ': ' + formatNumber(count) + ' (' + pct + '%)';
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        var pct = totalVal > 0 ? Math.round(val / totalVal * 100) : 0;
                        return formatNumber(val) + ' (' + pct + '%)';
                    }
                }
            },
            stroke: { width: 2 },
            responsive: [{ breakpoint: 480, options: { chart: { height: 300 }, legend: { position: 'bottom' } } }]
        };

        var chart = new ApexCharts(el, options);
        chart.render();
        chartInstances.category = chart;
    }

    /* ===================================================================
     *  CHART — Year-over-Year Comparison
     * =================================================================== */
    function renderYearComparison(data) {
        if (chartInstances.yearComparison) chartInstances.yearComparison.destroy();

        var el = document.querySelector('#chartYearComparison');
        if (!el) return;

        if (!data.labels.length) {
            el.innerHTML = '<div class="text-center text-muted py-5">No multi-year data available</div>';
            return;
        }

        // Assign distinct color per year bar
        var yearBarColors = data.labels.map(function (_, i) {
            return COLORS.palette[i % COLORS.palette.length];
        });

        var options = {
            series: [{ name: 'Turnover', data: data.counts }],
            chart: {
                type: 'bar',
                height: 350,
                toolbar: { show: true, tools: { download: true, selection: false, zoom: false, zoomin: false, zoomout: false, pan: false, reset: false } },
                fontFamily: 'inherit'
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    columnWidth: '55%',
                    distributed: true,
                    dataLabels: { position: 'top' }
                }
            },
            colors: yearBarColors,
            dataLabels: {
                enabled: true,
                offsetY: -20,
                style: { fontSize: '13px', fontWeight: 700, colors: ['#333'] }
            },
            xaxis: {
                categories: data.labels,
                labels: { style: { fontSize: '13px', fontWeight: 600 } }
            },
            yaxis: {
                title: { text: 'Total Turnover' },
                labels: { style: { fontSize: '12px' } },
                min: 0
            },
            grid: { borderColor: '#e7e7e7', row: { colors: ['#f3f3f3', 'transparent'], opacity: 0.5 } },
            legend: { show: false },
            tooltip: {
                y: { formatter: function (val) { return formatNumber(val) + ' employees'; } }
            }
        };

        var chart = new ApexCharts(el, options);
        chart.render();
        chartInstances.yearComparison = chart;
    }

    /* ===================================================================
     *  TABLE — Department × Month Matrix with Heatmap
     * =================================================================== */
    function renderDeptMonthTable(data) {
        var container = document.getElementById('deptMonthMatrix');
        if (!container) return;

        if (!data.departments.length) {
            container.innerHTML = '<div class="text-center text-muted py-4">No data available</div>';
            return;
        }

        // Find global max for heatmap scaling
        var globalMax = 0;
        data.departments.forEach(function (dept) {
            for (var m = 1; m <= 12; m++) {
                var v = (data.matrix[dept] && data.matrix[dept][m]) || 0;
                if (v > globalMax) globalMax = v;
            }
        });

        var html = '<div class="table-responsive"><table class="table table-sm table-bordered mb-0" style="font-size:12px;">';

        // Header
        html += '<thead><tr style="background:#f8f9fa;">';
        html += '<th class="text-nowrap" style="min-width:160px; position:sticky; left:0; background:#f8f9fa; z-index:1;">Department</th>';
        MONTH_NAMES.forEach(function (m) {
            html += '<th class="text-center" style="min-width:48px;">' + m + '</th>';
        });
        html += '<th class="text-center font-weight-bold" style="min-width:55px; background:#e8eaf6;">YTD</th>';
        html += '</tr></thead>';

        // Body rows
        html += '<tbody>';
        data.departments.forEach(function (dept) {
            html += '<tr>';
            html += '<td class="text-nowrap font-weight-bold" style="position:sticky; left:0; background:#fff; z-index:1;">' + escapeHtml(dept) + '</td>';
            for (var m = 1; m <= 12; m++) {
                var val = (data.matrix[dept] && data.matrix[dept][m]) || 0;
                var cellStyle = '';
                if (val > 0) {
                    var hm = getHeatmapColor(val, globalMax);
                    cellStyle = 'background:' + hm.bg + '; color:' + hm.color + '; font-weight:600;';
                }
                html += '<td class="text-center" style="' + cellStyle + '">' + (val || '') + '</td>';
            }
            var ytd = data.totals[dept] || 0;
            html += '<td class="text-center font-weight-bold" style="background:#e8eaf6;">' + ytd + '</td>';
            html += '</tr>';
        });

        // Totals row
        html += '<tr style="background:#e3f2fd; font-weight:700;">';
        html += '<td style="position:sticky; left:0; background:#e3f2fd; z-index:1;">Total</td>';
        var grandTotal = 0;
        for (var m = 1; m <= 12; m++) {
            var mTotal = data.monthTotals[m] || 0;
            grandTotal += mTotal;
            html += '<td class="text-center">' + (mTotal || '') + '</td>';
        }
        html += '<td class="text-center" style="background:#c5cae9;">' + grandTotal + '</td>';
        html += '</tr>';

        html += '</tbody></table></div>';

        container.innerHTML = html;
    }

    /* ===================================================================
     *  TABLE — Reason × Department Matrix with Heatmap
     * =================================================================== */
    function renderReasonDeptTable(data) {
        var container = document.getElementById('reasonDeptMatrix');
        if (!container) return;

        if (!data.reasons.length) {
            container.innerHTML = '<div class="text-center text-muted py-4">No data available</div>';
            return;
        }

        // Limit to top 8 departments for readability
        var topDepts = data.departments.slice(0, 8);

        // Find global max
        var globalMax = 0;
        data.reasons.forEach(function (reason) {
            topDepts.forEach(function (dept) {
                var v = (data.matrix[reason] && data.matrix[reason][dept]) || 0;
                if (v > globalMax) globalMax = v;
            });
        });

        var html = '<div class="table-responsive"><table class="table table-sm table-bordered mb-0" style="font-size:12px;">';

        // Header
        html += '<thead><tr style="background:#f8f9fa;">';
        html += '<th class="text-nowrap" style="min-width:180px; position:sticky; left:0; background:#f8f9fa; z-index:1;">Reason</th>';
        topDepts.forEach(function (dept) {
            html += '<th class="text-center text-nowrap" style="min-width:70px;">' + escapeHtml(dept) + '</th>';
        });
        html += '<th class="text-center font-weight-bold" style="min-width:55px; background:#e8eaf6;">Total</th>';
        html += '</tr></thead>';

        // Body
        html += '<tbody>';
        data.reasons.forEach(function (reason) {
            html += '<tr>';
            html += '<td class="text-nowrap font-weight-bold" style="position:sticky; left:0; background:#fff; z-index:1;">' + escapeHtml(reason) + '</td>';
            topDepts.forEach(function (dept) {
                var val = (data.matrix[reason] && data.matrix[reason][dept]) || 0;
                var cellStyle = '';
                if (val > 0) {
                    var hm = getHeatmapColor(val, globalMax);
                    cellStyle = 'background:' + hm.bg + '; color:' + hm.color + '; font-weight:600;';
                }
                html += '<td class="text-center" style="' + cellStyle + '">' + (val || '') + '</td>';
            });
            var rowT = data.rowTotals[reason] || 0;
            html += '<td class="text-center font-weight-bold" style="background:#e8eaf6;">' + rowT + '</td>';
            html += '</tr>';
        });

        // Column totals row
        html += '<tr style="background:#e3f2fd; font-weight:700;">';
        html += '<td style="position:sticky; left:0; background:#e3f2fd; z-index:1;">Total</td>';
        var grand = 0;
        topDepts.forEach(function (dept) {
            var ct = data.colTotals[dept] || 0;
            grand += ct;
            html += '<td class="text-center">' + (ct || '') + '</td>';
        });
        html += '<td class="text-center" style="background:#c5cae9;">' + grand + '</td>';
        html += '</tr>';

        html += '</tbody></table></div>';

        container.innerHTML = html;
    }

    /* ===================================================================
     *  CROSS-FILTER SYSTEM
     * =================================================================== */
    function toggleCrossFilter(key, value) {
        if (crossFilters[key] === value) {
            delete crossFilters[key];
        } else {
            crossFilters[key] = value;
        }
        applyFiltersAndRender();
    }

    function removeCrossFilter(key) {
        delete crossFilters[key];
        applyFiltersAndRender();
    }

    function clearAllCrossFilters() {
        crossFilters = {};
        applyFiltersAndRender();
    }

    function updateFilterBadges() {
        var container = $('#activeFilterBadges');
        container.empty();

        var allFilters = Object.assign({}, activeFilters, crossFilters);
        var hasFilters = false;

        var filterLabels = {
            month: 'Month',
            department: 'Department',
            designation: 'Designation',
            gender: 'Gender',
            category: 'Category',
            resignationType: 'Type',
            reason: 'Reason'
        };

        Object.keys(allFilters).forEach(function (key) {
            if (allFilters[key]) {
                hasFilters = true;
                var label = filterLabels[key] || key;
                var displayValue = allFilters[key];

                // Convert month number to name
                if (key === 'month') {
                    var idx = parseInt(allFilters[key]) - 1;
                    displayValue = (idx >= 0 && idx < 12) ? MONTH_NAMES[idx] : allFilters[key];
                }

                var badge = $('<span class="badge badge-info mr-1 mb-1" style="font-size:13px; padding:6px 10px; cursor:pointer;">' +
                    escapeHtml(label) + ': ' + escapeHtml(String(displayValue)) + ' <i class="fas fa-times ml-1"></i></span>');

                badge.on('click', (function (k) {
                    return function () {
                        if (crossFilters[k] !== undefined) {
                            removeCrossFilter(k);
                        } else {
                            delete activeFilters[k];
                            resetDropdownFilter(k);
                            applyFiltersAndRender();
                        }
                    };
                })(key));

                container.append(badge);
            }
        });

        if (hasFilters) {
            var clearAll = $('<a href="javascript:void(0);" class="text-danger ml-2" style="font-size:13px;">' +
                '<i class="fas fa-times-circle"></i> Clear All</a>');
            clearAll.on('click', function () {
                crossFilters  = {};
                activeFilters = {};
                resetAllDropdowns();
                applyFiltersAndRender();
            });
            container.append(clearAll);
            container.show();
        } else {
            container.hide();
        }
    }

    /* ===================================================================
     *  FILTER BAR EVENTS
     * =================================================================== */
    $('#applyFilters').on('click', function () {
        activeFilters = {};

        var month  = $('#filterMonth').val();
        var dept   = $('#filterDepartment').val();
        var desig  = $('#filterDesignation').val();
        var gender = $('#filterGender').val();
        var cat    = $('#filterCategory').val();

        if (month)  activeFilters.month       = parseInt(month);
        if (dept)   activeFilters.department   = $('#filterDepartment option:selected').text();
        if (desig)  activeFilters.designation  = $('#filterDesignation option:selected').text();
        if (gender) activeFilters.gender       = gender;
        if (cat)    activeFilters.category     = $('#filterCategory option:selected').text();

        applyFiltersAndRender();
    });

    $('#resetFilters').on('click', function () {
        activeFilters = {};
        crossFilters  = {};
        resetAllDropdowns();
        applyFiltersAndRender();
    });

    $('#filterYear').on('change', function () {
        crossFilters  = {};
        activeFilters = {};
        resetAllDropdowns();
        fetchData($(this).val());
    });

    /* ===================================================================
     *  EXPORT — Excel
     * =================================================================== */
    $('#exportExcel').on('click', function () {
        if (!filteredData.length) {
            window.swal.fire({ title: 'No Data', text: 'No data available to export.', icon: 'info' });
            return;
        }

        var wb = XLSX.utils.book_new();

        // ── Sheet 1: Summary KPIs ──
        var kpis = computeKPIs();
        var selectedYear = $('#filterYear').val() || new Date().getFullYear();
        var summaryData = [
            ['Employee Turnover Dashboard - Summary'],
            ['Year', selectedYear],
            ['Generated', new Date().toLocaleDateString()],
            [''],
            ['Metric', 'Value'],
            ['Total Turnover', kpis.total],
            ['Current Month Turnover', kpis.monthly],
            ['YTD Turnover', kpis.yearly],
            ['Average Tenure (Years)', kpis.avgTenure],
            ['Male', kpis.maleCount + ' (' + kpis.malePercent + '%)'],
            ['Female', kpis.femaleCount + ' (' + kpis.femalePercent + '%)']
        ];
        var ws1 = XLSX.utils.aoa_to_sheet(summaryData);
        ws1['!cols'] = [{ wch: 30 }, { wch: 25 }];
        XLSX.utils.book_append_sheet(wb, ws1, 'Summary');

        // ── Sheet 2: Department × Month Matrix ──
        var matrix = computeDeptMonthMatrix();
        var matrixRows = [['Department'].concat(matrix.months).concat(['YTD'])];
        matrix.departments.forEach(function (dept) {
            var row = [dept];
            for (var m = 1; m <= 12; m++) {
                row.push((matrix.matrix[dept] && matrix.matrix[dept][m]) || 0);
            }
            row.push(matrix.totals[dept] || 0);
            matrixRows.push(row);
        });
        // Totals row
        var totalsRow = ['Total'];
        var gt = 0;
        for (var m = 1; m <= 12; m++) {
            var mt = matrix.monthTotals[m] || 0;
            gt += mt;
            totalsRow.push(mt);
        }
        totalsRow.push(gt);
        matrixRows.push(totalsRow);

        var ws2 = XLSX.utils.aoa_to_sheet(matrixRows);
        XLSX.utils.book_append_sheet(wb, ws2, 'Dept Monthly Matrix');

        // ── Sheet 3: Reason × Department Matrix ──
        var rdMatrix = computeReasonDeptMatrix();
        var rdRows = [['Reason'].concat(rdMatrix.departments).concat(['Total'])];
        rdMatrix.reasons.forEach(function (reason) {
            var row = [reason];
            rdMatrix.departments.forEach(function (dept) {
                row.push((rdMatrix.matrix[reason] && rdMatrix.matrix[reason][dept]) || 0);
            });
            row.push(rdMatrix.rowTotals[reason] || 0);
            rdRows.push(row);
        });
        var ws3 = XLSX.utils.aoa_to_sheet(rdRows);
        XLSX.utils.book_append_sheet(wb, ws3, 'Reason Dept Matrix');

        // ── Sheet 4: All Records (filtered) ──
        var recordsData = filteredData.map(function (r) {
            return {
                'Employee ID':       r.Employee_ID || '',
                'Name':              r.Name || '',
                'Gender':            r.Gender || '',
                'Department':        r.Department || '',
                'Designation':       r.Designation || '',
                'Location':          r.Location || '',
                'Employee Category': r.Employee_Category || '',
                'Date of Joining':   r.DOJ || '',
                'Date of Leaving':   r.DOL || '',
                'Resignation Type':  r.Resignation_Type || '',
                'Reason':            r.Reason || '',
                'Tenure (Months)':   r.Tenure_Months || ''
            };
        });
        var ws4 = XLSX.utils.json_to_sheet(recordsData);
        XLSX.utils.book_append_sheet(wb, ws4, 'Turnover Records');

        var yearLabel = selectedYear;
        XLSX.writeFile(wb, 'Turnover_Dashboard_' + yearLabel + '.xlsx');
    });

    // ── Print ──
    $('#printReport').on('click', function () {
        window.print();
    });

    /* ===================================================================
     *  UTILITY FUNCTIONS
     * =================================================================== */
    function animateValue(element, start, end, duration) {
        if (!element) return;
        end = parseInt(end) || 0;
        start = parseInt(start) || 0;
        if (start === end) { element.textContent = formatNumber(end); return; }

        var range = Math.abs(end - start);
        if (range === 0) { element.textContent = formatNumber(end); return; }

        var increment = end > start ? 1 : -1;
        var stepTime = Math.abs(Math.floor(duration / range));
        stepTime = Math.max(stepTime, 10);
        var current = start;

        var timer = setInterval(function () {
            current += increment * Math.ceil(range / (duration / stepTime));
            if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                current = end;
                clearInterval(timer);
            }
            element.textContent = formatNumber(current);
        }, stepTime);
    }

    function formatNumber(n) {
        if (n === null || n === undefined) return '0';
        return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function getHeatmapColor(value, max) {
        if (!value || !max) return { bg: 'transparent', color: '#333' };
        var intensity = Math.min(value / max, 1);
        // Gradient from soft green (#E8F5E9) → deep red/coral (#E53935)
        var r = Math.round(232 - intensity * (232 - 27));
        var g = Math.round(245 - intensity * (245 - 94));
        var b = Math.round(233 - intensity * (233 - 32));
        var textColor = intensity > 0.5 ? '#fff' : '#333';
        return { bg: 'rgb(' + r + ',' + g + ',' + b + ')', color: textColor };
    }

    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function resetDropdownFilter(key) {
        var map = {
            month:       '#filterMonth',
            department:  '#filterDepartment',
            designation: '#filterDesignation',
            gender:      '#filterGender',
            category:    '#filterCategory'
        };
        if (map[key]) {
            var $el = $(map[key]);
            $el.val('');
            // If Tom Select is initialized, sync it
            if ($el[0] && $el[0].tomselect) {
                $el[0].tomselect.clear(true);
            }
        }
    }

    function resetAllDropdowns() {
        ['#filterMonth', '#filterDepartment', '#filterDesignation', '#filterGender', '#filterCategory'].forEach(function (sel) {
            var $el = $(sel);
            $el.val('');
            if ($el[0] && $el[0].tomselect) {
                $el[0].tomselect.clear(true);
            }
        });
    }

    /* ===================================================================
     *  INITIALIZATION
     * =================================================================== */
    fetchData($('#filterYear').val() || new Date().getFullYear());

}); // end docReady
