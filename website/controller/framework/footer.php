<?php
class ControllerFrameworkFooter extends Controller {
	public function index() {
		$this->load->language('framework/footer');

		$data['text_footer'] = $this->language->get('text_footer');

		//for cart
		$data['view_cart'] = $this->url->link('checkout/cart');
		$data['product_category'] = $this->url->link('product/category');
		//ajax url
		$data['check_cart'] = $this->url->link('ajax/cart/add');
		$data['checkout_remove'] = $this->url->link('ajax/cart/remove');
		$data['checkout_update'] = $this->url->link('ajax/cart/update');
		$data['_checkout'] = $this->url->link('checkout/checkout');
		$data['component_checkout_total'] = $this->url->link('component/checkout/total');

		$data['about'] = $this->url->link('information/about');
		$data['location'] = $this->url->link('information/about#contact');
		$data['rooms'] = $this->url->link('room/category');
		$data['contact'] = $this->url->link('support/contact');
		$data['shop'] = $this->url->link('common/home');
		$data['news'] = $this->url->link('news/category');
		$data['article'] = $this->url->link('article/category');
		$data['problem'] = $this->url->link('problem/problem');

		$data['doc_footer'] = $this->load->controller('framework/doc_footer');
		
		return $this->load->view('framework/footer', $data);
	}
}
