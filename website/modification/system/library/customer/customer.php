<?php
namespace Customer;
class Customer {
	private $customer_id;
	private $name;
	private $customer_group_id;
	private $mobile;
	private $email;
	private $telephone;
	private $newsletter;
	private $address_id;
	private $birthday;

	public function __construct($registry) {
		$this->config = $registry->get('config');
		$this->db = $registry->get('db');
		$this->request = $registry->get('request');
		$this->session = $registry->get('session');

		if (isset($this->session->data['customer_id'])) {
			$customer_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$this->session->data['customer_id'] . "' AND status = '1'");

			if ($customer_query->num_rows) {
				$this->customer_id = $customer_query->row['customer_id'];
				$this->name = $customer_query->row['name'];
				$this->customer_group_id = $customer_query->row['customer_group_id'];
				$this->email = $customer_query->row['email'];
				$this->mobile = $customer_query->row['mobile'];
				$this->newsletter = $customer_query->row['newsletter'];
				$this->address_id = $customer_query->row['address_id'];
				$this->birthday = isset($customer_query->row['birthday']) ? $customer_query->row['birthday'] : '';

				$this->db->query("UPDATE " . DB_PREFIX . "customer SET language_id = '" . (int)$this->config->get('config_language_id') . "', ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "' WHERE customer_id = '" . (int)$this->customer_id . "'");

				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_ip WHERE customer_id = '" . (int)$this->session->data['customer_id'] . "' AND ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "'");

				if (!$query->num_rows) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "customer_ip SET customer_id = '" . (int)$this->session->data['customer_id'] . "', ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "', date_added = NOW()");
				}
			} else {
				$this->logout();
			}
		}
	}

  public function login($email, $password, $override = false) {
		// $approval = $this->approval($email, $password);
		// if($approval){
			// print_r($approval);
			if ($override) {
				$customer_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "' AND status = '1'");
			} else {
				$customer_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "' AND (password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1('" . $this->db->escape($password) . "'))))) OR password = '" . $this->db->escape(md5($password)) . "') AND status = '1'");
			}

			if ($customer_query->num_rows) {
				$this->session->data['customer_id'] = $customer_query->row['customer_id'];

				$this->customer_id = $customer_query->row['customer_id'];
				$this->name = $customer_query->row['name'];
				$this->customer_group_id = $customer_query->row['customer_group_id'];
				$this->email = $customer_query->row['email'];
				$this->mobile = $customer_query->row['mobile'];
				$this->newsletter = $customer_query->row['newsletter'];
				$this->address_id = $customer_query->row['address_id'];
			
				$this->db->query("UPDATE " . DB_PREFIX . "customer SET language_id = '" . (int)$this->config->get('config_language_id') . "', ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "' WHERE customer_id = '" . (int)$this->customer_id . "'");

				return true;
			} else {
				return false;
			}
		// }
		// else{
		// 	return false;
		// }
	}

	public function approval($email, $password)
	{
		//先判定帳號是否審核中
  	$approval = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "' AND (password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1('" . $this->db->escape($password) . "'))))) OR password = '" . $this->db->escape(md5($password)) . "')");

  	$approval = $approval->row;
  	if($approval){
  		$customer_id = $approval['customer_id'];
  		$approval_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_approval` WHERE customer_id = '" . $customer_id . "'");
  		$approval_query = $approval_query->row;
  		
  		if($approval_query){
  			return true;
  		}
  	}

	}

	public function logout() {
		unset($this->session->data['customer_id']);

		$this->customer_id = '';
		$this->name = '';
		$this->customer_group_id = '';
		$this->email = '';
		$this->telephone = '';
		$this->newsletter = '';
		$this->address_id = '';
	}

	public function isLogged() {
		return $this->customer_id;
	}

	public function getId() {
		return $this->customer_id;
	}

	public function getName() {
		return $this->name;
	}

	public function getGroupId() {
		return $this->customer_group_id;
	}

	public function getEmail() {
		return $this->email;
	}

	public function getMobile() {
		return $this->mobile;
	}

	public function getNewsletter() {
		return $this->newsletter;
	}

	public function getAddressId() {
		return $this->address_id;
	}

	public function getBirthday() {
		return $this->birthday;
	}

	/**
	 * 獲取驗證項目
	 * @return [array] [返回規定項目的陣列]
	 */
	public function getVerify() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_verify WHERE customer_id = '" . (int)$this->customer_id . "'");

		return $query->row;
	}

	public function getBalance() {
		$query = $this->db->query("SELECT SUM(amount) AS total FROM " . DB_PREFIX . "customer_transaction WHERE customer_id = '" . (int)$this->customer_id . "'");

		return $query->row['total'];
	}

	public function getRewardPoints() {
		$query = $this->db->query("SELECT SUM(points) AS total FROM " . DB_PREFIX . "customer_reward WHERE customer_id = '" . (int)$this->customer_id . "'");

		return $query->row['total'];
	}
}
