<?php
/**
*
* @package Breizh Shoutbox Extension
* @copyright (c) 2019-2023 Sylver35  https://breizhcode.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace sylver35\breizhshoutbox\core;

use sylver35\breizhshoutbox\core\work;
use phpbb\config\config;
use phpbb\user;
use phpbb\language\language;
use phpbb\controller\helper;
use phpbb\db\driver\driver_interface as db;
use phpbb\cache\driver\driver_interface as cache;
use phpbb\event\dispatcher_interface as phpbb_dispatcher;

class robot
{
	/* @var \sylver35\breizhshoutbox\core\work */
	protected $work;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\language\language */
	protected $language;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\event\dispatcher_interface */
	protected $phpbb_dispatcher;

	/**
	 * The database tables
	 *
	 * @var string */
	protected $shoutbox_table;
	protected $shoutbox_priv_table;

	/**
	 * Constructor
	 */
	public function __construct(work $work, config $config, user $user, language $language, helper $helper, db $db, cache $cache, phpbb_dispatcher $phpbb_dispatcher, $shoutbox_table, $shoutbox_priv_table)
	{
		$this->work = $work;
		$this->config = $config;
		$this->user = $user;
		$this->language = $language;
		$this->helper = $helper;
		$this->db = $db;
		$this->cache = $cache;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->shoutbox_table = $shoutbox_table;
		$this->shoutbox_priv_table = $shoutbox_priv_table;
	}

	public function shout_run_robot($sleep = false)
	{
		if ($this->config['shout_enable_robot'] && $this->config['shout_cron_hour'] == date('H'))
		{
			// Say hello Mr Robot :-)
			$this->hello_robot_shout($sleep);
			// Wish birthdays Mr Robot :-)
			$this->robot_birthday_shout($sleep);
		}
	}

	/*
	 * Display infos Robot for purge, delete messages
	 * and enter in the private shoutbox
	 */
	public function post_robot_shout($userid, $ip, $priv = false, $purge = false, $robot = false, $auto = false, $delete = false, $deleted = '')
	{
		$info = 3;
		$sort_info = 1;
		$message = '-';
		$userid = (int) $userid;
		$_priv = ($priv) ? '_priv' : '';
		$table = ($priv) ? $this->shoutbox_priv_table : $this->shoutbox_table;

		if ($priv && !$purge && !$robot && !$auto && !$delete)
		{
			$sql = $this->db->sql_build_query('SELECT', [
				'SELECT'	=> 'shout_time',
				'FROM'		=> [$table => ''],
				'WHERE'		=> "shout_robot = 8 AND shout_robot_user = $userid AND shout_time BETWEEN " . (time() - 60 * 30) . " AND " . time(),
			]);
			$result = $this->db->sql_query($sql);
			$is_posted = $this->db->sql_fetchfield('shout_time');
			$this->db->sql_freeresult($result);
			if ($is_posted)
			{
				return;
			}
			$sort_info = 8;
			$message = $this->user->data['username'];
		}
		else
		{
			if (!$this->config['shout_enable_robot'])
			{
				return;
			}
			$get_info = $this->get_info_session($priv, $purge, $robot, $auto, $delete, $deleted);
			$info = $get_info['info'];
			$message = $get_info['message'];
		}

		$sql_data = [
			'shout_time'				=> time(),
			'shout_user_id'				=> 0,
			'shout_ip'					=> (string) $ip,
			'shout_text'				=> (string) $message,
			'shout_bbcode_uid'			=> '',
			'shout_bbcode_bitfield'		=> '',
			'shout_bbcode_flags'		=> 0,
			'shout_robot'				=> (int) $sort_info,
			'shout_robot_user'			=> (int) $userid,
			'shout_forum'				=> 0,
			'shout_info'				=> (int) $info,
		];

		$this->db->sql_query('INSERT INTO ' . $table . ' ' . $this->db->sql_build_array('INSERT', $sql_data));
		$this->config->increment("shout_nr{$_priv}", 1, true);
	}

	/*
	 * Traduct and display infos robot
	 * for all infos robot functions
	 */
	public function display_infos_robot($row, $info, $acp)
	{
		$message = '';
		$start = $this->language->lang('SHOUT_ROBOT_START');

		switch ($info)
		{
			case 1:
				$message = $this->language->lang('SHOUT_SESSION_ROBOT', $this->work->construct_action_shout($row['v_user_id'], $row['v_username'], $row['v_user_colour'], $acp));
			break;
			case 2:
				$message = $this->language->lang('SHOUT_SESSION_ROBOT_BOT', $start, get_username_string('no_profile', $row['v_user_id'], $row['v_username'], $row['v_user_colour']));
			break;
			case 3:
				$message = $this->language->lang('SHOUT_ENTER_PRIV', $start, $this->work->construct_action_shout($row['v_user_id'], $row['v_username'], $row['v_user_colour'], $acp));
			break;
			case 4:
			case 5:
			case 6:
			case 7:
			case 8:
				$message = $this->language->lang('SHOUT_PURGE_' . $info, $start, $row['shout_text']);
			break;
			case 9:
			case 10:
				$message = $this->language->lang('SHOUT_DELETE_AUTO_' . $info, $start, $row['shout_text']);
			break;
			case 11:
				$message = $this->language->lang('SHOUT_BIRTHDAY_ROBOT' . (($row['shout_info_nb'] > 0) ? '_FULL' : ''), $this->config['sitename'], $this->work->construct_action_shout($row['v_user_id'], $row['v_username'], $row['v_user_colour'], $acp), $this->work->tpl('close'), $this->work->tpl('bold') . $row['shout_info_nb']);
			break;
			case 12:
				$message = $this->language->lang('SHOUT_HELLO_ROBOT', $this->work->tpl('close'), $this->work->tpl('bold') . $this->user->format_date($row['shout_time'], $this->language->lang('SHOUT_ROBOT_DATE'), true));
			break;
			case 13:
				$message = $this->language->lang('SHOUT_NEWEST_ROBOT', $this->work->construct_action_shout($row['v_user_id'], $row['v_username'], $row['v_user_colour'], $acp), $this->config['sitename']);
			break;
			case 14:
			case 15:
			case 16:
			case 17:
			case 18:
			case 19:
			case 20:
			case 21:
				$message = $this->language->lang('SHOUT_POST_ROBOT_' . $info, $start, $this->work->construct_action_shout($row['v_user_id'], $row['v_username'], $row['v_user_colour'], $acp), $this->work->tpl('url', append_sid($this->work->shout_url($row['shout_text2']), false), $row['shout_text']));
			break;
			case 22:
				$message = $this->language->lang('SHOUT_UPDATE_USERNAME', $this->work->construct_action_shout($row['v_user_id'], $row['shout_text'], $row['v_user_colour'], $acp), $this->work->construct_action_shout($row['v_user_id'], $row['shout_text2'], $row['v_user_colour'], $acp));
			break;
			case 30:
				list($title, $artist) = explode('||', $row['shout_text']);
				$url = $this->helper->route('sylver35_breizhcharts_page_music', ['mode' => 'list_newest']);
				$message = $this->language->lang('SHOUT_CHARTS_NEW', $this->work->construct_action_shout($row['v_user_id'], $row['v_username'], $row['v_user_colour'], $acp), $this->work->tpl('url', $url, $this->language->lang('SHOUT_FROM_OF', $title, $artist)));
				$message .= ($row['shout_text2']) ? ' ⇒ ' . $this->work->tpl('url', $row['shout_text2'], $this->language->lang('SHOUT_CHARTS_SUBJECT')) : '';
			break;
			case 31:
				$url = $this->helper->route('sylver35_breizhcharts_page_music', ['mode' => 'winners']);
				$message = $this->language->lang('SHOUT_CHARTS_RESET', $this->work->tpl('url', $url, $row['shout_text']), $this->work->tpl('url', $url, $row['shout_text2']));
			break;
			case 35:
				$title = (strlen($row['shout_text']) > 45) ? substr($row['shout_text'], 0, 42) . '...' : $row['shout_text'];
				$cat_url = $this->work->tpl('url', $this->helper->route('sylver35_breizhyoutube_controller', ['mode' => 'cat', 'id' => $row['shout_robot']]), $row['shout_text2']);
				$message = $this->language->lang('SHOUT_NEW_VIDEO', $this->work->tpl('url', $this->helper->route('sylver35_breizhyoutube_controller', ['mode' => 'view', 'id' => $row['shout_info_nb']]), $title, $row['shout_text']), $cat_url);
			break;
			case 36:
			case 37:
			case 38:
				$message = $this->language->lang("SHOUT_NEW_SCORE_{$info}", $row['shout_robot'], $this->work->tpl('url', $this->helper->route('teamrelax_relaxarcade_page_games', ['gid' => $row['shout_info_nb']]), $row['shout_text']));
				$message .= ($row['shout_robot_user'] && $row['shout_text2']) ? $this->language->lang('SHOUT_IN', $this->work->tpl('url', $this->helper->route('teamrelax_relaxarcade_page_list', ['cid' => $row['shout_robot_user']]), $row['shout_text2'])) : '';
			break;
			case 65:
			case 66:
				$data = generate_text_for_display($row['shout_text'], $row['shout_bbcode_uid'], $row['shout_bbcode_bitfield'], $row['shout_bbcode_flags']);
				$message = $this->work->tpl('cite', $this->language->lang(($info === 65) ? 'SHOUT_USER_POST' : 'SHOUT_ACTION_CITE_ON'), $this->work->construct_action_shout($row['v_user_id'], $row['v_username'], $row['v_user_colour'], $acp), $data);
			break;
			case 60:
			case 70:
			case 71:
			case 72:
			case 73:
			case 74:
			case 75:
			case 76:
			case 77:
			case 80:
				$message = $this->language->lang('SHOUT_PREZ_ROBOT_' . $info, $start, $this->work->construct_action_shout($row['v_user_id'], $row['v_username'], $row['v_user_colour'], $acp), $this->work->tpl('url', append_sid($this->work->shout_url($row['shout_text2']), false), $row['shout_text']));
			break;
			case 99:
				$message = $this->language->lang('SHOUT_WELCOME');
			break;
		}

		return $message;
	}

	public function insert_message_robot($sql_data, $insert, $insert_priv)
	{
		if ($this->config['shout_enable_robot'])
		{
			if ($insert)
			{
				$this->db->sql_query('INSERT INTO ' . $this->shoutbox_table . ' ' . $this->db->sql_build_array('INSERT', $sql_data));
				$this->config->increment('shout_nr', 1, true);
			}

			if ($insert_priv)
			{
				$this->db->sql_query('INSERT INTO ' . $this->shoutbox_priv_table . ' ' . $this->db->sql_build_array('INSERT', $sql_data));
				$this->config->increment('shout_nr_priv', 1, true);
			}
		}
	}

	/*
	 * Display info of birthdays
	 */
	public function robot_birthday_shout($sleep)
	{
		if ((!$this->config['shout_birthday'] && !$this->config['shout_birthday_priv']) || $this->config['shout_last_run_birthday'] == date('d-m-Y'))
		{
			return;
		}
		else if ($sleep)
		{
			usleep(mt_rand(500000, 2000000));
		}

		$table = ($this->config['shout_birthday']) ? $this->shoutbox_table : $this->shoutbox_priv_table;
		$sql = $this->db->sql_build_query('SELECT', [
			'SELECT'	=> 'COUNT(shout_id) as nr',
			'FROM'		=> [$table => ''],
			'WHERE'		=> 'shout_info = 11 AND shout_time BETWEEN ' . (time() - 60 * 60) . ' AND ' . time(),
			'GROUP_BY'	=> 'shout_robot_user',
		]);
		$result = $this->db->sql_query($sql);
		$is_posted = (int) $this->db->sql_fetchfield('nr');
		$this->db->sql_freeresult($result);

		$i = 0;
		$sql_data = [];
		$time = $this->user->create_datetime();
		$now = phpbb_gmgetdate($time->getTimestamp() + $time->getOffset());

		if (!$is_posted)
		{
			$rows = $this->extract_birthdays($time, $now);
			if (!empty($rows))
			{
				$exclude_group = explode(', ', $this->config['shout_birthday_exclude']);
				foreach ($rows as $row)
				{
					if (in_array($row['group_id'], $exclude_group))
					{
						continue;
					}

					$sql_data[] = [
						'shout_time'			=> time(),
						'shout_user_id'			=> 0,
						'shout_ip'				=> $this->user->ip,
						'shout_text'			=> 'SHOUT_BIRTHDAY_ROBOT',
						'shout_bbcode_uid'		=> '',
						'shout_bbcode_bitfield'	=> '',
						'shout_bbcode_flags'	=> 0,
						'shout_robot'			=> 5,
						'shout_robot_user'		=> (int) $row['user_id'],
						'shout_forum'			=> 0,
						'shout_info_nb'			=> $row['user_birthday'] ? max(0, $now['year'] - substr($row['user_birthday'], -4)) : 0,
						'shout_info'			=> 11,
					];
					$i++;
				}

				if ($i > 0)
				{
					if ($this->config['shout_birthday'])
					{
						$this->db->sql_multi_insert($this->shoutbox_table, $sql_data);
						$this->config->increment('shout_nr', $i, true);
					}
					if ($this->config['shout_birthday_priv'])
					{
						$this->db->sql_multi_insert($this->shoutbox_priv_table, $sql_data);
						$this->config->increment('shout_nr_priv', $i, true);
					}
				}
			}
			$this->config->set('shout_last_run_birthday', date('d-m-Y'), true);
		}
		else if ($is_posted > 1)
		{
			$this->delete_double_birthdays($is_posted, $time, $now);
		}
	}

	/*
	 * Display the date info Robot
	 */
	public function hello_robot_shout($sleep)
	{
		if ((!$this->config['shout_hello'] && !$this->config['shout_hello_priv']) || $this->config['shout_cron_run'] == date('d-m-Y'))
		{
			return;
		}
		else if ($sleep)
		{
			usleep(mt_rand(500000, 2000000));
		}

		$table = ($this->config['shout_hello']) ? $this->shoutbox_table : $this->shoutbox_priv_table;
		$sql = $this->db->sql_build_query('SELECT', [
			'SELECT'	=> 'COUNT(shout_id) as nr',
			'FROM'		=> [$table => ''],
			'WHERE'		=> 'shout_info = 12 AND shout_time BETWEEN ' . (time() - 60 * 60) . ' AND ' . time(),
		]);
		$result = $this->db->sql_query($sql);
		$is_posted = (int) $this->db->sql_fetchfield('nr');
		$this->db->sql_freeresult($result);

		if (!$is_posted)
		{
			$this->insert_message_robot([
				'shout_time'			=> time(),
				'shout_user_id'			=> 0,
				'shout_ip'				=> (string) $this->user->ip,
				'shout_text'			=> (string) date('d-m-Y'),
				'shout_bbcode_uid'		=> '',
				'shout_bbcode_bitfield'	=> '',
				'shout_bbcode_flags'	=> 0,
				'shout_robot'			=> 1,
				'shout_robot_user'		=> 0,
				'shout_forum'			=> 0,
				'shout_info'			=> 12,
			], $this->config['shout_hello'], $this->config['shout_hello_priv']);

			$this->config->set('shout_cron_run', date('d-m-Y'), true);
		}
		else if ($is_posted > 1)
		{
			$this->delete_double_messages($is_posted);
		}
	}

	private function delete_double_birthdays($nr, $time, $now)
	{
		$birthdays = count($this->extract_birthdays($time, $now));
		$del = ($nr - 1) * $birthdays;
		if ($this->config['shout_birthday'])
		{
			$sql = $this->db->sql_build_query('SELECT', [
				'SELECT'	=> 'shout_id',
				'FROM'		=> [$this->shoutbox_table => ''],
				'WHERE'		=> 'shout_info = 11 AND shout_time BETWEEN ' . (time() - 60 * 60) . ' AND ' . time(),
			]);
			$result = $this->db->sql_query_limit($sql, $del);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->db->sql_query('DELETE FROM ' . $this->shoutbox_table . ' WHERE shout_id = ' . $row['shout_id']);
			}
			$this->db->sql_freeresult($result);
		}

		if ($this->config['shout_birthday_priv'])
		{
			$sql = $this->db->sql_build_query('SELECT', [
				'SELECT'	=> 'shout_id',
				'FROM'		=> [$this->shoutbox_priv_table => ''],
				'WHERE'		=> 'shout_info = 11 AND shout_time BETWEEN ' . (time() - 60 * 60) . ' AND ' . time(),
			]);
			$result = $this->db->sql_query_limit($sql, $del);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->db->sql_query('DELETE FROM ' . $this->shoutbox_priv_table . ' WHERE shout_id = ' . $row['shout_id']);
			}
			$this->db->sql_freeresult($result);
		}
	}

	private function extract_birthdays($time, $now)
	{
		if (($rows = $this->cache->get('_shoutbox_birthdays')) === false)
		{
			// Display birthdays of 29th february on 28th february in non-leap-years
			$leap_year_birthdays = '';
			if ($now['mday'] == 28 && $now['mon'] == 2 && !$time->format('L'))
			{
				$leap_year_birthdays = " OR u.user_birthday LIKE '" . $this->db->sql_escape(sprintf('%2d-%2d-', 29, 2)) . "%'";
			}

			$sql_ary = [
				'SELECT'	=> 'u.user_id, u.user_birthday, u.group_id',
				'FROM'		=> [USERS_TABLE => 'u'],
				'LEFT_JOIN'	=> [
					[
						'FROM'	=> [BANLIST_TABLE => 'b'],
						'ON'	=> 'u.user_id = b.ban_userid',
					],
				],
				'WHERE'		=> "(b.ban_id IS NULL OR b.ban_exclude = 1)
					AND (u.user_birthday LIKE '" . $this->db->sql_escape(sprintf('%2d-%2d-', $now['mday'], $now['mon'])) . "%' $leap_year_birthdays)
					AND u.user_type IN (" . USER_NORMAL . ', ' . USER_FOUNDER . ')',
			];

			$sql = $this->db->sql_build_query('SELECT', $sql_ary);
			$result = $this->db->sql_query($sql);
			$rows = $this->db->sql_fetchrowset($result);
			$this->db->sql_freeresult($result);

			// cache for 2 hour
			$this->cache->put('_shoutbox_birthdays', $rows, 7200);
		}

		return $rows;
	}

	private function delete_double_messages($nr)
	{
		if ($this->config['shout_hello'])
		{
			$sql = $this->db->sql_build_query('SELECT', [
				'SELECT'	=> 'shout_id',
				'FROM'		=> [$this->shoutbox_table => ''],
				'WHERE'		=> "shout_info = 12 AND shout_text = '" . date('d-m-Y') . "'",
			]);
			$result = $this->db->sql_query_limit($sql, (int) $nr - 1);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->db->sql_query('DELETE FROM ' . $this->shoutbox_table . ' WHERE shout_id = ' . $row['shout_id']);
			}
			$this->db->sql_freeresult($result);
		}

		if ($this->config['shout_hello_priv'])
		{
			$sql = $this->db->sql_build_query('SELECT', [
				'SELECT'	=> 'shout_id',
				'FROM'		=> [$this->shoutbox_priv_table => ''],
				'WHERE'		=> "shout_info = 12 AND shout_text = '" . date('d-m-Y') . "'",
			]);
			$result = $this->db->sql_query_limit($sql, (int) $nr - 1);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->db->sql_query('DELETE FROM ' . $this->shoutbox_priv_table . ' WHERE shout_id = ' . $row['shout_id']);
			}
			$this->db->sql_freeresult($result);
		}
	}
}
