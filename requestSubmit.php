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
		day: <?php echo $_POST['day']; ?>
		</br>
		weeks: <?php echo $_POST['week']; ?>
		</br>
		time: <?php echo $_POST['time']; ?>
		</br>
		special requirements: <?php echo $_POST['specialReq']; ?>
		</br>
		room code: <?php echo $_POST['roomCode']; ?>
		</br>
		wheelchair: <?php echo $_POST['wheelchair']; ?>
		</br>
		projector: <?php echo $_POST['projector']; ?>
		</br>
		visuliser: <?php echo $_POST['visualiser']; ?>
		</br>
		whitboard: <?php echo $_POST['whiteboard']; ?>
		</br>
	</body>
</html>