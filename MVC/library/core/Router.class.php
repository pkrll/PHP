<?php

class Router {
	
	private $_controller = DEFAULT_CONTROLLER;
	private $_method = DEFAULT_METHOD;
	
	function __construct($URIRequest) {

		if (!is_null($URIRequest)) {
			$requestAsArray = explode("/", $URIRequest);
			foreach ($requestAsArray as $key => $value) {
				switch ($key) {
					case 0: $this->_controller = ucfirst($value); break;
					case 1: $this->_method = $value; break;
				}
			}
		}
	}
	
	public function getPathToController() {
		return CONTROLLERS.'/'.$this->_controller.'Controller.php';
	}

	public function methodOfController() {
		return $this->_method;
	}
	
	public function setControllerToDefault() {
		$this->_controller = DEFAULT_CONTROLLER;
	}
}