<?php
class ModelLocalisationPostcode extends Model {

	public function getFullPostcode() {
		$sql = "SELECT p.postcode,z.name AS zone,z.zone_id,c.name AS city,c.city_id FROM " . DB_PREFIX . "postcode p LEFT JOIN " . DB_PREFIX . "zone z ON (p.zone_id = z.zone_id) LEFT JOIN " . DB_PREFIX . "city c ON (c.city_id = z.city_id)";

		$query = $this->db->query($sql);

		return $query->rows;
	}
}