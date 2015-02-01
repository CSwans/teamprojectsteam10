<html>

	<head>
		<title>
			Room availability
		</title>
		
		<?php
			//Starts the session, if there is not any sessions then it will transfer to the login page and the user will ave to log in again
			//Inthuch Therdchanakul
			session_start();
			if(!isset($_SESSION['username']) || !isset($_SESSION['password'])) {
				header('Location: login.html');
			}
			//connects to the database using the username and passoword
			require_once "MDB2.php";
			$host = "co-project.lboro.ac.uk"; //host name
			$dbName = "team10"; //database name
			$dsn = "mysql://team10:abg83rew@$host/$dbName"; //login information
			$db =& MDB2::connect($dsn); //connecting to the server and connecting to the database
			if(PEAR::isError($db)){ //if we couldnt connect then end the connection
				die($db->getMessage());
			}
			$db->setFetchMode(MDB2_FETCHMODE_ASSOC);
			//username is the uppercase dept code that was loggged in
			$username = strtoupper($_SESSION['username']);
		?>
		<script src="js/jquery-1.11.1.min.js"></script>
		<script src="js/jquery-ui.js"></script>
		
		<script type="text/javascript">
		
			//finds the park chosen Callan Swanson
			function ParkChange() {
				var parkChosen = "All";
				parkChosen = document.getElementById("ParkSelect").value;
			}
			
		</script>
		
		
	</head>

	<body>
		<div>
			
			<select name="ParkSelect" id="ParkSelect" onchange="">
				<option value="Any">Any</option>
				<option value="C">C</option>
				<option value="E">E</option>
				<option value="W">W</option>
			</select>
			<select name="RoomSelect" id="RoomSelect" >
				<?php
					$sql = "SELECT DISTINCT ROOMS.room_code from ROOMS, PARKS where ParkSelect "
				?>
			</select>
			
		</div>
		
		
	</body>

</html>