<?php 
@include_once("session.php");
$_SESSION['language'] = 'zh-cn';
/* if (empty($_SESSION['language'])) {	
	foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $lang_) {
		$pattern_ = '/^(?P<primarytag>[a-zA-Z]{2,8})'.
		'(?:-(?P<subtag>[a-zA-Z]{2,8}))?(?:(?:;q=)'.
		'(?P<quantifier>\d\.\d))?$/';
		$splits_ = array();
		if (preg_match($pattern_, $lang_, $splits_)) {
			if ($splits_['primarytag'] == 'zh') 
			{
				$_SESSION['language'] = 'zh-cn';
				break;
			}
		} else {
			$_SESSION['language'] = 'zh-cn';
		}
	}
	if (!isset($_SESSION['language'])) {
		$_SESSION['language'] = 'en';
	}
} */
if (!file_exists("lang/".$_SESSION['language'].".php")) $_SESSION['language'] = 'en';
@include_once("lang/".$_SESSION['language'].".php");
function lang($langtag) {
	return $lang[$langtag];
}
function GetLanguage() {
	return !empty($_SESSION['language'])?$_SESSION['language']:"en";
}