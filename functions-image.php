<?php   
	
	function insertrecord ($entryid, $title, $description, $creator, $tags, $cate, $url, $size, $db) {
		$entryid = mysqli_real_escape_string($db, $entryid);
		$title = mysqli_real_escape_string($db, $title);
		$description = mysqli_real_escape_string($db, $description);
		$creator = mysqli_real_escape_string($db, $creator);
		$tags = mysqli_real_escape_string($db, $tags);
		$cate = mysqli_real_escape_string($db, $cate);
		$url = mysqli_real_escape_string($db, $url);
		$size = mysqli_real_escape_string($db, $size);
		$type = "image";
		$query = "INSERT INTO kalturaimage VALUES ('" . $entryid . "', '" . $title . "', '" . $description . "', '" . $creator . "', '" . $tags . "', '" . $cate . "', '" . $url . "', '$type', $size);";
		//echo $query . "<br />";
		mysqli_query($db, $query);
		if (mysqli_affected_rows($db)===0) {
			echo "No data is inserted.";
		}
	}

	function findrecord ($entryid, $db) {
		$entryid = mysqli_real_escape_string($db, $entryid);
		$query = "SELECT * FROM kalturaimage WHERE entryid = '" . $entryid . "';";
		//echo $query . "<br />";
		$result = mysqli_query($db, $query);
		if (mysqli_num_rows($result) === 0) {
			//echo "false<br />";
			return false;
		}

		mysqli_free_result($result);
		//echo "true<br />";
		return true;
	}




?>