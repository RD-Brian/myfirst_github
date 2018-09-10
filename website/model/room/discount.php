<?php
class ModelRoomDiscount extends Model {

	/**
	 * 取得指定條件的分類資料集
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public function getRoomDiscounts($room_id) {
		$sql = "SELECT *,rd.room_discount_id AS room_discount_id FROM " . DB_PREFIX . "room_discount rd LEFT JOIN " . DB_PREFIX . "room_discount_once rdo ON (rd.room_discount_id = rdo.room_discount_id) LEFT JOIN " . DB_PREFIX . "room_discount_cycle rdc ON (rd.room_discount_id = rdc.room_discount_id) LEFT JOIN " . DB_PREFIX . "room_discount_range rdr ON (rd.room_discount_id = rdr.room_discount_id) WHERE rd.room_id = '" . (int)$room_id . "'";

		$sort_data = array(
			'rd.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY rd.date_added";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	/**
	 * [getTotalCategories description]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function getTotalFacilities($data = array()) {
      	$sql = "SELECT COUNT(rf.room_facility_id) AS total FROM " . DB_PREFIX . "room_facility rf LEFT JOIN " . DB_PREFIX . "room_facility_description rfd ON (rf.room_facility_id = rfd.room_facility_id) WHERE rfd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		if (!empty($data['filter_name'])) {
			$sql .= " AND rfd.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}
		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	public function getFacility($room_facility_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "room_facility rf LEFT JOIN " . DB_PREFIX . "room_facility_description rfd ON (rf.room_facility_id = rfd.room_facility_id) WHERE rf.room_facility_id = '" . (int)$room_facility_id . "' AND rfd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getFacilityDescriptions($room_facility_id) {
		$description = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "room_facility_description WHERE room_facility_id = '" . (int)$room_facility_id . "'");

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
}