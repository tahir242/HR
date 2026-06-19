<?php 

function get_modules($data = array()){

    $model = registry()->get('loader')->model('module');
	$modules = $model->getModules($data);
	return $modules;

}

function get_the_module($ModuleID, $field = null) 
{
	$model = registry()->get('loader')->model('module');
	$row = $model->getModuleByModuleID($ModuleID);
	if($field){
		return $row->$field;
	}else{
		return $row;
	}
}