<?php
function is_loggedin()
{
	global $user;
	return $user->isLogged();
}

function user($field) 
{
	global $user;
	return $user->getUserDetail($field);
}

function user_id() 
{
	global $user;
	return $user->getId();
}

function employee_id() 
{
	global $user;
	return $user->getEmpID();
}

function employee_initial() 
{
	global $user;
	return $user->getEmpInitial();
}

function user_group_id() 
{
	global $user;
	return $user->getRoleId();
}

function user_group() 
{
	global $user;
	return $user->getRole();
}

function get_the_user($UserID, $field = null) 
{
	$model = registry()->get('loader')->model('user');
	$row = $model->getLocalUser($UserID);
	if($field){
		return $row->$field;
	}else{
		return $row;
	}
}

function get_the_user_by_initial($EmpInitial, $field = null) 
{
	$user_model = registry()->get('loader')->model('user');
	$row = $user_model->getUserByInitial($EmpInitial);
	if($field){
		return $row->$field;
	}else{
		return $row;
	}
}

function get_users($hostID) 
{
	$user_model = registry()->get('loader')->model('user');
	$stmts = $user_model->getUsers($hostID);
	return $stmts;
}

function get_local_users() 
{
	$model = registry()->get('loader')->model('user');
	$results = $model->getLocalUsers();
	return $results;
}

function get_user_by_role_id($RoleID){

    $user_model = registry()->get('loader')->model('user');
	$users = $user_model->getUsersByRoleID($RoleID);
	return $users;

}

function count_user_by_permission($PermissionID){

    $user_model = registry()->get('loader')->model('user');
	$users = $user_model->getUsersPermissionID($PermissionID);
	return count($users);

}

function get_userpermissions($UserID){
	$user_model = registry()->get('loader')->model('user');
	$permissions = $user_model->getUserPermission($UserID);
	return $permissions;
}

function has_permission($type, $param)
{
	global $user;
	return $user->hasPermission($type, $param);
}