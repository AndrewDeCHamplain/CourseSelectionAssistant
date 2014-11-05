<?php
	/* This page is displayed only if the user logged in */
	
	/* is the cookie set ?*/
	
	if ( ! isset($_COOKIE['login']) ) {
		echo "Need to log in first";
		header("refresh:2;url=view1.php");
		exit;
	}
	
	$login = $_COOKIE['login'];
	
?>

<html>
<head>
	<script>
	
		function SetStream(){
			var div2 = document.getElementById("setstreamview"),
			div1 = document.getElementById("logoutview");
			
			div1.style.display='none';
			div2.style.display='block';
		}
		
		function Update(){
			var div2 = document.getElementById("updateview"),
			div1 = document.getElementById("logoutview");
			
			div1.style.display='none';
			div2.style.display='block';
		}
		
		
		function Logout(){
			var div2 = document.getElementById("updateview"),
			div1 = document.getElementById("logoutview");
			
			div1.style.display='block';
			div2.style.display='none';
		}
		
		function cancelUpdate(){
			var div = document.getElementById("updateview");
			div.style.display='none';
		}
		
		function cancelLogout(){
			var div = document.getElementById("logoutview");
			div.style.display='none';
		}
	</script>
</head>
<body>
	<div style="text-align:center"> 
		<button onclick="Update()">Change password</button>
		<button onclick="Logout()">Logout </button>
		<button onclick="SetStream()">Set your Stream </button>
	</div>
	
	<h2>Welcome <?php echo $login; ?></h2>
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
							<option>Electrical Engineering</option>
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
	
	
</body>

</html>