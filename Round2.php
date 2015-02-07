
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
		$username = strtoupper($_SESSION['username']);
		//retrieve rooms data from database and put them in javacript array using json
		$sql = "SELECT DISTINCT PARKS.park, ROOMS.room_code, ROOMS.building_code, ROOMS.capacity, ROOMS.wheelchair, ROOMS.projector, ROOMS.visualiser, ROOMS.whiteboard FROM ROOMS,PARKS WHERE ROOMS.building_code = PARKS.building_code";
		$res =& $db->query($sql); //getting the result from the database
		if(PEAR::isError($res)){
			die($res->getMessage());
		}
		$value = array();
		//put each rows into value array
		while($row = $res->fetchRow()){
			$value[] = $row;
		}
		$json = json_encode($value);
		
		$sql = "SELECT module_code, module_title FROM MODULES WHERE dept_code='$username' ORDER BY module_code;";
			$res =& $db->query($sql); //getting the result from the database
			if(PEAR::isError($res)){
				die($res->getMessage());
			}
			$moduleInfo = array();
			while($row = $res->fetchRow()){
				$moduleInfo[] = $row;
			}
			$moduleJson = json_encode($moduleInfo);
			//retrieveing info abou the modules and their titles
	?>
	<script type="text/javascript">
		<?php
			//pass value array onto javascript array roomData
			echo "var roomData = ". $json . ";\n";
			echo "var moduleData = ". $moduleJson . ";\n";
		?>
	</script>
	
	ROUND """"2323