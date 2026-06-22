<?php

class ModelDesignation extends Model
{
    public function addDesignation($data)
    {
        $field = array("[Designation]", "[Active]", "[Created_By]");
        $params = array($data['Designation'], $data['Active'], user_id());
        $this->db->insert("[Designation]", $field, $params);
        $query = "SELECT TOP 1 [Designation_ID] FROM [Designation] ORDER BY [Designation_ID] DESC";
        $row = $this->db->get_row($query, []);
        return $row->Designation_ID;
    }

    public function editDesignation($id, $data)
    {
        $what = array("[Designation]", "[Active]");
        $where = array("Designation_ID");
        $params = array($data['Designation'], $data['Active'], $id);
        $this->db->update("[Designation]", $what, $where, $params);

        if ($this->db->rows_effected) {
            return $id;
        } else {
            throw new Exception("Error Designation Updating Failed..");
        }
    }

    public function getDesignation($id)
    {
        $query = "SELECT * FROM [Designation] WHERE [Designation_ID] = ?";
        $row = db()->get_row($query, [$id]);
        return $row;
    }

    public function checkAssigned($id)
    {
        $tables = array('Employee', 'Employee_PDF');
        foreach ($tables as $table) {
            $query = "SELECT COUNT(*) as total FROM [$table] WHERE [Designation] = ?";
            $row = $this->db->get_row($query, [$id]);
            if ($row && $row->total > 0) {
                return true;
            }
        }
        return false;
    }

    public function deleteDesignation($id, $shift_id = null)
    {
        if ($this->checkAssigned($id)) {
            if ($shift_id) {
                $tables = array('Employee', 'Employee_PDF');
                foreach ($tables as $table) {
                    $what = array("[Designation]");
                    $where = array("Designation");
                    $params = array($shift_id, $id);
                    $this->db->update("[$table]", $what, $where, $params);
                }
            } else {
                throw new Exception("This Designation is assigned to an Employee or Employee_PDF. Please shift the data first to another value then delete it.");
            }
        }
        $where = array("Designation_ID");
        $params = array($id);
        $this->db->delete("[Designation]", $where, $params);
    }


    public function getDesignations($data = array())
    {
        $sql = "SELECT * FROM [Designation] WHERE 1=1 ";

        $sort_data = array(
            'Designation',
            'Designation_ID',
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY [Designation_ID]";
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
