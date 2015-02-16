<?php
	/*
		This file will retrieve all the needed data from the database. This
		includes all the requests from REQUEST, all the bookings from BOOKING
		and then the requests that have been rejected, this is taken from
		REJECTION. The data will be echoed into the JavaScript variables lower
		down in the file.
		
		Created by Callan Swanson and Inthuch Therdchanakul
	*/
		
	session_start();
	if(!isset($_SESSION['username']) || !isset($_SESSION['password'])) {
		header('Location: login.html');
	}
	//connects to the database using the username and password
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
	
	//this selects  all the requests
	$sql = "SELECT REQUEST.request_id, module_code, REQUEST.room_code, 
			capacity, wheelchair, projector, visualiser, whiteboard,
			special_requirements, priority, period, day,
			duration,
			GROUP_CONCAT(CONVERT(REQUEST_WEEKS.week, CHAR(8)) SEPARATOR ', ')
			AS week 
			FROM REQUEST,REQUEST_WEEKS 
			WHERE REQUEST.request_id = REQUEST_WEEKS.request_id 
				AND dept_code = '".$username."'
			GROUP BY request_id";
			
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
	
	//contains the requests and the full data list containing
	//all the requests and bookings and rejections
	$jsonRequests = json_encode($value);
	$jsonFullData = json_encode($fullList);
	
	//this will select the booking requests that have been made from the 
	//table without the other requests
	$sql = "SELECT REQUEST.request_id, module_code, BOOKING.room_code,
			capacity, wheelchair, projector, visualiser, whiteboard, 
			special_requirements, priority, period, day, duration,
			GROUP_CONCAT(CONVERT(REQUEST_WEEKS.week, CHAR(8)) SEPARATOR ',')
			AS week, 
			CASE WHEN 
				REQUEST.room_code = BOOKING.room_code 
					THEN 0 
					ELSE 1 
				END AS partial 
			FROM REQUEST, REQUEST_WEEKS, BOOKING 
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
		$value2[] = $row; //booked
	}
	
	//this holds the bookings that have been made without the other requests
	$jsonBookings = json_encode($value2);
	
	//this will select the rejections that have been found in the database,
	//no other results
	$sql = "SELECT REQUEST.request_id, module_code, REQUEST.room_code, 
			capacity, wheelchair, projector, visualiser, whiteboard, 
			special_requirements, priority, period, day, duration,
			GROUP_CONCAT(CONVERT(REQUEST_WEEKS.week, CHAR(8)) SEPARATOR ',')
			AS week  
			FROM REQUEST,REQUEST_WEEKS, REJECTION 
			WHERE REQUEST.request_id = REQUEST_WEEKS.request_id AND 
			dept_code = '".$username."' AND 
			REJECTION.request_id = REQUEST.request_id 
			GROUP BY request_id";
			
	$res =& $db->query($sql); //getting the result from the database
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	$value3 = array(); //rejected
	
	//put each rows into value array
	while($row = $res->fetchRow()){
		$value3[] = $row;
	}
	//this only contains rejected requests
	$jsonRejections = json_encode($value3);
	
	//this will find the module codes that are contained within the department
	$sql = "SELECT module_code, module_title 
			FROM MODULES 
			WHERE dept_code='$username' 
			ORDER BY module_code;";
			
	$res =& $db->query($sql); 
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	$moduleInfo = array();
	while($row = $res->fetchRow()){
		$moduleInfo[] = $row;
	}
	//this holds the modules that are linked with that department
	$moduleJson = json_encode($moduleInfo);
	
	//this is searching for all the rooms and some specific data that is 
	//linked with it so we are able to delete some rooms from the dropdown
	//when they are facilities or capacity are chosen
	$sql = "SELECT DISTINCT ROOMS.capacity, wheelchair, projector, visualiser,
			whiteboard, PARKS.park, ROOMS.room_code, ROOMS.building_code 
			FROM ROOMS, PARKS 
			WHERE ROOMS.building_code = PARKS.building_code";
			
	$res =& $db->query($sql); //getting the result from the database
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	//this holds specific room data associated with every room, see then
	//sql above to know field names
	$roomData = array();
	
	while($row = $res->fetchRow()){
		$roomData[] = $row;
	}
	$roomDataJson = json_encode($roomData);
	
	//this is finding all the requests in teh REQUEST_WEEK table
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
	//this holds all the requests in teh REQUEST_WEEK table
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
	//this holds data about the buildings and parks where each room is located
	$buildingJson = json_encode($buildingData);
		
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
	echo "var fullData = ".$jsonFullData.";\n";
	echo "var moduleData = ".$moduleJson.";\n";
	echo "var roomData = ".$roomDataJson.";\n";
	echo "var weeksData = ".$weeksJson.";\n";
	echo "var buildingData = ".$buildingJson.";\n";
?>

<?php 
	/*
		This script simply searches for the department name that is logged in
		
		Created by Callan Swanson, Inthuch Therdchanakul
	*/
	
	$dept_code = strtolower($username); 
	
	$sql = "SELECT dept_name FROM DEPT WHERE dept_code = '$dept_code' "; 		
	$res =& $db->query($sql); //getting the result from the database
	if(PEAR::isError($res)){
		die($res->getMessage());
	}

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