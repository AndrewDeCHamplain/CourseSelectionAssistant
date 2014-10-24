<?php
	
	echo "<link rel=\"stylesheet\" href=\"style/coursecolors.css\">";
	
	/*
		The content of the form is sent as an array to php
		named as:
		$_GET[] if the method is GET as defined in method="get" of the form
		$_POST[] is the method is method="POST" in the form
	*/
	$program = $_GET['program'];
	echo "You Selected program $program. ";

	if( $program == 'Computer Systems Engineering'){
		$json = file_get_contents("json/cseReq.json");
	} 
	elseif($program == 'Software Engineering'){
		$json = file_get_contents("json/softwareReq.json");
	}
	elseif($program == 'Communication Engineering'){
		$json = file_get_contents("json/commReq.json");
	}
	elseif($program == 'Biomedical Engineering'){
		$json = file_get_contents("json/biomedReq.json");
	}
	else{
		echo "Sorry, we don't care about Electrical Engineers";
		exit();
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
				<input height="2" id="getSchedule" type="submit" value="Get Schedule" style="width:90%"/>
			</td>
		</tr>
	</table>';
	/********** Table has now been made ***********/
	
	for($semIdx = 0; $semIdx < $numClassesPerSemester; $semIdx++){
		echo '<tr>';
			for($idx = 0; $idx < $numOfSemesters; $idx++){
				//courseTableEntry($idx, $semIdx, $array);
			}
		echo '</tr>';
	
	}
?>
	
<script type="text/javascript">

	/******** Populating the table with the selected course data ********/
	
	var courseTable = document.getElementById("courseSelectionTable");
	var rowLength = courseTable.rows.length;
	var courseArray = <?php echo json_encode($array); ?>; // Get php variable
	
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
					cell.appendChild(checkbox);
	
				}
			}
		}	
	}
</script>
<?php
	/*
	for($semIdx = 0; $semIdx < $numClassesPerSemester; $semIdx++){
		echo '<tr>';
			for($idx = 0; $idx < $numOfSemesters; $idx++){
				courseTableEntry($idx, $semIdx, $array);
			}
		echo '</tr>';
	
	}
	*/
	
	function courseTableEntry($semester, $semIdx, $array){
		if($array[$semester][$semIdx]->SUBJ.$array[$semester][$semIdx]->CRSE !== ""){
			echo '<td>'.$array[$semester][$semIdx]->SUBJ.$array[$semester][$semIdx]->CRSE.
				'<br/><br/><input type="checkbox" name="" value="">Completed?<br></td>';
		} else echo '<td></td>';
	}
?>