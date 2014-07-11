<?php
@include_once("session.php");
if (empty($page) && empty($adminpage)) ForceDie();
ForceLogin();
?>
<br />
<p style="color:#000000;font-size:14px;">&copy; 2014 Web Control Panel For <a href="http://www.lnmpv.com">LNMPV</a> All rights reserved. Processed in <?php echo round(get_microtime()-$page_start_time,6); ?> second(s) <br /><br />
<?php if (IsAdmin()) { ?>
</p>
<?php } ?>
<?php echo $statisticalCode; ?>
</body>
</html>
