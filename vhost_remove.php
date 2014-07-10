<?php
$page = "vhost_remove.php";
@include_once("header.php");
@include_once("function.php");
@include_once("vhost_function.php");
if ("remove" == @$_POST['action'])
{
	if (empty($_POST['confirm'])) {
		SetColorAlert('请勾选确认删除选项!', 'pink');
		header("Location: vhost_remove.php?id=".@$_GET['id']);
		exit;
	}
	$UserID = $row['owner'];
	// 计算退款余额
	if ("Unpaid" == $row['status']){
		$credit = "0.00";
	} elseif ("Available" == $row['status']){
		// 已付款未使用的虚拟主机全额返款
		$credit = round(((double)$row['cycle']*(double)$row['price']),2);
	} else {
		$date_1 = date("Y-m-d");
		$date_2 = $row['duedate'];
		$d1 = strtotime($date_1);
		$d2 = strtotime($date_2);
		$days = round(($d2-$d1)/3600/24);
		$credit = round((int)$row['price']/30*$days*0.9,2);
		if ($credit < 0.01) $credit = 0;
	}
	if (!empty($_POST['confirm2']) || !empty($_POST['confirm3']))
		if (!IsAdmin()) ForceDie();
	// 检查是否在删除状态，避免重复删除以及退款金额多倍
	if ('Remove' == $row['status']) ForceDie();
	// 设置主机为删除中状态
	mysql_query(" UPDATE vhost SET status='Remove' WHERE ID=".$row['ID'], $con);
	// 开始缓冲输出，输出页面头
	echo '<div id="page">';
	echo '<p class="breadcrumb"><a href="index.php">'.$PanelName.'</a> &raquo; <a href="vhost_panel.php?id='.$row['ID'].'">'.$row['domain'].'</a> &raquo; <strong>删除虚拟主机</strong></p>';
	echo '<p id="progressbar"><img src="images/progress_bar_1.gif" /></p>';
	echo '<p>开始删除虚拟主机</p>';
	flush2();
	// 已经初始化的虚拟主机
	if ($row['serverID'] != 0) {
		if (!empty($_POST['confirm2']))
		{
			mysql_query("DELETE FROM vhost_mysql_db WHERE ID=".$row_mysql_db['ID'], $con);
			mysql_query("DELETE FROM vhost_ftp WHERE ID=".$row_ftp_user['ID'], $con);
			mysql_query("DELETE FROM vhost_nginx_conf WHERE vhostID=".$row['ID'], $con);
			mysql_query("DELETE FROM vhost_job_queue WHERE VhostID=".$row['ID'], $con);
		} else {
			$result_server = mysql_query("select * FROM vhost_servers WHERE ID=".$row['serverID'], $con);
			if (@mysql_num_rows($result_server) == 0){
				if ($row['status'] != 'Unpaid' && $row['status'] != 'Available')
				{
					echo '<p>节点服务器已被删除或不存在</p>';
					flush2();
				} else {
					$error = true;
					echo '<p>致命错误</p>';
					flush2();
				}
			} else {
				$row_server = mysql_fetch_array($result_server);
				// 连接远程MySQL 以便进行后续的FTP帐户,MySQL帐号删除
				$remotecon = @mysql_connect($row_server['ip'],'root',$row_server['mysqlpasswd']);
				if (!$remotecon) {
					$error = true;
					echo '<p>无法连接节点数据库</p>';
					flush2();
				} else {
					mysql_select_db("ftpusers", $remotecon);
					echo '<p>与节点服务器MySQL连接成功</p>';
					flush2();
				}
				if(empty($error)){
					// 删除 MySQL数据库
					$result_mysql_dbs = mysql_query("select * FROM vhost_mysql_db WHERE VhostID=".$row['ID'], $con);
					while($row_mysql_db = mysql_fetch_array($result_mysql_dbs)) {
						$drop_db_user_1 = "DROP USER '".$row_mysql_db['User']."'@'".$row_mysql_db['Host']."' ;";
						$drop_db_user_2 = "drop database `".$row_mysql_db['DB']."`";
						if (@mysql_query($drop_db_user_1,$remotecon)){
							if (@mysql_query($drop_db_user_2,$remotecon)){
								mysql_query("DELETE FROM vhost_mysql_db WHERE ID=".$row_mysql_db['ID'], $con);
								echo '<p>删除MySQL数据库 <strong>'.$row_mysql_db['DB'].'</strong> 成功</p>';
								flush2();
							} else $error = true;
						} else $error = true;
						if(!empty($error)){
							$error = true;
							echo '<p>删除MySQL数据库 <strong>'.$row_mysql_db['DB'].'</strong> 失败</p>';
							flush2();
						}
					}
/* 					if(empty($error)){
						mysql_query("DELETE FROM vhost_mysql_db WHERE VhostID=".$row['ID'], $con);
						echo '<p>删除MySQL数据库记录项成功</p>';
						flush2();
					} */
				}
				// 删除 FTP帐户
				if(empty($error)){
					$result_ftp_users = mysql_query("select * FROM vhost_ftp WHERE VhostID=".$row['ID'], $con);
					while($row_ftp_user = mysql_fetch_array($result_ftp_users)) {
						if(@mysql_query("DELETE FROM users WHERE User='".$row_ftp_user['User']."'",$remotecon)){
							mysql_query("DELETE FROM vhost_ftp WHERE ID=".$row_ftp_user['ID'], $con);
							echo '<p>删除FTP帐户 <strong>'.$row_ftp_user['User'].'</strong> 成功</p>';
							flush2();
						} else {
							$error = true;
							echo '<p>删除FTP帐户 <strong>'.$row_ftp_user['User'].'</strong> 失败</p>';
							flush2();
						}
					}
/* 					if(empty($error)){
						mysql_query("DELETE FROM vhost_ftp WHERE VhostID=".$row['ID'], $con);
						echo '<p>删除FTP帐户数据库记录项成功</p>';
						flush2();
					} */
				}
				@mysql_close($remotecon);
				if(empty($error)){
					// 删除Nginx配置文件 等等
					set_include_path('api/ssh/' . PATH_SEPARATOR . 'phpseclib');
					@include_once("api/ssh/Net/SSH2.php");
					@include_once("api/ssh/Net/SFTP.php");
					$ssh = new Net_SSH2($row_server['ip'],$row_server['port']); 
					$sftp = new Net_SFTP($row_server['ip'],$row_server['port']);
					if (!@$ssh->login($row_server['root'], $row_server['passwd'])) {
						// $err[]='与节点服务器通讯失败，无法连接节点服务器SSH。ERROR_CODE:SSHCONNECT';
						$error = true;
						echo '<p>与节点服务器SSH通讯失败</p>';
						flush2();
					} else {
						echo '<p>与节点服务器SSH通讯成功</p>';
						flush2();
						$vhost_nginx_conf = mysql_query("select * FROM vhost_nginx_conf WHERE vhostID=".$row['ID'], $con);
						// if (mysql_num_rows($vhost_nginx_conf) == 0) die("ERROR");
						$commands = '';
						while($row_vhost_nginx_conf = mysql_fetch_array($vhost_nginx_conf))
						{	
							$commands  .= 'rm -f "/usr/local/nginx/conf/vhost/'.$row_vhost_nginx_conf['server_name'].'.conf";';
							$commands  .= 'rm -f "/usr/local/nginx/conf/vhost/'.$row_vhost_nginx_conf['server_name'].'.conf.bak";';
							$commands  .= 'rm -rf "'.$row_vhost_nginx_conf['root'].'";';
							$commands  .= 'rm -f "/home/wwwlogs/'.$row_vhost_nginx_conf['server_name'].'.log";';
							// $commands  .= 'rm -f /home/wwwlogs/' . $row['ID'] . '.log;'; // 删除日志文件
						}
						if ($commands != '') $ssh->exec($commands);
							echo '<p>删除虚拟主机配置文件/虚拟主机目录成功</p>';
							echo '<p>删除虚拟主机日志文件成功</p>';
							flush2();
						mysql_query("DELETE FROM vhost_nginx_conf WHERE vhostID=".$row['ID'], $con);
							echo '<p>删除虚拟主机域名/子域名绑定记录成功</p>';
							flush2();
						mysql_query("DELETE FROM vhost_job_queue WHERE VhostID=".$row['ID'], $con);
							echo '<p>删除虚拟主机任务列表成功</p>';
							flush2();
					}
				}
			}
		} // 强制删除结束
	}
		if(empty($error)){
			if($row['status'] == 'Unpaid'){
				// 未支付状态，删除订单
				mysql_query("DELETE FROM users_billing WHERE ID=".$row['orderID'], $con);
				echo '<p>成功删除未支付订单</p>';
				flush2();
			}
			if ($row['serverID'] != 0) {
				// 补充该节点可创建的虚拟主机数
				
				mysql_query("	UPDATE vhost_servers SET
								vhostFree=vhostFree+1
								WHERE ID=".$row['serverID']."
								", $con);
			}
			mysql_query("DELETE FROM vhost WHERE ID=".$row['ID'], $con);
					echo '<p>删除虚拟主机数据库记录项成功</p>';
					flush2();
			if ($credit >= 0.01 && empty($_POST['confirm3']))
			{
				//更改金额
				mysql_query(" UPDATE users_extension SET credit=credit+".$credit." WHERE UserID=".$UserID, $con);
				mysql_query("	INSERT INTO users_billing(UserID,type,date,description,amount,paid)
								VALUES(
									".$UserID.",
									0,
									NOW(),
									'虚拟主机删除返款',
									".$credit.",
									1
								)", $con);
				$_SESSION['msg']['alert-success']='删除虚拟主机成功, '.$credit.' 金额已经返回到您的帐户';
			} else {
				$_SESSION['msg']['alert-success']='删除虚拟主机成功';
			}
		}
	if(empty($error)){
		echo '<script language=JavaScript>top.location=\'vhost.php\';</script>';
	} else {
		mysql_query(" UPDATE vhost SET status='Running' WHERE ID=".$row['ID'], $con);
		echo '<p>虚拟主机删除失败，请按F5重试。</p>';
	}
	// echo '<p>虚拟主机删除成功</p>';
	// echo '<p><a href="vhost.php">进入虚拟主机列表</a></p>';
	// echo '<script language=JavaScript>document.getElementById(\'progressbar\').style.display = "none";</script>';
	// echo '</div><br />';
	// @include_once("footer.php");
	flush2();
	exit();	
}
?>
<div id="page">
<p class='breadcrumb'><a href='index.php'><?php echo $PanelName;?></a> &raquo; <a href='vhost_panel.php?id=<?php echo $row['ID']; ?>'><?php echo $row['domain']; ?></a> &raquo; <strong>删除虚拟主机</strong></p>
<?php EchoAlert(); ?>
<form name="vhost_delete" id="vhost_delete" action="" method="post" onsubmit="document.getElementById('doit').disabled = true;">
        <table class="list">
            <tr>
                <th>虚拟主机删除确认</th>
            </tr>
            <tr class="list_entry">
                <td>
<?php if ("Unpaid" == $row['status']){?>
是否删除这个未付款的虚拟主机从你的帐户?<br />
<?php } elseif ("Available" == $row['status']) { ?>
是否删除这个未使用的虚拟主机从你的帐户?<br />
<?php } elseif ("Running" == $row['status']) { ?>
你确定你要删除绑定域名为 "<strong><?php echo $row['domain']; ?></strong>" 的虚拟主机从你的帐户?<br />
<?php } elseif (@$row['domain']) { ?>
<span style="color:#F00;">删除虚拟主机操作将会删除你的网站数据，MySQL数据库！</span><br />
<?php } ?>
<?php
if ("Unpaid" == $row['status']){
	$credit = "0.00";
} elseif ("Available" == $row['status']){
	// 已付款未使用的虚拟主机全额返款
	$credit = round(((double)$row['cycle']*(double)$row['price']),2);
} else {
	$date_1 = date("Y-m-d");
	$date_2 = $row['duedate'];
	$d1 = strtotime($date_1);
	$d2 = strtotime($date_2);
	$days = round(($d2-$d1)/3600/24);
	$credit = round((int)$row['price']/30*$days*0.9,2);
}
if ($credit > 0.01)
	echo "$credit 金额将返回你的帐户。";
?></td>
            </tr>
            <tr class="list_entry"><td>
                <input type="checkbox" name="confirm" id="confirm"> <label for="confirm">是的, 删除这个虚拟主机从我的帐户.</label>
            </td></tr>
			<?php if (IsAdmin()) {  ?>
            <tr class="list_entry"><td>
                <input type="checkbox" name="confirm2" id="confirm2"> <label for="confirm2"><span style="color:red">强制删除, 如果节点服务器已损坏.</span></label>
            </td></tr>
            <tr class="list_entry"><td>
                <input type="checkbox" name="confirm3" id="confirm3"> <label for="confirm3"><span style="color:red">删除后不进行余额结算及返款.</a></label>
            </td></tr>
			<?php } ?>
            <tr class="list_entry">
				<input type="hidden" name="vhostid" value="<?php echo $_GET['id']; ?>" />
				<input type="hidden" name="action" value="remove" />
                <td><input class="button" type="submit" name="doit" id="doit" value="删除这个虚拟主机"></td>
            </tr>
        </table>
</form>
</div>
<?php @include_once("footer.php") ?>