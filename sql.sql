-- phpMyAdmin SQL Dump
-- version 4.2.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: 2014-07-11 20:14:57
-- 服务器版本： 10.0.12-MariaDB-log
-- PHP Version: 5.4.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `webpanel`
--

-- --------------------------------------------------------

--
-- 表的结构 `tickets`
--

CREATE TABLE IF NOT EXISTS `tickets` (
`ID` int(11) NOT NULL,
  `TicketID` int(11) DEFAULT NULL,
  `Summary` varchar(255) DEFAULT NULL,
  `Description` text NOT NULL,
  `Status` varchar(255) DEFAULT NULL,
  `Opened` datetime DEFAULT NULL,
  `OpenedBy` int(11) DEFAULT NULL,
  `LastUpdated` datetime NOT NULL,
  `LastUpdatedBy` int(11) NOT NULL,
  `ClosedOn` datetime DEFAULT NULL,
  `ClosedBy` int(11) DEFAULT NULL,
  `Regarding` varchar(255) DEFAULT NULL,
  `RegardingURL` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE IF NOT EXISTS `users` (
`ID` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `locked` int(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `users`
--

INSERT INTO `users` (`ID`, `username`, `password`, `email`, `api_key`, `locked`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin@admin.com', '', 0);

-- --------------------------------------------------------

--
-- 表的结构 `users_billing`
--

CREATE TABLE IF NOT EXISTS `users_billing` (
`ID` int(11) NOT NULL,
  `Parent` int(11) DEFAULT NULL,
  `UserID` int(11) NOT NULL,
  `type` int(1) NOT NULL COMMENT '0 充值 1 订单 2 续费',
  `date` datetime NOT NULL,
  `datefrom` date DEFAULT NULL,
  `dateto` date DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `One_time_fee` double DEFAULT '0',
  `amount` double NOT NULL,
  `paid` int(1) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `users_extension`
--

CREATE TABLE IF NOT EXISTS `users_extension` (
  `UserID` int(11) NOT NULL,
  `credit` double NOT NULL,
  `companyname` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `address1` varchar(255) DEFAULT NULL,
  `address2` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `zip` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `phone1` varchar(255) DEFAULT NULL,
  `phone2` varchar(255) DEFAULT NULL,
  `qq` varchar(255) DEFAULT NULL,
  `regdate` datetime DEFAULT NULL,
  `regip` varchar(255) DEFAULT NULL,
  `lastlogindate` datetime DEFAULT NULL,
  `lastloginip` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `users_extension`
--

INSERT INTO `users_extension` (`UserID`, `credit`, `companyname`, `email`, `firstname`, `lastname`, `address1`, `address2`, `city`, `state`, `zip`, `country`, `phone1`, `phone2`, `qq`, `regdate`, `regip`, `lastlogindate`, `lastloginip`) VALUES
(1, 888888, '', 'admin@admin.com', '', '', '', '', '', '', '', '', '', '', '', '1970-01-01 00:00:00', '127.0.0.1', '1970-01-01 00:00:00', '127.0.0.1');

-- --------------------------------------------------------

--
-- 表的结构 `users_forgot`
--

CREATE TABLE IF NOT EXISTS `users_forgot` (
`ID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `token` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `vhost`
--

CREATE TABLE IF NOT EXISTS `vhost` (
`ID` int(11) NOT NULL,
  `owner` int(11) NOT NULL,
  `serverID` int(11) DEFAULT NULL,
  `orderID` int(11) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `root` varchar(255) NOT NULL,
  `status` varchar(255) CHARACTER SET ucs2 NOT NULL,
  `plan` int(11) NOT NULL,
  `planname` varchar(255) NOT NULL,
  `cycle` int(11) NOT NULL,
  `duedate` date NOT NULL,
  `nodes` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `backup` int(11) DEFAULT NULL,
  `space` int(11) DEFAULT NULL,
  `spaceUsed` int(11) DEFAULT NULL,
  `webtraffic` int(11) DEFAULT NULL,
  `webtrafficUsed` bigint(20) DEFAULT '0',
  `db` int(11) DEFAULT NULL,
  `subdomain` int(11) DEFAULT NULL,
  `addon` int(11) DEFAULT '0' COMMENT '附加域',
  `ftp` int(11) DEFAULT NULL,
  `price` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `vhost_app`
--

CREATE TABLE IF NOT EXISTS `vhost_app` (
`ID` int(11) NOT NULL,
  `sort` int(11) NOT NULL,
  `app_name` varchar(255) NOT NULL,
  `app_version` varchar(255) NOT NULL,
  `app_site` varchar(255) CHARACTER SET ucs2 NOT NULL,
  `app_dl_url` varchar(255) NOT NULL,
  `app_image` varchar(255) NOT NULL,
  `app_localpath` varchar(255) NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- 转存表中的数据 `vhost_app`
--

INSERT INTO `vhost_app` (`ID`, `sort`, `app_name`, `app_version`, `app_site`, `app_dl_url`, `app_image`, `app_localpath`) VALUES
(1, 1, 'WordPress', '3.9', 'http://wordpress.org/', 'http://dl/wordpress.zip', 'images/install/wordpress.png', '/root/app/wordpress.zip'),
(2, 2, 'Typecho', '1.0', 'http://typecho.org/', 'http://dl/typecho.zip', 'images/install/typecho.png', '/root/app/typecho.zip'),
(3, 3, 'Discuz', 'X3.1', 'http://www.discuz.net/', 'http://dl/discuz.zip', 'images/install/discuz.png', '/root/app/discuz.zip'),
(4, 4, 'phpwind', '9.0', 'http://www.phpwind.net/', 'http://dl/phpwind.zip', 'images/install/phpwind.png', '/root/app/phpwind.zip'),
(5, 5, 'phpBB', '3.0.12', 'http://www.phpbb.com/', 'http://dl/phpbb.zip', 'images/install/phpbb.png', '/root/app/phpbb.zip');

-- --------------------------------------------------------

--
-- 表的结构 `vhost_ftp`
--

CREATE TABLE IF NOT EXISTS `vhost_ftp` (
`ID` int(11) NOT NULL,
  `VhostID` int(11) NOT NULL,
  `ServerID` int(11) NOT NULL,
  `User` varchar(16) NOT NULL DEFAULT '',
  `Password` varchar(32) NOT NULL DEFAULT '',
  `Uid` int(11) NOT NULL DEFAULT '14',
  `Gid` int(11) NOT NULL DEFAULT '5',
  `Dir` varchar(128) NOT NULL DEFAULT '',
  `RelativePath` varchar(255) NOT NULL,
  `QuotaFiles` int(10) NOT NULL DEFAULT '500',
  `QuotaSize` int(10) NOT NULL DEFAULT '30',
  `ULBandwidth` int(10) NOT NULL DEFAULT '80',
  `DLBandwidth` int(10) NOT NULL DEFAULT '80',
  `Ipaddress` varchar(15) NOT NULL DEFAULT '*',
  `Comment` tinytext,
  `Status` enum('0','1') NOT NULL DEFAULT '1',
  `ULRatio` smallint(5) NOT NULL DEFAULT '1',
  `DLRatio` smallint(5) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `vhost_job_queue`
--

CREATE TABLE IF NOT EXISTS `vhost_job_queue` (
`JobID` int(11) NOT NULL,
  `VhostID` int(11) NOT NULL,
  `Action` varchar(255) NOT NULL,
  `Result` varchar(255) NOT NULL,
  `Entered` datetime NOT NULL,
  `Started` datetime NOT NULL,
  `Finished` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `vhost_mysql_db`
--

CREATE TABLE IF NOT EXISTS `vhost_mysql_db` (
`ID` int(11) NOT NULL,
  `VhostID` int(11) NOT NULL,
  `ServerID` int(11) NOT NULL,
  `User` varchar(255) NOT NULL,
  `Host` varchar(255) NOT NULL DEFAULT 'localhost',
  `Password` varchar(255) NOT NULL,
  `DB` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `vhost_nginx_conf`
--

CREATE TABLE IF NOT EXISTS `vhost_nginx_conf` (
`ID` int(11) NOT NULL,
  `vhostID` int(11) NOT NULL,
  `serverID` int(11) DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'main',
  `server_name` varchar(255) NOT NULL,
  `root` varchar(255) NOT NULL,
  `subdirectory` varchar(255) NOT NULL,
  `index` varchar(255) DEFAULT 'index.htm index.html index.php' COMMENT '默认首页',
  `static` int(11) DEFAULT NULL,
  `mode` int(1) DEFAULT '1' COMMENT '0 HTML 1 PHP 2 反代 3 重定向',
  `proxy_pass` varchar(255) DEFAULT 'http://',
  `http_status_code` varchar(3) DEFAULT '301',
  `path` int(1) DEFAULT '1' COMMENT '0 绝对路径 1 相对路径',
  `rewrite` varchar(255) DEFAULT 'http://',
  `allow_spider` int(1) DEFAULT '0' COMMENT '0 允许 1 不允许',
  `error_page_403` varchar(255) DEFAULT '/403.html',
  `error_page_404` varchar(255) DEFAULT '/404.html',
  `error_page_500` varchar(255) DEFAULT '/500.html',
  `error_page_502` varchar(255) DEFAULT '/502.html',
  `gzip` int(1) DEFAULT '1' COMMENT '0 关闭 1 开启',
  `cache_images` int(1) DEFAULT '1' COMMENT '0 关闭 1 开启',
  `cache_cssjs` int(1) DEFAULT '1' COMMENT '0 关闭 1 开启',
  `ssl` int(1) DEFAULT NULL,
  `ssl_certificate` text,
  `ssl_certificate_key` text,
  `ssl_certificate_ca` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `vhost_plan`
--

CREATE TABLE IF NOT EXISTS `vhost_plan` (
`ID` int(11) NOT NULL,
  `sort` int(11) DEFAULT NULL,
  `checked` int(11) NOT NULL,
  `hidden` int(11) DEFAULT '0',
  `planname` varchar(255) NOT NULL,
  `cycle` int(11) NOT NULL,
  `nodes` varchar(255) NOT NULL,
  `backup` int(11) NOT NULL,
  `space` int(11) NOT NULL,
  `webtraffic` double NOT NULL,
  `db` int(11) NOT NULL,
  `subdomain` int(11) NOT NULL,
  `addon` int(11) NOT NULL DEFAULT '0' COMMENT '附加域',
  `ftp` int(11) NOT NULL,
  `price` double NOT NULL,
  `available` int(11) NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=28 ;

-- --------------------------------------------------------

--
-- 表的结构 `vhost_servers`
--

CREATE TABLE IF NOT EXISTS `vhost_servers` (
`ID` int(11) NOT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `root` varchar(255) NOT NULL,
  `passwd` varchar(255) NOT NULL,
  `mysqlpasswd` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `port` varchar(255) NOT NULL DEFAULT '22',
  `location` varchar(255) DEFAULT NULL,
  `hidden` int(1) NOT NULL DEFAULT '0',
  `vhostTotal` int(11) NOT NULL,
  `vhostFree` int(11) NOT NULL,
  `cpucore` int(11) DEFAULT NULL,
  `cpuinfo` varchar(255) CHARACTER SET ucs2 DEFAULT NULL,
  `uptime` varchar(255) DEFAULT NULL,
  `uptimedays` int(11) DEFAULT NULL,
  `loadaverage` varchar(255) DEFAULT NULL,
  `memTotal` int(11) DEFAULT NULL,
  `memFree` int(11) DEFAULT NULL,
  `swapTotal` int(11) DEFAULT NULL,
  `swapFree` int(11) DEFAULT NULL,
  `diskTotal` int(11) DEFAULT NULL,
  `diskFree` int(11) DEFAULT NULL,
  `netInput` varchar(255) DEFAULT NULL,
  `netOut` varchar(255) DEFAULT NULL,
  `downtime` int(1) NOT NULL DEFAULT '0',
  `lastupdate` datetime DEFAULT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=31 ;

-- --------------------------------------------------------

--
-- 表的结构 `vhost_server_ips`
--

CREATE TABLE IF NOT EXISTS `vhost_server_ips` (
`ID` int(11) NOT NULL,
  `serverID` int(11) NOT NULL,
  `IPv` int(1) NOT NULL,
  `Address` varchar(255) NOT NULL,
  `Private` int(1) DEFAULT '0',
  `VhostID` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `vhost_static`
--

CREATE TABLE IF NOT EXISTS `vhost_static` (
`ID` int(11) NOT NULL,
  `class` varchar(255) NOT NULL,
  `program` varchar(255) NOT NULL,
  `rewrite` text NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

--
-- 转存表中的数据 `vhost_static`
--

INSERT INTO `vhost_static` (`ID`, `class`, `program`, `rewrite`) VALUES
(1, 'blog', 'WordPress', 'if ($http_referer ~* "www\\.17ce\\.com") {\n	return 200;\n}\nif ($http_user_agent ~* "webbench|ApacheBench|JoeDog") {\n	return 200;\n}\nif ($http_user_agent ~ ^$) {\n	return 200;\n}\n\nlocation / {\nrewrite ^/wp-admin$ /wp-admin/ permanent;\nif (-f $request_filename/index.html){\n                rewrite (.*) $1/index.html break;\n        }\nif (-f $request_filename/index.php){\n                rewrite (.*) $1/index.php;\n        }\nif (!-f $request_filename){\n                rewrite (.*) /index.php;\n        }\n}'),
(2, 'blog', 'Typecho', 'if ($http_referer ~* "www\\.17ce\\.com") {\n	return 200;\n}\nif ($http_user_agent ~* "webbench|ApacheBench|JoeDog") {\n	return 200;\n}\nif ($http_user_agent ~ ^$) {\n	return 200;\n}\n\nlocation / {\n        index index.html index.php;\n        if (-f $request_filename/index.html){\n            rewrite (.*) $1/index.html break;\n        }\n        if (-f $request_filename/index.php){\n            rewrite (.*) $1/index.php;\n        }\n        if (!-f $request_filename){\n            rewrite (.*) /index.php;\n        }\n    }'),
(3, 'blog', 'emlog', 'if ($http_referer ~* "www\\.17ce\\.com") {\n	return 200;\n}\nif ($http_user_agent ~* "webbench|ApacheBench|JoeDog") {\n	return 200;\n}\nif ($http_user_agent ~ ^$) {\n	return 200;\n}\n\nlocation / {\n        index index.php index.html;\n        if (!-e $request_filename)\n        {\n                rewrite ^/(.+)$ /index.php last;\n        }\n}'),
(4, 'forum', 'Discuz', 'if ($http_referer ~* "www\\.17ce\\.com") {\n	return 200;\n}\nif ($http_user_agent ~* "webbench|ApacheBench|JoeDog") {\n	return 200;\n}\nif ($http_user_agent ~ ^$) {\n	return 200;\n}\n\nlocation / {\n            rewrite ^/archiver/((fid|tid)-[\\w\\-]+\\.html)$ /archiver/index.php?$1 last;\n            rewrite ^/forum-([0-9]+)-([0-9]+)\\.html$ /forumdisplay.php?fid=$1&page=$2 last;\n            rewrite ^/thread-([0-9]+)-([0-9]+)-([0-9]+)\\.html$ /viewthread.php?tid=$1&extra=page%3D$3&page=$2 last;\n            rewrite ^/space-(username|uid)-(.+)\\.html$ /space.php?$1=$2 last;\n            rewrite ^/tag-(.+)\\.html$ /tag.php?name=$1 last;\n        }'),
(5, 'forum', 'DiscuzX', 'if ($http_referer ~* "www\\.17ce\\.com") {\n	return 200;\n}\nif ($http_user_agent ~* "webbench|ApacheBench|JoeDog") {\n	return 200;\n}\nif ($http_user_agent ~ ^$) {\n	return 200;\n}\n\nrewrite ^([^\\.]*)/topic-(.+)\\.html$ $1/portal.php?mod=topic&topic=$2 last;\nrewrite ^([^\\.]*)/article-([0-9]+)-([0-9]+)\\.html$ $1/portal.php?mod=view&aid=$2&page=$3 last;\nrewrite ^([^\\.]*)/forum-(\\w+)-([0-9]+)\\.html$ $1/forum.php?mod=forumdisplay&fid=$2&page=$3 last;\nrewrite ^([^\\.]*)/thread-([0-9]+)-([0-9]+)-([0-9]+)\\.html$ $1/forum.php?mod=viewthread&tid=$2&extra=page%3D$4&page=$3 last;\nrewrite ^([^\\.]*)/group-([0-9]+)-([0-9]+)\\.html$ $1/forum.php?mod=group&fid=$2&page=$3 last;\nrewrite ^([^\\.]*)/space-(username|uid)-(.+)\\.html$ $1/home.php?mod=space&$2=$3 last;\nrewrite ^([^\\.]*)/([a-z]+)-(.+)\\.html$ $1/$2.php?rewrite=$3 last;\nif (!-e $request_filename) {\n        return 404;\n}'),
(9, 'other', 'ownCloud', 'if ($http_referer ~* "www\\.17ce\\.com") {\n	return 200;\n}\nif ($http_user_agent ~* "webbench|ApacheBench|JoeDog") {\n	return 200;\n}\nif ($http_user_agent ~ ^$) {\n	return 200;\n}\n\nlocation / {\nif (!-f $request_filename){\n                rewrite (.*) /index.php;\n        }\n}'),
(8, 'other', '空', 'if ($http_referer ~* "www\\.17ce\\.com") {\n	return 200;\n}\nif ($http_user_agent ~* "webbench|ApacheBench|JoeDog") {\n	return 200;\n}\nif ($http_user_agent ~ ^$) {\n	return 200;\n}');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
 ADD PRIMARY KEY (`ID`), ADD UNIQUE KEY `username` (`username`), ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users_billing`
--
ALTER TABLE `users_billing`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `users_forgot`
--
ALTER TABLE `users_forgot`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `vhost`
--
ALTER TABLE `vhost`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `vhost_app`
--
ALTER TABLE `vhost_app`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `vhost_ftp`
--
ALTER TABLE `vhost_ftp`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `vhost_job_queue`
--
ALTER TABLE `vhost_job_queue`
 ADD PRIMARY KEY (`JobID`);

--
-- Indexes for table `vhost_mysql_db`
--
ALTER TABLE `vhost_mysql_db`
 ADD PRIMARY KEY (`ID`), ADD UNIQUE KEY `ID` (`ID`);

--
-- Indexes for table `vhost_nginx_conf`
--
ALTER TABLE `vhost_nginx_conf`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `vhost_plan`
--
ALTER TABLE `vhost_plan`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `vhost_servers`
--
ALTER TABLE `vhost_servers`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `vhost_server_ips`
--
ALTER TABLE `vhost_server_ips`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `vhost_static`
--
ALTER TABLE `vhost_static`
 ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `users_billing`
--
ALTER TABLE `users_billing`
MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users_forgot`
--
ALTER TABLE `users_forgot`
MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `vhost`
--
ALTER TABLE `vhost`
MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `vhost_app`
--
ALTER TABLE `vhost_app`
MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `vhost_ftp`
--
ALTER TABLE `vhost_ftp`
MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `vhost_job_queue`
--
ALTER TABLE `vhost_job_queue`
MODIFY `JobID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `vhost_mysql_db`
--
ALTER TABLE `vhost_mysql_db`
MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `vhost_nginx_conf`
--
ALTER TABLE `vhost_nginx_conf`
MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `vhost_plan`
--
ALTER TABLE `vhost_plan`
MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=28;
--
-- AUTO_INCREMENT for table `vhost_servers`
--
ALTER TABLE `vhost_servers`
MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=31;
--
-- AUTO_INCREMENT for table `vhost_server_ips`
--
ALTER TABLE `vhost_server_ips`
MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `vhost_static`
--
ALTER TABLE `vhost_static`
MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=10;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
