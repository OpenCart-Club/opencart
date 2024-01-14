<?php
class ControllerStartupSeoUrl extends Controller {
	// Окнчание для SeoUrl
	private $postfix = 'html';
	private $postfix_route = ['product/product', 'information/information'];
	private $enable_postfix = false;
	private $enable_slash = false;
	private $mode = 0;

	/* Префикс ЧПУ для определённых роутов.

	Пример - Приставка /brand/ для всех производителей :
	private $prefix_by_route = [
		'product/manufacturer/info' => 'brand',
	];

	Пример - Приставка /category/ для категорий и /product/ для товаров :
	private $prefix_by_route = [
		'product/category'          => 'category',
		'product/product'           => 'product',
	];
	*/
	private $prefix_by_route = [];

	public function __construct($registry) {
		parent::__construct($registry);
		
		$this->enable_postfix = $this->config->get('config_seo_url_postfix');
		$this->enable_slash = $this->config->get('config_seo_url_slash');
		$this->mode = (int)$this->config->get('config_seo_url_mode');
	}

	public function index() {
		// Add rewrite to url class
		if ($this->config->get('config_seo_url')) {
			$this->url->addRewrite($this);
		}

		// Decode URL
		if (isset($this->request->get['_route_'])) {
			// Преобразуем url к нижнему регистру
			$this->request->get['_route_'] = utf8_strtolower($this->request->get['_route_']);
		  
			$parts = explode('/', $this->request->get['_route_']);

			$parts_filtered = array();
			foreach ($parts as $part) {
				$part = trim($part);
				if ($part) {
					$parts_filtered[] = $part;
				}
			}
			$parts = $parts_filtered;

			if ( count($parts) > 1 && in_array($parts[0], $this->prefix_by_route) ) {
				array_shift($parts);
			}
			
			// Убираем окончание после точки, если оно есть
			if ($this->postfix && count($parts) > 0) {
				$last = array_pop($parts);
				$point_parts = explode('.', $last);
				if (count($point_parts) > 1 && end($point_parts) == $this->postfix) {
					array_pop($point_parts);
					$last = implode('.', $point_parts); 
				}
				$parts[] = $last;
			}

			foreach ($parts as $part) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE keyword = '" . $this->db->escape($part) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

				if ($query->num_rows) {
					$url = explode('=', $query->row['query']);

					if ($url[0] == 'product_id') {
						$this->request->get['product_id'] = $url[1];
					}

					if ($url[0] == 'category_id') {
						if ($this->mode == 4) {
							$this->request->get['path'] = $this->getPathByCategory($url[1]);
						} else {
							if (!isset($this->request->get['path'])) {
								$this->request->get['path'] = $url[1];
							} else {
								$this->request->get['path'] .= '_' . $url[1];
							}
						}
					}

					if ($url[0] == 'manufacturer_id') {
						$this->request->get['manufacturer_id'] = $url[1];
					}

					if ($url[0] == 'information_id') {
						$this->request->get['information_id'] = $url[1];
					}

					if ($query->row['query'] && $url[0] != 'information_id' && $url[0] != 'manufacturer_id' && $url[0] != 'category_id' && $url[0] != 'product_id') {
						$this->request->get['route'] = $query->row['query'];
					}
				} else {
					$this->request->get['route'] = 'error/not_found';

					break;
				}
			}

			if (!isset($this->request->get['route'])) {
				if (isset($this->request->get['product_id'])) {
					$this->request->get['route'] = 'product/product';
				} elseif (isset($this->request->get['path'])) {
					$this->request->get['route'] = 'product/category';
				} elseif (isset($this->request->get['manufacturer_id'])) {
					$this->request->get['route'] = 'product/manufacturer/info';
				} elseif (isset($this->request->get['information_id'])) {
					$this->request->get['route'] = 'information/information';
				}
			}
			
			unset($this->request->get['_route_']);
		}

		if (empty($this->request->get['route'])) {
			$this->request->get['route'] = 'common/home';
		}
		
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			return;
		}
		
		if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && utf8_strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			return;
		}
		
		if ($this->config->get('config_secure')) {
			$original_url = $this->config->get('config_ssl');
		} else {
			$original_url = $this->config->get('config_url');
		}
		
		$original_request = $this->request->server['REQUEST_URI'];
		
		// Корректируем базовый запрос, если сайт находится в отдельной папке
		$path = parse_url($original_url, PHP_URL_PATH);
		if ($path && strpos($original_request, $path) === 0) {
			$original_request = utf8_substr($original_request, utf8_strlen($path));
		}
		
		$original_url .= ltrim($original_request, '/');
	  
		$params = array();
		foreach ($this->request->get as $key => $value) {
			if (in_array($key, ['route'])) {
				continue;
			}
			
			if (is_array($value)) {
				$params[$key] = $value;
			} else {
				$params[$key] = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
			}
		}
		
		
		if ($this->request->get['route'] != 'error/not_found') {
			$seo_url = $this->url->link($this->request->get['route'], http_build_query($params), $this->config->get('config_secure'));

			if (rawurldecode($original_url) != rawurldecode($seo_url)) {
				$this->response->redirect($seo_url, 301);
			}
		}
	}

	public function rewrite($link) {
		$url_info = parse_url(str_replace('&amp;', '&', $link));

		$url = null;
		$has_postfix = false;
		$route = null;

		$data = array();

		parse_str($url_info['query'], $data);

		if (isset($data['route'])) {
			$route = $data['route'];

			$keyword = $this->getKeyword($data['route']);

			if ($keyword !== false) {
				$url = '/' . $keyword;
				unset($data['route']);
			}

			if ( !empty($this->prefix_by_route[$route]) ) {
				$url = '/' . $this->prefix_by_route[$route] . $url;
			}
		}
		
		if (!empty($route) && $route == 'product/product' && !empty($data['product_id'])) {
			if ($this->mode == 0) {
				if (isset($data['path'])) {
					$path = $data['path'];
					unset($data['path']);
					$data = array_merge(['path' => $path], $data);
				}
			} else {
				unset($data['path']);
				unset($data['manufacturer_id']);
			}
			
			if ($this->mode == 2) {
				$query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$data['product_id'] . "' AND main_category = '1'");
				if ($query->row) {
					$data = array_merge(['path' => $query->row['category_id']], $data);
				}
			}
			
			if ($this->mode == 3) {
				$query = $this->db->query("SELECT manufacturer_id FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$data['product_id'] . "'");
				if ($query->row && $query->row['manufacturer_id'] > 0) {
					$data = array_merge(['manufacturer_id' => $query->row['manufacturer_id']], $data);
				}
			}
		}
		
		foreach ($data as $key => $value) {
			if (isset($data['route'])) {
				if (($data['route'] == 'product/product' && $key == 'product_id') || (($data['route'] == 'product/manufacturer/info' || $data['route'] == 'product/product') && $key == 'manufacturer_id') || ($data['route'] == 'information/information' && $key == 'information_id')) {
					$keyword = $this->getKeyword($key . '=' . (int)$value);

					if ($keyword) {
						$url .= '/' . $keyword;
						
						unset($data[$key]);
					}
				} elseif ($key == 'path') {
					$categories = explode('_', $value);
					
					$category_id = array_pop($categories);
					
					if ($this->mode == 4) {
						$keyword = $this->getKeyword('category_id=' . $category_id);
						
						if ($keyword) {
							$url .= '/' . $keyword;
						}
					} else {
						foreach ($this->getKeywordsByCategory($category_id) as $keyword) {
							if ($keyword) {
								$url .= '/' . $keyword;
							} else {
								$url = null;
								break;
							}
						}
					}

					unset($data[$key]);
				}
			}
		}

		if (isset($url)) {
			if (!empty($route) && in_array($route, $this->postfix_route)) {
				$has_postfix = true;
				if ($this->enable_postfix && $this->postfix) {
					$url .= '.' . $this->postfix;
				}
			}

			if ($this->enable_slash && !$has_postfix && $url != '/') {
				$url .= '/';
			}

			unset($data['route']);
			
			if (isset($data['page']) && $data['page'] != '{page}' && $data['page'] <= 1) {
				unset($data['page']);
			}

			$query = '';

			if ( !empty($data) ) {
				/*
				foreach ($data as $key => $value) {
					$query .= '&' . rawurlencode((string)$key) . '=' . rawurlencode((is_array($value) ? http_build_query($value) : (string)$value));
				}
				*/
				
				$raw_query = str_replace('+', '%20', http_build_query($data));
				$parts = explode('&', $raw_query);
				foreach ($parts as $part) {
					$values = explode('=', $part);
					$key = str_replace(array('%5B', '%5D'), array('[', ']'), $values[0]);
					$value = isset($values[1]) ? $values[1] : '';
					$query .= '&' . $key . '=' . $value;
				}

				if ($query) {
					$query = '?' . str_replace('&', '&amp;', trim($query, '&'));
				}
			}

			return $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . str_replace('/index.php', '', $url_info['path']) . $url . $query;
		} else {
			return $link;
		}
	}

	private $category_keywords = [];
	private $category_path = [];
	private $keyword = [];
	
	private function getKeywordsByCategory($category_id) {
		if (!isset($this->category_keywords[$category_id])) {
			$query = $this->db->query("SELECT su.keyword FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "seo_url su ON (su.query = CONCAT('category_id=', cp.path_id)) WHERE cp.category_id = '" . (int)$category_id . "' AND su.store_id = '" . (int)$this->config->get('config_store_id') . "' AND su.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY cp.level");
			
			$this->category_keywords[$category_id] = array();
			
			foreach ($query->rows as $row) {
				$this->category_keywords[$category_id][] = $row['keyword'];
			}
		}
		
		return $this->category_keywords[$category_id];
	}
	
	private function getPathByCategory($category_id) {
		if (!isset($this->category_path[$category_id])) {
			$query = $this->db->query("SELECT GROUP_CONCAT(path_id ORDER BY level SEPARATOR '_') AS path FROM " . DB_PREFIX . "category_path WHERE category_id = '" . (int)$category_id . "'");
			
			$this->category_path[$category_id] = $query->row['path'] ?? $category_id;
		}
		
		return $this->category_path[$category_id];
	}
	
	private function getKeyword($query_string) {
		if (!isset($this->keyword[$query_string])) {
			$query = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "seo_url WHERE `query` = '" . $this->db->escape($query_string) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
			
			$this->keyword[$query_string] = $query->num_rows ? $query->row['keyword'] : false;
		}
		
		return $this->keyword[$query_string];
	}
}
