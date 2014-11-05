<?php
class database{
	
	function __construct($db){
		$host="localhost";
		$user="root";
		$password="";
		if($db!="")
			$this->connection = mysqli_connect($host, $user, $password, $db);
		else
			$this->connection = mysqli_connect($host, $user, $password);
	}

	function execute($sql){
		return $this->connection->query($sql);
	}
	
	function getError(){
		return mysqli_error($this->connection);
	}
}

?>
