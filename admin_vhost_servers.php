<?php
$adminpage = "admin_vhost_servers.php";
@include_once("header.php");
@include_once("function.php");
@include_once("alert_function.php");
if (@$_GET['action'] == 'update')
{
/* 	echo '<div id="page">';
	echo '<p class=\'breadcrumb\'><strong>虚拟主机节点服务器管理</strong></p>';
	echo '<p id="progressbar"><img src="images/progress_bar_1.gif" /></p>';
	echo '<p>开始更新节点服务器信息</p>';
	flush2(); */
	if(!empty($_GET['serverid']))
		UpdateServer($_GET['serverid']);
/* 	echo '<p>节点服务器信息更新成功</p>';
	echo '<p><a href="admin_vhost_servers.php">返回</a></p>';
	echo '<script language=JavaScript>document.getElementById(\'progressbar\').style.display = "none";</script>';
	echo '<script language=JavaScript>top.location=\'admin_vhost_servers.php\';</script>';
	echo '</div><br />';
	@include_once("footer.php");
	flush2();
	exit();	 */
}
function UpdateServer($serverID){
	set_include_path('api/ssh/' . PATH_SEPARATOR . 'phpseclib');
	@include_once("api/ssh/Net/SSH2.php");
	if ($serverID == 'ALL')
		$result = mysql_query("select * FROM vhost_servers" );
	else
		$result = mysql_query("select * FROM vhost_servers WHERE ID=".$serverID );
	if (mysql_num_rows($result) == 0) return false; //无服务器信息
	//$row = mysql_fetch_array($result);
	while($row = mysql_fetch_array($result)) {
		$ssh = new Net_SSH2($row['ip'],$row['port']);
		if (!$ssh->login($row['root'], $row['passwd'])){
			mysql_query(" UPDATE vhost_servers SET 
				downtime=1,
				lastupdate=NOW()
				WHERE ID=".$row['ID']."
		");
			continue; // 无法连接该节点服务器, 继续执行下一个
		}
		if (!empty($row['cpucore']) && !empty($row['cpuinfo'])){
			$server_cpucore = $row['cpucore'];
			$server_cpuinfo = $row['cpuinfo'];
		} else {
			$server_cpucore = trim($ssh->exec("cat /proc/cpuinfo | grep name | cut -f2 -d: | uniq -c | awk '{print $1}'")); 
			$server_cpuinfo = trim($ssh->exec('cat /proc/cpuinfo | grep name | cut -f2 -d: | uniq -c | awk \'{print $2" "$3" "$4" "$5" "$6" "$7" "$8" "$9" "$10" "$11" "$12" "$13" "$14" "$15" "$16" "$17" "$18}\'')); // CPU信息
		}
		$server_uptime_temp = $ssh->exec('cat /proc/uptime');
		$server_uptime_temp = explode(" ", $server_uptime_temp);
		$server_uptime_temp = trim($server_uptime_temp[0]);
		$min = $server_uptime_temp / 60;
		$hours = $min / 60;
		$days = floor($hours / 24);
		$server_uptime_days = $days; //服务器在线时间 days
		$hours = floor($hours - ($days * 24));
		$min = floor($min - ($days * 60 * 24) - ($hours * 60));
		if ($days !== 0) $server_uptime = $days."天";
		if ($hours !== 0) $server_uptime .= $hours."小时";
		$server_uptime .= $min."分钟"; //服务器在线时间 date
		$server_meminfo_temp = $ssh->exec('cat /proc/meminfo');
		preg_match_all("/MemTotal\s{0,}\:+\s{0,}([\d\.]+).+?MemFree\s{0,}\:+\s{0,}([\d\.]+).+?Cached\s{0,}\:+\s{0,}([\d\.]+).+?SwapTotal\s{0,}\:+\s{0,}([\d\.]+).+?SwapFree\s{0,}\:+\s{0,}([\d\.]+)/s", $server_meminfo_temp, $buf);
		preg_match_all("/Buffers\s{0,}\:+\s{0,}([\d\.]+)/s", $server_meminfo_temp, $buffers);
		$server_memory_total = round($buf[1][0]/1024, 2); //总内存
		$server_memory_free = round($buf[2][0]/1024, 2); // 可用内存
		$server_swap_total = round($buf[4][0]/1024, 2); // SWAP总
		$server_swap_free = round($buf[5][0]/1024, 2); // SWAP可用
		$server_load_temp = $ssh->exec('cat /proc/loadavg');
		$server_load_temp = explode(" ",$server_load_temp );
		$server_load_temp = array_chunk($server_load_temp, 4);
		$server_load = implode(" ", $server_load_temp[0]); // 服务器负载
		$server_disk_total = $ssh->exec("df | grep '/' | awk '{sum+=$2} END {print sum}'"); //总硬盘
		$server_disk_free = $ssh->exec("df | grep '/' | awk '{sum+=$4} END {print sum}'"); //空闲硬盘
		$server_netio = $ssh->exec("cat /proc/net/dev | grep -E 'eth|venet'");
		$server_netio_array = explode("\n", $server_netio);
		foreach ($server_netio_array as &$line) {
			preg_match_all( "/([^\s]+):[\s]{0,}(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/", $line, $info );
			@$server_netinput += $info[2][0]; // 网卡流入流量
			@$server_netout   += $info[10][0]; // 网卡流出流量
		}
		mysql_query(" UPDATE vhost_servers SET 
			cpucore=".$server_cpucore.",
			cpuinfo='".$server_cpuinfo."',
			uptime='".$server_uptime."',
			uptimedays=".$server_uptime_days.",
			loadaverage='".$server_load."',
			memTotal=".$server_memory_total.",
			memFree=".$server_memory_free.",
			swapTotal=".$server_swap_total.",
			swapFree=".$server_swap_free.",
			diskTotal=".$server_disk_total.",
			diskFree=".$server_disk_free.",
			netInput='".$server_netinput."',
			netOut='".$server_netout."',
			downtime=0,
			lastupdate=NOW()
			WHERE ID=".$row['ID']."
		");
	}
	return true;
}
?>
<script language=JavaScript>
function UpdateServer(serverid)
{
if (document.getElementById('progressbar').style.display == "")
  {
  alert('一个线程正在执行，请等待线程完毕。');
  return;
  }
document.getElementById('progressbar').style.display = "";
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    //document.getElementById("myDiv").innerHTML=xmlhttp.responseText;\
	document.getElementById('progressbar').style.display = "none";
	location.reload();
    }
  }
xmlhttp.open("GET","?action=update&serverid="+serverid,true);
xmlhttp.send();
}
</script>
<div id="page">
<p class='breadcrumb'><strong>虚拟主机节点服务器管理</strong></p>
<?php EchoAlert(); ?>
<table class="list sortable">
	<tr class="list_head">
		<th class="sortfirstasc">节点</th>
		<th>IP地址</th>
		<th>虚拟主机</th>
		<th>在线时间</th>
		<th>负载</th>
		<th>内存</th>
		<th>硬盘</th>
		<th>最后更新</th>
		<th class="nosort" style="text-align: center">设置</th>
	</tr>
		<?php
			$result_servers = mysql_query("select * FROM vhost_servers");
			while($row_server = mysql_fetch_array($result_servers)) {
		?>
		<tr class="list_entry" <?php
		if ($row_server['downtime'] == 1)
			echo ' style="background-color:red;"';
		elseif ($row_server['ID'] == @$_GET['highlightserverid'])
			echo ' style="background-color:#97CBFF;"';
		?>>
			<td class="sortfirstdesc"><?php echo $row_server['alias']; ?></td>
			<td><?php 
			
			$server_ips = mysql_query("select * FROM vhost_server_ips WHERE serverID=".$row_server['ID']." AND IPv=4");
			$ips_alt = mysql_num_rows($server_ips).' IPv4';
			$server_ips = mysql_query("select * FROM vhost_server_ips WHERE serverID=".$row_server['ID']." AND IPv=6");
			if (@mysql_num_rows($server_ips) > 0)
				$ips_alt .= ', '.mysql_num_rows($server_ips).' IPv6';
			echo '<a title="'.$ips_alt.'">'.$row_server['ip'].'</a>'; 
			?></td>
			<td><?php
			echo '<a title="已用">'.((int)$row_server['vhostTotal']-(int)$row_server['vhostFree']).'</a> / <a title="最大虚拟主机数">'.$row_server['vhostTotal']."</a>";
			?></td>
			<td><?php echo $row_server['uptime']; ?></td>
			<td><?php echo $row_server['loadaverage']; ?></td>
			<td><?php echo $row_server['memFree'].'M'; ?></td>
			<td><?php echo round((int)$row_server['diskFree']/1024/1024,2).'G'; ?></td>
			<td><?php echo $row_server['lastupdate']; ?></td>
			<td class="list_options">
				<a href="admin_vhost_server_conf.php?id=<?php echo $row_server['ID']; ?>&action=edit">查看</a> 
				|
				<a href="admin_vhost_server_ips.php?id=<?php echo $row_server['ID']; ?>">IP地址池</a> 
				|
				<a href="javascript:UpdateServer(<?php echo $row_server['ID']; ?>);">更新</a>
			</td>
		</tr>
		<?php
		}
		?>
</table>
<table class="list">
	<tr class="list_entry" style="height: 40px;">
		<td id="progressbar" style="display:none;">
			<img src="images/progress_bar_1.gif" />
		</td>
		<td colspan="4" align="right">
				<a href="javascript:UpdateServer('ALL');" onclick="return confirm('批量更新将会导致长时间载入其他页面假死仅对于当前SESSION, 请在更新时勿进行其他操作, 确认继续执行?');">更新所有服务器状态</a> |
				<a href="admin_vhost_server_conf.php?action=add">添加一个节点服务器</a> 
		</td>
	</tr>
</table>
<span style="color:red;font-size:40px;">■</span> 宕机 
<span style="color:#97CBFF;font-size:40px;">■</span> 当前选中虚拟主机所在节点 
</div>
<?php @include_once("footer.php") ?>