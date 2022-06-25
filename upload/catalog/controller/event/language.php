<?php
class ControllerEventLanguage extends Controller {
	public function index(&$route, &$args) {
		foreach ($this->language->all() as $key => $value) {
			if (!isset($args[$key])) {
				$args[$key] = $value;
			}
		}
	}	
	
	// 1. Before controller load store all current loaded language data
	public function before(&$route, &$output) {
		$this->language->backup();
	}
	
	// 2. After contoller load restore old language data
	public function after(&$route, &$args, &$output) {
		$this->language->restore();
	}
}