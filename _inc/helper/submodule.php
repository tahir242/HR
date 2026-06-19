<?php 

function get_submodules($data = array()){

    $module_model = registry()->get('loader')->model('submodule');
	$modules = $module_model->getSubModules($data);
	return $modules;

}

function count_submodules_by_module_id($ModuleID){
	$module_model = registry()->get('loader')->model('submodule');
	$modules = $module_model->coundSubModuleByModuleID($ModuleID);
	return $modules->total;
}

function get_the_submodule($SubModuleID, $field = null) 
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