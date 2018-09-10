<?php
class ControllerAjaxAccount extends Controller {

	private $error = array();

	public function index() {}
	public function login() {
		$json = '';
		$this->load->model('customer/customer');
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->login_validate()) {

			// Unset guest
			unset($this->session->data['guest']);

			$json = $this->language->get('text_success');
		} else {
			if($this->error) {
				foreach ($this->error as $key => $error) {
					$json .= $error;
				}
			}
			$json = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	protected function login_validate() {
		// Check how many login attempts have been made.
		$login_info = $this->model_customer_customer->getLoginAttempts($this->request->post['email']);

		if ($login_info && ($login_info['total'] >= $this->config->get('config_login_attempts')) && strtotime('-1 hour') < strtotime($login_info['date_modified'])) {
			$this->error['warning'] = $this->language->get('error_attempts');
		}

		// Check if customer has been approved.
		$customer_info = $this->model_customer_customer->getCustomerByEmail($this->request->post['email']);

		if ($customer_info && !$customer_info['status']) {
			$this->error['warning'] = $this->language->get('error_approved');
		}

		if (!$this->error) {
			if (!$this->customer->login($this->request->post['email'], $this->request->post['password'])) {
				$this->error['warning'] = $this->language->get('error_login');

				$this->model_customer_customer->addLoginAttempt($this->request->post['email']);
			} else {
				$this->model_customer_customer->deleteLoginAttempts($this->request->post['email']);
			}
		}

		return !$this->error;
	}
}
