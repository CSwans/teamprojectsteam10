<?php
	/*
		This is selecting the usual information about the rooms and the
		department. The data passed back includes: week, day, period, duration for
		the selected room.
		
		Created by Tom Middleton, Calan Swanson  and Inthuch Inthuch Therdchanakul
	*/
	session_start();
	if(!isset($_SESSION['username']) || !isset($_SESSION['password'])) {
	header('Location: login.html');
	}
	
	require_once "MDB2.php";
	$host = "co-project.lboro.ac.uk"; 
	$dbName = "team10";
	$dsn = "mysql://team10:abg83rew@$host/$dbName";
	$db =& MDB2::connect($dsn); 
	if(PEAR::isError($db)){
		die($db->getMessage());
	}
	$db->setFetchMode(MDB2_FETCHMODE_ASSOC);
	
	//username is the uppercase dept code that was loggged in
	$username = strtoupper($_SESSION['username']);
	
	//retrieve rooms data from database and put them in javacript array using json
	$sql = "SELECT DISTINCT PARKS.park, ROOMS.room_code, ROOMS.building_code,
			ROOMS.capacity, ROOMS.wheelchair, ROOMS.projector, 
			ROOMS.visualiser, ROOMS.whiteboard FROM ROOMS,PARKS 
			WHERE ROOMS.building_code = PARKS.building_code";
			
	$res =& $db->query($sql); //getting the result from the database
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	$value = array();
	//put each rows into value array
	while($row = $res->fetchRow()){
		$value[] = $row;
	}
	//this contains the room data we retrieved
	$json = json_encode($value);
	
	//this is selecting the module code and title where teh department 
	//is the one that has logged in
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
		
		
	//Initialise list of bookings to take into account when populating room pref lists	
	$sql = "SELECT REQUEST.request_id, REQUEST.day, REQUEST.period, 
			REQUEST.duration, REQUEST_WEEKS.week, BOOKING.room_code 
			FROM REQUEST, BOOKING, REQUEST_WEEKS 
			WHERE REQUEST.request_id = REQUEST_WEEKS.request_id AND 
			REQUEST.request_id = BOOKING.request_id ";
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


<?php
	//pass value array onto javascript array roomData
	echo "var roomData = ". $json . ";\n";
	echo "var moduleData = ". $moduleJson . ";\n";
	echo "var bookingData = ". $bookingJson . ";\n";
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