<?php
class ControllerExtensionCaptchaYandex extends Controller {
    public function index($error = array()) {
		if (!empty($this->session->data['ycapcha'])) {
			return '';
		}

		$this->load->language('extension/captcha/yandex');

		if (isset($error['captcha'])) {
			$data['error_captcha'] = $error['captcha'];
		} else {
			$data['error_captcha'] = '';
		}

		$data['site_key'] = $this->config->get('captcha_yandex_key');

		$data['route'] = isset($this->request->get['route']) ? $this->request->get['route'] : 'common/home'; 

		return $this->load->view('extension/captcha/yandex', $data);
    }

    public function validate() {
		if (empty($this->session->data['ycapcha'])) {
			$this->load->language('extension/captcha/yandex');

			if (empty($this->request->post['smart-token'])) {
				return $this->language->get('error_captcha');
			}

			$args = http_build_query([
				"secret"  => $this->config->get('captcha_yandex_secret'),
				"token"   => $this->request->post['smart-token'],
				"ip"      => $this->request->server['REMOTE_ADDR'] ?? ''
			]);
			
			$ch = curl_init("https://captcha-api.yandex.ru/validate?$args");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$result = curl_exec($ch);
			curl_close($ch);

			$result = json_decode($result, true);

			if (isset($result['status']) && $result['status'] === 'ok') {
				$this->session->data['ycapcha'] = true;
			} else {
				return $this->language->get('error_captcha');
			}
		}
    }
}
