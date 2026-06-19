function docReady(fn) {
    if (document.readyState === "complete" || document.readyState === "interactive") {
        setTimeout(fn, 1);
    } else {
        document.addEventListener("DOMContentLoaded", fn);
    }
}

docReady(function () {

    var i;    
    var dt = $("#list");
    var hideColumns = dt.attr("data-hide-colums");
    var hideColumnsArray = [];

    if (hideColumns) {
        var hideColumnsSplit = hideColumns.split(",");
        for (var i = 0; i < hideColumnsSplit.length; i++) {
            hideColumnsArray.push(parseInt(hideColumnsSplit[i]));
        }
    }

//================
    // Start datatable
    //================
    dt.dataTable({
        "processing": true,
        "serverSide": true,
        "dom": "rtip",
        "ajax": {
            url: window.sso + "/_inc/user.php?hostID=" + hostID,
            crossDomain: true,
            xhrFields: {
                withCredentials: true
            }
        },
        "order": [[ 0, "desc"]],
        "aLengthMenu": [
            [10, 25, 50, 100, 200, -1],
            [10, 25, 50, 100, 200, "All"]
        ],
        "columnDefs": [
            {"targets": [4,5,7,8], "orderable": false},
            {"visible": false,  "targets": hideColumnsArray},
            {"className": "text-center", "targets": [8]},
            { 
                "targets": [0],
                'createdCell':  function (td, cellData, rowData, row, col) {
                   $(td).attr('data-title', $("#user-user-list thead tr th:eq(0)").html());
                }
            },
            { 
                "targets": [1],
                'createdCell':  function (td, cellData, rowData, row, col) {
                   $(td).attr('data-title', $("#user-user-list thead tr th:eq(1)").html());
                }
            },
            { 
                "targets": [2],
                'createdCell':  function (td, cellData, rowData, row, col) {
                   $(td).attr('data-title', $("#user-user-list thead tr th:eq(2)").html());
                }
            },
            { 
                "targets": [3],
                'createdCell':  function (td, cellData, rowData, row, col) {
                   $(td).attr('data-title', $("#user-user-list thead tr th:eq(3)").html());
                }
            },
            { 
                "targets": [4],
                'createdCell':  function (td, cellData, rowData, row, col) {
                   $(td).attr('data-title', $("#user-user-list thead tr th:eq(4)").html());
                }
            },
            { 
                "targets": [5],
                'createdCell':  function (td, cellData, rowData, row, col) {
                   $(td).attr('data-title', $("#user-user-list thead tr th:eq(5)").html());
                }
            },
            { 
                "targets": [6],
                'createdCell':  function (td, cellData, rowData, row, col) {
                   $(td).attr('data-title', $("#user-user-list thead tr th:eq(6)").html());
                }
            },
            { 
                "targets": [7],
                'createdCell':  function (td, cellData, rowData, row, col) {
                   $(td).attr('data-title', $("#user-user-list thead tr th:eq(7)").html());
                }
            },
            { 
                "targets": [8],
                'createdCell':  function (td, cellData, rowData, row, col) {
                   $(td).attr('data-title', $("#user-user-list thead tr th:eq(8)").html());
                }
            },
        ],
        "aoColumns": [
            {data : "UserID"},
            {data : "EmpID"},
            {data : "Username"},
            {data : "Fullname"},
            {data : "Email"},
            {data : "Mobile"},
            {data : "CreatedDtTm"},
            {data : "Active"},
            {data : "btn_profile"}
        ],
        "pageLength": 50,
    });

    $('#searchInput').keyup(function () {
        dt.DataTable().search($(this).val()).draw();
    });

    $(document).delegate("#userprofileid", "click", function(e) {
        e.stopImmediatePropagation();
        e.stopPropagation();
        e.preventDefault();
        const $tag = $(this);
        const userid = $tag.data().userid;
        const form = document.createElement("form");
        form.setAttribute("method", "post");
        form.setAttribute("action", "profile.php");
        const EID = document.createElement("input");
        EID.setAttribute("type", "hidden");
        EID.setAttribute("name", "UserID");
        EID.setAttribute("value", userid);
        form.appendChild(EID);
        document.getElementsByTagName("body")[0].appendChild(form);
        form.submit();
    });

});
