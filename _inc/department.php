<?php
ob_start();
include("../_init.php");

if (!is_loggedin()) {
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => "Error Login"));
    exit();
}

$model = registry()->get('loader')->model('department');

function validate_request_data($request)
{
    if (!validateString($request->post['Department'])) {
        throw new Exception("Please Write Department Name");
    }
}

function validate_existance($request, $id = 0)
{
    $statement = "SELECT * FROM [Department] WHERE [Department] = ? AND [Department_ID] != ?";
    $params = array($request->post['Department'], $id);
    $stmt = sqlsrv_query(db()->conn, $statement, $params, array("Scrollable" => SQLSRV_CURSOR_KEYSET));
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $total_try = sqlsrv_num_rows($stmt);
    if ($total_try > 0) {
        throw new Exception("Department Name Already Exists");
    }
}

// Create
if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'CREATE') {
    try {
        if (user_role_id() != 1 && !has_permission(1, 'create_department')) {
            throw new Exception("Error Create Permission");
        }

        validate_request_data($request);
        validate_existance($request);

        $id = $model->addDepartment($request->post);

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
        if (user_role_id() != 1 && !has_permission(1, 'modify_department')) {
            throw new Exception("Error Update Permission");
        }

        if (!validateInteger($request->post['Department_ID'])) {
            throw new Exception("Error in Department ID");
        }

        $id = $request->post['Department_ID'];

        validate_request_data($request);
        validate_existance($request, $id);

        $id = $model->editDepartment($id, $request->post);

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
        if (user_role_id() != 1 && !has_permission(1, 'delete_department')) {
            throw new Exception("Error Delete Permission");
        }

        if (!validateInteger($request->post['Department_ID'])) {
            throw new Exception("Error in Department ID");
        }

        $id = $request->post['Department_ID'];
        $shift_id = isset($request->post['Shift_Department_ID']) && !empty($request->post['Shift_Department_ID']) ? $request->post['Shift_Department_ID'] : null;
        $model->deleteDepartment($id, $shift_id);

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
    include 'template/department/department_create_form.php';
    exit();
}

// edit form
if (isset($request->get['Department_ID']) and isset($request->get['action_type']) && $request->get['action_type'] == 'EDIT') {
    $row = $model->getDepartment($request->get['Department_ID']);
    include 'template/department/department_edit_form.php';
    exit();
}

// delete form
if (isset($request->get['Department_ID']) && isset($request->get['action_type']) && $request->get['action_type'] == 'DELETE_FORM') {
    $row = $model->getDepartment($request->get['Department_ID']);
    include 'template/department/department_delete_form.php';
    exit();
}

/**
 * DATATABLE
 */
if (user_role_id() != 1 && !has_permission(1, 'read_department')) {
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => "Error Read Permission"));
    exit();
}

require DIR_LIBRARY . "mssql.ssp.class.php";
$table = 'Department';
$primaryKey = 'Department_ID';

$columns = array(
    array(
        'db' => 'Department_ID',
        'dt' => 'DT_RowId',
        'formatter' => function ($d, $row) {
            return 'row_' . $d;
        }
    ),
    array('db' => 'Department_ID', 'dt' => 'Department_ID'),
    array('db' => 'Department', 'dt' => 'Department'),
    array(
        'db' => 'Active',
        'dt' => 'Active',
        'formatter' => function ($d, $row) {
            return $d == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
        }
    ),
    array(
        'db' => 'Department_ID',
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
