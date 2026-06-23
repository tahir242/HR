<?php
class ModelEmployeeTurnover extends Model 
{

    public function addTurnover($data) 
    {
        $fields = array(
            "[Employee_ID]", "[Name]", "[Gender]", "[Date_of_Birth]", 
            "[Department]", "[Designation]", "[Location]", 
            "[Date_of_Joining]", "[Date_of_Leaving]", "[Employee_Category]", 
            "[Resignation_Type]", "[Reason_of_Turnover]", "[Remarks]", 
            "[Scan]", "[Status]", "[Active]", "[Created_By]", "[Created_DtTm]"
        );
        $params = array(
            $data['Employee_ID'],
            ucwords($data['Employee_Name']),
            $data['Gender'] ?: null,
            $data['Date_of_Birth'] ?: null,
            $data['Department'] ?: null,
            $data['Designation'] ?: null,
            $data['Location'] ?: null,
            $data['DOJ'] ?: null,
            $data['Date_of_Leaving'] ?: null,
            $data['Employee_Category'] ?: null,
            $data['Resignation_Type'] ?: null,
            $data['Reason_of_Turnover'] ?: null,
            $data['Remarks'] ?: null,
            $data['Scan'] ?: null,
            'Pending',
            'Y',
            $data['Created_By'],
            $data['Created_DtTm']
        );
        $this->db->insert("[HR].[dbo].[Employee_PDF]", $fields, $params);
        if ($this->db->rows_effected) {
            return $data['Employee_ID'];
        } else {
            throw new Exception("Error: Failed to save Employee Turnover record.");
        }
    }

    public function checkDuplicate($employeeId)
    {
        $query = "SELECT COUNT(*) AS cnt FROM [HR].[dbo].[Employee_PDF] WHERE Employee_ID = ? AND Active = 'Y'";
        $row = $this->db->get_row($query, [$employeeId]);
        return $row && $row->cnt > 0;
    }

    public function getTurnoverByID($scan)
    {
        $query = "SELECT * FROM [HR].[dbo].[Employee_PDF] WHERE Scan = ?";
        $row = $this->db->get_row($query, [$scan]);
        return $row;
    }

    public function getTurnoverByEmployeeID($id)
    {
        $query = "SELECT * FROM [HR].[dbo].[Employee_PDF] WHERE Employee_ID = ? AND Active = 'Y'";
        $row = $this->db->get_row($query, [$id]);
        return $row;
    }

    public function updateTurnover($data)
    {
        $what  = array(
            "Name", "Gender", "Date_of_Birth", "Department", "Designation",
            "Location", "Date_of_Joining", "Date_of_Leaving", "Employee_Category",
            "Resignation_Type", "Reason_of_Turnover", "Remarks",
            "Modified_By", "Modified_DtTm"
        );
        $where  = array("Employee_ID");
        $params = array(
            ucwords($data['Employee_Name']),
            $data['Gender'] ?: null,
            $data['Date_of_Birth'] ?: null,
            $data['Department'] ?: null,
            $data['Designation'] ?: null,
            $data['Location'] ?: null,
            $data['DOJ'] ?: null,
            $data['Date_of_Leaving'] ?: null,
            $data['Employee_Category'] ?: null,
            $data['Resignation_Type'] ?: null,
            $data['Reason_of_Turnover'] ?: null,
            $data['Remarks'] ?: null,
            $data['Modified_By'],
            $data['Modified_DtTm'],
            $data['Employee_ID']
        );
        $this->db->update("[HR].[dbo].[Employee_PDF]", $what, $where, $params);
        if ($this->db->rows_effected) {
            return $data['Employee_ID'];
        } else {
            throw new Exception("Error: Failed to update Employee Turnover record.");
        }
    }

    public function uploadScan($scan, $scanFile)
    {
        $what  = array("Scan", "Modified_By", "Modified_DtTm");
        $where = array("Employee_ID");
        $params = array($scanFile, user_id(), date_time(), $scan);
        $this->db->update("[HR].[dbo].[Employee_PDF]", $what, $where, $params);
        if ($this->db->rows_effected) {
            return true;
        } else {
            throw new Exception("Error: Failed to upload PDF.");
        }
    }

}
