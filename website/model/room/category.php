<?php
class ModelRoomCategory extends Model {

	public function getCategory($room_category_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "room_category c LEFT JOIN " . DB_PREFIX . "room_category_description cd ON (c.room_category_id = cd.room_category_id) LEFT JOIN " . DB_PREFIX . "room_category_to_store c2s ON (c.room_category_id = c2s.room_category_id) WHERE c.room_category_id = '" . (int)$room_category_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1'");

		return $query->row;
	}

	/**
	 * 取得指定條件的分類資料集
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public function getCategories($data = array()) {
		$sql = "SELECT bcp.room_category_id AS room_category_id, cd1.name AS name, (SELECT COUNT(*) FROM " . DB_PREFIX."room_to_category ba2c WHERE ba2c.room_category_id = bcp.room_category_id) AS total, rpc.*, c1.parent_id, c1.sort_order,i.path,i.image FROM " . DB_PREFIX . "room_category_path bcp LEFT JOIN " . DB_PREFIX . "room_category c1 ON (bcp.room_category_id = c1.room_category_id) LEFT JOIN " . DB_PREFIX."room_price_category rpc ON(bcp.room_category_id = rpc.room_category_id) LEFT JOIN " . DB_PREFIX . "room_category_description cd1 ON (bcp.path_id = cd1.room_category_id) LEFT JOIN " . DB_PREFIX . "room_category_description cd2 ON (bcp.room_category_id = cd2.room_category_id) LEFT JOIN " . DB_PREFIX . "image i ON (c1.image_id = i.image_id) WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND cd2.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sql .= " GROUP BY bcp.room_category_id";

		$sort_data = array(
			'name',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY sort_order";
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

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}


	/**
	 * [getTotalCategories description]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function getTotalCategories($data = array()) {
      	$sql = "SELECT COUNT(bc.room_category_id) AS total FROM " . DB_PREFIX . "room_category bc LEFT JOIN " . DB_PREFIX . "room_category_description bcd ON (bc.room_category_id = bcd.room_category_id) WHERE bcd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		if (!empty($data['filter_name'])) {
			$sql .= " AND bcd.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}
		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	public function getCategoryPrice($room_category_id)
	{
		$sql = "SELECT * FROM " . DB_PREFIX . "room_price_category WHERE room_category_id = " . $room_category_id . "";
		$query = $this->db->query($sql);
		return $query->row;
	}

}