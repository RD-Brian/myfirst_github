<?php
class ControllerExtensionPaymentBankTransfer extends Controller {
	public function index($module_id) {
		$this->load->language('extension/payment/bank_transfer');
		$this->load->model('setting/module');
		$module = $this->model_setting_module->getModule($module_id);
		$data['bank'] = nl2br($module['payment_bank_transfer_bank' . $this->config->get('config_language_id')]);
		return $this->load->view('extension/payment/bank_transfer', $data);
	}

	public function confirm() {
		$json = array();
		
		if ($this->session->data['payment_method']['code'] == 'bank_transfer') {
			$this->load->language('extension/payment/bank_transfer');

			$this->load->model('checkout/order');

			$comment  = $this->language->get('text_instruction') . "\n\n";
			$comment .= $this->config->get('payment_bank_transfer_bank' . $this->config->get('config_language_id')) . "\n\n";
			$comment .= $this->language->get('text_payment');

			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_bank_transfer_order_status_id'), $comment, true);
		
			$json['redirect'] = $this->url->link('checkout/success');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));		
	}
}