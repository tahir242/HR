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
        $department  = $this->checkDepartment($data['Department']);
        $designation = $this->checkDesignation($data['Designation']);
		$what  = array("Employee_ID", "Name", "Department", "Designation", "Date_of_Joining");
		$where  = array("Scan");
		$params = array($data['Employee_ID'], ucwords($data['Employee_Name']), $department, $designation, $data['DOJ'], $data['Scan'],);
        $this->db->update("[HR].[dbo].[Employee_PDF]", $what, $where, $params);
		if($this->db->rows_effected){
            $this->updateStatus( "Indexed", $data['Scan'] );
            return $data['Employee_ID'];
		}else{
			throw new Exception("Error: Indexing Failed..");
		}
	}

	public function checkDepartment($department)
	{
        if($department){
            $stmt = "SELECT * FROM [Department] WHERE Department = ?";
            $row = $this->db->get_row($stmt, [ucwords($department)]);
            if(!$row){
                $field = ["Department", "Created_By"];
                $this->db->insert("[Department]", $field, [ucwords($department), user_id()]);
                $stmt = "SELECT TOP 1 * FROM [Department] ORDER BY Department_ID DESC";
                $row = $this->db->get_row($stmt, []);
                return $row->Department_ID;
            }
            return $row->Department_ID;
        }else{
            return null;
        }
	}

	public function checkDesignation($designation)
	{
        if($designation){
            $stmt = "SELECT * FROM [Designation] WHERE Designation = ?";
            $row = $this->db->get_row($stmt, [ucwords($designation)]);
            if(!$row){
                $field = ["Designation", "Created_By"];
                $this->db->insert("[Designation]", $field, [ucwords($designation), user_id()]);
                $stmt = "SELECT TOP 1 * FROM [Designation] ORDER BY Designation_ID DESC";
                $row = $this->db->get_row($stmt, []);
                return $row->Designation_ID;
            }
            return $row->Designation_ID;
        }else{
            return null;
        }
	}

}