<?php

	/*
		The content of the form is sent as an array to php
		named as:
		$_GET[] if the method is GET as defined in method="get" of the form
		$_POST[] is the method is method="POST" in the form
	*/
	$program = $_GET['program'];
	echo "You Selected program $program. ";

	if( $program == 'Computer Systems Engineering'){
		$json = file_get_contents("cseReq.json");
	} 
	elseif($program == 'Software Engineering'){
		$json = file_get_contents("softwareReq.json");
	}
	elseif($program == 'Communication Engineering'){
		$json = file_get_contents("commReq.json");
	}
	elseif($program == 'Biomedical Engineering'){
		$json = file_get_contents("biomedReq.json");
	}
	else{
		echo "Sorry, we don't care about Electrical Engineers";
		exit();
	}
	
	$array = json_decode($json);

    echo '<table  border="1" style="text-align:center">';
    echo '<tr>
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
	
    $numClassesPerSemester = 6;
	$numOfSemesters = 8;	

	
	for($semIdx = 0; $semIdx < $numClassesPerSemester; $semIdx++){
		echo '<tr>';
			for($idx = 0; $idx < $numOfSemesters; $idx++){
				courseTableEntry($idx, $semIdx, $array);
			}
		echo '</tr>';
	
	}
	echo '<tr>
			<td colspan="8" style="text-align:center">
				<input height="2" id="getSchedule" type="submit" value="Get Schedule" style="width:90%"/>
			</td>
		</tr>';
	
    echo '</table>';
	
	
	
	function courseTableEntry($semester, $semIdx, $array){
		if($array[$semester][$semIdx]->SUBJ.$array[$semester][$semIdx]->CRSE !== ""){
			echo '<td>'.$array[$semester][$semIdx]->SUBJ.$array[$semester][$semIdx]->CRSE.
				'<br/><br/><input type="checkbox" name="" value="">Completed?<br></td>';
		} else echo '<td></td>';
	}
?>