<?php

class Controller {
	
	private $_name;
	private $_view;
	private $_model;
	
	public function __construct($method, $arguments = null) {
		$this->_name = str_replace('Controller', '', get_class($this));
		$this->_loadModel();
		$this->_loadView();

		if (!is_null($arguments))
			$this->_arguments = $arguments;

		if (!method_exists($this, $method))
			$method = DEFAULT_METHOD;
		$this->$method();
	}

	private function _loadModel() {		
		$class = $this->_name.'Model';
		$path = MODELS.'/'.$class.'.php';
		
		// Check if the model exists
		if (!file_exists($path)) {
			$this->_model = new Model();
		} else {
			if (!class_exists($class))
				include $path;
			
			$this->_model = new $class();
		}
	}

	private function _loadView() {
		$class = $this->_name.'View';
		$path = VIEWS.'/'.$class.'.php';
		
		if (!file_exists($path)) {
			$this->_view = new View();	
		} else {
			if (!class_exists($class))
				include $path;
			
			$this->_view = new $class();
		}
	}
	
	protected function name() {
		return $this->_name;
	}
	
	protected function view() {
		return $this->_view;
	}
	
	protected function model() {
		return $this->_model;
	}
}