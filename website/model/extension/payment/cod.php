<?php
class ModelExtensionPaymentCod extends Model {
	public function getMethod() {

		$this->load->language('extension/payment/cod');

		$extensions = $this->getExtensions();

		$method_data = array();

		if(!empty($extensions)) {
			foreach ($extensions as $key => $extension) {
				$method_data[] = array(
					'module_id'    => $extension['module_id'],
					'code'       => 'cod.'.$extension['module_id'],
					'title'      => $extension['name'],
					'terms'      => '',
					'sort_order' => $extension['setting']['sort_order'],
					'error'      => false
					);
			}
		}

		return $method_data;
	}

	private function getExtensions() {
		$modules = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "module WHERE code = 'payment_cod'");

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
