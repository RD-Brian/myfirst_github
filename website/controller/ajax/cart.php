<?php
class ControllerAjaxCart extends Controller {
	public function index() {}

	public function add() {
		$json = array();
		$this->load->language('ajax/cart');

		$this->load->model('checkout/cart');
		$this->load->model('product/product');

		if (($this->request->server['REQUEST_METHOD'] == 'POST')){

			if (isset($this->request->post['product_id'])) {
				$product_id = (int)$this->request->post['product_id'];
			} else {
				$product_id = 0;
			}

			$product_info = $this->model_product_product->getProduct($product_id);

			if($product_info) {

				if (isset($this->request->post['quantity'])) {
					$quantity = (int)$this->request->post['quantity'];
				} else {
					$quantity = 1;
				}

				/**
				 * 處理 option
				 */
				if (isset($this->request->post['option'])) {
					$options = array_filter($this->request->post['option']);
				} else {
					$options = array();
				}

				// 檢查是否有必填的option沒有填寫到
				$product_options = $this->model_product_product->getProductOptions($this->request->post['product_id']);
				foreach ($product_options as $product_option) {
					if ($product_option['required'] && empty($options[$product_option['product_option_id']])) {
						$json['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
					}
				}

				// 獲取這個選項規格在購物車中已有購買的數量
				$cart_product_quantity = $this->model_checkout_cart->getCartProductOptionQuantity($product_id,$options);

				// 如果有購買選項則->檢查選擇的項目是否有足夠的庫存
				if($options) {
					// 開始進行option的庫存逐項檢查
					foreach ($options as $product_option_id => $option) {
						$product_option_info = $this->model_product_product->getProductOption($product_option_id);
						if(isset($product_option_info['value']) && !empty($product_option_info['value'])) {
							// 當購物車中的購買數量加上現在要購買的數量大於庫存量時->則代表庫存不足
							if($cart_product_quantity + $quantity > $product_option_info['value']) {
								$json['error'] = '庫存量不足';
							}
						}
					}
				}

				/**
				 * 處理 additional_option
				 */
				if (isset($this->request->post['additional_option'])) {
					$additional_options = array_filter($this->request->post['additional_option']);
				} else {
					$additional_options = array();
				}

				// 檢查是否有必填的additional_option沒有填寫到
				$product_additional_options = $this->model_product_product->getProductAdditionalOptions($this->request->post['product_id']);
				foreach ($product_additional_options as $product_additional_option) {
					if ($product_additional_option['required'] && empty($options[$product_additional_option['product_additional_option_id']])) {
						$json['error']['additional_option'][$product_additional_option['product_additional_option_id']] = sprintf($this->language->get('error_required'), $product_additional_option['name']);
					}
				}

				// 都沒有錯誤訊息時則可以寫入
				if(!$json){
					// 先不啟用週期性付款的方式，如：月費,年費...等
					$recurring_id = 0;
					$cart_id = $this->cart->add($this->request->post['product_id'],$quantity,$options,$additional_options,$recurring_id);

					if($cart_id) {
						// Totals
						$this->load->model('setting/extension');

						$totals = array();
						$taxes = $this->cart->getTaxes();
						$total = 0;

						$total_data = array(
							'totals' => &$totals,
							'taxes'  => &$taxes,
							'total'  => &$total
							);

						// Display prices
						if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
							$sort_order = array();
							$results = $this->model_setting_extension->getExtensions('total');
							foreach ($results as $key => $value) {
								$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
							}
							array_multisort($sort_order, SORT_ASC, $results);

							foreach ($results as $result) {
								if ($this->config->get('total_' . $result['code'] . '_status')) {
									$this->load->model('extension/total/' . $result['code']);
									$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
								}
							}

							$sort_order = array();

							foreach ($totals['total'] as $key => $value) {
								$sort_order[$key] = $value['sort_order'];
							}

							array_multisort($sort_order, SORT_ASC, $totals['total']);

							$json['status'] = 'success';
							$json['msg'] = $this->language->get('text_success');
							$json['total'] = $this->cart->countProducts();
						}
					} else {
						$json['status'] = 'error';
						$json['msg'] = $this->language->get('text_error');
					}
				}
			} else {
				$json['redirect'] = str_replace('&amp;', '&', $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']));
			}

		} else {
			$json['redirect'] = str_replace('&amp;', '&', $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * 移除購物車商品
	 * @return [type] [description]
	 */
	public function remove() {

		$this->load->language('ajax/cart');

		$json = array();

		if (isset($this->request->post['cart_id'])) {
			$this->cart->remove($this->request->post['cart_id']);

			unset($this->session->data['vouchers'][$this->request->post['cart_id']]);
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['reward']);

			// Totals
			$this->load->model('setting/extension');

			$totals = array();
			$taxes = $this->cart->getTaxes();
			$total = 0;

			$total_data = array(
				'totals' => &$totals,
				'taxes'  => &$taxes,
				'total'  => &$total
			);

			// Display prices
			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$sort_order = array();
				$results = $this->model_setting_extension->getExtensions('total');
				foreach ($results as $key => $value) {
					$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
				}
				array_multisort($sort_order, SORT_ASC, $results);

				foreach ($results as $result) {
					if ($this->config->get('total_' . $result['code'] . '_status')) {
						$this->load->model('extension/total/' . $result['code']);
						$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
					}
				}

				$sort_order = array();

				foreach ($totals['total'] as $key => $value) {
					$sort_order[$key] = $value['sort_order'];
				}

				array_multisort($sort_order, SORT_ASC, $totals['total']);
			}

			$json['status'] = 'success';
			$json['message'] = $this->language->get('text_remove');
		} else {
			$json['status'] = 'error';
			$json['message'] = $this->language->get('error_remove');
		}

		$cart_count = $this->cart->countProducts();

		if($cart_count <= 0) {
			$json['status'] = 'warning';
			$json['message'] = $this->language->get('text_cart_empty');
		}

		// 購物車商品數量及金額
		$json['cart_count'] = $cart_count;
		$json['cart_total'] = $this->currency->format($total, $this->session->data['currency']);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function update()
	{

		$json = array();
		$json['success'] = false;

		if(isset($this->request->post['product_id']) && isset($this->request->post['quantity'])) {
			$this->load->model('product/product');
			$this->load->model('checkout/cart');

			$product_id = $this->request->post['product_id'];
			$quantity = $this->request->post['quantity'];
			//取得單項商品資料
			$product_info = $this->model_checkout_cart->getProduct($product_id);

			if(isset($product_info['quantity']) && $product_info['stock'] == 1 && $quantity > $product_info['quantity']){
				$json['error'] = '庫存量不足';
				$json['quantity'] = $product_info['quantity'];
				$updatePrice = $this->model_checkout_cart->UpdateItemsPrice($this->request->post['product_id'],$product_info['quantity']);
 				$json['total'] = $this->model_checkout_cart->countProducts();
			} else {
				$updatePrice = $this->model_checkout_cart->UpdateItemsPrice($this->request->post['product_id'],$this->request->post['quantity']);
				$json['success'] = true;
				$json['total'] = $this->model_checkout_cart->countProducts();
			}
		}

		// print_r($json['total']);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function plus()
	{
		$json = array();
		if(isset($this->request->post['product_id']) && isset($this->request->post['quantity'])) {

			$this->load->model('checkout/cart');
			$this->load->model('product/product');

			$product_id = $this->request->post['product_id'];
			$quantity = $this->request->post['quantity'];
			
			$product = $this->model_product_product->getProduct($product_id);
			//判斷有無庫存限制
			if(isset($product['quantity']) && $product['stock'] == 1)
				//判斷輸入後foucsout
				if($this->request->post['is_plus'] == 'false') {
					if($quantity > $product['quantity']) {
						$json['error'] = '庫存量不足';
						$json['quantity'] = $product['quantity'];
					}
				} else if ($quantity+1 > $product['quantity']) {
					$json['error'] = '庫存量不足';
					$json['quantity'] = $product['quantity'];
				}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
