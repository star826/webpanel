<?php
@include_once("session.php");
if (empty($page) && empty($adminpage)) ForceDie();
ForceLogin();
?>
<div id="subtabs">
	<a class="<?php if($page == "vhost_panel.php" || $page == "vhost_panel_conf.php") echo "active"; ?>"  href="vhost_panel.php?id=<?php echo $_GET['id']; ?>">控制面板</a>
	<a class="<?php if($page == "vhost_panel_ftp.php") echo "active"; ?>" href="vhost_panel_ftp.php?id=<?php echo $_GET['id']; ?>">FTP</a>
	<a class="<?php if($page == "vhost_panel_mysql.php") echo "active"; ?>" href="vhost_panel_mysql.php?id=<?php echo $_GET['id']; ?>">MYSQL</a>
	<a class="<?php if($page == "vhost_panel_ip.php") echo "active"; ?>" href="vhost_panel_ip.php?id=<?php echo $_GET['id']; ?>">IP</a>
	<a class="<?php if($page == "vhost_panel_install.php") echo "active"; ?>" href="vhost_panel_install.php?id=<?php echo $_GET['id']; ?>">安装</a>
	<a class="<?php if($page == "vhost_panel_unzip.php") echo "active"; ?>" href="vhost_panel_unzip.php?id=<?php echo $_GET['id']; ?>">解压</a>
	<a class="<?php if($page == "vhost_panel_chmod.php") echo "active"; ?>" href="vhost_panel_chmod.php?id=<?php echo $_GET['id']; ?>">权限</a>
	<a class="<?php if($page == "vhost_panel_traffic.php") echo "active"; ?>" href="vhost_panel_traffic.php?id=<?php echo $_GET['id']; ?>">流量</a>
</div>