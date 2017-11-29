<?php

ini_set('display_errors', false);
error_reporting(0);

require_once('config.php');
require_once('functions.php');

session_start();

$sql = sql_connect();
checkLogin();

$dateRange = getDateRange();
$selectedDate = $_GET['date'];
if(date($selectedDate) !== $selectedDate || empty($selectedDate)){
	$selectedDate = $dateRange['startDate'];
}
$selectedIssueExternalId = $_GET['issue'];

$editArticle = array();
$edit = 0;
$articleId = 0;
if(array_key_exists('edit', $_GET)){
	$editArticle = getArticle($_GET['edit']);
	$edit = 1;
	$articleId = $_GET['edit'];
}

?>
<!DOCTYPE html>
<html>
	<head>
		<title>Altpapier - Artikel erstellen</title>
		<link href="css/jquery-ui.min.css" rel="stylesheet" />
		<link href="css/jquery.Jcrop.min.css" rel="stylesheet"/>
		<style type="text/css" >
			#menu {
				width: 100%;
				margin: 0 auto;
			}
		
			#pagenumbers {
				float: left;
				width: 49%;
			}
			
			#slider {
				float: right;
				width: 49%;
			}
		
			#content {
				width: 100%;
				margin: 0 auto;
			}
			
			#pagetext {
			/*	float: left; */
			/*	width: 49%; */
				height: 400px;
				overflow-y: scroll ;
			}
		
			#imageviewerhelper {
				
				height: 800px;
				
				overflow: hidden;
				border: 1px solid black;
			}
			
			#image {
				cursor: all-scroll;
				-webkit-transform-origin: 0% 0%;
				-moz-transform-origin: 0% 0%;
				-ms-transform-origin: 0% 0%;
				-o-transform-origin: 0% 0%;
				transform-origin: 0% 0%;
			}
			
			#articleForm {
			/*	float: left; */
				height: 400px;
			/*	width: 49%; */
			}
			
			#leftSide {
				height: 800;
				width: 49%;
				float: left;
			}
			
			#rightSide {
				float: right;
				width: 49%;
			}
			
			.selected {
				font-weight: bold;
				background-color: lightblue;
				padding: 3px 5px;
			}
			
			label {
				width: 100px;
				display: inline-block;
			}
			
			input, textarea {
				width: 400px;
			}
			
			#textfield {
				height: 300px;
			}
			
			#imagesnippet {
				max-width: 100%;
			}
			
		</style>
	</head>
	<body>
		Eingeloggt als: <?=$_SESSION['USER']?>  <a href="login.php?logout">&raquo;Logout</a><br />
		<form action="<?=$_SERVER['PHP_SELF']?>" method="GET" id="picker">
			<input type="text" name="date" id="datepicker" value="<?=$selectedDate?>" />
			<select id="issuePicker" name="issue">
				<?=createIssueOptions($selectedDate, $selectedIssueExternalId)?>
			</select>
		</form>
		<br />
		
		<div id="menu">
			<div id="pagenumbers">
				<?php 
					if(!empty($selectedIssueExternalId)){
						
						$pages = getPages($selectedIssueExternalId);
						
						foreach($pages as $page){
							echo '<a id="pagenr'.$page['id'].'" href="javascript:loadImage(\''.$page['id'].'\')" >'.$page['page_nr'].'</a> | '.PHP_EOL;
						}
					}
				?>
			</div>
			
			<div id="slider">
			  <div id="custom-handle" class="ui-slider-handle"></div>
			</div>
		</div>
		
		<br />
		
		<div id="content">
		<div id="leftSide">
			<div id="pagetext">
			</div>
			<br /><br />
			<div id="articleForm">
				<input type="hidden" id="zoom" value="100"/>
				<input type="hidden" id="update" value="<?=$edit?>"/>
				<input type="hidden" id="articleId" value="<?=$articleId?>"/>
				<label for="headline">Überschrift:</label><input type="text" name="headline" id="headline" value="<?=htmlentities($editArticle['headline'])?>" /><br />
				<label for="textfield">Artikeltext:</label><textarea  name="text" id="textfield"><?=htmlentities($editArticle['text'])?></textarea><br />
				<label for="tags">Schlagworte:</label><textarea  name="tags" id="tags"><?=htmlentities($editArticle['tags'])?></textarea><br />
				<br />
				

				<button id="cropButton" onclick="return getImageCoords();">Bildausschnitt wählen</button><br />
				<?php
					if($edit){
						echo '<img src="'.WEB_URL.'/api/v1/image/article_snippet_'.$editArticle['id'].'.jpg" id="imagesnippet" /><br />';
					}
				?>
				<label for="xPos">X-Position:</label><input type="text" name="xPos" id="xPos" value="<?=$editArticle['hpos']?>" /><br />
				<label for="yPos">Y-Position:</label><input type="text" name="yPos" id="yPos" value="<?=$editArticle['vpos']?>" /><br />
				<label for="width">Breite:</label><input type="text" name="width" id="width" value="<?=$editArticle['width']?>" /><br />
				<label for="height">Höhe:</label><input type="text" name="height" id="height" value="<?=$editArticle['height']?>" /><br />
				<br />
				<?php
					if($edit){
						echo '';
					}
				?>
				<label style="<?=(!$edit?'display:none':'')?>" for="delete">Löschen?</label><input style="<?=(!$edit?'display:none':'')?>" type="checkbox" name="delete" id="delete" value="1" /><br />
				<button id="saveArticle" onclick="return saveArticle();" <?=(isGuestUser()&&$edit)?'disabled':''?>><?=($edit)?'Geänderten':'Neuen'?> Artikel speichern</button>
				<button id="cancel" onclick="return cancel();">Abbrechen</button>
			</div>
		</div>
		
		<div id="rightSide">
			<div id="imageviewerhelper">
				<div id="imageviewer" >
					<div id="progressbar">
					</div>
					<img id="image" />
				</div >
				
			</div>	
			
			<div id="articleList">
				<br />Bereits existierende Artikel:<br />
				<?php
					$i = 1;
					foreach($pages as $p){
						echo '<br />Seite '.$i.'<br />';
						$articles = getArticlesForPage($p['id']);
						foreach($articles as $a){
							echo '<a href="?date='.urlencode($selectedDate).'&issue='.urlencode($selectedIssueExternalId).'&edit='.$a['id'].'#'.$p['id'].'">'.htmlentities($a['headline']).'</a><br />';
						}
						$i++;
					}
				?>
			</div>
		</div>
			
			
		</div>
		
		
		<script src="js/jquery-3.2.1.min.js"></script>
		<script src="js/jquery-ui.min.js"></script>
		<script src="js/jquery.Jcrop.min.js"></script>
		
		<script>
			var currentPageId;
			$( "#datepicker" ).datepicker({
				inline: true,
				firstDay: 1,
				yearRange: "<?=year($dateRange['startDate'])?>:<?=year($dateRange['endDate'])?>",
				dateFormat: "yy-mm-dd",
				changeYear: true,
				changeMonth: true,
				minDate: "<?=$dateRange['startDate']?>",
				maxDate: "<?=$dateRange['endDate']?>"
			});
			
			function loadImage(pageId){
				window.location.hash = pageId;
				currentPageId = pageId;
				highlightCurrentPage(pageId);
				$("#image").attr("src","");
				$( "#progressbar" ).show();
				$("#image").attr("src","image.php?pageId="+pageId);
				$.ajax({
				  url: "text.php?pageId="+pageId,
				}).done(function(data) {
				  $("#pagetext").html(data);
				});
			}
			
			$("#image").bind("load", function(){
				$( "#progressbar" ).hide();
			});
			
			$( function() {
				var handle = $( "#custom-handle" );
				$( "#slider" ).slider({
					value: 100,
					create: function() {
						handle.text( $( this ).slider( "value" ) );
					},
					slide: function( event, ui ) {
						handle.text( ui.value );
						$("#zoom").val(ui.value);
						$("#image").css({
						  '-webkit-transform' : 'scale(' + ui.value/100 + ')',
						  '-moz-transform'    : 'scale(' + ui.value/100 + ')',
						  '-ms-transform'     : 'scale(' + ui.value/100 + ')',
						  '-o-transform'      : 'scale(' + ui.value/100 + ')',
						  'transform'         : 'scale(' + ui.value/100 + ')'
						});
					}
				});
			} );
			
			$( function() {
				$( "#imageviewer" ).resizable();
				$( "#image" ).draggable();
			} );
			$( "#progressbar" ).progressbar({
				value: false
			});
			$( "#progressbar" ).hide();
			
			$("#datepicker").on('change', function() {
			  $.ajax({
				  url: "getIssues.php?date="+this.value,
				}).done(function(data) {
				  $("#issuePicker").html(data);
				  $("#picker").submit();
				});
				
			})
			
			$("#issuePicker").on('change', function() {
			  $("#picker").submit();
			})
			
			$(function(){
				
				if(window.location.hash){
					currentPageId = window.location.hash.replace("#", "");
					loadImage(currentPageId);
				}else{
					if("<?=$selectedIssueExternalId?>"){
						loadImage(<?=$pages[0]['id']?>);
					}
				}
				
				
			});
			
			function highlightCurrentPage(currentPageId){
				$("#pagenumbers").children().removeClass("selected");
				$("#pagenr"+currentPageId).addClass("selected");

			}
			var jcrop_api;
			function getImageCoords(){
				$( "#image" ).draggable('disable');	
				$( "#slider" ).slider('disable');
				$("#imagesnippet").hide();
				$("#cropButton").text("Auswahl beenden");
				$("#cropButton").attr("onclick", "return endSelection();")
				var x;
				var y;
				var height;
				var width;
				$('#imageviewer').Jcrop({
			      onChange:   showCoords,
			      onSelect:   showCoords
			    },function(){
			      jcrop_api = this;
			    });

				
				return false;
			}
			
			function showCoords(c)
			  {
			    $('#xPos').val(Math.round((c.x - $('#image').position().left) / ($("#zoom").val()/100)));
			    $('#yPos').val(Math.round((c.y - $('#image').position().top) / ($("#zoom").val()/100)));
			
			    $('#width').val(Math.round(c.w / ($("#zoom").val()/100)));
			    $('#height').val(Math.round(c.h / ($("#zoom").val()/100)));
			  };
			  
			function endSelection(){
				jcrop_api.disable();
			
				$("#cropButton").text("Bildausschnitt wählen");
				$("#cropButton").attr("onclick", "return getImageCoords();");
				return false;
			}
			
			function saveArticle(){
				
				if($("#update").val()==1){
					if(<?=isGuestUser()?1:0?>){
						alert('Keine Berechtigung!');
						return false;
					}
					if (!confirm('Bist du sicher, dass du einen bereits vorhandenen Artikel überschreiben willst?')) {
						return false;
					}
				}
				
				var data = {
					headline: $("#headline").val(),
					text:  $("#textfield").val(),
					tags:  $("#tags").val(),
					pageId: currentPageId,
					width: $('#width').val(),
					height: $('#height').val(),
					hpos: $('#xPos').val(),
					vpos: $('#yPos').val(),
					del: $('#delete').is(':checked'),
					id: <?=$articleId?>,
					update: <?=$edit?>
				};
				
				$.ajax({
					type: "POST",
				  	url: "save.php",
				  	data: data
				}).done(function(result) {
				  alert(result);
				  window.location = '<?=WEB_URL?>/creator?date=<?=urlencode($selectedDate)?>&issue=<?=urlencode($selectedIssueExternalId)?>'+window.location.hash;
				});
				
				
				return false;
			}
			
			function cancel(){
				window.location = '<?=WEB_URL?>/creator?date=<?=urlencode($selectedDate)?>&issue=<?=urlencode($selectedIssueExternalId)?>'+window.location.hash;
				return false;
			}

		</script>
	</body>
</html>