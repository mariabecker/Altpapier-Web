<?php

ini_set('display_errors', false);
error_reporting(0);

require_once('config.php');
require_once('functions.php');

session_start();

$sql = sql_connect();
checkLogin();

$selectedDate = $_GET['date'];
if(date($selectedDate) !== $selectedDate || empty($selectedDate)){
	$selectedDate = $dateRange['startDate'];
}

echo createIssueOptions($selectedDate, null);