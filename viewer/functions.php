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

function getDateRange(){
	global $sql;
	$range = array();
	if ($result = $sql->query("SELECT min(date_issued) AS startDate, max(date_issued) AS endDate FROM issues")) {
		$row = $result->fetch_object();
		$range['startDate'] = $row->startDate;
		$range['endDate'] = $row->endDate;
		
	}else{
		die(__LINE__ . 'query execution failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
	}
	
	return $range;
}

function year($date){
	return date('Y', strtotime($date));
}


function createIssueOptions($date, $selectedIssue){
	$issues = getAvailableIssues($date);
	$options = '';
	$lastTitle='';
	$i = 0;
	
	if(count($issues) > 0){
		foreach($issues AS $issue){
			$isSelected = $issue['external_id'] == $selectedIssue;
			
			if($lastTitle != $issue['title']){
				if($i > 0){
					$options .= '</optgroup>';
				}
				$options .= '<optgroup label="'.$issue['title'].'">'.PHP_EOL;
				$i = 0;
			}
			$options .= '<option value="'.$issue['external_id'].'"'.($isSelected?' selected':'').'>'.$i.' - Jahrgang '.$issue['volume'].' - Ausgabe '.$issue['issue'].'</option>'.PHP_EOL;
			
			
			$lastTitle = $issue['title'];
			$i++;
		}
	
	}else{
		$options .= '<optgroup label="Keine Ausgaben für dieses Datum">'.PHP_EOL;
		$options .= '<option value="null"'.($isSelected?' selected':'').'> - </option>'.PHP_EOL;
	}
	$options .= '</optgroup>';
	return $options;
}

function getAvailableIssues($date){
	global $sql;
	
	$stmt = $sql->prepare("SELECT external_id, title, volume, issue FROM issues WHERE date_issued = ? ORDER BY external_id") OR die('Prepare failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
	$stmt->bind_param('s', $date);
	$stmt->execute() OR die(__LINE__ . 'query execution failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
	$result = $stmt->get_result();
	
	
	$issues = array();
	while($row = $result->fetch_array()){
		$issues[]=$row;
	}
	
	return $issues;
}

function getPages($issueExternalId){
	global $sql;
	
	$stmt = $sql->prepare("SELECT id, external_id, page_nr FROM pages WHERE issue_id = (SELECT id FROM issues WHERE external_id = ? LIMIT 1) ORDER BY page_nr") OR die('Prepare failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
	$stmt->bind_param('s', $issueExternalId);
	$stmt->execute() OR die(__LINE__ . 'query execution failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
	$result = $stmt->get_result();
	
	
	$pages = array();
	while($row = $result->fetch_array()){
		$pages[]=$row;
	}
	
	return $pages;
}

function getImage($pageId){
	global $sql;
	
	$stmt = $sql->prepare("SELECT image_data, mimetype, filename FROM images WHERE page_id = ? LIMIT 1") OR die('Prepare failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
	$stmt->bind_param('i', $pageId);
	$stmt->execute() OR die(__LINE__ . 'query execution failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
	$result = $stmt->get_result();
	
	return $result->fetch_array();
}

function getPageText($pageId){
	global $sql;
	
	$stmt = $sql->prepare("SELECT id, composed_blocks_id, text FROM text_blocks WHERE page_id = ?") OR die('Prepare failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
	$stmt->bind_param('i', $pageId);
	$stmt->execute() OR die(__LINE__ . 'query execution failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
	$result = $stmt->get_result();
	
	$texts = array();
	
	while($row = $result->fetch_array()){
		$texts[] = $row;
	}
	
	return $texts;
	
}











?>