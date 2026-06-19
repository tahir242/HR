<?php

class ModelUser extends Model 
{

	public function getUsers($HostID) 
	{
		$statement = "SELECT u.* FROM [SSO].[dbo].[Users] u LEFT JOIN [SSO].[dbo].[UserHost] uh ON uh.UserID = u.UserID WHERE uh.HostID = ?";
	  	$param = array($HostID);
		$users = $this->db->get_results($statement, $param);
		return $users;
	}

	public function getLocalUsers($data = array())
	{
		$sql = "SELECT * FROM Users WHERE Active = ?";
		$stmt = dblite()->prepare($sql);
		$stmt->execute([1]);
		$results = $stmt->fetchAll(PDO::FETCH_OBJ);
		return $results;
	}

	public function getLocalUser($UserID)
	{
		$sql = "SELECT * FROM Users WHERE UserID = ?";
		$stmt = dblite()->prepare($sql);
		$stmt->execute([$UserID]);
		$row = $stmt->fetch(PDO::FETCH_OBJ);
		return $row;
	}

	public function getUsersByRoleID($Role_ID)
	{
		$query = "SELECT * FROM User_Role WHERE Role_ID = ?";
		$stmt = dblite()->prepare($query);
		$stmt->execute([$Role_ID]);
		$result = $stmt->fetchAll(PDO::FETCH_OBJ);
		return $result;
	}

	public function getUserRoleByUserID($User_ID)
	{
		$query = "SELECT * FROM User_Role WHERE User_ID = ?";
		$stmt = dblite()->prepare($query);
		$stmt->execute([$User_ID]);
		$row = $stmt->fetch(PDO::FETCH_OBJ);
		return $row;
	}

	public function getUserRole($User_Role_ID)
	{
		$query = "SELECT * FROM User_Role WHERE User_Role_ID = ?";
		$stmt = dblite()->prepare($query);
		$stmt->execute([$User_Role_ID]);
		$row = $stmt->fetch(PDO::FETCH_OBJ);
		return $row;
	}

	public function assignUserRole($data)
	{
		$field 		= array("User_ID", "Role_ID", "Active", "Created_By", "Created_DtTm");
		$params 	= array($data['User_ID'], $data['Role_ID'], 1, user_id(), date_time());
    	$this->db->insert("[HR].[dbo].[User_Role]", $field, $params);
		$query  = "SELECT TOP 1 User_Role_ID FROM [HR].[dbo].[User_Role] ORDER BY User_Role_ID DESC";
		$param  = array();
		$row	= $this->db->get_row($query, $param);

		$insertQuery = "INSERT INTO User_Role (User_Role_ID, User_ID, Role_ID, Active) VALUES (?, ?, ?, ?)";
		$insertStmt = dblite()->prepare($insertQuery);
		$insertStmt->execute(array($row->User_Role_ID, $data['User_ID'], $data['Role_ID'], 1));

		return $row->User_Role_ID;
	}

	public function editassignUserRole($data) 
	{

		$what 	= array("Role_ID", "Active", "Modified_By", "Modified_DtTm");
		$where 	= array("User_ID");
		$params = array($data['Role_ID'], 1, user_id(), date_time(), $data['User_ID']);
		$this->db->update("[HR].[dbo].[User_Role]", $what, $where, $params);

		if($this->db->rows_effected){

			$updateQuery = "UPDATE User_Role SET Role_ID = ?, Active = ? WHERE User_ID = ?";
			$updateStmt = dblite()->prepare($updateQuery);
			$updateStmt->execute([$data['Role_ID'], 1, $data['User_ID']]);

			$query = "SELECT User_Role_ID FROM [User_Role] WHERE User_ID = ? ORDER BY User_Role_ID DESC LIMIT 1";
			$stmt = dblite()->prepare($query);
			$stmt->execute([$data['User_ID']]);
			$row = $stmt->fetch(PDO::FETCH_OBJ);

			return $row->User_Role_ID;
		}else{
			throw new Exception("Error Assgining User Role Failed..");
		}

	}

	public function getUserPermission($User_ID)
	{
		$query = "SELECT * FROM User_Permission WHERE User_ID = ?";
		$stmt = dblite()->prepare($query);
		$stmt->execute([$User_ID]);
		$result = $stmt->fetchAll(PDO::FETCH_OBJ);
		return $result;
	}

	public function getUsersPermissionID($Permission_ID)
	{
		$query = "SELECT * FROM User_Permission WHERE [Permission_ID] = ? AND Active = ?";
		$stmt = dblite()->prepare($query);
		$stmt->execute([$Permission_ID, 1]);
		$result = $stmt->fetchAll(PDO::FETCH_OBJ);
		return $result;
	}
}