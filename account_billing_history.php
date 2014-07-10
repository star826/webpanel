<?php
$page = "account_billing_history.php";
@include_once("header.php");
$result = mysql_query("select * FROM users_extension WHERE UserID=".$_SESSION['UserID']);
$row = mysql_fetch_array($result);
$credit = $row['credit'];
?>
<div id="page">
<table class="list">
	<tr>
		<th colspan="3">账单历史</th>
	</tr>
	<tr class="list_head">
		<td>日期</td>
		<td>说明</td>
		<td width="10%">金额</td>
	</tr>
<?php
$result = mysql_query("select * FROM users_billing WHERE UserID=".$_SESSION['UserID']." ORDER BY ID DESC ");
while($row = mysql_fetch_array($result)) {
	if ($row['type'] == 0 && $row['paid'] == 0)
		continue;
?>
		<tr class="list_entry<?php if (@++$i%2==0) echo "_alt"; ?>">
			<td><?php echo date("Y-m-d",strtotime($row['date'])); ?></td>
			<td>
					<?php
					if ($row['type'] == 1 || $row['type'] == 2 ) 
						echo '<a href="account_invoice.php?id='.$row['ID'].'">';
					echo $row['description'];
					if ($row['paid'] == 0)
						// echo ' - 未支付';
					if ($row['type'] == 1 || $row['type'] == 2 ) 
						echo '</a>';
					?>
			</td>
			<td><?php
			if ($row['type'] == 0 ){
				if ($row['paid'] == 0 )
					echo '<strike><span style="color:red">未完成';
				echo '+'.$row['amount'];
				if ($row['paid'] == 0 )
					echo '</span></strike>';
			}
			if ($row['type'] == 1 || $row['type'] == 2 ){
				if ($row['paid'] == 0 )
					echo '<span style="color:red">未支付 '.$row['amount'].'</span>';
				if ($row['paid'] != 0 )
					echo '-'.$row['amount'];
			}
			?></td>
		</tr>
<? } ?>
	<tr class="list_entry<?php if (@++$i%2==0) echo "_alt"; ?>">
		<td>&nbsp;</td>
		<td align="right"><strong>目前余额</strong></td>
		<td nowrap>
				<span style="color: green; font-weight: bold"><?php echo money($credit); ?> 元</span><br>
		</td>
	</tr>
</table>
</div>
<?php @include_once("footer.php") ?>