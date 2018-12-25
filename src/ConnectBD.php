<?php

namespace Selwer\Migration;

class ConnectBD 
{
	private static $instance = null;
	private $dbConnect;

    private function __construct($dbHost, $dbName, $dbUser, $dbPass) 
    {
		$this->dbConnect = new \PDO(
			'mysql:host=' . $dbHost . ';dbname=' . $dbName,
	    	$dbUser,
	    	$dbPass,
	    	[\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"]
        );
        if (!$this->dbConnect) {
            throw new Exception('Невозможно подключиться к серверу базы данных');
        }
	}

	private function __clone () {}
	private function __wakeup () {}

	public static function getInstance($dbHost, $dbName, $dbUser, $dbPass)
	{
		if (self::$instance != null) {
			return self::$instance;
		}

		return new self($dbHost, $dbName, $dbUser, $dbPass);
    }
    
    public function getConnection()
    {
        return $this->dbConnect;
    }
}