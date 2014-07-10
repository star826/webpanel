<?php
@include_once("session.php");
if (empty($page) && empty($adminpage)) ForceDie();
ForceLogin();
?>
<div id="subtabs">
		<a class="<?php if($page == "account.php") echo "active"; ?>" href="account.php"><?php echo $lang['account']; ?></a>
		<a class="<?php if($page == "account_contact.php") echo "active"; ?>" href="account_contact.php"><?php echo $lang['contact_info']; ?></a>
		<a class="<?php if($page == "account_make_a_payment.php") echo "active"; ?>" href="account_make_a_payment.php"><?php echo $lang['make_a_payment']; ?></a>
		<a class="<?php if($page == "account_billing_history.php" || $page == 'account_invoice.php' ) echo "active"; ?>" href="account_billing_history.php"><?php echo $lang['billing_history']; ?></a>
</div>