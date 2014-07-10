<?php
$page = "profile.php";
if (@$_POST['action'] == 'generateapikey') $NoHeader = true;
@include_once("header.php");
@include_once("function.php");
@include_once("alert_function.php");
if (@$_POST['action'] == 'changepassword') {
	$err = array();
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
		$_POST['password'] = check_input(md5($_POST['password']));
		mysql_query("UPDATE users SET password=".($_POST['password'])." WHERE ID=".$_SESSION['UserID']." AND username='".$_SESSION['username']."' AND email='".$_SESSION['email']."'");
		$_SESSION['msg']['alert-success']='修改密码成功';
		header("Location: profile.php");
		exit();
	}
	SetErrAlert($err);
}
if (@$_POST['action'] == 'changeemail') {
	$err = array();
	if(!filter_var($_POST['email'],FILTER_VALIDATE_EMAIL))
	{
		$err[]='您的邮件地址无效！';
	}
	if(!count($err))
	{
		$_POST['email'] = check_input($_POST['email']);
		mysql_query("UPDATE users SET email=".$_POST['email']." WHERE ID=".$_SESSION['UserID']);
		mysql_query("UPDATE users_extension SET email=".$_POST['email']." WHERE UserID=".$_SESSION['UserID']);
		$_SESSION['email'] = $_POST['email'];
		$_SESSION['msg']['alert-success']='更改邮箱成功';
		header("Location: profile.php");
		exit();		
	}
	SetErrAlert($err);
}

if (@$_POST['action'] == 'generateapikey') {
	$api_key = randStr(64);
	$_SESSION['api_key'] = $api_key;
	mysql_query("UPDATE users SET api_key='".$api_key."' WHERE ID=".$_SESSION['UserID']);
	die($api_key);
}
?>
<script language=JavaScript>
function GenerateAPIKey()
{
document.getElementById("GenerateAPIKeyButton").disabled = true;
if (window.XMLHttpRequest)
  {
  xmlhttp=new XMLHttpRequest();
  }
else
  {
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    document.getElementById("apikey").innerHTML=xmlhttp.responseText;
	document.getElementById("GenerateAPIKeyButton").disabled = false;
    }
  }
xmlhttp.open("POST","profile.php",true);
xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
xmlhttp.send("action=generateapikey");
}
</script>
<div id="page">
<?php EchoAlert(); ?>
<table class="list">
	<tr>
		<th colspan="2"><?php echo $lang['my_profile']; ?> </th>
	</tr>
<form name="password" id="password" action="" method="post">
		<tr class="list_head">
			<td colspan="2"><?php echo $lang['change_password']; ?></td>
		</tr>
		
		<tr class="list_entry">
			<td class="table_form_header"><?php echo $lang['new_password']; ?></td>
			<td>
				<input name="password" id="password" style="margin-bottom:2px;" type="password" maxlength="128"  size="24"  autocomplete="off"  /><br />
				<input name="password2" id="password2"  type="password" maxlength="128"  size="24"  autocomplete="off"  />
			</td>
		</tr>
		<tr class="list_entry">
			<td class="table_form_header">
			</td>
			<td><input class="button" type="submit" value="<?php echo $lang['change_password']; ?>"></td>
		</tr>
		<input type="hidden" name="action" value="changepassword" />
		</form>
<form name="email" id="email" action="" method="post">

		<tr class="list_head">
			<td colspan="2"><?php echo $lang['email']; ?></td>
		</tr>
		<tr class="list_entry">
			<td class="table_form_header"><?php echo $lang['current_email']; ?></td>
			<td><?php echo $_SESSION['email']; ?></td>
		</tr>
		<tr class="list_entry">
			<td class="table_form_header"><?php echo $lang['new_email']; ?></td>
			<td><input name="email" id="email"  type="text" maxlength="50"  size="30"  /></td>
		</tr>
		<tr class="list_entry">
			<td class="table_form_header">
			</td>
			<td><input class="button" type="submit" value="<?php echo $lang['change_email']; ?>"></td>
		</tr>
	<input type="hidden" name="action" value="changeemail" />
	</form>
	<tr class="list_head">
		<td colspan="2"><?php echo $lang['api_key']; ?></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header"><?php echo $lang['api_key']; ?></td>
		<td>
			<code id="apikey"><?php echo @$_SESSION['api_key']; ?></code>
		</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">
		</td>
		<td><input id="GenerateAPIKeyButton" class="button" type="submit" value="<?php echo $lang['generate_a_new_api_password']; ?>" onclick="GenerateAPIKey();"></td>
	</tr>
</table>
</div>
<?php @include_once("footer.php") ?>