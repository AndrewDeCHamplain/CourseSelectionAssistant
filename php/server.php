<?php
	require_once("db.php");
	include "connection.php";
	
	$data = new database("usersdb2");

	$type = $_POST['typeofrequest'];
	
	if($type=="createaccount"){
		$login = $_POST['login'];
		
		/* make sure that the login is not an empty string or
		it has at least 3 chars*/
		
		if( strlen($login) < 3 ){
			echo "Login must be at least 3 char long";
			header("Refresh:2;url=view1.php");
			exit;
		}
		
		$firstname = $_POST['firstname'];
		$lastname = $_POST['lastname'];
		$password = $_POST['password'];
		$studentnumber = $_POST['studentnumber'];
		$coursedata = '000000000000000000000000000000000000000000000000';
		$stream = $_POST['stream'];
		
		$sql = "INSERT INTO userslist VALUES('$login', '$firstname', '$lastname', '".crypt( $password, 'abc' ). "', '$studentnumber', '$stream', '$coursedata')";
			
		$row = $data->execute($sql);
		if($row){
			echo "Account created ... you can log in";
		}else{
			echo "Did not create the account. Change login";
		}
		
		header("refresh:2;url=view1.php");
		
		exit;
	}
	
	if($type=="login"){
		$login = $_POST['login'];
		$password = $_POST['password'];
		
		$sql = "SELECT * FROM userslist WHERE login='$login' 
				AND password='". crypt($password, "abc")."'";
				
		$rows = $data->execute($sql);		
		$num = $rows->num_rows;
		if($num>0){
			setcookie("login", $login, time() + 3600);
			
			header("Location: view2.php");
		}else{
			echo "Invalid information";
			header("refresh:2;url=view1.php");
		}
	}
	
	if($type=="update"){
		$login = $_COOKIE['login'];
		$old = $_POST['oldpassword'];
		$new = $_POST['newpassword'];
		
		$sql = "UPDATE userslist SET password='".crypt($new, 'abc')
			."' WHERE login='$login' AND password='".crypt($old, 'abc')."'";
			
		$data->execute($sql);
		if($data->connection->affected_rows == 1){
			echo "The password is updated";
		}else{
			echo "The password was not update";
		}
		
		header("refresh:2;url=view2.php");
		
	}
	
	if($type=="savecourses"){
		$login = $_COOKIE['login'];
		$coursedata = $_POST['coursedatastring'];
		
		$sql = "UPDATE userslist SET coursedata='$coursedata' WHERE login='$login'";
			
		$data->execute($sql);
		
		if($data->connection->affected_rows == 1){
			echo "The stream is updated";
		}else{
			echo "The stream was not update";
		}
	
		header("refresh:2;url=view2.php");
		
	}
	
	function returnCourseArray($program)
	{
		 
		if( $program == 'Computer Systems Engineering'){
		$json = file_get_contents("../json/cseReq.json");
		} 
		elseif($program == 'Software Engineering'){
		$json = file_get_contents("../json/softwareReq.json");
		}
		elseif($program == 'Communication Engineering'){
		$json = file_get_contents("../json/commReq.json");
		}
		elseif($program == 'Biomedical Engineering'){
		$json = file_get_contents("../json/biomedReq.json");
		}
		 
		$array = json_decode($json);
		return $array;
	}
 if($type=="setstream"){
  
 	$login = $_COOKIE['login'];
 	$stream = $_POST['program'];
 	$coursedata = "";
 	 
 	 
 	$coursearray = returnCourseArray($stream);
 	$numClassesPerSemester = 6;
 	$numOfSemesters = 8;
 	 
 	for($i = 0;$i<$numOfSemesters;$i++)
 	{
		for($j = 0;$j<$numClassesPerSemester;$j++)
		{
			if($coursearray[$i][$j]->SUBJ === "")
			{
				$coursedata=$coursedata."9";
			}
			else
			{
				$coursedata=$coursedata."0";
			}
		}
 	}
 	 
 	$sql = "UPDATE userslist SET stream='$stream', coursedata='$coursedata' WHERE login='$login'"; 
 	$data->execute($sql);
 	 
 	if($data->connection->affected_rows == 1){
		echo "The stream is updated ".$coursedata;
 	}else{
		echo "The stream was not update";
 	}
  
 	header("refresh:2;url=view2.php");
 }
	
	if($type=="logout"){
		setcookie("login", "", time() -100);
		header("Location: view1.php");

	}
	
	if($type=="getschedule"){
		$numClassesPerSemester = 6;
		$numOfSemesters = 8;
		$schedule = array(array(), array()); // the schedule array will have a fall and winter array
		
		$login = $_COOKIE['login'];
		$coursedata = $_POST['coursedata'];
		
		
		$query = mysql_query("SELECT * FROM userslist WHERE login='{$login}'") or die(mysql_error());
		$row = mysql_fetch_array($query);
		$program = $row['stream'];
		
		$programArray = returnCourseArray($program);
		$coursedataarray = getCourseDataArray($coursedata);
		
		$tempcount=0;
		for($semIdx = 0; $semIdx<$numOfSemesters; $semIdx++){
			for($courseIdx = 0; $courseIdx<5; $courseIdx++){
				if($coursedataarray[$semIdx][$courseIdx] == 0){
				
					if($tempcount < 5){
						
						array_push($schedule[0], $programArray[$semIdx][$courseIdx]);
						/*$query = mysql_query("SELECT * FROM falldata WHERE subj='{$programArray[$semIdx][$courseIdx]->SUBJ}', 
							crse='{$programArray[$semIdx][$courseIdx]->CRSE}' LIMIT 1") or die(mysql_error());
						$row = mysql_fetch_array($query);
						if($row !== false){
							//array_push($schedule[0], $programArray[$semIdx][$courseIdx]);
						}*/
					} else {
						array_push($schedule[1], $programArray[$semIdx][$courseIdx]);
					/*
						array_push($schedule[1], $programArray[$semIdx][$courseIdx]);
						$query = mysql_query("SELECT * FROM winterdata WHERE subj='{$programArray[$semIdx][$courseIdx]->SUBJ}', 
							crse='{$programArray[$semIdx][$courseIdx]->CRSE}' LIMIT 1") or die(mysql_error());
						$row = mysql_fetch_array($query);
						if($row !== false){
							//array_push($schedule[1], $programArray[$semIdx][$courseIdx]);
						}*/
					}
					$tempcount++;
				}
			}
		}
		
	/* LOGIC FOR CONFLICT FREE SCHEDULER	
	if(courses.startTime <= newcourse.startTime && courses.endTime>=newcourse.startTime)
	{
		//newcourse starts within an old course
		//return false
		return false
	}
	
	if(courses.startTime <= newcourse.endTime && courses.endTime>=newcourse.endTime)
	{
		//new course ends within an old course
		//return false
		return false
	}

	if(courses.startTime >= newcourse.startTime && courses.endTime<=newcourse.endTime)
	{
		//newcourse encapsulated old course
		//return false
		return false
	}
	

	
	
	//new course fits
	//return true
	return true
*/
		$result= "<schedule><fall>";
		//while ( ($row = $rows->fetch_object() ) ){
		for($fallIdx = 0; $fallIdx<5; $fallIdx++){
			$result .= "<course>".$schedule[0][$fallIdx]->SUBJ.$schedule[0][$fallIdx]->CRSE."</course>";
		}
		$result .= "</fall><winter>";
		for($winterIdx = 0; $winterIdx<5; $winterIdx++){
			$result .= "<course>".$schedule[1][$winterIdx]->SUBJ.$schedule[1][$winterIdx]->CRSE."</course>";
		}
		$result .= "</winter>";
		for($i = 0;$i<8;$i++)
		{
			$result .= "<sem".$i.">";
			for($j = 0;$j<6;$j++)
			{
				$result .= $coursedataarray[$i][$j];
			}
			$result .= "</sem".$i.">";
		}
		
		$result .= "</schedule>";
		
		header("content-type: text/xml");
		echo $result;
	}
	function schedule($coursedata)
	{
		$query = mysql_query("SELECT * FROM userslist WHERE login='{$login}'") or die(mysql_error());
		$row = mysql_fetch_array($query);
		$stream = $row['stream'];
		
		
		
		
		
		return "testing";
	}
	function checkIfClassFull($fallorwinter, $courseid)
	{
		
		$query = mysql_query("SELECT * FROM $fallorwinter WHERE id='{$courseid}'") or die(mysql_error());
		$row = mysql_fetch_array($query);
		$available = $row['enrolled'];
		
		if($available>0)
		{
			return true;
		}
		return false;
	}
	
	function getCourseDataArray($coursedata){
	
		//turn coursedata into a 2 dimensional array
		$temp = array(array("","","","","","","",""),
				  array("","","","","","","",""),
				  array("","","","","","","",""),
				  array("","","","","","","",""),
				  array("","","","","","","",""),
				  array("","","","","","","",""),
				  array("","","","","","","",""),
				  array("","","","","","","",""));
		
					  
		$coursedataarray = str_split($coursedata, 1);
		for($i =0;$i<strlen($coursedata);$i++)
		{
			if($i%8 == 0)
			{
				$index = 0;
				for($j =0;$j<sizeof($temp[$index]);$j++)
				{
					if($temp[$index][$j] == "")
					{
						$temp[$index][$j] = $coursedataarray[$i];
						$j = sizeof($temp[$index]);
					}
				}
	
			}
			else if($i%8==1)
			{
				$index = 1;
				for($j =0;$j<sizeof($temp[$index]);$j++)
				{
					if($temp[$index][$j] == "")
					{
						$temp[$index][$j] = $coursedataarray[$i];
						$j = sizeof($temp[$index]);
					}
				}			
			}
			else if($i%8==2)
			{
				$index = 2;
				for($j =0;$j<sizeof($temp[$index]);$j++)
				{
					if($temp[$index][$j] == "")
					{
						$temp[$index][$j] = $coursedataarray[$i];
						$j = sizeof($temp[$index]);
					}
				}			
			}
			else if($i%8==3)
			{
				$index = 3;
				for($j =0;$j<sizeof($temp[$index]);$j++)
				{
					if($temp[$index][$j] == "")
					{
						$temp[$index][$j] = $coursedataarray[$i];
						$j = sizeof($temp[$index]);
					}
				}			
			}
			else if($i%8==4)
			{
				$index = 4;
				for($j =0;$j<sizeof($temp[$index]);$j++)
				{
					if($temp[$index][$j] == "")
					{
						$temp[$index][$j] = $coursedataarray[$i];
						$j = sizeof($temp[$index]);
					}
				}			
				
			}
			else if($i%8==5)
			{
				$index = 5;
				for($j =0;$j<sizeof($temp[$index]);$j++)
				{
					if($temp[$index][$j] == "")
					{
						$temp[$index][$j] = $coursedataarray[$i];
						$j = sizeof($temp[$index]);
					}
				}			
			}
			else if($i%8==6)
			{
				$index = 6;
				for($j =0;$j<sizeof($temp[$index]);$j++)
				{
					if($temp[$index][$j] == "")
					{
						$temp[$index][$j] = $coursedataarray[$i];
						$j = sizeof($temp[$index]);
					}
				}			
			}
			else if($i%8==7)
			{
				$index = 7;
				for($j =0;$j<sizeof($temp[$index]);$j++)
				{
					if($temp[$index][$j] == "")
					{
						$temp[$index][$j] = $coursedataarray[$i];
						$j = sizeof($temp[$index]);
					}
				}		
			}
		}
		//course data is now a 2 dimensional array stored in temp
		return $temp;
	}
?>