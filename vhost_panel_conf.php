<?php
$page = "vhost_panel_conf.php";
@include_once("header.php");
@include_once("function.php");
@include_once("vhost_function.php");
@include_once("alert_function.php");
$err = array();
$domain_type = @$_REQUEST['type'];
$conf_action = @$_REQUEST['action'];
$vhost_conf_id = @$_REQUEST['vhost_conf_id'];
$vhostID = 					trim(mysql_real_escape_string(@$_POST['vhostid']));
$server_name = 				trim(mysql_real_escape_string(@$_POST['server_name']));
$subdomain = 				trim(mysql_real_escape_string(@$_POST['subdomain']));
$subdirectory =				trim(mysql_real_escape_string(@$_POST['subdirectory']));
$index = 					trim(mysql_real_escape_string(@$_POST['index']));
$static = 					trim(mysql_real_escape_string(@$_POST['static']));
$mode = 					trim(mysql_real_escape_string(@$_POST['mode']));
$proxy_pass = 				trim(mysql_real_escape_string(@$_POST['proxy_pass']));
$http_status_code =			trim(mysql_real_escape_string(@$_POST['http_status_code']));
$path = 					trim(mysql_real_escape_string(@$_POST['path']));
$rewrite = 					trim(mysql_real_escape_string(@$_POST['rewrite']));
$allow_spider =				trim(mysql_real_escape_string(@$_POST['allow_spider']));
$error_page_403 = 			trim(mysql_real_escape_string(@$_POST['error_page_403']));
$error_page_404 =			trim(mysql_real_escape_string(@$_POST['error_page_404']));
$error_page_500 = 			trim(mysql_real_escape_string(@$_POST['error_page_500']));
$error_page_502 = 			trim(mysql_real_escape_string(@$_POST['error_page_502']));
$gzip =						trim(mysql_real_escape_string(@$_POST['gzip']));
$cache_images = 			trim(mysql_real_escape_string(@$_POST['cache_images']));
$cache_cssjs = 				trim(mysql_real_escape_string(@$_POST['cache_cssjs']));
$ssl_enabled = 				trim(mysql_real_escape_string(@$_POST['ssl_enable']));
$ssl_cer =				 	trim((@$_POST['ssl_cer']));
$ssl_key = 					trim((@$_POST['ssl_key']));
$ssl_ca = 					trim((@$_POST['ssl_ca']));
if ('add' == $conf_action){
	if ('main' == $domain_type){
		if (mysql_num_rows(mysql_query("select * FROM vhost_nginx_conf WHERE vhostID=".$row['ID']." AND type='main'")) > 0) $err[] = '一个虚拟主机只能创建一个主域';
	} elseif ('subdomain' == $domain_type) {
		$check_main_domain = true;
		if ($row['subdomain'] != -1 && mysql_num_rows(mysql_query("select * FROM vhost_nginx_conf WHERE vhostID=".$row['ID']." AND type='subdomain'")) >= $row['subdomain']) $err[] = '可创建的子域已经达到上限！';
	} elseif ('addon' == $domain_type) {
		$check_main_domain = true;
		if ($row['addon'] != -1 && mysql_num_rows(mysql_query("select * FROM vhost_nginx_conf WHERE vhostID=".$row['ID']." AND type='addon'")) >= $row['addon']) $err[] = '可创建的附加域已经达到上限！';
	} else {
		ForceDie(); // 不存在的域名类型
	}
} elseif ('edit' == $conf_action) {
	$check_main_domain = true;
	if (0 == $vhost_conf_id || !is_numeric($vhost_conf_id)) {
		ForceDie(); //修改虚拟主机配置文件时找不到虚拟主机配置文件ID
	} elseif ('main' == $domain_type || 'subdomain' == $domain_type || 'addon' == $domain_type) {
		$result_nginx_conf = mysql_query("select * FROM vhost_nginx_conf WHERE vhostID=".$row['ID']." AND ID=".$vhost_conf_id);
		if (mysql_num_rows($result_nginx_conf) != 1) ForceDie();
		$row_nginx_conf = mysql_fetch_array($result_nginx_conf);
	} else {
		ForceDie(); //不存在的域名类型
	}
} else {
	ForceDie(); // 未知操作
}
// 检查是否存在主域
if(@$check_main_domain) if (mysql_num_rows(mysql_query("select * FROM vhost_nginx_conf WHERE vhostID=".$row['ID']." AND type='main'")) < 1) ForceDie();
if (count($err)){
	SetErrAlert($err);
	header("Location: vhost_panel.php?id=".$row['ID']);
	exit();
}
if ((@$conf_action == 'add' || @$conf_action == 'edit') && '' != @trim($_POST['token']) )
{
	// 获取 ServerID
	if ('add' == $conf_action){
		if ('main' != $domain_type)
			$serverid = $row['serverID'];
		else
			$serverid = trim(mysql_real_escape_string(@$_POST['serverid']));
/*
			if ('main' != $domain_type && !empty($serverid)){
			if ($row['serverID'] != $serverid) ForceDie(); // 初始化节点服务器与当前选择节点服务器不同
		}
		if ('main' != $domain_type)
		{
			$serverid = @$row['serverID'];// 获取 serverID
		} */
	} elseif ('edit' == $conf_action) {
		if (@$row['serverID']) {
			$serverid = @$row_nginx_conf['serverID'];// 获取 serverID
		} else {
			$err[]='致命的错误，无法获取节点服务器ID';
		}
	}
	if (is_numeric($serverid) && '' != $serverid)
	{ // 检查服务器节点是否存在
		if (IsAdmin()) {
			$result_server = mysql_query("select * FROM vhost_servers WHERE ID=".$serverid);
		} else {
			$nodes_array = explode(",", $row['nodes']);
			$where_nodes = 'AND alias in (';
			foreach ($nodes_array as $i) {
				$where_nodes .= "'$i', ";
			}
			$where_nodes = substr($where_nodes, 0, -2);
			$where_nodes .= ')';
			
			$result_server = mysql_query("select * FROM vhost_servers WHERE ID=".$serverid." AND !hidden AND vhostFree>0 $where_nodes");
		}
		if (mysql_num_rows($result_server) != 1)
			$err[]='请选择服务器节点';
	} else {
		$err[]='请选择服务器节点';
	}
	if ('subdomain' == $domain_type || 'addon' == $domain_type)
	{ // Same Check

		// 设置该 子域/附加域 根目录
		if ('add' == $conf_action) {
			// 子目录绑定检测
			if(trim($subdirectory) == '')$err[]='绑定子目录不能为空！';
			if(strstr($subdirectory, './'))$err[]='您的子目录包含无效字符！';
			if(substr($subdirectory, 0, 1)!='/')$err[]='您的子目录必须以正斜杠开始！';
			if(preg_match('/[^a-z0-9\_\/\.]+/i',$subdirectory))$err[]='您的子目录包含无效字符！';
			
			$root = $row['root'] . $subdirectory;
		} elseif ('edit' == $conf_action) { 
			
		} else {
			ForceDie();
		}
	}
	// 在编辑状态直接获取root路径
	if ('edit' == $conf_action)
		$root = $row_nginx_conf['root'];
	if ('subdomain' == $domain_type)
	{ // 子域，配置
		if ('add' == $conf_action) {
			if('' == $server_name)$err[]='请输入子域';//绑定子域
			if(preg_match('/[^a-z0-9\-\.]+/i',$server_name))$err[]='你输入的子域无效';// 绑定子域
			$result_nginx_domain_conf = mysql_query("select * FROM vhost_nginx_conf WHERE vhostID=".$row['ID']." AND server_name='".$subdomain."' AND serverID=".$serverid."  AND ( type='addon' OR type='main' ) ");
			if (mysql_num_rows($result_nginx_domain_conf) != 1) ForceDie(); // 主域/附加域，不存在
			$row_nginx_domain_conf = mysql_fetch_array($result_nginx_domain_conf);
			if ($row_nginx_domain_conf['server_name'] != $subdomain) ForceDie(); // 错误的主域/附加域
			// 设置 子域时的server_name
			$server_name = $server_name . '.' . $subdomain;
			if(!preg_match('/^([0-9a-zA-Z][0-9a-zA-Z-]{0,62}\.)+([0-9a-zA-Z][0-9a-zA-Z-]{0,62})\.?$/',$server_name)) $err[]='请输入正确的子域';
		} elseif ('edit' == $conf_action) {
			$server_name = $row_nginx_conf['server_name'];
		} else {
			ForceDie();
		}
	}
	elseif ('addon' == $domain_type)
	{ // 附加域，配置
		if ('add' == $conf_action)
		{
			if('' == $server_name)$err[]='请输入子域';//绑定子域
			if (substr($server_name,0,4) == 'www.')$err[]='绑定附加域请不要输入带www的子域';// 主域不带www
			if(!preg_match('/^([0-9a-zA-Z][0-9a-zA-Z-]{0,62}\.)+([0-9a-zA-Z][0-9a-zA-Z-]{0,62})\.?$/',$server_name)) $err[]='请输入正确的附加域';
		} elseif ('edit' == $conf_action) {
			$server_name = $row_nginx_conf['server_name'];
		}
	}
	elseif ('main' == $domain_type)
	{ // 主域，配置
		if ('add' == $conf_action)
		{
			if (substr($server_name,0,4) == 'www.')$err[]='绑定主域请不要输入带www的子域';// 主域不带www
			if(!preg_match('/^([0-9a-zA-Z][0-9a-zA-Z-]{0,62}\.)+([0-9a-zA-Z][0-9a-zA-Z-]{0,62})\.?$/',$server_name)) $err[]='请输入正确的主域';
			$root = '/home/wwwroot/' . $server_name;
		} elseif('edit' == $conf_action) {
			$server_name = $row_nginx_conf['server_name'];
		} else {
			ForceDie();
		}
	}
	if($row['ID'] != $vhostID) $err[]='致命的错误';
	if ($index == '')$err[]='请输入默认首页文件';
	if ($static == 0)$err[]='请选择伪静态规则';
	if ($mode != '0' && $mode != '1' && $mode != '2' && $mode != '3')$err[]='请选择模式';// 模式 0 HTML 1 PHP 2 反代 3 重定向
	if ($mode == '2' && !preg_match('/^((http|https):\/\/)?([\w-]+)\.([\w-]+)(\.[\w-]+)*(\/)?[^s]*?$/',$proxy_pass))$err[]='反向代理目标URL格式错误';
	if ($http_status_code != '301' && $http_status_code != '302')$err[]='请选择HTTP状态码';
	if ($path != '0' && $path != '1')$err[]='请选择相对或绝对路径';
	if ($mode == '3' && !preg_match('/^([a-z]+:\/\/)?([\w-]+)\.([\w-]+)(\.[\w-]+)*(\/)?[^s]*?$/',$rewrite))$err[]='重定向目标URL格式错误';
	if ($allow_spider != '0' && $allow_spider != '1')$err[]='请选择是否允许蜘蛛访问';
	if ($gzip != '0' && $gzip != '1')$err[]='请选择是否开启GZIP';
	if ($cache_images != '0' && $cache_images != '1')$err[]='请选择是否启用图片文件缓存';
	if ($cache_cssjs != '0' && $cache_cssjs != '1')$err[]='请选择是否启用CSS/JS文件缓存';
	if (@$ssl_enabled != 1) $ssl_enabled = 0; // 修正
	if ( 1 == $ssl_enabled )
	{
		// 检查是否有独立IP, 若没有则无法启用SSL
		$server_ips = mysql_query("select * FROM vhost_server_ips WHERE serverID=".$row['serverID']." AND IPv=4 AND Private=1 AND VhostID=".$row['ID']);
		if (mysql_num_rows($server_ips) < 1) $err[]='您的虚拟主机没有独立IP, 无法启用SSL!';
		if (empty($ssl_cer)) $err[]='请填写 Certificate 内容!';
		if (empty($ssl_key)) $err[]='请填写 Certificate Key 内容!';
		if (empty($ssl_ca)) $err[]='请填写 Certificate CA 内容!';
	}
	if ('add' == $conf_action){
	// 同一服务器是否已经绑定相同的域名
		$result_bind_domain = mysql_query("select * FROM vhost_nginx_conf WHERE serverID=".$serverid." AND server_name='".$server_name."'");
		if (@mysql_num_rows($result_bind_domain) > 0)
			$err[]='您输入的域名已在该节点上绑定。';
	}
	if ($static != 0)
	{ // 检查选择的伪静态规则是否存在
	$result_static = mysql_query("select * FROM vhost_static WHERE ID=".$static);
	if (mysql_num_rows($result_static) == 0)
		$err[]='请选择伪静态规则';
	}
	if(!count($err))
	{
		$conf_file = '';
		if ( 1 == $ssl_enabled )
		{
			$conf_file .= 'map $scheme $fastcgi_https {' . "\n"; // SSL 支援
			$conf_file .= 'default off;' . "\n"; // SSL 支援
			$conf_file .= 'https on;' . "\n"; // SSL 支援
			$conf_file .= '}' . "\n"; // SSL 支援
		}
	    $conf_file .= 'log_format  ' . $server_name . '.log  \'$remote_addr - $remote_user [$time_local] "$request" \'' . "\n" ;
		$conf_file .= '\'$status $body_bytes_sent "$http_referer" \'' . "\n" ;
		$conf_file .= '\'"$http_user_agent" $http_x_forwarded_for\';' . "\n" ;
		$conf_file .= "server\n{\n";
		$server_ips = mysql_query("select * FROM vhost_server_ips WHERE serverID=".$row['serverID']." AND IPv=4 AND Private=1 AND VhostID=".$row['ID']);
		if (@mysql_num_rows($server_ips) < 1) {
			$conf_file .= "listen 80; \n"; // 绑定默认IP, 默认端口
		} else {
			while($row_server_ip = mysql_fetch_array($server_ips)) {
				$conf_file .= "listen ".$row_server_ip['Address'].":80; \n"; // 绑定独立IP
				if ( 1 == $ssl_enabled )
				{
					$conf_file .= "listen ".$row_server_ip['Address'].":443 ssl; \n"; // SSL 支援
				}
			}
		}
		$conf_file .= "server_name www.$server_name $server_name;\n"; //主机名
		$conf_file .= "index $index;\n"; //默认文档
		if ($mode == '0' || $mode == '1')
			$conf_file .= "root $root;\n"; //根目录
		if ( 1 == $ssl_enabled )
		{
			$conf_file .= "ssl on; \n";
			$conf_file .= "ssl_certificate /home/wwwroot/$server_name.ssl/crt; \n";
			$conf_file .= "ssl_certificate_key /home/wwwroot/$server_name.ssl/key; \n";
			$conf_file .= "ssl_session_timeout 5m; \n";
			$conf_file .= "ssl_protocols SSLv2 SSLv3 TLSv1; \n";
			$conf_file .= "ssl_ciphers ALL:!ADH:!EXPORT56:RC4+RSA:+HIGH:+MEDIUM:+LOW:+SSLv2:+EXP; \n";
			$conf_file .= "ssl_prefer_server_ciphers on; \n";
		}
		// 伪静态
		if ($static != 0 && $mode =='1')
		{
			$row_static = mysql_fetch_array($result_static);
			$conf_file .= "\n" . $row_static['rewrite'] . "\n";
		}
		if ($mode == '0' || $mode == '1') 
		{ // HTTP 错误响应 与 错误页
			if ($error_page_403 != '')
				$conf_file .= "error_page 403 = $error_page_403;\n";
			if ($error_page_404 != '')
				$conf_file .= "error_page 404 = $error_page_404;\n";
			if ($error_page_500 != '')
				$conf_file .= "error_page 500 = $error_page_500;\n";
			if ($error_page_502 != '')
				$conf_file .= "error_page 502 = $error_page_502;\n";
		}
		if ($mode == '3'){ 
			$conf_file .= " rewrite ^(.*) $rewrite";//重定向
			if ($path == '1')
				$conf_file .= '$1'; // 相对路径
			if ($http_status_code == '301')
				$conf_file .= " permanent;\n"; //301
			else
				$conf_file .= ";\n"; //302
		}
		if ($mode == '2'){ //反向代理
			$conf_file .= "location / {\n";
			$conf_file .= "proxy_pass              ".$proxy_pass.";\n";
			$conf_file .= "proxy_redirect          off;\n";
			// $conf_file .= 'proxy_set_header        Host            $host;'."\n";
			$conf_file .= 'proxy_set_header        Referer         $http_referer;'."\n";
			$conf_file .= 'proxy_set_header        Cookie          $http_cookie;'."\n";
			$conf_file .= 'proxy_set_header        X-Real-IP       $remote_addr;'."\n";
			$conf_file .= 'proxy_set_header        X-Forwarded-For $proxy_add_x_forwarded_for;'."\n";
			$conf_file .= "}\n";
		}
		if ($mode == '1'){ //PHP 模式
		$conf_file .= "location ~ .*\.(php|php5)?$\n";
		$conf_file .= "{\n";
		$conf_file .= "fastcgi_pass  unix:/tmp/php-cgi.sock;\n"; // PHP 支持
		$conf_file .= "fastcgi_index index.php;\n";
		$conf_file .= "fastcgi_param  GATEWAY_INTERFACE  CGI/1.1;\n";
		$conf_file .= 'fastcgi_param  SERVER_SOFTWARE    Nginx;'."\n";
		$conf_file .= "\n";
		$conf_file .= 'fastcgi_param  QUERY_STRING       $query_string;'."\n";
		$conf_file .= 'fastcgi_param  REQUEST_METHOD     $request_method;'."\n";
		$conf_file .= 'fastcgi_param  CONTENT_TYPE       $content_type;'."\n";
		$conf_file .= 'fastcgi_param  CONTENT_LENGTH     $content_length;'."\n";
		$conf_file .= "\n";
		// $conf_file .= 'fastcgi_param  SCRIPT_FILENAME    '.$root.'/$fastcgi_script_name;'."\n";
		$conf_file .= 'fastcgi_param  SCRIPT_FILENAME    $document_root$fastcgi_script_name;';
		$conf_file .= 'fastcgi_param  SCRIPT_NAME        $fastcgi_script_name;'."\n";
		$conf_file .= 'fastcgi_param  REQUEST_URI        $request_uri;'."\n";
		$conf_file .= 'fastcgi_param  DOCUMENT_URI       $document_uri;'."\n";
		// $conf_file .= 'fastcgi_param  DOCUMENT_ROOT      '.$root.'/;'."\n";
		$conf_file .= 'fastcgi_param  DOCUMENT_ROOT      $document_root;';
		$conf_file .= 'fastcgi_param  SERVER_PROTOCOL    $server_protocol;'."\n";
		$conf_file .= "\n";
		$conf_file .= 'fastcgi_param  REMOTE_ADDR        $remote_addr;'."\n";
		$conf_file .= 'fastcgi_param  REMOTE_PORT        $remote_port;'."\n";
		$conf_file .= 'fastcgi_param  SERVER_ADDR        $server_addr;'."\n";
		$conf_file .= 'fastcgi_param  SERVER_PORT        $server_port;'."\n";
		$conf_file .= 'fastcgi_param  SERVER_NAME        $server_name;'."\n";
		$conf_file .= "\n";
		if ( 1 == $ssl_enabled )
		{
			$conf_file .= 'fastcgi_param  HTTPS $fastcgi_https; '."\n"; // SSL 支援
		}
		$conf_file .= "\n";
		$conf_file .= "# PHP only, required if PHP was built with --enable-force-cgi-redirect\n";
		$conf_file .= "fastcgi_param  REDIRECT_STATUS    200;\n";
		$conf_file .= "\n";
		$conf_file .= 'fastcgi_param  PHP_ADMIN_VALUE "open_basedir=$document_root:/tmp/";'; // 限制主目录
		$conf_file .= "}\n";
		}
		if ($mode == '0' || $mode == '1' && $cache_images == '1'){
		$conf_file .= "location ~ .*\.(gif|jpg|jpeg|png|bmp|swf)$\n"; // 图片 缓存
		$conf_file .= "{\n";
		$conf_file .= "expires      30d;\n";
		$conf_file .= "}\n";
		}
		if ($mode == '0' || $mode == '1' && $cache_cssjs == '1'){
		$conf_file .= "location ~ .*\.(js|css)?$\n"; // CSS JS 缓存
		$conf_file .= "{\n";
		$conf_file .= "expires      12h;\n";
		$conf_file .= "}\n";
		}
		$conf_file .= 'access_log  /home/wwwlogs/' . $server_name . ".log  " . $server_name . ".log; \n" ;
		$conf_file .= "}";
		echo '<div id="page">';
		echo '<p class="breadcrumb"><a href="index.php">'.$PanelName.'</a> &raquo; <a href="vhost_panel.php?id="'.$row['ID'].'">'.$row['domain'].'</a> &raquo; <strong>';
		if ('add' == $conf_action && 'main' == $domain_type ) echo '主机初始化/主域绑定';
		if ('add' == $conf_action && 'subdomain' == $domain_type ) echo '添加子域绑定';
		if ('add' == $conf_action && 'addon' == $domain_type ) echo '添加附加域绑定';
		if ('edit' == $conf_action && 'main' == $domain_type ) echo '修改主域绑定';
		if ('edit' == $conf_action && 'subdomain' == $domain_type ) echo '修改子域绑定';
		if ('edit' == $conf_action && 'addon' == $domain_type ) echo '修改附加域绑定';
		echo '</strong></p>';
		echo '<p id="progressbar"><img src="images/progress_bar_1.gif" /></p>';
		if ('add' == $conf_action){
			echo '<p>开始创建虚拟主机...</p>';
		} elseif ('edit' == $conf_action) {
			echo '<p>开始修改虚拟主机配置...</p>';
		}
		flush2();
		$s = date("Y-m-d g:i:s a");
		set_include_path('api/ssh/' . PATH_SEPARATOR . 'phpseclib');
		@include_once("api/ssh/Net/SSH2.php");
		@include_once("api/ssh/Net/SFTP.php");
		$row_server = mysql_fetch_array($result_server);
		$ssh = new Net_SSH2($row_server['ip'],$row_server['port']);
		$sftp = new Net_SFTP($row_server['ip'],$row_server['port']);
		if (!@$ssh->login($row_server['root'], $row_server['passwd'])) {
			// $err[]='与节点服务器通讯失败，无法连接节点服务器SSH。ERROR_CODE:SSHCONNECT';
			$error = true;
			echo '<p>与节点服务器SSH通讯失败</p>';
			flush2();
		} else {
			if (!@$sftp->login($row_server['root'], $row_server['passwd'])) {
				// $err[]='与节点服务器通讯失败，无法连接节点服务器SFTP。ERROR_CODE:SFTPCONNECT';
				$error = true;
				echo '<p>与节点服务器SFTP通讯失败</p>';
				flush2();
			} else {
				echo '<p>与节点服务器SSH&SFTP通讯成功</p>';
				flush2();
			}
		}
		if ( @$ssh && @$sftp ) { // 连接SSH和SFTP是否成功
		$ssh->exec("mkdir /usr/local/nginx/conf/vhost"); //创建虚拟主机配置文件存放目录
		if (!$sftp->chdir('/usr/local/nginx/conf/vhost')){
			// $err[]='与节点服务器通讯失败，无法切换到虚拟主机配置文件目录。ERROR_CODE:CHDIRVHOST';
			$error = true;
		} else {
			if ('edit' == $conf_action) {
				$ssh->exec('cp -f "/usr/local/nginx/conf/vhost/'.$server_name.'.conf" "/usr/local/nginx/conf/vhost/'.$server_name.'.conf.bak"');
				echo '<p>备份虚拟主机配置文件成功</p>';
			}	
			if ($sftp->put($server_name.'.conf', $conf_file)){
				$created_conf_ok = true;
				if ('add' == $conf_action){
					echo '<p>创建虚拟主机配置文件('.$server_name.'.conf)成功</p>';
				} elseif ('edit' == $conf_action) {
					echo '<p>修改虚拟主机配置文件('.$server_name.'.conf)成功</p>';
				}
				// SSL 支援
				if ( 1 == $ssl_enabled )
				{
					$ssh->exec("mkdir /home/wwwroot/$server_name.ssl");
					if ($sftp->chdir("/home/wwwroot/$server_name.ssl")) {
						if ($sftp->put('crt', $ssl_cer."\n".$ssl_ca)) {
							if (!$sftp->put('key', $ssl_key)) {
								$error = true;
								echo '<p>SSL证书文件上传失败</p>';
							}
						} else {
							$error = true;
							echo '<p>SSL证书文件上传失败</p>';
						}
					} else {
						$error = true;
						echo '<p>创建SSL证书及相关文件存储目录失败</p>';
					}
					if (!empty($error)) {
						// SSL 配置失败, 放弃.
						$ssh->exec("rm -fr /home/wwwroot/$server_name.ssl");
					}
				} else {
					$ssh->exec("rm -fr /home/wwwroot/$server_name.ssl");
				}
				flush2();
			} else {
				// $err[]='与节点服务器通讯失败，无法创建虚拟主机配置文件。ERROR_CODE:CREATEVHOST';
				$error = true;
				if ('add' == $conf_action){
					echo '<p>创建虚拟主机配置文件失败</p>';
				} elseif ('edit' == $conf_action) {
					echo '<p>修改虚拟主机配置文件失败</p>';
				}
				echo '<p></p>';
				flush2();
			}
		}
		if(empty($error))
		{
			$nginx_syntax_check = trim($ssh->exec("/usr/local/nginx/sbin/nginx -t"));
			if (!strstr($nginx_syntax_check,'syntax is ok')) // || strstr($nginx_syntax_check,'nginx:'))
			{ // 虚拟主机配置文件错误
				// $err[]='虚拟主机配置文件存在未知错误。ERROR_CODE:CHECKCONFFAIL';
				$error = true;
				$vhost_conf_error = true;
				echo '<p>虚拟主机配置文件存在错误</p>';
				$nginx_syntax_check = @str_replace("/usr/local/nginx/conf/", "", $nginx_syntax_check);
				/* if (strstr($nginx_syntax_check,$server_name.'.conf')) */
					echo '<textarea name="nginx_conf" rows="3" cols="60">'.@$nginx_syntax_check.'</textarea>';
				flush2();
			} else {
				echo '<p>虚拟主机配置文件语法检测通过</p>';
				flush2();
			}
		}
		if(empty($error))
		{
			$nginx_reload = trim($ssh->exec("/etc/init.d/nginx reload"));
			if (!strstr($nginx_reload,'Reloading nginx daemon configuration....')) // || strstr($nginx_syntax_check,'nginx can'))
			{
				// 重载失败
				// $err[]='虚拟主机配置文件存在未知错误。';
				// 尝试重启Nginx
				echo '<p>重载Nginx失败，尝试重启Nginx</p>';
				echo '<textarea name="nginx_conf" rows="3" cols="60">'.@$nginx_reload.'</textarea>';
				flush2();
				$ssh->exec("/etc/init.d/nginx restart");
				$nginx_restart = trim($ssh->exec("/etc/init.d/nginx restart"));
				if (!strstr($nginx_restart,'Restarting nginx daemon: nginx.'))
				{
					echo '<textarea name="nginx_conf" rows="3" cols="60">'.@$nginx_restart.'</textarea>';
					$nginx_restart = trim($ssh->exec("/etc/init.d/nginx restart")); //第二次重启
					if (!strstr($nginx_restart,'Restarting nginx daemon: nginx.'))
					{
						// $err[]='虚拟主机配置文件存在未知错误。ERROR_CODE:NGINXRESTART'; //重启两次失败，放弃尝试。
						$error = true;
						echo '<textarea name="nginx_conf" rows="3" cols="60">'.@$nginx_restart.'</textarea>';
					} else {
					echo '<p>经过2次Nginx重启，虚拟主机运行正常。</p>';
					flush2();
					}
				} else {
					echo '<p>经过1次Nginx重启，虚拟主机运行正常。</p>';
					flush2();
				}
			} else {
				//直接重载成功
				echo '<p>Nginx重载成功</p>';
				flush2();
			}
			if ('add' == $conf_action && 'main' == $domain_type){
				$ssh->exec("mkdir ".$root);
				$ssh->exec("chown -R www:www ".$root);
				$ssh->exec("chmod -R 777 ".$root);
				echo '<p>虚拟主机目录创建成功</p>';
				flush2();
			} elseif ('edit' == $conf_action) {
				/*
				$ssh->exec("chown -R www:www ".$root);
				$ssh->exec("chmod -R 777 ".$root);
				*/
				//删除虚拟主机配置文件备份
				$ssh->exec('rm -f "/usr/local/nginx/conf/vhost/'.$server_name.'.conf.bak"');
			}
			if ('edit' == $conf_action)
			{
				mysql_query("DELETE FROM vhost_nginx_conf WHERE ID=".$row_nginx_conf['ID']); // $vhost_conf_id
			}
			mysql_query("INSERT INTO vhost_nginx_conf(vhostID,serverID,type,server_name,root,subdirectory,`index`,static,mode,proxy_pass,http_status_code,path,rewrite,allow_spider,error_page_403,error_page_404,error_page_500,error_page_502,gzip,cache_images,cache_cssjs,`ssl`,ssl_certificate,ssl_certificate_key,ssl_certificate_ca)
							VALUES(
								".$vhostID.",
								".$serverid.",
								'".$domain_type."',
								'".$server_name."',
								'".$root."',
								'".$subdirectory."',
								'".$index."',
								'".$static."',
								".$mode.",
								'".$proxy_pass."',
								".$http_status_code.",
								".$path.",
								'".$rewrite."',
								".$allow_spider.",
								'".$error_page_403."',
								'".$error_page_404."',
								'".$error_page_500."',
								'".$error_page_502."',
								".$gzip.",
								".$cache_images.",
								".$cache_cssjs.",
								".$ssl_enabled.",
								'".$ssl_cer."',
								'".$ssl_key."',
								'".$ssl_ca."'
							)");
			if ('add' == $conf_action && 'main' == $domain_type){
				mysql_query("	UPDATE vhost SET
		
								serverID=$serverid,
								domain='$server_name',
								root='$root',
								status='Running',
								ip='".$row_server['ip']."',
								location='".$row_server['location']."'
								
								WHERE owner=".$_SESSION['UserID']."
								AND ID=$vhostID
								");
				mysql_query("	UPDATE vhost_servers SET
								vhostFree=vhostFree-1
								WHERE ID=".$row_server['ID']."
								");
								
			}
			if ('add' == $conf_action && 'main' == $domain_type ) {
				AddJob($vhostID, '虚拟主机初始化', '初始化成功', $s, $s, date("Y-m-d g:i:s a"), $con);
				$_SESSION['msg']['alert-success']='• 虚拟主机初始化成功';
			}
			if ('add' == $conf_action && 'subdomain' == $domain_type ) {
				AddJob($vhostID, '子域绑定成功', '绑定成功', $s, $s, date("Y-m-d g:i:s a"), $con);
				$_SESSION['msg']['alert-success']='• 子域绑定成功';
			}
			if ('add' == $conf_action && 'addon' == $domain_type ) {
				AddJob($vhostID, '附加域绑定成功', '绑定成功', $s, $s, date("Y-m-d g:i:s a"), $con);
				$_SESSION['msg']['alert-success']='• 附加域绑定成功';
			}
			if ('edit' == $conf_action && 'main' == $domain_type ) {
				AddJob($vhostID, '修改主域虚拟主机配置', '修改成功', $s, $s, date("Y-m-d g:i:s a"), $con);
				$_SESSION['msg']['alert-success']='• 主域虚拟主机配置文件修改成功';
			}
			if ('edit' == $conf_action && 'subdomain' == $domain_type ) {
				AddJob($vhostID, '修改子域虚拟主机配置', '修改成功', $s, $s, date("Y-m-d g:i:s a"), $con);
				$_SESSION['msg']['alert-success']='• 子域虚拟主机配置文件修改成功';
			}
			if ('edit' == $conf_action && 'addon' == $domain_type ) {
				AddJob($vhostID, '修改附加域虚拟主机配置', '修改成功', $s, $s, date("Y-m-d g:i:s a"), $con);
				$_SESSION['msg']['alert-success']='• 附加域虚拟主机配置文件修改成功';
			}
				if ('add' == $conf_action){

				} elseif ('edit' == $conf_action) {

				}
			// echo '<p>完成</p>';
			// echo '<p><a href="vhost_panel.php?id='.$vhostID.'">进入虚拟主机控制面板</a></p>';
			echo '<script language=JavaScript>top.location="vhost_panel.php?id='.$vhostID.'";</script>';
			flush2();
		}
		if(!empty($error)) //存在错误则删除配置文件
		{
			if (@$created_conf_ok)
			{
				if ('add' == $conf_action){
					$ssh->exec("rm /usr/local/nginx/conf/vhost/$server_name.conf");
					echo '<p>创建的虚拟主机配置文件已删除</p>';
					flush2();
				} elseif ('edit' == $conf_action) {
					$ssh->exec('mv -f "/usr/local/nginx/conf/vhost/'.$server_name.'.conf.bak" "/usr/local/nginx/conf/vhost/'.$server_name.'.conf"');
					echo '<p>备份虚拟主机配置文件已还原</p>';
					flush2();
				}
				if (@!$vhost_conf_error){
					// 如果不是虚拟主机配置文件语法检测错误
					$ssh->exec("/etc/init.d/nginx restart");
					echo '<p>重启Nginx成功</p>';
					flush2();
				}
			}
			if ('add' == $conf_action){
				echo '<p>虚拟主机创建失败</p>';
				flush2();
			} elseif ('edit' == $conf_action) {
				echo '<p>修改虚拟主机配置文件失败</p>';
				flush2();
			}
			// echo '<textarea name="nginx_conf" rows="3" cols="60">'.@$conf_file.'</textarea>';
			echo '<p><a href="javascript:history.go(-1)">返回修改虚拟主机配置</a></p>';
			flush2();
		}
		echo '<script language=JavaScript>document.getElementById(\'progressbar\').style.display = "none";</script>';
		echo '</div><br />';
		@include_once("footer.php");
		flush2();
		exit();		
		}
	}
	SetErrAlert($err);
}
?>
<script language=JavaScript>
function function_show(mode){
if (mode==2) {
document.getElementById('proxy_setting').style.display = "";
document.getElementById('proxy_setting_pass').style.display = "";
document.getElementById('urlredirect_setting').style.display = "none";
document.getElementById('urlredirect_http_status_code').style.display = "none";
document.getElementById('urlredirect_path').style.display = "none";
document.getElementById('urlredirect_rewrite').style.display = "none";
} else if (mode==3) {
document.getElementById('proxy_setting').style.display = "none";
document.getElementById('proxy_setting_pass').style.display = "none";
document.getElementById('urlredirect_setting').style.display = "";
document.getElementById('urlredirect_http_status_code').style.display = "";
document.getElementById('urlredirect_path').style.display = "";
document.getElementById('urlredirect_rewrite').style.display = "";
} else {
document.getElementById('proxy_setting').style.display = "none";
document.getElementById('proxy_setting_pass').style.display = "none";
document.getElementById('urlredirect_setting').style.display = "none";
document.getElementById('urlredirect_http_status_code').style.display = "none";
document.getElementById('urlredirect_path').style.display = "none";
document.getElementById('urlredirect_rewrite').style.display = "none";
}
}
function function_ssl(mode){
if (mode==1) {
document.getElementById('ssl_cer_setting').style.display = "";
document.getElementById('ssl_key_setting').style.display = "";
document.getElementById('ssl_ca_setting').style.display = "";
} else {
document.getElementById('ssl_cer_setting').style.display = "none";
document.getElementById('ssl_key_setting').style.display = "none";
document.getElementById('ssl_ca_setting').style.display = "none";
}
}
</script>
<div id="page">
<p class='breadcrumb'><a href='index.php'><?php echo $PanelName;?></a> &raquo; <a href='vhost_panel.php?id=<?php echo $row['ID']; ?>'><?php echo $row['domain']; ?></a> &raquo; <strong><?php
if ('add' == $conf_action && 'main' == $domain_type ) echo '主机初始化/主域绑定';
if ('add' == $conf_action && 'subdomain' == $domain_type ) echo '添加子域绑定';
if ('add' == $conf_action && 'addon' == $domain_type ) echo '添加附加域绑定';
if ('edit' == $conf_action && 'main' == $domain_type ) echo '修改主域绑定';
if ('edit' == $conf_action && 'subdomain' == $domain_type ) echo '修改子域绑定';
if ('edit' == $conf_action && 'addon' == $domain_type ) echo '修改附加域绑定';
?></strong></p>
<?php EchoAlert(); ?>
<form name="config_save" id="config_save" action="" method="post" onsubmit="document.getElementById('button_save').disabled = true;">
<table class="list">
	<tr>
		<th colspan="3"><?php
if ('add' == $conf_action && 'main' == $domain_type ) echo '主机初始化/主域绑定';
if ('add' == $conf_action && 'subdomain' == $domain_type ) echo '添加子域绑定';
if ('add' == $conf_action && 'addon' == $domain_type ) echo '添加附加域绑定';
if ('edit' == $conf_action && 'main' == $domain_type ) echo '修改主域绑定';
if ('edit' == $conf_action && 'subdomain' == $domain_type ) echo '修改子域绑定';
if ('edit' == $conf_action && 'addon' == $domain_type ) echo '修改附加域绑定';
?></th>
	</tr>
	<tr class="list_head">
		<td colspan="3"><?php
		if ('main' == $domain_type ) echo '主域';
		if ('subdomain' == $domain_type ) echo '子域';
		if ('addon' == $domain_type ) echo '附加域';
		?></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header"><?php
		if ('add' == $conf_action && 'main' == $domain_type ) echo 'www.';
		if ('add' == $conf_action && 'subdomain' == $domain_type ) echo '子域';
		if ('add' == $conf_action && 'addon' == $domain_type ) echo 'www.';
		if ('edit' == $conf_action && 'main' == $domain_type ) echo 'www.';
		if ('edit' == $conf_action && 'subdomain' == $domain_type ) echo '子域';
		if ('edit' == $conf_action && 'addon' == $domain_type ) echo 'www';
		?></td>
		<td>
		<input name="server_name" id="server_name" type="text" value="<?php echo @$row_nginx_conf['server_name']?$row_nginx_conf['server_name']:''; ?>" maxlength="99"  size="15" <?php echo @$row_nginx_conf['server_name']?' disabled="disabled" ':'';  ?>  />
		<?php if ('subdomain' == $domain_type && 'add' == $conf_action) { ?>
			 <strong>.</strong> 
			<select name="subdomain" id="subdomain" size="1">
			<?php 
			$result_domains = mysql_query("select * FROM vhost_nginx_conf WHERE vhostID=".$row['ID']." AND ( type='addon' OR type='main' ) ");
			while($row_domain = mysql_fetch_array($result_domains)) {
			?>
				<option value="<?php echo $row_domain['server_name']; ?>"><?php echo $row_domain['server_name']; ?></option>
			<?php } ?>
			</select>
		<?php } ?>
		</td> 
		<td class="hint"><?php
		if ('add' == $conf_action && 'main' == $domain_type ) echo '请输入不带www的域名';
		if ('add' == $conf_action && 'subdomain' == $domain_type ) echo '请输入子域名，以及选择已创建的主域或附加域';
		if ('add' == $conf_action && 'addon' == $domain_type ) echo '请输入不带www的域名';
		if ('edit' == $conf_action && 'main' == $domain_type ) echo '如需更改主域请删除主机后重新购买';
		if ('edit' == $conf_action && 'subdomain' == $domain_type ) echo '如需更改子域请删除绑定后重新创建';
		if ('edit' == $conf_action && 'addon' == $domain_type ) echo '如需更改附加域请删除绑定后重新创建';
		?></td>
	</tr>
<?php if ('subdomain' == $domain_type || 'addon' == $domain_type ) { ?>
	<tr class="list_entry">
		<td class="table_form_header">子目录</td>
		<td>
		<input name="subdirectory" id="subdirectory" type="text" value="<?php echo @$row_nginx_conf['subdirectory']?$row_nginx_conf['subdirectory']:'/'; ?>" maxlength="99"  size="30" <?php if ('edit' == $conf_action) echo 'disabled="disabled"' ?> />
		</td>
		<td class="hint">请输入该子域或附加域绑定的位于你网站根目录的子目录路径</td>
	</tr>
<?php } ?>
<?php if (empty($row['serverID'])) { ?>
	<tr class="list_head">
		<td colspan="3">选择数据中心</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">节点</td>
		<td colspan="4">
		<?php
			$result_servers = mysql_query("select * FROM vhost_servers"); // WHERE !hidden
			if (mysql_num_rows($result_servers) == 0) die("找不到任何可部署的节点, 请联系客服.");
			while($row_server = mysql_fetch_array($result_servers)) {
				if (IsAdmin()) {
					if ($row_server['hidden'])
						$showing_hidden = true;
					if ($row_server['vhostFree'] <= 0)
						$oversell = true;					
				} elseif ($row_server['hidden'] || $row_server['vhostFree'] <= 0) {
					continue;
				} else {
					$nodes_array = explode(",", $row['nodes']);
					foreach ($nodes_array as $i) {
						if ($i == $row_server['alias']){
							$show_node = true;
						}
					}
					if (@$show_node){
						$show_node = false;
					} else {
						continue;
					}
				}
			@$node_count++;
		?>
			<input type="radio" name="serverid" value="<?php echo $row_server['ID']; ?>" id="node_<?php echo $row_server['ID']; ?>" border="0" <?php
			if (@$row_nginx_conf['serverID'] == $row_server['ID'] || @$serverid == $row_server['ID'])
				echo ' checked="checked" ';
			if (@$row_nginx_conf['serverID'])
				echo ' disabled="disabled" ';
			if (@$i != 1 && !@$row_nginx_conf['serverID'])
			{
				$i = 1;
				echo ' checked="checked" ';
			}
			?>>
			<?php /*
			 #<?php echo $row_server['ID']; ?>
			*/ ?>
			<label for="node_<?php echo $row_server['ID']; ?>">
			<?php echo @$showing_hidden?'<span style="color:blue" title="该节点已隐藏">':''; ?>
			<?php echo @$oversell?'<span style="color:red" title="该节点已超售">':''; ?>
			代号: <strong><?php echo $row_server['alias']; ?></strong>
			CPU: <strong><?php echo $row_server['cpuinfo']; ?></strong>
			节点位置: <strong><?php echo @$row_server['location']?$row_server['location']:'未知'; ?></strong>
			<?php echo @$showing_hidden?'</span>':''; ?>
			<?php echo @$oversell?'</span>':''; ?>
			<?php /*
 			负载: <?php echo $row_server['loadaverage']; ?>
			在线时间: <?php echo $row_server['uptime']; ?>
			*/ ?>
			<?php
/* 			$server_ips = mysql_query("select * FROM vhost_server_ips WHERE serverID=".$row_server['ID']." AND IPv=6 AND public=1");
			if (@mysql_num_rows($server_ips) > 0) echo '<span style="color:#B766AD;">IPv6 Enabled</span>'; */
			?>
			</label>
			<br />
		<?php
		@$showing_hidden = false;
		@$oversell = false;
		}
		if (empty($node_count)) echo '<span style="color:red">可用于选择的节点列表为空, 请提交Ticket联系技术支持!</span>';
		?>
		</td>
	</tr>
<?php } ?>
<?php 
/*
if (@$conf_file != '' && 1 == 2) {
?>
	<tr class="list_head">
		<td colspan="3">高级设置</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">Nginx配置文件</td>
		<td colspan="2"><textarea name="nginx_conf" rows="3" cols="60"><?php echo @$conf_file; ?></textarea></td>
	</tr>
<?php } */ ?>
	<tr class="list_head">
		<td colspan="3">基本设置</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">默认首页文件</td>
		<td><input name="index" id="index" type="text" value="<?php echo @$row_nginx_conf['index']?$row_nginx_conf['index']:'index.htm index.html index.php'; ?>" maxlength="99"  size="40"  /></td><?php /* size="50" 填充满 */ ?>
		<td class="hint">以空格分隔</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">伪静态规则</td>
		<td><?php /* colspan="2" */ ?>
			<select name="static" id="static" size="1" >
<optgroup label="博客">
<?php
	$static_blog = mysql_query("select * FROM vhost_static WHERE class='blog' ");
	while($row_static_blog = mysql_fetch_array($static_blog)) {
?>
    <option value="<?php echo $row_static_blog['ID']; ?>" <?php echo @$row_nginx_conf['static']==$row_static_blog['ID']?@'selected="selected"':''?> ><?php echo $row_static_blog['program']; ?></option>
<?php
}
?>
</optgroup>
<optgroup label="论坛">
<?php 
	$static_forum = mysql_query("select * FROM vhost_static WHERE class='forum' ");
	while($row_static_forum = mysql_fetch_array($static_forum)) {
?>
    <option value="<?php echo $row_static_forum['ID']; ?>" <?php echo @$row_nginx_conf['static']==$row_static_forum['ID']?@'selected="selected"':''?> ><?php echo $row_static_forum['program']; ?></option>
<?php
}
?>
</optgroup>
<optgroup label="其他">
<?php 
	$static_other = mysql_query("select * FROM vhost_static WHERE class='other' ");
	while($row_static_other = mysql_fetch_array($static_other)) {
?>
    <option value="<?php echo $row_static_other['ID']; ?>" <?php echo @$row_nginx_conf['static']==$row_static_other['ID']?@'selected="selected"':''?> ><?php echo $row_static_other['program']; ?></option>
<?php 
}
?>
</optgroup>
</select>
<td class="hint">如无伪静态规则请选择空</td>
		</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">运行模式</td>
		<td colspan="2">
			<input type="radio" name="mode" id="mode_0" value="0" border="0" onclick="function_show(0);" <?php echo @$row_nginx_conf['mode']==0?@'checked="checked"':''?> /><label for="mode_0">HTML</label><br />
			<input type="radio" name="mode" id="mode_1" value="1"  border="0" onclick="function_show(0);" <?php
			echo @$row_nginx_conf['mode']=='1'?@'checked="checked"':'';
			if (empty($row_nginx_conf['mode'])) echo 'checked="checked"';
			?> /><label for="mode_1">PHP</label><br />
			<input type="radio" name="mode" id="mode_2" value="2"  border="0" onclick="function_show(2);" <?php echo @$row_nginx_conf['mode']=='2'?@'checked="checked"':''?> /><label for="mode_2">反向代理</label><br />
			<input type="radio" name="mode" id="mode_3" value="3"  border="0" onclick="function_show(3);" <?php echo @$row_nginx_conf['mode']=='3'?@'checked="checked"':''?> /><label for="mode_3">URL重定向</label>
		</td>
	</tr>
	<tr class="list_head" id="proxy_setting" <?php echo @$row_nginx_conf['mode']=='2'?@'':'style="display:none;"'?>>
		<td colspan="3">反向代理配置</td>
	</tr>
	<tr class="list_entry" id="proxy_setting_pass" <?php echo @$row_nginx_conf['mode']=='2'?@'':'style="display:none;"'?>>
		<td class="table_form_header">反向代理目标</td>
		<td colspan="2"><input name="proxy_pass" id="proxy_pass" type="text" value="<?php echo @$row_nginx_conf['proxy_pass']!=''?@$row_nginx_conf['proxy_pass']:'http://'?>" maxlength="255"  size="50"  /></td>
	</tr>
	<tr class="list_head" id="urlredirect_setting" <?php echo @$row_nginx_conf['mode']=='3'?@'':'style="display:none;"'?>>
		<td colspan="3">URL重定向配置</td>
	</tr>
	<tr class="list_entry" id="urlredirect_http_status_code" <?php echo @$row_nginx_conf['mode']=='3'?@'':'style="display:none;"'?>>
		<td class="table_form_header">HTTP状态码</td>
		<td>
			<input type="radio" name="http_status_code" value="301" id="301_redirect" border="0" <?php
			echo @$row_nginx_conf['http_status_code']=='301'?@'checked="checked"':'';
			if (empty($row_nginx_conf['http_status_code'])) echo 'checked="checked"';
			?> /><label for="301_redirect">301</label> <span style="color:#9D9D9D;">永久重定向</span><br />
			<input type="radio" name="http_status_code" value="302" id="302_redirect" border="0" <?php echo @$row_nginx_conf['http_status_code']=='302'?@'checked="checked"':''?> /><label for="302_redirect">302</label> <span style="color:#9D9D9D;">临时重定向</span><br />
		</td>
	</tr>
	<tr class="list_entry" id="urlredirect_path" <?php echo @$row_nginx_conf['mode']=='3'?@'':'style="display:none;"'?>>
		<td class="table_form_header">重定向至</td>
		<td>
			<input type="radio" name="path" id="path_1" value="1" border="0" <?php
			echo @$row_nginx_conf['path']=='1'?@'checked="checked"':'';
			if (empty($row_nginx_conf['path'])) echo 'checked="checked"';
			?> /><label for="path_1">相对路径</label> <span style="color:#9D9D9D;"></span><br />
			<input type="radio" name="path" value="0" id="path_0" border="0" <?php echo @$row_nginx_conf['path']=='0'?@'checked="checked"':''?> /><label for="path_0">绝对路径</label> <span style="color:#9D9D9D;"></span><br />
		</td>
	</tr>
	<tr class="list_entry" id="urlredirect_rewrite" <?php echo @$row_nginx_conf['mode']=='3'?@'':'style="display:none;"'?>>
		<td class="table_form_header">重定向目标</td>
		<td colspan="2"><input name="rewrite" id="rewrite" type="text" value="<?php echo @$row_nginx_conf['rewrite']!=''?$row_nginx_conf['rewrite']:'http://'?>" maxlength="255" size="50" /></td>
	</tr>
<?php
$server_ips = mysql_query("select * FROM vhost_server_ips WHERE serverID=".$row['serverID']." AND IPv=4 AND Private=1 AND VhostID=".$row['ID']);
if (@mysql_num_rows($server_ips) > 0) {
?>
	<tr class="list_head">
		<td colspan="3">SSL配置</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">是否启用SSL</td>
		<td>
			<input type="radio" id="ssl_enable" name="ssl_enable" value="1" <?php
			echo @$row_nginx_conf['ssl']=='1'?@'checked="checked"':'';
			?>  onclick="function_ssl(1);"><label for="ssl_enable">是</label>
			<input type="radio" name="ssl_enable" id="ssl_disable" value="0" <?php
			echo @$row_nginx_conf['ssl']=='0'?@'checked="checked"':'';
			if (empty($row_nginx_conf['ssl'])) echo 'checked="checked"';
			?> onclick="function_ssl(0);"><label for="ssl_disable">否</label>
		</td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry" id="ssl_cer_setting" <?php echo @$row_nginx_conf['ssl']=='1'?@'':'style="display:none;"'?>>
		<td class="table_form_header">SSL Certificate</td>
		<td><textarea name="ssl_cer" style="width:310px;height:80px;"><?php echo @$row_nginx_conf['ssl_certificate']; ?></textarea></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry" id="ssl_key_setting" <?php echo @$row_nginx_conf['ssl']=='1'?@'':'style="display:none;"'?>>
		<td class="table_form_header">SSL Certificate Key</td>
		<td><textarea name="ssl_key" style="width:310px;height:80px;"><?php echo @$row_nginx_conf['ssl_certificate_key']; ?></textarea></td>
		<td class="hint"></td>
	</tr>
	<tr class="list_entry" id="ssl_ca_setting" <?php echo @$row_nginx_conf['ssl']=='1'?@'':'style="display:none;"'?>>
		<td class="table_form_header">SSL Certificate CA</td>
		<td><textarea name="ssl_ca" style="width:310px;height:80px;"><?php echo @$row_nginx_conf['ssl_certificate_ca']; ?></textarea></td>
		<td class="hint"></td>
	</tr>
<?php } ?>
	<tr class="list_head">
		<td colspan="3">蜘蛛访问权限设置</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">是否允许蜘蛛</td>
		<td>
			<input type="radio" id="allow_spider" name="allow_spider" value="0" <?php
			echo @$row_nginx_conf['allow_spider']=='0'?@'checked="checked"':'';
			if (empty($row_nginx_conf['allow_spider'])) echo 'checked="checked"';
			?>><label for="allow_spider">是</label>
			<input type="radio" name="allow_spider" id="deny_spider" value="1" <?php echo @$row_nginx_conf['allow_spider']=='1'?@'checked="checked"':''?>><label for="deny_spider">否</label>
		</td>
		<td class="hint">禁止蜘蛛访问后将不会再被搜索引擎收录</td>
	</tr>
	<tr class="list_head">
		<td colspan="3">错误页</td> <!-- fastcgi_intercept_errors on; -->
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">错误页 403</td>
		<td><input name="error_page_403" id="error_page_403" value="<?php echo @$row_nginx_conf['error_page_403']!=''?$row_nginx_conf['error_page_403']:'/403.html'?>"  type="text" value="ZhuJiMao_Nginx" maxlength="60"  size="20"  /></td>
		<td class="hint">禁止访问</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">错误页 404</td>
		<td><input name="error_page_404" id="error_page_404" value="<?php echo @$row_nginx_conf['error_page_404']!=''?$row_nginx_conf['error_page_404']:'/404.html'?>"  type="text" value="ZhuJiMao_Nginx" maxlength="60"  size="20"  /></td>
		<td class="hint">没有找到文件或目录</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">错误页 500</td>
		<td><input name="error_page_500" id="error_page_500" value="<?php echo @$row_nginx_conf['error_page_500']!=''?$row_nginx_conf['error_page_500']:''?>"  type="text" value="ZhuJiMao_Nginx" maxlength="60"  size="20"  /></td>
		<td class="hint">内部服务器错误</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">错误页 502</td>
		<td><input name="error_page_502" id="error_page_502" value="<?php echo @$row_nginx_conf['error_page_502']!=''?$row_nginx_conf['error_page_502']:'/502.html'?>"  type="text" value="ZhuJiMao_Nginx" maxlength="60"  size="20"  /></td>
		<td class="hint">Web服务器用作网关或代理服务器时收到了无效响应</td>
	</tr>
	<tr class="list_head">
		<td colspan="3">缓存及页面优化设置</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">启用GZip压缩</td>
		<td><input type="radio" name="gzip" id="enable_gzip" value="1" <?php
		echo @$row_nginx_conf['gzip']=='1'?@'checked="checked"':'';
		if (empty($row_nginx_conf['gzip'])) echo 'checked="checked"';
		?>><label for="enable_gzip">是</label>
		<input type="radio" name="gzip" id="disable_gzip" value="0" <?php echo @$row_nginx_conf['gzip']=='0'?@'checked="checked"':''?> ><label for="disable_gzip">否</label></td>
		<td class="hint">Gzip开启后会将输出到用户浏览器的数据进行压缩处理</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">启用图片文件缓存</td>
		<td><input type="radio" name="cache_images" id="cache_images_enable" value="1" <?php
		echo @$row_nginx_conf['cache_images']=='1'?@'checked="checked"':'';
		if (empty($row_nginx_conf['cache_images'])) echo 'checked="checked"';
		?>><label for="cache_images_enable">是</label>
		<input type="radio" name="cache_images" id="cache_images_disable" value="0" <?php echo @$row_nginx_conf['cache_images']=='0'?@'checked="checked"':''?>><label for="cache_images_disable">否</label></td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">启用CSS/JS文件缓存</td>
		<td><input type="radio" name="cache_cssjs" id="cache_cssjs_enable" value="1" <?php
		echo @$row_nginx_conf['cache_cssjs']=='1'?@'checked="checked"':'';
		if (empty($row_nginx_conf['cache_cssjs'])) echo 'checked="checked"';
		?>><label for="cache_cssjs_enable">是</label>
		<input type="radio" name="cache_cssjs" id="cache_cssjs_disable" value="0" <?php echo @$row_nginx_conf['cache_cssjs']=='0'?@'checked="checked"':''?>><label for="cache_cssjs_disable">否</label></td>
	</tr>
	<tr class="list_entry">
		<td width="200"></td>
		<td colspan="3">
			<br />
			<input type="hidden" name="type" value="<?php echo @$domain_type; ?>" />
			<input type="hidden" name="action" value="<?php echo @$conf_action; ?>" />
			<input type="hidden" name="vhost_conf_id" value="<?php echo @$vhost_conf_id; ?>" />
			<input type="hidden" name="vhostid" value="<?php echo $row['ID']; ?>" />
			<input type="hidden" name="token" value="<?php echo randStr(32); ?>" />
			<input class="button" id="button_save" type="submit" value="保存设置">
		</td>
	</tr>
</table>
</form>
</div>
<?php @include_once("footer.php") ?>