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
    if (!validateString($request->post['Item_Name'])) {
        throw new Exception("Please Write Item Name");
    }

    if (!validateString($request->post['Unit'])) {
        throw new Exception("Please Write Unit");
    }
    
    if (!validateInteger($request->post['Packing_Unit'])) {
        throw new Exception("Please Write Packing Unit");
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
        if (user_role_id() != 1 && !has_permission(1, 'create_item')) {
            throw new Exception("Error Create Permission");
        }

        // Validate post data
        validate_request_data($request);
        // Validate existance
        validate_existance($request);

        // Fetch
        $id = $model->addItem($request->post);

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
        if (user_role_id() != 1 && !has_permission(1, 'modify_item')) {
          throw new Exception("Error Update Permission");
        }

        // Validate Permission ID
        if (!validateInteger($request->post['Item_ID'])) {
            throw new Exception("Error in Item ID");
        }

        $id = $request->post['Item_ID'];

        // Validate post data
        validate_request_data($request);

        // Validate existance
        validate_existance($request, $id);

        $id = $model->editItem($id, $request->post);

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
    include 'template/item/item_create_form.php';
    exit();
}

// edit form
if (isset($request->get['Item_ID']) and isset($request->get['action_type']) && $request->get['action_type'] == 'EDIT') {
    $row = $model->getItem($request->get['Item_ID']);
    include 'template/item/item_edit_form.php';
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
// DB table to use
$table = 'Ration_Item';

// Table's primary key
$primaryKey = 'Item_ID';

$columns = array(
    array(
        'db' => 'Item_ID',
        'dt' => 'DT_RowId',
        'formatter' => function ($d, $row) {
            return 'row_' . $d;
        }
    ),
    array('db' => 'Item_ID', 'dt' => 'Item_ID'),
    array('db' => 'Item_Name', 'dt' => 'Item_Name'),
    array('db' => 'Unit', 'dt' => 'Unit'),
    array('db' => 'Packing_Unit', 'dt' => 'Packing_Unit'),
    array('db' => 'Issue_Qty', 'dt' => 'Issue_Qty'),
    array('db' => 'Balance', 'dt' => 'Balance'),
    array(
        'db' => 'Item_ID',
        'dt' => 'btn_edit',
        'formatter' => function ($d, $row) {
            return '<button class="btn btn-sm btn-primary edit" type="button" title="Edit"><i class="fas fa-pencil-alt"></i></button>';
        }
    )
);

echo json_encode(
    SSP::simple($request->get, $sql_details, $table, $primaryKey, $columns)
);

/**
 *===================
 * END DATATABLE
 *===================
 */
