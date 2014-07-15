<?php

$current_version = 'V0.1';

if (@$_GET['phpinfo'])	die(phpinfo());
$adminpage = "admin_system.php";
@include_once("header.php");
@include_once("function.php");
?>



<div id="page">
<div id="version">
<table class="list">
	<tr>
		<th colspan="3">版本</th>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">当前版本</td>
		<td><span style="color: green"><?php echo $current_version; ?></span><br></td>
	</tr>
	<tr class="list_entry">
	<td class="table_form_header">注册状态</td>
	  <td>
			<?php
				if(!$RegSwitch){
					echo "<span style=\"color: green\">开放注册</span><br />";
				}else{
					echo "<span style=\"color: red\">注册关闭</span><br />";
				}			
			?>
		</td>
		<td class="hint"></td>
	</tr>
</table>
</div>

<table class="list">
	<tr>
		<th colspan="3">系统信息</th>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">面板启用HTTPS</td>
		<td><span style="color: green"><?php echo $_SERVER["SERVER_PORT"]==443?'是':'否'; ?></span><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">面板本地路径</td>
		<td><span style="color: green"><?php echo $_SERVER["DOCUMENT_ROOT"]; ?></span><br /></td>
		<td class="hint"></td>
	</tr>
	
	<tr class="list_entry">
		<td class="table_form_header">PHP版本</td>
		<td><span style="color: green"><?php echo phpversion(); ?></span><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">Web服务器</td>
		<td><span style="color: green"><?php echo $_SERVER["SERVER_SOFTWARE"]; ?></span><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">服务器IP</td>
		<td><span style="color: green"><?php echo $_SERVER["SERVER_ADDR"]; ?></span><br /></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">PHPINFO</td>
		<td><span style="color: green"><a href="?phpinfo=1" target="_blank">phpinfo();</a></span><br /></td>
		<td class="hint"></td>
	</tr>
</table>
<table class="list">
	<tr>
		<th colspan="3">技术支持</th>
	</tr>
			<tr class="list_entry">
		<td class="table_form_header">重要提示</td>
		<td><span style="color: green">本程序作者已经不再开发此程序,此程序仅供个人使用,请勿用于商业,否则风险自负.</span><br /></td>
		<td class="hint"></td>
	</tr>
</table>
</div>
<?php @include_once("footer.php") ?>
