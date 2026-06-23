<?php

class ModelResignationType extends Model
{
    public function addResignationType($data)
    {
        $field = array("[Resignation_Type]", "[Active]", "[Created_By]");
        $params = array($data['Resignation_Type'], $data['Active'], user_id());
        $this->db->insert("[Resignation_Type]", $field, $params);
        $query = "SELECT TOP 1 [Resignation_Type_ID] FROM [Resignation_Type] ORDER BY [Resignation_Type_ID] DESC";
        $row = $this->db->get_row($query, []);
        return $row->Resignation_Type_ID;
    }

    public function editResignationType($id, $data)
    {
        $what = array("[Resignation_Type]", "[Active]");
        $where = array("Resignation_Type_ID");
        $params = array($data['Resignation_Type'], $data['Active'], $id);
        $this->db->update("[Resignation_Type]", $what, $where, $params);

        if ($this->db->rows_effected) {
            return $id;
        } else {
            throw new Exception("Error Resignation Type Updating Failed..");
        }
    }

    public function getResignationType($id)
    {
        $query = "SELECT * FROM [Resignation_Type] WHERE [Resignation_Type_ID] = ?";
        $row = db()->get_row($query, [$id]);
        return $row;
    }

    public function checkAssigned($id)
    {
        $tables = array('Employee_PDF');
        foreach ($tables as $table) {
            $query = "SELECT COUNT(*) as total FROM [$table] WHERE [Resignation_Type] = ?";
            $row = $this->db->get_row($query, [$id]);
            if ($row && $row->total > 0) {
                return true;
            }
        }
        return false;
    }

    public function deleteResignationType($id, $shift_id = null)
    {
        if ($this->checkAssigned($id)) {
            if ($shift_id) {
                $tables = array('Employee_PDF');
                foreach ($tables as $table) {
                    $what = array("[Resignation_Type]");
                    $where = array("Resignation_Type");
                    $params = array($shift_id, $id);
                    $this->db->update("[$table]", $what, $where, $params);
                }
            } else {
                throw new Exception("This Resignation Type is assigned to an Employee or Employee_PDF. Please shift the data first to another value then delete it.");
            }
        }
        $where = array("Resignation_Type_ID");
        $params = array($id);
        $this->db->delete("[Resignation_Type]", $where, $params);
    }

    public function getResignationTypes($data = array())
    {
        $sql = "SELECT * FROM [Resignation_Type] WHERE 1=1 ";

        $sort_data = array(
            'Resignation_Type',
            'Resignation_Type_ID',
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY [Resignation_Type_ID]";
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

