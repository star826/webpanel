<?php
@include_once("session.php");
if (!empty($_GET['r'])) setcookie("referral", $_GET['r'], time()+2592000);
ForceLogin();
if(IsUser())
{
	header( "HTTP/1.1 301 Moved Permanently" );    
	header( "Location: vhost.php" );
	exit();
}