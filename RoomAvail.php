
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
				//Populates the correct room values into the form as the page is loaded
				$("#RoomSubmit").html("Room: "+document.getElementById("RoomSelect").value);
				$("#WeekSubmit").html("Week: "+document.getElementById("Weeks").value);
			});
			
			<?php
				echo "var roomData = ".$json.";\n";
			?>
			
			//ajax to remove the book buttons within the table 
			//Callan Swanson, Inthuch Therdchanakul
			function ajaxFunction() {
			  	var MyForm = $("#options").serializeJSON();
				console.log(MyForm);
				
				//clear the table of any booked rooms
				refreshBooks();
				
				$.ajax( {
					url : "RoomAvailAJAX.php",
					type : "POST", 
					data : {valArray:MyForm},
					success : function (data){
							data = JSON.parse(data);
							console.log(data); //quick check
							for(var i=0; i<data.length; i++) { //looking throught the whole data array to find the correct weeks
								if(document.getElementById("Weeks").value == data[i].week) { 
									for(var j=0; j<data[i].duration; j++) {  //looping through the whole duration of the booked slot
										console.log(data[i].day+(parseInt(data[i].period)+j));
										$("#"+data[i].day+(parseInt(data[i].period)+j)).html("Booked"); //removes the Book button in teh table
									}
								} else {  //default weeks are put in as a 0 in teh relationship table 
									if(parseInt(document.getElementById("Weeks").value) <= 12 && data[i].week == 0) {
										for(var j=0; j<data[i].duration; j++) {  //looping through the whole duration of the booked slot
											console.log(data[i].day+(parseInt(data[i].period)+j));
											$("#"+data[i].day+(parseInt(data[i].period)+j)).html("Booked"); //removes the Book button in teh table
										}
									}
								}
							}
						},
					error : function(jqXHR, textStatus, errorThrown) {
					}
				});
			}
			
			//replaces all the books within teh table so no previously booked rooms are still displayed as booked
			//Callan Swanson, Inthuch Therdchanakul
			function refreshBooks() {
				for(var i=1; i<10; i++) {
					$("#Monday"+i).html("<input type='button' value='Book' id='Monday"+i+"b' onclick='bookClick(this.id)'>");
					$("#Tuesday"+i).html("<input type='button' value='Book' id='Tuesday"+i+"b' onclick='bookClick(this.id)'>");
					$("#Wednesday"+i).html("<input type='button' value='Book' id='Wednesday"+i+"b' onclick='bookClick(this.id)'>");
					$("#Thursday"+i).html("<input type='button' value='Book' id='Thursday"+i+"b' onclick='bookClick(this.id)'>");
					$("#Friday"+i).html("<input type='button' value='Book' id='Friday"+i+"b' onclick='bookClick(this.id)'>");
				}
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
			
			//chnages teh relevant places weeks are located
			function WeekChange() {
				$("#weekChosen").html("Week - "+document.getElementById("Weeks").value);
				$("#WeekSubmit").html("Week: "+document.getElementById("Weeks").value);
			}
			
			//changes the relevant places rooms are located
			function roomChange() {
				$("#RoomSubmit").html("Room: "+document.getElementById("RoomSelect").value);
			}
			
			//when the module code dropdown changed its index, change the module title index with it
			//Callan Swanson
			function module_code_change() {
				var index = document.getElementById("module_code_select").selectedIndex;
				document.getElementById("module_title_select").selectedIndex = index;
			}
			//when the module title dropdown changed its index, change the module code index with it
			//Calan Swanson
			function module_title_change() {
				var index = document.getElementById("module_title_select").selectedIndex;
				document.getElementById("module_code_select").selectedIndex = index;
			}
			
			//will populate the bottom table with values that are chosen from the choice of button
			//Callan Swanson
			function bookClick(buttonId) {
				console.log(buttonId);
				//gets the room id from the top of the page and inputs it into the box at the bottom
				$("#RoomSubmit").html("Room: "+document.getElementById("RoomSelect").value);
				//gets the week from the box at the top and places it into the bottom 
				$("#WeekSubmit").html("Week: "+document.getElementById("Weeks").value);
				//gets the day from the buttonId and places into teh bottom again
				$("#DaySubmit").html("Day: "+buttonId.substr(0, buttonId.length-2));
				//gets the period from the buttonId and finds the time, then places into teh bottom
				$("#PeriodSubmit").html("Period/Time: "+buttonId.substr(buttonId.length-2,1)+" / "+(8+parseInt(buttonId.substr(buttonId.length-2,1)))+":00");
			}
			
			function formValidation() {
				<?php
					$sql = "SELECT room_code, capacity FROM ROOMS";
					$res =& $db->query($sql); //getting the result from the database
					if(PEAR::isError($res)){
						die($res->getMessage());
					}
					
					//put each rows into value array
					while($row = $res->fetchRow()){
						$roomSizes[] = $row[''];
					}
					echo "var roomSizes = ".$roomSizes.";";
				?>
				console.log(document.getElementById("capacity1").value);
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
				<select name="RoomSelect" id="RoomSelect" onChange="ajaxFunction();roomChange()" >
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
					for($i=1; $i<10; $i++) { //describing the table with teh day and the time period
						echo "<td id=Monday".$i.">Book</td>";
					}
				?>
			</tr>
			<tr id="tuesday">
				<td>Tuesday</td>
				<?php
					for($i=1; $i<10; $i++) {
						echo "<td id=Tuesday".$i.">Book</td>";
					}
				?>
			</tr>
			<tr id="wednesday">
				<td>Wednesday</td>
				<?php
					for($i=1; $i<10; $i++) {
						echo "<td id=Wednesday".$i.">Book</td>";
					}
				?>
			</tr>
			<tr id="thursday">
				<td>Thursday</td>
				<?php
					for($i=1; $i<10; $i++) {
						echo "<td id=Thursday".$i.">Book</td>";
					}
				?>
			</tr>
			<tr id="friday">
				<td>Friday</td>
				<?php
					for($i=1; $i<10; $i++) {
						echo "<td id=Friday".$i.">Book</td>";
					}
				?>
			</tr>
		</table>
		
		

		
		
		<div id="inputs">
			<form id="requestForm" action="requestSubmit.php" method="post">
				<table class="inputs">
					<tr>
						<td>
							<a href="RoomAvail.php">here!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!</a><?php echo "Department: ".$username; ////////////////////////////?>
						</td>
					</tr>
					<tr>
						<td>
							<?php
								//will output the whole set of module codes from the database, module codes will change when module titles change
								//Callan Swanson, Inthuch Therdchanakul
								//Scott Marshall: added order by to SQL and name to the <select>. 'module_code_select' is now part of the Form Data
								echo "Module code: <select id='module_code_select' name='module_code_select' onchange='module_code_change()'>";
								$sql = "SELECT module_code FROM MODULES WHERE dept_code='$username' ORDER BY module_code;";
								$res =& $db->query($sql); //getting the result from the database
								if(PEAR::isError($res)){
									die($res->getMessage());
								}
								
								
								while($row = $res->fetchRow()){
									echo "<option>".$row["module_code"]."</option>";
									
								}
								//outputs all the options from the database return result
								echo "</select>";
							?>
						</td>
					</tr>
					<tr>
						<td>
							<?php
								//displays the module titles, titles will change when module codes change
								//Callan Swanson, Inthuch Therdchanakul
								echo "Module title: <select id='module_title_select' name='module_title_select' onchange='module_title_change()' >";
								$sql = "SELECT module_title FROM MODULES WHERE dept_code='$username' ORDER BY module_code;";
								$res =& $db->query($sql); //getting the result from the database
								if(PEAR::isError($res)){
									die($res->getMessage());
								}
								
								
								while($row = $res->fetchRow()){
									echo "<option>".$row["module_title"]."</option>";
									
								}
								echo "</select>";
							?>
						</td>
					</tr>
					
					<tr>
						<td id="capacityCell"> Capacity:
							<input name="capacity" type="text" id="capacity1" onchange="formValidation()" value="" />
						</td>
					</tr>
				
					<tr>
						<td> Special requirements: <br/>
							<textarea name="specialReq" maxlength="1000" placeholder="1000 chars max..."></textarea>
						</td>
					</tr>
					
					<tr>
						<td id="RoomSubmit">
							
						</td>
					</tr>
					
					<tr>
						<td id="WeekSubmit">
							
						</td>
					</tr>
					
					<tr>
						<td id="DaySubmit">
							Day: 
						</td>
					</tr>
					
					<tr>
						<td id="PeriodSubmit">
							Period/Time: 
						</td>
					</tr>
					
					<tr>
						<td>
							<input type="submit" value="Submit" />
						</td>
					</tr>
					
					
				
				</table>
			</form>
		</div>
		

		
		
	</body>

</html>