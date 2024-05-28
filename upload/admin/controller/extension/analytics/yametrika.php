<?php
 /*******
 * @brief           Yandex Metrika counter: support
 * @author      	<a href='https://opencart.com/'>OpenCart</a> & <a href='https://opencart.club/'>OpenCart Club</a>
 * @copyright   	(c) 2023 Powered By OpenCart & OpenCart Club
 * @license         https://github.com/opencart/opencart/blob/master/LICENSE.md
 * @package         OpenCart Club Edition
 * @subpackage      Yandex Metrika
 * @since           30 Nov 2023
 *******/
class ControllerExtensionAnalyticsYametrika extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/analytics/yametrika');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('analytics_yametrika', $this->request->post, $this->request->get['store_id']);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=analytics', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['code'])) {
			$data['error_code'] = $this->error['code'];
		} else {
			$data['error_code'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=analytics', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/analytics/yametrika', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id'], true)
		);

		$data['action'] = $this->url->link('extension/analytics/yametrika', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=analytics', true);
		
		$data['user_token'] = $this->session->data['user_token'];
				
		if (isset($this->request->post['analytics_yametrika_code'])) {
			$data['analytics_yametrika_code'] = $this->request->post['analytics_yametrika_code'];
		} else {
			$data['analytics_yametrika_code'] = $this->model_setting_setting->getSettingValue('analytics_yametrika_code', $this->request->get['store_id']);
		}
		
		if (isset($this->request->post['analytics_yametrika_status'])) {
			$data['analytics_yametrika_status'] = $this->request->post['analytics_yametrika_status'];
		} else {
			$data['analytics_yametrika_status'] = $this->model_setting_setting->getSettingValue('analytics_yametrika_status', $this->request->get['store_id']);
		}
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/analytics/yametrika', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/analytics/yametrika')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['analytics_yametrika_code']) {
			$this->error['code'] = $this->language->get('error_code');
		}			

		return !$this->error;
	}
}
