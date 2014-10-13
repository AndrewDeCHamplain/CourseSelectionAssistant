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

    echo '<table>';
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

	
	for($semIdx = 0; $semIdx < $numClassesPerSemester; $semIdx++){
		echo '<tr>';
			echo '<td>'.$array[0][$semIdx]->SUBJ.$array[0][$semIdx]->CRSE.'</td>';
			echo '<td>'.$array[1][$semIdx]->SUBJ.$array[1][$semIdx]->CRSE.'</td>';
			echo '<td>'.$array[2][$semIdx]->SUBJ.$array[2][$semIdx]->CRSE.'</td>';
			echo '<td>'.$array[3][$semIdx]->SUBJ.$array[3][$semIdx]->CRSE.'</td>';
			echo '<td>'.$array[4][$semIdx]->SUBJ.$array[4][$semIdx]->CRSE.'</td>';
			echo '<td>'.$array[5][$semIdx]->SUBJ.$array[5][$semIdx]->CRSE.'</td>';
			echo '<td>'.$array[6][$semIdx]->SUBJ.$array[6][$semIdx]->CRSE.'</td>';
			echo '<td>'.$array[7][$semIdx]->SUBJ.$array[7][$semIdx]->CRSE.'</td>';
		echo '</tr>';
	
	}
	
    echo '</table>';
?>