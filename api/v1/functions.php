<?php
function sql_connect(){
	$mysqli = new mysqli(SQL_HOST, SQL_USER, SQL_PASSWORD, SQL_DATABASE);
	
	if ($mysqli->connect_errno) {
		echo 'Error: Failed to make a MySQL connection, here is why: ' . PHP_EOL;
		echo 'Errno: ' . $mysqli->connect_errno . PHP_EOL;
		echo 'Error: ' . $mysqli->connect_error . PHP_EOL;
		exit;
	}

	return $mysqli;
}

function getContent($start, $count){
	global $sql;
	
	$stmt = $sql->prepare("SELECT * FROM content_api WHERE isDeleted = 0 LIMIT ?,?") OR die('Prepare failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
	$stmt->bind_param('ii', $start, $count);
	$stmt->execute() OR die(__LINE__ . 'query execution failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
	$result = $stmt->get_result();
	
	$content = array();
	
	while($row = $result->fetch_assoc()){
		$content[]=$row;
	}
	
	return $content;
	
}

function getImage($id){
	global $sql;
	
	$stmt = $sql->prepare("SELECT image_data FROM image_snippets WHERE article_id = ? LIMIT 1") OR die('Prepare failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
	$stmt->bind_param('i', $id);
	$stmt->execute() OR die(__LINE__ . 'query execution failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
	$result = $stmt->get_result();
	
	$row = $result->fetch_array();
	
	return $row['image_data'];
	
}


?>