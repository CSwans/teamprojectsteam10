<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<link rel="icon" href="lboro_logo_large.ico" >
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Timetable | Loughborough University</title>
		<link rel="stylesheet" href="http://code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">
        <link rel="stylesheet" href="css/style.css">
        <?php 
	//Starts the session, if there is not any sessions then it will transfer to the login page and the user will ave to log in again
	//Inthuch Therdchanakul
	session_start();
	if(!isset($_SESSION['username']) || !isset($_SESSION['password']))
	{
		header('Location: login.html');	
	}
	
		?>
	<?php
			//connects to the database using the username and passoword
			require_once "MDB2.php";
			$host = "co-project.lboro.ac.uk"; 	//host name
			$dbName = "team10";					//database name
			$dsn = "mysql://team10:abg83rew@$host/$dbName";	//login information
			$db =& MDB2::connect($dsn);	//connecting to the server and connecting to the database
			if(PEAR::isError($db)){ 	//if we couldnt connect then end the connection
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
		?>
		<script src="js/jquery-1.11.1.min.js"></script>
		<script src="js/jquery-ui.js"></script>	
		<script type="text/javascript">
			  
			  <?php
			  	//pass value array onto javascript array roomData
				echo "var roomData = ". $json . ";\n";
			  ?>
			  //call this function when the page load
			  $(function() {
				//implement multiple selecttion to selectable jquery-ui
				$("#week").bind("mousedown", function(e) {
 					e.metaKey = true;
					}).selectable();
				insert_room_code();
 			  });
		
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
			
			//change room preference base on capacity and park
			//Inthuch Therchanakul
			function change_room_code(){
				//work in progress
				/*var park = document.getElementById("park").value;
				var capacity = document.getElementById("capacity").value;
				if(document.getElementById("wheelchair_yes").checked)
					var wheelchair = document.getElementsByName("wheelchair")[0].value;
				else
					var wheelchair = document.getElementsByName("wheelchair")[1].value;
				if(document.getElementById("projector_yes").checked)
					var projector =  document.getElementsByName("projector")[0].value;
				else
					var projector = document.getElementsByName("projector")[1].value;
				if(document.getElementById("visualiser_yes").checked)
					var visualiser = document.getElementsByName("visualiser")[0].value;
				else
					var visualiser = document.getElementsByName("visualiser")[1].value;
				if(document.getElementById("whiteboard_yes").checked)
					var whiteboard = document.getElementsByName("whiteboard")[0].value;
					
				else
					var whiteboard = document.getElementsByName("whiteboard")[1].value;*/
				$("#room_list").empty();
				$("#room_col").empty();
				$("#room_col").html("Room code: <select id='room_list'>");
				$("#room_list").append("<option>" + "" + "</option>");
				if(park == "Any"){
					for(var i=0;i<roomData.length;i++){
						if(roomData[i].capacity >= capacity)
							$("#room_list").append("<option>" + roomData[i].room_code + "</option>");
						}
					}
				else{
					for(var i=0;i<roomData.length;i++){
						if(roomData[i].capacity >= capacity && roomData[i].park == park)
							$("#room_list").append("<option>" + roomData[i].room_code + "</option>");
						}
					}
				
				$("#room_list").append("</select>");
			}
			//Initial room choice
			//Inthuch Therdhchanakul
			function insert_room_code(){
					$("#room_col").html("Room code: <select id='room_list'>");
					$("#room_list").append("<option>" + "" + "</option>");
					for(var i=0;i<roomData.length;i++){
						$("#room_list").append("<option>" + roomData[i].room_code + "</option>");
						}
					$("#room_list").append("</select>");
				}
		</script>
		
	</head>

	<body>
		<p id="test"></p>
        <table>
			<form action="requestSubmit.php" method="post">
				<tr>
					<td>
						<?php echo "Department: ".$username; ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php 
							//will output the whole set of module codes from the database, module codes will change when module titles change
							//Callan Swanson, Inthuch Therdchanakul
							echo "Module code: <select id='module_code_select' onchange='module_code_change()'>";  
							$sql = "SELECT module_code FROM MODULES WHERE dept_code='$username';";
							$res =& $db->query($sql); //getting the result from the database
							if(PEAR::isError($res)){
								die($res->getMessage());
							}
							while($row = $res->fetchRow()){
								echo "<option>".$row["module_code"]."</option>";
							}//outputs all the options from the database return result
							echo "</select>";
						?>
					</td>
				</tr>
				<tr>
					<td>
						<?php 
							//displays the module titles, titles will change when module codes change
							//Callan Swanson, Inthuch Therdchanakul
							echo "Module title: <select id='module_title_select' onchange='module_title_change()' >"; 
							//selects the module title from the databse
							$sql = "SELECT module_title FROM MODULES WHERE dept_code='$username';";
							$res =& $db->query($sql); //getting the result from the database
							if(PEAR::isError($res)){
								die($res->getMessage());
							}
							while($row = $res->fetchRow()){
								echo "<option>".$row["module_title"]."</option>";
							}//outputs all the options from the database return result
							echo "</select>";
						?>
					</td>
				</tr>
				<tr>
					<td>
						Day:
						<!--radio buttons for the day of the week-->
						<input type="radio" name="day" value="1">Monday
						<input type="radio" name="day" value="2">Tuesday<br/>
						<input type="radio" name="day" value="3">Wednesday
						<input type="radio" name="day" value="4">Thursday<br/>
						<input type="radio" name="day" value="5">Friday
					</td>
				</tr>
				<tr>
					<td>
						<!--selectable weeks with weeks 1-12 pre-selected as default-->
						Week:
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
					</td>
				</tr>
				<tr>
					<td>
						Period:
						<?php
							//dropdown for the period, includes the time in 24hr format
							//Callan Swanson
							echo "<select name='time'>";
							for($i=1;$i<=9;$i++){
								$time = $i+8;
								echo "<option value='".$i."'>".$i." - ".$time.":00</option>";
							}
							echo "</select>";
						  ?>
					</td>
				</tr>
				<tr>
					<td>
						Special requirements:
						</br>
						<textarea name="specialReq" maxlength="1000" placeholder="Extra requirements..."></textarea>
					</td>
				</tr>
				<tr>
                	<td>
                    	Number of rooms:
                        <input name="noRooms" type="text" />
                    </td>
                </tr>
                <tr>
                	<td>
                    	
                        Capacity:
                        <input name="capacity" type="text" id="capacity" onchange="change_room_code()" value="1" />
                    </td>
                </tr>
                <tr>
                	<td>
                    	Park:
                        <select id="park" name="park" onchange="change_room_code()">
                        	<option>Any</option>
                            <option>C</option>
                            <option>E</option>
                            <option>W</option>
                        </select>
                    </td>
                </tr>
                <tr>
					<td id="room_col">
						<!--room preference default value is blank-->
					</td>
				</tr>
				<tr>
					<td>
						Wheelchair </br>
                        <input type="radio" name="wheelchair" id="wheelchair_yes" value="1" onchange="change_room_code()">Yes
						<input name="wheelchair" type="radio" id="wheelchair_no" onchange="change_room_code()" value="0" checked="checked">No<br/>
						Projector </br>
                      <input name="projector" type="radio" id="projector_yes" onchange="change_room_code()" value="1" checked="checked">Yes
					  <input name="projector" type="radio" id="projector_no" onchange="change_room_code()" value="0">No<br/>
                        
                        Visualiser </br>
                      <input name="visualiser" type="radio" id="visualiser_yes" onchange="change_room_code()" value="1" checked="checked">Yes
					  <input name="visualiser" type="radio" id="visualiser_no" onchange="change_room_code()" value="0">No<br/>
                        
                        Whiteboard </br>
                      <input name="whiteboard" type="radio" id="whiteboard_yes" onchange="change_room_code()" value="1" checked="checked">Yes
					  <input name="whiteboard" type="radio" id="whiteboard_no" onchange="change_room_code()" value="0">No<br/>
					</td>
				</tr>
				<tr>
					<td>
						<input type="submit" value="submit">
					</td>
				</tr>
			</form>
		</table>
		
		<table id="resultsTable">
			<?php
				
			?>
		</table>
		
	</body>
</html>
