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
	$newPWord = $_POST['newPWord1'];
	
	$sql1 = "SELECT * FROM DEPT WHERE dept_code = '".$_SESSION['username']."'";
	$res =& $db->query($sql1); //query the result from the database
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	
	while($row = $res->fetchRow()){
		$pass = $row['password'];
	}
	
	if($pass != $_POST['oldPWord']) {
		echo json_encode("Wrong password");
	} else {
		$sql = "update DEPT SET password = '$newPWord' where dept_code = '".$_SESSION['username']."'";
		$res =& $db->query($sql); //query the result from the database
		if(PEAR::isError($res)){
			die($res->getMessage());
		}
		
		echo json_encode("Password updated");
	}
	
	
	
	
?>