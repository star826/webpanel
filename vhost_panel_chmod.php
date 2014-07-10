<?php
$page = "vhost_panel_chmod.php";
@include_once("header.php");
@include_once("vhost_function.php");
@include_once("alert_function.php");
if (@$_POST['action'] == 'chmod')
{
	$err = array();
	$permissions = @$_POST['permissions'];
	if ('444' != $permissions && '555' != $permissions && '666' != $permissions && '755' != $permissions && '777' != $permissions )
		$err[] = '请选择权限';
	if(!count($err))
	{
		$s = date("Y-m-d g:i:s a");
		$result_server = mysql_query("select * FROM vhost_servers WHERE ID=".$row['serverID']);
		$row_server = mysql_fetch_array($result_server);
		set_include_path('api/ssh/' . PATH_SEPARATOR . 'phpseclib');
		@include_once("api/ssh/Net/SSH2.php");
		@include_once("api/ssh/Net/SFTP.php");
		$ssh = new Net_SSH2($row_server['ip'],$row_server['port']); 
		if (!@$ssh->login($row_server['root'], $row_server['passwd']))
			$err[]='与节点服务器通讯失败';
		if(!count($err))
		{
			$vhost_nginx_conf = mysql_query("select * FROM vhost_nginx_conf WHERE vhostID=".$row['ID']." AND server_name='".$row['domain']."'");
			$row_vhost_nginx_conf = mysql_fetch_array($vhost_nginx_conf);
			$commands = 'chmod -R '.$permissions.' \'' . $row_vhost_nginx_conf['root'] . '\'';
			$ssh->exec($commands);
				AddJob($vhostid, '权限设置', '设置成功', $s, $s, date("Y-m-d g:i:s a"), $con);
				$_SESSION['msg']['alert-success']='设置目录/文件权限成功';
				header("Location: vhost_panel_chmod.php?id=".$vhostid);
				exit();
		}
	}
	SetErrAlert($err);
}
?>
<div id="page">
<p class='breadcrumb'><a href='index.php'><?php echo $PanelName;?></a> &raquo; <a href='vhost_panel.php?id=<?php echo $row['ID']; ?>'><?php echo $row['domain']; ?></a> &raquo; <strong>权限设置</strong></p><form name="config_save" id="config_save" action="" method="post" onsubmit="document.getElementById('button_save').disabled = true;">
<?php EchoAlert(); ?>
<table class="list">
	<tr>
		<th colspan="3">设置目录/文件权限</th>
	</tr>
		<tr class="list_head">
			<td colspan="3">设置权限</td> <!-- fastcgi_intercept_errors on; -->
		</tr>
	<tr class="list_entry">
		<td class="table_form_header">权限</td>
		<td>
				<select name="permissions">
					<option value="444">444</option>
					<option value="555">555</option>
					<option value="666">666</option>
					<option value="755" selected="selected">755</option>
					<option value="777">777</option>
				</select>
		</td>
		<td class="hint">所更改的权限将会应用到所有目录及文件</td>
	</tr>
<tr class="list_entry">
		<td width="200"></td>
		<td colspan="3">
			<br />
			<input type="hidden" name="action" value="chmod">
			<input class="button" id="button_save" type="submit" value="更新">
		</td>
	</tr>
</table>
</form>
</div>
<?php @include_once("footer.php") ?>