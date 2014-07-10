<?php
$page = "account_contact.php";
@include_once("header.php");
@include_once("alert_function.php");
if(@$_POST['action']=='changecontact')
{
	$err = array();
	if(!filter_var($_POST['Email'],FILTER_VALIDATE_EMAIL))
	{
		$err[]='您的邮件地址无效！';
	}
	if(!count($err))
	{
		$_POST['CompanyName'] = check_input($_POST['CompanyName']);
		$_POST['Email'] = check_input($_POST['Email']);
		$_POST['FirstName'] = check_input($_POST['FirstName']);
		$_POST['LastName'] = check_input($_POST['LastName']);
		$_POST['Address1'] = check_input($_POST['Address1']);
		$_POST['Address2'] = check_input($_POST['Address2']);
		$_POST['City'] = check_input($_POST['City']);
		$_POST['State'] = check_input($_POST['State']);
		$_POST['Zip'] = check_input($_POST['Zip']);
		$_POST['Phone1'] = check_input($_POST['Phone1']);
		$_POST['Phone2'] = check_input($_POST['Phone2']);
		$_POST['QQ'] = check_input($_POST['QQ']);
		mysql_query(" UPDATE users SET email='".$_POST['Email']."' WHERE ID=".$_SESSION['UserID']);
		mysql_query(" UPDATE users_extension SET 
					companyname=".$_POST['CompanyName'].",
					email=".$_POST['Email'].",
					firstname=".$_POST['FirstName'].",
					lastname=".$_POST['LastName'].",
					address1=".$_POST['Address1'].",
					address2=".$_POST['Address2'].",
					city=".$_POST['City'].",
					state=".$_POST['State'].",
					zip=".$_POST['Zip'].",
					phone1=".$_POST['Phone1'].",
					phone2=".$_POST['Phone2'].",
					qq=".$_POST['QQ']." 
					WHERE UserID=".$_SESSION['UserID']);
		$_SESSION['msg']['alert-success']='修改个人信息成功';
	}
	SetErrAlert($err);
}
$result = mysql_query("select * FROM users_extension WHERE UserID=".$_SESSION['UserID']);
$row = mysql_fetch_array($result);
?>
<div id="page">
<form name="contact_save" id="contact_save" action="" method="post">
<?php EchoAlert(); ?>
<table class="list">
	<tr>
		<th colspan="3"><?php echo $lang['contact_information']; ?></th>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header"><?php echo $lang['company_name']; ?></td>
		<td><input name="CompanyName" id="CompanyName"  type="text" value="<?php echo $row['companyname']; ?>" maxlength="128"  size="30"  /> <span style="color:#F00;font-weight:bold;">*</span> </td>
		<td class="hint">个人请填写自己的姓名</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header"><?php echo $lang['Email']; ?></td>
		<td><input name="Email" id="Email"  type="text" value="<?php echo $row['email']; ?>" maxlength="128"  size="30"  /> <span style="color:#F00;font-weight:bold;">*</span> </td>
		<td class="hint">用户接收各种通知</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header"><?php echo $lang['first_ame']; ?></td>
		<td><input name="FirstName" id="FirstName"  type="text" value="<?php echo $row['firstname']; ?>" maxlength="50"  size="30"  /> <span style="color:#F00;font-weight:bold;">*</span> </td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header"><?php echo $lang['last_name']; ?></td>
		<td><input name="LastName" id="LastName"  type="text" value="<?php echo $row['lastname']; ?>" maxlength="50"  size="30"  /> <span style="color:#F00;font-weight:bold;">*</span> </td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header"><?php echo $lang['Address1']; ?></td>
		<td><input name="Address1" id="Address1"  type="text" value="<?php echo $row['address1']; ?>" maxlength="64"  size="30"  /> <span style="color:#F00;font-weight:bold;">*</span> </td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header"><?php echo $lang['Address2']; ?></td>
		<td><input name="Address2" id="Address2"  type="text" value="<?php echo $row['address2']; ?>" maxlength="64"  size="30"  /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header"><?php echo $lang['city']; ?></td>
		<td><input name="City" id="City"  type="text" value="<?php echo $row['city']; ?>" maxlength="50"  size="30"  /> <span style="color:#F00;font-weight:bold;">*</span> </td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header"><?php echo $lang['State']; ?></td>
		<td><input name="State" id="State"  type="text" value="<?php echo $row['state']; ?>" maxlength="50"  size="30"  /> <span style="color:#F00;font-weight:bold;">*</span> </td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header"><?php echo $lang['Zip']; ?></td>
		<td><input name="Zip" id="Zip"  type="text" value="<?php echo $row['zip']; ?>" maxlength="50"  size="30"  /> <span style="color:#F00;font-weight:bold;">*</span> </td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header"><?php echo $lang['Phone1']; ?></td>
		<td><input name="Phone1" id="Phone1"  type="text" value="<?php echo $row['phone1']; ?>" maxlength="50"  size="30"  /> <span style="color:#F00;font-weight:bold;">*</span> </td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header"><?php echo $lang['Phone2']; ?></td>
		<td><input name="Phone2" id="Phone2"  type="text" value="<?php echo $row['phone2']; ?>" maxlength="50"  size="30"  /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header"><?php echo $lang['qq']; ?></td>
		<td><input name="QQ" id="QQ"  type="text" value="<?php echo $row['qq']; ?>" maxlength="50"  size="30"  /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td></td>
		<td><input class="button" type="submit" name="confirmed" value="<?php echo $lang['save_changes']; ?>"></td>
		<td class="hint"></td>
	</tr>
</table>
<input type="hidden" name="action" value="changecontact" />
</form>
</div>
<?php @include_once("footer.php") ?>