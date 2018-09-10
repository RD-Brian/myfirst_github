<?php
class ModelNewsNews extends Model {

	/**
	 * 獲得資料集
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function getNewsies($data)	{
		$sql = "SELECT * , n.date_added AS date_added FROM " . DB_PREFIX . "news n LEFT JOIN " . DB_PREFIX . "news_description nd ON (n.news_id = nd.news_id) LEFT JOIN " . DB_PREFIX . "image i ON (i.image_id = n.image_id) WHERE nd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND n.status = '1'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND nd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sql .= " GROUP BY n.news_id";

		$sort_data = array(
			'nd.name',
			'n.status',
			'n.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY n.news_id";
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
	 * 獲得指定ID資料
	 * @param  [type] $news_id [description]
	 * @return [type]          [description]
	 */
	public function getNews($news_id) {
		$query = $this->db->query("SELECT DISTINCT *,n.date_added AS date_added FROM " . DB_PREFIX . "news n LEFT JOIN " . DB_PREFIX . "news_description nd ON (n.news_id = nd.news_id) LEFT JOIN " . DB_PREFIX . "image i ON (i.image_id = n.image_id) LEFT JOIN " . DB_PREFIX . "news_to_category n2c ON (n.news_id = n2c.news_id) WHERE n.news_id = '" . (int)$news_id . "' AND nd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getImage($image_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "image WHERE image_id = '" . (int)$image_id . "'");

		return $query->row;
	}

	public function getImageDescription($image_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "image_description WHERE image_id = '" . (int)$image_id . "'");

		return $query->rows;
	}

	public function getNewsStores($news_id) {
		$store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "news_to_store WHERE news_id = '" . (int)$news_id . "'");

		foreach ($query->rows as $result) {
			$store_data[] = $result['store_id'];
		}

		return $store_data;
	}

	public function getTotalNews($data) {
		$sql = "SELECT COUNT(n.news_id) AS total FROM " . DB_PREFIX . "news n LEFT JOIN " . DB_PREFIX . "news_description nd ON (n.news_id = nd.news_id) WHERE nd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		if (!empty($data['filter_name'])) {
			$sql .= " AND nd.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}
		$sql .= " AND n.status=1";
		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	public function updateViewed($news_id) {
		$this->db->query("UPDATE " . DB_PREFIX . "news SET viewed = (viewed + 1) WHERE news_id = '" . (int)$news_id . "'");
	}
}