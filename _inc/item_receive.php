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

// Validate post data
function validate_request_data($request)
{
    if (!validateInteger($request->post['Item_ID'])) {
        throw new Exception("Please Select Item");
    }

    if (!validateString($request->post['Received_Qty'])) {
        throw new Exception("Please Write Received Qty");
    }

}

// Validate, if exist or not
function validate_existance($request, $id = 0)
{
    // Check Duplicate
    $statement = "SELECT * FROM [Ration_Item] WHERE [Item_Name] = ? AND [Item_ID] != ?";
    $params = array($request->post['Item_Name'], $id);
    $stmt = sqlsrv_query(db()->conn, $statement, $params, array("Scrollable" => SQLSRV_CURSOR_KEYSET));
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $total_try = sqlsrv_num_rows($stmt);
    if ($total_try > 0) {
        throw new Exception("Item Name Already Exists");
    }
}

// Create
if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'CREATE') {
    try {

        // Check create permission
        if (user_role_id() != 1 && !has_permission(1, 'create_ir')) {
            throw new Exception("Error Create Permission");
        }

        // Validate post data
        validate_request_data($request);
        // Validate existance
        // validate_existance($request);

        // Fetch
        $id = $model->addReceiveItem($request->post);

        header('Content-Type: application/json');
        echo json_encode(array("valid" => true, 'msg' => "Adding Success", 'id' => $id));
        exit();

    } catch (Exception $e) {

        header('HTTP/1.1 422 Unprocessable Entity');
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('errorMsg' => $e->getMessage()));
        exit();
    }
}

// Update
if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'UPDATE') {
    try {

        // Check update permission
        if (user_role_id() != 1 && !has_permission(1, 'modify_ir')) {
            throw new Exception("Error Update Permission");
        }

        // Validate Permission ID
        if (!validateInteger($request->post['Transaction_ID'])) {
            throw new Exception("Error in Transaction ID");
        }

        $id = $request->post['Transaction_ID'];

        // Validate post data
        validate_request_data($request);

        // Validate existance
        // validate_existance($request, $id);

        $id = $model->editReceiveItem($id, $request->post);

        header('Content-Type: application/json');
        echo json_encode(array('valid' => true, 'msg' => 'Update Success', 'id' => $id));
        exit();

    } catch (Exception $e) {
        $error_message = $e->getMessage();
        header('HTTP/1.1 422 Unprocessable Entity');
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('errorMsg' => $error_message));
        exit();
    }
}

// create form
if (isset($request->get['action_type']) && $request->get['action_type'] == 'CREATE') {
    include 'template/item/item_receive_form.php';
    exit();
}

// edit form
if (isset($request->get['Transaction_ID']) and isset($request->get['action_type']) && $request->get['action_type'] == 'EDIT') {
    $query = "SELECT TOP 1 * FROM [Ration_Transaction] WHERE [Transaction_ID] = ?";
    $row = $db->get_row($query, [$request->get['Transaction_ID']]);
    include 'template/item/item_receive_edit_form.php';
    exit();
}

/**
 *===================
 * START DATATABLE
 *===================
 */

// Check, if user has reading permission or not
// If user have not reading permission return an alert message
if (user_role_id() != 1 && !has_permission(1, 'read_item')) {
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

if (isset($request->get['y']) && $request->get['y'] !== 'null' && $request->get['y'] != '') {
    $year = $request->get['y'];
    $where_query .= " AND RT.[Year] = " . $year;
}

// DB table to use
$table = '(SELECT RT.Item_ID, RT.Transaction_ID, RT.Transaction_DtTm, RT.Transaction_By, RT.Received_Qty, RI.Item_Name, RI.Unit  FROM [Ration_Transaction] RT LEFT JOIN [Ration_Item] RI ON RT.Item_ID = RI.Item_ID WHERE RT.Received_Qty <> 0 ' . $where_query . ') AS [Ration_Transaction]';
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
        'db' => 'Item_Name',
        'dt' => 'Item_Name',
        'formatter' => function ($d, $row) {
            return $row['Item_Name'];
        }
    ),
    array(
        'db' => 'Received_Qty',
        'dt' => 'Received_Qty',
        'formatter' => function ($d, $row) {
            return $row['Received_Qty'];
        }
    ),
    array(
        'db' => 'Unit',
        'dt' => 'Unit',
        'formatter' => function ($d, $row) {
            return $row['Unit'];
        }
    ),
    array(
        'db' => 'Transaction_ID',
        'dt' => 'btn_edit',
        'formatter' => function ($d, $row) {
            return '<button class="btn btn-sm btn-primary edit" type="button" title="Edit"><i class="fas fa-pencil-alt"></i></button>';
        }
    ),
);

$where = "1=1";

echo json_encode(
    SSP::complex($request->get, $sql_details, $table, $primaryKey, $columns, NULL, $where),
);
