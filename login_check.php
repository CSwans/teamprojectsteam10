<?php
	//starts teh session, needed at teh top of every page where we are to use sessions
	//March Intuch
	session_start();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Untitled Document</title>
</head>

<body>

	<?php
		//initial session startup, connecting to the database and creating the session data, username and password
		//March Intuch
		if(!isset($_REQUEST["username"]) || !isset($_REQUEST["password"])){
			$_SESSION["username"] = 0;
			$_SESSION["password"] = 0;
		}
		else{
			$_SESSION["username"] = $_REQUEST["username"];
			$_SESSION["password"] = $_REQUEST["password"];	
		}
		
		$user = strtolower($_SESSION["username"]);
		$pass = $_SESSION["password"];
		require_once "MDB2.php";
		$host = "co-project.lboro.ac.uk"; 	//host name
		$dbName = "team10";					//database name
		$dsn = "mysql://team10:abg83rew@$host/$dbName";	//login information
		$db =& MDB2::connect($dsn);	//connecting to the server and connecting to the database
		if(PEAR::isError($db)){ 	//if we couldnt connect then end the connection
    		die($db->getMessage());
		}
		$db->setFetchMode(MDB2_FETCHMODE_ASSOC);
		$sql = "SELECT count(*) FROM DEPT WHERE dept_code='$user' AND password='$pass'"; 
		$res =& $db->query($sql);
		if(PEAR::isError($res)){
    		die($res->getMessage());
		}
		//finding the username and the password from the department table and then comapring them to the input
		$row = $res->fetchRow();
		if($row['count(*)'] == 1){
			$sql = "SELECT dept_code,password FROM DEPT WHERE dept_code='$user' AND password='$pass'"; 
			$res =& $db->query($sql);
			if(PEAR::isError($res)){
				die($res->getMessage());
			}
			//if the username and pass is the same as in the table, then move to the timetable page
			$row = $res->fetchRow();
			if($row["dept_code"] == $user && $row["password"] == $pass){
				header('Location: timetable.html');
				}
			}
		else{
			//if the username and password is wrong, then the session will be destroyed and the data is free
			session_destroy();
			echo "Invalid Username or Password</br>";
			echo "<a href='login.html'>Login</a>";
		}
	?>
</body>
</html>
