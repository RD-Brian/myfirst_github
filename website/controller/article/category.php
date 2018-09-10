<?php
class ControllerArticleCategory extends Controller {
	public function index() {
		//doc
		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));
		//load
		$this->load->language('article/category');
		$this->load->model('setting/setting');
		$this->load->model('article/article');
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
			$limit = 2;
		}

		$data['articles'] = array();

		$filter_data = array(
			'sort'               => $sort,
			'order'              => $order,
			'start'              => ($page - 1) * $limit,
			'limit'              => $limit
		);

		$totals = $this->model_article_article->getTotalArticle($filter_data);
		$articles = $this->model_article_article->getArticles($filter_data);

		$image_setting = $this->model_setting_setting->getSetting('image');
// $image_setting['image_article_thumb_width'], $image_setting['image_article_thumb_height']
		foreach ($articles as $key => $article) {
			if (is_file(DIR_UPLOADS . $article['path'] . $article['image'])) {
				$image = $this->model_tool_image->resize($article['path'].$article['image'],$image_setting['image_article_thumb_width'], $image_setting['image_article_thumb_height']);
			} else {
				$image = $this->model_tool_image->resize('placeholder.png',$image_setting['image_article_thumb_width'], $image_setting['image_article_thumb_height']);
			}


			$data['articles'][] = array(
				'article_id'     => $article['article_id'],
				'name'        => strlen($article['name']) > 48 ? utf8_substr(strip_tags(html_entity_decode($article['name'])), 0, 16) . '...' : $article['name'],
				'thumb'       => $image,
				'description'	=> html_entity_decode($article['description'], ENT_QUOTES, 'UTF-8'),
				'date_added'  => get_date_format($article['date_added'],'Y-m-d'),
				'href'        => $this->url->link('article/article', 'article_id=' . $article['article_id'])
				);
		}


		$pagination = new Pagination();
		$pagination->total = $totals;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('article/category', 'page={page}');
		$data['pagination'] = $pagination->render();


		$this->_output('article/category', $data);
	}
}