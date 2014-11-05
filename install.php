<?php
	/* 'import' db.php*/
	require_once("db.php");
	/* create the object*/
	$data = new database("");
	
	$sql = "CREATE DATABASE IF NOT EXISTS usersdb2" ;
	$data->execute($sql);
	
	
	$data = new database("usersdb2");
	$sql = "CREATE TABLE IF NOT EXISTS userslist(
		login VARCHAR(20),
		firstname VARCHAR(40),
		lastname VARCHAR(40),
		password VARCHAR(100),
		studentnumber INT(9),
		stream VARCHAR(100),
		coursedata VARCHAR(100),
		PRIMARY KEY (login)
	)";
	$data->execute($sql);
	
	echo "done";
	
?>