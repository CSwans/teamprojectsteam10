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
		
		$sql = "SELECT module_code, module_title 
				FROM MODULES 
				WHERE dept_code='$username' 
				ORDER BY module_code;";
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
	<script src="http://code.jquery.com/jquery-1.10.2.js"></script>
	<script src="http://code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
	<script type="text/javascript">
		<?php
			//pass value array onto javascript array roomData
			echo "var roomData = ". $json . ";\n";
			echo "var moduleData = ". $moduleJson . ";\n";
		?>
		
		$(function() {
			partChange();
			insert_room_code();
		});
		function showModDialog(){
					$("#modDialog").dialog();
			}
			function addMod(){
				var code = document.getElementById("modCode").value;
				var title = document.getElementById("modTitle").value;
				for(var i=0;i<moduleData.length;i++){
					if(code == moduleData[i].module_code){
						alert("This module already exist");
						$("#modDialog").dialog("close");
						$("#module_code_select").val(moduleData[i].module_code);
						$("#module_title_select").val(moduleData[i].module_title);
						return "Module existed";
					}
				}
				
				if (confirm('Are you sure you want to add the following module: Module code - ' + code + ' Module title - ' + title)){
					$("#module_code_select").append("<option>" + code + "</option>");
					$("#module_title_select").append("<option>" + title + "</option>");
					$.ajax( {
						url : "insertMod.php",
						type : "POST", 
						data : $("#modForm").serialize(),
						success : function (data){
								data = JSON.parse(data);
								console.log("data "+data); //quick check
								alert(data);
							},
						error : function(jqXHR, textStatus, errorThrown) {
						}
					});
					
					$("#modDialog").dialog("close");
					$("#module_code_select").val(code);
					$("#module_title_select").val(title);
				}else{
				return false;
				}
				
				
			}

		
	</script>
		
		
		<link rel="stylesheet" href="Theme.css"/>
	
	</head>
	<body>
	<form id="lastYear" name="lastYear">
		<input type="hidden" id="form" name="form" value="1"/>
	</form>
	<form id="requestForm" name="requestForm" action="requestSubmit.php" method="post">
		<div class="input_boxes" >
        <div id="buttons">
			<div id="button_wrap1">
				<a><button id="add_mod" type="button" onClick="showModDialog()"> &gt; &nbsp;&nbsp;&nbsp;&nbsp;ENTER NEW MODULE</button></a>
				<a href="ViewRequests.php"><button id ="All" type="button" >&gt;&nbsp;&nbsp;&nbsp;&nbsp;VIEW ALL ENTRIES </button></a>
				<a href="RoomAvail.php"><button type="button">&gt;&nbsp;&nbsp;&nbsp;&nbsp;CHECK AVAILABILITY</button></a>
				<a><button id="Load_Last_Year" type="button" onClick="loadRequest()" > &gt; &nbsp;&nbsp;&nbsp;&nbsp;LOAD REQUESTS</button></a>
			</div>
        </div>
		<div id="everything">
		<div id="input_wrap">
			<div id="inputs">
			
				<table class="inputs box_class">
					<tr>
						<td>
							Priority: 
						</td>
						<td>
							<input name="priorityInput" type="radio" id="priorityInput" onchange="change_room_code()" value="1"/>Yes
							<input name="priorityInput" type="radio" id="priorityInput" onchange="change_room_code()" value="0"/>No
						</td>
					</tr>
					
				<tr> <td>&nbsp;  </td> </tr>
					
					<tr>
						<td style="min-width:300px" align="center"> Part: </td>
						<td style="min-width:200px" align="center"> Module Code: </td>
						<td align="center"> Module Title: </td>
						</tr>
						<tr>
						
						<td>
							<input type='radio' name='partCode' id='allPart' checked='checked' value='All' onchange='partChange()'> All 
							<input type='radio' name='partCode' id='aPart' value='A' onchange='partChange()' > A 
							<input type='radio' name='partCode' id='bPart' value='B' onchange='partChange()'> B 
							<input type='radio' name='partCode' id='iPart' value='I' onchange='partChange()'> I 
							<input type='radio' name='partCode' id='cPart' value='C' onchange='partChange()'> C 
							<input type='radio' name='partCode' id='dPart' value='D' onchange='partChange()'> D
						</td> 
						
						<td align="center">
							<?php
								//will output the whole set of module codes from the database, module codes will change when module titles change
								//Callan Swanson, Inthuch Therdchanakul
								//Scott Marshall: added order by to SQL and name to the <select>. 'module_code_select' is now part of the Form Data
								echo "<select id='module_code_select' name='module_code_select' onchange='module_code_change()'>";
								
								echo "</select>";
							?>
						</td>

						<td align="center">
							<?php
								//displays the module titles, titles will change when module codes change
								//Callan Swanson, Inthuch Therdchanakul
								echo "<select id='module_title_select' name='module_title_select' onchange='module_title_change()' >";
							
								echo "</select>";
							?>
						</td>
					</tr>
					<tr> <td>&nbsp;  </td> </tr>
					</table>
				
				<br><br>
					
									
					<table class="inputs box_class floating" >
						<tr>
							<td style="height: 28px;" width="300px" align="center"> Day: </td>
						</tr>
						<tr>
							<td style="height: 120px;">
							  <!--radio buttons for the day of the week--> 
							  <!--Scott Marshall: added ids for each element. Day is now part of the Form Data -->
							  
								<input onchange="change_room_code();" type="radio" name="day" id='monday' value="1" required checked />
								Monday
								<input onchange="change_room_code();" type="radio" name="day" id='tuesday' value="2" required/>
								Tuesday<br/>
								<input onchange="change_room_code();" type="radio" name="day" id='wednesday' value="3" required/>
								Wednesday
								<input onchange="change_room_code();" type="radio" name="day" id='thursday' value="4" required/>
								Thursday<br/>
								<input onchange="change_room_code();" type="radio" name="day" id='friday' value="5" required/>
								Friday 
							</td>
						</tr>
						
					</table>
					
					
					<table id="period_duration" class="inputs box_class floating">
						<tr> 
							<td width="100px" align="center"> Period: </td>
						</tr>
							<td align="center" valign="top">
								<?php
									//dropdown for the period, includes the time in 24hr format
									//Callan Swanson
									//Scott Marshall - trigger a re-evaluation of the duration when the period is changed
									echo "<select name='time' id='time' onchange='refill_duration();  change_room_code();'>";
									for($i=1;$i<=9;$i++){
										$time = $i+8;
										echo "<option value='".$i."'>".$i." - ".$time.":00</option>";
									}
									echo "</select>";
								?> </td>
						</tr>
						<tr> <td>&nbsp;  </td> </tr>
						<tr> 
							<td width="100px" align="center"> Duration: </td> 
							</tr>
							<tr>
							<td style="text-align:center;">
							<?php
								//dropdown for the duration
								//Scott Marshall
								echo "<select name='duration' id='duration' onchange='change_room_code();'>";
								for($i=1;$i<=9;$i++){
									$duration = $i+8;
									echo "<option value='".$i."'>".$i."</option>";
								}
								echo "</select>";
							?>
						</td>
						</tr>
						
						
					</table>
				
				<table  width="480px" height="180px;" class="inputs box_class floating" style="margin-left: 65px;" width='480px'>
				
					<tr>		
						<td align="center">
						  Number of rooms: </br>
							<select id="noRooms" name="noRooms" onChange="showCapacity(); change_room_code();" >
								<option>1</option>
								<option>2</option>
								<option>3</option>
								<option>4</option>
							</select>
						</td>
						
						<td id="capacityCell"> 
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Capacity: </br>
							<input name="capacity" type="text" id="capacity1" onChange="change_room_code()" value=""/> 
						</td>												
						
						</tr>
					</table>
					
					
					<br> <br> <br> <br> <br> <br> <br> <br> 
					
					<table class="inputs box_class">
						
						<!--Checkboxes, using binary to add an independednt value to each week, selectable weeks with weeks 1-12 pre-selected as default--> 
						  <!-- allowing a raneg of weeks to be chosen --> 

					<tr>
						
						<td align="center" style="min-width:800px"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Week(s): </td>
						<td align="center" style="min-width:270px">&nbsp;  </td>
					</tr>
                    <tr> <td>&nbsp;  </td> </tr>
					<tr>
                    
						<td colspan="2">
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week1" value="1" checked="checked" class="vis-hidden new-post-tags"/></input>
							<label style="margin-left: 100px;" id="week" for="week1" class="week_label">  1  </label>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week2" value="2" checked="checked" class="vis-hidden new-post-tags"/></input>
							<label  id="week" for="week2" class="week_label">  2  </label>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week3" value="3" checked="checked" class="vis-hidden new-post-tags"/></input>
							<label id="week" for="week3" class="week_label">  3  </label>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week4" value="4" checked="checked" class="vis-hidden new-post-tags"/></input>
							<label id="week" for="week4" class="week_label">  4  </label>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week5" value="5" checked="checked" class="vis-hidden new-post-tags"/></input>
							<label id="week" for="week5" class="week_label">  5  </label>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week6" value="6" checked="checked" class="vis-hidden new-post-tags"/></input>
							<label id="week" for="week6" class="week_label">  6  </label>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week7" value="7" checked="checked" class="vis-hidden new-post-tags"/></input>
							<label id="week" for="week7" class="week_label">  7  </label>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week8" value="8" checked="checked" class="vis-hidden new-post-tags"/></input>
							<label id="week" for="week8" class="week_label">  8  </label>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week9" value="9" checked="checked" class="vis-hidden new-post-tags"/></input>
							<label id="week" for="week9" class="week_label">  9  </label>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week10" value="10" checked="checked" class="vis-hidden new-post-tags"/></input>
							<label id="week" for="week10" class="week_label"> 10 </label>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week11" value="11" checked="checked" class="vis-hidden new-post-tags"/></input>
							<label id="week" for="week11" class="week_label"> 11 </label>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week12" value="12" checked="checked" class="vis-hidden new-post-tags"/></input>
							<label id="week" for="week12" class="week_label"> 12 </label>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week13" value="13" class="vis-hidden new-post-tags"/></input>
							<label id="week" for="week13" class="week_label"> 13 </label>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week14" value="14" class="vis-hidden new-post-tags"/></input>
							<label id="week" for="week14" class="week_label"> 14 </label>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week15" value="15" class="vis-hidden new-post-tags"/></input>
							<label id="week" for="week15" class="week_label"> 15 </label>
							<input onchange="change_room_code();" type="checkbox" name="weeks[]" id="week16" value="16" class="vis-hidden new-post-tags"/></input>
							<label id="week" for="week16" class="week_label"> 16 </label>
						</td>


					</tr>
					<tr> <td>&nbsp;  </td> </tr>
					</table>
					
				<br><br>
				
				<table   style='margin-bottom:50px;' class="inputs box_class">
					<tr> <td>&nbsp;  </td> </tr>
					
					<tr>
						<td style="min-width:230px;"> Special requirements: &nbsp;&nbsp;&nbsp;&nbsp; </td>
						<td>
							<textarea style="width:835px;" name="specialReq" cols="80" maxlength="1000" placeholder="1000 chars max..."></textarea>
						</td>
					</tr>
					<tr> <td>&nbsp;  </td> </tr>
					</table>
					
				</div>
                				<div id="subdiv">
							<input type="hidden" name="priorityInput" value="1" >
                            <input id="submit" type="button" onClick="ajaxFunction()" value="Send Request!" >

				</div>
				<!--inputs-->
				
				<div id="div_holding_everything" style="clear:both;"> 
				
								  <table id="parkTable" class="box_class">
					<tr>
					  <td> Park:
					  </td>
					  <td>
						<select id="park" name="park" onChange="change_room_code()">
						  <option>Any</option>
						  <option>C</option>
						  <option>E</option>
						  <option>W</option>
						</select></td>
					</tr>
				</table>
				
				
				
				<div id="advance" class="advance">

				<div id="prefOptions">
					<table id="advancedinputs1" class="box_class">
					<tr>
					  <td id="room_col"><!--Scott Marshall: added in empty select so it is part of the form data --> 
						Room Pref:
					  </td>
					  <td>
						  <select name='roomCode0' id='room_list' onchange='refill_codes();'>
						</select> 
					  </td>  
					  <td>
						<button type='button' onClick="ext_toggle(1);" id='expand'>Expand ↓</button>
					  </td>
					</tr>
					<tr id="ad_pref1" style="display:none;">
					  <td>
						<span id="adv_block">  
					    	Wheelchair <br/>
							<input name="wheelchair" type="radio" id="wheelchair_yes" onChange="change_room_code()" value="1"/>
							Yes
							<input name="wheelchair" type="radio" id="wheelchair_no" onChange="change_room_code()" value="0" checked="checked"/>
							No
						</span>
						<span id="adv_block">
							Projector <br/>
							<input name="projector" type="radio" id="projector_yes" onChange="change_room_code()" value="1" checked="checked"/>
							Yes
							<input name="projector" type="radio" id="projector_no" onChange="change_room_code()" value="0"/>
							No
						</span>
					  </td>
					<td>
					<span id="adv_block">
						Visualiser <br/>
						<input name="visualiser" type="radio" id="visualiser_yes" onChange="change_room_code()" value="1" checked="checked"/>
						Yes
						<input name="visualiser" type="radio" id="visualiser_no" onChange="change_room_code()" value="0"/>
						No
					</span>
					<span id="adv_block">
						Whiteboard <br/>
						<input name="whiteboard" type="radio" id="whiteboard_yes" onChange="change_room_code()" value="1" checked="checked"/>
						Yes
						<input name="whiteboard" type="radio" id="whiteboard_no" onChange="change_room_code()" value="0"/>
						No
					</span>
						</td>
					</tr>
					</table>
					</div>
					<div id="prefOptions">
					<table id="advancedinputs2" class="box_class">
					<tr id="add_room_col">
						<td id="roomlabel2" style="display: none;">
							Room Pref 2:
						</td>
					  <td><span id='room_list2' style="display: none;">
						  <select name='roomCode1' onchange='refill_codes();'>
						</select> 
						</span>
					  </td>
					  <td>
						<button type='button' onClick="ext_toggle(2);" id='expand2'>Expand ↓</button>
					  </td>
					</tr>
					<tr id="ad_pref2" style="display: none;">
					  <td>
					<span id="adv_block">  
					    Wheelchair <br/>
						<input name="wheelchair2" type="radio" id="wheelchair_yes2" onChange="change_room_code()" value="1"/>
						Yes
						<input name="wheelchair2" type="radio" id="wheelchair_no2" onChange="change_room_code()" value="0" checked="checked"/>
						No
					</span>
					<span id="adv_block">
						Projector <br/>
						<input name="projector2" type="radio" id="projector_yes2" onChange="change_room_code()" value="1" checked="checked"/>
						Yes
						<input name="projector2" type="radio" id="projector_no2" onChange="change_room_code()" value="0"/>
						No
					</span> 
					</td>
					<td>
					<span id="adv_block">
						Visualiser <br/>
						<input name="visualiser2" type="radio" id="visualiser_yes2" onChange="change_room_code()" value="1" checked="checked"/>
						Yes
						<input name="visualiser2" type="radio" id="visualiser_no2" onChange="change_room_code()" value="0"/>
						No
					</span>
					<span id="adv_block">
						Whiteboard <br/>
						<input name="whiteboard2" type="radio" id="whiteboard_yes2" onChange="change_room_code()" value="1" checked="checked"/>
						Yes
						<input name="whiteboard2" type="radio" id="whiteboard_no2" onChange="change_room_code()" value="0"/>
						No
					</span>
						</td>
					</tr>
					</table>
					</div>
					</div>
					<div id="advance2" class="advance">
					<div id="prefOptions">
					<table id="advancedinputs3" class="box_class">
					<tr>
					<td id="roomlabel3" style="display: none;">
							Room Pref 3:
						</td>
					  <td><span id='room_list3' style="display: none;">
						  <select name='roomCode2' onchange='refill_codes();'>
					  
						</select>
						</span>
					  </td>
					  <td>
						<button type='button' onClick="ext_toggle(3);" id='expand3'>Expand ↓</button>
					  </td>
					</tr>
					<tr id="ad_pref3" style="display: none;">
					  <td>
					<span id="adv_block">  
					    Wheelchair <br/>
						<input name="wheelchair3" type="radio" id="wheelchair_yes3" onChange="change_room_code()" value="1"/>
						Yes
						<input name="wheelchair3" type="radio" id="wheelchair_no3" onChange="change_room_code()" value="0" checked="checked"/>
						No
					</span>
					<span id="adv_block">
						Projector <br/>
						<input name="projector3" type="radio" id="projector_yes3" onChange="change_room_code()" value="1" checked="checked"/>
						Yes
						<input name="projector3" type="radio" id="projector_no3" onChange="change_room_code()" value="0"/>
						No
					</span> 
					</td>
					<td>					
					<span id="adv_block">
						Visualiser <br/>
						<input name="visualiser3" type="radio" id="visualiser_yes3" onChange="change_room_code()" value="1" checked="checked"/>
						Yes
						<input name="visualiser3" type="radio" id="visualiser_no3" onChange="change_room_code()" value="0"/>
						No
					</span>
					<span id="adv_block">
						Whiteboard <br/>
						<input name="whiteboard3" type="radio" id="whiteboard_yes3" onChange="change_room_code()" value="1" checked="checked"/>
						Yes
						<input name="whiteboard3" type="radio" id="whiteboard_no3" onChange="change_room_code()" value="0"/>
						No
					</span>
						</td>
					</tr>
					
					</table>
					</div>
					<div id="prefOptions">
					<table id="advancedinputs4" class="box_class">
					<tr>
						<td id="roomlabel4" style="display: none;">
							Room Pref 4:
						</td>
					  <td><span  id='room_list4' style="display: none;">
						  <select name='roomCode3' onchange='refill_codes();'>
						</select>
						</span>
					  </td>
					  <td>
						<button type='button' onClick="ext_toggle(4);" id='expand4'>Expand ↓</button>
					  </td>
					</tr>
					<tr id="ad_pref4" style="display: none;">
					  <td>
					<span id="adv_block">  
					    Wheelchair <br/>
						<input name="wheelchair4" type="radio" id="wheelchair_yes4" onChange="change_room_code()" value="1"/>
						Yes
						<input name="wheelchair4" type="radio" id="wheelchair_no4" onChange="change_room_code()" value="0" checked="checked"/>
						No
					</span>
					<span id="adv_block">
						Projector <br/>
						<input name="projector4" type="radio" id="projector_yes4" onChange="change_room_code()" value="1" checked="checked"/>
						Yes
						<input name="projector4" type="radio" id="projector_no4" onChange="change_room_code()" value="0"/>
						No
					</span>
					</td>
					<td>
					<span id="adv_block">
						Visualiser <br/>
						<input name="visualiser4" type="radio" id="visualiser_yes4" onChange="change_room_code()" value="1" checked="checked"/>
						Yes
						<input name="visualiser4" type="radio" id="visualiser_no4" onChange="change_room_code()" value="0"/>
						No
					</span>
					<span id="adv_block">
						Whiteboard <br/>
						<input name="whiteboard4" type="radio" id="whiteboard_yes4" onChange="change_room_code()" value="1" checked="checked"/>
						Yes
						<input name="whiteboard4" type="radio" id="whiteboard_no4" onChange="change_room_code()" value="0"/>
						No
					</span>
						</td>
					</tr>
				  </table>
				  </div>	
				</div>
				<!--advance-->

				<!--subdiv--> 
		</div>
      <!--input wrap--> 
	</div>
</form>
</div>
<div id="modDialog" title="Enter module data" style="display:none;">
			<form id="modForm" name="modForm">
				Module code: <input type="text" id="modCode" name="modCode">
				Module title: <input type="text" id="modTitle" name="modTitle">
				<input type="button" onclick="addMod()" value="Submit">
			</form>
</div>
	</body>
	</html>
