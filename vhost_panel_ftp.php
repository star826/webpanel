<?php
$page = "vhost_panel_ftp.php";
@include_once("header.php");
@include_once("function.php");
@include_once("vhost_function.php");
@include_once("alert_function.php");
$err = array();
if (@$_GET['action'] == 'add' && $row['ftp'] != -1 && mysql_num_rows(mysql_query("select * FROM vhost_ftp WHERE VhostID=".$row['ID'])) >= $row['ftp'])
{
	$err[]='可创建FTP帐户数已达到上限！';
	SetErrAlert($err);
	header("Location: vhost_panel_ftp.php?id=".$row['ID']);
	exit();
}
if (@$_POST['action'] == 'create' || @$_POST['action'] == 'update') {
	if ($_POST['action'] == 'create'){
		if ($row['ftp'] != -1 && mysql_num_rows(mysql_query("select * FROM vhost_ftp WHERE VhostID=".$row['ID'])) >= $row['ftp'])
		{ 
			$err[]='可创建FTP帐户数已达到上限！';
		}
		if(strlen($_POST['username'])<5 || strlen($_POST['username'])>16)
		{
			$err[]='您的FTP登录名必须为5到16个字符！';
		}
		if(preg_match('/[^a-z0-9\_]+/i',$_POST['username']))
		{
			$err[]='您的FTP登录名包含无效字符！';
		}
		if(strlen($_POST['password'])<6 || strlen($_POST['password'])>32)
		{
			$err[]='您的FTP登录密码必须为6到32个字符！';
		}
	}
	if($_POST['password'] != $_POST['password2'])
	{
		$err[]='两次输入的密码不同！';
	}
	if(preg_match('/[^a-z0-9\-\_\.]+/i',$_POST['password']))
	{
		$err[]='您的FTP登录密码包含无效字符！';
	}
	if($_POST['action'] == 'create') {
		if(trim($_POST['ftproot']) == '')
		{
			$err[]='FTP根目录不能为空！';
		}
		if(strstr($_POST['ftproot'], './'))
		{
			$err[]='您的FTP根目录包含无效字符！';
		}
		if(substr($_POST['ftproot'], 0, 1)!='/')
		{
			$err[]='您的FTP根目录必须以正斜杠开始！';
		}
		if(preg_match('/[^a-z0-9\_\/]+/i',$_POST['ftproot']))
		{
			$err[]='您的FTP根目录包含无效字符！';
		}
	}
	$serverid = $row['serverID'];
	if($_POST['action'] == 'create') {
		$result_ftp_users = mysql_query("select * FROM vhost_ftp WHERE ServerID=".$serverid." AND User='".$_POST['username']."'");
		if (@mysql_num_rows($result_ftp_users) > 0){
			$err[]='您输入的FTP登录名已存在于该节点服务器';
		}
		$vhostdomain = $row['domain'];
		$result_nginx_conf = mysql_query("select * FROM vhost_nginx_conf WHERE serverID=".$serverid." AND server_name='".$vhostdomain."'");
		if (@mysql_num_rows($result_nginx_conf) != 1){
			$err[]='致命的错误，<a href="support_ticket_new.php">请提交一个Ticket</a>，联系技术服务进行解决。';
		}
		$row_nginx_conf = mysql_fetch_array($result_nginx_conf);
	} elseif ($_POST['action'] == 'update') {
		$ftpuserid = $_GET['ftpuserid'];
		$result_ftp_users = mysql_query("select * FROM vhost_ftp WHERE ID=".$ftpuserid." AND ServerID=".$serverid." AND VhostID='".$vhostid."'");
		if (@mysql_num_rows($result_ftp_users) != 1){
			$err[]='您编辑的FTP帐户不存在';
		} else {
			$row_ftp_users = mysql_fetch_array($result_ftp_users);
		}
	}
	if(!count($err))
	{
		$s = date("Y-m-d g:i:s a");
		$result_server = mysql_query("select * FROM vhost_servers WHERE ID=".$serverid);
		$row_server = mysql_fetch_array($result_server);
		$remotecon = @mysql_connect($row_server['ip'],'root',$row_server['mysqlpasswd']);
		if (!$remotecon) $err[]='无法连接节点服务器MySQL数据库';
		if(!count($err))
		{
			mysql_query("set names 'utf8'", $remotecon);
			mysql_select_db("ftpusers", $remotecon);
			if($_POST['action'] == 'create') {
				if (!@mysql_query("INSERT INTO `users` (`User`, `Password`, `Uid`, `Gid`, `Dir`, `QuotaFiles`, `QuotaSize`, `ULBandwidth`, `DLBandwidth`, `Ipaddress`, `Comment`, `Status`, `ULRatio`, `DLRatio`)
				VALUES (
				'".$_POST['username']."',
				'".md5($_POST['password'])."',
				501, 501,
				'".$row_nginx_conf['root'].$_POST['ftproot']."',
				100, 100, 2048, 512, '*', '', '1', 0, 0 );",$remotecon)) {
					$err[]='在节点服务器创建FTP帐户时发生错误，创建失败';
					mysql_close($remotecon);
					}
				else {
					mysql_query("INSERT INTO `vhost_ftp` (`VhostID`, `ServerID`, `User`, `Password`, `Uid`, `Gid`, `Dir`, `RelativePath`, `QuotaFiles`, `QuotaSize`, `ULBandwidth`, `DLBandwidth`, `Ipaddress`, `Comment`, `Status`, `ULRatio`, `DLRatio`)
						VALUES (
						".$vhostid.", 
						".$serverid.", 
						'".$_POST['username']."',
						'".($_POST['password'])."',
						501, 501,
						'".$_POST['ftproot']."',
						'".$row_nginx_conf['root'].$_POST['ftproot']."',
						100, 100, 2048000, 2048000, '*', '', '1', 0, 0 );",$con);
					AddJob($vhostid, 'FTP帐号创建 - '.$_POST['username'], '创建成功', $s, $s, date("Y-m-d g:i:s a"), $con);
					$_SESSION['msg']['alert-success']='创建FTP帐号成功';
					header("Location: vhost_panel_ftp.php?id=".$vhostid);
					exit();
				}
			} elseif ($_POST['action'] == 'update') {
				if (!@mysql_query("UPDATE users SET Password='".md5($_POST['password'])."' WHERE User='".$row_ftp_users['User']."' ")){
					mysql_close($remotecon);
					$err[]='在节点服务器修改FTP帐户时发生错误，修改失败';
					}
				else {
					AddJob($vhostid, 'FTP帐号修改 - '.$row_ftp_users['User'], '修改成功', $s, $s, date("Y-m-d g:i:s a"), $con);
					mysql_query("UPDATE vhost_ftp SET Password='".($_POST['password'])."' WHERE ID='".$row_ftp_users['ID']."' ",$con);
					$_SESSION['msg']['alert-success']='修改FTP帐号成功';
					header("Location: vhost_panel_ftp.php?id=".$vhostid);
					exit();
				}
			}
		}
	}
	SetErrAlert($err);
}
elseif (@$_GET['action'] == 'delete') {
	$err = array();
	$serverid = $row['serverID'];
	$result_ftp_users = mysql_query("select * FROM vhost_ftp WHERE ID=".$_GET['ftpuserid']." AND ServerID=".$serverid." AND VhostID=".$vhostid);
	if (@mysql_num_rows($result_ftp_users) != 1){
		$err[]='欲删除的FTP帐号不存在';
	}
	$row_ftp_user = mysql_fetch_array($result_ftp_users);
	$s = date("Y-m-d g:i:s a");
	if(!count($err))
	{
		$result_server = mysql_query("select * FROM vhost_servers WHERE ID=".$serverid);
		$row_server = mysql_fetch_array($result_server);
		$remotecon = @mysql_connect($row_server['ip'],'root',$row_server['mysqlpasswd']);
		if (!$remotecon) $err[]='无法连接节点服务器MySQL数据库';
		if(!count($err))
		{
			mysql_query("set names 'utf8'", $remotecon);
			mysql_select_db("ftpusers", $remotecon);
			if (!@mysql_query("DELETE FROM users WHERE User='".$row_ftp_user['User']."'",$remotecon)){
				$err[]='在节点服务器远程删除FTP帐户时发生错误，删除失败';
				mysql_close($remotecon);
				}
			else {
				AddJob($vhostid, 'FTP帐号删除 - '.$row_ftp_user['User'], '删除成功', $s, $s, date("Y-m-d g:i:s a"), $con);
				mysql_query("DELETE FROM vhost_ftp WHERE ID=".$_GET['ftpuserid'], $con);
				$_SESSION['msg']['alert-success']='删除FTP帐号成功';
			}
		}
	}
	SetErrAlert($err);
	header("Location: vhost_panel_ftp.php?id=".$vhostid);
	exit();
}
?>
<div id="page">
<?php
if (@$_GET['action'] == 'edit') {
?>
<p class='breadcrumb'><a href='index.php'><?php echo $PanelName;?></a> &raquo; <a href='vhost_panel.php?id=<?php echo $row['ID']; ?>'><?php echo $row['domain']; ?></a> &raquo; <a href='vhost_panel_ftp.php?id=<?php echo $row['ID']; ?>'>FTP</a> &raquo; <strong>编辑FTP帐户</strong></p>
<?php
} elseif (@$_GET['action'] == 'add') {
?>
<p class='breadcrumb'><a href='index.php'><?php echo $PanelName;?></a> &raquo; <a href='vhost_panel.php?id=<?php echo $row['ID']; ?>'><?php echo $row['domain']; ?></a> &raquo; <a href='vhost_panel_ftp.php?id=<?php echo $row['ID']; ?>'>FTP</a> &raquo; <strong>添加FTP帐户</strong></p>
<?php
} else {
?>
<p class='breadcrumb'><a href='index.php'><?php echo $PanelName;?></a> &raquo; <a href='vhost_panel.php?id=<?php echo $row['ID']; ?>'><?php echo $row['domain']; ?></a> &raquo; <strong>FTP管理</strong></p>
<?php 
}
?>
<?php EchoAlert(); ?>
<?php if (@$_GET['action'] == 'edit') { 
$result_ftp_users = mysql_query("select * FROM vhost_ftp WHERE ID=".$_GET['ftpuserid']." AND ServerID=".$row['serverID']." AND VhostID=".$row['ID']);
if (@mysql_num_rows($result_ftp_users) != 1){
	ForceDie();
}
$row_ftp_user = mysql_fetch_array($result_ftp_users);
?>
<form name="config_save" id="config_save" action="" method="post" onsubmit="document.getElementById('button_save').disabled = true;">
<table class="list">
	<tr>
		<th colspan="3">编辑FTP帐户</th>
	</tr>
	<tr class="list_head">
		<td colspan="3">帐号信息</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">登录名</td>
		<td><?php echo $row_ftp_user['User'] ?></td>
	</tr>
		<tr class="list_entry">
			<td class="table_form_header">密码</td>
			<td>
				<input name="password" id="password" style="margin-bottom:2px;" type="password" maxlength="32"  size="20"  autocomplete="off"  /><br />
				<input name="password2" id="password2" type="password" maxlength="32"  size="20"  autocomplete="off"  />
			</td>
			<td class="hint">只允许字母,数字,下划线,横杠,半角句号</td>
		</tr>
	<tr class="list_entry">
		<td width="200"></td>
		<td colspan="3">
			<br />
			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="ftpuserid" value="<?php echo $row_ftp_user['ID']; ?>" />
			<input class="button" id="button_save" type="submit" value="保存设置">
		</td>
	</tr>
</table>
</form>
<?php } elseif (@$_GET['action'] == 'add') { ?>
<form name="config_save" id="config_save" action="" method="post" onsubmit="document.getElementById('button_save').disabled = true;">
<table class="list">
	<tr>
		<th colspan="3">创建FTP帐户</th>
	</tr>
	<tr class="list_head">
		<td colspan="3">帐号信息</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">登录名</td>
		<td><input name="username" id="username" type="text" value="<?php echo @$_POST['username']; ?>" maxlength="16" size="16" /></td>
		<td class="hint">只允许字母,数字,下划线</td>
	</tr>
		<tr class="list_entry">
			<td class="table_form_header">密码</td>
			<td>
				<input name="password" id="password" style="margin-bottom:2px;" type="password" maxlength="32"  size="20"  autocomplete="off"  /><br />
				<input name="password2" id="password2" type="password" maxlength="32"  size="20"  autocomplete="off"  />
			</td>
			<td class="hint">只允许字母,数字,下划线,横杠,半角句号</td>
		</tr>
		<tr class="list_head">
			<td colspan="3">主目录</td> <!-- fastcgi_intercept_errors on; -->
		</tr>
	<tr class="list_entry">
		<td class="table_form_header">主目录</td>
		<td><input name="ftproot" id="ftproot" type="text" value="<?php echo @$_POST['ftproot']?$_POST['ftproot']:'/'; ?>" maxlength="60"  size="20"  /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td width="200"></td>
		<td colspan="3">
			<br />
			<input type="hidden" name="action" value="create" />
			<input class="button" id="button_save" type="submit" value="保存设置">
		</td>
	</tr>
</table>
</form>
<?php } else { ?>
<table class="list">
	<tr>
		<th colspan="4">FTP管理</th>
	</tr>
	<tr class="list_head">
		<td>登录名</td>
		<td>相对目录</td>
		<td>绝对目录</td>
		<td align="center" width="10%">选项</td>
	</tr>
<?php
	$result_ftp_users = mysql_query("select * FROM vhost_ftp WHERE VhostID=".$vhostid);
	while($row_ftp_user = mysql_fetch_array($result_ftp_users)) {
?>
		<tr class="list_entry">
			<td>
				<?php echo $row_ftp_user['User']; ?>
				<?php if ($row_ftp_user['Status'] != 1) { ?>
				<span class="hint">禁用</span>
				<?php } ?>
			</td>
			<td><?php echo $row_ftp_user['Dir']; ?></td>
			<td><?php echo $row_ftp_user['RelativePath']; ?></td>
			<td nowrap align="center">
				<a href="ftp://<?php echo $row_ftp_user['User']; ?>:<?php echo $row_ftp_user['Password']; ?>@<?php echo $row['ip']; ?>/" target="_blank">登录</a> | 
				<a href="?id=<?php echo $vhostid; ?>&amp;action=edit&amp;ftpuserid=<?php echo $row_ftp_user['ID']; ?>">编辑</a> | 
				<a href="?id=<?php echo $vhostid; ?>&amp;action=delete&amp;ftpuserid=<?php echo $row_ftp_user['ID']; ?>" onClick="return confirm('确认删除这个FTP帐户?')">删除</a>
			</td>
		</tr>
<?php
}
?>
	<tr class="list_entry " style="height: 35px;">
		<td colspan="7" align="right">
			<?php if ($row['ftp'] != -1 && mysql_num_rows(mysql_query("select * FROM vhost_ftp WHERE VhostID=".$row['ID'])) >= $row['ftp']) { ?>
			<strike>添加一个FTP帐号</strike>
			<?php } else { ?>
			<a href="?id=<?php echo $vhostid; ?>&amp;action=add">添加一个FTP帐号</a>
			<?php } ?>
		</td>
	</tr>
</table>
<?php } ?>
</div>
<?php @include_once("footer.php") ?>