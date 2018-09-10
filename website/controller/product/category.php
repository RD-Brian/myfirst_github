<?php
class ControllerProductCategory extends Controller {
	public function index() {
		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));

		$this->load->model('product/category');
		$this->load->model('product/product');
		$this->load->model('tool/image');
		$this->load->model('setting/setting');

		//靜態data
		$data['action'] = $this->url->link('product/category');

		if(isset($this->request->get['product_category_id'])){
			$product_category_id = $this->request->get['product_category_id'];
		}
		else{
			$product_category_id = '';
		}

		//分類
		$categoryTree = $this->model_product_category->getCategoryTree();

		$this->load->helper('category/list');
		$data['category'] = makeList($categoryTree,$product_category_id);

		if(isset($this->request->post['product_name'])){
			$product_name = $this->request->post['product_name'];
		}	elseif(isset($this->request->get['product_name'])){
			$product_name = $this->request->get['product_name'];
		}	else{
			$product_name = '';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$product_data = array(
			'product_name' 				=> $product_name,
			'product_category_id'	=> $product_category_id,
			'sort'								=> 'p.sort_order',
			'order'								=> 'ASC',
		);

		$productList = $this->model_product_product->getProducts($product_data,($page - 1) * 6, 6);

		//圖片大小
		$setting_image = $this->model_setting_setting->getSetting('image');
		foreach ($productList as $key => $value) {
// $setting_image['image_product_category_width'], $setting_image['image_product_category_height']
			if (is_file(DIR_UPLOADS . $value['path'] . $value['image'])) {
				$image = $this->model_tool_image->resize($value['path'].$value['image'], $setting_image['image_product_category_width'], $setting_image['image_product_category_height']);
			} else {
				$image = $this->model_tool_image->resize($value['path'].$value['image'], $setting_image['image_product_category_width'], $setting_image['image_product_category_height']);
			}
			$price = $this->currency->format($value['price'], $this->config->get('config_currency'));
			//是否有原價
			$is_original_price = $value['is_original_price'];
			$original_price = $this->currency->format($value['original_price'], $this->config->get('config_currency'));

			$data['productLists'][] = array(
				'product_id'           => $value['product_id'],
				'product_category_id'  => isset($value['product_category_id']) ? $value['product_category_id'] : '',
				'href'								 => $this->url->link('product/product','&product_id=' . $value['product_id']),
				'model'								 => $value['model'],
				'image'								 => $image,
				'img'									 => $image,
				'path'								 => $value['path'],
				'images'							 => $value['image'],
				'name'								 => strlen($value['name']) > 18 ? utf8_substr(strip_tags(html_entity_decode($value['name'])), 0, 6) . '...' : $value['name'],
				'price'								 => $price,
				'is_original_price'		 => !empty($is_original_price) ? true : false,
				'original_price'			 => $original_price,
				'description'					 => $value['description']
				);
			// print_r($data['productLists']);
		}
		// print_r($productList);

		//分頁
		$category_total = $this->model_product_product->getProductTotal($product_data);

		$pagination = new Pagination();
		$pagination->total = $category_total;
		$pagination->page = $page;
		$pagination->limit = 6;
		$pagination->url = $this->url->link('product/category', 'page={page}', true);
		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($category_total) ? (($page - 1) * 6) + 1 : 0, ((($page - 1) * 6) > ($category_total - 6)) ? $category_total : ((($page - 1) * 6) + 6), $category_total, ceil($category_total / 6));
		 
		// print_r($category_total);

		$this->_output('product/category', $data);
	}
}

