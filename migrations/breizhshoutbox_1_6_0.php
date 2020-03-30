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

class breizhshoutbox_1_6_0 extends migration
{
	static public function depends_on()
	{
		return array('\sylver35\breizhshoutbox\migrations\breizhshoutbox_1_5_0');
	}

	public function update_data()
	{
		return array(
			// Version of extension
			array('config.update', array('shout_version', '1.6.0')),
			// Config
			array('config.add', array('shout_see_cite', 1)),

			// Delete ".mp3" in somes config
			array('config.update', array('shout_sound_new', str_replace('.mp3', '', $this->config['shout_sound_new']))),
			array('config.update', array('shout_sound_new_priv', str_replace('.mp3', '', $this->config['shout_sound_new']))),
			array('config.update', array('shout_sound_error', str_replace('.mp3', '', $this->config['shout_sound_error']))),
			array('config.update', array('shout_sound_del', str_replace('.mp3', '', $this->config['shout_sound_del']))),
			array('config.update', array('shout_sound_add', str_replace('.mp3', '', $this->config['shout_sound_add']))),
			array('config.update', array('shout_sound_edit', str_replace('.mp3', '', $this->config['shout_sound_edit']))),
		);
	}
}
