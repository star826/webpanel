<?php 
@include_once("language.php");
@include_once("session.php");
@include_once("config.php");
@include_once("function.php");
@include_once("alert_function.php");

if(IsUser())
{
	header( "HTTP/1.1 301 Moved Permanently" );    
	header( "Location: vhost.php" );
	exit;
}
if(@$_POST['action']=='reg')
{
	$err = array();
	if(strlen($_POST['auth_username'])<3 || strlen($_POST['auth_username'])>32)
	{
		$err[]='您的用户名必须为3到32个字符！';
	}
	if(strlen($_POST['auth_password'])<6 || strlen($_POST['auth_password'])>32)
	{
		$err[]='您的密码必须为6到32个字符！';
	}
	if($_POST['auth_password'] != $_POST['auth_password2'])
	{
		$err[]='两次输入的密码不同！';
	}
	if(preg_match('/[^a-z0-9\-\_\.]+/i',$_POST['auth_username'])) // '/[^a-z0-9\-\_\.]+/i'
	{
		$err[]='您的用户名包含无效字符！';
	}
	if(!filter_var($_POST['email'],FILTER_VALIDATE_EMAIL))
	{
		$err[]='您的邮件地址无效！';
	}
	if(!count($err))
	{
		mysql_query("	INSERT INTO users(username,password,email)
						VALUES(
							".check_input($_POST['auth_username']).",
							".check_input(md5($_POST['auth_password'])).",
							".check_input($_POST['email'])."
						)");
		$_SESSION['UserID'] = mysql_insert_id();
		$_SESSION['username']=$_POST['auth_username'];
		$_SESSION['email'] = $_POST['email'];
		if(mysql_affected_rows($con)==1)
		{
			$ip_ = !empty($_SERVER["HTTP_CF_CONNECTING_IP"])?$_SERVER["HTTP_CF_CONNECTING_IP"]:$_SERVER['REMOTE_ADDR'];
			mysql_query("INSERT INTO users_extension(UserID,credit,email,regdate,regip,lastlogindate,lastloginip)
						 VALUES(
						".mysql_insert_id().",
						0,
						".check_input($_POST['email']).",
						NOW(),
						'".$ip_."',
						NOW(),
						'".$ip_."'
						)");
			if (empty($_SESSION['Referer'])){
				header("Location: vhost.php");
			} else {
				header("Location: " . $_SESSION['Referer']);
			}
			exit();
		}
		else $err[]='用户名或邮箱已存在！';
	}
	if(count($err))
	{
		$_SESSION['msg']['reg-err'] = implode('\n',$err);
	}
}
?>
<!DOCTYPE html>
<html dir="ltr" lang="zh-CN">
<head>
<meta charset="UTF-8" />
<title><?php echo $PanelName;?> 注册</title>
<link rel="stylesheet" type="text/css" href="css/login.css">
<script language=JavaScript>
function CheckForm()
{
	if (document.CFForm_1.auth_username.value.length == 0) {
		alert("请输入用户名!");
		document.CFForm_1.auth_username.focus();
		return false;
	}
	if (document.CFForm_1.auth_password.value.length == 0) {
		alert("请输入密码!");
		document.CFForm_1.auth_password.focus();
		return false;
	}
	if (document.CFForm_1.auth_password2.value.length == 0) {
		alert("请输入确认密码!");
		document.CFForm_1.auth_password2.focus();
		return false;
	}
	if (document.CFForm_1.email.value.length == 0) {
		alert("请输入邮箱!");
		document.CFForm_1.email.focus();
		return false;
	}
}  
</script>
</head>
<body>
<div id="overlay"></div>
<div id="lines"></div>
<div id="page">
<br /><br /><br />
<br /><br />

<?php
if($RegSwitch){
	echo "<h1 style=\"color:#fff;\">对不起，注册已关闭 3秒后自动跳转</h1>"; 
	echo "<h5 style=\"color:#fff;\">如果浏览器不支持自动跳转，请点击<a href=\"http://$SiteDomain\">链接</a></h5>"; 
	echo "<meta http-equiv=Refresh content=3;URL=http://$SiteDomain>";
	die;
}
?>

<form name="CFForm_1" id="CFForm_1" action="" method="post" onsubmit="return CheckForm();">
	<div id="login">
		<h1><?php echo $lang['register']; ?></h1>
		<p>&nbsp;</p>
		<input name="auth_username" id="auth_username" title="用户名" type="text" maxlength="32"  tabindex="1"  class="input"  size="24"  border="0"  value="<?php echo @$_POST['auth_username']; ?>"  autofocus="autofocus" autocomplete="off" placeholder="请输入您的用户名." required />
		<br />
		<input name="auth_password" id="auth_password" title="密码" type="password" maxlength="128"  tabindex="2"  class="input"  size="24"  border="0" autocomplete="off" placeholder="请输入您的密码." required />
		<br />
		<input name="auth_password2" id="auth_password2" title="确认密码" type="password" maxlength="128"  tabindex="3"  class="input"  size="24"  border="0" autocomplete="off" placeholder="请再次输入您的密码." required />
		<br />
		<input name="email" id="email" type="email" maxlength="64" title="邮箱" tabindex="4"  class="input"  size="24"  border="0" value="<?php echo @$_POST['email']; ?>" autocomplete="off" placeholder="请输入您的邮箱地址." required />
		<input type="submit" style="position: absolute; top: -999em" />
		<p>&nbsp;</p>
		<input type="hidden" name="action" value="reg" />
		<div id="ui">
			<ul id="navigation">
				<li class="first"><a class="first" href="#" onclick="document.CFForm_1.submit();">&nbsp;&nbsp;<?php echo $lang['submit']; ?></a></li>
				<li><a href="login.php"><?php echo $lang['back_to_login']; ?></a></li>
			</ul>
		</div>
	</div>
</form>
<br /><br /><br />
<br /><br /><br />
</div>
<?php echo $statisticalCode; ?>
</body>
</html>
<?php
	if(@$_SESSION['msg']['reg-err'])
	{
		echo '<script type="text/JavaScript">alert("'.$_SESSION['msg']['reg-err'].'");</script>';
		unset($_SESSION['msg']['reg-err']);
	}
	if(@$_SESSION['msg']['reg-success'])
	{
		echo '<script type="text/JavaScript">alert("'.$_SESSION['msg']['reg-success'].'");top.location="index.php";</script>';
		unset($_SESSION['msg']['reg-success']);
	}
?>
