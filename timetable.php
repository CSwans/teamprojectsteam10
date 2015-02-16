
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<link rel="icon" href="lboro_logo_large.ico">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Timetable - Round 1 | Loughborough University</title>
		<link rel="stylesheet" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css"/>
		<script src="//code.jquery.com/jquery-1.10.2.js"></script>
		<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
        
		<link rel="stylesheet" href="Theme.css"/>
		<script>
			$(function() {
			//Callan Swanson, Inthuch Therdchanakul, Tom Middleton
			$( "#tabs" ).tabs({ //loads the tabs and deletes the others so we can access the variables within the other pages#
				beforeActivate: function (event, ui){
					
					if(ui.newPanel.attr('id') == "tabs-2") {
						$("#tabs-1").empty();
						$("#tabs-3").empty();
						$("#tabs-4").empty();
 						$.ajax({
							url: "Round2.php",
							success: function(data) {
								$("#tabs-2").html(data);
								partChange();
							}
						}); 

					} else if(ui.newPanel.attr('id') == "tabs-3") {
						$("#tabs-1").empty();
						$("#tabs-2").empty();
						$("#tabs-4").empty();
						$.ajax({
							url: "Round3.php",
							success: function(data) {
								$("#tabs-3").html(data);
								partChange();
							}
						});
						
					} else if(ui.newPanel.attr('id') == "tabs-4") {
						$("#tabs-1").empty();
						$("#tabs-2").empty();
						$("#tabs-3").empty();
						$.ajax({
							url: "Round4.php",
							success: function(data) {
								$("#tabs-4").html(data);
								partChange();
							}
						});
						
					}	else if(ui.newPanel.attr('id') == "tabs-1") {
						$("#tabs-2").empty();
						$("#tabs-3").empty();
						$("#tabs-4").empty();
						$.ajax({
							url: "Round1.php",
							success: function(data) {
								$("#tabs-1").html(data);
								partChange();
							}
						});
					}
				}
			});
			//loads the default tab (tab1)
			$("#tabs-1").load("Round1.php");
			partChange();
			
			
			
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
			//retrieveing info abou the modules and their titles
			
			
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
	<script src="js/jquery-1.11.1.min.js"></script>
	<script src="js/jquery-ui.js"></script>
    <script type="text/javascript">
		function ext_toggle(n) {
		    if(n==1){
				if(document.getElementById('ad_pref1').style.display==""){
					document.getElementById('expand').innerHTML="Expand ↓";
					document.getElementById('ad_pref1').style.display="none";
				}
				else {
					document.getElementById('expand').innerHTML="Hide ↑";
					document.getElementById('ad_pref1').style.display="";
				}
			}
			
			if(n==2){
				if(document.getElementById('ad_pref2').style.display==""){
					document.getElementById('expand2').innerHTML="Expand ↓";
					document.getElementById('ad_pref2').style.display="none";
				}
				else {
					document.getElementById('expand2').innerHTML="Hide ↑";
					document.getElementById('ad_pref2').style.display="";
				}
			}
			
			if(n==3){
				if(document.getElementById('ad_pref3').style.display==""){
					document.getElementById('expand3').innerHTML="Expand ↓";
					document.getElementById('ad_pref3').style.display="none";
				}
				else {
					document.getElementById('expand3').innerHTML="Hide ↑";
					document.getElementById('ad_pref3').style.display="";
				}
			}
			
			if(n==4){
				if(document.getElementById('ad_pref4').style.display==""){
					document.getElementById('expand4').innerHTML="Expand ↓";
					document.getElementById('ad_pref4').style.display="none";
				}
				else {
					document.getElementById('expand4').innerHTML="Hide ↑";
					document.getElementById('ad_pref4').style.display="";
				}
			}
		}

			
		
<?php
//pass value array onto javascript array roomData
echo "var roomData = ". $json . ";\n";
echo "var moduleData = ". $moduleJson . ";\n";
echo "var bookingData = ". $bookingJson . ";\n";
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
//Scott Marshall and Tom Middleton
function change_room_code() {
//cache user settings
var noOfRooms = parseInt(document.getElementById('noRooms').value);
var park = document.getElementById("park").value;
var capacity = parseInt(document.getElementById("capacity1").value);
var capacity2=""; if(noOfRooms > 1) capacity2 = parseInt(document.getElementById("capacity2").value);
var capacity3=""; if(noOfRooms > 2) capacity3 = parseInt(document.getElementById("capacity3").value);
var capacity4=""; if(noOfRooms > 3) capacity4 = parseInt(document.getElementById("capacity4").value);
var isWheelchair = document.getElementById("wheelchair_yes").checked;
var isWheelchair2; if(noOfRooms > 1) isWheelchair2 = document.getElementById("wheelchair_yes2").checked;
var isWheelchair3; if(noOfRooms > 2) isWheelchair3 = document.getElementById("wheelchair_yes3").checked;
var isWheelchair4; if(noOfRooms > 3) isWheelchair4 = document.getElementById("wheelchair_yes4").checked;
var isVisualiser = document.getElementById("visualiser_yes").checked;
var isVisualiser2; if(noOfRooms > 1) isVisualiser2 = document.getElementById("visualiser_yes2").checked;
var isVisualiser3; if(noOfRooms > 2) isVisualiser3 = document.getElementById("visualiser_yes3").checked;
var isVisualiser4; if(noOfRooms > 3) isVisualiser4 = document.getElementById("visualiser_yes4").checked;
var isProjector = document.getElementById("projector_yes").checked;
var isProjector2; if(noOfRooms > 1) isProjector2 = document.getElementById("projector_yes2").checked;
var isProjector3; if(noOfRooms > 2) isProjector3 = document.getElementById("projector_yes3").checked;
var isProjector4; if(noOfRooms > 3) isProjector4 = document.getElementById("projector_yes4").checked;
var isWhiteboard = document.getElementById("whiteboard_yes").checked;
var isWhiteboard2; if(noOfRooms > 1) isWhiteboard2 = document.getElementById("whiteboard_yes2").checked;
var isWhiteboard3; if(noOfRooms > 2) isWhiteboard3 = document.getElementById("whiteboard_yes3").checked;
var isWhiteboard4; if(noOfRooms > 3) isWhiteboard4 = document.getElementById("whiteboard_yes4").checked;

var n = parseInt(document.forms.requestForm.elements.day.value)-1;
var day;
if(n>-1){
day = document.forms.requestForm.elements.day[n].id;
day = day.charAt(0).toUpperCase() + day.slice(1);
}

var weeks=[];

for(var x=0;x<16;x++){
if(document.forms.requestForm.elements['weeks[]'][x].checked){
weeks.push(x+1);
}
}



var period = document.getElementById('time').selectedIndex+1;
var duration = document.getElementById('duration').selectedIndex+1;

var bookedRooms=[];

var flag=false;

for(var x=0;x<weeks.length;x++){
if(weeks[x]>0 && weeks[x]<13) flag=true;
}


for(var x=1;x<bookingData.length;x++){
for(var y=0;y<weeks.length;y++){
if(parseInt(bookingData[x].week)==weeks[y] || (bookingData[x].week=="0" && flag==true)){
if( bookingData[x].day==day){
if((parseInt(bookingData[x].period) <= period && ((parseInt(bookingData[x].period) + parseInt(bookingData[x].duration)) > period )) ||
((period+duration)> parseInt(bookingData[x].period) && period < parseInt(bookingData[x].period)+ parseInt(bookingData[x].duration))){
bookedRooms.push(bookingData[x].room_code);
}
}
}
}
}

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
if(bookedRooms.indexOf(roomData[i].room_code) == -1 && (roomData[i].capacity >= capacity || isNaN(capacity)) &&
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

for(var i=0;i<roomData.length;i++){
if(bookedRooms.indexOf(roomData[i].room_code) == -1 && (roomData[i].capacity >= capacity2 || isNaN(capacity2)) &&
(park == "Any" || park == roomData[i].park) &&
(!isWheelchair2 || roomData[i].wheelchair == 1) &&
(!isVisualiser2 || roomData[i].visualiser == 1) &&
(!isProjector2 || roomData[i].projector == 1) &&
(!isWhiteboard2 || roomData[i].whiteboard == 1))
$("#room_list2").find( "select" ).append("<option value='" + roomData[i].room_code + "'>" + roomData[i].room_code + "</option>");
}

//else {}
for(var x=1;x<4;x++){
document.getElementById('room_list'+ (x+1)).style.display='none';
document.getElementById('roomlabel'+ (x+1)).style.display='none';
document.getElementById('advancedinputs'+ (x+1)).style.display='none';
}
noOfRooms = parseInt(document.getElementById('noRooms').value);
if(noOfRooms>1){
for(var x=1;x<noOfRooms;x++){
document.getElementById('room_list'+ (x+1)).style.display='block';
document.getElementById('roomlabel'+ (x+1)).style.display='block';
document.getElementById('advancedinputs'+ (x+1)).style.display='block';
}
}
}
else {
for(var x=1;x<4;x++){
document.getElementById('room_list'+ (x+1)).style.display='none';
document.getElementById('roomlabel'+ (x+1)).style.display='none';
document.getElementById('advancedinputs'+ (x+1)).style.display='none';
}
}
if(parseInt(document.getElementById('noRooms').value) > 2){
for(var i=0;i<roomData.length;i++){

if(bookedRooms.indexOf(roomData[i].room_code) == -1 && (roomData[i].capacity >= capacity3 || isNaN(capacity3)) &&
(park == "Any" || park == roomData[i].park) &&
(!isWheelchair3 || roomData[i].wheelchair == 1) &&
(!isVisualiser3 || roomData[i].visualiser == 1) &&
(!isProjector3 || roomData[i].projector == 1) &&
(!isWhiteboard3 || roomData[i].whiteboard == 1))
$("#room_list3").find( "select" ).append("<option value='" + roomData[i].room_code + "'>" + roomData[i].room_code + "</option>");

}
}
if(parseInt(document.getElementById('noRooms').value) > 3){

for(var i=0;i<roomData.length;i++){
if(bookedRooms.indexOf(roomData[i].room_code) == -1 && (roomData[i].capacity >= capacity4 || isNaN(capacity))&&
(park == "Any" || park == roomData[i].park) &&
(!isWheelchair4 || roomData[i].wheelchair == 1) &&
(!isVisualiser4 || roomData[i].visualiser == 1) &&
(!isProjector4 || roomData[i].projector == 1) &&
(!isWhiteboard4 || roomData[i].whiteboard == 1))
$("#room_list4").find( "select" ).append("<option value='" + roomData[i].room_code + "'>" + roomData[i].room_code + "</option>");
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
var capacityName = "capacity" + (i);
if(i == 0){
//$("#labelCell").append('1:'); 
$("#capacityCell").append(' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Capacity: </br> 1) <input name="capacity" type="text" onchange="change_room_code();" id="' + capacityID + '"/><br/>');
}
else {
//$("#labelCell").append('<br>'+(i+1)+ ':'); 
$("#capacityCell").append( (i+1) + ') <input name="'+ capacityName +'" type="text" onchange="change_room_code();" id="' + capacityID + '"/><br/>');
}
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

var activeLists = [];
var currentSelections = [];

var cap1= parseInt(document.getElementById('capacity1').value);
if(noOfRooms >=2)var cap2= parseInt(document.getElementById('capacity2').value);
if(noOfRooms >=3) var cap3= parseInt(document.getElementById('capacity3').value);
if(noOfRooms >=4) var cap4= parseInt(document.getElementById('capacity4').value);

var sel1 = document.getElementById('room_list').value;
var sel2= document.getElementById('room_list2').children[0].value;
var sel3 = document.getElementById('room_list3').children[0].value
var sel4 = document.getElementById('room_list4').children[0].value

activeLists.push(1); currentSelections.push(sel1);
if(cap2 != null && cap2 != ''){ activeLists.push(2); currentSelections.push(sel2); }
if(cap3 != null && cap3 != ''){ activeLists.push(3); currentSelections.push(sel3); }
if(cap4 != null && cap4 != ''){ activeLists.push(4); currentSelections.push(sel4); }

var isWheelchair = document.getElementById("wheelchair_yes").checked;
var isWheelchair2; if(noOfRooms >=2) var isWheelchair2 = document.getElementById("wheelchair_yes2").checked;
var isWheelchair3; if(noOfRooms >=3) var isWheelchair3 = document.getElementById("wheelchair_yes3").checked;
var isWheelchair4; if(noOfRooms >=4) var isWheelchair4 = document.getElementById("wheelchair_yes4").checked;

var isVisualiser = document.getElementById("visualiser_yes").checked;
var isVisualiser2; if(noOfRooms >=2) var isVisualiser2 = document.getElementById("visualiser_yes2").checked;
var isVisualiser3; if(noOfRooms >=3) var isVisualiser3 = document.getElementById("visualiser_yes3").checked;
var isVisualiser4; if(noOfRooms >=4) var isVisualiser4 = document.getElementById("visualiser_yes4").checked;

var isProjector = document.getElementById("projector_yes").checked;
var isProjector2; if(noOfRooms >=2) var isProjector2 = document.getElementById("projector_yes2").checked;
var isProjector3; if(noOfRooms >=3) var isProjector3 = document.getElementById("projector_yes3").checked;
var isProjector4; if(noOfRooms >=4) var isProjector4 = document.getElementById("projector_yes4").checked;

var isWhiteboard = document.getElementById("whiteboard_yes").checked;
var isWhiteboard2; if(noOfRooms >=2) var isWhiteboard2 = document.getElementById("whiteboard_yes2").checked;
var isWhiteboard3; if(noOfRooms >=3) var isWhiteboard3 = document.getElementById("whiteboard_yes3").checked;
var isWhiteboard4; if(noOfRooms >=4) var isWhiteboard4 = document.getElementById("whiteboard_yes4").checked;


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
if((roomData[i].capacity >= cap1 || isNaN(cap1))&&
(currentSelections.indexOf(roomData[i].room_code) == -1 || currentSelections.indexOf(roomData[i].room_code) == x) &&
(park == "Any" || park == roomData[i].park) &&
(!isWheelchair || roomData[i].wheelchair == 1) &&
(!isVisualiser || roomData[i].visualiser == 1) &&
(!isProjector || roomData[i].projector == 1) &&
(!isWhiteboard || roomData[i].whiteboard == 1))
$("#room_list").append("<option value='" + roomData[i].room_code + "'>" + roomData[i].room_code + "</option>");
}
if(activeLists[x]==2) {
if((roomData[i].capacity >= cap2 || isNaN(cap2))&&
(currentSelections.indexOf(roomData[i].room_code) == -1 || currentSelections.indexOf(roomData[i].room_code) == x) &&
(park == "Any" || park == roomData[i].park) &&
(!isWheelchair2 || roomData[i].wheelchair == 1) &&
(!isVisualiser2 || roomData[i].visualiser == 1) &&
(!isProjector2 || roomData[i].projector == 1) &&
(!isWhiteboard2 || roomData[i].whiteboard == 1))
$("#room_list" + activeLists[x]).find( "select" ).append("<option value='" + roomData[i].room_code + "'>" + roomData[i].room_code + "</option>");

}
if(activeLists[x]==3){
if((roomData[i].capacity >= cap3 || isNaN(cap3)) &&
(currentSelections.indexOf(roomData[i].room_code) == -1 || currentSelections.indexOf(roomData[i].room_code) == x) &&
(park == "Any" || park == roomData[i].park) &&
(!isWheelchair3 || roomData[i].wheelchair == 1) &&
(!isVisualiser3 || roomData[i].visualiser == 1) &&
(!isProjector3 || roomData[i].projector == 1) &&
(!isWhiteboard3 || roomData[i].whiteboard == 1))
$("#room_list" + activeLists[x]).find( "select" ).append("<option value='" + roomData[i].room_code + "'>" + roomData[i].room_code + "</option>");
}
if(activeLists[x]==4){
if((roomData[i].capacity >= cap4 || isNaN(cap4))&&
(currentSelections.indexOf(roomData[i].room_code) == -1 || currentSelections.indexOf(roomData[i].room_code) == x) &&
(park == "Any" || park == roomData[i].park) &&
(!isWheelchair4 || roomData[i].wheelchair == 1) &&
(!isVisualiser4 || roomData[i].visualiser == 1) &&
(!isProjector4 || roomData[i].projector == 1) &&
(!isWhiteboard4 || roomData[i].whiteboard == 1))
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
	//changes the values within the dropdown to correspond to the part chosen
	//Callan Swanson , Inthuch Therdhchanakul
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
	function ajaxFunction(){
	
		//finds the day that has been chosen
		var dayChosen = null;
		if(document.getElementById("monday").checked) {
			dayChosen = "Monday";
		} else if(document.getElementById("tuesday").checked) {
			dayChosen = "Tuesday";
		} else if(document.getElementById("wednesday").checked) {
			dayChosen = "Wednesday";
		} else if(document.getElementById("thursday").checked) {
			dayChosen = "Thursday";
		} else if(document.getElementById("friday").checked) {
			dayChosen = "Friday";
		}
		if (dayChosen == null) { //stops if there is no day input
			return(alert("Please Enter a day!"));
		}
		
		var roomChosen = document.getElementById("room_list");
		
		//checks the room capacity asked for
		var noRoom = document.getElementById("noRooms");
		if(document.getElementById("capacity1").value > 1000 || document.getElementById("capacity1").value < 1) {
			return(alert("Please enter a suitable capacity"));
		}
		if(noRoom == 2) {
			if(document.getElementById("capacity2").value > 1000 || document.getElementById("capacity2").value < 1)
				return(alert("Please enter a suitable capacity 2"));
		}
		if(noRoom == 3) {
			if(document.getElementById("capacity3").value > 1000 || document.getElementById("capacity3").value < 1)
				return(alert("Please enter a suitable capacity 3"));
		}
		if(noRoom ==4 ) {
			if(document.getElementById("capacity4").value > 1000 || document.getElementById("capacity4").value < 1)
				return(alert("Please enter a suitable capacity 4"));
		}
		
		//alert(document.getElementById("week").checked);
		if(document.getElementById("week").checked == false) {
			//return(alert("Please enter a week you wish to book for"));
		}
		
		var checked = false;
		$('#requestForm  input[type="checkbox"]').each(function() {
			if ($(this).is(":checked")) {
				checked = true;
			}
		});
 
		if (checked == false) {
			return(alert("Please enter a week"));
		} 
	
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
	
	function validationBeforeSend() {
		
	}
	
	function adhocAjaxFunction(){
		
		var noRoom = parseInt(document.getElementById("noRooms").value);
		
		if(document.getElementById("room_list").value=="") return(alert("Please specify a room!"));
		
		if(noRoom == 2) {
				if(document.getElementById("room_list_2").value=="") {
					 return(alert("Please specify all room choices!"));
				}
		}
		
		if(noRoom == 3) {
				if(document.getElementById("room_list_3").value=="") {
					 return(alert("Please specify all room choices!"));
				}
		}
		
		if(noRoom == 4) {
				if(document.getElementById("room_list_4").value=="") {
					 return(alert("Please specify all room choices!"));
				}
		}
		
		
		
		//finds the day that has been chosen
		var dayChosen = null;
		if(document.getElementById("monday").checked) {
			dayChosen = "Monday";
		} else if(document.getElementById("tuesday").checked) {
			dayChosen = "Tuesday";
		} else if(document.getElementById("wednesday").checked) {
			dayChosen = "Wednesday";
		} else if(document.getElementById("thursday").checked) {
			dayChosen = "Thursday";
		} else if(document.getElementById("friday").checked) {
			dayChosen = "Friday";
		}
		if (dayChosen == null) { //stops if there is no day input
			return(alert("Please Enter a day!"));
		}
		
		var roomChosen = document.getElementById("room_list");
		
		//checks the room capacity asked for
		if(document.getElementById("capacity1").value > 1000 || document.getElementById("capacity1").value < 1) {
			return(alert("Please enter a suitable capacity"));
		}
		if(noRoom == 2) {
			if(document.getElementById("capacity2").value > 1000 || document.getElementById("capacity2").value < 1)
				return(alert("Please enter a suitable capacity 2"));
		}
		if(noRoom == 3) {
			if(document.getElementById("capacity3").value > 1000 || document.getElementById("capacity3").value < 1)
				return(alert("Please enter a suitable capacity 3"));
		}
		if(noRoom ==4 ) {
			if(document.getElementById("capacity4").value > 1000 || document.getElementById("capacity4").value < 1)
				return(alert("Please enter a suitable capacity 4"));
		}
		
		//alert(document.getElementById("week").checked);
		if(document.getElementById("week").checked == false) {
			//return(alert("Please enter a week you wish to book for"));
		}
		
		var checked = false;
		$('#requestForm  input[type="checkbox"]').each(function() {
			if ($(this).is(":checked")) {
				checked = true;
			}
		});
 
		if (checked == false) {
			return(alert("Please enter a week"));
		} 
		
		
		$.ajax( {
			url : "insertInfoAdhoc.php",
			type : "POST", 
			data : $("#requestForm").serialize(),
			success : function (data){					
					data = JSON.parse(data);
					alert("Booking submitted successfully.");
					console.log("data "+data); //quick check
					
				},
			error : function(jqXHR, textStatus, errorThrown) {
			}
		});
	}
	
	function loadRequest(){
		$.ajax( {
			url : "loadRequest.php",
			type : "POST", 
			data : $("#lastYear").serialize(),
			success : function (data){					
					data = JSON.parse(data);
					alert("Request has been loaded successfully");
					console.log("data "+data); //quick check
					
				},
			error : function(jqXHR, textStatus, errorThrown) {
			}
		});
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
</head>
<body>
<div id="top_style">
<div  align="middle" style="top:0; width: 50px; float: left; margin-left: 165px;">  
<a onclick="goBack();"> <img width="30" height="20" border="0" alt="Back" src="Back_Arrow.png" align="middle" style=" cursor: pointer;"> </a> </div>
<a href="timetable.php"> <img width="40" height="40" border="0" alt="Home!" src="Home_Button.png" align="left"> </a> 
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;    
<input class='class_change_passoword' id='id_change_password' type="button" value="change password" onclick='$("#changePWordForm").dialog("open");'>
<b> <a href="login.html" style="margin-right: 140px; font-weight: 900; font-size: 1em;" onclick='return logout_question();'>Logout</a></b>  </div>
<div id = "header_style" >
  <div id="title">
	 <!-- Nikolaos Demosthenous	Riccardo Mangiapelo Tom Middleton Inthuch Therdchanakul-->
	  <!--Adding the department name on the header  -->
    <h1>Loughborough University Timetabling </h1><h2> <?php $dept_code = strtolower($username); $sql = "SELECT dept_name FROM DEPT WHERE dept_code = '$dept_code' "; 		$res =& $db->query($sql); //getting the result from the database
		if(PEAR::isError($res)){
			die($res->getMessage());
		}
		//put each rows into value array
		while($row = $res->fetchRow()){
			echo $row["dept_name"];
		}  ?>   <br/> </h2> 
  </div>
  <div id="logo"> <a href="http://www.lboro.ac.uk/?external"> <img id = "lboro_logo" src="LU-mark-rgb.png" alt="Loughborough University Logo" /> </a> </div>
</div>
<div id="main_wrap">
	<div id="tabs">
    <ul >
		<li id="tab1"><a href="#tabs-1">Round 1</a></li>
		<li id="tab2" ><a href="#tabs-2" >Round 2</a></li>
		<li id="tab3"><a href="#tabs-3">Round 3</a></li>
		<li id="tab4"><a href="#tabs-4">Ad-hoc Request</a></li>
    </ul>
	
    <div id="tabs-1"> <!--Tab for Round 1 -->		
    
	</div>
    <!-- Tab 1 -->
    <div id="tabs-2"> <!--Tab for Round 2 -->
		
	</div>
	<!-- Tab 2 -->
	<div id="tabs-3"> <!--Tab for Round 3 -->
		
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
	  
	</table>
