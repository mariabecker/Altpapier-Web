<?php

ini_set('display_errors', false);
error_reporting(0);

require_once('config.php');
require_once('functions.php');

session_start();

$sql = sql_connect();
checkLogin();


$pageId = $_GET['pageId'];
if(empty($pageId) || !is_numeric($pageId)){
	die('Ungültige pageId');
}

$texts = getPageText($pageId);

foreach($texts as $text){
	echo nl2br(htmlentities($text['text'])).'<br /><br />';
}