<?php
ob_start();
include("../_init.php");

// Check, if user logged in or not
// If user is not logged in then return an alert message
if (!is_loggedin()) {
  header('HTTP/1.1 422 Unprocessable Entity');
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode(array('errorMsg' => "Error Login"));
  exit();
}

// view support modal
if (isset($request->get['action_type']) && $request->get['action_type'] == 'VIEWSUPPORTMODAL') {
  include 'template/partial/supportdesk.php';
  exit();
}

if ($request->server['REQUEST_METHOD'] == 'GET' && isset($request->get['action_type']) && $request->get['action_type'] == 'AUTOCOMPLETE')
{
  try {
    $results = [];
    $term = $request->get['value'];
    $column = $request->get['column'];

    if(!$term) return;

    $stmt = "SELECT TOP 5 $column FROM $column WHERE $column LIKE '%$term%' ORDER BY $column";
    $resutls = $db->get_results($stmt, []);

    if($resutls){
      foreach($resutls AS $row) {
        $results[] = $row->$column;
      }
    }

    header('Content-Type: application/json');
    echo json_encode(array('valid' => true, 'results' => $results));
    exit();
  } catch (Exception $e) { 
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => $e->getMessage()));
    exit();
  }
}
if ($request->server['REQUEST_METHOD'] == 'GET' && isset($request->get['action_type']) && $request->get['action_type'] == 'GET_REASON_BY_TYPE')
{
  try {
    $resignation_type_id = $request->get['Resignation_Type_ID'];
    $stmt = "SELECT Reason_ID, Reason FROM Reason_of_Turnover WHERE Resignation_Type_ID = ? AND Active = 1 ORDER BY Reason";
    $results = db()->get_results($stmt, [$resignation_type_id]);

    header('Content-Type: application/json');
    echo json_encode(array('valid' => true, 'results' => $results ? $results : []));
    exit();
  } catch (Exception $e) { 
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => $e->getMessage()));
    exit();
  }
}
