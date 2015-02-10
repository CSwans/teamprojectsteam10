<?php
	require_once "MDB2.php";
	$host = "co-project.lboro.ac.uk"; //host name
	$dbName = "team10"; //database name
	$dsn = "mysql://team10:abg83rew@$host/$dbName"; //login information
	$db =& MDB2::connect($dsn);
	if(PEAR::isError($db)){ //if we couldnt connect then end the connection
		die($db->getMessage());
	}
	$db->setFetchMode(MDB2_FETCHMODE_ASSOC);
	
	$sql="INSERT INTO `team10`.`REQUEST` (`request_id`, `dept_code`, `module_code`, `room_code`, `capacity`, `wheelchair`, `projector`, `visualiser`, `whiteboard`, `special_requirements`, `priority`, `period`, `day`, `duration`, `req_group`) VALUES (NULL, 'CO', '14COA102', 'A.0.01', '77', '1', '1', '1', '1', 'eeeeeeeeeee', '1', '2', 'Friday', '2', NULL)";
	$res =& $db->query($sql); //getting the result from the database
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	$id1 = $db->lastInsertID('REQUEST');
	if (PEAR::isError($id1)) {
    	die($id1->getMessage());
	}
	$id2 = $id1 + 1;
	$id3 = $id1 + 2;
	$id4 = $id1 + 3;
	echo $id2."\n";
	echo $id3."\n";
	echo $id4."\n";
	
?>