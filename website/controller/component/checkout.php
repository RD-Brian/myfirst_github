<?php
class ControllerComponentCheckout extends Controller {

    public function index() {
        # code...
    }

    /**
     * 輸出購物車內容與樣板
     */
 //    public function cart() {

 //        $this->load->language('component/cart');

 //        $this->load->model('checkout/cart');
 //        $this->load->model('tool/image');

 //        // Validate minimum quantity requirements.
        
 //        $products = $this->model_checkout_cart->getProducts();

	// 	foreach ($products as $key => $value) {

 //            $product_total = 0;

	// 		foreach ($products as $product_2) {
	// 			if ($product_2['product_id'] == $value['product_id']) {
	// 				$product_total += $product_2['quantity'];
	// 			}
 //            }
            
 //            if(!isset($value['minimum'])) {
 //                $value['minimum'] = 0;
 //            }

	// 		if ($value['minimum'] > $product_total) {
	// 			$this->response->redirect($this->url->link('checkout/cart'));
 //            } else {
 //                $products_cart = $this->model_checkout_cart->getProduct($value['product_id']);
				
	// 			if (is_file(DIR_IMAGE . $products_cart['path'] . $products_cart['image'])) {
	// 				$image = $this->model_tool_image->resize($products_cart['path'].$products_cart['image'], 227, 227);
	// 			} else {
	// 				$image = $this->model_tool_image->resize($products_cart['path'].$products_cart['image'], 227, 227);
	// 			}
	// 			$count = $value['quantity'] * $value['price'];

	// 			$data['cart'][] = array(
	// 				'cart_id' 				=> $value['cart_id'],
	// 				'product_id'			=> $value['product_id'],
	// 				'quantity'				=> $value['quantity'],
	// 				'href'						=> $this->url->link('product/product','&product_id='.$value['product_id']),
	// 				'price'						=> $this->currency->format($value['price'], $this->config->get('config_currency')),
	// 				'name'						=> $products_cart['name'],
	// 				'image'						=> $image,
	// 				'path'						=> $products_cart['path'],
	// 				'ID'							=> sprintf('%05d',$value['product_id']),
	// 				'model'						=> $products_cart['model'],
	// 				'count'						=> $this->currency->format($count, $this->config->get('config_currency')),
	// 			);
 //            }	 
	// 	}
        
 //        return $this->load->view('component/checkout/cart',$data);
 //    }

 //    /**
 //     * Cart 的 Total
 //     */
 //    public function total() {

 //        $this->load->language('component/cart');
 //        $this->load->model('checkout/cart');

 //        // Totals
	// 		$this->load->model('setting/extension');

	// 		$totals = array();
	// 		$taxes = $this->cart->getTaxes();
	// 		$total = 0;
			
	// 		// Because __call can not keep var references so we put them into an array. 			
	// 		$total_data = array(
	// 			'totals' => &$totals,
	// 			'taxes'  => &$taxes,
	// 			'total'  => &$total
	// 		);
			
	// 		// Display prices
	// 		if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
	// 			$sort_order = array();

	// 			$results = $this->model_setting_extension->getExtensions('total');

	// 			foreach ($results as $key => $value) {
	// 				$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
	// 			}

	// 			array_multisort($sort_order, SORT_ASC, $results);

	// 			foreach ($results as $result) {
	// 				if ($this->config->get('total_' . $result['code'] . '_status')) {
	// 					$this->load->model('extension/total/' . $result['code']);
						
	// 					// We have to put the totals in an array so that they pass by reference.
	// 					$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
	// 				}
	// 			}

	// 			$sort_order = array();

	// 			foreach ($totals as $key => $value) {
	// 				$sort_order[$key] = $value['sort_order'];
	// 			}

	// 			array_multisort($sort_order, SORT_ASC, $totals);
	// 		}

	// 		$data['totals'] = array();

	// 		foreach ($totals as $total) {
	// 			$data['totals'][] = array(
	// 				'title' => $total['title'],
	// 				'text'  => $this->currency->format($total['value'], $this->session->data['currency'])
	// 			);
	// 		}

 //        return $this->load->view('component/cart/total',$data);
	// }
	
	// public function get_total() {
	// 	$data['totals'] = $this->total();
	// 	$this->_disableFrame();
	// 	$this->_output('component/checkout/get_total',$data);
	// }
}
