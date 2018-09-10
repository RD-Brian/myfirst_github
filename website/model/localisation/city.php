<?php
class ModelLocalisationCity extends Model {

	public function getCities() {
		$city_data = $this->cache->get('city.all');
		if (!$city_data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "city ORDER BY city_id ASC";
			$query = $this->db->query($sql);
			$city_data = $query->rows;
			$this->cache->set('city.all', $city_data);
		}
		return $city_data;
	}

	public function getCity($city_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "city WHERE city_id = '" . (int)$city_id . "'");

		return $query->row;
	}

	public function getCityByCountry($country_id = 1) {
		$city_data = $this->cache->get('city.country.id.'.$country_id);
		if (!$city_data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "city WHERE country_id = '".(int)$country_id."' ORDER BY city_id ASC";
			$query = $this->db->query($sql);
			$city_data = $query->rows;
			$this->cache->set('city.country.id.'.$country_id, $city_data);
		}
		return $city_data;
	}

	public function getTotalCountries() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "country");

		return $query->row['total'];
	}
}