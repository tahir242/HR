<?php
ob_start();
include("../_init.php");

// Check, if user logged in or not
if (!is_loggedin()) {
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => "Error Login"));
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

// GET NOT DISTRIBUTED LIST
if ($request->server['REQUEST_METHOD'] == 'POST' && $input['action_type'] == "GET_NOT_DISTRIBUTED") {
    try {
        $year = isset($input['year']) ? $input['year'] : current_year();
        
        $query = "SELECT E.[Employee_ID], E.[Name], E.[CNIC], D.[Designation], DP.Department 
        FROM [Employee] E
        LEFT JOIN [Designation] D ON E.[Designation] = D.[Designation_ID]
        LEFT JOIN [Department] DP ON E.[Department] = DP.[Department_ID]
        LEFT JOIN [Ration_Issue_Log] RIL ON E.[Employee_ID] = RIL.[Employee_ID] AND RIL.[Ration_Year] = ?
        WHERE RIL.[Status] = 'Eligible'
        ORDER BY E.[Name]";
        
        $results = db()->get_results($query, [$year]);
        
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['valid' => true, 'data' => $results]);
        exit();
    } catch (Exception $e) {
        http_response_code(422);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['errorMsg' => $e->getMessage()]);
        exit();
    }
}

// GET ISSUE TYPE BREAKDOWN
if ($request->server['REQUEST_METHOD'] == 'POST' && $input['action_type'] == "GET_ISSUE_TYPE_BREAKDOWN") {
    try {
        $year = isset($input['year']) ? $input['year'] : current_year();
        
        $query = "SELECT 
            COUNT(CASE WHEN [Issue_Type] = 'Oil' THEN 1 END) AS Oil,
            COUNT(CASE WHEN [Issue_Type] = 'Ghee' THEN 1 END) AS Ghee,
            COUNT(CASE WHEN [Issue_Type] = 'Both' THEN 1 END) AS Both
        FROM [Ration_Issue_Log]
        WHERE [Ration_Year] = ? AND [Status] = 'Issued'";
        
        $row = db()->get_row($query, [$year]);
        
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['valid' => true, 'data' => $row]);
        exit();
    } catch (Exception $e) {
        http_response_code(422);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['errorMsg' => $e->getMessage()]);
        exit();
    }
}

// GET DAILY TREND
if ($request->server['REQUEST_METHOD'] == 'POST' && $input['action_type'] == "GET_DAILY_TREND") {
    try {
        $year = isset($input['year']) ? $input['year'] : current_year();
        
        $query = "SELECT 
            CONVERT(VARCHAR(10), [Issue_DateTime], 120) AS Date,
            COUNT(1) AS Count
        FROM [Ration_Issue_Log]
        WHERE [Ration_Year] = ? AND [Status] = 'Issued'
        GROUP BY CONVERT(VARCHAR(10), [Issue_DateTime], 120)
        ORDER BY CONVERT(VARCHAR(10), [Issue_DateTime], 120)";
        
        $results = db()->get_results($query, [$year]);
        
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['valid' => true, 'data' => $results]);
        exit();
    } catch (Exception $e) {
        http_response_code(422);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['errorMsg' => $e->getMessage()]);
        exit();
    }
}

// GET DEPARTMENT BREAKDOWN
if ($request->server['REQUEST_METHOD'] == 'POST' && $input['action_type'] == "GET_DEPARTMENT_BREAKDOWN") {
    try {
        $year = isset($input['year']) ? $input['year'] : current_year();
        
        $query = "SELECT 
            DP.Department,
            COUNT(1) AS Total,
            COUNT(CASE WHEN RIL.[Status] = 'Issued' THEN 1 END) AS Issued,
            COUNT(CASE WHEN RIL.[Status] = 'Eligible' THEN 1 END) AS Pending
        FROM [Ration_Issue_Log] RIL
        LEFT JOIN [Employee] E ON RIL.[Employee_ID] = E.[Employee_ID]
        LEFT JOIN [Department] DP ON E.[Department] = DP.[Department_ID]
        WHERE RIL.[Ration_Year] = ?
        GROUP BY DP.Department
        ORDER BY Issued DESC";
        
        $results = db()->get_results($query, [$year]);
        
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['valid' => true, 'data' => $results]);
        exit();
    } catch (Exception $e) {
        http_response_code(422);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['errorMsg' => $e->getMessage()]);
        exit();
    }
}

// GET ISSUED BY USER STATS (Admin only)
if ($request->server['REQUEST_METHOD'] == 'POST' && $input['action_type'] == "GET_ISSUED_BY_USER") {
    try {
        if (user_role_id() != 1) {
            throw new Exception("Unauthorized access");
        }
        
        $year = isset($input['year']) ? $input['year'] : current_year();
        
        // Get counts from SQL Server
        $query = "SELECT 
            RIL.[Issue_By] AS UserID,
            COUNT(1) AS Count
        FROM [Ration_Issue_Log] RIL
        WHERE RIL.[Ration_Year] = ? AND RIL.[Status] = 'Issued' AND RIL.[Issue_By] IS NOT NULL
        GROUP BY RIL.[Issue_By]
        ORDER BY Count DESC";
        
        $results = db()->get_results($query, [$year]);
        
        // Get user details from SQLite and merge with counts
        $finalResults = [];
        if ($results) {
            foreach ($results as $row) {
                $stmt = dblite()->prepare("SELECT Fullname FROM Users WHERE UserID = ?");
                $stmt->execute([$row->UserID]);
                $user = $stmt->fetch(PDO::FETCH_OBJ);
                
                $finalResults[] = (object)[
                    'Fullname' => $user ? $user->Fullname : 'Unknown User',
                    'Count' => $row->Count
                ];
            }
        }
        
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['valid' => true, 'data' => $finalResults]);
        exit();
    } catch (Exception $e) {
        http_response_code(422);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['errorMsg' => $e->getMessage()]);
        exit();
    }
}
