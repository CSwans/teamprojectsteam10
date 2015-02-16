<?php
	/*
		This passes back the booked, pending and rejected requests. Will also
		include a partial booking field if the room code
		that has been requested is the same as the allocated room, then partial 
		will be =0
		
		Created by Callan Sweanson and Inthuch Therdchanakul
	*/
	
	session_start();
	if(!isset($_SESSION['username']) || !isset($_SESSION['password'])) {
		header('Location: login.html');
	}
	//connects to the database using the username and passoword
	require_once "MDB2.php";
	$host = "co-project.lboro.ac.uk"; 
	$dbName = "team10";
	$dsn = "mysql://team10:abg83rew@$host/$dbName"; 
	$db =& MDB2::connect($dsn); 
	if(PEAR::isError($db)){ 
		die($db->getMessage());
	}
	$db->setFetchMode(MDB2_FETCHMODE_ASSOC);
	
	//username is the upper-case dept code that was loggged in
	$username = strtoupper($_SESSION['username']);
	
	//this is selecting the information about the requests that have been
	//made by this department
	$sql = "SELECT REQUEST.request_id, module_code, REQUEST.room_code,
			capacity, wheelchair, projector, visualiser, whiteboard, 
			special_requirements, priority, period, day, duration,
			GROUP_CONCAT(CONVERT(REQUEST_WEEKS.week, CHAR(8)) SEPARATOR ', ')
			AS week 
			FROM REQUEST,REQUEST_WEEKS 
			WHERE REQUEST.request_id = REQUEST_WEEKS.request_id AND 
			dept_code = '".$username."'GROUP BY request_id";
			
	$res =& $db->query($sql); //getting the result from the database
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	$value = array();
	
	//put each rows into value array
	while($row = $res->fetchRow()){
		$value[] = $row;
		
	}
	//this holds the information about those requests the department has made
	$jsonRequests = json_encode($value);
	
	//this is finding all the booking data from the table, if the room code
	//that has been requested is the same as the allocated room, then partial 
	//will be =0
	$sql = "SELECT REQUEST.request_id, module_code, BOOKING.room_code,
			capacity, wheelchair, projector, visualiser, whiteboard, 
			special_requirements, priority, period, day, duration,
			GROUP_CONCAT(CONVERT(REQUEST_WEEKS.week, CHAR(8)) SEPARATOR ',') 
			AS week, 
			CASE 
				WHEN REQUEST.room_code = BOOKING.room_code 
					THEN 0 
					ELSE 1 
				END AS partial 
			FROM REQUEST,REQUEST_WEEKS, BOOKING 
			WHERE REQUEST.request_id = REQUEST_WEEKS.request_id AND 
			dept_code = '".$username."' AND 
			BOOKING.request_id = REQUEST.request_id 
			GROUP BY request_id";
			
	$res =& $db->query($sql); //getting the result from the database
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	$value2 = array();
	
	//put each rows into value array
	while($row = $res->fetchRow()){
		$value2[] = $row;
	}
	//this holds the booking data
	$jsonBookings = json_encode($value2);
	
	//this is finding the rejections from the database, no other requests
	$sql = "SELECT REQUEST.request_id, module_code, REQUEST.room_code,
			capacity, wheelchair, projector, visualiser, whiteboard,
			special_requirements, priority, period, day, duration,
			GROUP_CONCAT(CONVERT(REQUEST_WEEKS.week, CHAR(8)) SEPARATOR ',') 
			AS week  
			FROM REQUEST,REQUEST_WEEKS, REJECTION 
			WHERE REQUEST.request_id = REQUEST_WEEKS.request_id AND 
			dept_code = '".$username."' AND 
			REJECTION.request_id = REQUEST.request_id GROUP BY request_id";
			
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
	
	//selecting some select data from the room information
	$sql = "SELECT DISTINCT ROOMS.capacity, wheelchair, projector, visualiser,
	whiteboard, PARKS.park, ROOMS.room_code, ROOMS.building_code 
	FROM ROOMS, PARKS WHERE ROOMS.building_code = PARKS.building_code";
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
	
	//selecting the module code and title from teh list that this dept owns
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
	
?>

<?php
	/*
		This will echo all the data we have received from the database above
		into javascript arrays where we are able to reference them and insert
		into dropdowns ect.
		
		Created by Callan Swanson and Inthuch Therdchanakul
	*/
	
	//requestData WILL CHANGE TO HOLD THE PENDING DATA WHEN PAGE LOADS
	echo "var requestData = ".$jsonRequests.";\n"; 
	echo "var bookingData = ".$jsonBookings.";\n";
	echo "var rejectedData = ".$jsonRejections.";\n";
	echo "var roomData = ". $roomDataJson . ";\n";
	echo "var moduleData = ". $moduleJson . ";\n";
	echo "var buildingData = ". $buildingJson . ";\n";
?>

<?php 
	/*
		This script simply searches for the department name that is logged in
		
		Created by Callan Swanson, Inthuch Therdchanakul
	*/
	$dept_code = strtolower($username);
	$sql = "SELECT dept_name 
			FROM DEPT 
			WHERE dept_code = '$dept_code' ";
	$res =& $db->query($sql); //getting the result from the database
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
				//put each rows into value array
	while($row = $res->fetchRow()){
		echo $row["dept_name"];
	}  
?>  

<?php
	/*
		this is to create the dropdown for the period, includes the time in 24hr
		format

		Created by Scott Marshal
	*/
	echo "<select name='time' id='time' onchange='refill_duration()'>";
	for($i=1;$i<=9;$i++){
		$time = $i+8;
		echo "<option value='".$i."'>".$i." - ".$time.":00</option>";
	}
	echo "</select>";
?>

<?php
	/*
		This creates the dropdown for the duration
		
		Created by Scott Marshall
	*/
	echo "<select name='duration' id='duration'>";
	for($i=1;$i<=9;$i++){
		$duration = $i+8;
		echo "<option value='".$i."'>".$i."</option>";
	}
	echo "</select>";
?>

<?php
	/*
		This is populating and creating all the table cells we need in order
		to access and populate the table with the three different value tables
		(Pending, Rejected and Booked)
		
		Created by Callan Swanson 
	*/
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
			echo"<td>1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12</td><td></td></tr>";
		} else {
			//sorting the list of numbers into lowest first order 
			$sortedWeeks = explode(', ' , $value[$i]['week']);
			$sortedWeeks1 = sort($sortedWeeks);
			$sortedWeeks1 = implode(', ', $sortedWeeks);
			echo "<td>".$sortedWeeks1."</td><td></td></tr>";
		}
	}
?>
