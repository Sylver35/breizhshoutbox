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

class breizhshoutbox_1_7_0 extends migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['shout_version'], '1.7.0', '>=');
	}

	static public function depends_on()
	{
		return array('\sylver35\breizhshoutbox\migrations\breizhshoutbox_1_6_0');
	}

	public function update_data()
	{
		return array(
			// Version of extension
			array('config.update', array('shout_version', '1.7.0')),

			// Config
			array('config.add', array('shout_ext_category', 1)),

			// Change img to '.webp'
			array('config.update', array('shout_avatar_img', str_replace(array('.gif', '.png', '.jpg'), '.webp', $this->config['shout_avatar_img']))),
			array('config.update', array('shout_avatar_img_robot', str_replace(array('.gif', '.png', '.jpg'), '.webp', $this->config['shout_avatar_img_robot']))),
			array('config.update', array('shout_panel_img', str_replace(array('.gif', '.png', '.jpg'), '.webp', $this->config['shout_panel_img']))),
			array('config.update', array('shout_panel_exit_img', str_replace(array('.gif', '.png', '.jpg'), '.webp', $this->config['shout_panel_exit_img']))),
		);
	}
}
