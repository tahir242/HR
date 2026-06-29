<?php
ob_start();
include ("../_init.php");

if (!is_loggedin()) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(array('errorMsg' => "Unauthorized"));
    exit();
}

if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'GET_REPORT') {
    try {
        $fromDate = $request->post['fromDate'];
        $toDate = $request->post['toDate'];

        if (!$fromDate || !$toDate) {
            throw new Exception("Dates are required.");
        }

        // Validate dates are not future
        $currentDate = date('Y-m-d');
        if ($fromDate > $currentDate || $toDate > $currentDate) {
            throw new Exception("Dates cannot be in the future.");
        }

        if ($toDate < $fromDate) {
            throw new Exception("To Date cannot be less than From Date.");
        }

        $query = "SELECT 
            ISNULL(DP.Department, 'Unknown') as Department,
            ISNULL(DS.Designation, 'Unknown') as Designation,
            COUNT(*) as Count
        FROM [HR].[dbo].[Employee_PDF] P
        LEFT JOIN [Department] DP ON P.Department = DP.Department_ID
        LEFT JOIN [Designation] DS ON P.Designation = DS.Designation_ID
        WHERE P.Active = 'Y' 
          AND P.Date_of_Leaving >= ? 
          AND P.Date_of_Leaving <= ?
        GROUP BY DP.Department, DS.Designation
        ORDER BY DP.Department ASC, Count DESC";

        $params = array($fromDate, $toDate);
        $results = $db->get_results($query, $params);

        header('Content-Type: application/json');
        echo json_encode(array("valid" => true, 'data' => $results));
        exit();

    } catch (Exception $e) {
        header('HTTP/1.1 422 Unprocessable Entity');
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('errorMsg' => $e->getMessage()));
        exit();
    }
}
