<?php
class ControllerCommonCart extends Controller {
	public function index() {
		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));

		$this->document->addStyle('view/javascript/css/mightyslider.css');
		$this->document->addStyle('view/javascript/css/mightyslider.animate.css');
		$this->document->addScript('view/javascript/mightyslider/tweenlite.js');
		$this->document->addScript('view/javascript/mightyslider/mightyslider.js');
		$this->document->addScript('view/javascript/mightyslider/mightyslider.animate.plugin.min.js');

		$this->load->model('product/cart');
		$this->load->model('tool/image');
		// $this->load->library('cart/cart');

			
			$product_total = $this->model_product_cart->countProducts();
			$total = $this->model_product_cart->getTotal();
			$data['total'] = $this->currency->format($total,$this->config->get('config_currency'));
			$data['text_items'] = "共" . $product_total . "件商品-總共".$this->currency->format($total,$this->config->get('config_currency'));


			foreach ($this->model_product_cart->getProducts() as $key => $value) {
				$products_cart = $this->model_product_cart->getProduct($value['product_id']);
				
				if (is_file(DIR_IMAGE . $products_cart['path'] . $products_cart['image'])) {
					$image = $this->model_tool_image->resize($products_cart['path'].$products_cart['image'], 60, 60);
				} else {
					$image = $this->model_tool_image->resize($products_cart['path'].$products_cart['image'], 60, 60);
				}

				$data['cart'][] = array(
					'cart_id' 				=> $value['cart_id'],
					'product_id'			=> $value['product_id'],
					'quantity'				=> $value['quantity'],
					'price'						=> $this->currency->format($value['price'], $this->config->get('config_currency')),
					'name'						=> $products_cart['name'],
					'image'						=> $image,
					'path'						=> $products_cart['path'],
				); 
			 // print_r($data['cart']);
			}
		





		// $this->_output('common/cart', $data);
		return $this->load->view('common/cart', $data);
	}

	public function info() {
		$this->response->setOutput($this->index());
	}
}
