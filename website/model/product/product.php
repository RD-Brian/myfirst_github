<?php
/**
 * 1. GET
 * 2. ADD
 * 3. EDIT
 * 4. DELETE
 * 5. UPDATA
 * 6. OTHER
 * 7. CUSTOM
 */
class ModelProductProduct extends Model {

	/*-----------------------------------------------*
	 * 1.Get Method
	 *-----------------------------------------------*/

	/**
	 * 獲取指定商品資料
	 * @param  [int] $product_id [商品的ID]
	 * @return [array]           [description]
	 */
	public function getProduct($product_id)	{
		$sql = "SELECT p.product_id,p.model,p.stock_status_id,p.shipping,p.original_price,p.is_original_price,p.price,pd.name,pd.description,pd.meta_keyword,pd.meta_description,i.image,i.image_id,i.path,p.stock,p.subtract,p.quantity FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX ."product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "image i ON (p.image_id = i.image_id) WHERE p.product_id=" . $product_id;

		$query = $this->db->query($sql);
		return $query->row;
	}


	/**
	 * 獲取指定商品選項
	 * @param  [int] $product_id [description]
	 * @return [type]            [description]
	 */
	public function getProductOptions($product_id) {
		$product_option_data = array();

		$product_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.sort_order");

		foreach ($product_option_query->rows as $product_option) {
			$product_option_value_data = array();

			$product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ov.sort_order");

			foreach ($product_option_value_query->rows as $product_option_value) {
				$product_option_value_data[] = array(
					'product_option_value_id' => $product_option_value['product_option_value_id'],
					'option_value_id'         => $product_option_value['option_value_id'],
					'name'                    => $product_option_value['name'],
					'quantity'                => $product_option_value['quantity'],
					'price'                   => $product_option_value['price'],
					'price_prefix'            => $product_option_value['price_prefix'],
					'weight'                  => $product_option_value['weight'],
					'weight_prefix'           => $product_option_value['weight_prefix']
				);
			}

			$product_option_data[] = array(
				'product_option_id'    => $product_option['product_option_id'],
				'product_option_value' => $product_option_value_data,
				'option_id'            => $product_option['option_id'],
				'name'                 => $product_option['name'],
				'type'                 => $product_option['type'],
				'value'                => $product_option['value'],
				'required'             => $product_option['required']
			);
		}

		return $product_option_data;
	}


	/**
	 * 獲取指定商品的附加選項
	 * @param  [int] $product_id [description]
	 * @return [type]            [description]
	 */
	public function getProductAdditionalOptions($product_id) {
		$product_additional_option_data = array();

		$product_additional_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_additional_option po LEFT JOIN `" . DB_PREFIX . "additional_option` o ON (po.additional_option_id = o.additional_option_id) LEFT JOIN " . DB_PREFIX . "additional_option_description od ON (o.additional_option_id = od.additional_option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.sort_order");

		foreach ($product_additional_option_query->rows as $product_additional_option) {
			$product_additional_option_value_data = array();

			$product_additional_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_additional_option_value pov LEFT JOIN " . DB_PREFIX . "additional_option_value ov ON (pov.additional_option_value_id = ov.additional_option_value_id) LEFT JOIN " . DB_PREFIX . "additional_option_value_description ovd ON (ov.additional_option_value_id = ovd.additional_option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_additional_option_id = '" . (int)$product_additional_option['product_additional_option_id'] . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ov.sort_order");

			foreach ($product_additional_option_value_query->rows as $product_additional_option_value) {
				$product_additional_option_value_data[] = array(
					'product_additional_option_value_id' => $product_additional_option_value['product_additional_option_value_id'],
					'additional_option_value_id'         => $product_additional_option_value['additional_option_value_id'],
					'name'                    => $product_additional_option_value['name'],
				);
			}

			$product_additional_option_data[] = array(
				'product_additional_option_id'    => $product_additional_option['product_additional_option_id'],
				'product_additional_option_value' => $product_additional_option_value_data,
				'additional_option_id'            => $product_additional_option['additional_option_id'],
				'name'                 => $product_additional_option['name'],
				'type'                 => $product_additional_option['type'],
				'value'                => $product_additional_option['value'],
				'required'             => $product_additional_option['required']
			);
		}

		return $product_additional_option_data;
	}


	

	/**
	 * 獲取指定商品所設定的選項資料
	 * @param  [type] $product_id [description]
	 * @return [type]             [description]
	 */
	public function getProductOption($product_option_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option WHERE product_option_id = '" . (int)$product_option_id . "'");
		return $query->row;
	}





	// ------




	//觀看數
	public function updateViewed($product_id) {
		$this->db->query("UPDATE " . DB_PREFIX . "product SET viewed = (viewed + 1) WHERE product_id = '" . (int)$product_id . "'");
	}

	

	//取得該商品的所屬分類
	public function getProductBelong($product_id)
	{
		$sql = "SELECT * FROM " . DB_PREFIX . "product_category pc LEFT JOIN " . DB_PREFIX . "product_category_description pcd ON (pc.product_category_id = pcd.product_category_id) LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p2c.product_category_id = pcd.product_category_id) WHERE p2c.product_id = '" .$product_id . "' ORDER BY pc.parent_id ASC";

		$query = $this->db->query($sql);
		return $query->rows;
	}

	//搜尋商品
	public function searchProduct($product_name)
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "image i ON (p.image_id = i.image_id) WHERE pd.name LIKE '%" . $this->db->escape($product_name) . "%'");
		return $query->rows;
	}

	//相關圖片
	public function getProductImages($product_id)
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "image_to_module i2m LEFT JOIN " . DB_PREFIX . "image i ON (i.image_id = i2m.image_id) WHERE i2m.module = 'product' AND i2m.module_id = '" . (int)$product_id . "'");

		return $query->rows;

	}

		//特價
	public function getProductSpecials($product_id) {

		$sql = "SELECT * FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "' ORDER BY priority, price";



		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "' ORDER BY priority, price");

		return $query->rows;
	}

	//熱銷商品
	public function getPurchased($data = array()) {
		$sql = "SELECT op.name, op.model,op.product_id, SUM(op.quantity) AS quantity FROM " . DB_PREFIX . "order_product op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id)";

		if (!empty($data['filter_order_status_id'])) {
			$sql .= " WHERE o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " WHERE o.order_status_id > '0'";
		}

		if (!empty($data['filter_date_start'])) {
			$sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}

		$sql .= " GROUP BY op.product_id ORDER BY quantity DESC";

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

	//分類的商品
	public function getProducts($data = array(),$start, $limit)
	{
		$sql = "SELECT *,p.date_added FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "image i ON (p.image_id = i.image_id)  LEFT JOIN " . DB_PREFIX . "product_to_classify ptc ON (p.product_id = ptc.product_id)";

		//判定有無分類的網站
		if(!empty($data['product_category_id'])){
			$sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) LEFT JOIN " . DB_PREFIX . "product_category pc ON (p2c.product_category_id = pc.product_category_id) ";
		}

		$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if(!empty($data['product_category_id'])){
			$sql .= " AND p2c.product_category_id = '" . $this->db->escape($data['product_category_id']) . "'";
			//商品分類有無被禁用
			// $sql .= " AND pc.status = 1 ";
		}

		if(!empty($data['product_name'])){
			$sql .= " AND pd.name LIKE '%" . $this->db->escape($data['product_name']) . "%'";
		}

		if(!$this->customer->isLogged()){
			$sql .= " AND ptc.classify_id = 0";
		}

		$sql .= " AND p.status = 1";


		$sort_data = array(
			'pd.name',
			'p.model',
			'p.price',
			'p.viewed',
			'p.sort_order',
			'p.status'
		);

		$sql .= " GROUP BY pd.product_id";

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY p.product_id";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 1;
		}

		$sql .= " LIMIT " . (int)$start . "," . (int)$limit;

		$result = $this->db->query($sql);
		return $result->rows;
	}
	//分類用商品總數
	public function getProductTotal($data = array())
	{
		$sql = "SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_classify ptc ON (p.product_id = ptc.product_id)";
		
		//判定有無分類的網站
		if(!empty($data['product_category_id'])){
			$sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) LEFT JOIN " . DB_PREFIX . "product_category pc ON (p2c.product_category_id = pc.product_category_id) ";
		}

		$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";		

		if(!empty($data['product_category_id'])){
			$sql .= " AND p2c.product_category_id = '" . $this->db->escape($data['product_category_id']) . "'";
			//商品分類有無被禁用
			// $sql .= " AND pc.status = 1 ";
		}

		if(!empty($data['product_name'])){
			$sql .= " AND pd.name LIKE '%" . $this->db->escape($data['product_name']) . "%'";
		}

		if(!$this->customer->isLogged()){
			$sql .= " AND ptc.classify_id = 0";
		}


		$sql .= " AND p.status = 1";
		$sql .= " GROUP BY pd.product_id";
		
		$query = $this->db->query($sql);
		return count($query->rows);
	}

		//配送方式
	public function getShippingMethod($shipping)
	{
		foreach ($shipping as $key => $value) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "shipping_method WHERE language_id = '" . (int)$this->config->get('config_language_id') . "' AND shipping_method_id = '" . $value . "'");
			$shipping_method[] = $query->row;
		}
		
		return $shipping_method;
	}

	//商品規格
	public function getProductSpecs($product_id) {
		$product_specification_group_data = array();

		$product_specification_group_query = $this->db->query("SELECT ag.specification_group_id, agd.name FROM " . DB_PREFIX . "product_specification pa LEFT JOIN " . DB_PREFIX . "specification a ON (pa.specification_id = a.specification_id) LEFT JOIN " . DB_PREFIX . "specification_group ag ON (a.specification_group_id = ag.specification_group_id) LEFT JOIN " . DB_PREFIX . "specification_group_description agd ON (ag.specification_group_id = agd.specification_group_id) WHERE pa.product_id = '" . (int)$product_id . "' AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY ag.specification_group_id ORDER BY ag.sort_order, agd.name");

		foreach ($product_specification_group_query->rows as $product_specification_group) {
			$product_spec_data = array();

			$product_spec_query = $this->db->query("SELECT a.specification_id, ad.name, pa.text FROM " . DB_PREFIX . "product_specification pa LEFT JOIN " . DB_PREFIX . "specification a ON (pa.specification_id = a.specification_id) LEFT JOIN " . DB_PREFIX . "specification_description ad ON (a.specification_id = ad.specification_id) WHERE pa.product_id = '" . (int)$product_id . "' AND a.specification_group_id = '" . (int)$product_specification_group['specification_group_id'] . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND pa.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY a.sort_order, ad.name");

			foreach ($product_spec_query->rows as $product_specification) {
				$product_spec_data[] = array(
					'specification_id' => $product_specification['specification_id'],
					'name'         => $product_specification['name'],
					'text'         => $product_specification['text']
				);
			}

			$product_specification_group_data[] = array(
				'specification_group_id' => $product_specification_group['specification_group_id'],
				'name'               => $product_specification_group['name'],
				'spec'          => $product_spec_data
			);
		}

		return $product_specification_group_data;
	}
	

	//附加商品選項
		public function getProductAddOptions($product_id) {
		$product_additional_option_data = array();

		$product_additional_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_additional_option po LEFT JOIN `" . DB_PREFIX . "additional_option` o ON (po.additional_option_id = o.additional_option_id) LEFT JOIN " . DB_PREFIX . "additional_option_description od ON (o.additional_option_id = od.additional_option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.sort_order");

		foreach ($product_additional_option_query->rows as $product_additional_option) {
			$product_additional_option_value_data = array();

			$product_additional_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_additional_option_value pov LEFT JOIN " . DB_PREFIX . "additional_option_value ov ON (pov.additional_option_value_id = ov.additional_option_value_id) LEFT JOIN " . DB_PREFIX . "additional_option_value_description ovd ON (ov.additional_option_value_id = ovd.additional_option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_additional_option_id = '" . (int)$product_additional_option['product_additional_option_id'] . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ov.sort_order");

			foreach ($product_additional_option_value_query->rows as $product_additional_option_value) {
				$product_additional_option_value_data[] = array(
					'product_additional_option_value_id' => $product_additional_option_value['product_additional_option_value_id'],
					'additional_option_value_id'         => $product_additional_option_value['additional_option_value_id'],
					'name'                    => $product_additional_option_value['name'],
					'price'                   => $product_additional_option_value['price'],
					'price_prefix'            => $product_additional_option_value['price_prefix'],
					'weight'                  => $product_additional_option_value['weight'],
					'weight_prefix'           => $product_additional_option_value['weight_prefix']
				);
			}

			$product_additional_option_data[] = array(
				'product_additional_option_id'    => $product_additional_option['product_additional_option_id'],
				'product_additional_option_value' => $product_additional_option_value_data,
				'additional_option_id'            => $product_additional_option['additional_option_id'],
				'name'                 => $product_additional_option['name'],
				'type'                 => $product_additional_option['type'],
				'value'                => $product_additional_option['value'],
				'required'             => $product_additional_option['required']
			);
		}

		return $product_additional_option_data;
	}
}