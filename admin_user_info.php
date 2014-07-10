<?php
$adminpage = "admin_user_info.php";
@include_once("header.php");
@include_once("function.php");
@include_once("alert_function.php");
$userid = empty($_GET['userid'])?@$_POST['userid']:@$_GET['userid'];
if (!is_numeric($userid))	ForceDie();
$result_users = mysql_query("select * FROM users WHERE ID=" . $userid);
if (@mysql_num_rows($result_users) != 1) ForceDie();
$row_user = mysql_fetch_array($result_users);
$row_user_extension = mysql_fetch_array(mysql_query("select * FROM users_extension WHERE UserID=" . $row_user['ID']));
?>
<div id="page">
<?php EchoAlert(); ?>
<form name="config_save" id="config_save" action="" method="post" onsubmit="document.getElementById('confirmed').disabled = true;">
<table class="list">
	<tr>
		<th colspan="3">用户信息</th>
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
		<td class="table_form_header">API Key</td>
		<td><span style="color: green"><?php echo $row_user['api_key']; ?></span><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">当前余额</td>
		<td><a href="admin_user_balance.php?userid=<?php echo $row_user_extension['UserID']; ?>"><span style="color: green; font-weight: bold"><?php echo money($row_user_extension['credit']); ?> 元</span></a><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">公司名</td>
		<td><?php echo ($row_user_extension['companyname']); ?> <br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">姓</td>
		<td><?php echo ($row_user_extension['firstname']); ?> <br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">名</td>
		<td><?php echo ($row_user_extension['lastname']); ?> <br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">地址1</td>
		<td><?php echo ($row_user_extension['address1']); ?> <br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">地址2</td>
		<td><?php echo ($row_user_extension['address2']); ?> <br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">城市</td>
		<td><?php echo ($row_user_extension['city']); ?> <br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">省</td>
		<td><?php echo ($row_user_extension['state']); ?> <br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">邮编</td>
		<td><?php echo ($row_user_extension['zip']); ?> <br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">国家</td>
		<td><?php echo ($row_user_extension['country']); ?> <br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">电话1</td>
		<td><?php echo ($row_user_extension['phone1']); ?> <br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">电话2</td>
		<td><?php echo ($row_user_extension['phone2']); ?> <br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">QQ</td>
		<td><?php echo ($row_user_extension['qq']); ?> <br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">注册时间</td>
		<td><?php echo ($row_user_extension['regdate']); ?> <br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">注册IP</td>
		<td><?php echo ($row_user_extension['regip']); ?> <br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">最后登录时间</td>
		<td><?php echo ($row_user_extension['lastlogindate']); ?> <br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">最后登录IP</td>
		<td><?php echo ($row_user_extension['lastloginip']); ?> <br /></td>
		<td class="hint"></td>
	</tr>
</table>
</div>
<?php @include_once("footer.php") ?>