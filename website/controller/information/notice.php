<?php
class ControllerInformationNotice extends Controller {
	public function index() {
		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));
		$this->document->addStyle('view/theme/default/stylesheet/page/notice.css');
		$this->document->addStyle('view/javascript/swiper/css/swiper.min.css');
		$this->document->addStyle('view/javascript/swiper/css/ease.css');
		$this->document->addScript('view/javascript/swiper/js/swiper.jquery.js');

		if (isset($this->request->get['route'])) {
			$this->document->addLink($this->config->get('config_url'), 'canonical');
		}

		$data['images'] = array(
			'images/home_1.jpg',
			'images/home_2.jpg',
			'images/home_3.jpg',
			'images/home_4.jpg'
			);

		$this->load->model('setting/setting');
		$this->load->model('information/information');

		$language_id = $this->config->get('config_language_id');

		$notice_id = $this->model_setting_setting->getSetting('info');
		
		$room_notice = $this->model_information_information->getInformation($notice_id['info_room_notice']);

		$data['room_notice'] = html_entity_decode($room_notice['description'], ENT_QUOTES, 'UTF-8');

		$this->_output('information/notice', $data);
	}
}
