<?php 
$adminpage = "admin_vhost_server_ip_modify.php";
@include_once("header.php");
@include_once("function.php");
@include_once("alert_function.php");
@include_once("account_function.php");
$err = array();
if ("vhost_server_ip_modify" == @$_POST['action'])
{
	$ip_id = $_POST['ip_id'];
	$vhostid = $_POST['vhostid'];
	$result_server_ip = mysql_query("select * FROM vhost_server_ips WHERE ID=".$ip_id);
	if (mysql_num_rows($result_server_ip) == 0) ForceDie();
	$row_server_ip = mysql_fetch_array($result_server_ip);
	if (!empty($vhostid)) {
		$result_vhosts = mysql_query("select * FROM vhost WHERE ID=" . $vhostid);
		if (@mysql_num_rows($result_vhosts) != 1) {
			$err[] = '您输入的虚拟主机ID不存在!';
		}
		$row_vhost = mysql_fetch_array($result_vhosts);
		if (!count($err)) {
			$_SESSION['msg']['alert-success']='分配IP '.$row_server_ip['Address'].' 给虚拟主机 ' . $row_vhost['domain'] . ' 成功!';
			mysql_query("UPDATE vhost_server_ips SET VhostID=".$vhostid." WHERE ID=".$ip_id);
		}
	} else {
		$_SESSION['msg']['alert-success']='撤销IP '.$row_server_ip['Address'].' 分配成功!';
		mysql_query("UPDATE vhost_server_ips SET VhostID=NULL WHERE ID=".$ip_id);
	}
	if (count($err)){
		SetErrAlert($err);
	} else {
		header("Location: admin_vhost_server_ips.php?id=".$row_server_ip['serverID']);
		exit();
	}
}
$result_server_ip = mysql_query("select * FROM vhost_server_ips WHERE ID=".$_GET['id']);
if (mysql_num_rows($result_server_ip) == 0) ForceDie();
$row_server_ip = mysql_fetch_array($result_server_ip);
?>
<div id="page">
<?php EchoAlert(); ?>
<form name="config_save" id="config_save" action="" method="post" onsubmit="document.getElementById('confirmed').disabled = true;">
<table class="list">
	<tr>
		<th colspan="3">虚拟主机节点服务器IP地址编辑</th>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">VhostID</td>
		<td>
			<input name="vhostid" id="vhostid" type="text" value="<?php echo @($row_server_ip['VhostID']); ?>" maxlength="" size="18" autocomplete="off" />
		</td>
		<td class="hint">请输入欲分配的虚拟主机ID, 留空撤销分配</td>
	</tr>
	<tr class="list_entry">
		<td></td>
		<td><input tabindex="3" class="button" type="submit" name="confirmed" id="confirmed" value="确认"></td>
		<td class="hint"></td>
	</tr>
</table>
<input type="hidden" name="ip_id" value="<?php echo $_GET['id']; ?>" />
<input type="hidden" name="action" value="vhost_server_ip_modify" />
</form>
</div>
<?php @include_once("footer.php") ?>