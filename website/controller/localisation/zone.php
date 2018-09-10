<?php
class ControllerLocalisationZone extends Controller {
	private $error = array();

	public function index() {}

	public function zone() {
		$json = array();
		$this->load->model('localisation/zone');
		$zones = $this->model_localisation_zone->getZonesByCity($this->request->get['city_id']);

		if ($zones) {
			foreach ($zones as $key => $zone) {
				$json[] = array(
					'zone_id' => $zone['zone_id'],
					'name' => $zone['name']
					);
			}
		}
		$this->_json($json);
	}

	public function postcode() {
		$json = '';
		$this->load->model('localisation/zone');
		$postcode = $this->model_localisation_zone->getPostcodeByZone($this->request->get['zone_id']);

		if ($postcode) {
			$json = $postcode;
		}
		$this->_json($json);
	}
}