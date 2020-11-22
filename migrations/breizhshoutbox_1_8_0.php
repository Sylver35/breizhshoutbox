<?php
/**
 *
 * @package Breizh Shoutbox Extension
 * @copyright (c) 2018-2020 Sylver35  https://breizhcode.com
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace sylver35\breizhshoutbox\migrations;

use phpbb\db\migration\migration;

class breizhshoutbox_1_8_0 extends migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['shout_version'], '1.8.0', '>=');
	}

	static public function depends_on()
	{
		return ['\sylver35\breizhshoutbox\migrations\breizhshoutbox_1_7_0'];
	}

	public function update_data()
	{
		return [
			// Version of extension
			['config.update', ['shout_version', '1.8.0']],

			// Config add
			['config.add', ['shout_defil_pop', 1]],
			['config.add', ['shout_defil_priv', 1]],
			['config.add', ['shout_div_img', 'discus.png']],
			['config.add', ['shout_div_img_pop', '']],
			['config.add', ['shout_div_img_priv', 'stamp-secret.png']],
			['config.add', ['shout_img_horizontal', 'right']],
			['config.add', ['shout_img_horizontal_pop', '']],
			['config.add', ['shout_img_horizontal_priv', 'right']],
			['config.add', ['shout_img_vertical', 'bottom']],
			['config.add', ['shout_img_vertical_pop', '']],
			['config.add', ['shout_img_vertical_priv', 'center']],
			['config.add', ['shout_num', $this->config['shout_non_ie_nr']]],
			['config.add', ['shout_num_pop', $this->config['shout_non_ie_nr_pop']]],
			['config.add', ['shout_num_priv', $this->config['shout_non_ie_nr_priv']]],
			['config.add', ['shout_height_pop', $this->config['shout_non_ie_height_pop']]],
			['config.add', ['shout_height_priv', $this->config['shout_non_ie_height_priv']]],
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

			// Config remove
			['config.remove', ['shout_color_background_sub']],
			['config.remove', ['shout_forum']],
			['config.remove', ['shout_ie_nr']],
			['config.remove', ['shout_ie_nr_pop']],
			['config.remove', ['shout_ie_nr_priv']],
			['config.remove', ['shout_index']],
			['config.remove', ['shout_non_ie_height_pop']],
			['config.remove', ['shout_non_ie_height_priv']],
			['config.remove', ['shout_non_ie_nr_pop']],
			['config.remove', ['shout_non_ie_nr_priv']],
			['config.remove', ['shout_pagin_option']],
			['config.remove', ['shout_pagin_option_pop']],
			['config.remove', ['shout_pagin_option_priv']],
			['config.remove', ['shout_topic']],
			['config.remove', ['shout_width_post_sub']],

			// Permission remove
			['permission.remove', ['m_shout_purge', true]],

			['custom', [
				[&$this, 'update_user_shoutbox']
			]],
		];
	}

	public function update_schema()
	{
		return [
			'change_columns'	=> [
				$this->table_prefix . 'users'	=> [
					'user_shout'	=> ['VCHAR:255', '{"user":2,"new":"N","new_priv":"N","error":"N","del":"N","add":"N","edit":"N","index":3,"forum":3,"topic":3}'],
					'user_shoutbox'	=> ['VCHAR:255', '{"bar":2,"bar_pop":2,"bar_priv":2,"defil":2,"defil_pop":2,"defil_priv":2,"panel":2,"panel_float":2,"dateformat":""}'],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'change_columns'	=> [
				$this->table_prefix . 'users'	=> [
					'user_shout'	=> ['VCHAR:255', '{"user":2,"new":0,"new_priv":0,"error":0,"del":0,"add":0,"edit":0,"index":3,"forum":3,"topic":3}'],
					'user_shoutbox'	=> ['VCHAR:255', '{"bar":"N","pagin":"N","bar_pop":"N","pagin_pop":"N","bar_priv":"N","pagin_priv":"N","defil":"N","panel":"N","dateformat":""}'],
				],
			],
		];
	}

	public function update_user_shoutbox()
	{
		$user_shout = '{"user":2,"new":"N","new_priv":"N","error":"N","del":"N","add":"N","edit":"N","index":3,"forum":3,"topic":3}';
		$user_shoutbox = '{"bar":2,"bar_pop":2,"bar_priv":2,"defil":2,"defil_pop":2,"defil_priv":2,"panel":2,"panel_float":2,"dateformat":""}';
		$sql = 'UPDATE ' . $this->table_prefix . "users
			SET user_shout = '" . $this->db->sql_escape($user_shout) . "', user_shoutbox = '" . $this->db->sql_escape($user_shoutbox) . "'
				WHERE user_id > 0";
		$this->db->sql_query($sql);
	}
}
