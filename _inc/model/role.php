<?php

class ModelRole extends Model 
{

	public function addRole($data) 
	{
		$field 		= array("Role", "Active", "Created_By");
		$params 	= array($data['Role'], $data['Active'], user_id());
    	$this->db->insert("[HR].[dbo].[Role]", $field, $params);

		$query  = "SELECT TOP 1 Role_ID FROM [HR].[dbo].[Role] ORDER BY Role_ID DESC";
		$param  = array();
		$row	= $this->db->get_row($query, $param);

		$insertQuery = "INSERT INTO [Role] (Role_ID, [Role], Active) VALUES (?, ?, ?)";
		$insertStmt = dblite()->prepare($insertQuery);
		$insertStmt->execute(array($row->Role_ID, $data['Role'], $data['Active']));

		return $row->Role_ID;
	}

	public function editRole($Role_ID, $data) 
	{

		$what 	= array("Role", "Active", "Modified_By", "Modified_DtTm");
		$where 	= array("Role_ID");
		$params = array($data['Role'], $data['Active'], user_id(), date_time(), $Role_ID);
		$this->db->update("[HR].[dbo].[Role]", $what, $where, $params);

		if($this->db->rows_effected){

			$updateQuery = "UPDATE [Role] SET [Role] = ?, Active = ? WHERE Role_ID = ?";
			$updateStmt = dblite()->prepare($updateQuery);
			$updateStmt->execute(array($data['Role'], $data['Active'], $Role_ID));

			return $Role_ID;
		}else{
			throw new Exception("Error Role Updating Failed..");
		}

	}
	
	public function getRole($Role_ID)
	{
		$query = "SELECT * FROM [Role] WHERE Role_ID = ?";
		$stmt = dblite()->prepare($query);
		$stmt->execute([$Role_ID]);
		$role = $stmt->fetch(PDO::FETCH_OBJ);
		return $role;
	}

	public function deleteRolePermission($Role_ID) 
	{

		$table  	= "[HR].[dbo].[Role_Permission]";
		$where  	= array("Role_ID");
		$params 	= array($Role_ID);
    	$statement  = $this->db->delete($table, $where, $params);

		$deleteQuery = "DELETE FROM Role_Permission WHERE Role_ID = ?";
		$deleteStmt = dblite()->prepare($deleteQuery);
		$deleteStmt->execute([$Role_ID]);

	}

	public function insertRolePermission($data, $permission) 
	{
		if(isset($permission['access'])){
			$field 		 = array("Role_ID", "Permission_ID", "Active", "Created_By", "Created_DtTm");
			$insertQuery = "INSERT INTO Role_Permission (Role_ID, Permission_ID, Active) VALUES (?, ?, ?)";
			foreach ($permission['access'] AS $key => $value){
				$params 	= array($data['Role_ID'], $key, 1, employee_id(), date_time());
				$this->db->insert("[HR].[dbo].[Role_Permission]", $field, $params);

				$insertStmt = dblite()->prepare($insertQuery);
				$insertStmt->execute([$data['Role_ID'], $key, 1]);

			}
		}

	}

	public function getRolePermission($Role_ID)
	{
		$query = "SELECT * FROM Role_Permission WHERE Role_ID = ?";
		$stmt = dblite()->prepare($query);
		$stmt->execute([$Role_ID]);
		$results = $stmt->fetchAll(PDO::FETCH_OBJ);
		return $results;
	}

	public function CountUserInRole($Role_ID){

		$query = "SELECT COUNT(1) AS total FROM User_Role WHERE Role_ID = ?";
		$stmt = dblite()->prepare($query);
		$stmt->execute([$Role_ID]);
		$row = $stmt->fetch(PDO::FETCH_OBJ);
		return $row;
	}

	public function getRoles()
	{
		$query = "SELECT * FROM [Role] WHERE Active = ?";
		$stmt = dblite()->prepare($query);
		$stmt->execute([1]);
		$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $roles;
	}

}