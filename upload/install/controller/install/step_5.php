<?php
class ControllerInstallStep5 extends Controller {
	public function index() {
		$data = $this->load->language('install/step_5');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('install/step_5', $data));
	}
}
