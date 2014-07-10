<?php
$page = "account_make_a_payment.php";
@include_once("header.php");
$credit = @$_GET['credit'];
if(@$_POST['action']=='pay')
{
	$err = array();
	if ($_POST['amount'] < 1)
		$err[]='充值金额必须大于等于1元';
	if(!count($err))
	{
		$_POST['amount'] = mysql_real_escape_string($_POST['amount']);
		mysql_query("	INSERT INTO users_billing(UserID,type,date,description,amount,paid)
						VALUES(
							".$_SESSION['UserID'].",
							0,
							NOW(),
							'".$PanelName."账户余额充值 - 支付宝 - ".$_POST['amount']."元',
							".$_POST['amount'].",
							0
						)");
		echo '<div id="page">';
		echo '<p id="progressbar"><img src="images/progress_bar_1.gif" /></p><p>正在跳转至支付宝...</p>';
		require_once("api/alipay/lib/alipay_service.class.php");
		$out_trade_no = mysql_insert_id();
		$subject      = $PanelName."账户余额充值[".$_SESSION['username']."]";
		$body         = $PanelName."账户余额充值";
		$total_fee    = $_POST['amount'];
		$paymethod    = '';
		$defaultbank  = '';
		$anti_phishing_key  = '';
		$exter_invoke_ip = '';
		$show_url			= '';
		$extra_common_param = '';
		$royalty_type		= "";
		$royalty_parameters	= "";
		$parameter = array(
		"service"			=> "create_direct_pay_by_user",
		"payment_type"		=> "1",
		"partner"			=> trim($aliapy_config['partner']),
		"_input_charset"	=> trim(strtolower($aliapy_config['input_charset'])),
        "seller_email"		=> trim($aliapy_config['seller_email']),
        "return_url"		=> trim($aliapy_config['return_url']),
        "notify_url"		=> trim($aliapy_config['notify_url']),
		"out_trade_no"		=> $out_trade_no,
		"subject"			=> $subject,
		"body"				=> $body,
		"total_fee"			=> $total_fee,
		"paymethod"			=> $paymethod,
		"defaultbank"		=> $defaultbank,
		"anti_phishing_key"	=> $anti_phishing_key,
		"exter_invoke_ip"	=> $exter_invoke_ip,
		"show_url"			=> $show_url,
		"extra_common_param"=> $extra_common_param,
		"royalty_type"		=> $royalty_type,
		"royalty_parameters"=> $royalty_parameters
		);
		$alipayService = new AlipayService($aliapy_config);
		$html_text = $alipayService->create_direct_pay_by_user($parameter);
		echo $html_text;
		echo '</div><br />';
		@include_once("footer.php");
		exit;
	}
	if(count($err))
	{
		$_SESSION['msg']['pay-err'] = implode('\n',$err);
	}
}
$result = mysql_query("select * FROM users_extension WHERE UserID=".$_SESSION['UserID']);
$row = mysql_fetch_array($result);
?>
<?php
	if(@$_SESSION['msg']['pay-err'])
	{
		echo '<script type="text/JavaScript">alert("'.$_SESSION['msg']['pay-err'].'");</script>';
		unset($_SESSION['msg']['pay-err']);
	}
	if(@$_SESSION['msg']['pay-success'])
	{
		echo '<script type="text/JavaScript">alert("'.$_SESSION['msg']['pay-success'].'");</script>';
		unset($_SESSION['msg']['pay-success']);
	}
?>
<script language=JavaScript>
function CheckForm()
{
	if (document.make_a_payment_save.amount.value.length == 0) {
		alert("请输入充值金额.");
		document.make_a_payment_save.amount.focus();
		return false;
	}
	var reg	= new RegExp(/^\d*\.?\d{0,2}$/);
	if (! reg.test(document.make_a_payment_save.amount.value))
	{
        alert("请正确输入充值金额");
		document.make_a_payment_save.amount.focus();
		return false;
	}
	if (Number(document.make_a_payment_save.amount.value) < 1.00) {
		alert("充值金额最小是1.00");
		document.make_a_payment_save.amount.focus();
		return false;
	}
}
</script>
<div id="page">
<form name="make_a_payment_save" id="make_a_payment_save" action="" onSubmit="return CheckForm();" method="post" target="_blank">
<table class="list">
	<tr>
		<th colspan="3">充值</th>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">目前余额</td>
		<td>
				<span style="color: green; font-weight: bold"><?php echo money($row['credit']); ?> 元</span><br>
		</td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">充值金额</td>
		<td>
			<input name="amount" id="amount" type="text" value="<?php echo @$credit?$credit:'500'; ?>" maxlength="8"  size="8"  tabindex="1"  />
			<span class="hint">(人民币元)</span>
		</td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">付款方式</td>
		<td>
			<select name="payment">
					<option value="alipay">支付宝
					</option>
			</select>
		</td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td></td>
		<td><input tabindex="3" class="button" type="submit" name="confirmed" value="继续"></td>
		<td class="hint"></td>
	</tr>
</table>
<input type="hidden" name="action" value="pay" />
</form>
</div>
<?php @include_once("footer.php") ?>