<?php
@include_once("session.php");
@include_once("alert_function.php");
$vhostid = @$_GET['id'];
if (!is_numeric($vhostid)) ForceDie();
if (IsAdmin())
	$result = mysql_query("select * FROM vhost WHERE ID=".$vhostid);
else
	$result = mysql_query("select * FROM vhost WHERE owner=".$_SESSION['UserID']." AND ID=".$vhostid);
if (mysql_num_rows($result) == 0) ForceDie();
$row = mysql_fetch_array($result);
if ($page != "vhost_remove.php"){ //是否在删除主机状态
	if ("Unpaid" == $row['status'])
	{
		$err = array();
		$err[]='您的虚拟主机还未付款';
		SetErrAlert($err);
		header("Location: account_invoice.php?id=".$row['orderID']);
		exit;
	}
	if ("Available" == $row['status'] && "Unallocated" == $row['domain']) {
		// 已付款，未配置
		if ($page != 'vhost_panel_conf.php')
		{
			$err = array();
			$err[]='您的虚拟主机还未初始化';
			SetErrAlert($err);
			header("Location: vhost_panel_conf.php?id=".$row['ID']."&type=main&action=add");
			exit;
		}
	}
	if (daydiff(strtotime($row['duedate']),strtotime(date("Y-m-d"))) <= 15) {
		if (daydiff(strtotime($row['duedate'])) <= -1) {
			SetColorAlert('<span style="color:#FFF;">您的虚拟主机已过期。</span>', 'red');
			header("Location: vhost.php");
			exit;
		} else {
			SetColorAlert('您的虚拟主机即将过期！', 'pink');
		}
	}
}
if ('Remove' == $row['status']) {
	$err = array();
	$err[]='您的虚拟主机删除失败，请 <a href="support_ticket_new.php">提交Ticket</a> 联系技术支持。';
	SetErrAlert($err);
	header("Location: vhost.php ");
	exit;
}
function AddJob($VhostID, $Action, $Result, $Entered, $Started, $Finished, $Connection) {
	return mysql_query("	INSERT INTO vhost_job_queue(VhostID,Action,Result,Entered,Started,Finished)
							VALUES(
								$VhostID,
								'$Action',
								'$Result',
								'$Entered',
								'$Started',
								'$Finished'
							)", $Connection);
}