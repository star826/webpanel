<?php
$page = "vhost_panel_ip.php";
@include_once("header.php");
@include_once("vhost_function.php");
?>
<div id="page">
<p class='breadcrumb'><a href='index.php'><?php echo $PanelName;?></a> &raquo; <a href='vhost_panel.php?id=<?php echo $row['ID']; ?>'><?php echo $row['domain']; ?></a> &raquo; <strong>主机IP地址</strong></p><form name="config_save" id="config_save" action="" method="post">
<?php EchoAlert(); ?>
<table class="list">
	<tr>
		<th colspan="3">主机IP地址</th>
	</tr>
	<tr class="list_head">
		<td colspan="3">IP地址</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">IPv4 地址</td>
		<td nowrap>
			<?php 
			$server_ips = mysql_query("select * FROM vhost_server_ips WHERE serverID=".$row['serverID']." AND IPv=4 AND Private=0"); 
			while($row_server_ip = mysql_fetch_array($server_ips)) {
				echo $row_server_ip['Address']." [共享]<br />";
			}
			?>
			<?php 
			$server_ips = mysql_query("select * FROM vhost_server_ips WHERE serverID=".$row['serverID']." AND IPv=4 AND Private=1 AND VhostID=".$row['ID']); 
			while($row_server_ip = mysql_fetch_array($server_ips)) {
				echo '<span style="color:red">'.$row_server_ip['Address']." [独享]</span><br />";
			}
			?>
		</td>
	</tr>
<?php
$server_ips = mysql_query("select * FROM vhost_server_ips WHERE serverID=".$row['serverID']." AND IPv=6 AND Private=0");
if (mysql_num_rows($server_ips) > 0)
{
?>
<a name="ipv6"></a>
	<tr class="list_entry">
		<td class="table_form_header">IPv6 地址</td>
		<td nowrap>
			<?php
			while($row_server_ip = mysql_fetch_array($server_ips)) {
				echo $row_server_ip['Address']." [共享]<br />";
			}
			?>
			<?php
			$server_ips = mysql_query("select * FROM vhost_server_ips WHERE serverID=".$row['serverID']." AND IPv=6 AND Private=1 AND VhostID=".$row['ID']);
			while($row_server_ip = mysql_fetch_array($server_ips)) {
				echo $row_server_ip['Address']." [独享]<br />";
			}
			?>
		</td>
	</tr>
<?php }  ?>
</table>
</div>
<?php @include_once("footer.php") ?>