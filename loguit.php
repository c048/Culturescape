<?php

	session_start();
	if(isset($_SESSION['user']))
		unset($_SESSION['user']);
	setcookie('65qs4f898', "", time()-3600);
	setcookie('qs9df7856',  "", time()-3600);
	header('Location: index.php');	
	exit;

?>