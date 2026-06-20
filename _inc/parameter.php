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

// Check, if user has reading permission or not
// If user have not reading permission return an alert message
if (user_role_id() != 1 && !has_permission('access', 'read_parameter')) {
  header('HTTP/1.1 422 Unprocessable Entity');
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode(array('errorMsg' => "Error Read Permission"));
  exit();
}

function validate_request_data($request)
{
  if (!validateString($request->post['Parameter'])) {
    throw new Exception("Error Parameter");
  }
  if (!validateString($request->post['Value'])) {
    throw new Exception("Error Value");
  }
}

// Validate, if exist or not
function validate_existance($Parameter, $id = 0)
{

  // Check Module ID Duplicate
  $statement = "SELECT * FROM [HR].[dbo].[Parameter] WHERE Parameter = ? AND ID != ?";
  $params = array($Parameter, $id);
  $stmt = sqlsrv_query(db()->conn, $statement, $params, array("Scrollable" => SQLSRV_CURSOR_KEYSET));
  if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
  }
  $total_try = sqlsrv_num_rows($stmt);
  if ($total_try > 0) {
    throw new Exception("Error Parameter Already Exists");
  }

}

// Create Module
if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'CREATE') {
  try {

    // Check create permission
    if (user_role_id() != 1 && !has_permission('access', 'create_parameter')) {
      throw new Exception("Error Read Permission");
    }

    // Validate post data
    validate_request_data($request);
    $Parameter = strtolower(str_replace(" ", "_", $request->post['Parameter']));
    // Validate existance
    validate_existance($Parameter);

    $field = array("Parameter", "Value", "Active", "Created_By");
    $params = array($Parameter, $request->post['Value'], $request->post['Active'], user_id());
    $db->insert("[HR].[dbo].[Parameter]", $field, $params);

    $query = "SELECT TOP 1 ID FROM [HR].[dbo].[Parameter] ORDER BY ID DESC";
    $param = array();
    $row = $db->get_row($query, $param);

    $insertQuery = "INSERT INTO Parameter (ID, Parameter, [Value], Active) VALUES (?, ?, ?, ?)";
    $insertStmt = dblite()->prepare($insertQuery);
    $insertStmt->execute(array($row->ID, $Parameter, $request->post['Value'], $request->post['Active']));

    header('Content-Type: application/json');
    echo json_encode(array("valid" => true, 'msg' => "Adding Success", 'id' => $row->ID));
    exit();

  } catch (Exception $e) {

    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => $e->getMessage()));
    exit();
  }
}

if ($request->server['REQUEST_METHOD'] == 'POST' and isset($request->get['action_type']) and $request->get['action_type'] == 'UPDATEPARAMETER') {
  try {

    // Check modify permission
    if (user_role_id() != 1 && !has_permission('access', 'modify_parameter')) {
      throw new Exception("Error Update Permission");
    }

    $id = $request->post['id'];
    $param_value = $request->post['param_value'];

    $what = array("Value", "Modified_By", "Modified_DtTm");
    $where = array("ID");
    $params = array($param_value, user_id(), date_time(), $id);
    $db->update("[HR].[dbo].[Parameter]", $what, $where, $params);

    if ($db->rows_effected) {

      $updateQuery = "UPDATE Parameter SET [Value] = ? WHERE ID = ?";
      $updateStmt = dblite()->prepare($updateQuery);
      $updateStmt->execute(array($param_value, $id));

    } else {
      throw new Exception("Error Parameter Updating Failed..");
    }

    header('Content-Type: application/json');
    echo json_encode(array('valid' => true, 'msg' => "Update Success..", 'id' => $id));
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
  include 'template/partial/add_parameter.php';
  exit();
}


/**
 *===================
 * START DATATABLE
 *===================
 */

require DIR_LIBRARY . "sqllitessp.class.php";
// DB table to use
$table = 'Parameter';
// Table's primary key
$primaryKey = 'ID';

$columns = array(
  array(
    'db' => 'ID',
    'dt' => 'DT_RowId',
    'formatter' => function ($d, $row) {
      return 'row_' . $row['ID'];
    }
  ),
  array('db' => 'ID', 'dt' => 'ID'),
  array(
    'db' => 'Parameter',
    'dt' => 'Parameter',
    'formatter' => function ($d, $row) {
      return strtoupper(str_replace("_", " ", $row['Parameter']));
    }
  ),
  array(
    'db' => 'Value',
    'dt' => 'Value',
    'formatter' => function ($d, $row) {
      return '<input id="value' . $row['ID'] . '" class="form-control" type="text" style="width:100%;max-width:100%;padding:2px 4px;" name="paramValue" value="' . trim($row['Value']) . '">';
    }
  ),
  array(
    'db' => 'ID',
    'dt' => 'btn_update',
    'formatter' => function ($d, $row) {
      return '<button class="btn btn-sm btn-info transbtn" data-id="' . $row['ID'] . '" type="button" data-loading-text="Updating..."><i class="fa fa-pencil-alt"></i> Update</button>';
    }
  ),
);

echo json_encode(
  SQLLITESSP::simple($request->get, $sql_details, $table, $primaryKey, $columns)
);
