<?php

ini_set('display_errors', false);
error_reporting(0);

require_once('config.php');
require_once('functions.php');

$sql = sql_connect();

	if(array_key_exists('send', $_POST)){
		saveAppContent($_POST['date'], $_POST['selected']);
		header('Location: '.$_SERVER['REQUEST_URI']);
		exit;
	}elseif(array_key_exists('update', $_POST)){
		updateAppContent($_POST['position']);
		header('Location: '.$_SERVER['REQUEST_URI']);
		exit;
	}
	

?>

<!DOCTYPE html>
<html>
	<head>
		<title>Altpapier - CMS</title>
		<style type="text/css" >
			* {
				font-family: sans-serif;
			}
			
			td {
				border: 1px solid black;
				padding: 3px;
			}
			
			table {
				border-collapse: collapse;
			}
			
			.nobreak {
				white-space: nowrap;
			}
			
			.minwidth {
				min-width: 150px;
			}
			
			.imagesnippet {
				max-width: 250px;
			}
		</style>
	</head>
	<body>
		<?php 
			if(!array_key_exists('addTo', $_GET)){
				$articles = getNumberOfArticles(); 
		?>
		<table>
			<tr>
				<th>Datum</th>
				<th>#Artikel</th>
				<th>Artikel hinzufügen</th>
			</tr>
			<?php
				for($timestamp = time(); $timestamp <= time()+30*24*60*60; $timestamp += 24*60*60){
					$date = date('d.m.Y', $timestamp);
					echo '<tr>
						<td>'.$date.'</td>
						<td>'.(array_key_exists($date, $articles)? $articles[$date]:0).'</td>
						<td><a href="?addTo='.date('Y-m-d', $timestamp).'">-&gt;</a></td>
						</tr>'.PHP_EOL;
				}
			?>
		</table>
		<?php
			}else{
				$selectedArticlesForDate = getSelectedArticlesForDate($_GET['addTo']);
				$freeArticlesForDate = getFreeArticlesForDate($_GET['addTo']);
				
		?>
		<a href="?">Zurück zur Übersicht</a>
		<br />Diesem Tag zugeordnet:
		<form action="" method="POST">
			<table>
				<tr>
					<th>Position</th>
					<th>Jahr</th>
					<th>Schlagworte</th>
					<th>Überschrift</th>
					<th>Text</th>
					<th>Bild</th>
				</tr>
				<?php
					foreach($selectedArticlesForDate as $article){
						$year = $article['date_issued'];
						
						
						echo '<tr>
							<td><select name="position['.$article['contentId'].']" />';
							
							for($i = 1; $i <= count($selectedArticlesForDate); $i++){
								echo '<option value="'.$i.'" '.($i == intval($article['position'])?'selected':'').'>'.$i.'</option>';
							}
							echo '<option value="remove">Entfernen</option>';
						echo '</td>
							<td class="nobreak">'.$year.'</td>
							<td class="minwidth">'.htmlentities($article['tags']).'</td>
							<td class="minwidth">'.htmlentities($article['headline']).'</td>
							<td>'.nl2br(htmlentities($article['text'], 0, 100)).'</td>
							<td><img src="'.WEB_URL.'/api/v1/image/article_snippet_'.$article['id'].'.jpg" class="imagesnippet" id="snippet'.$article['id'].'" /></td>
							</tr>'.PHP_EOL;
					}
				?>
			</table>
			<input type="submit" name="update" />
			<input type="hidden" name="date" value="<?=htmlentities($_GET['addTo'])?>" />
		</form>
		
		<br />Diesem Tag nicht zugeordnet:
		<form action="" method="POST">
			<table>
				<tr>
					<th></th>
					<th>Jahr</th>
					<th>Schlagworte</th>
					<th>Überschrift</th>
					<th>Text</th>
					<th>Bild</th>
				</tr>
				<?php
					foreach($freeArticlesForDate as $article){
						$year = $article['date_issued'];
						
						
						echo '<tr>
							<td><input type="checkbox" name="selected['.$article['id'].']" value="true" /></td>
							<td class="nobreak">'.$year.'</td>
							<td class="minwidth">'.htmlentities($article['tags']).'</td>
							<td class="minwidth">'.htmlentities($article['headline']).'</td>
							<td>'.nl2br(htmlentities($article['text'], 0, 100)).'</td>
							<td><img src="'.WEB_URL.'/api/v1/image/article_snippet_'.$article['id'].'.jpg" class="imagesnippet" id="snippet'.$article['id'].'" /></td>
							</tr>'.PHP_EOL;
					}
				?>
			</table>
			<input type="submit" name="send" />
			<input type="hidden" name="date" value="<?=htmlentities($_GET['addTo'])?>" />
		</form>
		<?php
			}
		?>
	</body>
</html>