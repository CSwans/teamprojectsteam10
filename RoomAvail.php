
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
			
			$sql = "SELECT DISTINCT ROOMS.capacity, wheelchair, projector, visualiser, whiteboard, PARKS.park, ROOMS.room_code, ROOMS.building_code FROM ROOMS, PARKS WHERE ROOMS.building_code = PARKS.building_code";
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
			
			$sql = "SELECT * FROM PARKS";
			$res =& $db->query($sql); //getting the result from the database
			if(PEAR::isError($res)){
				die($res->getMessage());
			}
			$value2 = array();
			//put each rows into value array
			while($row = $res->fetchRow()){
				$value2[] = $row;
			}
			$buildingJson = json_encode($value2);
		?>
		<script src="js/jquery-1.11.1.min.js"></script>
		<script src="js/jquery-ui.js"></script>
		<script src="js/jquery.serializejson.min.js"></script>
	</head>
			<link rel="stylesheet" href="Theme.css"/>
	<script type="text/javascript">
	
			
			
			$(function() {
				
				
				ParkChange();
				WeekChange();
				RoomChange();
				ajaxFunction();
				//Populates the correct room values into the form as the page is loaded
				$("#RoomSubmit").html("Room: "+document.getElementById("RoomSelect").value);
				$("#WeekSubmit").html("Week: "+document.getElementById("Weeks").value);
				buildingInitialise();
				
			});
			
			<?php
				echo "var roomData = ".$json.";\n";
				echo "var buildingData = ".$buildingJson.";\n";
			
				//retrieveing all the room codes with their sizes attatched
				//Callan Swanson
				$sql = "SELECT room_code, capacity, wheelchair, projector, visualiser, whiteboard FROM ROOMS";
				$res =& $db->query($sql); //getting the result from the database
				if(PEAR::isError($res)){
					die($res->getMessage());
				}
				
				$roomInfo = array();
				
				//put each rows into value array
				while($row = $res->fetchRow()){
					$roomInfo[] = $row;
				}
				$roomInfojson = json_encode($roomInfo);
				//declares the roomInfo in javascript
				echo "var roomInfo = ".$roomInfojson.";";
				
				$sql = "SELECT module_code, module_title 
				FROM MODULES 
				WHERE dept_code='$username' 
				ORDER BY module_code;";
				$res =& $db->query($sql); //getting the result from the database
				if(PEAR::isError($res)){
					die($res->getMessage());
				}
				$moduleInfo = array();
				while($row = $res->fetchRow()){
					$moduleInfo[] = $row;
				}
				$moduleJson = json_encode($moduleInfo);
				echo "var moduleData = ". $moduleJson . ";\n";
				
			?>
			
			//ajax to remove the book buttons within the table 
			//Callan Swanson, Inthuch Therdchanakul
			function ajaxFunction() {
			  	var MyForm = $("#options").serializeJSON();
				console.log("MyForm: "+MyForm);
				
				//clear the table of any booked rooms
				refreshBooks();
				
				$.ajax( {
					url : "RoomAvailAJAX.php",
					type : "POST", 
					data : {valArray:MyForm},
					success : function (data){
							data = JSON.parse(data);
							console.log("data "+data); //quick check
							for(var i=0; i<data.length; i++) { //looking throught the whole data array to find the correct weeks
								console.log("data[i] "+data[i]);
								if(document.getElementById("Weeks").value == data[i].week) { 
									for(var j=0; j<data[i].duration; j++) {  //looping through the whole duration of the booked slot
										console.log(data[i].day+(parseInt(data[i].period)+j));
										$("#"+data[i].day+(parseInt(data[i].period)+j)).html(""); //removes the Book button in the table
									}
								} else {  //default weeks are put in as a 0 in teh relationship table 
									if(parseInt(document.getElementById("Weeks").value) <= 12 && data[i].week == 0) {
										for(var j=0; j<data[i].duration; j++) {  //looping through the whole duration of the booked slot
											console.log(data[i].day+(parseInt(data[i].period)+j));
											$("#"+data[i].day+(parseInt(data[i].period)+j)).html(""); //removes the Book button in teh table
										}
									}
								}
							}
						},
					error : function(jqXHR, textStatus, errorThrown) {
					}
				});
			}
			
			//replaces all the books within the table so no previously booked rooms are still displayed as booked
			//Callan Swanson, Inthuch Therdchanakul
				function refreshBooks() {
					for(var i=1; i<10; i++) {
					$("#Monday"+i).html("<input type='button' value='Select' id='Monday"+i+"b' onclick='bookClick(this.id)'>");
					$("#Tuesday"+i).html("<input type='button' value='Select' id='Tuesday"+i+"b' onclick='bookClick(this.id)'>");
					$("#Wednesday"+i).html("<input type='button' value='Select' id='Wednesday"+i+"b' onclick='bookClick(this.id)'>");
					$("#Thursday"+i).html("<input type='button' value='Select' id='Thursday"+i+"b' onclick='bookClick(this.id)'>");
					$("#Friday"+i).html("<input type='button' value='Select' id='Friday"+i+"b' onclick='bookClick(this.id)'>");
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
				
				hideForm();
				
				$("#BuildingCodeSelect").html("<option>All</option>");
				$("#BuildingNameSelect").html("<option>All</option>");				
				for(var i=0; i<buildingData.length; i++) {
					if(buildingData[i].park == document.getElementById("ParkSelect").value) {
						$("#BuildingCodeSelect").append("<option>"+buildingData[i].building_code+"</option>");
						$("#BuildingNameSelect").append("<option>"+buildingData[i].building_name+"</option>");
					}
				}
			}
			
			//chnages teh relevant places weeks are located
			function WeekChange() {
				$("#weekChosen").html("Week - "+document.getElementById("Weeks").value);
				$("#WeekSubmit").html("Week: "+document.getElementById("Weeks").value);
				$("#WeekSubmitInput").val(document.getElementById("Weeks").value);
				
				hideForm();
			}
			
			//changes the relevant places rooms are located
			//Callan Swanson, Inthuch Therdchanakul
			function RoomChange() {
				facilityChange();
				
				
				hideForm();
			}
			
			function facilityChange() {
				$("#RoomSubmit").html("Room: "+document.getElementById("RoomSelect").value);
				
				var wheelchairVal = document.getElementById("wheelchair_yes").checked;
				var whiteboardVal = document.getElementById("whiteboard_yes").checked;
				var projectorVal = document.getElementById("projector_yes").checked;
				var visualiserVal = document.getElementById("visualiser_yes").checked;
				
				if(whiteboardVal)
					$("#WhiteboardSubmit").html("Whiteboard: Yes");
				else 
					$("#WhiteboardSubmit").html("Whiteboard: No");
				if(projectorVal)
					$("#ProjectorSubmit").html("Projector: Yes");
				else
					$("#ProjectorSubmit").html("Projector: No");
				if(visualiserVal)
					$("#VisualiserSubmit").html("Visualiser: Yes");
				else
					$("#VisualiserSubmit").html("Visualiser: No");
				if(wheelchairVal)
					$("#WheelchairSubmit").html("Wheelchair: Yes");
				else
					$("#WheelchairSubmit").html("Wheelchair: No");
				
				
				$("#Wheelchair").val(~~wheelchairVal);
				$("#Projector").val(~~projectorVal);
				$("#Visualiser").val(~~visualiserVal);
				$("#Whiteboard").val(~~whiteboardVal);
				$("#RoomSubmitInput").val(document.getElementById("RoomSelect").value);
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
				
				if(document.getElementById("priorityInputTrue").checked) {
					$("#priorityCell").html("Priority:		Yes");		
				} else {
					$("#priorityCell").html("Priority:		No");
				}
			
				$("#priority").val(~~document.getElementById("priorityInputTrue").checked);
				//changes the values of the hidden input fields to correspond to the button clicked
				var days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"];
				var dayNo = days.indexOf(buttonId.substr(0, buttonId.length-2));
				$("#DaySubmitInput").val((dayNo+1));
				
				$("#PeriodSubmitInput").val(buttonId.substr(buttonId.length-2,1));
				
				showForm();
			}
			
			//checks the room size and information against the database result
			//uses global variable roomInfo
			//only allows the form to be sumbitted if this function returns true
			//Callan Swanson, Inthuch Therdchanakul
			function formValidation() {
				console.log(document.getElementById("capacity1").value);
				console.log(roomInfo[roomIndex()].capacity);
				
				var capacity = roomInfo[roomIndex()].capacity;
				
				
				
				//only returns true if both the capacity fits (and not empty) and the day has been chosen
				if(document.getElementById("capacity1").value != "") {
					if(capacity >= parseInt(document.getElementById("capacity1").value)) {
						if(document.getElementById("DaySubmitInput").value != "") {
							alert("True");
							return true;
						}
					}
				}
				
				alert("Please input a correct value!");
				return false;
			}
			
			//returns the index of the chosen room
			function roomIndex() {
				//for loop finding the correct rrom capacity for the room
				for(var i=0; i<roomInfo.length; i++) {
					if(roomInfo[i].room_code == document.getElementById("RoomSelect").value) {
						return i; //breaks the for loop at the correct index, leaving i
					}
				}
				
			}
			
			$("#requestForm").submit(function() {
				var url = "insertInfo.php";
				var MyForm = $("#requestForm").serializeJSON();
				
				$.ajax({
					type: "POST",
					url: url,
					data: {valArray:MyForm},
					success: function (data) {
						console.log("Fine");
						console.log(data);
					}
				});
				return false;	
			}); 
			
			
			function showForm() {
				var e = document.getElementById("inputs");
				
				e.style.display = 'block';
				
			}
			
			function hideForm() {
				var e = document.getElementById("inputs");
				
				e.style.display = 'none';
			}
			
			function change_room_code() {
				//cache user settings
				
				var ParkSelect = document.getElementById("ParkSelect").value;
				var capacity = document.getElementById("capacity").value;
				var isWheelchair = document.getElementById("wheelchair_yes").checked;
				var isVisualiser = document.getElementById("visualiser_yes").checked;

				var isProjector = document.getElementById("projector_yes").checked;

				var isWhiteboard = document.getElementById("whiteboard_yes").checked;
				var buildingCode = document.getElementById("BuildingCodeSelect").value;
				
				document.getElementById("capacity1").value = capacity;
				
				//empty the room code list
				$("#RoomSelect").empty();
				

				for(var i=0;i<roomData.length;i++){
				//if the room has enough capacity, and has the options the user asked for - or he didn't ask for the option, then add it to the list
					if((roomData[i].capacity >= parseInt(capacity) || capacity == "") &&
					(ParkSelect == "Any" || ParkSelect == roomData[i].park) &&
					(!isWheelchair || roomData[i].wheelchair == 1) &&
					(!isVisualiser || roomData[i].visualiser == 1) &&
					(!isProjector || roomData[i].projector == 1) &&
					(!isWhiteboard || roomData[i].whiteboard == 1) && 
					(buildingCode == "All" || buildingCode == roomData[i].building_code)) {
						$("#RoomSelect").append("<option value='" + roomData[i].room_code + "'>" + roomData[i].room_code + "</option>");
					}
				}
			}
			
			function buildingCodeChange() {
				document.getElementById("BuildingNameSelect").selectedIndex = document.getElementById("BuildingCodeSelect").selectedIndex;
			}
			
			function buildingNameChange() {
				document.getElementById("BuildingCodeSelect").selectedIndex = document.getElementById("BuildingNameSelect").selectedIndex;
			}
			
			function buildingInitialise() {
				$("#BuildingCodeSelect").html("<option>All</option>");
				$("#BuildingNameSelect").html("<option>All</option>");				
				for(var i=0; i<buildingData.length; i++) {
					$("#BuildingCodeSelect").append("<option>"+buildingData[i].building_code+"</option>");
					$("#BuildingNameSelect").append("<option>"+buildingData[i].building_name+"</option>");
				}
			}
			function changePWordAjax() {
				if(document.getElementById("") != document.getElementById("")) {
					return(alert("New passwords do not match"));
				}
				
				$.ajax({
					url : "updatePWord.php",
					type : "POST", 
					data : $("#changePWordForm").serialize(),
					success : function (data){					
							data = JSON.parse(data);
							alert("Your password has been changed");
						},
					error : function(jqXHR, textStatus, errorThrown) {
					}
				});
			}
			function insertAjax(){
				
				$.ajax( {
					url : "insertInfo.php",
					type : "POST", 
					data : $("#requestForm").serialize(),
					success : function (data){					
							data = JSON.parse(data);
							alert("Request submitted.");
							console.log("data "+data); //quick check
							
						},
					error : function(jqXHR, textStatus, errorThrown) {
					}
				});
			}
			function partChange() {
		//looks through all of the moduleData
		$("#module_code_select").empty();
		$("#module_title_select").empty();
		console.log(moduleData[0].module_code.substr(4,1));
		console.log(document.getElementsByName("partCode").value);
		
		//finding out which part is checked one by one
		var checkedVal;
		if(document.getElementById("allPart").checked) {
			checkedVal = document.getElementById("allPart").value;
		}else if(document.getElementById("aPart").checked) {
			checkedVal = document.getElementById("aPart").value;
		}else if(document.getElementById("bPart").checked) {
			checkedVal = document.getElementById("bPart").value;
		}else if(document.getElementById("iPart").checked) {
			checkedVal = document.getElementById("iPart").value;
		}else if(document.getElementById("cPart").checked) {
			checkedVal = document.getElementById("cPart").value;
		}else if(document.getElementById("dPart").checked) {
			checkedVal = document.getElementById("dPart").value;
		}
		
		console.log(checkedVal);
		
		for(var i=0; i<moduleData.length; i++) {
			if(moduleData[i].module_code.substr(4,1) == checkedVal) {
				$("#module_code_select").append("<option>"+moduleData[i].module_code+"</option>");
				$("#module_title_select").append("<option>"+moduleData[i].module_title+"</option>");
			} else if(checkedVal == "All") {
				$("#module_code_select").append("<option>"+moduleData[i].module_code+"</option>");
				$("#module_title_select").append("<option>"+moduleData[i].module_title+"</option>");
			}
		}
	}
			
					function logout_question(){
  if (confirm('Are you sure you want to logout?')){
    return true;
  }else{
    return false;
  }
}
	
	function goBack() {
    window.history.back()
}			
			
			
		</script>
		
	<body>
	
	   
<div id="top_style">
<div  align="middle" style="top:0; width: 50px; float: left; margin-left: 165px;">  
<a onclick="goBack();"> <img width="30" height="20" border="0" alt="Back" src="Back_Arrow.png" align="middle" style=" cursor: pointer;"> </a> </div>

<a href="timetable.php"> <img width="40" height="40" border="0" alt="Home!" src="Home_Button.png" align="left"> </a> 
<input class='class_change_passoword' id='id_change_password' type="button" value="change password" onclick='$("#changePWordForm").dialog();'>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;    
<b> <a href="login.html" style="margin-right: 140px; font-weight: 900; font-size: 1em;" onclick='return logout_question();'>Logout</a></b>  </div>
	<div id = "header_style" >
  		<div id="title">
    		<h1>Loughborough University Timetabling </h1>
    		<h2> 
    			<?php $dept_code = strtolower($username); $sql = "SELECT dept_name FROM DEPT WHERE dept_code = '$dept_code' "; 		$res =& $db->query($sql); //getting the result from the database
				if(PEAR::isError($res)){
					die($res->getMessage());
				}
							//put each rows into value array
				while($row = $res->fetchRow()){
					echo $row["dept_name"];
				}  ?>   
				<br/> 
			</h2> 
  		</div>
  	<div id="logo"> <a href="http://www.lboro.ac.uk/?external"> <img id = "lboro_logo" src="LU-mark-rgb.png" alt="Loughborough University Logo" /> </a> </div>
		</div>
		
		<hr/>
	
	
		<div id="checkOptions">
		<div id="checkWrap">
			<form name="options" id="options" method="POST">
				
				<!-- functionality selection-->
				</br>
				<div id="checkFacilities">
				<table>
					<tr>
						<td>
							<label name="priorityInput">Priority: </label>
						</td>
						<td>
							<input name="priorityInput" type="radio" id="priorityInputTrue" onchange="change_room_code()" value="1"/>Yes
							<input name="priorityInput" type="radio" id="priorityInputFalse" onchange="change_room_code()" value="0" checked/>No
						</td>
					</tr>
					<tr>
						<td>
							<label name="wheelchair">Wheelchair: </label>
						</td>
						<td>
							<input type="radio" name="wheelchair" id="wheelchair_yes" value="1" onchange="change_room_code();facilityChange();hideForm();"> Yes
							<input type="radio" name="wheelchair" id="wheelchair_no" value="0" onchange="change_room_code();facilityChange();hideForm();" checked="checked">No
						</td>
					<tr>
						<td>
							<label name="projector">Projector: </label>
						</td>
						<td>
						<input type="radio" name="projector" id="projector_yes" value="1" onchange="change_room_code();facilityChange();hideForm();" checked="checked"> Yes
						<input type="radio" name="projector" id="projector_no" value="0" onchange="change_room_code();facilityChange();hideForm();" > No
						</td>
					</tr>
					<tr>
						<td>
							<label name="radio">Visualiser: </label>
						</td>
						<td>
							<input type="radio" name="visualiser" id="visualiser_yes" value="1" onchange="change_room_code();facilityChange();hideForm();" checked="checked"> Yes
							<input type="radio" name="visualiser" id="visualiser_no" value="0" onchange="change_room_code();facilityChange();hideForm();"> No
						</td>
					</tr>
					<tr>
						<td>
							<label name="whiteboard">Whiteboard: </label>
						</td>
						<td>
							<input type="radio" name="whiteboard" id="whiteboard_yes" value="1" onchange="change_room_code();facilityChange();hideForm();" checked="checked"> Yes
							<input type="radio" name="whiteboard" id="whiteboard_no" value="0" onchange="change_room_code();facilityChange();hideForm();" > No
						</td>
					<tr>
				</table>
				</div>
				<div id="checkCap">
				<table>
					<tr>
						<td>
							<label name="capacity1">Capacity: <label>
						</td>
						<td>
							<input type="text" id="capacity" name="capacity1" onchange="change_room_code();hideForm();" >
						</td>
					</tr>
					<tr>
						<td>
							<label name="weeks[]">Week: </label>
						</td>
						<td>
								<select name="weeks[]" id="Weeks" onChange="WeekChange();ajaxFunction();">
									<?php
										for($i = 1; $i<=16; $i++) { //displaying 1-16 weeks
											echo "<option>".$i."</option>";
										}
									?>
								</select>
						</td>
					</tr>
				</table>
				</div>
				<div id="checkLocation">
					<table>
					<tr>
						<td>
							<label name="ParkSelect">Park: </label>
						</td>
						<td>
							<select name="ParkSelect" id="ParkSelect" onChange="ParkChange();change_room_code()" >
								<option value="Any">Any</option>
								<option value="C">C</option>
								<option value="E">E</option>
								<option value="W">W</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>
							<label name="BuildingNameSelect">Building Name: </label> 
						</td>
						<td>
							<select name="BuildingNameSelect" id="BuildingNameSelect" onChange="buildingNameChange();change_room_code()" >
						
							</select>
						</td>
					</tr>
					<tr>
						<td>
							<label name="BuildingCodeSelect">Building Code: </label>
						</td>
						<td>
							<select name="BuildingCodeSelect" id="BuildingCodeSelect" onChange="buildingCodeChange();change_room_code()" >
					
							</select>
						</td>
					</tr>
					<tr>
						<td>
							<label name="RoomSelect">Rooms: </label>
						</td>
						<td>
							<select name="RoomSelect" id="RoomSelect" onChange="ajaxFunction();RoomChange()" >
		
							</select>
						<td>
					</tr>
					</table>
				</div>
			</form>
		</div>
		</div>
		
		<div id="page_wrap2">
		<br/> <hr/  id="round_input_separator"> <br/>
		
		
		<table style="width:85%; max-width:1450px;" align="center" frame="box" id="RoomAvailTable">
			<tr id="time">
				<td id="weekChosen" class='top_table_style'></td>
				<?php
					for($i=9;$i<18;$i++) { //stating the time for booking at the top of the table
						echo "<td class='top_table_style'>".$i.":00</td>";
					}
				?>
			</tr>
			<tr id="monday" >
				<td class='row_table_style day1'>Monday</td>
				<?php
					for($i=1; $i<10; $i++) { //describing the table with teh day and the time period
						echo "<td class='row_table_style' id=Monday".$i.">Book</td>";
					}
				?>
			</tr>
			<tr id="tuesday">
				<td class='row_table_style day1'>Tuesday</td>
				<?php
					for($i=1; $i<10; $i++) {
						echo "<td class='row_table_style' id=Tuesday".$i.">Book</td>";
					}
				?>
			</tr>
			<tr id="wednesday">
				<td class='row_table_style day1'>Wednesday</td>
				<?php
					for($i=1; $i<10; $i++) {
						echo "<td class='row_table_style' id=Wednesday".$i.">Book</td>";
					}
				?>
			</tr>
			<tr id="thursday">
				<td class='row_table_style day1'>Thursday</td>
				<?php
					for($i=1; $i<10; $i++) {
						echo "<td class='row_table_style' id=Thursday".$i.">Book</td>";
					}
				?>
			</tr>
			<tr id="friday">
				<td  class='row_table_style day1' >Friday</td>
				<?php
					for($i=1; $i<10; $i++) {
						echo "<td class='row_table_style' id=Friday".$i.">Book</td>";
					}
				?>
			</tr>
		</table>
		
		

		
		<div id="input_wrap2">
		<div id="inputs"  style="display:none;">
			<form id="requestForm" >
			<div id="after1">
				<table class="inputs">
					<tr>
						<td>
							<?php echo "Department: </td><td>".$username."</td>"; ////////////////////////////?>
						
					</tr>
					<tr>
						<td id="priorityCell"> 
							Priority: 
						</td>
						<td>
							<input name="priorityInput" type="hidden" id="priority" value="" />
						</td>
					</tr>
					<tr>
						<td>
							<input type='radio' name='partCode' id='allPart' checked='checked' value='All' onchange='partChange()'> All 
							<input type='radio' name='partCode' id='aPart' value='A' onchange='partChange()' > A 
							<input type='radio' name='partCode' id='bPart' value='B' onchange='partChange()'> B 
							<input type='radio' name='partCode' id='iPart' value='I' onchange='partChange()'> I 
							<input type='radio' name='partCode' id='cPart' value='C' onchange='partChange()'> C 
							<input type='radio' name='partCode' id='dPart' value='D' onchange='partChange()'> D
						</td> 
					</tr>
					<tr>
						
						<td>
							<?php
								//will output the whole set of module codes from the database, module codes will change when module titles change
								//Callan Swanson, Inthuch Therdchanakul
								//Scott Marshall: added order by to SQL and name to the <select>. 'module_code_select' is now part of the Form Data
								echo "Module code: </td><td><select id='module_code_select' name='module_code_select' onchange='module_code_change()'>";
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
								$sql = "SELECT module_title FROM MODULES WHERE dept_code='$username' ORDER BY module_code;";
								$res =& $db->query($sql); //getting the result from the database
								if(PEAR::isError($res)){
									die($res->getMessage());
								}
								echo "Module title: </td><td><select id='module_title_select' name='module_title_select' onchange='module_title_change()' >";
								while($row = $res->fetchRow()){
									echo "<option>".$row["module_title"]."</option>";
									
								}
								echo "</select>";
							?>
							
						</td>
					</tr>
					
					<tr>
						<td id="capacityCell"> 
							
						</td>
						<td>
							<input name="capacity" type="hidden" id="capacity1" value="" />
						</td>
					</tr>
				
					<tr>
						<td> Special requirements: </td>
						<td>
							<textarea name="specialReq" maxlength="1000" placeholder="1000 chars max..."></textarea>
						</td>
					</tr>
					
					<tr>
						<td id="RoomSubmit">
							
						</td>
						<td>
						<input type="hidden" id="RoomSubmitInput" name="roomCode0" value=""  >
						</td>
					</tr>
					
					<tr>
						<td id="WeekSubmit">
							
						</td>
						<td>
							<input type="hidden" id="WeekSubmitInput" name="weeks[]" value=""  >
						</td>
					</table>
					</div>
					<div id="after2">
					<table class="inputs">
					</tr>
					
					<tr>
						<td id="DaySubmit">
							Day: 
							
						</td>
						<td>
							<input type="hidden" id="DaySubmitInput" name="day" value=""  >
						</td>
					</tr>
					
					<tr>
						<td id="PeriodSubmit">
							Period/Time: 
							
						</td>
						<td>
							<input type="hidden" id="PeriodSubmitInput" name="time" value=""  >
						</td>
					</tr>
					
					<tr>
						<td id="WheelchairSubmit">
							Wheelchair:
						</td>
						<input type="hidden" id="Wheelchair" name="wheelchair" value="">
					</tr>
					
					<tr>
						<td id="ProjectorSubmit">
							Projector:
						</td>
						<input type="hidden" id="Projector" name="projector" value="">
					</tr>
					
					<tr>
						<td id="VisualiserSubmit">
							Visualiser:
						</td>
						<input type="hidden" id="Visualiser" name="visualiser" value="">
					</tr>
					
					<tr>
						<td id="WhiteboardSubmit">
							Whiteboard:
						</td>
						<input type="hidden" id="Whiteboard" name="whiteboard" value="">
					</tr>
					
					<input type="hidden" name="duration" value="1">
					<input type="hidden" name="noRooms" value="1">
					
					<tr>
						<td>
							<input id="checkButton" type="button" value="Send Request!" onclick="insertAjax()" />
						</td>
					</tr>
					
					
				
				</table>
		</div>
		</form>
		</div>
		</div>
		</div>
		<div id="changePWord" style="display:none">
			<form name="changePWordForm" id="changePWordForm">
				Current Password: <input type="text" id="oldPWord" name="oldPWord">
				New Password: <input type="text" id="newPWord1" name="newPWord1">
				Confirm New Password: <input type="text" id="newPWord2" name="newPWord2">
				<input type="button" value="Change" onClick="changePWordAjax()">
			</form>
</div>
		
	</body>

</html>
