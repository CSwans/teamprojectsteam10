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
			
			
			$sql = "SELECT REQUEST.request_id, module_code, REQUEST.room_code, capacity, wheelchair, projector, visualiser, whiteboard, special_requirements, priority, period, day, duration,GROUP_CONCAT(CONVERT(REQUEST_WEEKS.week, CHAR(8)) SEPARATOR ',') AS week FROM REQUEST,REQUEST_WEEKS WHERE REQUEST.request_id = REQUEST_WEEKS.request_id AND dept_code = '".$username."'GROUP BY request_id";
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
				
			?>
			
			
			
			$(function() {
				//console.log(bookingData);
				populateTable();
				
				findPendings();
				console.log(pendingData);
			});
			
			
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


			
		</script>
		<link rel="stylesheet" href="Theme.css"/>
	</head>
	<body>
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
	<div id="table_header">
		<table id="scrollTable">
			<tr>
				Click on the headers to sort the table<br>
                Status: 
                <select id="status" onChange="populateTable()">
                	<option>Rejected</option>
                    <option>Booked</option>
                    <option>Pending</option>
                </select>
				<td id="request_id" onClick="sortHeader(this.id);">
					Request</br>Id
				</td>
				<td id="module_code" onClick="sortHeader(this.id);">
					Module </br> Code
				</td>
				<td id="room_code" onClick="sortHeader(this.id)">
					Room Code
				</td>
				<td id="capacity" onClick="sortHeader(this.id)">
					Capacity
				</td>
				<td id="wheelchair" onClick="sortHeader(this.id)">
					Wheelchair
				</td>
				<td id="projector" onClick="sortHeader(this.id)">
					Projector
				</td>
				<td id="visualiser" onClick="sortHeader(this.id)">
					Visualiser
				</td>
				<td id="whiteboard" onClick="sortHeader(this.id)">
					Whiteboard
				</td>
				<td id="special_requirements">
					Special </br>Requirements
				</td>
				<td id="priority" onClick="sortHeader(this.id)">
					Priority
				</td>
				<td id="period" onClick="sortHeader(this.id)">
					Period
				</td>
				<td id="day" onClick="sortHeader(this.id)">
					Day
				</td>
				<td id="duration" onClick="sortHeader(this.id)">
					Duration
				</td>
				<td id="week(s)">
					Week(s)
				</td>
				<td id="status" onClick="sortHeader(this.id)">
					Status
				</td>
			</tr>
			</table>
			
			</div>
			
			
			<div id="content_wrap">
					<table id="dataTable">
			<tr style="display:none;">
                <select style="display:none;" id="status" onChange="populateTable()">
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
				<td id="week(s)">
					week(s)
				</td>
				<td id="status" onClick="sortHeader(this.id)">
					Status
				</td>
			</tr>
			
			
			
			<?php
				//putting in all the information about requests into the table
				//Callan Swanson
				for($i = 0; $i<sizeof($value);$i++) {
					
					echo "<tr><td>".$value[$i]['request_id']."</td>";
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
						echo "<td>1,2,3,4,5,6,7,8,9,10,11,12</td><td></td></tr>";
					} else {
						//sorting the list of numbers into lowest first order 
						$sortedWeeks = explode(',', $value[$i]['week']);
						$sortedWeeks1 = sort($sortedWeeks);
						$sortedWeeks1 = implode(',', $sortedWeeks);
						echo "<td>".$sortedWeeks1."</td><td></td></tr>";
					}
				}
			?>
		</table>
		Click on the
		</div>
		</div> 
	</body>
</html>
