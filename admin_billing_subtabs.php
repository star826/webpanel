<?php
@include_once("session.php");
if (empty($adminpage)) ForceDie();
ForceAdmin();
$billing_display = @$_GET['display'];
?>
<div id="subtabs">
	<a class="<?php if(empty($billing_display)) echo "active"; ?>"  href="admin_billing.php">所有账单</a>
	<a class="<?php if($billing_display == "service_paid") echo "active"; ?>" href="admin_billing.php?display=service_paid">已支付</a>
	<a class="<?php if($billing_display == "service_unpad") echo "active"; ?>" href="admin_billing.php?display=service_unpad">未支付</a>
	<a class="<?php if($billing_display == "service_refund") echo "active"; ?>" href="admin_billing.php?display=service_refund">服务删除</a>
	<a class="<?php if($billing_display == "recharge_done") echo "active"; ?>" href="admin_billing.php?display=recharge_done">充值成功</a>
	<a class="<?php if($billing_display == "recharge_undo") echo "active"; ?>" href="admin_billing.php?display=recharge_undo">充值失败</a>
	
	<?php if($billing_display == "user_all" && @is_numeric($_GET['userid'])) { ?>
	<a class="<?php if($billing_display == "user_all") echo "active"; ?>" href="admin_billing.php?display=user_all&userid=<?php echo $_GET['userid']; ?>">用户账单</a>
	<?php } ?>
</div>