<?php 
	
	function dbconn(){
		$servername = "localhost";
		$username = "root";
		$password = "root";
		$schema = "kaltura";
				
		$db = new mysqli($servername, $username, $password, $schema);

		if ($db->connect_error) {
			die("Connection failed: " . $db->connect_error);
		}

		return $db;
	}
	

?>