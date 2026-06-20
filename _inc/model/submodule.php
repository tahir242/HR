<?php

class ModelSubmodule extends Model 
{

	public function addSubModule($data) 
	{

		$field 	= array("Sub_Module_ID", "Sub_Module", "Url", "Show_In_Menu", "Active", "Sort", "Module_ID", "Created_By");
		$params = array(strtoupper($data['Sub_Module_ID']), $data['Sub_Module'], $data['Url'], $data['Show_In_Menu'], $data['Active'], $data['Sort'], $data['Module_ID'], user_id());
    	$this->db->insert("[HR].[dbo].[Sub_Module]", $field, $params);

		$query  = "SELECT TOP 1 Sub_Module_URN FROM [HR].[dbo].[Sub_Module] ORDER BY Sub_Module_URN DESC";
		$param  = array();
		$row	= $this->db->get_row($query, $param);

		$insertQuery = "INSERT INTO Sub_Module (Sub_Module_URN, Sub_Module_ID, Sub_Module, Url, Show_In_Menu, Active, Sort, Module_ID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
		$insertStmt = dblite()->prepare($insertQuery);
		$insertStmt->execute(array($row->Sub_Module_URN, strtoupper($data['Sub_Module_ID']), $data['Sub_Module'], $data['Url'], $data['Show_In_Menu'], $data['Active'], $data['Sort'], $data['Module_ID']));

		return $row->Sub_Module_URN;
	}

	public function editSubModule($Sub_Module_URN, $data) 
	{

		$what 		= array("Sub_Module_ID", "Sub_Module", "Url", "Show_In_Menu", "Active", "Sort", "Module_ID", "Modified_By", "Modified_DtTm");
		$where 		= array("Sub_Module_URN");
		$params 	= array(strtoupper($data['Sub_Module_ID']), $data['Sub_Module'], $data['Url'], $data['Show_In_Menu'], $data['Active'], $data['Sort'], $data['Module_ID'], user_id(), date_time(), $Sub_Module_URN);
		$this->db->update("[HR].[dbo].[Sub_Module]", $what, $where, $params);

		if($this->db->rows_effected){

			$updateQuery = "UPDATE Sub_Module SET Sub_Module_ID = ?, Sub_Module = ?, Url = ?, Show_In_Menu = ?, Active = ?, Sort = ?, Module_ID = ? WHERE Sub_Module_URN = ?";
			$updateStmt = dblite()->prepare($updateQuery);
			$updateStmt->execute(array(strtoupper($data['Sub_Module_ID']), $data['Sub_Module'], $data['Url'], $data['Show_In_Menu'], $data['Active'], $data['Sort'], $data['Module_ID'], $Sub_Module_URN));

			return $Sub_Module_URN;
		}else{
			throw new Exception("Error Sub Module Updating Failed..");
		}

	}

	public function getSubModule($Sub_Module_URN)
	{
		$query = "SELECT * FROM Sub_Module WHERE Sub_Module_URN = ?";
		$stmt = dblite()->prepare($query);
		$stmt->execute([$Sub_Module_URN]);
		$row = $stmt->fetch(PDO::FETCH_OBJ);
		return $row;
	}

	public function getSubModuleBySubModuleID($Sub_Module_ID)
	{
		$query = "SELECT * FROM Sub_Module WHERE Sub_Module_ID = ?";
		$stmt = dblite()->prepare($query);
		$stmt->execute([$Sub_Module_ID]);
		$row = $stmt->fetch(PDO::FETCH_OBJ);
		return $row;
	}

	public function getSubModules($data = array())
	{
		$sql = "SELECT * FROM Sub_Module WHERE 1=1 AND Active = ?";

		$sort_data = array(
			'Sub_Module',
			'Sub_Module_ID',
			'Module_ID',
			'Sort'
		);

		if (isset($data['filter_module'])) {
			$sql .= " AND Module_ID = '" . $data['filter_module'] . "'";
		}

		if (isset($data['filter_menu'])) {
			$sql .= " AND Show_In_Menu = " . $data['filter_menu'] . "";
		}

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY Sub_Module_ID";
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
		$modules = $stmt->fetchAll(PDO::FETCH_OBJ);
		return $modules;
	}
	
	public function countSubModuleByModuleID($Module_ID){

		$query = "SELECT COUNT(1) AS total FROM Sub_Module WHERE Module_ID = ?";
		$stmt = dblite()->prepare($query);
		$stmt->execute([$Module_ID]);
		$row = $stmt->fetch(PDO::FETCH_OBJ);
		return $row;
	}

}
