<?php
	// Include the connecttion file
	include "connection.php";
	include "courseTable.php";

	/* This page is displayed only if the user logged in */
	
	/* is the cookie set ?*/
	
	if ( ! isset($_COOKIE['login']) ) {
		echo "Need to log in first";
		header("refresh:2;url=view1.php");
		exit;
	}
	
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
		
	// Create an array of php data to be used in JS
	$jsdata = array('courseArray' => $array);
?>

<html>
<head>
	<script type="text/javascript">
	
	var JSDATA = <?= json_encode($jsdata, JSON_HEX_TAG | JSON_HEX_AMP); ?>; // Get php array
	
		function Home(){
			var div4 = document.getElementById("home"),
			div3 = document.getElementById("updateview"),
			div2 = document.getElementById("setstreamview"),
			div1 = document.getElementById("logoutview"),
			div0 = document.getElementById("schedule");
			
			div0.style.display='none';
			div1.style.display='none';
			div2.style.display='none';
			div3.style.display='none';
			div4.style.display='block';
		}
		
		function SetStream(){
			var div4 = document.getElementById("setstreamview"),
			div3 = document.getElementById("updateview"),
			div2 = document.getElementById("home"),
			div1 = document.getElementById("logoutview"),
			div0 = document.getElementById("schedule");
			
			div0.style.display='none';
			div1.style.display='none';
			div2.style.display='none';
			div3.style.display='none';
			div4.style.display='block';
			
		}
		
		function Update(){
			var div4 = document.getElementById("updateview"),
			div3 = document.getElementById("home"),
			div2 = document.getElementById("setstreamview"),
			div1 = document.getElementById("logoutview"),
			div0 = document.getElementById("schedule");
			
			div0.style.display='none';
			div1.style.display='none';
			div2.style.display='none';
			div3.style.display='none';
			div4.style.display='block';

		}
		
		
		function Logout(){
			var div4 = document.getElementById("logoutview"),
			div3 = document.getElementById("updateview"),
			div2 = document.getElementById("setstreamview"),
			div1 = document.getElementById("home"),
			div0 = document.getElementById("schedule");
			
			div0.style.display='none';
			div1.style.display='none';
			div2.style.display='none';
			div3.style.display='none';
			div4.style.display='block';
		}
		
		function GetSchedule(){
			var div4 = document.getElementById("logoutview"),
			div3 = document.getElementById("updateview"),
			div2 = document.getElementById("setstreamview"),
			div1 = document.getElementById("home"),
			div0 = document.getElementById("schedule");
			
			div0.style.display='block';
			div1.style.display='none';
			div2.style.display='none';
			div3.style.display='none';
			div4.style.display='none';
		
		}
		function cancelUpdate(){
			var div = document.getElementById("updateview");
			div.style.display='none';
		}
		
		function cancelLogout(){
			var div = document.getElementById("logoutview");
			div.style.display='none';
		}
		
		function getCourseData(){
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
			
			var elem = document.getElementById("coursedatastring");
			elem.value = strCoursesSelected;
		}
		
		
	</script>
</head>
<body>
	<div style="text-align:center"> 
		<button onclick="Home()">Home</button>
		<button onclick="GetSchedule()">Your Schedule </button>
		<button onclick="Update()">Change password</button>
		<button onclick="Logout()">Logout </button>
		<button onclick="SetStream()">Set your Stream </button>
	</div>
	
	<h2>Welcome <?php echo $login; ?></h2>
	<h3>Your Current Stream is <?php echo $program; ?></h3>
	
	<div id="home" style="display:block">
		<form method="post" id="getcoursedata" action="server.php" onsubmit="return getCourseData()">	
		<?php
			$query = mysql_query("SELECT * FROM userslist WHERE login='{$login}'") or die(mysql_error());

			$row = mysql_fetch_array($query);
			createCourseTable($row['stream'], $row['coursedata']);
		?>
		</form>
		
	</div>
	
	<div id="schedule" style="display:none">
		<div id="viewyourschedule">
		<?php
			$query = mysql_query("SELECT * FROM userslist WHERE login='{$login}'") or die(mysql_error());
			$row = mysql_fetch_array($query);
			
			if($row['fallschedule'] == "" && $row['fallschedule'] == ""){
				echo '<br /><br /><h2> You Have Not Created A Schedule Yet, Head Over to Your "Home" Tab to Build It! </h2>';
			
			} else{
			
				$fallschedule = explode(',', $row['fallschedule']);
				$winterschedule = explode(',', $row['winterschedule']);
				
				echo '<h3>Fall Schedule</h3>';
				for($fallIdx=0; $fallIdx<sizeof($fallschedule); $fallIdx++){
					echo $fallschedule[$fallIdx].'<br />';
				}
				echo '<h3>Winter Schedule</h3>';
				for($winterIdx=0; $winterIdx<sizeof($winterschedule); $winterIdx++){
					echo $winterschedule[$winterIdx].'<br />';
				}
			}
		?>
		</div>
	</div>
	
	<div id="updateview" style="display:none">
		<form method="post" action="server.php">
			
			Old Password : <input type="text" name="oldpassword"/><br/>
			New Password : <input type="text" name="newpassword"/><br/>
			<input type="hidden" value="update" name="typeofrequest"/>
			<input type="submit" value="Change my password"/>
			<button  type="button"  onclick="cancelUpdate()">Cancel</button>
		</form>
	</div>	
	
	
	<div id="logoutview" style="display:none">
		<form method="post" action="server.php">
			Do you want to exit ?
			<input type="hidden" value="logout" name="typeofrequest"/>
			<input type="submit" value="Exit"/>
			<button type="button" onclick="cancelLogout()">Cancel</button>
		</form>
	</div>
	
	
	<div id="setstreamview" style="display:none">
	
		<form method="post" action="server.php">
			<table>			
				<tr>
					<td style="width:300px">Please Select Your Program</td>
					<td>
						<select name="program" id="program">
							<option>Computer Systems Engineering</option>
							<option>Software Engineering</option>
							<option>Communication Engineering</option>
							<option>Biomedical Engineering</option>
						</select>
					</td>
				</tr>
				
				
				<tr>
					<td colspan="2" style="text-align:center">
						<input type="hidden" value="setstream" name="typeofrequest"/>
						<input type="submit" value="Send it to server" style="width:90%"/>
					</td>
				</tr>
			</table>
		</form>
		
	</div>
	
	<div id="schedule"></div>
	
</body>

</html>