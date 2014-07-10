<?php
$page = "vhost_panel.php";
@include_once("header.php");
@include_once("function.php");
@include_once("vhost_function.php");
@include_once("alert_function.php");
$err = array();
if (@$_GET['action'] == 'updatediskused')
{
	$s = date("Y-m-d g:i:s a");
	$result_server = mysql_query("select * FROM vhost_servers WHERE ID=".$row['serverID'], $con);
	$row_server = mysql_fetch_array($result_server);
	set_include_path('api/ssh/' . PATH_SEPARATOR . 'phpseclib');
	@include_once("api/ssh/Net/SSH2.php");
	$ssh = new Net_SSH2($row_server['ip'],$row_server['port']); 
	if (!@$ssh->login($row_server['root'], $row_server['passwd'])) {
		$err[]='与节点服务器通讯失败';
	}
	if (!count($err)){
		$commands   = 'du -sb "' . $row['root'] . '" | awk \'{print $1}\' ';
		$diskUsed = trim($ssh->exec($commands));
		if (!is_numeric($diskUsed))
		{
			mysql_query("UPDATE vhost SET spaceUsed=NULL WHERE ID=".$row['ID'], $con);
			$err[] = '统计硬盘使用概况时发生错误';
			SetErrAlert($err);
		} else {
			mysql_query("UPDATE vhost SET spaceUsed=" . $diskUsed . " WHERE ID=".$row['ID'], $con);
			$_SESSION['msg']['alert-success']='刷新磁盘使用概况成功';
			AddJob($vhostid, '刷新磁盘使用概况', '刷新成功', $s, $s, date("Y-m-d g:i:s a"), $con);
		}
		header("Location: vhost_panel.php?id=".$row['ID']);
		exit();
	}
}
if (@$_GET['action'] == 'updatetrafficused')
{
	$s = date("Y-m-d g:i:s a");
	$result_server = mysql_query("select * FROM vhost_servers WHERE ID=".$row['serverID'], $con);
	$row_server = mysql_fetch_array($result_server);
	set_include_path('api/ssh/' . PATH_SEPARATOR . 'phpseclib');
	@include_once("api/ssh/Net/SSH2.php");
	$ssh = new Net_SSH2($row_server['ip'],$row_server['port']);
	if (!@$ssh->login($row_server['root'], $row_server['passwd'])) {
		$err[]='与节点服务器通讯失败';
	}
	if (!count($err)){
		$trafficUsedTotal = 0;
		$result_vhost_nginx_conf = mysql_query("select * FROM vhost_nginx_conf WHERE vhostID=".$row['ID']);
		while($row_vhost_nginx_conf = mysql_fetch_array($result_vhost_nginx_conf)) {
			$commands   = 'cat /home/wwwlogs/' . $row_vhost_nginx_conf['server_name'] . '.log |awk \'{sum+=$10} END {print sum}\'; rm -f /home/wwwlogs/' . $row_vhost_nginx_conf['server_name'] . '.log;'; // >/home/wwwlogs/' . $row['ID'] . '.log'
			$trafficUsed = trim($ssh->exec($commands));
			if (is_numeric($trafficUsed))
			{
				$trafficUsedTotal += $trafficUsed;
			}
		}
		$ssh->exec('/etc/init.d/nginx reload');
		if (!is_numeric($trafficUsedTotal) || $trafficUsedTotal == 0)
		{
			// mysql_query("UPDATE vhost SET webtrafficUsed=NULL WHERE ID=".$row['ID'], $con);
			$err[] = '距上一次流量统计到现在并未产生任何流量, 统计失败.';
			SetErrAlert($err);
		} else {
			if(!mysql_query("UPDATE vhost SET webtrafficUsed=webtrafficUsed+" . $trafficUsedTotal . " WHERE ID=".$row['ID'], $con))
			{
				$_SESSION['msg']['alert-success']='刷新流量使用概况失败';
			} else {
				$_SESSION['msg']['alert-success']='刷新流量使用概况成功';
				AddJob($vhostid, '刷新流量使用概况', '刷新成功', $s, $s, date("Y-m-d g:i:s a"), $con);
			}
			header("Location: vhost_panel.php?id=".$row['ID']);
			exit();
		}	
	}
}
if (@$_GET['action'] == 'updatehttpstatus')
{
		$s = date("Y-m-d g:i:s a");
		$curl = curl_init();
		$url='http://'.$row['domain'].'/';
		curl_setopt($curl, CURLOPT_URL, $url); //设置URL
		curl_setopt($curl, CURLOPT_HEADER, 1); //获取Header
		curl_setopt($curl,CURLOPT_NOBODY,true); //Body就不要了吧，我们只是需要Head
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); //数据存到成字符串吧，别给我直接输出到屏幕了
		$data = curl_exec($curl); //开始执行啦～
		$http_status_code = curl_getinfo($curl,CURLINFO_HTTP_CODE); //我知道HTTPSTAT码哦～
		curl_close($curl); //用完记得关掉他
		if ($http_status_code == '200')
			SetColorAlert('网站稳定运行中','blue');
		elseif ($http_status_code == 0) 
			SetColorAlert('网站异常', 'pink');
		else 
			SetColorAlert('网站异常, HTTP状态码: '.$http_status_code, 'pink');
		header("Location: vhost_panel.php?id=".$row['ID']);
		exit();
}
if (@$_POST['action'] == 'deldomain')
{
	$vhost_conf_id = @$_POST['vhost_conf_id'];
	if(!is_numeric($vhost_conf_id)) ForceDie(); // 没有传递 vhost_conf_id 参数
	$result_nginx_domain_conf = mysql_query("select * FROM vhost_nginx_conf WHERE ID=".$vhost_conf_id." AND vhostID=".$row['ID']." AND serverID=".$row['serverID']."  AND ( type='addon' OR type='subdomain' ) ");
	if (mysql_num_rows($result_nginx_domain_conf) != 1) ForceDie(); // 主域/附加域，不存在
	$row_nginx_domain_conf = mysql_fetch_array($result_nginx_domain_conf);
	$result_server = mysql_query("select * FROM vhost_servers WHERE ID=".$row['serverID'], $con);
	$row_server = mysql_fetch_array($result_server);
	// 删除Nginx配置文件 等等
	set_include_path('api/ssh/' . PATH_SEPARATOR . 'phpseclib');
	@include_once("api/ssh/Net/SSH2.php");
	$ssh = new Net_SSH2($row_server['ip'],$row_server['port']); 
	if (!@$ssh->login($row_server['root'], $row_server['passwd'])) {
		$err[]='与节点服务器通讯失败';
	}
	if (!count($err)){
		$commands   = 'rm -f "/usr/local/nginx/conf/vhost/'.$row_nginx_domain_conf['server_name'].'.conf";';
		$commands  .= 'rm -f "/usr/local/nginx/conf/vhost/'.$row_nginx_domain_conf['server_name'].'.conf.bak";';
		$commands  .= 'rm -f "/home/wwwlogs/'.$row_nginx_domain_conf['server_name'].'.log";';
		$ssh->exec($commands);
		mysql_query("DELETE FROM vhost_nginx_conf WHERE ID=".$vhost_conf_id, $con);
		$_SESSION['msg']['alert-success']='删除子域/附加域绑定成功';
		header("Location: vhost_panel.php?id=".$row['ID']);
		exit();
	}
}
// 获取主机信息
$diskUsedPercentage = round((($row['spaceUsed']/1024/1024) / $row['space']) * 100, 0);
if ($diskUsedPercentage>=100)
	$err[] = '此虚拟主机磁盘使用已超过默认配额，请升级磁盘容量。';
$trafficUsedPercentage = @round((($row['webtrafficUsed']) / ($row['webtraffic'] * 1024 * 1024 * 1024)) * 100, 0);
if ($trafficUsedPercentage>=100)
	$err[] = '此虚拟主机流量使用已超过默认配额，请升级流量。';
if (count($err))
	SetErrAlert($err);
?>
<div id="page">
<p class='breadcrumb'><a href='index.php'><?php echo $PanelName; ?></a> &raquo; <strong><?php echo $row['domain']; ?></strong></p>
<?php EchoAlert(); ?>
<div id="dashboard_migration"> </div>
<div id="dashboard_mqueue"> </div>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tr>
		<td valign="top">
			<div id="dashboard_configs" style="padding-bottom: 15px;">
			<form name="vhost_control" id="vhost_control" action="" method="post" style="margin-bottom: 0px" onsubmit="document.getElementById('button_save').disabled = true;">
<?php if (@$_GET['action'] == 'deletedomain') { ?>
	<div class="alert red">
		<form name="delete_confirm" id="delete_confirm" action="" method="post" onsubmit="">
				<h2 style="color: white; margin-top: 5px">确认删除域名绑定!</h2>
				你确定要删除绑定域名为 <strong><?php echo $_GET['domain'] ?></strong> 的子域(附加域)绑定 ?
				<br /><br />
				该操作并不会删除你的网站目录 <strong><?php echo $_GET['root'] ?></strong> .
				<br /><br />
				<input type="hidden" name="action" value="deldomain" />
				<input type="hidden" name="vhost_conf_id" value="<?php echo $_GET['vhost_conf_id']; ?>">
				<input type="submit" class="button" id="button_save" value="&nbsp;&nbsp;Yes&nbsp;&nbsp;"> &nbsp;
				<input type="submit" class="button" value="&nbsp;&nbsp;No&nbsp;&nbsp;" onclick="top.location='vhost_panel.php?id=<?php echo $row['ID']; ?>';return false;">
			</form>
	</div>
	<br />
<?php } ?>
<table class="list dashboard" border="0">
	<tr>
		<th>网站</th>
	    <th>服务器IP: <font color="#0000CC"><?php echo $row['ip'] ?></font></th>
	</tr>
	<tr class="list_head">
		<? /*
		<td align="center">选择</td>'
		*/ ?>
		<td>域名绑定</td>
		<td align="right" width="27%">选项</td>
	</tr>
<?php
$result_vhost_nginx_conf = mysql_query("select * FROM vhost_nginx_conf WHERE vhostID=".$row['ID'].' ORDER BY `ID` ASC ');
while($row_vhost_nginx_conf = mysql_fetch_array($result_vhost_nginx_conf)) {
?>
		<tr class="list_entry">
			<td>
				<a href="http://<?php echo $row_vhost_nginx_conf['server_name']; ?>/" target="_blank"><?php echo $row_vhost_nginx_conf['server_name']; ?></a>
				
				<span class="hint">( <?php
				echo $row_vhost_nginx_conf['root'];
				?> )</span>
			</td>
			<td nowrap align="right">
				<a href="vhost_panel_conf.php?id=<?php echo $row['ID']; ?>&amp;vhost_conf_id=<?php echo $row_vhost_nginx_conf['ID']; ?><?php echo '&amp;type='.$row_vhost_nginx_conf['type']; ?>&action=edit">编辑</a>
				<?php if($row_vhost_nginx_conf['type'] != 'main'){ ?>
				| <a href="vhost_panel.php?id=<?php echo $row['ID']; ?>&amp;domain=<?php echo $row_vhost_nginx_conf['server_name']; ?>&amp;root=<?php echo $row_vhost_nginx_conf['root']; ?>&amp;vhost_conf_id=<?php echo $row_vhost_nginx_conf['ID']; ?>&amp;action=deletedomain">删除</a>
				<?php } ?>
			</td>
		</tr>
<?php
}
?>
	<tr class="list_entry " style="height: 35px;">
		<td colspan="7" align="right">
		<?php if ($row['subdomain'] != -1 && mysql_num_rows(mysql_query("select * FROM vhost_nginx_conf WHERE vhostID=".$_GET['id']." AND type='subdomain'")) >= $row['subdomain']) { ?>
		<strike>添加一个子域绑定</strike>
		<?php } else { ?>
		<a href="vhost_panel_conf.php?id=<?php echo $row['ID']; ?>&amp;type=subdomain&amp;action=add">添加一个子域绑定</a>
		<?php } ?>
		<?php if ($row['addon'] != -1 && mysql_num_rows(mysql_query("select * FROM vhost_nginx_conf WHERE vhostID=".$_GET['id']." AND type='addon'")) >= $row['addon']) { ?>
		 | <strike>添加一个附加域绑定</strike>
		<?php } else { ?>
		 | <a href="vhost_panel_conf.php?id=<?php echo $row['ID']; ?>&amp;type=addon&amp;action=add">添加一个附加域绑定</a>
		<?php } ?>
		</td>
	</tr>
</table>
</form>
</div>
			<div id="dashboard_disks" style="padding-bottom: 10px"> 
<table class="list " border="0">
	<tr>
		<th colspan="2">使用概况</th>
		<th align="center" width="10%"></th>
	</tr>

		<tr class="list_entry">
			<td colspan="2">
				<img src="images/icon_disk.gif" align="absmiddle"  width="26" height="24">
				<a href="#" title="可创建的子域名为创建绑定的主域或附加域的子域名且绑定主域的子目录">可创建的子域名</a>
				<span class="hint">( 已创建 <strong><?php echo mysql_num_rows(mysql_query("select * FROM vhost_nginx_conf WHERE vhostID=".$_GET['id']." AND type='subdomain'")); ?></strong> 个 / <?php 
				if ($row['subdomain'] == -1 )
					echo '<strong>无限</strong>';
				elseif ($row['subdomain'] == 0)
					echo '<strong>不可创建</strong>';
				else
					echo '<strong>'.($row['subdomain'] - mysql_num_rows(mysql_query("select * FROM vhost_nginx_conf WHERE vhostID=".$_GET['id']." AND type='subdomain'"))).'</strong> 个可创建'; 
				
				?> )</span>
			</td>
			<td nowrap align="center">
				<?php /*
				<a href="?id=<?php echo $_GET['id']; ?>">添加一个子域绑定</a>
				*/ ?>
			</td>
		</tr>
		<tr class="list_entry">
			<td colspan="2">
				<img src="images/icon_disk.gif" align="absmiddle"  width="26" height="24">
				<a href="#" title="可创建的附加域为额外可绑定的顶级域名，附加域绑定主域的子目录">可创建的附加域名</a>
				<span class="hint">( 已创建 <strong><?php echo mysql_num_rows(mysql_query("select * FROM vhost_nginx_conf WHERE vhostID=".$_GET['id']." AND type='addon'")); ?></strong> 个 / <?php 
				if ($row['addon'] == -1 )
					echo '<strong>无限</strong>';
				elseif ($row['addon'] == 0)
					echo '<strong>不可创建</strong>'; 
				else echo '<strong>'.($row['addon'] - mysql_num_rows(mysql_query("select * FROM vhost_nginx_conf WHERE vhostID=".$_GET['id']." AND type='addon'"))).'</strong> 个可创建';
				?> )</span>
			</td>
			<td nowrap align="center">
				<?php /*
				<a href="?id=<?php echo $_GET['id']; ?>">添加一个附加域绑定</a>
				*/ ?>
			</td>
		</tr>
<?php /*
		<tr class="list_entry">
			<td colspan="2">
				<img src="images/icon_disk.gif" align="absmiddle"  width="26" height="24">
				网站存储空间
				<span class="hint">( 已用 <strong>未知</strong> MB / <?php 
				if ($row['space'] == 0 )
					echo '<strong>不限空间大小</strong>';
				else
					echo '<strong>'.$row['space'].'</strong> MB 可用'; 
				
				?> )</span>
			</td>
			<td nowrap align="center">
			</td>
		</tr>
		
		<tr class="list_entry">
			<td colspan="2">
				<img src="images/icon_disk.gif" align="absmiddle"  width="26" height="24">
				网站本月流量
				<span class="hint">( 已使用 <strong>未知</strong> Bytes / <?php 
				if ($row['webtraffic'] == 0 )
					echo '<strong>不限流量</strong>';
				else
					echo '<strong>'.$row['webtraffic']*1024*1024*1024 .'</strong> Byte 可用'; 
				
				?> )</span>
			</td>
			<td nowrap align="center">
			</td>
		</tr>
	
*/  ?>
		<tr class="list_entry">
			<td colspan="2">
				<img src="images/icon_disk.gif" align="absmiddle"  width="26" height="24">
				<a href="vhost_panel_ftp.php?id=<?php echo $_GET['id']; ?>">FTP登陆帐号</a>
				<span class="hint">( 已创建 <strong><?php 
				$result_ftp_users = mysql_query("select * FROM vhost_ftp WHERE VhostID=".$vhostid);
				echo (mysql_num_rows($result_ftp_users));
				?> </strong>个帐号 / <?php 
				if ($row['ftp'] == -1 )
					echo '<strong>无限</strong>';
				else
					echo '<strong>'.($row['ftp'] - mysql_num_rows($result_ftp_users)).'</strong> 个可创建'; 
				
				?> )</span>	
			</td>
			<td nowrap align="center">
				<?php if ($row['ftp'] <= mysql_num_rows($result_ftp_users) && -1 != $row['ftp']) { ?>
				<strike>添加FTP帐号</strike>
				<?php } else { ?>
				<a href="vhost_panel_ftp.php?id=<?php echo $_GET['id']; ?>&amp;action=add">添加FTP帐号</a>
				<?php } ?>
				 | <a href="vhost_panel_ftp.php?id=<?php echo $_GET['id']; ?>">管理FTP帐号</a>
			</td>
		</tr>
		<tr class="list_entry">
			<td colspan="2">
				<img src="images/icon_disk.gif" align="absmiddle"  width="26" height="24">
				<a href="vhost_panel_mysql.php?id=<?php echo $_GET['id']; ?>">MySQL数据库</a>
				<span class="hint">( 已创建 <strong><?php 
				$result_mysql_dbs = mysql_query("select * FROM vhost_mysql_db WHERE VhostID=".$row['ID']);
				echo (mysql_num_rows($result_mysql_dbs));
				?></strong> 个数据库 / <?php 
				if ($row['ftp'] == -1 )
					echo '<strong>无限</strong>';
				else
					echo '<strong>'.($row['db'] - mysql_num_rows($result_mysql_dbs)).'</strong> 个可创建';
				?> )</span>
			</td>
			<td nowrap align="center">
				<?php if ($row['db'] <= mysql_num_rows($result_mysql_dbs) && -1 != $row['db']) { ?>
				<strike>创建MySQL数据库</strike>
				<?php } else { ?>
				<a href="vhost_panel_mysql.php?id=<?php echo $_GET['id']; ?>&amp;action=add">创建MySQL数据库</a>
				<?php } ?>
				 | <a href="vhost_panel_mysql.php?id=<?php echo $_GET['id']; ?>">管理MySQL数据库</a>
			</td>
		</tr>
<?php 
/*
	<tr class="list_entry" style="height: 40px;">
		<td colspan="3" align="right">
			<a href="/linodes/disk/Caboo?id=0">升级存储空间 / 数据库</a>
		</td>
	</tr>
*/
?>
</table>
</div>
<div id="dashboard_jobQueue"> 
<table class="list jobs" border="0" width="100%" cellpadding="2" cellspacing="2">
	<tr>
		<th colspan="4">
			主机任务队列
		</th>
	</tr>
</table>
<table class="jobs" border="0" width="100%" cellpadding="2" cellspacing="2">
<?php
$result_jobs = mysql_query("select * FROM vhost_job_queue WHERE VhostID=".$_GET['id']." ORDER BY `Entered` DESC LIMIT 0 , 10");
while($row_jobs = mysql_fetch_array($result_jobs)) {
?>
		<tr>
			<td align="right" nowrap width="60" class="">
					<div class="job-status jgreen">成功</div>
			</td>
			<td class="job-cell">
				<strong><?php echo $row_jobs['Action']; ?></strong>
				<?php
				/*
				if ($row_jobs['subname'] != '' )
					echo ' - '.$row_jobs['subname']; 
				*/
				?>
				<br />
				<span class="job_note">
						进入: <?php echo timediff2(strtotime($row_jobs['Entered']),strtotime(date("Y-m-d g:i:s")),$lang); ?> - 
						耗时: <?php echo timedifffull(strtotime($row_jobs['Started']),strtotime($row_jobs['Finished']),$lang); ?>
				</span>
			</td>
			<td class="job-cell" align="right"><?php // echo $row_jobs['Result']; ?>&nbsp;</td>
		</tr>
<?php
}
?>
</table>
</div>
		</td>
		<td width="180" valign="top" style="padding-left: 15px">
			<div id="dashboard_status">
<div class="dashbox">
	<h3>网站状态 <a href="?id=<?php echo $row['ID']; ?>&amp;action=updatehttpstatus" style="float:right;" onclick="this.parentNode.removeChild(this);"><img src="images/refresh.png" alt="refresh" /></a></h3>
	<div class="dashbox-content" style="text-align: center">
		你的网站当前状态
		<p class="node-status"><?php
		if ('Available' == $row['status']) echo '未分配';
		if ('Unpaid' == $row['status']) echo '未付款';
		if ('Running' == $row['status']) echo '运行';
		if ('Stop' == $row['status']) echo '停止';
		?></p>
		<?php /*
			<input class="button" type="submit" name="action" value="停止运行" onClick="return confirm('Are you sure you want to issue a Shutdown?')">
		*/ ?>
<?php if ('Running' == $row['status']) { ?>
			<!-- <p style="font-size: 12px; margin-bottom: 0px"></p> -->
<?php } ?>
	</div>
</div>
</div>
			<div id="dashboard_stats"> 
<div class="dashbox">
	<h3>流量 <a href="?id=<?php echo $row['ID']; ?>&amp;action=updatetrafficused" style="float:right;" onclick="this.parentNode.removeChild(this);"><img src="images/refresh.png" alt="refresh" /></a></h3>
	<div class="dashbox-content">
		<ul>
			<li>每月: <?php 
				if ($row['webtraffic'] == 0 )
					echo '无限';
				else
					echo format_bytes($row['webtraffic']*1024*1024*1024); 
			?></li>
			<li>已用: <?php 
			
			if (empty($row['webtrafficUsed'])) {
				echo '未知';
			} else {
				echo format_bytes($row['webtrafficUsed']);
			}
			?></li>
			<li>空闲: <?php
				if($row['space'] == 0 )
					echo '无限';
				else 
					echo format_bytes($row['webtraffic']*1024*1024*1024 - $row['webtrafficUsed']);
			?></li>
		</ul>
		<p>
		<center>
			您已使用
			<table border="0" cellspacing="1" cellpadding="0" width="160" bgcolor="#888888">
				<tr>
					<td bgcolor="#CCCCCC" colspan="4">
						<table border="0" cellspacing="0" cellpadding="1" width="<?php echo $trafficUsedPercentage>100?'100':$trafficUsedPercentage; ?>%">
							<tr><td background="images/percent_gradient_bg.gif" bgcolor="yellow" align="right" class="smallbold"><?php echo $trafficUsedPercentage; ?>%</td></tr>
						</table>
					</td>
				</tr>
			</table>
			在你当月流量
		</center>
		</p>
	</div>
</div>
<div class="dashbox">
	<h3>存储 <a href="?id=<?php echo $row['ID']; ?>&amp;action=updatediskused" style="float:right;" onclick="this.parentNode.removeChild(this);"><img src="images/refresh.png" alt="refresh" /></a></h3>
	<div class="dashbox-content">
		<ul>
			<li>总: <?php
				if ($row['space'] == 0 )
					echo '无限';
				else
					echo format_bytes_1000($row['space']*1000*1000);
				?></li>
			<li>已用: <?php
				echo format_bytes($row['spaceUsed']);
			?></li>
			<li>空闲: <?php
				if($row['space'] == 0 )
					echo '无限';
				else 
					echo format_bytes($row['space']*1024*1024 - $row['spaceUsed']);
			?></li>
		</ul>
		<p>
		<center>
			您已使用
			<table border="0" cellspacing="1" cellpadding="0" width="160" bgcolor="#888888">
				<tr>
					<td bgcolor="#CCCCCC" colspan="4">
						<table border="0" cellspacing="0" cellpadding="1" width="<?php echo $diskUsedPercentage>100?'100':$diskUsedPercentage; ?>%">
							<tr><td background="images/percent_gradient_bg.gif" bgcolor="yellow" align="right" class="smallbold"><?php echo $diskUsedPercentage; ?>%</td></tr>
						</table>
					</td>
				</tr>
			</table>
			在所有可用存储容量
		</center>
		</p>
	</div>
</div>
<?php /*
$result_server = mysql_query("select * FROM vhost_servers WHERE ID=".$row['serverID']);
$row_server = mysql_fetch_array($result_server);
$mem_percentage = floor(((double)$row_server['memTotal']-(double)$row_server['memFree'])/(double)$row_server['memTotal']*100);
// $ = floor(((double)$row_server['memTotal']-(double)$row_server['memFree'])/(double)$row_server['memTotal']*100);
$disk_percentage = floor(((double)$row_server['diskTotal']-(double)$row_server['diskFree'])/(double)$row_server['diskTotal']*100);
$load_array = explode(" ", $row_server['loadaverage']);
// 负载 = 15分钟内负载值 * 100 / CPU核心数
$load_percentage = floor($load_array[0]*100/$row_server['cpucore']);
?>
<div class="dashbox">
	<h3>节点<a href="?id=<?php echo $row['ID']; ?>&amp;action=updatenodeinfo" style="float:right;">刷新</a></h3>
	<div class="dashbox-content">
		<center>
		<br />内存使用百分比
			<table border="0" cellspacing="1" cellpadding="0" width="160" bgcolor="#888888">
				<tr>
					<td bgcolor="#CCCCCC" colspan="4">
						<table border="0" cellspacing="0" cellpadding="1" width="<?php
						if ($mem_percentage>100)
							echo '100';
						else echo $mem_percentage; ?>%">
							<tr><td background="images/percent_gradient_bg.gif" bgcolor="yellow" align="right" class="smallbold"><?php echo $mem_percentage; ?>%</td></tr>
						</table>
					</td>
				</tr>
			</table>
		<br />节点负载百分比
			<table border="0" cellspacing="1" cellpadding="0" width="160" bgcolor="#888888">
				<tr>
					<td bgcolor="#CCCCCC" colspan="4">
						<table border="0" cellspacing="0" cellpadding="1" width="<?php
						if ($load_percentage>100)
							echo '100';
						else echo $load_percentage; ?>%">
							<tr><td background="images/percent_gradient_bg.gif" bgcolor="yellow" align="right" class="smallbold"><?php echo $load_percentage; ?>%</td></tr>
						</table>
					</td>
				</tr>
			</table>
		<br />硬盘使用百分比
			<table border="0" cellspacing="1" cellpadding="0" width="160" bgcolor="#888888">
				<tr>
					<td bgcolor="#CCCCCC" colspan="4">
						<table border="0" cellspacing="0" cellpadding="1" width="<?php
						if ($disk_percentage>100)
							echo '100';
						else echo $disk_percentage; ?>%">
							<tr><td background="images/percent_gradient_bg.gif" bgcolor="yellow" align="right" class="smallbold"><?php echo $disk_percentage; ?>%</td></tr>
						</table>
					</td>
				</tr>
			</table>
		</center>
		<br />
	</div>
</div>
<?php */ ?>
</div>
		</td>
	</tr>
</table>
</div>
<?php @include_once("footer.php") ?>