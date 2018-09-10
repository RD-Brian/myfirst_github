<?php
class ControllerFrameworkDocHead extends Controller {
	public function index() {

		// <html>
		$data['lang'] = $this->language->get('code');
		$data['direction'] = $this->language->get('direction');
		// <title></title>
		$data['title'] = $this->document->getTitle();
		// <base>
		$data['base'] = HTTP_BASE;
		// theme
		$data['theme'] = $this->_theme;
		/**
		 * <meta></meta>
		 */
		$data['description'] = $this->document->getDescription();
		$data['keywords'] = $this->document->getKeywords();
		$data['links'] = $this->document->getLinks();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts();

		/**
		 * og tag
		 */
		$data['ogs'] = $this->document->getOg();

		// Analytics
		$this->load->model('setting/extension');
		$data['analytics'] = array();
		$analytics = $this->model_setting_extension->getExtensions('analytics');
		foreach ($analytics as $analytic) {
			if ($this->config->get('analytics_' . $analytic['code'] . '_status')) {
				$data['analytics'][] = $this->load->controller('extension/analytics/' . $analytic['code'], $this->config->get('analytics_' . $analytic['code'] . '_status'));
			}
		}

		return $this->load->view('framework/doc_head', $data);
	}
}