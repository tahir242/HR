<?php
class ModelModule extends Model 
{

	public function addModule($data) 
	{
		$field = array("Module_ID", "Module", "Icon", "Url", "Has_Sub_Menu", "Active", "Sort", "Created_By");
		$params = array(strtoupper($data['Module_ID']), $data['Module'], $data['Icon'], $data['Url'], $data['Has_Sub_Menu'], $data['Active'], $data['Sort'], user_id());
    	$this->db->insert("[HR].[dbo].[Module]", $field, $params);

		$query  = "SELECT TOP 1 Module_URN FROM [HR].[dbo].[Module] ORDER BY Module_URN DESC";
		$param  = array();
		$row	= $this->db->get_row($query, $param);

		$insertQuery = "INSERT INTO Module (Module_URN, Module_ID, Module, Icon, Url, Has_Sub_Menu, Active, Sort) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
		$insertStmt = dblite()->prepare($insertQuery);
		$insertStmt->execute(array($row->Module_URN, strtoupper($data['Module_ID']), $data['Module'], $data['Icon'], $data['Url'], $data['Has_Sub_Menu'], $data['Active'], $data['Sort']));
		return $row->Module_URN;
	}

	public function editModule($Module_URN, $data) 
	{

		$what 		= array("Module_ID", "Module", "Icon", "Url", "Has_Sub_Menu", "Active", "Sort", "Modified_By", "Modified_DtTm");
		$where 		= array("Module_URN");
		$params 	= array(strtoupper($data['Module_ID']), $data['Module'], $data['Icon'], $data['Url'], $data['Has_Sub_Menu'], $data['Active'], $data['Sort'], user_id(), date_time(), $Module_URN);
		$this->db->update("[HR].[dbo].[Module]", $what, $where, $params);

		if($this->db->rows_effected){

			$updateQuery = "UPDATE Module SET Module_ID = ?, Module = ?, Icon = ?, Url = ?, Has_Sub_Menu = ?, Active = ?, Sort = ? WHERE Module_URN = ?";
			$updateStmt = dblite()->prepare($updateQuery);
			$updateStmt->execute(array(strtoupper($data['Module_ID']), $data['Module'], $data['Icon'], $data['Url'], $data['Has_Sub_Menu'], $data['Active'], $data['Sort'], $Module_URN));

			return $Module_URN;
		}else{
			throw new Exception("Error Module Updating Failed..");
		}

	}
	
	public function getModule($Module_URN)
	{
		$query = "SELECT * FROM Module WHERE Module_URN = ? LIMIT 1";
		$stmt = dblite()->prepare($query);
		$stmt->execute([$Module_URN]);
		$Module = $stmt->fetch(PDO::FETCH_OBJ);
		return $Module;
	}
	public function getModuleByModuleID($ModuleID)
	{
		$query = "SELECT * FROM Module WHERE Module_ID = ?";
		$stmt = dblite()->prepare($query);
		$stmt->execute([$ModuleID]);
		$Module = $stmt->fetch(PDO::FETCH_OBJ);
		return $Module;
	}

	public function getModules($data = array())
	{
		$sql = "SELECT * FROM Module WHERE 1=1 AND Active = ?";

		if (isset($data['filter_name'])) {
			$sql .= " AND Module LIKE '" . $data['filter_name'] . "%'";
		}

		if (isset($data['has_submodules'])) {
			$sql .= " AND Has_Sub_Menu = " . $data['has_submodules'] . "";
		}

		$sort_data = array(
			'Module',
			'Sort'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY Module_URN";
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

}
