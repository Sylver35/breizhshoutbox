<?php
/**
 *
 * @package Breizh Shoutbox Extension
 * @copyright (c) 2019-2023 Sylver35  https://breizhcode.com
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace sylver35\breizhshoutbox\migrations;

use phpbb\db\migration\migration;

class breizhshoutbox_1_6_0 extends migration
{
	public function effectively_installed()
	{
		return (bool) phpbb_version_compare($this->config['shout_version'], '1.6.0', '>=');
	}

	static public function depends_on()
	{
		return ['\sylver35\breizhshoutbox\migrations\breizhshoutbox_1_5_0'];
	}

	public function update_data()
	{
		return [
			// Version of extension
			['config.update', ['shout_version', '1.6.0']],

			// Config
			['config.add', ['shout_see_cite', 1]],

			// Delete ".mp3" in somes config
			['config.update', ['shout_sound_new', str_replace('.mp3', '', $this->config['shout_sound_new'])]],
			['config.update', ['shout_sound_new_priv', str_replace('.mp3', '', $this->config['shout_sound_new'])]],
			['config.update', ['shout_sound_error', str_replace('.mp3', '', $this->config['shout_sound_error'])]],
			['config.update', ['shout_sound_del', str_replace('.mp3', '', $this->config['shout_sound_del'])]],
			['config.update', ['shout_sound_add', str_replace('.mp3', '', $this->config['shout_sound_add'])]],
			['config.update', ['shout_sound_edit', str_replace('.mp3', '', $this->config['shout_sound_edit'])]],
		];
	}
}
