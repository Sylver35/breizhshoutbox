<?php
/**
*
* @package Breizh Shoutbox Extension
* @copyright (c) 2019-2023 Sylver35  https://breizhcode.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace sylver35\breizhshoutbox\core;

use sylver35\breizhshoutbox\core\shoutbox;
use phpbb\config\config;
use phpbb\db\driver\driver_interface as db;
use phpbb\event\dispatcher_interface as phpbb_dispatcher;

class smilies
{
	/* @var \sylver35\breizhshoutbox\core\shoutbox */
	protected $shoutbox;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher_interface */
	protected $phpbb_dispatcher;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string root path web */
	protected $root_path_web;

	/**
	 * Constructor
	 */
	public function __construct(shoutbox $shoutbox, config $config, db $db, phpbb_dispatcher $phpbb_dispatcher, $root_path)
	{
		$this->shoutbox = $shoutbox;
		$this->config = $config;
		$this->db = $db;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->root_path = $root_path;
		$this->root_path_web = generate_board_url() . '/';
	}

	public function smilies()
	{
		$smilies = $this->extact_list_smilies(1);

		$sql = 'SELECT COUNT(smiley_id) as total
			FROM ' . SMILIES_TABLE . '
				WHERE display_on_shout = 0';
		$result = $this->shoutbox->shout_sql_query($sql);
		$row_nb = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		$content = [
			'smilies'	=> $smilies['list'],
			'total'		=> $smilies['nb'],
			'nb_pop'	=> $row_nb,
			'url'		=> $this->root_path_web . $this->config['smilies_path'] . '/',
		];

		/**
		 * You can use this event to modify the content array.
		 *
		 * @event breizhshoutbox.smilies
		 * @var	array	content		The content array to be displayed in the smilies form
		 * @since 1.7.0
		 */
		$vars = ['content'];
		extract($this->phpbb_dispatcher->trigger_event('breizhshoutbox.smilies', compact($vars)));

		return $content;
	}

	public function smilies_popup($cat)
	{
		$smilies = $this->extact_list_smilies(0);

		$content = [
			'smilies'	=> $smilies['list'],
			'total'		=> $smilies['nb'],
			'nb_pop'	=> 0,
			'url'		=> $this->root_path_web . $this->config['smilies_path'] . '/',
			'on_cat'	=> $cat,
		];

		/**
		 * You can use this event to modify the content array.
		 *
		 * @event breizhshoutbox.smilies_popup
		 * @var	array	content			The content array to be displayed in the smilies form
		 * @var	int		cat				The id of smilies category if needed
		 * @since 1.7.0
		 */
		$vars = ['content', 'cat'];
		extract($this->phpbb_dispatcher->trigger_event('breizhshoutbox.smilies_popup', compact($vars)));

		return $content;
	}

	public function display_smilies($smiley, $display)
	{
		$var_set = ($display === 1) ? 0 : 1;
		$data = [
			'type'	=> ($display === 1) ? 1 : 2,
		];

		$this->db->sql_query('UPDATE ' . SMILIES_TABLE . " SET display_on_shout = $var_set WHERE smiley_id = $smiley");

		$smilies = $this->extact_list_smilies(1);
		$smilies_pop = $this->extact_list_smilies(0);

		$data = array_merge($data, [
			'smilies'		=> $smilies['list'],
			'smiliesPop'	=> $smilies_pop['list'],
			'total'			=> $smilies['nb'],
			'totalPop'		=> $smilies_pop['nb'],
			'url'			=> $this->root_path_web . $this->config['smilies_path'] . '/',
		]);

		return $data;
	}

	private function extact_list_smilies($sort)
	{
		$i = 0;
		$smilies = [];
		$sql = $this->db->sql_build_query('SELECT', [
			'SELECT'	=> 'smiley_url, MIN(smiley_id) AS smiley_id, MIN(code) AS code, MIN(smiley_order) AS min_smiley_order, MIN(smiley_width) AS smiley_width, MIN(smiley_height) AS smiley_height, MIN(emotion) AS emotion, MIN(display_on_shout) AS display_on_shout',
			'FROM'		=> [SMILIES_TABLE => ''],
			'WHERE'		=> 'display_on_shout = ' . $sort,
			'GROUP_BY'	=> 'smiley_url',
			'ORDER_BY'	=> 'min_smiley_order ASC',
		]);
		$result = $this->shoutbox->shout_sql_query($sql);
		if (!$result)
		{
			return;
		}
		while ($row = $this->db->sql_fetchrow($result))
		{
			$smilies[$i] = [
				'nb'		=> $i,
				'id'		=> (int) $row['smiley_id'],
				'width'		=> (int) $row['smiley_width'],
				'height'	=> (int) $row['smiley_height'],
				'code'		=> (string) $row['code'],
				'emotion'	=> (string) $row['emotion'],
				'image'		=> (string) $row['smiley_url'],
			];
			$i++;
		}
		$this->db->sql_freeresult($result);

		return [
			'list'	=> $smilies,
			'nb'	=> $i,
		];
	}
}
