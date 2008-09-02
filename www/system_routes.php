#!/usr/local/bin/php
<?php
/*
	system_routes.php
	part of m0n0wall (http://m0n0.ch/wall)
	
	Copyright (C) 2003-2005 Manuel Kasper <mk@neon1.net>.
	All rights reserved.
	
	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:
	
	1. Redistributions of source code must retain the above copyright notice,
	   this list of conditions and the following disclaimer.
	
	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.
	
	THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
	INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
	AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
	AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
	OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
	SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
	INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
	CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
	ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
	POSSIBILITY OF SUCH DAMAGE.
*/
require("guiconfig.inc");

$pgtitle = array(gettext("System"), gettext("Static routes"));

if (!is_array($config['staticroutes']['route']))
	$config['staticroutes']['route'] = array();

array_sort_key($config['staticroutes']['route'], "network");

$a_routes = &$config['staticroutes']['route'];

if ($_POST) {
	$pconfig = $_POST;

	if ($_POST['apply']) {
		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			$retval |= ui_process_updatenotification("routes", "routes_process_updatenotification");
			$retval |= rc_update_service("routing");
		}
		$savemsg = get_std_save_message($retval);
		if ($retval == 0) {
			ui_cleanup_updatenotification("routes");
		}
	}
}

if ($_GET['act'] === "del") {
	if ($a_routes[$_GET['id']]) {
		ui_set_updatenotification("routes", UPDATENOTIFICATION_MODE_DIRTY, $a_routes[$_GET['id']]['uuid']);
		header("Location: system_routes.php");
		exit;
	}
}

function routes_process_updatenotification($mode, $data) {
	global $config;

	$retval = 0;

	switch ($mode) {
		case UPDATENOTIFICATION_MODE_NEW:
		case UPDATENOTIFICATION_MODE_MODIFIED:
			break;
		case UPDATENOTIFICATION_MODE_DIRTY:
			if (is_array($config['staticroutes']['route'])) {
				$index = array_search_ex($data, $config['staticroutes']['route'], "uuid");
				if (false !== $index) {
					unset($config['staticroutes']['route'][$index]);
					write_config();
				}
			}
			break;
	}

	return $retval;
}
?>
<?php include("fbegin.inc"); ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td class="tabcont">
			<form action="system_routes.php" method="post">
				<?php if ($savemsg) print_info_box($savemsg); ?>
				<?php if (ui_exists_updatenotification("routes")) print_config_change_box();?>
				<table width="100%" border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td width="15%" class="listhdrr"><?=gettext("Interface");?></td>
						<td width="25%" class="listhdrr"><?=gettext("Network");?></td>
						<td width="20%" class="listhdrr"><?=gettext("Gateway");?></td>
						<td width="30%" class="listhdr"><?=gettext("Description");?></td>
						<td width="10%" class="list"></td>
					</tr>
					<?php $i = 0; foreach ($a_routes as $route):?>
					<?php $notificationmode = ui_get_updatenotification_mode("routes", $route['uuid']);?>
					<tr>
						<td class="listlr">
							<?php
					  	$iflabels = array('lan' => 'LAN', 'wan' => 'WAN', 'pptp' => 'PPTP');
					  	for ($j = 1; isset($config['interfaces']['opt' . $j]); $j++)
					  	$iflabels['opt' . $j] = $config['interfaces']['opt' . $j]['descr'];
					  	echo htmlspecialchars($iflabels[$route['interface']]);?>
						</td>
	          <td class="listr"><?=strtolower($route['network']);?></td>
	          <td class="listr"><?=strtolower($route['gateway']);?></td>
	          <td class="listbg"><?=htmlspecialchars($route['descr']);?>&nbsp;</td>
	          <?php if (UPDATENOTIFICATION_MODE_DIRTY != $notificationmode):?>
	          <td valign="middle" nowrap class="list">
							<a href="system_routes_edit.php?id=<?=$i;?>"><img src="e.gif" title="<?=gettext("Edit Route");?>" width="17" height="17" border="0"></a>
	          	<a href="system_routes.php?act=del&id=<?=$i;?>" onclick="return confirm('<?=gettext("Do you really want to delete this route?");?>')"><img src="x.gif" title="<?=gettext("Delete Route");?>" width="17" height="17" border="0"></a>
						</td>
						<?php else:?>
						<td valign="middle" nowrap class="list">
							<img src="del.gif" border="0">
						</td>
						<?php endif;?>
					</tr>
				  <?php $i++; endforeach;?>
					<tr> 
						<td class="list" colspan="4"></td>
						<td class="list">
							<a href="system_routes_edit.php"><img src="plus.gif" title="<?=gettext("Add Route");?>" width="17" height="17" border="0"></a>
						</td>
					</tr>
				</table>
      </form>
		</td>
	</tr>
</table>
<?php include("fend.inc");?>
