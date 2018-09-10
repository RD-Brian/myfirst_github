<?php
class ControllerAttractionsList extends Controller {
	public function index() {
		$this->document->addStyle('view/theme/default/stylesheet/page/attractions.css');
		$this->document->addStyle('view/javascript/swiper/css/swiper.min.css');
		$this->document->addStyle('view/javascript/swiper/css/green.css');
		$this->document->addScript('view/javascript/swiper/js/swiper.jquery.js');

		$this->load->language('attractions/list');
		$this->load->model('attractions/attractions');
		$this->load->model('tool/image');

		$data['banner'] = $this->model_tool_image->resize('room_banner.png', 1920, 725);

		$data['config_room_price'] = $this->config->get('config_room_price');

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'rd.name';
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
		
		$data['attractions'] = array();

		$filter_data = array(
			'sort'               => $sort,
			'order'              => $order,
			'start'              => ($page - 1) * $limit,
			'limit'              => $limit
		);

		$totals = $this->model_attractions_attractions->getTotalAttractions($filter_data);
		$attractions = $this->model_attractions_attractions->getAttractions($filter_data);

		foreach ($attractions as $key => $info) {
			if (is_file(DIR_UPLOADS . $info['path'] . $info['image'])) {
				$image = $this->model_tool_image->resize($info['path'].$info['image'], 585, 390);
			} else {
				$image = $this->model_tool_image->resize('placeholder.png', 585, 390);
			}

			$data['attractions'][] = array(
				'attractions_id'     => $info['attractions_id'],
				'name'        => $info['name'],
				'thumb'       => $image,
				'latitude'    => $info['latitude'],
				'longitude'    => $info['longitude'],
				'description' => html_entity_decode($info['description'], ENT_QUOTES, 'UTF-8'),
				'url'         => $info['url'] ? $info['url'] : '',
				'href'        => $this->url->link('attractions/attractions', 'attractions_id=' . $info['attractions_id'])
				);
		}

		$pagination = new Pagination();
		$pagination->total = $totals;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('room/category', 'page={page}');
		$data['pagination'] = $pagination->render();

		$data['images'] = array(
			'images/home_1.jpg',
			'images/home_2.jpg',
			'images/home_3.jpg',
			'images/home_4.jpg'
			);

		$this->_output('attractions/list', $data);
	}
}