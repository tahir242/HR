<?php
ob_start();
include("../_init.php");

// Check, if user logged in or not
// If user is not logged in then return an alert message
if (!is_loggedin()) {
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => "Error Login"));
    exit();
}

// LOAD MODEL 
$model = registry()->get('loader')->model('item');

/**
 *===================
 * START DATATABLE
 *===================
 */

// Check, if user has reading permission or not
// If user have not reading permission return an alert message
if (user_role_id() != 1 && !has_permission(1, 'read_ii')) {
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => "Error Read Permission"));
    exit();
}

require DIR_LIBRARY . "mssql.ssp.class.php";

$where_query = "";
if (isset($request->get['i']) && $request->get['i'] !== 'null' && $request->get['i'] != '') {
    $item = $request->get['i'];
    $where_query .= ' AND RT.Item_ID = ' . $item . '';
}

if (isset($request->get['s']) && $request->get['s'] != 'null' && $request->get['s'] != '') {
    $search = $request->get['s'];
    $where_query .= " AND RT.Employee_ID LIKE '%$search%'";
}

if (isset($request->get['y']) && $request->get['y'] !== 'null' && $request->get['y'] != '') {
    $year = $request->get['y'];
    $where_query .= " AND RT.[Year] = " . $year;
}
    
// DB table to use
$table = '(SELECT RT.Item_ID, RT.Transaction_ID, RT.Employee_ID, RT.Transaction_DtTm, RT.Transaction_By, RT.Issued_Qty, RI.Item_Name, RI.Unit  FROM [Ration_Transaction] RT LEFT JOIN [Ration_Item] RI ON RT.Item_ID = RI.Item_ID WHERE RT.Issued_Qty <> 0 ' . $where_query . ') AS [Ration_Transaction]';
// Table's primary key
$primaryKey = 'Transaction_ID';

$columns = array(
    array(
        'db' => 'Transaction_ID',
        'dt' => 'DT_RowId',
        'formatter' => function ($d, $row) {
            return 'row_' . $d;
        }
    ),
    array('db' => 'Transaction_ID', 'dt' => 'Transaction_ID'),
    array(
        'db' => 'Transaction_DtTm',
        'dt' => 'Transaction_DtTm',
        'formatter' => function ($d, $row) {
            return date_normalizer($row['Transaction_DtTm'], "d-M-Y h:i A");
        }
    ),
    array(
        'db' => 'Employee_ID',
        'dt' => 'Employee_ID',
        'formatter' => function ($d, $row) {
            return $row['Employee_ID'];
        }
    ),
    array(
        'db' => 'Item_Name',
        'dt' => 'Item_Name',
        'formatter' => function ($d, $row) {
            return $row['Item_Name'];
        }
    ),
    array(
        'db' => 'Issued_Qty',
        'dt' => 'Issued_Qty',
        'formatter' => function ($d, $row) {
            return $row['Issued_Qty'];
        }
    ),
    array(
        'db' => 'Unit',
        'dt' => 'Unit',
        'formatter' => function ($d, $row) {
            return $row['Unit'];
        }
    ),
);

$where = "1=1";

echo json_encode(
    SSP::complex($request->get, $sql_details, $table, $primaryKey, $columns, NULL, $where),
);
