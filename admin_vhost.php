<?php
$adminpage = "admin_vhost.php";
@include_once("header.php");
@include_once("function.php");
@include_once("alert_function.php");
@include_once("account_function.php");
ForceAdmin();
?>
<div id="page">
<?php EchoAlert(); ?>
	<table class="list sortable">
		<thead>
				<tr>
					<th class="sortfirstdesc">所有者</th>
					<th>绑定</th>
					<th>节点</th>
					<th>方案</th>
					<th>状态</th>
					<th>到期</th>
					<th>硬盘使用</th>
					<th>流量使用</th>
					<th class="nosort" style="text-align: center" align="center">选项</th>
				</tr>
		</thead>
		<tbody>
<?php
if("subdomain" == $vhost_display){
	$result = mysql_query("select * FROM vhost_nginx_conf WHERE type='subdomain'" );
} elseif ("addon" == $vhost_display) {
	$result = mysql_query("select * FROM vhost_nginx_conf WHERE type='addon'" );
} elseif ("main" == $vhost_display) {
	$result = mysql_query("select * FROM vhost_nginx_conf WHERE type='main'" );
} elseif ("all" == $vhost_display) {
	$result = mysql_query("select * FROM vhost" );
} else {
	$result = mysql_query("select * FROM vhost WHERE status='".$_GET['display']."'" );
}
while($row = mysql_fetch_array($result)) {
if("subdomain" == $vhost_display || "addon" == $vhost_display || "main" == $vhost_display){
	$result2 = mysql_query("select * FROM vhost WHERE ID=" . $row['vhostID'] );
	$row2 = mysql_fetch_array($result2);
	if (empty($row['owner'])) $row['owner'] = $row2['owner'];
	if (empty($row['domain'])) $row['domain'] = $row['server_name'];
	if (empty($row['status'])) $row['status'] = $row2['status'];
	if (empty($row['duedate'])) $row['duedate'] = $row2['duedate'];
	if (empty($row['space'])) $row['space'] = $row2['space'];
	if (empty($row['spaceUsed'])) $row['spaceUsed'] = $row2['spaceUsed'];
	if (empty($row['webtraffic'])) $row['webtraffic'] = $row2['webtraffic'];
	if (empty($row['webtrafficUsed'])) $row['webtrafficUsed'] = $row2['webtrafficUsed'];
	if (empty($row['planname'])) $row['planname'] = $row2['planname'];
	$row['serverID'] = $row2['serverID'];
	$row['ID'] = $row2['ID'];
}
?>
			<tr class="list_entry">
				<td><?php echo UserID2UserName($row['owner']); ?></td>
				<td><?php 
				if ("Unallocated" != $row['domain'])
					echo '<a href="http://'.$row['domain'].'" target="_blank">'.$row['domain'].'</a>';
				?></td>
				<td><?php
				$server_alias = mysql_query("select * FROM vhost_servers WHERE ID=".$row['serverID']);
				if (@mysql_num_rows($server_alias) > 0) {
				$row_server_alias = mysql_fetch_array($server_alias);
				echo '<a href="admin_vhost_servers.php?highlightserverid='.$row['serverID'].'">'.$row_server_alias['alias'].'</a>';
				}
				?></td>
				<td><a href=""><?php echo $row['planname']; ?></td>
				<td><?php if ($row['status']=='Running') echo '<font color="#00BB00">运行中</font>'; if ($row['status']=='Stop') echo '<font color="red">已停止</font>'; if ($row['status']=='Available') echo '<font color="#46A3FF">未初始化</font>';if ($row['status']=='Unpaid') echo '<font color="red">未付款</font>';if ($row['status']=='Remove') echo '<font color="#777777">删除中</font>';?></td>
				<td>
				<?php if (!empty($row['order'])) { ?>
				<a href="account_invoice.php?id=<?php echo $row['orderID']; ?>"><?php echo $row['duedate']; ?></a>
				<?php } else { ?>
				<?php echo $row['duedate']; ?>
				<?php } ?>
				</td>
				<td><?php
					echo round((($row['spaceUsed']/1024/1024) / $row['space']) * 100, 0).'%';
				?></td>
				<td>
				<?php echo @round((($row['webtrafficUsed']) / ($row['webtraffic'] * 1024 * 1024 * 1024)) * 100, 0) . '%'; ?>
				</td>
				<td class="list_options" nowrap>
				<a href="admin_vhost_modify.php?id=<?php echo $row['ID']; ?>">修改配置</a>
				|
				<a href="vhost_panel.php?id=<?php echo $row['ID']; ?>">控制面板</a>
				| 
				<a href="vhost_remove.php?id=<?php echo $row['ID']; ?>">删除主机</a>
				</td>
			</tr>
<?php } ?>
		</tbody>
	</table>
	<br />
</div>
<?php @include_once("footer.php") ?>