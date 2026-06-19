<?php

class User
{
	private $id;
	private $empid;
	private $empinitial;
	private $username;
	private $location;
	private $sso;
	private $db;
	private $dblite;
	private $permission = array();
	private $group_id;
	private $group;
	private $session;

	public function __construct()
	{
		$this->db = registry()->get('db');
		$this->dblite = registry()->get('dblite');
		$this->request = registry()->get('request');
		$this->session = registry()->get('session');
		$this->sso = registry()->get('sso');

		if ($this->sso) {
			$this->id = $this->sso->data->UserID;
			$this->empid = $this->sso->data->EmpID;
			$this->empinitial = $this->sso->data->EmpInitial;
			$this->username = $this->sso->data->Fullname;
		
			$query = "SELECT r.Role_ID, r.Role FROM User_Role sr LEFT JOIN [Role] r ON sr.Role_ID = r.Role_ID WHERE sr.User_ID = ?";
			$stmt = dblite()->prepare($query);
			$stmt->execute([$this->id]);
			$row = $stmt->fetch(PDO::FETCH_OBJ);

			if ($row) {
				$this->group_id = $row->Role_ID;
				$this->group = $row->Role;
				$this->location = $row->Role_ID;

				$query = "SELECT * FROM User_Permission WHERE User_ID = ?";
				$stmt = dblite()->prepare($query);
				$stmt->execute([$this->id]);
				$permissions = $stmt->fetchAll(PDO::FETCH_OBJ);

				if ($permissions) {
					foreach ($permissions as $permission) {
						$this->permission[$permission->Permission_ID] = $permission->Active;
					}
				}


			} else {
				header('Content-Type: application/json');
				echo json_encode(array('msg' => "You have not assigned any Role. Please contact the application administrator"));
				exit();
			}

		}
	}

	public function isLogged()
	{
		return $this->id;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getName()
	{
		return $this->username;
	}

	public function getEmpID()
	{
		return $this->empid;
	}

	public function getEmpInitial()
	{
		return $this->empinitial;
	}

	public function getUserDetail($field = false)
	{
		if ($field) {
			return $this->sso->data->$field;
		} else {
			return $this->sso->data;
		}
	}

	public function getRoleId()
	{
		return $this->group_id;
	}

	public function getRole()
	{
		return $this->group;
	}

	public function getLocation()
	{
		return trim($this->location);
	}

	public function hasPermission($key, $value)
	{
		if (isset($this->permission[$value])) {
			return isset($this->permission[$value]) && $this->permission[$value] == 1 ? true : false;
		} else {
			return false;
		}
	}

}