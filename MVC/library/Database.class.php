<?php

class Database {
	
	private $_connection;
	private $_statement;
	
	public function __construct($hostname, $database, $username, $password) {
		try {
			$this->_connection = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
			$this->_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->_connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		} catch (PDOException $error) {
			die("Connection error: " . $error->getMessage());
		}
	}

	public function runQueryAndFetchResults($query) {
		$this->prepare($query);
		$this->execute();
		return $this->fetchAll();
	}

	public function prepare($query) {
		$this->_statement = $this->_connection->prepare($query);
	}
	
	public function execute($args = null) {
		try {
			$this->_statement->execute($args);	
		} catch (PDOException $error) {
			return $error->getMessage();
		}
	}
	
	public function fetchAll($flags = PDO::FETCH_ASSOC) {
		return $this->_statement->fetchAll($flags);
	}
	
	public function fetch($flags = PDO::FETCH_ASSOC) {
		return $this->_statement->fetch($flags);
	}
	
	public function rowCount() {
		return $this->_statement->rowCount();
	}
}