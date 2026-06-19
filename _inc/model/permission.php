<?php

class ModelPermission extends Model 
{

	public function addPermission($data) 
	{

		$field 		= array("Permission_ID", "Permission", "Active", "Sub_Module_ID", "Created_By");
		$params 	= array(strtolower(str_replace(" ", "_", $data['Permission_ID'])), $data['Permission'], $data['Active'], strtoupper($data['Sub_Module_ID']), user_id());
    	$this->db->insert("[HR].[dbo].[Permission]", $field, $params);

		$query  = "SELECT TOP 1 Permission_URN FROM [HR].[dbo].[Permission] ORDER BY Permission_URN DESC";
		$param  = array();
		$row	= $this->db->get_row($query, $param);

		$insertQuery = "INSERT INTO Permission (Permission_URN, Permission_ID, Permission, Active, Sub_Module_ID) VALUES (?, ?, ?, ?, ?)";
		$insertStmt = dblite()->prepare($insertQuery);
		$insertStmt->execute([$row->Permission_URN, strtolower($data['Permission_ID']), $data['Permission'], $data['Active'], strtoupper($data['Sub_Module_ID'])]);

		return $row->Permission_URN;
	}

	public function editPermission($Permission_URN, $data) 
	{
		$what 		= array("Permission_ID", "Permission", "Active", "Sub_Module_ID", "Modified_By", "Modified_DtTm");
		$where 		= array("Permission_URN");
		$params 	= array($data['Permission_ID'], $data['Permission'], $data['Active'], strtoupper($data['Sub_Module_ID']), user_id(), date_time(), $Permission_URN);
		$this->db->update("[HR].[dbo].[Permission]", $what, $where, $params);

		if($this->db->rows_effected){

			$updateQuery = "UPDATE Permission SET Permission_ID = ?, Permission = ?, Active = ?, Sub_Module_ID = ? WHERE Permission_URN = ?";
			$updateStmt = dblite()->prepare($updateQuery);
			$updateStmt->execute([$data['Permission_ID'], $data['Permission'], $data['Active'], strtoupper($data['Sub_Module_ID']), $Permission_URN]);

			return $Permission_URN;
		}else{
			throw new Exception("Error Permission Updating Failed..");
		}

	}

	public function getPermission($Permission_URN)
	{
		$query = "SELECT * FROM Permission WHERE Permission_URN = ?";
		$stmt = dblite()->prepare($query);
		$stmt->execute([$Permission_URN]);
		$row = $stmt->fetch(PDO::FETCH_OBJ);
		return $row;
	}

	public function getPermissions($data = array()) 
	{
		$sql = "SELECT * FROM Permission WHERE 1=1 AND Active = ?"; 

		if (isset($data['filter_name'])) {
			$sql .= " AND Permission LIKE '" . $data['filter_name'] . "%'";
		}

		if (isset($data['filter_submodule'])) {
			$sql .= " AND Sub_Module_ID = '" . $data['filter_submodule'] . "'";
		}

		$sort_data = array(
			'Permission'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY Permission_URN";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$stmt = dblite()->prepare($sql);
		$stmt->execute([1]);
		$permissions = $stmt->fetchAll(PDO::FETCH_OBJ);
		return $permissions;
	}

	public function coundPermissionBySubModuleID($Sub_Module_ID){

		$query = "SELECT COUNT(1) AS total FROM Permission WHERE Sub_Module_ID = ?";
		$stmt = dblite()->prepare($query);
		$stmt->execute([$Sub_Module_ID]);
		$row = $stmt->fetch(PDO::FETCH_OBJ);
		return $row;
	}

}