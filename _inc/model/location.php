<?php

class ModelLocation extends Model
{
    public function addLocation($data)
    {
        $field = array("[Location]", "[Active]", "[Created_By]");
        $params = array($data['Location'], $data['Active'], user_id());
        $this->db->insert("[Location]", $field, $params);
        $query = "SELECT TOP 1 [Location_ID] FROM [Location] ORDER BY [Location_ID] DESC";
        $row = $this->db->get_row($query, []);
        return $row->Location_ID;
    }

    public function editLocation($id, $data)
    {
        $what = array("[Location]", "[Active]");
        $where = array("Location_ID");
        $params = array($data['Location'], $data['Active'], $id);
        $this->db->update("[Location]", $what, $where, $params);

        if ($this->db->rows_effected) {
            return $id;
        } else {
            throw new Exception("Error Location Updating Failed..");
        }
    }

    public function getLocation($id)
    {
        $query = "SELECT * FROM [Location] WHERE [Location_ID] = ?";
        $row = db()->get_row($query, [$id]);
        return $row;
    }

    public function checkAssigned($id)
    {
        $tables = array('Employee', 'Employee_PDF');
        foreach ($tables as $table) {
            $query = "SELECT COUNT(*) as total FROM [$table] WHERE [Location] = ?";
            $row = $this->db->get_row($query, [$id]);
            if ($row && $row->total > 0) {
                return true;
            }
        }
        return false;
    }

    public function deleteLocation($id, $shift_id = null)
    {
        if ($this->checkAssigned($id)) {
            if ($shift_id) {
                $tables = array('Employee', 'Employee_PDF');
                foreach ($tables as $table) {
                    $what = array("[Location]");
                    $where = array("Location");
                    $params = array($shift_id, $id);
                    $this->db->update("[$table]", $what, $where, $params);
                }
            } else {
                throw new Exception("This Location is assigned to an Employee or Employee_PDF. Please shift the data first to another value then delete it.");
            }
        }
        $where = array("Location_ID");
        $params = array($id);
        $this->db->delete("[Location]", $where, $params);
    }


    public function getLocations($data = array())
    {
        $sql = "SELECT * FROM [Location] WHERE 1=1 ";

        $sort_data = array(
            'Location',
            'Location_ID',
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY [Location_ID]";
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

            $sql .= "OFFSET " . (int) $data['start'] . " ROWS FETCH NEXT " . (int) $data['limit'] . " ROWS ONLY";
        }

        return $this->db->get_results($sql, [1]);
    }
}
