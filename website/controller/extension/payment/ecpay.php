<?php
/**
 * 訂單狀態有問題在這支
 */
class ControllerExtensionPaymentEcpay extends Controller {
    private $prefix = 'payment_ecpay_';
    private $model_name = 'payment_ecpay';
    private $model_path = 'extension/payment/ecpay';
    public function index($module_data) {
        // Get the translations
        $this->load->language($this->model_path);
        $data['text_title'] = $this->language->get('text_title');
        if(isset($module_data[2])) {
            $data['text_description'] = $this->language->get('text_'.strtolower($module_data[2]));
        } else {
            $data['text_description'] = '';
        }
        // ecpay_choose_payment
        return $this->load->view('extension/payment/ecpay/index', $data);
    }

    public function confirm($module_data) {

        $this->load->language($this->model_path);

        $data['action'] = $this->url->link('extension/payment/ecpay/redirect', '', 'SSL');
        $data['success'] = $this->url->link('checkout/confirm/success');

        $data['order_id'] = $this->session->data['order_id'];

        if(isset($module_data[1])) {
            $data['module_id'] = $module_data[1];
        } else {
            $data['module_id'] = '';
        }

        if(isset($module_data[2])) {
            $data['payment_ecpay_choose_payment'] = $module_data[2];
        } else {
            $data['payment_ecpay_choose_payment'] = '';
        }

        return $this->load->view('extension/payment/ecpay/confirm', $data);
    }

    public function get_payment($recurring) {
        $this->load->language('extension/payment/ecpay');
        $this->load->model('extension/payment/ecpay');
        $method = $this->model_extension_payment_ecpay->getMethod();
        if ($method) {
            if ($recurring) {
                if (property_exists($this->model_extension_payment_ecpay, 'recurringPayments') && $this->model_extension_payment_ecpay->recurringPayments()) {
                    $modules['payment'] = $method;
                    $modules['method'] = $this->language->get('payment')->get('text_title');
                }
            } else {
                $modules['payment'] = $method;
                $modules['method'] = $this->language->get('text_title');
            }

            foreach ($modules['payment'] as $k => $module) {
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "module WHERE module_id = '" . (int)$module['module_id'] . "'");
                $payment_method = json_decode($query->row['setting'], true);
                $sub_methods = array();
                if(isset($payment_method['payment_ecpay_payment_methods']) && !empty($payment_method['payment_ecpay_payment_methods'])) {
                    foreach($payment_method['payment_ecpay_payment_methods'] as $k=>$v ) {
                        $sub_methods[$module['code'].'.'.$k] = $this->language->get('text_'.strtolower($v));
                    }
                }
                // print_r($payment_method);
                $quotes = array(
                    'method'    =>$modules['method'],
                    'module_id' => $module['module_id'],
                    'code' => $module['code'],
                    'title' => $module['title'],
                    'terms' => sprintf($this->language->get('text_free'), $payment_method['payment_ecpay_free']),
                    'sort_order' => $module['sort_order'],
                    'paymenys' =>$payment_method,
                    'sub_methods'  => $sub_methods
                    );
            }

            if (!empty($quotes)) {
                $data['payment_method'] = $quotes;
            } else {
                $data['payment_method'] = array();
            }

            if(isset($this->session->data['payment_method']['code']) || !empty($this->session->data['payment_method']['code'])) {
                $data['code'] = $this->session->data['payment_method']['code'];
            } else {
                $data['code'] = '';
            }

            return $this->load->view('extension/payment/ecpay/get_payment', $data);
        }
    }

	public function redirect() {
        try {
            // Load translation
            $this->load->language($this->model_path);

            // Load model
            $this->load->model($this->model_path);
            $this->model_extension_payment_ecpay->loadLibrary();
            
            // Get the order info
            $order_id = $this->request->post['order_id'];
            $this->load->model('checkout/order');
            $order = $this->model_checkout_order->getOrder($order_id);
            $order_total = $order['total'];

            // 開始處理payment module
            $payment_array = explode('.', $order['payment_code']);
            $module_id = $payment_array[1];
            if($module_id) {
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "module WHERE module_id = '" . (int)$module_id . "'");
                $payment_method = json_decode($query->row['setting'], true);
            } else {
                $payment_method = array();
            }

            // $payment_methods = $this->config->get($this->prefix . 'payment_methods');
            $payment_type = $this->request->post[$this->prefix . 'choose_payment'];
            $helper = $this->model_extension_payment_ecpay->getHelper($payment_method['payment_ecpay_merchant_id']);

	        // Validate choose payment
	        if (!isset($payment_method['payment_ecpay_payment_methods'][$payment_type])) {
	            throw new Exception($this->language->get('error_invalid_payment'));
	        }

            // Validate the order id
            if (isset($this->request->post['order_id']) === false) {
                throw new Exception($this->language->get('error_order_id_miss'));
            }

            // Update order status and comments
            $comment = $this->language->get('text_' . $payment_method['payment_ecpay_payment_methods'][$payment_type]);
		    $status_id = $payment_method[$this->prefix . 'create_status'];
		    $this->model_checkout_order->addOrderHistory($order_id, $status_id, $comment, false, false);

            // Add to activity log
            $this->load->model('customer/activity');
            if ($this->customer->isLogged()) {
                $activity_data = array(
                    'customer_id' => $this->customer->getId(),
                    'name'        => $this->customer->getName(),
                    'order_id'    => $order_id
                );
                $this->model_customer_activity->addActivity('order_account', $activity_data);
            } else {
                $guest = $this->session->data['guest'];
                $activity_data = array(
                    'name'     => $guest['firstname'] . ' ' . $guest['lastname'],
                    'order_id' => $order_id
                );
                $this->model_customer_activity->addActivity('order_guest', $activity_data);
            }

            // 獲取訂單商品並組成顯示字串
            $products = $this->model_checkout_order->getOrderProducts($order_id);
            if($products) {
                $items = array();
                foreach ($products as $key => $product) {
                    $items[] = sprintf($this->language->get('text_item_name'), $product['name'],$this->currency->format($product['price'], $this->config->get('config_currency'),'',false),$product['quantity']);
                }

                // 處理運費顯示
                $total = $this->model_checkout_order->getOrderTotalByCode($order_id,'shipping');
                $shipping = $this->currency->format($total['value'], $this->config->get('config_currency'),'',false);
                if($shipping) {
                    $items[] = sprintf($this->language->get('text_shipping_amount'),$this->currency->format($shipping, $this->config->get('config_currency'),'',false));
                }
                $item_string = implode('#',$items);
            } else {
                $item_string = $this->language->get('text_items');
            }

            // Checkout
            $helper_data = array(
            	'choosePayment' => $payment_type,
            	'hashKey' => $payment_method[$this->prefix . 'hash_key'],
            	'hashIv' => $payment_method[$this->prefix . 'hash_iv'],
            	'returnUrl' => $this->url->link($this->model_path . '/response', '', true),
            	'clientBackUrl' =>$this->url->link('account/order/info', 'order_id=' . $order_id, true),
            	'orderId' => $order_id,
            	'total' => $order_total,
            	'itemName' => $item_string,
            	'version' => $this->prefix . 'module_opencart_1.0.0710',
                'currency' => $this->config->get('config_currency'),
        	);

            $helper->checkout($helper_data);
        } catch (Exception $e) {
            // Process the exception
            $this->session->data['error'] = $e->getMessage();
            print_r($this->session->data['error']);
            // $this->response->redirect($this->url->link('checkout/cart', '', true));
        }
    }

    public function response() {
        // Load the model and translation
        $this->load->language($this->model_path);
        $this->load->model($this->model_path);
        $this->load->model('checkout/order');
        $this->model_extension_payment_ecpay->loadLibrary();
        $helper = $this->model_extension_payment_ecpay->getHelper();

        // Set the default result message
        $result_message = '1|OK';
        $order_id = null;
        $order = null;
        try {
            // Get valid feedback
            $helper_data = array(
                'hashKey' => $payment_method[$this->prefix . 'hash_key'],
                'hashIv' => $payment_method[$this->prefix . 'hash_iv'],
            );
            $feedback = $helper->getValidFeedback($helper_data);
            unset($helper_data);

            $order_id = $helper->getOrderId($feedback['MerchantTradeNo']);

            // Get the cart order info
            $order = $this->model_checkout_order->getOrder($order_id);
            $order_status_id = $order['order_status_id'];

            // Get Module Setting
            $payment_array = explode('.', $order['payment_code']);
            $module_id = $payment_array[1];
            if($module_id) {
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "module WHERE module_id = '" . (int)$module_id . "'");
                $payment_method = json_decode($query->row['setting'], true);
            } else {
                $payment_method = array();
            }

            $create_status_id = $payment_method[$this->prefix . 'create_status'];
            $order_total = $order['total'];

            // Check the amounts
            if (!$helper->validAmount($feedback['TradeAmt'], $order_total)) {
                throw new Exception($helper->getAmountError($order_id));
            }

            // Get the response status
            $helper_data = array(
                'validStatus' => ($helper->toInt($order_status_id) === $helper->toInt($create_status_id)),
                'orderId' => $order_id,
            );
            $response_status = $helper->getResponseStatus($feedback, $helper_data);
            unset($helper_data);

            // Update the order status
            switch($response_status) {
                // Paid
                case 1:
                    $status_id = $payment_method[$this->prefix . 'success_status'];
                    $pattern = $this->language->get('text_payment_result_comment');
                    $comment = $helper->getPaymentSuccessComment($pattern, $feedback);
                    $this->model_checkout_order->addOrderHistory($order_id, $status_id, $comment, true);
                    unset($status_id, $pattern, $comment);

                    // Check E-Invoice model
                    $opay_invoice_status = $this->config->get('opayinvoice_status');
                    $ecpay_invoice_status = $this->config->get('ecpayinvoice_status');

                    // Get E-Invoice model name
                    $invoice_prefix = '';
                    if ($opay_invoice_status === '1' and is_null($ecpay_invoice_status) === true) {
                        $invoice_prefix = 'opay';
                    }
                    if ($ecpay_invoice_status === '1' and is_null($opay_invoice_status) === true) {
                        $invoice_prefix = 'ecpay';
                    }
                    
                    // E-Invoice auto issuel
                    if ($invoice_prefix !== '') {
                        $invoice_model_name = 'model_extension_payment_' . $invoice_prefix . 'invoice';
                        $this->load->model('extension/payment/' . $invoice_prefix . 'invoice');
                        $invoice_autoissue = $this->config->get($invoice_prefix . 'invoice_autoissue');
                        $valid_invoice_sdk = $this->$invoice_model_name->check_invoice_sdk();
                        if($invoice_autoissue === '1' and $valid_invoice_sdk != false) {    
                            $this->$invoice_model_name->createInvoiceNo($order_id, $valid_invoice_sdk);
                        }
                    }
                    break;
                // ATM get code
                case 2:
                    $status_id = $order_status_id;
                    $pattern = $this->language->get('text_atm_comment');
                    $comment = $helper->getObtainingCodeComment($pattern, $feedback);
                    $this->model_checkout_order->addOrderHistory($order_id, $status_id, $comment);
                    unset($status_id, $pattern, $comment);
                    break;
                // CVS get code
                case 3:
                    $status_id = $order_status_id;
                    $pattern = $this->language->get('text_cvs_comment');
                    $comment = $helper->getObtainingCodeComment($pattern, $feedback);
                    $this->model_checkout_order->addOrderHistory($order_id, $status_id, $comment);
                    unset($status_id, $pattern, $comment);
                    break;
                // Barcode get code
                case 4:
                    $status_id = $order_status_id;
                    $pattern = $this->language->get('text_barcode_comment');
                    $comment = $helper->getObtainingCodeComment($pattern, $feedback);
                    $this->model_checkout_order->addOrderHistory($order_id, $status_id, $comment);
                    unset($status_id, $pattern, $comment);
                    break;
                default:
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (!is_null($order_id)) {
                $status_id = $payment_method[$this->prefix . 'failed_status'];
                $pattern = $this->language->get('text_failure_comment');
                $comment = $helper->getFailedComment($pattern, $error);
                $this->model_checkout_order->addOrderHistory($order_id, $status_id, $comment);
                unset($status_id, $pattern, $comment);
            }
            
            // Set the failure result
            $result_message = '0|' . $error;
        }

        echo $result_message;
        exit;
    }
}
