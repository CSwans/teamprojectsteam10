<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<link rel="icon" href="lboro_logo_large.ico">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Timetable - Round 1 | Loughborough University</title>
		<link rel="stylesheet" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css"/>
		<script src="//code.jquery.com/jquery-1.10.2.js"></script>
		<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
		<link rel="stylesheet" href="/resources/demos/style.css"/>
		<link rel="stylesheet" href="Theme.css"/>
		<script>
			$(function() {
			$( "#tabs" ).tabs();
			});
		</script>
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
	?>
	<script src="js/jquery-1.11.1.min.js"></script>
	<script src="js/jquery-ui.js"></script>
	<script type="text/javascript">

		function ext_toggle(n) {
		    if(n==1){
				if(document.getElementById('ad_pref1').style.display=="block"){
					document.getElementById('expand').innerHTML="Expand ↓";
					document.getElementById('ad_pref1').style.display="none";
				}
				else {
					document.getElementById('expand').innerHTML="Hide ↑";
					document.getElementById('ad_pref1').style.display="block";
				}
			}
			
			if(n==2){
				if(document.getElementById('ad_pref2').style.display=="block"){
					document.getElementById('expand2').innerHTML="Expand ↓";
					document.getElementById('ad_pref2').style.display="none";
				}
				else {
					document.getElementById('expand2').innerHTML="Hide ↑";
					document.getElementById('ad_pref2').style.display="block";
				}
			}
			
			if(n==3){
				if(document.getElementById('ad_pref3').style.display=="block"){
					document.getElementById('expand3').innerHTML="Expand ↓";
					document.getElementById('ad_pref3').style.display="none";
				}
				else {
					document.getElementById('expand3').innerHTML="Hide ↑";
					document.getElementById('ad_pref3').style.display="block";
				}
			}
			
			if(n==4){
				if(document.getElementById('ad_pref4').style.display=="block"){
					document.getElementById('expand4').innerHTML="Expand ↓";
					document.getElementById('ad_pref4').style.display="none";
				}
				else {
					document.getElementById('expand4').innerHTML="Hide ↑";
					document.getElementById('ad_pref4').style.display="block";
				}
			}
		}

  //show or hide advanced input that aren't compulsory
  //Tom Middleton
  		function advToggle() {
        	var e = document.getElementById('advance');
       		if(e.style.display == 'block')
          	e.style.display = 'none';
       	else
          	e.style.display = 'block';
    	}	
		

<?php
//pass value array onto javascript array roomData
echo "var roomData = ". $json . ";\n";
?>
//call this function when the page loads
/*$(function() {
//implement multiple selecttion to selectable jquery-ui
$("#week").bind("mousedown", function(e) {
e.metaKey = true;
}).selectable();
insert_room_code();
});*/
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




//change room preference Based on capacity, park and additional options
//Scott Marshall
function change_room_code() {
//cache user settings
var noOfRooms = parseInt(document.getElementById('noRooms').value);
var park = document.getElementById("park").value;

var capacity = parseInt(document.getElementById("capacity1").value);
var capacity2=""; if(noOfRooms > 1) capacity2 = document.getElementById("capacity2").value;
var capacity3=""; if(noOfRooms > 2) capacity3 = document.getElementById("capacity3").value;
var capacity4=""; if(noOfRooms > 3) capacity4 = document.getElementById("capacity4").value;

var isWheelchair = document.getElementById("wheelchair_yes").checked;
var isWheelchair2; if(noOfRooms > 1) isWheelchair2 = document.getElementById("wheelchair_yes2").checked;
var isWheelchair3; if(noOfRooms > 2) isWheelchair3 = document.getElementById("wheelchair_yes3").checked;
var isWheelchair4; if(noOfRooms > 3) isWheelchair4 = document.getElementById("wheelchair_yes4").checked;

var isVisualiser = document.getElementById("visualiser_yes").checked;
var isVisualiser2; if(noOfRooms > 1) isVisualiser2 = document.getElementById("visualiser_yes2").checked;
var isVisualiser3; if(noOfRooms > 2) isVisualiser3 = document.getElementById("visualiser_yes3").checked;
var isVisualiser4; if(noOfRooms > 3) isVisualiser4 = document.getElementById("visualiser_yes4").checked;

var isProjector = document.getElementById("projector_yes").checked;
var isProjector2; if(noOfRooms > 1)  isProjector2 = document.getElementById("projector_yes2").checked;
var isProjector3; if(noOfRooms > 2)  isProjector3 = document.getElementById("projector_yes3").checked;
var isProjector4; if(noOfRooms > 3)  isProjector4 = document.getElementById("projector_yes4").checked;

var isWhiteboard = document.getElementById("whiteboard_yes").checked;
var isWhiteboard2; if(noOfRooms > 1) isWhiteboard2 = document.getElementById("whiteboard_yes2").checked;
var isWhiteboard3; if(noOfRooms > 2) isWhiteboard3 = document.getElementById("whiteboard_yes3").checked;
var isWhiteboard4; if(noOfRooms > 3) isWhiteboard4 = document.getElementById("whiteboard_yes4").checked;


//empty the room code list
$("#room_list").empty();
$("#room_list").append("<option>" + "" + "</option>");
$("#room_list2").find( "select" ).empty();
$("#room_list2").find( "select" ).append("<option>" + "" + "</option>");
$("#room_list3").find( "select" ).empty();
$("#room_list3").find( "select" ).append("<option>" + "" + "</option>");
$("#room_list4").find( "select" ).empty();
$("#room_list4").find( "select" ).append("<option>" + "" + "</option>");

for(var i=0;i<roomData.length;i++){
//if the room has enough capacity, and has the options the user asked for - or he didn't ask for the option, then add it to the list
if(roomData[i].capacity >= capacity &&
(park == "Any" || park == roomData[i].park) &&
(!isWheelchair || roomData[i].wheelchair == 1) &&
(!isVisualiser || roomData[i].visualiser == 1) &&
(!isProjector || roomData[i].projector == 1) &&
(!isWhiteboard || roomData[i].whiteboard == 1))
$("#room_list").append("<option value='" + roomData[i].room_code + "'>" + roomData[i].room_code + "</option>");
}
//additional stages if more than one room pref option required
//Tom middleton
if(parseInt(document.getElementById('noRooms').value) > 1){
	if(capacity2 != '' && capacity2 > 0){
for(var i=0;i<roomData.length;i++){
if(roomData[i].capacity >= capacity2 &&
(park == "Any" || park == roomData[i].park) &&
(!isWheelchair2 || roomData[i].wheelchair == 1) &&
(!isVisualiser2 || roomData[i].visualiser == 1) &&
(!isProjector2 || roomData[i].projector == 1) &&
(!isWhiteboard2 || roomData[i].whiteboard == 1))
$("#room_list2").find( "select" ).append("<option value='" + roomData[i].room_code + "'>" + roomData[i].room_code + "</option>");
}
}
else {}


for(var x=1;x<4;x++){
 document.getElementById('room_list'+ (x+1)).style.display='none';
}

noOfRooms = parseInt(document.getElementById('noRooms').value);
if(noOfRooms>1){
for(var x=1;x<noOfRooms;x++){
 document.getElementById('room_list'+ (x+1)).style.display='block';
} 
}
}
else {
for(var x=1;x<4;x++){
 document.getElementById('room_list'+ (x+1)).style.display='none';
}	
}

if(parseInt(document.getElementById('noRooms').value) > 2){

for(var i=0;i<roomData.length;i++){
if(capacity3 != '' && capacity3 > 0){
if(roomData[i].capacity >= capacity3 &&
(park == "Any" || park == roomData[i].park) &&
(!isWheelchair3 || roomData[i].wheelchair == 1) &&
(!isVisualiser3 || roomData[i].visualiser == 1) &&
(!isProjector3 || roomData[i].projector == 1) &&
(!isWhiteboard3 || roomData[i].whiteboard == 1))
$("#room_list3").find( "select" ).append("<option value='" + roomData[i].room_code + "'>" + roomData[i].room_code + "</option>");
}
}
}


if(parseInt(document.getElementById('noRooms').value) > 3){
if(capacity4 != '' && capacity4 > 0){
for(var i=0;i<roomData.length;i++){
if(roomData[i].capacity >= capacity4 &&
(park == "Any" || park == roomData[i].park) &&
(!isWheelchair4 || roomData[i].wheelchair == 1) &&
(!isVisualiser4 || roomData[i].visualiser == 1) &&
(!isProjector4 || roomData[i].projector == 1) &&
(!isWhiteboard4 || roomData[i].whiteboard == 1))
$("#room_list4").find( "select" ).append("<option value='" + roomData[i].room_code + "'>" + roomData[i].room_code + "</option>");
}
}
}
}


//change number of capacity inputs based on no of rooms
//Tom Middleton
function showCapacity(){
var capacityNumber = document.getElementById("noRooms").value;
$("#capacityCell").html("");
for(var i=0;i<capacityNumber;i++){
var capacityID = "capacity" + (i + 1);
if(i == 0){
$("#capacityCell").append('Capacity: &nbsp;&nbsp; <input type="text" onchange="change_room_code();" id="' + capacityID + '"/><br/>');
}
else $("#capacityCell").append('Capacity ' +(i+1)+ ': <input type="text" onchange="change_room_code();" id="' + capacityID + '"/><br/>');
}
}
//Initial room choice
//Inthuch Therdhchanakul
//Scott Marshall Changed to just fill the select that is element that is part of the table html. Also added a value for each option
// so it is entered into the form data
function insert_room_code(){
$("#room_list").append("<option>" + "" + "</option>");
for(var i=0;i<roomData.length;i++){
$("#room_list").append("<option value='" + roomData[i].room_code + "'>" + roomData[i].room_code + "</option>");
}
}
//Refill duration combo based on the period selected
//Scott Marshall
function refill_duration(){
$("#duration").empty();
var period = $('#time').val();
for(var i=1; i<=10-period;i++){
$('#duration').append("<option value='" + i + "'>" + i + "</option>");
}
}


// preventing choosing two room preferences that are the same
//Tom Middleton
function refill_codes() {
	var noOfRooms = parseInt(document.getElementById('noRooms').value);
	var park = document.getElementById("park").value;
	var capacity = parseInt(document.getElementById("capacity1").value);
	var isWheelchair = document.getElementById("wheelchair_yes").checked;
	var isVisualiser = document.getElementById("visualiser_yes").checked;
	var isProjector = document.getElementById("projector_yes").checked;
	var isWhiteboard = document.getElementById("whiteboard_yes").checked;
		
	var activeLists = [];
	var currentSelections = [];
	
	var cap1=document.getElementById('capacity1').value;
	if(noOfRooms >=2)var cap2=document.getElementById('capacity2').value; 
	if(noOfRooms >=3) var cap3=document.getElementById('capacity3').value; 
	if(noOfRooms >=4) var cap4=document.getElementById('capacity4').value; 
	
	var sel1 = document.getElementById('room_list').value;
    var sel2= document.getElementById('room_list2').children[0].value;
    var sel3 = document.getElementById('room_list3').children[0].value
    var sel4 = document.getElementById('room_list4').children[0].value
    
	if(cap1 != null && cap1 != ''){ activeLists.push(1); currentSelections.push(sel1); }
	if(cap2 != null && cap2 != ''){ activeLists.push(2); currentSelections.push(sel2); }
	if(cap3 != null && cap3 != ''){ activeLists.push(3); currentSelections.push(sel3); }
	if(cap4 != null && cap4 != ''){  activeLists.push(4); currentSelections.push(sel4); }
    
    
    for(var x=0;x<activeLists.length;x++){
    	if(activeLists[x]==1){
    		$("#room_list").empty();
			$("#room_list").append("<option>" + "" + "</option>");
    	}
    	else {
    		$("#room_list" + activeLists[x]).find( "select" ).empty();
    		$("#room_list" + activeLists[x]).find( "select" ).append("<option>" + "" + "</option>");
    	}
    }
    
    for(var x=0;x<activeLists.length;x++){
    	for(var i=0;i<roomData.length;i++){
    		if(activeLists[x]==1){
    			if(roomData[i].capacity >= capacity &&
    				(currentSelections.indexOf(roomData[i].room_code) == -1 || currentSelections.indexOf(roomData[i].room_code) == x) &&
					(park == "Any" || park == roomData[i].park) &&
					(!isWheelchair || roomData[i].wheelchair == 1) &&
					(!isVisualiser || roomData[i].visualiser == 1) &&
					(!isProjector || roomData[i].projector == 1) &&
					(!isWhiteboard || roomData[i].whiteboard == 1))
						$("#room_list").append("<option value='" + roomData[i].room_code + "'>" + roomData[i].room_code + "</option>");
			}
			else {
				if(roomData[i].capacity >= capacity &&
    				(currentSelections.indexOf(roomData[i].room_code) == -1 || currentSelections.indexOf(roomData[i].room_code) == x) &&
					(park == "Any" || park == roomData[i].park) &&
					(!isWheelchair || roomData[i].wheelchair == 1) &&
					(!isVisualiser || roomData[i].visualiser == 1) &&
					(!isProjector || roomData[i].projector == 1) &&
					(!isWhiteboard || roomData[i].whiteboard == 1))
						$("#room_list" + activeLists[x]).find( "select" ).append("<option value='" + roomData[i].room_code + "'>" + roomData[i].room_code + "</option>");
			
        	}
		}	
	}
   
       for(var x=0;x<activeLists.length;x++){
        		for(var y=0;y<document.getElementById('room_list').options.length;y++){
        			if(activeLists[x]==1){
        				if(document.getElementById('room_list').options[y].value == currentSelections[x]){
        					document.getElementById('room_list').options[y].selected=true;
        				}
        			}
        			else {
        				if(document.getElementById('room_list' + activeLists[x]).children[0].options[y].value == currentSelections[x]){
        					document.getElementById('room_list' + activeLists[x]).children[0].options[y].selected=true;
        				}
        			}
        		}
        }
        			
}

</script>
</head>
<body>
<div id = "top_style"  > <b><a href="login.html" style="margin-right: 140px; font-size: 12px;">Logout</a></b> </div>
<div id = "header_style" >
  <div id="title">
    <h1>Loughborough University Timetabling</h1>
  </div>
  <div id="logo"> <a href="http://www.lboro.ac.uk/?external"> <img id = "lboro_logo" src="LU-mark-rgb.png" alt="Loughborough University Logo" /> </a> </div>
</div>
<div id="main_wrap">
	<div id="tabs">
    <ul>
		<li><a href="#tabs-1">Round 1</a></li>
		<li><a href="#tabs-2">Round 2</a></li>
		<li><a href="#tabs-3">Round 3</a></li>
		<li><a href="#tabs-4">Ad-hoc Request</a></li>
    </ul>
	
    <div id="tabs-1"> <!--Tab for Round 1 -->
      
		<div class="input_boxes" >
        <div id="buttons">
			<div id="button_wrap1">
				<button id="adv_options" type="button" onclick="advToggle();"> &gt; &nbsp;&nbsp;&nbsp;&nbsp;SHOW ADVANCED OPTIONS</button>
				<button id ="All" type="button">&gt;&nbsp;&nbsp;&nbsp;&nbsp;VIEW ALL ENTRIES </button>
				<button id="Load_Last_Year" type="button" > &gt; &nbsp;&nbsp;&nbsp;&nbsp;LOAD REQUESTS</button>
			</div>
        </div>
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
							//Scott Marshall: added order by to SQL and name to the <select>. 'module_title_select' is now part of the Form Data
							echo "Module title: <select id='module_title_select' name='module_title_select' onchange='module_title_change()' >";
							//selects the module title from the databse
							$sql = "SELECT module_title FROM MODULES WHERE dept_code='$username' ORDER BY module_title ;";
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
						Week: <br/>

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
				</form>
				</div>
				<!--subdiv--> 
		</div>
      <!--input wrap--> 
    </div>
    <!--input boxes--> 
    
  </div>
  <!-- Tab 1 -->
  <div id="tabs-2"> <!--Tab for Round 2 -->
    <p>Morbi tincidunt, dui sit amet facilisis feugiat, odio metus gravida ante, ut pharetra massa metus id nunc. Duis scelerisque molestie turpis. Sed fringilla, massa eget luctus malesuada, metus eros molestie lectus, ut tempus eros massa ut dolor. Aenean aliquet fringilla sem. Suspendisse sed ligula in ligula suscipit aliquam. Praesent in eros vestibulum mi adipiscing adipiscing. Morbi facilisis. Curabitur ornare consequat nunc. Aenean vel metus. Ut posuere viverra nulla. Aliquam erat volutpat. Pellentesque convallis. Maecenas feugiat, tellus pellentesque pretium posuere, felis lorem euismod felis, eu ornare leo nisi vel felis. Mauris consectetur tortor et purus.</p>
  </div>
  <!-- Tab 2 -->
  <div id="tabs-3"> <!--Tab for Round 3 -->
    <p>Mauris eleifend est et turpis. Duis id erat. Suspendisse potenti. Aliquam vulputate, pede vel vehicula accumsan, mi neque rutrum erat, eu congue orci lorem eget lorem. Vestibulum non ante. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Fusce sodales. Quisque eu urna vel enim commodo pellentesque. Praesent eu risus hendrerit ligula tempus pretium. Curabitur lorem enim, pretium nec, feugiat nec, luctus a, lacus.</p>
    <p>Duis cursus. Maecenas ligula eros, blandit nec, pharetra at, semper at, magna. Nullam ac lacus. Nulla facilisi. Praesent viverra justo vitae neque. Praesent blandit adipiscing velit. Suspendisse potenti. Donec mattis, pede vel pharetra blandit, magna ligula faucibus eros, id euismod lacus dolor eget odio. Nam scelerisque. Donec non libero sed nulla mattis commodo. Ut sagittis. Donec nisi lectus, feugiat porttitor, tempor ac, tempor vitae, pede. Aenean vehicula velit eu tellus interdum rutrum. Maecenas commodo. Pellentesque nec elit. Fusce in lacus. Vivamus a libero vitae lectus hendrerit hendrerit.</p>
  </div>
  <!-- Tab 3 -->
  <div id="tabs-4"> <!--Tab for Round 4 --> 
  </div>
  <!-- Tab 4 --> 
</div>
</div>
</body>
</html>
<table id="resultsTable">
  <?php
		?>
</table>
</body>
</html>
