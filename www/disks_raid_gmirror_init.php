#!/usr/local/bin/php
<?php
/*
	disks_raid_gmirror_init.php
	
	part of FreeNAS (http://www.freenas.org)
	Copyright (C) 2005-2006 Olivier Cochard-Labb� <olivier@freenas.org>.
	All rights reserved.
	
	Based on m0n0wall (http://m0n0.ch/wall)
	Copyright (C) 2003-2006 Manuel Kasper <mk@neon1.net>.
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

$pgtitle = array(_DISKSPHP_NAME,"Geom mirror", _DISKSRAIDEDITPHP_NAMEDESC);

if ($_POST) {
	unset($input_errors);
	unset($do_format);

	/* input validation */
	$reqdfields = explode(" ", "disk");
	$reqdfieldsn = explode(",", "Disk");
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, &$input_errors);

	
	if (!$input_errors)
	{
		$do_format = true;
		$disk = $_POST['disk'];
	}
}
if (!isset($do_format))
{
	$do_format = false;
	$disk = '';
}

?>
<?php include("fbegin.inc"); ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
 <tr><td class="tabnavtbl">
  <ul id="tabnav">
	<li class="tabact"><?=_DISKSRAIDPHP_GMIRROR; ?></li>
	<li class="tabinact"><a href="disks_raid_gvinum.php"><?=_DISKSRAIDPHP_GVINUM; ?><?=_DISKSRAIDPHP_UNSTABLE ;?> 
  </ul>
  </td></tr>
  <tr><td class="tabnavtbl">
  <ul id="tabnav">
	<li class="tabinact"><a href="disks_raid_gmirror.php"><?=_DISKSRAIDPHP_MANAGE; ?></a></li>
	<li class="tabact"><?=_DISKSRAIDPHP_FORMAT; ?></li>
	<li class="tabinact"><a href="disks_raid_gmirror_tools.php"><?=_DISKSRAIDPHP_TOOLS; ?></a></li>
	<li class="tabinact"><a href="disks_raid_gmirror_info.php"><?=_DISKSRAIDPHP_INFO; ?></a></li>
  </ul>
  </td></tr>
  <tr> 
    <td class="tabcont">
<?php if ($input_errors) print_input_errors($input_errors); ?>
			<form action="disks_raid_gmirror_init.php" method="post" name="iform" id="iform">
			  <table width="100%" border="0" cellpadding="6" cellspacing="0">
                <tr>
				  <td width="22%" valign="top" class="vncellreq"><?=_DISKSRAIDPHP_VOLUME;?></td>
				  <td width="78%" class="vtable"> 
                    <?=$mandfldhtml;?><input name="disk" type="text" class="formfld" id="disk" size="20" value="<?=htmlspecialchars($disk);?>"></td>
				</tr>
				<tr>
				  <td width="22%" valign="top">&nbsp;</td>
				  <td width="78%"> 
                    <input name="Submit" type="submit" class="formbtn" value="<?=_DISKSRAIDINITPHP_FORMAT;?>">
				</td>
				</tr>
				<tr>
				<td valign="top" colspan="2">
				<? if ($do_format)
				{
					echo("<strong>" . _DISKSRAIDINITPHP_INFO . "</strong><br>");
					echo('<pre>');
					ob_end_flush();
					
					/* Create filesystem */
					system("/sbin/newfs -U /dev/mirror/" . escapeshellarg($disk));
										
					echo('</pre>');
				}
				?>
				</td>
				</tr>
			</table>
</form>
<p><span class="vexpl"><span class="red"><strong><?=_WARNING;?>:<br>
                </strong></span><?=_DISKSRAIDINITPHP_TEXT;?>
                </span></p>
</td></tr></table>
<?php include("fend.inc"); ?>
