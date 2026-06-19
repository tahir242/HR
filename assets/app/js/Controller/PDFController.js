function docReady(fn) {
    if (document.readyState === "complete" || document.readyState === "interactive") {
        setTimeout(fn, 1);
    } else {
        document.addEventListener("DOMContentLoaded", fn);
    }
}

docReady(() => {


    let dt = $("#list");
    let i;
    let s = window.getParameterByName("s");
    let q = window.getParameterByName("q");
    let hideColums = dt.data("hide-colums").split(",");
    let hideColumsArray = [];
    if (hideColums.length) {
        for (i = 0; i < hideColums.length; i += 1) {
            hideColumsArray.push(parseInt(hideColums[i]));
        }
    }

    dt.dataTable({
        "processing": true,
        "serverSide": true,
        "dom": "rtip",
        "ajax": window.baseUrl + "/_inc/upload.php?s=" + s + "&q=" + q,
        "order": [[0, "desc"]],
        "aLengthMenu": [
            [10, 25, 50, 100, 200, -1],
            [10, 25, 50, 100, 200, "All"]
        ],
        "columnDefs": [
            { "targets": [5, 6, 7], "orderable": false },
            { "visible": false, "targets": hideColumsArray },
            { "className": "text-center", "targets": [7] },
        ],
        "columnDefs": [
            { "targets": [7], "orderable": false },
            { "visible": false, "targets": hideColumsArray },
            { "className": "text-center", "targets": [6, 7] },
            {
                "targets": [0],
                'createdCell': function (td, cellData, rowData, row, col) {
                    $(td).attr('data-title', $("#list thead tr th:eq(0)").html());
                }
            },
            {
                "targets": [1],
                'createdCell': function (td, cellData, rowData, row, col) {
                    $(td).attr('data-title', $("#list thead tr th:eq(1)").html());
                }
            },
            {
                "targets": [2],
                'createdCell': function (td, cellData, rowData, row, col) {
                    $(td).attr('data-title', $("#list thead tr th:eq(2)").html());
                }
            },
            {
                "targets": [3],
                'createdCell': function (td, cellData, rowData, row, col) {
                    $(td).attr('data-title', $("#list thead tr th:eq(3)").html());
                }
            },
            {
                "targets": [4],
                'createdCell': function (td, cellData, rowData, row, col) {
                    $(td).attr('data-title', $("#list thead tr th:eq(4)").html());
                }
            },
            {
                "targets": [5],
                'createdCell': function (td, cellData, rowData, row, col) {
                    $(td).attr('data-title', $("#list thead tr th:eq(5)").html());
                }
            },
            {
                "targets": [6],
                'createdCell': function (td, cellData, rowData, row, col) {
                    $(td).attr('data-title', $("#list thead tr th:eq(6)").html());
                }
            },
            {
                "targets": [7],
                'createdCell': function (td, cellData, rowData, row, col) {
                    $(td).attr('data-title', $("#list thead tr th:eq(7)").html());
                }
            },
        ],
        "aoColumns": [
            { data: "Employee_ID" },
            { data: "Name" },
            { data: "Department" },
            { data: "Designation" },
            { data: "Date_of_Joining" },
            { data: "Scan" },
            { data: "Status" },
            { data: "btn_edit" }
        ],
        "pageLength": 10,
    });

    // $('#searchInput').keyup(function () {
    //     dt.DataTable().search($(this).val()).draw();
    // });

    if (window.getParameterByName('s')) {
        $("#filter").val(window.getParameterByName('s'));
    }

    if (window.getParameterByName('q')) {
        $("#searchInput").val(window.getParameterByName('q'));
    }

    function updateURL() {
        var statusValue = $("#filter").val();
        var search = $('#searchInput').val();

        var url = "pdf_list.php";
        var params = [];

        if (statusValue != 'null') {
            params.push("s=" + statusValue);
        }
        if (search != '') {
            params.push("q=" + search);
        }
        if (params.length > 0) {
            url += "?" + params.join("&");
        }

        window.location = url;
    }

    // Event listeners for dropdown changes
    $("#apply-filter").on("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        updateURL();
    });

    // View Form
    $(document).delegate(".delete-pdf", "click", function (e) {
        e.stopPropagation();
        e.preventDefault();
        var d = dt.DataTable().row($(this).closest("tr")).data();
        window.swal.fire({
            title: "Are you sure!",
            html: "You want to delete this pdf.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: 'Yes, Delete it!',
        }).then(function (willDelete) {
            if (willDelete.isConfirmed) {
                $("#ajaxWait").children().show();
                let formData = new URLSearchParams();
                formData.append('action_type', 'DELETEPDF');
                formData.append('file', d.Scan);
                axios.post('../_inc/upload.php', formData).then(function(response) {
                    $("#ajaxWait").children().hide();
                    if (response.data.valid == true) {
                        window.swal.fire({
                            text: response.data.msg,
                            icon: "success",
                        });
                        dt.DataTable().ajax.reload();
                    } else {
                        window.swal.fire({
                            title: "Error!",
                            text: response.data.msg,
                            icon: "error",
                        });
                    }
                }).catch(function(error) {
                    $("#ajaxWait").children().hide();
                    if (error.response && error.response.data && error.response.data.errorMsg) {
                        Swal.fire({
                            position: 'top-end',
                            icon: 'error',
                            title: error.response.data.errorMsg,
                            showConfirmButton: false,
                            timer: 1500
                          });
                    } else {
                        console.error('Error:', error);
                    }
                });
            }
        });
    });

    $(document).delegate(".view-pdf", "click", function (e) {
        e.stopImmediatePropagation();
        e.stopPropagation();
        e.preventDefault();
        var d = dt.DataTable().row($(this).closest("tr")).data();
        var formData = new FormData();
        formData.append("action", "VIEWFILE");
        formData.append("file", d.Scan);
        axios.post("../_inc/upload.php", formData, { responseType: 'blob' }).then(function(response){
            var blob = response.data;
            if (blob.type === 'application/pdf') {
                var pdfUrl = URL.createObjectURL(blob);
                var viewerUrl = '../_inc/vendor/pdfjs/web/viewer.html?file=' + encodeURIComponent(pdfUrl);
                var width = 520;
                var height = 570;
                var left = window.screen.width / 2 - width / 2;
                var top = window.screen.height / 2 - height / 2;
                var newWindow = window.open(
                    viewerUrl,
                    '_blank',
                    'location=yes, height=' + height + ', width=' + width + ', top=' + top + ', left=' + left + ', scrollbars=yes, status=yes'
                );
                // Check if the new window/tab opened successfully
                if (!newWindow || newWindow.closed || typeof newWindow.closed == 'undefined') {
                    // Handle if the window didn't open
                    alert("Popup blocked. Please allow popups for this website.");
                }
            } else {
                alert('Unsupported file format');
            }
        }).catch(function(error) {
            if (error.response && error.response.data && error.response.data.errorMsg) {
                Swal.fire({
                    position: 'top-end',
                    icon: 'error',
                    title: 'File not open. Please try again',
                    showConfirmButton: false,
                    timer: 1500
                });
            } else {
                console.error('Error:', error);
            }
        });
    });


});