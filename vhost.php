<?php
$page = "vhost.php";
@include_once("header.php");
@include_once("alert_function.php");
$result = mysql_query("select * FROM vhost WHERE owner=".$_SESSION['UserID']);
?>
<div id="page">
<p class='breadcrumb'><strong><?php echo $PanelName; ?> <?php echo $lang['vhosts']; ?></strong></p>
<?php EchoAlert(); ?>
	<table class="list sortable">
		<thead>
				<tr>
					<th class="sortfirstdesc">绑定域名</th>
					<th class="sortfirstdesc">状态</th>
					<th>方案</th>
					<th>到期日期</th>
					<th>IP</th>
					<th>位置</th>
					<th class="nosort" style="text-align: center" align="center">选项</th>
				</tr>
		</thead>
		<tfoot>
			<tr class="list_entry" style="height: 40px;">
				<td colspan="4" id="AdminFunction">
				<img src="images/progress_bar_1.gif" id="progressbar" style="display:none;" />
				</td>
				<td colspan="7" align="right">				
					<a href="vhost_add.php">添加一个虚拟主机</a>
				</td>
			</tr>
		</tfoot>
		<tbody>
<?php
while($row = mysql_fetch_array($result)) {
	if ('Unpaid' == $row['status'])
	{
		// 获取账单信息
		$result_invoice = mysql_query("select * FROM users_billing WHERE ID=".$row['orderID']);
		if (mysql_num_rows($result_invoice) >= 1)
		{
			$row_invoice = mysql_fetch_array($result_invoice);
			if (1 == $row_invoice['paid'] && 1 == $row_invoice['type']) {
				mysql_query(" UPDATE vhost SET status='Available' WHERE orderID=".$row_invoice['ID']." AND ID=".$row['ID']);
				echo '<script language=JavaScript>location.reload(true);</script>';
				exit(); // 重新载入虚拟主机列表. 
			} 
		}
	}
	elseif (daydiff(strtotime($row['duedate']),strtotime(date("Y-m-d"))) <= 15) // 在离到期15天内生成续费订单 
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
	}
?>
			<tr class="list_entry">
				<td><?php 
				if ("Unallocated" != $row['domain'])
					// echo '<a href="http://'.$row['domain'].'" target="_blank">'.$row['domain'].'</a>';
					echo '<a href="vhost_panel.php?id='.$row['ID'].'">'.$row['domain'].'</a>';
				?></td>
				<td>
				<?php if ($row['status'] == 'Stop') { ?>
				<a href="vhost_panel.php?id=<?php echo $row['ID']; ?>" style="color:red;font-wight:bold;">已停止</a>
				<?php } elseif ($row['status'] == 'Available') { ?>
				<a href="vhost_panel.php?id=<?php echo $row['ID']; ?>" style="color:#46A3FF;">未初始化</a>
				<?php } elseif ($row['status'] == 'Running') { ?>
					<?php if(daydiff(strtotime($row['duedate']),strtotime(date("Y-m-d"))) > 15){ ?>
				<a href="vhost_panel.php?id=<?php echo $row['ID']; ?>" style="color:#00BB00;">运行中</a>
					<?php } elseif (daydiff(strtotime($row['duedate']),strtotime(date("Y-m-d"))) > -1 ) { ?>
				<a href="account_invoice.php?id=<?php echo $row['orderID']; ?>" style="color:#EAC100;">即将到期</a>
					<?php } else { ?>
				<a href="account_invoice.php?id=<?php echo $row['orderID']; ?>" style="color:#EAC100;">已过期</a>
					<?php } ?>
				<?php } elseif ($row['status'] == 'Unpaid') { ?>
				<a href="account_invoice.php?id=<?php echo $row['orderID']; ?>" style="color:red;">未付款</a>
				<?php } elseif ($row['status'] == 'Remove') { ?>
				<a href="vhost_panel.php?id=<?php echo $row['ID']; ?>" style="color:red;">删除失败</a>
				<?php } ?>
				</td>
				<td><?php echo $row['planname']; ?></td>
				<td>
				<?php if (!empty($row['order'])) { ?>
				<a href="account_invoice.php?id=<?php echo $row['orderID']; ?>"><?php echo $row['duedate']; ?></a>
				<?php } else { ?>
				<?php echo $row['duedate']; ?>
				<?php } ?>
				</td>
				<td>
				<?php if ("Unallocated" != $row['ip']) { ?>
				<a href="vhost_panel_ip.php?id=<?echo $row['ID']; ?>"><?php echo $row['ip']; ?></a>
				<?php
				/* $server_ips = mysql_query("select * FROM vhost_server_ips WHERE serverID=".$row['serverID']." AND IPv=6 AND public=1");
				if (@mysql_num_rows($server_ips) > 0) echo '<a href="vhost_panel_ip.php?id='.$row['ID'].'#ipv6"><span style="color:#B766AD;">IPv6 启用</span></a>'; */
				?>
				<?php } ?>
				</td>
				<td><?php
				if ("Unallocated" != $row['location'])
					echo $row['location'];
				?></td>
				<td class="list_options" nowrap>
				<a href="vhost_panel.php?id=<?php echo $row['ID']; ?>" style="font-weight:bold;">控制面板</a>
				| 
				<a href="vhost_remove.php?id=<?php echo $row['ID']; ?>">删除</a>
				</td>
			</tr>
<?php } // 结束虚拟主机列表循环 ?>
		</tbody>
	</table>
</div>
<?php @include_once("footer.php") ?>