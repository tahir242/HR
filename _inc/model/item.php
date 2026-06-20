<?php

class ModelItem extends Model
{

    public function addItem($data)
    {
        $field = array("[Year]", "[Item_Name]", "[Unit]", "[Packing_Unit]", "[Issue_Qty]", "[Created_By]");
        $params = array(current_year(), $data['Item_Name'], ucwords($data['Unit']), $data['Packing_Unit'], $data['Issue_Qty'], user_id());
        $this->db->insert("[Ration_Item]", $field, $params);
        $query = "SELECT TOP 1 [Item_ID] FROM [Ration_Item] ORDER BY [Item_ID] DESC";
        $row = $this->db->get_row($query, []);
        return $row->Item_ID;
    }

    public function editItem($id, $data)
    {
        $what = array("[Item_Name]", "[Unit]", "[Packing_Unit]", "[Issue_Qty]", "[Modified_By]", "[Modified_DtTm]");
        $where = array("Item_ID");
        $params = array($data['Item_Name'], ucwords($data['Unit']), $data['Packing_Unit'], $data['Issue_Qty'], user_id(), date_time(), $id);
        $this->db->update("[Ration_Item]", $what, $where, $params);

        if ($this->db->rows_effected) {
            return $id;
        } else {
            throw new Exception("Error Item Updating Failed..");
        }

    }

    public function getItem($id)
    {
        $query = "SELECT * FROM [Ration_Item] WHERE [Item_ID] = ?";
        $row = db()->get_row($query, [$id]);
        return $row;
    }

    public function getItems($data = array())
    {
        $sql = "SELECT * FROM [Ration_Item] WHERE 1=1 ";

        $sort_data = array(
            'Item_Name',
            'Item_ID',
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY [Item_ID]";
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

    public function updateItemBalance($id, $qty)
    {
        $what = array("[Balance]");
        $where = array("Item_ID");
        $params = array($qty, $id);
        $this->db->update("[Ration_Item]", $what, $where, $params);
    }

    public function addReceiveItem($data)
    {
        $field = array("[Year]", "[Employee_ID]", "[Item_ID]", "[Received_Qty]", "[Transaction_By]");
        $params = array(current_year(), 0, $data['Item_ID'], $data['Received_Qty'], user_id());
        $this->db->insert("[Ration_Transaction]", $field, $params);

		if ($this->db->rows_effected) {
            $query = "SELECT TOP 1 * FROM [Ration_Transaction] ORDER BY [Transaction_ID] DESC";
            $row = $this->db->get_row($query, []);
            $item = $this->getItem($row->Item_ID);
            $qty = (int)$item->Balance + (int)$row->Received_Qty;
            $this->updateItemBalance($row->Item_ID, $qty);
            return $row->Transaction_ID;
		} else {
			throw new Exception("Inserting Item Failed..");
		}

    }

    public function editReceiveItem($id, $data)
    {

        $query   = "SELECT TOP 1 * FROM [Ration_Transaction] WHERE [Transaction_ID] = ? ORDER BY [Transaction_ID] DESC";
        $preT    = $this->db->get_row($query, [$id]);
        $preitem = $this->getItem($preT->Item_ID);

        $what = array("[Item_ID]", "[Received_Qty]", "[Modified_By]", "[Modified_DtTm]");
        $where = array("Transaction_ID");
        $params = array($data['Item_ID'], $data['Received_Qty'], user_id(), date_time(), $id);
        $this->db->update("[Ration_Transaction]", $what, $where, $params);

        if ($this->db->rows_effected) {
            $qtyMinus = (int)$preitem->Balance - (int)$preT->Received_Qty;
            $this->updateItemBalance($preT->Item_ID, $qtyMinus);

            $query = "SELECT TOP 1 * FROM [Ration_Transaction] WHERE [Transaction_ID] = ? ORDER BY [Transaction_ID] DESC";
            $row = $this->db->get_row($query, [$id]);
            $item = $this->getItem($row->Item_ID);
            $qty = (int)$item->Balance + (int)$row->Received_Qty;
            $this->updateItemBalance($row->Item_ID, $qty);
            return $id;
        } else {
            throw new Exception("Error Item Updating Failed..");
        }

    }


}
