<?php
class ControllerRoomCategory extends Controller {
	public function index() {
		$this->load->language('room/category');

		$this->load->model('room/category');
		$this->load->model('room/room');
		$this->load->model('room/discount');
		$this->load->model('setting/setting');

		$this->load->model('tool/image');
		
		$this->document->addStyle('view/javascript/revolution/css/layers.css');
		$this->document->addStyle('view/javascript/revolution/css/navigation.css');
		$this->document->addStyle('view/javascript/revolution/css/settings.css');
		$this->document->addStyle('view/theme/default/stylesheet/page/room_category.css');


		$this->document->addScript('view/javascript/revolution/js/jquery.themepunch.tools.min.js');
		$this->document->addScript('view/javascript/revolution/js/jquery.themepunch.revolution.min.js');
		$this->document->addScript('view/javascript/revolution/js/extensions/revolution.extension.actions.min.js');
		$this->document->addScript('view/javascript/revolution/js/extensions/revolution.extension.carousel.min.js');
		$this->document->addScript('view/javascript/revolution/js/extensions/revolution.extension.kenburn.min.js');
		$this->document->addScript('view/javascript/revolution/js/extensions/revolution.extension.layeranimation.min.js');
		$this->document->addScript('view/javascript/revolution/js/extensions/revolution.extension.migration.min.js');
		$this->document->addScript('view/javascript/revolution/js/extensions/revolution.extension.navigation.min.js');
		$this->document->addScript('view/javascript/revolution/js/extensions/revolution.extension.parallax.min.js');
		$this->document->addScript('view/javascript/revolution/js/extensions/revolution.extension.slideanims.min.js');
		$this->document->addScript('view/javascript/revolution/js/extensions/revolution.extension.video.min.js');

		$data['config_room_price'] = $this->config->get('config_room_price');

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'r.sort_order';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = 8;
		}

		$data['categories'] = array();

		$filter_data = array();

		// $totals = $this->model_room_room->getTotalRooms($filter_data);
		$categories = $this->model_room_category->getCategories($filter_data);

		$image_setting = $this->model_setting_setting->getSetting('image');

		foreach ($categories as $key => $category) {
			if (is_file(DIR_UPLOADS . $category['path'] . $category['image'])) {
				$image = $this->model_tool_image->resize($category['path'].$category['image'], $image_setting['image_room_category_width'], $image_setting['image_room_category_height']);
			} else {
				$image = $this->model_tool_image->resize('placeholder.png',  $image_setting['image_room_category_width'], $image_setting['image_room_category_height']);
			}

			$room = $this->model_room_room->getCtegoryLastRoom($category['room_category_id']);
			$discount = array();
			$weekday = '';
			$holiday = '';
			$current_price = '';
			$room_id = '';

			$category_rooms = $this->model_room_room->getCategoryOtherRooms($category['room_category_id'],$room['room_id']);



			$rooms = array();

			$rooms[] = array(
				'name' => $room['name'],
				'href' => $this->url->link('room/room', 'room_id=' . $room['room_id'] . '&room_category_id=' . $category['room_category_id'])
				);

			if(!empty($category_rooms)) {
				foreach ($category_rooms as $key => $category_room) {
					$rooms[] = array(
						'name' => $category_room['name'],
						'href' => $this->url->link('room/room', 'room_id=' . $category_room['room_id'] . '&room_category_id=' . $category['room_category_id'])
						);
				}
			}

			$weekday = $this->currency->format($category['weekday_price'], $this->config->get('config_currency'));
			$holiday = $this->currency->format($category['holiday_price'], $this->config->get('config_currency'));
			$hectic_day_price = $this->currency->format($category['hectic_day_price'], $this->config->get('config_currency'));

			// if(!empty($room)) {
			// 	// $discount = $this->model_room_discount->getRoomDiscounts($room['room_category_id']);
			// 	foreach ($room as $key => $value) {
			// 			$weekday = $this->currency->format($value['weekday_price'], $this->config->get('config_currency'));
			// 	}

			// 	// $current_price = $this->booking->getCurrentPrice($room['price'],$discount);
			// 	$room_id = $room['room_id'];
			// }

			$_price = $this->currency->format($room['price'], $this->config->get('config_currency'));

			$data['categories'][] = array(
				'room_category_id' => $category['room_category_id'],
				'name'        => $category['name'],
				'thumb'       => $image,
				'price'       => !empty($room['price']) ? $this->currency->format($room['price'], $this->config->get('config_currency')) : '',
				'rest_price'  => !empty($room['rest_price']) ? $this->currency->format($room['rest_price'], $this->config->get('config_currency')) : '',
				'rest_time'   => !empty($room['rest_time']) ? $room['rest_time'] : '',
				'weekday' => $weekday,
				'holiday' => $holiday,
				'hectic_day_price' => $hectic_day_price,
				'current_price' => !empty($current_price) ? $this->currency->format($current_price, $this->config->get('config_currency')) : null,
				'discount' => $discount,
				'rooms'    => $rooms,
				'href'        => $this->url->link('room/room', 'room_id=' . $room_id . '&room_category_id=' . $category['room_category_id'])
				);
		}

		$this->_output('room/category', $data);
	}
}