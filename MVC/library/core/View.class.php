<?php

class View {
	protected $_variables = null;
	protected $_templateDirectory;
	
	public function __construct() {
		$this->_templateDirectory = TEMPLATES;
	}
	
	public function __set($key, $value) {
		$this->_variables[$key] = $value;
	}
	
	public function __get($key) {
		return $this->_variables[$key];
	}
		
	public function assign($key, $value) {
		$this->_variables[$key] = $value;
	}
	
	public function render($template) {
		$template = $this->_templateDirectory.'/'.$template;
		if($this->_variables) {
			extract($this->_variables);
			$this->_variables = null;
		}
		ob_start();
		include $template;
		echo ob_get_clean();
	}

	public function description() {
		echo '<pre>';
		print_r($this->_variables);
	}
	
}