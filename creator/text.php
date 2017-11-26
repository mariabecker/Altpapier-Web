<?php

ini_set('display_errors', false);
error_reporting(0);

require_once('config.php');
require_once('functions.php');

$sql = sql_connect();


$pageId = $_GET['pageId'];
if(empty($pageId) || !is_numeric($pageId)){
	die('Ungültige pageId');
}

$texts = getPageText($pageId);

foreach($texts as $text){
	echo nl2br($text['text']).'<br /><br />';
}