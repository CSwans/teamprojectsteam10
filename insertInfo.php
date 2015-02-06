<?php
	session_start();
	if(!isset($_SESSION['username']) || !isset($_SESSION['password'])) {
		header('Location: login.html');
	}
	
	//connects to the database using the username and password
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
	
	//selects the values from the table, 0 week only offurs once per room if it is in the default weeks 
	//callan Swanson 
	$sql = "SELECT week, day, period, duration FROM REQUEST_WEEKS, BOOKING, REQUEST WHERE REQUEST_WEEKS.request_id=BOOKING.request_id AND REQUEST.request_id=BOOKING.request_id AND BOOKING.room_code='".$room."'";
	$res =& $db->query($sql); //getting the result from the database
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	$value = array();
	//put each rows into value array
	while($row = $res->fetchRow()){
		$results[] = $row;
	}
	
	echo json_encode($results);
?>