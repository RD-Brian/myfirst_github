<?php
class ModelExtensionTotalPayment extends Model {
	public function getTotal($total) {

		$this->load->language('extension/total/payment');

		$payment_method = array();

		// 如果已經有選擇付款方法才進入處理免運問題
		if(isset($this->session->data['payment_method']['code']) && !empty($this->session->data['payment_method']['code'])) {

			$method = explode('.',$this->session->data['payment_method']['code']);
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "module WHERE module_id = '" . (int)$method[1] . "'");

			if ($query->row) {
				$payment_method = json_decode($query->row['setting'], true);

				if(isset($total['totals']['free']) && !empty($total['totals']['free'])) {
					// 如果有設定則進入比較
					if($total['totals']['free'] > $payment_method['payment_'.$method[0].'_free']) {
						// 如果原本的免運大於付款方式的免運則替代掉
						$total['totals']['free'] = $payment_method['payment_'.$method[0].'_free'];
					}

				} else {
					$total['totals']['free'] = $payment_method['payment_'.$method[0].'_free'];
				}


				if(isset($total['totals']['sub_total']) && !empty($total['totals']['sub_total'])) {
					$sub_total = $total['totals']['sub_total'];
				} else {
					$sub_total = $this->cart->getSubTotal();
				}

				$total_free = $total['totals']['free'];

				// 訂購金額 < 免運金額 ->要計算運費
				if ($sub_total > $total_free) {
					$total['totals']['total']['shipping'] = array(
						'code'       => 'shipping',
						'title'      => $this->language->get('text_total_shipping'),
						'value'      => $this->language->get('text_total_shipping_free'),
						'sort_order' => $this->config->get('total_shipping_sort_order')
						);

					// 先判定是否有添加運費
					if(isset($total['totals']['is_shipping']) && !empty($total['totals']['is_shipping'])) {
						$total['total'] = $total['total'] - $this->config->get('total_shipping_cost');
					}
				}
			}
		}
	}
}