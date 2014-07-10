<?php
$page = "vhost_panel_install.php";
@include_once("header.php");
@include_once("function.php");
@include_once("vhost_function.php");
@include_once("alert_function.php");
$err = array();
if (@$_POST['action'] == 'install_app')
{
	if(trim($_POST['install_root']) == '')
	{
		$err[]='安装应用的根目录不能为空！';
	}
	if(strstr($_POST['install_root'], './'))
	{
		$err[]='安装应用的根目录包含无效字符！';
	}
	if(substr($_POST['install_root'], 0, 1)!='/')
	{
		$err[]='安装应用的根目录必须以正斜杠开始！';
	}
	if(preg_match('/[^a-z0-9\_\/]+/i',$_POST['install_root']))
	{
		$err[]='安装应用的根目录包含无效字符！';
	}
		$appid = $_POST['appid'];
		$result_app = mysql_query("select * FROM vhost_app WHERE ID=".$appid);
		if (@mysql_num_rows($result_app) != 1){
			ForceDie();
		} else {
			$row_app = mysql_fetch_array($result_app);
		}
	if(!count($err))
	{
		$s = date("Y-m-d g:i:s a");
		$result_server = mysql_query("select * FROM vhost_servers WHERE ID=".$row['serverID']);
		$row_server = mysql_fetch_array($result_server);
		set_include_path('api/ssh/' . PATH_SEPARATOR . 'phpseclib');
		@include_once("api/ssh/Net/SSH2.php");
		$ssh = new Net_SSH2($row_server['ip'],$row_server['port']); 
		if (!@$ssh->login($row_server['root'], $row_server['passwd']))
			$err[]='与节点服务器通讯失败';
		if(!count($err))
		{
			$commands = 'unzip -o -d \''.$row['root'].$_POST['install_root'].'\' \''.$row_app['app_localpath'].'\'';
			$unzip_return_text = $ssh->exec($commands);
			// zip
			if (strstr($unzip_return_text,'unzip:  cannot find or open'))
				$err[]='该APP不存在，请联系技术支持。';
			if (strstr($unzip_return_text,'unzip:  unzip:  cannot find zipfile directory'))
				$err[]='致命错误！';
			if (strstr($unzip_return_text,'unzip: command not found'))
				$err[]='unzip未安装，请联系技术支持。';
			if(!count($err))
			{
				// 设置目录所有用户组
				$ssh->exec('chown -R www:www \''.$row['root'].$_POST['install_root'].'\'');
				AddJob($vhostid, '安装Web应用 - '.$row_app['app_name'], '安装成功', $s, $s, date("Y-m-d g:i:s a"), $con);
				$_SESSION['msg']['alert-success']='安装 ' . $row_app['app_name'] . ' 成功！';
				header("Location: vhost_panel.php?id=".$vhostid);
				exit();
			} else {
				$err[]='安装' . $row_app['app_name'] . '失败';
			}
		}
	}
	if (count($err)){
		SetErrAlert($err);
		header("Location: vhost_panel_install.php?id=".$vhostid."&action=install&appid=".$appid);
		exit;
	}
}
?>
<div id="page">
<p class='breadcrumb'><a href='index.php'><?php echo $PanelName;?></a> &raquo; <a href='vhost_panel.php?id=<?php echo $row['ID']; ?>'><?php echo $row['domain']; ?></a> &raquo; <strong>安装应用</strong></p>
<?php EchoAlert(); ?>
<?php if (@$_GET['action'] == 'install') { 
SetErrAlert($err);
$result_app = mysql_query("select * FROM vhost_app WHERE ID=".$_GET['appid']);
if (@mysql_num_rows($result_app) != 1){
	ForceDie();
}
$row_app = mysql_fetch_array($result_app);
?>
<form name="config_save" id="config_save" action="" method="post" onsubmit="document.getElementById('button_install').disabled = true;">
<table class="list">
	<tr>
		<th colspan="3">安装 <?php echo $row_app['app_name']; ?></th>
	</tr>
		<tr class="list_head">
			<td colspan="3">参数</td> <!-- fastcgi_intercept_errors on; -->
		</tr>
	<tr class="list_entry">
		<td class="table_form_header">安装到</td>
		<td><input name="install_root" id="install_root" type="text" value="/" maxlength="60"  size="20"  /></td>
		<td class="hint">安装到指定的目录中，正斜杠表示为根目录。</td>
	</tr>
	<tr class="list_entry">
		<td width="200"></td>
		<td colspan="3">
			<br />
			<input type="hidden" name="action" value="install_app" />
			<input type="hidden" name="appid" value="<?php echo $_GET['appid']; ?>" />
			<input class="button" id="button_install" type="submit" value="安装" />
		</td>
	</tr>
</table>
</form>
<?php } else { ?>
<table class="list">
	<tr>
		<th colspan="4">安装Web应用</th>
	</tr>
	<tr class="list_head">
		<td>应用</td>
		<td>版本</td>
		<td>官网</td>
		<td align="center" width="10%">选项</td>
	</tr>
<?php
$result_app = mysql_query("select * FROM vhost_app ");
while($row_app = mysql_fetch_array($result_app)) {
?>
		<tr class="list_entry">
			<td><img src="<?php echo $row_app['app_image']; ?>" alt="<?php echo $row_app['app_name']; ?>" title="<?php echo $row_app['app_name']; ?>" /></td>
			<td><?php echo $row_app['app_version']; ?></td>
			<td><?php echo $row_app['app_site']; ?></td>
			<td nowrap align="center">
				<a href="?id=<?php echo $row['ID']; ?>&amp;action=install&amp;appid=<?php echo $row_app['ID']; ?>">安装</a>
			</td>
		</tr>
<?php } ?>
</table>
<?php } ?>
</div>
<?php @include_once("footer.php") ?>