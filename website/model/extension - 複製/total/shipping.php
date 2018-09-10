<?php
class ModelExtensionTotalShipping extends Model {
	public function getTotal($total) {

		$this->load->language('extension/total/shipping');

		// 先判訂購物車中商品是需要配送的
		if ($this->cart->hasShipping()) {

			if(isset($total['totals']['sub_total']) && !empty($total['totals']['sub_total'])) {
				$sub_total = $total['totals']['sub_total'];
			} else {
				$sub_total = $this->cart->getSubTotal();
			}

			// 紀錄免運門檻
			if(isset($total['totals']['free']) && !empty($total['totals']['free'])) {
				// 如果有設定則進入比較
				if($total['totals']['free'] > $this->config->get('total_shipping_free')) {
					// 如果原本的免運大於付款方式的免運則替代掉
					$total['totals']['free'] = $this->config->get('total_shipping_free');
				}
			} else {
				$total['totals']['free'] = $this->config->get('total_shipping_free');
			}

			$total_free = $total['totals']['free'];

			// 訂購金額 < 免運金額 ->要計算運費
			if ($sub_total < $total_free) {

				$total['totals']['total']['shipping'] = array(
					'code'       => 'shipping',
					'title'      => $this->language->get('text_total_shipping'),
					'value'      => $this->config->get('total_shipping_cost'),
					'sort_order' => $this->config->get('total_shipping_sort_order')
					);

				$total['total'] += $this->config->get('total_shipping_cost');

				$total['totals']['is_shipping'] = 1;

			} else {
				$total['totals']['total']['shipping'] = array(
					'code'       => 'shipping',
					'title'      => $this->language->get('text_total_shipping'),
					'value'      => $this->language->get('text_total_shipping_free'),
					'sort_order' => $this->config->get('total_shipping_sort_order')
					);

				$total['totals']['is_shipping'] = 0;
			}
		}
	}
}