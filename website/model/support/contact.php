<?php
class ModelSupportContact extends Model {
	public function addContact($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "contact SET name = '" . $this->db->escape($data['name']) . "', email = '" . $this->db->escape($data['email']) . "', mobile = '" . $this->db->escape($data['mobile']) . "', description = '" . $this->db->escape($data['description']) . "', user_agent = '" . $this->db->escape($data['user_agent']) . "', accept_language = '" . $this->db->escape($data['accept_language']) . "', ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "', date_added = NOW()");

		$contact_id = $this->db->getLastId();

		return $contact_id;
	}
}