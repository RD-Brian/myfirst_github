<?php
class ControllerAccountRegister extends Controller {
	private $error = array();

	public function index() {
		if ($this->customer->isLogged()) {
			$this->response->redirect($this->url->link('account/edit', '', true));
		}

		$this->load->language('account/register');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('customer/customer');
		//靜態data
		$data['login'] = $this->url->link('account/login');
		$data['action'] = $this->url->link('account/register', '', true);

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$customer_id = $this->model_customer_customer->addCustomer($this->request->post);
			$customer_approval = $this->model_customer_customer->getCustomerApproval($customer_id);
			if($customer_approval){
				$data['customer_approval'] = '註冊成功，待經過審核後方可成為會員!!';
			}
			// Clear any previous login attempts for unregistered accounts.
			else{
				$this->model_customer_customer->deleteLoginAttempts($this->request->post['email']);

				$this->customer->login($this->request->post['email'], $this->request->post['password']);

				unset($this->session->data['guest']);

				$this->response->redirect($this->url->link('account/edit'));
			}
		}


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

		if (isset($this->error['mobile'])) {
			$data['error_mobile'] = $this->error['mobile'];
		} else {
			$data['error_mobile'] = '';
		}

		if (isset($this->error['custom_field'])) {
			$data['error_custom_field'] = $this->error['custom_field'];
		} else {
			$data['error_custom_field'] = array();
		}

		if (isset($this->error['password'])) {
			$data['error_password'] = $this->error['password'];
		} else {
			$data['error_password'] = '';
		}

		if (isset($this->error['confirm'])) {
			$data['error_confirm'] = $this->error['confirm'];
		} else {
			$data['error_confirm'] = '';
		}

		if (isset($this->error['check'])) {
			$data['error_check'] = $this->error['check'];
		} else {
			$data['error_check'] = '';
		}



		$data['customer_groups'] = array();

		if (is_array($this->config->get('config_customer_group_display'))) {
			$this->load->model('customer/group');

			$customer_groups = $this->model_customer_group->getCustomerGroups();

			foreach ($customer_groups as $customer_group) {
				if (in_array($customer_group['customer_group_id'], $this->config->get('config_customer_group_display'))) {
					$data['customer_groups'][] = $customer_group;
				}
			}
		}

		if (isset($this->request->post['customer_group_id'])) {
			$data['customer_group_id'] = $this->request->post['customer_group_id'];
		} else {
			$data['customer_group_id'] = $this->config->get('config_customer_group_id');
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} else {
			$data['email'] = '';
		}

		if (isset($this->request->post['mobile'])) {
			$data['mobile'] = $this->request->post['mobile'];
		} else {
			$data['mobile'] = '';
		}

		if (isset($this->request->post['password'])) {
			$data['password'] = $this->request->post['password'];
		} else {
			$data['password'] = '';
		}

		// if (isset($this->request->post['check'])) {
		// 	$data['check'] = 1;
		// }

		// Custom Fields
		$data['custom_fields'] = array();
		
		$this->load->model('customer/custom_field');
		
		$custom_fields = $this->model_customer_custom_field->getCustomFields();
		
		foreach ($custom_fields as $custom_field) {
			if ($custom_field['location'] == 'account') {
				$data['custom_fields'][] = $custom_field;
			}
		}
		
		if (isset($this->request->post['custom_field']['account'])) {
			$data['register_custom_field'] = $this->request->post['custom_field']['account'];
		} else {
			$data['register_custom_field'] = array();
		}



		if (isset($this->request->post['confirm'])) {
			$data['confirm'] = $this->request->post['confirm'];
		} else {
			$data['confirm'] = '';
		}

		if (isset($this->request->post['newsletter'])) {
			$data['newsletter'] = $this->request->post['newsletter'];
		} else {
			$data['newsletter'] = '';
		}

		// Captcha
		if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('register', (array)$this->config->get('config_captcha_page'))) {
			$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'), $this->error);
		} else {
			$data['captcha'] = '';
		}

		if ($this->config->get('config_account_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));

			if ($information_info) {
				$data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('config_account_id'), true), $information_info['title'], $information_info['title']);
			} else {
				$data['text_agree'] = '';
			}
		} else {
			$data['text_agree'] = '';
		}

		if (isset($this->request->post['agree'])) {
			$data['agree'] = $this->request->post['agree'];
		} else {
			$data['agree'] = false;
		}

		// 載入相關模組
		$data['modules'] = array();
		$files = glob(DIR_APPLICATION . '/controller/extension/social/*.php');
		if ($files) {
			foreach ($files as $file) {
				$result = $this->load->controller('extension/social/' . basename($file, '.php'));
				if ($result) {
					$data['modules'][] = $result;
				}
			}
		}

		// 註冊須知
			if($this->config->get('customer_account_id') !== null) {
				$this->load->model('information/information');
				$notice = $this->model_information_information->getInformation($this->config->get('customer_account_id'));
				$data['notice'] = html_entity_decode($notice['description'], ENT_QUOTES, 'UTF-8');
			} else {
				$data['notice'] = '';
			}
			

		$this->_output('account/register', $data);
	}

	private function validate() {
		if ((utf8_strlen(trim($this->request->post['name'])) < 1) || (utf8_strlen(trim($this->request->post['name'])) > 32)) {
			$this->error['name'] = '姓名長度須介於1-32字';//$this->language->get('error_lastname');
		}

		if ((utf8_strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}

		if ($this->model_customer_customer->getTotalCustomersByEmail($this->request->post['email'])) {
			$this->error['warning'] = $this->language->get('error_exists');
		}

		if ((utf8_strlen($this->request->post['mobile']) != 10)) {
			$this->error['mobile'] = $this->language->get('error_mobile');
		}

		if(empty($this->request->post['label-01'])){
			$this->error['check'] = '請先同意會員條款並打勾';
		}

		// Customer Group
		if (isset($this->request->post['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->post['customer_group_id'], $this->config->get('config_customer_group_display'))) {
			$customer_group_id = $this->request->post['customer_group_id'];
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}


		if ((utf8_strlen(html_entity_decode($this->request->post['password'], ENT_QUOTES, 'UTF-8')) < 4) || (utf8_strlen(html_entity_decode($this->request->post['password'], ENT_QUOTES, 'UTF-8')) > 40)) {
			$this->error['password'] = $this->language->get('error_password');
		}

		if ($this->request->post['confirm'] != $this->request->post['password']) {
			$this->error['confirm'] = $this->language->get('error_confirm');
		}

		return !$this->error;
	}

}