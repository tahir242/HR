<?php

function count_permission_by_submodule_id($Sub_Module_ID){
	$model = registry()->get('loader')->model('permission');
	$permissions = $model->coundPermissionBySubModuleID($Sub_Module_ID);
	return $permissions->total;
}

function get_permissions($data = array()){

	$model = registry()->get('loader')->model('permission');
	$permissions = $model->getPermissions($data);
	return $permissions;

}