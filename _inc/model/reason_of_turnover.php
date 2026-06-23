<?php

class ModelReasonOfTurnover extends Model
{
    public function addReasonOfTurnover($data)
    {
        $field = array("[Resignation_Type_ID]", "[Reason]", "[Active]", "[Created_By]");
        $params = array($data['Resignation_Type_ID'], $data['Reason'], $data['Active'], user_id());
        $this->db->insert("[Reason_of_Turnover]", $field, $params);
        $query = "SELECT TOP 1 [Reason_ID] FROM [Reason_of_Turnover] ORDER BY [Reason_ID] DESC";
        $row = $this->db->get_row($query, []);
        return $row->Reason_ID;
    }

    public function editReasonOfTurnover($id, $data)
    {
        $what = array("[Resignation_Type_ID]", "[Reason]", "[Active]", "[Modified_By]", "[Modified_DtTm]");
        $where = array("Reason_ID");
        $params = array($data['Resignation_Type_ID'], $data['Reason'], $data['Active'], user_id(), date_time(), $id);
        $this->db->update("[Reason_of_Turnover]", $what, $where, $params);

        if ($this->db->rows_effected) {
            return $id;
        } else {
            throw new Exception("Error Reason of Turnover Updating Failed..");
        }
    }

    public function getReasonOfTurnover($id)
    {
        $query = "SELECT * FROM [Reason_of_Turnover] WHERE [Reason_ID] = ?";
        $row = db()->get_row($query, [$id]);
        return $row;
    }

    public function checkAssigned($id)
    {
        $tables = array('Employee', 'Employee_PDF');
        foreach ($tables as $table) {
            $query = "SELECT COUNT(*) as total FROM [$table] WHERE [Reason] = ?";
            $row = $this->db->get_row($query, [$id]);
            if ($row && $row->total > 0) {
                return true;
            }
        }
        return false;
    }

    public function deleteReasonOfTurnover($id, $shift_id = null)
    {
        if ($this->checkAssigned($id)) {
            if ($shift_id) {
                $tables = array('Employee', 'Employee_PDF');
                foreach ($tables as $table) {
                    $what = array("[Reason]");
                    $where = array("Reason");
                    $params = array($shift_id, $id);
                    $this->db->update("[$table]", $what, $where, $params);
                }
            } else {
                throw new Exception("This Reason is assigned to an Employee or Employee_PDF. Please shift the data first to another value then delete it.");
            }
        }
        $where = array("Reason_ID");
        $params = array($id);
        $this->db->delete("[Reason_of_Turnover]", $where, $params);
    }


    public function getReasonOfTurnovers($data = array())
    {
        $sql = "SELECT * FROM [Reason_of_Turnover] WHERE 1=1 ";

        $sort_data = array(
            'Reason',
            'Reason_ID',
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY [Reason_ID]";
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
