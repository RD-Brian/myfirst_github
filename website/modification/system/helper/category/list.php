<?php

if(!function_exists('makeList')) {
	function makeList($makeList,$product_category_id/*,$level = 1*/) {
		$level = 1;
		$html = '';

		if(!empty($makeList)) {
			foreach ($makeList as $menu) {
				$html .= '<div class="product-list">';
        $html .= '  <div class="product-list-box">';
				if ($menu['product_category_id'] == $product_category_id) {
					$html .= '		<a href="'.$menu['href'].'" class="product-active fadeInUp wow">'.$menu['name'].'</a>';
				} else {
					$html .= '		<a href="'.$menu['href'].'" class="fadeInUp wow">'.$menu['name'].'</a>';
				}

				if (!empty($menu['subcategory'])) {

					$html .= '		<div class="product-list-class">';
					$html .= submakeList($menu['subcategory'],$level);
					$html .= '		</div>';
				}

				$html .= '	</div>';
				$html .= '</div>';
			}
		}
		
		return $html;
	}
}

if(!function_exists('submakeList')) {
	function submakeList($makeList,$level=1) {
		$level ++;
		$html = '';
		if(!empty($makeList)) {
			foreach ($makeList as $menu) {
				
				if (!empty($menu['subcategory'])) {
					//第三層選單
					// $html .= '<li class="has-sub"><span class="p-icon p-icon0'. $level . '"></span><a href="'.$menu['href'].'">'.$menu['name'].'</a>';
				}	else{
					$html .= '<a href="'.$menu['href'].'">'.$menu['name'].'</a>';
				}

			}
		}
		return $html;
	}
}




