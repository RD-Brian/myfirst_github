<?php
class ControllerFrameworkDocFooter extends Controller {
	public function index() {
		$data['scripts'] = $this->document->getScripts('footer');
		$data['theme'] = $this->_theme;
		return $this->load->view('framework/doc_footer', $data);
	}
}
