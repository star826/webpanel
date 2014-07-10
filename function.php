<?php
function check_input($value, $quotes_added = TRUE)
{
	if (!is_numeric($value))
	{
		if (function_exists('mysql_real_escape_string'))
		{
			$value = mysql_real_escape_string($value);
		}
		elseif (function_exists('mysql_escape_string'))
		{
			$value = mysql_escape_string($value);
		}
		else
		{
			$value = addslashes($value);
		}
		
		if ($quotes_added)
		{
			$value = "'" . $value . "'";
		}
	}
	return $value;
}

function money($n){
	return sprintf ("%01.2f", is_numeric($n)?$n:0);
}

function GetIP()
{
	return (!empty($_SERVER["HTTP_CF_CONNECTING_IP"])?$_SERVER["HTTP_CF_CONNECTING_IP"]:$_SERVER['REMOTE_ADDR']);
}

function send_mail($from,$to,$subject,$body)
{
	$headers = '';
	$headers .= "From: $from\r\n";
	$headers .= "Reply-to: $from\r\n";
	$headers .= "Return-Path: $from\r\n";
	$headers .= "X-Mailer: PHP\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-type:text/html;charset=utf-8\r\n";
	@mail($to,$subject,$body,$headers);
}

function randStr($len)
{ 
    $chars="ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz"; // characters to build the password from 
    $string=""; 
    for(;$len >= 1;$len--)
    {
        $position=rand()%strlen($chars);
        $string.=substr($chars,$position,1); 
    }
    return $string; 
}

function flush2 (){
    echo(str_repeat(' ',256));
    if (ob_get_length()){            
        @ob_flush();
        @flush();
        @ob_end_flush();
    }    
    @ob_start();
}

function get_microtime(){   
 list($usec, $sec) = explode(' ', microtime());   
 return ((float)$usec + (float)$sec);   
}

function daydiff($begin_time,$end_time) 
{ 
	if (empty($end_time)) $end_time = strtotime(date("Y-m-d"));
    { 
       $starttime = $end_time; 
       $endtime = $begin_time; 
    } 
    $timediff = $endtime-$starttime; 
    $days = intval($timediff/86400); 
    $remain = $timediff%86400; 
    $hours = intval($remain/3600); 
    $remain = $remain%3600; 
    $mins = intval($remain/60); 
    $secs = $remain%60; 
    $res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs);
	if ($res['day'])
	{
		return $res['day'];
	} else {
		return 0;
	}
}

function timediff2($begin_time,$end_time,$lang) 
{	
    if($begin_time < $end_time){ 
       $starttime = $begin_time; 
       $endtime = $end_time; 
    } 
    else{ 
       $starttime = $end_time; 
       $endtime = $begin_time; 
    } 
    $timediff = $endtime-$starttime; 
    $days = intval($timediff/86400); 
    $remain = $timediff%86400; 
    $hours = intval($remain/3600); 
    $remain = $remain%3600; 
    $mins = intval($remain/60); 
    $secs = $remain%60; 
    $res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs); 
	$time_format = '';
	if ($res['day'])
	{
		$time_format .= $res['day'].' '.$lang['datetime_day'];
		if ($res['day'] > 1)
			$time_format .= $lang['datetime_plural'];
	} elseif ($res['hour']) {
		$time_format .= $res['hour'].' '.$lang['datetime_hour'];
		if ($res['hour'] > 1)
			$time_format .= $lang['datetime_plural'];
	} elseif ($res['min']) {
		$time_format .= $res['min'].' '.$lang['datetime_minute'];
		if ($res['min'] > 1)
			$time_format .= $lang['datetime_plural'];
	} elseif ($res['sec']) {
		$time_format .= $res['sec'].' '.$lang['datetime_second'];
		if ($res['sec'] > 1)
			$time_format .= $lang['datetime_plural'];
	}
	if ($time_format)
		$time_format .= ' '.$lang['datetime_ago'];
	else 
		$time_format = $lang['datetime_now'];
	return $time_format;
}

function timedifffull($begin_time,$end_time,$lang) 
{
    if($begin_time < $end_time){ 
       $starttime = $begin_time; 
       $endtime = $end_time; 
    } 
    else{ 
       $starttime = $end_time; 
       $endtime = $begin_time; 
    } 
    $timediff = $endtime-$starttime; 
    $days = intval($timediff/86400); 
    $remain = $timediff%86400; 
    $hours = intval($remain/3600); 
    $remain = $remain%3600; 
    $mins = intval($remain/60); 
    $secs = $remain%60; 
    $res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs); 
	$time_format = '';
	if ($res['day']){
		$time_format .= $res['day'].' '.$lang['datetime_day'];
		if ($res['day'] > 1)
			$time_format .= $lang['datetime_plural'];
	} 
	if($res['hour']) {
		$time_format .= $res['hour'].' '.$lang['datetime_hour'];
		if ($res['hour'] > 1)
			$time_format .= $lang['datetime_plural'];
	} 
	if ($res['min']) {
		$time_format .= $res['min'].' '.$lang['datetime_minute'];
		if ($res['min'] > 1)
			$time_format .= $lang['datetime_plural'];
	} 
	if ($res['sec']) {
		$time_format .= $res['sec'].' '.$lang['datetime_second'];
		if ($res['sec'] > 1)
			$time_format .= $lang['datetime_plural'];
	} 
	if ($time_format == '') { 
		$time_format = $lang['datetime_moment'];
	}
	return $time_format;
}

function timediff($begin_time,$end_time,$lang) 
{
    if($begin_time < $end_time){ 
       $starttime = $begin_time; 
       $endtime = $end_time; 
    } 
    else{ 
       $starttime = $end_time; 
       $endtime = $begin_time; 
    } 
    $timediff = $endtime-$starttime; 
    $days = intval($timediff/86400); 
    $remain = $timediff%86400; 
    $hours = intval($remain/3600); 
    $remain = $remain%3600; 
    $mins = intval($remain/60); 
    $secs = $remain%60; 
    $res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs); 
	$time_format = '';
	if ($res['day'])
	{
		$time_format .= $res['day'].' '.$lang['datetime_day'];
	} elseif ($res['hour']) {
		$time_format .= $res['hour'].' '.$lang['datetime_hour'];
	} elseif ($res['min']) {
		$time_format .= $res['min'].' '.$lang['datetime_minute'];
	} elseif ($res['sec']) {
		$time_format .= $res['sec'].' '.$lang['datetime_second'];
	}
 	if ($res['day'] > 1 || $res['hour'] > 1 || $res['min'] > 1 || $res['sec'] > 1)
		$time_format .= $lang['datetime_plural'];
	$time_format .= ' '.$lang['datetime_ago'];
	return $time_format;
}

function format_bytes($size) {
    $units = array(' B', ' KB', ' MB', ' GB', ' TB');
    for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
    return round($size, 2).$units[$i];
}

function format_bytes_1000($size) {
    $units = array(' B', ' KB', ' MB', ' GB', ' TB');
	for ($i = 0; $size >= 1000 && $i < 4; $i++) $size /= 1000;
    return round($size, 2).$units[$i];
}
