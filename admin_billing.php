<?php
$adminpage = "admin_billing.php";
@include_once("header.php");
@include_once("function.php");
@include_once("account_function.php");
?>
<div id="page">
<table class="list sortable">
	<tr class="list_head">
		<th class="sortfirstdesc">ID</th>
		<th>用户</th>
		<th>日期</th>
		<th>说明</th>
		<th width="10%">金额</th>
		<th class="nosort" style="text-align: right">选项</th>
	</tr>
<?php
if (empty($billing_display))
	$result = mysql_query("select * FROM users_billing ");
elseif ($billing_display == 'service_paid')
	$result = mysql_query("select * FROM users_billing WHERE type=1 AND paid=1 ");
elseif ($billing_display == 'service_unpad')
	$result = mysql_query("select * FROM users_billing WHERE type=1 AND paid=0 ");
elseif ($billing_display == 'service_refund')
	$result = mysql_query("select * FROM users_billing WHERE type=0 AND description LIKE '%删除%' ");
elseif ($billing_display == 'recharge_done')
	$result = mysql_query("select * FROM users_billing WHERE type=0 AND paid=1 AND description LIKE '%支付宝交易号%' ");
elseif ($billing_display == 'recharge_undo')
	$result = mysql_query("select * FROM users_billing WHERE type=0 AND paid=0 ");
elseif ($billing_display == 'user_all' && is_numeric($_GET['userid']))
	$result = mysql_query("select * FROM users_billing WHERE UserID=" . $_GET['userid']);
if(!$result) ForceDie();
while($row = mysql_fetch_array($result)) {
/* 	if ($row['type'] == 0 && $row['paid'] == 0)
		continue; */
?>
		<tr class="list_entry">
			<td><?php echo $row['ID'] ?></td>
			<td><a href="admin_user_info.php?userid=<?php echo $row['UserID'] ?>"><?php echo UserID2UserName($row['UserID']) ?></a></td>
			<td><?php echo date("Y-m-d",strtotime($row['date'])); ?></td>
			<td>
					<?php
/* 					if ($row['type'] == 1 || $row['type'] == 2 ) 
						 echo '<a href="account_invoice.php?id='.$row['ID'].'" target="_blank">'; */
					echo $row['description'];
					if ($row['paid'] == 0)
						// echo ' - 未支付';
						
/* 					if ($row['type'] == 1 || $row['type'] == 2 ) 
						 echo '</a>'; */
					?>
			</td>
			<td><?php
			if ($row['type'] == 0 ){
				echo '+'.$row['amount'];
			} else {
				echo '-'.$row['amount'];
			}
			?></td>
			<td class="list_options"><a href="admin_billing_modify.php?id=<?php echo $row['ID']; ?>">修改</a></td>
		</tr>
<? } ?>
</table>
</div>
<?php @include_once("footer.php") ?>