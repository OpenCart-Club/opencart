<?php
class ModelExtensionFeedGoogleSitemap extends Model {
    /**
     * @return Generator
     */
    public function getProducts() {
        $query = $this->db->queryFetchable("SELECT p.product_id, p.date_modified, p.image, pd.name FROM " . DB_PREFIX . "product p 
            LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
            LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)
            WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p.no_index = 0
            ORDER BY p.product_id");

        while ( $result = $query->fetch() ) {
            yield $result;
        }
    }

    /**
     * @return Generator
     */
    public function getManufacturers() {
        $query = $this->db->queryFetchable("SELECT m.manufacturer_id FROM " . DB_PREFIX . "manufacturer m 
            LEFT JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id)
            WHERE m2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND m.no_index = 0
            ORDER BY m.manufacturer_id");

        while ( $result = $query->fetch() ) {
            yield $result;
        }
    }

    /**
     * @return Generator
     */
    public function getInformations() {
        $query = $this->db->queryFetchable("SELECT i.information_id FROM " . DB_PREFIX . "information i 
            LEFT JOIN " . DB_PREFIX . "information_to_store i2s ON (i.information_id = i2s.information_id) 
            WHERE i2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1' AND i.no_index = 0
            ORDER BY i.information_id");

        while ( $result = $query->fetch() ) {
            yield $result;
        }
    }

    /**
     * @return Generator
     */
    public function getCategories() {
        $query = $this->db->queryFetchable("SELECT c.category_id FROM " . DB_PREFIX . "category c
            LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id)
            WHERE c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1' AND c.no_index = 0
            ORDER BY c.category_id");

        while ( $result = $query->fetch() ) {
            yield $result;
        }
    }
}
