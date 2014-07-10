<?php
$adminpage = "admin_status.php";
@include_once("header.php");
@include_once("function.php");
@include_once("alert_function.php");
if (@$status_display == "Today") {
	$result_users = mysql_query("select * FROM users_extension WHERE to_days(regdate) = to_days(now());");
	$result_users_recharge = mysql_query("select SUM(amount) sum FROM users_billing WHERE type=0 AND paid=1 AND description LIKE '%支付宝交易号%' AND to_days(`date`) = to_days(now()); ");
	$result_users_recharge_fail = mysql_query("select SUM(amount) sum FROM users_billing WHERE type=0 AND paid=0 AND to_days(`date`) = to_days(now()); ");
	$result_users_paid = mysql_query("select SUM(amount) sum FROM users_billing WHERE type=1 AND paid=1 AND to_days(`date`) = to_days(now()); ");
	$result_users_unpaid = mysql_query("select SUM(amount) sum FROM users_billing WHERE type=1 AND paid=0 AND to_days(`date`) = to_days(now()); ");
} elseif (@$status_display == "Yesterday") {
	$result_users = mysql_query("select * FROM users_extension WHERE to_days(NOW()) - to_days(regdate) <= 1;");
	$result_users_recharge = mysql_query("select SUM(amount) sum FROM users_billing WHERE type=0 AND paid=1 AND description LIKE '%支付宝交易号%' AND to_days(NOW()) - to_days(`date`) <= 1; ");
	$result_users_recharge_fail = mysql_query("select SUM(amount) sum FROM users_billing WHERE type=0 AND paid=0 AND to_days(NOW()) - to_days(`date`) <= 1; ");
	$result_users_paid = mysql_query("select SUM(amount) sum FROM users_billing WHERE type=1 AND paid=1 AND to_days(NOW()) - to_days(`date`) = 1; ");
	$result_users_unpaid = mysql_query("select SUM(amount) sum FROM users_billing WHERE type=1 AND paid=0 AND to_days(NOW()) - to_days(`date`) = 1; ");
} elseif (@$status_display == "Week") {
	$result_users = mysql_query("select * FROM users_extension WHERE DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= date(regdate);");
	$result_users_recharge = mysql_query("select SUM(amount) sum FROM users_billing WHERE type=0 AND paid=1 AND description LIKE '%支付宝交易号%' AND DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= date(date); ");
	$result_users_recharge_fail = mysql_query("select SUM(amount) sum FROM users_billing WHERE type=0 AND paid=0 AND DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= date(date); ");
	$result_users_paid = mysql_query("select SUM(amount) sum FROM users_billing WHERE type=1 AND paid=1 AND DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= date(date); ");
	$result_users_unpaid = mysql_query("select SUM(amount) sum FROM users_billing WHERE type=1 AND paid=0 AND DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= date(date); ");
} elseif (@$status_display == "Last30Days") {
	$result_users = mysql_query("select * FROM users_extension WHERE DATE_SUB(CURDATE(), INTERVAL 30 DAY) <= date(regdate);");
	$result_users_recharge = mysql_query("select SUM(amount) sum FROM users_billing WHERE type=0 AND paid=1 AND description LIKE '%支付宝交易号%' AND DATE_SUB(CURDATE(), INTERVAL 30 DAY) <= date(date); ");
	$result_users_recharge_fail = mysql_query("select SUM(amount) sum FROM users_billing WHERE type=0 AND paid=0 AND DATE_SUB(CURDATE(), INTERVAL 30 DAY) <= date(date); ");
	$result_users_paid = mysql_query("select SUM(amount) sum FROM users_billing WHERE type=1 AND paid=1 AND DATE_SUB(CURDATE(), INTERVAL 30 DAY) <= date(date); ");
	$result_users_unpaid = mysql_query("select SUM(amount) sum FROM users_billing WHERE type=1 AND paid=0 AND DATE_SUB(CURDATE(), INTERVAL 30 DAY) <= date(date); ");
} elseif (@$status_display == "All") {
	$result_users = mysql_query("select * FROM users_extension");
	$result_users_recharge = mysql_query("select SUM(amount) sum FROM users_billing WHERE type=0 AND paid=1 AND description LIKE '%支付宝交易号%'");
	$result_users_recharge_fail = mysql_query("select SUM(amount) sum FROM users_billing WHERE type=0 AND paid=0");
	$result_users_paid = mysql_query("select SUM(amount) sum FROM users_billing WHERE type=1 AND paid=1");
	$result_users_unpaid = mysql_query("select SUM(amount) sum FROM users_billing WHERE type=1 AND paid=0");
} else {
	ForceDie();
}
?>
<div id="page">
<?php EchoAlert(); ?>
<form name="config_save" id="config_save" action="" method="post" onsubmit="document.getElementById('confirmed').disabled = true;">
<table class="list">
	<tr>
		<th colspan="3">统计</th>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">注册用户数</td>
		<td><span style="color: green"><?php
		echo mysql_num_rows($result_users);
		?></span><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">用户充值成功金额</td>
		<td><span style="color: green"><?php
		$users_paid = mysql_fetch_assoc($result_users_recharge);
		echo money($users_paid['sum']);
		?></span><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">用户充值失败金额</td>
		<td><span style="color: green"><?php
		$users_paid = mysql_fetch_assoc($result_users_recharge_fail);
		echo money($users_paid['sum']);
		?></span><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">用户已支付订单金额</td>
		<td><span style="color: green"><?php
		$users_paid = mysql_fetch_assoc($result_users_paid);
		echo money($users_paid['sum']);
		?></span><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">用户未支付订单金额</td>
		<td><span style="color: green"><?php
		$users_paid = mysql_fetch_assoc($result_users_unpaid);
		echo money($users_paid['sum']);
		?></span><br /></td>
		<td class="hint"></td>
	</tr>
</table>
</div>
<?php @include_once("footer.php") ?>