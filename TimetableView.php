<!doctype html>
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
			
			
			$sql = "SELECT REQUEST.request_id, module_code, REQUEST.room_code, capacity, wheelchair, projector, visualiser, whiteboard, special_requirements, priority, period, day, duration,GROUP_CONCAT(CONVERT(REQUEST_WEEKS.week, CHAR(8)) SEPARATOR ', ') AS week FROM REQUEST,REQUEST_WEEKS WHERE REQUEST.request_id = REQUEST_WEEKS.request_id AND dept_code = '".$username."'GROUP BY request_id";
			$res =& $db->query($sql); //getting the result from the database
			if(PEAR::isError($res)){
				die($res->getMessage());
			}
			$value = array();
			$fullList=array();
			
			//put each rows into value array
			while($row = $res->fetchRow()){
				$value[] = $row;
				$fullList[] = $row;
			}
			
			$jsonRequests = json_encode($value);
			$jsonFullData = json_encode($fullList);
			
			$sql = "SELECT REQUEST.request_id, module_code, BOOKING.room_code, capacity, wheelchair, projector, visualiser, whiteboard, special_requirements, priority, period, day, duration,GROUP_CONCAT(CONVERT(REQUEST_WEEKS.week, CHAR(8)) SEPARATOR ',') AS week, CASE WHEN REQUEST.room_code = BOOKING.room_code THEN 0 ELSE 1 END AS partial FROM REQUEST,REQUEST_WEEKS, BOOKING WHERE REQUEST.request_id = REQUEST_WEEKS.request_id AND dept_code = '".$username."' AND BOOKING.request_id = REQUEST.request_id GROUP BY request_id";
			$res =& $db->query($sql); //getting the result from the database
			if(PEAR::isError($res)){
				die($res->getMessage());
			}
			$value2 = array();
			
			//put each rows into value array
			while($row = $res->fetchRow()){
				$value2[] = $row; //booked
			}
			
			$jsonBookings = json_encode($value2);
			
			
			$sql = "SELECT REQUEST.request_id, module_code, REQUEST.room_code, capacity, wheelchair, projector, visualiser, whiteboard, special_requirements, priority, period, day, duration,GROUP_CONCAT(CONVERT(REQUEST_WEEKS.week, CHAR(8)) SEPARATOR ',') AS week  FROM REQUEST,REQUEST_WEEKS, REJECTION WHERE REQUEST.request_id = REQUEST_WEEKS.request_id AND dept_code = '".$username."' AND REJECTION.request_id = REQUEST.request_id GROUP BY request_id";
			$res =& $db->query($sql); //getting the result from the database
			if(PEAR::isError($res)){
				die($res->getMessage());
			}
			$value3 = array(); //rejected
			
			//put each rows into value array
			while($row = $res->fetchRow()){
				$value3[] = $row;
			}
			
			$jsonRejections = json_encode($value3);
			
			$sql = "SELECT module_code, module_title FROM MODULES WHERE dept_code='$username' ORDER BY module_code;";
			$res =& $db->query($sql); 
			if(PEAR::isError($res)){
				die($res->getMessage());
			}
			$moduleInfo = array();
			while($row = $res->fetchRow()){
				$moduleInfo[] = $row;
			}
			$moduleJson = json_encode($moduleInfo);
			
			$sql = "SELECT DISTINCT ROOMS.capacity, wheelchair, projector, visualiser, whiteboard, PARKS.park, ROOMS.room_code, ROOMS.building_code FROM ROOMS, PARKS WHERE ROOMS.building_code = PARKS.building_code";
			$res =& $db->query($sql); //getting the result from the database
			if(PEAR::isError($res)){
				die($res->getMessage());
			}
			$roomData = array();
			
			while($row = $res->fetchRow()){
				$roomData[] = $row;
			}
			$roomDataJson = json_encode($roomData);
		
		$sql = "SELECT * FROM `REQUEST_WEEKS`; ";
		$res =& $db->query($sql); //getting the result from the database
		if(PEAR::isError($res)){
			die($res->getMessage());
		}
		$value4 = array();
		//put each rows into value array
		while($row = $res->fetchRow()){
			$value5[] = $row;
		}
	
		$weeksJson = json_encode($value5);
		
		$sql = "SELECT * FROM PARKS";
			$res =& $db->query($sql); //getting the result from the database
			if(PEAR::isError($res)){
				die($res->getMessage());
			}
			$buildingData = array();
			//put each rows into value array
			while($row = $res->fetchRow()){
				$buildingData[] = $row;
			}
			$buildingJson = json_encode($buildingData);
			
			
		?>
		<script src="//code.jquery.com/jquery-1.10.2.js"></script>
		<script src="js/jquery-1.11.1.min.js"></script>
		<script src="js/jquery-ui.js"></script>
		<script src="js/jquery.serializejson.min.js"></script>
		<script type="text/javascript">
		
		
			<?php
				echo "var requestData = ".$jsonRequests.";\n"; //WILL CHANGE TO HOLD THE PENDING DATA WHEN PAGE LOADS
				echo "var bookingData = ".$jsonBookings.";\n";
				echo "var rejectedData = ".$jsonRejections.";\n";
				echo "var fullData = ".$jsonFullData.";\n";
				echo "var moduleData = ".$moduleJson.";\n";
				echo "var roomData = ".$roomDataJson.";\n";
				echo "var weeksData = ".$weeksJson.";\n";
				echo "var buildingData = ".$buildingJson.";\n";
			?>
			
			$(function() {
				findPendings();
			});
			
			$(function() {
				//console.log(bookingData);
				//apply dialo				
				$("#dialog-form1").dialog({
					modal:true,
					height: 500,

					width: 700,
					position: { my:"center",
								at: "top",
								of: window}	
				});
			
				//hide dialog
				$("#dialog-form1").dialog("close");
				populateTable();
				findPendings();
				buildingInitialise();
				
				 
			});
			
			function updateBookedAjax() {
				//validation
				if(document.getElementById("capacity1").value > 1000 || document.getElementById("capacity1").value < 1) {
					return(alert("Please enter a suitable capacity"));
				}
				
				
				var checked = false;
				$('#editForm  input[type="checkbox"]').each(function() {
					if ($(this).is(":checked")) {
						checked = true;
					}
				});
			
				$.ajax({
				url : "updateBookingInfo.php",
				type : "POST", 
				data : $("#editForm").serialize(),
				success : function (data){					
						data = JSON.parse(data);
						alert("Request submitted with request id " + data[data.length-1].request_id);
						closeDialog();
						window.location.href = "http://co-project.lboro.ac.uk/team10/Deliverable2/ViewRequests.php";
						//redirects to the full view once one has been deleted
					},
				error : function(jqXHR, textStatus, errorThrown) {
				}
				});
				
			}
			
			function moduleList() {
				for(var x=0;x<moduleData.length;x++){
					$('#moduleCodeList').append("<option>"+moduleData[x].module_code+"</option>");
				}
				for(var x=0;x<moduleData.length;x++){
					$('#moduleTitleList').append("<option>"+moduleData[x].module_title+"</option>");
				}
				for(var x=0;x<moduleData.length;x++){
					$('#roomList').append("<option>"+roomData[x].room_code+"</option>");
				}
			}
			
			function module_code_change() {
				var index = document.getElementById("moduleCodeList").selectedIndex;
				document.getElementById("moduleTitleList").selectedIndex = index;
			}

			function module_title_change() {
				var index = document.getElementById("moduleTitleList").selectedIndex;
				document.getElementById("moduleCodeList").selectedIndex = index;
			}
			
			/*	function partChange() {
		//looks through all of the moduleData
		$("#moduleCodeList").empty();
		$("#moduleTitleList").empty();
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
				$("#moduleCodeList").append("<option>"+moduleData[i].module_code+"</option>");
				$("#moduleTitleList").append("<option>"+moduleData[i].module_title+"</option>");
			} else if(checkedVal == "All") {
				$("#moduleCodeList").append("<option>"+moduleData[i].module_code+"</option>");
				$("#moduleTitleList").append("<option>"+moduleData[i].module_title+"</option>");
			}
		}
	}
	*/
	
			function findPendings() {
				for(var i=0; i<requestData.length; i++) {
					for(var j=0; j<bookingData.length; j++) {
						if(requestData[i].request_id == bookingData[j].request_id) {
							console.log(requestData[i]);
							requestData.splice(i,1);
						}
					}
				}
				console.log(requestData);
				for(var i=0; i<requestData.length; i++) {
					for(var j=0; j<rejectedData.length; j++) {
						if(requestData[i].request_id == rejectedData[j].request_id) {
							console.log(requestData[i]);
							requestData.splice(i,1);
						}
					}
				}
				console.log("REJECTED");
				console.log(rejectedData);
				console.log('REQUEST');
				console.log(requestData);
			}
			
				
			
			function initialise() {
				document.getElementById('current_week').value = 1;
			}
			
			function statusChange() {
				var stat = document.getElementById('statusList').selectedIndex;
				
				if(stat==0) {rejected_grid_view();}
				if(stat==1) {booked_grid_view();}
				if(stat==2) {pending_grid_view();}
			}
			
			function booked_grid_view(){
var sort = document.getElementById('sortList').selectedIndex;
var part;
var module;
var room;
if(sort==0) {
var radios = document.getElementsByName('partCode');
for(var x=0;x<radios.length;x++){
if (radios[x].checked) {
part=document.getElementsByName('partCode')[x].value;
}
}
}
if(sort==1) {
module=document.getElementById('moduleCodeList').value;
}
if(sort==2) {
room=document.getElementById('roomList').value;
}
var myNode = document.getElementById("Monday");
myNode.innerHTML = '<td class="day">Monday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
var myNode = document.getElementById("Tuesday");
myNode.innerHTML = '<td class="day">Tuesday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
var myNode = document.getElementById("Wednesday");
myNode.innerHTML = '<td class="day">Wednesday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
var myNode = document.getElementById("Thursday");
myNode.innerHTML = '<td class="day">Thursday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
var myNode = document.getElementById("Friday");
myNode.innerHTML = '<td class="day">Friday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
var n = parseInt(document.getElementById('current_week').value);
for(var x = 0;x<bookingData.length;x++) {
var weeks = bookingData[x].week;
var day = bookingData[x].day;
var period = parseInt(bookingData[x].period);
var duration = parseInt(bookingData[x].duration);
var weekArr = weeks.split(",");
for(var i=0;i<weekArr.length;i++){
weekArr[i]=parseInt(weekArr[i]);
if(weekArr[i]==0){
weekArr=[];
weekArr.push(1,2,3,4,5,6,7,8,9,10,11,12);
}
}
if(weekArr.indexOf(n)> -1){
if(duration>1){
for(var n =0;n<duration;n++){
if(part != null && bookingData[x].module_code.charAt(4)==part){
document.getElementById(day).children['p'+(period+n)].innerHTML=document.getElementById(day).children['p'+(period+n)].innerHTML+'<p id="'+ bookingData[x].request_id +'" onclick="showDialog(this);"> Request ID: <span id="tableP">&nbsp;'+bookingData[x].request_id+'</span><br/> Module code: <span id="tableP">'+bookingData[x].module_code+'</span><br/> Room code: <span id="tableP">'+bookingData[x].room_code+'</span></p>';
}
if(module != null && bookingData[x].module_code==module){
document.getElementById(day).children['p'+(period+n)].innerHTML=document.getElementById(day).children['p'+(period+n)].innerHTML+'<p id="'+ bookingData[x].request_id +'" onclick="showDialog(this);"> Request ID: <span id="tableP">&nbsp;'+bookingData[x].request_id+'</span><br/> Module code: <span id="tableP">'+bookingData[x].module_code+'</span><br/> Room code: <span id="tableP">'+bookingData[x].room_code+'</span></p>';
}
if(room != null && bookingData[x].room_code==room){
document.getElementById(day).children['p'+(period+n)].innerHTML=document.getElementById(day).children['p'+(period+n)].innerHTML+'<p id="'+ bookingData[x].request_id +'" onclick="showDialog(this);"> Request ID: <span id="tableP">&nbsp;'+bookingData[x].request_id+'</span><br/> Module code: <span id="tableP">'+bookingData[x].module_code+'</span><br/> Room code: <span id="tableP">'+bookingData[x].room_code+'</span></p>';
}
}
}
else {
if(part != null && bookingData[x].module_code.charAt(4)==part) {
document.getElementById(day).children['p'+period].innerHTML=document.getElementById(day).children['p'+period].innerHTML+'<p id="'+ bookingData[x].request_id +'" onclick="showDialog(this);"> Request ID: <span id="tableP">&nbsp;'+bookingData[x].request_id+'</span><br/> Module code: <span id="tableP">'+bookingData[x].module_code+'</span><br/> Room code: <span id="tableP">'+bookingData[x].room_code+'</span></p>';
}
if(module != null && bookingData[x].module_code==module){
document.getElementById(day).children['p'+period].innerHTML=document.getElementById(day).children['p'+period].innerHTML+'<p id="'+ bookingData[x].request_id +'" onclick="showDialog(this);"> Request ID: <span id="tableP">&nbsp;'+bookingData[x].request_id+'</span><br/> Module code: <span id="tableP">'+bookingData[x].module_code+'</span><br/> Room code: <span id="tableP">'+bookingData[x].room_code+'</span></p>';
}
if(room != null && bookingData[x].room_code==room){
document.getElementById(day).children['p'+period].innerHTML=document.getElementById(day).children['p'+period].innerHTML+'<p id="'+ bookingData[x].request_id +'" onclick="showDialog(this);"> Request ID: <span id="tableP">&nbsp;'+bookingData[x].request_id+'</span><br/> Module code: <span id="tableP">'+bookingData[x].module_code+'</span><br/> Room code: <span id="tableP">'+bookingData[x].room_code+'</span></p>';
}
}
}
}
}
function pending_grid_view(){
var sort = document.getElementById('sortList').selectedIndex;
var part;
var module;
var room;
if(sort==0) {
var radios = document.getElementsByName('partCode');
for(var x=0;x<radios.length;x++){
if (radios[x].checked) {
part=document.getElementsByName('partCode')[x].value;
}
}
}
if(sort==1) {
module=document.getElementById('moduleCodeList').value;
}
if(sort==2) {
room=document.getElementById('roomList').value;
}
var myNode = document.getElementById("Monday");
myNode.innerHTML = '<td class="day" >Monday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
var myNode = document.getElementById("Tuesday");
myNode.innerHTML = '<td class="day" >Tuesday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
var myNode = document.getElementById("Wednesday");
myNode.innerHTML = '<td class="day" >Wednesday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
var myNode = document.getElementById("Thursday");
myNode.innerHTML = '<td class="day" >Thursday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
var myNode = document.getElementById("Friday");
myNode.innerHTML = '<td class="day" >Friday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
var n = parseInt(document.getElementById('current_week').value);
for(var x = 0;x<requestData.length;x++) {
var weeks = requestData[x].week;
var day = requestData[x].day;
var period = parseInt(requestData[x].period);
var duration = parseInt(requestData[x].duration);
var weekArr = weeks.split(",");
for(var i=0;i<weekArr.length;i++){
weekArr[i]=parseInt(weekArr[i]);
if(weekArr[i]==0){
weekArr=[];
weekArr.push(1,2,3,4,5,6,7,8,9,10,11,12);
}
}
if(weekArr.indexOf(n)> -1){
if(duration>1){
for(var n =0;n<duration;n++){
if(part != null && requestData[x].module_code.charAt(4)==part){
document.getElementById(day).children['p'+(period+n)].innerHTML=document.getElementById(day).children['p'+(period+n)].innerHTML+'<p id="'+ requestData[x].request_id +'" onclick="showDialog(this);"> Request ID: <span id="tableP">&nbsp;'+requestData[x].request_id+'</span><br/> Module code: <span id="tableP">'+requestData[x].module_code+'</span><br/> Room code: <span id="tableP">'+requestData[x].room_code+'</span></p>';
}
if(module != null && requestData[x].module_code==module){
document.getElementById(day).children['p'+(period+n)].innerHTML=document.getElementById(day).children['p'+(period+n)].innerHTML+'<p id="'+ requestData[x].request_id +'" onclick="showDialog(this);"> Request ID: <span id="tableP">&nbsp;'+requestData[x].request_id+'</span><br/> Module code: <span id="tableP">'+requestData[x].module_code+'</span><br/> Room code: <span id="tableP">'+requestData[x].room_code+'</span></p>';
}
if(room != null && requestData[x].room_code==room){
document.getElementById(day).children['p'+(period+n)].innerHTML=document.getElementById(day).children['p'+(period+n)].innerHTML+'<p id="'+ requestData[x].request_id +'" onclick="showDialog(this);"> Request ID: <span id="tableP">&nbsp;'+requestData[x].request_id+'</span><br/> Module code: <span id="tableP">'+requestData[x].module_code+'</span><br/> Room code: <span id="tableP">'+requestData[x].room_code+'</span></p>';
}
}

}
else {
if(part != null && requestData[x].module_code.charAt(4)==part) {
document.getElementById(day).children['p'+period].innerHTML=document.getElementById(day).children['p'+period].innerHTML+'<p id="'+ requestData[x].request_id +'" onclick="showDialog(this);"> Request ID: <span id="tableP">&nbsp;'+requestData[x].request_id+'</span><br/> Module code: <span id="tableP">'+requestData[x].module_code+'</span><br/> Room code: <span id="tableP">'+requestData[x].room_code+'</span></p>';
}
if(module != null && requestData[x].module_code==module){
document.getElementById(day).children['p'+period].innerHTML=document.getElementById(day).children['p'+period].innerHTML+'<p id="'+ requestData[x].request_id +'" onclick="showDialog(this);"> Request ID: <span id="tableP">&nbsp;'+requestData[x].request_id+'</span><br/> Module code: <span id="tableP">'+requestData[x].module_code+'</span><br/> Room code: <span id="tableP">'+requestData[x].room_code+'</span></p>';
}
if(room != null && requestData[x].room_code==room){
document.getElementById(day).children['p'+period].innerHTML=document.getElementById(day).children['p'+period].innerHTML+'<p id="'+ requestData[x].request_id +'" onclick="showDialog(this);"> Request ID: <span id="tableP">&nbsp;'+requestData[x].request_id+'</span><br/> Module code: <span id="tableP">'+requestData[x].module_code+'</span><br/> Room code: <span id="tableP">'+requestData[x].room_code+'</span></p>';
}
}
}
}
}
function rejected_grid_view(){
var sort = document.getElementById('sortList').selectedIndex;
var part;
var module;
var room;
if(sort==0) {
var radios = document.getElementsByName('partCode');
for(var x=0;x<radios.length;x++){
if (radios[x].checked) {
part=document.getElementsByName('partCode')[x].value;
}
}
}
if(sort==1) {
module=document.getElementById('moduleCodeList').value;
}
if(sort==2) {
room=document.getElementById('roomList').value;
}
var myNode = document.getElementById("Monday");
myNode.innerHTML = '<td class="day">Monday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
var myNode = document.getElementById("Tuesday");
myNode.innerHTML = '<td class="day">Tuesday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
var myNode = document.getElementById("Wednesday");
myNode.innerHTML = '<td class="day">Wednesday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
var myNode = document.getElementById("Thursday");
myNode.innerHTML = '<td class="day">Thursday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
var myNode = document.getElementById("Friday");
myNode.innerHTML = '<td class="day">Friday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
var n = parseInt(document.getElementById('current_week').value);
for(var x = 0;x<rejectedData.length;x++) {
var weeks = rejectedData[x].week;
var day = rejectedData[x].day;
var period = parseInt(rejectedData[x].period);
var duration = parseInt(rejectedData[x].duration);
var weekArr = weeks.split(",");
for(var i=0;i<weekArr.length;i++){
weekArr[i]=parseInt(weekArr[i]);
if(weekArr[i]==0){
weekArr=[];
weekArr.push(1,2,3,4,5,6,7,8,9,10,11,12);
}
}
if(weekArr.indexOf(n)> -1){
if(duration>1){
for(var n =0;n<duration;n++){
if(part != null && rejectedData[x].module_code.charAt(4)==part){
document.getElementById(day).children['p'+(period+n)].innerHTML=document.getElementById(day).children['p'+(period+n)].innerHTML+'<p id="'+ rejectedData[x].request_id +'" onclick="showDialog(this);"> Request ID: <span id="tableP">&nbsp;'+rejectedData[x].request_id+'</span><br/> Module code: <span id="tableP">'+rejectedData[x].module_code+'</span><br/> Room code: <span id="tableP">'+rejectedData[x].room_code+'</span></p>';
}
if(module != null && rejectedData[x].module_code==module){
document.getElementById(day).children['p'+(period+n)].innerHTML=document.getElementById(day).children['p'+(period+n)].innerHTML+'<p id="'+ rejectedData[x].request_id +'" onclick="showDialog(this);"> Request ID: <span id="tableP">&nbsp;'+rejectedData[x].request_id+'</span><br/> Module code: <span id="tableP">'+rejectedData[x].module_code+'</span><br/> Room code: <span id="tableP">'+rejectedData[x].room_code+'</span></p>';
}
if(room != null && rejectedData[x].room_code==room){
document.getElementById(day).children['p'+(period+n)].innerHTML=document.getElementById(day).children['p'+(period+n)].innerHTML+'<p id="'+ rejectedData[x].request_id +'" onclick="showDialog(this);"> Request ID: <span id="tableP">&nbsp;'+rejectedData[x].request_id+'</span><br/> Module code: <span id="tableP">'+rejectedData[x].module_code+'</span><br/> Room code: <span id="tableP">'+rejectedData[x].room_code+'</span></p>';
}
}
}
else {
if(part != null && rejectedData[x].module_code.charAt(4)==part) {
document.getElementById(day).children['p'+period].innerHTML=document.getElementById(day).children['p'+period].innerHTML+'<p id="'+ rejectedData[x].request_id +'" onclick="showDialog(this);"> Request ID: <span id="tableP">&nbsp;'+rejectedData[x].request_id+'</span><br/> Module code: <span id="tableP">'+rejectedData[x].module_code+'</span><br/> Room code: <span id="tableP">'+rejectedData[x].room_code+'</span></p>';
}
if(module != null && rejectedData[x].module_code==module){
document.getElementById(day).children['p'+period].innerHTML=document.getElementById(day).children['p'+period].innerHTML+'<p id="'+ rejectedData[x].request_id +'" onclick="showDialog(this);"> Request ID: <span id="tableP">&nbsp;'+rejectedData[x].request_id+'</span><br/> Module code: <span id="tableP">'+rejectedData[x].module_code+'</span><br/> Room code: <span id="tableP">'+rejectedData[x].room_code+'</span></p>';
}
if(room != null && rejectedData[x].room_code==room){
document.getElementById(day).children['p'+period].innerHTML=document.getElementById(day).children['p'+period].innerHTML+'<p id="'+ rejectedData[x].request_id +'" onclick="showDialog(this);"> Request ID: <span id="tableP">&nbsp;'+rejectedData[x].request_id+'<span id="tableP"><br/> Module code: <span id="tableP">'+rejectedData[x].module_code+'<span id="tableP"><br/> Room code: <span id="tableP">'+rejectedData[x].room_code+'<span id="tableP"></p>';
}
}
}
}
}

			
			
			
			
			function increaseWeek () {
				var currentWeek=parseInt(document.getElementById('current_week').value);
				if(currentWeek != 16){
					document.getElementById('current_week').value = (currentWeek+1);
				}
				
			}
			
			function decreaseWeek () {
				var currentWeek=parseInt(document.getElementById('current_week').value);
				if(currentWeek != 1){
					document.getElementById('current_week').value = (currentWeek-1);
				}
			}
			
			function showInfo(id) {
				$( "#info" ).empty();
				alert("<h2>Request No: " + id + "</h2>");
			}
			
			function changeSort() {
				var n = document.getElementById('sortList').selectedIndex;
				if(n==0) {
				document.getElementById('partDiv').style.display="block";
				document.getElementById('moduleDiv').style.display="none";
				document.getElementById('roomDiv').style.display="none";
				}
				if(n==1) {
				document.getElementById('partDiv').style.display="none";
				document.getElementById('moduleDiv').style.display="block";
				document.getElementById('roomDiv').style.display="none";
				}
				if(n==2) {
				document.getElementById('partDiv').style.display="none";
				document.getElementById('moduleDiv').style.display="none";
				document.getElementById('roomDiv').style.display="block";
				}
			}
			
			
					function logout_question(){
  if (confirm('Are you sure you want to logout?')){
    return true;
  }else{
    return false;
  }
}

	
			
	function showDialog(el){
	
			if(document.getElementById('statusList').selectedIndex==1){ 
				$("#dialog-form1").dialog("open");
				$('#updateButton').html('<input type="button" value="Submit" onClick="updateBookedAjax()" />');
				$('#deleteButton').html('<input type="button" value="Delete" onClick="deleteAjax()" />');
			}	
			if(document.getElementById('statusList').selectedIndex==2){ 
				$("#dialog-form1").dialog("open");
				$('#updateButton').html('<input type="button" value="Submit" onClick="updateAjax()" />');
				$('#deleteButton').html('<input type="button" value="Delete" onClick="deleteAjax()" />');
			}	
				var request_id=parseInt(el.id);
				var module_code=el.children[2].innerHTML;
				$("#requestId").val(request_id);	
				$("#requestIdDel").val(request_id);				
				inputModule();
				$("#module_code_select").val(module_code);
				 module_code_change();
				checkPriority(el);
				checkDay(el);
				checkWeek(el);
				checkperiod(el);
				checkDuration(el);
				checkSpecialReq(el);
				checkCapacity(el);
				checkFacility(el);
				checkRoomCode(el);
					
	}
			
	function inputModule(){
				for(var i=0;i<moduleData.length;i++){
					$("#module_code_select").append("<option>" + moduleData[i].module_code + "</option>");
					$("#module_title_select").append("<option>" + moduleData[i].module_title + "</option>");
				}
			}
			
	function checkPriority(el){
	
				var request_id=parseInt(el.id);
				var priority;
				
				for(var x=0;x<fullData.length;x++){
					if(fullData[x].request_id == request_id) priority=fullData[x].priority;
				}
				
				if(priority==0)
				$("#priorityInput2").prop('checked',true);
				else
				$("#priorityInput1").prop('checked',true);
			}
			
	function checkDay(el){
				var request_id=parseInt(el.id);
				var day; 
				
				for(var x=0;x<fullData.length;x++){
					if(fullData[x].request_id == request_id) day=fullData[x].day;
				}
				
				if(day == "Monday")
					$("#monday").prop('checked', true);
				if(day == "Tuesday")
					$("#tuesday").prop('checked', true);
				if(day == "Wednesday")
					$("#wednesday").prop('checked', true);
				if(day == "Thursday")
					$("#thursday").prop('checked', true);
				if(day == "Friday")
					$("#friday").prop('checked', true);
			}
			
	function checkWeek(el){
				var request_id = parseInt(el.id);
				var weeks=[];
				
				for(var x=0;x<weeksData.length;x++){
					if(weeksData[x].request_id == request_id) weeks.push(weeksData[x].week);
				}
				
				if(weeks[0] == 0){
					for(var j=1;j<=12;j++){
						$("#week" + j).prop('checked',true);
					}
				}
						
				else{
					for(var k=0;k<weeks.length;k++){
						$("#week" + weeks[k]).prop('checked',true);
					}
				}
			}
			
	function checkperiod(el){
			var request_id = parseInt(el.id);
				var period;
				for(var x=0;x<fullData.length;x++){
					if(fullData[x].request_id == request_id) period=fullData[x].period;
				}
				
				document.getElementById("time").selectedIndex = period - 1;
			}
			
	function checkDuration(el){
		var request_id = parseInt(el.id);
		var duration;

		for(var x=0;x<fullData.length;x++){
					if(fullData[x].request_id == request_id) duration=fullData[x].duration;
				}
				
				document.getElementById("duration").selectedIndex = duration - 1;
	}
	
	function checkSpecialReq(el){
		var request_id = parseInt(el.id);
		var req;
		
		for(var x=0;x<fullData.length;x++){
					if(fullData[x].request_id == request_id) req=fullData[x].special_requirements;
				}
		
		$("#specialReq").val(req);
	
	}
	
	function checkCapacity(el){
			var request_id = parseInt(el.id);
			var capacity;
			
			for(var x=0;x<fullData.length;x++){
					if(fullData[x].request_id == request_id) capacity=fullData[x].capacity;
				}
			
			$("#capacity1").val(capacity);
	}

		function checkFacility(el){
			var request_id = parseInt(el.id);
			var wheelchair;
			var projector;
			var visualiser;
			var whiteboard;
			
			for(var x=0;x<fullData.length;x++){
					if(fullData[x].request_id == request_id) { 
						wheelchair=fullData[x].wheelchair;
						projector=fullData[x].projector;
						visualiser=fullData[x].visualiser;
						whiteboard=fullData[x].whiteboard;
					}
				}
			
				if (wheelchair == 1)
					$("#wheelchair_yes").prop('checked', true);
				else 
					$("#wheelchair_no").prop('checked', true);
				if (projector == 1)
					$("#projector_yes").prop('checked', true);
				else 
					$("#projector_no").prop('checked', true);
				if (visualiser == 1)
					$("#visualiser_yes").prop('checked', true);
				else 
					$("#visualiser_no").prop('checked', true);
				if (whiteboard == 1)
					$("#whiteboard_yes").prop('checked', true);
				else 
					$("#whiteboard_no").prop('checked', true);
			}
			
	function checkRoomCode(el){
				var room = el.children[4].innerHTML;
				$("#room_list").empty();
				for(var i=0;i<roomData.length;i++){
					$("#room_list").append("<option>" + roomData[i].room_code + "</option>");
				}
				
				if(room != "null"){
					$("#room_list").val(room);
				}
				else $("#room_list").val(null);
			}
			
	Element.prototype.remove = function() {
		this.parentElement.removeChild(this);
	}
	
	NodeList.prototype.remove = HTMLCollection.prototype.remove = function() {
    for(var i = 0, len = this.length; i < len; i++) {
        if(this[i] && this[i].parentElement) {
            this[i].parentElement.removeChild(this[i]);
        }
    }
}		
			
	function updateAjax(){
				
				//validation
				if(document.getElementById("capacity1").value > 1000 || document.getElementById("capacity1").value < 1) {
					return(alert("Please enter a suitable capacity"));
				}
				
				
				var checked = false;
				$('#editForm  input[type="checkbox"]').each(function() {
					if ($(this).is(":checked")) {
						checked = true;
					}
				});
		 
				if (checked == false) {
					return(alert("Please enter a week"));
				} 
	
				$.ajax({
				url : "updateInfo.php",
				type : "POST", 
				data : $("#editForm").serialize(),
				success : function (data){					
						data = JSON.parse(data);
						alert("Request submitted with request id " + data[data.length-1].request_id);
						//window.location.href = "TimetableView.php";
						document.getElementById(data[data.length-1].request_id).children[2].innerHTML=data[data.length-1].module_code;
						document.getElementById(data[data.length-1].request_id).children[4].innerHTML=data[data.length-1].room_code;
						closeDialog();
					},
				error : function(jqXHR, textStatus, errorThrown) {
				}
				});
			}
			//delete selected request from database

			function deleteAjax(){
				$.ajax({
				url : "deleteInfo.php",
				type : "POST", 
				data : $("#deleteForm").serialize(),
				success : function (data){					
						data = JSON.parse(data);
						alert("Request deleted with request id " + data + " has been deleted");
						var id = data;
						document.getElementById(data).remove();
						//window.location.href = "TimetableView.php";
					},
				error : function(jqXHR, textStatus, errorThrown) {
				}
				});
			}
			function confirmDelete(){
				if (confirm('Are you sure you want to delete this request?')){
					deleteAjax();
				}else{
				return false;
				}
			}
			
	function module_code_change(){
				document.getElementById("module_title_select").selectedIndex = document.getElementById("module_code_select").selectedIndex ;
			}
			function module_title_change(){
				document.getElementById("module_code_select").selectedIndex = document.getElementById("module_title_select").selectedIndex;
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
			
	function change_room_code() {
						//cache user settings
						
						var ParkSelect = document.getElementById("park").value;
						var capacity = document.getElementById("capacity1").value;
						var isWheelchair = document.getElementById("wheelchair_yes").checked;
						var isVisualiser = document.getElementById("visualiser_yes").checked;

						var isProjector = document.getElementById("projector_yes").checked;

						var isWhiteboard = document.getElementById("whiteboard_yes").checked;
						var buildingCode = document.getElementById("BuildingCodeSelect").value;
						
						
						//empty the room code list
						$("#room_list").empty();
						
						for(var i=0;i<roomData.length;i++){
						//if the room has enough capacity, and has the options the user asked for - or he didn't ask for the option, then add it to the list
							if((roomData[i].capacity >= parseInt(capacity) || capacity == "") &&
							(ParkSelect == "Any" || ParkSelect == roomData[i].park) &&
							(!isWheelchair || roomData[i].wheelchair == 1) &&
							(!isVisualiser || roomData[i].visualiser == 1) &&
							(!isProjector || roomData[i].projector == 1) &&
							(!isWhiteboard || roomData[i].whiteboard == 1) && 
							(buildingCode == "All" || buildingCode == roomData[i].building_code)) {
								$("#room_list").append("<option>" + roomData[i].room_code + "</option>");
								
							}
							
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
					
					function ParkChange() {
						var parkChosen = "Any";
						parkChosen = document.getElementById("park").value;
						$("#room_list").empty();
						
						//if any parks are chosen then all the rooms are displayed
						if(parkChosen=="Any") {
							for(var i=0; i<roomData.length; i++) {
								$("#room_list").append("<option> " + roomData[i].room_code + "</option>");
							}
						} else { //if a park is chosen teh jsut that park's rooms are displayed
							for(var i=0; i<roomData.length; i++) {
								if(roomData[i].park == parkChosen) {
									$("#room_list").append("<option> " + roomData[i].room_code + "</option>");
								}
							}
						}
						$("#BuildingCodeSelect").html("<option>All</option>");
						$("#BuildingNameSelect").html("<option>All</option>");				
						for(var i=0; i<buildingData.length; i++) {
							if(buildingData[i].park == document.getElementById("park").value) {
								$("#BuildingCodeSelect").append("<option>"+buildingData[i].building_code+"</option>");
								$("#BuildingNameSelect").append("<option>"+buildingData[i].building_name+"</option>");
							}
						}
					}
			
			function goBack() {
				window.history.back()
			}
			
			
		</script>	
<link rel="stylesheet" href="Theme.css"/>
</head>
<body onload="initialise(); statusChange(); moduleList();">

<div id="top_style">
<div align="middle" style="top:0; width: 50px; float: left; margin-left: 165px;">
<a onclick="goBack();"> <img width="30" height="20" border="0" alt="Back" src="Back_Arrow.png" align="middle" style=" cursor: pointer;"> </a> </div><a href="timetable.php"> <img width="40" height="40" border="0" alt="Home!" src="Home_Button.png" align="middle"> </a> 
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
	
	
		<div id="images_holder" >
			<div id="image_wrap">
				<a id="listLink" style="color:black;" href="ViewRequests.php"> <img style="margin-left:20%; display: block;" width="40" height="40" border="0" alt="List" src="list_picture.png" > List View </a> 
				<a style="color:#99165c;" href="TimetableView.php"> <img style="margin-left:15%; display: block;"  width="40" height="40" border="0" alt="Timetable" src="RED_timetable_grid_view.png" > Timetable<br/>Grid View </a> 
			</div>
		</div>
	
	<div id="page_wrap">
	<div style="display:none;">
		<form id="deleteForm" name="deleteForm">
			<input type="hidden" value="" id="requestIdDel" name="requestIdDel"/>
		</form>

		<form id="weekForm" name="weekForm">
			<input type="hidden" value="" id="weekCheck" name="weekCheck"/>
		</form>

	</div>
	<div id="dialog-form1" title="Edit information" style="display: none;" >
			<form id="editForm" name="editForm">
				<fieldset>
					<input type="hidden" value="" id="requestId" name="requestId"/>

					<table style='margin-left: 227px;' id='priority_id'  class="inputs box_class">
					<tr>
						<td>
							Priority: 
						</td>
						<td>
					<input name="priorityInput" type="radio" id="priorityInput1" onchange="change_room_code()" value="1"/>Yes
					<input name="priorityInput" type="radio" id="priorityInput2" onchange="change_room_code()" value="0"/>No
                    	</td>
					</tr>
                    </table>
					
                    <table style='margin-bottom: 10px;' class="inputs box_class">
					<tr>
						<td>
					<label for="module_code_select"> Module Code: </label>
                    </td>
                    	<td>				
                        	<label id='label_module_title' for="module_title_select"> Module Title: </label>
                        </td>
                        </tr>
                    <tr>
						<td>
					<select id="module_code_select" name="module_code_select" onchange='module_code_change()'> </select>
					 </td> <td>
					<select id="module_title_select" name="module_title_select"  onchange='module_title_change()'> </select>
					</td>
					</tr>
                    </table>
                    
                    

                    <table style='margin-bottom: 10px; margin-left:65px; ' class="inputs box_class floating">

					<tr>
						<td style='text-align:center;'>
					Day: </td>
					</tr> 
                    <tr>
						<td>
                    
					<input type="radio" name="day" id='monday' value="1" required/>
					Monday
					<input type="radio" name="day" id='tuesday' value="2" required/>
					Tuesday<br/>
					<input type="radio" name="day" id='wednesday' value="3" required/>
					Wednesday
					<input type="radio" name="day" id='thursday' value="4" required/>
					Thursday<br/>
					<input type="radio" name="day" id='friday' value="5" required/>
					Friday </td>
					</tr>
                    </table>
                    
                     

                    <table style=' margin-left:320px; height: 115px; margin-bottom: 10px;' class="inputs box_class ">

					<tr>
						<td>
                    Period:  </td> <td>
                    
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
					 </td> </tr>  <tr> <td>
					Duration: </td> <td>
					<?php
						//dropdown for the duration
						//Scott Marshall
						echo "<select name='duration' id='duration'>";
						for($i=1;$i<=9;$i++){
							$duration = $i+8;
							echo "<option value='".$i."'>".$i."</option>";
						}
						echo "</select>";
					?>
                    </td>
                    </tr>
                    </table>
                    
                    <table style="width: 630px; margin-bottom: 10px;" class="inputs box_class">
					<tr>
						<td style='text-align:center' >
					Week: </td> </tr> <tr> <td> 
					<span class="week_label"> 1 </span>
<input type="checkbox" name="weeks[]" id="week1" value="1"/></input>
<span class="week_label"> 2 </span>
<input type="checkbox" name="weeks[]" id="week2" value="2" /></input>
<span class="week_label"> 3 </span>
<input type="checkbox" name="weeks[]" id="week3" value="3" /></input>
<span class="week_label"> 4 </span>
<input type="checkbox" name="weeks[]" id="week4" value="4" /></input>
<span class="week_label"> 5 </span>
<input type="checkbox" name="weeks[]" id="week5" value="5" /></input>
<span class="week_label"> 6 </span>
<input type="checkbox" name="weeks[]" id="week6" value="6" /></input>
<span class="week_label"> 7 </span>
<input type="checkbox" name="weeks[]" id="week7" value="7" /></input>
<span class="week_label"> 8 </span>
<input type="checkbox" name="weeks[]" id="week8" value="8" /></input>
<br/>
<br/>
<span class="week_label"> 9 </span>
<input type="checkbox" name="weeks[]" id="week9" value="9" /></input>
<span class="week_label"> 10 </span>
<input type="checkbox" name="weeks[]" id="week10" value="10" /></input>
<span class="week_label"> 11 </span>
<input type="checkbox" name="weeks[]" id="week11" value="11" /></input>
<span class="week_label"> 12 </span>
<input type="checkbox" name="weeks[]" id="week12" value="12" /></input>
<span class="week_label"> 13 </span>
<input type="checkbox" name="weeks[]" id="week13" value="13" /></input>
<span class="week_label"> 14 </span>
<input type="checkbox" name="weeks[]" id="week14" value="14" /></input>
<span class="week_label"> 15 </span>
<input type="checkbox" name="weeks[]" id="week15" value="15" /></input>
<span class="week_label"> 16 </span>
<input type="checkbox" name="weeks[]" id="week16" value="16" /></input>
					</td> </tr>
					
					
                    </table>
                    
                     
                      <table style="width: 630px; margin-bottom: 10px;" class="inputs box_class">
					<tr> <td>  Park: </td> <td> Building Name : </td> <td> Building Code : </td> <td> Rooms </td> </tr>
                     <tr> <td>
					<select id="park" name="park" onChange="ParkChange();change_room_code();">
						<option>Any</option>
						<option>C</option>
						<option>E</option>
						<option>W</option>
					</select>
					</td><td>
					
					<select style='width:240px;' name="BuildingNameSelect" id="BuildingNameSelect" onChange="buildingNameChange();change_room_code()" >
						
					</select>
				</td><td>
					
					<select style='width:90px;' name="BuildingCodeSelect" id="BuildingCodeSelect" onChange="buildingCodeChange();change_room_code()" >
						
					</select>
					</td> <td>
					
					<select name='roomCode0' id='room_list' onchange='refill_codes();'>
					</select>
                    
                    </td> </tr>


                    <tr> <td>&nbsp;  </td> </tr>
                    <tr> <td>&nbsp;  </td> <td colspan="2" > &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Capacity: &nbsp;&nbsp;
					<input style='width:52px;' name="capacity" type="text" id="capacity1" onChange="change_room_code()" value=""/>   </td></tr></table>                    
					
                    
                    <table style="width: 636px; margin-bottom: 10px;" class="inputs box_class">
                    <tr> <td> Wheelchair: </td>  <td> Visualiser:</td> <td> Projector:</td> <td> Whiteboard: </td> </tr> 
                    	<tr>
					<td>

					<input name="wheelchair" type="radio" id="wheelchair_yes" onChange="change_room_code()" value="1"/>
					Yes
					<input name="wheelchair" type="radio" id="wheelchair_no" onChange="change_room_code()" value="0" checked="checked"/>
					No

						</td> <td>	
					

					<input name="visualiser" type="radio" id="visualiser_yes" onChange="change_room_code()" value="1" checked="checked"/>
					Yes
					<input name="visualiser" type="radio" id="visualiser_no" onChange="change_room_code()" value="0"/>
					No

						</td> <td>
					

					<input name="projector" type="radio" id="projector_yes" onChange="change_room_code()" value="1" checked="checked"/>
					Yes
					<input name="projector" type="radio" id="projector_no" onChange="change_room_code()" value="0"/>
					No

						</td> <td>	
					

					<input name="whiteboard" type="radio" id="whiteboard_yes" onChange="change_room_code()" value="1" checked="checked"/>
					Yes
					<input name="whiteboard" type="radio" id="whiteboard_no" onChange="change_room_code()" value="0"/>
					No

					</td></tr></table>
                    
                    
                     <table style="width: 636px; margin-bottom: 10px;" class="inputs box_class">
                     <tr> <td>
					Special Requirements: &nbsp;&nbsp;</td><td>
					<textarea style='width:413px;' name="specialReq" id="specialReq" maxlength="1000" placeholder="1000 chars max..."></textarea> </td> </tr> <tr> <td>&nbsp;  </td> </tr> </table>
                    
                    
					<div id="updateButton" >
						
                    </div>
					<div id="deleteButton" >
						
                    </div>
                    
						

				</fieldset>
			</form>
		</div>
	
	
		<hr/>

	<table class="box_class">

	
	<h3>View: </h3><select id="statusList" onChange="statusChange()">
                	<option>Rejected</option>
                    <option>Booked</option>
                    <option>Pending</option>
                </select>
				
	<h3>Sort By: </h3><select id="sortList" onchange="changeSort();statusChange();">
				<option>Part</option>
				<option>Module</option>
				<option>Room</option>
			</select>
	
	<div id="partDiv" style="display:block" class="sort">
	<h4>Part: </h4>
	<input type='radio' name='partCode' id='aPart' value='A' onchange='statusChange();' checked> A
	<input type='radio' name='partCode' id='bPart' value='B' onchange='statusChange();'> B
	<input type='radio' name='partCode' id='iPart' value='I' onchange='statusChange();'> I
	<input type='radio' name='partCode' id='cPart' value='C' onchange='statusChange();'> C
	<input type='radio' name='partCode' id='dPart' value='D' onchange='statusChange();'> D
	</div>
	
	<div id="moduleDiv" style="display:none" class="sort">
	<h4>Module Code: </h4><select id="moduleCodeList" onchange="module_code_change(); statusChange();">
	</select>			
	
	<h4>Module Title: </h4><select id="moduleTitleList"onchange="module_title_change();statusChange();">
	</select>
	</div>
	
	<div id="roomDiv" style="display:none" class="sort">
	<h4>Room: </h4><select id="roomList" onchange="statusChange();">
	</select>

	</div>
    </table>			
	<table frame="box" style="width:100%;" align "center" id="testTable">			

	<br/>
	Click on pending requests to make changes or delete your request.
	<div id="hours">
	
		<button type="button" onclick="decreaseWeek(); statusChange();">-</button><input id="current_week" type="text" name="current_week"  disabled></input><button type="button" onclick="increaseWeek();statusChange(); ">+</button>
		<tr id="headers">
			<th>Timetable</th>
			<th>09.00</th>
			<th>10.00</th>
			<th>11.00</th>
			<th>12.00</th>
			<th>13.00</th>
			<th>14.00</th>
			<th>15.00</th>
			<th>16.00</th>
			<th>17.00</th>
			<th>18.00</th>
		</tr>
	</div>	

	<div id="Days">
		<tr id="Monday">	
			<td class="day" style="background-color:#f1f1f1;">Monday</td>
			<td id="p1"></td>
			<td id="p2"></td>
			<td id="p3"></td>
			<td id="p4"></td>
			<td id="p5"></td>
			<td id="p6"></td>
			<td id="p7"></td>
			<td id="p8"></td>
			<td id="p9"></td>	
			<td id="p10"></td>
		</tr>
		<tr id="Tuesday">
			<td class="day" style="background-color:#f1f1f1;">Tuesday</td>
			<td id="p1"></td>
			<td id="p2"></td>
			<td id="p3"></td>
			<td id="p4"></td>
			<td id="p5"></td>
			<td id="p6"></td>
			<td id="p7"></td>
			<td id="p8"></td>
			<td id="p9"></td>
			<td id="p10"></td>			
		</tr>
		<tr id="Wednesday">
			<td class="day"></td>
			<td id="p1"></td>
			<td id="p2"></td>
			<td id="p3"></td>
			<td id="p4"></td>
			<td id="p5"></td>
			<td id="p6"></td>
			<td id="p7"></td>
			<td id="p8"></td>
			<td id="p9"></td>	
			<td id="p10"></td>
		</tr>
		<tr id="Thursday">
			<td class="day" style="background-color:#f1f1f1;">Thursday</td>
			<td id="p1"></td>
			<td id="p2"></td>
			<td id="p3"></td>
			<td id="p4"></td>
			<td id="p5"></td>
			<td id="p6"></td>
			<td id="p7"></td>
			<td id="p8"></td>
			<td id="p9"></td>
			<td id="p10"></td>			
		</tr>
		<tr id="Friday">
			<td class="day" style="background-color:#f1f1f1;">Friday</td>
			<td id="p1"></td>
			<td id="p2"></td>
			<td id="p3"></td>
			<td id="p4"></td>
			<td id="p5"></td>
			<td id="p6"></td>
			<td id="p7"></td>
			<td id="p8"></td>
			<td id="p9"></td>
			<td id="p10"></td>			
		</tr>
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
