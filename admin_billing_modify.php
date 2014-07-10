<?php
$adminpage = "admin_billing_modify.php";
@include_once("header.php");
@include_once("function.php");
@include_once("alert_function.php");
@include_once("account_function.php");
$invoice_id = empty($_GET['id'])?@$_POST['invoice_id']:@$_GET['id'];
if (!is_numeric($invoice_id))	ForceDie();
$result_billings = mysql_query("select * FROM users_billing WHERE ID=" . $invoice_id);
if (@mysql_num_rows($result_billings) != 1) ForceDie();
$row_invoice = mysql_fetch_array($result_billings);
if (@$_POST['action'] == 'invoice_modify')
{
	$invoice_status = @$_POST['status'];
	$invoice_date = @$_POST['date'];
	$invoice_datefrom = @$_POST['datefrom'];
	$invoice_dateto = @$_POST['dateto'];
	$invoice_description = @$_POST['description'];
	// $invoice_one_time_fee = @$_POST['one_time_fee'];
	$invoice_amount = @$_POST['amount'];
	$err = array();
	if(!count($err))
	{
		if(mysql_query("UPDATE users_billing SET 		
		date='$invoice_date',
		datefrom='$invoice_datefrom',
		dateto='$invoice_dateto',
		description='$invoice_description',
		amount=$invoice_amount,
		paid=$invoice_status
		WHERE ID=".$invoice_id))
		{
			$_SESSION['msg']['alert-success']='修改账单成功';
			header("Location: admin_billing_modify.php?id=".$invoice_id);
			exit();
		} else {
			$err[]='修改VPS配置失败, 请检查输入的参数!';
		}
	}
	SetErrAlert($err);
}
if ("invoice_delete" == @$_POST['action'])
{
	if (mysql_query("DELETE FROM users_billing WHERE ID=$invoice_id"))
	{
		$_SESSION['msg']['alert-success']='删除账单成功';
		header("Location: admin_billing.php?display=recharge_done");
		exit();
	} else {
		$err[]='删除账单失败, 未知错误!';
		SetErrAlert($err);
	}
}
?>
<div id="page">
<?php EchoAlert(); ?>
<form name="config_save" id="config_save" action="" method="post" onsubmit="document.getElementById('confirmed').disabled = true;">
<table class="list">
	<tr>
		<th colspan="3">账单编辑</th>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">所有者</td>
		<td><span style="color: green;"><?php echo UserID2UserName($row_invoice['UserID']); ?></span> | 
		<a href="admin_user_info.php?userid=<?php echo $row_invoice['UserID']; ?>">查看该用户信息</a>
		</td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">账单ID</td>
		<td><span style="color: green;"><?php echo ($row_invoice['ID']); ?></span><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">账单状态</td>
		<td>
			<select name="status" id="status" size="1">
				<option value="0" <?php if ($row_invoice['paid']==0) echo 'selected="selected"'; ?>>未付款</option>
				<option value="1" <?php if ($row_invoice['paid']==1) echo 'selected="selected"'; ?>>已付款(或充值成功)</option>
			</select>
		</td>
		<td class="hint">修改已付款的账单并不会发生什么</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">账单创建日期</td>
		<td>
			<input name="date" id="date" type="text" value="<?php echo $row_invoice['date']; ?>" maxlength="" size="18" autocomplete="off" required />
		</td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">服务开始日期</td>
		<td>
			<input name="datefrom" id="datefrom" type="text" value="<?php echo $row_invoice['datefrom']; ?>" maxlength="" size="18" autocomplete="off" />
		</td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">服务结束日期</td>
		<td>
			<input name="dateto" id="dateto" type="text" value="<?php echo $row_invoice['dateto']; ?>" maxlength="" size="18" autocomplete="off" />
		</td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">描述信息</td>
		<td>
			<input name="description" id="description" type="text" value="<?php echo $row_invoice['description']; ?>" maxlength="" size="42" autocomplete="off" required />
		</td>
		<td class="hint"></td>
	</tr>
	<?php /*
	<tr class="list_entry">
		<td class="table_form_header">设置费</td>
		<td>
			<input name="one_time_fee" id="one_time_fee" type="text" value="<?php echo money($row_invoice['One_time_fee']); ?>" maxlength="" size="18" autocomplete="off" required />
		</td>
		<td class="hint">设置费为一次性支付费用</td>
	</tr>
	*/ ?>
	<tr class="list_entry">
		<td class="table_form_header">金额</td>
		<td>
			<input name="amount" id="amount" type="text" value="<?php echo money($row_invoice['amount']); ?>" maxlength="" size="18" autocomplete="off" required />
		</td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td></td>
		<td><input tabindex="3" class="button" type="submit" name="confirmed" id="confirmed" value="确认"></td>
		<td class="hint"></td>
	</tr>
</table>
<input type="hidden" name="invoice_id" value="<?php echo $_GET['id']; ?>" />
<input type="hidden" name="action" value="invoice_modify" />
</form>
<hr />
<form name="config_save" id="config_save" action="" method="post" onsubmit="return confirm('确认是否彻底删除此VPS?\n该操作仅删除该VPS在数据库中的数据.');">
<table class="list">
	<tr class="list_entry">
		<td width="200"></td>
		<td colspan="3">
			<input type="hidden" name="invoice_id" value="<?php echo @$_GET['id']; ?>" />
			<input type="hidden" name="action" value="invoice_delete" />
			<input class="button" id="button_save" type="submit" value="彻底删除此账单">
		</td>
	</tr>
</table>
</form>
</div>
<?php @include_once("footer.php") ?>