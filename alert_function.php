<?php
function SetErrAlert($err) {
	if(count($err))
	{
		$_SESSION['msg']['alert-err'] = '';
		foreach ($err as $i)
		{
			$_SESSION['msg']['alert-err'] .= '• '.$i.'<br />';
		}
	}
}
function SetColorAlert($text,$color) {
		$_SESSION['msg']['alert-color-text'] = '<p><div class="alert '.$color.'"> '.$text.'</div></p>';
}
function EchoAlert() {
	if(@$_SESSION['msg']['alert-err'])
	{
		echo '<p><div class="alert pink"> '.$_SESSION['msg']['alert-err'].'</div></p>';		
		unset($_SESSION['msg']['alert-err']);
	}
	if(@$_SESSION['msg']['alert-success'])
	{
		echo '<p><div class="alert blue"> '.$_SESSION['msg']['alert-success'].'</div></p>';		
		unset($_SESSION['msg']['alert-success']);
	}
	if(@$_SESSION['msg']['alert-color-text'])
	{
		echo $_SESSION['msg']['alert-color-text'];
		unset($_SESSION['msg']['alert-color-text']);
	}
}