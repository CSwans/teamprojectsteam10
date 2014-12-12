<?php
	//starts the session and destroys, will redirect to the login page
	//March Intuch
	session_start();
	session_destroy();
	header('Location: login.html');
?>
