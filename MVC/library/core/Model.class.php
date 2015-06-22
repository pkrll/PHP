<?php

class Model {
	
	protected $_database;
	
	public function __construct() {
		$this->_database = new Database(HOSTNAME, DATABASE, USERNAME, PASSWORD);
	}
	
}