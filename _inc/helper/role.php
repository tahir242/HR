<?php 

function get_rolepermissions($Role_ID){
	$model = registry()->get('loader')->model('role');
	$permissions = $model->getRolePermission($Role_ID);
	return $permissions;
}

function get_roles($data = array()){

    $role_model = registry()->get('loader')->model('role');
	$roles = $role_model->getRoles($data);
	return $roles;

}

function get_the_role($RoleID, $field = null) 
{
	$role_model = registry()->get('loader')->model('role');
	$row = $role_model->getRole($RoleID);
	if($field){
		return $row->$field;
	}else{
		return $row;
	}
}

function get_usergroup_user_count($Role_ID){
    $role_model = registry()->get('loader')->model('role');
	$role = $role_model->CountUserInRole($Role_ID);
	return $role->total;
}

?>