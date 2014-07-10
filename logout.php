<?php
@include_once("session.php");
$_SESSION = array();
session_destroy();
header("Location: login.php");
exit();