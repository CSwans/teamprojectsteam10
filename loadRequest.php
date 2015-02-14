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
	
	//the number of rows within te table, to be added in the request weeks table
	$sql = "COUNT(request_id) as number FROM REQUEST"; 
	
	$res =& $db->query($sql); //query the result from the database
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	while($row = $res->fetchRow()){
		$l = $row['number'];
	}
	
	//deptCode is the uppercase dept code that was loggged in
	$i = $_POST['form'];
	$deptCode = strtoupper($_SESSION['username']);
	//insert request from last year with the same department code into REQUEST table
	$sql = "INSERT INTO REQUEST (dept_code, module_code, room_code, capacity, wheelchair, projector, visualiser, whiteboard, special_requirements, priority, period, day, duration, req_group) SELECT dept_code, module_code, room_code, capacity, wheelchair, projector, visualiser, whiteboard, special_requirements, priority, period, day, duration, req_group  FROM LAST_YEAR_REQUEST WHERE dept_code='$deptCode'";
	$res =& $db->query($sql); //query the result from the database
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	//get id of last inserted request
	$id1 = $db->lastInsertID('REQUEST');
	if (PEAR::isError($id1)) {
    	die($id->getMessage());
	}
	
	$sql = "INSERT INTO REQUEST_WEEKS(request_id, week) SELECT SUM(request_id+$l), week FROM LAST_YEAR_WEEK";
	$res =& $db->query($sql); //query the result from the database
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	
	//get the last inserted request from REQUEST table
	$sql = "SELECT * FROM REQUEST WHERE dept_code='$deptCode' AND request_id = $id1";
	$res =& $db->query($sql); //query the result from the database
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	$results = array();
	//putting sql data into results array
	while($row = $res->fetchRow()){
		$results[] = $row;
	}
	//parse back as json
	echo json_encode($results);
?>