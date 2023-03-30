<?php
class ModelInstallInstall extends Model {
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
		$db = new DB(DB_DRIVER, htmlspecialchars_decode(DB_HOSTNAME), htmlspecialchars_decode(DB_USERNAME), htmlspecialchars_decode(DB_PASSWORD), htmlspecialchars_decode(DB_DATABASE), DB_PORT);
		
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "attribute`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "attribute_description`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "attribute_group`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "attribute_group_description`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "banner`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "banner_image`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "category`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "category_description`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "category_filter`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "category_path`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "category_to_store`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "filter`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "filter_description`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "filter_group`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "filter_group_description`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "manufacturer`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "manufacturer_description`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "manufacturer_to_store`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "option`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "option_description`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "option_value`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "option_value_description`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "product`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_attribute`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_description`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_discount`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_filter`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_image`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_option`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_option_value`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_related`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_reward`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_special`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_to_category`");
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_to_store`");
		
		$db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `code` = 'module_filter'");

		$db->query("DELETE FROM `" . DB_PREFIX . "extension` WHERE `type` = 'module' AND `code` = 'banner'");
		$db->query("DELETE FROM `" . DB_PREFIX . "extension` WHERE `type` = 'module' AND `code` = 'carousel'");
		$db->query("DELETE FROM `" . DB_PREFIX . "extension` WHERE `type` = 'module' AND `code` = 'featured'");
		$db->query("DELETE FROM `" . DB_PREFIX . "extension` WHERE `type` = 'module' AND `code` = 'slideshow'");
		$db->query("DELETE FROM `" . DB_PREFIX . "extension` WHERE `type` = 'module' AND `code` = 'filter'");
		
		$db->query("TRUNCATE TABLE `" . DB_PREFIX . "module`");
		
		$db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `seo_url_id` > '100'");
		$db->query("DELETE FROM `" . DB_PREFIX . "layout_module` WHERE `layout_module_id` > '10'");
	}
}
