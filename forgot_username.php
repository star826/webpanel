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
if (@$_POST['action'] == 'forgot_username' )
{
	$err = array();
	if(!$_POST['email'])
		$err[] = '请输入你的邮箱地址！';
	if(!filter_var($_POST['email'],FILTER_VALIDATE_EMAIL))
	{
		$err[]='您的邮件地址无效！';
	}
	if(!count($err))
	{
		$_POST['email'] = check_input($_POST['email']);
		$row = mysql_fetch_assoc(mysql_query("SELECT * FROM users WHERE email={$_POST['email']} "));
		if($row['ID'])
		{	
			$randsession = randStr(32);
			send_mail(	'forgot_username@' . $SiteDomain ,
							$row['email'],
							$PanelName . ' - 您的用户名 ',
							'您的用户名是: '. $row['username'] );			

			$_SESSION['msg']['forgot-success']='已发送您的用户名至您的邮箱地址！';
		}
		else $err[]='你的邮箱不存在！';
	}
	if(count($err))
	{
		$_SESSION['msg']['forgot-err'] = implode('\n',$err);
	}
}
?>
<!DOCTYPE html>
<html dir="ltr" lang="zh-CN">
<head>
<meta charset="UTF-8" />
<title><?php echo $PanelName;?> <?php echo $lang['forgot_username']; ?></title>
<link rel="stylesheet" type="text/css" href="css/login.css">
<script language=JavaScript>
function CheckForm()
{
	if (document.forgot.email.value.length == 0) {
			alert("请输入邮箱!");
			document.forgot.email.focus();
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
	<div id="login">
		<h1><?php echo $lang['forgot_username']; ?></h1>
		<p>&nbsp;</p>
		<input name="email" id="email" type="text" maxlength="64" title="邮箱地址" tabindex="1"  class="input"  size="24"  border="0" autocomplete="off" placeholder="<?php echo $lang['input_email']; ?>" required />
		<input type="submit" style="position: absolute; top: -999em" />
		<p>&nbsp;</p>
		<input type="hidden" name="action" value="forgot_username" />
		<div id="ui">
			<ul id="navigation">
				<li class="first"><a class="first" href="#" onclick="document.forgot.submit();">&nbsp;&nbsp;<?php echo $lang['submit']; ?></a></li>
				<li><a href="login.php"><?php echo $lang['back_to_login']; ?></a></li>
				<li><a href="forgot_password.php"><?php echo $lang['forgot_password']; ?></a></li>
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
if(@$_SESSION['msg']['forgot-err'])
{
	echo '<script type="text/JavaScript">alert("'.$_SESSION['msg']['forgot-err'].'");</script>';
	unset($_SESSION['msg']['forgot-err']);
}
if(@$_SESSION['msg']['forgot-success'])
{
	echo '<script type="text/JavaScript">alert("'.$_SESSION['msg']['forgot-success'].'");top.location="index.php";</script>';
	unset($_SESSION['msg']['forgot-success']);
}
?>