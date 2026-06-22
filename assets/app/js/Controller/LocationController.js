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
        "ajax": window.baseUrl + "/_inc/location.php",
        "order": [[1, "asc"]],
        "aLengthMenu": [
            [10, 25, 50, 100, 200, -1],
            [10, 25, 50, 100, 200, "All"]
        ],
        "columnDefs": [
            { "targets": [3], "orderable": false },
            { "visible": false, "targets": hideColumnsArray },
            { "className": "text-center", "targets": [0, 2, 3] },
            {
                "targets": [0],
                'createdCell': function (td, cellData, rowData, row, col) {
                    $(td).attr('data-title', $("#list thead tr th:eq(0)").text());
                }
            },
            {
                "targets": [1],
                'createdCell': function (td, cellData, rowData, row, col) {
                    $(td).attr('data-title', $("#list thead tr th:eq(1)").text());
                }
            },
            {
                "targets": [2],
                'createdCell': function (td, cellData, rowData, row, col) {
                    $(td).attr('data-title', $("#list thead tr th:eq(2)").text());
                }
            },
            {
                "targets": [3],
                'createdCell': function (td, cellData, rowData, row, col) {
                    $(td).attr('data-title', $("#list thead tr th:eq(3)").text());
                }
            },
        ],
        "aoColumns": [
            { data: "Location_ID" },
            { data: "Location" },
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
                        position: 'top-end',
                        icon: "success",
                        showConfirmButton: false,
                        timer: 1500
                    });

                    dt.DataTable().ajax.reload(function (json) {
                        if ($("#row_" + response.data.id).length) {
                            $("#row_" + response.data.id).flash("yellow", 5000);
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
            title: "Create New Location",
            url: window.baseUrl + "/_inc/location.php?action_type=CREATE"
        };
        openModal($scope);
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

    $(document).delegate(".edit", "click", function (e) {
        e.stopPropagation();
        e.preventDefault();
        var d = dt.DataTable().row($(this).closest("tr")).data();
        let $scope = {
            size: "md",
            title: d.Location,
            url: window.baseUrl + "/_inc/location.php?action_type=EDIT&Location_ID=" + d.Location_ID
        };
        openModal($scope);
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

    $(document).delegate(".delete", "click", function (e) {
        e.stopPropagation();
        e.preventDefault();
        var d = dt.DataTable().row($(this).closest("tr")).data();
        let $scope = {
            size: "md",
            title: "Delete " + d.Location,
            url: window.baseUrl + "/_inc/location.php?action_type=DELETE_FORM&Location_ID=" + d.Location_ID
        };
        openModal($scope);
    });

    $(document).delegate("#delete-submit", "click", function (e) {
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
