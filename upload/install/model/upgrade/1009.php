<?php
class ModelUpgrade1009 extends Model {
	public function upgrade() {
		// Affiliate customer merge code
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "affiliate'");
		
		if ($query->num_rows) {
			$this->db->query("DROP TABLE `" . DB_PREFIX . "affiliate`");
			
			$affiliate_query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "affiliate_activity'");
			
			if (!$affiliate_query->num_rows) {
				$this->db->query("DROP TABLE `" . DB_PREFIX . "affiliate_activity`");
			}
			
			$affiliate_query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "affiliate_login'");
			
			if (!$affiliate_query->num_rows) {			
				$this->db->query("DROP TABLE `" . DB_PREFIX . "affiliate_login`");
			}
			
			$this->db->query("DROP TABLE `" . DB_PREFIX . "affiliate_transaction`");
		}
	
		// Api
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "api' AND COLUMN_NAME = 'name'");

		if ($query->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "api` DROP COLUMN `username`");
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "api` CHANGE COLUMN `name` `username` VARCHAR(64) NOT NULL");
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "api` MODIFY `username` VARCHAR(64) NOT NULL AFTER `api_id`");
		}
		
		// Events
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "event' AND COLUMN_NAME = 'sort_order'");
		
		if (!$query->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "event` ADD `sort_order` INT(3) NOT NULL AFTER `action`");
		}

		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "event' AND COLUMN_NAME = 'date_added'");
		
		if ($query->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "event` DROP COLUMN `date_added`");
		}
		
	
		$files = glob(DIR_OPENCART . '{config.php,admin/config.php}', GLOB_BRACE);

		foreach ($files as $file) {
			$lines = file($file);
	
			for ($i = 0; $i < count($lines); $i++) { 
				if ((strpos($lines[$i], 'DIR_IMAGE') !== false) && (strpos($lines[$i + 1], 'DIR_STORAGE') === false)) {
					array_splice($lines, $i + 1, 0, array('define(\'DIR_STORAGE\', DIR_SYSTEM . \'storage/\');'));
				}

				if ((strpos($lines[$i], 'DIR_MODIFICATION') !== false) && (strpos($lines[$i + 1], 'DIR_SESSION') === false)) {
					array_splice($lines, $i + 1, 0, array('define(\'DIR_SESSION\', DIR_STORAGE . \'session/\');'));
				}

				if (strpos($lines[$i], 'DIR_CACHE') !== false) {
					$lines[$i] = 'define(\'DIR_CACHE\', DIR_STORAGE . \'cache/\');' . "\n";
				}

				if (strpos($lines[$i], 'DIR_DOWNLOAD') !== false) {
					$lines[$i] = 'define(\'DIR_DOWNLOAD\', DIR_STORAGE . \'download/\');' . "\n";
				}

				if (strpos($lines[$i], 'DIR_LOGS') !== false) {
					$lines[$i] = 'define(\'DIR_LOGS\', DIR_STORAGE . \'logs/\');' . "\n";
				}

				if (strpos($lines[$i], 'DIR_MODIFICATION') !== false) {
					$lines[$i] = 'define(\'DIR_MODIFICATION\', DIR_STORAGE . \'modification/\');' . "\n";
				}
				
				if (strpos($lines[$i], 'DIR_SESSION') !== false) {
					$lines[$i] = 'define(\'DIR_SESSION\', DIR_STORAGE . \'session/\');' . "\n";
				}				
	
				if (strpos($lines[$i], 'DIR_UPLOAD') !== false) {
					$lines[$i] = 'define(\'DIR_UPLOAD\', DIR_STORAGE . \'upload/\');' . "\n";
				}
			}
			
			$output = implode('', $lines);
			
			$handle = fopen($file, 'w');

			fwrite($handle, $output);

			fclose($handle);
		}
	}
}
