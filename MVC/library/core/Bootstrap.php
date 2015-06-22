<?php
require_once("Config.php");

class Bootstrap {
	private $_controller = DEFAULT_CONTROLLER;
	
	public function __construct() {
		$URIRequest = substr($_SERVER['REQUEST_URI'], 1);
		$this->_parseURI($URIRequest);
		$this->_loadController();
	}
	
	private function _loadController() {
		$pathToController = $this->_getPathOfControllerAsArray();
		if (!class_exists($pathToController['className']))
			include $pathToController['fullPath'];

		$instanceOfController = new $pathToController['className']($this->_method, $this->_arguments);
	}
	
	private function _parseURI($URIRequest) {
		if (!is_null($URIRequest)) {
			$URIAsArray = explode("/", $URIRequest);
			foreach ($URIAsArray as $key => $value) {
				if (is_null($value)) // Check for value, ie /place/holder
					continue;
				switch ($key) {
					case 0: $this->_controller = ucfirst($value); break;
					case 1: $this->_method = $value; break;
					default: $this->_arguments[] = $value;
				}
			}

		}
	}
	
	private function _getPathOfControllerAsArray() {
		$file = $this->_controller.'Controller';
		$path = CONTROLLERS.'/'.$file.'.php';
		
		// Check if the controller exists
		if (!file_exists($path)) {
			$path = CONTROLLERS.'/'.DEFAULT_CONTROLLER.'Controller.php';
			$file = DEFAULT_CONTROLLER.'Controller';
		}

		$pathAsArray = array(
			"fullPath" => $path,
			"className" => $file
		);

		return $pathAsArray;
	}
}
