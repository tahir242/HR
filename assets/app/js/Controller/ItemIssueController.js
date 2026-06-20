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

    let search = window.getParameterByName("s");
    let item = window.getParameterByName("i");
    let year = window.getParameterByName("y");

    year = year ? year : $('#Working_Year').val();

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
        "ajax": window.baseUrl + "/_inc/item_issue.php?s=" + search + "&i=" + item + "&y=" + year,
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
                    $(td).attr('data-title', $("#list thead tr th:eq(5)").text());
                }
            },
        ],
        "aoColumns": [
            { data: "Transaction_ID" },
            { data: "Transaction_DtTm" },
            { data: "Employee_ID" },
            { data: "Item_Name" },
            { data: "Issued_Qty" },
            { data: "Unit" },
        ],
        "pageLength": 15,
    });

    if (window.getParameterByName('i')) {
        $("#Item_ID").val(window.getParameterByName('i'));
    }

    if (window.getParameterByName('s')) {
        $("#searchInput").val(window.getParameterByName('s'));
    }

    if (window.getParameterByName('y')) {
        $("#Working_Year").val(window.getParameterByName('y'));
    }

    function updateURL() {
        var item = $("#Item_ID").val();
        var search = $('#searchInput').val();
        var workingYear = $('#Working_Year').val();

        var url = "item_issue.php";
        var params = [];

        if (item != 'null') {
            params.push("i=" + item);
        }
        if (search != '') {
            params.push("s=" + search);
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


});
