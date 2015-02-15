<?php
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
	
	//username is the uppercase dept code that was loggged in
	$deptCode = strtoupper($_SESSION['username']);
	$modCode = $_POST['modCode'];
	$modTitle= $_POST['modTitle'];
	$sql = "INSERT INTO MODULES(dept_code, module_code, module_title) VALUES('$deptCode', '$modCode', '$modTitle')";
	$res =& $db->query($sql); //query the result from the database
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	echo json_encode("The module has been added");
?>