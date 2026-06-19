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

    var u = window.getParameterByName("u");
    var t = window.getParameterByName("t");

    dt.dataTable({
        "serverSide": true,
        "dom": "rtip",
        "ajax": {
            "url": window.baseUrl + "/_inc/systemlog.php",
            "type": "GET",
            "data": function (d) {
                d.t = t;
                d.u = u;
                d.lastRecordID = getLastRecordID();
            }
        },
        "order": [[0, "desc"]],
        "aLengthMenu": [
            [10, 25, 50, 100, 200, -1],
            [10, 25, 50, 100, 200, "All"]
        ],
        "columnDefs": [
            { "targets": [1, 2, 3, 4, 5], "orderable": false },
            { "visible": false, "targets": hideColumnsArray },
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
        ],
        "aoColumns": [
            { data: "ID" },
            { data: "Date_Time" },
            { data: "User" },
            { data: "Type" },
            { data: "Source" },
            { data: "Time" }
        ],
        "pageLength": 20,
    });

    var intervalId;

    function refreshData() {
        dt.DataTable().ajax.reload(null, false);
    }

    function getLastRecordID() {
        var table = $('#list').DataTable();
        var lastRowData = table.row(':first').data();
        var lastRecordID = lastRowData ? (lastRowData.ID - 20) : null;
        if (intervalId) {
            return lastRecordID;
        } else {
            return null;
        }
    }

    function toggleRefresh() {
        if (intervalId) {
            clearInterval(intervalId);
            $("#refreshButton").text("Start Refresh");
            intervalId = null;
            dt.DataTable().ajax.reload(null, false, function () {
                dt.DataTable().settings()[0].ajax.data.lastRecordID = 1;
            });
        } else {
            refreshData();
            intervalId = setInterval(refreshData, 1500);
            $("#refreshButton").text("Stop Refresh");
        }
    }

    $("#refreshButton").click(toggleRefresh);

    //================
    // End datatable
    //================

    $(document).delegate("#apply-filter", "click", function (e) {
        e.preventDefault();
        var t = $('#t').val();
        var u = $('#u').val();
        window.location.href = "system-time-log.php?t=" + t + "&u=" + u;
    });

});