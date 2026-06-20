<?php 
declare(strict_types=1);

function get_modules(array $data = array()) : ?array {

    $model = registry()->get('loader')->model('module');
	$modules = $model->getModules($data);
	return $modules;

}

function get_the_module(string $ModuleID, ?string $field = null) : object|string|bool|null
{
	$model = registry()->get('loader')->model('module');
	$row = $model->getModuleByModuleID($ModuleID);
	if($field){
		return $row->$field;
	}else{
		return $row;
	}
}
