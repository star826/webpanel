<?php

$page = "access_denied.php";

@include_once("header.php");

?>


<div id="page">
<h2 style="margin: 0px"><?php echo $lang['access_denied']; ?></h2>
<p><?php echo $lang['access_denied_text']; ?></p>
<p class="hint"><?php echo $lang['access_denied_hint']; ?></p>
</div>

<?php @include_once("footer.php") ?>