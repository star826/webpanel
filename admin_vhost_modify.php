<?php
$adminpage = "admin_vhost_modify.php";
@include_once("header.php");
@include_once("function.php");
@include_once("alert_function.php");
@include_once("account_function.php");
$vhostid = empty($_GET['id'])?@$_POST['vhostid']:@$_GET['id'];
if (!is_numeric($vhostid))	ForceDie();
$result_vhosts = mysql_query("select * FROM vhost WHERE ID=" . $vhostid);
if (@mysql_num_rows($result_vhosts) != 1) ForceDie();
$row_vhost = mysql_fetch_array($result_vhosts);
if (@$_POST['action'] == 'vhost_modify')
{
	$vhost_owner = @$_POST['userid'];
	$vhost_status = @$_POST['status'];
	$vhost_nodes = @$_POST['nodes'];
	$vhost_space = @$_POST['space'];
	$vhost_traffic = @$_POST['webtraffic'];
	$vhost_db = @$_POST['db_max'];
	$vhost_subdomain = @$_POST['subdomain_max'];
	$vhost_addon = @$_POST['addon_max'];
	$vhost_ftp = @$_POST['ftp_max'];
	$vhost_cycle = @$_POST['cycle'];
	$vhost_price = @$_POST['price'];
	$vhost_duedate = @$_POST['duedate'];
	$err = array();
	$vhost_owner = UserName2UserID($vhost_owner);
	if (!$vhost_owner)
		$err[] = '用户不存在';
	if(!count($err))
	{
		if(mysql_query("UPDATE vhost SET 
		owner=$vhost_owner,
		status='$vhost_status',
		space=$vhost_space,
		webtraffic=$vhost_traffic,
		db=$vhost_db,
		subdomain=$vhost_subdomain,
		addon=$vhost_addon,
		ftp=$vhost_ftp,
		cycle=$vhost_cycle,
		price=$vhost_price,
		duedate='$vhost_duedate',
		nodes='$vhost_nodes'
		WHERE ID=".$vhostid))
		{
			$_SESSION['msg']['alert-success']='修改虚拟主机配置成功';
			header("Location: admin_vhost_modify.php?id=".$vhostid);
			exit();
		} else {
			$err[]='修改虚拟主机配置失败, 请检查输入的参数!';
		}
	}
	SetErrAlert($err);
}
if ('empty_traffic_used' == @$_GET['action'])
{
		$err = array();
		if(mysql_query("UPDATE vhost SET 
		webtrafficUsed=0
		WHERE ID=".$vhostid))
		{
			$_SESSION['msg']['alert-success']='清空流量使用成功';
			header("Location: admin_vhost_modify.php?id=".$vhostid);
			exit();
		} else {
			$err[]='清空流量使用成功失败!';
		}
		SetErrAlert($err);
}
?>
<div id="page">
<?php EchoAlert(); ?>
<form name="config_save" id="config_save" action="" method="post" onsubmit="document.getElementById('confirmed').disabled = true;">
<table class="list">
	<tr>
		<th colspan="3">虚拟主机编辑</th>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">所有者</td>
		<td>
		<input name="userid" id="userid" type="text" value="<?php echo UserID2UserName($row_vhost['owner']); ?>" maxlength="" size="18" autocomplete="off" required title="保持不变或输入新的用户名进行虚拟主机所有者转让" />
		<br />
		<a href="admin_user_info.php?userid=<?php echo $row_vhost['owner']; ?>">查看该用户信息</a>
		</td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">虚拟主机ID</td>
		<td><span style="color: green;"><?php echo ($row_vhost['ID']); ?> <a href="vhost_panel.php?id=<?php echo $row_vhost['ID']; ?>">进入虚拟主机控制面板</a> | <a href="vhost_remove.php?id=<?php echo $row_vhost['ID']; ?>">删除该主机</a></span><br /></td>
		<td class="hint"></td>
	</tr>
<?php if (!empty($row_vhost['serverID'])) { ?>
	<tr class="list_entry">
		<td class="table_form_header">服务器ID</td>
		<td><span style="color: green;"><?php echo ($row_vhost['serverID']); ?> <a href="admin_vhost_server_conf.php?id=<?php echo $row_vhost['serverID']; ?>&action=edit">查看该服务器信息</a> | <a href="admin_vhost_servers.php?highlightserverid=<?php echo $row_vhost['serverID']; ?>">在服务器列表中高亮选择</a></span><br /></td>
		<td class="hint"></td>
	</tr>
<?php } ?>
	<tr class="list_entry">
		<td class="table_form_header">主机状态</td>
		<td>
			<select name="status" id="status" size="1">
				<option value="Stop" <?php if ($row_vhost['status']=='Stop') echo 'selected="selected"'; ?>>停止运行</option>
				<option value="Available" <?php if ($row_vhost['status']=='Available') echo 'selected="selected"'; ?>>未初始化</option>
				<option value="Running" <?php if ($row_vhost['status']=='Running') echo 'selected="selected"'; ?>>运行中</option>
				<option value="Unpaid" <?php if ($row_vhost['status']=='Unpaid') echo 'selected="selected"'; ?>>未付款</option>
				<option value="Remove" <?php if ($row_vhost['status']=='Remove') echo 'selected="selected"'; ?>>删除中状态</option>
				
			</select>
		</td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">主机方案</td>
		<td><span style="color: green"><?php echo ($row_vhost['planname']); ?></span><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">主域绑定</td>
		<td><span style="color: green"><?php echo ($row_vhost['domain']); ?></span><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">网站根目录</td>
		<td><span style="color: green"><?php echo empty($row_vhost['root'])?'Unallocated':$row_vhost['root']; ?></span><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">主IP</td>
		<td><span style="color: green"><?php echo ($row_vhost['ip']); ?></span><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">地理位置</td>
		<td><span style="color: green"><?php echo ($row_vhost['location']); ?></span><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">可用节点</td>
		<td>
			<input name="nodes" id="nodes" type="text" value="<?php echo $row_vhost['nodes']; ?>" maxlength="" size="18" autocomplete="off" required />
		</td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">备份周期</td>
		<td><span style="color: green"><?php echo ($row_vhost['backup']); ?> 天</span><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">空间大小 (MB)</td>
		<td>
		<input name="space" id="space" type="text" value="<?php echo $row_vhost['space']?$row_vhost['space']:'0'; ?>" maxlength="" size="18" autocomplete="off" required />
		</td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">已用空间</td>
		<td><span style="color: green"><?php echo $row_vhost['spaceUsed']?$row_vhost['spaceUsed']:'0'; ?> Bytes</span><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">流量大小 (GB)</td>
		<td>
			<input name="webtraffic" id="webtraffic" type="text" value="<?php echo $row_vhost['webtraffic']?$row_vhost['webtraffic']:'0'; ?>" maxlength="" size="18" autocomplete="off" required />
		</td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">已用流量</td>
		<td><span style="color: green"><?php echo $row_vhost['webtrafficUsed']?$row_vhost['webtrafficUsed']:'0'; ?> Bytes <a href="admin_vhost_modify.php?id=<?php echo $vhostid; ?>&action=empty_traffic_used">清零</a></span><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">MySQL (个)</td>
		<td>
			<input name="db_max" id="db_max" type="text" value="<?php echo ($row_vhost['db']); ?>" maxlength="" size="18" autocomplete="off" required />
		</td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">子域绑定 (个)</td>
		<td>
			<input name="subdomain_max" id="subdomain_max" type="text" value="<?php echo ($row_vhost['subdomain']); ?>" maxlength="" size="18" autocomplete="off" required />
		</td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">附加域绑定 (个)</td>
		<td>
			<input name="addon_max" id="addon_max" type="text" value="<?php echo ($row_vhost['addon']); ?>" maxlength="" size="18" autocomplete="off" required />
		</td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">FTP (个)</td>
		<td>
			<input name="ftp_max" id="ftp_max" type="text" value="<?php echo ($row_vhost['ftp']); ?>" maxlength="" size="18" autocomplete="off" required />
		</td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">付款周期 (月)</td>
		<td>
			<input name="cycle" id="cycle" type="text" value="<?php echo ($row_vhost['cycle']); ?>" maxlength="" size="18" autocomplete="off" required />
		</td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">周期价格</td>
		<td>
			<input name="price" id="price" type="text" value="<?php echo money($row_vhost['price']); ?>" maxlength="" size="18" autocomplete="off" required />
		</td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">到期时间</td>
		<td>
		<input name="duedate" id="duedate" type="text" value="<?php echo ($row_vhost['duedate']); ?>" maxlength="" size="18" autocomplete="off" required />
		</td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td></td>
		<td><input tabindex="3" class="button" type="submit" name="confirmed" id="confirmed" value="确认"></td>
		<td class="hint"></td>
	</tr>
</table>
<input type="hidden" name="vhostid" value="<?php echo $_GET['ID']; ?>" />
<input type="hidden" name="action" value="vhost_modify" />
</form>
</div>
<?php @include_once("footer.php") ?>