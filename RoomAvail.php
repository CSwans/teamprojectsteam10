
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
		<script src="js/jquery.serializejson.min.js"></script>
	</head>
	<script type="text/javascript">
			
			$(function() {
				
				ParkChange();
				WeekChange();
				ajaxFunction();
			});
			
			<?php
				echo "var roomData = ".$json.";\n";
			?>
			function ajaxFunction() {
			  	var MyForm = $("#options").serializeJSON();
				console.log(MyForm);
				
				$.ajax( {
					url : "RoomAvailAJAX.php",
					type : "POST", 
					data : {valArray:MyForm},
					success : function (data){
							data = JSON.parse(data);
							console.log(data);
							alert(data.length);
							alert(data[0].week + "," + data[0].day);
						},
					error : function(jqXHR, textStatus, errorThrown) {
					}
				});
					}
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
			
			
			
			
		</script>
		
	<body>
		<div>
			<form name="options" id="options" method="POST">
				<a href="timetable.php">here!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!</a>
				Park :-
				<select name="ParkSelect" id="ParkSelect" onChange="ParkChange()">
					<option value="Any">Any</option>
					<option value="C">C</option>
					<option value="E">E</option>
					<option value="W">W</option>
				</select>
				Rooms :-
				<select name="RoomSelect" id="RoomSelect" onChange="ajaxFunction()" >
				</select>
				Week-
				<select name="Weeks" id="Weeks" onChange="WeekChange();ajaxFunction();">
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
						echo "<td id=monday".$i."></td>";
					}
				?>
			</tr>
			<tr id="tuesday">
				<td>Tuesday</td>
				<?php
					for($i=9; $i<18; $i++) {
						echo "<td id=tuesday".$i."></td>";
					}
				?>
			</tr>
			<tr id="wednesday">
				<td>Wednesday</td>
				<?php
					for($i=9; $i<18; $i++) {
						echo "<td id=wednesday".$i."></td>";
					}
				?>
			</tr>
			<tr id="thursday">
				<td>Thursday</td>
				<?php
					for($i=9; $i<18; $i++) {
						echo "<td id=thursday".$i."></td>";
					}
				?>
			</tr>
			<tr id="friday">
				<td>Friday</td>
				<?php
					for($i=9; $i<18; $i++) {
						echo "<td id=friday".$i."></td>";
					}
				?>
			</tr>
		</table>
		
		
	</body>

</html>