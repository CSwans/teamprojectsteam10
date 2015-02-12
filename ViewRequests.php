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
			
			//put each rows into value array
			while($row = $res->fetchRow()){
				$value[] = $row;
				
			}
			
			$jsonRequests = json_encode($value);
			
			$sql = "SELECT REQUEST.request_id, module_code, BOOKING.room_code, capacity, wheelchair, projector, visualiser, whiteboard, special_requirements, priority, period, day, duration,GROUP_CONCAT(CONVERT(REQUEST_WEEKS.week, CHAR(8)) SEPARATOR ',') AS week, CASE WHEN REQUEST.room_code = BOOKING.room_code THEN 0 ELSE 1 END AS partial FROM REQUEST,REQUEST_WEEKS, BOOKING WHERE REQUEST.request_id = REQUEST_WEEKS.request_id AND dept_code = '".$username."' AND BOOKING.request_id = REQUEST.request_id GROUP BY request_id";
			$res =& $db->query($sql); //getting the result from the database
			if(PEAR::isError($res)){
				die($res->getMessage());
			}
			$value2 = array();
			
			//put each rows into value array
			while($row = $res->fetchRow()){
				$value2[] = $row;
			}
			
			$jsonBookings = json_encode($value2);
			
			
			$sql = "SELECT REQUEST.request_id, module_code, REQUEST.room_code, capacity, wheelchair, projector, visualiser, whiteboard, special_requirements, priority, period, day, duration,GROUP_CONCAT(CONVERT(REQUEST_WEEKS.week, CHAR(8)) SEPARATOR ',') AS week  FROM REQUEST,REQUEST_WEEKS, REJECTION WHERE REQUEST.request_id = REQUEST_WEEKS.request_id AND dept_code = '".$username."' AND REJECTION.request_id = REQUEST.request_id GROUP BY request_id";
			$res =& $db->query($sql); //getting the result from the database
			if(PEAR::isError($res)){
				die($res->getMessage());
			}
			$value3 = array();
			
			//put each rows into value array
			while($row = $res->fetchRow()){
				$value3[] = $row;
			}
			
			$jsonRejections = json_encode($value3);
			
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
			
		?>
		<link rel="stylesheet" href="Theme.css"/>
		 <link rel="stylesheet" href="http://code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">
		<script src="http://code.jquery.com/jquery-1.10.2.js"></script>
		<script src="http://code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
	
		 <style>

label, input { display:block; }

h1 { font-size: 1.2em; margin: .6em 0; }

.ui-dialog .ui-state-error { padding: .3em; }
</style>
		<script src="js/jquery.serializejson.min.js"></script>
		<script type="text/javascript">
		
		
		function currentSort(id) {
			document.getElementById('request_id').className="";
			document.getElementById('module_code').className="";
			document.getElementById('room_code').className="";
			document.getElementById('capacity').className="";
			document.getElementById('wheelchair').className="";
			document.getElementById('projector').className="";
			document.getElementById('visualiser').className="";
			document.getElementById('whiteboard').className="";

			document.getElementById('priority').className="";
			document.getElementById('period').className="";
			document.getElementById('day').className="";
			document.getElementById('duration').className="";
			document.getElementById('status2').className="";
			
			
			
					
			document.getElementById(id).className="currentSort";
		}
		
			<?php
			echo "var requestData = ".$jsonRequests.";\n"; //WILL CHANGE TO HOLD THE PENDING DATA WHEN PAGE LOADS
			echo "var bookingData = ".$jsonBookings.";\n";
			echo "var rejectedData = ".$jsonRejections.";\n";
			echo "var roomData = ". $roomDataJson . ";\n";
			echo "var moduleData = ". $moduleJson . ";\n";
			echo "var buildingData = ". $buildingJson . ";\n";
			?>
			
			
			$(function() {
				//console.log(bookingData);
				//apply dialog to input form
				$("#dialog-form1").dialog({
					height: 500,
					width: 700
				});
				//hide dialog
				$("#dialog-form1").dialog("close");
				populateTable();
				findPendings();
				buildingInitialise();
				
				 
			});
			//show dialog when edit button is clicked
			//callan swanson, Inthuch Therdchanakul
			function showDialog(el){
				$("#dialog-form1").dialog("open");
				var request_id = parseInt(el.parentNode.parentNode.cells[0].textContent);
				$("#requestId").val(request_id);
				inputModule();
				$("#module_code_select").val(el.parentNode.parentNode.cells[1].textContent);
				module_code_change(); 
				checkDay(el);
				checkperiod(el);
				checkDuration(el);
				checkSpecialReq(el);
				checkCapacity(el);
				checkFacility(el);
				checkRoomCode(el);
			}
			//close dialog when cancel is clicked
			function closeDialog(){
				$("#dialog-form1").dialog("close");
			}
			///input module data into dialog div
			function inputModule(){
				for(var i=0;i<moduleData.length;i++){
					$("#module_code_select").append("<option>" + moduleData[i].module_code + "</option>");
					$("#module_title_select").append("<option>" + moduleData[i].module_title + "</option>");
				}
			}
			function updateAjax(){
				$.ajax({
				url : "updateInfo.php",
				type : "POST", 
				data : $("#editForm").serialize(),
				success : function (data){					
						data = JSON.parse(data);
						alert("Request submitted with request id " + data[data.length-1].request_id);
						console.log("data "+data); //quick check
						
					},
				error : function(jqXHR, textStatus, errorThrown) {
				}
				});
			}
			//onChange for module title/code
			function module_code_change(){
				document.getElementById("module_title_select").selectedIndex = document.getElementById("module_code_select").selectedIndex ;
			}
			function module_title_change(){
				document.getElementById("module_code_select").selectedIndex = document.getElementById("module_title_select").selectedIndex;
			}
			//select radio button matching the day in the table cell
			function checkDay(el){
				var day = el.parentNode.parentNode.cells[11].textContent;
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
			//populate dialog form with data from selected table row
			function checkperiod(el){
				var period = parseInt(el.parentNode.parentNode.cells[10].textContent);
				document.getElementById("time").selectedIndex = period - 1;
			}
			function checkDuration(el){
				var duration = parseInt(el.parentNode.parentNode.cells[12].textContent);
				document.getElementById("duration").selectedIndex = duration - 1;
			}
			function checkSpecialReq(el){
				var req = el.parentNode.parentNode.cells[8].textContent;
				$("#specialReq").val(req);
			}
			function checkCapacity(el){
				var capacity = parseInt(el.parentNode.parentNode.cells[3].textContent);
				$("#capacity1").val(capacity);
			}
			function checkRoomCode(el){
				var room = el.parentNode.parentNode.cells[2].textContent;
				$("#room_list").empty();
				for(var i=0;i<roomData.length;i++){
					$("#room_list").append("<option>" + roomData[i].room_code + "</option>");
				}
				$("#room_list").val(room);
			}
			function checkFacility(el){
				if(parseInt(el.parentNode.parentNode.cells[4].textContent) == 1)
					$("#wheelchair_yes").prop('checked', true);
				else 
					$("#wheelchair_no").prop('checked', true);
				if(parseInt(el.parentNode.parentNode.cells[5].textContent) == 1)
					$("#projector_yes").prop('checked', true);
				else 
					$("#projector_no").prop('checked', true);
				if(parseInt(el.parentNode.parentNode.cells[6].textContent) == 1)
					$("#visualiser_yes").prop('checked', true);
				else 
					$("#visualiser_no").prop('checked', true);
				if(parseInt(el.parentNode.parentNode.cells[4].textContent) == 1)
					$("#whiteboard_yes").prop('checked', true);
				else 
					$("#whiteboard_no").prop('checked', true);
			}
			
			//building function
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
			//finds the pending data by searching through both the rejected and the booked arrays
			//callan swanson
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
			
			function request_id(a, b) {
				return parseInt(a["request_id"]) - parseInt(b["request_id"]);
			}
			
			//sorting the module code in order a-z 1-9
			function module_code(a, b) {
				return a["module_code"] >  b["module_code"];
			}
			
			function room_code(a, b) {
				if (a["room_code"]===null) a["room_code"]='';
				if (b["room_code"]===null) b["room_code"]='';

				if (''+a["room_code"] < ''+b["room_code"]) return -1;
				if (''+a["room_code"] > ''+b["room_code"]) return  1;
				
				return 0;
				
			}
			
			function capacity(a, b) {
				return parseInt(a["capacity"]) - parseInt(b["capacity"]);
			}
			
			function wheelchair(a, b) {
				return parseInt(a["wheelchair"]) - parseInt(b["wheelchair"]);
			}
			
			function projector(a, b) {
				return parseInt(a["projector"]) - parseInt(b["projector"]);
			}
			
			function visualiser(a, b) {
				return parseInt(a["visualiser"]) - parseInt(b["visualiser"]);
			}
			
			function whiteboard(a, b) {
				return parseInt(a["whiteboard"]) - parseInt(b["whiteboard"]);
			}
			
			function priority(a, b) {
				return parseInt(b["priority"]) - parseInt(a["priority"]);
			}
			
			function period(a, b) {
				return parseInt(a["period"]) - parseInt(b["period"]);
			}
			
			//will find the index of the day in teh dayInt array and order interms of days of teh week
			function day(a, b) {
				var dayInt = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"];
				return parseInt(dayInt.indexOf(a["day"])) - parseInt(dayInt.indexOf(b["day"]));
			}
			
			function duration(a, b) {
				return parseInt(a["duration"]) - parseInt(b["duration"]);
			}
			
			
			//sorts the data in the table according to which id has been passed (uses sort functions above)
			//Callan Swanson
			function sortHeader(id) {

				//chooses which to sort by the id passed to this function
				switch(id) {
					case "request_id" : 
						requestData.sort(request_id);
						break;
					case "module_code" : 
						requestData.sort(module_code);
						break;
					case "room_code" : 
						requestData.sort(room_code);
						break;
					case "capacity" : 
						requestData.sort(capacity);
						break;
					case "wheelchair" : 
						requestData.sort(wheelchair);
						break;
					case "projector" : 
						requestData.sort(projector);
						break;
					case "visualiser" : 
						requestData.sort(visualiser);
						break;
					case "whiteboard" : 
						requestData.sort(whiteboard);
						break;
					case "priority" : 
						requestData.sort(priority);
						break;
					case "period" : 
						requestData.sort(period);
						break;
					case "day" : 
						requestData.sort(day);
						break;
					case "duration" : 
						requestData.sort(duration);
						break;
					default : 
						break;
				}
				
				populateTable();
			}
			
			//puts data into the table using jquery
			//callan swanson
			function populateTable() {
				var currentStatus = document.getElementById("status").value;
				if(currentStatus == "Rejected"){
					statusChange(rejectedData);
				}
				if(currentStatus == "Booked"){
					statusChange(bookingData);
					for(var i=0; i<bookingData.length; i++) {
						if(bookingData[i].partial == 1) {
							$("#dataTable tr:eq("+(i+1)+") td:eq(2)").html("<b>"+bookingData[i].room_code+"</b>");
						}
					}
				}
				if(currentStatus == "Pending"){
					statusChange(requestData);
				}
			}
			
			//alters the table to contain the data they want to see
			//callan swanson, Inthuch Therdchanakul
			function statusChange(status){
			
				for(var i=1; i<=requestData.length; i++) {
					for(var j=0; j<14; j++) {
						$("#dataTable tr:eq("+(i+1)+") td:eq("+j+")").empty();
					}
				}
			
				for(var i=0; i<status.length; i++) {

					$("#dataTable tr:eq("+(i+1)+") td:eq(0)").html(status[i].request_id);
					
					if(status[i].room_code === null) console.log("NULL");
					$("#dataTable tr:eq("+(i+1)+") td:eq(1)").html(status[i].module_code);
					
					$("#dataTable tr:eq("+(i+1)+") td:eq(2)").html(status[i].room_code);
					$("#dataTable tr:eq("+(i+1)+") td:eq(3)").html(status[i].capacity);
					$("#dataTable tr:eq("+(i+1)+") td:eq(4)").html(status[i].wheelchair);
					$("#dataTable tr:eq("+(i+1)+") td:eq(5)").html(status[i].projector);
					$("#dataTable tr:eq("+(i+1)+") td:eq(6)").html(status[i].visualiser);
					$("#dataTable tr:eq("+(i+1)+") td:eq(7)").html(status[i].whiteboard);
					$("#dataTable tr:eq("+(i+1)+") td:eq(8)").html(status[i].special_requirements);
					$("#dataTable tr:eq("+(i+1)+") td:eq(9)").html(status[i].priority);
					$("#dataTable tr:eq("+(i+1)+") td:eq(10)").html(status[i].period);
					$("#dataTable tr:eq("+(i+1)+") td:eq(11)").html(status[i].day);
					$("#dataTable tr:eq("+(i+1)+") td:eq(12)").html(status[i].duration);
					
					
					
					if(status[i].week==0) { //default weeks
						$("#dataTable tr:eq("+(i+1)+") td:eq(13)").html("1,2,3,4,5,6,7,8,9,10,11,12");
					} else {
						//sorting the list of numbers into lowest first order 
						var sortedWeeks = status[i].week.split(",");
						sortedWeeks.sort();
						$("#dataTable tr:eq("+(i+1)+") td:eq(13)").html(sortedWeeks.join());
					}
					if(document.getElementById("status").value == "Pending")
						$("#dataTable tr:eq("+(i+1)+") td:eq(14)").html("<input type='button' value='edit' onclick='showDialog(this)'>");
					
				}
			}
			
			//table styling - Tom Middleton
			
			
					var fixed = false;

					$(document).scroll(function() {
						if( $(this).scrollTop() >= 150 ) {
							if( !fixed ) {
								fixed = true;
								$('#table_header').css({position:'fixed',top:0}); 
								$('#content_wrap').css({position:'relative',top:70}); 
							}                                           
						} else {
							if( fixed ) {
								fixed = false;
								$('#table_header').css({position:'static'});
								$('#content_wrap').css({position:'static'});
							}
						}
					});



					function hideEmpty() {
							var row = $('#dataTable tr').length;
							var x = $('#dataTable').children('tbody').children('tr').children('td').length;
							var col = x/row;
							var count;
							
							for(var i = 0;i<row;i++){
								var count=0;
								for(var y = 0;y<col;y++){
									if(document.getElementById(i+1).children[y].innerHTML == ""){
									count=count+1;
									}
								}
								if(count==15) document.getElementById(i+1).style.display='none';
								else document.getElementById(i+1).style.display='block';
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
	
		</script>
		
	</head>
	<body onLoad="hideEmpty();">
    
	<div id = "top_style" > 
		<b>
			<a href="login.html" style="margin-right: 140px; font-weight: 900; font-size: 1em;">Logout</a>
		</b> 
	</div>
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
	
			
    <div id="page_wrap">
	<hr/>
	<div id="dialog-form1" title="Edit information" style="display: none;" >
			<form id="editForm" name="editForm">
				<fieldset>
					<input type="hidden" value="" id="requestId"/>
					
					<label for="module_code_select"> Module Code: </label>
					<select id="module_code_select" name="module_code_select" onchange='module_code_change()'>
					
					</select>
					
					<label for="module_title_select" onchange='module_title_change()'> Module Title: </label>
					<select id="module_title_select" name="module_title_select">
					
					</select>
					
					Day: 
					<input type="radio" name="day" id='monday' value="1" required/>
					Monday
					<input type="radio" name="day" id='tuesday' value="2" required/>
					Tuesday<br/>
					<input type="radio" name="day" id='wednesday' value="3" required/>
					Wednesday
					<input type="radio" name="day" id='thursday' value="4" required/>
					Thursday<br/>
					<input type="radio" name="day" id='friday' value="5" required/>
					Friday 
					
					Week: 
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
					
					Period:
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
					
					Duration: 
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
					
					Special Requirements:
					<textarea name="specialReq" id="specialReq" maxlength="1000" placeholder="1000 chars max..."></textarea>
					
					Capacity:
					<input name="capacity" type="text" id="capacity1" onChange="change_room_code()" value="1"/>
					
					Park:
					<select id="park" name="park" onChange="ParkChange();change_room_code();">
						<option>Any</option>
						<option>C</option>
						<option>E</option>
						<option>W</option>
					</select>
					
					Building Name :
					<select name="BuildingNameSelect" id="BuildingNameSelect" onChange="buildingNameChange();change_room_code()" >
						
					</select>
				
					Building Code :
					<select name="BuildingCodeSelect" id="BuildingCodeSelect" onChange="buildingCodeChange();change_room_code()" >
						
					</select>
					
					Room:
					<select name='roomCode0' id='room_list' onchange='refill_codes();'>
					</select>
					
					Wheelchair:
					<input name="wheelchair" type="radio" id="wheelchair_yes" onChange="change_room_code()" value="1"/>
					Yes
					<input name="wheelchair" type="radio" id="wheelchair_no" onChange="change_room_code()" value="0" checked="checked"/>
					No
							
					Visualiser:
					<input name="visualiser" type="radio" id="visualiser_yes" onChange="change_room_code()" value="1" checked="checked"/>
					Yes
					<input name="visualiser" type="radio" id="visualiser_no" onChange="change_room_code()" value="0"/>
					No
						
					Projector:
					<input name="projector" type="radio" id="projector_yes" onChange="change_room_code()" value="1" checked="checked"/>
					Yes
					<input name="projector" type="radio" id="projector_no" onChange="change_room_code()" value="0"/>
					No
							
					Whiteboard:
					<input name="whiteboard" type="radio" id="whiteboard_yes" onChange="change_room_code()" value="1" checked="checked"/>
					Yes
					<input name="whiteboard" type="radio" id="whiteboard_no" onChange="change_room_code()" value="0"/>
					No
					
					<input type="button" value="Submit" onClick="updateAjax()" />
					<input type="button" value="Cancel" onClick="closeDialog()" />
				</fieldset>
			</form>
		</div>
	<div id="table_header">
		<table id="scrollTable">
			<tr>
				
                <div id="status_change"><h3>Sort by Status: </h3>
				
                <select id="status" onChange="populateTable();hideEmpty(); ">
                	<option>Rejected</option>
                    <option>Booked</option>
                    <option>Pending</option>
                </select><br/>
				</div>
				<h4>Click on the headers to sort the table</h4><br/><br/>
				<td id="request_id" onClick="sortHeader(this.id);currentSort(this.id);">
					Request</br>Id
				</td>
				<td id="module_code" onClick="sortHeader(this.id); currentSort(this.id);">
					Module </br> Code
				</td>
				<td id="room_code" onClick="sortHeader(this.id); currentSort(this.id);">
					Room Code
				</td>
				<td id="capacity" onClick="sortHeader(this.id); currentSort(this.id);">
					Capacity
				</td>
				<td id="wheelchair" onClick="sortHeader(this.id); currentSort(this.id);">
					Wheelchair
				</td>
				<td id="projector" onClick="sortHeader(this.id); currentSort(this.id);">
					Projector
				</td>
				<td id="visualiser" onClick="sortHeader(this.id); currentSort(this.id);">
					Visualiser
				</td>
				<td id="whiteboard" onClick="sortHeader(this.id); currentSort(this.id);">
					Whiteboard
				</td>
				<td id="special_requirements" style="cursor:default; font-size:0.8em; font-weight:bold;">
					Special </br>Requirements
				</td>
				<td id="priority" onClick="sortHeader(this.id);currentSort(this.id);">
					Priority
				</td>
				<td id="period" onClick="sortHeader(this.id);currentSort(this.id);">
					Period
				</td>
				<td id="day" onClick="sortHeader(this.id);currentSort(this.id);">
					Day
				</td>
				<td id="duration" onClick="sortHeader(this.id);currentSort(this.id);">
					Duration
				</td>
				<td id="week(s)" style="cursor:default;">
					Week(s)
				</td>
				<td id="status2" onClick="sortHeader(this.id);currentSort(this.id);">
					Edit
				</td>
			</tr>
			</table>
			
			</div>
			
			
			<div id="content_wrap">
					<table id="dataTable" class="entries_table">
			<tr style="display:none;">
                <select style="display:none;" id="statusList" onChange="populateTable()">
                	<option>Rejected</option>
                    <option>Booked</option>
                    <option>Pending</option>
                </select>
				<td id="request_id" onClick="sortHeader(this.id);">
					request_id
				</td>
				<td id="module_code" onClick="sortHeader(this.id);">
					module_code
				</td>
				<td id="room_code" onClick="sortHeader(this.id)">
					room_code
				</td>
				<td id="capacity" onClick="sortHeader(this.id)">
					capacity
				</td>
				<td id="wheelchair" onClick="sortHeader(this.id)">
					wheelchair
				</td>
				<td id="projector" onClick="sortHeader(this.id)">
					projector
				</td>
				<td id="visualiser" onClick="sortHeader(this.id)">
					visualiser
				</td>
				<td id="whiteboard" onClick="sortHeader(this.id)">
					whiteboard
				</td>
				<td id="special_requirements">
					Special </br>Requirements
				</td>
				<td id="priority" onClick="sortHeader(this.id)">
					priority
				</td>
				<td id="period" onClick="sortHeader(this.id)">
					period
				</td>
				<td id="day" onClick="sortHeader(this.id)">
					day
				</td>
				<td id="duration" onClick="sortHeader(this.id)">
					duration
				</td>
				<td id="week(s)" >
					week(s)
				</td>
				<td id="status" onClick="sortHeader(this.id)">
					Edit
				</td>
			</tr>
				
			
			<?php
				//putting in all the information about requests into the table
				//Callan Swanson
				for($i = 0; $i<sizeof($value);$i++) {
					
					echo "<tr id='".($i+1)."'><td>".$value[$i]['request_id']."</td>";
					echo "<td>".$value[$i]['module_code']."</td>";
					echo "<td>".$value[$i]['room_code']."</td>";
					echo "<td>".$value[$i]['capacity']."</td>";
					echo "<td>".$value[$i]['wheelchair']."</td>";
					echo "<td>".$value[$i]['projector']."</td>";
					echo "<td>".$value[$i]['visualiser']."</td>";
					echo "<td>".$value[$i]['whiteboard']."</td>";
					echo "<td>".$value[$i]['special_requirements']."</td>";
					echo "<td>".$value[$i]['priority']."</td>";
					echo "<td>".$value[$i]['period']."</td>";
					echo "<td>".$value[$i]['day']."</td>";
					echo "<td>".$value[$i]['duration']."</td>";
					
					
					if($value[$i]['week']==0) { //default weeks
						echo "<td>1, 2, 3, 4, 5, 6,7,8, 9, 10, 11, 12</td><td></td></tr>";
					} else {
						//sorting the list of numbers into lowest first order 
						$sortedWeeks = explode(', ' , $value[$i]['week']);
						$sortedWeeks1 = sort($sortedWeeks);
						$sortedWeeks1 = implode(', ', $sortedWeeks);
						echo "<td>".$sortedWeeks1."</td><td></td></tr>";
					}
				}
			?>
		</table>
		
		</div>
		

		</div>

		
		

	</body>
</html>
