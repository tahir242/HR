<?php
ob_start();
include ("../_init.php");

if (!is_loggedin()) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(array('errorMsg' => "Unauthorized"));
    exit();
}

if ($request->server['REQUEST_METHOD'] == 'POST' && isset($request->post['action_type']) && $request->post['action_type'] == 'GET_DASHBOARD_DATA') {
    try {
        $year = isset($request->post['year']) ? $request->post['year'] : date('Y');

        // --- Main records query ---
        $query = "SELECT 
            P.Employee_ID,
            P.Name,
            P.Gender,
            ISNULL(DP.Department, 'Unknown') AS Department,
            P.Department AS Department_ID,
            ISNULL(DS.Designation, 'Unknown') AS Designation,
            P.Designation AS Designation_ID,
            ISNULL(LC.Location, 'Unknown') AS Location,
            ISNULL(EC.Employee_Category, 'Unknown') AS Employee_Category,
            P.Employee_Category AS Category_ID,
            CONVERT(varchar, P.Date_of_Joining, 23) AS DOJ,
            CONVERT(varchar, P.Date_of_Leaving, 23) AS DOL,
            ISNULL(RT.Resignation_Type, 'Unknown') AS Resignation_Type,
            P.Resignation_Type AS Resignation_Type_ID,
            ISNULL(ROT.Reason, 'Unknown') AS Reason,
            CASE 
                WHEN P.Date_of_Joining IS NOT NULL AND P.Date_of_Leaving IS NOT NULL 
                THEN DATEDIFF(MONTH, P.Date_of_Joining, P.Date_of_Leaving) 
                ELSE NULL 
            END AS Tenure_Months,
            YEAR(P.Date_of_Leaving) AS Leave_Year,
            MONTH(P.Date_of_Leaving) AS Leave_Month,
            FORMAT(P.Date_of_Leaving, 'MMM') AS Leave_Month_Name
        FROM [HR].[dbo].[Employee_PDF] P
        LEFT JOIN [Department] DP ON P.Department = DP.Department_ID
        LEFT JOIN [Designation] DS ON P.Designation = DS.Designation_ID
        LEFT JOIN [Location] LC ON P.Location = LC.Location_ID
        LEFT JOIN [Employee_Category] EC ON P.Employee_Category = EC.Category_ID
        LEFT JOIN [Resignation_Type] RT ON P.Resignation_Type = RT.Resignation_Type_ID
        LEFT JOIN [Reason_of_Turnover] ROT ON P.Reason_of_Turnover = ROT.Reason_ID
        WHERE P.Active = 'Y'
          AND P.Date_of_Leaving IS NOT NULL";

        $params = array();

        if ($year !== 'all') {
            $query .= " AND YEAR(P.Date_of_Leaving) = ?";
            $params[] = intval($year);
        }

        $query .= " ORDER BY P.Date_of_Leaving DESC";

        $records = $db->get_results($query, $params);

        // --- Filter: Available years ---
        $years = $db->get_results(
            "SELECT DISTINCT YEAR(Date_of_Leaving) as yr FROM [Employee_PDF] WHERE Active = 'Y' AND Date_of_Leaving IS NOT NULL ORDER BY yr DESC",
            array()
        );

        // --- Filter: Departments ---
        $departments = $db->get_results(
            "SELECT Department_ID as id, Department as name FROM [Department] WHERE Active = 1 ORDER BY Department",
            array()
        );

        // --- Filter: Designations ---
        $designations = $db->get_results(
            "SELECT Designation_ID as id, Designation as name FROM [Designation] WHERE Active = 1 ORDER BY Designation",
            array()
        );

        // --- Filter: Categories ---
        $categories = $db->get_results(
            "SELECT Category_ID as id, Employee_Category as name FROM [Employee_Category] WHERE Active = 1 ORDER BY Employee_Category",
            array()
        );

        // --- Filter: Resignation Types ---
        $resignation_types = $db->get_results(
            "SELECT Resignation_Type_ID as id, Resignation_Type as name FROM [Resignation_Type] ORDER BY Resignation_Type",
            array()
        );

        $filters = array(
            'years'             => $years,
            'departments'       => $departments,
            'designations'      => $designations,
            'categories'        => $categories,
            'resignation_types' => $resignation_types
        );

        header('Content-Type: application/json');
        echo json_encode(array("valid" => true, 'records' => $records, 'filters' => $filters));
        exit();

    } catch (Exception $e) {
        header('HTTP/1.1 422 Unprocessable Entity');
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('errorMsg' => $e->getMessage()));
        exit();
    }
}
