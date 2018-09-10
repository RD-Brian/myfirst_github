<?php
class ControllerInformationAbout extends Controller {
	public function index() {
		//doc
		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));
		$data = array();

		$this->_output('information/about', $data);
	}
}
