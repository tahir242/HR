<?php
ob_start();
include ("../_init.php");

if (!is_loggedin()) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(array('errorMsg' => "Unauthorized"));
    exit();
}

if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'GET_TENURE') {
    try {
        $fromMonth = $request->post['fromMonth']; 
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
        $toDate = $toDateObj->format('Y-m-t'); 

        $query = "SELECT
            SUM(CASE WHEN DATEDIFF(month, Date_of_Joining, Date_of_Leaving) < 12 THEN 1 ELSE 0 END) as bucket_0_1,
            SUM(CASE WHEN DATEDIFF(month, Date_of_Joining, Date_of_Leaving) >= 12 AND DATEDIFF(month, Date_of_Joining, Date_of_Leaving) <= 36 THEN 1 ELSE 0 END) as bucket_1_3,
            SUM(CASE WHEN DATEDIFF(month, Date_of_Joining, Date_of_Leaving) > 36 AND DATEDIFF(month, Date_of_Joining, Date_of_Leaving) <= 60 THEN 1 ELSE 0 END) as bucket_3_5,
            SUM(CASE WHEN DATEDIFF(month, Date_of_Joining, Date_of_Leaving) > 60 AND DATEDIFF(month, Date_of_Joining, Date_of_Leaving) <= 120 THEN 1 ELSE 0 END) as bucket_5_10,
            SUM(CASE WHEN DATEDIFF(month, Date_of_Joining, Date_of_Leaving) > 120 THEN 1 ELSE 0 END) as bucket_10_plus,
            COUNT(*) as Total
        FROM [HR].[dbo].[Employee_PDF]
        WHERE Active = 'Y' 
          AND Date_of_Leaving >= ? 
          AND Date_of_Leaving <= ?
          AND Date_of_Joining IS NOT NULL";

        $params = array($fromDate, $toDate);
        $results = $db->get_row($query, $params);

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
