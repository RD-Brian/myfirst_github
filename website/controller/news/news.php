<?php
class ControllerNewsNews extends Controller {
	private $error = array();

	/**
	 * 顯示列表
	 * @return [type] [description]
	 */
	public function index() {
		//doc
		
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));
		//load
		$this->load->language('news/news');
		$this->load->model('news/news');

		if (isset($this->request->get['news_id'])) {
			$info = $this->model_news_news->getNews($this->request->get['news_id']);
		} else {
			$this->response->redirect($this->url->link('news/category'));
		}
		//data
		if ($info) {

			$this->document->setTitle($info['name']);
			$data['back'] = $this->url->link('news/category', true);
			$data['description'] = html_entity_decode($info['description'], ENT_QUOTES, 'UTF-8');
			$data['title'] = $info['name'];
			//圖片
			$this->load->model('tool/image');
			$this->load->model('setting/setting');
			$image_setting = $this->model_setting_setting->getSetting('image');

			//處理日期
			//星期
			$weekday  = date('w', strtotime($info['date_added']));
			$weeklist = array('日', '一', '二', '三', '四', '五', '六');
			$data['weekday'] = $weeklist[$weekday];
			//月
			$data['month']  = date('M', strtotime($info['date_added']));
			//日
			$data['day']  = date('d', strtotime($info['date_added']));
			
			if (is_file(DIR_UPLOADS . $info['path'] . $info['image'])) {
				$data['image'] = $this->_previewImage.$info['path'].$info['image'];
			} else {
				$data['image'] = $this->_previewImage.$info['path'].$info['image'];
			}
			// $this->_previewImage.$info['path'].$info['image'];

			$this->model_news_news->updateViewed($this->request->get['news_id']);

		} else {
			$this->response->redirect($this->url->link('news/category'));
		}

		

		$this->_output('news/news',$data);
	}
}
