<?php
class ControllerAccountEdit extends Controller {
	private $error = array();

	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/edit', '', true);

			$this->response->redirect($this->url->link('common/home', '', true));
		}
		
		$this->load->language('account/edit');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('customer/customer');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_customer_customer->editCustomer($this->customer->getId(), $this->request->post);
			$updateAddress = $this->model_customer_customer->updateAddress($this->customer->getId(), $this->request->post);
			$this->model_customer_customer->editAddressId($this->customer->getId(),$updateAddress);
			$data['edit_success'] = $this->language->get('text_success');
		}

		//靜態data
		$data['action'] = $this->url->link('account/edit', '', true);

		$data['account_menu'] = array(
			1 => array('name' => '修改資料','href' => $this->url->link('account/edit', '', true)),
			2 => array('name' => '變更密碼','href' => $this->url->link('account/password', '', true)),
			3 => array('name' => '訂單查詢','href' => $this->url->link('account/order', '', true)),
			4 => array('name' => '登出會員','href' => $this->url->link('account/logout', '', true))
		);
		// error
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['email'])) {
			$data['error_email'] = $this->error['email'];
		} else {
			$data['error_email'] = '';
		}

		if (isset($this->error['address'])) {
			$data['error_address'] = $this->error['address'];
		} else {
			$data['error_address'] = '';
		}

		if (isset($this->error['mobile'])) {
			$data['error_mobile'] = $this->error['mobile'];
		} else {
			$data['error_mobile'] = '';
		}

		if (isset($this->error['birthday'])) {
			$data['error_birthday'] = $this->error['birthday'];
		} else {
			$data['error_birthday'] = '';
		}


		if (isset($this->error['custom_field'])) {
			$data['error_custom_field'] = $this->error['custom_field'];
		} else {
			$data['error_custom_field'] = array();
		}

		//接收post動態資料
		if ($this->request->server['REQUEST_METHOD'] != 'POST') {
			$customer_info = $this->model_customer_customer->getCustomer($this->customer->getId());
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($customer_info)) {
			$data['name'] = $customer_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} elseif (!empty($customer_info)) {
			$data['email'] = $customer_info['email'];
		} else {
			$data['email'] = '';
		}

		if (isset($this->request->post['address'])) {
			$data['address'] = $this->request->post['address'];
		} elseif (!empty($customer_info)) {
			$data['address'] = $customer_info['address'];
		} else {
			$data['address'] = '';
		}

		if (isset($this->request->post['mobile'])) {
			$data['mobile'] = $this->request->post['mobile'];
		} elseif (!empty($customer_info)) {
			$data['mobile'] = $customer_info['mobile'];
		} else {
			$data['mobile'] = '';
		}

		if (isset($this->request->post['birthday'])) {
			$data['birthday'] = $this->request->post['birthday'];
		} elseif (!empty($customer_info)) {
			$data['birthday'] = $customer_info['birthday'];
		} else {
			$data['birthday'] = '';
		}

		if (isset($this->request->post['sex'])) {
			$data['sex'] = $this->request->post['sex'];
		} elseif (!empty($customer_info)) {
			$data['sex'] = $customer_info['sex'];
		} else {
			$data['sex'] = '';
		}



		//選擇地區
		$this->load->model('localisation/city');
		$this->load->model('localisation/zone');

		if(isset($this->session->data['payment_name'])) {
			$data['payment_name'] = $this->session->data['payment_name'];
		} elseif(isset($this->request->post['payment_name'])) {
			$data['payment_name'] = $this->request->post['payment_name'];
		} else {
			$data['payment_name'] = '';
		}

		if(isset($this->session->data['payment_email'])) {
			$data['payment_email'] = $this->session->data['payment_email'];
		} elseif(isset($this->request->post['payment_email'])) {
			$data['payment_email'] = $this->request->post['payment_email'];
		} else {
			$data['payment_email'] = '';
		}

		if(isset($this->session->data['payment_mobile'])) {
			$data['payment_mobile'] = $this->session->data['payment_mobile'];
		} elseif(isset($this->request->post['payment_mobile'])) {
			$data['payment_mobile'] = $this->request->post['payment_mobile'];
		} else {
			$data['payment_mobile'] = '';
		}


		$data['payment_cities'] = $this->model_localisation_city->getCityByCountry();

		if(isset($this->request->post['payment_city_id'])) {
			$payment_city_id = $this->request->post['payment_city_id'];
		} else {
			$payment_city_id = $customer_info['city_id'];
		}

		if(!empty($payment_city_id)) {
			$data['payment_zones'] = $this->model_localisation_zone->getZonesByCity($payment_city_id);
		} else {
			$data['payment_zones'] = array();
		}

		$data['payment_city_id'] = $payment_city_id;

		if(isset($this->request->post['payment_zone_id'])) {
			$payment_zone_id = $this->request->post['payment_zone_id'];
		} else {
			$payment_zone_id = $customer_info['zone_id'];
		}

		$data['payment_zone_id'] = $payment_zone_id;

		if(isset($this->request->post['payment_postcode'])) {
			$data['payment_postcode'] = $this->request->post['payment_postcode'];
		} elseif(!empty($payment_zone_id)) {
			$data['payment_postcode'] = $this->model_localisation_zone->getPostcodeByZone($payment_zone_id);
		} else {
			$data['payment_postcode'] = '';
		}

		if (isset($this->session->data['payment_address']['address'])) {
			$data['payment_address'] = $this->session->data['payment_address']['address'];
		} elseif(isset($this->request->post['payment_address'])) {
			$data['payment_address'] = $this->request->post['payment_address'];
		} else {
			$data['payment_address'] = '';
		}

		if (isset($this->session->data['payment_method']['code'])) {
			$data['payment_method'] = $this->session->data['payment_method']['code'];
		} elseif(isset($this->request->post['payment_method'])) {
			$data['payment_method'] = $this->request->post['payment_method'];
		} else {
			$data['payment_method'] = '';
		}
		//地址ajax
		$data['get_city'] = $this->url->link('localisation/city/city');
		$data['get_zone'] = $this->url->link('localisation/zone/zone');
		$data['get_postcode'] = $this->url->link('localisation/zone/postcode');



		$this->_output('account/edit', $data);
	}

	protected function validate() {
		if ((utf8_strlen(trim($this->request->post['name'])) < 1) || (utf8_strlen(trim($this->request->post['name'])) > 32)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if ((utf8_strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}

		if (($this->customer->getEmail() != $this->request->post['email']) && $this->model_account_customer->getTotalCustomersByEmail($this->request->post['email'])) {
			$this->error['warning'] = $this->language->get('error_exists');
		}

		if ((utf8_strlen($this->request->post['mobile']) < 3) || (utf8_strlen($this->request->post['mobile']) > 32)) {
			$this->error['mobile'] = $this->language->get('error_mobile');
		}

		if(empty($this->request->post['birthday'])){
			$this->error['birthday'] = $this->language->get('error_birthday');
		}

		if ((utf8_strlen($this->request->post['address']) < 1) || (utf8_strlen($this->request->post['address']) > 96) || empty($this->request->post['payment_city_id']) ||empty($this->request->post['payment_zone_id'])) {
			$this->error['address'] = $this->language->get('error_address');
		}

		return !$this->error;
	}
}