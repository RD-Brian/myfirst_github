<?php
/**
 * @package		Ming Core System
 * @author		Caspar Chen
 * @copyright	Copyright (c) 2005 - 2017, Ming Design, Ltd. (https://www.mids.com.tw/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.mids.com.tw
 */

/**
* Controller class
*/
class Controller extends Engine_Controller {

	protected $_frame = array(
		'visual'         => 'framework/visual',
		'column_left'    => 'framework/column_left',
		'column_right'   => 'framework/column_right',
		'content_top'    => 'framework/content_top',
		'content_bottom' => 'framework/content_bottom',
		'bottom'         => 'framework/bottom',
		'footer'         => 'framework/footer',
		'header'         => 'framework/header'
		);

	protected $_useFrame = true;
	/**
	 *  相對路徑
	 * @var [string]
	 */
	protected $_server;
	/**
	 * 絕對路徑
	 * @var [type]
	 */
	protected $_base;

	/**
	 * 樣板圖片路徑
	 */
	protected $_themeImagePath;

	protected $_theme;

	protected $_previewImage;

	public function __construct($registry) {
		parent::__construct($registry);

		$this->_server = HTTP_SERVER;
		$this->_base   = HTTP_BASE;
		$this->_previewImage   = HTTP_UPLOADS;

		$this->_theme = $this->config->get('theme_default_directory');
		$this->_themeImagePath = $this->_base.'view/theme/'.$this->_theme.'/images/';
	}

	protected function _renewFrame($key,$frame) {
		$this->_frame[$key] = $frame;
	}

	protected function _removeFrame($key) {
		unset($this->_frame[$key]);
	}

	protected function _addFrame($frame = array(),$position = 'before') {
		if(!empty($frame)) {
			if($position == 'before') {
				$this->_frame = array_merge($frame,$this->_frame);
			}

			if($position == 'after') {
				$this->_frame = array_merge($this->_frame,$frame);
			}
		}
	}

	protected function _disableFrame() {
		$this->_useFrame = false;
	}

	protected function _output($route,$data,$return = false) {

		if($this->_useFrame && $this->_frame) {
			foreach ($this->_frame as $key => $path) {
				$data[$key] = $this->load->controller($path,$data);
			}
		}

		if($return == true) {
			return $this->load->view($route, $data);
		} else {
			$this->response->setOutput($this->load->view($route, $data));
		}
	}

	protected function _json($json) {
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	protected function _viewImage($image) {
		if(strpos($image, '/') === 0) {
			$image = substr($image,1);
		}
		return $this->_themeImagePath.$image;
	}
}