<?php
class ModelArticleArticle extends Model {

	/**
	 * 獲得資料集
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function getArticles($data)	{
		$sql = "SELECT * , a.date_added AS date_added FROM " . DB_PREFIX . "article a LEFT JOIN " . DB_PREFIX . "article_description ad ON (a.article_id = ad.article_id) LEFT JOIN " . DB_PREFIX . "image i ON (i.image_id = a.image_id) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND a.status = '1'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND nad.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sql .= " GROUP BY a.article_id";

		$sort_data = array(
			'nd.name',
			'a.status',
			'a.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY a.article_id";
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
	 * @param  [type] $article_id [description]
	 * @return [type]          		[description]
	 */
	public function getArticle($article_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "article a LEFT JOIN " . DB_PREFIX . "article_description ad ON (a.article_id = ad.article_id) LEFT JOIN " . DB_PREFIX . "image i ON (i.image_id = a.image_id) WHERE a.article_id = '" . (int)$article_id . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "'");

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


	public function getTotalArticle($data) {
		$sql = "SELECT COUNT(a.article_id) AS total FROM " . DB_PREFIX . "article a LEFT JOIN " . DB_PREFIX . "article_description ad ON (a.article_id = ad.article_id) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		if (!empty($data['filter_name'])) {
			$sql .= " AND ad.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}
		$sql .= " AND a.status=1";
		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	public function updateViewed($article_id) {
		$this->db->query("UPDATE " . DB_PREFIX . "article SET viewed = (viewed + 1) WHERE article_id = '" . (int)$article_id . "'");
	}
}