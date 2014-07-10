<?php
@include_once("../config.php");
?>
<html>
<head>
<title>Whoops</title>
<meta http-equiv="content-type" content="text/html;charset=utf-8">
<style type="text/css" media="screen">
body {
	background-color: #F8F8F8;
	color : #000;
	margin: 0px;
	text-align: center;
	font-family: "Trebuchet MS", Verdana, Arial, Helvetica, sans-serif;
}

h1 {
	font-size: 82px;
	padding: 0px;
	margin: 0px;
}

#error {
	border: 6px solid #999;
	width: 500px;
	margin: auto;
	background-color: white;
	padding: 20px;
}

.hint {
	color: #999;
}

</style>
</head>
<body>
	<br><br><br>
	<br><br><br>
	<div id="error">
		<h1>我勒个去!</h1>
		<h2>页面不存在  :(</h2>
		<p class="hint">
			404错误，有木有！！！到底有木有！！！
		</p>
		<br />
		<a href="/">返回主页</a>
	</div>
</body>
</html>