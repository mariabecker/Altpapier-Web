<?php

ini_set('display_errors', true);
error_reporting(E_ALL);

require_once('config.php');
require_once('functions.php');

ob_start();
session_start();



$error = '';
$showAddUser = FALSE;

// Logout
if(array_key_exists('logout',$_GET)){
	$_SESSION['USER'] = NULL;
	session_destroy();
	header('Location: login.php');
	exit;
}


// Guest Login
if(array_key_exists('guest', $_POST)){
	$_SESSION['USER'] = 'gast';
	header('Location: index.php');
	exit;
}


// Login
if(array_key_exists('login', $_POST)){
	
	$sql = sql_connect();

	if(login($_POST['username'], $_POST['password'])) {
	$_SESSION['USER'] = $_POST['username'];
	header('Location: index.php');
	exit;
	}
	else {
		$error = '<span id="error">Benutzer/Passwort falsch.</span><br />';
	}
}

// user creation
if(array_key_exists('createUser', $_POST)){
	
	$sql = sql_connect();

	checkLogin();
	if(isGuestUser()){
		$error = 'Nicht erlaubt.<br />';
	}
	else {
		$success = createUser($_POST['username'], $_POST['password']);
		header('Location: login.php?createSuccess=' . $success);
		exit;
	}
}


// Redirect logged in Guests to index.php
if(array_key_exists('USER', $_SESSION) && isGuestUser()){
	header('Location: index.php');
	exit;
}
// other logged in users should see "add user" form
elseif(array_key_exists('USER', $_SESSION) && $_SESSION['USER']!=NULL){
	$showAddUser = TRUE;
	if(array_key_exists('createSuccess', $_GET) && $_GET['createSuccess']!==TRUE){
		$error = 'Erfolgreich angelegt.<br />';
	}
}


?>
<!DOCTYPE html>
<html>
	<head>
		<title>Altpapier - Login</title>
		<style type="text/css">
			* {
				font-family:sans-serif;
			}
			label, input{
				width: 200px;
				display: inline-block;
				margin: 20px;
			}
			form{
				border: 1px solid black;
				display: block;
				width: 550px;
				margin: auto;
				padding: 20px;
			}
			#error {
				font-weight: bold;
				padding: 10px;
			}
		</style>
	</head>
	<body>
	
		<form method="POST">
		
			<?=$error?>
			
			
			<?php
			if($showAddUser) {
			?>
			Angemeldet als: <?=$_SESSION['USER']?><br />
			Neuen Benutzer anlegen:<br />
			<?php
			}
			?>
		
			<label for="username">Benutzername</label>
			<input type="text" id="username" name="username"/>
			<br />
			
			<label for="password">Passwort</label>
			<input type="password" id="password" name="password"/>			
			<br />
			<?php
			if($showAddUser) {
			?>
			<input type="submit" name="createUser" value="Benutzer Anlegen"/> oder <button onclick="window.location='login.php?logout'; return false;">Abmelden</button>
			<?php
			}
			else {
			?>
			<input type="submit" name="login" value="Anmelden"/> oder <input type="submit" name="guest" value="Als Gast weiter"/>
			<?php
			}
			?>
		</form>
	</body>
</hrml>