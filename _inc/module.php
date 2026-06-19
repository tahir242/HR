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

// LOAD Module MODEL 
$module_model = registry()->get('loader')->model('module');

// Validate post data
function validate_request_data($request) 
{  
  if (!validateString($request->post['Module_ID'])) {
    throw new Exception("Error Module ID");
  }
  if (!validateString($request->post['Module'])) {
    throw new Exception("Error Module");
  }
}

// Validate, if exist or not
function validate_existance($request, $id = 0)
{

  // Check Module ID Duplicate
  $statement = "SELECT * FROM [HR].[dbo].[Module] WHERE Module_ID = ? AND Module_URN != ?";
  $params  = array($request->post['Module_ID'], $id);
  $stmt       = sqlsrv_query( db()->conn, $statement, $params, array( "Scrollable" => SQLSRV_CURSOR_KEYSET ));
  if( $stmt === false ) {
      die( print_r( sqlsrv_errors(), true));
  }
  $total_try  = sqlsrv_num_rows($stmt);
  if ($total_try > 0) {
    throw new Exception("Error Module ID Already Exists");
  }

  // Check Sub Module Duplicate
  $statement = "SELECT * FROM [HR].[dbo].[Sub_Module] WHERE Sub_Module_ID = ?";
  $params  = array($request->post['Module_ID'], $id);
  $stmt       = sqlsrv_query( db()->conn, $statement, $params, array( "Scrollable" => SQLSRV_CURSOR_KEYSET ));
  if( $stmt === false ) {
      die( print_r( sqlsrv_errors(), true));
  }
  $total_try  = sqlsrv_num_rows($stmt);
  if ($total_try > 0) {
    throw new Exception("Error Sub Module Already Exists");
  }

}

// Create Module
if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'CREATE')
{
  try {

    // Check create permission
    // if (user_group_id() != 1 && !has_permission('access', 'create_usergroup')) {
    //   throw new Exception("Error Read Permission");
    // }

    // Validate post data
    validate_request_data($request);
    // Validate existance
    validate_existance($request);

    // Fetch Module
    $Module_URN = $module_model->addModule($request->post);
    
    header('Content-Type: application/json');
    echo json_encode(array("valid" => true, 'msg' => "Adding Success", 'id' => $Module_URN));
    exit();

  } catch (Exception $e) { 
    
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => $e->getMessage()));
    exit();
  }
} 

// Update Module
if($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'UPDATE')
{
  try {

    // Check update permission
    // if (user_group_id() != 1 && !has_permission('access', 'update_usergroup')) {
    //   throw new Exception("Error Update Permission");
    // }

    // Validate Module ID
    if (empty($request->post['Module_URN'])) {
      throw new Exception("Error in Module ID");
    }

    $Module_URN = $request->post['Module_URN'];

    // Validate post data
    validate_request_data($request);
    // Validate existance
    validate_existance($request, $Module_URN);

    $Module_URN = $module_model->editModule($Module_URN, $request->post);    
    header('Content-Type: application/json');
    echo json_encode(array('valid' => true, 'msg' => 'Update Success', 'id' => $Module_URN));
    exit();

  } catch (Exception $e) { 

    $error_message = $e->getMessage();
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => $error_message));
   exit();
  }
} 

// Module create form
if (isset($request->get['action_type']) && $request->get['action_type'] == 'CREATE') 
{
  include 'template/module/module_create_form.php';
  exit();
}

// Role edit form
if (isset($request->get['Module_URN']) AND isset($request->get['action_type']) && $request->get['action_type'] == 'EDIT') 
{
  $module = $module_model->getModule($request->get['Module_URN']);
  include 'template/module/module_form.php';
  exit();
}

/**
 *===================
 * START DATATABLE
 *===================
*/

require DIR_LIBRARY . "sqllitessp.class.php";
// DB table to use
$table = 'Module'; 
// Table's primary key
$primaryKey = 'Module_URN';
 
$columns = array(
  array(
      'db' => 'Module_URN',
      'dt' => 'DT_RowId',
      'formatter' => function( $d, $row ) {
          return 'row_'.$d;
      }
  ),
  array( 'db' => 'Module_URN',
         'dt' => 'Module_URN',
         'formatter' => function( $d, $row ) {
          return $row['Module_URN'];
      }      
  ),
  array( 'db' => 'Module_ID',
         'dt' => 'Module_ID',
         'formatter' => function( $d, $row ) {
            return $row['Module_ID'];
          }      
  ),
  array( 
    'db' => 'Module',   
    'dt' => 'Module' ,
    'formatter' => function($d, $row) {
        return $row['Module'];
    }
  ),
  array( 
    'db' => 'Module_URN',   
    'dt' => 'total_user' ,
    'formatter' => function($d, $row) {
        return count_submodules_by_module_id($row['Module_ID']);
    }
  ),
  array( 
    'db' => 'Has_Sub_Menu',   
    'dt' => 'Has_Sub_Menu' ,
    'formatter' => function($d, $row) {
      if($row['Has_Sub_Menu'] == 1){
        return '<h6 class="text-success">Yes</h6>';
      }else{
        return '<h6 class="text-danger">No</h6>';
      };
    }
  ),
  array( 
    'db' => 'Active',   
    'dt' => 'Active' ,
    'formatter' => function($d, $row) {
      if($row['Active'] == 1){
        return '<h6 class="text-success">Yes</h6>';
      }else{
        return '<h6 class="text-danger">No</h6>';
      };
    }
  ),
  array(
      'db' => 'Module_URN',
      'dt' => 'btn_edit',
      'formatter' => function( $d, $row ) {
        return '<button class="btn btn-sm btn-info edit-module" type="button" title="Edit"><i class="fas fa-pencil-alt"></i></button>';
      }
  )
);
 
echo json_encode(
  SQLLITESSP::simple($request->get, $sql_details, $table, $primaryKey, $columns)
);
