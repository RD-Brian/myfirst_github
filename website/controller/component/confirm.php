<?php
class ControllerComponentConfirm extends Controller {

    public function index() {
        # code...
    }

    /**
     * 輸出購物車內容與樣板
     */
    public function cart($order_id) {

		$this->load->language('component/confirm');
		$this->load->model('checkout/cart');
        $this->load->model('checkout/order');
        $this->load->model('tool/image');

        // Validate minimum quantity requirements.
        
        $products = $this->model_checkout_order->getOrderProducts($order_id);

		foreach ($products as $key => $value) {

            $product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $value['product_id']) {
					$product_total += $product_2['quantity'];
				}
            }
            
            if(!isset($value['minimum'])) {
                $value['minimum'] = 0;
            }

			if ($value['minimum'] > $product_total) {
				$this->response->redirect($this->url->link('checkout/cart'));
            } else {
                $products_cart = $this->model_checkout_cart->getProduct($value['product_id']);
				
				if (is_file(DIR_UPLOADS . $products_cart['path'] . $products_cart['image'])) {
					$image = $this->model_tool_image->resize($products_cart['path'].$products_cart['image'], 227, 227);
				} else {
					$image = $this->model_tool_image->resize($products_cart['path'].$products_cart['image'], 227, 227);
				}
				$count = $value['quantity'] * $value['price'];

				$data['cart'][] = array(
					'product_id'			=> $value['product_id'],
					'quantity'				=> $value['quantity'],
					'href'						=> $this->url->link('product/product','&product_id='.$value['product_id']),
					'price'						=> $this->currency->format($value['price'], $this->config->get('config_currency')),
					'name'						=> $products_cart['name'],
					'image'						=> $image,
					'path'						=> $products_cart['path'],
					'ID'							=> sprintf('%05d',$value['product_id']),
					'model'						=> $products_cart['model'],
					'count'						=> $this->currency->format($count, $this->config->get('config_currency')),
				);
            }	 
		}
        
        return $this->load->view('component/confirm/cart',$data);
    }

    /**
     * Cart 的 Total
     */
    public function total($order_id) {

        $this->load->language('component/cart');
		$this->load->model('checkout/order');
		
		$totals =$this->model_checkout_order->getOrderTotals($order_id);

		$data['totals'] = array();

		foreach ($totals as $total) {
			$data['totals'][] = array(
				'title' => $total['title'],
				'text'  => $this->currency->format($total['value'], $this->session->data['currency'])
			);
		}

        return $this->load->view('component/confirm/total',$data);
    }

	public function add()
	{
		$json = array();

		$this->load->model('checkout/cart');
		// $this->load->library('cart/cart');
		if (isset($this->request->post['product_id'])) {
			$product_id = (int)$this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		$product_info = $this->model_checkout_cart->getProduct($product_id);

		if($product_info)
		{
			if (isset($this->request->post['quantity'])) {
				$quantity = (int)$this->request->post['quantity'];
			} else {
				$quantity = 1;
			}

		}
		if(!$json){
			$this->model_checkout_cart->add($this->request->post['product_id'],$quantity,$product_info['price']);
			$json['success'] = $product_info['name'] . "已經成功加入購物車囉";

			$product_total = $this->model_checkout_cart->countProducts();
			$total = $this->model_checkout_cart->getTotal();

			$json['total'] = $product_total;

		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function remove()
	{
		$json = array();
		$this->load->model('checkout/cart');
		
		$this->model_checkout_cart->deleteItems($this->request->post['cart_id']);
		$json['remove'] = "已經成功移除該商品";
		$total = $this->model_checkout_cart->getTotal();
		$product_total = $this->model_checkout_cart->countProducts();
		$json['total'] = $product_total;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function update()
	{
		$json = array();
		$this->load->model('checkout/cart');
		$updatePrice = $this->model_checkout_cart->UpdateItemsPrice($this->request->post['product_id'],$this->request->post['quantity']);
		$json['price'] = $this->request->post['quantity'];
		$product_total = $this->model_checkout_cart->countProducts();
		$json['total'] = $product_total;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
