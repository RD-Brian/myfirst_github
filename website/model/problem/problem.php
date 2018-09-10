<?php
class ModelProblemProblem extends Model {

	/**
	 * 獲得資料集
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	//取得問題分類
	public function getCategories($data = array()) {
		$sql = "SELECT c1.problem_category_id AS problem_category_id, ncd.name, (SELECT COUNT(*) FROM " . DB_PREFIX."problem_to_category ba2c WHERE ba2c.problem_category_id = c1.problem_category_id) AS total, c1.sort_order FROM " . DB_PREFIX . "problem_category c1 LEFT JOIN " . DB_PREFIX . "problem_category_description ncd ON (c1.problem_category_id = ncd.problem_category_id) WHERE ncd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND ncd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND ncd.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sql .= " GROUP BY c1.problem_category_id";

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



	public function getProblems($data)	{
		$sql = "SELECT * , p.sort_order AS sort_order, pd.name AS name, pd.description AS description FROM " . DB_PREFIX . "problem p LEFT JOIN " . DB_PREFIX . "problem_description pd ON (p.problem_id = pd.problem_id) LEFT JOIN " . DB_PREFIX . "problem_to_category p2c ON (p.problem_id = p2c.problem_id) LEFT JOIN " . DB_PREFIX . "problem_category pc ON (pc.problem_category_id = p2c.problem_category_id) LEFT JOIN " . DB_PREFIX . "problem_category_description pcd ON (pcd.problem_category_id = p2c.problem_category_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['problem_category_id'])) {
			$sql .= " AND pc.problem_category_id = '" . $this->db->escape($data['problem_category_id']) . "'";
		}

		$sql .= " GROUP BY p.problem_id";

		$sort_data = array(
			'pd.name',
			'p.status',
			'p.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY p.sort_order";
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
	 * @param  [type] $problem_id [description]
	 * @return [type]          [description]
	 */
	public function getNews($news_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "news n LEFT JOIN " . DB_PREFIX . "news_description nd ON (n.news_id = nd.news_id) LEFT JOIN " . DB_PREFIX . "image i ON (i.image_id = n.image_id) LEFT JOIN " . DB_PREFIX . "news_to_category n2c ON (n.news_id = n2c.news_id) WHERE n.news_id = '" . (int)$news_id . "' AND nd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
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