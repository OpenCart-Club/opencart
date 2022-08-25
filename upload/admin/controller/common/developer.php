<?php
class ControllerCommonDeveloper extends Controller {
	private $php_recomended = array(
		'max_input_vars' => array(
			'value' => 20000, 
			'prefix' => '>='
		),
		'session.gc_maxlifetime' => array(
			'value' => 604800,
			'prefix' => '>='
		),
		'session.cookie_lifetime' => array(
			'value' => 604800,
			'prefix' => '>='
		),
	);
	
	public function index() {
		$this->load->language('common/developer');

		$data['user_token'] = $this->session->data['user_token'];

		$data['developer_theme'] = $this->config->get('developer_theme');
		$data['developer_sass'] = $this->config->get('developer_sass');
		$data['cache_engine'] = $this->config->get('cache_engine');
		
		$version_part = explode('-', phpversion());
		$data['php_version'] = $version_part[0];
        
		$data['twig_version'] = class_exists('Twig_Environment') ? Twig_Environment::VERSION : false;
        
		$data['params'] = array();
		
		foreach ($this->php_recomended as $key => $recomended) {
			$value = ini_get($key);
			
			if ($value < $recomended['value']) {
				$warning = true;
			} else {
				$warning = false;
			}
			
			$data['params'][] = array(
				'name'      => $key,
				'info'      => sprintf($this->language->get('php_info_' . $key), $recomended['value']),
				'recomended'=> $recomended['value'],
				'value'     => $value,
				'warning'   => $warning
			);
		}
		
		$eval = false;

		$eval = '$eval = true;';

		eval($eval);

		if ($eval === true) {
			$data['eval'] = true;
		} else {
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting('developer', array('developer_theme' => 1), 0);

			$data['eval'] = false;
		}

		$this->response->setOutput($this->load->view('common/developer', $data));
	}

	public function edit() {
		$this->load->language('common/developer');

		$json = array();

		if (!$this->user->hasPermission('modify', 'common/developer')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting('developer', $this->request->post, 0);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function theme() {
		$this->load->language('common/developer');

		$json = array();

		if (!$this->user->hasPermission('modify', 'common/developer')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$directories = glob(DIR_CACHE . '/template/*', GLOB_ONLYDIR);

			if ($directories) {
				foreach ($directories as $directory) {
					$files = glob($directory . '/*');

					foreach ($files as $file) { 
						if (is_file($file)) {
							unlink($file);
						}
					}

					if (is_dir($directory)) {
						rmdir($directory);
					}
				}
			}

			$json['success'] = sprintf($this->language->get('text_cache'), $this->language->get('text_theme'));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function cache() {
		$this->load->language('common/developer');

		$json = array();

		if (!$this->user->hasPermission('modify', 'common/developer')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$this->cache->delete('*');

			$json['success'] = sprintf($this->language->get('text_cache'), $this->language->get('text_cache_engine'));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function sass() {
		$this->load->language('common/developer');

		$json = array();

		if (!$this->user->hasPermission('modify', 'common/developer')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			// Before we delete we need to make sure there is a sass file to regenerate the css
			$file = DIR_APPLICATION  . 'view/stylesheet/bootstrap.css';

			if (is_file($file) && is_file(DIR_APPLICATION . 'view/stylesheet/sass/_bootstrap.scss')) {
				unlink($file);
			}
			 
			$files = glob(DIR_CATALOG  . 'view/theme/*/stylesheet/sass/_bootstrap.scss');
			 
			foreach ($files as $file) {
				$file = substr($file, 0, -21) . '/bootstrap.css';

				if (is_file($file)) {
					unlink($file);
				}
			}

			$json['success'] = sprintf($this->language->get('text_cache'), $this->language->get('text_sass'));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
