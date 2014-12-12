<?php 
	//Starts the session, if there is not any sessions then it will transfer to the login page and the user will ave to log in again
	//March Intuch
	session_start();
	if(!isset($_SESSION['username']) || !isset($_SESSION['password']))
	{
		header('Location: login.html');	
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
			
		<link rel="icon" href="lboro_logo_large.ico" >
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Timetable | Loughborough University</title>
		<script type="text/javascript">
		
			//when the module code dropdown changed its index, change the module title index with it
			//Callan Swanson
			function module_code_change() {
				var index = document.getElementById("module_code_select").selectedIndex;
				document.getElementById("module_title_select").selectedIndex = index;
			}
			
			//when the module title dropdown changed its index, change the module code index with it
			//Calan Swanson
			function module_title_change() {
				var index = document.getElementById("module_title_select").selectedIndex;
				document.getElementById("module_code_select").selectedIndex = index;
			}
		
			//changing the end week according to the start week
			//March Intuch, Callan Swanson
			function start_week_change() {
				var startWeek = document.getElementById("start_week").value;
				$('#end_week').empty();
				for(var i=startWeek; i<=15; i++) {
					if(i == 12) { //if there is a week 12, then make this the default
						$('#end_week').append("<option selected>"+i+"</option>");
					} else {
						$('#end_week').append("<option>"+i+"</option>");
					}
				}
			}
		
		</script>
		<script src="js/jquery-1.11.1.min.js"></script>
			
	</head>

	<body>
		<?php
			//connects to the database using the username and passoword
			require_once "MDB2.php";
			$host = "co-project.lboro.ac.uk"; 	//host name
			$dbName = "team10";					//database name
			$dsn = "mysql://team10:abg83rew@$host/$dbName";	//login information
			$db =& MDB2::connect($dsn);	//connecting to the server and connecting to the database
			if(PEAR::isError($db)){ 	//if we couldnt connect then end the connection
				die($db->getMessage());
			}
			$db->setFetchMode(MDB2_FETCHMODE_ASSOC);
			//username is the uppercase dept code that was loggged in
			$username = strtoupper($_SESSION['username']);
		?>
		
		<table>
			<tr>
				<td>
					<?php echo "Department: ".$username; ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php 
						//will output the whole set of module codes frm the database, module codes will change when module titles change
						//Callan Swanson, March Intuch
						$moduleTable = "dept_".strtolower($username);
						echo "Module code: <select id='module_code_select' onchange='module_code_change()'>";  
						$sql = "SELECT module_code FROM MODULES WHERE dept_code='$username';";
						$res =& $db->query($sql); //gtting the result from the database
						if(PEAR::isError($res)){
							die($res->getMessage());
						}
						while($row = $res->fetchRow()){
							echo "<option>".$row["module_code"]."</option>";
							}//outputs all the options from the database return result
						echo "</select>";
					?>
				</td>
			</tr>
			<tr>
				<td>
					<?php 
						//displays the module titles, titles will change when module codes change
						//Callan Swanson, March Intuch
						echo "Module title: <select id='module_title_select' onchange='module_title_change()' >"; 
						//selects teh module title from the databse
						$sql = "SELECT module_title FROM MODULES WHERE dept_code='$username';";
						$res =& $db->query($sql); //gtting the result from the database
						if(PEAR::isError($res)){
							die($res->getMessage());
						}
						while($row = $res->fetchRow()){
							echo "<option>".$row["module_title"]."</option>";
						}//outputs all the options from the database return result
						echo "</select>";
					?>
				</td>
			</tr>
			<tr>
				<td>
					Day:
					<form>
						<!--radio buttons for the day of the week-->
						<input type="radio" name="day">Monday
						<input type="radio" name="day">Tuesday<br/>
						<input type="radio" name="day">Wednesday
						<input type="radio" name="day">Thursday<br/>
						<input type="radio" name="day">Friday
					</form>
				</td>
			</tr>
			<tr>
				<td id="start">
					Start week:
					<?php
						//loop to create the 1-15 weeks
						//March Intuch
						echo "<select id='start_week' onchange='start_week_change()'>";
						for($i=1;$i<=15;$i++){  //loops round to make a select to for all the weeks
							echo "<option>".$i."</option>";
						}
						echo "</select>";
					?>
				</td>
			</tr>
			<tr>
				<td id="end">
					End week:
					<?php
						//end week loops around to 1-15
						//March Intuch, Callan Swanson
						echo "<select id='end_week'>";
						for($i=1;$i<=15;$i++){
							if($i == 12) { //makes the default value 12
								echo "<option selected>".$i."</option>";
							} else {
								echo "<option>".$i."</option>";
							}
						}
						echo "</select>";
					?>
				</td>
			</tr>
			<tr>
				<td>
					Period:
					<?php
						//dropdown for the period, includes the time in 24hr format
						//Callan Swanson
						echo "<select>";
						for($i=1;$i<=9;$i++){
							$time = $i+8;
							echo "<option>".$i." - ".$time.":00</option>";
						}
						echo "</select>";
					  ?>
				</td>
			</tr>
		</table>
	</body>
</html>
