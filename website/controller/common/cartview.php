<?php
class ControllerCommonCartview extends Controller {
	public function index() {
		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));

		$this->document->addStyle('view/javascript/css/mightyslider.css');
		$this->document->addStyle('view/javascript/css/mightyslider.animate.css');
		$this->document->addScript('view/javascript/mightyslider/tweenlite.js');
		$this->document->addScript('view/javascript/mightyslider/mightyslider.js');
		$this->document->addScript('view/javascript/mightyslider/mightyslider.animate.plugin.min.js');

		$this->load->model('checkout/cart');
		$this->load->model('tool/image');
		// $this->load->library('cart/cart');

			$data['view_cart'] = $this->url->link('checkout/cart');
			$data['product_category'] = $this->url->link('product/category');
			$data['checkcart'] = $this->url->link('checkout/cart');
			//ajax url
			$data['check_cart'] = $this->url->link('ajax/cart/add');
			$data['cart_info'] = $this->url->link('common/cartview/info');
			$data['checkout_remove'] = $this->url->link('ajax/cart/remove');
			$data['checkout_update'] = $this->url->link('ajax/cart/update');
			$data['checkout'] = $this->url->link('checkout/checkout');
			$data['component_checkout_total'] = $this->url->link('component/checkout/total');

			$product_total = $this->model_checkout_cart->countProducts();
			$total = $this->model_checkout_cart->getTotal();
			$data['total'] = $this->currency->format($total,$this->config->get('config_currency'));
			$data['text_items'] = $product_total;


			foreach ($this->model_checkout_cart->getProducts() as $key => $value) {
				$products_cart = $this->model_checkout_cart->getProduct($value['product_id']);
				
				if (is_file(DIR_UPLOADS . $products_cart['path'] . $products_cart['image'])) {
					$image = $this->model_tool_image->resize($products_cart['path'].$products_cart['image'], 60, 60);
				} else {
					$image = $this->model_tool_image->resize($products_cart['path'].$products_cart['image'], 60, 60);
				}

				$data['cart'][] = array(
					'cart_id' 				=> $value['cart_id'],
					'product_id'			=> $value['product_id'],
					'quantity'				=> $value['quantity'],
					'href'						=> $this->url->link('product/product','&product_id='.$value['product_id']),
					'price'						=> $this->currency->format($value['price'], $this->config->get('config_currency')),
					'name'						=> $products_cart['name'],
					'image'						=> $image,
					'path'						=> $products_cart['path'],
				); 
			 // print_r($data['cart']);
			}
		





		// $this->_output('common/cart', $data);
		return $this->load->view('common/cartview', $data);
	}

	public function info() {
		$this->response->setOutput($this->index());
	}
}
