<?php
ob_start();
include ("../_init.php");

if (!is_loggedin()) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(array('errorMsg' => "Unauthorized"));
    exit();
}

if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'GET_MATRIX') {
    try {
        $fromMonth = $request->post['fromMonth']; // e.g., '2025-09'
        $toMonth = $request->post['toMonth'];

        if (!$fromMonth || !$toMonth) {
            throw new Exception("Dates are required.");
        }

        $currentMonth = date('Y-m');
        if ($fromMonth > $currentMonth || $toMonth > $currentMonth) {
            throw new Exception("Dates cannot be in the future.");
        }

        if ($toMonth < $fromMonth) {
            throw new Exception("To Month cannot be less than From Month.");
        }

        $fromDate = $fromMonth . '-01';
        $toDateObj = new DateTime($toMonth . '-01');
        $toDate = $toDateObj->format('Y-m-t'); // Last day of the selected To Month

        $query = "SELECT 
            ISNULL(DP.Department, 'Unknown') as Department,
            YEAR(P.Date_of_Leaving) as Year,
            MONTH(P.Date_of_Leaving) as Month,
            COUNT(*) as Count
        FROM [HR].[dbo].[Employee_PDF] P
        LEFT JOIN [Department] DP ON P.Department = DP.Department_ID
        WHERE P.Active = 'Y' 
          AND P.Date_of_Leaving >= ? 
          AND P.Date_of_Leaving <= ?
        GROUP BY DP.Department, YEAR(P.Date_of_Leaving), MONTH(P.Date_of_Leaving)";

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
