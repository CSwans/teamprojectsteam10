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
		dept: <?php echo $_SESSION['username']; ?>
		</br>
		day: 
		<?php
			if(isset($_POST['day'])) {
				echo $_POST['day'];
			} else {
				echo 'No day chosen';
			}
		?>
		</br>
		weeks: <?php echo $_POST['week']; ?>
		</br>
		time: <?php echo $_POST['time']; ?>
		</br>
		special requirements: <?php echo $_POST['specialReq']; ?>
		</br>
		room code: 
		<?php 
			if(isset($_POST['roomCode'])) {
				//if($_POST['room_list'])!="" {
					echo $_POST['roomCode']; 
				//} else {
					echo "No room selected";
				//}
			} else {
				echo "Room code not posted";
			}
		?>
		</br>
		wheelchair: 
		<?php 
			if(isset($_POST['wheelchair'])&&$_POST['wheelchair']=='1')
				echo "1";
			else 
				echo"0";
		?>
		</br>
		projector: 
		<?php 
			if(isset($_POST['projector'])&&$_POST['projector']=='1')
				echo "1";
			else 
				echo"0";
		?>
		</br>
		visuliser: 
		<?php 
			if(isset($_POST['visualiser'])&&$_POST['visualiser']=='1')
				echo "1";
			else 
				echo"0";
		?>
		</br>
		whiteboard: 
		<?php 
			if(isset($_POST['whiteboard'])&&$_POST['whiteboard']=='1')
				echo "1";
			else 
				echo"0";
		?>
		</br>
	</body>
</html>