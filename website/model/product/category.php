<?php
class ModelProductCategory extends Model {

	public function getProductCategory()
	{
		$sql = "SELECT pc.parent_id,pc.product_category_id,pcd.name AS name FROM ". DB_PREFIX . "product_category pc LEFT JOIN " . DB_PREFIX . "product_category_description pcd ON (pc.product_category_id = pcd.product_category_id) WHERE pc.status = 1";
		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getCategoryTree($parent_id = 0)
	{
		$categorys = $this->getProductCategory();
		// print_r($categorys);
		if($categorys) {
			$data = array();
			foreach ($categorys as $key => $category) {
				if($category['parent_id'] == $parent_id){
					$data[] = array(
						'product_category_id' => $category['product_category_id'],
						'name' 				  => $category['name'],
						'parent_id' 		  => $category['parent_id'],
						'href'		  		  => $this->url->link('product/category','product_category_id='.$category['product_category_id']),
						'subcategory' 		  => $this->getSubCategory($categorys,$category['product_category_id'])
						);
				}
			}

			return $data;
		}

	}

	public function getSubCategory($categorys,$parent_id)
	{
		$data = array();
		//$product_category_id 傳進來就一個
		//$category['product_category_id'] 迴圈的
		if($categorys) {
			foreach ($categorys as $key => $category) {
				if($category['parent_id'] == $parent_id){
					$data[] = array(
						'product_category_id' => $category['product_category_id'],
						'name' 				  => $category['name'],
						'href'		  		  => $this->url->link('product/category','product_category_id='.$category['product_category_id']),
						'subcategory' 		  => $this->getSubCategory($categorys,$category['product_category_id'])
						);
				}
			}
		}

		return $data;
	}
	//分類的商品 之後改成 $sql.=
	public function getProducts($data = array(),$start, $limit)
	{

		$sql = "SELECT p.product_id,p2c.product_category_id,p.model,p.image_id,p.price,pd.name,pd.description,i.image,i.path FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "image i ON (p.image_id = i.image_id)";

		$implode = array();

		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 1;
		}

		$implode[] = "p.status = 1";

		if(!empty($data['product_category_id'])){
			$implode[] = "p2c.product_category_id = '" . $this->db->escape($data['product_category_id']) . "'";
		}

		if(!empty($data['product_name'])){
			$implode[] = "pd.name LIKE '%" . $this->db->escape($data['product_name']) . "%'";
		}

		if(!empty($implode)){
			$sql .= ' WHERE ' .implode(" AND ",$implode);
		}
		$sql .= " GROUP BY pd.product_id LIMIT " . (int)$start . "," . (int)$limit;// LIMIT " . (int)$start . "," . (int)$limit
		
		$result = $this->db->query($sql);
		return $result->rows;
	}

	// public function getCategoryTotal()
	// {
	// 	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_category");
	// 	return $query->row['total'];
	// }
	public function getProductTotal($data = array())
	{
		$sql = "SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";
		$implode = array();

		$implode[] = "p.status = 1";
		
		if(!empty($data['product_category_id'])){
			$implode[] = "p2c.product_category_id = '" . $this->db->escape($data['product_category_id']) . "'";
		}

		if(!empty($data['product_name'])){
			$implode[] = "pd.name LIKE '%" . $this->db->escape($data['product_name']) . "%'";
		}

		if(!empty($implode)){
			$sql .= ' WHERE ' .implode(" AND ",$implode);
		}
		$sql .= " GROUP BY p2c.product_id";
		
		$query = $this->db->query($sql);
		return count($query->rows);
	}

}

