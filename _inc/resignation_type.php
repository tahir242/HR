<?php
ob_start();
include("../_init.php");

if (!is_loggedin()) {
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => "Error Login"));
    exit();
}

$model = registry()->get('loader')->model('resignation_type');

function validate_request_data($request)
{
    if (!validateString($request->post['Resignation_Type'])) {
        throw new Exception("Please Write Resignation Type Name");
    }
}

function validate_existance($request, $id = 0)
{
    $statement = "SELECT * FROM [Resignation_Type] WHERE [Resignation_Type] = ? AND [Resignation_Type_ID] != ?";
    $params = array($request->post['Resignation_Type'], $id);
    $stmt = sqlsrv_query(db()->conn, $statement, $params, array("Scrollable" => SQLSRV_CURSOR_KEYSET));
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $total_try = sqlsrv_num_rows($stmt);
    if ($total_try > 0) {
        throw new Exception("Resignation Type Name Already Exists");
    }
}

// Create
if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'CREATE') {
    try {
        if (user_role_id() != 1 && !has_permission(1, 'create_resignation_type')) {
            throw new Exception("Error Create Permission");
        }

        validate_request_data($request);
        validate_existance($request);

        $id = $model->addResignationType($request->post);

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
        if (user_role_id() != 1 && !has_permission(1, 'modify_resignation_type')) {
            throw new Exception("Error Update Permission");
        }

        if (!validateInteger($request->post['Resignation_Type_ID'])) {
            throw new Exception("Error in Resignation Type ID");
        }

        $id = $request->post['Resignation_Type_ID'];

        validate_request_data($request);
        validate_existance($request, $id);

        $id = $model->editResignationType($id, $request->post);

        header('Content-Type: application/json');
        echo json_encode(array('valid' => true, 'msg' => 'Update Success', 'id' => $id));
        exit();

    } catch (Exception $e) {
        header('HTTP/1.1 422 Unprocessable Entity');
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('errorMsg' => $e->getMessage()));
        exit();
    }
}

// Delete
if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'DELETE') {
    try {
        if (user_role_id() != 1 && !has_permission(1, 'delete_resignation_type')) {
            throw new Exception("Error Delete Permission");
        }

        if (!validateInteger($request->post['Resignation_Type_ID'])) {
            throw new Exception("Error in Resignation Type ID");
        }

        $id = $request->post['Resignation_Type_ID'];
        $shift_id = isset($request->post['Shift_Resignation_Type_ID']) && !empty($request->post['Shift_Resignation_Type_ID']) ? $request->post['Shift_Resignation_Type_ID'] : null;
        $model->deleteResignationType($id, $shift_id);

        header('Content-Type: application/json');
        echo json_encode(array('valid' => true, 'msg' => 'Delete Success'));
        exit();

    } catch (Exception $e) {
        header('HTTP/1.1 422 Unprocessable Entity');
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('errorMsg' => $e->getMessage()));
        exit();
    }
}

// create form
if (isset($request->get['action_type']) && $request->get['action_type'] == 'CREATE') {
    $resignationTypes = registry()->get('loader')->model('resignation_type')->getResignationTypes();
    include 'template/resignation_type/resignation_type_create_form.php';
    exit();
}

// edit form
if (isset($request->get['Resignation_Type_ID']) and isset($request->get['action_type']) && $request->get['action_type'] == 'EDIT') {
    $resignationTypes = registry()->get('loader')->model('resignation_type')->getResignationTypes();
    $row = $model->getResignationType($request->get['Resignation_Type_ID']);
    include 'template/resignation_type/resignation_type_edit_form.php';
    exit();
}

// delete form
if (isset($request->get['action_type']) && $request->get['action_type'] == 'DELETE_FORM') {
    $Resignation_Type_ID = $request->get['Resignation_Type_ID'];
    $Resignation_Type = $model->getResignationType($Resignation_Type_ID);
    $Resignation_Types = $model->getResignationTypes();
    include 'template/resignation_type/resignation_type_delete_form.php';
    exit();
}

/**
 * DATATABLE
 */
if (user_role_id() != 1 && !has_permission(1, 'read_resignation_type')) {
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => "Error Read Permission"));
    exit();
}

require DIR_LIBRARY . "mssql.ssp.class.php";
$table = 'Resignation_Type';
$primaryKey = 'Resignation_Type_ID';

$columns = array(
    array(
        'db' => 'Resignation_Type_ID',
        'dt' => 'DT_RowId',
        'formatter' => function ($d, $row) {
            return 'row_' . $d;
        }
    ),
    array('db' => 'Resignation_Type_ID', 'dt' => 'Resignation_Type_ID'),
    array('db' => 'Resignation_Type', 'dt' => 'Resignation_Type'),
    array(
        'db' => 'Active',
        'dt' => 'Active',
        'formatter' => function ($d, $row) {
            return $d == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
        }
    ),
    array(
        'db' => 'Resignation_Type_ID',
        'dt' => 'btn_edit',
        'formatter' => function ($d, $row) {
            return '<button class="btn btn-sm btn-primary edit" type="button" title="Edit"><i class="fas fa-pencil-alt"></i></button> ' .
                   '<button class="btn btn-sm btn-danger delete" type="button" title="Delete"><i class="fas fa-trash"></i></button>';
        }
    )
);

echo json_encode(
    SSP::simple($request->get, $sql_details, $table, $primaryKey, $columns)
);


