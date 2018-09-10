<?php
class ControllerExtensionPaymentBankTransfer extends Controller {
	private $prefix = 'payment_bank_transfer_';
    private $model_name = 'payment_bank_transfer';
    private $model_path = 'extension/payment/bank_transfer';
	public function index($module_data) {
		$this->load->language('extension/payment/bank_transfer');
		$this->load->model('setting/module');
		$module = $this->model_setting_module->getModule($module_data[1]);
		$data['bank'] = nl2br($module['payment_bank_transfer_bank' . $this->config->get('config_language_id')]);
		return $this->load->view('extension/payment/bank_transfer/index', $data);
	}

	public function get_payment($recurring) {
		$this->load->language('extension/payment/bank_transfer');
		$this->load->model('extension/payment/bank_transfer');
		$method = $this->model_extension_payment_bank_transfer->getMethod();
		if ($method) {
			if ($recurring) {
				if (property_exists($this->model_extension_payment_bank_transfer, 'recurringPayments') && $this->model_extension_payment_bank_transfer->recurringPayments()) {
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
				$quotes[] = array(
					'method'    =>$modules['method'],
					'module_id' => $module['module_id'],
					'code' => $module['code'],
					'title' => $module['title'],
					'terms' => sprintf($this->language->get('text_free'), $payment_method['payment_bank_transfer_free']),
					'sort_order' => $module['sort_order']
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
			return $this->load->view('extension/payment/bank_transfer/get_payment', $data);
		}
	}

	public function confirm($module_data) {
		
		if ($module_data[0] == 'bank_transfer') {
			$this->load->language($this->model_path);
			$this->load->model('checkout/order');
			$this->load->model('setting/module');
			$module = $this->model_setting_module->getModule($module_data[1]);

			$comment  = $this->language->get('text_instruction') . "\n\n";
			$comment .= $module['payment_bank_transfer_bank' . $this->config->get('config_language_id')] . "\n\n";
			$comment .= $this->language->get('text_payment');

			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $module['payment_bank_transfer_order_status_id'], $comment, true);

			$data['message'] = $module['payment_bank_transfer_bank' . $this->config->get('config_language_id')];

			$data['success'] = $this->url->link('checkout/confirm/success');
		
			return $this->load->view('extension/payment/bank_transfer/confirm', $data);
		}
	}
}