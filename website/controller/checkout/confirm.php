<?php
class ControllerCheckoutConfirm extends Controller {

	private $error = array();

	public function index() {

		if(!isset($this->session->data['order_id']) || empty($this->session->data['order_id'])) {
			$this->response->redirect($this->url->link('checkout/cart'));
		} else {
			$order_id = $this->session->data['order_id'];
		}

		$this->load->model('checkout/order');

		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));

		$this->load->language('checkout/confirm');

		$order = $this->model_checkout_order->getOrder($order_id);
		// 輸出訂單資訊
		$data['date_added'] = $order['date_added'];
		$data['order_number'] = $order['order_number'];
		$data['order_status'] = $order['order_status'];
		$data['order_payment_method'] = $order['payment_method'];

		// payment info
		$data['payment_name'] = $order['payment_name'];
		$data['payment_email'] = $order['email'];
		$data['payment_mobile'] = $order['mobile'];
		$data['payment_country'] = $order['payment_country'];
		$data['payment_postcode'] = $order['payment_postcode'];
		$data['payment_city'] = $order['payment_city'];
		$data['payment_zone'] = $order['payment_zone'];
		$data['payment_address'] = $order['payment_address'];

		//shipping info
		$data['shipping_name'] = $order['shipping_name'];
		$data['shipping_email'] = $order['shipping_email'];
		$data['shipping_mobile'] = $order['shipping_mobile'];
		$data['shipping_country'] = $order['shipping_country'];
		$data['shipping_postcode'] = $order['shipping_postcode'];
		$data['shipping_city'] = $order['shipping_city'];
		$data['shipping_zone'] = $order['shipping_zone'];
		$data['shipping_address'] = $order['shipping_address'];

		$data['home'] = $this->url->link('common/home');

        // 載入樣板
		$data['confirm_cart'] = $this->cart($order_id);
		$data['confirm_total'] = $this->total($order_id);
		$data['hide_order'] = false;
		if(isset($order['payment_code']) && !empty($order['payment_code'])) {
			$payment_code = explode('.',$order['payment_code']);
			$data['payment_method'] = $this->load->controller('extension/payment/' . $payment_code[0] . '/confirm',$payment_code);
		} else {
			$data['payment_method'] = '';
		}

		$this->_output('checkout/confirm/index', $data);
	}

	public function success() {
		$this->cart->clear();

		unset($this->session->data['shipping_method']);
		unset($this->session->data['shipping_methods']);
		unset($this->session->data['payment_method']);
		unset($this->session->data['payment_methods']);
		unset($this->session->data['guest']);
		unset($this->session->data['comment']);
		unset($this->session->data['order_id']);
		unset($this->session->data['coupon']);
		unset($this->session->data['reward']);
		unset($this->session->data['voucher']);
		unset($this->session->data['vouchers']);
		unset($this->session->data['totals']);
	}

	public function cart($order_id) {

		$this->load->language('checkout/confirm');
		$this->load->model('product/product');
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
				
				if (is_file(DIR_UPLOADS . $value['path'] . $value['image'])) {
					$image = $this->model_tool_image->resize($value['path'].$value['image'], 227, 227);
				} else {
					$image = $this->model_tool_image->resize($value['path'].$value['image'], 227, 227);
				}

				$data['cart'][] = array(
					'ID'				 => sprintf('%05d',$value['product_id']),
					'product_id'		 => $value['product_id'],
					'name'				 => $value['name'],
					'quantity'			 => $value['quantity'],
					'model'				 => $value['model'],
					'tax'				 => $value['tax'],
					'reward'			 => $value['reward'],
					'options'			 => $this->model_checkout_order->getOrderProductOptions($value['order_id'],$value['order_product_id']),
					'additional_options' => $this->model_checkout_order->getOrderProductAdditionalOptions($value['order_id'],$value['order_product_id']),
					'image'				 => $image,
					'path'				 => $value['path'],
					'price'				 => $this->currency->format($value['price'], $this->config->get('config_currency')),
					'total'				 => $this->currency->format($value['total'], $this->config->get('config_currency')),
					'href'				 => $this->url->link('product/product','&product_id='.$value['product_id'])
				);
            }
		}
        
        return $this->load->view('checkout/confirm/cart',$data);
    }

    /**
     * Cart 的 Total
     */
    public function total($order_id) {

    	$this->load->language('checkout/cart');
		$this->load->model('checkout/order');

		$totals = $this->model_checkout_order->getOrderTotals($order_id);

		$data['totals'] = array();
		foreach ($totals as $total) {
			$data['totals'][] = array(
				'title' => $total['title'],
				'text'  => $this->currency->format($total['value'], $this->session->data['currency'])
			);
		}

		return $this->load->view('checkout/confirm/total',$data);
    }
}