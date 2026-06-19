<?php 

function get_services($data = array()){
    $model = registry()->get('loader')->model('service');
	$results = $model->getServices($data);
	return $results;
}

function get_the_service($Service_ID, $field = null) 
{
	$model = registry()->get('loader')->model('service');
	$row = $model->getService($Service_ID);
	if($field){
		return $row->$field;
	}else{
		return $row;
	}
}