<?php
class ModelInformationAbout extends Model {

	public function getArticle($article_id)
	{
		$sql = "SELECT * FROM " . DB_PREFIX . "article_description ad LEFT JOIN " . DB_PREFIX . "article a ON(a.article_id = ad.article_id) LEFT JOIN " . DB_PREFIX . "image i ON (i.image_id = a.image_id)" ;
		if(!empty($article_id)){
			$sql .= ' WHERE a.article_id =' . $article_id;
		}

		$sql .= " GROUP BY a.article_id";

		$result = $this->db->query($sql);
		return $result->rows;
	}
}