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

// LOAD MODEL 
$model = registry()->get('loader')->model('item');

if ($request->server['REQUEST_METHOD'] == 'POST' && $input['action_type'] == "SUBMITBARCODE") {
    try {

        if (!validateInteger($input['value'])) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['valid' => false, "msg" => "Invalid barcode value " . $input['value']]);
            exit();
        }

        // Common values
        $empid = $input['value'];
        $currentTimestamp = date_time();
        $currentUserId = user_id();

        $query = "SELECT E.[Employee_ID], E.[Name], E.[CNIC], D.[Designation], DP.Department, RIL.Status, RIL.Issue_DateTime, RIL.Issue_By FROM [Employee] E
        LEFT JOIN [Designation] D ON E.[Designation] = D.[Designation_ID]
        LEFT JOIN [Department] DP ON E.[Department] = DP.[Department_ID]
        LEFT JOIN [Ration_Issue_Log] RIL ON E.[Employee_ID] = RIL.[Employee_ID]
        WHERE E.[Employee_ID] = ? AND RIL.[Ration_Year] = ?";
        $row = db()->get_row($query, [$empid, current_year()]);

        if ($row) {
            if ($row->Status == "Issued") {
                header('Content-Type: application/json; charset=UTF-8');
                $cardHTML = '<h2>Ration Already Issued</h2><div class="rounded bg-secondary-subtle" style="font-size: 1rem; text-align: left; color: black;">
                <div class="row border-bottom py-1">
                    <div class="col-3 text-primary fw-bold">Code</div>
                    <div class="col-1">:</div>
                    <div class="col-8 text-dark">' . $row->Employee_ID . '</div>
                </div>
                <div class="row border-bottom py-1">
                    <div class="col-3 text-primary fw-bold">Name</div>
                    <div class="col-1">:</div>
                    <div class="col-8 text-dark">' . htmlspecialchars($row->Name) . '</div>
                </div>
                <div class="row border-bottom py-1">
                    <div class="col-3 text-primary fw-bold">Issued At</div>
                    <div class="col-1">:</div>
                    <div class="col-8 text-dark">' . date_normalizer($row->Issue_DateTime, "d-M-Y h:i A") . '</div>
                </div>
                <div class="row border-bottom py-1">
                    <div class="col-3 text-primary fw-bold">Issue By</div>
                    <div class="col-1">:</div>
                    <div class="col-8 text-dark">' . get_the_user($row->Issue_By, "Fullname") . '</div>
                </div>
            </div>';
                echo json_encode(['valid' => false, "msg" => $cardHTML]);
                exit();
            }


            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['valid' => true, "data" => $row]);
            exit();
        } else {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['valid' => false, "msg" => "Employee Not Found!<br>Please Check Barcode value " . $input['value']]);
            exit();
        }
    } catch (Exception $e) {
        // Handle errors gracefully
        http_response_code(422); // Unprocessable Entity
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['errorMsg' => $e->getMessage()]);
        exit();
    }
}

if ($request->server['REQUEST_METHOD'] == 'POST' && $input['action_type'] == "ISSUERATION") {
    try {

        header('Content-Type: application/json; charset=UTF-8');

        if (!validateInteger($input['value'])) {
            echo json_encode(['valid' => false, "msg" => "Invalid barcode value " . $input['value']]);
            exit();
        }

        // Common values
        $empid = $input['value'];
        $type = $input['issue'];
        $currentTimestamp = date_time();
        $currentUserId = user_id();

        $query = "SELECT * FROM [Ration_Issue_Log] WHERE [Employee_ID] = ? AND [Ration_Year] = ?";
        $row = db()->get_row($query, [$empid, current_year()]);

        if (!$row) {
            echo json_encode(['valid' => false, "msg" => "Employee Not Found!<br>Or<br> Employee " . $input['value'] . " Not Eligible for Ration"]);
            exit();
        }

        switch ($type) {
            case "Oil":
                $query = "SELECT * FROM [Ration_Item] WHERE [Item_ID] <> ?";
                $results = db()->get_results($query, [5]);
                break;
            case "Ghee":
                $query = "SELECT * FROM [Ration_Item] WHERE [Item_ID] <> ?";
                $results = db()->get_results($query, [4]);
                break;
            case "Both":
                $query = "SELECT * FROM [Ration_Item]";
                $results = db()->get_results($query, []);
                break;
            default:
                echo json_encode(['valid' => false, "msg" => "Invalid Ration Type"]);
                exit();
        }

        if ($results) {

            foreach ($results as $result) {
                if ($result->Balance < $result->Issue_Qty) {
                    echo json_encode(['valid' => false, "msg" => "Item: " . $result->Item_Name . " is out of stock"]);
                    exit();
                }
            }

            foreach ($results as $result) {
                if ($type == "Both" && $result->InChoice) {
                    $qty = $result->Issue_Qty / 2;
                } else {
                    $qty = $result->Issue_Qty;
                }

                $field = array("[Year]", "[Employee_ID]", "[Item_ID]", "[Issued_Qty]", "[Transaction_By]");
                $params = array(current_year(), $empid, $result->Item_ID, $qty, user_id());
                $db->insert("[Ration_Transaction]", $field, $params);
                if ($db->rows_effected) {
                    $afterMinus = $result->Balance - $qty;  // use $qty (halved for 'Both'), not Issue_Qty
                    $model->updateItemBalance($result->Item_ID, $afterMinus);
                } else {
                    throw new Exception("Inserting Item Transaction Failed..");
                }
            }

            $what = array("[Status]", "[Issue_Type]", "[Issue_By]", "[Issue_DateTime]");
            $where = array("Employee_ID", "Ration_Year");
            $params = array("Issued", $type, user_id(), date_time(), $empid, current_year());
            $db->update("[Ration_Issue_Log]", $what, $where, $params);
            if ($db->rows_effected) {
                echo json_encode(['valid' => true, "msg" => "Ration Issued Successfully"]);
                exit();
            } else {
                throw new Exception("Updating Ration Issue Log Failed..");
            }
        }

    } catch (Exception $e) {
        // Handle errors gracefully
        http_response_code(422); // Unprocessable Entity
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['errorMsg' => $e->getMessage()]);
        exit();
    }
}
