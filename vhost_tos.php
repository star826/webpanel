<?php
$page = "vhost_tos.php";
@include_once("header.php");
?>
<div id="page">
<p class='breadcrumb'><a href="vhost.php"><?php echo $PanelName; ?></a> &raquo; <a href="vhost.php">虚拟主机</a> &raquo; <strong>服务条款</strong></p>
<h2 align="center">服务条款</h2>
<ul style="color: #333">
	<li><strong>重要须知:</strong></li>
	<ul>
		<li>请您审慎阅读并选择接受或不接受本服务条款. 除非您接受本服务条款的所有条款, 否则您无权购买, 开通, 使用<?php echo $PanelName;?>所提供的虚拟主机. 您的购买, 开通, 使用等行为将视为对本服务条款的接受, 且同意接受本服务条款各项条款的约束. </li>
		<li><?php echo $PanelName;?>保留在没有另行通知的情况下修改<i>服务条款</i>的权利. </li>
	</ul>
	<li><strong>可接受行为:</strong></li>
	<ul>
		<li>您应对任何使用您虚拟主机的行为负责, 并对在使用<?php echo $PanelName;?>过程中任何在您帐户下创建, 存储, 播放, 或传播的数据负责. </li>
		<li>您不得参与任何干扰, 打断<?php echo $PanelName;?>服务或连接到<?php echo $PanelName;?>网络的活动. </li>
	</ul>
	<li><strong>使用禁止:</strong></li>
	<ul>
		<li>任何下列被禁止的活动将致使帐户暂停使用或取消(无退款).</li> 
		<li>故意滥用系统资源, 包括但不限于使用消耗大量网络容量, CPU周期, 或者磁盘IO的程序.</li>
		<li>垃圾邮件和批量不请自来邮件. </li>
		<li>发表, 存储含有受到知识产权法律保护的图像, 相片, 软件或其他资料的文件.</li>
	</ul>
	<li><strong>责任范围:</strong></li>
	<ul>
		<li>除了<?php echo $PanelName;?>的过失, 还有其他很多原因可能造成服务的中断, 并且服务中断所造成的损害难以探明. </li><li>对于此类原因造成的损害, 超出了<?php echo $PanelName;?>的直接和唯一控制范围以外, 因此<?php echo $PanelName;?>概不负责. </li><li>此外, 在任何情况下, <?php echo $PanelName;?>因过失产生的责任均不超过损害发生期间用户应支付的服务费用金额. </li><li>任何情况下, <?php echo $PanelName;?>亦决不对特殊或间接的损害, 损失或伤害负责. </li><li><?php echo $PanelName;?>不对您业务可能遭受的任何损失负责, <?php echo $PanelName;?>不对我们的任何服务作暗示或书面保证. </li><li><?php echo $PanelName;?>拒绝其他任何个别目的的保证, 包括但不限于服务中断而造成的数据损失. 
		</li>
	</ul>
	<li><strong>免责声明:</strong></li>
	<ul>
		<li>您应对使用<?php echo $PanelName; ?>的虚拟主机服务自行承担风险. 由<?php echo $PanelName; ?>提供的所有服务没有任何类型的担保. 
		</li>
	</ul>
</ul>
<form name="vhost_add" id="vhost_add" action="vhost_add.php" method="post">
		<div align="center">
			<input type="submit" name="submit" value="我已阅读服务条款 &raquo;" class="button" style="width: 250px" onclick="top.location='vhost_add.php';return false;">
		</div>
	</form>
</div>
<?php @include_once("footer.php") ?>