<?php
class ControllerSupportContact extends Controller {

	private $error = array();

	public function index() {

		$this->load->language('support/contact');
		$this->load->model('support/contact');

		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));

		// 靜態data 
		$data['action'] = $this->url->link('support/contact');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			$post = $this->request->post;

			if (isset($this->request->server['HTTP_USER_AGENT'])) {
				$user_agent = $this->request->server['HTTP_USER_AGENT'];
			} else {
				$user_agent = '';
			}

			if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
				$accept_language = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
			} else {
				$accept_language = '';
			}

			$contact_data = array(
				'name'              => $post['name'],
				'email'             => $post['email'],
				'mobile'            => $post['mobile'],
				'description'       => $post['description'],
				'user_agent'        => $user_agent,
				'accept_language'   => $accept_language,
				'ip'                => $this->request->server['REMOTE_ADDR']
				);

			$contact_id = $this->model_support_contact->addContact($contact_data);
			$data['success'] = '您已成功送出訊息。';

			$this->request->post = array();

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

		if (isset($this->error['description'])) {
			$data['error_description'] = $this->error['description'];
		} else {
			$data['error_description'] = '';
		}

		$data['action'] = $this->url->link('support/contact');

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

		if (isset($this->request->post['description'])) {
			$data['description'] = $this->request->post['description'];
		} else {
			$data['description'] = '';
		}

		$data['action'] = $this->url->link('support/contact');
		$this->_output('support/contact', $data);
	}

	private function validate() {

		$verify = new Verify();

		if ((utf8_strlen(trim($this->request->post['name'])) < 1) || (utf8_strlen(trim($this->request->post['name'])) > 32)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if(!empty($this->request->post['email'])) {
			if ((utf8_strlen($this->request->post['email']) > 96)) {
					$this->error['email'] = $this->language->get('error_email_text');
				} else {
					if(!$verify->isEmail($this->request->post['email'])) {
						$this->error['email'] = $this->language->get('error_email_format');
					}
				}

		} else {
			$this->error['email'] = $this->language->get('error_email_empty');
		}

		if ((utf8_strlen(trim($this->request->post['description'])) < 1) || (utf8_strlen(trim($this->request->post['description'])) > 32)) {
			$this->error['description'] = $this->language->get('error_description_empty');
		}
print_r($this->error);
		return !$this->error;
	}
}
