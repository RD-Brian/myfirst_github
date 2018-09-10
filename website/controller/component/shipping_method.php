<?php
class ControllerComponentShippingMethod extends Controller {

	private $error = array();

	public function index() {
		if (isset($this->session->data['shipping_address'])) {
			$this->load->language('component/shipping_method');

			$quote_data = array();

			$this->load->model('setting/extension');

			$results = $this->model_setting_extension->getExtensions('shipping');

			foreach ($results as $result) {

					$this->load->model('extension/shipping/' . $result['code']);
					$quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($this->session->data['shipping_address']);

					if ($quote) {
						foreach ($quote as $key => $info) {
							$quote_data[$info['code']][] = array(
								'title'      => $info['title'],
								'quote'      => $info['quote'],
								'sort_order' => $info['sort_order'],
								'error'      => $info['error']
								);
						}
					}
			}

			$quotes = array();

			foreach ($quote_data as $key => $modules) {
				foreach ($modules as $k => $module) {
					$quotes[$key] = array(
						'title' => $module['title'],
						'quote' => $module['quote'],
						'sort_order' => $module['sort_order'],
						'error' => $module['error']
						);
				}
			}

			$sort_order = array();

			foreach ($quotes as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $quotes);

			$this->session->data['shipping_methods'] = $quotes;

			if ($this->session->data['shipping_methods']) {
				$data['shipping_methods'] = $this->session->data['shipping_methods'];
			} else {
				$data['error']['warning'] = sprintf($this->language->get('error_no_shipping'), $this->url->link('information/contact'));
			}

			// 處理預設的配送方式
			if(!isset($this->session->data['shipping_method']) || empty($this->session->data['shipping_method'])) {
				$shipping_keys = array_keys($this->session->data['shipping_methods']);
				$shipping = explode('.', $shipping_keys[0]);
				$this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping_keys[0]]['quote'][$shipping[0]];
			}

			$data['shipping_method'] = $this->session->data['shipping_method'];
			
			return $this->load->view('component/shipping_method/index', $data);
		}
	}

	public function get() {
		$data['shipping_methods'] = $this->index();
		$this->_disableFrame();
		$this->_output('component/shipping_method/get',$data);
	}

	public function save() {
		$this->load->language('checkout/checkout');

		$json = array();

		// error
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$post = $this->request->post;

			$this->session->data['shipping_name'] = $post['shipping_name'];
			$this->session->data['shipping_mobile'] = $post['shipping_mobile'];
			$this->session->data['shipping_email'] = $post['shipping_email'];
			$this->session->data['shipping_address'] = array(
				'address_id' => '',
				'country_id' => $post['shipping_country_id'],
				'city_id' => $post['shipping_city_id'],
				'zone_id' => $post['shipping_zone_id'],
				'postcode' => $post['shipping_postcode'],
				'address' => $post['shipping_address']
			);

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

		if ((utf8_strlen(trim($this->request->post['shipping_name'])) < 1) || (utf8_strlen(trim($this->request->post['shipping_name'])) > 32)) {
			$this->error['warning']['shipping_name'] = $this->language->get('error_name');
		}

		if ((utf8_strlen($this->request->post['shipping_email']) > 96) || !filter_var($this->request->post['shipping_email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['warning']['shipping_email'] = $this->language->get('error_email');
		}

		if ((utf8_strlen($this->request->post['shipping_mobile']) < 3) || (utf8_strlen($this->request->post['shipping_mobile']) > 32)) {
			$this->error['warning']['shipping_mobile'] = $this->language->get('error_mobile');
		}

		if (empty($this->request->post['shipping_country_id'])) {
			$this->error['warning']['shipping_country_id'] = $this->language->get('error_country_id');
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

		return !$this->error;
	}

	public function quote() {
		$this->load->language('extension/total/shipping');

		$json = array();

		if (!$this->cart->hasProducts()) {
			$json['error']['warning'] = $this->language->get('error_product');
		}

		if (!$this->cart->hasShipping()) {
			$json['error']['warning'] = sprintf($this->language->get('error_no_shipping'), $this->url->link('information/contact'));
		}

		// if ($this->request->post['country_id'] == '') {
		// 	$json['error']['country'] = $this->language->get('error_country');
		// }

		// if ($this->request->post['city_id'] == '') {
		// 	$json['error']['city'] = $this->language->get('error_city');
		// }

		// if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '') {
		// 	$json['error']['zone'] = $this->language->get('error_zone');
		// }

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

		if ($country_info && $country_info['postcode_required'] && (utf8_strlen(trim($this->request->post['postcode'])) < 2 || utf8_strlen(trim($this->request->post['postcode'])) > 10)) {
			$json['error']['postcode'] = $this->language->get('error_postcode');
		}

		if (!$json) {
			$this->tax->setShippingAddress($this->request->post['country_id'], $this->request->post['city_id']);

			if ($country_info) {
				$country = $country_info['name'];
				$iso_code_2 = $country_info['iso_code_2'];
				$iso_code_3 = $country_info['iso_code_3'];
				$address_format = $country_info['address_format'];
			} else {
				$country = '';
				$iso_code_2 = '';
				$iso_code_3 = '';
				$address_format = '';
			}

			$this->load->model('localisation/city');

			$city_info = $this->model_localisation_city->getCity($this->request->post['city_id']);

			if ($city_info) {
				$city = $city_info['name'];
				$city_code = $city_info['code'];
			} else {
				$city = '';
				$city_code = '';
			}

			$this->load->model('localisation/zone');

			$zone_info = $this->model_localisation_zone->getZone($this->request->post['zone_id']);

			if ($zone_info) {
				$zone = $zone_info['name'];
			} else {
				$zone = '';
			}

			$this->session->data['shipping_address'] = array(
				'name'      => '',
				'company'        => '',
				'address_1'      => '',
				'address_2'      => '',
				'postcode'       => $this->request->post['postcode'],
				'city_id'        => $this->request->post['city_id'],
				'city'           => $city,
				'city_code'      => $city_code,
				'zone_id'        => $this->request->post['zone_id'],
				'zone'           => $zone,
				'country_id'     => $this->request->post['country_id'],
				'country'        => $country,
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format
			);

			$this->session->data['shipping_address'] = array(
				'name'      => '',
				'company'        => '',
				'address_1'      => '',
				'address_2'      => '',
				'postcode'       => $this->request->post['postcode'],
				'city_id'        => $this->request->post['city_id'],
				'city'           => $city,
				'city_code'      => $city_code,
				'zone_id'        => $this->request->post['zone_id'],
				'zone'           => $zone,
				'country_id'     => $this->request->post['country_id'],
				'country'        => $country,
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format
			);

			$quote_data = array();

			$this->load->model('setting/extension');

			$results = $this->model_setting_extension->getExtensions('shipping');

			foreach ($results as $result) {

					$this->load->model('extension/shipping/' . $result['code']);
					$quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($this->session->data['shipping_address']);

					if ($quote) {
						foreach ($quote as $key => $info) {
							$quote_data[$info['code']][] = array(
								'title'      => $info['title'],
								'quote'      => $info['quote'],
								'sort_order' => $info['sort_order'],
								'error'      => $info['error']
								);
						}
					}
			}

			$quotes = array();

			foreach ($quote_data as $key => $modules) {
				foreach ($modules as $k => $module) {
					$quotes[$key] = array(
						'title' => $module['title'],
						'quote' => $module['quote'],
						'sort_order' => $module['sort_order'],
						'error' => $module['error']
						);
				}
			}

			$sort_order = array();

			foreach ($quotes as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $quotes);

			$this->session->data['shipping_methods'] = $quotes;

			if ($this->session->data['shipping_methods']) {
				$json['shipping_method'] = $this->session->data['shipping_methods'];
			} else {
				$json['error']['warning'] = sprintf($this->language->get('error_no_shipping'), $this->url->link('information/contact'));
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function shipping() {
		$this->load->language('extension/total/shipping');

		$json = array();

		if (!empty($this->request->post['shipping_method'])) {
			$shipping = explode('.', $this->request->post['shipping_method']);

			if (!isset($shipping[0]) || !isset($shipping[1]) || !isset($this->session->data['shipping_methods'][$this->request->post['shipping_method']]['quote'][$shipping[0]])) {
				$json['warning'] = $this->language->get('error_shipping');
			}
		} else {
			$json['warning'] = $this->language->get('error_shipping');
		}

		if (!$json) {
			$shipping = explode('.', $this->request->post['shipping_method']);

			$this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$this->request->post['shipping_method']]['quote'][$shipping[0]];

			$this->session->data['success'] = $this->language->get('text_success');

			$json['redirect'] = $this->url->link('checkout/cart');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}