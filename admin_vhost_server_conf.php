<?php
$adminpage = "admin_vhost_server_conf.php";
@include_once("header.php");
@include_once("function.php");
@include_once("alert_function.php");
$serverid = empty($_GET['id'])?@$_POST['id']:@$_GET['id'];
$alias                  = @$_POST['alias'];
$passwd                 = @$_POST['passwd'];
$port                   = @$_POST['port'];
$mysqlpasswd            = @$_POST['mysqlpasswd'];
$mainip                 = @$_POST['mainip'];
$location               = @$_POST['location'];
$hidden                 = @$_POST['hidden'];
$vhostTotal             = @$_POST['vhostTotal'];
$vhostFree              = @$_POST['vhostFree'];
$err = array();
if (@$_POST['action'] == 'add' || @$_POST['action'] == 'edit')
{
	if (empty($alias)) {
		$err[] = '请输入节点别名';
	}
	if (empty($passwd)) {
		$err[] = '请输入SSH Root密码';
	}
	if (empty($mysqlpasswd)) {
		$err[] = '请输入MySQL Root密码';
	}
	if (@!filter_var($mainip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
		$err[] = '请输入节点主IP';
	}
	if (empty($port)) {
		$err[] = '请输入SSH端口默认22';
	}
	if (empty($location)) {
		$err[] = '请输入节点所在位置';
	}
	if (@$hidden != '0' && @$hidden != '1') {
		$err[] = '请选择是否隐藏节点';
	}
	if (empty($vhostTotal) || !is_numeric($vhostTotal)) {
		$err[] = '请输入最大虚拟主机数量';
	}
	if (empty($vhostFree) || !is_numeric($vhostFree)) {
		$err[] = '请输入空闲虚拟主机数量';
	}
}
if (@$_POST['action'] == 'add' && !count($err)){
	if(mysql_query(" INSERT INTO vhost_servers(alias,root,passwd,port,mysqlpasswd,ip,location,hidden,vhostTotal,vhostFree,downtime) values (
				'" . $alias . "',
				'root',
				'" . $passwd . "',
		        '" . $port . "',
				'" . $mysqlpasswd . "',
				'" . $mainip . "',
				'" . $location . "',
				" . $hidden . ",
				" . $vhostTotal . ",
				" . $vhostFree . ",
				0
	)"))
	{
		$_SESSION['msg']['alert-success']='添加节点服务器成功';
		header("Location: admin_vhost_servers.php");
		exit();
	} else {
		$err[] = '添加节点服务器失败';
	}
}
elseif (@$_POST['action'] == 'edit'  && !count($err)) {
	if (!is_numeric($serverid))	ForceDie();
	$result_servers = mysql_query("select * FROM vhost_servers WHERE ID=" . $serverid);
	if (@mysql_num_rows($result_servers) != 1) ForceDie();
	if(mysql_query(" UPDATE vhost_servers
				SET
				alias='" . $alias . "',
				root='root',
				passwd='" . $passwd . "',
		     	port='" . $port . "',
				mysqlpasswd='" . $mysqlpasswd . "',
				ip='" . $mainip . "',
				location='" . $location . "',
				hidden=" . $hidden . ",
				vhostTotal=" . $vhostTotal . ",
				vhostFree=" . $vhostFree . ",
				downtime=0
				WHERE ID=".$serverid ))
	{
		$_SESSION['msg']['alert-success']='修改节点服务器成功';
		header("Location: admin_vhost_server_conf.php?id=$serverid&action=edit");
		exit();
	} else {
		$err[] = '修改节点服务器失败';
	}
} elseif (@$_POST['action'] == 'del') {
	$result_vhosts = mysql_query("select * FROM vhost WHERE serverID=$serverid" );
	if (mysql_num_rows($result_vhosts) != 0){
		$err[] = '该节点服务器存在虚拟主机, 无法删除!';
		SetErrAlert($err);
		header("Location: admin_vhost_server_conf.php?id=$serverid&action=edit");
		exit();
	} else { 
		if (mysql_query("DELETE FROM vhost_servers WHERE ID=$serverid")) 
		{
			$_SESSION['msg']['alert-success']='删除节点服务器成功';
			header("Location: admin_vhost_servers.php");
		} else {
			$err[] = '删除节点服务器失败!';
			SetErrAlert($err);
			header("Location: admin_vhost_server_conf.php?id=$serverid&action=edit");
		}
		exit();
	}
} elseif (@$_GET['action'] == 'edit') {
	if (!is_numeric($serverid))	ForceDie();
	$result_servers = mysql_query("select * FROM vhost_servers WHERE ID=" . $serverid);
	if (@mysql_num_rows($result_servers) != 1) ForceDie();
	$row_server = mysql_fetch_array($result_servers);
} elseif (@$_GET['action'] == 'add') {

} else {
	ForceDie();
}
if (count($err))
{
	SetErrAlert($err);
}
?>
<div id="page">
<?php EchoAlert(); ?>
<form name="config_save" id="config_save" action="" method="post" >
<table class="list">
	<tr>
		<th colspan="3">节点信息</th>
	</tr>
<?php if (@$_GET['action'] != 'add'){ ?>
	<tr class="list_entry">
		<td class="table_form_header">ID</td>
		<td><span style="color: green"><?php echo $row_server['ID']; ?></span><br /></td>
		<td class="hint"></td>
	</tr>
<?php } ?>
	<tr class="list_entry">
		<td class="table_form_header">别名</td>
		<td><input name="alias" id="alias" type="text" value="<?php echo @$row_server['alias']?$row_server['alias']:$alias; ?>" autocomplete="off" required /><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">Linux Root</td>
		<td><input name="passwd" id="passwd" type="text" value="<?php echo @$row_server['passwd']?$row_server['passwd']:$passwd; ?>" autocomplete="off" required /><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">MySQL Root</td>
		<td><input name="mysqlpasswd" id="mysqlpasswd" type="text" value="<?php echo @$row_server['mysqlpasswd']?$row_server['mysqlpasswd']:$mysqlpasswd; ?>" autocomplete="off" required /><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">主IP</td>
		<td><input name="mainip" id="mainip" type="text" value="<?php echo @$row_server['ip']?$row_server['ip']:$mainip; ?>" autocomplete="off" required /><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">SSH端口(默认22)</td>
		<td><input name="port" id="port" type="text" value="<?php echo @$row_server['port']?$row_server['port']:$port; ?>" autocomplete="off" required /><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">地理位置</td>
		<td><input name="location" id="location" type="text" value="<?php echo @$row_server['location']?$row_server['location']:$location; ?>" autocomplete="off" required /><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">隐藏</td>
		<td><select name="hidden" id="hidden" size="1">
			<option value="1" <?php if(@$row_server['hidden'] && @$hidden) echo 'selected="selected"'; ?>>Yes</option>
			<option value="0" <?php if(@!$row_server['hidden'] && @!$hidden) echo 'selected="selected"'; ?>>No</option>
			</select> <br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">最大虚拟主机数</td>
		<td><input name="vhostTotal" id="vhostTotal" type="text" value="<?php echo @$row_server['vhostTotal']?$row_server['vhostTotal']:$vhostTotal; ?>" autocomplete="off" style="width:80px;" required /><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">空闲虚拟主机数</td>
		<td><input name="vhostFree" id="vhostFree" type="text" value="<?php echo @$row_server['vhostFree']?$row_server['vhostFree']:$vhostFree; ?>" autocomplete="off" style="width:80px;" required /><br /></td>
		<td class="hint"></td>
	</tr>
<?php if (@$_GET['action'] != 'add'){ ?>
	<tr class="list_entry">
		<td class="table_form_header">CPU核心数</td>
		<td><?php echo ($row_server['cpucore']); ?> <br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">CPU信息</td>
		<td><?php echo ($row_server['cpuinfo']); ?> <br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">在线时间</td>
		<td><?php echo ($row_server['uptime']); ?> <br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">负载</td>
		<td><?php echo ($row_server['loadaverage']); ?> <br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">总内存</td>
		<td><?php echo ($row_server['memTotal']); ?> MB <br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">空闲内存</td>
		<td><?php echo ($row_server['memFree']); ?> MB <br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">总SWAP</td>
		<td><?php echo ($row_server['swapTotal']); ?> MB <br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">空闲SWAP</td>
		<td><?php echo ($row_server['swapFree']); ?> MB <br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">总磁盘</td>
		<td><?php echo ($row_server['diskTotal']); ?> KB <br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">空闲磁盘</td>
		<td><?php echo ($row_server['diskFree']); ?> KB <br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">网络流入带宽</td>
		<td><?php echo ($row_server['netInput']); ?> Bytes <br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">网络流出带宽</td>
		<td><?php echo ($row_server['netOut']); ?> Bytes <br /></td>
		<td class="hint"></td>
	</tr>
<?php } ?>
	<tr class="list_entry">
		<td width="200"></td>
		<td colspan="3">
			<br />
			<input type="hidden" name="id" value="<?php echo empty($_GET['id'])?@$_POST['id']:@$_GET['id']; ?>" />
			<input type="hidden" name="action" value="<?php echo empty($_GET['action'])?@$_POST['action']:@$_GET['action']; ?>" />
			<input class="button" id="button_save" type="submit" value="保存设置">
		</td>
	</tr>
</table>
</form>
<?php if (@$_GET['action'] != 'add'){ ?>
<hr />
<form name="config_save" id="config_save" action="" method="post" onsubmit="return confirm('确认是否彻底删除此节点服务器?');">
<table class="list">
	<tr class="list_entry">
		<td width="200"></td>
		<td colspan="3">
			<input type="hidden" name="id" value="<?php echo empty($_GET['id'])?@$_POST['id']:@$_GET['id']; ?>" />
			<input type="hidden" name="action" value="del" />
			<input class="button" id="button_save" type="submit" value="彻底删除此节点服务器">
		</td>
	</tr>
</table>
</form>
<?php } ?>
<?php if (@$_GET['action'] != 'add') { ?>
<?php } else { ?>
<?php } ?>
</div>
<?php @include_once("footer.php") ?>