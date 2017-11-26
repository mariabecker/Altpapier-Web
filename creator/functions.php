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

function saveArticle($data){

	global $sql;
		
	$stmt = $sql->prepare("INSERT INTO articles (headline, tags, page_id, width, height, hpos, vpos, userName, text) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)") OR die('Prepare failed: (' . $sql->errno . ') ' . $sql->error 	. PHP_EOL);
 	$stmt->bind_param('ssiiiiiss', $data['headline'], $data['tags'], $data['pageId'], $data['width'], $data['height'], $data['hpos'], $data['vpos'], $data['user'], $data['text']);
	$stmt->execute() OR die(__LINE__ . 'query execution failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);	
	$id = $sql->insert_id;
	
	$stmt->close();
	
	return $id;
}

function updateArticle($data){
	global $sql;
		
	$stmt = $sql->prepare("UPDATE articles SET headline = ?, tags = ?, width = ?, height = ?, hpos = ?, vpos = ?, isDeleted = ?, userName = ?, text = ? WHERE id = ?") OR die('Prepare failed: (' . $sql->errno . ') ' . $sql->error 	. PHP_EOL);
 	$stmt->bind_param('ssiiiiissi', $data['headline'], $data['tags'], $data['width'], $data['height'], $data['hpos'], $data['vpos'], $data['del'], $data['user'], $data['text'], $data['id']);
	$stmt->execute() OR die(__LINE__ . 'query execution failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);	
	$stmt->close();
}

function saveImageSnippet($data){
	global $sql;
	
	$stmt = $sql->prepare("INSERT INTO image_snippets (mimetype, article_id, filename, image_data) VALUES (?, ?, ?, ?)") OR die('Prepare failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
    $stmt->bind_param('siss', $data['mimetype'], $data['article_id'], $data['filename'], $data['image_data']);
	
	$stmt->execute() OR die(__LINE__ . 'query execution failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);	
	$id = $sql->insert_id;
	
	$stmt->close();
	
	return $id;
}

function updateImageSnippet($data){
	
	global $sql;
	$stmt = $sql->prepare("UPDATE image_snippets SET mimetype = ?, filename = ?, image_data = ? WHERE article_id = ?") OR die('Prepare failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
    $stmt->bind_param('sssi', $data['mimetype'], $data['filename'], $data['image_data'], $data['article_id']);
	
	$stmt->execute() OR die(__LINE__ . 'query execution failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);	
	
	$stmt->close();
}


function save_issue($data) {
	
	global $sql;
	
	$stmt = $sql->prepare("INSERT INTO issues (external_id, title, date_issued, language_term, volume, issue) VALUES (?, ?, ?, ?, ?, ?)") OR die('Prepare failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
    $stmt->bind_param('ssssii', $data['external_id'], $data['title'], $data['date_issued'], $data['language_term'], $data['volume'], $data['issue']);
	
	$stmt->execute() OR die(__LINE__ . 'query execution failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);	
	$id = $sql->insert_id;
	
	$stmt->close();
	
	return $id;
}

function save_image($data) {
	
	global $sql;
	
	$stmt = $sql->prepare("INSERT INTO images (external_id, image_data, mimetype, page_id, filename) VALUES (?, ?, ?, ?, ?)") OR die('Prepare failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
    $stmt->bind_param('sssis', $data['external_id'], $data['image_data'], $data['mimetype'], $data['page_id'], $data['filename']);
	
	$stmt->execute() OR die(__LINE__ . 'query execution failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);	
	$id = $sql->insert_id;
	
	$stmt->close();
	
	return $id;
}

function getNumberOfArticles() {
	global $sql;
	
	$dates = array();
	if ($result = $sql->query("SELECT date_format(date, '%d.%m.%Y') AS dateFormatted, count(*) AS numberArticles FROM app_content WHERE isDeleted = 0 AND date BETWEEN curdate() AND date_add(curdate(), INTERVAL 30 DAY) GROUP BY date")) {
		while($row = $result->fetch_object()){ 
			$dates[$row->dateFormatted] = $row->numberArticles;
		}
		
		
	}else{
		die(__LINE__ . 'query execution failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
	}
	
	return $dates;
}

function getSelectedArticlesForDate($date){
	global $sql;
	
	$stmt = $sql->prepare("SELECT * FROM `articles_with_issues` WHERE MONTH(date_issued) = MONTH(?) AND DAY(date_issued) = DAY(?) AND contentId IS NOT NULL AND isDeleted=0 ORDER BY position") OR die('Prepare failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
	$stmt->bind_param('ss', $date, $date);
	$stmt->execute() OR die(__LINE__ . 'query execution failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
	$result = $stmt->get_result();
	
	$articles = array();
	
	while($row = $result->fetch_array()){
		$articles[] = $row;
	}
	
	return $articles;
	
}

function getFreeArticlesForDate($date){
	global $sql;
	
	$stmt = $sql->prepare("SELECT * FROM `articles_with_issues` WHERE MONTH(date_issued) = MONTH(?) AND DAY(date_issued) = DAY(?) AND (contentId IS NULL OR isDeleted=1)") OR die('Prepare failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
	$stmt->bind_param('ss', $date, $date);
	$stmt->execute() OR die(__LINE__ . 'query execution failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
	$result = $stmt->get_result();
	
	$articles = array();
	
	while($row = $result->fetch_array()){
		$articles[] = $row;
	}
	
	return $articles;
	
}

function getArticlesForPage($id){
	global $sql;
	
	$stmt = $sql->prepare("SELECT * FROM `articles_with_issues` WHERE pageId = ?") OR die('Prepare failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
	$stmt->bind_param('i', $id);
	$stmt->execute() OR die(__LINE__ . 'query execution failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
	$result = $stmt->get_result();
	
	$articles = array();
	
	while($row = $result->fetch_array()){
		$articles[] = $row;
	}
	
	return $articles;
	
}

function getArticle($id){
	global $sql;
	
	$stmt = $sql->prepare("SELECT * FROM `articles_with_issues` WHERE id = ? LIMIT 1") OR die('Prepare failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
	$stmt->bind_param('i', $id);
	$stmt->execute() OR die(__LINE__ . 'query execution failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
	$result = $stmt->get_result();
	
	return $result->fetch_array();
}

function saveAppContent($date, $selected){
	global $sql;
	
	
	
	$stmt = $sql->prepare("INSERT INTO app_content (date, article_id) VALUES (?, ?)") OR die('Prepare failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
	
	foreach($selected AS $key=>$value){
		$stmt->bind_param('si', $date, $key);
		$stmt->execute() OR die(__LINE__ . 'query execution failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);	
	}
    
	
	$stmt->close();
	
}

function updateAppContent($positions){
	global $sql;
	$updStmt = $sql->prepare("UPDATE app_content SET position = ? WHERE id = ? ") OR die('Prepare failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
	$delStmt = $sql->prepare("UPDATE app_content SET isDeleted = 1 WHERE id = ? ") OR die('Prepare failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
	
	foreach($positions as $key=>$value){
		if($value == 'remove'){
			$delStmt->bind_param('i', $key);
			$delStmt->execute() OR die(__LINE__ . 'query execution failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
		}else{
			$updStmt->bind_param('ii', $value, $key);
			$updStmt->execute() OR die(__LINE__ . 'query execution failed: (' . $sql->errno . ') ' . $sql->error . PHP_EOL);
		}
	}
	$updStmt->close();
	$delStmt->close();
	
}

?>