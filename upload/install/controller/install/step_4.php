<?php
class ControllerInstallStep4 extends Controller {
	private $error = array();

	public function index() {
		$this->load->model('install/install');
        
		$data = $this->load->language('install/step_4');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if ($this->request->post['delete_demodata'] == 'yes') {
				$demo_images = DIR_IMAGE . 'catalog/demo';
                
				foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($demo_images), RecursiveIteratorIterator::CHILD_FIRST) as $file) {
					if ($file->getFilename() === '.' || $file->getFilename() === '..') {
						continue;
					}

					$path = $file->getRealPath();

					if (is_writable($path)) {
						if ($file->isDir()){
							rmdir($path);
						} else {
							unlink($path);
						}
					}
				}
				if (is_writable($demo_images)) {
					@rmdir($demo_images);
				}
				
				$this->model_install_install->deleteDemoData();
			}

			$this->model_install_install->enableCountries($this->request->post['country']);

			unset($this->session->data['install']);

			$this->response->redirect($this->url->link('install/step_5'));
		}

		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->error['delete_demodata'])) {
			$data['error_delete_demodata'] = $this->error['delete_demodata'];
		} else {
			$data['error_delete_demodata'] = '';
		}
		
		if (isset($this->error['country'])) {
			$data['error_country'] = $this->error['country'];
		} else {
			$data['error_country'] = '';
		}
		
		$data['countries'] = $this->model_install_install->getCountries();

		if (isset($this->request->post['delete_demodata'])) {
			$data['delete_demodata'] = $this->request->post['delete_demodata'];
		} else {
			$data['delete_demodata'] = 'no';
		}
		
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$data['country'] = !empty($this->request->post['country']) ? $this->request->post['country'] : array();
		} else {
			$data['country'] = array();
			
			foreach ($data['countries'] as $country) {
				if ($country['status']) {
					$data['country'][] = $country['country_id'];
				}
			}
		}

		$data['back'] = $this->url->link('install/step_3');

		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');

		$this->response->setOutput($this->load->view('install/step_4', $data));
	}

	private function validate() {
		if (empty($this->request->post['delete_demodata'])) {
			$this->error['delete_demodata'] = $this->language->get('error_delete_demodata');
		}
		
		if (empty($this->request->post['country'])) {
			$this->error['country'] = $this->language->get('error_country');
		}
		
		return !$this->error;
	}
}
