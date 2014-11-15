<?php
	/* 'import' db.php*/
	require_once("db.php");
	/* create the object*/
	$data = new database("");
	
	$sql = "CREATE DATABASE IF NOT EXISTS usersdb2" ;
	$data->execute($sql);
	
	
	// Create database to hold user profiles
	$data = new database("usersdb2");
	$sql = "CREATE TABLE IF NOT EXISTS `userslist`(
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
	
	// Create database to hold data for fall classes
	$data->execute("DROP TABLE IF EXISTS `falldata`");
	$sqld = "CREATE TABLE IF NOT EXISTS `falldata`(
		id INT NOT NULL AUTO_INCREMENT,
		subj VARCHAR(4),
		crse INT(4),
		seq VARCHAR(5),
		title VARCHAR(100),
		type VARCHAR(3),
		day VARCHAR(2),
		starttime INT(4),
		endtime INT(4),
		roomcap INT(4),
		enrolled INT(4),
		PRIMARY KEY (id)
	)";
	$data->execute($sqld);
	
	// Create database to hold data for winter classes
	$data->execute("DROP TABLE IF EXISTS `winterdata`");
	$sqld = "CREATE TABLE IF NOT EXISTS `winterdata`(
		id INT NOT NULL AUTO_INCREMENT,
		subj VARCHAR(4),
		crse INT(4),
		seq VARCHAR(5),
		title VARCHAR(100),
		type VARCHAR(3),
		day VARCHAR(2),
		starttime INT(4),
		endtime INT(4),
		roomcap INT(4),
		enrolled INT(4),
		PRIMARY KEY (id)
	)";
	$data->execute($sqld);
	
	set_time_limit(800);
	// Populate fall classes database with csv file 'datafall.csv' to be used when scheduling the classes.
	$row = 0;
	if (($handle = fopen(".\csv\datafall.csv", "r")) !== FALSE) {
		while (($datarow = fgetcsv($handle, 1000, ";")) !== FALSE) {
			$num = count($datarow);
			$row++;
			if($row > 1){	
				$sql = "INSERT INTO `falldata` VALUES (default, '$datarow[0]','$datarow[1]','$datarow[2]','$datarow[3]','$datarow[4]','$datarow[5]','$datarow[6]','$datarow[7]','$datarow[8]','$datarow[8]')";
				
				$rowcheck = $data->execute($sql);
				if(!$rowcheck){
					echo "Winter error: " . $data->getError(). "<br \>";
				}
			}
		}
		fclose($handle);
	}

	// Populate fall classes database with csv file 'datawinter.csv' to be used when scheduling the classes.
	$row = 0;
	if (($handle = fopen(".\csv\datawinter.csv", "r")) !== FALSE) {
		while (($datarow = fgetcsv($handle, 1000, ";")) !== FALSE) {
			$num = count($datarow);
			$row++;
			if($row > 1){	
				$sql = "INSERT INTO `winterdata` VALUES (default, '$datarow[0]','$datarow[1]','$datarow[2]','$datarow[3]','$datarow[4]','$datarow[5]','$datarow[6]','$datarow[7]','$datarow[8]', '$datarow[8]')";
				$rowcheck = $data->execute($sql);
				if(!$rowcheck){
					echo "Winter error: " . $data->getError(). "<br \>";
				}
			}
		}
		fclose($handle);
	}
	
	echo "If no errors above, all database tables have been made and populated successfully.";
	
	
	
?>