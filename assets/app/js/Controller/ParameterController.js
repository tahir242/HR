function docReady(fn) {
    if (document.readyState === "complete" || document.readyState === "interactive") {
        setTimeout(fn, 1);
    } else {
        document.addEventListener("DOMContentLoaded", fn);
    }
}

docReady(function () {


    document.addEventListener("visibilitychange", function () {
        if (document.visibilityState === "hidden") {
            // User switched tab or minimized the browser
            console.log("User switched tab or minimized the browser");
        } else {
            // User returned to the tab
            console.log("User returned to the tab");
        }
    });

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
        "ajax": window.baseUrl + "/_inc/parameter.php",
        "order": [[0, "asc"]],
        "aLengthMenu": [
            [10, 25, 50, 100, 200, -1],
            [10, 25, 50, 100, 200, "All"]
        ],
        "columnDefs": [
            { "targets": [1, 2, 3], "orderable": false },
            { "visible": false, "targets": hideColumnsArray },
            { "className": "text-center", "targets": [0, 2, 3] },
            { "className": "text-left", "targets": [1] },
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
        ],
        "aoColumns": [
            { data: "ID" },
            { data: "Parameter" },
            { data: "Value" },
            { data: "btn_update" }
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
            title: "Create New Parameter",
            url: window.baseUrl + "/_inc/parameter.php?action_type=CREATE"
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

    $(document).delegate(".transbtn", "click", function (e) {
        e.stopImmediatePropagation();
        e.stopPropagation();
        e.preventDefault();

        var $id, $btn, $paramValue, $queryData;
        $btn = $(this);
        $id = $btn.data("id");
        $paramValue = $("#value" + $id).val();
        $queryData = "id=" + $id + "&param_value=" + $paramValue;

        axios.post(window.baseUrl + "/_inc/parameter.php?action_type=UPDATEPARAMETER", $queryData, {
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        })
            .then(function (response) {
                if (response.data.valid == true) {
                    window.swal.fire({
                        text: response.data.msg,
                        icon: "success",
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
            })
            .catch(function (error) {
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
    });

});