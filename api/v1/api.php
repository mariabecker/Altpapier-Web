<?php

	if($_GET['dummy']=='1'){
		if($_GET['method']=='content'){
			$response = array();
			
			$article = array();
			
			for($i = 0; $i < (int)$_GET['pagesize']; $i++){
				$article['id']=(int)$i+((int)$_GET['pagesize']*((int)$_GET['page'] - 1));
				$article['dateIssued']=(string)'1917-11-30';
				$article['newspaperTitle']=(string)'Volkszeitung (1890-1904) \/Berliner Volkszeitung (1904-1930)';
				$article['volume']=(int)65;
				$article['issue']=(int)611;
				$article['headline']=(string)'Heirats-Gesuche';
				$article['text']=(string)'Kriegswitwe, 35 Jahre, dunkel, wünscht Herrenbekanntschaft zwecks\nsp\u00e4terer Heirat. Offerten unter\n\"Rk. 418\" an Expedition dieses\nBlattes, Neuk\u00f6lln, Berlinerstr. 41.';
				$article['imageUrl']=(string)'http://altpapier-app.de/api/v1/image/article_snippet_4.jpg';
				$article['imageWidth']=(int)458;
				$article['imageHeight']=(int)142;
				$article['imageSize']=(int)8902;
				$article['isDeleted']=(bool)0;
				$article['lastModified']=(string)'2017-11-21 08:34:28';
				$article['pageNr']=1;
				$response[]=$article;
			}
			
			echo json_encode($response);
		}
	}else{
		
		ini_set('display_errors', false);
		error_reporting(0);

		require_once('config.php');
		require_once('functions.php');

		$sql = sql_connect();
		
		if($_GET['method']=='content'){
			
			if( $_GET['pagesize'] != intval($_GET['pagesize']) || $_GET['page'] != intval($_GET['page'])|| $_GET['pagesize'] < 1 || $_GET['page'] < 1){
				$error = array('error'=>true, 'message'=>'invalid parameters');
				die(json_encode($error));
			}
			
			
			$content = getContent((int)$_GET['pagesize']*((int)$_GET['page'] - 1),(int)$_GET['pagesize']);
			
			foreach($content as &$row){
				$row['isDeleted'] = (bool)$row['isDeleted'];
				if(array_key_exists('page_nr', $row)){
					$row['pageNr'] = $row['page_nr'];
					unset($row['page_nr']);
				}
				
			
				if($row['isDeleted']){
					foreach($row as $fieldname=>&$field){
						if($fieldname != 'id' && $fieldname != 'isDeleted' && $fieldname != 'lastModified'){
							$field = null;
						}
					}
				}
			}
			
			echo json_encode($content);
		}elseif($_GET['method']=='image'){
			if($_GET['articleId'] != intval($_GET['articleId']) || $_GET['articleId'] < 0){
				$error = array('error'=>true, 'message'=>'invalid parameters');
				die(json_encode($error));
			}
			
			header('Content-type: image/jpeg');
			
			echo getImage($_GET['articleId']);
		}
	}

?>