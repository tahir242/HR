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

// Check, if user has reading permission or not
// If user have not reading permission return an alert message
// if (user_group_id() != 1 && !has_permission('access', 'read_role')) {
//   header('HTTP/1.1 422 Unprocessable Entity');
//   header('Content-Type: application/json; charset=UTF-8');
//   echo json_encode(array('errorMsg' => "Error Read Permission"));
//   exit();
// }

// LOAD Permission MODEL 
$model = registry()->get('loader')->model('permission');

// Validate post data
function validate_request_data($request) 
{

  if (!validateString($request->post['Sub_Module_ID'])) {
    throw new Exception("Error Module");
  }

  if (!validateString($request->post['Permission_ID'])) {
    throw new Exception("Error Permission ID");
  }
  
  if (!validateString($request->post['Permission'])) {
    throw new Exception("Error Permission");
  }

}

// Validate, if exist or not
function validate_existance($request, $id = 0)
{

  // Check Sub Permission Duplicate
  $statement = "SELECT * FROM [HR].[dbo].[Permission] WHERE Permission_ID = ? AND Permission_URN != ?";
  $params  = array($request->post['Permission_ID'], $id);
  $stmt       = sqlsrv_query( db()->conn, $statement, $params, array( "Scrollable" => SQLSRV_CURSOR_KEYSET ));
  if( $stmt === false ) {
      die( print_r( sqlsrv_errors(), true));
  }
  $total_try  = sqlsrv_num_rows($stmt);
  if ($total_try > 0) {
    throw new Exception("Error Permission ID Already Exists");
  }

}

// Create Permission
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

    // Fetch Permission
    $Permission_URN = $model->addPermission($request->post);
    
    header('Content-Type: application/json');
    echo json_encode(array("valid" => true, 'msg' => "Adding Success", 'id' => $Permission_URN));
    exit();

  } catch (Exception $e) { 
    
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => $e->getMessage()));
    exit();
  }
} 

// Update Permission
if($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'UPDATE')
{
  try {

    // Check update permission
    // if (user_group_id() != 1 && !has_permission('access', 'update_usergroup')) {
    //   throw new Exception("Error Update Permission");
    // }

    // Validate Permission ID
    if (empty($request->post['Permission_ID'])) {
      throw new Exception("Error in Permission ID");
    }

    $Permission_URN = $request->post['Permission_URN'];

    // Validate post data
    validate_request_data($request);

    // Validate existance
    validate_existance($request, $Permission_URN);

    $Permission_URN = $model->editPermission($Permission_URN, $request->post);
    
    header('Content-Type: application/json');
    echo json_encode(array('valid' => true, 'msg' => 'Update Success', 'id' => $Permission_URN));
    exit();

  } catch (Exception $e) { 
    $error_message = $e->getMessage();
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => $error_message));
   exit();
  }
} 

// Permission create form
if (isset($request->get['action_type']) && $request->get['action_type'] == 'CREATE') 
{
  include 'template/module/permission_create_form.php';
  exit();
}

// Permission edit form
if (isset($request->get['Permission_URN']) AND isset($request->get['action_type']) && $request->get['action_type'] == 'EDIT') 
{
  $permission = $model->getPermission($request->get['Permission_URN']);
  include 'template/module/permission_form.php';
  exit();
}

/**
 *===================
 * START DATATABLE
 *===================
*/
require DIR_LIBRARY . "sqllitessp.class.php";
// DB table to use
$table = 'Permission';
 
// Table's primary key
$primaryKey = 'Permission_URN';
 
$columns = array(
  array(
      'db' => 'Permission_URN',
      'dt' => 'DT_RowId',
      'formatter' => function( $d, $row ) {
          return 'row_'.$d;
      }
  ),
  array( 'db' => 'Sub_Module_ID',
  'dt' => 'Sub_Module_ID',
  'formatter' => function( $d, $row ) {
   $submodule = get_the_submodule($row['Sub_Module_ID'], "Sub_Module");
   if($submodule){
    return $submodule;
   }else{
    return get_the_module($row['Sub_Module_ID'], "Module");
   }
  }      
  ),
  array( 'db' => 'Permission_URN',
         'dt' => 'Permission_URN',
         'formatter' => function( $d, $row ) {
          return $row['Permission_URN'];
      }      
  ),
  array( 'db' => 'Permission_ID',
         'dt' => 'Permission_ID',
         'formatter' => function( $d, $row ) {
            return $row['Permission_ID'];
          }      
  ),
  array( 
    'db' => 'Permission',   
    'dt' => 'Permission' ,
    'formatter' => function($d, $row) {
        return $row['Permission'];
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
    'db' => 'Permission_ID',   
    'dt' => 'total_user' ,
    'formatter' => function($d, $row) {
        return count_user_by_permission($row['Permission_ID']);
    }
  ),
  array(
      'db' => 'Permission_URN',
      'dt' => 'btn_edit',
      'formatter' => function( $d, $row ) {
        return '<button class="btn btn-sm btn-info edit-permission" type="button" title="Edit"><i class="fas fa-pencil-alt"></i></button>';
      }
  )
);
 
echo json_encode(
    SQLLITESSP::simple($request->get, $sql_details, $table, $primaryKey, $columns)
);

/**
 *===================
 * END DATATABLE
 *===================
*/
