<?php
class ModelNewsCategory extends Model {

	public function getCategory($news_category_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "news_category c LEFT JOIN " . DB_PREFIX . "news_category_description cd ON (c.news_category_id = cd.news_category_id) LEFT JOIN " . DB_PREFIX . "news_category_to_store c2s ON (c.news_category_id = c2s.news_category_id) WHERE c.news_category_id = '" . (int)$news_category_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1'");

		return $query->row;
	}

	/**
	 * 取得指定條件的分類資料集
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public function getCategories($data = array()) {
		$sql = "SELECT c1.news_category_id AS news_category_id, ncd.name, (SELECT COUNT(*) FROM " . DB_PREFIX."news_to_category ba2c WHERE ba2c.news_category_id = c1.news_category_id) AS total, c1.sort_order FROM " . DB_PREFIX . "news_category c1 LEFT JOIN " . DB_PREFIX . "news_category_description ncd ON (c1.news_category_id = ncd.news_category_id) WHERE ncd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND ncd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND ncd.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sql .= " GROUP BY c1.news_category_id";

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
      	$sql = "SELECT COUNT(bc.news_category_id) AS total FROM " . DB_PREFIX . "news_category bc LEFT JOIN " . DB_PREFIX . "news_category_description bcd ON (bc.news_category_id = bcd.news_category_id) WHERE bcd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		if (!empty($data['filter_name'])) {
			$sql .= " AND bcd.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}
		$query = $this->db->query($sql);
		return $query->row['total'];
	}

}