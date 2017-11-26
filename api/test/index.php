<?php

	$apiUrl = 'http://altpapier-app.de/api/v1/content';

	if($_POST['submit'] == 1){
		$requestUrl = $apiUrl.'?page='.$_POST['page'].'&pagesize='.$_POST['pagesize'];
		
		$result = file_get_contents($requestUrl);
		
		echo 'Result:<br /><pre>';
		var_dump($result);
		echo '</pre>';
		
		
		echo 'JSON:<br /><pre>';
		var_dump(json_decode($result));
		echo '</pre>';
	}
?>

<form method="POST" action="<?=$_SERVER['PHP_SELF']?>">
	Seite: <input type="text" name="page" value="1" />
	Anzahl: <input type="text" name="pagesize" value="10" />
	<input type="hidden" name="submit" value="1" />
	<input type="submit" value="Senden" />
	
</form>