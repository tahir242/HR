function docReady(fn) {
    if (document.readyState === "complete" || document.readyState === "interactive") {
        setTimeout(fn, 1);
    } else {
        document.addEventListener("DOMContentLoaded", fn);
    }
}

docReady(function () {

    let q = window.getParameterByName("q");
    let dt = $("#list");
    let hideColums = dt.data("hide-colums").split(",");
    let hideColumsArray = [];
    if (hideColums.length) {
        for (var i = 0; i < hideColums.length; i += 1) {
            hideColumsArray.push(parseInt(hideColums[i]));
        }
    }

    dt.dataTable({
        "processing": true,
        "serverSide": true,
        "dom": "rtip",
        "ajax": window.baseUrl + "/_inc/employee_turnover.php?q=" + q,
        "order": [[0, "asc"]],
        "aLengthMenu": [
            [10, 25, 50, 100, 200, -1],
            [10, 25, 50, 100, 200, "All"]
        ],
        "columnDefs": [
            { "targets": [7, 8, 9], "orderable": false },
            { "visible": false, "targets": hideColumsArray },
            { "className": "text-center", "targets": [8, 9] },
        ],
        "aoColumns": [
            { data: "Employee_ID" },
            { data: "Name" },
            { data: "Department" },
            { data: "Designation" },
            { data: "Location" },
            { data: "Date_of_Joining" },
            { data: "Date_of_Leaving" },
            { data: "Resignation_Type" },
            { data: "Status" },
            { data: "btn_actions" }
        ],
        "pageLength": 15,
    });

    // Search filter
    if (window.getParameterByName('q')) {
        $("#searchInput").val(window.getParameterByName('q'));
    }

    $("#apply-filter").on("click", function (e) {
        e.preventDefault();
        var search = $('#searchInput').val();
        var url = "turnover_list.php";
        if (search) {
            url += "?q=" + search;
        }
        window.location = url;
    });

    // Press Enter to search
    $("#searchInput").on("keydown", function(e) {
        if (e.key === 'Enter') {
            $("#apply-filter").trigger('click');
        }
    });

    // Helper: fetch record data
    function fetchRecord(empId, callback) {
        var formData = new FormData();
        formData.append('action_type', 'GET');
        formData.append('Employee_ID', empId);
        axios.post(window.baseUrl + '/_inc/employee_turnover.php', formData)
            .then(function(response) {
                if (response.data.valid) {
                    callback(response.data.record);
                }
            }).catch(function(error) {
                if (error.response && error.response.data && error.response.data.errorMsg) {
                    window.swal.fire({ title: "Error!", text: error.response.data.errorMsg, icon: "error" });
                }
            });
    }

    // Dictionary name lookups from the select options in the edit modal
    function getOptionText(selectId, value) {
        var el = document.getElementById(selectId);
        if (!el || !value) return '';
        for (var i = 0; i < el.options.length; i++) {
            if (el.options[i].value == value) return el.options[i].text;
        }
        return '';
    }

    // ========== VIEW RECORD ==========
    $(document).on("click", ".view-record", function (e) {
        e.preventDefault();
        var d = dt.DataTable().row($(this).closest("tr")).data();
        fetchRecord(d.Employee_ID, function(rec) {
            var html = '<div class="row p-2">';
            
            // Personal Info Section
            html += '<div class="col-md-12 mb-3">';
            html += '  <h6 class="text-muted border-bottom pb-2" style="font-weight:600;"><i class="fas fa-user-circle text-info mr-1"></i> Personal Information</h6>';
            html += '</div>';
            
            html += '<div class="col-md-3 col-sm-6 mb-3"><span class="d-block text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Employee ID</span><span class="d-block font-weight-bold text-dark" style="font-size: 14px;">' + (rec.Employee_ID || '-') + '</span></div>';
            html += '<div class="col-md-3 col-sm-6 mb-3"><span class="d-block text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Name</span><span class="d-block font-weight-bold text-dark" style="font-size: 14px;">' + (rec.Name || '-') + '</span></div>';
            html += '<div class="col-md-3 col-sm-6 mb-3"><span class="d-block text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Gender</span><span class="d-block font-weight-bold text-dark" style="font-size: 14px;">' + (rec.Gender || '-') + '</span></div>';
            html += '<div class="col-md-3 col-sm-6 mb-3"><span class="d-block text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Date of Birth</span><span class="d-block font-weight-bold text-dark" style="font-size: 14px;">' + (rec.Date_of_Birth || '-') + '</span></div>';

            // Employment Details Section
            html += '<div class="col-md-12 mb-3 mt-2">';
            html += '  <h6 class="text-muted border-bottom pb-2" style="font-weight:600;"><i class="fas fa-briefcase text-primary mr-1"></i> Employment Details</h6>';
            html += '</div>';

            html += '<div class="col-md-3 col-sm-6 mb-3"><span class="d-block text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Department</span><span class="d-block font-weight-bold text-dark" style="font-size: 14px;">' + (getOptionText('edit_Department', rec.Department) || '-') + '</span></div>';
            html += '<div class="col-md-3 col-sm-6 mb-3"><span class="d-block text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Designation</span><span class="d-block font-weight-bold text-dark" style="font-size: 14px;">' + (getOptionText('edit_Designation', rec.Designation) || '-') + '</span></div>';
            html += '<div class="col-md-3 col-sm-6 mb-3"><span class="d-block text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Location</span><span class="d-block font-weight-bold text-dark" style="font-size: 14px;">' + (getOptionText('edit_Location', rec.Location) || '-') + '</span></div>';
            html += '<div class="col-md-3 col-sm-6 mb-3"><span class="d-block text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Category</span><span class="d-block font-weight-bold text-dark" style="font-size: 14px;">' + (getOptionText('edit_Employee_Category', rec.Employee_Category) || '-') + '</span></div>';
            
            html += '<div class="col-md-3 col-sm-6 mb-3"><span class="d-block text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Date of Joining</span><span class="d-block font-weight-bold text-dark" style="font-size: 14px;">' + (rec.Date_of_Joining || '-') + '</span></div>';
            html += '<div class="col-md-3 col-sm-6 mb-3"><span class="d-block text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Date of Leaving</span><span class="d-block font-weight-bold text-dark" style="font-size: 14px;">' + (rec.Date_of_Leaving || '-') + '</span></div>';

            // Turnover Details Section
            html += '<div class="col-md-12 mb-3 mt-2">';
            html += '  <h6 class="text-muted border-bottom pb-2" style="font-weight:600;"><i class="fas fa-sign-out-alt text-danger mr-1"></i> Turnover Details</h6>';
            html += '</div>';

            html += '<div class="col-md-4 col-sm-6 mb-3"><span class="d-block text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Resignation Type</span><span class="d-block font-weight-bold text-dark" style="font-size: 14px;">' + (getOptionText('edit_Resignation_Type', rec.Resignation_Type) || '-') + '</span></div>';
            html += '<div class="col-md-8 col-sm-6 mb-3"><span class="d-block text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Reason of Turnover</span><span class="d-block font-weight-bold text-dark" style="font-size: 14px;" id="view_reason_text">Loading...</span></div>';
            
            html += '<div class="col-md-12 mb-3"><span class="d-block text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Remarks</span><div class="p-3 bg-light rounded border mt-1 text-dark" style="font-size: 14px; min-height: 50px;">' + (rec.Remarks || '-') + '</div></div>';

            // Status & Scan
            var statusBadge = '';
            if (rec.Status == 'Indexed') statusBadge = '<span class="badge badge-success px-2 py-1">' + rec.Status + '</span>';
            else if (rec.Status == 'Pending') statusBadge = '<span class="badge badge-warning px-2 py-1">' + rec.Status + '</span>';
            else statusBadge = '<span class="badge badge-info px-2 py-1">' + rec.Status + '</span>';

            var scanDisplay = rec.Scan ? '<span class="text-dark"><i class="fas fa-file-pdf text-danger"></i> ' + rec.Scan + '</span>' : '<span class="text-muted"><i class="fas fa-times-circle"></i> No PDF Uploaded</span>';

            html += '<div class="col-md-4 col-sm-6 mb-3"><span class="d-block text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Status</span><span class="d-block mt-1">' + statusBadge + '</span></div>';
            html += '<div class="col-md-8 col-sm-6 mb-3"><span class="d-block text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Scanned Document</span><span class="d-block mt-1 font-weight-bold" style="font-size: 14px;">' + scanDisplay + '</span></div>';

            html += '</div>';
            $('#viewModalBody').html(html);

            // Fetch reason name
            if (rec.Resignation_Type && rec.Reason_of_Turnover) {
                axios.get(window.baseUrl + '/_inc/value.php?action_type=GET_REASON_BY_TYPE&Resignation_Type_ID=' + rec.Resignation_Type)
                    .then(function(resp) {
                        if (resp.data.valid && resp.data.results) {
                            var reasonName = '-';
                            resp.data.results.forEach(function(item) {
                                if (item.Reason_ID == rec.Reason_of_Turnover) reasonName = item.Reason;
                            });
                            $('#view_reason_text').text(reasonName);
                        }
                    });
            } else {
                $('#view_reason_text').text('-');
            }

            // Show/hide Open PDF button
            if (rec.Scan) {
                $('#viewOpenPdf').show().data('scan', rec.Scan);
            } else {
                $('#viewOpenPdf').hide();
            }
            $('#viewModal').modal('show');
        });
    });

    // Open PDF from view modal
    $(document).on("click", "#viewOpenPdf", function(e) {
        e.preventDefault();
        var scan = $(this).data('scan');
        var formData = new FormData();
        formData.append("action", "VIEWFILE");
        formData.append("file", scan);
        axios.post(window.baseUrl + "/_inc/upload.php", formData, { responseType: 'blob' }).then(function(response){
            var blob = response.data;
            if (blob.type === 'application/pdf') {
                var pdfUrl = URL.createObjectURL(blob);
                var viewerUrl = '../_inc/vendor/pdfjs/web/viewer.html?file=' + encodeURIComponent(pdfUrl);
                window.open(viewerUrl, '_blank', 'location=yes, height=570, width=520, scrollbars=yes, status=yes');
            } else {
                alert('Unsupported file format');
            }
        }).catch(function(error) {
            console.error('Error:', error);
        });
    });

    // Open PDF from list action column
    $(document).on("click", ".open-pdf", function(e) {
        e.preventDefault();
        var d = dt.DataTable().row($(this).closest("tr")).data();
        var formData = new FormData();
        formData.append("action", "VIEWFILE");
        formData.append("file", d.Scan);
        axios.post(window.baseUrl + "/_inc/upload.php", formData, { responseType: 'blob' }).then(function(response){
            var blob = response.data;
            if (blob.type === 'application/pdf') {
                var pdfUrl = URL.createObjectURL(blob);
                var viewerUrl = '../_inc/vendor/pdfjs/web/viewer.html?file=' + encodeURIComponent(pdfUrl);
                window.open(viewerUrl, '_blank', 'location=yes, height=570, width=520, scrollbars=yes, status=yes');
            } else {
                alert('Unsupported file format');
            }
        }).catch(function(error) {
            console.error('Error:', error);
        });
    });

    // ========== EDIT RECORD ==========
    $(document).on("click", ".edit-record", function (e) {
        e.preventDefault();
        var d = dt.DataTable().row($(this).closest("tr")).data();
        fetchRecord(d.Employee_ID, function(rec) {
            $('#edit_Scan').val(rec.Scan);
            $('#edit_Employee_ID').val(rec.Employee_ID);
            $('#edit_Employee_Name').val(rec.Name);
            $('#edit_Gender').val(rec.Gender);
            $('#edit_Date_of_Birth').val(rec.Date_of_Birth);
            $('#edit_Department').val(rec.Department);
            $('#edit_Designation').val(rec.Designation);
            $('#edit_Location').val(rec.Location);
            $('#edit_DOJ').val(rec.Date_of_Joining);
            $('#edit_Date_of_Leaving').val(rec.Date_of_Leaving);
            $('#edit_Employee_Category').val(rec.Employee_Category);
            $('#edit_Resignation_Type').val(rec.Resignation_Type);
            $('#edit_Remarks').val(rec.Remarks);

            // Load reasons for the selected resignation type, then set the value
            var rotSelect = $('#edit_Reason_of_Turnover');
            rotSelect.html('<option value="">Select Reason</option>');
            if (rec.Resignation_Type) {
                axios.get(window.baseUrl + '/_inc/value.php?action_type=GET_REASON_BY_TYPE&Resignation_Type_ID=' + rec.Resignation_Type)
                    .then(function(resp) {
                        if (resp.data.valid && resp.data.results) {
                            resp.data.results.forEach(function(item) {
                                rotSelect.append('<option value="' + item.Reason_ID + '">' + item.Reason + '</option>');
                            });
                            rotSelect.val(rec.Reason_of_Turnover);
                        }
                    });
            }

            $('#editModal').modal('show');
        });
    });

    // Cascade: Resignation Type -> Reason of Turnover in edit modal
    $(document).on('change', '#edit_Resignation_Type', function() {
        var value = $(this).val();
        var rotSelect = $('#edit_Reason_of_Turnover');
        rotSelect.html('<option value="">Select Reason</option>');
        if (value) {
            axios.get(window.baseUrl + '/_inc/value.php?action_type=GET_REASON_BY_TYPE&Resignation_Type_ID=' + value)
                .then(function(resp) {
                    if (resp.data.valid && resp.data.results) {
                        resp.data.results.forEach(function(item) {
                            rotSelect.append('<option value="' + item.Reason_ID + '">' + item.Reason + '</option>');
                        });
                    }
                });
        }
    });

    // Submit edit form
    $(document).on("click", "#update-submit", function(e) {
        e.preventDefault();
        var form = $('#edit-form');
        var formData = new FormData(form[0]);

        // Basic validation
        if (!$('#edit_Employee_Name').val()) {
            window.swal.fire({ title: "Field Missing!", text: "Please enter Employee Name.", icon: "warning" });
            return;
        }
        if (!$('#edit_Gender').val()) {
            window.swal.fire({ title: "Field Missing!", text: "Please select Gender.", icon: "warning" });
            return;
        }
        if (!$('#edit_Date_of_Birth').val()) {
            window.swal.fire({ title: "Field Missing!", text: "Please enter Date of Birth.", icon: "warning" });
            return;
        }
        if (!$('#edit_Department').val()) {
            window.swal.fire({ title: "Field Missing!", text: "Please select Department.", icon: "warning" });
            return;
        }
        if (!$('#edit_Designation').val()) {
            window.swal.fire({ title: "Field Missing!", text: "Please select Designation.", icon: "warning" });
            return;
        }
        if (!$('#edit_Location').val()) {
            window.swal.fire({ title: "Field Missing!", text: "Please select Location.", icon: "warning" });
            return;
        }
        if (!$('#edit_DOJ').val()) {
            window.swal.fire({ title: "Field Missing!", text: "Please enter Date of Joining.", icon: "warning" });
            return;
        }
        if (!$('#edit_Date_of_Leaving').val()) {
            window.swal.fire({ title: "Field Missing!", text: "Please enter Date of Leaving.", icon: "warning" });
            return;
        }
        if (!$('#edit_Employee_Category').val()) {
            window.swal.fire({ title: "Field Missing!", text: "Please select Employee Category.", icon: "warning" });
            return;
        }
        if (!$('#edit_Resignation_Type').val()) {
            window.swal.fire({ title: "Field Missing!", text: "Please select Resignation Type.", icon: "warning" });
            return;
        }
        if (!$('#edit_Reason_of_Turnover').val()) {
            window.swal.fire({ title: "Field Missing!", text: "Please select Reason of Turnover.", icon: "warning" });
            return;
        }

        axios.post(window.baseUrl + '/_inc/employee_turnover.php', formData)
            .then(function(response) {
                if (response.data.valid) {
                    window.swal.fire({ position: 'top-end', text: response.data.msg, icon: "success", showConfirmButton: false, timer: 1500 });
                    $('#editModal').modal('hide');
                    dt.DataTable().ajax.reload();
                } else {
                    window.swal.fire({ title: "Error!", text: response.data.msg, icon: "error" });
                }
            }).catch(function(error) {
                if (error.response && error.response.data && error.response.data.errorMsg) {
                    window.swal.fire({ title: "Error!", text: error.response.data.errorMsg, icon: "error" });
                }
            });
    });

    // ========== UPLOAD PDF ==========
    $(document).on("click", ".upload-pdf", function(e) {
        e.preventDefault();
        var d = dt.DataTable().row($(this).closest("tr")).data();
        $('#upload_Employee_ID').val(d.Employee_ID);
        $('#upload_Scan').val('');
        $('#upload_Scan_label').text('Choose file');
        $('#upload-scan-error').text('');
        $('#uploadModal').modal('show');
    });

    $(document).on("click", "#upload-submit", function(e) {
        e.preventDefault();
        var fileInput = document.getElementById('upload_Scan');
        if (!fileInput.files.length) {
            $('#upload-scan-error').text('Please select a PDF file.');
            return;
        }
        var ext = fileInput.files[0].name.split('.').pop().toLowerCase();
        if (ext !== 'pdf') {
            $('#upload-scan-error').text('Only PDF files are allowed.');
            return;
        }

        var form = $('#upload-form');
        var formData = new FormData(form[0]);
        axios.post(window.baseUrl + '/_inc/employee_turnover.php', formData)
            .then(function(response) {
                if (response.data.valid) {
                    window.swal.fire({ position: 'top-end', text: response.data.msg, icon: "success", showConfirmButton: false, timer: 1500 });
                    $('#uploadModal').modal('hide');
                    dt.DataTable().ajax.reload();
                } else {
                    window.swal.fire({ title: "Error!", text: response.data.msg, icon: "error" });
                }
            }).catch(function(error) {
                if (error.response && error.response.data && error.response.data.errorMsg) {
                    window.swal.fire({ title: "Error!", text: error.response.data.errorMsg, icon: "error" });
                }
            });
    });

});

function formatDate(input) {
    let value = input.value.replace(/\D/g, '');
    let formattedValue = '';

    if (value.length > 0) {
        let day = value.substring(0, 2);
        formattedValue = day;
    }
    if (value.length > 2) {
        let month = value.substring(2, 4);
        formattedValue += '-' + month;
    }
    if (value.length > 4) {
        formattedValue += '-' + value.substring(4, 8);
    }

    input.value = formattedValue;
}
