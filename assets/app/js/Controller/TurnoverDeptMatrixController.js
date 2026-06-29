function docReady(fn) {
    if (document.readyState === "complete" || document.readyState === "interactive") {
        setTimeout(fn, 1);
    } else {
        document.addEventListener("DOMContentLoaded", fn);
    }
}

docReady(function () {
    var form = $('#filterForm');
    var chartInstance = null;
    
    $('#resetBtn').on('click', function() {
        form[0].reset();
        $('#reportContainer').slideUp();
        $('#printBtnContainer').hide();
        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }
    });

    form.on('submit', function (e) {
        e.preventDefault();
        
        var fromMonth = $('#fromMonth').val(); // format: YYYY-MM
        var toMonth = $('#toMonth').val();     // format: YYYY-MM
        
        var d = new Date();
        var currentMonth = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
        
        if (fromMonth > currentMonth || toMonth > currentMonth) {
            window.swal.fire({ title: "Validation Error", text: "Dates cannot be in the future.", icon: "warning" });
            return;
        }
        if (toMonth < fromMonth) {
            window.swal.fire({ title: "Validation Error", text: "To Month cannot be earlier than From Month.", icon: "warning" });
            return;
        }

        window.swal.fire({
            title: 'Fetching Report...',
            text: 'Please wait while we gather the data.',
            allowOutsideClick: false,
            didOpen: () => {
                window.swal.showLoading();
            }
        });

        var formData = new FormData();
        formData.append('action_type', 'GET_MATRIX');
        formData.append('fromMonth', fromMonth);
        formData.append('toMonth', toMonth);

        axios.post(window.baseUrl + '/_inc/report_dept_matrix.php', formData)
            .then(function(response) {
                window.swal.close();
                if (response.data.valid) {
                    renderReport(response.data.data, fromMonth, toMonth);
                }
            })
            .catch(function(error) {
                var msg = "An error occurred while fetching the report.";
                if (error.response && error.response.data && error.response.data.errorMsg) {
                    msg = error.response.data.errorMsg;
                }
                window.swal.fire({ title: "Error!", text: msg, icon: "error" });
            });
    });

    function getMonthsInRange(start, end) {
        var dateStart = new Date(start + '-01');
        var dateEnd = new Date(end + '-01');
        var months = [];
        
        var current = new Date(dateStart);
        while (current <= dateEnd) {
            var y = current.getFullYear();
            var m = String(current.getMonth() + 1).padStart(2, '0');
            months.push(y + '-' + m);
            current.setMonth(current.getMonth() + 1);
        }
        return months;
    }

    function formatMonthYear(yyyymm) {
        var parts = yyyymm.split('-');
        var d = new Date(parts[0], parseInt(parts[1])-1, 1);
        return d.toLocaleDateString('en-US', { month: 'short', year: '2-digit' });
    }

    function renderReport(data, fromMonth, toMonth) {
        if (!data || data.length === 0) {
            window.swal.fire({ title: "No Records", text: "No turnovers found for the selected period.", icon: "info" });
            $('#reportContainer').hide();
            $('#printBtnContainer').hide();
            return;
        }

        var monthsList = getMonthsInRange(fromMonth, toMonth);
        var depts = {};
        var monthlyTotals = {};
        monthsList.forEach(m => monthlyTotals[m] = 0);
        
        var grandTotal = 0;

        // Process data
        data.forEach(function(row) {
            var dept = row.Department;
            var mKey = row.Year + '-' + String(row.Month).padStart(2, '0');
            var c = parseInt(row.Count, 10);

            if (!depts[dept]) {
                depts[dept] = { total: 0, months: {} };
            }
            if (!depts[dept].months[mKey]) {
                depts[dept].months[mKey] = 0;
            }
            depts[dept].months[mKey] += c;
            depts[dept].total += c;
            
            if (monthlyTotals[mKey] !== undefined) {
                monthlyTotals[mKey] += c;
            }
            grandTotal += c;
        });

        // Sort departments by YTD (total) descending
        var sortedDepts = Object.keys(depts).sort(function(a, b) {
            return depts[b].total - depts[a].total;
        });

        var startLabel = formatMonthYear(fromMonth);
        var endLabel = formatMonthYear(toMonth);
        
        // Render Summary
        var summaryHtml = `During the selected period from <b class="text-primary">${startLabel}</b> to <b class="text-primary">${endLabel}</b>, a total of <b>${grandTotal}</b> employees left the organization across <b>${sortedDepts.length}</b> departments. The highest turnover was observed in the <b>${sortedDepts.length > 0 ? sortedDepts[0] : ''}</b> department with <b>${sortedDepts.length > 0 ? depts[sortedDepts[0]].total : 0}</b> departures.`;
        $('#summaryText').html(summaryHtml);

        // Render Table Header
        var theadHtml = '<th style="width: 25%;">DEPARTMENT</th>';
        var chartCategories = [];
        monthsList.forEach(function(m) {
            var label = formatMonthYear(m);
            theadHtml += `<th class="text-center">${label}</th>`;
            chartCategories.push(label);
        });
        theadHtml += '<th class="text-center bg-light font-weight-bold">YTD</th>';
        $('#matrixTheadRow').html(theadHtml);

        // Render Table Body
        var tbodyHtml = '';
        sortedDepts.forEach(function(deptName) {
            tbodyHtml += `<tr><td class="font-weight-bold text-uppercase" style="font-size: 12px; color: #333;">${deptName}</td>`;
            var dData = depts[deptName];
            
            monthsList.forEach(function(m) {
                var val = dData.months[m] ? dData.months[m] : '';
                tbodyHtml += `<td class="text-center">${val}</td>`;
            });
            tbodyHtml += `<td class="text-center bg-light font-weight-bold text-danger">${dData.total}</td></tr>`;
        });

        // Add Monthly Totals Row
        tbodyHtml += `<tr class="bg-light font-weight-bold" style="font-size: 13px;">`;
        tbodyHtml += `<td class="text-right text-uppercase" style="color: #333;">Monthly Total</td>`;
        monthsList.forEach(function(m) {
            var mTotal = monthlyTotals[m] ? monthlyTotals[m] : '';
            tbodyHtml += `<td class="text-center text-primary">${mTotal}</td>`;
        });
        tbodyHtml += `<td class="text-center text-danger">${grandTotal}</td></tr>`;

        $('#matrixTbody').html(tbodyHtml);
        $('#matrixTitle').html(`Department With Higher Turnover (${startLabel} - ${endLabel})`);

        // Render Chart
        var monthlyDataSeries = [];
        var cumulativeDataSeries = [];
        var runningTotal = 0;

        monthsList.forEach(function(m) {
            var mt = monthlyTotals[m];
            monthlyDataSeries.push(mt);
            runningTotal += mt;
            cumulativeDataSeries.push(runningTotal);
        });

        if (chartInstance) {
            chartInstance.destroy();
        }

        var options = {
            series: [{
                name: 'Monthly Turnovers',
                type: 'column',
                data: monthlyDataSeries
            }, {
                name: 'Cumulative (YTD)',
                type: 'line',
                data: cumulativeDataSeries
            }],
            chart: {
                height: 350,
                type: 'line',
                toolbar: { show: false },
                fontFamily: 'inherit'
            },
            stroke: {
                width: [0, 3],
                curve: 'smooth'
            },
            colors: ['#008FFB', '#FF4560'],
            dataLabels: {
                enabled: true,
                enabledOnSeries: [0, 1],
                style: {
                    fontSize: '12px'
                },
                background: {
                    enabled: true,
                    foreColor: '#000',
                    borderRadius: 2,
                    padding: 4,
                    borderWidth: 0,
                }
            },
            xaxis: {
                categories: chartCategories,
                labels: {
                    style: { fontSize: '12px', fontWeight: 500 }
                }
            },
            yaxis: [{
                title: { text: 'Monthly Count' },
            }, {
                opposite: true,
                title: { text: 'Cumulative Total (YTD)' }
            }],
            grid: {
                borderColor: '#e7e7e7',
                row: { colors: ['#f3f3f3', 'transparent'], opacity: 0.5 }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'center'
            },
            tooltip: {
                custom: function({series, seriesIndex, dataPointIndex, w}) {
                    var monthKey = monthsList[dataPointIndex];
                    var monthTotal = monthlyTotals[monthKey];
                    var cumeTotal = cumulativeDataSeries[dataPointIndex];
                    
                    var html = '<div class="arrow_box" style="padding: 12px; border: 1px solid #e3e3e3; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-radius: 4px;">';
                    html += '<div style="font-weight: 600; font-size: 13px; border-bottom: 1px solid #eee; margin-bottom: 8px; padding-bottom: 6px;">' + formatMonthYear(monthKey) + '</div>';
                    html += '<div style="color: #008FFB; margin-bottom: 4px; font-size: 13px;"><strong>Monthly Total:</strong> ' + monthTotal + '</div>';
                    html += '<div style="color: #FF4560; margin-bottom: 12px; font-size: 13px;"><strong>Cumulative YTD:</strong> ' + cumeTotal + '</div>';
                    
                    // Add department breakdown
                    html += '<div style="font-size: 11px; color: #555; max-height: 180px; overflow-y: auto;">';
                    html += '<strong style="display:block; margin-bottom: 4px; color: #333;">Department Breakdown:</strong>';
                    
                    var breakdown = [];
                    sortedDepts.forEach(function(dName) {
                        var count = depts[dName].months[monthKey] ? depts[dName].months[monthKey] : 0;
                        if (count > 0) {
                            breakdown.push({ name: dName, val: count });
                        }
                    });
                    
                    breakdown.sort((a, b) => b.val - a.val);
                    
                    if (breakdown.length === 0) {
                        html += '<i>No turnover</i>';
                    } else {
                        breakdown.forEach(function(item) {
                            html += `<div style="display: flex; justify-content: space-between; margin-top: 3px; border-bottom: 1px dashed #f0f0f0; padding-bottom: 2px;">
                                        <span style="margin-right: 20px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">${item.name}</span>
                                        <strong style="color: #000;">${item.val}</strong>
                                     </div>`;
                        });
                    }
                    html += '</div></div>';
                    
                    return html;
                }
            }
        };

        chartInstance = new ApexCharts(document.querySelector("#trendChart"), options);
        chartInstance.render();

        $('#reportContainer').slideDown();
        $('#printBtnContainer').show();
    }
});
