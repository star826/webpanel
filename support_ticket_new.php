<?php
$page = "support_ticket_new.php";
@include_once("header.php");
@include_once("alert_function.php");
if (@$_POST['action'] == 'open')
{
	$err = array();
	$summary = 					((@$_POST['summary']));
	$Entity = 					trim(mysql_real_escape_string(@$_POST['Entity']));
	$description = 				@$_POST['content'];
	$RegardingArray = explode("|", $Entity);
	$Regarding = $RegardingArray[0];
	$RegardingURL = @$RegardingArray[1];
	if ($summary == '' || $Entity == '' || $description == '')
		$err[]=$lang['summary_description_regarding_required'];
	$description = nl2br($description);
	if(!count($err))
	{
		mysql_query("	INSERT INTO tickets(Summary,Description,Status,Opened,OpenedBy,LastUpdated,LastUpdatedBy,Regarding,RegardingURL)
						VALUES(
							".check_input($summary).",
							".check_input($description).",
							'OPEN',
							NOW(),
							".$_SESSION['UserID'].",
							NOW(),
							".$_SESSION['UserID'].",
							'".$Regarding."',
							'".$RegardingURL."'
						)");		
		$TicketID = mysql_insert_id();
		$_SESSION['msg']['alert-success']=$lang['ticket_submit_success'];
		header("Location: support_ticket.php?id=".$TicketID);
		exit; 
	}
	SetErrAlert($err);
}
?>
<div id="page">
<p class='breadcrumb'><a href='support_index.php'><?php echo $lang['support']; ?></a> &raquo; <strong><?php echo $lang['open_a_ticket']; ?></strong></p>
<?php EchoAlert(); ?>
<form name="ticket_new_save" id="ticket_new_save" action="" method="post">
<table class="list">
	<tr>
		<th colspan="2"><?php echo $lang['open_a_ticket']; ?></th>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header"><?php echo $lang['summary']; ?></td>
		<td><input name="summary" id="summary"  type="text" maxlength="40" size="40" value="<?php echo @$summary; ?>"  /></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header"><?php echo $lang['regarding']; ?></td>
		<td>
			<select name="Entity">
				<option value=""><?php echo $lang['please_select']; ?></option>
					<?php 
					$result_service_vhosts = mysql_query("select * FROM vhost WHERE owner=".$_SESSION['UserID']);
					while($row_service_vhosts = mysql_fetch_array($result_service_vhosts)) {
					?>
					<option value="<?php
					$server_summary = $lang['vhost'].': ';
					if ("Unallocated" == $row_service_vhosts['domain']) {
						$server_summary .= 'vhost'.$row_service_vhosts['ID'];
						if ($row_service_vhosts['status'] == 'Available')
							$server_summary .= ' ('.$lang['available'].')';
						if ($row_service_vhosts['status'] == 'Unpaid')
							$server_summary .= ' ('.$lang['unpaid'].')';
					} else {
						$server_summary .= $row_service_vhosts['domain'];
						if ($row_service_vhosts['status'] == 'Stop')
							$server_summary .= ' ('.$lang['suspended'].')';
					}
					echo $server_summary;
					echo '|vhost_panel.php?id='.$row_service_vhosts['ID'];
					?>"><?php
					echo $server_summary;
					?></option>
					<?php } ?>
				<option value="<?php echo $lang['other_general_billing_etc']; ?>" <?php echo @$Entity=='other'?'selected="selected"':''; ?>><?php echo $lang['other_general_billing_etc']; ?></option>
			</select>
		</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header"><?php echo $lang['description']; ?></td>
		<td>
			<textarea name="content" style="width:600px;height:200px;"><?php echo get_magic_quotes_gpc()?stripslashes(@$description):@$description; ?></textarea>
		</td>
	</tr>
	<tr class="list_entry">
		<td></td>
		<td colspan="3">
			<input type="hidden" name="action" value="open" />
			<input class="button" type="submit" value="<?php echo $lang['open_ticket']; ?>">
		</td>
	</tr>
</table>
</form>
</div>
<?php @include_once("footer.php") ?>