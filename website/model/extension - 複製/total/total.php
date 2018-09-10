<?php
class ModelExtensionTotalTotal extends Model {
	public function getTotal($total) {
		$this->load->language('extension/total/total');

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

		if ($sub_total < $total_free) {
			$total['totals']['difference'] = $total_free - $sub_total;
		} else {
			$total['totals']['difference'] = 0;
		}

		$total['totals']['total']['total'] = array(
			'code'       => 'total',
			'title'      => $this->language->get('text_total'),
			'value'      => max(0, $total['total']),
			'sort_order' => $this->config->get('total_total_sort_order')
		);
	}
}