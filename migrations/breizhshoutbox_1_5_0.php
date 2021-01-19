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

class breizhshoutbox_1_5_0 extends migration
{
	public function effectively_installed()
	{
		return version_compare($this->config['shout_version'], '1.5.0', '>=');
	}

	static public function depends_on()
	{
		return ['\sylver35\breizhshoutbox\migrations\breizhshoutbox_1_4_0'];
	}

	public function update_data()
	{
		return [
			// Version of extension
			['config.update', ['shout_version', '1.5.0']],

			// Config
			['config.add', ['shout_name_robot', 'Robot']],
			['config.add', ['shout_rules_open_priv', 0]],
			['config.add', ['shout_see_button_ip', 0]],
		];
	}
}
