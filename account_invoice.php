<?php
$page = "account_invoice.php";
@include_once("header.php");
@include_once("alert_function.php");
$err = array();
// 获取账单信息
if (!is_numeric($_GET['id'])) ForceDie();
	$result = mysql_query("select * FROM users_billing WHERE ID=".$_GET['id']);
if (mysql_num_rows($result) == 0)
	ForceDie();
$row = mysql_fetch_array($result);
if (!IsAdmin()){
	if ($row['UserID'] != $_SESSION['UserID'])
		ForceDie();
}
// 获取当前账户余额
$result_user = mysql_query("select * FROM users_extension WHERE UserID=".$row['UserID']);
$row_user = mysql_fetch_array($result_user);
$credit = $row_user['credit'];
if (@$_POST['action'] == "paynow")
{
	if ($row['paid'] != 0 )
		$err[]='账单已支付';
	if ($credit - $row['amount'] < 0)
		$err[]='<a href="account_make_a_payment.php?credit='.$row['amount'].'" style="color:#FFF;">您的帐户余额不足，请充值。</a>';
	if(!count($err)){
		// 余额运算
		mysql_query(" UPDATE users_extension SET credit=credit-".$row['amount']." WHERE UserID=".$_SESSION['UserID']);
		// 订单状态改变
		mysql_query(" UPDATE users_billing SET paid=1 WHERE ID=".$_GET['id']." AND UserID=".$_SESSION['UserID']);
		$_SESSION['msg']['alert-success']='支付成功';
	}
	SetErrAlert($err);
	header("Location: account_invoice.php?id=".$_GET['id']);
	exit;
}
?>
<div id="page">
<?php EchoAlert(); ?>
<table class="list">
	<tr>
		<th colspan="5">账单 #<?php echo $row['ID']; ?></th>
	</tr>
		<tr class="list_head">
			<td title="服务描述">描述</td>
			<td title="服务开始日期">开始</td>
			<td title="服务结束日期">结束</td>
			<td align="right" title="设置费用/一次性支付费用">设置费</td>
			<td align="right" title="周期支付费用">周期费用</td>
		</tr>
			<tr class="list_entry">
				<td><?php echo $row['description']; ?></td>
				<td><?php echo $row['datefrom']; ?></td>
				<td><?php echo $row['dateto']; ?></td>
				<td align="right"><?php echo @$row['One_time_fee']; ?> 元</td>
				<td align="right"><?php echo $row['amount']; ?> 元</td>
			</tr>
	<tr>
		<td colspan="5" align="right">&nbsp;</td>
	</tr>
	<tr class="list_entry">
	<td colspan="5" align="right">
	<form action="" method="post">
	<input type="hidden" name="action" value="paynow" />
	<button class="pay_button <?php echo $row['paid']!=0?'pay_button_green':'pay_button_yellow';?>"<?php echo $row['paid']!=0?' onclick="return false;"':''; ?>>
		<div class="pay_title"><?php echo $row['paid']!=0?'已支付':'支付';?></div>
		<div class="pay_price"><?php echo $row['amount']; ?>元</div>
	</button>
	</p>
	</form>
	</td>
	</tr>
</table>
</div>
<?php
	if(@$_SESSION['msg']['pay-success'])
	{
		echo '<script type="text/JavaScript">alert("'.$_SESSION['msg']['pay-success'].'");</script>';
		unset($_SESSION['msg']['pay-success']);
	}
?>
<?php @include_once("footer.php") ?>