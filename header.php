<?php
@include_once("session.php");
@include_once("language.php");
@include_once("config.php");
if (empty($page) && empty($adminpage)) ForceDie();
ForceLogin();
if (mysql_num_rows(mysql_query('select * from users where ID=' . $_SESSION['UserID'])) == 0){$_SESSION = array();session_destroy();header("Location: login.php");exit();}
if (!empty($adminpage))	ForceAdmin();
if (@$NoHeader) return;
@include_once("function.php");
$page_start_time = get_microtime();
?>
<!DOCTYPE html>
<html dir="ltr" lang="zh-CN">
<head>
<meta charset="UTF-8" />
<title><?php echo $PanelName;?> <?php echo $PanelSubName;?></title>
<meta http-equiv="X-UA-Compatible" content="IE=9" />
<link rel="stylesheet" type="text/css" href="css/header.css">
<link rel="stylesheet" type="text/css" href="css/manager.css">
<link rel="stylesheet" type="text/css" href="css/css3.css">
<?php if ('vhost_add.php' == @$page || 'vps_add.php' == @$page) { ?>
<link rel="stylesheet" type="text/css" href="css/signup.css">
<?php } ?>
<script src="js/prototype.js" type="text/javascript"></script>
<script src="js/tablekit.js" type="text/javascript"></script>
<?php echo @$header_addition; ?>
</head>
<body>
<div class="header">
	<div class="container">
		<p class="header_logo top left"><?php echo $PanelName;?> <?php echo $PanelSubName;?></p>
		<div class="header_profile top right"></div>
		<div class="menu_container">
			<ul class="menu">
					<?php if (IsAdmin() && !empty($adminpage)) { ?>
						<li><a href="admin_vhost.php?display=main"><?php echo $lang['vhosts']; ?></a></li>
						<li><a href="admin_users.php?display=todayreg"><?php echo $lang['users']; ?><?php
						$result = mysql_query("select users.* FROM users,users_extension WHERE to_days(users_extension.regdate) = to_days(now()) AND users_extension.UserID = users.ID;");
						if (@mysql_num_rows($result) > 0)
							echo '<span class="notification blue">'.mysql_num_rows($result).'</span>';
						?></a></li>
						<li><a href="admin_billing.php?display=recharge_done"><?php echo $lang['billing']; ?><?php
						$result = mysql_query("select * FROM users_billing WHERE type=0 AND paid=1 AND description LIKE '%支付宝交易号%' ");
						if (@mysql_num_rows($result) > 0)
							echo '<span class="notification yellow">'.mysql_num_rows($result).'</span>';
						?></a></li>
						<li><a href="admin_status.php?display=Today"><?php echo $lang['statistics']; ?></a></li>
						<li><a href="admin_support.php"><?php echo $lang['support']; ?><?php
						$result_tickets = mysql_query("select * FROM tickets WHERE Status!='CLOSED' AND LastUpdatedBy!='' ORDER BY `LastUpdated` DESC ");
						if (mysql_num_rows($result_tickets) > 0)
							echo '<span class="notification yellow">'.mysql_num_rows($result_tickets).'</span>';
						?></a></li>
						<li><a href="admin_system.php">系统</a></li>
					<?php } else { ?>
						<li><a href="profile.php"><?php echo $_SESSION['username']; ?></a></li>
						<li><a href="vhost.php"><?php echo $lang['vhosts']; ?><?php
						$result = mysql_query("select * FROM vhost WHERE status='Unpaid' AND owner=".$_SESSION['UserID']);
						if (@mysql_num_rows($result) > 0)
							echo '<span class="notification pink">'.mysql_num_rows($result).'</span>';
						else { 
							$result = mysql_query("select * FROM vhost WHERE status='Available' AND owner=".$_SESSION['UserID']);
							if (@mysql_num_rows($result) > 0)
								echo '<span class="notification blue">'.mysql_num_rows($result).'</span>';
						}
						?></a></li>
						<li><a href="account.php"><?php echo $lang['account']; ?><?php
						$result = mysql_query("select * FROM users_billing WHERE type=1 AND paid=0 AND UserID=".$_SESSION['UserID']);
						if (@mysql_num_rows($result) > 0)
							echo '<span class="notification pink">'.mysql_num_rows($result).'</span>';
						?></a></li>
						<li><a href="support_index.php"><?php echo $lang['support']; ?><?php
						$result_tickets = mysql_query("select * FROM tickets WHERE OpenedBy=".$_SESSION['UserID']." AND Status!='CLOSED' AND LastUpdatedBy!='' ORDER BY `LastUpdated` DESC ");
						if (mysql_num_rows($result_tickets) > 0)
							echo '<span class="notification yellow">'.mysql_num_rows($result_tickets).'</span>';
						?></a></li>
						<li><a href="logout.php"><?php echo $lang['log_out']; ?></a></li>
					<?php } ?>
			</ul>
		</div>
	</div>
</div>
<br />
<?php
	if (@substr($page,0,7) == "account") @include_once("account_subtabs.php");
	if (@substr($page,0,5) == "vhost" &&
	$page != "vhost_add.php"  &&
	$page != "vhost.php" &&
	$page != "vhost_remove.php" &&
	$page != "vhost_tos.php"
	) @include_once("vhost_subtabs.php");
	if (@substr($adminpage,0,11) == "admin_vhost") 
		@include_once("admin_vhost_subtabs.php");
	if (@substr($adminpage,0,9) == "admin_vps") 
		@include_once("admin_vps_subtabs.php");
	if (@substr($adminpage,0,10) == "admin_user") 
		@include_once("admin_user_subtabs.php");
	if (@substr($adminpage,0,13) == "admin_billing") 
		@include_once("admin_billing_subtabs.php");
	if (@substr($adminpage,0,12) == "admin_status") 
		@include_once("admin_status_subtabs.php");
	if (@substr($adminpage,0,12) == "admin_linode") 
		@include_once("admin_linode_subtabs.php");
?>