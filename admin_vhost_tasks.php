<?php
$adminpage = "admin_vhost_tasks.php";
@include_once("config.php");
@include_once("function.php");
@include_once("alert_function.php");
@include_once("session.php");
if (@$_GET['action'] == 'checkabouttoexpire' && $TaskAPI == @$_GET['api'])
{
	$i = 0;
	$result = mysql_query("select * FROM vhost");
	while($row = mysql_fetch_array($result)) {
		if(daydiff(strtotime($row['duedate']),strtotime(date("Y-m-d"))) <= 15) // 在离到期15天内生成续费订单 
		{
			// 检查是否已经创建订单
			$result_invoice = mysql_query("select * FROM users_billing WHERE ID=".$row['orderID']);
			if (mysql_num_rows($result_invoice) >= 1)
			{
				// 检查是否已支付
				$row_invoice = mysql_fetch_array($result_invoice);
				if (1 == $row_invoice['paid'] && 2 == $row_invoice['type']) {
					mysql_query(" UPDATE vhost SET duedate='".$row_invoice['dateto']."', orderID=NULL WHERE orderID=".$row_invoice['ID']." AND ID=".$row['ID']);
				}
				if (1 == $row_invoice['paid'] && 1 == $row_invoice['type']) {
					// 未创建续费订单
					$create_renew_order = true;
				
				}
			} else {
				// 订单已不存在
				$create_renew_order = true;
			}
			if (@$create_renew_order)
			{
				$i++;
				$create_renew_order = false; // 清除创建续费订单状态
				$dateto = date('Y-m-d',strtotime("+".$row['cycle']." month", strtotime($row['duedate'])));
				$price = $row['price'] * $row['cycle'];
				mysql_query("	INSERT INTO users_billing(UserID,type,date,datefrom,dateto,description,amount,paid)
										VALUES(
											".$row['owner'].",
											2,
											NOW(),
											'".$row['duedate']."',
											'".$dateto."',
											'".$row['planname']." - 续费 - 付款周期".$row['cycle']."个月 - ".$price."元',
											".$price.",
											0
										)");
				$orderID = mysql_insert_id();
				mysql_query(" UPDATE vhost SET orderID=".$orderID." WHERE ID=".$row['ID']);
			}
		}
	}
	$_SESSION['msg']['alert-success']='检查即将到期虚拟主机并生成订单成功, 共生成' . $i . '个续费订单.';
	header("Location: admin_vhost_tasks.php");
	exit();
}
if (@$_GET['action'] == 'updatediskandtrafficused' && $TaskAPI == @$_GET['api'])
{
	$i_servers = 0;
	$i_vhosts = 0;
	$result_servers = mysql_query("select * FROM vhost_servers");
	while($row_server = mysql_fetch_array($result_servers)) {
		$result = mysql_query("select * FROM vhost WHERE serverID=".$row_server['ID']);
		if (mysql_num_rows($result) > 0) {
			@$i_servers++;
			set_include_path('api/ssh/' . PATH_SEPARATOR . 'phpseclib');
			@include_once("api/ssh/Net/SSH2.php");
			$ssh = new Net_SSH2($row_server['ip'],$row_server['port']); 	
			if (!@$ssh->login($row_server['root'], $row_server['passwd']))
				continue;
		}
		while($row = mysql_fetch_array($result)) {
			@$i_vhosts++;
				$commands   = 'du -sb "' . $row['root'] . '" | awk \'{print $1}\' ';
				$diskUsed = trim($ssh->exec($commands));
				if (is_numeric($diskUsed))
					mysql_query("UPDATE vhost SET spaceUsed=" . $diskUsed . " WHERE ID=".$row['ID'], $con);
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
				if (is_numeric($trafficUsedTotal) || $trafficUsedTotal == 0)
				{
					mysql_query("UPDATE vhost SET webtrafficUsed=webtrafficUsed+" . $trafficUsedTotal . " WHERE ID=".$row['ID'], $con);
				}
		} // 结束节点服务器中的虚拟主机的循环
		if (mysql_num_rows($result) > 0) {
			$ssh->exec('/etc/init.d/nginx reload;/etc/init.d/nginx reload;/etc/init.d/nginx reload;'); //重启Nginx 以便于生成新的日志文件, 重启三次, 防止发生错误
		}
	} // 结束节点服务器循环
	$_SESSION['msg']['alert-success']="刷新所有虚拟主机已用磁盘及流量成功. 节点".$i_servers."个, 虚拟主机".$i_vhosts."个. ";
	header("Location: admin_vhost_tasks.php");
	exit();
}

if ("cleartrafficused" == @$_GET['action'] && $TaskAPI == @$_GET['api'])
{
	$i_vhosts = 0;
	$result = mysql_query("select * FROM vhost");
	while($row = mysql_fetch_array($result)) {		
		if( date("d", strtotime($row['duedate'])) == date("d") )
		{
			if(mysql_query("UPDATE vhost SET 
							webtrafficUsed=0
							WHERE ID=".$row['ID']))
							{
								$i_vhosts++;
							}
		}
	}
	$_SESSION['msg']['alert-success']="清空虚拟主机流量使用成功. 影响".$i_vhosts."个虚拟主机. ";
	header("Location: admin_vhost_tasks.php");
	exit();
}
@include_once("header.php");
?>
<div id="page">
<p class='breadcrumb'><strong>虚拟主机任务计划</strong></p>
<?php EchoAlert(); ?>
<table class="list sortable">
	<tr class="list_head">
		<th class="nosort">任务名</th>
		<th style="text-align: right" class="nosort">选项</th>
	</tr>
	<tr>
		<td>检查即将到期虚拟主机并生成订单 (每天0点执行一次)</td>
		<td class="list_options">
			<a href="admin_vhost_tasks.php?action=checkabouttoexpire&api=<?php echo @$TaskAPI; ?>" onclick="this.parentNode.removeChild(this);" title="右键复制链接地址后添加URL定时任务计划即可">执行</a> 
		</td>
	</tr>
	<tr>
		<td>更新所有虚拟主机磁盘以及流量使用 (每小时执行一次)</td>
		<td class="list_options">
			<a href="admin_vhost_tasks.php?action=updatediskandtrafficused&api=<?php echo @$TaskAPI; ?>" onclick="this.parentNode.removeChild(this);" title="右键复制链接地址后添加URL定时任务计划即可">执行</a> 
		</td>
	</tr>
	<tr>
		<td>所有虚拟主机每月底清空流量使用 (每天0点执行一次)</td>
		<td class="list_options">
			<a href="admin_vhost_tasks.php?action=cleartrafficused&api=<?php echo @$TaskAPI; ?>" onclick="this.parentNode.removeChild(this);" title="右键复制链接地址后添加URL定时任务计划即可">执行</a> 
		</td>
	</tr>
</table>
</div>
<?php @include_once("footer.php") ?>