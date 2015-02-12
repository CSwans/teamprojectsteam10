<!doctype html>
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
			
			
			$sql = "SELECT REQUEST.request_id, module_code, REQUEST.room_code, capacity, wheelchair, projector, visualiser, whiteboard, special_requirements, priority, period, day, duration,GROUP_CONCAT(CONVERT(REQUEST_WEEKS.week, CHAR(8)) SEPARATOR ', ') AS week FROM REQUEST,REQUEST_WEEKS WHERE REQUEST.request_id = REQUEST_WEEKS.request_id AND dept_code = '".$username."'GROUP BY request_id";
			$res =& $db->query($sql); //getting the result from the database
			if(PEAR::isError($res)){
				die($res->getMessage());
			}
			$value = array();
			$fullList=array();
			
			//put each rows into value array
			while($row = $res->fetchRow()){
				$value[] = $row;
				$fullList[] = $row;
			}
			
			$jsonRequests = json_encode($value);
			$jsonFullData = json_encode($fullList);
			
			$sql = "SELECT REQUEST.request_id, module_code, BOOKING.room_code, capacity, wheelchair, projector, visualiser, whiteboard, special_requirements, priority, period, day, duration,GROUP_CONCAT(CONVERT(REQUEST_WEEKS.week, CHAR(8)) SEPARATOR ',') AS week, CASE WHEN REQUEST.room_code = BOOKING.room_code THEN 0 ELSE 1 END AS partial FROM REQUEST,REQUEST_WEEKS, BOOKING WHERE REQUEST.request_id = REQUEST_WEEKS.request_id AND dept_code = '".$username."' AND BOOKING.request_id = REQUEST.request_id GROUP BY request_id";
			$res =& $db->query($sql); //getting the result from the database
			if(PEAR::isError($res)){
				die($res->getMessage());
			}
			$value2 = array();
			
			//put each rows into value array
			while($row = $res->fetchRow()){
				$value2[] = $row; //booked
			}
			
			$jsonBookings = json_encode($value2);
			
			
			$sql = "SELECT REQUEST.request_id, module_code, REQUEST.room_code, capacity, wheelchair, projector, visualiser, whiteboard, special_requirements, priority, period, day, duration,GROUP_CONCAT(CONVERT(REQUEST_WEEKS.week, CHAR(8)) SEPARATOR ',') AS week  FROM REQUEST,REQUEST_WEEKS, REJECTION WHERE REQUEST.request_id = REQUEST_WEEKS.request_id AND dept_code = '".$username."' AND REJECTION.request_id = REQUEST.request_id GROUP BY request_id";
			$res =& $db->query($sql); //getting the result from the database
			if(PEAR::isError($res)){
				die($res->getMessage());
			}
			$value3 = array(); //rejected
			
			//put each rows into value array
			while($row = $res->fetchRow()){
				$value3[] = $row;
			}
			
			$jsonRejections = json_encode($value3);
			
			$sql = "SELECT module_code, module_title FROM MODULES WHERE dept_code='$username' ORDER BY module_code;";
			$res =& $db->query($sql); 
			if(PEAR::isError($res)){
				die($res->getMessage());
			}
			$moduleInfo = array();
			while($row = $res->fetchRow()){
				$moduleInfo[] = $row;
			}
			$moduleJson = json_encode($moduleInfo);
			
			$sql = "SELECT * FROM ROOMS;";
		$res =& $db->query($sql); //getting the result from the database
		if(PEAR::isError($res)){
			die($res->getMessage());
		}
		$value4 = array();
		//put each rows into value array
		while($row = $res->fetchRow()){
			$value4[] = $row;
		}
	
		$roomsJson = json_encode($value4);
			
			
		?>
		<script src="//code.jquery.com/jquery-1.10.2.js"></script>
		<script src="js/jquery-1.11.1.min.js"></script>
		<script src="js/jquery-ui.js"></script>
		<script src="js/jquery.serializejson.min.js"></script>
		<script type="text/javascript">
		
		
			<?php
				echo "var requestData = ".$jsonRequests.";\n"; //WILL CHANGE TO HOLD THE PENDING DATA WHEN PAGE LOADS
				echo "var bookingData = ".$jsonBookings.";\n";
				echo "var rejectedData = ".$jsonRejections.";\n";
				echo "var fullData = ".$jsonFullData.";\n";
				echo "var moduleData = ".$moduleJson.";\n";
				echo "var roomData = ".$roomsJson.";\n";
			?>
			
			$(function() {
				findPendings();
			});
			
			function moduleList() {
				for(var x=0;x<moduleData.length;x++){
					$('#moduleCodeList').append("<option>"+moduleData[x].module_code+"</option>");
				}
				for(var x=0;x<moduleData.length;x++){
					$('#moduleTitleList').append("<option>"+moduleData[x].module_title+"</option>");
				}
				for(var x=0;x<moduleData.length;x++){
					$('#roomList').append("<option>"+roomData[x].room_code+"</option>");
				}
			}
			
			function module_code_change() {
				var index = document.getElementById("moduleCodeList").selectedIndex;
				document.getElementById("moduleTitleList").selectedIndex = index;
			}

			function module_title_change() {
				var index = document.getElementById("moduleTitleList").selectedIndex;
				document.getElementById("moduleCodeList").selectedIndex = index;
			}
			
			/*	function partChange() {
		//looks through all of the moduleData
		$("#moduleCodeList").empty();
		$("#moduleTitleList").empty();
		console.log(moduleData[0].module_code.substr(4,1));
		console.log(document.getElementsByName("partCode").value);
		
		//finding out which part is checked one by one
		var checkedVal;
		if(document.getElementById("allPart").checked) {
			checkedVal = document.getElementById("allPart").value;
		}else if(document.getElementById("aPart").checked) {
			checkedVal = document.getElementById("aPart").value;
		}else if(document.getElementById("bPart").checked) {
			checkedVal = document.getElementById("bPart").value;
		}else if(document.getElementById("iPart").checked) {
			checkedVal = document.getElementById("iPart").value;
		}else if(document.getElementById("cPart").checked) {
			checkedVal = document.getElementById("cPart").value;
		}else if(document.getElementById("dPart").checked) {
			checkedVal = document.getElementById("dPart").value;
		}
		
		console.log(checkedVal);
		
		for(var i=0; i<moduleData.length; i++) {
			if(moduleData[i].module_code.substr(4,1) == checkedVal) {
				$("#moduleCodeList").append("<option>"+moduleData[i].module_code+"</option>");
				$("#moduleTitleList").append("<option>"+moduleData[i].module_title+"</option>");
			} else if(checkedVal == "All") {
				$("#moduleCodeList").append("<option>"+moduleData[i].module_code+"</option>");
				$("#moduleTitleList").append("<option>"+moduleData[i].module_title+"</option>");
			}
		}
	}
	*/
	
			function findPendings() {
				for(var i=0; i<requestData.length; i++) {
					for(var j=0; j<bookingData.length; j++) {
						if(requestData[i].request_id == bookingData[j].request_id) {
							console.log(requestData[i]);
							requestData.splice(i,1);
						}
					}
				}
				console.log(requestData);
				for(var i=0; i<requestData.length; i++) {
					for(var j=0; j<rejectedData.length; j++) {
						if(requestData[i].request_id == rejectedData[j].request_id) {
							console.log(requestData[i]);
							requestData.splice(i,1);
						}
					}
				}
				console.log("REJECTED");
				console.log(rejectedData);
				console.log('REQUEST');
				console.log(requestData);
			}
			
				
			
			function initialise() {
				document.getElementById('current_week').value = 1;
			}
			
			function statusChange() {
				var stat = document.getElementById('statusList').selectedIndex;
				
				if(stat==0) {rejected_grid_view();}
				if(stat==1) {booked_grid_view();}
				if(stat==2) {pending_grid_view();}
			}
			
			function booked_grid_view(){
			
				var sort = document.getElementById('sortList').selectedIndex;
				var part;
				var module;
				var room;
				
				if(sort==0) {
					var radios = document.getElementsByName('partCode');
					for(var x=0;x<radios.length;x++){
						if (radios[x].checked) {
							part=document.getElementsByName('partCode')[x].value;
						}
					}
				}
				
				if(sort==1) {
					module=document.getElementById('moduleCodeList').value;
				}
				
				if(sort==2) {
					room=document.getElementById('roomList').value;
				}
				
					var myNode = document.getElementById("Monday");
				myNode.innerHTML = '<td>Monday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
				var myNode = document.getElementById("Tuesday");
				myNode.innerHTML = '<td>Tuesday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
				var myNode = document.getElementById("Wednesday");
				myNode.innerHTML = '<td>Wednesday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
				var myNode = document.getElementById("Thursday");
				myNode.innerHTML = '<td>Thursday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
				var myNode = document.getElementById("Friday");
				myNode.innerHTML = '<td>Friday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
			
					var n = parseInt(document.getElementById('current_week').value);
				
					for(var x = 0;x<bookingData.length;x++) {
						var weeks = bookingData[x].week;
						var day = bookingData[x].day;
						var period = parseInt(bookingData[x].period);
						var duration = parseInt(bookingData[x].duration);
						var weekArr = weeks.split(",");
						
						for(var i=0;i<weekArr.length;i++){
							weekArr[i]=parseInt(weekArr[i]);
							if(weekArr[i]==0){
								weekArr=[];
								weekArr.push(1,2,3,4,5,6,7,8,9,10,11,12);
							}
						}
					
						if(weekArr.indexOf(n)> -1){
							if(duration>1){
								for(var n =0;n<duration;n++){
									if(part != null && bookingData[x].module_code.charAt(4)==part){
										document.getElementById(day).children['p'+(period+n)].innerHTML=document.getElementById(day).children['p'+(period+n)].innerHTML+'<p id="'+ bookingData[x].request_id +'" onclick="showInfo(this.id);"> Request ID: '+bookingData[x].request_id+'<br/> Module code: '+bookingData[x].module_code+'<br/> Room code: '+bookingData[x].room_code+'</p>';
									}
									if(module != null && bookingData[x].module_code==module){
										document.getElementById(day).children['p'+(period+n)].innerHTML=document.getElementById(day).children['p'+(period+n)].innerHTML+'<p id="'+ bookingData[x].request_id +'" onclick="showInfo(this.id);"> Request ID: '+bookingData[x].request_id+'<br/> Module code: '+bookingData[x].module_code+'<br/> Room code: '+bookingData[x].room_code+'</p>';
									}						
									if(room != null && bookingData[x].room_code==room){
										document.getElementById(day).children['p'+(period+n)].innerHTML=document.getElementById(day).children['p'+(period+n)].innerHTML+'<p id="'+ bookingData[x].request_id +'" onclick="showInfo(this.id);"> Request ID: '+bookingData[x].request_id+'<br/> Module code: '+bookingData[x].module_code+'<br/> Room code: '+bookingData[x].room_code+'</p>';
									}
								}
							}
							else {
								if(part != null && bookingData[x].module_code.charAt(4)==part) {
									document.getElementById(day).children['p'+period].innerHTML=document.getElementById(day).children['p'+period].innerHTML+'<p id="'+ bookingData[x].request_id +'" onclick="showInfo(this.id);"> Request ID: '+bookingData[x].request_id+'<br/> Module code: '+bookingData[x].module_code+'<br/> Room code: '+bookingData[x].room_code+'</p>';
								}
								if(module != null && bookingData[x].module_code==module){
										document.getElementById(day).children['p'+period].innerHTML=document.getElementById(day).children['p'+period].innerHTML+'<p id="'+ bookingData[x].request_id +'" onclick="showInfo(this.id);"> Request ID: '+bookingData[x].request_id+'<br/> Module code: '+bookingData[x].module_code+'<br/> Room code: '+bookingData[x].room_code+'</p>';
									}
								if(room != null && bookingData[x].room_code==room){
										document.getElementById(day).children['p'+period].innerHTML=document.getElementById(day).children['p'+period].innerHTML+'<p id="'+ bookingData[x].request_id +'" onclick="showInfo(this.id);"> Request ID: '+bookingData[x].request_id+'<br/> Module code: '+bookingData[x].module_code+'<br/> Room code: '+bookingData[x].room_code+'</p>';
									}
							}
						}
					}
			}
			
			function pending_grid_view(){
			
				var sort = document.getElementById('sortList').selectedIndex;
				var part;
				var module;
				var room;
				
				if(sort==0) {
					var radios = document.getElementsByName('partCode');
					for(var x=0;x<radios.length;x++){
						if (radios[x].checked) {
							part=document.getElementsByName('partCode')[x].value;
						}
					}
				}
				
				if(sort==1) {
					module=document.getElementById('moduleCodeList').value;
				}
				
				if(sort==2) {
					room=document.getElementById('roomList').value;
				}
				
					var myNode = document.getElementById("Monday");
				myNode.innerHTML = '<td>Monday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
				var myNode = document.getElementById("Tuesday");
				myNode.innerHTML = '<td>Tuesday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
				var myNode = document.getElementById("Wednesday");
				myNode.innerHTML = '<td>Wednesday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
				var myNode = document.getElementById("Thursday");
				myNode.innerHTML = '<td>Thursday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
				var myNode = document.getElementById("Friday");
				myNode.innerHTML = '<td>Friday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
			
					var n = parseInt(document.getElementById('current_week').value);
				
					for(var x = 0;x<requestData.length;x++) {
						var weeks = requestData[x].week;
						var day = requestData[x].day;
						var period = parseInt(requestData[x].period);
						var duration = parseInt(requestData[x].duration);
						var weekArr = weeks.split(",");
						
						for(var i=0;i<weekArr.length;i++){
							weekArr[i]=parseInt(weekArr[i]);
							if(weekArr[i]==0){
								weekArr=[];
								weekArr.push(1,2,3,4,5,6,7,8,9,10,11,12);
							}
						}
					
						if(weekArr.indexOf(n)> -1){
							if(duration>1){
								for(var n =0;n<duration;n++){
									if(part != null && requestData[x].module_code.charAt(4)==part){
										document.getElementById(day).children['p'+(period+n)].innerHTML=document.getElementById(day).children['p'+(period+n)].innerHTML+'<p id="'+ requestData[x].request_id +'" onclick="showInfo(this.id);"> Request ID: '+requestData[x].request_id+'<br/> Module code: '+requestData[x].module_code+'<br/> Room code: '+requestData[x].room_code+'</p>';
									}
									if(module != null && requestData[x].module_code==module){
										document.getElementById(day).children['p'+(period+n)].innerHTML=document.getElementById(day).children['p'+(period+n)].innerHTML+'<p id="'+ requestData[x].request_id +'" onclick="showInfo(this.id);"> Request ID: '+requestData[x].request_id+'<br/> Module code: '+requestData[x].module_code+'<br/> Room code: '+requestData[x].room_code+'</p>';
									}						
									if(room != null && requestData[x].room_code==room){
										document.getElementById(day).children['p'+(period+n)].innerHTML=document.getElementById(day).children['p'+(period+n)].innerHTML+'<p id="'+ requestData[x].request_id +'" onclick="showInfo(this.id);"> Request ID: '+requestData[x].request_id+'<br/> Module code: '+requestData[x].module_code+'<br/> Room code: '+requestData[x].room_code+'</p>';
									}
								}
							}
							else {
								if(part != null && requestData[x].module_code.charAt(4)==part) {
									document.getElementById(day).children['p'+period].innerHTML=document.getElementById(day).children['p'+period].innerHTML+'<p id="'+ requestData[x].request_id +'" onclick="showInfo(this.id);"> Request ID: '+requestData[x].request_id+'<br/> Module code: '+requestData[x].module_code+'<br/> Room code: '+requestData[x].room_code+'</p>';
								}
								if(module != null && requestData[x].module_code==module){
										document.getElementById(day).children['p'+period].innerHTML=document.getElementById(day).children['p'+period].innerHTML+'<p id="'+ requestData[x].request_id +'" onclick="showInfo(this.id);"> Request ID: '+requestData[x].request_id+'<br/> Module code: '+requestData[x].module_code+'<br/> Room code: '+requestData[x].room_code+'</p>';
									}
								if(room != null && requestData[x].room_code==room){
										document.getElementById(day).children['p'+period].innerHTML=document.getElementById(day).children['p'+period].innerHTML+'<p id="'+ requestData[x].request_id +'" onclick="showInfo(this.id);"> Request ID: '+requestData[x].request_id+'<br/> Module code: '+requestData[x].module_code+'<br/> Room code: '+requestData[x].room_code+'</p>';
									}
							}
						}
					}
			}
			
			
			
			function rejected_grid_view(){
			
				var sort = document.getElementById('sortList').selectedIndex;
				var part;
				var module;
				var room;
				
				if(sort==0) {
					var radios = document.getElementsByName('partCode');
					for(var x=0;x<radios.length;x++){
						if (radios[x].checked) {
							part=document.getElementsByName('partCode')[x].value;
						}
					}
				}
				
				if(sort==1) {
					module=document.getElementById('moduleCodeList').value;
				}
				
				if(sort==2) {
					room=document.getElementById('roomList').value;
				}
				
					var myNode = document.getElementById("Monday");
				myNode.innerHTML = '<td>Monday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
				var myNode = document.getElementById("Tuesday");
				myNode.innerHTML = '<td>Tuesday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
				var myNode = document.getElementById("Wednesday");
				myNode.innerHTML = '<td>Wednesday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
				var myNode = document.getElementById("Thursday");
				myNode.innerHTML = '<td>Thursday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
				var myNode = document.getElementById("Friday");
				myNode.innerHTML = '<td>Friday</td> <td id="p1"> </td> <td id="p2"> </td> <td id="p3"></td> <td id="p4"></td> <td id="p5"></td> <td id="p6"></td> <td id="p7"></td> <td id="p8"></td> <td id="p9"></td> <td id="p1"></td>';
			
					var n = parseInt(document.getElementById('current_week').value);
				
					for(var x = 0;x<rejectedData.length;x++) {
						var weeks = rejectedData[x].week;
						var day = rejectedData[x].day;
						var period = parseInt(rejectedData[x].period);
						var duration = parseInt(rejectedData[x].duration);
						var weekArr = weeks.split(",");
						
						for(var i=0;i<weekArr.length;i++){
							weekArr[i]=parseInt(weekArr[i]);
							if(weekArr[i]==0){
								weekArr=[];
								weekArr.push(1,2,3,4,5,6,7,8,9,10,11,12);
							}
						}
					
						if(weekArr.indexOf(n)> -1){
							if(duration>1){
								for(var n =0;n<duration;n++){
									if(part != null && rejectedData[x].module_code.charAt(4)==part){
										document.getElementById(day).children['p'+(period+n)].innerHTML=document.getElementById(day).children['p'+(period+n)].innerHTML+'<p id="'+ rejectedData[x].request_id +'" onclick="showInfo(this.id);"> Request ID: '+rejectedData[x].request_id+'<br/> Module code: '+rejectedData[x].module_code+'<br/> Room code: '+rejectedData[x].room_code+'</p>';
									}
									if(module != null && rejectedData[x].module_code==module){
										document.getElementById(day).children['p'+(period+n)].innerHTML=document.getElementById(day).children['p'+(period+n)].innerHTML+'<p id="'+ rejectedData[x].request_id +'" onclick="showInfo(this.id);"> Request ID: '+rejectedData[x].request_id+'<br/> Module code: '+rejectedData[x].module_code+'<br/> Room code: '+rejectedData[x].room_code+'</p>';
									}						
									if(room != null && rejectedData[x].room_code==room){
										document.getElementById(day).children['p'+(period+n)].innerHTML=document.getElementById(day).children['p'+(period+n)].innerHTML+'<p id="'+ rejectedData[x].request_id +'" onclick="showInfo(this.id);"> Request ID: '+rejectedData[x].request_id+'<br/> Module code: '+rejectedData[x].module_code+'<br/> Room code: '+rejectedData[x].room_code+'</p>';
									}
								}
							}
							else {
								if(part != null && rejectedData[x].module_code.charAt(4)==part) {
									document.getElementById(day).children['p'+period].innerHTML=document.getElementById(day).children['p'+period].innerHTML+'<p id="'+ rejectedData[x].request_id +'" onclick="showInfo(this.id);"> Request ID: '+rejectedData[x].request_id+'<br/> Module code: '+rejectedData[x].module_code+'<br/> Room code: '+rejectedData[x].room_code+'</p>';
								}
								if(module != null && rejectedData[x].module_code==module){
										document.getElementById(day).children['p'+period].innerHTML=document.getElementById(day).children['p'+period].innerHTML+'<p id="'+ rejectedData[x].request_id +'" onclick="showInfo(this.id);"> Request ID: '+rejectedData[x].request_id+'<br/> Module code: '+rejectedData[x].module_code+'<br/> Room code: '+rejectedData[x].room_code+'</p>';
									}
								if(room != null && rejectedData[x].room_code==room){
										document.getElementById(day).children['p'+period].innerHTML=document.getElementById(day).children['p'+period].innerHTML+'<p id="'+ rejectedData[x].request_id +'" onclick="showInfo(this.id);"> Request ID: '+rejectedData[x].request_id+'<br/> Module code: '+rejectedData[x].module_code+'<br/> Room code: '+rejectedData[x].room_code+'</p>';
									}
							}
						}
					}
			}
			
			
			
			
			function increaseWeek () {
				var currentWeek=parseInt(document.getElementById('current_week').value);
				if(currentWeek != 16){
					document.getElementById('current_week').value = (currentWeek+1);
				}
				
			}
			
			function decreaseWeek () {
				var currentWeek=parseInt(document.getElementById('current_week').value);
				if(currentWeek != 1){
					document.getElementById('current_week').value = (currentWeek-1);
				}
			}
			
			function showInfo(id) {
				$( "#info" ).empty();
				alert("<h2>Request No: " + id + "</h2>");
			}
			
			function changeSort() {
				var n = document.getElementById('sortList').selectedIndex;
				if(n==0) {
				document.getElementById('partDiv').style.display="block";
				document.getElementById('moduleDiv').style.display="none";
				document.getElementById('roomDiv').style.display="none";
				}
				if(n==1) {
				document.getElementById('partDiv').style.display="none";
				document.getElementById('moduleDiv').style.display="block";
				document.getElementById('roomDiv').style.display="none";
				}
				if(n==2) {
				document.getElementById('partDiv').style.display="none";
				document.getElementById('moduleDiv').style.display="none";
				document.getElementById('roomDiv').style.display="block";
				}
			}
			
		</script>	
<link rel="stylesheet" href="Theme.css"/>
</head>
<body onload="initialise(); statusChange(); moduleList();">

<div id="page_wrap">
	<table width="80%" align "center" id="testTable">
	
	View: <select id="statusList" onChange="statusChange()">
                	<option>Rejected</option>
                    <option>Booked</option>
                    <option>Pending</option>
                </select>
				
	Sort By:<select id="sortList" onchange="changeSort();statusChange();">
				<option>Part</option>
				<option>Module</option>
				<option>Room</option>
			</select>
	
	<div id="partDiv" style="display:block">
	Part:
	<input type='radio' name='partCode' id='aPart' value='A' onchange='statusChange();' checked> A
	<input type='radio' name='partCode' id='bPart' value='B' onchange='statusChange();'> B
	<input type='radio' name='partCode' id='iPart' value='I' onchange='statusChange();'> I
	<input type='radio' name='partCode' id='cPart' value='C' onchange='statusChange();'> C
	<input type='radio' name='partCode' id='dPart' value='D' onchange='statusChange();'> D
	</div>
	
	<div id="moduleDiv" style="display:none">
	Module Code:<select id="moduleCodeList" onchange="module_code_change(); statusChange();">
	</select>			
	
	Module Title:<select id="moduleTitleList"onchange="module_title_change();statusChange();">
	</select>
	</div>
	
	<div id="roomDiv" style="display:none">
	Room:<select id="roomList" onchange="statusChange();">
	</select>
	</div>			
				
	<br/>
	
	<div id="hours">
	
		<button type="button" onclick="decreaseWeek(); statusChange();">-</button><input id="current_week" type="text" name="current_week"  disabled></input><button type="button" onclick="increaseWeek();statusChange(); ">+</button>
		<tr>
			<th>Timetable</th>
			<th>09.00</th>
			<th>10.00</th>
			<th>11.00</th>
			<th>12.00</th>
			<th>13.00</th>
			<th>14.00</th>
			<th>15.00</th>
			<th>16.00</th>
			<th>17.00</th>
			<th>18.00</th>
		</tr>
	</div>	

	<div id="Days">
		<tr id="Monday">	
			<td>Monday</td>
			<td id="p1"></td>
			<td id="p2"></td>
			<td id="p3"></td>
			<td id="p4"></td>
			<td id="p5"></td>
			<td id="p6"></td>
			<td id="p7"></td>
			<td id="p8"></td>
			<td id="p9"></td>	
			<td id="p10"></td>
		</tr>
		<tr id="Tuesday">
			<td>Tuesday</td>
			<td id="p1"></td>
			<td id="p2"></td>
			<td id="p3"></td>
			<td id="p4"></td>
			<td id="p5"></td>
			<td id="p6"></td>
			<td id="p7"></td>
			<td id="p8"></td>
			<td id="p9"></td>
			<td id="p10"></td>			
		</tr>
		<tr id="Wednesday">
			<td>Wednesday</td>
			<td id="p1"></td>
			<td id="p2"></td>
			<td id="p3"></td>
			<td id="p4"></td>
			<td id="p5"></td>
			<td id="p6"></td>
			<td id="p7"></td>
			<td id="p8"></td>
			<td id="p9"></td>	
			<td id="p10"></td>
		</tr>
		<tr id="Thursday">
			<td>Thursday</td>
			<td id="p1"></td>
			<td id="p2"></td>
			<td id="p3"></td>
			<td id="p4"></td>
			<td id="p5"></td>
			<td id="p6"></td>
			<td id="p7"></td>
			<td id="p8"></td>
			<td id="p9"></td>
			<td id="p10"></td>			
		</tr>
		<tr id="Friday">
			<td>Friday</td>
			<td id="p1"></td>
			<td id="p2"></td>
			<td id="p3"></td>
			<td id="p4"></td>
			<td id="p5"></td>
			<td id="p6"></td>
			<td id="p7"></td>
			<td id="p8"></td>
			<td id="p9"></td>
			<td id="p10"></td>			
		</tr>
		</div>
</body>

</html>
