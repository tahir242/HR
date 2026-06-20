/**
 * Ration Issue Log Controller
 * Handles the display and filtering of ration issue logs
 */

$(document).ready(function () {
    let table;

    // Initialize DataTable
    function initDataTable() {
        if ($.fn.DataTable.isDataTable('#issue_log_table')) {
            $('#issue_log_table').DataTable().destroy();
        }

        table = $('#issue_log_table').DataTable({
            data: [],
            columns: [
                { data: 'Employee_ID' },
                { data: 'Name' },
                { data: 'CNIC' },
                { data: 'Department' },
                { data: 'Designation' },
                { data: 'Ration_Year' },
                {
                    data: 'Status',
                    render: function (data, type, row) {
                        if (data === 'Issued') {
                            return '<span class="badge badge-status bg-success">Received</span>';
                        } else if (data === 'Eligible') {
                            return '<span class="badge badge-status bg-warning text-dark">Not Received</span>';
                        } else {
                            return '<span class="badge badge-status bg-secondary">' + data + '</span>';
                        }
                    }
                },
                { data: 'Issue_Type' },
                { data: 'Issue_DateTime' },
                { data: 'Issue_By' }
            ],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            responsive: true,
            order: [[5, 'desc'], [1, 'asc']], // Sort by Year descending, then Name ascending
            language: {
                emptyTable: "No records found. Please apply filters to load data.",
                info: "Showing _START_ to _END_ of _TOTAL_ records",
                infoEmpty: "Showing 0 to 0 of 0 records",
                infoFiltered: "(filtered from _MAX_ total records)",
                lengthMenu: "Show _MENU_ records",
                search: "Search:",
                zeroRecords: "No matching records found"
            }
        });
    }

    // Load data based on filters
    function loadData() {
        const filters = {
            action_type: "GET_ISSUE_LOG",
            year: $('#filter_year').val(),
            employee_id: $('#filter_employee').val(),
            department: $('#filter_department').val(),
            designation: $('#filter_designation').val(),
            status: $('#filter_status').val(),
            issue_type: $('#filter_issue_type').val()
        };

        // Show loading indicator
        Swal.fire({
            title: 'Loading...',
            text: 'Please wait while we fetch the data',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '../_inc/ration_issue_log.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(filters),
            dataType: 'json',
            success: function (response) {
                Swal.close();
                if (response.valid) {
                    // Update summary cards
                    $('#total_records').text(response.summary.total);
                    $('#total_issued').text(response.summary.issued);
                    $('#total_eligible').text(response.summary.eligible);

                    // Update DataTable
                    table.clear();
                    table.rows.add(response.data);
                    table.draw();

                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Data loaded successfully: ' + response.summary.total + ' records found',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.errorMsg || 'Failed to load data'
                    });
                }
            },
            error: function (xhr, status, error) {
                Swal.close();
                let errorMsg = 'An error occurred while loading data';
                if (xhr.responseJSON && xhr.responseJSON.errorMsg) {
                    errorMsg = xhr.responseJSON.errorMsg;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg
                });
            }
        });
    }

    // Apply filters button click
    $('#apply_filters').on('click', function () {
        loadData();
    });

    // Reset filters button click
    $('#reset_filters').on('click', function () {
        $('#filter_year').val($('#filter_year option:selected').val() || '');
        $('#filter_employee').val('');
        $('#filter_department').val('');
        $('#filter_designation').val('');
        $('#filter_status').val('');
        $('#filter_issue_type').val('');

        // Reset summary cards
        $('#total_records').text('0');
        $('#total_issued').text('0');
        $('#total_eligible').text('0');

        // Clear table
        if (table) {
            table.clear().draw();
        }
    });

    // Export to Excel functionality
    window.exportToExcel = function () {
        const filters = {
            action_type: "EXPORT_EXCEL",
            year: $('#filter_year').val(),
            employee_id: $('#filter_employee').val(),
            department: $('#filter_department').val(),
            designation: $('#filter_designation').val(),
            status: $('#filter_status').val(),
            issue_type: $('#filter_issue_type').val()
        };

        Swal.fire({
            title: 'Exporting...',
            text: 'Please wait while we prepare your export',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '../_inc/ration_issue_log.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(filters),
            dataType: 'json',
            success: function (response) {
                Swal.close();
                if (response.valid && response.data.length > 0) {
                    // Convert data to CSV
                    const data = response.data;
                    const headers = Object.keys(data[0]);
                    
                    let csv = headers.join(',') + '\n';
                    data.forEach(row => {
                        const values = headers.map(header => {
                            const value = row[header] !== null ? String(row[header]) : '';
                            // Escape quotes and wrap in quotes if contains comma
                            return value.includes(',') || value.includes('"') || value.includes('\n')
                                ? '"' + value.replace(/"/g, '""') + '"'
                                : value;
                        });
                        csv += values.join(',') + '\n';
                    });

                    // Create download link
                    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                    const link = document.createElement('a');
                    const url = URL.createObjectURL(blob);
                    const filename = 'Ration_Issue_Log_' + new Date().toISOString().slice(0, 10) + '.csv';
                    
                    link.setAttribute('href', url);
                    link.setAttribute('download', filename);
                    link.style.visibility = 'hidden';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Data exported successfully',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else if (response.data.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Data',
                        text: 'No data available to export'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.errorMsg || 'Failed to export data'
                    });
                }
            },
            error: function (xhr, status, error) {
                Swal.close();
                let errorMsg = 'An error occurred while exporting data';
                if (xhr.responseJSON && xhr.responseJSON.errorMsg) {
                    errorMsg = xhr.responseJSON.errorMsg;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg
                });
            }
        });
    };

    // Allow Enter key to trigger search in Employee ID field
    $('#filter_employee').on('keypress', function (e) {
        if (e.which === 13) { // Enter key
            loadData();
        }
    });

    // Initialize DataTable on page load
    initDataTable();

    // Auto-load data with default filters (current year)
    if ($('#filter_year').val()) {
        loadData();
    }
});
