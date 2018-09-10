<?php
class ControllerRoomRoom extends Controller {
	private $error = array();

	/**
	 * 顯示列表
	 * @return [type] [description]
	 */
	public function index() {
		$this->load->language('room/room');
		$this->load->model('room/room');
		$this->load->model('room/category');
		// $this->load->model('room/discount');
		$this->load->model('tool/image');
		$this->load->model('setting/setting');

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addStyle('view/javascript/revolution/css/layers.css');
		$this->document->addStyle('view/javascript/revolution/css/navigation.css');
		$this->document->addStyle('view/javascript/revolution/css/settings.css');
		$this->document->addStyle('view/theme/default/stylesheet/page/room.css');


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

	
		/* default breadcrumbs */
		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', true)
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('room/room', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => !isset($this->request->get['room_id']) ? $this->language->get('text_add') : $this->language->get('text_edit'),
			'href' => $this->url->link('room/room/edit', true)
		);


		if (isset($this->request->get['room_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$info = $this->model_room_room->getRoomInformation($this->request->get['room_id']);
			$room_price = $this->model_room_category->getCategoryPrice($this->request->get['room_category_id']);
			// $discount = $this->model_room_discount->getRoomDiscounts($this->request->get['room_id']);
		} else {
			$this->response->redirect($this->url->link('common/home'));
		}

		$data['heading_title'] = '全部房型';
		$data['name'] = $info['name'];
		$data['price'] = $this->currency->format($room_price['price'], $this->config->get('config_currency'));
		$data['rest_price'] = $this->currency->format($room_price['rest_price'], $this->config->get('config_currency'));
		// $data['plus_price'] = !empty($info['plus_price']) ? $this->currency->format($info['plus_price'], $this->config->get('config_currency')) : 0;
		$data['rest_time'] = $room_price['rest_time'];
		$data['config_room_price'] = $this->config->get('config_room_price');
		$data['description'] = html_entity_decode($info['description'], ENT_QUOTES, 'UTF-8');
		$data['notice'] = html_entity_decode($info['notice'], ENT_QUOTES, 'UTF-8');
		// $current_price = $this->booking->getCurrentPrice($info['price'],$discount);
		// $data['current_price'] = $current_price;
		// $data['current_price_text'] = $this->currency->format($current_price, $this->config->get('config_currency'));
		$data['hectic_day_price'] = $this->currency->format($room_price['hectic_day_price'], $this->config->get('config_currency'));
		$data['back'] = $this->url->link('room/category');

		$data['weekday'] = $this->currency->format($room_price['weekday_price'], $this->config->get('config_currency'));
		$data['holiday'] = $this->currency->format($room_price['holiday_price'], $this->config->get('config_currency'));

			// if(!empty($discount)) {
			// 	foreach ($discount as $key => $value) {
			// 		if($value['cycle_type'] == 'weekday') {
			// 			$data['weekday'] = $this->currency->format($value['value'], $this->config->get('config_currency'));
			// 		}

			// 		if($value['cycle_type'] == 'holiday') {
			// 			$data['holiday'] = $this->currency->format($value['value'], $this->config->get('config_currency'));
			// 		}
			// 	}
			// }

		// 圖片集
		$images = $this->model_room_room->getRoomImages($this->request->get['room_id']);

		$image_setting = $this->model_setting_setting->getSetting('image');

		$data['images'] = array();

		foreach ($images as $k => $info) {

			if (is_file(DIR_UPLOADS . $info['path'] . $info['image'])) {
				$image = $this->model_tool_image->resize($info['path'] . $info['image'], $image_setting['image_room_popup_width'], $image_setting['image_room_popup_height']);
				$thumb = $info['path'] . $info['image'];
			} else {
				$image = $this->model_tool_image->resize('placeholder.png',  $image_setting['image_room_popup_width'], $image_setting['image_room_popup_height']);
				$thumb = 'no_image.png';
			}


			$data['images'][] = array(
				'image'      => $image,
				'thumb'      => $this->model_tool_image->resize($thumb, 150, 100),
				'sort_order' => $info['sort_order']
			);
		}

		// 房型設備
		$this->load->model('room/facility');
		$facilities = $this->model_room_room->getRoomFacilities($this->request->get['room_id']);
		foreach ($facilities as $room_facility_id) {
			$facility = $this->model_room_facility->getFacility($room_facility_id);

			if ($facility) {
				$data['facilities'][] = array(
					'room_facility_id' => $facility['room_facility_id'],
					'name'        => $facility['name']
				);
			}
		}

		$data['others'] = array();
		$others = $this->model_room_room->getCategoryOtherRooms($this->request->get['room_category_id'],$this->request->get['room_id']);

		if(!empty($others)) {
			foreach ($others as $key => $other) {
				$data['others'][] = array(
					'name' => $other['name'],
					'href' => $this->url->link('room/room', 'room_id=' . $other['room_id'] . '&room_category_id=' . $this->request->get['room_category_id'])
					);
			}
		}

		$this->model_room_room->updateViewed($this->request->get['room_id']);

		$this->_output('room/room',$data);
	}
}