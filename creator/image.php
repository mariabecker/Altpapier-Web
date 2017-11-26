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

$imagedata = getImage($pageId);
header('Content-Disposition: inline; filename="'.str_replace('.jp2', '.jpg', $imagedata['filename']).'"');
header("Content-type: image/jpeg");

if(file_exists('imgcache/'.$imagedata['filename'])){
	echo file_get_contents('imgcache/'.$imagedata['filename']);
	exit;
}

$image = new imagick();

$image->setResourceLimit(\imagick::RESOURCETYPE_MEMORY, 67108864); 
$image->setResourceLimit(\imagick::RESOURCETYPE_MAP, 67108864);

$image->readImageBlob($imagedata['image_data']);
$image->setImageFormat("jpeg");

$imageBlob = $image->getImageBlob();

file_put_contents('imgcache/'.$imagedata['filename'], $imageBlob);

echo $imageBlob;