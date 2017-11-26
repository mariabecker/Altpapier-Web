<?php

ini_set('display_errors', true);
error_reporting(E_ALL);

require_once('config.php');
require_once('functions.php');

$sql = sql_connect();

$selectedDate = $_GET['date'];
if(date($selectedDate) !== $selectedDate || empty($selectedDate)){
	$selectedDate = $dateRange['startDate'];
}

echo createIssueOptions($selectedDate, null);