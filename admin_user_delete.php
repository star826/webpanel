<?php
$adminpage = "admin_user_delete.php";
@include_once("header.php");
@include_once("function.php");
@include_once("alert_function.php");
@include_once("account_function.php");
$userid = empty($_GET['userid'])?@$_POST['userid']:@$_GET['userid'];
if (!is_numeric($userid))	ForceDie();
$result_users = mysql_query("select * FROM users WHERE ID=" . $userid);
if (@mysql_num_rows($result_users) != 1) ForceDie();
$row_user = mysql_fetch_array($result_users);
$row_user_extension = mysql_fetch_array(mysql_query("select * FROM users_extension WHERE UserID=" . $row_user['ID']));
if ('delete' == @$_POST['action']) 
{
	if (empty($_POST['confirm'])) {
		SetColorAlert('请勾选确认删除选项!', 'pink');
		header("Location: admin_user_delete.php?userid=".$userid.'&display='.$_GET['display']);
		exit;
	}
	
	$AdminUserArray = explode("|", $AdminUser);
	foreach ($AdminUserArray as $i) {
		if ($i == UserID2UserName($userid)){
			SetColorAlert('您不能直接删除管理员帐户, 请先在config.php中删除该用户名的管理员权限!', 'pink');
			header("Location: admin_user_delete.php?userid=".$userid.'&display='.$_GET['display']);
			exit;
		}
	}
	$result_vhost = mysql_query("select * FROM vhost WHERE owner=".$userid);
	if (mysql_num_rows($result_vhost) > 0)
	{
		SetColorAlert('该用户有虚拟主机服务, 请先删除该用户的虚拟主机服务!', 'pink');
		header("Location: admin_user_delete.php?userid=".$userid.'&display='.$_GET['display']);
		exit;
	}
	
	mysql_query('delete from users where ID=' . $userid);
	mysql_query('delete from users_billing where UserID=' . $userid);
	mysql_query('delete from users_extension where UserID=' . $userid);
	mysql_query('delete from tickets where OpenedBy=' . $userid);
	SetColorAlert('删除用户成功!', 'blue');
	header("Location: admin_users.php?display=".$_GET['display']);
	exit;

} 

?>
<div id="page">
<?php EchoAlert(); ?>
<form name="config_save" id="config_save" action="" method="post" onsubmit="document.getElementById('confirmed').disabled = true;">
<table class="list">
	<tr>
		<th colspan="3">删除用户</th>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">用户ID</td>
		<td><span style="color: green"><?php echo $row_user['ID']; ?></span><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">用户名</td>
		<td><span style="color: green"><?php echo $row_user['username']; ?></span><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">邮箱</td>
		<td><span style="color: green"><?php echo $row_user['email']; ?></span><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">当前余额</td>
		<td><a href="admin_user_balance.php?userid=<?php echo $row_user_extension['UserID']; ?>"><span style="color: green; font-weight: bold"><?php echo money($row_user_extension['credit']); ?> 元</span></a><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">注册时间</td>
		<td><?php echo ($row_user_extension['regdate']); ?> <br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">最后登录时间</td>
		<td><?php echo ($row_user_extension['lastlogindate']); ?> <br /></td>
		<td class="hint"></td>
	</tr>
<tr class="list_entry">
	<td class="table_form_header"></td>
	<td>
		<input type="checkbox" name="confirm" id="confirm"> <label for="confirm">确认删除该用户.</label>
	</td>
</tr>
<tr class="list_entry">
	<td class="table_form_header"></td>
	<td><input class="button" type="submit" name="doit" id="doit" value="确认删除"></td>
</tr>

</table>
<input type="hidden" name="userid" value="<?php echo $userid;?>" />
<input type="hidden" name="display" value="<?php echo $_GET['display']?$_GET['display']:$_POST['display']; ?>" />
<input type="hidden" name="action" value="delete" />
</form>
</div>
<?php @include_once("footer.php") ?>