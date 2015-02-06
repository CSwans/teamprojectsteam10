<?php 
	//Starts the session, if there is not any sessions then it will transfer to the login page and the user will ave to log in again
	//March Intuch
	session_start();
	if(!isset($_SESSION['username']) || !isset($_SESSION['password']))
	{
		header('Location: login.html');	
	}
	
	$requestId=3;
	$requestId2=4;
	$requestId3=5;
	$requestId4=6;
	
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
	
	$priority=1;
	
	$specialRequirements="";
	if(isset($_POST['specialReq'])) $specialRequirements=$_POST['specialReq'];
	
	$period=$_POST['time'];
	
	$days=array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday" );
	$dayNo=$_POST['day'];
	$day=$days[$dayNo-1];
	
	
	$duration=$_POST['duration'];
	
	$group=3;
	
	$weekInsert="";
	$defaultWeeks = array(1,2,3,4,5,6,7,8,9,10,11,12);
	
	
	//connects to the database using the username and passoword 
	$host = "co-project.lboro.ac.uk"; //host name
	$dbName = "team10"; //database name
	$username = "team10";
	$password = "abg83rew";
	
	// Create connection
	$conn = new mysqli($host, $username, $password, $dbName);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
	
	
	
		$requestId = mysqli_insert_id($conn);	
			
	$sql1= 'INSERT INTO REQUEST(request_id, dept_code, module_code, room_code, capacity, wheelchair, projector, visualiser, whiteboard, special_Requirements, priority, period, day, duration)
           VALUES (' . $requestId .',\''. $deptCode .'\',\''. $moduleCode .'\',\''. $roomCode .'\',\''. $cap1 .'\','. $wheelchair .','. $projector .','. $visualiser .','. $whiteboard .',\''. $specialRequirements .'\','.  $priority .','. $period .',\''. $day .'\','. $duration .');';
	
	
	
		//inputs the first row of the requst as he REQUEST
		if ($conn->multi_query($sql1) === TRUE) {
			echo "SQL1 created		";
			} 
		else {
			echo "Error: " . $sql1 . "<br>" . $conn->error;
		}

		$requestId = mysqli_insert_id($conn);
		
		if(!empty($_POST['weeks'])){ //checks to see if it is the default week format (1-12) if it is then it will put 0 in the REQUEST_WEEKS table 
			if(sizeof(array_diff($_POST['weeks'],$defaultWeeks)) != 0 || sizeof(array_diff($defaultWeeks,$_POST['weeks'])) != 0) {
				foreach($_POST['weeks'] as $weeks){
					$weekInsert .= 'INSERT INTO REQUEST_WEEKS (request_id, week) VALUES (' . $requestId . ',' . $weeks . '); ';
					//$weekInsert2 .= 'INSERT INTO REQUEST_WEEKS (request_id, week) VALUES (' . ($requestId+1) . ',' . $weeks . '); ';
					//$weekInsert3 .= 'INSERT INTO REQUEST_WEEKS (request_id, week) VALUES (' . ($requestId+2) . ',' . $weeks . '); ';
					//$weekInsert4 .= 'INSERT INTO REQUEST_WEEKS (request_id, week) VALUES (' . ($requestId+3) . ',' . $weeks . '); ';
				}
			} else {
				$weekInsert = 'INSERT INTO REQUEST_WEEKS (request_id, week) VALUES (' . $requestId . ',0); ';
				//$weekInsert2 = 'INSERT INTO REQUEST_WEEKS (request_id, week) VALUES (' . ($requestId+1) . ',0); ';
				//$weekInsert3 = 'INSERT INTO REQUEST_WEEKS (request_id, week) VALUES (' . ($requestId+2) . ',0); ';
				//$weekInsert4 = 'INSERT INTO REQUEST_WEEKS (request_id, week) VALUES (' . ($requestId+3) . ',0); ';
			}
		}	
		
		
		//inputs the weeks into the REQUEST_WEEKS table along with the correct requestID
		if ($conn->multi_query($weekInsert) === TRUE) {
			echo "Weeks1 created		";
		} 
		else {
			echo "Error: " . $weekInsert . "<br>" . $conn->error;
		}

	$conn->close();

	if($n > 1){
		$sql2 = 'INSERT INTO REQUEST(request_id, dept_code, module_code, room_code, capacity, wheelchair, projector, visualiser, whiteboard, special_Requirements, priority, period, day, duration, req_group)
           VALUES (' . ($requestId+1) .',\''. $deptCode .'\',\''. $moduleCode .'\',\''. $roomCode2 .'\',\''. $cap2 .'\','. $wheelchair2 .','. $projector2 .','. $visualiser2 .','. $whiteboard2 .',\''. $specialRequirements .'\','.  $priority .','. $period .',\''. $day .'\','. $duration .','. $requestId .');';
	   
		// Create connection
		$conn = new mysqli($host, $username, $password, $dbName);
		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		if ($conn->multi_query($sql2) === TRUE) {
			echo "SQL2 created		";
			} 
		else {
			echo "Error: " . $sql2 . "<br>" . $conn->error;
		}
		
		//inputs the weeks into the REQUEST_WEEKS table along with the correct requestID
		if ($conn->multi_query($weekInsert) === TRUE) {
			echo "Weeks2 created		";
			} 
		else {
			echo "Error: " . $weekInsert . "<br>" . $conn->error;
		}
		

		
	$conn->close();
	}
	
	
	if($n > 2){
	  $sql3 = 'INSERT INTO REQUEST(request_id, dept_code, module_code, room_code, capacity, wheelchair, projector, visualiser, whiteboard, special_Requirements, priority, period, day, duration, req_group)
           VALUES (' . ($requestId+2) .',\''. $deptCode .'\',\''. $moduleCode .'\',\''. $roomCode3 .'\',\''. $cap3 .'\','. $wheelchair3 .','. $projector3 .','. $visualiser3 .','. $whiteboard3 .',\''. $specialRequirements .'\','.  $priority .','. $period .',\''. $day .'\','. $duration .','. $requestId .');';
	   
		// Create connection
		$conn = new mysqli($host, $username, $password, $dbName);
		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		if ($conn->multi_query($sql3) === TRUE) {
			echo "SQL3 created		";
			} 
		else {
			echo "Error: " . $sql3 . "<br>" . $conn->error;
		}
		
		//inputs the weeks into the REQUEST_WEEKS table along with the correct requestID
		if ($conn->multi_query($weekInsert) === TRUE) {
			echo "Weeks3 created		";
		} 
		else {
			echo "Error: " . $weekInsert . "<br>" . $conn->error;
		}
	$conn->close();
	}
	
	if($n > 3){
		$sql4 = 'INSERT INTO REQUEST(request_id, dept_code, module_code, room_code, capacity, wheelchair, projector, visualiser, whiteboard, special_Requirements, priority, period, day, duration, req_group)
           VALUES (' . ($requestId+3) .',\''. $deptCode .'\',\''. $moduleCode .'\',\''. $roomCode4 .'\',\''. $cap4 .'\','. $wheelchair4 .','. $projector4 .','. $visualiser4 .','. $whiteboard4 .',\''. $specialRequirements .'\','.  $priority .','. $period .',\''. $day .'\','. $duration .','. $requestId .');';
	   
		// Create connection
		$conn = new mysqli($host, $username, $password, $dbName);
		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		if ($conn->multi_query($sql4) === TRUE) {
			echo "SQL4 created		";
			} 
		else {
			echo "Error: " . $sql4 . "<br>" . $conn->error;
		}
		
		//inputs the weeks into the REQUEST_WEEKS table along with the correct requestID
		if ($conn->multi_query($weekInsert) === TRUE) {
			echo "Weeks4 created		";
		} 
		else {
			echo "Error: " . $weekInsert . "<br>" . $conn->error;
		}
	$conn->close();
	
	}
    	

	
	
?>
