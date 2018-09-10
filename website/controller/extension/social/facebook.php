<?php

class ControllerExtensionSocialFacebook extends Controller {



	/**

	 * 顯示按鈕

	 * @return [type] [description]

	 */

	public function index() {

		if ($this->config->get('social_facebook_status')) {

			$this->language->load('extension/social/facebook');

			$this->language->load('social/facebook');

			$current_url = 'redirect=' . $this->url->getCurrentUrl();

			$data['href'] = $this->url->link('extension/social/facebook/api',$current_url ,true);

			return $this->load->view('extension/social/facebook', $data);

		}

	}



	public function api() {

		$this->session->data['social_facebook_url'] = $this->request->get['redirect'];

		// 先判定是要執行註冊還是登入

		require_once(modification(DIR_SYSTEM . 'library/Facebook/autoload.php'));

		$fb = new Facebook\Facebook([

			'app_id'  => $this->config->get('social_facebook_appid'),

			'app_secret' => $this->config->get('social_facebook_app_secret'),

			'default_graph_version' => $this->config->get('social_facebook_graph_version')

			]);



		$helper = $fb->getRedirectLoginHelper();

		$permissions = ['email','public_profile']; // Optional permissions

		$callbackUrl = $this->url->link('extension/social/facebook/callback','' ,true);

		$loginUrl = $helper->getLoginUrl($callbackUrl, $permissions);

		$this->response->redirect($loginUrl);

	}



	public function callback() {



		$this->language->load('extension/social/facebook');



		require_once(modification(DIR_SYSTEM . 'library/Facebook/autoload.php'));

		$fb = new Facebook\Facebook([

			'app_id'  => $this->config->get('social_facebook_appid'),

			'app_secret' => $this->config->get('social_facebook_app_secret'),

			'default_graph_version' => $this->config->get('social_facebook_graph_version')

			]);



		$helper = $fb->getRedirectLoginHelper();



		// 處理 Cross-site request forgery validation failed. Required param “state” missing from persistent data

		$helper->getPersistentDataHandler()->set('state', $this->request->get['state']);



		try {

			$accessToken = $helper->getAccessToken();

		} catch(Facebook\Exceptions\FacebookResponseException $e) {

			// When Graph returns an error

			echo 'Graph returned an error: ' . $e->getMessage();

			exit;

		} catch(Facebook\Exceptions\FacebookSDKException $e) {

			// When validation fails or other local issues

			echo 'Facebook SDK returned an error: ' . $e->getMessage();

			exit;

		}



		if (!isset($accessToken)) {

			if ($helper->getError()) {

				header('HTTP/1.0 401 Unauthorized');

				echo "Error: " . $helper->getError() . "\n";

				echo "Error Code: " . $helper->getErrorCode() . "\n";

				echo "Error Reason: " . $helper->getErrorReason() . "\n";

				echo "Error Description: " . $helper->getErrorDescription() . "\n";

			} else {

				header('HTTP/1.0 400 Bad Request');

				echo 'Bad request';

			}

			exit;

		}



		$this->session->data['fb_access_token'] = (string)$accessToken;



		// 取得個人檔案

		$user = $this->_getUser($fb);



		$this->load->model('customer/customer');



		$url = $this->session->data['social_facebook_url'];

		unset($this->session->data['social_facebook_url']);



		// 先偵測是否為登入狀態

		if($this->customer->isLogged()) {

			// 如果則進行綁定處理

			$this->_bind($this->customer->getId(),$user);

			$this->response->redirect($url);

		} else {

			// 沒有的話則進行登入或註冊處理

			// 如果有給予mail授權

			if(isset($user['email']) && !empty($user['email'])) {

				$customer = $this->model_customer_customer->getCustomerByEmail($user['email']);

				if($customer) {

					// 如果這裡有的話->檢查使否需要綁定

					$this->_bind($customer['customer_id'],$user);

				}

			}

			

			if(!$customer) {

				// 沒有的話則檢查ID是否有註冊過->避免有的人不給mail時系統採用ID註冊

				$customer = $this->model_customer_customer->getCustomerBySocial($user['id'],'facebook');

			}



			if($customer) {

				//有資料的話就進行登入

				$this->_login($customer);

				$this->response->redirect($url);

			} else {

				// 都沒有資料的話就進行註冊

				$this->_register($user);

				$this->response->redirect($this->url->link('account/edit'));

			}

		}

	}



	// 獲取個人檔案

	private function _getUser($fb) {

		try {

			// Returns a `Facebook\FacebookResponse` object

			$response = $fb->get('/me?fields=id,name,email', $this->session->data['fb_access_token']);

		} catch(Facebook\Exceptions\FacebookResponseException $e) {

			echo 'Graph returned an error: ' . $e->getMessage();

			exit;

		} catch(Facebook\Exceptions\FacebookSDKException $e) {

			echo 'Facebook SDK returned an error: ' . $e->getMessage();

			exit;

		}

		return $response->getGraphUser();

	}



	/**

	 * Facebook 註冊

	 * @return [type] [description]

	 */

	private function _register($user) {



		$post = array();

		$social = array();



		// 如果有mail就使用mail註冊

		if(isset($user['email']) && !empty($user['email'])) {

			$post['email'] = $user['email'];

		} else {

			$post['email'] = '';

		}



		if(isset($user['name']) && !empty($user['name'])) {

			$post['name'] = $user['name'];

		} else {

			$post['name'] = $user['id'];

		}



		$post['password'] = $user['id'];

		$post['customer_group_id'] = $this->config->get('social_facebook_customer_group_id');



		$customer_id = $this->model_customer_customer->addSocialCustomer($post);



		$social['customer_id'] = $customer_id;

		$social['id'] = $user['id'];

		$social['social'] = 'facebook';

		$customer_social_id = $this->model_customer_customer->addCustomerSocial($social);



		// $customer_approval = $this->model_customer_customer->getCustomerApproval($customer_id);

		// 將ID記錄到session成為登入狀態

		$this->session->data['customer_id'] = $customer_id;

	}



	/**

	 * 綁定帳號

	 * @return [type] [description]

	 */

	private function _bind($customer_id,$user) {

		$social = $this->model_customer_customer->getCustomerSocial($user['id'],'facebook');

		if(!$social) {

			// 沒有這個社群資料才進行綁定

			$post['customer_id'] = $customer_id;

			$post['id'] = $user['id'];

			$post['social'] = 'facebook';

			$customer_social_id = $this->model_customer_customer->addCustomerSocial($post);

		}

	}



	/**

	 * Facebook 登入

	 * @param  string $value [description]

	 * @return [type]        [description]

	 */

	private function _login($customer) {

		$this->session->data['customer_id'] = $customer['customer_id'];

		$this->db->query("UPDATE " . DB_PREFIX . "customer SET language_id = '" . (int)$this->config->get('config_language_id') . "', ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "' WHERE customer_id = '" . (int)$customer['customer_id'] . "'");

	}

}

