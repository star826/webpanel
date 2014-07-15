<?php
/****************************************
*              服务商配置               *
****************************************/
$PanelName = 'Web'; // 主机商名称
$PanelSubName = 'Panel'; // 面板名称
$SiteDomain = 'www.lnmpv.com'; // 主站域名
$PanelDomain = 'cp.lnmpv.com'; // 管理面板域名
$statisticalCode = '<div style="display:none"></div>'; // 统计代码
/****************************************
*             注册开关配置              *
****************************************/
$RegSwitch = false; //true是开放注册 false是关闭注册

/****************************************
*             计划任务配置              *
****************************************/
$TaskAPI = ''; //执行任务API, 请随机生成并填写一个

/****************************************
*              管理员配置               *
****************************************/
$AdminUser = 'admin'; // 管理员帐号, 用 | 符号隔开

/****************************************
*              支付宝配置               *
****************************************/
$aliapy_config['partner']      = ''; //合作身份者id，以2088开头的16位纯数字
$aliapy_config['key']          = ''; //安全检验码，以数字和字母组成的32位字符
$aliapy_config['seller_email'] = ''; //签约支付宝账号或卖家支付宝帐户
$aliapy_config['return_url']   = 'http://'.$PanelDomain.'/api/alipay/return_url.php';
$aliapy_config['notify_url']   = 'http://'.$PanelDomain.'/api/alipay/notify_url.php';
$aliapy_config['sign_type']    = 'MD5';
$aliapy_config['input_charset']= 'utf-8';
$aliapy_config['transport']    = 'http';

/****************************************
*              数据库配置               *
****************************************/
$host = 'localhost';              // 主机
$user = 'root';           // 用户名
$password = 'tmppasswd';          // 密码
$database = 'webpanel';       // 数据库

/****************************************
*           下面的就不要改了            *
****************************************/
date_default_timezone_set('Asia/Shanghai');
error_reporting(0); // E_ALL ^ E_USER_NOTICE
if (get_magic_quotes_gpc()) 
{
	function stripslashes_deep($value) 
	{
		return(is_array($value)?array_map('stripslashes_deep',$value):stripslashes($value));
	}
	$_GET     = 	array_map('stripslashes_deep', $_GET);
	$_POST    = 	array_map('stripslashes_deep', $_POST);
	$_COOKIE = 		array_map('stripslashes_deep', $_COOKIE);
	$_REQUEST = 	array_map('stripslashes_deep', $_REQUEST);
}
$con = mysql_pconnect($host,$user,$password);
if (!$con) die('Could not connect: ' . mysql_error());
mysql_query("set names 'utf8'");
mysql_select_db($database, $con);
