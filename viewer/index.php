<?php

ini_set('display_errors', false);
error_reporting(0);

require_once('config.php');
require_once('functions.php');

$sql = sql_connect();
$dateRange = getDateRange();
$selectedDate = $_GET['date'];
if(date($selectedDate) !== $selectedDate || empty($selectedDate)){
	$selectedDate = $dateRange['startDate'];
}
$selectedIssueExternalId = $_GET['issue'];



?>

<!DOCTYPE html>
<html>
	<head>
		<title>Altpapier - Viewer</title>
		<link href="css/jquery-ui.min.css" rel="stylesheet">
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
				float: left;
				width: 49%;
				height: 800px;
				overflow-y: scroll ;
			}
		
			#imageviewer {
				float: right;
				height: 800px;
				width: 49%;
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
			
			.selected {
				font-weight: bold;
				background-color: lightblue;
				padding: 3px 5px;
			}
			
		</style>
	</head>
	<body>
		
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
			<div id="pagetext">
			</div>
			
			<div id="imageviewer" >
				<div id="progressbar">
				</div>
				<img id="image" />
			</div>
			
		</div>
		
		
		<script src="js/jquery-3.2.1.min.js"></script>
		<script src="js/jquery-ui.min.js"></script>
		
		<script>
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
					var currentPageId = window.location.hash.replace("#", "");
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
			
		</script>
	</body>
</html>