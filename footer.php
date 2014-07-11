<?php
@include_once("session.php");
if (empty($page) && empty($adminpage)) ForceDie();
ForceLogin();
?>
<br />
<p style="color:#000000;font-size:14px;">&copy; 2014 Web Control Panel For <a href="http://www.lnmpv.com">LNMPV</a> Processed in <?php echo round(get_microtime()-$page_start_time,6); ?> second(s) <br /><br />
<?php if (IsAdmin()) { ?>
 <?php
if (empty($adminpage)){
	echo '<a href="admin_system.php" style="color:#FF0000;font-size:14px;">转到-'.$lang['admin_panel'].'</a>';
} else {
	echo '<a href="vhost.php" style="color:#FF0000;font-size:14px;">转到-'.$lang['user_panel'].'</a>';
}
?></p>
<?php } ?>
<?php echo $statisticalCode; ?>
</body>
</html>
