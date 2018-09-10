<?php
class ControllerComponentPaymentMethod extends Controller {

	private $error = array();

	public function index() {
		$this->load->language('component/checkout');

		// 如果沒有payment_address,但是是登入狀態
		// 那就把customer的地址資料記錄到payment_address
		if(!isset($this->session->data['payment_address']) && $this->customer->isLogged()) {
			$address_id = $this->customer->getAddressId();
			//如果有address_id代表為曾購物過或者以補登資料的會員
			//那就取出會員地址資料寫入payment_address
			if(!empty($address_id)) {
				$this->load->model('customer/address');
				$address = $this->model_customer_address->getAddress($address_id);
				$this->session->data['payment_address'] = array(
					'address_id' => $address_id,
					'country_id' => $address['country_id'],
					'city_id' => $address['city_id'],
					'zone_id' => $address['zone_id'],
					'postcode' => $address['postcode'],
					'address' => $address['address']
				);
			}
		}

		if (isset($this->session->data['payment_address'])) {

			// Totals
			$totals = array();
			$taxes = $this->cart->getTaxes();
			$total = 0;

			// Because __call can not keep var references so we put them into an array.
			$total_data = array(
				'totals' => &$totals,
				'taxes'  => &$taxes,
				'total'  => &$total
			);
			
			$this->load->model('setting/extension');

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

			// Payment Methods
			$method_data = array();

			$results = $this->model_setting_extension->getExtensions('payment');

			$recurring = $this->cart->hasRecurringProducts();

			foreach ($results as $result) {
					$this->load->model('extension/payment/' . $result['code']);

					$method = $this->{'model_extension_payment_' . $result['code']}->getMethod($this->session->data['payment_address'], $total);

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

			$sort_order = array();

			foreach ($quotes as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $quotes);

			$this->session->data['payment_methods'] = $quotes;
		}

		if (isset($this->session->data['payment_methods'])) {
			$data['payment_methods'] = $this->session->data['payment_methods'];
		} else {
			$data['payment_methods'] = array();
		}

		if (isset($this->session->data['payment_method']['code'])) {
			$data['code'] = $this->session->data['payment_method']['code'];
		} else {
			$data['code'] = '';
		}

		return $this->load->view('component/payment_method/index', $data);
	}

	public function get() {
		$data['payment_methods'] = $this->index();
		$this->_disableFrame();
		$this->_output('component/payment_method/get',$data);
	}

	public function save() {
		$this->load->language('component/payment_method');

		$json = array();

		// error
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			// 沒有登入且沒有會員地址才紀錄
			if(!$this->customer->isLogged() && !$this->customer->getAddressId()) {
				$post = $this->request->post;
				$this->session->data['payment_name'] = $post['payment_name'];
				$this->session->data['payment_mobile'] = $post['payment_mobile'];
				$this->session->data['payment_email'] = $post['payment_email'];
				$this->session->data['payment_address'] = array(
					'address_id' => '',
					'country_id' => $post['payment_country_id'],
					'city_id' => $post['payment_city_id'],
					'zone_id' => $post['payment_zone_id'],
					'postcode' => $post['payment_postcode'],
					'address' => $post['payment_address']
				);
			}
			
			$json = array(
				'success' => 'success',
				'msg' => 'ok'
			);
		} else {
			$json = $this->error;
		}

		$this->_json($json);
	}

	protected function validate() {

		$this->load->model('customer/customer');

		if(!$this->customer->isLogged()) {

		

		if ((utf8_strlen(trim($this->request->post['payment_name'])) < 1) || (utf8_strlen(trim($this->request->post['payment_name'])) > 32)) {
			$this->error['warning']['payment_name'] = $this->language->get('error_name');
		}

		if ((utf8_strlen($this->request->post['payment_email']) > 96) || !filter_var($this->request->post['payment_email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['warning']['payment_email'] = $this->language->get('error_email');
		}

		if ($this->model_customer_customer->getTotalCustomersByEmail($this->request->post['payment_email'])) {
			$this->error['warning']['payment_email'] = $this->language->get('error_exists_email');
		}

		if ((utf8_strlen($this->request->post['payment_mobile']) < 3) || (utf8_strlen($this->request->post['payment_mobile']) > 32)) {
			$this->error['warning']['payment_mobile'] = $this->language->get('error_mobile');
		}

		if (empty($this->request->post['payment_country_id'])) {
			$this->error['warning']['payment_country_id'] = $this->language->get('error_country_id');
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

	}

		return !$this->error;
	}
}
