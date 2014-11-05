<html>
<head>
	<script>
		function Create(){
			var div1 = document.getElementById("loginview"),
			div2 = document.getElementById("createview");
			
			div1.style.display='none';
			div2.style.display='block';
		}
		
		function Login(){
			var div1 = document.getElementById("loginview"),
			div2 = document.getElementById("createview");
			
			div1.style.display='block';
			div2.style.display='none';
		}
	</script>
</head>
<body>
	<div style="text-align:center"> 
		<button onclick="Create()">Create account</button>
		<button onclick="Login()">Login </button>
	</div>
	
	<div id="loginview">
		<form method="post" action="server.php">
			Login : <input type="text" name="login"/><br/>
			Password : <input type="text" name="password"/><br/>
			<input type="hidden" value="login" name="typeofrequest"/>
			<input type="submit" value="Login"/>
		</form>
	</div>
	
	<div id="createview" style="display:none">
		<form method="post" action="server.php">
			Login : <input type="text" name="login"/><br/>
			Firstname : <input type="text" name="firstname"/><br/>
			Lastname : <input type="text" name="lastname"/><br/>
			Password : <input type="text" name="password"/><br/>
			Student Number : <input type="number_format" name="studentnumber"/><br/>
			<input type="hidden" value="createaccount" name="typeofrequest"/>
			<input type="submit" value="Create my account"/>
		</form>
	</div>
	
	
	
	
	
	
	
	
	
	
	
	
</body>
</html>