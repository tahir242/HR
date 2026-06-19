<?php

class ModelDictionary extends Model 
{

	public function getDepartment($id) 
	{
	    $statement = "SELECT * FROM [HR].[dbo].[Department] WHERE [Department_ID] = ?";
		$row  = $this->db->get_row($statement, [$id]);
  		return $row;
	}

	public function getDesignation($id) 
	{
	    $statement = "SELECT * FROM [HR].[dbo].[Designation] WHERE [Designation_ID] = ?";
		$row  = $this->db->get_row($statement, [$id]);
  		return $row;
	}

	public function getDepartments($data = array()) 
	{
		$sql = "SELECT * FROM [Department] WHERE Active  = ?";

		$sort_data = array(
			'Department'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY [Department]";
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

			$sql .= "OFFSET ".(int)$data['start']." ROWS FETCH NEXT ".(int)$data['limit']." ROWS ONLY";
		}

		return $this->db->get_results($sql, [1]);
	}

	public function getDesignations($data = array()) 
	{
		$sql = "SELECT * FROM [Designation] WHERE Active  = ?";

		$sort_data = array(
			'Designation'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY [Designation]";
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

			$sql .= "OFFSET ".(int)$data['start']." ROWS FETCH NEXT ".(int)$data['limit']." ROWS ONLY";
		}

		return $this->db->get_results($sql, [1]);
	}

}