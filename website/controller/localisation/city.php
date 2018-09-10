<?php
class ControllerLocalisationCity extends Controller {
	private $error = array();

	public function index() {}

	public function city() {
		$json = array();
		$this->load->model('localisation/city');
		$cities = $this->model_localisation_city->getCityByCountry($this->request->get['country_id']);

		if ($cities) {
			foreach ($cities as $key => $city) {
				$json[] = array(
					'city_id' => $city['city_id'],
					'name' => $city['name']
					);
			}
		}
		$this->_json($json);
	}	
}