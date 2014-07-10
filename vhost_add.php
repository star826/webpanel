<?php
$page = "vhost_add.php";
@include_once("header.php");
@include_once("alert_function.php");
if (@$_POST['action'] == 'order')
{
	$err = array();
	if (!$_POST['PlanTag'])
		$err[]='请选择一款主机方案';
	if (!$_POST['Frequency'])
		$err[]='请选择付款周期';
	if(count($err))
	{
		SetErrAlert($err);
		header("Location: vhost_add.php");
		exit();
	}
	$frequency = check_input(floor($_POST['Frequency'])); //付款周期
	$plantag = check_input($_POST['PlanTag']); //主机方案
	$result = mysql_query("select * FROM vhost_plan WHERE ID=".$plantag);
	if (mysql_num_rows($result) == 0) ForceDie();
	$row = mysql_fetch_array($result);
	$plan_planname = $row['planname'];
	$plan_cycle = $row['cycle'];
	$plan_location = @$row['location'];
	$plan_nodes = $row['nodes'];
	$plan_backup = $row['backup'];
	$plan_space = $row['space'];
	$plan_webtraffic = $row['webtraffic'];
	$plan_database = $row['db'];
	$plan_subdomain = $row['subdomain'];
	$plan_addon = $row['addon'];
	$plan_ftp = $row['ftp'];
	$plan_price = $row['price'];
	if ($row['available'] <= 0)
		$err[]=$plan_planname.'主机已缺货';
	if ($row['hidden'] == 1)
		$err[]=$plan_planname.'为隐藏方案, 无法购买';
	if ( $frequency < $plan_cycle ){
		if ( $plan_cycle == 1 )
			$err[]=$plan_planname.'最少付款周期为一个月';
		if ( $plan_cycle == 3 )
			$err[]=$plan_planname.'最少付款周期为一季度';
		if ( $plan_cycle == 6 )
			$err[]=$plan_planname.'最少付款周期为半年';
		if ( $plan_cycle == 12 )
			$err[]=$plan_planname.'最少付款周期为一年';
		if ( $plan_cycle == 24 )
			$err[]=$plan_planname.'最少付款周期为两年';
	}
	$dateto = date('Y-m-d',strtotime("+".$frequency." month"));
	$price = $plan_price * $frequency;
	// 优惠码部分
	if (!count($err) && !empty($_POST['promotionCode']))
	{
		$promotionCode = $_POST['promotionCode'];
		switch ($promotionCode) {
			case '买两年送一年':
				if ($frequency != 24) {
					$err[] = '买两年送一年的优惠仅适用于两年的付款周期';
				} else {
					$dateto = date('Y-m-d',strtotime("+".($frequency+12)." month"));
				}
				break;
			case '买一年送一个月':
				if ($frequency != 12) {
					$err[] = '买一年送一个月的优惠仅适用于一年的付款周期';
				} else {
					$dateto = date('Y-m-d',strtotime("+".($frequency+1)." month"));
				}
				break;
			case '满两百打九折':
				if ($price < 200) {
					$err[] = '满两百打九折仅适用于总价大于200元';
				} else {
					$price = $price*0.9;
				}
				break;
			case '全场八折':
				$plan_price = $plan_price * 0.8;
				$price = $plan_price * $frequency;
				break;
			case '满三百优惠五十':
				if ($price < 300) {
					$err[] = '满三百优惠五十仅适用于总价大于300元';
				} else {
					$price = $price - 50;
				}
				break;
			default:
				$err[] = '您输入的优惠码无效!';
				break;
		}
	}
	if(!count($err))
	{
		mysql_query("	INSERT INTO users_billing(UserID,type,date,datefrom,dateto,description,amount,paid)
						VALUES(
							".$_SESSION['UserID'].",
							1,
							NOW(),
							NOW(),
							'".$dateto."',
							'".$plan_planname." - 付款周期".$frequency."个月 - ".$price."元',
							".$price.",
							0
						)");
		$orderID = mysql_insert_id();
		mysql_query("	INSERT INTO vhost(owner,orderID,domain,status,plan,planname,cycle,duedate,ip,nodes,location,backup,space,webtraffic,db,subdomain,addon,ftp,price)
						VALUES(
							".$_SESSION['UserID'].",
							".$orderID.",
							'Unallocated',
							'Unpaid',
							".$plantag.",
							'".$plan_planname."',
							".$frequency.",
							'".$dateto."',
							'Unallocated',
							'$plan_nodes',
							'Unallocated',
							".$plan_backup.",
							".$plan_space.",
							".$plan_webtraffic.",
							".$plan_database.",
							".$plan_subdomain.",
							".$plan_addon.",
							".$plan_ftp.",
							".$plan_price."
						)");
			$vhostID = mysql_insert_id();
			$_SESSION['msg']['alert-success']='下单成功，您的虚拟主机将在付款后立即开通。';
			header("Location: account_invoice.php?id=".$orderID);
			exit; 
	}
	SetErrAlert($err);
}
?>
<div id="page">
<p class='breadcrumb'><a href='vhost.php'><?php echo $PanelName;?></a> &raquo; <strong>添加一个虚拟主机</strong></p>
<?php EchoAlert(); ?>
<form id="signup" action="" method="post" onsubmit="">
<?php
	if(@$_SESSION['msg']['vhost-err'])
	{
		echo '<p><div class="alert orange"> '.$_SESSION['msg']['vhost-err'].'</div></p>';		
		unset($_SESSION['msg']['vhost-err']);
	}
?>
<table>
<tr>
	<td>
		<label for="plan" class="signupLabel">选择你的方案</label><br>
<table border="0" cellspacing="0" cellpadding="0" width="880">
	<tr>
		<td>
			<table border="0" cellspacing="0" cellpadding="4" class="signupPlanTable" width="100%">
				<tr>
					<th>&nbsp;</th>
					<th>方案名称<img src="images/51.png" title="<?php echo $PanelName; ?>提供不同虚拟主机解决方案" align="absmiddle"></th>
					<th>容量<img src="images/51.png" title="空间大小" align="absmiddle"></th>
					<th>流量<img src="images/51.png" title="每月流量" align="absmiddle"></th>
					<th>FTP<img src="images/51.png" title="可创建的FTP帐户数量" align="absmiddle"></th>
					<th>数据库<img src="images/51.png" title="可创建的数据库数量" align="absmiddle"></th>
					<th>子域<img src="images/51.png" title="可绑定的子域数量" align="absmiddle"></th>
					<th>附加域<img src="images/51.png" title="可绑定的附加域数量" align="absmiddle"></th>
					<?php /*<th>备份<img src="images/51.png" title="异地备份服务" align="absmiddle"></th>*/ ?>
					<?php /*<th>IPv6<img src="images/51.png" title="IPv6支持" align="absmiddle"></th>*/ ?>
					<th>月付价格</th>
					<th>季付价格</th>
					<th>半年付价格</th>
					<th>年付价格</th>
				</tr>
<?php
$result = mysql_query("select * FROM vhost_plan WHERE hidden=0 ORDER BY `sort` ASC ");
while($row = mysql_fetch_array($result)) {
	if ("hr" == $row['planname'])
	{
		echo '<tr><td></td></tr>';
		continue;
	}
?>
					<tr>
						<td align="center">
						<?php if ($row['available'] < 1) {
							echo '&nbsp;';
						} else {  ?>
						<input type="radio" name="PlanTag" value="<?php echo $row['ID']; ?>" id="vhost<?php echo $row['ID']; ?>" <?php
						if (@$_POST['PlanTag'] == $row['ID'])
							echo 'checked="checked"';
						elseif ($row['available'] < 1)
							echo 'disabled="disabled"';
						elseif ($row['checked'] != 0)
							echo 'checked="checked"';
						?>>
						<?php } ?>
						</td>
						<td><label for="vhost<?php echo $row['ID']; ?>" class="signupPlanName"><?php echo $row['planname']; ?></label></td>
						<td><?php 
						if ($row['space'] == 0 )
							echo '不限';
						elseif ($row['space'] / 1000 >= 1)
							echo $row['space'] / 1000 . 'G';
						else
							echo $row['space'] . 'M'; 
						?></td>
						<td><?php
						if ($row['webtraffic'] == 0 )
							echo '不限';
						else
							echo $row['webtraffic'] . 'G' ; 
						?></td>
						<td><?php 
						if ($row['ftp'] == -1 )
							echo '不限';
						else
							echo $row['ftp'] . '个'; 
						?></td>
						<td><?php 
						if ($row['db'] == -1 )
							echo '不限';
						else
							echo $row['db'] . '个'; 
						?></td>
						<td><?php 
						if ($row['subdomain'] == -1 )
							echo '不限';
						/* elseif ($row['subdomain'] == 0)
							echo '无'; */
						else
							echo $row['subdomain'] . '个'; 
						?></td>
						<td><?php 
						if ($row['addon'] == -1 )
							echo '不限';
						/* elseif ($row['addon'] == 0)
							echo '无'; */
						else
							echo $row['addon'] . '个'; 
						?></td>
						<?php /* IPV6 <td><img src="images/tick_16.png" /></td> */ ?>
						<td id="signupPlanPrice"><label for="pterm_1"><?php 
						if ( $row['cycle'] <= 1) 
							echo ($row['price']); // . '<img src="images/37.png" width="18" />';
						else
							// echo '<img src="images/delete_16.png"> 不支持';
						?></label></td>
						<td id="signupPlanPrice"><label for="pterm_3"><?php 
						if ( $row['cycle'] <= 3) 
							echo ($row['price']*3); // . '<img src="images/37.png" width="18" />';
						else
							// echo '<img src="images/delete_16.png"> 不支持';
						?></label></td>
						<td id="signupPlanPrice"><label for="pterm_6"><?php 
						if ( $row['cycle'] <= 6) 
							echo ($row['price']*6); // . '<img src="images/37.png" width="18" />';
						else
							// echo '<img src="images/delete_16.png"> 不支持';
						?></label></td>
						<td id="signupPlanPrice"><label for="pterm_12"><?php 
						if ( $row['cycle'] <= 12) 
							echo ($row['price']*12); // . '<img src="images/37.png" width="18" />';
						else
							// echo '<img src="images/delete_16.png"> 不支持';
						?></label></td>
					</tr>
<?
}
?>
			</table>
		</td>
	</tr>
</table>
	</td>
</tr>
<tr>
	<td colspan="2">
		<br>
		<label for="Frequency" class="signupLabel">付款周期</label><br>
		<input type="radio" name="Frequency" value="1" id="pterm_1"><label class="signupText" for="pterm_1">一个月</label>&nbsp;&nbsp;&nbsp;
		<input type="radio" name="Frequency" value="3" id="pterm_3"><label class="signupText" for="pterm_3">一季度</label>&nbsp;&nbsp;&nbsp;
		<input type="radio" name="Frequency" value="6" id="pterm_6"><label class="signupText" for="pterm_6">半年</label>&nbsp;&nbsp;&nbsp;
		<input type="radio" name="Frequency" value="12" id="pterm_12" checked="checked"><label class="signupText" for="pterm_12">一年</label>&nbsp;&nbsp;&nbsp;
		<input type="radio" name="Frequency" value="24" id="pterm_24"><label class="signupText" for="pterm_24">两年<br></label>
	</td>
</tr>
<tr>
	<td colspan="2">
		<br>
		<label for="promotionCode" class="signupLabel">优惠码</label> <span class="signup_note">可选</span><br>
		<input name="promotionCode" id="promotionCode" type="text" size="50"  border="0"  />
	</td>
</tr>
<tr>
	<td colspan="2" align="center">
		<br><br>
		<input type="submit" name="Next" value="继续 &rarr;" class="signupSubmit">
	</td>
</tr>
</table>
<input type="hidden" name="action" value="order" />
</form>
</div>
<?php @include_once("footer.php") ?>