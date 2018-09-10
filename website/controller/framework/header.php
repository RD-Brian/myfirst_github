<?php
class ControllerFrameworkHeader extends Controller {
	public function index() {

		//http
		$data['title'] = $this->document->getTitle();
		$data['base'] = HTTP_BASE;
		$server = HTTP_SERVER;
		//doc
		$data['description'] = $this->document->getDescription();
		$data['keywords'] = $this->document->getKeywords();
		$data['links'] = $this->document->getLinks();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts();
		$data['lang'] = $this->language->get('code');
		$data['direction'] = $this->language->get('direction');


		$this->load->language('common/header');

		// Text
		$data['text_menu'] = $this->language->get('text_menu');

		// 固定選單
		$data['home'] = $this->url->link('common/home');
		$data['about'] = $this->url->link('information/about');
		$data['rooms'] = $this->url->link('room/category');
		$data['contact'] = $this->url->link('support/contact');
		$data['attractions'] = $this->url->link('attractions/list');
		//login 靜態 data
		$data['shop'] = $this->url->link('product/category');
		$data['login'] = $this->url->link('account/login');
		$data['edit'] = $this->url->link('account/edit');
		$data['login_confirm'] = $this->url->link('framework/menu/login');
		$data['menu'] = $this->url->link('framework/menu');
		$data['register'] = $this->url->link('account/register');
		$data['forgotten'] = $this->url->link('account/forgotten');
		//顯示首頁動畫
		$data['is_home'] = false;

		$this->load->model('information/information');

		$data['informations'] = array();

		$informations = $this->model_information_information->getInformations();

		foreach ($informations as $result) {
			if ($result['menu']) {
				$data['informations'][] = array(
					'title' => $result['title'],
					'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
				);
			}
		}

		//FB 社群
		$data['modules'] = array();
		$files = glob(DIR_APPLICATION . '/controller/extension/social/*.php');
		if ($files) {
			foreach ($files as $file) {
				$result = $this->load->controller('extension/social/' . basename($file, '.php'));
				if ($result) {
					$data['modules'][] = $result;
				}
			}
		}

		$data['doc_head'] = $this->load->controller('framework/doc_head');
		$data['menu'] = $this->load->controller('framework/menu');
		return $this->load->view('framework/header', $data);
	}
}