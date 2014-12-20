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
		  <link rel="stylesheet" href="http://code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">
          <link rel="stylesheet" href="css/style.css">
		<script src="js/jquery-1.11.1.min.js"></script>
		<script src="js/jquery-ui.js"></script>	
		<script type="text/javascript">
			  //call this function when the page load
			  $(function() {
				//implement multiple selecttion to selectable jquery-ui
				$("#week").bind("mousedown", function(e) {
 					e.metaKey = true;
					}).selectable();
 			  });
		
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

		</script>
		
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
			<form action="requestSubmit.php" method="post">
				<tr>
					<td>
						<?php echo "Department: ".$username; ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php 
							//will output the whole set of module codes from the database, module codes will change when module titles change
							//Callan Swanson, Inthuch Therdchanakul
							$moduleTable = "dept_".strtolower($username);
							echo "Module code: <select id='module_code_select' onchange='module_code_change()'>";  
							$sql = "SELECT module_code FROM MODULES WHERE dept_code='$username';";
							$res =& $db->query($sql); //getting the result from the database
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
							//Callan Swanson, Inthuch Therdchanakul
							echo "Module title: <select id='module_title_select' onchange='module_title_change()' >"; 
							//selects the module title from the databse
							$sql = "SELECT module_title FROM MODULES WHERE dept_code='$username';";
							$res =& $db->query($sql); //getting the result from the database
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
						<!--radio buttons for the day of the week-->
						<input type="radio" name="day" value="1">Monday
						<input type="radio" name="day" value="2">Tuesday<br/>
						<input type="radio" name="day" value="3">Wednesday
						<input type="radio" name="day" value="4">Thursday<br/>
						<input type="radio" name="day" value="5">Friday
					</td>
				</tr>
				<tr>
					<td>
						<!--selectable weeks with weeks 1-12 pre-selected as default-->
						Week:
						<ol id="week" name="week">
							<li class="ui-state-default ui-selected" value="1">1</li>
							<li class="ui-state-default ui-selected" value="1">2</li>
							<li class="ui-state-default ui-selected" value="1">3</li>
							<li class="ui-state-default ui-selected" value="1">4</li>
							<li class="ui-state-default ui-selected" value="1">5</li>
							<li class="ui-state-default ui-selected" value="1">6</li>
							<li class="ui-state-default ui-selected" value="1">7</li>
							<li class="ui-state-default ui-selected" value="1">8</li>
							<li class="ui-state-default ui-selected" value="1">9</li>
							<li class="ui-state-default ui-selected" value="1">10</li>
							<li class="ui-state-default ui-selected" value="1">11</li>
							<li class="ui-state-default ui-selected" value="1">12</li>
							<li class="ui-state-default" value="1">13</li>
							<li class="ui-state-default" value="1">14</li>
							<li class="ui-state-default" value="1">15</li>
						</ol>
					</td>
				</tr>
				<tr>
					<td>
						Period:
						<?php
							//dropdown for the period, includes the time in 24hr format
							//Callan Swanson
							echo "<select name='time'>";
							for($i=1;$i<=9;$i++){
								$time = $i+8;
								echo "<option value='".$i."'>".$i." - ".$time.":00</option>";
							}
							echo "</select>";
						  ?>
					</td>
				</tr>
				<tr>
					<td>
						Special requirements:
						</br>
						<textarea name="specialReq" maxlength="1000" placeholder="Extra requirements..."></textarea>
					</td>
				</tr>
				<tr>
					<td>
						Room code:
						<input name="roomCode" type="text" maxlength="15" >
					</td>
				</tr>
				<tr>
					<td>
						<input name="wheelchair" type="checkbox" value="wheelchair">wheelchair</br>
						<input name="projector" type="checkbox" value="projector" checked="true">projector</br>
						<input name="visualiser" type="checkbox" value="visualiser" checked="true">visualiser</br>
						<input name="whiteboard" type="checkbox" value="whiteboard" checked="true">whiteboard</br>
					</td>
				</tr>
				<tr>
					<td>
						<input type="submit" value="submit">
					</td>
				</tr>
			</form>
		</table>
		
		<table id="resultsTable">
			<?php
				
			?>
		</table>
		
	</body>
</html>
