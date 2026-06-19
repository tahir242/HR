<?php

class ModelField extends Model 
{

	private $table	= "[d_Field]";

	public function getField($id) 
	{
	    $statement = "SELECT * FROM [d_Field] WHERE [Field_ID] = ?";
		$row  = $this->db->get_row($statement, [$id]);
  		return $row;
	}

	public function getFields($data = array()) 
	{
		$sql = "SELECT * FROM [d_Field] WHERE Active  = ?";

		if (isset($data['filter_type'])) {
			$sql .= " AND [Type] = '" . $data['filter_type'] . "'";
		}

		$sort_data = array(
			'Sort'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY [Sort]";
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

		return $this->db->get_results($sql, ['Y']);
	}

}