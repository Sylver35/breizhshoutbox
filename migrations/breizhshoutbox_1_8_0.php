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
		return array('\sylver35\breizhshoutbox\migrations\breizhshoutbox_1_7_0');
	}

	public function update_data()
	{
		return array(
			// Version of extension
			array('config.update', array('shout_version', '1.8.0')),

			// Config add
			array('config.add', array('shout_defil_pop', 1)),
			array('config.add', array('shout_defil_priv', 1)),
			array('config.add', array('shout_num', $this->config['shout_non_ie_nr'])),
			array('config.add', array('shout_num_pop', $this->config['shout_non_ie_nr_pop'])),
			array('config.add', array('shout_num_priv', $this->config['shout_non_ie_nr_priv'])),
			array('config.add', array('shout_height_pop', $this->config['shout_non_ie_height_pop'])),
			array('config.add', array('shout_height_priv', $this->config['shout_non_ie_height_priv'])),

			// Config remove
			array('config.remove', array('shout_color_background_sub')),
			array('config.remove', array('shout_ie_nr')),
			array('config.remove', array('shout_ie_nr_pop')),
			array('config.remove', array('shout_ie_nr_priv')),
			array('config.remove', array('shout_non_ie_nr_pop')),
			array('config.remove', array('shout_non_ie_nr_priv')),
			array('config.remove', array('shout_non_ie_height_pop')),
			array('config.remove', array('shout_non_ie_height_priv')),
			array('config.remove', array('shout_pagin_option')),
			array('config.remove', array('shout_pagin_option_pop')),
			array('config.remove', array('shout_pagin_option_priv')),
			array('config.remove', array('shout_width_post_sub')),

			// Permission remove
			array('permission.remove', array('m_shout_purge', true)),

			array('custom', array(
				array(&$this, 'update_user_shoutbox')
			)),
		);
	}

	public function update_schema()
	{
		return array(
			'change_columns'	=> array(
				$this->table_prefix . 'users'	=> array(
					'user_shout'	=> array('VCHAR:255', '{"user":2,"new":"N","new_priv":"N","error":"N","del":"N","add":"N","edit":"N","index":3,"forum":3,"topic":3}'),
					'user_shoutbox'	=> array('VCHAR:255', '{"bar":2,"bar_pop":2,"bar_priv":2,"defil":2,"defil_pop":2,"defil_priv":2,"panel":2,"panel_float":2,"dateformat":""}'),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'change_columns'	=> array(
				$this->table_prefix . 'users'	=> array(
					'user_shout'	=> array('VCHAR:255', '{"user":2,"new":0,"new_priv":0,"error":0,"del":0,"add":0,"edit":0,"index":3,"forum":3,"topic":3}'),
					'user_shoutbox'	=> array('VCHAR:255', '{"bar":"N","pagin":"N","bar_pop":"N","pagin_pop":"N","bar_priv":"N","pagin_priv":"N","defil":"N","panel":"N","dateformat":""}'),
				),
			),
		);
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
