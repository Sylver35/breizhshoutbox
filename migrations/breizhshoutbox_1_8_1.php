<?php
/**
 *
 * @package Breizh Shoutbox Extension
 * @copyright (c) 2018-2021 Sylver35  https://breizhcode.com
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace sylver35\breizhshoutbox\migrations;

use phpbb\db\migration\migration;

class breizhshoutbox_1_8_1 extends migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['shout_version'], '1.8.1', '>=');
	}

	static public function depends_on()
	{
		return ['\sylver35\breizhshoutbox\migrations\breizhshoutbox_1_8_0'];
	}

	public function update_data()
	{
		return [
			// Version of extension
			['config.update', ['shout_version', '1.8.1']],

			// Config add
			['config.add', ['shout_tpl_action', '<a onclick="shoutbox.actionUser(%1$s);" title="%2$s" class="username-coloured action-user">%3$s</a>']],
			['config.add', ['shout_tpl_cite', '<span style="color:#%4$s;font-weight:bold;">%1$s </span> %2$s :: %3$s']],
			['config.add', ['shout_tpl_url', '<a class="action-user" href="%1$s" title="%3$s">%2$s</a>']],
			['config.add', ['shout_tpl_italic', '<span class="shout-italic" style="color:#%2$s">%1$s</span>']],
			['config.add', ['shout_tpl_bold', '<span class="shout-bold">']],
			['config.add', ['shout_tpl_close', '</span>']],
			['config.add', ['shout_tpl_colorbot', '[color=#%2$s][i]%1$s[/i][/color]']],
			['config.add', ['shout_tpl_personal', 'onclick="shoutbox.personalMsg();" title="%1$s"><span title="">%1$s']],
			['config.add', ['shout_tpl_citemsg', 'onclick="shoutbox.citeMsg();" title="%1$s"><span title="">%2$s']],
			['config.add', ['shout_tpl_citemulti', 'onclick="shoutbox.citeMultiMsg(\'%1$s\', \'%2$s\', true);" title="%3$s"><span title="">%4$s']],
			['config.add', ['shout_tpl_perso', 'onclick="shoutbox.changePerso(%1$s);" title="%2$s"><span title="">%2$s']],
			['config.add', ['shout_tpl_robot', 'onclick="shoutbox.robotMsg(%1$s);" title="%2$s"><span title="">%3$s']],
			['config.add', ['shout_tpl_auth', 'onclick="shoutbox.runAuth(%1$s, \'%2$s\');" title="%3$s"><span title="">%3$s']],
			['config.add', ['shout_tpl_prefs', 'onclick="shoutbox.shoutPopup(\'%1$s\', \'850\', \'500\', \'_popup\');" title="%2$s"><span title="">%2$s']],
			['config.add', ['shout_tpl_delreqto', 'onclick="if(confirm(\'%2$s\'))shoutbox.delReqTo(%1$s);" title="%3$s"><span title="">%3$s']],
			['config.add', ['shout_tpl_delreq', 'onclick="if(confirm(\'%2$s\'))shoutbox.delReq(%1$s);" title="%3$s"><span title="">%3$s']],
			['config.add', ['shout_tpl_remove', 'onclick="if(confirm(\'%2$s\'))shoutbox.removeMsg(%1$s);" title="%3$s"><span title="">%3$s']],
			['config.add', ['shout_tpl_profile', '%1$s" title="%2$s"><span title="">%2$s']],
			['config.add', ['shout_tpl_admin', '%1$s" title="%2$s"><span title="">%2$s']],
			['config.add', ['shout_tpl_modo', '%1$s" title="%2$s"><span title="">%2$s']],
			['config.add', ['shout_tpl_ban', '%1$s" title="%2$s"><span title="">%2$s']],

			// Add user permissions
			['permission.add', ['u_shout_bbcode_custom', true]],

			// Set permissions users
			['permission.permission_set', ['ADMINISTRATORS', 'u_shout_bbcode_custom', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'u_shout_bbcode_custom', 'group']],
			['permission.permission_set', ['REGISTERED', 'u_shout_bbcode_custom', 'group']],
		];
	}
}
