<?php 

include ("functions/init.php");
session_destroy();

if(isset($_COOKIE['p_email']))
{
	unset($_COOKIE['p_email']);
	setcookie('p_email', '', time()-60);
}
 
redirect("login.php");


?>