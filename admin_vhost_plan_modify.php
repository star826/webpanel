<?php
$adminpage = "admin_vhost_plan_modify.php";
@include_once("header.php");
@include_once("function.php");
@include_once("alert_function.php");
@include_once("account_function.php");
if (@$_POST['action'] == 'vhost_plan_modify' || @$_POST['action'] == 'vhost_plan_add')
{
	$plan_sort = @$_POST['sort']; //排序
	$plan_checked = @$_POST['checked']; // 默认选中
	$plan_hidden = @$_POST['hidden']; // 隐藏
	$plan_available = @$_POST['available']; // 可用
	$plan_name = @$_POST['planname']; // 方案名
	$plan_cycle = @$_POST['cycle']; // 最低付款周期
	$plan_nodes = @$_POST['nodes']; // 区域
	$plan_backup = @$_POST['backup']; // 备份周期
	$plan_space = @$_POST['space']; // 空间大小
	$plan_webtraffic = @$_POST['webtraffic']; // 流量大小
	$plan_db = @$_POST['db_max']; // 数据库
	$plan_subdomain = @$_POST['subdomain_max']; // 子域名数
	$plan_addon = @$_POST['addon_max']; // 附加域数
	$plan_ftp = @$_POST['ftp_max']; // FTP数
	$plan_price = @$_POST['price']; // 一个月价格
	$plan_planid = @$_POST['planid']; // 虚拟主机方案ID
	$err = array();
	if(!count($err))
	{
		if(@$_POST['action'] == 'vhost_plan_modify'){
			if(mysql_query("UPDATE vhost_plan SET 
			sort=$plan_sort,
			checked=$plan_checked,
			hidden=$plan_hidden,
			planname='$plan_name',
			cycle=$plan_cycle,
			nodes='$plan_nodes',
			backup=$plan_backup,
			space=$plan_space,
			webtraffic=$plan_webtraffic,
			db=$plan_db,
			subdomain=$plan_subdomain,
			addon=$plan_addon,
			ftp=$plan_ftp,
			price=$plan_price,
			available=$plan_available
			WHERE ID=".$plan_planid))
			{
				$_SESSION['msg']['alert-success']='修改虚拟主机方案成功';
				header("Location: admin_vhost_plans.php");
				exit();
			} else {
				$err[]='修改虚拟主机方案失败, 请检查输入的参数!';
			}
		} elseif (@$_POST['action'] == 'vhost_plan_add') {
			if(mysql_query("INSERT INTO vhost_plan(sort,checked,hidden,planname,cycle,nodes,backup,space,webtraffic,db,subdomain,addon,ftp,price,available) VALUES (
			$plan_sort,
			$plan_checked,
			$plan_hidden,
			'$plan_name',
			$plan_cycle,
			'$plan_nodes',
			$plan_backup,
			$plan_space,
			$plan_webtraffic,
			$plan_db,
			$plan_subdomain,
			$plan_addon,
			$plan_ftp,
			$plan_price,
			$plan_available
			)"))
			{
				$_SESSION['msg']['alert-success']='创建虚拟主机方案成功';
				header("Location: admin_vhost_plans.php");
				exit();
			} else {
				$err[]='创建虚拟主机方案失败, 请检查输入的参数!';
			}
		}
	}
	SetErrAlert($err);
} elseif (@$_POST['action'] == 'vhost_plan_delete') {
$plan_planid = @$_POST['planid']; // 虚拟主机方案ID
	if(mysql_query("DELETE FROM vhost_plan WHERE ID=".$plan_planid))
	{
		$_SESSION['msg']['alert-success']='删除虚拟主机方案成功';
		header("Location: admin_vhost_plans.php");
		exit();
	} else {
		$err[]='删除虚拟主机方案失败!';
		SetErrAlert($err);
	}
}
if ($_GET['action'] == 'modify')
{
	$planid = empty($_GET['planid'])?@$_POST['planid']:@$_GET['planid'];
	if (!is_numeric($planid)) ForceDie();	
	$result_plans = mysql_query("select * FROM vhost_plan WHERE ID=" . $planid);
	if (@mysql_num_rows($result_plans) != 1) ForceDie();
	$row_plan = mysql_fetch_array($result_plans);
} elseif ($_GET['action'] == 'new') {

} else {
	ForceDie();
}
?>
<div id="page">
<?php EchoAlert(); ?>
<form name="config_save" id="config_save" action="" method="post" onsubmit="document.getElementById('confirmed').disabled = true;">
<table class="list">
	<tr>
		<th colspan="3">虚拟主机方案编辑</th>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">排序</td>
		<td>
		<input name="sort" type="number" value="<?php echo (@$row_plan['sort']); ?>" maxlength="" autocomplete="off" required style="width:100px;" />
		</td>
		<td class="hint">在虚拟主机选择方案列表中的排序</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">默认选中</td>
		<td>
		<input type="radio" name="checked" value="1" <?php echo @$row_plan['checked']==1?'checked="checked"':'';?>> 是
		<input type="radio" name="checked" value="0" <?php echo @$row_plan['checked']==1?'':'checked="checked"';?>> 否
		</td>
		<td class="hint">在虚拟主机选择方案列表中选中, 只能有一个虚拟主机方案被选中.</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">隐藏方案</td>
		<td>
		<input type="radio" name="hidden" value="1" <?php echo @$row_plan['hidden']==1?'checked="checked"':'';?>> 是
		<input type="radio" name="hidden" value="0" <?php echo @$row_plan['hidden']==1?'':'checked="checked"';?>> 否
		</td>
		<td class="hint">在虚拟主机选择方案列表中隐藏, 无法可见且选择与购买.</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">方案有货</td>
		<td>
		<input type="radio" name="available" value="1" <?php echo @$row_plan['available']==1?'checked="checked"':'';?>> 是
		<input type="radio" name="available" value="0" <?php echo @$row_plan['available']==1?'':'checked="checked"';?>> 否
		</td>
		<td class="hint">方案无货时无法选择, 但可见</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">方案名</td>
		<td>
			<input name="planname" id="planname" type="text" value="<?php echo @($row_plan['planname']); ?>" maxlength="" size="18" autocomplete="off" required />
		</td>
		<td class="hint">输入hr则插入换行符, 用于分割多组不同方案</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">最低付款周期(月)</td>
		<td>
			<input name="cycle" id="cycle" type="text" value="<?php echo @($row_plan['cycle']); ?>" maxlength="" size="18" autocomplete="off" required />
		</td>
		<td class="hint">最低付款的周期, 例如12个月, 6个月.</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">可选节点</td>
		<td>
			<input name="nodes" id="nodes" type="text" value="<?php echo @($row_plan['nodes']); ?>" maxlength="" size="18" autocomplete="off" required />
		</td>
		<td class="hint">输入可使用的节点别名, 以 <span style="color:red;font-weight:bold">,</span> 分隔.</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">备份周期</td>
		<td>
			<input name="backup" id="backup" type="text" value="<?php echo @($row_plan['backup']); ?>" maxlength="" size="18" autocomplete="off" required />
		</td>
		<td class="hint">自动备份的备份周期</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">空间大小 (MB)</td>
		<td>
		<input name="space" id="space" type="text" value="<?php echo @$row_plan['space']; ?>" maxlength="" size="18" autocomplete="off" required />
		</td>
		<td class="hint">输入 -1 则为无限</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">流量大小 (GB)</td>
		<td>
			<input name="webtraffic" id="webtraffic" type="text" value="<?php echo @$row_plan['webtraffic']; ?>" maxlength="" size="18" autocomplete="off" required />
		</td>
		<td class="hint">输入 -1 则为无限</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">MySQL (个)</td>
		<td>
			<input name="db_max" id="db_max" type="text" value="<?php echo @($row_plan['db']); ?>" maxlength="" size="18" autocomplete="off" required />
		</td>
		<td class="hint">输入 -1 则为无限</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">子域绑定 (个)</td>
		<td>
			<input name="subdomain_max" id="subdomain_max" type="text" value="<?php echo @($row_plan['subdomain']); ?>" maxlength="" size="18" autocomplete="off" required />
		</td>
		<td class="hint">输入 -1 则为无限</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">附加域绑定 (个)</td>
		<td>
			<input name="addon_max" id="addon_max" type="text" value="<?php echo @($row_plan['addon']); ?>" maxlength="" size="18" autocomplete="off" required />
		</td>
		<td class="hint">输入 -1 则为无限</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">FTP (个)</td>
		<td>
			<input name="ftp_max" id="ftp_max" type="text" value="<?php echo @($row_plan['ftp']); ?>" maxlength="" size="18" autocomplete="off" required />
		</td>
		<td class="hint">输入 -1 则为无限</td>
	</tr>
	<tr class="list_entry">
		<td class="table_form_header">周期价格(一个月价格)</td>
		<td>
			<input name="price" id="price" type="text" value="<?php echo @money($row_plan['price']); ?>" maxlength="" size="18" autocomplete="off" required />
		</td>
		<td class="hint">总价 = 付款周期 * 周期价格</td>
	</tr>
	<tr class="list_entry">
		<td></td>
		<td><input tabindex="3" class="button" type="submit" name="confirmed" id="confirmed" value="确认"></td>
		<td class="hint"></td>
	</tr>
</table>
<?php 
if ($_GET['action'] == 'modify')
{
?>
<input type="hidden" name="planid" value="<?php echo $_GET['planid']; ?>" />
<input type="hidden" name="action" value="vhost_plan_modify" />
<?php
} elseif ($_GET['action'] == 'new') {
?>
<input type="hidden" name="action" value="vhost_plan_add" />
<?php } ?>
</form>
<hr />
<form name="config_save" id="config_save" action="" method="post" onsubmit="return confirm('确认是否删除该主机方案? 删除主机方案并不会改变或停用使用此方案的虚拟主机. ');">
<table class="list">
	<tr class="list_entry">
		<td width="200"></td>
		<td colspan="3">
			<input type="hidden" name="planid" value="<?php echo $_GET['planid']; ?>" />
			<input type="hidden" name="action" value="vhost_plan_delete" />
			<input class="button" id="button_save" type="submit" value="删除该主机方案">
		</td>
	</tr>
</table>
</form>
</div>
<?php @include_once("footer.php") ?>