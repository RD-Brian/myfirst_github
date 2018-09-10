<?php
class ControllerFrameworkAccountMenu extends Controller {

	private $controller = 'framework/account/menu';
	
	public function index() {
		$this->load->language($this->controller);

		$data['edit'] = $this->url->link('account/edit');
		$data['password'] = $this->url->link('account/password');
		$data['logout'] = $this->url->link('account/logout');
		$data['order'] = $this->url->link('account/order');
		$data['rooms'] = $this->url->link('account/category');
		$data['location'] = $this->url->link('account/location');
		$data['contact'] = $this->url->link('account/contact');
		$data['attractions'] = $this->url->link('account/list');
		$data['shop'] = $this->url->link('account/category');
		$data['login'] = $this->url->link('account/login');
		$data['cart'] = $this->load->controller('account/cart');

		return $this->load->view($this->controller, $data);
	}
}
