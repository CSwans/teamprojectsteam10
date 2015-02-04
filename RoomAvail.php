
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
										$("#"+data[i].day+(parseInt(data[i].period)+j)).html("THIS IS TAKEN"); //removes the Book button in teh table
									}
								} else {  //default weeks are put in as a 0 in teh relationship table 
									if(parseInt(document.getElementById("Weeks").value) <= 12 && data[i].week == 0) {
										for(var j=0; j<data[i].duration; j++) {  //looping through the whole duration of the booked slot
											console.log(data[i].day+(parseInt(data[i].period)+j));
											$("#"+data[i].day+(parseInt(data[i].period)+j)).html("THIS IS TAKEN"); //removes the Book button in teh table
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
			function refreshBooks() {
				for(var i=1; i<10; i++) {
					$("#Monday"+i).html("Book");
					$("#Tuesday"+i).html("Book");
					$("#Wednesday"+i).html("Book");
					$("#Thursday"+i).html("Book");
					$("#Friday"+i).html("Book");
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
		
		

		
		<div id="input_wrap">
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
				<td> 
					Day: 
				  <!--radio buttons for the day of the week--> 
				  <!--Scott Marshall: added ids for each element. Day is now part of the Form Data -->
				  
					<input type="radio" name="day" id='monday' value="1"/>
					Monday
					<input type="radio" name="day" id='tuesday' value="2"/>
					Tuesday<br/>
					<input type="radio" name="day" id='wednesday' value="3"/>
					Wednesday
					<input type="radio" name="day" id='thursday' value="4"/>
					Thursday<br/>
					<input type="radio" name="day" id='friday' value="5"/>
					Friday 
				</td>
			</tr>
			<tr>
				<td><!--Checkboxes, using binary to add an independednt value to each week, selectable weeks with weeks 1-12 pre-selected as default--> 
				  <!-- allowing a raneg of weeks to be chosen --> 
				  <!-- Scott Marshall (Still in progress) --> 
					Week: <br/>
				  
				  <!--
							<ol id="week" name="week">
							<li class="ui-state-default ui-selected" value="1">1</li>
							<li class="ui-state-default ui-selected" value="1">2</li>
							<li class="ui-state-default ui-selected" value="1">3</li>
							<li class="ui-state-default ui-selected" value="1">4</li>
							<li class="ui-state-default ui-selected" value="1">5</li>
							<li class="ui-state-default ui-selected" value="1">6</li>
							<li class="ui-state-default ui-selected" value="1">7</li>
							<li class="ui-state-default ui-selected" value="1">8</li>
							<li class="ui-state-default ui-selected" value="1">9</li>
							<li class="ui-state-default ui-selected" value="1">10</li>
							<li class="ui-state-default ui-selected" value="1">11</li>
							<li class="ui-state-default ui-selected" value="1">12</li>
							<li class="ui-state-default" value="1">13</li>
							<li class="ui-state-default" value="1">14</li>
							<li class="ui-state-default" value="1">15</li>
							</ol>
							--> 
					<span class="week_label"> 1 </span>
					<input type="checkbox" name="weeks[]" id="week" value="1" checked="checked" /></input>
					<span class="week_label"> 2 </span>
					<input type="checkbox" name="weeks[]" id="week" value="2" checked="checked" /></input>
					<span class="week_label"> 3 </span>
					<input type="checkbox" name="weeks[]" id="week" value="3" checked="checked" /></input>
					<span class="week_label"> 4 </span>
					<input type="checkbox" name="weeks[]" id="week" value="4" checked="checked" /></input>
					<span class="week_label"> 5 </span>
					<input type="checkbox" name="weeks[]" id="week" value="5" checked="checked" /></input>
					<span class="week_label"> 6 </span>
					<input type="checkbox" name="weeks[]" id="week" value="6" checked="checked" /></input>
					<span class="week_label"> 7 </span>
					<input type="checkbox" name="weeks[]" id="week" value="7" checked="checked" /></input>
					<span class="week_label"> 8 </span>
					<input type="checkbox" name="weeks[]" id="week" value="8" checked="checked" /></input>
					<br/>
					<br/>
					<span class="week_label"> 9 </span>
					<input type="checkbox" name="weeks[]" id="week" value="9" checked="checked" /></input>
					<span class="week_label"> 10 </span>
					<input type="checkbox" name="weeks[]" id="week" value="10" checked="checked" /></input>
					<span class="week_label"> 11 </span>
					<input type="checkbox" name="weeks[]" id="week" value="11" checked="checked" /></input>
					<span class="week_label"> 12 </span>
					<input type="checkbox" name="weeks[]" id="week" value="12" checked="checked" /></input>
					<span class="week_label"> 13 </span>
					<input type="checkbox" name="weeks[]" id="week" value="13" /></input>
					<span class="week_label"> 14 </span>
					<input type="checkbox" name="weeks[]" id="week" value="14" /></input>
					<span class="week_label"> 15 </span>
					<input type="checkbox" name="weeks[]" id="week" value="15" /></input>
					<span class="week_label"> 16 </span>
					<input type="checkbox" name="weeks[]" id="week" value="16" /></input>
				</td>
			</tr>
			<tr>
				<td> Period:
					<?php
						//dropdown for the period, includes the time in 24hr format
						//Callan Swanson
						//Scott Marshall - trigger a re-evaluation of the duration when the period is changed
						echo "<select name='time' id='time' onchange='refill_duration()'>";
						for($i=1;$i<=9;$i++){
							$time = $i+8;
							echo "<option value='".$i."'>".$i." - ".$time.":00</option>";
						}
						echo "</select>";
					?>
				</td>
			</tr>
			<tr>
				<td> Duration:
				  <?php
					//dropdown for the duration
					//Scott Marshall
					echo "<select name='duration' id='duration'>";
					for($i=1;$i<=9;$i++){
						$duration = $i+8;
						echo "<option value='".$i."'>".$i."</option>";
					}
					echo "</select>";
				?></td>
			</tr>
			<tr>
				<td> Special requirements: <br/>
					<textarea name="specialReq" maxlength="1000" placeholder="1000 chars max..."></textarea>
				</td>
			</tr>
			<tr>
				<td> Number of rooms:
					<select id="noRooms" name="noRooms" onchange="showCapacity(); change_room_code();" >
						<option>1</option>
						<option>2</option>
						<option>3</option>
						<option>4</option>
					</select></td>
			  </tr>
			  <tr>
				<td id="capacityCell"> Capacity:
				  <input name="capacity" type="text" id="capacity1" onchange="change_room_code()" value="1" /></td>
			  </tr>
			</table>
			</div>
			<!--inputs-->
			<div id="advance">
			  <table id="advancedinputs">
				<tr>
				  <td> Park:
					<select id="park" name="park" onchange="change_room_code()">
					  <option>Any</option>
					  <option>C</option>
					  <option>E</option>
					  <option>W</option>
					</select></td>
				</tr>
		

		
		
	</body>

</html>