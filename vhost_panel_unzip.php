<?php
$page = "vhost_panel_unzip.php";
@include_once("header.php");
@include_once("vhost_function.php");
@include_once("alert_function.php");
if (@$_POST['action'] == 'unzip')
{
	$err = array();
	$archive = @trim($_POST['archive']);
	$format = @trim($_POST['format']);
	$directory = @trim($_POST['directory']);
	if($archive == '' || $archive == '/')
	{
		$err[]='档案文件名不能为空';
	}
	if(strstr($archive, './'))
	{
		$err[]='档案文件名包含跨目录操作符！';
	}
	if(substr($archive, 0, 1)!='/')
	{
		$archive = '/' . $archive;
		//$err[]='档案文件名必须以正斜杠开始！';
	}
	if(preg_match('/[^a-z0-9\_\-\/\.]+/i',$archive))
	{
		$err[]='档案文件名包含无效字符！';
	}
	if ('tar' != $format && 'tgz' != $format && 'zip' != $format)
	{
		$err[]='请选择正确的压缩档案格式';
	}
	if($directory == '')
	{
		$err[]='解压到的目录不能为空';
	}
	if(strstr($directory, './'))
	{
		$err[]='解压到的目录包含跨目录操作符！';
	}
	if(substr($directory, 0, 1)!='/')
	{
		$err[]='解压到的目录必须以正斜杠开始！';
	}
	if(preg_match('/[^a-z0-9\_\/]+/i',$directory))
	{
		$err[]='解压到的目录包含无效字符！';
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
			$vhost_nginx_conf = mysql_query("select * FROM vhost_nginx_conf WHERE vhostID=".$row['ID']." AND server_name='".$row['domain']."'");
			$row_vhost_nginx_conf = mysql_fetch_array($vhost_nginx_conf);
			if ('tar' == $format) $commands = 'tar xvf \''.$row_vhost_nginx_conf['root'].$archive.'\' -C \''.$row_vhost_nginx_conf['root'].$directory.'\'';
			if ('tgz' == $format) $commands = 'tar zxvf \''.$row_vhost_nginx_conf['root'].$archive.'\' -C \''.$row_vhost_nginx_conf['root'].$directory.'\'';
			if ('rar' == $format) $commands = 'rar e o+ \''.$row_vhost_nginx_conf['root'].$archive.'\' \''.$row_vhost_nginx_conf['root'].$directory.'\'';
			if ('zip' == $format) $commands = 'unzip -o -d \''.$row_vhost_nginx_conf['root'].$directory.'\' \''.$row_vhost_nginx_conf['root'].$archive.'\'';
			$unzip_return_text = $ssh->exec($commands);
			// zip
			if (strstr($unzip_return_text,'unzip:  cannot find or open'))
				$err[]='找不到或无法打开压缩档案';
			if (strstr($unzip_return_text,'unzip:  unzip:  cannot find zipfile directory'))
				$err[]='不是zip档案格式';
			if (strstr($unzip_return_text,'unzip: command not found'))
				$err[]='unzip未安装，请联系技术解决。';
			// tar || tar.gz
			if (strstr($unzip_return_text,'Cannot chdir: No such file or directory'))
				$err[]='解压到的目标目录不存在';
			if (strstr($unzip_return_text,'Cannot open: No such file or directory'))
				$err[]='压缩档案不存在';
			if (strstr($unzip_return_text,'not in gzip format'))
				$err[]='不是 tar.gz 档案文件';
			if (strstr($unzip_return_text,'This does not look like a tar archive'))
				$err[]='不是 tar 档案文件';
			if(!count($err))
			{
				// 设置目录所有用户组
				$ssh->exec('chown -R www:www \''.$row_vhost_nginx_conf['root'].$directory.'\'');
				AddJob($vhostid, '解压缩 - '.$archive, '解压成功', $s, $s, date("Y-m-d g:i:s a"), $con);
				$_SESSION['msg']['alert-success']='解压成功';
				header("Location: vhost_panel_unzip.php?id=".$vhostid);
				exit();
			} else {
				$err[]='解压失败';
			}
		}
	}
	SetErrAlert($err);
}
?>
<div id="page">
<p class='breadcrumb'><a href='index.php'><?php echo $PanelName;?></a> &raquo; <a href='vhost_panel.php?id=<?php echo $row['ID']; ?>'><?php echo $row['domain']; ?></a> &raquo; <strong>解压缩</strong></p><form name="config_save" id="config_save" action="" method="post" onsubmit="document.getElementById('button_save').disabled = true;">
<?php EchoAlert(); ?>
<table class="list">
	<tr>
		<th colspan="3">解压缩文件</th>
	</tr>
	<tr class="list_head">
		<td colspan="3">压缩包文件</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">档案文件名</td>
		<td><input name="archive" id="archive" type="text" value="<?php echo @$archive!=''?$archive:'/';?>" maxlength="60" size="16" /></td>
		<td class="hint"></td>
	</tr>
		<tr class="list_entry">
			<td class="table_form_header">压缩档案类型</td>
			<td>
				<select name="format">
					<option value="zip" <?php echo @$format=='zip'?'selected="selected"':''; ?>>zip</option>
					<option value="tar" <?php echo @$format=='tar'?'selected="selected"':''; ?>>tar</option>
					<option value="tgz" <?php echo @$format=='tgz'?'selected="selected"':''; ?>>tgz(tar.gz)</option>
				</select>
			</td>
			<td class="hint"></td>
		</tr>
		<tr class="list_head">
			<td colspan="3">解压到</td> <!-- fastcgi_intercept_errors on; -->
		</tr>
	<tr class="list_entry">
		<td class="table_form_header">解压到目录</td>
		<td><input name="directory" id="directory" type="text" value="<?php echo @$directory!=''?$directory:'/';?>" maxlength="60"  size="20"  /></td>
	</tr>
<tr class="list_entry">
		<td width="200"></td>
		<td colspan="3">
			<br />
			<input type="hidden" name="action" value="unzip">
			<input class="button" id="button_save" type="submit" value="解压"> 设置完毕后，猛击<span style="color:#F00;">解压</span>按钮，进行解压。
		</td>
	</tr>
</table>
</form>
</div>
<?php @include_once("footer.php") ?>