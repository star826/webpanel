<?php
$page = "vhost_panel_traffic.php";
@include_once("header.php");
@include_once("vhost_function.php");
@include_once("alert_function.php");
$err = array();
if (!empty($_POST['domain'])) {
	$domain = @$_POST['domain'];
	if(!is_numeric($domain)) ForceDie();
	$result_nginx_domain_conf = mysql_query("select * FROM vhost_nginx_conf WHERE ID=$domain AND vhostID=".$row['ID']." AND serverID=".$row['serverID']);
	if (mysql_num_rows($result_nginx_domain_conf) != 1) ForceDie(); 
	$row_nginx_domain_conf = mysql_fetch_array($result_nginx_domain_conf);
$commands = array(
	"visit_the_top_10_list_for_the_ip_address" => 'cat /home/wwwlogs/' . $row_nginx_domain_conf['server_name'] . '.log|awk \'{print $1}\'|sort|uniq -c|sort -nr|head -10',
	"list_the_most_visited_pages" => 'cat /home/wwwlogs/' . $row_nginx_domain_conf['server_name'] . '.log|awk \'{print $11}\'|sort|uniq -c|sort -nr|head -20',
	"list_of_largest_transmission_exe_file" => 'cat /home/wwwlogs/' . $row_nginx_domain_conf['server_name'] . '.log |awk \'($7~/\.exe/){print $10 " " $1 " " $4 " " $7}\'|sort -nr|head -20',
	"list_of_largest_transmission_rar_file" => 'cat /home/wwwlogs/' . $row_nginx_domain_conf['server_name'] . '.log |awk \'($7~/\.rar/){print $10 " " $1 " " $4 " " $7}\'|sort -nr|head -20',
	"list_more_than_30_seconds_transport_time_of_the_file" => 'cat /home/wwwlogs/' . $row_nginx_domain_conf['server_name'] . '.log |awk \'($NF > 30){print $7}\'|sort -n|uniq -c|sort -nr|head -20',
	"statistics_404_error_page" => 'awk \'($9 ~/404/)\' /home/wwwlogs/' . $row_nginx_domain_conf['server_name'] . '.log | awk \'{print $9,$7}\' | sort | uniq -d',
	"statistics_http_status" => 'cat /home/wwwlogs/' . $row_nginx_domain_conf['server_name'] . '.log |awk \'{print $9}\'|sort|uniq -c|sort -rn'
);
if (!@empty($commands[$_POST['count_method']])) {
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
		$trafficCount = trim($ssh->exec($commands[$_POST['count_method']]));
		if ($trafficCount == '')
		{
			$err[] = '该统计方式未输出任何数据.';
		} elseif (strstr($trafficCount, "No such file or directory")) {
			$err[] = '虚拟主机日志文件不存在.';
		} else {
			SetColorAlert(nl2br($trafficCount), 'yellow');
		}
	}
	SetErrAlert($err);
	header("Location: vhost_panel_traffic.php?id=".$row['ID']);
	exit();
	}
}
?>
<div id="page">
<p class='breadcrumb'><a href='index.php'><?php echo $PanelName;?></a> &raquo; <a href='vhost_panel.php?id=<?php echo $row['ID']; ?>'><?php echo $row['domain']; ?></a> &raquo; <strong>流量</strong></p>
<?php EchoAlert(); ?>
<form name="config_save" id="config_save" action="" method="post" onsubmit="document.getElementById('run_count_command').disabled = true;">
<table class="list">
	<tr>
		<th colspan="3">查看流量统计</th>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">域名</td>
		<td><select name="domain" id="domain" size="1" style="width:300px;">
<?php
		$result_vhost_nginx_conf = mysql_query("select * FROM vhost_nginx_conf WHERE vhostID=".$row['ID']);
		while($row_vhost_nginx_conf = mysql_fetch_array($result_vhost_nginx_conf)) {
?>
				<option value="<?php echo $row_vhost_nginx_conf['ID']; ?>"><?php echo $row_vhost_nginx_conf['server_name']; ?></option>
<?php } ?>
		</select></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">选择统计方式</td>
		<td colspan="2">
			<input type="radio" name="count_method" value="visit_the_top_10_list_for_the_ip_address" checked="checked" > 列出访问数前10的ip地址
			<br />
			<input type="radio" name="count_method" value="list_the_most_visited_pages" > 列出访问次数最多的页面
			<br />
			<input type="radio" name="count_method" value="list_of_largest_transmission_exe_file" > 列出传输最大的exe文件
			<br />
			<input type="radio" name="count_method" value="list_of_largest_transmission_rar_file" > 列出传输最大的rar文件
			<br />
			<input type="radio" name="count_method" value="list_more_than_30_seconds_transport_time_of_the_file" > 列出传输时间超过30秒的文件
			<br />
			<input type="radio" name="count_method" value="statistics_404_error_page" > 统计404错误页
			<br />
			<input type="radio" name="count_method" value="statistics_http_status" > 统计HTTP Status
		</td>
	</tr>
	<tr class="list_entry">
		<td></td>
		<td><span style="color:red">流量统计用于分析的日志仅在系统每日自动统计或手动统计之后所产生的流量才可用于分析。</span></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td></td>
		<td><input class="button" type="submit" name="run_count_command" id="run_count_command" value="执行"></td>
		<td class="hint"></td>
	</tr>
</table>
</form>
</div>
<?php @include_once("footer.php") ?>