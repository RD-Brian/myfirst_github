<?php
class ModelLocalisationZone extends Model {

	public function getZone($zone_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "zone WHERE zone_id = '" . (int)$zone_id . "'");

		return $query->row;
	}

	public function getZonesByCity($city_id) {
		$zone_data = $this->cache->get('zone.city.id.' . (int)$city_id);

		if (!$zone_data) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone WHERE city_id = '" . (int)$city_id . "' AND status = '1' ORDER BY name");
			$zone_data = $query->rows;
			$this->cache->set('zone.city.id.' . (int)$city_id, $zone_data);
		}

		return $zone_data;
	}

	public function getPostcodeByZone($zone_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "postcode WHERE zone_id = '" . (int)$zone_id . "'");
		return $query->row['postcode'];
	}
}