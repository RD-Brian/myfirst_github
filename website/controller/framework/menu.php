<?php
class ControllerFrameworkMenu extends Controller {

	private $controller = 'framework/menu';
	
	public function index() {
		$this->load->language($this->controller);

		//靜態data
			//menu
			$data['home'] = $this->url->link('common/home');
			$data['news'] = $this->url->link('news/category');
			$data['about'] = $this->url->link('information/about');
			$data['notice'] = $this->url->link('information/notice');
			$data['contact'] = $this->url->link('support/contact');
			$data['article'] = $this->url->link('article/category');
			$data['problem'] = $this->url->link('problem/problem');
			$data['shop'] = $this->url->link('product/category');
			//會員用
			$data['login'] = $this->url->link('account/login');
			$data['edit'] = $this->url->link('account/edit');
			$data['login_confirm'] = $this->url->link('framework/menu/login');
			$data['menu'] = $this->url->link('framework/menu');
			$data['register'] = $this->url->link('account/register');
			$data['forgotten'] = $this->url->link('account/forgotten');
			$data['logout'] = $this->url->link('account/logout');
			$data['login_session'] = false;

			if ($this->customer->isLogged()) {
				$data['login_session'] = true;
				$data['username'] = $this->customer->getName();
			}

			//購物用
			
			$data['checkout_cart'] = $this->url->link('checkout/cart');
			$data['checkout_checkout'] = $this->url->link('checkout/checkout');

			$data['minicart'] = $this->load->controller('widget/cart');

		return $this->load->view($this->controller, $data);
	}
	public function login()
	{
		$json = array();
		$this->load->model('customer/customer');

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} else {
			$data['email'] = '';
		}

		if (isset($this->request->post['password'])) {
			$data['password'] = $this->request->post['password'];
		} else {
			$data['password'] = '';
		}
		
		$approval = $this->customer->approval($this->request->post['email'],$this->request->post['password']);

		$login = $this->customer->login($this->request->post['email'],$this->request->post['password']);


		if($approval){
			$json['unapproval'] = true;
		}
		if(!$login){
			$json['msg'] = false;
		}
		else{
			$json['msg'] = true;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
