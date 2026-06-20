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

    let item = window.getParameterByName("i");
    let year = window.getParameterByName("y");

    year = year ? year : $('#Working_Year').val();

    console.log("Item Receive Controller Loaded", { item, year });

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
        "ajax": window.baseUrl + "/_inc/item_receive.php?i=" + item + "&y=" + year,
        "order": [[0, "asc"]],
        "aLengthMenu": [
            [10, 25, 50, 100, 200, -1],
            [10, 25, 50, 100, 200, "All"]
        ],
        "columnDefs": [
            { "targets": [5], "orderable": false },
            { "visible": false, "targets": hideColumnsArray },
            { "className": "text-center", "targets": [0, 2, 3, 4] },
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
            {
                "targets": [4],
                'createdCell': function (td, cellData, rowData, row, col) {
                    $(td).attr('data-title', $("#list thead tr th:eq(4)").text());
                }
            },
            {
                "targets": [5],
                'createdCell': function (td, cellData, rowData, row, col) {
                    $(td).attr('data-title', $("#list thead tr th:eq(4)").text());
                }
            },
        ],
        "aoColumns": [
            { data: "Transaction_ID" },
            { data: "Transaction_DtTm" },
            { data: "Item_Name" },
            { data: "Received_Qty" },
            { data: "Unit" },
            { data: "btn_edit" }
        ],
        "pageLength": 15,
    });

    if (window.getParameterByName('i')) {
        $("#Item_ID").val(window.getParameterByName('i'));
    }

    if (window.getParameterByName('y')) {
        $("#Working_Year").val(window.getParameterByName('y'));
    }

    function updateURL() {
        var item = $("#Item_ID").val();
        var workingYear = $('#Working_Year').val();

        var url = "item_receive.php";
        var params = [];

        if (item != 'null') {
            params.push("i=" + item);
        }
        if (workingYear != '') {
            params.push("y=" + workingYear);
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
            title: "Receive Item",
            url: window.baseUrl + "/_inc/item_receive.php?action_type=CREATE"
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
            title: d.Item_Name,
            url: window.baseUrl + "/_inc/item_receive.php?action_type=EDIT&Transaction_ID=" + d.Transaction_ID
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

});
