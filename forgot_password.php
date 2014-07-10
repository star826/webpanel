<?php
@include_once("language.php");
@include_once("session.php");
@include_once("config.php");
@include_once("function.php"); // function send_mail,randStr
if(IsUser())
{
	header( "HTTP/1.1 301 Moved Permanently" );    
	header( "Location: vhost.php" );
}
if (!empty($_GET['token']))
{
	$err = array();
	$_GET['token'] = check_input($_GET['token']);
	$row = mysql_fetch_assoc(mysql_query("SELECT * FROM users_forgot WHERE token=".$_GET['token']));
	if(!$row['UserID'])
	{
		$err[] = '密码重设链接已失效！';
	}
}
if (!empty($_POST['token']) && $_POST['action'] == 'reset_password') {
	$err = array();
	$_GET['token'] = check_input($_GET['token']);
	$_POST['token'] = check_input($_POST['token']);	
	$row = mysql_fetch_assoc(mysql_query("SELECT * FROM users_forgot WHERE token=".$_GET['token']));
	if($row['UserID'])
	{
		$userID = $row['UserID'];
	} else {
		$err[] = '密码重设链接已失效！';
	}
	if(!count($err))
	{
		if(strlen($_POST['password'])<6 || strlen($_POST['password'])>32)
		{
			$err[]='您的密码必须为6到32个字符！';
		}
		if($_POST['password'] != $_POST['password2'])
		{
			$err[]='两次输入的密码不同！';
		}
		if(!count($err))
		{
			mysql_query("DELETE FROM users_forgot WHERE UserID=".$userID." ");
			mysql_query("UPDATE users SET password='".md5($_POST['password'])."' WHERE ID=".$userID);
			$_SESSION['msg']['forgot-success']='重设密码成功';
		}
	}
}
if (@$_POST['action'] == 'forget_password' )
{
	$err = array();
	if(!$_POST['username'])
		$err[] = '请输入你的用户名！';
	if(!count($err))
	{
		$_POST['username'] = check_input($_POST['username']);	
		$row = mysql_fetch_assoc(mysql_query("SELECT * FROM users WHERE username={$_POST['username']} "));
		if($row['ID'])
		{
			$randsession = randStr(32);
			mysql_query("	INSERT INTO users_forgot(UserID,token)
							VALUES(
								'".$row['ID']."',
								'".$randsession."'
							)");
			send_mail(	'forgot_password@' . $SiteDomain ,
							$row['email'],
							$PanelName . ' - 密码重设 ',
							'密码重设链接: '.'http://'. $PanelDomain .'/forgot_password.php?token='.$randsession );
			$_SESSION['msg']['forgot-success']='已发送密码重置邮件，请按照邮件内提示找回密码！';
		}
		else $err[]='你的用户名不存在！';
	}
}
if(count(@$err))
{
	$_SESSION['msg']['forgot-err'] = implode('\n',$err);
}
?>
<!DOCTYPE html>
<html dir="ltr" lang="zh-CN">
<head>
<meta charset="UTF-8" />
<title><?php echo $PanelName; ?> <?php echo $lang['forgot_password']; ?></title>
<link rel="stylesheet" type="text/css" href="css/login.css">
<script language=JavaScript>
function CheckForm()
{
	if (document.forgot.username.value.length == 0) {
		alert("请输入用户名!");
		document.forgot.username.focus();
		return false;
	}
}
</script>
</head>
<body>
<div id="overlay"></div>
<div id="lines"></div>
<div id="page" style="width: 800px; text-align: center;">
<br /><br /><br />
<br /><br />
<form name="forgot" id="forgot" action="" method="post" onsubmit="return CheckForm();">
<?php 
if (empty($_GET['token'])) {
?>
	<div id="login">
		<h1><?php echo $lang['forgot_password']; ?></h1>
		<p>&nbsp;</p>
		<input name="username" id="username" type="text" maxlength="32" title="用户名" tabindex="1"  class="input"  size="24"  border="0" autocomplete="off" placeholder="<?php echo $lang['input_username']; ?>" required />
		<input type="submit" style="position: absolute; top: -999em" />
		<p>&nbsp;</p>
		<input type="hidden" name="action" value="forget_password" />
		<div id="ui">
			<ul id="navigation">
				<li class="first"><a class="first" href="#" onclick="document.forgot.submit();">&nbsp;&nbsp;<?php echo $lang['submit']; ?></a></li>
				<li><a href="login.php"><?php echo $lang['back_to_login']; ?></a></li>
				<li><a href="forgot_username.php"><?php echo $lang['forgot_username']; ?></a></li>
			</ul>
		</div>
	</div>
<?php 
} else {
?>
	<div id="login">
		<h1>重设密码</h1>
		<p>&nbsp;</p>
		<input name="password" id="auth_password" type="password" title="新密码" maxlength="128"  tabindex="2"  class="input"  size="24"  border="0" autocomplete="off" placeholder="请输入新密码." required />
		<input name="password2" id="auth_password2" type="password" title="确认新密码" maxlength="128"  tabindex="3"  class="input"  size="24"  border="0" autocomplete="off" placeholder="请再次输入新密码." required />
		<input type="submit" style="position: absolute; top: -999em" />
		<p>&nbsp;</p>
		<input type="hidden" name="action" value="reset_password" />
		<input type="hidden" name="token" value="<?php echo $_GET['token']; ?>" />
		<div id="ui">
			<ul id="navigation">
				<li class="first"><a class="first" href="#" onclick="document.forgot.submit();">&nbsp;&nbsp;<?php echo $lang['submit']; ?></a></li>
				<li><a href="login.php"><?php echo $lang['back_to_login']; ?></a></li>
				<li><a href="forgot_username.php"><?php echo $lang['forgot_username']; ?></a></li>
			</ul>
		</div>
	</div>
<?php } ?>
</form>
<br /><br /><br />
<br /><br /><br />
</div>
<?php echo $statisticalCode; ?>
</body>
</html>
<?php
if(@$_SESSION['msg']['forgot-err'])
{
	echo '<script type="text/JavaScript">alert("'.$_SESSION['msg']['forgot-err'].'");history.go(-1);</script>';
	unset($_SESSION['msg']['forgot-err']);
}
if(@$_SESSION['msg']['forgot-success'])
{
	echo '<script type="text/JavaScript">alert("'.$_SESSION['msg']['forgot-success'].'");top.location="index.php";</script>';
	unset($_SESSION['msg']['forgot-success']);
}
?>