<?php

function get_the_department($id, $field = null)
{
    $model = registry()->get('loader')->model('dictionary');
    $row = $model->getDepartment($id);
    if ($field) {
        return $row ? $row->$field : "";
    } else {
        return $row;
    }
}

function get_the_designation($id, $field = null)
{
    $model = registry()->get('loader')->model('dictionary');
    $row = $model->getDesignation($id);
    if ($field) {
        return $row ? $row->$field : "";
    } else {
        return $row;
    }
}

function get_departments($data = array())
{
    $model = registry()->get('loader')->model('dictionary');
    $results = $model->getDepartments($data);
    return $results;
}

function get_designations($data = array())
{
    $model = registry()->get('loader')->model('dictionary');
    $results = $model->getDesignations($data);
    return $results;
}