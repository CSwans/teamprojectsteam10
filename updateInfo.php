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
	$request_id = $_POST['requestId'];
	$moduleCode = $_POST['module_code_select'];
	$roomCode = $_POST['roomCode0'];
	$cap1=$_POST['capacity'];
	$wheelchair=$_POST['wheelchair'];
	$projector=$_POST['projector'];
	$visualiser=$_POST['visualiser'];
	$whiteboard=$_POST['whiteboard'];
	$priority=$_POST['priorityInput'];
	$priority=$_POST['priorityInput'];
	$specialRequirements="";
	if(isset($_POST['specialReq'])) 
		$specialRequirements=$_POST['specialReq'];
	
	$period=$_POST['time'];
	$days=array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday" );
	$dayNo=$_POST['day'];
	$day=$days[$dayNo-1];
	$duration=$_POST['duration'];
	$weekInsert="";
	$defaultWeeks = array(1,2,3,4,5,6,7,8,9,10,11,12);
	
	$sql1 = "UPDATE REQUEST SET module_code='$moduleCode', room_code='$roomCode', capacity=$cap1, wheelchair=$wheelchair, projector=$projector, visualiser=$visualiser, whiteboard=$whiteboard, special_requirements='$specialRequirements',
	 priority=$priority, period=$period, day='$day', duration=$duration WHERE request_id=$request_id";
	$res =& $db->query($sql1); //query the result from the database
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	
	$sql2 = "DELETE FROM REQUEST_WEEKS WHERE request_id=$request_id";
	$res =& $db->query($sql2); //query the database
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	
	if(!empty($_POST['weeks'])){ //checks to see if it is the default week format (1-12) if it is then it will put 0 in the REQUEST_WEEKS table 
		if(sizeof(array_diff($_POST['weeks'],$defaultWeeks)) != 0 || sizeof(array_diff($defaultWeeks,$_POST['weeks'])) != 0) {
			foreach($_POST['weeks'] as $weeks){
				$weekInsert = 'INSERT INTO REQUEST_WEEKS(request_id, week) VALUES (' . $request_id . ',' . $weeks . '); ';
				$res =& $db->query($weekInsert); 
				if(PEAR::isError($res)){
					die($res->getMessage());
				} 
		
			}
		}
	}	
	else{
		$weekInsert = 'INSERT INTO REQUEST_WEEKS (request_id, week) VALUES (' . $request_id . ',0); ';
		$res =& $db->query($weekInsert); 
		if(PEAR::isError($res)){
			die($res->getMessage());
		} 
	}
	
<<<<<<< HEAD
	$sql5 = "SELECT * FROM REQUEST WHERE request_id='$request_id'";
=======
	$sql5 = "SELECT REQUEST.request_id, module_code, REQUEST.room_code, capacity, wheelchair, projector, visualiser, whiteboard, special_requirements, priority, period, day, duration,GROUP_CONCAT(CONVERT(REQUEST_WEEKS.week, CHAR(8)) SEPARATOR ', ') AS week FROM REQUEST,REQUEST_WEEKS WHERE REQUEST.request_id = REQUEST_WEEKS.request_id AND REQUEST.request_id='$request_id' GROUP BY request_id";
>>>>>>> origin/march
	$res =& $db->query($sql5); 
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	$results = array();
	while($row = $res->fetchRow()){
		$results[] = $row;
	}
	
	echo json_encode($results);
?>
