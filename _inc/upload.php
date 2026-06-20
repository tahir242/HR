<?php
ob_start();
include("../_init.php");
if (!is_loggedin()) {
  header('HTTP/1.1 422 Unprocessable Entity');
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode(array('errorMsg' => 'error_login'));
  exit();
}

// LOAD MODEL 
$model = registry()->get('loader')->model('indexing');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
  try {

    $storeFolder = parameter("file_path");

    // Validate storage folder
    $storeFolder = realpath($storeFolder) . DIRECTORY_SEPARATOR;
    if (!$storeFolder || !is_dir($storeFolder) || !is_writable($storeFolder)) {
      echo "Storage folder is not accessible.";
      exit();
    }

    // Validate file upload
    if ($_FILES["file"]["error"] !== UPLOAD_ERR_OK) {
      echo "File upload error: " . $_FILES["file"]["error"];
      exit();
    }

    $fileName = basename($_FILES["file"]["name"]);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Ensure only PDFs are uploaded
    if ($fileExt !== "pdf") {
      echo "Only PDF files are allowed.";
      exit();
    }

    // Generate a unique filename using hash to prevent duplicates
    $newFileName = $fileName;
    $sourcePath = $_FILES["file"]["tmp_name"];
    $targetPath = $storeFolder . $newFileName;

    // Check if the file already exists in the database
    $query = "SELECT * FROM [Employee_PDF] WHERE [Scan] = ?";
    $param = array($newFileName);
    $fileExists = db()->get_row($query, $param);

    if ($fileExists) {
      echo "File already exists.";
      exit();
    }

    // Move uploaded file securely
    if (!move_uploaded_file($sourcePath, $targetPath)) {
      echo "File upload failed.";
      exit();
    }

    // Insert file details into database
    $fields = array("[Scan]", "[Status]", "[Created_By]");
    $params = array($newFileName, "Uploaded", user_id());
    db()->insert("[Employee_PDF]", $fields, $params);
    if (!db()->rows_effected) {
      unlink($targetPath); // Rollback file move if DB insertion fails
      echo "Database insertion failed.";
      exit();
    }
    insert_system_time_log(4, $newFileName);
    echo "Successfully Uploaded..!!";
    exit();
  } catch (Exception $e) {
    header('HTTP/1.1 422 Unprocessable Entity');
    echo $e->getMessage();
    exit();
  }
}

// VIEW FILE
if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action']) && $request->post['action'] == 'VIEWFILE') {
  try {
    $file = $request->post['file'];
    $targetPath = parameter("file_path") . $file;
    $fileExtension = pathinfo($targetPath, PATHINFO_EXTENSION);
    $fileData = file_get_contents($targetPath);
    insert_system_time_log(5, $file);
    // Set the appropriate headers based on file extension
    if ($fileExtension === 'pdf') {
      header("Content-Type: application/pdf");
      header("Content-Disposition: attachment; filename=\"$file\"");
    } else {
      header("Content-Type: image/jpeg");
    }

    header("Content-Length: " . strlen($fileData));
    // Output the file data
    echo $fileData;
    exit();

  } catch (Exception $e) {
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => $e->getMessage()));
    exit();
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_type']) && $_POST['action_type'] === 'DELETEPDF') {
  try {
    if (empty($_POST['file'])) {
      throw new Exception("Error: Invalid PDF");
    }

    $file = basename(trim($_POST['file']));

    // Only allow .pdf files with safe characters
    if (!preg_match('/^[\w\s\-]+\.(pdf)$/i', $file)) {
      throw new Exception("Invalid file name.");
    }

    // Resolve paths
    $originalPath = rtrim(parameter("file_path"), '/') . '/' . $file;
    $deletePath = rtrim(parameter("delete_path"), '/') . '/' . $file;

    // Ensure original file exists
    if (!file_exists($originalPath)) {
      throw new Exception("File does not exist.");
    }

    // Create delete directory if it doesn't exist
    $deleteDir = dirname($deletePath);
    if (!is_dir($deleteDir)) {
      if (!mkdir($deleteDir, 0755, true)) {
        throw new Exception("Failed to create delete path.");
      }
    }

    // Move file to delete path first (soft delete)
    if (!rename($originalPath, $deletePath)) {
      throw new Exception("Failed to move file to delete path.");
    }

    // Update database (soft delete)
    $table = "[Employee_PDF]";
    $what = ["Active"];
    $where = ["Scan"];
    $params = ["N", $file];
    $statement = $db->update($table, $what, $where, $params);

    if (!$db->rows_effected) {
      // Optional: Rollback the move if needed
      rename($deletePath, $originalPath); // Try to revert
      throw new Exception("Delete failed in DB. File restored.");
    }

    insert_system_time_log(6, $file);

    // Secure JSON Response
    header('Content-Type: application/json');
    echo json_encode(['valid' => true, 'msg' => 'Deleted Successfully!', 'id' => $file]);
    exit();

  } catch (Exception $e) {
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['errorMsg' => $e->getMessage()]);
    exit();
  }
}


/**
 *===================
 * START DATATABLE
 *===================
 */

require_once DIR_LIBRARY . 'mssql.ssp.class.php';

$where_query = "P.Active = 'Y'";

if (isset($request->get['s']) && $request->get['s'] !== '') {
  $status = $request->get['s'];
  $where_query .= ' AND P.[Status] = \'' . $status . '\'';
}

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
      P.Date_of_Joining,
      P.Scan,
      P.Status
  FROM [Employee_PDF] P
  LEFT JOIN [Department] DP ON P.Department = DP.Department_ID
  LEFT JOIN [Designation] DS ON P.Designation = DS.Designation_ID
  WHERE $where_query) AS view_form";

$primaryKey = 'Scan';
$columns = array(
  array(
    'db' => 'Scan',
    'dt' => 'DT_RowId',
    'formatter' => function ($d, $row) {
      return 'row_' . $d;
    }
  ),
  array('db' => 'Employee_ID', 'dt' => 'Employee_ID', ),
  array(
    'db' => 'Name',
    'dt' => 'Name',
    'formatter' => function ($d, $row) {
      return !empty($row['Name']) ? ucwords(strtolower($row['Name'])) : "";
    }
  ),
  array(
    'db' => 'Department',
    'dt' => 'Department',
    'formatter' => function ($d, $row) {
      return !empty($row['Department']) ? ucwords(strtolower($row['Department'])) : "";
    }
  ),
  array(
    'db' => 'Designation',
    'dt' => 'Designation',
    'formatter' => function ($d, $row) {
      return !empty($row['Designation']) ? ucwords(strtolower($row['Designation'])) : "";
    }
  ),
  array(
    'db' => 'Date_of_Joining',
    'dt' => 'Date_of_Joining',
    'formatter' => function ($d, $row) {
      return !empty($row['Date_of_Joining']) ? date_normalizer($row['Date_of_Joining'], "d M, Y") : "";
    }
  ),
  array('db' => 'Scan', 'dt' => 'Scan'),
  array(
    'db' => 'Status',
    'dt' => 'Status',
    'formatter' => function ($d, $row) {
      if ($row['Status'] == "Indexed") {
        return '<span class="badge badge-success">' . $row['Status'] . '</span>';
      } elseif ($row['Status'] == "Indexing") {
        return '<span class="badge badge-danger">' . $row['Status'] . '</span>';
      } else {
        return '<span class="badge badge-warning">' . $row['Status'] . '</span>';
      }
    }
  ),
  array(
    'db' => 'Scan',
    'dt' => 'btn_edit',
    'formatter' => function ($d, $row) {
      $menu = "<button class=\"btn shadow btn-sm py-0 px-2 view-pdf\"><i class=\"fas fa-file-pdf\" style=\"font-size:18px;color:red\"></i> </button>";
      if ($row['Status'] != "Indexed" || user_role_id() == 1 || has_permission(1, 'delete_pdf')) {
        $menu .= "<button class=\"btn btn-sm py-0 px-2  delete-pdf\"><i class=\"fas fa-trash\" style=\"font-size:18px;\"></i> </button>";
      }
      return $menu;
    }
  ),
);
$where_query1 = "1=1";
echo json_encode(
  SSP::complex($request->get, $sql_details, $table, $primaryKey, $columns, $where_query1),
);
