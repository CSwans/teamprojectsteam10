<?php
	//This makes JSON work!
	header("Content-Type: text/javascript; charset=utf-8");
	//Starts the session, if there is not any sessions then it will transfer to
	//the login page and the user will ave to log in again
	//Inthuch Therdchanakul
	session_start();
	if(!isset($_SESSION['username']) || !isset($_SESSION['password'])) {
		header('Location: login.html');
	}
	
	//finding the values we want to find in the database
	//Callan Swanson, Inthuch Therdchanakul
	//$day = $_POST['day'];
	require_once "MDB2.php";
	$host = "co-project.lboro.ac.uk"; //host name
	$dbName = "team10"; //database name
	$dsn = "mysql://team10:abg83rew@$host/$dbName"; //login information
	$db =& MDB2::connect($dsn);
	if(PEAR::isError($db)){ //if we couldnt connect then end the connection
		die($db->getMessage());
	}
	$db->setFetchMode(MDB2_FETCHMODE_ASSOC);
	//username is the uppercase dept code that was loggged in
	$username = strtoupper($_SESSION['username']);
	
	//selects the values from the table, 0 week only offurs once per 
	//room if it is in the default weeks 
	//Callan Swanson 
	$sql = "SELECT * FROM REQUEST";
	$res =& $db->query($sql); //getting the result from the database
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	$value = array();
	//put each rows into value array
	while($row = $res->fetchRow()){
		$value[] = $row;
	}
	echo json_encode($value);
?>