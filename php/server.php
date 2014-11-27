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
		
		// Save the courseData to the users profile so if they refresh they don't lose any changes made when getting
		// their schedule.
		$sql = "UPDATE userslist SET coursedata='$coursedata' WHERE login='$login'";
		$data->execute($sql);
		
		$query = mysql_query("SELECT * FROM userslist WHERE login='{$login}'") or die(mysql_error());
		$row = mysql_fetch_array($query);
		$program = $row['stream'];
		
		
		$programArray = returnCourseArray($program);
		$coursedataarray = getCourseDataArray($coursedata);
		$yearstanding = getYearStanding($coursedataarray);
		$completedcourses = getCompletedCourseArray($coursedataarray, $programArray);

		for($semIdx = 0; $semIdx<$numOfSemesters; $semIdx++){
			for($courseIdx = 0; $courseIdx<$numClassesPerSemester; $courseIdx++){
				
					if(/*($semIdx % 2) === $fallId && */sizeof($schedule[$fallId]) < 5){
						if($coursedataarray[$semIdx][$courseIdx] == 0  )
						{
				
							if($programArray[$semIdx][$courseIdx]->SUBJ !== 'ELECTIVE'){
								$query = mysql_query("SELECT * FROM falldata WHERE subj='{$programArray[$semIdx][$courseIdx]->SUBJ}' AND 
									crse='{$programArray[$semIdx][$courseIdx]->CRSE}' LIMIT 1") or die(mysql_error());
								if(mysql_fetch_array($query) !== false){
									if(checkPrereqs($programArray[$semIdx][$courseIdx]->SUBJ." ".$programArray[$semIdx][$courseIdx]->CRSE, 
									$schedule, $coursedataarray, $programArray, $yearstanding, $completedcourses, $program, false)){
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
									if(checkPrereqs($programArray[$semIdx][$courseIdx]->SUBJ." ".$programArray[$semIdx][$courseIdx]->CRSE, 
									$schedule, $coursedataarray, $programArray, $yearstanding, $completedcourses, $program, true)){
										array_push($schedule[$winterId], $programArray[$semIdx][$courseIdx]);
										$coursedataarray[$semIdx][$courseIdx] = 2;
									}
								}
							}
						}
					}
				
			}
		}
	
		$schedule = getNumberOfOccurences($schedule);
		$falltimetable = array();
		//$fallsemester = schedule($schedule, $falltimetable, 0, $fallId, 0);
		$wintertimetable = array();
		//$wintersemester = schedule($schedule, $wintertimetable, 0, $winterId, 0);				
		$fallsemester =  scheduleTimetable($schedule, $falltimetable, 0);
		$wintersemester =  scheduleTimetable($schedule, $wintertimetable, 1);
		$result = "<schedule><fall>";
		
		for($fallIdx = 0; $fallIdx<sizeof($schedule[0]); $fallIdx++){
			$result .= "<course>".$schedule[0][$fallIdx]->SUBJ." ".$schedule[0][$fallIdx]->CRSE."</course>";
		}
		for($i = 0;$i<sizeof($fallsemester);$i++)
		{
				$result .= "<course>Time Table ".$i ."</course>";
				for($fallIdx = 0; $fallIdx<sizeof($fallsemester[$i]); $fallIdx++){
					$result .= "<course>".$fallsemester[$i][$fallIdx]['subj'].$fallsemester[$i][$fallIdx]['crse']."  ".$fallsemester[$i][$fallIdx]['seq']
							."||Days: ".$fallsemester[$i][$fallIdx]['day']
							."||StartTime: ".$fallsemester[$i][$fallIdx]['starttime']
							."   EndTime: ".$fallsemester[$i][$fallIdx]['endtime']."</course>";
				}
				
			
		}
		$result .= "</fall><winter>";	
		for($winterIdx = 0; $winterIdx<sizeof($schedule[1]); $winterIdx++){
			$result .= "<course>".$schedule[1][$winterIdx]->SUBJ." ".$schedule[1][$winterIdx]->CRSE."</course>";
		}
	
		for($i = 0;$i<sizeof($wintersemester);$i++)
		{
				$result .= "<course>Time Table ". $i ."</course>";
				for($winterIdx = 0; $winterIdx<sizeof($wintersemester[$i]); $winterIdx++){
					$result .= "<course>".$wintersemester[$i][$winterIdx]['subj'].$wintersemester[$i][$winterIdx]['crse']."  ".$wintersemester[$i][$winterIdx]['seq']
							."||Days: ".$wintersemester[$i][$winterIdx]['day']
							."||StartTime: ".$wintersemester[$i][$winterIdx]['starttime']
							."   EndTime: ".$wintersemester[$i][$winterIdx]['endtime']."</course>";
				}
				
			
		}
		$result .= "</winter></schedule>";
		
		header("content-type: text/xml");
		echo $result;
	}
	function numberOfSections($semester, $subj, $crse)
	{
		$semesterstring = "";
		if($semester === 0)
		{
			$semesterstring = "falldata";
		}
		else
		{
			$semesterstring = "winterdata";
		}
		
		$numberofresults = 0;
		$query = mysql_query("SELECT * FROM ".$semesterstring." WHERE subj='$subj' AND crse='$crse' AND type='LEC'") or die(mysql_error());
		while(mysql_fetch_array($query) !== false)
		{
			$numberofresults = $numberofresults+1;
		}	 
		return $numberofresults;
	}
	
	//sort array from least occurences to most to allow for easier time table creation
	function getNumberOfOccurences($schedule)
	{
		$fallId = 0;
		$winterId = 1;
		$scheduleOrganized = array(array(), array());
			
		for($i = 0;$i<sizeof($schedule);$i++)
		{
			for($j = 0;$j<sizeof($schedule[$i]);$j++)
			{
				array_push($scheduleOrganized[$i], numberOfSections($i, $schedule[$i][$j]->SUBJ, $schedule[$i][$j]->CRSE));

			}
		}
		$temp = 0;
		$tempsubject = $schedule[0][0]; 
		for($semester = 0; $semester<sizeof($scheduleOrganized);$semester++)
		{
			for ( $i = 0; $i < sizeof($scheduleOrganized[$semester])-1; $i++)
			{
				while($scheduleOrganized[$semester][$i] > $scheduleOrganized[$semester][$i+1])
				{
					$tempsubject = $schedule[$semester][$i];
					$schedule[$semester][$i]=$schedule[$semester][$i+1];
					$schedule[$semester][$i+1]=$tempsubject;
					
					$temp = $scheduleOrganized[$semester][$i];
					$scheduleOrganized[$semester][$i]=$scheduleOrganized[$semester][$i+1];
					$scheduleOrganized[$semester][$i+1]=$temp;
					$i= 0;
				}
			}
		
		}
		
	
	
		return $schedule;
		
	}

	function scheduleTimetable($schedule, $timetable, $semester)
	{
			set_time_limit(120);
			$semesterstring = "";
			if($semester === 0)
			{
				$semesterstring = "falldata";
			}
			else
			{
				$semesterstring = "winterdata";
			}
			$alltimetables = array();
			$timetables = array();
			$alltimetables = getClass($semester, $semesterstring, $schedule, $timetable, $alltimetables);
			
			$result = array();
			$resultindex = array();
			for($i = 0;$i<sizeof($alltimetables);$i++)
			{
				array_push($resultindex, numberOfLecturesInSemester($alltimetables[$i]));
			
			}
			//*********************************************
			
			
			for ( $i = 0; $i < sizeof($resultindex)-1; $i++)
			{
				while($resultindex[$i] < $resultindex[$i+1])
				{
					$tempsubject = $alltimetables[$i];
					$alltimetables[$i]=$alltimetables[$i+1];
					$alltimetables[$i+1]=$tempsubject;
					
					$temp = $resultindex[$i];
					$resultindex[$i]=$resultindex[$i+1];
					$resultindex[$i+1]=$temp;
					$i= 0;
				}
			}
			
			
		
	
			
			//**********************************************
			$result = array();
			
			for($i = 0;$i<5 &&$i<sizeof($alltimetables);$i++)
			{
				array_push($result, $alltimetables[$i]);
			}
			return $result;
			
			
	}
	function getClass($semester, $semesterstring, $schedule, $timetable, $alltimetables)
	{
		$count = 0;
		for($i = 0;$i<sizeof($timetable);$i++)
		{
			if($timetable[$i]['type'] === "LEC")
			{
				$count = $count+1;
			}
		}
		if($count === sizeof($schedule[$semester]))
		{
			array_push($alltimetables, $timetable);
			return $alltimetables;
		}
	
		$query = mysql_query("SELECT * FROM ".$semesterstring." WHERE subj='{$schedule[ $semester][$count]->SUBJ}' AND crse='{$schedule[$semester][ $count ]->CRSE}' AND type='LEC'") or die(mysql_error());
		$class = mysql_fetch_array($query); 
		while($class !== false)
		{
			if(hasConflicts($timetable, $class)) // if this class has no conflict do this
			{
				
				//************************************LLLAAAABBBBBBSSSSS START********************************************
				$sec = $class['seq'];
				$subj = $class['subj'];
				$crse = $class['crse'];
				$query = mysql_query("SELECT * FROM ".$semesterstring." WHERE subj='{$subj}' AND crse='{$crse}' AND type!='LEC'") or die(mysql_error());
				$labs = mysql_fetch_array($query);
				$labclass = $labs;
				if($labs === false)
				{
					$temptable = $timetable;
					array_push($temptable, $class); //push the specified class into the array
					array_push($alltimetables, $temptable);
					$alltimetables = getClass($semester, $semesterstring, $schedule, $temptable, $alltimetables);
				}
				
				while($labs !== false )
				{
					//if(strlen($labs['seq'])>1 )
					//{
						if(strpos($labs['seq'], $sec)!==false || strpos($labs['seq'], "L")!==false || strpos($labs['seq'], "G")!==false)
						{
							if(hasConflicts($timetable, $labs) )
							{
								
								$temptable = $timetable;
								array_push($temptable, $labs); 
								array_push($temptable, $class); //push the specified class into the array
								array_push($alltimetables, $temptable);
								$alltimetables = getClass($semester, $semesterstring, $schedule, $temptable, $alltimetables);
								
							}
						}
						
						/*if(strpos($labs['seq'], "L")!==false || strpos($labs['seq'], "G")!==false )
						{
							
							if(hasConflicts($timetable, $labs) )
							{
								
								$temptable = $timetable;
								array_push($temptable, $labs); 
								array_push($temptable, $class); //push the specified class into the array
								array_push($alltimetables, $temptable);
								$alltimetables = getClass($semester, $semesterstring, $schedule, $temptable, $alltimetables);
								
							}
						}*/
					//}
					$labs = mysql_fetch_array($query); 			
				}	
			
				//************************************LLLAAAABBBBBBSSSSS END********************************************
							
			}
			
			$class = mysql_fetch_array($query);
		}
	
		return $alltimetables;
		
	}
	function numberOfLecturesInSemester( $timetable)
	{
		$count = 0;
		for($i = 0;$i<sizeof($timetable);$i++)
		{
			if($timetable[$i]['type'] === "LEC")
			{
				$count = $count+1;
			}
		}
		return $count;
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
	
	
	function getCompletedCourseArray($coursedataarray, $programArray)
	{
		$numClassesPerSemester = 6;
		$numOfSemesters = 8;
		$completed = array(); // the schedule array will have a fall and winter array
		
		for($semIdx = 0; $semIdx<$numOfSemesters; $semIdx++)
		{
		
			for($courseIdx = 0; $courseIdx<$numClassesPerSemester; $courseIdx++)
			{
					if($coursedataarray[$semIdx][$courseIdx] == 1  )
					{
						array_push($completed, $programArray[$semIdx][$courseIdx]->SUBJ." ".$programArray[$semIdx][$courseIdx]->CRSE);
					}	
			}
		}
		
		
		return $completed;
				
	}
	function getConcurrentCourses($coursedataarray, $programArray)
	{
		$numClassesPerSemester = 6;
		$numOfSemesters = 8;
		$completed = array(); // the schedule array will have a fall and winter array
		
		for($semIdx = 0; $semIdx<$numOfSemesters; $semIdx++){
		
			for($courseIdx = 0; $courseIdx<$numClassesPerSemester; $courseIdx++)
			{
					if($coursedataarray[$semIdx][$courseIdx] == 2  )
					{
						array_push($completed, $programArray[$semIdx][$courseIdx]->SUBJ." ".$programArray[$semIdx][$courseIdx]->CRSE);
					}	
					
			}
		}
		
		return $completed;
	}
	function checkStanding($prereqinfo, $yearstanding, $program)
	{
		$tempProgram = explode("in", $prereqinfo);
		$currentBool = true;
		if(strpos($tempProgram[0], "fourth-year status")!==false && $yearstanding!=4)
		{
			$currentBool =  false;
		}
		else if(strpos($tempProgram[0], "third-year status")!==false && $yearstanding<3)
		{
			$currentBool =  false;
		}
		else if(strpos($tempProgram[0], "second-year status")!==false && $yearstanding<2)
		{
			$currentBool =  false;
		}
		
		if(strpos($tempProgram[1],"Engineering")<5 && strpos($tempProgram[1],"Engineering")>-1 )
		{
			return $currentBool;
		}
		else if(strpos($tempProgram[1],  $program)!==false)
		{
			return false;
		}
		
		return $currentBool;
	}
	function checkEnrolment($prereqinfo, $program)
	{
		if(strpos($prereqinfo, $program)!==false)
		{
			return true;
		}
		
		return false;
	}
	function executePrereqCheck($requirementsArray, $yearstanding, $completedcourses, $program, $schedule, $isWinter)
	{
		for($i = 0;$i<sizeof($requirementsArray);$i++)
		{
			if(strpos($requirementsArray[$i], "or")!==false )
			{
				//function here
				
				$ortemp = explode("or", $requirementsArray[$i]);
				if(!executePrereqOrCheck($ortemp, $yearstanding, $completedcourses, $program, $schedule, $isWinter))
				{
					return false;
				}
				//end function
			}
			else if(strpos($requirementsArray[$i], "status")!==false )
			{
				if(checkStanding($requirementsArray[$i], $yearstanding, $program) === false)
				{
					return false;
				}
			}
			else if(strpos($requirementsArray[$i], "enrolment")!==false )
			{
				if(checkEnrolment($requirementsArray[$i], $program) === false)
				{
					return false;
				}
			}
			else 
			{
				$tempout = array();
				preg_match("/[A-Z]{4}\s[0-9]{4}/", $requirementsArray[$i], $tempout);
				if(sizeof($tempout)>0)
				{
					$matched = false;
					for($j = 0;$j<sizeof($completedcourses);$j++)
					{
						if($completedcourses[$j] === $tempout[0])
						{
							$matched = true;
						}
						
						
					}
					if($isWinter)
					{
						for($j = 0;$j<sizeof($schedule[0]);$j++)
						{
						
							if( $schedule[0][$j]->SUBJ." ".$schedule[0][$j]->CRSE === $tempout[0])
							{
								$matched = true;
							}
						}
					}
					
					if(!$matched)
					{
						return false;
					}
					
				}
			}
			
		}
		return true;
	}
	function executePrereqOrCheck($requirementsArray, $yearstanding, $completedcourses, $program, $schedule, $isWinter)
	{
		for($i = 0;$i<sizeof($requirementsArray);$i++)
		{
			if(strpos($requirementsArray[$i], "status")!==false )
			{
				if(checkStanding($requirementsArray[$i], $yearstanding, $program) === true)
				{
					return true;
				}
			}
			else if(strpos($requirementsArray[$i], "enrolment")!==false )
			{
				if(checkEnrolment($requirementsArray[$i], $program) === true)
				{
					return true;
				}
			}
			else 
			{
				$tempout = array();
				preg_match("/[A-Z]{4}\s[0-9]{4}/", $requirementsArray[$i], $tempout);
				if(sizeof($tempout)>0)
				{
					for($j = 0;$j<sizeof($completedcourses);$j++)
					{
						if($completedcourses[$j] === $tempout[0])
						{
							return true;
						}
						
						
					}
					if($isWinter)
					{
						for($j = 0;$j<sizeof($schedule[0]);$j++)
						{
						
							if( $schedule[0][$j]->SUBJ." ".$schedule[0][$j]->CRSE=== $tempout[0])
							{
								return  true;
							}
						}
					}
				}
			}
		
			
		}
		
		return false;
	}
	function checkPrereqs($class, $schedule, $coursedataarray, $programArray, $yearstanding, $completedcourses, $program, $isWinter)
	{
		$query = mysql_query("SELECT * FROM prereqdata WHERE course='{$class}'") or die(mysql_error());
		$row = mysql_fetch_array($query);
		$prereqinfo = $row['prereq'];
		
		$andtemp = explode("and", $prereqinfo);
		
		return executePrereqCheck($andtemp, $yearstanding, $completedcourses,$program, $schedule, $isWinter);
	
	}
	
	function hasConflicts($arrayofcourses, $newcourse)
	{
		//test new course against all courses in the array
		$newcourseday = $newcourse['day'];//get the day for the class newcourse
		$newcoursedayarray = str_split($newcourseday, 1);
		if(strlen($newcourseday) == 1)
		{
			array_push($newcoursedayarray, " " );
		}
		else if(strlen($newcourseday) == 0)
		{
			return true;
		}
			
		$newcoursestarttime = intval($newcourse['starttime']);
		$newcourseendtime = intval($newcourse['endtime']);
		
		for($i=0;$i<sizeof($arrayofcourses);$i++)
		{
			$oldcourseday = $arrayofcourses[$i]['day'];//get the day for arrayofcourses[$i]
			$oldcoursedayarray = str_split($oldcourseday, 1);
			
			$oldcoursestarttime = intval($arrayofcourses[$i]['starttime']);
			$oldcourseendtime = intval($arrayofcourses[$i]['endtime']);
			
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