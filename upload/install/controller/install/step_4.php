<?php
class ControllerInstallStep4 extends Controller {
	public function index() {
		$data = $this->load->language('install/step_4');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('install/step_4', $data));
	}
}
