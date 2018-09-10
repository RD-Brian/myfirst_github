<?php
class ControllerAccountOrder extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/order', '', true);

			$this->response->redirect($this->url->link('common/home', '', true));
		}

		$this->load->language('account/order');

		$this->document->setTitle($this->language->get('heading_title'));

		//靜態data
		$data['account_menu'] = array(
			1 => array('name' => '修改資料','href' => $this->url->link('account/edit', '', true)),
			2 => array('name' => '變更密碼','href' => $this->url->link('account/password', '', true)),
			3 => array('name' => '訂單查詢','href' => $this->url->link('account/order', '', true)),
			4 => array('name' => '登出會員','href' => $this->url->link('account/logout', '', true))
		);

		
		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['orders'] = array();

		$this->load->model('customer/order');

		$order_total = $this->model_customer_order->getTotalOrders();

		$results = $this->model_customer_order->getOrders(($page - 1) * 10, 10);

		foreach ($results as $result) {
			$product_total = $this->model_customer_order->getTotalOrderProductsByOrderId($result['order_id']);
			// $voucher_total = $this->model_customer_order->getTotalOrderVouchersByOrderId($result['order_id']);

			$data['orders'][] = array(
				'order_id'   => $result['order_number'],
				'name'       => $result['name'],
				'status'     => $result['status'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'products'   => $product_total,
				'total'      => $this->currency->format($result['total'],$this->config->get('config_currency')),
				'view'       => $this->url->link('account/order/info', 'order_id=' . $result['order_id'], true),
			);
		}

		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('account/order', 'page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($order_total - 10)) ? $order_total : ((($page - 1) * 10) + 10), $order_total, ceil($order_total / 10));
		
		
		$this->_output('account/order/list', $data);
	}

	public function info() {
		$this->load->language('account/order');
		$this->load->model('customer/order');

		$data['account_menu'] = array(
			1 => array('name' => '修改資料','href' => $this->url->link('account/edit', '', true)),
			2 => array('name' => '變更密碼','href' => $this->url->link('account/password', '', true)),
			3 => array('name' => '訂單查詢','href' => $this->url->link('account/order', '', true)),
			4 => array('name' => '登出會員','href' => $this->url->link('account/logout', '', true))
		);

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/order/info', 'order_id=' . $order_id, true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}
		//訂單資料
		$order_info = $this->model_customer_order->getOrder($order_id);
		//商品品項
		$order_data = $this->model_customer_order->getOrderProducts($order_id);
		//總價
		$product_total = $this->model_customer_order->getTotalOrderProductsByOrderId($order_id);
		$price_data = $this->model_customer_order->getOrderTotals($order_id);

		$data['order_info'] = array(
			'order_id'   => $order_info['order_number'],
			'name'       => $order_info['name'],
			'status'     => $order_info['status'],
			'date_added' => date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
			'products'   => $product_total,
			'total'      => $this->currency->format($order_info['total'],$this->config->get('config_currency')),
		);

		foreach ($order_data as $key => $value) {
			$data['order_data'][] = array(
				'name'					=> $value['name'],
				'model'					=> $value['model'],
				'quantity'			=> $value['quantity'],
				'price'					=> $this->currency->format($value['price'], $this->config->get('config_currency')),
				'total'					=> $this->currency->format($value['total'], $this->config->get('config_currency')),
			);
		}
		foreach ($price_data as $value) {
			$data['price_data'][] = array(
				'value'						=> $this->currency->format($value['value'], $this->config->get('config_currency')),
				'title'						=> $value['title']
			);
		}
		

		if ($order_info) {
			$this->document->setTitle($this->language->get('text_order'));

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}



			$data['continue'] = $this->url->link('account/order', '', true);

			$this->_output('account/order/info', $data);
		} 
	}

	public function reorder() {
		$this->load->language('account/order');

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		$this->load->model('account/order');

		$order_info = $this->model_customer_order->getOrder($order_id);

		if ($order_info) {
			if (isset($this->request->get['order_product_id'])) {
				$order_product_id = $this->request->get['order_product_id'];
			} else {
				$order_product_id = 0;
			}

			$order_product_info = $this->model_customer_order->getOrderProduct($order_id, $order_product_id);

			if ($order_product_info) {
				$this->load->model('catalog/product');

				$product_info = $this->model_catalog_product->getProduct($order_product_info['product_id']);

				if ($product_info) {
					$option_data = array();

					$order_options = $this->model_customer_order->getOrderOptions($order_product_info['order_id'], $order_product_id);

					foreach ($order_options as $order_option) {
						if ($order_option['type'] == 'select' || $order_option['type'] == 'radio' || $order_option['type'] == 'image') {
							$option_data[$order_option['product_option_id']] = $order_option['product_option_value_id'];
						} elseif ($order_option['type'] == 'checkbox') {
							$option_data[$order_option['product_option_id']][] = $order_option['product_option_value_id'];
						} elseif ($order_option['type'] == 'text' || $order_option['type'] == 'textarea' || $order_option['type'] == 'date' || $order_option['type'] == 'datetime' || $order_option['type'] == 'time') {
							$option_data[$order_option['product_option_id']] = $order_option['value'];
						} elseif ($order_option['type'] == 'file') {
							$option_data[$order_option['product_option_id']] = $this->encryption->encrypt($this->config->get('config_encryption'), $order_option['value']);
						}
					}

					$this->cart->add($order_product_info['product_id'], $order_product_info['quantity'], $option_data);

					$this->session->data['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . $product_info['product_id']), $product_info['name'], $this->url->link('checkout/cart'));

					unset($this->session->data['shipping_method']);
					unset($this->session->data['shipping_methods']);
					unset($this->session->data['payment_method']);
					unset($this->session->data['payment_methods']);
				} else {
					$this->session->data['error'] = sprintf($this->language->get('error_reorder'), $order_product_info['name']);
				}
			}
		}

		$this->response->redirect($this->url->link('account/order/info', 'order_id=' . $order_id));
	}
}