<?php
class ControllerCheckoutCart extends Controller {
	public function index() {
		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));

		//login
		$data['login_session'] = false;
		if ($this->customer->isLogged()) {
			$data['login_session'] = true;
		}
		// URL
		$data['checkout'] = $this->url->link('checkout/checkout');
		$data['product_category'] = $this->url->link('product/category');

		$data['payment_method'] = $this->_payment();
		$data['shopping_cart'] = $this->_cart();
		$data['shopping_total'] = $this->_total();
        
		$this->_output('checkout/cart/index', $data);
    }

    public function get_total() {
    	if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

    		if($this->request->post['payment_method']) {
    			$this->session->data['payment_method']['code'] = $this->request->post['payment_method'];
    		}

    		$data['totals'] = $this->_total();
    		$this->_disableFrame();
    		$this->_output('checkout/cart/get_total',$data);
    	}
    }

    /**
     * 輸出付款方式
     * @return [type] [description]
     */
    private function _payment() {

		$this->load->language('checkout/cart');
		$this->load->model('setting/extension');

		$data['renew_total'] = $this->url->link('checkout/cart/get_total');

		// Payment Methods
		$method_data = array();
		$results = $this->model_setting_extension->getExtensions('payment');
		$recurring = $this->cart->hasRecurringProducts();
		foreach ($results as $result) {
			$quotes[$result['code']] = $this->load->controller('extension/payment/' . $result['code'] .'/get_payment',$recurring);
		}

		// print_r($quotes);
		// 排序晚點在處理
		// $sort_order = array();

		// foreach ($results as $key => $value) {
		// 	$sort_order[$key] = $value['sort_order'];
		// }

		// array_multisort($sort_order, SORT_ASC, $quotes);

		if (!empty($quotes)) {
			$data['payment_methods'] = $quotes;
		} else {
			$data['payment_methods'] = array();
		}
		
		return $this->load->view('checkout/cart/payment', $data);
	}

    /**
     * 輸出購物車內容與樣板
     */
    private function _cart() {
    	$this->load->language('component/cart');
    	$this->load->model('product/product');
		$this->load->model('tool/image');

		$products = $this->cart->getProducts();

		

		if(!$products){
			exit('<script>alert("購物稱內無商品!請先回商城逛逛"); location.href="' . $this->url->link('product/category') . '";</script>');
		}

		$data['product_category'] = $this->url->link('product/category');
		//圖片
		$this->load->model('setting/setting');
		$setting = $this->model_setting_setting->getSetting('image');

		foreach ($products as $key => $value) {
				$products_cart = $this->model_product_product->getProduct($value['product_id']);
				if (is_file(DIR_UPLOADS . $products_cart['path'] . $products_cart['image'])) {
					$image = $this->model_tool_image->resize($products_cart['path'].$products_cart['image'], 277, 277 );//$setting['image_product_cart_width'], $setting['image_product_cart_height']
				} else {
					$image = $this->model_tool_image->resize($products_cart['path'].$products_cart['image'], 277, 277);
				}
				$count = $value['quantity'] * $value['price'];

				$data['cart'][] = array(
					'cart_id' 				=> $value['cart_id'],
					'product_id'			=> $value['product_id'],
					'quantity'				=> $value['quantity'],
					'options'				=> $value['option'],
					'additional_options'	=> $value['additional_option'],
					'href'						=> $this->url->link('product/product','&product_id='.$value['product_id']),
					'price'						=> $this->currency->format($value['price'], $this->config->get('config_currency')),
					'name'						=> $value['name'],
					'image'						=> $image,
					'path'						=> $products_cart['path'],
					'ID'							=> sprintf('%05d',$value['product_id']),
					'model'						=> $products_cart['model'],
					'count'						=> $this->currency->format($count, $this->config->get('config_currency')),
				);
		}
        return $this->load->view('checkout/cart/cart',$data);
    }

	private function _total() {

        $this->load->language('checkout/cart');
        $this->load->model('checkout/cart');

        // Totals
		$this->load->model('setting/extension');

		$totals = array();
		$taxes = $this->cart->getTaxes();
		$total = 0;
		// Because __call can not keep var references so we put them into an array.
		$total_data = array(
			'totals' => &$totals,
			'taxes'  => &$taxes,
			'total'  => &$total
			);

		// Display prices
		if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
			$sort_order = array();
			$results = $this->model_setting_extension->getExtensions('total');
			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
			}
			array_multisort($sort_order, SORT_ASC, $results);

			foreach ($results as $result) {
				if ($this->config->get('total_' . $result['code'] . '_status')) {
					$this->load->model('extension/total/' . $result['code']);
					// We have to put the totals in an array so that they pass by reference.
					$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
				}
			}

			$sort_order = array();

			foreach ($totals['total'] as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $totals['total']);
		}

		$data['totals'] = array();

		foreach ($totals['total'] as $total) {
			if(is_numeric($total['value'])) {
				$text = $this->currency->format($total['value'], $this->session->data['currency']);
			} else {
				$text = $total['value'];
			}

			$data['totals'][] = array(
				'title' => $total['title'],
				'text'  => $text
				);
		}

		$data['free'] = '';
		$data['difference'] = '';

		if(!empty($totals['difference'])) {
			$data['free'] = sprintf($this->language->get('text_free'), $totals['free']);
			$data['difference'] = sprintf($this->language->get('text_difference'), $totals['difference']);
		}

        return $this->load->view('checkout/cart/total',$data);
    }
}
