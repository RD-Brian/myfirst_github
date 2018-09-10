<?php
class ControllerNewsCategory extends Controller {
	public function index() {
		//doc
		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));
		//load
		$this->load->language('news/category');
		$this->load->model('setting/setting');
		$this->load->model('news/news');
		$this->load->model('tool/image');
		//過濾data
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'n.date_added';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = 6;
		}

		$data['newsies'] = array();

		$filter_data = array(
			'sort'               => $sort,
			'order'              => $order,
			'start'              => ($page - 1) * $limit,
			'limit'              => $limit
		);

		$totals = $this->model_news_news->getTotalNews($filter_data);
		$newsies = $this->model_news_news->getNewsies($filter_data);

		$image_setting = $this->model_setting_setting->getSetting('image');
// $image_setting['image_news_thumb_width'], $image_setting['image_news_thumb_height']
		foreach ($newsies as $key => $news) {
			if (is_file(DIR_UPLOADS . $news['path'] . $news['image'])) {
				$image = $this->model_tool_image->resize($news['path'].$news['image'],$image_setting['image_news_thumb_width'], $image_setting['image_news_thumb_height']);
			} else {
				$image = $this->model_tool_image->resize('placeholder.png',$image_setting['image_news_thumb_width'], $image_setting['image_news_thumb_height']);
			}
			//星期
			$weekday  = date('w', strtotime($news['date_added']));
			$weeklist = array('日', '一', '二', '三', '四', '五', '六');
			//月
			$month  = date('M', strtotime($news['date_added']));
			//日
			$day  = date('d', strtotime($news['date_added']));

			$data['newsies'][] = array(
				'news_id'     => $news['news_id'],
				'name'        => strlen($news['name']) > 48 ? utf8_substr(strip_tags(html_entity_decode($news['name'])), 0, 16) . '...' : $news['name'],
				'thumb'       => $image,
				'weekday'     => $weeklist[$weekday],
				'day'         => $day,
				'month'       => strtoupper($month),
				'date_added'  => get_date_format($news['date_added'],'Y-m-d'),
				'href'        => $this->url->link('news/news', 'news_id=' . $news['news_id'])
				);
		}


		$pagination = new Pagination();
		$pagination->total = $totals;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('news/category', 'page={page}');
		$data['pagination'] = $pagination->render();


		$this->_output('news/category', $data);
	}
}