<?php
@include_once("session.php");
if (empty($adminpage)) ForceDie();
ForceAdmin();
$user_display = @$_GET['display'];
?>
<div id="subtabs">
	<a class="<?php if($user_display == "all" && $adminpage == 'admin_users.php') echo "active"; ?>"  href="admin_users.php?display=all">所有用户</a>
	<a class="<?php if($user_display == "todayreg" && $adminpage == 'admin_users.php') echo "active"; ?>"  href="admin_users.php?display=todayreg">今日注册</a>
	<a class="<?php if($user_display == "yesterdayreg" && $adminpage == 'admin_users.php') echo "active"; ?>"  href="admin_users.php?display=yesterdayreg">昨日注册</a>
	<a class="<?php if($user_display == "balance" && $adminpage == 'admin_users.php') echo "active"; ?>"  href="admin_users.php?display=balance">付费用户</a>
	<?php if($adminpage == "admin_user_balance.php") { ?>
	<a class="active">用户余额</a>
	<?php } ?>
	<?php if($adminpage == "admin_user_info.php") { ?>
	<a class="active">用户信息</a>
	<?php } ?>
	<?php if($adminpage == "admin_user_delete.php") { ?>
	<a class="active">删除用户</a>
	<?php } ?>
</div>