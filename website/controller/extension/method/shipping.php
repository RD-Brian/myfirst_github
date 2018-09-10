<?php
class ControllerExtensionMethodShipping extends Controller {
	public function index() {
		if ($this->config->get('total_shipping_status') && $this->config->get('total_shipping_estimator') && $this->cart->hasShipping()) {
			$this->load->language('extension/method/shipping');

			// URL
			$data['get_city'] = $this->url->link('localisation/city/city');
			$data['get_zone'] = $this->url->link('localisation/zone/zone');
			$data['get_postcode'] = $this->url->link('localisation/zone/postcode');
			$data['post_shipping_method'] = $this->url->link('extension/method/shipping/shipping');
			$data['shipping_quote'] = $this->url->link('extension/method/shipping/quote');

			if (isset($this->session->data['shipping_address']['country_id'])) {
				$country_id = (int)$this->session->data['shipping_address']['country_id'];
			} else {
				$country_id = $this->config->get('config_country_id');
			}

			$data['country_id'] = $country_id;

			$this->load->model('localisation/country');
			$data['countries'] = $this->model_localisation_country->getCountries();

			$this->load->model('localisation/city');
			$data['cities'] = $this->model_localisation_city->getCityByCountry($country_id);

			if (isset($this->session->data['shipping_address']['city_id'])) {
				$data['city_id'] = (int)$this->session->data['shipping_address']['city_id'];
				$this->load->model('localisation/zone');
				$data['zones'] = $this->model_localisation_zone->getZonesByCity($this->session->data['shipping_address']['city_id']);
			} else {
				$data['city_id'] = '';
				$data['zones'] = array();
			}

			if (isset($this->session->data['shipping_address']['zone_id'])) {
				$zone_id = $this->session->data['shipping_address']['zone_id'];
			} else {
				$zone_id = '';
			}

			$data['zone_id'] = $zone_id;

			if (isset($this->session->data['shipping_address']['postcode'])) {
				$data['postcode'] = (int)$this->session->data['shipping_address']['postcode'];
			} else if(!empty($zone_id)) {
				$this->load->model('localisation/zone');
				$data['postcode'] = $this->model_localisation_zone->getPostcodeByZone($zone_id);
			} else {
				$data['postcode'] = '';
			}

			if (isset($this->session->data['shipping_method'])) {
				$data['shipping_method'] = $this->session->data['shipping_method']['code'];
			} else {
				$data['shipping_method'] = '';
			}

			return $this->load->view('extension/method/shipping', $data);
		}
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

		if ($this->request->post['country_id'] == '') {
			$json['error']['country'] = $this->language->get('error_country');
		}

		if ($this->request->post['city_id'] == '') {
			$json['error']['city'] = $this->language->get('error_city');
		}

		if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '') {
			$json['error']['zone'] = $this->language->get('error_zone');
		}

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

			$this->session->data['payment_address'] = array(
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