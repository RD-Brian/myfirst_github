<?php
class ControllerComponentCart extends Controller {

    

    /**
     * Cart 的 Total
     */
    

	public function cart()
	{
		$json = array();
		$this->load->model('checkout/cart');
		$this->load->model('tool/image');

		foreach ($this->model_checkout_cart->getProducts() as $key => $value) {
				$products_cart = $this->model_checkout_cart->getProduct($value['product_id']);
				
				if (is_file(DIR_IMAGE . $products_cart['path'] . $products_cart['image'])) {
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
		}


			// $this->_output('product/cart', $data);
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
