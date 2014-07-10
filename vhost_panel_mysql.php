<?php
$page = "vhost_panel_mysql.php";
@include_once("header.php");
@include_once("function.php");
@include_once("vhost_function.php");
@include_once("alert_function.php");
$err = array();
if (@$_GET['action'] == 'add' && $row['db'] != -1 && mysql_num_rows(mysql_query("select * FROM vhost_mysql_db WHERE VhostID=".$row['ID'])) >= $row['db'])
{
	$err[]='可创建MySQL数据库已达到上限！';
	SetErrAlert($err);
	header("Location: vhost_panel_mysql.php?id=".$row['ID']);
	exit();
}
if (@$_POST['action'] == 'create') {
	if($row['db'] != -1 && mysql_num_rows(mysql_query("select * FROM vhost_mysql_db WHERE VhostID=".$row['ID'])) >= $row['db'])
	{ 
		$err[]='可创建MySQL数据库已达到上限！';
	}
	if(strlen($_POST['username'])<4 || strlen($_POST['username'])>16)
	{
		$err[]='您的用户名/数据库名必须为4到16个字符！';
	}
	if(preg_match('/[^a-z0-9\_]+/i',$_POST['username']))
	{
		$err[]='您的用户名/数据库名包含无效字符！';
	}
	if('mysql' == $_POST['username'] || 'information_schema' == $_POST['username'] || 'root' == $_POST['username'] || 'ftp' == $_POST['username'] || 'ftpusers' == $_POST['username'] )
	{
		$err[]='您的数据库名为系统数据库名，无法创建！';
	}
	if(strlen($_POST['password'])<6 || strlen($_POST['password'])>32)
	{
		$err[]='您的数据库密码必须为6到32个字符！';
	}
	if($_POST['password'] != $_POST['password2'])
	{
		$err[]='两次输入的密码不同！';
	}
	$serverid = $row['serverID'];
	if($_POST['action'] == 'create') {
		$result_mysql_dbs = mysql_query("select * FROM vhost_mysql_db WHERE ServerID=".$serverid." AND User='".$_POST['username']."'");
		if (@mysql_num_rows($result_mysql_dbs) > 0){
			$err[]='您输入的MySQL数据库名已存在于该节点服务器';
		}
	}
	$s = date("Y-m-d g:i:s a");	
	if(!count($err))
	{
		$result_server = mysql_query("select * FROM vhost_servers WHERE ID=".$serverid);
		$row_server = mysql_fetch_array($result_server);
		$remotecon = @mysql_connect($row_server['ip'],'root',$row_server['mysqlpasswd']);
		if (!$remotecon) $err[]='无法连接节点服务器MySQL数据库';
		if(!count($err))
		{
			mysql_query("set names 'utf8'", $remotecon);
			if($_POST['action'] == 'create') {
			$create_db_user_1 = "CREATE USER '".$_POST['username']."'@'localhost' IDENTIFIED BY  '".$_POST['password']."';";
			$create_db_user_2 = "GRANT USAGE ON * . * TO  '".$_POST['username']."'@'localhost' IDENTIFIED BY  '".$_POST['password']."' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;";
			$create_db_user_3 = "CREATE DATABASE IF NOT EXISTS  `".$_POST['username']."` ;";
			$create_db_user_4 = "GRANT ALL PRIVILEGES ON  `".$_POST['username']."` . * TO  '".$_POST['username']."'@'localhost';";
				if (!@mysql_query($create_db_user_1,$remotecon)) $err[]='在节点服务器创建MySQL数据库时发生错误，创建失败。<strong>'.$create_db_user_1.'</strong>';
				if (!count($err) && !@mysql_query($create_db_user_2,$remotecon)) $err[]='在节点服务器创建MySQL数据库时发生错误，创建失败<strong>'.$create_db_user_2.'</strong>';
				if (!count($err) && !@mysql_query($create_db_user_3,$remotecon)) $err[]='在节点服务器创建MySQL数据库时发生错误，创建失败<strong>'.$create_db_user_3.'</strong>';
				if (!count($err) && !@mysql_query($create_db_user_4,$remotecon)) $err[]='在节点服务器创建MySQL数据库时发生错误，创建失败<strong>'.$create_db_user_4.'</strong>';
				else {
					mysql_query("INSERT INTO `vhost_mysql_db` (`VhostID`, `ServerID`, `User`, `Host`, `Password`, `DB`)
						VALUES (
						".$vhostid.", 
						".$serverid.", 
						'".$_POST['username']."',
						'localhost',
						'".$_POST['password']."',
						'".$_POST['username']."'
						)" ,$con);
					AddJob($vhostid, 'MySQL数据库创建 - '.$_POST['username'], '创建成功', $s, $s, date("Y-m-d g:i:s a"), $con);
					$_SESSION['msg']['alert-success']='创建MySQL数据库成功';
					header("Location: vhost_panel_mysql.php?id=".$vhostid);
					exit();
				}
			}
		}
	}
	SetErrAlert($err);
}
elseif (@$_GET['action'] == 'delete') {
	$err = array();
	$databaseid = $_GET['databaseid'];
	$serverid = $row['serverID'];
	$result_mysql_dbs = mysql_query("select * FROM vhost_mysql_db WHERE ServerID=".$serverid." AND VhostID=".$row['ID']." AND ID=".$databaseid);
	if (@mysql_num_rows($result_mysql_dbs) == 0){
		$err[]='欲删除的MySQL数据库不存在';
	}
	$row_mysql_db = mysql_fetch_array($result_mysql_dbs);
	$s = date("Y-m-d g:i:s a");	
	if(!count($err))
	{
		$result_server = mysql_query("select * FROM vhost_servers WHERE ID=".$serverid);
		$row_server = mysql_fetch_array($result_server);
		$remotecon = @mysql_connect($row_server['ip'],'root',$row_server['mysqlpasswd']);
		if (!$remotecon) $err[]='无法连接节点服务器MySQL数据库';
		if(!count($err))
		{
			mysql_query("set names 'utf8'", $remotecon);
			$drop_db_user_1 = "DROP USER '".$row_mysql_db['User']."'@'".$row_mysql_db['Host']."' ;";
			$drop_db_user_2 = "drop database `".$row_mysql_db['DB']."`";
				if (!@mysql_query($drop_db_user_1,$remotecon)) $err[]='在节点服务器删除MySQL数据库时发生错误，删除失败。<strong>'.$drop_db_user_1.'</strong>';
				if (!count($err) && !@mysql_query($drop_db_user_2,$remotecon)) $err[]='在节点服务器删除MySQL数据库时发生错误，删除失败。<strong>'.$drop_db_user_2.'</strong>';
				else {
					AddJob($vhostid, 'MySQL数据库删除 - '.$row_mysql_db['DB'], '删除成功', $s, $s, date("Y-m-d g:i:s a"), $con);
					mysql_query("DELETE FROM vhost_mysql_db WHERE ID=".$row_mysql_db['ID'], $con);
					$_SESSION['msg']['alert-success']='删除MySQL数据库成功';
				}
		}
	}
	SetErrAlert($err);
	header("Location: vhost_panel_mysql.php?id=".$vhostid);
	exit();
}
elseif (@$_GET['action'] == 'dump'){
	$err = array();
	$databaseid = $_GET['databaseid'];
	$serverid = $row['serverID'];
	$result_mysql_dbs = mysql_query("select * FROM vhost_mysql_db WHERE ServerID=".$serverid." AND VhostID=".$row['ID']." AND ID=".$databaseid);
	if (@mysql_num_rows($result_mysql_dbs) == 0){
		$err[]='MySQL数据库不存在';
	}
	$row_mysql_db = mysql_fetch_array($result_mysql_dbs);
	if(!count($err))
	{
		$mysql_connect_code  = '<?php // Dump By ' . $PanelName . "\n" ;
		$mysql_connect_code .= '' . "\n" ;
		$mysql_connect_code .= '/** MySQL 数据库名 */' . "\n" ;
		$mysql_connect_code .= '$DB_NAME = \''.$row_mysql_db['DB'].'\';' . "\n" ;
		$mysql_connect_code .= '/** MySQL 数据库用户名 */' . "\n" ;
		$mysql_connect_code .= '$DB_USER = \''.$row_mysql_db['User'].'\';' . "\n" ;
		$mysql_connect_code .= '/** MySQL 数据库密码 */' . "\n" ;
		$mysql_connect_code .= '$DB_PASSWORD = \''.$row_mysql_db['Password'].'\';' . "\n" ;
		$mysql_connect_code .= '/** MySQL 主机 */' . "\n" ;
		$mysql_connect_code .= '$DB_HOST = \''.$row_mysql_db['Host'].'\';' . "\n" ;
		$mysql_connect_code .= '/** 创建数据表时默认的文字编码 */' . "\n" ;
		$mysql_connect_code .= '$DB_CHARSET = \'utf8\';' . "\n" ;
		$mysql_connect_code .= '' . "\n" ;
		$mysql_connect_code .= 'mysql_connect($DB_HOST, $DB_USER, $DB_PASSWORD);' . "\n" ;
		$mysql_connect_code .= 'mysql_query("set names \'$DB_CHARSET\'");' . "\n" ;
		$mysql_connect_code .= 'mysql_select_db($DB_NAME);' . "\n" ;
		$mysql_connect_code .= '' . "\n" ;
		$mysql_connect_code .= '?>';
		SetColorAlert('<pre>'.highlight_string($mysql_connect_code, true).'</pre>','yellow');
	}
	SetErrAlert($err);
	header("Location: vhost_panel_mysql.php?id=".$vhostid);
	exit();
} elseif (@$_GET['action'] == 'phpmyadmin') {
	$err = array();
	$databaseid = $_GET['databaseid'];
	$serverid = $row['serverID'];
	$result_mysql_dbs = mysql_query("select * FROM vhost_mysql_db WHERE ServerID=".$serverid." AND VhostID='".$row['ID']."' AND ID=".$databaseid);
	if (@mysql_num_rows($result_mysql_dbs) == 0){
		$err[]='MySQL数据库不存在';
	}
	$row_mysql_db = mysql_fetch_array($result_mysql_dbs);
	if(!count($err))
	{
		echo '<div id="page">';
		echo '<p class="breadcrumb"><a href="index.php">'.$PanelName.'</a> &raquo; <a href="vhost_panel.php?id='.$row['ID'].'">'.$row['domain'].'</a> &raquo; <a href="vhost_panel_mysql.php?id='.$row['ID'].'">MySQL</a> &raquo; <strong>phpMyAdmin</strong></p> ';
		echo '<p id="progressbar"><img src="images/progress_bar_1.gif" /></p>';
		echo "
		<form id='go_pma' action='http://".$row['ip']."/phpmyadmin/index.php' method='post'>
		<input type='hidden' name='pma_username' value='".$row_mysql_db['User']."' />
		<input type='hidden' name='pma_password' value='".$row_mysql_db['Password']."' />
		<input type='hidden' name='server' value='1' />
		</form>
		<script>document.forms['go_pma'].submit();</script>
		";
		echo '</div><br />';
		@include_once("footer.php");
		exit();
	}
	SetErrAlert($err);
	header("Location: vhost_panel_mysql.php?id=".$vhostid);
	exit();
}
?>
<div id="page">
<?php
if (@$_GET['action'] == 'add') {
?>
<p class='breadcrumb'><a href='index.php'><?php echo $PanelName;?></a> &raquo; <a href='vhost_panel.php?id=<?php echo $row['ID']; ?>'><?php echo $row['domain']; ?></a> &raquo; <a href='vhost_panel_mysql.php?id=<?php echo $row['ID']; ?>'>MySQL</a> &raquo; <strong>添加MySQL数据库帐户</strong></p>
<?php
} else {
?>
<p class='breadcrumb'><a href='index.php'><?php echo $PanelName;?></a> &raquo; <a href='vhost_panel.php?id=<?php echo $row['ID']; ?>'><?php echo $row['domain']; ?></a> &raquo; <strong>MySQL数据库管理</strong></p>
<?php 
}
?>
<?php EchoAlert(); ?>
<?php if (@$_GET['action'] == 'add') { ?>
<form name="config_save" id="config_save" action="" method="post" onsubmit="document.getElementById('button_save').disabled = true;">
<table class="list">
	<tr>
		<th colspan="3">创建MySQL数据库</th>
	</tr>
	<tr class="list_head">
		<td colspan="3">MySQL数据库信息</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">用户名/数据库名</td>
		<td><input name="username" id="username" type="text" value="<?php echo @$_POST['username']; ?>" maxlength="12" size="20" /></td>
		<td class="hint">只允许字母,数字,下划线</td>
	</tr>
		<tr class="list_entry">
			<td class="table_form_header">密码</td>
			<td>
				<input name="password" id="password" style="margin-bottom:2px;" type="password" maxlength="32"  size="20"  autocomplete="off"  /><br />
				<input name="password2" id="password2" type="password" maxlength="32"  size="20"  autocomplete="off"  />
			</td>
			<td class="hint"></td>
		</tr>
<?php /*
		<tr class="list_head">
			<td colspan="3">访问权限</td> <!-- fastcgi_intercept_errors on; -->
		</tr>
	<tr class="list_entry">
		<td class="table_form_header">主机</td>
		<td><select name="pred_hostname" id="select_pred_hostname" title="Host" onchange="if (this.value == 'any') { hostname.value = '%'; } else if (this.value == 'localhost') { hostname.value = 'localhost'; } else if (this.value == 'hosttable') { hostname.value = ''; } else if (this.value == 'userdefined') { hostname.focus(); hostname.select(); }">
        <option value="any">任意主机</option>
        <option value="localhost">本地</option>
        <option value="userdefined">使用文本域:</option>
		</select>
		<input type="text" name="hostname" maxlength="41" value="" title="Host" onchange="pred_hostname.value = 'userdefined';">
		</td>
	</tr>
*/ ?>
	<tr class="list_entry">
		<td width="200"></td>
		<td colspan="3">
			<br />
			<input type="hidden" name="action" value="create" />
			<input class="button" id="button_save" type="submit" value="保存设置">
		</td>
	</tr>
</table>
</form>
<?php } else { ?>
<table class="list">
	<tr>
		<th colspan="6">MySQL管理</th>
	</tr>
	<tr class="list_head">
		<td>数据库名</td>
		<td>数据库登录名</td>
		<!-- <td>数据库密码</td> -->
		<td>数据库主机</td>
		<td>数据库服务器IP</td>
		<td align="center" width="10%">选项</td>
	</tr>
<?php
	$result_mysql_dbs = mysql_query("select * FROM vhost_mysql_db WHERE VhostID=".$vhostid);
	while($row_mysql_db = mysql_fetch_array($result_mysql_dbs)) {
?>
		<tr class="list_entry">
			<td><?php echo $row_mysql_db['DB']; ?></td>
			<td><?php echo $row_mysql_db['User']; ?></td>
			<!-- <td><?php echo $row_mysql_db['Password']; ?></td> -->
			<td><?php echo $row_mysql_db['Host']; ?></td>
			<td><?php echo $row['ip']; ?></td>
			<td nowrap align="center">
				<a href="?id=<?php echo $vhostid; ?>&amp;action=phpmyadmin&amp;databaseid=<?php echo $row_mysql_db['ID']; ?>" target="_blank" title="登录到phpMyAdmin">phpMyAdmin</a> |
				<a href="?id=<?php echo $vhostid; ?>&amp;action=dump&amp;databaseid=<?php echo $row_mysql_db['ID']; ?>" title="输出PHP开发使用的数据库连接代码">PHP连接代码</a> |
				<a href="?id=<?php echo $vhostid; ?>&amp;action=delete&amp;databaseid=<?php echo $row_mysql_db['ID']; ?>" onClick="return confirm('确认删除这个MySQL数据库?')">删除</a>
			</td>
		</tr>
<?php
}
?>
	<tr class="list_entry " style="height: 35px;">
		<td colspan="7" align="right">
			<?php if ($row['db'] != -1 && mysql_num_rows(mysql_query("select * FROM vhost_mysql_db WHERE VhostID=".$row['ID'])) >= $row['db']) { ?>
			<strike>添加一个MySQL数据库</strike>
			<?php } else { ?>
			<a href="?id=<?php echo $vhostid; ?>&amp;action=add">添加一个MySQL数据库</a>
			<?php } ?>
		</td>
	</tr>
</table>
<?php } ?>
</div>
<?php @include_once("footer.php") ?>