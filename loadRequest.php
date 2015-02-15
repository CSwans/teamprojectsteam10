<?php
	/*
		This page will input all the requests that are within the
		LAST_YEAR_REQUEST and LAST_YEAR_WEEK tables. The concurrency
		with the request_id is held between these two tables and the
		two new ones that they are being inserted into. WIll first input
		the information into the REQUEST table, it will then input the
		information into the REQUEST_WEEKS table with the correct request_ids.

		Created by Inthuch Therdchanakul, Nikolas Demosthenous and Callan Swanson 
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
	
	//deptCode is the uppercase dept code that was loggged in
	$i = $_POST['form'];
	$deptCode = strtoupper($_SESSION['username']);
	
	//insert request from last year with the same department code into REQUEST table
	$sql = "INSERT INTO REQUEST (dept_code, module_code, room_code, capacity,
			wheelchair, projector, visualiser, whiteboard, 
			special_requirements, priority, period, day, duration, req_group)
			SELECT dept_code, module_code, room_code, capacity, wheelchair,
			projector, visualiser, whiteboard, special_requirements, priority,
			period, day, duration, req_group  
			FROM LAST_YEAR_REQUEST 
			WHERE dept_code='$deptCode'";
			
	$res =& $db->query($sql); //query the result from the database
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	//get id of last inserted request
	
	
	//get last year week array
	$sql = "SELECT * FROM LAST_YEAR_WEEK 
			WHERE request_id IN 
				(SELECT request_id 
				 FROM LAST_YEAR_REQUEST 
				 WHERE dept_code = '$deptCode')
			ORDER BY request_id DESC";
	
	$res =& $db->query($sql); //query the result from the database
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	$weekArr = array();
	while($row = $res->fetchRow()){
		$weekArr[] = $row;
	}
	
	//find the number of rows within the LAST_YEAR_REQUEST table
	$sql = "SELECT COUNT(request_id) AS id 
			FROM LAST_YEAR_REQUEST
			WHERE dept_code='$deptCode'";
			
	$res =& $db->query($sql); //query the result from the database
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	while($row = $res->fetchRow()){
		$length = $row['id'];
	}
	
	//recently inserted request id
	$sql = "SELECT request_id 
			FROM REQUEST 
			ORDER BY request_id DESC 
			LIMIT $length";
			
	$res =& $db->query($sql); //query the result from the database
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	$thisYearId = array();
	while($row = $res->fetchRow()){
		$thisYearId[] = $row;
	}
	
	//runs though the whole array and will input a week row if the
	//request_id == the request_id within the week information array 
	$index = 0;
	for($i=0;$i<sizeof($thisYearId);$i++){
		$currentId = $weekArr[$index]['request_id'];
		for($j=0;$j<sizeof($weekArr);$j++){
			if($currentId == $weekArr[$j]['request_id']){
				//creates a statement if there hasn't already been one 
				//for this week and request_id
				$sql = "INSERT INTO REQUEST_WEEKS(request_id,week) 
						VALUES(".$thisYearId[$i]['request_id'].", 
						".$weekArr[$j]['week'].")";
						
				$res =& $db->query($sql); //query the result from the database
				if(PEAR::isError($res)){
					die($res->getMessage());
				}
			}
			else{
				$index = $j;
			}
		}
	}
	
	//get the last inserted request from REQUEST table
	//works even if the information is not in order within the table
	$sql = "SELECT * FROM REQUEST 
			WHERE dept_code='$deptCode' 
			ORDER BY request_id DESC 
			LIMIT $length";
	
	$res =& $db->query($sql); //query the result from the database
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	$results = array();

	while($row = $res->fetchRow()){
		$results[] = $row;
	}
	
	echo json_encode($results);
?>