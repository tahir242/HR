<?php 
ob_start();
include ("../_init.php");

// Check, if user logged in or not
// If user is not logged in then return an alert message
if (!is_loggedin()) {
  header('HTTP/1.1 422 Unprocessable Entity');
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode(array('errorMsg' => "Error Login"));
  exit();
}

// LOAD MODEL 
$model = registry()->get('loader')->model('indexing');

// Validate post data
function validate_request_data($request) 
{  
  if (!validateString($request->post['Employee_ID'])) {
    throw new Exception("Error: Employee ID");
  }
  if (!validateString($request->post['Employee_Name'])) {
    throw new Exception("Error Name");
  }
}

function validate_existance($request, $id = 0)
{

  // Check Module ID Duplicate
  $statement = "SELECT * FROM [HR].[dbo].[Employee_PDF] WHERE Employee_ID = ? AND Employee_ID IS NOT NULL";
  $params  = array($request->post['Employee_ID'], $id);
  $stmt       = sqlsrv_query( db()->conn, $statement, $params, array( "Scrollable" => SQLSRV_CURSOR_KEYSET ));
  
  if( $stmt === false ) {
      die( print_r( sqlsrv_errors(), true));
  }

  $total_try  = sqlsrv_num_rows($stmt);
  if ($total_try > 0) {
    throw new Exception("Error: Employee Already Exist.");
  }

}

// Create Module
if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'CREATE')
{
  try {

    // Validate post data
    validate_request_data($request);
    // Validate existance
    //validate_existance($request);


    if (isset($request->post['DOJ']) && $request->post['DOJ'] !== '') {
        $request->post['DOJ'] = date_normalizer($request->post['DOJ'], "Y-m-d");
    } else {
        $request->post['DOJ'] = null;
    }

    if (isset($request->post['Department']) && $request->post['Department'] == '') {
        $request->post['Department'] = null;
    }

    if (isset($request->post['Designation']) && $request->post['Designation'] == '') {
        $request->post['Designation'] = null;
    }
    // Fetch Module
    $id = $model->addIndex($request->post);
    insert_system_time_log(2, "enter demoghrapichs");
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

// GET MISSING ID
if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'GETMISSINGID')
{
  try {
    $mid = "";
    $query = "SELECT TOP 1 Employee_ID FROM [HR].[dbo].[Employee_PDF] WHERE Employee_ID LIKE '%MEID%' ORDER BY CAST(SUBSTRING(Employee_ID, 6, LEN(Employee_ID) - 5) AS INT) DESC;";
    $row = $db->get_row($query, []);

    if($row){
        $last_id = explode("-", $row->Employee_ID);
        $inc = str_pad((int) end($last_id) + 1, 3, '0', STR_PAD_LEFT);
        $mid = "MEID-" . $inc;
    }else{
        $mid = "MEID-01";
    }
    insert_system_time_log(2, "fetch missing employee id");
    header('Content-Type: application/json');
    echo json_encode(array("valid" => true, 'msg' => "Adding Success", 'ID' => $mid));
    exit();

  } catch (Exception $e) { 
    
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => $e->getMessage()));
    exit();
  }
} 
