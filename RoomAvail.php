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
			
			$sql = "SELECT DISTINCT PARKS.park, ROOMS.room_code FROM ROOMS, PARKS WHERE ROOMS.building_code = PARKS.building_code";
			$res =& $db->query($sql); //getting the result from the database
			if(PEAR::isError($res)){
				die($res->getMessage());
			}
			$value = array();
			//put each rows into value array
			while($row = $res->fetchRow()){
				$value[] = $row;
			}
			$json = json_encode($value);
			
			
		?>
		<script src="js/jquery-1.11.1.min.js"></script>
		<script src="js/jquery-ui.js"></script>
		
		<script>
		$(function() {
			ParkChange();
			WeekChange();
		});
		</script>
		
		
		<script type="text/javascript">
		
			<?php
				echo "var roomData = ".$json.";\n";
			?>
			
			//finds the park chosen Callan Swanson, March Intuch
			function ParkChange() {
				var parkChosen = "Any";
				parkChosen = document.getElementById("ParkSelect").value;
				$("#RoomSelect").empty();
				
				//if any parks are chosen then all the rooms are displayed
				if(parkChosen=="Any") {
					for(var i=0; i<roomData.length; i++) {
						$("#RoomSelect").append("<option> " + roomData[i].room_code + "</option>");
					}
				} else { //if a park is chosen teh jsut that park's rooms are displayed
					for(var i=0; i<roomData.length; i++) {
						if(roomData[i].park == parkChosen) {
							$("#RoomSelect").append("<option> " + roomData[i].room_code + "</option>");
						}
					}
				}
			}
			
			function WeekChange() {
				$("#weekChosen").html("Week - "+document.getElementById("Weeks").value);
			}
			
			function ajaxFunction() {
				var ajaxRequest;  // The variable that makes Ajax possible!
								  // Base taken from http://www.tizag.com/ajaxTutorial/ajax-mysql-database.php
				try{
					// Opera 8.0+, Firefox, Safari
					ajaxRequest = new XMLHttpRequest();
				} catch (e){
					// Internet Explorer Browsers
					try{
						ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
					} catch (e) {
						try{
							ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
						} catch (e){
							// Something went wrong
							alert("Your browser broke!");
							return false;
						}
					}
				}
			
			var week = document.getElementById("Weeks").value;
			var room = document.getElementById("RoomSelect").value;
			var queryString = "?Weeks="+week+"&RoomSelect="+room;
			ajaxRequest.open("GET", "RoomAvailAJAX.php" + queryString, true);
			ajaxRequest.send();
			
			
		</script>
		
		
	</head>

	<body>
		<div>
			<form name="options">
				<a href="timetable.php">here!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!</a>
				Park :-
				<select name="ParkSelect" id="ParkSelect" onchange="ParkChange();ajaxFunction();">
					<option value="Any">Any</option>
					<option value="C">C</option>
					<option value="E">E</option>
					<option value="W">W</option>
				</select>
				Rooms :-
				<select name="RoomSelect" id="RoomSelect" >
				</select>
				Week-
				<select name="Weeks" id="Weeks" onchange="WeekChange();ajaxFunction();">
					<?php
						for($i = 1; $i<=16; $i++) { //displaying 1-16 weeks
							echo "<option>".$i."</option>";
						}
					?>
				</select>
			</form>
		</div>
		
		
		<table>
			<tr id="time">
				<td id="weekChosen"></td>
				<?php
					for($i=9;$i<18;$i++) { //stating the time for booking at the top of the table
						echo "<td>".$i.":00</td>";
					}
				?>
			</tr>
			<tr id="monday">
				<td>Monday</td>
				<?php
					for($i=9; $i<18; $i++) { //describing the table with teh day and the time period
						echo "<td id=monday".$i."></td>"
					}
				?>
			</tr>
			<tr id="tuesday">
				<td>Tuesday</td>
				<?php
					for($i=9; $i<18; $i++) {
						echo "<td id=tuesday".$i."></td>"
					}
				?>
			</tr>
			<tr id="wednesday">
				<td>Wednesday</td>
				<?php
					for($i=9; $i<18; $i++) {
						echo "<td id=wednesday".$i."></td>"
					}
				?>
			</tr>
			<tr id="thursday">
				<td>Thursday</td>
				<?php
					for($i=9; $i<18; $i++) {
						echo "<td id=thursday".$i."></td>"
					}
				?>
			</tr>
			<tr id="friday">
				<td>Friday</td>
				<?php
					for($i=9; $i<18; $i++) {
						echo "<td id=friday".$i."></td>"
					}
				?>
			</tr>
		</table>
		
		
	</body>

</html>