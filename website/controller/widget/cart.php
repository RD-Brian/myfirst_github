<?php
class ControllerWidgetCart extends Controller {
	public function index() {

		// load
		$this->load->language('widget/cart');
		$this->load->model('product/product');
		$this->load->model('tool/image');

		// data
		$data['url_checkout'] = $this->url->link('checkout/cart');
		$data['url_products'] = $this->url->link('product/category');

		// Totals
		// 如果要顯示更詳細的total資訊才打開
		// $this->load->model('setting/extension');

		// $totals = array();
		// $taxes = $this->cart->getTaxes();
		// $total = 0;
		// // Because __call can not keep var references so we put them into an array.
		// $total_data = array(
		// 	'totals' => &$totals,
		// 	'taxes'  => &$taxes,
		// 	'total'  => &$total
		// 	);

		// // Display prices
		// if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
		// 	$sort_order = array();
		// 	$results = $this->model_setting_extension->getExtensions('total');
		// 	foreach ($results as $key => $value) {
		// 		$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
		// 	}
		// 	array_multisort($sort_order, SORT_ASC, $results);

		// 	foreach ($results as $result) {
		// 		if ($this->config->get('total_' . $result['code'] . '_status')) {
		// 			$this->load->model('extension/total/' . $result['code']);
		// 			// We have to put the totals in an array so that they pass by reference.
		// 			$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
		// 		}
		// 	}

		// 	$sort_order = array();

		// 	foreach ($totals['total'] as $key => $value) {
		// 		$sort_order[$key] = $value['sort_order'];
		// 	}

		// 	array_multisort($sort_order, SORT_ASC, $totals['total']);
		// }

		$data['text_items'] = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0));

		// 小計金額
		$sub_total = $this->cart->getSubTotal();
		$data['text_total'] = sprintf($this->language->get('text_subtotal'), $this->currency->format($sub_total, $this->session->data['currency']));

		// Products
		$this->load->model('tool/image');
		$this->load->model('tool/upload');

		$data['products'] = array();

		$products = $this->cart->getProducts();

		foreach($products as $product) {

			$product_total = 0;

			// 處裡最小購買數量的判定
			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			// 商品最小購買量->目前還沒有這個欄位
			// if ($product['minimum'] > $product_total) {
			// 	$data['error_warning'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
			// }

			if (is_file(DIR_UPLOADS . $product['path'] . $product['image'])) {
				$image = $this->model_tool_image->resize($product['path'].$product['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height') );
			} else {
				$image = '';
			}

			$option_data = array();

			foreach ($product['option'] as $option) {
				if ($option['type'] != 'file') {
					$value = $option['value'];
				} else {
					$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

					if ($upload_info) {
						$value = $upload_info['name'];
					} else {
						$value = '';
					}
				}

				$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);
			}

			$additional_option_data = array();

			foreach ($product['additional_option'] as $additional_option) {
				$additional_option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);
			}

			// Display prices
			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$unit_price = $this->tax->calculate($product['price'], $this->config->get('shop_config_tax_class_id'), $this->config->get('shop_config_tax'));
				$price = $this->currency->format($unit_price, $this->session->data['currency']);
				$total = $this->currency->format($unit_price * $product['quantity'], $this->session->data['currency']);
			} else {
				$price = false;
				$total = false;
			}

			$recurring = '';

				// if ($product['recurring']) {
				// 	$frequencies = array(
				// 		'day'        => $this->language->get('text_day'),
				// 		'week'       => $this->language->get('text_week'),
				// 		'semi_month' => $this->language->get('text_semi_month'),
				// 		'month'      => $this->language->get('text_month'),
				// 		'year'       => $this->language->get('text_year')
				// 	);

				// 	if ($product['recurring']['trial']) {
				// 		$recurring = sprintf($this->language->get('text_trial_description'), $this->currency->format($this->tax->calculate($product['recurring']['trial_price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['trial_cycle'], $frequencies[$product['recurring']['trial_frequency']], $product['recurring']['trial_duration']) . ' ';
				// 	}

				// 	if ($product['recurring']['duration']) {
				// 		$recurring .= sprintf($this->language->get('text_payment_description'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
				// 	} else {
				// 		$recurring .= sprintf($this->language->get('text_payment_cancel'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
				// 	}
				// }

			$data['products'][] = array(
				'cart_id'   => $product['cart_id'],
				'product_id'			=> $product['product_id'],
				'thumb'     => $image,
				'name'      => $product['name'],
				'model'     => $product['model'],
				'options'    => $option_data,
				'additional_options' => $additional_option_data,
				'recurring' => $recurring,
				'quantity'  => $product['quantity'],
				// 'stock'     => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
				// 'reward'    => ($product['reward'] ? sprintf($this->language->get('text_points'), $product['reward']) : ''),
				'price'     => $price,
				'total'     => $total,
				'href'      => $this->url->link('product/product', 'product_id=' . $product['product_id'])
			);
		}

		return $this->load->view('widget/cart', $data);
	}

	public function info() {
		$this->response->setOutput($this->index());
	}
}
