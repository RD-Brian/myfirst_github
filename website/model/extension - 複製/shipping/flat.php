<?php
class ModelExtensionShippingFlat extends Model {
	function getQuote($address) {


		$this->load->language('extension/shipping/flat');

		$extensions = $this->getExtensions();

		$method_data = array();

		if(!empty($extensions)) {
			foreach ($extensions as $key => $extension) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "city_to_geo_zone WHERE geo_zone_id = '" . (int)$extension['setting']['shipping_flat_geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (city_id = '" . (int)$address['city_id'] . "' OR city_id = '0')");

				if (!$extension['setting']['shipping_flat_geo_zone_id']) {
					$status = true;
				} elseif ($query->num_rows) {
					$status = true;
				} else {
					$status = false;
				}

				if ($status) {
					$quote_data = array();
					$quote_data['flat'] = array(
						'module_id'    => $extension['module_id'],
						'code'         => 'flat.'.$extension['module_id'],
						'title'        => $extension['name'],
						'cost'         => $extension['setting']['shipping_flat_cost'],
						'tax_class_id' => $extension['setting']['shipping_flat_tax_class_id'],
						'text'         => $this->currency->format($this->tax->calculate($extension['setting']['shipping_flat_cost'], $extension['setting']['shipping_flat_tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'])
						);

					$method_data[] = array(
						'code'       => 'flat.'.$extension['module_id'],
						'title'      => $extension['name'],
						'quote'      => $quote_data,
						'sort_order' => $extension['setting']['sort_order'],
						'error'      => false
						);
				}
			}
		}

		return $method_data;
	}

	private function getExtensions() {
		$modules = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "module WHERE code = 'shipping_flat'");

		if ($query->rows) {
			foreach ($query->rows as $module) {
				$modules[] = array(
					'module_id'=>$module['module_id'],
					'name'=>$module['name'],
					'code'=>$module['code'],
					'setting'=>json_decode($module['setting'], true)
					);
			}
		}

		return $modules;
	}
}