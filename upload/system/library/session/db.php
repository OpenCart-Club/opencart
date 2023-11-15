<?php
namespace Session;

final class DB {
	private $db;
	private $config;
	
	private $currency;
	private $language;
	public $maxlifetime;

	public function __construct($registry) {
		$this->db = $registry->get('db');
		$this->config = $registry->get('config');
		
		$this->language = $this->config->get('config_language');
		$this->currency = $this->config->get('config_currency');

		$this->maxlifetime = ini_get('session.gc_maxlifetime') !== null ? (int)ini_get('session.gc_maxlifetime') : 1440;

		$this->gc();
	}

	public function read($session_id) {
		$query = $this->db->query("SELECT `data` FROM `" . DB_PREFIX . "session` WHERE `session_id` = '" . $this->db->escape($session_id) . "' AND `expire` > '" . $this->db->escape(date('Y-m-d H:i:s', time())) . "'");

		if ($query->num_rows) {
			$data = json_decode($query->row['data'], true);
			
			if (!empty($data['language'])) $this->language = $data['language'];
			if (!empty($data['currency'])) $this->currency = $data['currency'];
			
			return $data;
		} else {
			return array();
		}
	}

	public function write($session_id, $data) {
		if ($session_id) {
			if (empty($data)) {
				return true;
			}
			
			$session_empty = true;
			
			foreach ($data as $key => $value) {
				if ($key == 'language' && $value == $this->language) {
					continue;
				}
				if ($key == 'currency' && $value == $this->currency) {
					continue;
				}
				$session_empty = false;
				break;
			}
			
			if ($session_empty) {
				return true;
			}
			
			$this->db->query("REPLACE INTO `" . DB_PREFIX . "session` SET `session_id` = '" . $this->db->escape($session_id) . "', `data` = '" . $this->db->escape(json_encode($data)) . "', `expire` = '" . $this->db->escape(date('Y-m-d H:i:s', time() + (int)$this->maxlifetime)) . "'");
		}

		return true;
	}

	public function destroy($session_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "session` WHERE `session_id` = '" . $this->db->escape($session_id) . "'");

		return true;
	}

	public function gc() {
		if (ini_get('session.gc_divisor') && $gc_divisor = (int)ini_get('session.gc_divisor')) {
			$gc_divisor = $gc_divisor === 0 ? 100 : $gc_divisor;
		} else {
			$gc_divisor = 100;
		}

		if (ini_get('session.gc_probability')) {
			$gc_probability = (int)ini_get('session.gc_probability');
		} else {
			$gc_probability = 1;
		}

		if (mt_rand() / mt_getrandmax() < $gc_probability / $gc_divisor) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "session` WHERE `expire` < '" . $this->db->escape(date('Y-m-d H:i:s', time())) . "'");

			return true;
		}
	}
}
