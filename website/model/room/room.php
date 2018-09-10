<?php
class ModelRoomRoom extends Model {

	public function getRooms($data)	{
		$cache_name = md5(json_encode($data));
		$rooms_data = $this->cache->get('rooms.list' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $cache_name);

		if(!$rooms_data) {
			$sql = "SELECT r.room_id,r.room_category_id,r.image_id,rd.name,rp.price,rp.rest_price,rp.rest_time,i.image,i.path FROM " . DB_PREFIX . "room r LEFT JOIN " . DB_PREFIX . "room_description rd ON (r.room_id = rd.room_id) LEFT JOIN " . DB_PREFIX . "room_price rp ON (r.room_id = rp.room_id) LEFT JOIN " . DB_PREFIX . "image i ON (i.image_id = r.image_id) WHERE rd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
			if (!empty($data['filter_name'])) {
				$sql .= " AND rd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
			}

			if (isset($data['filter_status']) && $data['filter_status'] !== '') {
				$sql .= " AND r.status = '" . (int)$data['filter_status'] . "'";
			}

			$sql .= " GROUP BY r.room_id";

			$sort_data = array(
				'rd.name',
				'r.status',
				'r.sort_order'
				);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY r.sort_order";
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
			$rooms_data = $query->rows;
			$this->cache->set('rooms.list' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $cache_name,$rooms_data);
		}

		return $rooms_data;
	}

	public function getCtegoryLastRoom($room_category_id)	{
		$sql = "SELECT r2c.room_id,r2c.room_category_id,r.image_id,rd.name,rpc.price,rpc.rest_price,rpc.rest_time,i.image,i.path FROM " . DB_PREFIX . "room_to_category r2c LEFT JOIN " . DB_PREFIX . "room r ON (r2c.room_id = r.room_id) LEFT JOIN " . DB_PREFIX . "room_description rd ON (r2c.room_id = rd.room_id) LEFT JOIN " . DB_PREFIX . "room_price_category rpc ON (r2c.room_category_id = rpc.room_category_id) LEFT JOIN " . DB_PREFIX . "image i ON (i.image_id = r.image_id) WHERE rd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND r2c.room_category_id ='" . (int)$room_category_id . "'";
		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getCategoryOtherRooms($room_category_id,$room_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "room_to_category r2c LEFT JOIN " . DB_PREFIX . "room r ON (r2c.room_id = r.room_id) LEFT JOIN " . DB_PREFIX . "room_description rd ON (r.room_id = rd.room_id) WHERE r2c.room_category_id ='" . (int)$room_category_id . "' AND rd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND r.room_id != '".$room_id."' GROUP BY r.room_id";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getOtherRooms($data,$room_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "room r LEFT JOIN " . DB_PREFIX . "room_description rd ON (r.room_id = rd.room_id) LEFT JOIN " . DB_PREFIX . "room_price rp ON (r.room_id = rp.room_id) LEFT JOIN " . DB_PREFIX . "image i ON (i.image_id = r.image_id) WHERE rd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND r.room_id != '".$room_id."' GROUP BY r.room_id ORDER BY rand()";

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

	public function getRoom($room_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "room WHERE room_id = '" . (int)$room_id . "'");

		return $query->row;
	}

	public function getRoomDescriptions($room_id) {
		$description = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "room_description WHERE room_id = '" . (int)$room_id . "'");

		foreach ($query->rows as $result) {
			$description[$result['language_id']] = array(
				'name'             => $result['name'],
				'description'      => $result['description'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword'],
				'tag'              => $result['tag']
			);
		}

		return $description;
	}

	public function getRoomImages($room_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "image_to_module i2m LEFT JOIN " . DB_PREFIX . "image i ON (i.image_id = i2m.image_id) WHERE i2m.module = 'room' AND i2m.module_id = '" . (int)$room_id . "' ORDER BY sort_order ASC");

		return $query->rows;
	}

	public function getRoomInformation($room_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "room r LEFT JOIN " . DB_PREFIX . "room_description rd ON (r.room_id = rd.room_id) LEFT JOIN " . DB_PREFIX . "room_price_category rp ON (r.room_category_id = rp.room_category_id) WHERE rd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND r.room_id = '" . (int)$room_id . "'";

		$query = $this->db->query($sql);

		return $query->row;
	}

	public function getRoomStores($room_id) {
		$store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "room_to_store WHERE room_id = '" . (int)$room_id . "'");

		foreach ($query->rows as $result) {
			$store_data[] = $result['store_id'];
		}

		return $store_data;
	}

	public function getRoomFacilities($room_id) {
		$facility_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "room_facility_to_room WHERE room_id = '" . (int)$room_id . "'");

		foreach ($query->rows as $result) {
			$facility_data[] = $result['room_facility_id'];
		}

		return $facility_data;
	}

	public function getTotalRooms($data) {
		$sql = "SELECT COUNT(r.room_id) AS total FROM " . DB_PREFIX . "room r LEFT JOIN " . DB_PREFIX . "room_description rd ON (r.room_id = rd.room_id) WHERE rd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		if (!empty($data['filter_name'])) {
			$sql .= " AND rd.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}
		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	/**
	 * 更新瀏覽次數
	 * @param  [type] $product_id [description]
	 * @return [type]             [description]
	 */
	public function updateViewed($room_id) {
		$this->db->query("UPDATE " . DB_PREFIX . "room SET viewed = (viewed + 1) WHERE room_id = '" . (int)$room_id . "'");
	}
}