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
class ModelCheckoutCart extends Model {

	/**
	 * 1.Get Method
	 */
	
	/**
	 * 獲取指定商品在購物中的資料集
	 * @param  [int] $product_id [商品ID]
	 * @return [array]           [返回本商品已加入購物車中的所有規格資料集]
	 */
	public function getCartProducts($product_id)	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "cart WHERE api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "' AND product_id = '" . (int)$product_id . "'");
		return $query->rows;
	}

	/**
	 * 獲取已加入購務車中的指定商品並符合選項規格的商品資料
	 * 進行庫存管理使用
	 * @param  [type] $product_id [description]
	 * @return [type]             [description]
	 */
	public function getCartProductOptionQuantity($product_id,$option)	{
		$query = $this->db->query("SELECT SUM(quantity) AS total FROM " . DB_PREFIX . "cart WHERE api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "' AND product_id = '" . (int)$product_id . "' AND `option` = '" . $this->db->escape(json_encode($option)) . "'");
		return $query->row['total'];
	}

	/**
	 * 獲取已加入購務車中的指定商品並符合選項規格與附加規格的商品資料
	 * @param  [type] $product_id [description]
	 * @return [type]             [description]
	 */
	public function getCartProduct($product_id,$option,$additional_option)	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "cart WHERE api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "' AND product_id = '" . (int)$product_id . "' AND `option` = '" . $this->db->escape(json_encode($option)) . "' AND `additional_option` = '" . $this->db->escape(json_encode($additional_option)) . "'");
		return $query->row;
	}

	public function getProducts() {
		$cart_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "cart WHERE api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "'");
		return $cart_query->rows;
	}

	//cart update

	public function UpdateItemsPrice($product_id,$quantity)
	{
		$this->db->query("UPDATE " . DB_PREFIX . "cart SET quantity = '" . $quantity . "' WHERE session_id = '" . $this->db->escape($this->session->getId()) . "' AND product_id = '" . $product_id . "'");
	}
}