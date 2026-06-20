function docReady(fn) {
    if (document.readyState === "complete" || document.readyState === "interactive") {
        setTimeout(fn, 1);
    } else {
        document.addEventListener("DOMContentLoaded", fn);
    }
}

docReady(function () {

    var dt = $("#list");
    var hideColumns = dt.attr("data-hide-colums");
    var hideColumnsArray = [];

    if (hideColumns) {
        var hideColumnsSplit = hideColumns.split(",");
        for (var i = 0; i < hideColumnsSplit.length; i++) {
            hideColumnsArray.push(parseInt(hideColumnsSplit[i]));
        }
    }

    dt.dataTable({
        "processing": true,
        "serverSide": true,
        "dom": "rtip",
        "ajax": window.baseUrl + "/_inc/sub-module.php",
        "order": [[0, "asc"]],
        "aLengthMenu": [
            [10, 25, 50, 100, 200, -1],
            [10, 25, 50, 100, 200, "All"]
        ],
        "columnDefs": [
            { "targets": [7], "orderable": false },
            { "visible": false, "targets": hideColumnsArray },
            { "className": "text-center", "targets": [0, 1, 2, 3, 4, 5, 6, 7, 8] },
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
            {
                "targets": [8],
                'createdCell': function (td, cellData, rowData, row, col) {
                    $(td).attr('data-title', $("#list thead tr th:eq(8)").html());
                }
            },
        ],
        "aoColumns": [
            { data: "Sub_Module_URN" },
            { data: "Module_ID" },
            { data: "Sub_Module_ID" },
            { data: "Sub_Module" },
            { data: "Show_In_Menu" },
            { data: "Permission" },
            { data: "Sort" },
            { data: "Active" },
            { data: "btn_edit" }
        ],
        "pageLength": 15,
    });

    $('#search-input').keyup(function () {
        dt.DataTable().search($(this).val()).draw();
    });

    let formSubmittion = (form, url) => {
        const formData = new FormData(form[0]);
        axios.post(window.baseUrl + "/_inc/" + url, formData)
            .then(response => {
                if (response.data.valid == true) {
                    closeModal();
                    window.swal.fire({
                        text: response.data.msg,
                        icon: "success",
                    });

                    dt.DataTable().ajax.reload(function(json) {
                        if ($("#row_"+response.data.id).length) {
                            $("#row_"+response.data.id).flash("yellow", 5000);
                        }
                    }, false);
                } else {
                    window.swal.fire({
                        title: "Error!",
                        text: response.data.msg,
                        icon: "error",
                    });
                }
            }).catch(error => {
                if (error.response && error.response.data && error.response.data.errorMsg) {
                    window.swal.fire({
                        title: "Error!",
                        text: error.response.data.errorMsg,
                        icon: "error",
                    });
                } else {
                    console.error('Error:', error);
                }
            });
    }

    // Create Modal
    $(document).delegate(".create-new", "click", function (e) {
        e.stopImmediatePropagation();
        e.stopPropagation();
        e.preventDefault();
        let $scope = {
            size: "md",
            title: "Create New Sub Module",
            url: window.baseUrl + "/_inc/sub-module.php?action_type=CREATE"
        };
        openModal($scope);
        setTimeout(function () {
            $('#Module_ID').select2();
        }, 500);
    });

    $(document).delegate("#create-submit", "click", function (e) {
        e.stopImmediatePropagation();
        e.stopPropagation();
        e.preventDefault();

        var $tag = $(this);
        $("#ajaxWait").children().show();
        var form = $tag.closest('form');
        var actionUrl = form.attr("action");
        formSubmittion(form, actionUrl);
        $("#ajaxWait").children().hide();
    });

    $(document).delegate(".edit-sub-module", "click", function (e) {
        e.stopPropagation();
        e.preventDefault();
        var d = dt.DataTable().row($(this).closest("tr")).data();
        let $scope = {
            size: "md",
            title: d.Sub_Module,
            url: window.baseUrl + "/_inc/sub-module.php?action_type=EDIT&Sub_Module_URN=" + d.Sub_Module_URN
        };
        openModal($scope);
        setTimeout(function () {
            $('#Module_ID').select2();
        }, 500);
    });

    $(document).delegate("#edit-submit", "click", function (e) {
        e.stopImmediatePropagation();
        e.stopPropagation();
        e.preventDefault();

        var $tag = $(this);
        $("#ajaxWait").children().show();
        var form = $tag.closest('form');
        var actionUrl = form.attr("action");
        formSubmittion(form, actionUrl);
        $("#ajaxWait").children().hide();
    });

});
