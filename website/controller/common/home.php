<?php
class ControllerCommonHome extends Controller {
	public function index() {
		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));
		//靜態data
		$data['category'] = $this->url->link('room/category');
		$data['about'] = $this->url->link('information/about');
		$data['news'] = $this->url->link('news/category');
		$data['contact'] = $this->url->link('support/contact');
		$data['attractions'] = $this->url->link('attractions/list');
		$data['article'] = $this->url->link('article/category');

		//動態data 最新消新前5則
		$this->load->model('news/news');
		$newsies = $this->model_news_news->getNewsies(array('start' => 0,'limit' => 5,'order' => 'DESC'));

		$this->load->model('setting/setting');
		$this->load->model('tool/image');
		$setting_image = $this->model_setting_setting->getSetting('image');

		foreach ($newsies as $key => $news) {

		 if (is_file(DIR_UPLOADS . $news['path'] . $news['image'])) {
				$image = $this->model_tool_image->resize($news['path'].$news['image'],  295, 275);
			} else {
				$image = $this->model_tool_image->resize($news['path'].$news['image'],  295, 275);
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
				'image'				=> $image,
				'weekday'     => $weeklist[$weekday],
				'day'         => $day,
				'month'       => strtoupper($month),
				'date_added'  => get_date_format($news['date_added'],'Y-m-d'),
				'href'        => $this->url->link('news/news', 'news_id=' . $news['news_id'])
				);
		}
		//動態data 商品前三項(熱門)
		$this->load->model('product/product');
		$HotPurchased = $this->model_product_product->getPurchased(array('start'=>0,'limit'=>3));

		foreach ($HotPurchased as $key => $value) {

			$hot_product = $this->model_product_product->getProduct($value['product_id']);

			if (is_file(DIR_UPLOADS . $hot_product['path'] . $hot_product['image'])) {
				$image = $this->model_tool_image->resize($hot_product['path'].$hot_product['image'],  246, 461);
			} else {
				$image = $this->model_tool_image->resize($hot_product['path'].$hot_product['image'],  246, 461);
			}

			$data['productLists'][] = array(
				'product_id'           => $hot_product['product_id'],
				'product_category_id'  => isset($hot_product['product_category_id']) ? $hot_product['product_category_id'] : '',
				'href'								 => $this->url->link('product/product','&product_id=' . $hot_product['product_id']),
				'model'								 => $hot_product['model'],
				'image'								 => $image,
				'img'									 => $image,
				'path'								 => $hot_product['path'],
				'images'							 => $hot_product['image'],
				'name'								 => strlen($hot_product['name']) > 18 ? utf8_substr(strip_tags(html_entity_decode($hot_product['name'])), 0, 6) . '...' : html_entity_decode($hot_product['name']),
				'price'								 => $this->currency->format($hot_product['price'], $this->config->get('config_currency')),
				);
			// print_r($data['productLists']);
		}
		// print_r($productList);
		

		//文章
		$this->load->model('article/article');

		$filter_data = array(
			'sort'               => 'a.article',
			'order'              => 'DESC',
			'limit'				 => 1,
			'start'				 => 0,
		);

		$articles = $this->model_article_article->getArticles($filter_data);
		foreach ($articles as $key => $value) {

			if (is_file(DIR_UPLOADS . $value['path'] . $value['image'])) {
				$_image = $this->model_tool_image->resize($value['path'].$value['image'],  550, 600);
			} else {
				$_image = $this->model_tool_image->resize($value['path'].$value['image'],  550, 600);
			}

			$data['articles'][] = array(
				'name'        => $value['name'],
				'description' => strlen($value['description']) > 100 ? utf8_substr(strip_tags(html_entity_decode($value['description'])), 0, 20) . '...': html_entity_decode($value['description']),
				'href'        => $this->url->link('article/article', '&article_id=' . $value['article_id']),
				'image'		  => $_image,
			);
		}
		

		$this->_output('common/home', $data);
	}
}
