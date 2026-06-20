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

// LOAD Sub Module Model 
$model = registry()->get('loader')->model('submodule');

// Validate post data
function validate_request_data($request) 
{  
  if (!validateString($request->post['Module_ID'])) {
    throw new Exception("Please Select Module");
  }
  if (!validateString($request->post['Sub_Module_ID'])) {
    throw new Exception("Error Sub Module ID");
  }
  if (!validateString($request->post['Sub_Module'])) {
    throw new Exception("Error Sub Module");
  }
}

// Validate, if exist or not
function validate_existance($request, $id = 0)
{

  // Check Sub Module ID Duplicate
  $statement = "SELECT * FROM [HR].[dbo].[Sub_Module] WHERE Sub_Module_ID = ? AND Sub_Module_URN != ?";
  $params  = array($request->post['Sub_Module_ID'], $id);
  $stmt       = sqlsrv_query( db()->conn, $statement, $params, array( "Scrollable" => SQLSRV_CURSOR_KEYSET ));
  if( $stmt === false ) {
      die( print_r( sqlsrv_errors(), true));
  }
  $total_try  = sqlsrv_num_rows($stmt);
  if ($total_try > 0) {
    throw new Exception("Error Sub Module ID Already Exists");
  }

  // Check Module Duplicate
  $statement = "SELECT * FROM [HR].[dbo].[Module] WHERE Module_ID = ?";
  $params  = array($request->post['Sub_Module_ID']);
  $stmt       = sqlsrv_query( db()->conn, $statement, $params, array( "Scrollable" => SQLSRV_CURSOR_KEYSET ));
  if( $stmt === false ) {
      die( print_r( sqlsrv_errors(), true));
  }
  $total_try  = sqlsrv_num_rows($stmt);
  if ($total_try > 0) {
    throw new Exception("Error Module Already Exists");
  }

}

// Create Sub Module
if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'CREATE')
{
  try {

    // Check create permission
    // if (user_role_id() != 1 && !has_permission('access', 'create_usergroup')) {
    //   throw new Exception("Error Read Permission");
    // }

    // Validate post data
    validate_request_data($request);
    // Validate existance
    validate_existance($request);

    // Sub Module
    $Sub_Module_URN = $model->addSubModule($request->post);
    
    header('Content-Type: application/json');
    echo json_encode(array("valid" => true, 'msg' => "Adding Success", 'id' => $Sub_Module_URN));
    exit();

  } catch (Exception $e) { 
    
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => $e->getMessage()));
    exit();
  }
} 

// Update Sub Module
if($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'UPDATE')
{
  try {

    // Check update permission
    // if (user_role_id() != 1 && !has_permission('access', 'update_usergroup')) {
    //   throw new Exception("Error Update Permission");
    // }

    // Validate Module ID
    if (empty($request->post['Sub_Module_URN'])) {
      throw new Exception("Error in Sub Module");
    }

    $Sub_Module_URN = $request->post['Sub_Module_URN'];

    // Validate post data
    validate_request_data($request);
    // Validate existance
    validate_existance($request, $Sub_Module_URN);

    $Sub_Module_URN = $model->editSubModule($Sub_Module_URN, $request->post);    
    header('Content-Type: application/json');
    echo json_encode(array('valid' => true, 'msg' => 'Update Success', 'id' => $Sub_Module_URN));
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
  include 'template/module/submodule_create_form.php';
  exit();
}

// Role edit form
if (isset($request->get['Sub_Module_URN']) AND isset($request->get['action_type']) && $request->get['action_type'] == 'EDIT') 
{
  $submodule = $model->getSubModule($request->get['Sub_Module_URN']);
  include 'template/module/submodule_form.php';
  exit();
}

/**
 *===================
 * START DATATABLE
 *===================
*/

require DIR_LIBRARY . "sqllitessp.class.php";
// DB table to use
$table = 'Sub_Module'; 
// Table's primary key
$primaryKey = 'Sub_Module_URN';
 
$columns = array(
  array(
      'db' => 'Sub_Module_URN',
      'dt' => 'DT_RowId',
      'formatter' => function( $d, $row ) {
          return 'row_'.$d;
      }
  ),
  array( 'db' => 'Sub_Module_URN',
         'dt' => 'Sub_Module_URN',
         'formatter' => function( $d, $row ) {
          return $row['Sub_Module_URN'];
      }      
  ),
  array( 'db' => 'Module_ID',
          'dt' => 'Module_ID',
          'formatter' => function( $d, $row ) {
          return get_the_module($row['Module_ID'], "Module");
        }      
),
  array( 'db' => 'Sub_Module_ID',
         'dt' => 'Sub_Module_ID',
         'formatter' => function( $d, $row ) {
            return $row['Sub_Module_ID'];
          }      
  ),
  array( 
    'db' => 'Sub_Module',   
    'dt' => 'Sub_Module' ,
    'formatter' => function($d, $row) {
        return $row['Sub_Module'];
    }
  ),
  array( 
    'db' => 'Sub_Module_ID',   
    'dt' => 'Permission' ,
    'formatter' => function($d, $row) {
        return count_permission_by_submodule_id($row['Sub_Module_ID']);
    }
  ),
  array( 
    'db' => 'Show_In_Menu',   
    'dt' => 'Show_In_Menu' ,
    'formatter' => function($d, $row) {
      if($row['Show_In_Menu'] == 1){
        return '<h6 class="text-success">Yes</h6>';
      }else{
        return '<h6 class="text-danger">No</h6>';
      };
    }
  ),
  array('db' => 'Sort','dt' => 'Sort' ),
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
      'db' => 'Sub_Module_URN',
      'dt' => 'btn_edit',
      'formatter' => function( $d, $row ) {
        return '<button class="btn btn-sm btn-info edit-sub-module" type="button" title="Edit"><i class="fas fa-pencil-alt"></i></button>';
      }
  )
);
 
echo json_encode(
  SQLLITESSP::simple($request->get, $sql_details, $table, $primaryKey, $columns)
);
