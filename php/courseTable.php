<?php
	
	echo "<link rel=\"stylesheet\" href=\"../style/coursecolors.css\">";
	
	/*
		The content of the form is sent as an array to php
		named as:
		$_GET[] if the method is GET as defined in method="get" of the form
		$_POST[] is the method is method="POST" in the form
	*/

	function createCourseTable($program, $courseData){

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
		$numClassesPerSemester = 6;
		$numOfSemesters = 8;	

		
		/******* Create table for classes to be displayed in ********/
		echo '<table  id="courseSelectionTable" border="1" style="text-align:center">
		<tr>
			<td colspan="2">First Year</td>
			<td colspan="2">Second Year</td>
			<td colspan="2">Third Year</td>
			<td colspan="2">Fourth Year</td>
		</tr>
		<tr>
			<td>Fall</td>
			<td>Winter</td>
			<td>Fall</td>
			<td>Winter</td>
			<td>Fall</td>
			<td>Winter</td>
			<td>Fall</td>
			<td>Winter</td>
		</tr>';
		
		for($classIdx = 0; $classIdx < $numClassesPerSemester; $classIdx++){ // create rows
			echo '<tr>';
			for($semIdx = 0; $semIdx < $numOfSemesters; $semIdx++){ // create semesters
				echo '<td></td>';
			}
			echo '</tr>';
		}
		echo '<tr>  
				<td colspan="8" style="text-align:center">
					<input height="2" id="getSchedule" type="button" value="Get Schedule" style="width:90%" onclick="getCourseSchedule()"/>
				</td>
			</tr>
		</table>';
		/********** Table has now been made ***********/
		
		//**********ADDED A DIV FOR ELECTIVES
		echo '</br></br>';
		echo '<div id="electiveDiv"></div>';
		//**********************************

		// Create an array of php data to be used in JS
		$jsdata = array('courseArray' => $array, 'courseData' => $courseData, 'program'=>$program);
?>
		
		<script type="text/javascript"> 
			var JSDATA = <?= json_encode($jsdata, JSON_HEX_TAG | JSON_HEX_AMP); ?>; // Get php array
			
			var courseTable = document.getElementById("courseSelectionTable"),
				rowLength = courseTable.rows.length,
				courseArray = JSDATA.courseArray,
				courseDataString = JSDATA.courseData,
				courseData = [],
				courseDataIdx = 0,
				program = JSDATA.program;
			
			//***********ADDED ELECTIVE ARRAY
			var electiveCourses = [];
			//****************************
			// Get the courseData from a string into an array
			for (var i = 0, length = courseDataString.length; i < length; i += 1) {
				courseData.push(+courseDataString.charAt(i));
			}
			
			for(var classIdx=0; classIdx<rowLength; classIdx++){
				var row = courseTable.rows[classIdx];
				
				if(classIdx > 1 && classIdx < 8){
					var cellLength = row.cells.length;
					
					for(var semIdx = 0; semIdx < cellLength; semIdx++){
						var cell = row.cells[semIdx];
						if(courseArray[semIdx][classIdx-2].SUBJ + courseArray[semIdx][classIdx-2].CRSE !== ""){
							if(courseArray[semIdx][classIdx-2].SUBJ.indexOf("ELECTIVE") > -1){
								//*****ADDED DISPLAY FOR ENDING OF ELECTIVES
								cell.innerHTML = courseArray[semIdx][classIdx-2].SUBJ + courseArray[semIdx][classIdx-2].CRSE;
								//*************************************
								cell.className = (courseArray[semIdx][classIdx-2].SUBJ).replace(/\s+/g, '');
								
								var elecInfoArray = courseArray[semIdx][classIdx-2].CRSE.split(',');
								var electiveNum = elecInfoArray[0];
								var electiveNote = elecInfoArray[1];
								
								
								var checkbox = document.createElement('input');
								checkbox.type = "checkbox";
								checkbox.value = "Completed?";
								checkbox.id = "checkbox" + courseArray[semIdx][classIdx-2].SUBJ + courseArray[semIdx][classIdx-2].CRSE;
								
								if(courseData[courseDataIdx] === 1){
									checkbox.checked = true;
								} else {
									checkbox.checked = false;
								}
								cell.appendChild(checkbox);
								//************** ADDED PUSHING TO ELECTIVE ARRAY
								electiveCourses.push([""+courseArray[semIdx][classIdx-2].SUBJ, ""+courseArray[semIdx][classIdx-2].CRSE ]);
								//****************************
								
							} else {
								cell.innerHTML = courseArray[semIdx][classIdx-2].SUBJ + courseArray[semIdx][classIdx-2].CRSE;
								cell.className = courseArray[semIdx][classIdx-2].SUBJ;
								
								var checkbox = document.createElement('input');
								checkbox.type = "checkbox";
								checkbox.value = "Completed?";
								checkbox.id = "checkbox" + courseArray[semIdx][classIdx-2].SUBJ + courseArray[semIdx][classIdx-2].CRSE;
								
								if(courseData[courseDataIdx] === 1){
									checkbox.checked = true;
								} else {
									checkbox.checked = false;
								}
								cell.appendChild(checkbox);
							}
							
						}
						courseDataIdx++;
					}
				}	
			}
			//*******************ADDED DYNAMIC OPTIONS FOR ELECTIVES
			//console.log(electiveCourses);
			var myDiv = document.getElementById("electiveDiv");
		//	console.log(electiveCourses.length)
			for(var i=0;i<electiveCourses.length;i++)
			{
			
				//Create array of options to be added
				var splitcode = (electiveCourses[i][1].split("-"));
			
				var array = getElectiveInfo(program, splitcode[1])
			//	console.log("i="+i+" elec array "+JSON.stringify(array));
				//Create and append select list
				var selectList = document.createElement("select");
				selectList.id = "electiveOption"+i;
				selectList.name = "electiveOption";
				//console.log("electives: "+electiveCourses[i][0]+" "+electiveCourses[i][1]);
			
				myDiv.appendChild(selectList);

				//Create and append the options
				for (var j = 0; j < array.length; j++) {
					var option = document.createElement("option");
					option.value = array[j];
					option.text = array[j];
					selectList.appendChild(option);
				}
				selectList.label = electiveCourses[i][0]+" "+electiveCourses[i][1];
			}
			//**********************************************
	
	
		
		</script>
<?php
	}
?>

<script type="text/javascript"> 


	
	function getCourseSchedule(){
		
		// Update the users courseData in the database so it can be used by the server when building the schedule.
		var courseData = currentData(), xmlHttp, fallIndex = 0, winterIndex = 1;

		if(window.XMLHttpRequest){
			xmlHttp= new XMLHttpRequest();
		}else {
			xmlHttp = new ActiveXObject("Microsoft.XMLHTTP")
		}
		
		
		xmlHttp.open("POST", "server.php", true);
		xmlHttp.setRequestHeader("content-type", "application/x-www-form-urlencoded");
		xmlHttp.onreadystatechange = function() {
			if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
				// response from the server is received
				
				// XML
				
				var response = 	xmlHttp.responseXML; // responseXML or responseText
				var schedule = response.documentElement.childNodes;
				
				var fall = schedule[fallIndex];
				var winter = schedule[winterIndex];
				
				/* 
				* Creates a select menu for all the classes in the fall semester, this will be altered to work 
				* with all the possible electives that the student will be able to take.
				*/
				console.log(fall);
				console.log(winter);
			/*	document.getElementById("schedule").innerHTML += "<p> Fall schedule <br /><select id='optsfall'>";
				for(var i=0; i <fall.childNodes.length; i++){
					
					var info = fall.childNodes[i].innerHTML;
					for(var j=0; j <info.childNodes.length; j++)
					{
						var course = info.childNodes[j].innerHTML;
						document.getElementById("optsfall").innerHTML += "<option value='0'>"+course+"</option>";
					}					
				}
				document.getElementById("schedule").innerHTML += "</select></p><p> Winter Schedule<br /><select id='optswinter'>";
				
				for(var i=0; i <winter.childNodes.length; i++){
					
					var info = winter.childNodes[i].innerHTML;
					for(var j=0; j <info.childNodes.length; j++)
					{
						var course = info.childNodes[j].innerHTML;
						document.getElementById("optswinter").innerHTML += "<option value='0'>"+course+"</option>";
					}					
				}
				
				document.getElementById("schedule").innerHTML += "<p> Fall schedule";
				for(var i=0; i <fall.childNodes.length; i++){
					
					var info = fall.childNodes[i].innerHTML;
					document.getElementById("schedule").innerHTML += "<b>Class #: "+info +"<br/>";
				}
				document.getElementById("schedule").innerHTML += "</p><p> Winter Schedule";
				for(var i=0; i <winter.childNodes.length; i++){

					var info = winter.childNodes[i].innerHTML;
					document.getElementById("schedule").innerHTML += "<b>Class #: "+info +"<br/>";
						
				}*/
				
				//console.log(response);
			}
		};
		
		// Sending the courseData with the XMLHttpRequest
		
		
		
		//************ADDED SENDING ELECTIVE INFO ALONG WITH HTTP POST
		var elements = document.getElementsByName('electiveOption');
		//console.log(elements[0]);
		//console.log("element length = " +elements.length);
		var electiveString = "";
		for(var i =0;i<elements.length;i++)
		{
			
			electiveString = electiveString + "&electives[]="+elements[i].options[elements[i].selectedIndex].text;
		}
		
		//console.log(electiveString);
		//****************************************************
		
		
		
		xmlHttp.send("typeofrequest=getschedule&coursedata="+courseData+""+electiveString);

	}
	
	function currentData(){
	
	<?php
	
		$login = $_COOKIE['login'];
		$query = mysql_query("SELECT * FROM userslist WHERE login='{$login}'") or die(mysql_error());
		$row = mysql_fetch_array($query);
		$program = $row['stream'];
	
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
		$numClassesPerSemester = 6;
		$numOfSemesters = 8;
	?>
		var courseTable = document.getElementById('courseSelectionTable');
		var rowLength = courseTable.rows.length;
		var courseArray = <?php echo json_encode($array); ?>; // Get php variable
		
		var coursesSelected = [];	// variable to be returned, initially 0
		var selectedIdx = 0;  		// will be used to idx the coursesSelected
		
		for(var classIdx=0; classIdx<rowLength; classIdx++){
			var row = courseTable.rows[classIdx];
			
			if(classIdx > 1 && classIdx < 8){
				var cellLength = row.cells.length;
				
				for(var semIdx = 0; semIdx < cellLength; semIdx++){
					var cell = row.cells[semIdx];
					if(courseArray[semIdx][classIdx-2].SUBJ + courseArray[semIdx][classIdx-2].CRSE !== ""){
					
						if(document.getElementById("checkbox" + courseArray[semIdx][classIdx-2].SUBJ + courseArray[semIdx][classIdx-2].CRSE).checked){
							coursesSelected[selectedIdx] = 1;
						} else {
							coursesSelected[selectedIdx] = 0;
						}
						selectedIdx++;
						
					}
					else if(courseArray[semIdx][classIdx-2].SUBJ + courseArray[semIdx][classIdx-2].CRSE == "")
					{
						coursesSelected[selectedIdx] = 9;
						selectedIdx++;
					}
				}
			}	
		}
		var strCoursesSelected='';
		for (i in coursesSelected){
			strCoursesSelected+=coursesSelected[i].toString();
		}
		return strCoursesSelected;
	}

	function getElectiveInfo(program, electiveNote){
	
		var elecArray = [];
		
		if(program === "Computer Systems Engineering"){
			switch(electiveNote){
				case "GS":
					elecArray = ['AFRI 1001', 'AFRI 1002', 'AFRI 2001', 'ALDS 1001', 'ALDS 2201', 'ALDS 2202', 'ALDS 2203', 'ALDS 2701',
						'ALDS 2705', 'ANTH 1001', 'ANTH 1002', 'ANTH 2020', 'ANTH 2040', 'ANTH 2550', 'ANTH 2610', 'ANTH 2620', 'ANTH 2630',
						'ANTH 2640', 'ANTH 2670', 'ANTH 2850', 'ARTH 1100', 'ARTH 1101', 'ARTH 1105', 'ARTH 1200', 'ARTH 1201', 'ARTH 2002',
						'ARTH 2006', 'ARTH 2007', 'ARTH 2102', 'ARTH 2202', 'ARTH 2310', 'ARTH 2405', 'ARTH 2510', 'ARTH 2600', 'ARTH 2601',
						'ARTH 2610', 'BUSI 2101', 'CDNS 1101', 'CDNS 2000', 'CDNS 2300', 'CDNS 2400', 'CGSC 1001', 'CGSC 2001', 'CHST 1000',
						'CHST 1002', 'CLCV 1002', 'ENGL 2005', 'ENGL 2011', 'ENGL 2100', 'ENGL 2102', 'FILM 2101', 'FILM 2106', 'FILM 2201',
						'HIST 2001', 'HIST 2002', 'HIST 2103', 'HIST 2107', 'HIST 2109', 'HUMR 2001', 'HUMR 2202', 'JOUR 2106', 'LAWS 2105',
						'LAWS 2105', 'LAWS 2201', 'LAWS 2202', 'LING 1100', 'MUSI 1001', 'MUSI 1002', 'MUSI 2005', 'MUSI 2007', 'MUSI 2100',
						'PHIL 1000', 'PHIL 1200', 'PHIL 1301', 'PHIL 2001', 'PHIL 2103', 'PHIL 2510', 'RELI 1710', 'RELI 1730', 'RELI 1731', 
						'RELI 2110', 'RELI 2220', 'SOCI 1001', 'SOCI 1002', 'SOCI 2010', 'SOCI 2020', 'SOCI 2050', 'TSES 3001', 'WGST 2800'];
					return elecArray;
				case "NB":
					elecArray = [
						'MECH 4503', 'ECOR 2606', 'SYSC 3006', 'SYSC 3101', 'SYSC 3120', 'SYSC 3200', 'SYSC 3203', 'SYSC 4005',
						'SYSC 4102', 'SYSC 4106', 'SYSC 4202', 'SYSC 4205', 'SYSC 4405', 'SYSC 4505', 'SYSC 4607',
						'SYSC 4700', 'ELEC 3105', 'ELEC 3508', 'ELEC 3509', 'ELEC 3605', 'ELEC 3908', 'ELEC 3909', 
						'ELEC 4502', 'ELEC 4503', 'ELEC 4505', 'ELEC 4506', 'ELEC 4509', 'ELEC 4600', 'ELEC 4601', 
						'ELEC 4602', 'ELEC 4609', 'ELEC 4700', 'ELEC 4702', 'ELEC 4703', 'ELEC 4704', 'ELEC 4705', 
						'ELEC 4706', 'ELEC 4708', 'ELEC 4709'
						];
					return elecArray;
				default:
					alert("JSON has been edited, specify the type of Elective");
			}
		} else if (program === "Software Engineering"){
			switch(electiveNote){
				case "GS":
					elecArray = ['AFRI 1001', 'AFRI 1002', 'AFRI 2001', 'ALDS 1001', 'ALDS 2201', 'ALDS 2202', 'ALDS 2203', 'ALDS 2701',
						'ALDS 2705', 'ANTH 1001', 'ANTH 1002', 'ANTH 2020', 'ANTH 2040', 'ANTH 2550', 'ANTH 2610', 'ANTH 2620', 'ANTH 2630',
						'ANTH 2640', 'ANTH 2670', 'ANTH 2850', 'ARTH 1100', 'ARTH 1101', 'ARTH 1105', 'ARTH 1200', 'ARTH 1201', 'ARTH 2002',
						'ARTH 2006', 'ARTH 2007', 'ARTH 2102', 'ARTH 2202', 'ARTH 2310', 'ARTH 2405', 'ARTH 2510', 'ARTH 2600', 'ARTH 2601',
						'ARTH 2610', 'BUSI 2101', 'CDNS 1101', 'CDNS 2000', 'CDNS 2300', 'CDNS 2400', 'CGSC 1001', 'CGSC 2001', 'CHST 1000',
						'CHST 1002', 'CLCV 1002', 'ENGL 2005', 'ENGL 2011', 'ENGL 2100', 'ENGL 2102', 'FILM 2101', 'FILM 2106', 'FILM 2201',
						'HIST 2001', 'HIST 2002', 'HIST 2103', 'HIST 2107', 'HIST 2109', 'HUMR 2001', 'HUMR 2202', 'JOUR 2106', 'LAWS 2105',
						'LAWS 2105', 'LAWS 2201', 'LAWS 2202', 'LING 1100', 'MUSI 1001', 'MUSI 1002', 'MUSI 2005', 'MUSI 2007', 'MUSI 2100',
						'PHIL 1000', 'PHIL 1200', 'PHIL 1301', 'PHIL 2001', 'PHIL 2103', 'PHIL 2510', 'RELI 1710', 'RELI 1730', 'RELI 1731', 
						'RELI 2110', 'RELI 2220', 'SOCI 1001', 'SOCI 1002', 'SOCI 2010', 'SOCI 2020', 'SOCI 2050', 'TSES 3001', 'WGST 2800'];
					return elecArray;
				case "NA":
					elecArray = ['SYSC 3200', 'SYSC 3600', 'SYSC 3601', 'SYSC 4102', 'SYSC 4502', 'SYSC 4604',
						'SYSC 4602', 'ELEC 2507', 'ELEC 4708', 'ELEC 4509', 'ELEC 4506'];
					return elecArray;
				case "NB":
					elecArray = ['SYSC 4105', 'SYSC 4107', 'COMP 3002', 'COMP 4000', 'COMP 4001', 'COMP 4002', 'COMP 4003', 'COMP 4106'];
					return elecArray;
				default:
					alert("JSON has been edited, specify the type of Elective");
			}
		
		} else if (program === "Communication Engineering"){
			switch(electiveNote){
				case "GS":
					elecArray = ['AFRI 1001', 'AFRI 1002', 'AFRI 2001', 'ALDS 1001', 'ALDS 2201', 'ALDS 2202', 'ALDS 2203', 'ALDS 2701',
						'ALDS 2705', 'ANTH 1001', 'ANTH 1002', 'ANTH 2020', 'ANTH 2040', 'ANTH 2550', 'ANTH 2610', 'ANTH 2620', 'ANTH 2630',
						'ANTH 2640', 'ANTH 2670', 'ANTH 2850', 'ARTH 1100', 'ARTH 1101', 'ARTH 1105', 'ARTH 1200', 'ARTH 1201', 'ARTH 2002',
						'ARTH 2006', 'ARTH 2007', 'ARTH 2102', 'ARTH 2202', 'ARTH 2310', 'ARTH 2405', 'ARTH 2510', 'ARTH 2600', 'ARTH 2601',
						'ARTH 2610', 'BUSI 2101', 'CDNS 1101', 'CDNS 2000', 'CDNS 2300', 'CDNS 2400', 'CGSC 1001', 'CGSC 2001', 'CHST 1000',
						'CHST 1002', 'CLCV 1002', 'ENGL 2005', 'ENGL 2011', 'ENGL 2100', 'ENGL 2102', 'FILM 2101', 'FILM 2106', 'FILM 2201',
						'HIST 2001', 'HIST 2002', 'HIST 2103', 'HIST 2107', 'HIST 2109', 'HUMR 2001', 'HUMR 2202', 'JOUR 2106', 'LAWS 2105',
						'LAWS 2105', 'LAWS 2201', 'LAWS 2202', 'LING 1100', 'MUSI 1001', 'MUSI 1002', 'MUSI 2005', 'MUSI 2007', 'MUSI 2100',
						'PHIL 1000', 'PHIL 1200', 'PHIL 1301', 'PHIL 2001', 'PHIL 2103', 'PHIL 2510', 'RELI 1710', 'RELI 1730', 'RELI 1731', 
						'RELI 2110', 'RELI 2220', 'SOCI 1001', 'SOCI 1002', 'SOCI 2010', 'SOCI 2020', 'SOCI 2050', 'TSES 3001', 'WGST 2800'];
					return elecArray;
				case "NA":
					elecArray = ['GS'];
					return elecArray;
				case "NC":
					elecArray = ['COMM'];
					return elecArray;
				case "ND":
					relecArray = [
						'SYSC 3006', 'SYSC 3101', 'SYSC 3120', 'SYSC 3200', 'SYSC 3203', 'SYSC 4005',
						'SYSC 4102', 'SYSC 4106', 'SYSC 4202', 'SYSC 4205', 'SYSC 4505', 'SYSC 4607',
						'ELEC 3105', 'ELEC 3508', 'ELEC 3605', 'ELEC 3908', 'ELEC 4502', 'ELEC 4503', 
						'ELEC 4505', 'ELEC 4506', 'ELEC 4509', 'ELEC 4600', 'ELEC 4601', 'ELEC 4602', 
						'ELEC 4609', 'ELEC 4700', 'ELEC 4702', 'ELEC 4703', 'ELEC 4704', 'ELEC 4705', 
						'ELEC 4706', 'ELEC 4708', 'ELEC 4709'
						];
					return elecArray;
				default:
					alert("JSON has been edited, specify the type of Elective");
			}
		
		} else if (program === "Biomedical Engineering"){
			switch(electiveNote){	
				case "GS":
					elecArray = ['AFRI 1001', 'AFRI 1002', 'AFRI 2001', 'ALDS 1001', 'ALDS 2201', 'ALDS 2202', 'ALDS 2203', 'ALDS 2701',
						'ALDS 2705', 'ANTH 1001', 'ANTH 1002', 'ANTH 2020', 'ANTH 2040', 'ANTH 2550', 'ANTH 2610', 'ANTH 2620', 'ANTH 2630',
						'ANTH 2640', 'ANTH 2670', 'ANTH 2850', 'ARTH 1100', 'ARTH 1101', 'ARTH 1105', 'ARTH 1200', 'ARTH 1201', 'ARTH 2002',
						'ARTH 2006', 'ARTH 2007', 'ARTH 2102', 'ARTH 2202', 'ARTH 2310', 'ARTH 2405', 'ARTH 2510', 'ARTH 2600', 'ARTH 2601',
						'ARTH 2610', 'BUSI 2101', 'CDNS 1101', 'CDNS 2000', 'CDNS 2300', 'CDNS 2400', 'CGSC 1001', 'CGSC 2001', 'CHST 1000',
						'CHST 1002', 'CLCV 1002', 'ENGL 2005', 'ENGL 2011', 'ENGL 2100', 'ENGL 2102', 'FILM 2101', 'FILM 2106', 'FILM 2201',
						'HIST 2001', 'HIST 2002', 'HIST 2103', 'HIST 2107', 'HIST 2109', 'HUMR 2001', 'HUMR 2202', 'JOUR 2106', 'LAWS 2105',
						'LAWS 2105', 'LAWS 2201', 'LAWS 2202', 'LING 1100', 'MUSI 1001', 'MUSI 1002', 'MUSI 2005', 'MUSI 2007', 'MUSI 2100',
						'PHIL 1000', 'PHIL 1200', 'PHIL 1301', 'PHIL 2001', 'PHIL 2103', 'PHIL 2510', 'RELI 1710', 'RELI 1730', 'RELI 1731', 
						'RELI 2110', 'RELI 2220', 'SOCI 1001', 'SOCI 1002', 'SOCI 2010', 'SOCI 2020', 'SOCI 2050', 'TSES 3001', 'WGST 2800'];
					return elecArray;
				case "NA":
					elecArray = ['BIOL 2201', 'BIOL 2005', 'CHEM 2203'];
					return elecArray;
				case "NB":
					elecArray = ['ELEC 4709', 'SYSC 4202', 'SYSC 4205'];
					return elecArray;
				case "NC":
					elecArray = ['SYSC 3101', 'SYSC 3200', 'SYSC 3303', 'SYSC 3500', 'SYSC 3503', 'SYSC 3600', 'SYSC 3601', 'SYSC 4001', 
						'SYSC 4101', 'SYSC 4005', 'SYSC 4005', 'SYSC 4106', 'SYSC 4120', 'SYSC 4502', 'SYSC 4504', 'SYSC 4505', 'SYSC 4507', 
						'SYSC 4600', 'SYSC 4602', 'SYSC 4604', 'SYSC 4607', 'SYSC 4700', 'ELEC 3508', 'ELEC 3509', 'ELEC 3605', 'ELEC 3908', 
						'ELEC 3909', 'ELEC 4502', 'ELEC 4503', 'ELEC 4505', 'ELEC 4506', 'ELEC 4609', 'ELEC 4702', 'ELEC 4703', 'ELEC 4704', 
						'ELEC 4706', 'ELEC 4707', 'ELEC 4708', 'ELEC 4709'];
					return elecArray;
				default:
					alert("JSON has been edited, specify the type of Elective");
			}
		
		} else {
			alert("No Stream selected, how did you even do this? I'm truly curious because that should not be possible!");
		}
	
	}
</script>