<?php
ob_start();
include("../_init.php");

if (!is_loggedin()) {
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => "Error Login"));
    exit();
}

$model = registry()->get('loader')->model('reason_of_turnover');
$rmodel = registry()->get('loader')->model('resignation_type');
function validate_request_data($request)
{
    if (!validateString($request->post['Reason'])) {
        throw new Exception("Please Write Reason");
    }
    if (!validateInteger($request->post['Resignation_Type_ID'])) {
        throw new Exception("Please Select Resignation Type");
    }
}

function validate_existance($request, $id = 0)
{
    $statement = "SELECT * FROM [Reason_of_Turnover] WHERE [Reason] = ? AND [Reason_ID] != ?";
    $params = array($request->post['Reason'], $id);
    $stmt = sqlsrv_query(db()->conn, $statement, $params, array("Scrollable" => SQLSRV_CURSOR_KEYSET));
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $total_try = sqlsrv_num_rows($stmt);
    if ($total_try > 0) {
        throw new Exception("Reason Already Exists");
    }
}

// Create
if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'CREATE') {
    try {
        if (user_role_id() != 1 && !has_permission(1, 'create_reason_of_turnover')) {
            throw new Exception("Error Create Permission");
        }

        validate_request_data($request);
        validate_existance($request);

        $id = $model->addReasonOfTurnover($request->post);

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
        if (user_role_id() != 1 && !has_permission(1, 'modify_reason_of_turnover')) {
            throw new Exception("Error Update Permission");
        }

        if (!validateInteger($request->post['Reason_ID'])) {
            throw new Exception("Error in Reason ID");
        }

        $id = $request->post['Reason_ID'];

        validate_request_data($request);
        validate_existance($request, $id);

        $id = $model->editReasonOfTurnover($id, $request->post);

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
        if (user_role_id() != 1 && !has_permission(1, 'delete_reason_of_turnover')) {
            throw new Exception("Error Delete Permission");
        }

        if (!validateInteger($request->post['Reason_ID'])) {
            throw new Exception("Error in Reason ID");
        }

        $id = $request->post['Reason_ID'];
        $shift_id = isset($request->post['Shift_Reason_ID']) && !empty($request->post['Shift_Reason_ID']) ? $request->post['Shift_Reason_ID'] : null;
        $model->deleteReasonOfTurnover($id, $shift_id);

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
    $resignationTypes = $rmodel->getResignationTypes();
    include 'template/reason_of_turnover/reason_of_turnover_create_form.php';
    exit();
}

// edit form
if (isset($request->get['Reason_ID']) and isset($request->get['action_type']) && $request->get['action_type'] == 'EDIT') {
    $row = $model->getReasonOfTurnover($request->get['Reason_ID']);
    $resignationTypes = $rmodel->getResignationTypes();
    include 'template/reason_of_turnover/reason_of_turnover_edit_form.php';
    exit();
}

// delete form
if (isset($request->get['Reason_ID']) and isset($request->get['action_type']) && $request->get['action_type'] == 'DELETE_FORM') {
    $Reason_ID = $request->get['Reason_ID'];
    $reason = $model->getReasonOfTurnover($Reason_ID);
    $reasons = $model->getReasonOfTurnovers();
    include 'template/reason_of_turnover/reason_of_turnover_delete_form.php';
    exit();
}

/**
 * DATATABLE
 */
if (user_role_id() != 1 && !has_permission(1, 'read_reason_of_turnover')) {
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => "Error Read Permission"));
    exit();
}

require DIR_LIBRARY . "mssql.ssp.class.php";
$table = 'Reason_of_Turnover';
$primaryKey = 'Reason_ID';

$columns = array(
    array(
        'db' => 'Reason_ID',
        'dt' => 'DT_RowId',
        'formatter' => function ($d, $row) {
            return 'row_' . $d;
        }
    ),
    array('db' => 'Reason_ID', 'dt' => 'Reason_ID'),
    array(
        'db' => 'Resignation_Type_ID',
        'dt' => 'Resignation_Type_ID',
        'formatter' => function ($d, $row) {
            return get_the_resignation_type($d, 'Resignation_Type');
        }
    ),
    array('db' => 'Reason', 'dt' => 'Reason'),
    array(
        'db' => 'Active',
        'dt' => 'Active',
        'formatter' => function ($d, $row) {
            return $d == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
        }
    ),
    array(
        'db' => 'Reason_ID',
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
