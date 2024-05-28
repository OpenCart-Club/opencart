<?php
 /*******
 * @brief           Yandex.Metrika counter: support
 * @author      	<a href='https://opencart.com/'>OpenCart</a> & <a href='https://opencart.club/'>OpenCart Club</a>
 * @copyright   	(c) 2023 Powered By OpenCart & OpenCart Club
 * @license         https://github.com/opencart/opencart/blob/master/LICENSE.md
 * @package         OpenCart Club Edition
 * @subpackage      Yandex.Metrika
 * @since           30 Nov 2023
 *******/
class ControllerExtensionAnalyticsYametrika extends Controller {
    public function index() {
		return html_entity_decode($this->config->get('analytics_yametrika_code'), ENT_QUOTES, 'UTF-8');
	}
}
