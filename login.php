<?php
@include_once("language.php");
@include_once("session.php");
@include_once("config.php");
@include_once("function.php");
if(IsUser())
{
	header( "HTTP/1.1 301 Moved Permanently" );    
	header( "Location: vhost.php" );
	exit;
}
if (@$_POST['action'] == 'login' )
{
	$err = array();
	if(!$_POST['auth_username'] || !$_POST['auth_password'])
		$err[] = '请输入用户名和密码！';
	if(!count($err))
	{
		$_POST['auth_username'] = check_input($_POST['auth_username']);
		$_POST['auth_password'] = $_POST['auth_password'];
		$row = mysql_fetch_assoc(mysql_query("SELECT * FROM users WHERE username={$_POST['auth_username']} AND password=".check_input(md5($_POST['auth_password']))));
		if($row['ID'])
		{
			if ( 1 == $row['locked']) die("Your account has been disabled");
			$_SESSION['UserID'] = $row['ID'];
			$_SESSION['username']=$row['username'];
			$_SESSION['email'] = $row['email'];
			$_SESSION['api_key'] = $row['api_key'];
			$row = mysql_fetch_assoc(mysql_query("SELECT * FROM users_extension WHERE UserID='{$_SESSION['UserID']}' "));
			$_SESSION['lastlogindate'] = $row['lastlogindate'];
			$_SESSION['lastloginip'] = $row['lastloginip'];
			$AdminUserArray = explode("|", $AdminUser);
			foreach ($AdminUserArray as $i) {
				if ($i == $_SESSION['username']){
					$_SESSION['admin'] = true;
				}
			}
			$ip_ = GetIP();
			mysql_query("UPDATE users_extension SET lastlogindate=NOW(), lastloginip='".$ip_."' WHERE UserID=".$_SESSION['UserID'] );
			if (empty($_SESSION['Referer'])){
				header("Location: vhost.php");
			} else {
				header("Location: " . $_SESSION['Referer']);
			}
			exit();
		}
		else $err[]='错误的用户名或密码！';
	}
	if($err)
	$_SESSION['msg']['login-err'] = implode('<br />',$err);
	header("Location: login.php");
	exit; 
}
?>
<!DOCTYPE html>
<html dir="ltr" lang="zh-CN">
<head>
<meta charset="UTF-8" />
<title><?php echo $PanelName;?> <?php echo $lang['login'];?></title>
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
}
</script>
</head>
<body>
<div id="overlay"></div>
<div id="lines"></div>
<div id="page">
<br /><br /><br />
<br /><br />
<form name="CFForm_1" id="CFForm_1" action="" method="post" onsubmit="return CheckForm();">
	<div id="login">
		<h1><?php echo $PanelName;?> <?php echo $PanelSubName;?></h1>
		<p>&nbsp;</p>
			<input name="auth_username" id="auth_username" title="用户名"  type="text" maxlength="32"  tabindex="1"  class="input"  size="24"  border="0" value="<?php echo @$_GET['username'] ?>" autofocus="autofocus" placeholder="<?php echo $lang['input_username']; ?>" required  />
			<input name="auth_password" id="auth_password" title="密码" type="password" maxlength="128"  tabindex="2"  class="input"  size="24" border="0" placeholder="<?php echo $lang['input_password']; ?>" required />
			<input type="submit" style="position: absolute; top: -999em" />
		<p>&nbsp;</p>
		<input type="hidden" name="action" value="login" />
		<div id="ui">
			<ul id="navigation">
				<li class="first"><a class="first" href="#" onclick="document.CFForm_1.submit();">&nbsp;&nbsp;<?php echo $lang['login']; ?></a></li>
				<li><a href="register.php"><?php echo $lang['register']; ?></a></li>
				<li><a class="last" href="forgot_password.php"><?php echo $lang['forgot_password']; ?></a></li>
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
if(@$_SESSION['msg']['login-err'])
{
	echo '<script type="text/JavaScript">alert("'.$_SESSION['msg']['login-err'].'");</script>';
	unset($_SESSION['msg']['login-err']);
}
?>