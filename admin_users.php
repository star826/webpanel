<?php
$adminpage = "admin_users.php";
@include_once("header.php");
@include_once("function.php");
@include_once("alert_function.php");
@include_once("account_function.php");
if (!empty($_GET['userid']) && @$_GET['action'] == 'lock'){
	$userid = $_GET['userid'];
	if ('1' != $_GET['locked'] && '0' != $_GET['locked'])
		ForceDie();
	mysql_query("UPDATE users SET locked=" . $_GET['locked'] . " WHERE ID=" . $userid);
	$_SESSION['msg']['alert-success']=$_GET['locked']?'锁定用户 '.UserID2UserName($userid).' 成功':'解锁用户 '.UserID2UserName($userid).' 成功';
	header("Location: admin_users.php?display=".@$_GET['display']);
	exit();
}
?>
<div id="page">
<?php EchoAlert(); ?>
<table class="list sortable">
	<tr class="list_head">
		<th class="sortfirstasc">ID</th>
		<th>用户名</th>
		<th>邮箱</th>
		<th>余额</th>
		<th>注册时间</th>
		<th>最后登录</th>
		<th>最后登录IP</th>
		<th class="nosort" style="text-align: center">选项</th>
	</tr>
<?php
if($user_display == "todayreg") {
	$result_users = mysql_query("select users.* FROM users,users_extension WHERE to_days(users_extension.regdate) = to_days(now()) AND users_extension.UserID = users.ID;");
} elseif($user_display == "balance") {
	$result_users = mysql_query("select users.* FROM users,users_extension WHERE credit!=0 AND users_extension.UserID = users.ID");
} elseif($user_display == "yesterdayreg") {
	$result_users = mysql_query("select users.* FROM users,users_extension WHERE to_days(NOW()) - to_days(users_extension.regdate) <= 1 AND to_days(users_extension.regdate) != to_days(now()) AND users_extension.UserID = users.ID;");
} elseif($user_display == 'all') {
	$result_users = mysql_query("select * FROM users");
} else {
	ForceDie();
}
while($row_user = mysql_fetch_array($result_users)) {
$row_user_extension = mysql_fetch_array(mysql_query("select * FROM users_extension WHERE UserID=" . $row_user['ID']))
?>
		<tr class="list_entry" <?php
		if ($row_user['ID'] == @$_GET['highlightserverid']) echo ' style="background-color:#97CBFF;"';
		?>>
			<td class="sortfirstdesc"><?php echo $row_user['ID']; ?></td>
			<td><?php
			if (!($row_user_extension['companyname'] == '' && $row_user_extension['firstname'] == '' && $row_user_extension['address1'] == '' && $row_user_extension['address2'] == '')) {
				echo '<span style="color: green; font-weight: bold">' . $row_user['username'] . "</span>";
			} else {
				echo $row_user['username'];
			}
			?></td>
			<td><?php echo $row_user['email']; ?></td>
			<td><?php echo !empty($row_user_extension['credit'])?money($row_user_extension['credit']):''; ?></td>
			<td title="<?php echo $row_user_extension['regdate']; ?>"><?php echo date("Y-m-d", strtotime($row_user_extension['regdate'])); ?></td>
			<td title="<?php echo $row_user_extension['lastlogindate']; ?>"><?php echo date("Y-m-d", strtotime($row_user_extension['lastlogindate'])); ?></td>
			<td title="<?php echo $row_user_extension['lastloginip']; ?>"><?php
			if (strlen($row_user_extension['lastloginip']) > 15)
			{
				echo substr($row_user_extension['lastloginip'],0,15).'...';
			} else {
				echo $row_user_extension['lastloginip'];
			}
			?></td>
			<td class="list_options">
				<a href="admin_user_balance.php?userid=<?php echo $row_user['ID']; ?>">余额</a> 
				|
				<a href="admin_billing.php?display=user_all&userid=<?php echo $row_user['ID']; ?>">账单</a> 
				|
				<a href="admin_user_info.php?userid=<?php echo $row_user['ID']; ?>">信息</a> 
				|
				<?php if ( 1 == $row_user['locked']) { ?>
				<a href="admin_users.php?action=lock&locked=0&userid=<?php echo $row_user['ID']; ?>&display=<?php echo $_GET['display']; ?>">解锁</a>
				<?php } else { ?>
				<a href="admin_users.php?action=lock&locked=1&userid=<?php echo $row_user['ID']; ?>&display=<?php echo $_GET['display']; ?>">锁定</a>
				<?php } ?>
				| 
				<a href="admin_user_delete.php?userid=<?php echo $row_user['ID']; ?>&display=<?php echo $_GET['display']; ?>">删除</a>
			</td>
		</tr>
		<?php
		}
		?>
</table>
<span style="color:green;font-size:40px;">■</span> 已经填写详细资料的用户 
</div>
<?php @include_once("footer.php") ?>