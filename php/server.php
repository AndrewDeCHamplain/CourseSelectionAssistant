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
		$fallschedule = "";
		$winterschedule = "";
		
		$sql = "INSERT INTO userslist VALUES('$login', '$firstname', '$lastname', '".crypt( $password, 'abc' ). "', '$studentnumber', '$stream', '$coursedata', '$fallschedule', '$winterschedule')";
			
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
		echo "The stream is updated.";
 	}else{
		echo "The stream was not updated.";
 	}
  
 	header("refresh:2;url=view2.php");
 }
	
	if($type=="logout"){
		setcookie("login", "", time() -100);
		header("Location: view1.php");

	}
	
	if($type=="submitschedule"){
		$login = $_COOKIE['login'];
		$fallschedule = $_POST['fallschedule'];
		$winterschedule = $_POST['winterschedule'];

		$errorArray = array();

		// Fall schedule
		$schedule = "";
		$fallScheduleArray = explode(',', $fallschedule);
		
		for($fallIdx=0; $fallIdx<sizeof($fallScheduleArray); $fallIdx+=3){
			
			$query = mysql_query("SELECT * FROM falldata WHERE subj='".$fallScheduleArray[$fallIdx]."' AND 
				crse='".$fallScheduleArray[$fallIdx+1]."' AND seq='".$fallScheduleArray[$fallIdx+2]."'") or die(mysql_error());
			$row = mysql_fetch_array($query);
			$available = $row['available'];
			
			$sql = "UPDATE falldata SET available='".($available - 1)."' WHERE subj='".$fallScheduleArray[$fallIdx]."' AND 
				crse='".$fallScheduleArray[$fallIdx+1]."' AND seq='".$fallScheduleArray[$fallIdx+2]."'";
			$data->execute($sql);
			
			$schedulerow = $row['subj'] . $row['crse'] . " " . $row['seq'] .": " . $row['title'] . " " . $row['type'] . " on " . $row['day'] .
				" from " . $row['starttime'] . " to " . $row['endtime'];

			
			$errorCheck = errorCheckAvailable($available, $fallScheduleArray, $fallIdx, 'falldata');
			if ($errorCheck !== false){
				array_push($errorArray, $errorCheck);
			} else {
				// If there was no error then add schedulerow to the schedule.
				
				if($schedule == ""){
					$schedule = $schedulerow;
				} else {
					$schedule = ($schedule . ",".$schedulerow);
				
				}
			}
		}
		$sql = "UPDATE userslist SET fallschedule='$schedule' WHERE login='$login'"; 
		$data->execute($sql);
		
		//Winter schedule
		$schedule = "";
		$winterScheduleArray = explode(',', $winterschedule);
		
		for($winterIdx=0; $winterIdx<sizeof($winterScheduleArray); $winterIdx+=3){
			
			$query = mysql_query("SELECT * FROM winterdata WHERE subj='".$winterScheduleArray[$winterIdx]."' AND 
				crse='".$winterScheduleArray[$winterIdx+1]."' AND seq='".$winterScheduleArray[$winterIdx+2]."'") or die(mysql_error());
			$row = mysql_fetch_array($query);
			$available = $row['available'];

			$sql = "UPDATE winterdata SET available='".($available - 1)."' WHERE subj='".$winterScheduleArray[$winterIdx]."' AND 
				crse='".$winterScheduleArray[$winterIdx+1]."' AND seq='".$winterScheduleArray[$winterIdx+2]."'";
			$data->execute($sql);
			
			$schedulerow = $row['subj'] . $row['crse'] . " " . $row['seq'] .": " . $row['title'] . " " . $row['type'] . " on " . $row['day'] .
				" from " . $row['starttime'] . " to " . $row['endtime'];
				
			$errorCheck = errorCheckAvailable($available, $winterScheduleArray, $winterIdx, 'winterdata');
			if ($errorCheck !== false){
				array_push($errorArray, $errorCheck);
			} else {
				// If there was no error then add schedulerow to the schedule.
				
				if($schedule == ""){
					$schedule = $schedulerow;
				} else {
					$schedule = ($schedule . ",".$schedulerow);
				
				}
			}
		}
		$sql = "UPDATE userslist SET winterschedule='$schedule' WHERE login='$login'"; 
		$data->execute($sql);
		
		if(sizeof($errorArray) > 0){
			echo "<h2> You were NOT Enrolled in the Following Classes </h2>";
			for($idx=0; $idx<sizeof($errorArray); $idx++){
				echo $errorArray[$idx]."<br />";
				
			}
			echo '<h4> Returning to Home in 10 seconds. </h4>';
			header("refresh:10;url=view2.php");
		} else {
			
			echo "<h2> You Have Been Enrolled in All Your Courses. </h2>";
			echo '<h4> To view your schedule, visit to "Your Schedule" tab. </h4>';
			header("refresh:5;url=view2.php");
		}
		
		
	
	}
	
	function errorCheckAvailable($available, $array, $index, $databasetable){
	
		$query = mysql_query("SELECT * FROM $databasetable WHERE subj='".$array[$index]."' AND 
			crse='".$array[$index + 1]."' AND seq='".$array[$index + 2]."'") or die(mysql_error());
		$row = mysql_fetch_array($query);
		$errorCheck = $row['available'];

		if($available == ($errorCheck+1)){	
			return false;
		} else {
			if($databasetable == 'falldata'){
				return ("Fall: ". $array[$index] . " " . $array[$index + 1] . " " . $array[$index + 2]);
			} else {
				return ("Winter: ". $array[$index] . " " . $array[$index + 1] . " " . $array[$index + 2]);
			}
		}
	}
	
	if($type=="getschedule"){
		$numClassesPerSemester = 6;
		$numOfSemesters = 8;
		$schedule = array(array(), array()); // the schedule array will have a fall and winter array
		
		$fallId = 0;
		$winterId = 1;
		/*
		Year standing defs:
			First Year: Fewer than 4.0 credits
			Second Year: 4.0 through 8.5 credits
			Third Year: 9.0 through 13.5 credits
			Fourth Year: 14.0 or more credits (only for students in 20.0 credit degree programs)
		*/
		
		$login = $_COOKIE['login'];
		$coursedata = $_POST['coursedata'];

		$elecarray = $_POST['electives'];
		$electivecount = 0;
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
	
		
		$fallcount = 0;
		$wintercount = 0;

		for($i = 0;$i<sizeof($programArray);$i++)
		{	
			$fallmax = returnNumberOfCourses($programArray, $i);
			for($j = 0;$j<sizeof($programArray[$i]);$j++)
			{	
				if($programArray[$i][$j]->SUBJ !== "")
				{
					if($coursedataarray[$i][$j] == 0)
					{	
						if($fallcount< $fallmax)
						{

							if (strpos($programArray[$i][$j]->SUBJ, "ELECTIVE")!==false)
							{
								array_push($schedule[$fallId], $programArray[$i][$j]);
								$temp =  explode(" ", $elecarray[$electivecount]);
								$electivecount = $electivecount+1;
								$schedule[$fallId][sizeof($schedule[$fallId])-1]->SUBJ = $temp[0];
								$schedule[$fallId][sizeof($schedule[$fallId])-1]->CRSE = $temp[1];
								$coursedataarray[$i][$j] = 2;
								$fallcount = $fallcount+1;
							}
							else
							{
									$query = mysql_query("SELECT * FROM falldata WHERE subj='{$programArray[$i][$j]->SUBJ}' AND 
											crse='{$programArray[$i][$j]->CRSE}' LIMIT 1") or die(mysql_error());
									if(mysql_fetch_array($query) !== false)
									{
										if(checkPrereqs($programArray[$i][$j]->SUBJ." ".$programArray[$i][$j]->CRSE, 
										$schedule, $coursedataarray, $programArray, $yearstanding, $completedcourses, $program, false))
										{
											array_push($schedule[$fallId], $programArray[$i][$j]);
											$coursedataarray[$i][$j] = 2;
											$fallcount = $fallcount+1;
										}
									}		
							}

						}
						else{
							$i = sizeof($programArray);
							break;
						}
					}
					else if (strpos($programArray[$i][$j]->SUBJ, "ELECTIVE")!==false)
					{
							$electivecount = $electivecount+1;					
					}
					
					
					
				}
			}
		}
		
		$wintermax = 5;
		$electivecount = 0;
		for($i = 0;$i<sizeof($programArray);$i++)
		{	
			if($i>0)
			{
				$wintermax = returnNumberOfCourses($programArray, $i -1);
			}
	
			for($j = 0;$j<sizeof($programArray[$i]);$j++)
			{	
				if($programArray[$i][$j]->SUBJ !== "")
				{
					if($wintercount< $wintermax)
					{
						if($coursedataarray[$i][$j] == 0)
						{
							if (strpos($programArray[$i][$j]->SUBJ, "ELECTIVE")!==false)
							{

									array_push($schedule[$winterId], $programArray[$i][$j]);
									$temp =  explode(" ", $elecarray[$electivecount]);
									$electivecount = $electivecount+1;
									$schedule[$winterId][sizeof($schedule[$winterId])-1]->SUBJ = $temp[0];
									$schedule[$winterId][sizeof($schedule[$winterId])-1]->CRSE = $temp[1];
									$coursedataarray[$i][$j] = 2;
									$wintercount = $wintercount+1;
									
							}
							else
							{
									$query = mysql_query("SELECT * FROM winterdata WHERE subj='{$programArray[$i][$j]->SUBJ}' AND 
										crse='{$programArray[$i][$j]->CRSE}' LIMIT 1") or die(mysql_error());
									if(mysql_fetch_array($query) !== false){
										if(checkPrereqs($programArray[$i][$j]->SUBJ." ".$programArray[$i][$j]->CRSE, 
										$schedule, $coursedataarray, $programArray, $yearstanding, $completedcourses, $program, true)){
											array_push($schedule[$winterId], $programArray[$i][$j]);
											$coursedataarray[$i][$j] = 2;
											$wintercount = $wintercount+1;
										}
									}

							}

						}
						else if (strpos($programArray[$i][$j]->SUBJ, "ELECTIVE")!==false)
						{
							$electivecount = $electivecount+1;
						}
					}
					else
					{
						$i = sizeof($programArray);
						break;
					}	
				}
			}
		}
	


		
		$schedule = getNumberOfOccurences($schedule, $programArray);
		
		$falltimetable = array();
		$wintertimetable = array();
		$fallsemester =  scheduleTimetable($schedule, $falltimetable, 0, $program);
		$wintersemester =  scheduleTimetable($schedule, $wintertimetable, 1, $program);
		$result = "<schedule><fall>";
		
		for($i = 0;$i<sizeof($fallsemester);$i++)
		{
			$result .= "<timetable>";
				for($fallIdx = 0; $fallIdx<sizeof($fallsemester[$i]); $fallIdx++){
					$result .= "<course>".$fallsemester[$i][$fallIdx]['subj'].$fallsemester[$i][$fallIdx]['crse']."  ".$fallsemester[$i][$fallIdx]['seq']
							."||Days: ".$fallsemester[$i][$fallIdx]['day']
							."||StartTime: ".$fallsemester[$i][$fallIdx]['starttime']
							."   EndTime: ".$fallsemester[$i][$fallIdx]['endtime']."</course>";
				}

				$result .= "</timetable>";
			
		}
		$result .= "</fall><winter>";	
	
		for($i = 0;$i<sizeof($wintersemester);$i++)
		{

				$result .= "<timetable>";
				for($winterIdx = 0; $winterIdx<sizeof($wintersemester[$i]); $winterIdx++){
					$result .= "<course>".$wintersemester[$i][$winterIdx]['subj'].$wintersemester[$i][$winterIdx]['crse']."  ".$wintersemester[$i][$winterIdx]['seq']
							."||Days: ".$wintersemester[$i][$winterIdx]['day']
							."||StartTime: ".$wintersemester[$i][$winterIdx]['starttime']
							."   EndTime: ".$wintersemester[$i][$winterIdx]['endtime']."</course>";
				}
				$result .= "</timetable>";
			
		}
		$result .= "</winter></schedule>";

		header("content-type: text/xml");
		echo $result;
	
	}
	

	function returnNumberOfCourses($programArray, $semester)
	{
		$count = 0;
		for($i=0;$i<sizeof($programArray[$semester]);$i++)
		{
			if($programArray[$semester][$i]->SUBJ !== "")
			{
				$count=$count+1;
			}
		}
		
		return $count;
	}
	function getLengthOfSemester($semesterId, $semester, $program  )
	{
		if( $program == 'Computer Systems Engineering')
		{
			if($semesterId === 0)
			{
				//fall
				switch($semester)
				{
					case 0:
						return 5;
						
					case 1:
						return 5;
						
					case 2:
						return 6;
						
					case 3:
						return 5;
						
					case 4:
						return 5;
						
					case 5:
						return 5;
						
					case 6:
						return 6;
						
					case 7:
						return 6;
				}
						
					
				
			}
			else
			{
				//winter
				switch($semester)
				{
					case 0:
						return 5;
						
					case 1:
						return 5;
						
					case 2:
						return 5;
						
					case 3:
						return 5;
						
					case 4:
						return 5;
						
					case 5:
						return 5;
						
					case 6:
						return 6;
						
					case 7:
						return 6;
				}
			}
			
		} 
		elseif($program == 'Software Engineering')
		{

					if($semesterId === 0)
			{
				//fall
				switch($semester)
				{
					case 0:
						return 5;
						
					case 1:
						return 5;
						
					case 2:
						return 6;
						
					case 3:
						return 5;
						
					case 4:
						return 5;
						
					case 5:
						return 5;
						
					case 6:
						return 6;
						
					case 7:
						return 6;
				}
						
					
				
			}
			else
			{
				//winter
				switch($semester)
				{
					case 0:
						return 5;
						
					case 1:
						return 5;
						
					case 2:
						return 5;
						
					case 3:
						return 5;
						
					case 4:
						return 5;
						
					case 5:
						return 5;
						
					case 6:
						return 6;
						
					case 7:
						return 6;
				}
			}
		}
		elseif($program == 'Communication Engineering')
		{

					if($semesterId === 0)
			{
				//fall
				switch($semester)
				{
					case 0:
						return 5;
						
					case 1:
						return 5;
						
					case 2:
						return 6;
						
					case 3:
						return 5;
						
					case 4:
						return 5;
						
					case 5:
						return 5;
						
					case 6:
						return 6;
						
					case 7:
						return 6;
				}
						
					
				
			}
			else
			{
				//winter
				switch($semester)
				{
					case 0:
						return 5;
						
					case 1:
						return 5;
						
					case 2:
						return 5;
						
					case 3:
						return 5;
						
					case 4:
						return 5;
						
					case 5:
						return 5;
						
					case 6:
						return 6;
						
					case 7:
						return 6;
				}
			}
		}
		elseif($program == 'Biomedical Engineering')
		{

					if($semesterId === 0)
			{
				//fall
				switch($semester)
				{
					case 0:
						return 5;
						
					case 1:
						return 5;
						
					case 2:
						return 5;
						
					case 3:
						return 5;
						
					case 4:
						return 5;
						
					case 5:
						return 5;
						
					case 6:
						return 6;
						
					case 7:
						return 6;
				}
						
					
				
			}
			else
			{
				//winter
				switch($semester)
				{
					case 0:
						return 5;
						
					case 1:
						return 5;
						
					case 2:
						return 5;
						
					case 3:
						return 5;
						
					case 4:
						return 5;
						
					case 5:
						return 5;
						
					case 6:
						return 6;
						
					case 7:
						return 6;
				}
			}
		}
		
		
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
	
	function isElective($programArray, $subj, $crse)
	{
		for($i = 0;$i<sizeof($programArray);$i++)
		{	
			for($j = 0;$j<sizeof($programArray[$i]);$j++)
			{	
				if($programArray[$i][$j]->SUBJ === $subj && $programArray[$i][$j]->CRSE === $crse)
				{
					return false;
				}
			}
		}
		return true;
	}
	//sort array from least occurences to most to allow for easier time table creation
	function getNumberOfOccurences($schedule, $programArray)
	{
		$fallId = 0;
		$winterId = 1;
		$scheduleOrganized = array(array(), array());
			
		for($i = 0;$i<sizeof($schedule);$i++)
		{
			for($j = 0;$j<sizeof($schedule[$i]);$j++)
			{
				$subj =$schedule[ $i][$j]->SUBJ;
				$crse = $schedule[$i][$j]->CRSE;
				if(strpos($subj, "ELECTIVE")!==false)
				{
					array_push($scheduleOrganized[$i], 1000);
				}
				else if(!isElective($programArray, $subj, $crse))
				{	
					array_push($scheduleOrganized[$i], 1000);
				}
				else
				{
					array_push($scheduleOrganized[$i], numberOfSections($i, $subj, $crse));

				}
				
				
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
	function scheduleTimetable($schedule, $timetable, $semester, $program)
	{
			set_time_limit(600);
			$semesterstring = "";
			if($semester === 0)
			{
				$semesterstring = "falldata";
			}
			else
			{
				$semesterstring = "winterdata";
			}

			$timetables = array();
			$alltimetables = array();
			$alltimetables = getClass($semester, $semesterstring, $schedule, $timetable, $alltimetables, $program);
			//return $alltimetables;
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
function getClass($semester, $semesterstring, $schedule, $timetable, $alltimetables, $program)
	{
		if(sizeof($alltimetables)>100)
		{
			return $alltimetables;
		}
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
				$querylabs = mysql_query("SELECT * FROM ".$semesterstring." WHERE subj='{$subj}' AND crse='{$crse}' AND type!='LEC'") or die(mysql_error());
				$labs = mysql_fetch_array($querylabs);
				$labclass = $labs;
				if($labs === false)
				{
					$temptable = $timetable;
					array_push($temptable, $class); //push the specified class into the array
					array_push($alltimetables, $temptable);
					$alltimetables = getClass($semester, $semesterstring, $schedule, $temptable, $alltimetables, $program);
				}
				
				while($labs !== false )
				{
				
					if(strpos($labs['seq'], $sec)!==false || strpos($labs['seq'], "L")!==false || strpos($labs['seq'], "G")!==false)
					{
						if(hasConflicts($timetable, $labs) )
						{
							
							$temptable = $timetable;
							array_push($temptable, $labs); 
							array_push($temptable, $class); //push the specified class into the array
						//	array_push($alltimetables, $temptable);
							$alltimetables = getClass($semester, $semesterstring, $schedule, $temptable, $alltimetables, $program);
							
						}
					}
					
					$labs = mysql_fetch_array($querylabs); 			
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
		
		if(strlen($newcourseday) === 0)
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
			
			$sameday = false;
			for($k=0;$k<sizeof($oldcoursedayarray);$k++)
			{
				for($j=0;$j<sizeof($newcoursedayarray);$j++)
				{
					if($oldcoursedayarray[$k] === $newcoursedayarray[$j])
					{
						$sameday = true;
					}
				}
			}
			if($sameday)
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