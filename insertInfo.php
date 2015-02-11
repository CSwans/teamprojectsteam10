<?php
	//header("Content-Type: text/javascript; charset=utf-8");
	session_start();
	if(!isset($_SESSION['username']) || !isset($_SESSION['password'])) {
		header('Location: login.html');
	}
	
	$n=$_POST['noRooms'];
	
	$deptCode = strtoupper($_SESSION['username']);
	
	$moduleCode = $_POST['module_code_select'];
	
	$roomCode = $_POST['roomCode0'];
	$roomCode2; if($n > 1) $roomCode2=$_POST['roomCode1'];
	$roomCode3; if($n > 2) $roomCode3=$_POST['roomCode2'];
	$roomCode4; if($n > 3) $roomCode4=$_POST['roomCode3'];
	
	$cap1=$_POST['capacity'];
	$cap2; if($n > 1) $cap2 = $_POST['capacity1'];
	$cap3; if($n > 2) $cap3 = $_POST['capacity2'];
	$cap4; if($n > 3) $cap4 = $_POST['capacity3'];
	
	$wheelchair=$_POST['wheelchair'];
	$wheelchair2; if($n > 1) $wheelchair2=$_POST['wheelchair2'];
	$wheelchair3; if($n > 2) $wheelchair3=$_POST['wheelchair3'];
	$wheelchair4; if($n > 3) $wheelchair4=$_POST['wheelchair4'];
	
	$projector=$_POST['projector'];
	$projector2; if($n > 1) $projector2=$_POST['projector2'];
	$projector3; if($n > 2) $projector3=$_POST['projector3'];
	$projector4; if($n > 3) $projector4=$_POST['projector4'];
	
	$visualiser=$_POST['visualiser'];
	$visualiser2; if($n > 1) $visualiser2=$_POST['visualiser2'];
	$visualiser3; if($n > 2) $visualiser3=$_POST['visualiser3'];
	$visualiser4; if($n > 3) $visualiser4=$_POST['visualiser4'];
	
	$whiteboard=$_POST['whiteboard'];
	$whiteboard2; if($n > 1) $whiteboard2=$_POST['whiteboard2'];
	$whiteboard3; if($n > 2) $whiteboard3=$_POST['whiteboard3'];
	$whiteboard4; if($n > 3) $whiteboard4=$_POST['whiteboard4'];
	
	$priority=$_POST['priorityInput'];
	
	$specialRequirements="";
	if(isset($_POST['specialReq'])) $specialRequirements=$_POST['specialReq'];
	
	$period=$_POST['time'];
	
	$days=array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday" );
	$dayNo=$_POST['day'];
	$day=$days[$dayNo-1];
	
	$duration=$_POST['duration'];
	
	$weekInsert="";
	$weekInsert2="";
	$weekInsert3="";
	$weekInsert4="";
	$defaultWeeks = array(1,2,3,4,5,6,7,8,9,10,11,12);
	
	//connects to the database using the username and password
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
	$sql1 = "INSERT INTO REQUEST(dept_code, module_code, room_code, capacity, wheelchair, projector, visualiser, whiteboard, special_requirements, priority, period, day, duration, req_group) VALUES ('$deptCode', '$moduleCode', '$roomCode', '$cap1', '$wheelchair', '$projector', '$visualiser', '$whiteboard', '$specialRequirements', '$priority', '$period', '$day', '$duration' , NULL)";
	$res =& $db->query($sql1); //getting the result from the database
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	$id1 = $db->lastInsertID('REQUEST');
	if (PEAR::isError($id1)) {
    	die($id->getMessage());
	}
	$id2 = $id1 + 1;
	$id3 = $id1 + 2;
	$id4 = $id1 + 3;
	
	if(!empty($_POST['weeks'])){ //checks to see if it is the default week format (1-12) if it is then it will put 0 in the REQUEST_WEEKS table 
			if(sizeof(array_diff($_POST['weeks'],$defaultWeeks)) != 0 || sizeof(array_diff($defaultWeeks,$_POST['weeks'])) != 0) {
				foreach($_POST['weeks'] as $weeks){
					$weekInsert = 'INSERT INTO REQUEST_WEEKS(request_id, week) VALUES (' . $id1 . ',' . $weeks . '); ';
					
					$res =& $db->query($weekInsert); 
					if(PEAR::isError($res)){
						die($res->getMessage());
					}
					
					/* $weekInsert2.= 'INSERT INTO REQUEST_WEEKS(request_id, week) VALUES (' . ($id2) . ',' . $weeks . '); ';
					$weekInsert3.= 'INSERT INTO REQUEST_WEEKS(request_id, week) VALUES (' . ($id3) . ',' . $weeks . '); ';
					$weekInsert4.= 'INSERT INTO REQUEST_WEEKS(request_id, week) VALUES (' . ($id4) . ',' . $weeks . '); '; */
				}
			} else {
				$weekInsert = 'INSERT INTO REQUEST_WEEKS (request_id, week) VALUES (' . $id1 . ',0); ';
				$weekInsert2 = 'INSERT INTO REQUEST_WEEKS (request_id, week) VALUES (' . ($id2) . ',0); ';
				$weekInsert3 = 'INSERT INTO REQUEST_WEEKS (request_id, week) VALUES (' . ($id3) . ',0); ';
				$weekInsert4 = 'INSERT INTO REQUEST_WEEKS (request_id, week) VALUES (' . ($id4) . ',0); ';
			}
	}
		
	/* $res =& $db->query($weekInsert); 
	if(PEAR::isError($res)){
		die($res->getMessage());
	} */
	if($n > 1){
		$sql2 = 'INSERT INTO REQUEST(request_id, dept_code, module_code, room_code, capacity, wheelchair, projector, visualiser, whiteboard, special_Requirements, priority, period, day, duration, req_group)
           VALUES (' . ($id2) .',\''. $deptCode .'\',\''. $moduleCode .'\',\''. $roomCode2 .'\',\''. $cap2 .'\','. $wheelchair2 .','. $projector2 .','. $visualiser2 .','. $whiteboard2 .',\''. $specialRequirements .'\','.  $priority .','. $period .',\''. $day .'\','. $duration .','. $id1 .');';
	   
		$res =& $db->query($sql2); 
		if(PEAR::isError($res)){
			die($res->getMessage());
		}
		
		$res =& $db->query($weekInsert2); 
		if(PEAR::isError($res)){
			die($res->getMessage());
		}
	}
	
	if($n > 2){
		$sql3 = 'INSERT INTO REQUEST(request_id, dept_code, module_code, room_code, capacity, wheelchair, projector, visualiser, whiteboard, special_Requirements, priority, period, day, duration, req_group)
           VALUES (' . ($id3) .',\''. $deptCode .'\',\''. $moduleCode .'\',\''. $roomCode3 .'\',\''. $cap3 .'\','. $wheelchair3 .','. $projector3 .','. $visualiser3 .','. $whiteboard3 .',\''. $specialRequirements .'\','.  $priority .','. $period .',\''. $day .'\','. $duration .','. $id1 .');';
		
		$res =& $db->query($sql3); 
		if(PEAR::isError($res)){
			die($res->getMessage());
		}
		
		$res =& $db->query($weekInsert3); 
		if(PEAR::isError($res)){
			die($res->getMessage());
		}
	}
	
	if($n > 3){
		$sql4 = 'INSERT INTO REQUEST(request_id, dept_code, module_code, room_code, capacity, wheelchair, projector, visualiser, whiteboard, special_Requirements, priority, period, day, duration, req_group)
           VALUES (' . ($id4) .',\''. $deptCode .'\',\''. $moduleCode .'\',\''. $roomCode4 .'\',\''. $cap4 .'\','. $wheelchair4 .','. $projector4 .','. $visualiser4 .','. $whiteboard4 .',\''. $specialRequirements .'\','.  $priority .','. $period .',\''. $day .'\','. $duration .','. $id1 .');';
		
		$res =& $db->query($sql4); 
		if(PEAR::isError($res)){
			die($res->getMessage());
		}
		
		$res =& $db->query($weekInsert4); 
		if(PEAR::isError($res)){
			die($res->getMessage());
		}
	}
	
	$sql5 = "SELECT * FROM REQUEST";
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