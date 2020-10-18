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
	static public function depends_on()
	{
		return array('\sylver35\breizhshoutbox\migrations\breizhshoutbox_1_7_0');
	}

	public function update_data()
	{
		return array(
			// Version of extension
			array('config.update', array('shout_version', '1.8.0')),

			// Config
			array('config.add', array('shout_defil_pop', 1)),
			array('config.add', array('shout_defil_priv', 1)),

			array('custom', array(
				array(&$this, 'update_user_shoutbox')
			)),
		);
	}

	public function update_user_shoutbox()
	{
		$user_shoutbox = '{"bar":"N","bar_pop":"N","bar_priv":"N","defil":"N","defil_pop":"N","defil_priv":"N","panel":"N","panel_float":"N","dateformat":""}';
		$sql = 'UPDATE ' . $this->table_prefix . "users
			SET user_shoutbox = '" . $this->db->sql_escape($user_shoutbox) . "'
				WHERE user_id > 0";
		$this->db->sql_query($sql);
	}
}
