<?php
	require_once("db.php");
	include "connection.php";
	
	$data = new database("usersdb2");

	$type = $_POST['typeofrequest'];
	
	$fallId = 0;
	$winterId = 1;
	
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
		
		/*
		Year standing defs:
			First Year: Fewer than 4.0 credits
			Second Year: 4.0 through 8.5 credits
			Third Year: 9.0 through 13.5 credits
			Fourth Year: 14.0 or more credits (only for students in 20.0 credit degree programs)
		*/
		
		$login = $_COOKIE['login'];
		$coursedata = $_POST['coursedata'];
		
		
		$query = mysql_query("SELECT * FROM userslist WHERE login='{$login}'") or die(mysql_error());
		$row = mysql_fetch_array($query);
		$program = $row['stream'];
		
		$programArray = returnCourseArray($program);
		$coursedataarray = getCourseDataArray($coursedata);
		
		for($semIdx = 0; $semIdx<$numOfSemesters; $semIdx++){
			for($courseIdx = 0; $courseIdx<$numClassesPerSemester; $courseIdx++){
				
					if(/*($semIdx % 2) === $fallId && */sizeof($schedule[$fallId]) < 5){
						if($coursedataarray[$semIdx][$courseIdx] == 0  )
						{
				
							if($programArray[$semIdx][$courseIdx]->SUBJ !== 'ELECTIVE'){
								$query = mysql_query("SELECT * FROM falldata WHERE subj='{$programArray[$semIdx][$courseIdx]->SUBJ}' AND 
									crse='{$programArray[$semIdx][$courseIdx]->CRSE}' LIMIT 1") or die(mysql_error());
								if(mysql_fetch_array($query) !== false){
									if(checkPrereqs($programArray[$semIdx][$courseIdx]->SUBJ." ".$programArray[$semIdx][$courseIdx]->CRSE)){
										array_push($schedule[$fallId], $programArray[$semIdx][$courseIdx]);
										$coursedataarray[$semIdx][$courseIdx] = 2;
									}
								}
								
							}	
							
						}
					
					} 

					 if (/*($semIdx % 2) === $winterId &&*/ sizeof($schedule[$winterId]) < 5){
						if($coursedataarray[$semIdx][$courseIdx] == 0  )
						{
				
							if($programArray[$semIdx][$courseIdx]->SUBJ !== 'ELECTIVE'){
								$query = mysql_query("SELECT * FROM winterdata WHERE subj='{$programArray[$semIdx][$courseIdx]->SUBJ}' AND 
									crse='{$programArray[$semIdx][$courseIdx]->CRSE}' LIMIT 1") or die(mysql_error());
								if(mysql_fetch_array($query) !== false){
									if(checkPrereqs($programArray[$semIdx][$courseIdx]->SUBJ." ".$programArray[$semIdx][$courseIdx]->CRSE)){
										array_push($schedule[$winterId], $programArray[$semIdx][$courseIdx]);
										$coursedataarray[$semIdx][$courseIdx] = 2;
									}
								}
							}
						}
					}
				
			}
		}

		$result= "<schedule><fall>";
		//while ( ($row = $rows->fetch_object() ) ){
		for($fallIdx = 0; $fallIdx<sizeof($schedule[$fallId]); $fallIdx++){
			$result .= "<course>".$schedule[$fallId][$fallIdx]->SUBJ.$schedule[$fallId][$fallIdx]->CRSE."</course>";
		}
		$result .= "</fall><winter>";
		for($winterIdx = 0; $winterIdx<sizeof($schedule[$winterId]); $winterIdx++){
			$result .= "<course>".$schedule[$winterId][$winterIdx]->SUBJ.$schedule[$winterId][$winterIdx]->CRSE."</course>";
		}
		$result .= "</winter></schedule>";
		
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
	function getYearStanding($coursedataarray)
	{
		/*
		Year standing defs:
			First Year: Fewer than 4.0 credits
			Second Year: 4.0 through 8.5 credits
			Third Year: 9.0 through 13.5 credits
			Fourth Year: 14.0 or more credits (only for students in 20.0 credit degree programs)
		*/
		$weightpercredit = 0.5;
		$credits = 0;
		for($i = 0;$i<sizeof($coursedataarray);$i++)
		{
			for($j =0;$j<sizeof($coursedataarray[$i]);$j++)
			{
				if($coursedataarray[$i][$j] === "1")
				{
					$credits++;
				}
			}
		}
		$points = $credits * $weightpercredit;
		
		if($points<4.0)
		{
			return 1;
		}
		else if($points<8.5)
		{
			return 2;
		}
		else if($points< 13.5)
		{
			return 3;
		}
		else 
		{
			return 4;
		}
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
	
	function checkPrereqs($class){
		$query = mysql_query("SELECT * FROM prereqdata WHERE course='{$class}'") or die(mysql_error());
		$row = mysql_fetch_array($query);
		$prereqinfo = $row['prereq'];
		
		
		
		return true;
	}
	
	function hasConflicts($arrayofcourses, $newcourse)
	{
		//test new course against all courses in the array
		$newcourseday = $newcourse['day'];//get the day for the class newcourse
		$newcoursedayarray = str_split($newcourseday, 1);
		if(strlen($newcourseday) == 1)
		{
			$newcoursedayarray[1] = "";
		}
		$newcoursestarttime = intval($newcourse['starttime']);
		$newcourseendtime = intval($newcourse['endtime']);
		
		for($i=0;$i<sizeof($arrayofcourses);$i++)
		{
			$oldcourseday = $arrayofcourses[$i]['day'];//get the day for arrayofcourses[$i]
			$oldcoursedayarray = str_split($oldcourseday, 1);
			if(strlen($oldcourseday) == 1)
			{
				$oldcoursedayarray[1] = "";
			}
			$oldcoursestarttime = intval($arrayofcourses[$i]['starttime']);
			$oldcourseendtime = intval($arrayofcourses[$i]['endtime']);
			
			if($oldcoursedayarray[0] === $newcoursedayarray[0] || 
				$oldcoursedayarray[1] === $newcoursedayarray[1] || 
				$oldcoursedayarray[0] === $newcoursedayarray[1] || 
				$oldcoursedayarray[1] === $newcoursedayarray[0])
			{
				if($oldcoursestarttime <= $newcoursestarttime && $oldcourseendtime >= $newcoursestarttime)
				{
					//newcourse starts within an old course
					//return false
					return false;
				}
				
				if($oldcoursestarttime <= $newcourseendtime &&  $oldcourseendtime>=$newcourseendtime)
				{
					//new course ends within an old course
					//return false
					return false;
				}

				if($oldcoursestarttime >=$newcoursestarttime &&  $oldcourseendtime<=$newcourseendtime)
				{
					//newcourse encapsulated old course
					//return false
					return false;
				}
			}
		}
				
		//new course fits
		//return true
		return true;
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