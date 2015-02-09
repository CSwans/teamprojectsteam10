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
			
			$sql = "SELECT REQUEST.request_id, module_code, room_code, capacity, wheelchair, projector, visualiser, whiteboard, special_requirements, priority, period, day, duration,GROUP_CONCAT(CONVERT(REQUEST_WEEKS.week, CHAR(8)) SEPARATOR ',') AS week FROM REQUEST,REQUEST_WEEKS WHERE REQUEST.request_id = REQUEST_WEEKS.request_id AND dept_code = '".$username."'GROUP BY request_id";
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
		<script type="text/javascript">
		
			<?php
				echo "var requestData = ".$json.";\n";
			?>
			
			
			function request_id(a, b) {
				return parseInt(a["request_id"]) - parseInt(b["request_id"]);
			}
			
			//sorting the module code in order a-z 1-9
			function module_code(a, b) {
				return a["module_code"] >  b["module_code"];
			}
			
			function room_code(a, b) {
				return a["room_code"] >  b["room_code"];
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
			////////////////////////////////////////////////\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\//////////////////////////////////////\\\\\\\
			function status(a, b) {
				var statInt = ["Booked", "Not Booked"];
				return parseInt(a["status"]) - parseInt(b["status"]);
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
					/*case "status" : 
						requestData.sort(status);
						break;*/////////////////////////////////////////\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\/////////////////////////////////
 
					default : 
						break;
				}
				
				populateTable();
			}
			
			//puts data into the table using jquery
			//callan swanson
			function populateTable() {
			for(var i=0; i<requestData.length; i++) {

					$("#dataTable tr:eq("+(i+1)+") td:eq(0)").html(requestData[i].request_id);
					$("#dataTable tr:eq("+(i+1)+") td:eq(1)").html(requestData[i].module_code);
					$("#dataTable tr:eq("+(i+1)+") td:eq(2)").html(requestData[i].room_code);
					$("#dataTable tr:eq("+(i+1)+") td:eq(3)").html(requestData[i].capacity);
					$("#dataTable tr:eq("+(i+1)+") td:eq(4)").html(requestData[i].wheelchair);
					$("#dataTable tr:eq("+(i+1)+") td:eq(5)").html(requestData[i].projector);
					$("#dataTable tr:eq("+(i+1)+") td:eq(6)").html(requestData[i].visualiser);
					$("#dataTable tr:eq("+(i+1)+") td:eq(7)").html(requestData[i].whiteboard);
					$("#dataTable tr:eq("+(i+1)+") td:eq(8)").html(requestData[i].special_requirements);
					$("#dataTable tr:eq("+(i+1)+") td:eq(9)").html(requestData[i].priority);
					$("#dataTable tr:eq("+(i+1)+") td:eq(10)").html(requestData[i].period);
					$("#dataTable tr:eq("+(i+1)+") td:eq(11)").html(requestData[i].day);
					$("#dataTable tr:eq("+(i+1)+") td:eq(12)").html(requestData[i].duration);
					
					console.log(requestData[i].week);
					if(requestData[i].week==0) { //default weeks
						$("#dataTable tr:eq("+(i+1)+") td:eq(13)").html("1,2,3,4,5,6,7,8,9,10,11,12");
					} else {
						//sorting the list of numbers into lowest first order 
						var sortedWeeks = requestData[i].week.split(",");
						sortedWeeks.sort();
						$("#dataTable tr:eq("+(i+1)+") td:eq(13)").html(sortedWeeks);
					}
					
					
					//$("#dataTable tr:eq("+(i+1)+") td:eq(0)").html(requestData[i].status); ///////////////////////\\\\\\\\\\\\\\\\\\\\\\\///////////////

				}
			}
			
		</script>
	</head>
	<body>
		<table id="dataTable">
			<tr>
				Click on the headers to sort the table
				<td id="request_id" onclick="sortHeader(this.id);">
					request_id
				</td>
				<td id="module_code" onclick="sortHeader(this.id);">
					module_code
				</td>
				<td id="room_code" onclick="sortHeader(this.id)">
					room_code
				</td>
				<td id="capacity" onclick="sortHeader(this.id)">
					capacity
				</td>
				<td id="wheelchair" onclick="sortHeader(this.id)">
					wheelchair
				</td>
				<td id="projector" onclick="sortHeader(this.id)">
					projector
				</td>
				<td id="visualiser" onclick="sortHeader(this.id)">
					visualiser
				</td>
				<td id="whiteboard" onclick="sortHeader(this.id)">
					whiteboard
				</td>
				<td id="special_requirements">
					special_requirements
				</td>
				<td id="priority" onclick="sortHeader(this.id)">
					priority
				</td>
				<td id="period" onclick="sortHeader(this.id)">
					period
				</td>
				<td id="day" onclick="sortHeader(this.id)">
					day
				</td>
				<td id="duration" onclick="sortHeader(this.id)">
					duration
				</td>
				<td id="week(s)">
					week(s)
				</td>
				<td id="status" onclick="sortHeader(this.id)">
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
						echo "<td>1,2,3,4,5,6,7,8,9,10,11,12</td></tr>";
					} else {
						//sorting the list of numbers into lowest first order 
						$sortedWeeks = explode(',', $value[$i]['week']);
						$sortedWeeks1 = sort($sortedWeeks);
						$sortedWeeks1 = implode(',', $sortedWeeks);
						echo "<td>".$sortedWeeks1."</td></tr>";
					}
				}
			?>
		</table>
	</body>
</html>