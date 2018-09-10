<?php
namespace Booking;
class Booking {
	private $data = array();

	public function __construct($registry) {
		$this->config = $registry->get('config');
		$this->customer = $registry->get('customer');
		$this->session = $registry->get('session');
		$this->db = $registry->get('db');
		// $this->tax = $registry->get('tax');
		// $this->weight = $registry->get('weight');

		// Remove all the expired carts with no customer ID
		// $this->db->query("DELETE FROM " . DB_PREFIX . "cart WHERE (api_id > '0' OR customer_id = '0') AND date_added < DATE_SUB(NOW(), INTERVAL 1 HOUR)");

		// if ($this->customer->getId()) {
		// 	// We want to change the session ID on all the old items in the customers cart
		// 	$this->db->query("UPDATE " . DB_PREFIX . "cart SET session_id = '" . $this->db->escape($this->session->getId()) . "' WHERE api_id = '0' AND customer_id = '" . (int)$this->customer->getId() . "'");

		// 	// Once the customer is logged in we want to update the customers cart
		// 	$cart_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "cart WHERE api_id = '0' AND customer_id = '0' AND session_id = '" . $this->db->escape($this->session->getId()) . "'");

		// 	foreach ($cart_query->rows as $cart) {
		// 		$this->db->query("DELETE FROM " . DB_PREFIX . "cart WHERE cart_id = '" . (int)$cart['cart_id'] . "'");

		// 		// The advantage of using $this->add is that it will check if the products already exist and increaser the quantity if necessary.
		// 		$this->add($cart['product_id'], $cart['quantity'], json_decode($cart['option']), $cart['recurring_id']);
		// 	}
		// }
	}

	public function getCurrentPrice($price,$discounts) {
		$sort_data = array('once','range','cycle','weekday','holiday');
		$current_price = 0;
		if(!empty($discounts)) {
			foreach ($discounts as $key => $discount) {
				$is_discount = false;
				// 先判定今日是否有優惠價格
				switch ($discount['cycle_type']) {
					case 'once': // 指定日期
						if($discount['once_date'] == date('Y-m-d'))	{
							$is_discount = true;
						}
						break;
					case 'range': // 區間週期
						if(date('Y-m-d') >= $discount['date_start'] && date('Y-m-d') <= $discount['date_end']) {
							$is_discount = true;
						}
						break;
					case 'cycle': // 循環週期
						switch ($discount['cycle_time']) {
							case 'week':
								if(date('D') == $discount['week']) {
									$is_discount = true;
								}
								break;
							case 'month':
								if(date('d') == $discount['day']) {
									$is_discount = true;
								}
								break;

							case 'year':
								if(date('m') == $discount['month'] && date('d') == $discount['day']) {
									$is_discount = true;
								}
								break;
						}
						break;
					case 'weekday':
					    $weekday = array('Mon','Tue','Wed','Thu','Fri');
					    if(in_array(date('D'),$weekday))	{
							$is_discount = true;
						}
					    break;
					case 'holiday':
					    $holiday = array('Sat','Sun');
					    if(in_array(date('D'),$holiday))	{
							$is_discount = true;
						}
					    break;
				}

				if($is_discount) {
					switch ($discount['method']) {
						case 'percent':
						    if($discount['discount_type'] == '+') {
						    	$price_data[$discount['cycle_type']] = $price * (1+$discount['value']/100);
						    } else {
						    	$price_data[$discount['cycle_type']] = $price * (1-$discount['value']/100);
						    }
						    break;

						case 'amount':
						    if($discount['discount_type'] == '+') {
						    	$price_data[$discount['cycle_type']] = $price + $discount['value'];
						    } else {
						    	$price_data[$discount['cycle_type']] = $price - $discount['value'];
						    }
						    break;

						case 'pricing':
						    $price_data[$discount['cycle_type']] = $discount['value'];
						    break;
					}
				}
			}
			// 開始處理要顯示的價格順序
			foreach ($sort_data as $sort) {
				if(isset($price_data[$sort])) {
					$current_price = $price_data[$sort];
					break;
				}
			}
		}
		return $current_price;
	}
}
