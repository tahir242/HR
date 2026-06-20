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

$input = json_decode(file_get_contents('php://input'), true);

// GET RATION ISSUE LOG DATA
if ($request->server['REQUEST_METHOD'] == 'POST' && $input['action_type'] == "GET_ISSUE_LOG") {
    try {
        // Get filter parameters
        $year = isset($input['year']) && $input['year'] != '' ? $input['year'] : null;
        $employee_id = isset($input['employee_id']) && $input['employee_id'] != '' ? $input['employee_id'] : null;
        $department = isset($input['department']) && $input['department'] != '' ? $input['department'] : null;
        $designation = isset($input['designation']) && $input['designation'] != '' ? $input['designation'] : null;
        $status = isset($input['status']) && $input['status'] != '' ? $input['status'] : null;
        $issue_type = isset($input['issue_type']) && $input['issue_type'] != '' ? $input['issue_type'] : null;

        // Build WHERE clause
        $where_conditions = [];
        $params = [];

        if ($year !== null) {
            $where_conditions[] = "RIL.[Ration_Year] = ?";
            $params[] = $year;
        }

        if ($employee_id !== null) {
            $where_conditions[] = "RIL.[Employee_ID] LIKE ?";
            $params[] = '%' . $employee_id . '%';
        }

        if ($department !== null) {
            $where_conditions[] = "E.[Department] = ?";
            $params[] = $department;
        }

        if ($designation !== null) {
            $where_conditions[] = "E.[Designation] = ?";
            $params[] = $designation;
        }

        if ($status !== null) {
            $where_conditions[] = "RIL.[Status] = ?";
            $params[] = $status;
        }

        if ($issue_type !== null) {
            $where_conditions[] = "RIL.[Issue_Type] = ?";
            $params[] = $issue_type;
        }

        $where_clause = count($where_conditions) > 0 ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

        // Main query to get issue log data
        $query = "SELECT 
            RIL.[Employee_ID],
            E.[Name],
            E.[CNIC],
            DP.[Department],
            D.[Designation],
            RIL.[Ration_Year],
            RIL.[Status],
            RIL.[Issue_Type],
            RIL.[Issue_DateTime],
            RIL.[Issue_By],
            RIL.[Modified_By],
            RIL.[Modified_DateTime]
        FROM [Ration_Issue_Log] RIL
        LEFT JOIN [Employee] E ON RIL.[Employee_ID] = E.[Employee_ID]
        LEFT JOIN [Department] DP ON E.[Department] = DP.[Department_ID]
        LEFT JOIN [Designation] D ON E.[Designation] = D.[Designation_ID]
        $where_clause
        ORDER BY RIL.[Ration_Year] DESC, E.[Name]";

        $results = db()->get_results($query, $params);

        // Get summary counts
        $summary_query = "SELECT 
            COUNT(1) AS Total,
            COUNT(CASE WHEN RIL.[Status] = 'Issued' THEN 1 END) AS TotalIssued,
            COUNT(CASE WHEN RIL.[Status] = 'Eligible' THEN 1 END) AS TotalEligible
        FROM [Ration_Issue_Log] RIL
        LEFT JOIN [Employee] E ON RIL.[Employee_ID] = E.[Employee_ID]
        $where_clause";

        $summary = db()->get_row($summary_query, $params);

        // Format the results for display
        $formatted_results = [];
        foreach ($results as $row) {
            $issue_by = '';
            $issue_datetime = '';
            
            if ($row->Status == 'Issued' && $row->Issue_By) {
                $issue_by = get_the_user($row->Issue_By, "Fullname");
                $issue_datetime = $row->Issue_DateTime ? date_normalizer($row->Issue_DateTime, "d-M-Y h:i A") : '';
            }

            $formatted_results[] = [
                'Employee_ID' => $row->Employee_ID,
                'Name' => $row->Name,
                'CNIC' => $row->CNIC,
                'Department' => $row->Department ?? 'N/A',
                'Designation' => $row->Designation ?? 'N/A',
                'Ration_Year' => $row->Ration_Year,
                'Status' => $row->Status,
                'Issue_Type' => $row->Issue_Type ?? 'N/A',
                'Issue_DateTime' => $issue_datetime,
                'Issue_By' => $issue_by
            ];
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'valid' => true, 
            'data' => $formatted_results,
            'summary' => [
                'total' => $summary ? $summary->Total : 0,
                'issued' => $summary ? $summary->TotalIssued : 0,
                'eligible' => $summary ? $summary->TotalEligible : 0
            ]
        ]);
        exit();
    } catch (Exception $e) {
        http_response_code(422);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['errorMsg' => $e->getMessage()]);
        exit();
    }
}

// EXPORT TO EXCEL
if ($request->server['REQUEST_METHOD'] == 'POST' && $input['action_type'] == "EXPORT_EXCEL") {
    try {
        // Get filter parameters (same as above)
        $year = isset($input['year']) && $input['year'] != '' ? $input['year'] : null;
        $employee_id = isset($input['employee_id']) && $input['employee_id'] != '' ? $input['employee_id'] : null;
        $department = isset($input['department']) && $input['department'] != '' ? $input['department'] : null;
        $designation = isset($input['designation']) && $input['designation'] != '' ? $input['designation'] : null;
        $status = isset($input['status']) && $input['status'] != '' ? $input['status'] : null;
        $issue_type = isset($input['issue_type']) && $input['issue_type'] != '' ? $input['issue_type'] : null;

        // Build WHERE clause
        $where_conditions = [];
        $params = [];

        if ($year !== null) {
            $where_conditions[] = "RIL.[Ration_Year] = ?";
            $params[] = $year;
        }

        if ($employee_id !== null) {
            $where_conditions[] = "RIL.[Employee_ID] LIKE ?";
            $params[] = '%' . $employee_id . '%';
        }

        if ($department !== null) {
            $where_conditions[] = "E.[Department] = ?";
            $params[] = $department;
        }

        if ($designation !== null) {
            $where_conditions[] = "E.[Designation] = ?";
            $params[] = $designation;
        }

        if ($status !== null) {
            $where_conditions[] = "RIL.[Status] = ?";
            $params[] = $status;
        }

        if ($issue_type !== null) {
            $where_conditions[] = "RIL.[Issue_Type] = ?";
            $params[] = $issue_type;
        }

        $where_clause = count($where_conditions) > 0 ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

        // Query for export
        $query = "SELECT 
            RIL.[Employee_ID],
            E.[Name],
            E.[CNIC],
            DP.[Department],
            D.[Designation],
            RIL.[Ration_Year],
            RIL.[Status],
            RIL.[Issue_Type],
            RIL.[Issue_DateTime],
            RIL.[Issue_By]
        FROM [Ration_Issue_Log] RIL
        LEFT JOIN [Employee] E ON RIL.[Employee_ID] = E.[Employee_ID]
        LEFT JOIN [Department] DP ON E.[Department] = DP.[Department_ID]
        LEFT JOIN [Designation] D ON E.[Designation] = D.[Designation_ID]
        $where_clause
        ORDER BY RIL.[Ration_Year] DESC, E.[Name]";

        $results = db()->get_results($query, $params);

        // Format results with proper column names and Issue By user
        $formatted_export = [];
        foreach ($results as $row) {
            $issue_by = '';
            $issue_datetime = '';
            
            if ($row->Status == 'Issued' && $row->Issue_By) {
                $issue_by = get_the_user($row->Issue_By, "Fullname");
                $issue_datetime = $row->Issue_DateTime ? date_normalizer($row->Issue_DateTime, "d-M-Y h:i A") : '';
            }

            $formatted_export[] = [
                'Employee ID' => $row->Employee_ID,
                'Employee Name' => $row->Name,
                'CNIC' => $row->CNIC,
                'Department' => $row->Department ?? 'N/A',
                'Designation' => $row->Designation ?? 'N/A',
                'Year' => $row->Ration_Year,
                'Status' => $row->Status,
                'Issue Type' => $row->Issue_Type ?? 'N/A',
                'Issue Date/Time' => $issue_datetime,
                'Issued By' => $issue_by
            ];
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['valid' => true, 'data' => $formatted_export]);
        exit();
    } catch (Exception $e) {
        http_response_code(422);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['errorMsg' => $e->getMessage()]);
        exit();
    }
}
?>
