<?php
@include_once("session.php");
if (empty($adminpage)) ForceDie();
ForceAdmin();
$vhost_display = @$_GET['display'];
?>
<div id="subtabs">
	<a class="<?php if(@$vhost_display == "all") echo "active"; ?>"  href="admin_vhost.php?display=all">所有虚拟主机</a>
	<a class="<?php if(@$vhost_display == "main") echo "active"; ?>" href="admin_vhost.php?display=main">主域</a>
	<a class="<?php if(@$vhost_display == "subdomain") echo "active"; ?>" href="admin_vhost.php?display=subdomain">子域</a>
	<a class="<?php if(@$vhost_display == "addon") echo "active"; ?>" href="admin_vhost.php?display=addon">附加域</a>
	<a class="<?php if(@$vhost_display == "Running") echo "active"; ?>" href="admin_vhost.php?display=Running">运行</a>
	<a class="<?php if(@$vhost_display == "Stop") echo "active"; ?>" href="admin_vhost.php?display=Stop">停止</a>
	<a class="<?php if(@$vhost_display == "Available") echo "active"; ?>" href="admin_vhost.php?display=Available">未初始化</a>
	<a class="<?php if(@$vhost_display == "Unpaid") echo "active"; ?>" href="admin_vhost.php?display=Unpaid">未付款</a>
	<a class="<?php if($adminpage == "admin_vhost_servers.php" || $adminpage == "admin_vhost_server_ips.php" || $adminpage == 'admin_vhost_server_conf.php' || $adminpage == "admin_vhost_server_ip_modify.php") echo "active"; ?>" href="admin_vhost_servers.php">节点</a>
	<a class="<?php if($adminpage == "admin_vhost_plans.php" || $adminpage == "admin_vhost_plan_modify.php") echo "active"; ?>" href="admin_vhost_plans.php">主机方案</a>
	<a class="<?php if($adminpage == "admin_vhost_tasks.php") echo "active"; ?>" href="admin_vhost_tasks.php">任务计划</a>		
</div>


