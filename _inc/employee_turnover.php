<?php 
ob_start();
include ("../_init.php");

// Check, if user logged in or not
// If user is not logged in then return an alert message
if (!is_loggedin()) {
  header('HTTP/1.1 422 Unprocessable Entity');
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode(array('errorMsg' => "Error Login"));
  exit();
}

// LOAD MODEL 
$model = registry()->get('loader')->model('employee_turnover');

// Validate post data
function validate_request_data($request) 
{  
  if (!validateString($request->post['Employee_ID'])) {
    throw new Exception("Error: Employee ID is required.");
  }
  if (!validateString($request->post['Employee_Name'])) {
    throw new Exception("Error: Employee Name is required.");
  }
  if (empty($request->post['Gender'])) {
    throw new Exception("Error: Gender is required.");
  }
  if (empty($request->post['Date_of_Birth'])) {
    throw new Exception("Error: Date of Birth is required.");
  }
  if (empty($request->post['Department'])) {
    throw new Exception("Error: Department is required.");
  }
  if (empty($request->post['Designation'])) {
    throw new Exception("Error: Designation is required.");
  }
  if (empty($request->post['Location'])) {
    throw new Exception("Error: Location is required.");
  }
  if (empty($request->post['DOJ'])) {
    throw new Exception("Error: Date of Joining is required.");
  }
  if (empty($request->post['Date_of_Leaving'])) {
    throw new Exception("Error: Date of Leaving is required.");
  }
  if (empty($request->post['Employee_Category'])) {
    throw new Exception("Error: Employee Category is required.");
  }
  if (empty($request->post['Resignation_Type'])) {
    throw new Exception("Error: Resignation Type is required.");
  }
  if (empty($request->post['Reason_of_Turnover'])) {
    throw new Exception("Error: Reason of Turnover is required.");
  }
}

function validate_existance($request)
{
  $statement = "SELECT * FROM [HR].[dbo].[Employee_PDF] WHERE Employee_ID = ? AND Active = 'Y'";
  $params  = array($request->post['Employee_ID']);
  $stmt    = sqlsrv_query( db()->conn, $statement, $params, array( "Scrollable" => SQLSRV_CURSOR_KEYSET ));
  
  if( $stmt === false ) {
      die( print_r( sqlsrv_errors(), true));
  }

  $total_try  = sqlsrv_num_rows($stmt);
  if ($total_try > 0) {
    throw new Exception("Error: Employee ID already exists.");
  }
}

// Create Employee Turnover
if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'CREATE')
{
  try {

    // Validate post data
    validate_request_data($request);
    // Validate duplicate
    validate_existance($request);

    // Normalize Date of Birth
    if (isset($request->post['Date_of_Birth']) && $request->post['Date_of_Birth'] !== '') {
        $request->post['Date_of_Birth'] = date_normalizer($request->post['Date_of_Birth'], "Y-m-d");
    } else {
        $request->post['Date_of_Birth'] = null;
    }

    // Normalize Date of Joining
    if (isset($request->post['DOJ']) && $request->post['DOJ'] !== '') {
        $request->post['DOJ'] = date_normalizer($request->post['DOJ'], "Y-m-d");
    } else {
        $request->post['DOJ'] = null;
    }

    // Normalize Date of Leaving
    if (isset($request->post['Date_of_Leaving']) && $request->post['Date_of_Leaving'] !== '') {
        $request->post['Date_of_Leaving'] = date_normalizer($request->post['Date_of_Leaving'], "Y-m-d");
    } else {
        $request->post['Date_of_Leaving'] = null;
    }

    // Normalize nullable FK fields
    if (isset($request->post['Department']) && $request->post['Department'] == '') {
        $request->post['Department'] = null;
    }
    if (isset($request->post['Designation']) && $request->post['Designation'] == '') {
        $request->post['Designation'] = null;
    }
    if (isset($request->post['Location']) && $request->post['Location'] == '') {
        $request->post['Location'] = null;
    }
    if (isset($request->post['Employee_Category']) && $request->post['Employee_Category'] == '') {
        $request->post['Employee_Category'] = null;
    }
    if (isset($request->post['Resignation_Type']) && $request->post['Resignation_Type'] == '') {
        $request->post['Resignation_Type'] = null;
    }
    if (isset($request->post['Reason_of_Turnover']) && $request->post['Reason_of_Turnover'] == '') {
        $request->post['Reason_of_Turnover'] = null;
    }

    // Handle optional PDF upload
    $scanFileName = null;
    if (isset($_FILES['Scan']) && $_FILES['Scan']['error'] === UPLOAD_ERR_OK) {
        $storeFolder = parameter("file_path");
        $storeFolder = realpath($storeFolder) . DIRECTORY_SEPARATOR;

        if (!$storeFolder || !is_dir($storeFolder) || !is_writable($storeFolder)) {
            throw new Exception("Error: Storage folder is not accessible.");
        }

        $originalName = basename($_FILES["Scan"]["name"]);
        $fileExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if ($fileExt !== "pdf") {
            throw new Exception("Error: Only PDF files are allowed.");
        }

        // Generate unique filename
        $scanFileName = "TURNOVER_" . $request->post['Employee_ID'] . "_" . time() . ".pdf";
        $sourcePath = $_FILES["Scan"]["tmp_name"];
        $targetPath = $storeFolder . $scanFileName;

        if (!move_uploaded_file($sourcePath, $targetPath)) {
            throw new Exception("Error: File upload failed.");
        }
    }

    $request->post['Scan'] = $scanFileName;
    $request->post['Created_By'] = user_id();
    $request->post['Created_DtTm'] = date_time();

    // Insert record
    $id = $model->addTurnover($request->post);
    insert_system_time_log(2, "employee turnover entry");
    header('Content-Type: application/json');
    echo json_encode(array("valid" => true, 'msg' => "Employee Turnover saved successfully.", 'id' => $id));
    exit();

  } catch (Exception $e) {
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => $e->getMessage()));
    exit();
  }
} 

// Update Employee Turnover
if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'UPDATE')
{
  try {
    validate_request_data($request);

    // Normalize dates
    if (isset($request->post['Date_of_Birth']) && $request->post['Date_of_Birth'] !== '') {
        $request->post['Date_of_Birth'] = date_normalizer($request->post['Date_of_Birth'], "Y-m-d");
    } else {
        $request->post['Date_of_Birth'] = null;
    }
    if (isset($request->post['DOJ']) && $request->post['DOJ'] !== '') {
        $request->post['DOJ'] = date_normalizer($request->post['DOJ'], "Y-m-d");
    } else {
        $request->post['DOJ'] = null;
    }
    if (isset($request->post['Date_of_Leaving']) && $request->post['Date_of_Leaving'] !== '') {
        $request->post['Date_of_Leaving'] = date_normalizer($request->post['Date_of_Leaving'], "Y-m-d");
    } else {
        $request->post['Date_of_Leaving'] = null;
    }

    // Normalize nullable FK fields
    $fkFields = ['Department', 'Designation', 'Location', 'Employee_Category', 'Resignation_Type', 'Reason_of_Turnover'];
    foreach ($fkFields as $field) {
        if (isset($request->post[$field]) && $request->post[$field] == '') {
            $request->post[$field] = null;
        }
    }

    $request->post['Modified_By'] = user_id();
    $request->post['Modified_DtTm'] = date_time();

    $id = $model->updateTurnover($request->post);
    insert_system_time_log(2, "employee turnover update");
    header('Content-Type: application/json');
    echo json_encode(array("valid" => true, 'msg' => "Employee Turnover updated successfully.", 'id' => $id));
    exit();

  } catch (Exception $e) {
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => $e->getMessage()));
    exit();
  }
}

// Get Employee Turnover record by Employee_ID
if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'GET')
{
  try {
    $empId = $request->post['Employee_ID'];
    if (!$empId) {
        throw new Exception("Error: Invalid record.");
    }
    $row = $model->getTurnoverByEmployeeID($empId);
    if (!$row) {
        throw new Exception("Error: Record not found.");
    }

    // Format dates for display
    $record = array(
        'Employee_ID' => $row->Employee_ID,
        'Name' => $row->Name,
        'Gender' => $row->Gender,
        'Date_of_Birth' => $row->Date_of_Birth ? date_normalizer($row->Date_of_Birth, "d-m-Y") : '',
        'Department' => $row->Department,
        'Designation' => $row->Designation,
        'Location' => $row->Location,
        'Date_of_Joining' => $row->Date_of_Joining ? date_normalizer($row->Date_of_Joining, "d-m-Y") : '',
        'Date_of_Leaving' => $row->Date_of_Leaving ? date_normalizer($row->Date_of_Leaving, "d-m-Y") : '',
        'Employee_Category' => $row->Employee_Category,
        'Resignation_Type' => $row->Resignation_Type,
        'Reason_of_Turnover' => $row->Reason_of_Turnover,
        'Remarks' => $row->Remarks,
        'Scan' => $row->Scan,
        'Status' => $row->Status
    );

    header('Content-Type: application/json');
    echo json_encode(array("valid" => true, 'record' => $record));
    exit();

  } catch (Exception $e) {
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => $e->getMessage()));
    exit();
  }
}

// Upload PDF for existing record
if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'UPLOAD_SCAN')
{
  try {
    $employeeId = $request->post['Employee_ID'];
    if (!$employeeId) {
        throw new Exception("Error: Employee ID is required.");
    }

    if (!isset($_FILES['Scan']) || $_FILES['Scan']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Error: Please select a PDF file to upload.");
    }

    $storeFolder = parameter("file_path");
    $storeFolder = realpath($storeFolder) . DIRECTORY_SEPARATOR;
    if (!$storeFolder || !is_dir($storeFolder) || !is_writable($storeFolder)) {
        throw new Exception("Error: Storage folder is not accessible.");
    }

    $originalName = basename($_FILES["Scan"]["name"]);
    $fileExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if ($fileExt !== "pdf") {
        throw new Exception("Error: Only PDF files are allowed.");
    }

    $scanFileName = "TURNOVER_" . $employeeId . "_" . time() . ".pdf";
    $sourcePath = $_FILES["Scan"]["tmp_name"];
    $targetPath = $storeFolder . $scanFileName;

    if (!move_uploaded_file($sourcePath, $targetPath)) {
        throw new Exception("Error: File upload failed.");
    }

    $model->uploadScan($employeeId, $scanFileName);
    insert_system_time_log(2, "employee turnover pdf upload");
    header('Content-Type: application/json');
    echo json_encode(array("valid" => true, 'msg' => "PDF uploaded successfully."));
    exit();

  } catch (Exception $e) {
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => $e->getMessage()));
    exit();
  }
}

/**
 *===================
 * START DATATABLE
 *===================
 */

require_once DIR_LIBRARY . 'mssql.ssp.class.php';

$where_query = "P.Active = 'Y' AND P.Employee_ID IS NOT NULL AND P.Employee_ID != ''";

if (isset($request->get['q']) && $request->get['q'] != 'null' && $request->get['q'] != '') {
  $search = $request->get['q'];
  $where_query .= " AND (P.Employee_ID LIKE '%$search%' 
      OR P.Name LIKE '%$search%' 
      OR DP.Department LIKE '%$search%' 
      OR DS.Designation LIKE '%$search%')";
}

$table = "(SELECT 
      P.Employee_ID,
      P.Name,
      DP.Department,
      DS.Designation,
      LC.Location,
      P.Date_of_Joining,
      P.Date_of_Leaving,
      RT.Resignation_Type,
      P.Scan,
      P.Status
  FROM [Employee_PDF] P
  LEFT JOIN [Department] DP ON P.Department = DP.Department_ID
  LEFT JOIN [Designation] DS ON P.Designation = DS.Designation_ID
  LEFT JOIN [Location] LC ON P.Location = LC.Location_ID
  LEFT JOIN [Resignation_Type] RT ON P.Resignation_Type = RT.Resignation_Type_ID
  WHERE $where_query) AS view_form";

$primaryKey = 'Employee_ID';
$columns = array(
  array(
    'db' => 'Employee_ID',
    'dt' => 'DT_RowId',
    'formatter' => function ($d, $row) {
      return 'row_' . $d;
    }
  ),
  array('db' => 'Employee_ID', 'dt' => 'Employee_ID'),
  array(
    'db' => 'Name',
    'dt' => 'Name',
    'formatter' => function ($d, $row) {
      return !empty($row['Name']) ? $row['Name'] : "";
    }
  ),
  array(
    'db' => 'Department',
    'dt' => 'Department',
    'formatter' => function ($d, $row) {
      return !empty($row['Department']) ? $row['Department'] : "";
    }
  ),
  array(
    'db' => 'Designation',
    'dt' => 'Designation',
    'formatter' => function ($d, $row) {
      return !empty($row['Designation']) ? $row['Designation'] : "";
    }
  ),
  array(
    'db' => 'Location',
    'dt' => 'Location',
    'formatter' => function ($d, $row) {
      return !empty($row['Location']) ? $row['Location'] : "";
    }
  ),
  array(
    'db' => 'Date_of_Joining',
    'dt' => 'Date_of_Joining',
    'formatter' => function ($d, $row) {
      return !empty($row['Date_of_Joining']) ? date_normalizer($row['Date_of_Joining'], "d M, Y") : "";
    }
  ),
  array(
    'db' => 'Date_of_Leaving',
    'dt' => 'Date_of_Leaving',
    'formatter' => function ($d, $row) {
      return !empty($row['Date_of_Leaving']) ? date_normalizer($row['Date_of_Leaving'], "d M, Y") : "";
    }
  ),
  array(
    'db' => 'Resignation_Type',
    'dt' => 'Resignation_Type',
    'formatter' => function ($d, $row) {
      return !empty($row['Resignation_Type']) ? $row['Resignation_Type'] : "";
    }
  ),
  array('db' => 'Scan', 'dt' => 'Scan'),
  array(
    'db' => 'Status',
    'dt' => 'Status',
    'formatter' => function ($d, $row) {
      if ($row['Status'] == "Indexed") {
        return '<span class="badge badge-success">' . $row['Status'] . '</span>';
      } elseif ($row['Status'] == "Pending") {
        return '<span class="badge badge-warning">' . $row['Status'] . '</span>';
      } else {
        return '<span class="badge badge-info">' . $row['Status'] . '</span>';
      }
    }
  ),
  array(
    'db' => 'Scan',
    'dt' => 'btn_actions',
    'formatter' => function ($d, $row) {
      $menu = '<button class="btn btn-sm btn-info py-0 px-2 view-record" title="View"><i class="fas fa-eye" style="font-size:14px;"></i></button> ';
      $menu .= '<button class="btn btn-sm btn-primary py-0 px-2 edit-record" title="Edit"><i class="fas fa-edit" style="font-size:14px;"></i></button> ';
      if (empty($row['Scan']) || $row['Status'] == 'Pending') {
        $menu .= '<button class="btn btn-sm btn-warning py-0 px-2 upload-pdf" title="Upload PDF"><i class="fas fa-upload" style="font-size:14px;"></i></button>';
      } else {
        $menu .= '<button class="btn btn-sm btn-danger py-0 px-2 open-pdf" title="Open PDF"><i class="fas fa-file-pdf" style="font-size:14px;"></i></button>';
      }
      return $menu;
    }
  ),
);
$where_query1 = "1=1";
echo json_encode(
  SSP::complex($request->get, $sql_details, $table, $primaryKey, $columns, $where_query1),
);
