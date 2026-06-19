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

// LOAD MODEL 
$model = registry()->get('loader')->model('indexing');

// Create
if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'SEARCH') {
  try {

    // Initialize variables
    $empid = !empty($request->post['Employee_ID']) ? $request->post['Employee_ID'] : null;
    $name = !empty($request->post['Employee_Name']) ? $request->post['Employee_Name'] : null;
    $department = !empty($request->post['Department']) ? $request->post['Department'] : null;
    $designation = !empty($request->post['Designation']) ? $request->post['Designation'] : null;
    $doj = !empty($request->post['DOJ']) ? $request->post['DOJ'] : null;

    $whereQuery = " WHERE Active = 'Y'";
    $log = [];
    if ($empid) {
      $whereQuery .= " AND Employee_ID LIKE ?";
      $params[] = "%$empid%";
      $log["Employee_ID"] = $empid;
    }

    if ($name) {
      if (strpos($name, '%') !== false) {
        $whereQuery .= " AND [Name] LIKE ?";
        $params[] = $name;
      } else {
        $whereQuery .= " AND [Name] LIKE ?";
        $params[] = "%$name%";
      }
      $log["Employee_Name"] = $name;
    }

    if ($doj) {
      $date = date_normalizer($doj, "Y-m-d");
      $whereQuery .= " AND [Date_of_Joining] = ?";
      $params[] = $date;
      $log["DOJ"] = $date;
    }

    if ($department) {
      $whereQuery .= " AND [Department] = ?";
      $params[] = $department;
      $log["Department"] = $department;
    }

    if ($designation) {
      $whereQuery .= " AND [Designation] = ?";
      $params[] = $designation;
      $log["Designation"] = $designation;
    }

    $query = "SELECT * FROM [HR].[dbo].[Employee_PDF] $whereQuery ORDER BY [Employee_ID], [Name]";
    $results = $db->get_results($query, $params);

    // Load the search results template
    ob_start();
    if ($results && count($results) == 1) {
      $scan = $results[0]->Scan;
      include 'template/search/view_file.php';
    } else {
      include 'template/search/search.php';
    }
    $content = ob_get_clean();

    insert_system_time_log(3, json_encode($log, true));

    // Return the JSON response
    header('Content-Type: application/json');
    echo json_encode(["valid" => true, 'content' => $content]);
    exit();

  } catch (Exception $e) {
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => $e->getMessage()));
    exit();
  }
}

// Create
if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'VIEWPDF') {
  try {

    // Initialize variables
    $scan = !empty($request->post['Scan']) ? $request->post['Scan'] : null;


    // Load the search results template
    ob_start();
    include 'template/search/view_file.php';
    $content = ob_get_clean();

    // Return the JSON response
    header('Content-Type: application/json');
    echo json_encode(["valid" => true, 'content' => $content]);
    exit();

  } catch (Exception $e) {
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => $e->getMessage()));
    exit();
  }
}

// Create
if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'UPDATEDEMOGRAPHIC') {
  try {

    // Initialize variables
    $scan = !empty($request->post['Scan']) ? $request->post['Scan'] : null;
    $emp = $model->getPdfByID($scan);
    
    // Load the search results template
    ob_start();
    include 'template/search/indexing.php';
    $content = ob_get_clean();

    // Return the JSON response
    header('Content-Type: application/json');
    echo json_encode(["valid" => true, 'content' => $content]);
    exit();

  } catch (Exception $e) {
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => $e->getMessage()));
    exit();
  }
}

// Search Fields
if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'UPDATESEARHCFIELD') {
  try { 
    // Load the search results template
    ob_start();
    include 'template/search/search_fields.php';
    $content = ob_get_clean();

    // Return the JSON response
    header('Content-Type: application/json');
    echo json_encode(["valid" => true, 'content' => $content]);
    exit();

  } catch (Exception $e) {
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => $e->getMessage()));
    exit();
  }
}
