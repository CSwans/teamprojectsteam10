<?php
	/*
		This page will return the values of all the weeks that a specific 
		request_id holds. This is in the form of zero or many rows where 0
		is the default week value (1-12).
		
		Created by Inthuch Therdchanakul
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
	
	//username is the uppercase dept code that was loggged in
	$deptCode = strtoupper($_SESSION['username']);
	$request_id = $_POST['weekCheck'];
	
	//finding the correct 
	$sql = "SELECT week 
			FROM REQUEST_WEEKS 
			WHERE request_id=$request_id";
			
	$res =& $db->query($sql);
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	$results = array();
	while($row = $res->fetchRow()){
		$results[] = $row;
	}
	echo json_encode($results);
?>
