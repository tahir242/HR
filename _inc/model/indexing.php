<?php
class ModelIndexing extends Model 
{

    public function getpdfforindexing(){

        $query = "SELECT * FROM [Employee_PDF] WHERE [Status] = ? AND [Modified_By] = ? AND Active = ? ORDER BY Scan DESC";
        $row = $this->db->get_row($query, ["Indexing", user_id(), "Y"]);
        if(!$row){
            $query = "SELECT * FROM [Employee_PDF] WHERE [Status] = ? AND Active = ? ORDER BY Scan DESC";
            $row = $this->db->get_row($query, ["Uploaded", "Y"]);

            if($row){
                $this->updateStatus("Indexing", $row->Scan);
            }
        }
        return $row;
    }
    public function updateStatus($status, $scan){
        $query = "UPDATE [Employee_PDF] SET [Status] = ?, Modified_By = ?, Modified_DtTm = ? WHERE Scan = ?";
        $row = $this->db->query($query, [$status, user_id(), date_time(), $scan], false);
        return true;
    }
    
    public function getPdfsByEmployeeID($id){
        $query = "SELECT * FROM [Employee_PDF] WHERE Employee_ID = ?";
        $results = $this->db->get_results($query, [$id]);
        return $results;
    }

    public function getPdfByID($id){
        $query = "SELECT * FROM [Employee_PDF] WHERE Scan = ?";
        $row = $this->db->get_row($query, [$id]);
        return $row;
    }

	public function addIndex($data) 
	{
		$what  = array(
            "Employee_ID", "Name", "Gender", "Date_of_Birth", "Department", "Designation", 
            "Location", "Date_of_Joining", "Date_of_Leaving", "Employee_Category", 
            "Resignation_Type", "Reason_of_Turnover", "Remarks"
        );
		$where  = array("Scan");
		$params = array(
            $data['Employee_ID'] ?: null, 
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
            $data['Scan']
        );
        $this->db->update("[HR].[dbo].[Employee_PDF]", $what, $where, $params);
		if($this->db->rows_effected){
            $this->updateStatus( "Indexed", $data['Scan'] );
            return $data['Employee_ID'];
		}else{
			throw new Exception("Error: Indexing Failed..");
		}
	}

}