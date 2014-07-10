<?php
$adminpage = "admin_user_balance.php";
@include_once("header.php");
@include_once("function.php");
@include_once("alert_function.php");
$userid = empty($_GET['userid'])?@$_POST['userid']:@$_GET['userid'];
if (!is_numeric($userid))	ForceDie();
$result_users = mysql_query("select * FROM users WHERE ID=" . $userid);
if (@mysql_num_rows($result_users) != 1) ForceDie();
$row_user = mysql_fetch_array($result_users);
$row_user_extension = mysql_fetch_array(mysql_query("select * FROM users_extension WHERE UserID=" . $row_user['ID']));
if (@$_POST['action'] == 'balance_change')
{
	$amount = @$_POST['amount'];
	$reason = @$_POST['reason'];
	$err = array();
	if($amount == '')
	{
		$err[]='增减的金额不能为空';
	}
	if(substr($amount, 0, 1)!='+' && substr($amount, 0, 1)!='-')
	{
		$amount = '+' . $amount;
	}
	if(!count($err))
	{
		if(mysql_query("UPDATE users_extension SET credit=credit".$amount." WHERE UserID=".$userid))
		{
			$_SESSION['msg']['alert-success']='更改余额成功';
			header("Location: admin_user_balance.php?userid=".$row_user['ID']);
			exit();
		} else {
			$err[]='更改余额失败';
		}
	}
	SetErrAlert($err);
}
?>
<div id="page">
<?php EchoAlert(); ?>
<form name="config_save" id="config_save" action="" method="post" onsubmit="document.getElementById('confirmed').disabled = true;">
<table class="list">
	<tr>
		<th colspan="3">用户余额编辑</th>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">用户</td>
		<td><a href="admin_user_info.php?userid=<?php echo $row_user['ID']; ?>"><span style="color: green"><?php echo $row_user['username']; ?></span></a><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">当前余额</td>
		<td><span style="color: green; font-weight: bold"><?php echo money($row_user_extension['credit']); ?> 元</span><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">增减金额</td>
		<td>
			<input name="amount" id="amount" type="text" value="" maxlength=""  size="30"  tabindex="1" autocomplete="off" autofocus="autofocus" placeholder="输入要更变的余额, 包括运算操作符." required />
		</td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td></td>
		<td><input tabindex="3" class="button" type="submit" name="confirmed" id="confirmed" value="确认"></td>
		<td class="hint"></td>
	</tr>
</table>
<input type="hidden" name="userid" value="<?php echo $row_user['ID']; ?>" />
<input type="hidden" name="action" value="balance_change" />
</form>
</div>
<?php @include_once("footer.php") ?>