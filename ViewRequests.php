<html>
	<head>
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
			
			$sql = "SELECT REQUEST.request_id, module_code, room_code, capacity, wheelchair, projector, visualiser, whiteboard, special_requirements, priority, period, day, duration,GROUP_CONCAT(CONVERT(REQUEST_WEEKS.week, CHAR(8)) SEPARATOR ',') AS week FROM REQUEST,REQUEST_WEEKS WHERE REQUEST.request_id = REQUEST_WEEKS.request_id AND dept_code = '".$username."'GROUP BY request_id";
			$res =& $db->query($sql); //getting the result from the database
			if(PEAR::isError($res)){
				die($res->getMessage());
			}
			$value = array();
			
			//put each rows into value array
			while($row = $res->fetchRow()){
				$value[] = $row;
				
			}
			
			
		?>
		
		<script src="js/jquery-1.11.1.min.js"></script>
		<script src="js/jquery-ui.js"></script>
		<script src="js/jquery.serializejson.min.js"></script>
		<script type="text/javascript">
			

			

			
		</script>
	</head>
	<body>
		<table>
			<tr>
				<td>
					request_id
				</td>
				<td>
					module_code
				</td>
				<td>
					room_code
				</td>
				<td>
					capacity
				</td>
				<td>
					wheelchair
				</td>
				<td>
					projector
				</td>
				<td>
					visualiser
				</td>
				<td>
					whiteboard
				</td>
				<td>
					special_requirements
				</td>
				<td>
					priority
				</td>
				<td>
					period
				</td>
				<td>
					day
				</td>
				<td>
					duration
				</td>
				<td>
					week(s)
				</td>
				
			</tr>
			<?php
				for($i = 0; $i<sizeof($value);$i++) {
					
					echo "<tr><td>".$value[$i]['request_id']."</td>";
					echo "<td>".$value[$i]['module_code']."</td>";
					echo "<td>".$value[$i]['room_code']."</td>";
					echo "<td>".$value[$i]['capacity']."</td>";
					echo "<td>".$value[$i]['wheelchair']."</td>";
					echo "<td>".$value[$i]['projector']."</td>";
					echo "<td>".$value[$i]['visualiser']."</td>";
					echo "<td>".$value[$i]['whiteboard']."</td>";
					echo "<td>".$value[$i]['special_requirements']."</td>";
					echo "<td>".$value[$i]['priority']."</td>";
					echo "<td>".$value[$i]['period']."</td>";
					echo "<td>".$value[$i]['day']."</td>";
					echo "<td>".$value[$i]['duration']."</td>";
					
					
					if($value[$i]['week']==0) { //default weeks
						echo "<td>1,2,3,4,5,6,7,8,9,10,11,12</td></tr>";
					} else {
						echo "<td>".$value[$i]['week']."</td></tr>";
					}
				}
			?>
		</table>
	</body>
</html>