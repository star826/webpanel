<?php
$adminpage = "admin_vhost_plans.php";
@include_once("header.php");
@include_once("function.php");
@include_once("alert_function.php");
?>
<div id="page">
<p class='breadcrumb'><strong>虚拟主机方案管理</strong></p>
<?php EchoAlert(); ?>
<table class="list sortable">
	<tr class="list_head">
		<th class="sortfirstasc">排序</th>
		<th>默认</th>
		<th>隐藏</th>
		<th>名称</th>
		<th>周期</th>
		<th>备份</th>
		<th>空间</th>
		<th>流量</th>
		<th>数据库</th>
		<th>域名</th>
		<th>FTP</th>
		<th>节点</th>
		<th>价格</th>
		<th class="nosort" style="text-align: right">选项</th>
	</tr>
		<?php
			$result_plans = mysql_query("select * FROM vhost_plan");
			while($row_plans = mysql_fetch_array($result_plans)) {
		?>
		<tr <?php
		if ($row_plans['planname']=='hr'){
			echo ' style="background-color:#FFFFAA;"';
			echo '>';
			for ($i=0;$i<=13;$i++){
				if ($i==0)
					echo '<td>'.$row_plans['sort'].'</td>';
				elseif ($i==13)
					echo '<td class="list_options"><a href="admin_vhost_plan_modify.php?planid='.$row_plans['ID'].'&action=modify">编辑</a></td>';
				else
					echo '<td>&nbsp;</td>';
			}
			echo '</tr>';
			continue;
		}
		if ($row_plans['hidden']=='1') echo ' style="background-color:#BBFFBB;"';
		if ($row_plans['available']<=0)
			echo ' style="background-color:#FFB5B5;"';
		else 
			echo ' style="background-color:#C4E1FF;"';
		?>>
			<td class="sortfirstasc"><?php echo $row_plans['sort']; ?></td>
			<td><?php echo $row_plans['checked']=='1'?'是':''; ?></td>
			<td><?php echo $row_plans['hidden']=='1'?'是':''; ?></td>
			<td><?php echo $row_plans['planname']; ?></td>
			<td><?php echo $row_plans['cycle']; ?></td>
			<td><?php echo $row_plans['backup']; ?></td>
			<td><?php echo $row_plans['space']; ?></td>
			<td><?php echo $row_plans['webtraffic']; ?></td>
			<td><?php echo $row_plans['db']; ?></td>
			<td><?php echo $row_plans['subdomain']; ?></td>
			<td><?php echo $row_plans['ftp']; ?></td>
			<td><?php echo $row_plans['nodes']; ?></td>
			<td><?php echo $row_plans['price']; ?></td>
			<td class="list_options">
				<a href="admin_vhost_plan_modify.php?planid=<?php echo $row_plans['ID']; ?>&action=modify">编辑</a>
			</td>
		</tr>
		<?php
		}
		?>
</table>
<table class="list">
	<tr class="list_entry" style="height: 40px;">
		<td colspan="3" align="right">
				<a href="admin_vhost_plan_modify.php?action=new">新建方案</a>
		</td>
	</tr>
</table>
<span style="color:#BBFFBB;font-size:40px;">■</span> 隐藏 
<span style="color:#FFFFAA;font-size:40px;">■</span> 换行符 
<span style="color:#FFB5B5;font-size:40px;">■</span> 售空 
<span style="color:#C4E1FF;font-size:40px;">■</span> 正常
</div>
<?php @include_once("footer.php") ?>