<?php
ob_start();
include("../_init.php");

// Check, if user logged in or not
// If user is not logged in then return an alert message
if (!is_loggedin()) {
  header('HTTP/1.1 422 Unprocessable Entity');
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode(array('errorMsg' => ('Error Login')));
  exit();
}

// Load User Modal
$model = registry()->get('loader')->model('user');
$role_model = registry()->get('loader')->model('role');

// Assing role to user
if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'CHANGEUSERROLE') {
  try {

    // Validate user id
    if (!validateInteger($request->post['User_ID'])) {
      throw new Exception("Error User");
    }

    if (!validateInteger($request->post['Role_ID'])) {
      throw new Exception("Error Role");
    }

    $id = $request->post['User_ID'];

    if ($id == 3) {
      throw new Exception("Error Admin Account Can't be change..");
    }

    $User_ID = $request->post['User_ID'];
    $userrole = $model->getUserRoleByUserID($User_ID);

    if ($userrole) {

      $userroleid = $model->editassignUserRole($request->post);
      $userrole = $model->getUserRole($userroleid);
      $rolePermissions = $role_model->getRolePermission($userrole->Role_ID);
      $userPermissions = $model->getUserPermission($userrole->User_ID);

      if ($rolePermissions) {

        $the_permission = array();
        foreach ($userPermissions as $permission1) {
          $the_permission[$permission1->Permission_ID] = $permission1->Active;
        }

        foreach ($rolePermissions as $rolePermission) {

          if (isset($the_permission[$rolePermission->Permission_ID])) {
            //Update
            $what = array("Active", "Modified_By", "Modified_DtTm");
            $where = array("User_ID", "Permission_ID");
            $params = array($rolePermission->Active, user_id(), date_time(), $userrole->User_ID, $rolePermission->Permission_ID);
            db()->update("[HR].[dbo].[User_Permission]", $what, $where, $params);

            if (db()->rows_effected) {

              $updateQuery = "UPDATE User_Permission SET Active = ? WHERE User_ID = ? AND Permission_ID = ?";
              $updateStmt = dblite()->prepare($updateQuery);
              $updateStmt->execute(array($rolePermission->Active, $userrole->User_ID, $rolePermission->Permission_ID));

            } else {
              throw new Exception("Error Permission Updating Failed..");
            }

          } else {
            //Insert
            $field = array("User_ID", "Permission_ID", "Active", "Created_By", "Created_DtTm");
            $params = array($userrole->User_ID, $rolePermission->Permission_ID, $rolePermission->Active, user_id(), date_time());
            db()->insert("[HR].[dbo].[User_Permission]", $field, $params);

            $insertQuery = "INSERT INTO User_Permission (User_ID, Permission_ID, Active) VALUES (?, ?, ?)";
            $insertStmt = dblite()->prepare($insertQuery);
            $insertStmt->execute(array($userrole->User_ID, $rolePermission->Permission_ID, $rolePermission->Active));

          }
        }

      }

    } else {

      $userroleid = $model->assignUserRole($request->post);
      $userrole = $model->getUserRole($userroleid);
      $rolePermissions = $role_model->getRolePermission($userrole->Role_ID);
      if ($rolePermissions) {

        foreach ($rolePermissions as $permission) {
          $field = array("User_ID", "Permission_ID", "Active", "Created_By", "Created_DtTm");
          $params = array($userrole->User_ID, $permission->Permission_ID, $permission->Active, user_id(), date_time());
          db()->insert("[HR].[dbo].[User_Permission]", $field, $params);

          $insertQuery = "INSERT INTO User_Permission (User_ID, Permission_ID, Active) VALUES (?, ?, ?)";
          $insertStmt = dblite()->prepare($insertQuery);
          $insertStmt->execute([$userrole->User_ID, $permission->Permission_ID, $permission->Active]);

        }

      }
    }

    header('Content-Type: application/json');
    echo json_encode(array('valid' => true, 'msg' => "Assigned Success.."));
    exit();

  } catch (Exception $e) {

    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => $e->getMessage()));
    exit();
  }
}

// Change User Permission
if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'UPDATEUSERPERMISSION') {
  try {

    // Check update permission
    // if (user_role_id() != 1 && !has_permission('access', 'update_usergroup')) {
    //   throw new Exception("Error Update Permission");
    // }

    // Validate Role URN
    if (empty($request->post['User_ID'])) {
      throw new Exception("Error Invalid User");
    }

    $User_ID = $request->post['User_ID'];

    $permission = array();
    if (isset($request->post['access']) && $request->post['access']) {
      $permission['access'] = $request->post['access'];
    }else{
      throw new Exception("No Permission Selected");
    }
    if (isset($request->post['modify']) && $request->post['modify']) {
      $permission['modify'] = $request->post['modify'];
    }

    $userPermissions = $model->getUserPermission($User_ID);
    if ($userPermissions) {

      $the_permission = array();
      foreach ($userPermissions as $permission1) {
        $the_permission[$permission1->Permission_ID] = $permission1->Active;
        $what = array("Active", "Modified_By", "Modified_DtTm");
        $where = array("User_ID", "Permission_ID");
        $params = array(0, user_id(), date_time(), $User_ID, $permission1->Permission_ID);
        db()->update("[HR].[dbo].[User_Permission]", $what, $where, $params);

        $updateQuery = "UPDATE User_Permission SET Active = ? WHERE User_ID = ? AND Permission_ID = ?";
        $updateStmt = dblite()->prepare($updateQuery);
        $updateStmt->execute(array(0, $User_ID, $permission1->Permission_ID));

      }

      foreach ($permission['access'] as $key => $value) {

        if (isset($the_permission[$key])) {

          //Update
          $what = array("Active", "Modified_By", "Modified_DtTm");
          $where = array("User_ID", "Permission_ID");
          $params = array($value ? 1 : 0, user_id(), date_time(), $User_ID, $key);
          db()->update("[HR].[dbo].[User_Permission]", $what, $where, $params);

          if (db()->rows_effected) {

            $updateQuery = "UPDATE User_Permission SET Active = ? WHERE User_ID = ? AND Permission_ID = ?";
            $updateStmt = dblite()->prepare($updateQuery);
            $updateStmt->execute(array($value ? 1 : 0, $User_ID, $key));

          } else {
            throw new Exception("Error Permission Updating Failed..");
          }

        } else {
          //Insert
          $field = array("User_ID", "Permission_ID", "Active", "Created_By", "Created_DtTm");
          $params = array($User_ID, $key, $value ? 1 : 0, user_id(), date_time());
          db()->insert("[HR].[dbo].[User_Permission]", $field, $params);

          $insertQuery = "INSERT INTO User_Permission (User_ID, Permission_ID, Active) VALUES (?, ?, ?)";
          $insertStmt = dblite()->prepare($insertQuery);
          $insertStmt->execute(array($User_ID, $key, $value));

        }
      }
    }

    header('Content-Type: application/json');
    echo json_encode(array('valid' => true, 'msg' => 'Update Success', 'id' => $User_ID));
    exit();

  } catch (Exception $e) {

    $error_message = $e->getMessage();
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => $error_message));
    exit();
  }
}

// GET USER PERMISSION USER
if (isset($request->post['User_ID']) and isset($request->get['action_type']) && $request->get['action_type'] == 'CHANGEUSERPERMISSION') {
  $User_ID = $request->post['User_ID'];
  include 'template/user/user_permission_form.php';
  exit();
}

// Change role
if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->get['action_type']) && $request->get['action_type'] == 'CHANGEROLEFORM') {
  $User_ID = $request->post['User_ID'];
  $userrole = $model->getUserRoleByUserID($User_ID);
  include 'template/user/role_form.php';
  exit();
}

// Change Password
if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->get['action_type']) && $request->get['action_type'] == 'CHANGEPASSWORDFORM') {
  $User_ID = $request->post['User_ID'];
  include 'template/user/change_password.php';
  exit();
}

// User Acount Information
if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->get['action_type']) && $request->get['action_type'] == 'USERACCOUNTINFORMATION') {
  $User_ID = $request->post['User_ID'];

  $url = SSOURL . "/host_user.php";
  $data = ["UserID" => $User_ID, "auth" => APPID];

  $response = json_decode(make_request($url, $data));

  include 'template/user/user_account_information.php';
  exit();
}
