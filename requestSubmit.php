<html>
<head>
<?php 
	//Starts the session, if there is not any sessions then it will transfer to the login page and the user will ave to log in again
	//March Intuch
	session_start();
	if(!isset($_SESSION['username']) || !isset($_SESSION['password']))
	{
		header('Location: login.html');	
	}
?>
</head>
	<body>
		<!--
			WE NEED TO CONTAIN THESE FIELDS IN THE INSERT:
				dept_code 			(Department code)
				module_code			(Module code)
				room_code				(Room code)
				capacity				(Numeric)
				wheelchair			(1 - Yes 0 - No)
				projector				(1 - Yes 0 - No)
				visualiser			(1 - Yes 0 - No)
				whitebooard			(1 - Yes 0 - No)
				special_requirements		(Text field)
				priority				(1 - Yes 0 - No)
				period				(1 - 9)
				day					(Monday, Tuesday...)
				duration				(1, 2...)
				group	(can be null)	(Request id of the first room in the multiple room bookings)
		-->
		
		
	   <!-- modified for multiple room requests as individual requests - Tom Middleton -->
	   
		<?php
	   
		for ($x = 0; $x < $_POST['noRooms']; $x++) {
	   		echo 'Dept: ';  echo $_SESSION['username'];
	    	echo '</br>Day: '; 
		
			if(isset($_POST['day'])) {
				echo $_POST['day'];
			} else {
				echo 'No day chosen';
			}
		
			echo '</br>Weeks: ';
		
		// Echos the value set in HTML form for each checked checkbox, echoing the weeks that were picked -->
		// Scott Marshall -->
		
		 
			if(!empty($_POST['weeks'])){
				foreach($_POST['weeks'] as $weeks){
					echo ($weeks);
					echo (", ");
				}
			}
			
			echo '</br>Time: ';
			echo $_POST['time'];
		    echo '</br>Special Requirements: ';
		    echo $_POST['specialReq'];
			
			echo '</br>No of Rooms: ';
		
			//Added for multiple room request - Tom Middleton
		 
			echo $_POST['noRooms'];
			echo '</br>Room Pref ' .($x+1). ': ';
		
			if(isset($_POST['roomCode'.$x])) {
				if($_POST['roomCode'.$x]!="") {
					echo $_POST['roomCode'.$x]; 
				} else {
					echo "No room selected";
				}
			} else {
				echo "Room code not posted";
			}
		
			echo '</br>Wheelchair: '; 
	
			if(isset($_POST['wheelchair'])&&$_POST['wheelchair']=='1')
				echo "1";
			else 
				echo"0";
		
			echo '</br>Projector: '; 
	
			if(isset($_POST['projector'])&&$_POST['projector']=='1')
				echo "1";
			else 
				echo"0";
			
			echo '</br>Visuliser: ';
		
			if(isset($_POST['visualiser'])&&$_POST['visualiser']=='1')
				echo "1";
			else 
				echo"0";
		
			echo '</br>Whiteboard: ';
		
			if(isset($_POST['whiteboard'])&&$_POST['whiteboard']=='1')
				echo "1";
			else 
				echo"0";
			
			echo '<br/><br/><br/>';	
		}
	?>
	
	</body>
</html>
