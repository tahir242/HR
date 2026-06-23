<?php

class ModelEmployeeCategory extends Model
{
    public function addEmployeeCategory($data)
    {
        $field = array("[Employee_Category]", "[Active]", "[Created_By]");
        $params = array($data['Employee_Category'], $data['Active'], user_id());
        $this->db->insert("[Employee_Category]", $field, $params);
        $query = "SELECT TOP 1 [Category_ID] FROM [Employee_Category] ORDER BY [Category_ID] DESC";
        $row = $this->db->get_row($query, []);
        return $row->Category_ID;
    }

    public function editEmployeeCategory($id, $data)
    {
        $what = array("[Employee_Category]", "[Active]");
        $where = array("Category_ID");
        $params = array($data['Employee_Category'], $data['Active'], $id);
        $this->db->update("[Employee_Category]", $what, $where, $params);

        if ($this->db->rows_effected) {
            return $id;
        } else {
            throw new Exception("Error Employee Category Updating Failed..");
        }
    }

    public function getEmployeeCategory($id)
    {
        $query = "SELECT * FROM [Employee_Category] WHERE [Category_ID] = ?";
        $row = db()->get_row($query, [$id]);
        return $row;
    }

    public function checkAssigned($id)
    {
        $tables = array('Employee_PDF');
        foreach ($tables as $table) {
            $query = "SELECT COUNT(*) as total FROM [$table] WHERE [Employee_Category] = ?";
            $row = $this->db->get_row($query, [$id]);
            if ($row && $row->total > 0) {
                return true;
            }
        }
        return false;
    }

    public function deleteEmployeeCategory($id, $shift_id = null)
    {
        if ($this->checkAssigned($id)) {
            if ($shift_id) {
                $tables = array('Employee_PDF');
                foreach ($tables as $table) {
                    $what = array("[Employee_Category]");
                    $where = array("Employee_Category");
                    $params = array($shift_id, $id);
                    $this->db->update("[$table]", $what, $where, $params);
                }
            } else {
                throw new Exception("This Employee Category is assigned to an Employee or Employee_PDF. Please shift the data first to another value then delete it.");
            }
        }
        $where = array("Category_ID");
        $params = array($id);
        $this->db->delete("[Employee_Category]", $where, $params);
    }

    public function getEmployeeCategories($data = array())
    {
        $sql = "SELECT * FROM [Employee_Category] WHERE 1=1 ";

        $sort_data = array(
            'Employee_Category',
            'Category_ID',
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY [Category_ID]";
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

        return $this->db->get_results($sql, []);
    }
}

