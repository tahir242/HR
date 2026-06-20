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

// Check, if user has reading permission or not
// If user have not reading permission return an alert message
if (user_role_id() != 1 && !has_permission(1, 'read_time_log')) {
  header('HTTP/1.1 422 Unprocessable Entity');
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode(array('errorMsg' => "Error Read Permission"));
  exit();
}

/**
 *===================
 * START DATATABLE
 *===================
 **/
require DIR_LIBRARY . "sqllitessp.class.php";

$where = "WHERE 1=1 ";
if (isset($request->get['u']) && $request->get['u'] != 'null' && $request->get['u'] != 'All' && $request->get['u'] != '') {
  $u = $request->get['u'];
  $where .= " AND stl.User = $u";
}

if (isset($request->get['t']) && $request->get['t'] != 'null' && $request->get['t'] != '') {
  $t = $request->get['t'];
  $where .= " AND stl.[Type] = $t";
}

if (isset($request->get['lastRecordID']) && $request->get['lastRecordID'] != 'null' && $request->get['lastRecordID'] != '') {
  $last = $request->get['lastRecordID'];
  $where .= " AND stl.ID > $last";
}


// DB table to use
$table = "(SELECT stl.*, u.Fullname AS UserName FROM System_Process_Time_Log stl LEFT JOIN Users u
  ON stl.User = u.UserID " . $where . ") AS System_Log";

// Table's primary key
$primaryKey = 'ID';

$columns = array(
  array(
    'db' => 'ID',
    'dt' => 'DT_RowId',
    'formatter' => function ($d, $row) {
      return 'row_' . $d;
    }
  ),
  array(
    'db' => 'ID',
    'dt' => 'ID',
    'formatter' => function ($d, $row) {
      return $row['ID'];
    }
  ),
  array(
    'db' => 'Type',
    'dt' => 'Type',
    'formatter' => function ($d, $row) {
      return system_log_dictionary($row['Type']);
    }
  ),
  array(
    'db' => 'Source',
    'dt' => 'Source',
    'formatter' => function ($d, $row) {
      return $row['Source'];
    }
  ),
  array(
    'db' => 'Time',
    'dt' => 'Time',
    'formatter' => function ($d, $row) {
      if ($row['Time'] <= 2) {
        return '<h6 class="text-success">' . $row['Time'] . '</h6>';
      } else {
        return '<h6 class="text-danger">' . $row['Time'] . '</h6>';
      };
    }
  ),
  array(
    'db' => 'Date_Time',
    'dt' => 'Date_Time',
    'formatter' => function ($d, $row) {
      return $row['Date_Time'];
    }
  ),
  array(
    'db' => 'UserName',
    'dt' => 'User',
    'formatter' => function ($d, $row) {
      return $row['UserName'];
    }
  )
);

$where_query = "1=1";

echo json_encode(
  SQLLITESSP::complex($request->get, $sql_details, $table, $primaryKey, $columns, null, $where_query)
);

/**
 *===================
 * END DATATABLE
 *===================
 */
