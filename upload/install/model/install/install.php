<?php
class ModelInstallInstall extends Model {
	private $db;
	
	public function __construct($registry) {
		parent::__construct($registry);
		
		if (defined('DB_DRIVER') && defined('DB_HOSTNAME') && defined('DB_USERNAME') && defined('DB_PASSWORD') && defined('DB_DATABASE') && defined('DB_PORT')) {
			$this->db = new DB(DB_DRIVER, htmlspecialchars_decode(DB_HOSTNAME), htmlspecialchars_decode(DB_USERNAME), htmlspecialchars_decode(DB_PASSWORD), htmlspecialchars_decode(DB_DATABASE), DB_PORT);
		}
	}
	
	public function database($data) {
		$db = new DB($data['db_driver'], htmlspecialchars_decode($data['db_hostname']), htmlspecialchars_decode($data['db_username']), htmlspecialchars_decode($data['db_password']), htmlspecialchars_decode($data['db_database']), $data['db_port']);

		$file = DIR_APPLICATION . 'opencart.sql';

		if (!file_exists($file)) {
			exit('Could not load sql file: ' . $file);
		}

		$lines = file($file);

		if ($lines) {
			$sql = '';

			foreach($lines as $line) {
				if ($line && (substr($line, 0, 2) != '--') && (substr($line, 0, 1) != '#')) {
					$sql .= $line;

					if (preg_match('/;\s*$/', $line)) {
						$sql = str_replace("DROP TABLE IF EXISTS `oc_", "DROP TABLE IF EXISTS `" . $data['db_prefix'], $sql);
						$sql = str_replace("CREATE TABLE `oc_", "CREATE TABLE `" . $data['db_prefix'], $sql);
						$sql = str_replace("INSERT INTO `oc_", "INSERT INTO `" . $data['db_prefix'], $sql);

						$db->query($sql);

						$sql = '';
					}
				}
			}

			$db->query("SET CHARACTER SET utf8");

			$db->query("DELETE FROM `" . $data['db_prefix'] . "user` WHERE user_id = '1'");

			$db->query("INSERT INTO `" . $data['db_prefix'] . "user` SET user_id = '1', user_group_id = '1', username = '" . $db->escape($data['username']) . "', salt = '" . $db->escape($salt = token(9)) . "', password = '" . $db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "', firstname = 'John', lastname = 'Doe', email = '" . $db->escape($data['email']) . "', status = '1', date_added = NOW()");

			$db->query("DELETE FROM `" . $data['db_prefix'] . "setting` WHERE `key` = 'config_email'");
			$db->query("INSERT INTO `" . $data['db_prefix'] . "setting` SET `code` = 'config', `key` = 'config_email', value = '" . $db->escape($data['email']) . "'");

			$db->query("DELETE FROM `" . $data['db_prefix'] . "setting` WHERE `key` = 'config_encryption'");
			$db->query("INSERT INTO `" . $data['db_prefix'] . "setting` SET `code` = 'config', `key` = 'config_encryption', value = '" . $db->escape(token(1024)) . "'");

			$db->query("UPDATE `" . $data['db_prefix'] . "product` SET `viewed` = '0'");

			$db->query("INSERT INTO `" . $data['db_prefix'] . "api` SET username = 'Default', `key` = '" . $db->escape(token(256)) . "', status = 1, date_added = NOW(), date_modified = NOW()");

			$api_id = $db->getLastId();

			$db->query("DELETE FROM `" . $data['db_prefix'] . "setting` WHERE `key` = 'config_api_id'");
			$db->query("INSERT INTO `" . $data['db_prefix'] . "setting` SET `code` = 'config', `key` = 'config_api_id', value = '" . (int)$api_id . "'");
			
			// set the current years prefix
			$db->query("UPDATE `" . $data['db_prefix'] . "setting` SET `value` = 'INV-" . date('Y') . "-00' WHERE `key` = 'config_invoice_prefix'");
		}
	}
	
	public function deleteDemoData() {
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "attribute`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "attribute_description`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "attribute_group`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "attribute_group_description`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "banner`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "banner_image`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "category`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "category_description`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "category_filter`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "category_path`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "category_to_store`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "filter`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "filter_description`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "filter_group`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "filter_group_description`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "manufacturer`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "manufacturer_description`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "manufacturer_to_store`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "option`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "option_description`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "option_value`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "option_value_description`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_attribute`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_description`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_discount`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_filter`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_image`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_option`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_option_value`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_related`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_reward`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_special`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_to_category`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_to_store`");
		
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `code` = 'module_filter'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "extension` WHERE `type` = 'module' AND `code` = 'banner'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "extension` WHERE `type` = 'module' AND `code` = 'carousel'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "extension` WHERE `type` = 'module' AND `code` = 'featured'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "extension` WHERE `type` = 'module' AND `code` = 'slideshow'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "extension` WHERE `type` = 'module' AND `code` = 'filter'");
		
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "module`");
		
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `seo_url_id` > '100'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "layout_module` WHERE `layout_module_id` > '10'");
	}
	
	public function getCountries() {
		$query = $this->db->query("SELECT country_id, name, status FROM " . DB_PREFIX . "country ORDER BY status = 1 DESC, LCASE(name)");

		return $query->rows;
	}
	
	public function enableCountries($countries) {
		$this->db->query("UPDATE " . DB_PREFIX . "country SET status = '0'");
		
		$countries_filtered = array_map('intval', $countries);
		
		$this->db->query("UPDATE " . DB_PREFIX . "country SET status = '1' WHERE country_id IN (" . implode(',', $countries_filtered) . ")");
		
		if (in_array(176, $countries_filtered)) {
			$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `value` = '176' WHERE `key` = 'config_country_id'");
		} else {
			$country_id = array_shift($countries_filtered);
			$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `value` = '" . (int)$country_id . "' WHERE `key` = 'config_country_id'");
		}
	}
}
