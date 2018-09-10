<?php
class ControllerInformationLocation extends Controller {
	public function index() {

		$this->document->addStyle('view/theme/default/stylesheet/page/location.css');
		$this->document->addStyle('view/javascript/swiper/css/swiper.min.css');
		$this->document->addStyle('view/javascript/swiper/css/ease.css');
		$this->document->addScript('view/javascript/swiper/js/swiper.jquery.js');

		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));

		if (isset($this->request->get['route'])) {
			$this->document->addLink($this->config->get('config_url'), 'canonical');
		}

		$data['images'] = array(
			'images/home_1.jpg',
			'images/home_2.jpg',
			'images/home_3.jpg',
			'images/home_4.jpg'
			);

		$this->_output('information/location', $data);
	}
}
