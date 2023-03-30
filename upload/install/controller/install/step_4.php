<?php
class ControllerInstallStep4 extends Controller {
	private $error = array();

	public function index() {
		require_once(DIR_OPENCART . 'config.php');
		
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
				
				$this->load->model('install/install');
				$this->model_install_install->deleteDemoData();
			}

			unset($this->session->data['install']);

			$this->response->redirect($this->url->link('install/step_5'));
		}

		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->error['delete_demodata'])) {
			$data['error_delete_demodata'] = $this->error['delete_demodata'];
		} else {
			$data['error_delete_demodata'] = '';
		}

		if (isset($this->request->post['delete_demodata'])) {
			$data['delete_demodata'] = $this->request->post['delete_demodata'];
		} else {
			$data['delete_demodata'] = 'no';
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
		
		return !$this->error;
	}
}
