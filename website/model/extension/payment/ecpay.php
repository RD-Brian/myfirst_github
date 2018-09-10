<?php

class ModelExtensionPaymentEcpay extends Model {
    private $prefix = 'payment_ecpay_';
    private $model_name = 'ecpay';
    private $model_path = 'extension/payment/ecpay';
	private $trans = array();
	private $libraryList = array('EcpayCartLibrary.php');
	
	public function getMethod() {

        // Set the payment method parameters
        $this->load->language($this->model_path);
        $extensions = $this->getExtensions();
        $method_data = array();
        if ($extensions) {
            foreach ($extensions as $key => $extension) {
                $method_data[] = array(
                    'module_id'    => $extension['module_id'],
                    'code'       => $this->model_name.'.'.$extension['module_id'],
                    'title'      => $extension['name'],
                    'terms'      => '',
                    'sort_order' => 0,//$extension['setting']['sort_order'],
                    'error'      => false
                    );
            }
        }
        return $method_data;
    }

    private function getExtensions() {
        $modules = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "module WHERE code = 'payment_ecpay'");

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
	
	public function loadLibrary() {
		foreach ($this->libraryList as $path) {
            require_once(modification(DIR_SYSTEM . 'library/ecpay/'.$path));
		}
	}

    public function getHelper($merchant_id) {
        return new EcpayCartLibrary(array('merchantId' => $merchant_id));
    }
}
