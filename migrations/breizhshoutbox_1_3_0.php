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

class breizhshoutbox_1_3_0 extends migration
{
	public function effectively_installed()
	{
		return (bool) phpbb_version_compare($this->config['shout_version'], '1.3.0', '>=');
	}

	static public function depends_on()
	{
		return ['\sylver35\breizhshoutbox\migrations\breizhshoutbox_1_2_0'];
	}

	public function update_data()
	{
		return [
			// Version of extension
			['config.update', ['shout_version', '1.3.0']],

			// Config
			['config.add', ['shout_panel_float', 1]],
		];
	}
}
