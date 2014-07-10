<?php
// session_set_cookie_params(2*7*24*60*60);
@session_start();
function IsUser() {
	return (@$_SESSION['UserID']!='');
}
function IsAdmin() {
	return (@$_SESSION['admin']);
}
function ForceLogin(){
	if(!IsUser()) {
		$_SESSION['Referer'] = @$_SERVER['REQUEST_URI'];
		header("Location: login.php");
		exit; 
	}
}
function ForceAdmin(){
	if(!IsAdmin()) {
		ForceDie();
	}
}
function ForceDie(){
	header("Location: access_denied.php ");
	exit;
}
?>