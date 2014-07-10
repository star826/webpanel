<?php
@include_once("session.php");
if (empty($adminpage)) ForceDie();
ForceAdmin();
$status_display = @$_GET['display'];
?>
<div id="subtabs">
	<a class="<?php if(@$status_display == "Today" && empty($vhost_display)) echo "active"; ?>"  href="admin_status.php?display=Today">今日</a>
	<a class="<?php if(@$status_display == "Yesterday") echo "active"; ?>" href="admin_status.php?display=Yesterday">昨日</a>
	<a class="<?php if(@$status_display == "Week") echo "active"; ?>" href="admin_status.php?display=Week">一星期内</a>
	<a class="<?php if(@$status_display == "Last30Days") echo "active"; ?>" href="admin_status.php?display=Last30Days">一个月内</a>
	<a class="<?php if(@$status_display == "All") echo "active"; ?>" href="admin_status.php?display=All">全部</a>			
</div>