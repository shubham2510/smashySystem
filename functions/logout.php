<?php


if(isset($_SESSION['user_id'])) {
	unset($_SESSION['user_id']);
	unset($_SESSION['logged_in']);
	
	header('Location: ../index.php');

}

header('Location: ../index.php');
