<?php
ini_set('display_errors', false);
error_reporting(0);

require_once('config.php');
require_once('functions.php');

$sql = sql_connect();

$data = array();
					
$data['headline'] = $_POST['headline'];
$data['text'] = $_POST['text'];
$data['tags'] = $_POST['tags'];
$data['pageId'] = $_POST['pageId'];
$data['width'] = $_POST['width'];
$data['height'] = $_POST['height'];
$data['hpos'] = $_POST['hpos'];
$data['vpos'] = $_POST['vpos'];
$data['id'] = $_POST['id'];
$data['del'] = filter_var($_POST['del'], FILTER_VALIDATE_BOOLEAN);
$data['user'] = $_SERVER['PHP_AUTH_USER'];



$errors = '';
foreach($data as $key=>$value){
	if($key == 'del' || $key == 'id') continue;
	if(empty($value)){
		$errors .= $key.', ';
	}
}

if(!empty($errors)){
	echo('Felder nicht befüllt: '.$errors);
	exit;
}

$id = 0;
if($_POST['update']){
	$id = $_POST['id'];
	updateArticle($data);
}else{
	$id = saveArticle($data);
}

$bigImage = getImage($data['pageId']);

$image = new imagick();

$image->setResourceLimit(\imagick::RESOURCETYPE_MEMORY, 67108864); 
$image->setResourceLimit(\imagick::RESOURCETYPE_MAP, 67108864);

$image->readImageBlob($bigImage['image_data']);
$image->setImageFormat("jpeg");
$image->cropImage($data['width'], $data['height'], $data['hpos'], $data['vpos']);

$imageBlob = $image->getImageBlob();

$imageArray = array();

$imageArray['mimetype'] = 'image/jpeg';
$imageArray['article_id'] = $id;
$imageArray['filename'] = 'article_snippet_'.$id.'.jpg';
$imageArray['image_data'] = $imageBlob;

if($_POST['update']){
	updateImageSnippet($imageArray);
}else{
	$imageId = saveImageSnippet($imageArray);
}




echo "Erfolgreich gespeichert: ".$id;
?>