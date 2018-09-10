<?php
class ModelAttractionsAttractions extends Model {

	/**
	 * 取得指定條件的分類資料集
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public function getAttractions($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "attractions a LEFT JOIN " . DB_PREFIX . "attractions_description ad ON (a.attractions_id = ad.attractions_id) LEFT JOIN " . DB_PREFIX . "image i ON (i.image_id = a.image_id) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND ad.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sql .= " AND a.status = 1";
		$sql .= " GROUP BY ad.attractions_id";

		$sort_data = array(
			'ad.name',
			'a.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY a.sort_order";
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
	public function getTotalAttractions($data = array()) {
      	$sql = "SELECT COUNT(a.attractions_id) AS total FROM " . DB_PREFIX . "attractions a LEFT JOIN " . DB_PREFIX . "attractions_description ad ON (a.attractions_id = ad.attractions_id) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		if (!empty($data['filter_name'])) {
			$sql .= " AND ad.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}
		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	public function getAttractionsInfo($attractions_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "attractions WHERE attractions_id = '" . (int)$attractions_id . "'");

		return $query->row;
	}

	public function getAttractionsDescriptions($attractions_id) {
		$description = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "attractions_description WHERE attractions_id = '" . (int)$attractions_id . "'");

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