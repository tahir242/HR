<?php 
declare(strict_types=1);

function get_submodules(array $data = array()) : ?array {

    $module_model = registry()->get('loader')->model('submodule');
	$modules = $module_model->getSubModules($data);
	return $modules;

}

function count_submodules_by_module_id(string $ModuleID) : int {
	$module_model = registry()->get('loader')->model('submodule');
	$modules = $module_model->countSubModuleByModuleID($ModuleID);
	return $modules->total;
}

function get_the_submodule(string $SubModuleID, ?string $field = null) : object|string|bool|null
{
	$submodule_model = registry()->get('loader')->model('submodule');
	$row = $submodule_model->getSubModuleBySubModuleID($SubModuleID);
	if($field){
		if($row){
			return $row->$field;
		}else{
			return false;
		}
	}else{
		return $row;
	}
}
