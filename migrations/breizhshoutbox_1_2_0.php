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

class breizhshoutbox_1_2_0 extends migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['shout_version'], '1.2.0', '>=');
	}

	static public function depends_on()
	{
		return array('\sylver35\breizhshoutbox\migrations\breizhshoutbox_1_1_0');
	}

	public function update_data()
	{
		return array(
			// Version of extension
			array('config.update', array('shout_version', '1.2.0')),

			// Config
			array('config.add', array('shout_last_run_birthday', '', true)),
			array('config.add', array('shout_arcade_new', 0)),
			array('config.add', array('shout_arcade_record', 0)),
			array('config.add', array('shout_arcade_urecord', 0)),
		);
	}
}
