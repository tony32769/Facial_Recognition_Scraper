<?php
include 'debug.php';
class DB {

	private $db = false;
	private $user = 'root';
	private $pass = '';
	private $host = 'localhost';
	private $port = 3306;
	private $dbname = 'sscrape';
	public $error = '';


	public function __construct() {
		return $this->getDB();
	}


	public function getDB() {
		if ( $this->db === false ) {

			$db = new PDO(
				'mysql:host=' . $this->host.
					';port=' . $this->port .
					';dbname=' . $this->dbname,
				$this->user,
				$this->pass);

			$this->db = $db;

			return $this->db;

		} else {
			try {
				$this->db->query( 'select 1' );
				return $this->db;
			} catch (PDOException $e){
				dbgToFile( "There was an issue with the DB connection: {$e->getMessage()}" );
				$this->error = $e->getMessage();
				return false;
			}
		}
	}
}
