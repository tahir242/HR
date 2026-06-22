<?php

class ModelDepartment extends Model
{
    public function addDepartment($data)
    {
        $field = array("[Department]", "[Active]", "[Created_By]");
        $params = array($data['Department'], $data['Active'], user_id());
        $this->db->insert("[Department]", $field, $params);
        $query = "SELECT TOP 1 [Department_ID] FROM [Department] ORDER BY [Department_ID] DESC";
        $row = $this->db->get_row($query, []);
        return $row->Department_ID;
    }

    public function editDepartment($id, $data)
    {
        $what = array("[Department]", "[Active]", "[Modified_By]", "[Modified_DtTm]");
        $where = array("Department_ID");
        $params = array($data['Department'], $data['Active'], user_id(), date_time(), $id);
        $this->db->update("[Department]", $what, $where, $params);

        if ($this->db->rows_effected) {
            return $id;
        } else {
            throw new Exception("Error Department Updating Failed..");
        }
    }

    public function getDepartment($id)
    {
        $query = "SELECT * FROM [Department] WHERE [Department_ID] = ?";
        $row = db()->get_row($query, [$id]);
        return $row;
    }

    public function checkAssigned($id)
    {
        $tables = array('Employee', 'Employee_PDF');
        foreach ($tables as $table) {
            $query = "SELECT COUNT(*) as total FROM [$table] WHERE [Department] = ?";
            $row = $this->db->get_row($query, [$id]);
            if ($row && $row->total > 0) {
                return true;
            }
        }
        return false;
    }

    public function deleteDepartment($id, $shift_id = null)
    {
        if ($this->checkAssigned($id)) {
            if ($shift_id) {
                $tables = array('Employee', 'Employee_PDF');
                foreach ($tables as $table) {
                    $what = array("[Department]");
                    $where = array("Department");
                    $params = array($shift_id, $id);
                    $this->db->update("[$table]", $what, $where, $params);
                }
            } else {
                throw new Exception("This Department is assigned to an Employee or Employee_PDF. Please shift the data first to another value then delete it.");
            }
        }
        $where = array("Department_ID");
        $params = array($id);
        $this->db->delete("[Department]", $where, $params);
    }


    public function getDepartments($data = array())
    {
        $sql = "SELECT * FROM [Department] WHERE 1=1 ";

        $sort_data = array(
            'Department',
            'Department_ID',
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY [Department_ID]";
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
