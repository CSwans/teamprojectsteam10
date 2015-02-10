<html>
	<head>

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
		//retrieve rooms data from database and put them in javacript array using json
		$sql = "SELECT DISTINCT PARKS.park, ROOMS.room_code, ROOMS.building_code, ROOMS.capacity, ROOMS.wheelchair, ROOMS.projector, ROOMS.visualiser, ROOMS.whiteboard FROM ROOMS,PARKS WHERE ROOMS.building_code = PARKS.building_code";
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
		
		$sql = "SELECT module_code, module_title FROM MODULES WHERE dept_code='$username' ORDER BY module_code;";
			$res =& $db->query($sql); //getting the result from the database
			if(PEAR::isError($res)){
				die($res->getMessage());
			}
			$moduleInfo = array();
			while($row = $res->fetchRow()){
				$moduleInfo[] = $row;
			}
			$moduleJson = json_encode($moduleInfo);
			//retrieveing info about the modules and their titles
			
		//Initialise list of bookings to take into account when populating room pref lists
		//Tom Middleton		
		$sql = "SELECT REQUEST.request_id, REQUEST.day, REQUEST.period, REQUEST.duration, REQUEST_WEEKS.week, BOOKING.room_code FROM REQUEST, BOOKING, REQUEST_WEEKS WHERE REQUEST.request_id = REQUEST_WEEKS.request_id AND REQUEST.request_id = BOOKING.request_id ";
		$res =& $db->query($sql); //getting the result from the database
		if(PEAR::isError($res)){
			die($res->getMessage());
		}
		$bookingInfo[] = array();
		//put each rows into value array
		while($row = $res->fetchRow()){
			$bookingInfo[] = $row;
		}
		$bookingJson = json_encode($bookingInfo);
		
			
			
			
			
	?>
	<script type="text/javascript">
		<?php
			//pass value array onto javascript array roomData
			echo "var roomData = ". $json . ";\n";
			echo "var moduleData = ". $moduleJson . ";\n";
			echo "var bookingData = " .$bookingJson. ";\n";
		?>
	</script>
	
		<link rel="stylesheet" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css"/>
		<script src="//code.jquery.com/jquery-1.10.2.js"></script>
		<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
		<link rel="stylesheet" href="/resources/demos/style.css"/>
		<link rel="stylesheet" href="Theme.css"/>
	
	</head>
	<body>
<form id="requestForm" action="requestSubmit.php" method="post">
		<div class="input_boxes" >
        <div id="buttons">
			<div id="button_wrap1">
				<a><button id="adv_options" type="button" onclick="advToggle();"> &gt; &nbsp;&nbsp;&nbsp;&nbsp;SHOW ADVANCED OPTIONS</button></a>
				<a href="ViewRequests.php"><button id ="All" type="button" >&gt;&nbsp;&nbsp;&nbsp;&nbsp;VIEW ALL ENTRIES </button></a>
				<a><button type="button">&gt;&nbsp;&nbsp;&nbsp;&nbsp;CHECK AVAILABILITY</button></a>
				<a><button id="Load_Last_Year" type="button" > &gt; &nbsp;&nbsp;&nbsp;&nbsp;LOAD REQUESTS</button></a>
			</div>
        </div>
		<div id="input_wrap">
			<div id="inputs">
			
				<table class="inputs">
					<tr>
						<td>
							Priority: 
							<input name="priorityInput" type="radio" id="priorityInput" onchange="change_room_code()" value="1"/>Yes
							<input name="priorityInput" type="radio" id="priorityInput" onchange="change_room_code()" value="0"/>No
						</td>
					</tr>
					<tr>
						<td>
							Part: 
							<input type='radio' name='partCode' id='allPart' checked='checked' value='All' onchange='partChange()'> All 
							<input type='radio' name='partCode' id='aPart' value='A' onchange='partChange()' > A 
							<input type='radio' name='partCode' id='bPart' value='B' onchange='partChange()'> B 
							<input type='radio' name='partCode' id='iPart' value='I' onchange='partChange()'> I 
							<input type='radio' name='partCode' id='cPart' value='C' onchange='partChange()'> C 
							<input type='radio' name='partCode' id='dPart' value='D' onchange='partChange()'> D
						</td> 
					</tr>

					<!--<tr>-->
					<tr>
						<td>
							<?php
								//will output the whole set of module codes from the database, module codes will change when module titles change
								//Callan Swanson, Inthuch Therdchanakul
								//Scott Marshall: added order by to SQL and name to the <select>. 'module_code_select' is now part of the Form Data
								echo "Module code: <select id='module_code_select' name='module_code_select' onchange='module_code_change()'>";
								
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
							
								echo "</select>";
							?>
						</td>
					</tr>
					<tr>
						<td> 
							Day: 
						  <!--radio buttons for the day of the week--> 
						  <!--Scott Marshall: added ids for each element. Day is now part of the Form Data -->
						  
							<input onchange="change_room_code();" type="radio" name="day" id='monday' value="1" required/>
							Monday
							<input onchange="change_room_code();" type="radio" name="day" id='tuesday' value="2" required/>
							Tuesday<br/>
							<input onchange="change_room_code();" type="radio" name="day" id='wednesday' value="3" required/>
							Wednesday
							<input onchange="change_room_code();" type="radio" name="day" id='thursday' value="4" required/>
							Thursday<br/>
							<input onchange="change_room_code();" type="radio" name="day" id='friday' value="5" required/>
							Friday 
						</td>
					</tr>
					<tr>
						<td><!--Checkboxes, using binary to add an independednt value to each week, selectable weeks with weeks 1-12 pre-selected as default--> 
						  <!-- allowing a raneg of weeks to be chosen --> 
							Week: <br/>

							<span class="week_label"> 1 </span>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week" value="1" checked="checked" /></input>
							<span class="week_label"> 2 </span>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week" value="2" checked="checked" /></input>
							<span class="week_label"> 3 </span>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week" value="3" checked="checked" /></input>
							<span class="week_label"> 4 </span>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week" value="4" checked="checked" /></input>
							<span class="week_label"> 5 </span>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week" value="5" checked="checked" /></input>
							<span class="week_label"> 6 </span>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week" value="6" checked="checked" /></input>
							<span class="week_label"> 7 </span>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week" value="7" checked="checked" /></input>
							<span class="week_label"> 8 </span>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week" value="8" checked="checked" /></input>
							<br/>
							<br/>
							<span class="week_label"> 9 </span>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week" value="9" checked="checked" /></input>
							<span class="week_label"> 10 </span>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week" value="10" checked="checked" /></input>
							<span class="week_label"> 11 </span>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week" value="11" checked="checked" /></input>
							<span class="week_label"> 12 </span>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week" value="12" checked="checked" /></input>
							<span class="week_label"> 13 </span>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week" value="13" /></input>
							<span class="week_label"> 14 </span>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week" value="14" /></input>
							<span class="week_label"> 15 </span>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week" value="15" /></input>
							<span class="week_label"> 16 </span>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week" value="16" /></input>
						</td>
					</tr>
					<tr>
						<td> Period:
							<?php
								//dropdown for the period, includes the time in 24hr format
								//Callan Swanson
								//Scott Marshall - trigger a re-evaluation of the duration when the period is changed
								echo "<select name='time' id='time' onchange='refill_duration(); change_room_code();'>";
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
								echo "<select name='duration' id='duration' onchange='change_room_code();'>";
								for($i=1;$i<=9;$i++){
									$duration = $i+8;
									echo "<option value='".$i."'>".$i."</option>";
								}
								echo "</select>";
							?>
						</td>
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
							</select>
						</td>
					</tr>
					<tr>
						<td id="capacityCell"> Capacity:
							<input name="capacity" type="text" id="capacity1" onchange="change_room_code()" value="1"/>
						</td>
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
					<tr>
					  <td id="room_col"><!--Scott Marshall: added in empty select so it is part of the form data --> 
						Room Pref:
						  <select name='roomCode0' id='room_list' onchange='refill_codes();'>
						</select>   <button type='button' onclick="ext_toggle(1);" id='expand'>Hide ↑</button></td>
					</tr>
					<tr id="ad_pref1" style="display:block;">
					  <td>
					<span id="adv_block">  
					    Wheelchair <br/>
						<input name="wheelchair" type="radio" id="wheelchair_yes" onchange="change_room_code()" value="1"/>
						Yes
						<input name="wheelchair" type="radio" id="wheelchair_no" onchange="change_room_code()" value="0" checked="checked"/>
						No
					</span>
					<span id="adv_block">
						Projector <br/>
						<input name="projector" type="radio" id="projector_yes" onchange="change_room_code()" value="1" checked="checked"/>
						Yes
						<input name="projector" type="radio" id="projector_no" onchange="change_room_code()" value="0"/>
						No
					</span> <br>
					<span id="adv_block">
						Visualiser <br/>
						<input name="visualiser" type="radio" id="visualiser_yes" onchange="change_room_code()" value="1" checked="checked"/>
						Yes
						<input name="visualiser" type="radio" id="visualiser_no" onchange="change_room_code()" value="0"/>
						No
					</span>
					<span id="adv_block">
						Whiteboard <br/>
						<input name="whiteboard" type="radio" id="whiteboard_yes" onchange="change_room_code()" value="1" checked="checked"/>
						Yes
						<input name="whiteboard" type="radio" id="whiteboard_no" onchange="change_room_code()" value="0"/>
						No
					</span>
						</td>
					</tr>
					<tr id="add_room_col">
					  <td><span id='room_list2' style="display: none;">Room Pref 2:
						  <select name='roomCode1' onchange='refill_codes();'>
						</select> <button type='button' onclick="ext_toggle(2);" id='expand2'>Expand ↓</button>
						</span></td>
					</tr>
					<tr id="ad_pref2">
					  <td>
					<span id="adv_block">  
					    Wheelchair <br/>
						<input name="wheelchair2" type="radio" id="wheelchair_yes2" onchange="change_room_code()" value="1"/>
						Yes
						<input name="wheelchair2" type="radio" id="wheelchair_no2" onchange="change_room_code()" value="0" checked="checked"/>
						No
					</span>
					<span id="adv_block">
						Projector <br/>
						<input name="projector2" type="radio" id="projector_yes2" onchange="change_room_code()" value="1" checked="checked"/>
						Yes
						<input name="projector2" type="radio" id="projector_no2" onchange="change_room_code()" value="0"/>
						No
					</span> <br>
					<span id="adv_block">
						Visualiser <br/>
						<input name="visualiser2" type="radio" id="visualiser_yes2" onchange="change_room_code()" value="1" checked="checked"/>
						Yes
						<input name="visualiser2" type="radio" id="visualiser_no2" onchange="change_room_code()" value="0"/>
						No
					</span>
					<span id="adv_block">
						Whiteboard <br/>
						<input name="whiteboard2" type="radio" id="whiteboard_yes2" onchange="change_room_code()" value="1" checked="checked"/>
						Yes
						<input name="whiteboard2" type="radio" id="whiteboard_no2" onchange="change_room_code()" value="0"/>
						No
					</span>
						</td>
					</tr>
					<tr>
					  <td><span id='room_list3' style="display: none;">Room Pref 3:
						  <select name='roomCode2' onchange='refill_codes();'>
						</select> <button type='button' onclick="ext_toggle(3);" id='expand3'>Expand ↓</button>
						</span></td>
					</tr>
					<tr id="ad_pref3">
					  <td>
					<span id="adv_block">  
					    Wheelchair <br/>
						<input name="wheelchair3" type="radio" id="wheelchair_yes3" onchange="change_room_code()" value="1"/>
						Yes
						<input name="wheelchair3" type="radio" id="wheelchair_no3" onchange="change_room_code()" value="0" checked="checked"/>
						No
					</span>
					<span id="adv_block">
						Projector <br/>
						<input name="projector3" type="radio" id="projector_yes3" onchange="change_room_code()" value="1" checked="checked"/>
						Yes
						<input name="projector3" type="radio" id="projector_no3" onchange="change_room_code()" value="0"/>
						No
					</span> <br>
					<span id="adv_block">
						Visualiser <br/>
						<input name="visualiser3" type="radio" id="visualiser_yes3" onchange="change_room_code()" value="1" checked="checked"/>
						Yes
						<input name="visualiser3" type="radio" id="visualiser_no3" onchange="change_room_code()" value="0"/>
						No
					</span>
					<span id="adv_block">
						Whiteboard <br/>
						<input name="whiteboard3" type="radio" id="whiteboard_yes3" onchange="change_room_code()" value="1" checked="checked"/>
						Yes
						<input name="whiteboard3" type="radio" id="whiteboard_no3" onchange="change_room_code()" value="0"/>
						No
					</span>
						</td>
					</tr>
					<tr>
					  <td><span  id='room_list4' style="display: none;">Room Pref 4:
						  <select name='roomCode3' onchange='refill_codes();'>
						</select> <button type='button' onclick="ext_toggle(4);" id='expand4'>Expand ↓</button>
						</span></td>
					</tr>
					<tr id="ad_pref4">
					  <td>
					<span id="adv_block">  
					    Wheelchair <br/>
						<input name="wheelchair4" type="radio" id="wheelchair_yes4" onchange="change_room_code()" value="1"/>
						Yes
						<input name="wheelchair4" type="radio" id="wheelchair_no4" onchange="change_room_code()" value="0" checked="checked"/>
						No
					</span>
					<span id="adv_block">
						Projector <br/>
						<input name="projector4" type="radio" id="projector_yes4" onchange="change_room_code()" value="1" checked="checked"/>
						Yes
						<input name="projector4" type="radio" id="projector_no4" onchange="change_room_code()" value="0"/>
						No
					</span> <br>
					<span id="adv_block">
						Visualiser <br/>
						<input name="visualiser4" type="radio" id="visualiser_yes4" onchange="change_room_code()" value="1" checked="checked"/>
						Yes
						<input name="visualiser4" type="radio" id="visualiser_no4" onchange="change_room_code()" value="0"/>
						No
					</span>
					<span id="adv_block">
						Whiteboard <br/>
						<input name="whiteboard4" type="radio" id="whiteboard_yes4" onchange="change_room_code()" value="1" checked="checked"/>
						Yes
						<input name="whiteboard4" type="radio" id="whiteboard_no4" onchange="change_room_code()" value="0"/>
						No
					</span>
						</td>
					</tr>
				  </table>
				</div>
				<!--advance-->
				<div id="subdiv">
				<table id="subtable">
					<tr>
						<td>
							<input id="submit" type="submit" value="Submit"/>
						</td>
					</tr>
				</table>
				
				</div>
				<!--subdiv--> 
		</div>
      <!--input wrap--> 
	</div>
</form>
	</body>
	</html>
