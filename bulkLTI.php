<?php
	// This program will add an LTI tool to multiple courses
	// Copyright (C) 2014  Kenneth Larsen - Center for Innovative Design and Instruction
	// Utah State University

	// This program is free software: you can redistribute it and/or modify
	// it under the terms of the GNU Affero General Public License as
	// published by the Free Software Foundation, either version 3 of the
	// License, or (at your option) any later version.

	// This program is distributed in the hope that it will be useful,
	// but WITHOUT ANY WARRANTY; without even the implied warranty of
	// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	// GNU Affero General Public License for more details.
	// http://www.gnu.org/licenses/agpl-3.0.html

	// Display any php errors (for development purposes)
	// error_reporting(E_ALL);
	// ini_set('display_errors', '1');

	session_start();
	// Make sure the user has admin rights
	if(strpos($_POST["roles"],'Administrator') !== false) {
		$_SESSION['allowed'] = true;
	} else if ($_SESSION['allowed'] !== true) {
		echo "Sorry, you are not authorized to view this content";
		return false;
	}
	/********************************************/
	/*********  REQUIRED INFORMATION ************/
	/********************************************/

	// Root url for all api calls
	$canvasURL = 'https://<your institution>.instructure.com/api/v1/';
	// This is the header containing the authorization token from Canvas, depending on the features you use, 
	// this will need to be an admin token
	$token = "###############";

	/********************************************/
	/********************************************/
	$tokenHeader = array("Authorization: Bearer ".$token);
?>
<!DOCTYPE html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<title>Bulk External Tools</title>
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.min.css">
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.css">
	<script type="text/javascript" language="javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
	<script type="text/javascript" charset="utf-8">
		$(document).ready(function() {
			// Add new tool names to this array then add the desired values to the change function below
			var toolsArray = [
				"Paste XML Code", // Default for creating custom tool takes pasted xml code
				"Student Portfolios" // example of another tool that passes the url to an xml file
			];
			$.each(toolsArray, function(index, value){
				$("#predefinedTools").append('<option value="">'+value+'</option>');
			});
			// Add the desired values to this section
			$("#predefinedTools").change(function (){
				var selectedTool = $("#predefinedTools").find("option:selected").text();
				if (selectedTool === "Paste XML Code"){
					// Fill in the tool name field
					$("#name").attr("value", "");
					// LTI tool key
					$("#consumer_key").attr("value", "");
					// LTI tool shared secret
					$("#shared_secret").attr("value", "");
					// This will configure the form to take pasted xml
					$("#additional_params").attr("value", "&config_type=by_xml&config_xml=");
					// Fill in a placeholder for the XML Code field
					$("#xml").attr('placeholder', 'Paste XML Here').attr('value', '');
				} else if (selectedTool === "Student Portfolios"){
					// Set Tool name to the selected tool
					$("#name").attr("value", selectedTool);
					// LTI tool key
					$("#consumer_key").attr("value", "");
					// LTI tool shared secret
					$("#shared_secret").attr("value", "");
					// This will configure the form to take the url to an xml file
					$("#additional_params").attr("value", "&config_type=by_url&config_url=");
					// Insert the xml url into the XML Code field
					$("#xml").attr("value","https://something.edu/ltiConfiguration.xml").attr('placeholder','');
				} 
			}).trigger("change");
			$("#formSubmit").click(function (e){
				var canSubmit = true;
				var errorMessage = '<p style="font-weight:bold;">Please fill in the following fields and submit again:</p><ul>';
				if($("#courses").attr("value") === ""){
					errorMessage += "<li>Course List</li>";
					canSubmit = false;
				}
				if($("#name").attr("value") === ""){
					errorMessage += "<li>Tool Name</li>";
					canSubmit = false;
				}
				if($("#consumer_key").attr("value") === ""){
					errorMessage += "<li>Consumer Key</li>";
					canSubmit = false;
				}
				if($("#shared_secret").attr("value") === ""){
					errorMessage += "<li>Shared Secret</li>";
					canSubmit = false;
				}
				if($("#xml").attr("value") === ""){
					errorMessage += "<li>XML Code</li>";
					canSubmit = false;
				}
				errorMessage += '</ul>';
				if(canSubmit == true){
					$(".message").slideUp();
					$("#LTIform").submit();
				} else {
					e.preventDefault();
					$(".message").html(errorMessage).slideDown();
				}
			})
		});
	</script>
	<style>
		body {padding: 20px;}
		.btn {margin-left: 180px;}
		.center {text-align: center;}
		.message {
			display: none;
		}
	</style>
</head>
<body>
	<?php
		if(isset($_GET['task'])){
			$task = $_GET['task'];
		} else if (isset($_POST['task'])) {
			$task = $_POST['task'];
		}
		switch($task) {
			case 'form':
			bulkLTIform();
			break;

			case 'createTools':
			createTools();
			break;
		}
		function bulkLTIform(){
			echo '
			<div class="well span8">
				<h2 class="center">Bulk LTI Form</h2>
				<p class="center">Use this form to apply an external tool to multiple courses.</p>
				<hr>
				<form action="bulkLTI.php" id="LTIform" method="post" class="form-horizontal">
					<div class="control-group">
						<label for="courses" class="control-label">Course List:</label>
						<div class="controls">
							<input type="text" name="courses" id="courses" placeholder="12345, 67890">
							<br><small>(Enter course numbers seperated by commas)</small>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">External Tool: </label>
						<div class="controls">
						<select id="predefinedTools">
						</select>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="name">Tool Name: </label>
						<div class="controls">
							<input type="text" name="name" id="name">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="consumer_key">Consumer Key: </label>
						<div class="controls">
							<input type="text" name="consumer_key" id="consumer_key">
						</div>
					</div>
					<div class="control-group">
					<label class="control-label" for="shared_secret">Shared Secret: </label>
						<div class="controls">
							<input type="text" name="shared_secret" id="shared_secret">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">XML Code:</label>
						<div class="controls">
							<textarea id="xml" name="xml"></textarea>
						</div>
					</div>
					<input type="hidden" name="additional_params" id="additional_params" value="">
					<input type="hidden" name="task" id="task" value="createTools">
					<input type="submit" name="submit" class="btn btn-primary" id="formSubmit" value="Add Tools">
				</form>
				</div>
				<div class="alert alert-error message span4"></div>
			';

		}
		function curlPost($url, $data) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $GLOBALS['canvasURL'].$url);
			curl_setopt ($ch, CURLOPT_HTTPHEADER, $GLOBALS['tokenHeader']);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // ask for results to be returned

			// Send to remote and return data to caller.
			$response = curl_exec($ch);
			curl_close($ch);
			return $response;
		}
		function addToolFromXML($courseID, $toolParams){
			$apiURL = "courses/".$courseID."/external_tools";
			$response = curlPost($apiURL, $toolParams);
			return $response;
			// name=&consumer_key=&shared_secret=&config_type=by_url&config_url=
		}
		function createTools(){
			// Create variables with the data
			$toolName = $_POST['name'];
			$consumerKey = $_POST['consumer_key'];
			$sharedSecret = $_POST['shared_secret'];
			$additionalParams = $_POST['additional_params'];
			$xml = $_POST['xml'];
			$toolParams = 'name='.$toolName.'&consumer_key='.$consumerKey.'&shared_secret='.$sharedSecret.$additionalParams.$xml;
			// Break up the course list and create the tool in each course
			$courses = $_POST['courses'];
			$coursesArray = explode(",", $courses);
			$course = array_unique($coursesArray);
			foreach($course as $val) {
				$courseID = trim($val);
				$createTool = addToolFromXML($courseID, $toolParams);
			}
			echo '<h2>External Tools have been added.</h2><a href="bulkLTI.php?task=form" class="btn">Return to Form</a>';
		}
	?>
</body>
</html>