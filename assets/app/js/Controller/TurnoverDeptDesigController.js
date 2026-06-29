function docReady(fn) {
    if (document.readyState === "complete" || document.readyState === "interactive") {
        setTimeout(fn, 1);
    } else {
        document.addEventListener("DOMContentLoaded", fn);
    }
}

docReady(function () {
    var form = $('#filterForm');
    
    $('#resetBtn').on('click', function() {
        form[0].reset();
        $('#reportContainer').slideUp();
        $('#printBtnContainer').hide();
    });

    form.on('submit', function (e) {
        e.preventDefault();
        
        var fromDate = $('#fromDate').val();
        var toDate = $('#toDate').val();
        
        var today = new Date().toISOString().split('T')[0];
        if (fromDate > today || toDate > today) {
            window.swal.fire({ title: "Validation Error", text: "Dates cannot be in the future.", icon: "warning" });
            return;
        }
        if (toDate < fromDate) {
            window.swal.fire({ title: "Validation Error", text: "To Date cannot be earlier than From Date.", icon: "warning" });
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
        formData.append('action_type', 'GET_REPORT');
        formData.append('fromDate', fromDate);
        formData.append('toDate', toDate);

        axios.post(window.baseUrl + '/_inc/report_dept_desig.php', formData)
            .then(function(response) {
                window.swal.close();
                if (response.data.valid) {
                    renderReport(response.data.data, fromDate, toDate);
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

    function renderReport(data, fromDate, toDate) {
        if (!data || data.length === 0) {
            window.swal.fire({ title: "No Records", text: "No turnovers found for the selected period.", icon: "info" });
            $('#reportContainer').hide();
            $('#printBtnContainer').hide();
            return;
        }

        var depts = {};
        var totalTurnovers = 0;
        var globalMaxCount = 0;
        
        data.forEach(function(item) {
            var c = parseInt(item.Count, 10);
            if (c > globalMaxCount) globalMaxCount = c;
            
            if (!depts[item.Department]) {
                depts[item.Department] = { designations: [], total: 0 };
            }
            depts[item.Department].designations.push({ name: item.Designation, count: c });
            depts[item.Department].total += c;
            totalTurnovers += c;
        });

        // Sort designations within each department
        Object.keys(depts).forEach(function(deptName) {
            depts[deptName].designations.sort((a, b) => b.count - a.count);
        });

        // Sort departments by total descending
        var deptNames = Object.keys(depts).sort(function(a, b) {
            return depts[b].total - depts[a].total;
        });

        var topDept = deptNames.length > 0 ? deptNames[0] : '';
        var topDeptCount = deptNames.length > 0 ? depts[topDept].total : 0;

        var summaryHtml = `During the selected period from <b class="text-primary">${formatDateStr(fromDate)}</b> to <b class="text-primary">${formatDateStr(toDate)}</b>, a total of <b>${totalTurnovers}</b> employees left the organization across <b>${deptNames.length}</b> departments. The highest turnover was observed in the <b>${topDept}</b> department with <b>${topDeptCount}</b> departures.`;
        $('#summaryText').html(summaryHtml);

        var gridHtml = '';
        var colors = ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0', '#3F51B5', '#546E7A', '#D4526E', '#8D5B4C', '#F86624', '#2E93fA', '#66DA26', '#E91E63', '#FF9800'];

        for (var i = 0; i < deptNames.length; i += 2) {
            gridHtml += '<div class="row mb-3" style="page-break-inside: avoid;">';

            for (var j = 0; j < 2; j++) {
                if (i + j >= deptNames.length) break;
                
                var deptName = deptNames[i + j];
                var deptData = depts[deptName];
                var tbodyHtml = '';

                deptData.designations.forEach(function(dsg, idx) {
                    var widthPct = globalMaxCount > 0 ? (dsg.count / globalMaxCount) * 90 : 0;
                    var color = colors[idx % colors.length];

                    tbodyHtml += `
                    <tr>
                        <td class="align-middle p-2" style="width: 45%; font-size: 12px; font-weight: 500;">${dsg.name}</td>
                        <td class="align-middle p-2 font-weight-bold" style="width: 15%; text-align: center; color: #444;">${dsg.count}</td>
                        <td class="align-middle p-2" style="width: 40%;">
                            <div style="width: 100%; position: relative; height: 16px; display: flex; align-items: center; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                                <!-- Stick -->
                                <div style="width: ${widthPct}%; height: 3px; background-color: ${color}; border-radius: 2px; -webkit-print-color-adjust: exact; print-color-adjust: exact;"></div>
                                <!-- Candy (Circle) -->
                                <div style="width: 14px; height: 14px; border-radius: 50%; background-color: ${color}; position: absolute; left: ${widthPct}%; transform: translateX(-50%); box-shadow: 0 1px 3px rgba(0,0,0,0.3); z-index: 2; -webkit-print-color-adjust: exact; print-color-adjust: exact;"></div>
                            </div>
                        </td>
                    </tr>`;
                });

                // Total Row
                tbodyHtml += `
                    <tr class="bg-light" style="-webkit-print-color-adjust: exact; print-color-adjust: exact;">
                        <td class="p-2 font-weight-bold" style="font-size: 13px; text-align: right;">Total</td>
                        <td class="p-2 font-weight-bold text-danger" style="text-align: center; font-size: 14px;">${deptData.total}</td>
                        <td></td>
                    </tr>`;

                var cardHtml = `
                <div class="col-md-6">
                    <div class="card card-outline card-primary shadow-sm h-100 mb-0">
                        <div class="card-header p-2">
                            <h3 class="card-title font-weight-bold text-uppercase" style="font-size: 14px; color: #0056b3;">${deptName}</h3>
                        </div>
                        <div class="card-body p-0 d-flex flex-column">
                            <div class="table-responsive flex-grow-1">
                                <table class="table table-bordered table-sm mb-0 h-100">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="p-2">Designation</th>
                                            <th class="p-2 text-center">Count</th>
                                            <th class="p-2">Proportion</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${tbodyHtml}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>`;

                gridHtml += cardHtml;
            }
            gridHtml += '</div>'; // close row
        }

        $('#departmentsGrid').html(gridHtml);
        $('#reportContainer').slideDown();
        $('#printBtnContainer').show();
    }

    function formatDateStr(dateString) {
        var options = { year: 'numeric', month: 'short', day: 'numeric' };
        var d = new Date(dateString);
        return d.toLocaleDateString('en-US', options);
    }
});
