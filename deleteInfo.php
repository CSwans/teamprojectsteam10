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
	$deptCode = strtoupper($_SESSION['username']);
	$request_id = $_POST['requestIdDel'];
	
	$sql1 = "DELETE FROM REQUEST WHERE request_id=$request_id";
	$res =& $db->query($sql1); //query the result from the database
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	
	$sql2 = "DELETE FROM REQUEST_WEEKS WHERE request_id=$request_id";
	$res =& $db->query($sql2); //query the database
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	$results = array();
	$results[] = $request_id;
	echo json_encode($results);
?>