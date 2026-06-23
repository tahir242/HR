<?php
ob_start();
include("../_init.php");

if (!is_loggedin()) {
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => "Error Login"));
    exit();
}

$model = registry()->get('loader')->model('employee_category');

function validate_request_data($request)
{
    if (!validateString($request->post['Employee_Category'])) {
        throw new Exception("Please Write Employee Category Name");
    }
}

function validate_existance($request, $id = 0)
{
    $statement = "SELECT * FROM [Employee_Category] WHERE [Employee_Category] = ? AND [Category_ID] != ?";
    $params = array($request->post['Employee_Category'], $id);
    $stmt = sqlsrv_query(db()->conn, $statement, $params, array("Scrollable" => SQLSRV_CURSOR_KEYSET));
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $total_try = sqlsrv_num_rows($stmt);
    if ($total_try > 0) {
        throw new Exception("Employee Category Name Already Exists");
    }
}

// Create
if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'CREATE') {
    try {
        if (user_role_id() != 1 && !has_permission(1, 'create_employee_category')) {
            throw new Exception("Error Create Permission");
        }

        validate_request_data($request);
        validate_existance($request);

        $id = $model->addEmployeeCategory($request->post);

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
        if (user_role_id() != 1 && !has_permission(1, 'modify_employee_category')) {
            throw new Exception("Error Update Permission");
        }

        if (!validateInteger($request->post['Category_ID'])) {
            throw new Exception("Error in employee_category ID");
        }

        $id = $request->post['Category_ID'];

        validate_request_data($request);
        validate_existance($request, $id);

        $id = $model->editEmployeeCategory($id, $request->post);

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
        if (user_role_id() != 1 && !has_permission(1, 'delete_employee_category')) {
            throw new Exception("Error Delete Permission");
        }

        if (!validateInteger($request->post['Category_ID'])) {
            throw new Exception("Error in category ID");
        }

        $id = $request->post['Category_ID'];
        $shift_id = isset($request->post['Shift_Category_ID']) && !empty($request->post['Shift_Category_ID']) ? $request->post['Shift_Category_ID'] : null;
        $model->deleteEmployeeCategory($id, $shift_id);

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
    include 'template/employee_category/employee_category_create_form.php';
    exit();
}

// edit form
if (isset($request->get['Category_ID']) and isset($request->get['action_type']) && $request->get['action_type'] == 'EDIT') {
    $row = $model->getEmployeeCategory($request->get['Category_ID']);
    include 'template/employee_category/employee_category_edit_form.php';
    exit();
}

// delete form
if (isset($request->get['action_type']) && $request->get['action_type'] == 'DELETE_FORM') {
    $Category_ID = $request->get['Category_ID'];
    $Employee_Category = $model->getEmployeeCategory($Category_ID);
    $Employee_Categorys = $model->getEmployeeCategories(); 
    include 'template/employee_category/employee_category_delete_form.php';
    exit();
}

/**
 * DATATABLE
 */
if (user_role_id() != 1 && !has_permission(1, 'read_employee_category')) {
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => "Error Read Permission"));
    exit();
}

require DIR_LIBRARY . "mssql.ssp.class.php";
$table = 'Employee_Category';
$primaryKey = 'Category_ID';

$columns = array(
    array(
        'db' => 'Category_ID',
        'dt' => 'DT_RowId',
        'formatter' => function ($d, $row) {
            return 'row_' . $d;
        }
    ),
    array('db' => 'Category_ID', 'dt' => 'Category_ID'),
    array('db' => 'Employee_Category', 'dt' => 'Employee_Category'),
    array(
        'db' => 'Active',
        'dt' => 'Active',
        'formatter' => function ($d, $row) {
            return $d == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
        }
    ),
    array(
        'db' => 'Category_ID',
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


