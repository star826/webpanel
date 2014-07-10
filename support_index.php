<?php
$page = "support_index.php";
@include_once("header.php");
@include_once("function.php");
@include_once("account_function.php");
@include_once("alert_function.php");
?>
<div id="page">
<?php EchoAlert(); ?>
<table class="list">
	<tr>
		<th colspan="5"><?php echo $lang['open_tickets']; ?></th>
	</tr>
	<tr class="list_head">
		<td><?php echo $lang['ticket_id']; ?></td>
		<td><?php echo $lang['summary']; ?></td>
		<td><?php echo $lang['status']; ?></td>
		<td nowrap align="right"><?php echo $lang['opened']; ?></td>
		<td nowrap align="right"><?php echo $lang['last_updated']; ?></td>
	</tr>
<?php
$result_tickets = mysql_query("select * FROM tickets WHERE OpenedBy=".$_SESSION['UserID']." AND Status!='CLOSED' AND LastUpdatedBy!='' ORDER BY `LastUpdated` DESC ");
if (@mysql_num_rows($result_tickets) == 0) echo '<tr class="list_entry"><td colspan="5">'.$lang['none'].'.</td></tr>';
while($row_ticket = mysql_fetch_array($result_tickets)) {
?>
		<tr class="list_entry" style="">
			<td><a href="support_ticket.php?id=<?php echo $row_ticket['ID']; ?>"><?php echo $row_ticket['ID']; ?></a></td>
			<td><a href="support_ticket.php?id=<?php echo $row_ticket['ID']; ?>"><?php echo $row_ticket['Summary']; ?></a></td>
			<td><?php echo $row_ticket['Status']; ?></td>
			<td nowrap align="right">
				<?php echo timediff2(strtotime($row_ticket['Opened']),strtotime(date("Y-m-d g:i:s a")),$lang).' '.$lang['by']." <strong>".UserID2UserName($row_ticket['OpenedBy'])."</strong>"; ?> <br />
			</td>
			<td nowrap align="right">
				<?php echo timediff2(strtotime($row_ticket['LastUpdated']),strtotime(date("Y-m-d g:i:s a")),$lang).' '.$lang['by']." <strong>".UserID2UserName($row_ticket['LastUpdatedBy'])."</strong>"; ?> <br />
			</td>
		</tr>
<?php 
}
?>
	<tr class="list_entry" style="border-bottom-width: 0px">
		<td colspan="5" align="right">
			<a href="support_ticket_new.php"><?php echo $lang['open_a_new_support_ticket']; ?></a>
		</td>
	</tr>
	<tr colspan="5">
		<td>&nbsp;</td>
	</tr>
	<tr>
		<th colspan="5"><?php echo $lang['recently_closed_tickets']; ?></th>
	</tr>
	<tr class="list_head">
		<td><?php echo $lang['ticket_id']; ?></td>
		<td><?php echo $lang['summary']; ?></td>
		<td><?php echo $lang['status']; ?></td>
		<td nowrap align="right"><?php echo $lang['opened']; ?></td>
		<td nowrap align="right"><?php echo $lang['last_updated']; ?></td>
	</tr>
<?php
$result_tickets = mysql_query("select * FROM tickets WHERE OpenedBy=".$_SESSION['UserID']." AND Status='CLOSED' AND ClosedBy!='' ORDER BY `ClosedOn` DESC LIMIT 0 , 10");
if (@mysql_num_rows($result_tickets) == 0) echo '<tr class="list_entry"><td colspan="5">'.$lang['none'].'.</td></tr>';
while($row_ticket = mysql_fetch_array($result_tickets)) {
?>
		<tr class="list_entry" style="">
			<td><a href="support_ticket.php?id=<?php echo $row_ticket['ID']; ?>"><?php echo $row_ticket['ID']; ?></a></td>
			<td><a href="support_ticket.php?id=<?php echo $row_ticket['ID']; ?>"><?php echo $row_ticket['Summary']; ?></a></td>
			<td><?php echo $row_ticket['Status']; ?></td>
			<td nowrap align="right">
				<?php echo timediff2(strtotime($row_ticket['Opened']),strtotime(date("Y-m-d g:i:s a")),$lang).$lang['by'].' '." <strong>".UserID2UserName($row_ticket['OpenedBy'])."</strong>"; ?> <br />
			</td>
			<td nowrap align="right">
				<?php echo timediff2(strtotime($row_ticket['LastUpdated']),strtotime(date("Y-m-d g:i:s a")),$lang).' '.$lang['by']." <strong>".UserID2UserName($row_ticket['LastUpdatedBy'])."</strong>"; ?> <br />
			</td>
		</tr>
<?php 
}
?>
</table>
</div>
<?php @include_once("footer.php") ?>