<?php
$adminpage = "admin_vhost_server_ips.php";
@include_once("header.php");
@include_once("function.php");
@include_once("alert_function.php");
$serverid = empty($_GET['id'])?@$_POST['id']:@$_GET['id'];
if (!is_numeric($serverid))	ForceDie();
$result_servers = mysql_query("select * FROM vhost_servers WHERE ID=".$serverid);
if (@mysql_num_rows($result_servers) != 1) ForceDie(); // 该节点服务器不存在
$err = array();
if (@$_POST['action'] == 'addip')
{
	$IPAddress = $_POST['IPAddress'];
	if(filter_var($IPAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
		$IPv = '6';
	} elseif(filter_var($IPAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
		$IPv = '4';
	} else {
		$err[] = '请输入正确的IP地址';
	}
	$result_server_ips = mysql_query("select * FROM vhost_server_ips WHERE Address='".$IPAddress."' AND serverID=" . $serverid);
	if (@mysql_num_rows($result_server_ips) >= 1) $err[] = '您输入的IP地址已存在';
		if (!count($err)) {
			$Private = @$_POST['private']=='yes'?'1':'0';		
			if(mysql_query("INSERT INTO vhost_server_ips (serverID, IPv, Address, Private) VALUES ($serverid, $IPv, '$IPAddress', $Private)"))
				$_SESSION['msg']['alert-success']='添加IP地址到节点成功';
			else 
				$err[] = '添加IP地址到节点失败';
		}
		if (count($err)) SetErrAlert($err);
		header("Location: admin_vhost_server_ips.php?id=".$serverid);
		exit();
}
if (@$_GET['action'] == 'delip')
{
		if (@is_numeric($_GET['ip_id']) && !empty($_GET['ip_id']))
		if(mysql_query("DELETE FROM vhost_server_ips WHERE ID=".$_GET['ip_id']))
			$_SESSION['msg']['alert-success']='删除IP地址成功';
		else
			$err[] = '删除IP地址失败';
		if (count($err)) SetErrAlert($err);
		header("Location: admin_vhost_server_ips.php?id=".$serverid);
		exit();
}
if (count($err)) SetErrAlert($err);
?>
<div id="page">
<p class='breadcrumb'><strong>虚拟主机节点服务器IP地址池管理</strong></p>
<?php EchoAlert(); ?>
<table class="list sortable">
	<tr class="list_head">
		<th class="sortfirstdesc">ID</th>
		<th>协议</th>
		<th>地址</th>
		<th>虚拟主机</th>
		<th class="nosort" style="text-align: right">选项</th>
	</tr>
		<?php
			$result_server_ips = mysql_query("select * FROM vhost_server_ips WHERE serverID=".$serverid);
			while($row_server_ips = mysql_fetch_array($result_server_ips)) {
		?>
		<tr class="list_entry" <?php
		if ($row_server_ips['ID'] == @$_GET['highlightserverip']) echo ' style="background-color:#97CBFF;"';
		?>>
			<td class="sortfirstdesc"><?php echo $row_server_ips['ID']; ?></td>
			<td><?php echo $row_server_ips['IPv']=='6'?'IPv6':'IPv4'; ?></td>
			<td><?php echo $row_server_ips['Address']; ?></td>
			<td><?php
			if ($row_server_ips['Private']=='0')
			{
				echo '共享IP';
			} elseif (empty($row_server_ips['VhostID'])) {
				// echo '<a href="#">分配</a>';
			} else {
				$result_vhosts = mysql_query("select * FROM vhost WHERE ID=" . $row_server_ips['VhostID']);
				if (@mysql_num_rows($result_vhosts) != 1) {
					echo '<span style="color:red">分配此IP的虚拟主机已被删除</span>';
				} else {
					$row_vhost = mysql_fetch_array($result_vhosts);
					echo '<a href="admin_vhost_modify.php?id='.$row_vhost['ID'].'">'.$row_vhost['domain'].'</a>';
				}
			}
			?></td>
			<td class="list_options" nowrap style="text-align: right">
			<?php 
			if (($row_server_ips['Private']=='1' && empty($row_server_ips['VhostID'])) || ($row_server_ips['Private']=='1' && !empty($row_server_ips['VhostID']))) {
			?>
				<a href="admin_vhost_server_ip_modify.php?id=<?php echo $row_server_ips['ID']; ?>">分配</a> | 
			<?php } ?>
				<a href="admin_vhost_server_ips.php?action=delip&id=<?php echo $serverid; ?>&ip_id=<?php echo $row_server_ips['ID']; ?>" onClick="return confirm('确认删除这个IP地址?')">删除</a> 
			</td>
		</tr>
		<?php
		}
		?>
	<tr class="list_entry">
	<form name="config_save" id="config_save" action="" method="post">
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td><input name="IPAddress" id="IPAddress" type="text" tabindex="1" autofocus="autofocus" autocomplete="off" placeholder="请输入IPv4或IPv6地址." required /></td>
		<td><select name="private" id="private" size="1">
			<option value="yes">独立IP</option>
			<option value="no">共享IP</option>
			</select></td>
		<td align="center"><input class="button" type="submit" name="doit" tabindex="4" value="添加IP"></td>
		<input type="hidden" name="id" value="<?php echo $serverid; ?>" />
		<input type="hidden" name="action" value="addip" />
		</form>
	</tr>
</table>
</div>
<?php @include_once("footer.php") ?>