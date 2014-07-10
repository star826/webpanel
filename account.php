<?php

$page = "account.php";

@include_once("header.php");
@include_once("function.php");
@include_once("alert_function.php");

$err = array();

$result = mysql_query("select * FROM users_extension WHERE UserID=".$_SESSION['UserID']);
$row = mysql_fetch_array($result);

?>


<div id="page">
<?php EchoAlert(); ?>
	<table class="list">
		<tr>
			<th colspan="3"><?php echo $lang['account_information']; ?></th>
		</tr>

		<tr class="list_head">
			<td colspan="3"><?php echo $lang['contact']; ?></td>
		</tr>
		<?php if (!($row['companyname'] == '' && $row['firstname'] == '' && $row['address1'] == '' && $row['address2'] == '')) { ?>
		<tr class="list_entry">
			<td class="table_form_header"><?php echo $lang['address']; ?></td>
			<td>
				<?php echo @$row['companyname']; ?><br />
				<?php echo @$row['firstname']; ?> <?php echo $row['lastname']; ?><br />
				<?php echo @$row['address1']; ?><br />
				<?php echo @$row['address2']; ?>
			</td>
			<td></td>
		</tr>
		<?php } ?>
		<tr class="list_entry">
			<td class="table_form_header"><?php echo $lang['email_addr']; ?></td>
			<td><?php echo $row['email']; ?></td>
			<td class="hint"></td>
		</tr>

		<?php if (@$_SESSION['lastloginip'] != '') { ?>
		<tr class="list_head">
			<td colspan="3"><?php echo $lang['security']; ?></td>
		</tr>
		
		<?php if (@$_SESSION['lastloginip'] != '') { ?>
		<tr class="list_entry">
			<td class="table_form_header"><?php echo $lang['last_login_ip']; ?></td>
			<td><?php echo @$_SESSION['lastloginip']; ?></td>
			<td class="hint"></td>
		</tr>
		<tr class="list_entry">
			<td class="table_form_header"><?php echo $lang['last_login_time']; ?></td>
			<td><?php echo @(timediff2(strtotime($_SESSION['lastlogindate']),strtotime(date("Y-m-d g:i:s a")),$lang).' ('.$_SESSION['lastlogindate'].') '); ?></td>
			<td class="hint"></td>
		</tr>
		<?php } ?>
		<tr class="list_entry">
			<td class="table_form_header"><?php echo $lang['current_login_ip']; ?></td>
			<td><?php echo $row['lastloginip']; ?></td>
			<td class="hint"></td>
		</tr>
		<tr class="list_entry">
			<td class="table_form_header"><?php echo $lang['current_login_time']; ?></td>
			<td><?php echo (timediff2(strtotime($row['lastlogindate']),strtotime(date("Y-m-d g:i:s a")),$lang).' ('.$row['lastlogindate'].') '); ?></td>
			<td class="hint"></td>
		</tr>
		<?php } ?>

		<tr class="list_head">
			<td colspan="3"><?php echo $lang['recent_billing_activity_and_account_balance']; ?></td>
		</tr>

		<tr class="list_entry">
			<td class="table_form_header"><?php echo $lang['recent_activity']; ?></td>
			<td colspan="2">

				<table border="0" class="list">
					
<?php
$result_billing_history = mysql_query("select * FROM users_billing WHERE UserID=".$_SESSION['UserID']." ORDER BY ID DESC LIMIT 0 , 5");
while($row_billing_history = mysql_fetch_array($result_billing_history)) {
/* 	if ($row_billing_history['type'] == 0 && $row_billing_history['paid'] == 0)
		continue; */
?>
						<tr class="list_entry<?php if (@++$i%2==0) echo "_alt"; ?>">
							<td>
								<?php echo timediff2(strtotime($row_billing_history['date']),strtotime(date("Y-m-d g:i:s a")),$lang); ?><br />
								<span class="hint"><?php echo date("Y-m-d",strtotime($row_billing_history['date'])); ?></span>
							</td>
							<td>
								<?php 
								if ($row_billing_history['type'] == 1 || $row_billing_history['type'] == 2 ) 
									echo '<a href="account_invoice.php?id='.$row_billing_history['ID'].'">';
								echo $row_billing_history['description'];
								if ($row_billing_history['type'] == 1 || $row_billing_history['type'] == 2 )
									echo '</a>';
								?>
							</td>
							<td><?php 
							if ($row_billing_history['type'] == 0 ){
								if ($row_billing_history['paid'] == 0 ) {
									echo $lang['undone'];
								} else {
									echo '+'.$row_billing_history['amount'];
								}
							}
							if ($row_billing_history['type'] == 1 || $row_billing_history['type'] == 2 ){
								if ($row_billing_history['paid'] == 0 )
									echo '<span style="color:red">'.$lang['unpaid'].' '.$row_billing_history['amount'].'</span>';
								if ($row_billing_history['paid'] != 0 )
									echo '-'.$row_billing_history['amount'];
							}
							 ?></td>
						</tr>
<? } ?>

				</table>

			</td>
		</tr>

		<tr class="list_entry">
			<td class="table_form_header"><?php echo $lang['current_balance']; ?></td>
			<td>
				
					<span style="color: green; font-weight: bold"><?php echo money($row['credit']); ?><?php echo $lang['currency_symbol']; ?></span><br>
				
			</td>
			<td></td>
		</tr>
	</table>

	<br>


</div>


<?php @include_once("footer.php") ?>