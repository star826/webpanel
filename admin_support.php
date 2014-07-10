<?php
$adminpage = "admin_support.php";
@include_once("header.php");
@include_once("function.php");
@include_once("account_function.php");
@include_once("alert_function.php");
if ( 'close' == @$_GET['action'] )
{
	$ticketid = $_GET['ticketid'];
	mysql_query("UPDATE tickets SET Status='CLOSED', ClosedOn=NOW(), ClosedBy=".$_SESSION['UserID']." WHERE ID=".$ticketid);
	$_SESSION['msg']['alert-success']=$lang['close_success'];
	header("Location: admin_support.php");
	exit; 
}
if ( 'delete' == @$_GET['action'] )
{
	$ticketid = $_GET['ticketid'];
	mysql_query("DELETE FROM tickets WHERE ID=".$ticketid." OR TicketID=".$ticketid);
	$_SESSION['msg']['alert-success']=$lang['delete_success'];
	header("Location: admin_support.php");
	exit; 
}
?>
<div id="page">
<?php EchoAlert(); ?>
<table class="list">
	<tr>
		<th colspan="6"><?php echo $lang['open_tickets']; ?></th>
	</tr>
	<tr class="list_head">
		<td><?php echo $lang['ticket_id']; ?></td>
		<td><?php echo $lang['summary']; ?></td>
		<td><?php echo $lang['status']; ?></td>
		<td nowrap align="right"><?php echo $lang['opened']; ?></td>
		<td nowrap align="right"><?php echo $lang['last_updated']; ?></td>
		<td nowrap align="right">选项</td>
	</tr>
<?php
$result_tickets = mysql_query("select * FROM tickets WHERE Status!='CLOSED' AND LastUpdatedBy!='' ORDER BY `LastUpdated` DESC ");
if (@mysql_num_rows($result_tickets) == 0) echo '<tr class="list_entry"><td colspan="5">'.$lang['none'].'.</td></tr>';
while($row_ticket = mysql_fetch_array($result_tickets)) {
?>
		<tr class="list_entry" style="">
			<td><a href="admin_support_ticket.php?id=<?php echo $row_ticket['ID']; ?>"><?php echo $row_ticket['ID']; ?></a></td>
			<td><a href="admin_support_ticket.php?id=<?php echo $row_ticket['ID']; ?>"><?php echo $row_ticket['Summary']; ?></a></td>
			<td><?php echo $row_ticket['Status']; ?></td>
			<td nowrap align="right">
				<?php echo timediff2(strtotime($row_ticket['Opened']),strtotime(date("Y-m-d g:i:s a")),$lang).' '.$lang['by']." <strong>".UserID2UserName($row_ticket['OpenedBy'])."</strong>"; ?> <br />
			</td>
			<td nowrap align="right">
				<?php echo timediff2(strtotime($row_ticket['LastUpdated']),strtotime(date("Y-m-d g:i:s a")),$lang).' '.$lang['by']." <strong>".UserID2UserName($row_ticket['LastUpdatedBy'])."</strong>"; ?> <br />
			</td>
			<td nowrap align="right"><a href="admin_support.php?action=close&ticketid=<?php echo $row_ticket['ID']; ?>" onclick="return confirm('确认?')">关闭</a>
			| <a href="admin_support.php?action=delete&ticketid=<?php echo $row_ticket['ID']; ?>" onclick="return confirm('确认?')">删除</a></td>
		</tr>
<?php 
}
?>
	<tr colspan="5">
		<td>&nbsp;</td>
	</tr>
	<tr>
		<th colspan="6"><?php echo $lang['recently_closed_tickets']; ?></th>
	</tr>
	<tr class="list_head">
		<td><?php echo $lang['ticket_id']; ?></td>
		<td><?php echo $lang['summary']; ?></td>
		<td><?php echo $lang['status']; ?></td>
		<td nowrap align="right"><?php echo $lang['opened']; ?></td>
		<td nowrap align="right"><?php echo $lang['last_updated']; ?></td>
		<td nowrap align="right">选项</td>
	</tr>
<?php
$result_tickets = mysql_query("select * FROM tickets WHERE Status='CLOSED' AND ClosedBy!='' ORDER BY `ClosedOn` DESC LIMIT 0 , 100");
if (@mysql_num_rows($result_tickets) == 0) echo '<tr class="list_entry"><td colspan="5">'.$lang['none'].'.</td></tr>';
while($row_ticket = mysql_fetch_array($result_tickets)) {
?>
		<tr class="list_entry" style="">
			<td><a href="admin_support_ticket.php?id=<?php echo $row_ticket['ID']; ?>"><?php echo $row_ticket['ID']; ?></a></td>
			<td><a href="admin_support_ticket.php?id=<?php echo $row_ticket['ID']; ?>"><?php echo $row_ticket['Summary']; ?></a></td>
			<td><?php echo $row_ticket['Status']; ?></td>
			<td nowrap align="right">
				<?php echo timediff2(strtotime($row_ticket['Opened']),strtotime(date("Y-m-d g:i:s a")),$lang).$lang['by'].' '." <strong>".UserID2UserName($row_ticket['OpenedBy'])."</strong>"; ?> <br />
			</td>
			<td nowrap align="right">
				<?php echo timediff2(strtotime($row_ticket['LastUpdated']),strtotime(date("Y-m-d g:i:s a")),$lang).' '.$lang['by']." <strong>".UserID2UserName($row_ticket['LastUpdatedBy'])."</strong>"; ?> <br />
			</td><td nowrap align="right"><a href="admin_support.php?action=delete&ticketid=<?php echo $row_ticket['ID']; ?>" onclick="return confirm('确认?')">删除</a></td>
		</tr>
<?php 
}
?>
</table>
</div>
<?php @include_once("footer.php") ?>