<?php
class ControllerProductProduct extends Controller {
	public function index() {

		$this->load->model('product/category');
		$this->load->model('product/product');
		$this->load->model('tool/image');

		$product = $this->model_product_product->getProduct($this->request->get['product_id']);

		if(!isset($this->request->get['product_id']) || empty($this->request->get['product_id']) || !$product){
			$this->response->redirect($this->url->link('product/category'));
		}

		if($product) {
			$this->document->setTitle($product['name']);
			$this->document->addLink($this->url->link('product/product', 'product_id=' . $this->request->get['product_id']), 'canonical');

			// css & script
			$this->document->addStyle('view/theme/default/stylesheet/baguettebox.min.css');
			$this->document->addStyle('view/theme/default/stylesheet/slick.css');
			$this->document->addScript('view/theme/default/javascript/slick.min.js');
			$this->document->addScript('view/theme/default/javascript/baguettebox.min.js');

			// data
			$data['product_category'] = $this->url->link('product/category');
			$data['gocheckcart'] = $this->url->link('checkout/cart');
			$data['check_cart'] = $this->url->link('product/cart/add');
			$data['checkout_remove'] = $this->url->link('product/cart/remove');
			$data['url_widget_cart'] = $this->url->link('widget/cart/info');

			// product
			$data['heading_title'] = $product['name'];
			$data['name'] = $product['name'];
			$data['description'] = html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8');
			$data['model'] = $product['model'];
			$data['product_id'] = $product['product_id'];
			$data['stock_status_id'] = $product['stock_status_id'];
			$data['shipping'] = $product['shipping'];
			$data['price'] = $this->currency->format($product['price'], $this->config->get('config_currency'));
			//庫存
			// $data['is_stock'] = $product['stock'];
			// $data['quantity'] = $product['quantity'];
			//是否有原價
			$data['is_original_price'] = $product['is_original_price'];
			$data['original_price'] = $this->currency->format($product['original_price'], $this->config->get('config_currency'));

			//分類
			$categoryTree = $this->model_product_category->getCategoryTree();

			$this->load->helper('category/list');
			// $data['category'] = makeList($categoryTree);

			//觀看數
			$this->model_product_product->updateViewed($this->request->get['product_id']);
			// og image
			// og image
			$og_width = '600';
			$og_height = '315';
			// 圖片
			$this->load->model('setting/setting');
			$setting = $this->model_setting_setting->getSetting('image');

			if (is_file(DIR_UPLOADS . $product['path'] . $product['image'])) {
				$cover = $this->model_tool_image->resize($product['path'].$product['image'], $og_width, $og_height);
				//第一張商品圖
				$data['first_image'] = $this->model_tool_image->resize($product['path'].$product['image'],$setting['image_product_popup_width'], $setting['image_product_popup_height']);
				$data['first_thumb_image'] = $this->model_tool_image->resize($product['path'].$product['image'], $setting['image_product_thumb_width'], $setting['image_product_thumb_height']);
				$data['first_popup_image'] = $this->model_tool_image->resize($product['path'].$product['image'],$setting['image_product_popup_width'], $setting['image_product_popup_height']);

			} else {
				$cover = $this->model_tool_image->resize($product['path'].$product['image'], $og_width, $og_height);
				//第一張商品圖
				$data['first_image'] = $this->model_tool_image->resize($product['path'].$product['image'],$setting['image_product_popup_width'], $setting['image_product_popup_height']);
				$data['first_thumb_image'] = $this->model_tool_image->resize($product['path'].$product['image'], $setting['image_product_thumb_width'], $setting['image_product_thumb_height']);
				$data['first_popup_image'] = $this->model_tool_image->resize($product['path'].$product['image'],$setting['image_product_popup_width'], $setting['image_product_popup_height']);
			}
			$this->document->setImage($cover);
			// 圖片
			$images = $this->model_product_product->getProductImages($product['product_id']);
			foreach ($images as $key => $image) {
				if($image['image_id'] != $product['image_id']){
				// popup->大圖 , thumb->小圖
				if (is_file(DIR_UPLOADS . $image['path'] . $image['image'])) {
					$related = $this->model_tool_image->resize($image['path'].$image['image'], $setting['image_product_popup_width'], $setting['image_product_popup_height']);
					$popup = $this->model_tool_image->resize($image['path'].$image['image'],$setting['image_product_popup_width'], $setting['image_product_popup_height']);
					$thumb = $this->model_tool_image->resize($image['path'].$image['image'], $setting['image_product_thumb_width'], $setting['image_product_thumb_height']);
				} else {
					$related = $this->model_tool_image->resize($image['path'].$image['image'], $setting['image_product_popup_width'], $setting['image_product_popup_height']);
					$popup = $this->model_tool_image->resize($image['path'].$image['image'], $setting['image_product_popup_width'], $setting['image_product_popup_height']);
					$thumb = $this->model_tool_image->resize($image['path'].$image['image'], $setting['image_product_thumb_width'], $setting['image_product_thumb_height']);
				}

				$data['images'][] = array(
					'related'	=> $related,
					'popup' => $popup,
					'thumb' => $thumb
					);
				}
			}

			//特價
			$special = false;
			$product_specials = $this->model_product_product->getProductSpecials($product['product_id']);
			foreach ($product_specials  as $product_special) {
				$special = $this->currency->format($product_special['price'], $this->config->get('config_currency'));
				// if (($product_special['date_start'] == '0000-00-00' || strtotime($product_special['date_start']) < time()) && ($product_special['date_end'] == '0000-00-00' || strtotime($product_special['date_end']) > time())) {
				// 	$special = $this->currency->format($product_special['price'], $this->config->get('config_currency'));

				// 	break;
				// }
			}

			$data['special'] = $special;

			//商品所屬的分類

			$ProductBelong = $this->model_product_product->getProductBelong($this->request->get['product_id']);

			foreach ($ProductBelong as $key => $value) {
				$data['breadcrumbs'][] = array(
					'name' => $value['name'],
					'href' => $this->url->link('product/category', 'product_category_id=' . $value['product_category_id']),
				);
			}

			//商品規格
			$data['specification_groups'] = $this->model_product_product->getProductSpecs($this->request->get['product_id']);
			// 商品選項
			$data['options'] = $this->_options($this->request->get['product_id']);
			// 商品附加選項
			$data['additional_options'] = $this->_additional_options($this->request->get['product_id']);

			//交貨日
			$data['shop_delivery_date'] = $this->config->get('shop_delivery_date');

			// 訂購說明
			if($this->config->get('shop_order_notice_id') !== null) {
				$this->load->model('information/information');
				$notice = $this->model_information_information->getInformation($this->config->get('shop_order_notice_id'));
				$data['notice'] = html_entity_decode($notice['description'], ENT_QUOTES, 'UTF-8');
			} else {
				$data['notice'] = '';
			}
			
			$data['action'] = $this->url->link('ajax/cart/add');

			$this->_output('product/product', $data);
		} else {
			//-> 404
		}
	}

	/**
	 * 商品選項模板處理
	 * @param  [type] $product_id [description]
	 * @return [type]             [description]
	 */
	private function _options($product_id) {
		//商品選項
		$data['options'] = array();

		foreach ($this->model_product_product->getProductOptions($product_id) as $option) {

			$product_option_value_data = array();
			foreach ($option['product_option_value'] as $option_value) {
				if ($option_value['quantity'] > 0) {
					if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
						$price = $this->currency->format($option_value['price'], $this->config->get('config_currency'));
					} else {
						$price = false;
					}

					$product_option_value_data[] = array(
						'product_option_value_id' => $option_value['product_option_value_id'],
						'option_value_id'         => $option_value['option_value_id'],
						'name'                    => $option_value['name'],
						'price'                   => !empty($price) ? $price : 0,
						'price_prefix'            => $option_value['price_prefix']
						);
				}
			}

			$data['options'][] = array(
				'product_option_id'    => $option['product_option_id'],
				'product_option_value' => $product_option_value_data,
				'option_id'            => $option['option_id'],
				'name'                 => $option['name'],
				'type'                 => $option['type'],
				'value'                => $option['value'],
				'required'             => $option['required']
			);
		}

		return $this->load->view('product/options',$data);
	}

	/**
	 * 商品附加選項模板
	 * @param  [type] $product_id [description]
	 * @return [type]             [description]
	 */
	private function _additional_options($product_id) {
		$data['additional_options'] = array();
		foreach ($this->model_product_product->getProductAddOptions($product_id) as $additional_option) {
			$product_additional_option_value_data = array();

			foreach ($additional_option['product_additional_option_value'] as $additional_option_value) {
					if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$additional_option_value['price']) {
						$price = $this->currency->format($additional_option_value['price'], $this->config->get('config_currency'));
					} else {
						$price = false;
					}
					$product_additional_option_value_data[] = array(
						'product_additional_option_value_id' => $additional_option_value['product_additional_option_value_id'],
						'additional_option_value_id'         => $additional_option_value['additional_option_value_id'],
						'name'                        => $additional_option_value['name'],
						'price'                       => !empty($price) ? $price : 0,
						'price_prefix'                => $additional_option_value['price_prefix']
					);
			}

			$data['additional_options'][] = array(
				'product_additional_option_id'    => $additional_option['product_additional_option_id'],
				'product_additional_option_value' => $product_additional_option_value_data,
				'additional_option_id'            => $additional_option['additional_option_id'],
				'name'                 		 => $additional_option['name'],
				'type'                 		 => $additional_option['type'],
				'value'                		 => $additional_option['value'],
				'required'             		 => $additional_option['required']
			);
		}

		return $this->load->view('product/additional_options',$data);
	}
}
