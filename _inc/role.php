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
// if (user_role_id() != 1 && !has_permission('access', 'read_role')) {
//   header('HTTP/1.1 422 Unprocessable Entity');
//   header('Content-Type: application/json; charset=UTF-8');
//   echo json_encode(array('errorMsg' => "Error Read Permission"));
//   exit();
// }

// LOAD Role MODEL 
$model = registry()->get('loader')->model('role');

// Validate post data
function validate_request_data($request) 
{  
  if (!validateString($request->post['Role'])) {
    throw new Exception("Error Role");
  }
}

// Validate, if exist or not
function validate_existance($request, $id = 0)
{

  // Check Role Duplicate
  $statement = "SELECT * FROM [HR].[dbo].[Role] WHERE [Role] = ? AND Role_ID != ?";
  $params  = array($request->post['Role'], $id);
  $stmt       = sqlsrv_query( db()->conn, $statement, $params, array( "Scrollable" => SQLSRV_CURSOR_KEYSET ));
  if( $stmt === false ) {
      die( print_r( sqlsrv_errors(), true));
  }
  $total_try  = sqlsrv_num_rows($stmt);
  if ($total_try > 0) {
    throw new Exception("Error Role Already Exists");
  }

}

// Create Role
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

    // Fetch role
    $Role_ID = $model->addRole($request->post);
    
    header('Content-Type: application/json');
    echo json_encode(array('valid' => true, 'msg' => "Adding Success", 'id' => $Role_ID));
    exit();

  } catch (Exception $e) { 
    
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => $e->getMessage()));
    exit();
  }
} 

// Update Role
if($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'UPDATE')
{
  try {

    // Check update permission
    // if (user_role_id() != 1 && !has_permission('access', 'update_usergroup')) {
    //   throw new Exception("Error Update Permission");
    // }

    // Validate Role URN
    if (empty($request->post['Role_ID'])) {
      throw new Exception("Error in Role ID");
    }

    $Role_ID = $request->post['Role_ID'];

    // Validate post data
    validate_request_data($request);

    // Validate existance
    validate_existance($request, $Role_ID);

    $permission = array();
    if (isset($request->post['access']) && $request->post['access']) {
      $permission['access'] = $request->post['access'];
    }
    if (isset($request->post['modify']) && $request->post['modify']) {
      $permission['modify'] = $request->post['modify'];
    }
    $Role_ID = $model->editRole($Role_ID, $request->post);
    $rolepermissions = $model->getRolePermission($Role_ID);
    if($rolepermissions){
      $the_permission = array();
      foreach($rolepermissions AS $permission1){
        $the_permission[$permission1->Permission_ID] = $permission1->Active;
        $what 		= array("Active", "Modified_By", "Modified_DtTm");
        $where 		= array("Role_ID", "Permission_ID");
        $params 	= array(0, user_id(), date_time(), $Role_ID, $permission1->Permission_ID);
        db()->update("[HR].[dbo].[Role_Permission]", $what, $where, $params);

        $updateQuery = "UPDATE Role_Permission SET Active = ? WHERE Role_ID = ? AND Permission_ID = ?";
        $updateStmt = dblite()->prepare($updateQuery);
        $updateStmt->execute(array(0, $Role_ID, $permission1->Permission_ID));

      }
      
      foreach ($permission['access'] AS $key => $value){

        if(isset($the_permission[$key])){
          //Update
          $what 		= array("Active", "Modified_By", "Modified_DtTm");
          $where 		= array("Role_ID", "Permission_ID");
          $params 	= array($value ? 1 : 0, user_id(), date_time(), $Role_ID, $key);
          db()->update("[HR].[dbo].[Role_Permission]", $what, $where, $params);
      
          if(db()->rows_effected){
            $updateQuery = "UPDATE Role_Permission SET Active = ? WHERE Role_ID = ? AND Permission_ID = ?";
            $updateStmt = dblite()->prepare($updateQuery);
            $updateStmt->execute(array($value ? 1 : 0, $Role_ID, $key));
          }else{
            throw new Exception("Error Permission Updating Failed..");
          }
          
        }else{
          //Insert
          $field 		= array("Role_ID", "Permission_ID", "Active", "Created_By", "Created_DtTm");
          $params 	= array($Role_ID, $key, $value, user_id(), date_time());
          db()->insert("[HR].[dbo].[Role_Permission]", $field, $params);

          $insertQuery = "INSERT INTO Role_Permission (Role_ID, Permission_ID, Active) VALUES (?, ?, ?)";
          $insertStmt = dblite()->prepare($insertQuery);
          $insertStmt->execute(array($Role_ID, $key, $value ? 1 : 0));

          $rss = get_user_by_role_id($Role_ID);

          if($rss){

            foreach($rss AS $rs){

              $query = "SELECT * FROM [User_Permission] WHERE User_ID = ? AND Permission_ID = ?";
              $stmt = dblite()->prepare($query);
              $stmt->execute([$rs->User_ID, $key]);
              $result = $stmt->fetch(PDO::FETCH_OBJ);

              if($result){
                $what 		= array("Active", "Modified_By", "Modified_DtTm");
                $where 		= array("User_ID", "Permission_ID");
                $params 	= array($value, user_id(), date_time(), $rs->User_ID, $key);
                db()->update("[HR].[dbo].[User_Permission]", $what, $where, $params);

                $updateQuery = "UPDATE User_Permission SET Active = ? WHERE User_ID = ? AND Permission_ID = ?";
                $updateStmt = dblite()->prepare($updateQuery);
                $updateStmt->execute(array($value ? 1 : 0, $rs->User_ID, $key));

              }else{

                $field 		= array("User_ID", "Permission_ID", "Active", "Created_By", "Created_DtTm", "Modified_By", "Modified_DtTm");
                $params 	= array($rs->User_ID, $key, $value, user_id(), date_time(), user_id(), date_time());
                db()->insert("[HR].[dbo].[User_Permission]", $field, $params);

                $insertQuery = "INSERT INTO User_Permission (User_ID, Permission_ID, Active) VALUES (?, ?, ?)";
                $insertStmt = dblite()->prepare($insertQuery);
                $insertStmt->execute(array($rs->User_ID, $key, $value ? 1 : 0));

              }

            }

          }

        }
      }

    }else{

      $model->insertRolePermission($request->post, $permission);
    
    }

    // $rss = get_user_by_role_id($role->RoleID);
    // foreach($rss AS $rs){

    //   $userpermissions = get_userpermissions($rs->UserID);
    //   $the_permission = array();
    //   foreach($userpermissions AS $permission1){
    //     $the_permission[$permission1->PermissionID] = $permission1->Active;
    //     $what 		= array("Active", "ModifiedBy", "ModifiedDtTm");
    //     $where 		= array("UserID", "PermissionID");
    //     $params 	= array(0, user_id(), date_time(), $rs->UserID, $permission1->PermissionID);
    //     db()->update("[HR].[dbo].[UserPermission]", $what, $where, $params);
    //   }

    //   foreach ($permission['access'] AS $key => $value){
    //     if(isset($the_permission[$key])){
    //       $what 		= array("Active", "ModifiedBy", "ModifiedDtTm");
    //       $where 		= array("UserID", "PermissionID");
    //       $params 	= array($value, user_id(), date_time(), $rs->UserID, $key);
    //       db()->update("[HR].[dbo].[UserPermission]", $what, $where, $params);

    //     }
    //   }

    //   foreach($userpermissions AS $permission1){
    //     if(!isset($permission['access'][$permission1->PermissionID])){
    //       $what 		= array("Active", "ModifiedBy", "ModifiedDtTm");
    //       $where 		= array("UserID", "PermissionID");
    //       $params 	= array($permission1->Active, user_id(), date_time(), $rs->UserID, $permission1->PermissionID);
    //       db()->update("[HR].[dbo].[UserPermission]", $what, $where, $params);

    //     }
    //   }

    // }
    
    header('Content-Type: application/json');
    echo json_encode(array('valid' => true, 'msg' => 'Update Success', 'id' => $Role_ID));
    exit();

  } catch (Exception $e) { 

    $error_message = $e->getMessage();
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => $error_message));
   exit();
  }
} 

// Role create form
if (isset($request->get['action_type']) && $request->get['action_type'] == 'CREATE') 
{
  include 'template/role/role_create_form.php';
  exit();
}

// Role edit form
if (isset($request->get['Role_ID']) AND isset($request->get['action_type']) && $request->get['action_type'] == 'EDIT') 
{
  $role = $model->getRole($request->get['Role_ID']);
  include 'template/role/role_form.php';
  exit();
}

/**
 *===================
 * START DATATABLE
 *===================
*/
require DIR_LIBRARY . "sqllitessp.class.php";

// DB table to use
$table = '[Role]';
 
// Table's primary key
$primaryKey = 'Role_ID';
 
$columns = array(
  array(
      'db' => 'Role_ID',
      'dt' => 'DT_RowId',
      'formatter' => function( $d, $row ) {
          return 'row_'.$d;
      }
  ),
  array( 'db' => 'Role_ID',
         'dt' => 'Role_ID',
         'formatter' => function( $d, $row ) {
          return $row['Role_ID'];
      }      
  ),
  array( 
    'db' => 'Role',   
    'dt' => 'Role' ,
    'formatter' => function($d, $row) {
        return $row['Role'];
    }
  ),
  array( 
    'db' => 'Role_ID',   
    'dt' => 'total_user' ,
    'formatter' => function($d, $row) {
        return get_usergroup_user_count($row['Role_ID']);
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
      'db' => 'Role_ID',
      'dt' => 'btn_edit',
      'formatter' => function( $d, $row ) {
        if($row["Role_ID"] == 1){
          return '<button class="btn btn-sm btn-info edit-role" type="button" title="Edit" disabled><i class="fas fa-pencil-alt"></i></button>';
        }else{
          return '<button class="btn btn-sm btn-info edit-role" type="button" title="Edit"><i class="fas fa-pencil-alt"></i></button>';
        }
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
