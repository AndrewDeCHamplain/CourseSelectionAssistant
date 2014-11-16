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

		// Create an array of php data to be used in JS
		$jsdata = array('courseArray' => $array, 'courseData' => $courseData);
?>
		
		<script type="text/javascript"> 
			var JSDATA = <?= json_encode($jsdata, JSON_HEX_TAG | JSON_HEX_AMP); ?>; // Get php array
			
			var courseTable = document.getElementById("courseSelectionTable"),
				rowLength = courseTable.rows.length,
				courseArray = JSDATA.courseArray,
				courseDataString = JSDATA.courseData,
				courseData = [],
				courseDataIdx = 0;
			
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
							courseDataIdx++;
							cell.appendChild(checkbox);
				
						}
					}
				}	
			}
		</script>
<?php
	}
?>