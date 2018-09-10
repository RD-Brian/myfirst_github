<?php
class ControllerCheckoutCheckout extends Controller {

	private $error = array();

	public function index() {

		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));

		// 驗證購物車有產品並且有庫存。
		// if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
		// 	$this->response->redirect($this->url->link('checkout/cart'));
		// }

		/*
			靜態data
		*/
		$data['cart'] = $this->url->link('checkout/cart');
		$data['next'] = '下一步';

		$this->load->language('checkout/checkout');
		// 載入使用到的model檔案
		$this->load->model('customer/customer');
		$this->load->model('customer/address');
		// $this->load->model('localisation/country');
		$this->load->model('localisation/city');
		$this->load->model('localisation/zone');
		$this->load->model('setting/extension');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST')&& $this->validate()){// ) {

			$post = $this->request->post;
			$order_data = array();

			// 預設要註冊
			$is_register = true;

			// 未登入狀態時,先進行訂購者身份檢查
			if(!$this->customer->isLogged()) {
				$is_customer = $this->model_customer_customer->getCustomerByEmail($post['payment_email']);
				if($is_customer) {
					$is_register = false;
				} else {
					$is_customer = $this->model_customer_customer->getCustomerByMobile($post['payment_mobile']);
				    if($is_customer) {
					    $is_register = false;
				    }
				}				
			} else {
				$is_register = false;
			}

			if($is_register) {
				$add_Shopping_Customer =  $this->model_customer_customer->addShoppingCustomer($post);
				$set_payment_address = $this->model_customer_address->SetPaymentAddress($post,$add_Shopping_Customer);
				$updateAddress = $this->model_customer_address->updateAddress($set_payment_address,$add_Shopping_Customer);
			}

			// 處理紀錄會員訂購資料
			if ($this->customer->isLogged()) {
				$customer_info = $this->model_customer_customer->getCustomer($this->customer->getId());
				// order
				$order_data['customer_id'] = $this->customer->getId();
				$order_data['customer_group_id'] = $customer_info['customer_group_id'];
				$order_data['name'] = $customer_info['name'];
				$order_data['email'] = $customer_info['email'];
				$order_data['mobile'] = $customer_info['mobile'];
				$order_data['custom_field'] = json_decode($customer_info['custom_field'], true);
			} else {
				$order_data['customer_id'] = $add_Shopping_Customer;
				$order_data['customer_group_id'] = $this->config->get('config_customer_group_id');
				$order_data['name']         = $post['payment_name'];
				$order_data['email']        = $post['payment_email'];
				$order_data['mobile']       = $post['payment_mobile'];
				$order_data['custom_field'] = isset($post['custom_field']) ? $post['custom_field'] : '';
			}

			// 如果上面判定資料都是新的則註冊會員
			// if($is_register) {
			// 	$add_Shopping_Customer =  $this->model_customer_customer->addShoppingCustomer($post);
			// 	$set_payment_address = $this->model_customer_address->SetPaymentAddress($post,$add_Shopping_Customer);
			// 	$updateAddress = $this->model_customer_address->updateAddress($set_payment_address,$add_Shopping_Customer);
			// }

			// 如果有登入但並沒有會員地址資料則更新地址資料
			if($this->customer->isLogged() && !$this->customer->getAddressId()) {
				//將地址資料寫入到會員資料中
				$set_payment_address = $this->model_customer_address->SetPaymentAddress($post,$this->customer->getId());
				$updateAddress = $this->model_customer_address->updateAddress($set_payment_address,$this->customer->getId());
			}
			
			// 處理訂購者資料
			$order_data['payment_name']   = $post['payment_name'];
			$order_data['payment_mobile'] = $post['payment_mobile'];
			$order_data['payment_email']  = $post['payment_email'];
			$payment_city = $this->model_localisation_city->getCity($post['payment_city_id']);
			$order_data['payment_city_id'] = $post['payment_city_id'];
			$order_data['payment_city'] = $payment_city['name'];
			$payment_zone = $this->model_localisation_zone->getZone($post['payment_zone_id']);
			$order_data['payment_zone_id'] = $post['payment_zone_id'];
			$order_data['payment_zone'] = $payment_zone['name'];
			$payment_postcode = $this->model_localisation_zone->getPostcodeByZone($post['payment_zone_id']);
			$order_data['payment_postcode'] = $payment_postcode;
			$order_data['payment_address'] =  $post['payment_address'];

			// 處理付款方式
			$method_data = array();
			$results = $this->model_setting_extension->getExtensions('payment');
			$recurring = $this->cart->hasRecurringProducts();
			foreach ($results as $result) {
				$this->load->model('extension/payment/' . $result['code']);
				$method = $this->{'model_extension_payment_' . $result['code']}->getMethod();
				if ($method) {
					if ($recurring) {
						if (property_exists($this->{'model_extension_payment_' . $result['code']}, 'recurringPayments') && $this->{'model_extension_payment_' . $result['code']}->recurringPayments()) {
							$method_data[$result['code']] = $method;
						}
					} else {
						$method_data[$result['code']] = $method;
					}
				}
			}
	
			$quotes = array();
	
			foreach ($method_data as $key => $modules) {
				foreach ($modules as $k => $module) {
					$quotes[$module['code']] = array(
						'module_id' => $module['module_id'],
						'code' => $module['code'],
						'title' => $module['title'],
						'terms' => '',
						'sort_order' => $module['sort_order']
						);
				}
			}

			if (isset($this->session->data['payment_method']['code'])) {
				$order_data['payment_code'] = $this->session->data['payment_method']['code'];
			} else {
				$order_data['payment_code'] = '';
			}

			if (isset($this->session->data['payment_method']['title'])) {
				$order_data['payment_method'] = $this->session->data['payment_method']['title'];
			} elseif(isset($this->session->data['payment_method']['code'])) {
				$order_data['payment_method'] = $quotes[$this->session->data['payment_method']['code']]['title'];
			} else {
				$order_data['payment_method'] = '';
			}

			// 處理收件人資料
			if ($this->cart->hasShipping()) {

				// 如果是同訂購人
				if(isset($post['the_same']) && !empty($post['the_same'])) {
					$order_data['shipping_name'] = $post['payment_name'];
					$order_data['shipping_email'] = $post['payment_email'];
					$order_data['shipping_mobile'] = $post['payment_mobile'];
					$order_data['shipping_address'] = $post['payment_address'];
					$order_data['shipping_city_id'] = $post['payment_city_id'];
					$order_data['shipping_city']    = $payment_city['name'];
					$order_data['shipping_zone_id'] = $post['payment_zone_id'];
					$order_data['shipping_zone']    = $payment_zone['name'];
					$order_data['shipping_postcode'] = $payment_postcode;
				} else {
					$order_data['shipping_name'] = $post['shipping_name'];
					$order_data['shipping_email'] = $post['shipping_email'];
					$order_data['shipping_mobile'] = $post['shipping_mobile'];
					$order_data['shipping_address'] = $post['shipping_address'];
					$shipping_city = $this->model_localisation_city->getCity($post['shipping_city_id']);
					$order_data['shipping_city_id'] = $post['shipping_city_id'];
					$order_data['shipping_city']    = $shipping_city['name'];
					$shipping_zone = $this->model_localisation_zone->getZone($post['shipping_zone_id']);
					$order_data['shipping_zone_id'] = $post['shipping_zone_id'];
					$order_data['shipping_zone']    = $shipping_zone['name'];
					$order_data['shipping_postcode'] = $this->model_localisation_zone->getPostcodeByZone($post['shipping_zone_id']);
				}

			} else {
				$order_data['shipping_name'] = '';
				$order_data['shipping_mobile'] = '';
				$order_data['shipping_address'] = '';
				$order_data['shipping_city_id'] = '';
				$order_data['shipping_city'] = '';
				$order_data['shipping_postcode'] = '';
				$order_data['shipping_zone'] = '';
				$order_data['shipping_zone_id'] = '';
			}

			// 將購物車商品存入變數，準備存入order資料庫
			$order_data['products'] = $this->_products();

			// Gift Voucher
			$order_data['vouchers'] = array();

			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $voucher) {
					$order_data['vouchers'][] = array(
						'description'      => $voucher['description'],
						'code'             => token(10),
						'to_name'          => $voucher['to_name'],
						'to_email'         => $voucher['to_email'],
						'from_name'        => $voucher['from_name'],
						'from_email'       => $voucher['from_email'],
						'voucher_theme_id' => $voucher['voucher_theme_id'],
						'message'          => $voucher['message'],
						'amount'           => $voucher['amount']
					);
				}
			}

			$order_data['comment'] = !empty($post['comment']) ? $post['comment'] : '';

			$totals = array();
			$taxes = $this->cart->getTaxes();
			$total = 0;

			// Because __call can not keep var references so we put them into an array.
			$total_data = array(
				'totals' => &$totals,
				'taxes'  => &$taxes,
				'total'  => &$total
			);

			$sort_order = array();

			$results = $this->model_setting_extension->getExtensions('total');

			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
			}

			array_multisort($sort_order, SORT_ASC, $results);

			foreach ($results as $result) {
				if ($this->config->get('total_' . $result['code'] . '_status')) {
					$this->load->model('extension/total/' . $result['code']);

					// We have to put the totals in an array so that they pass by reference.
					$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
				}
			}

			$sort_order = array();

			foreach ($totals['total'] as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $totals['total']);

			$order_data['totals'] = $totals['total'];

			$order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
			$order_data['store_id'] = $this->config->get('config_store_id');
			$order_data['store_name'] = $this->config->get('config_name');

			if ($order_data['store_id']) {
				$order_data['store_url'] = $this->config->get('config_url');
			} else {
				$order_data['store_url'] = HTTP_SERVER;
			}

			$order_data['total'] = $total_data['total'];

			$order_data['language_id'] = $this->config->get('config_language_id');
			$order_data['currency_id'] = $this->currency->getId($this->session->data['currency']);
			$order_data['currency_code'] = $this->session->data['currency'];
			$order_data['currency_value'] = $this->currency->getValue($this->session->data['currency']);
			$order_data['ip'] = $this->request->server['REMOTE_ADDR'];

			if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
				$order_data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
			} elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
				$order_data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
			} else {
				$order_data['forwarded_ip'] = '';
			}

			if (isset($this->request->server['HTTP_USER_AGENT'])) {
				$order_data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
			} else {
				$order_data['user_agent'] = '';
			}

			if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
				$order_data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
			} else {
				$order_data['accept_language'] = '';
			}

			// // 目前用不到,往後在處理
			// $order_data['affiliate_id'] = 0; //經銷商代號
			// $order_data['commission'] = 0;   // 佣金
			// $order_data['marketing_id'] = 0;
			// $order_data['tracking'] = '';

			// print_r($order_data);

			$this->load->model('checkout/order');
			$this->load->model('checkout/cart');
			$order_id = $this->model_checkout_order->addOrder($order_data);

			if($order_id) {
				$this->session->data['order_id'] = $order_id;
				$this->response->redirect($this->url->link('checkout/confirm'));
			}
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_cart'),
			'href' => $this->url->link('checkout/cart')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('checkout/checkout', '', true)
		);

		$data['shipping_required'] = $this->cart->hasShipping();
		$data['action'] = $this->url->link('checkout/checkout');

		// 輸出 payment
		if(isset($this->request->post['payment_name'])) {
			$data['payment_name'] = $this->request->post['payment_name'];
		} elseif($this->customer->getName() !== null) {
			$data['payment_name'] = $this->customer->getName();
		} else {
			$data['payment_name'] = '';
		}

		if(isset($this->request->post['payment_email'])) {
			$data['payment_email'] = $this->request->post['payment_email'];
		} elseif($this->customer->getEmail() !== null) {
			$data['payment_email'] = $this->customer->getEmail();
		} else {
			$data['payment_email'] = '';
		}

		if(isset($this->request->post['payment_mobile'])) {
			$data['payment_mobile'] = $this->request->post['payment_mobile'];
		} elseif($this->customer->getMobile() !== null) {
			$data['payment_mobile'] = $this->customer->getMobile();
		} else {
			$data['payment_mobile'] = '';
		}

		// 處理地址
		$payment_address = array();
		if($this->customer->isLogged()) {
			$address_id = $this->customer->getAddressId();
			if($address_id) {
				// 代表有註冊，且住址資料完整
				$payment_address = $this->model_customer_address->getAddress($address_id);
			}
		}

		// $data['payment_country_id'] = $payment_country_id;
		$data['payment_cities'] = $this->model_localisation_city->getCities();

		if(isset($this->request->post['payment_city_id'])) {
			$payment_city_id = $this->request->post['payment_city_id'];
		} elseif(isset($payment_address['city_id'])) {
			$payment_city_id = $payment_address['city_id'];
		} else {
			$payment_city_id = '';
		}

		if(!empty($payment_city_id)) {
			$data['payment_zones'] = $this->model_localisation_zone->getZonesByCity($payment_city_id);
		} else {
			$data['payment_zones'] = array();
		}

		$data['payment_city_id'] = $payment_city_id;

		if(isset($this->request->post['payment_zone_id'])) {
			$payment_zone_id = $this->request->post['payment_zone_id'];
		} elseif(isset($payment_address['zone_id'])) {
			$payment_zone_id = $payment_address['zone_id'];
		} else {
			$payment_zone_id = '';
		}

		$data['payment_zone_id'] = $payment_zone_id;

		if (isset($this->request->post['payment_postcode'])) {
			$data['payment_postcode'] = $this->request->post['payment_postcode'];
		} elseif(!empty($payment_zone_id)) {
			$data['payment_postcode'] = $this->model_localisation_zone->getPostcodeByZone($payment_zone_id);
		} else {
			$data['payment_postcode'] = '';
		}

		if(isset($this->request->post['payment_address'])) {
			$data['payment_address'] = $this->request->post['payment_address'];
		} elseif(isset($payment_address['address'])) {
			$data['payment_address'] = $payment_address['address'];
		} else {
			$data['payment_address'] = '';
		}

		if (isset($this->session->data['payment_method']['code'])) {
			$data['payment_method'] = $this->session->data['payment_method']['code'];
		} else {
			$data['payment_method'] = '';
		}

		$data['shipping_cities'] = $this->model_localisation_city->getCities();

		if (isset($this->request->post['shipping_city_id'])) {
			$shipping_city_id = $this->request->post['shipping_city_id'];
		} else {
			$shipping_city_id = '';
		}

		if(!empty($shipping_city_id)) {
			$data['shipping_zones'] = $this->model_localisation_zone->getZonesByCity($shipping_city_id);
		} else {
			$data['shipping_zones'] = array();
		}

		$data['shipping_city_id'] = $shipping_city_id;

		if(isset($this->request->post['shipping_zone_id'])) {
			$shipping_zone_id = $this->request->post['shipping_zone_id'];
		} else {
			$shipping_zone_id = '';
		}

		$data['shipping_zone_id'] = $shipping_zone_id;

		if(isset($this->request->post['shipping_postcode'])) {
			$data['shipping_postcode'] = $this->request->post['shipping_postcode'];
		} elseif(!empty($shipping_zone_id)) {
			$data['shipping_postcode'] = $this->model_localisation_zone->getPostcodeByZone($shipping_zone_id);
		} else {
			$data['shipping_postcode'] = '';
		}

		if(isset($this->request->post['shipping_address'])) {
			$data['shipping_address'] = $this->request->post['shipping_address'];
		} else {
			$data['shipping_address'] = '';
		}

		// 載入相關模組
		$data['modules'] = array();
		$files = glob(DIR_APPLICATION . '/controller/extension/total/*.php');
		if ($files) {
			foreach ($files as $file) {
				$result = $this->load->controller('extension/total/' . basename($file, '.php'));
					
				if ($result) {
					$data['modules'][] = $result;
				}
			}
		}
		
		if (isset($this->session->data['comment'])) {
			$data['comment'] = $this->session->data['comment'];
		} elseif(isset($this->request->post['comment'])){
			$data['comment'] = $this->request->post['comment'];
		} else {
			$data['comment'] = '';
		}

		if ($this->config->get('config_checkout_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_checkout_id'));

			if ($information_info) {
				$data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('config_checkout_id'), true), $information_info['title'], $information_info['title']);
			} else {
				$data['text_agree'] = '';
			}
		} else {
			$data['text_agree'] = '';
		}

		if (isset($this->session->data['agree'])) {
			$data['agree'] = $this->session->data['agree'];
		} else {
			$data['agree'] = '';
		}

		// 顯示選擇的付款方式
		$data['payment'] = '';
		if(isset($this->session->data['payment_method']['code'])) {
			$payment_code = explode('.',$this->session->data['payment_method']['code']);
			$data['payment'] = $this->load->controller('extension/payment/' . $payment_code[0],$payment_code);
		}

		$data['get_city'] = $this->url->link('localisation/city/city');
		$data['get_zone'] = $this->url->link('localisation/zone/zone');
		$data['get_postcode'] = $this->url->link('localisation/zone/postcode');
		$data['shopping_cart'] = $this->_cart();
		$data['shopping_total'] = $this->_total();

		//處理驗證
		if(isset($this->error['warning'])){
			$data['warning'] = $this->error['warning'];
		} else {
			$data['warning'] = '';
		}

		$this->_output('checkout/checkout/index', $data);
	}

	protected function validate() {

		$this->load->model('customer/customer');

		if ((utf8_strlen(trim($this->request->post['payment_name'])) < 1) || (utf8_strlen(trim($this->request->post['payment_name'])) > 32)) {
			$this->error['warning']['payment_name'] = $this->language->get('error_name');
		}

		if ((utf8_strlen($this->request->post['payment_email']) > 96) || !filter_var($this->request->post['payment_email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['warning']['payment_email'] = $this->language->get('error_email');
		}

		if (!$this->customer->isLogged() && $this->model_customer_customer->getTotalCustomersByEmail($this->request->post['payment_email'])) {
			$this->error['warning']['payment_email'] = $this->language->get('error_exists_email');
		}

		if ((utf8_strlen($this->request->post['payment_mobile']) < 3) || (utf8_strlen($this->request->post['payment_mobile']) > 32)) {
			$this->error['warning']['payment_mobile'] = $this->language->get('error_mobile');
		}

		if (empty($this->request->post['payment_city_id'])) {
			$this->error['warning']['payment_city_id'] = $this->language->get('error_city_id');
		}

		if (empty($this->request->post['payment_zone_id'])) {
			$this->error['warning']['payment_zone_id'] = $this->language->get('error_zone_id');
		}

		if (empty($this->request->post['payment_address'])) {
			$this->error['warning']['payment_address'] = $this->language->get('error_address');
		}

		//shipping
		if(!isset($this->request->post['the_same'])){
			if ((utf8_strlen(trim($this->request->post['shipping_name'])) < 1) || (utf8_strlen(trim($this->request->post['shipping_name'])) > 32)) {
				$this->error['warning']['shipping_name'] = $this->language->get('error_name');
			}

			if ((utf8_strlen($this->request->post['shipping_email']) > 96) || !filter_var($this->request->post['shipping_email'], FILTER_VALIDATE_EMAIL)) {
				$this->error['warning']['shipping_email'] = $this->language->get('error_email');
			}

		if (!$this->customer->isLogged() && $this->model_customer_customer->getTotalCustomersByEmail($this->request->post['shipping_email'])) {
			$this->error['warning']['shipping_email'] = $this->language->get('error_exists_email');
		}

		if ((utf8_strlen($this->request->post['shipping_mobile']) < 3) || (utf8_strlen($this->request->post['shipping_mobile']) > 32)) {
			$this->error['warning']['shipping_mobile'] = $this->language->get('error_mobile');
		}

		if (empty($this->request->post['shipping_city_id'])) {
			$this->error['warning']['shipping_city_id'] = $this->language->get('error_city_id');
		}

		if (empty($this->request->post['shipping_zone_id'])) {
			$this->error['warning']['shipping_zone_id'] = $this->language->get('error_zone_id');
		}

		if (empty($this->request->post['shipping_address'])) {
			$this->error['warning']['shipping_address'] = $this->language->get('error_address');
		}
		}
		

		return !$this->error;
	}

	private function _products() {

		$products = array();
		foreach ($this->cart->getProducts() as $product) {

			// 處理購買規格
			$option_data = array();
			foreach ($product['option'] as $option) {
				$option_data[] = array(
					'product_option_id'       => $option['product_option_id'],
					'product_option_value_id' => $option['product_option_value_id'],
					'option_id'               => $option['option_id'],
					'option_value_id'         => $option['option_value_id'],
					'name'                    => $option['name'],
					'value'                   => $option['value'],
					'type'                    => $option['type']
				);
			}

			// 處理附加選項
			$additional_option_data = array();
			foreach ($product['additional_option'] as $additional_option) {
				$additional_option_data[] = array(
					'product_additional_option_id'       => $additional_option['product_additional_option_id'],
					'product_additional_option_value_id' => $additional_option['product_additional_option_value_id'],
					'additional_option_id'               => $additional_option['additional_option_id'],
					'additional_option_value_id'         => $additional_option['additional_option_value_id'],
					'name'                               => $additional_option['name'],
					'value'                              => $additional_option['value'],
					'type'                               => $additional_option['type']
				);
			}

			$products[] = array(
				'product_id'        => $product['product_id'],
				'name'              => $product['name'],
				'model'             => $product['model'],
				'option'            => $option_data,
				'additional_option' => $additional_option_data,
				'download'          => isset($product['download']) ? $product['download'] : '',
				'quantity'          => $product['quantity'],
				'subtract'          => $product['subtract'],
				'price'             => $product['price'],
				'total'             => $product['total'],
				'tax'               => '',//$this->tax->getTax($product['price'], $product['tax_class_id']),
				'reward'            => isset($product['reward']) ? $product['reward'] : ''
			);
		}
		return $products;
	}

	/**
     * 輸出購物車內容與樣板
     */
    private function _cart() {

    $this->load->language('component/cart');

    $this->load->model('product/product');
		$this->load->model('tool/image');

		$products = $this->cart->getProducts();

    if(!$products){
      exit('<script>alert("購物稱內無商品!請先回商城逛逛"); location.href="' . $this->url->link('product/category') . '";</script>');
    }

		if (isset($this->request->get['route'])) {
			$this->document->addLink($this->config->get('config_url'), 'canonical');
		}
		$data['product_category'] = $this->url->link('product/category');
		$data['cart_product'] = 'images/product/cart_product.jpg';


		foreach ($products as $key => $value) {
				$products_cart = $this->model_product_product->getProduct($value['product_id']);
				if (is_file(DIR_UPLOADS . $products_cart['path'] . $products_cart['image'])) {
					$image = $this->model_tool_image->resize($products_cart['path'].$products_cart['image'], 227, 227);
				} else {
					$image = $this->model_tool_image->resize($products_cart['path'].$products_cart['image'], 227, 227);
				}
				$count = $value['quantity'] * $value['price'];

				$data['cart'][] = array(
					'cart_id' 				=> $value['cart_id'],
					'product_id'			=> $value['product_id'],
					'options'				=> $value['option'],
					'additional_options'	=> $value['additional_option'],
					'quantity'				=> $value['quantity'],
					'href'						=> $this->url->link('product/product','&product_id='.$value['product_id']),
					'price'						=> $this->currency->format($value['price'], $this->config->get('config_currency')),
					'name'						=> $products_cart['name'],
					'image'						=> $image,
					'path'						=> $products_cart['path'],
					'ID'							=> sprintf('%05d',$value['product_id']),
					'model'						=> $products_cart['model'],
					'count'						=> $this->currency->format($count, $this->config->get('config_currency')),
				);
		}
        return $this->load->view('checkout/checkout/cart',$data);
	}
	
	private function _total() {

        $this->load->language('checkout/cart');
        $this->load->model('checkout/cart');

        // Totals
		$this->load->model('setting/extension');

		$totals = array();
		$taxes = $this->cart->getTaxes();
		$total = 0;
		// Because __call can not keep var references so we put them into an array.
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
					// We have to put the totals in an array so that they pass by reference.
					$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
				}
			}

			$sort_order = array();

			foreach ($totals['total'] as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $totals['total']);
		}

		$data['totals'] = array();

		foreach ($totals['total'] as $total) {
			if(is_numeric($total['value'])) {
				$text = $this->currency->format($total['value'], $this->session->data['currency']);
			} else {
				$text = $total['value'];
			}

			$data['totals'][] = array(
				'title' => $total['title'],
				'text'  => $text
				);
		}

		$data['free'] = '';
		$data['difference'] = '';

		if(!empty($totals['difference'])) {
			$data['free'] = sprintf($this->language->get('text_free'), $totals['free']);
			$data['difference'] = sprintf($this->language->get('text_difference'), $totals['difference']);
		}

        return $this->load->view('checkout/checkout/total',$data);
    }
}