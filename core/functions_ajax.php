<?php
/**
*
* @package phpBB Extension - Breizh Shoutbox
* @copyright (c) 2018-2025 Sylver35  https://breizhcode.com
* @license https://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace sylver35\breizhshoutbox\core;

use sylver35\breizhshoutbox\core\shoutbox;
use sylver35\breizhshoutbox\core\work;
use sylver35\breizhshoutbox\core\robot;
use sylver35\breizhshoutbox\core\bbcodes;
use sylver35\breizhshoutbox\core\avatar;
use phpbb\request\request;
use phpbb\config\config;
use phpbb\db\driver\driver_interface as db;
use phpbb\auth\auth;
use phpbb\user;
use phpbb\language\language;
use phpbb\event\dispatcher_interface as phpbb_dispatcher;

class functions_ajax
{
	/* @var \sylver35\breizhshoutbox\core\shoutbox */
	protected $shoutbox;

	/* @var \sylver35\breizhshoutbox\core\work */
	protected $work;

	/* @var \sylver35\breizhshoutbox\core\robot */
	protected $robot;

	/* @var \sylver35\breizhshoutbox\core\bbcodes */
	protected $bbcodes;

	/* @var \sylver35\breizhshoutbox\core\avatar */
	protected $avatar;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\event\dispatcher_interface */
	protected $phpbb_dispatcher;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string root path web */
	protected $root_path_web;

	/** @var string Custom form action */
	protected $u_action;

	/**
	 * The database tables
	 *
	 * @var string */
	protected $shoutbox_table;
	protected $shoutbox_priv_table;

	/**
	 * Constructor
	 */
	public function __construct(shoutbox $shoutbox, work $work, robot $robot, bbcodes $bbcodes, avatar $avatar, request $request, config $config, db $db, auth $auth, user $user, language $language, phpbb_dispatcher $phpbb_dispatcher, $root_path, $shoutbox_table, $shoutbox_priv_table)
	{
		$this->shoutbox = $shoutbox;
		$this->work = $work;
		$this->robot = $robot;
		$this->bbcodes = $bbcodes;
		$this->avatar = $avatar;
		$this->request = $request;
		$this->config = $config;
		$this->db = $db;
		$this->auth = $auth;
		$this->user = $user;
		$this->language = $language;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->root_path = $root_path;
		$this->shoutbox_table = $shoutbox_table;
		$this->shoutbox_priv_table = $shoutbox_priv_table;
		$this->root_path_web = generate_board_url() . '/';
	}

	/**
	 * Initialize somes variables
	 * @param string $mode mode to switch
	 * @param int $sort sort of shoutbox
	 * @param int $id id of javascript user
	 * @param int $other id of other user
	 * @return array
	 */
	public function shout_initialize($mode, $sort, $id, $other)
	{
		// First initialize somes variables, protect private
		// And select the good table for the type of shoutbox
		$val = [
			'is_user'		=> $this->user->data['is_registered'] && !$this->user->data['is_bot'],
			'userid'		=> (int) $this->user->data['user_id'],
			'founder'		=> (bool) $this->user->data['user_type'] == USER_FOUNDER,
			'id'			=> (int) $id,
			'other'			=> (int) $other,
			'sort'			=> (int) $sort,
			'mode'			=> (string) $mode,
			'on_priv'		=> false,
			'perm'			=> '_view',
			'auth'			=> '_manage',
			'priv'			=> '',
			'privat'		=> '',
			'sort_on'		=> '',
			'board'			=> $this->root_path_web,
			'table'			=> $this->shoutbox_table,
			'viewonline'	=> $this->auth->acl_get('u_viewonline'),
		];

		switch ($sort)
		{
			// Popup shoutbox
			case 1:
				$val['sort_on'] = '_pop';
			break;
			// Normal shoutbox
			case 2:
				// Nothing to add here
			break;
			// Private shoutbox
			case 3:
				$val = array_merge($val, [
					'on_priv'	=> true,
					'sort_on'	=> '_priv',
					'perm'		=> '_priv',
					'priv'		=> '_priv',
					'auth'		=> '_priv',
					'privat'	=> '_PRIV',
					'table'		=> $this->shoutbox_priv_table,
				]);
			break;
		}

		// Permissions and security verifications
		if (!$this->auth->acl_get('u_shout' . $val['perm']))
		{
			$this->work->shout_error('NO_VIEW' . $val['privat'] . '_PERM');
			return;
		}

		// We have our own error handling
		$this->db->sql_return_on_error(true);

		return $val;
	}

	/**
	 * Get variables from shoutbox and return good format
	 * @param $var string variable
	 * @param $default string|int|bool|array sort of variable
	 * Return string|int|bool|array
	 */
	public function get_var($var, $default)
	{
		if ($default === '')
		{
			return (string) $this->request->variable($var, '', true);
		}
		else if (is_bool($default))
		{
			return (bool) $this->request->variable($var, $default);
		}
		else if (is_array($default))
		{
			return $this->request->variable($var, [$default]);
		}
		else
		{
			return (int) $this->request->variable($var, $default);
		}
	}

	/**
	 * Displays list of users online
	 * Replace urls for users actions shout
	 * Return array|string
	 */
	public function online()
	{
		$online = obtain_users_online_string(obtain_users_online());
		$list_online = $online['online_userlist'];
		$start = $this->language->lang('REGISTERED_USERS') . ' ';

		$data = [
			'title'	=> $online['l_online_users'] . '<br>(' . $this->language->lang('VIEW_ONLINE_TIMES', (int) $this->config['load_online_time']) . ')',
			'list'	=> '',
		];

		if ($list_online === ($start . $this->language->lang('NO_ONLINE_USERS')))
		{
			$data['list'] = $list_online;
		}
		else
		{
			$r = $u = 0;
			$robots = $users = '';
			$userlist = explode(', ', str_replace($start, '', $list_online));
			foreach ($userlist as $this_user)
			{
				$id = $this->work->find_string($this_user, '&amp;u=', '" ');
				if (!$id)
				{
					$robots .= (($r > 0) ? ', ' : '') . $this_user;
					$r++;
				}
				else
				{
					$users .= ($u > 0) ? ', ' : '';
					// fix bug with Online users avatar ext
					$fix = $this->online_avatar($users, $this_user);
					$this_user = $fix['this_user'];
					$users .= $fix['avatar'] . $this->work->shout_url($this->work->construct_action_shout($id, $this->work->find_string($this_user, 'username-coloured">', '</a>'), $this->work->find_string($this_user, 'color: #', ';"')));
					$u++;
				}
			}
			$data['list'] .= $u . ' ' . (($u === 1) ? str_replace($this->language->lang('REGISTERED_USERS'), $this->language->lang('REGISTERED_USER'), $start) : $start);
			$data['list'] .= ($u > 0) ? $users : $this->language->lang('NO_ONLINE_USERS');
			$data['list'] .= '<hr>' . $r . ' ' . $this->language->lang('G_BOT' . (($r > 1) ? 'S' : '')) . ' : ';
			$data['list'] .= ($r > 0) ? $robots : $this->language->lang('NO_ONLINE_BOTS');
		}

		return $data;
	}

	private function online_avatar($users, $this_user)
	{
		$avatar = (strpos($this_user, 'class="useravatar"')) ? '<span class="useravatar">' . $this->work->find_string($this_user, 'class="useravatar">', '</span>') . '</span> ' : '';
		// Remove avatar now, it will be inserted later
		if ($avatar !== '')
		{
			$this_user = str_replace($avatar, '', $this_user);
		}

		return ['this_user' => $this_user, 'avatar' => $avatar];
	}

	public function auth($user_id, $username)
	{
		$this->language->add_lang('acp/common');
		$this->language->add_lang('permissions_shoutbox', 'sylver35/breizhshoutbox');

		$first = '';
		$title = $data = [];
		$sort = ['a' => 'ACP_VIEW_ADMIN_PERMISSIONS', 'm' => 'ACP_VIEW_GLOBAL_MOD_PERMISSIONS', 'u'	=> 'ACP_VIEW_USER_PERMISSIONS'];
		$list = $this->shoutbox->list_auth_options();

		for ($i = 0, $nb = sizeof($list); $i < $nb; $i++)
		{
			// Extract sort of auth a, m, u
			$second = substr($list[$i], 0, 1);
			$active = $this->auth->acl_get_list($user_id, $list[$i]);
			$class = ($active) ? 'auth_yes' : 'auth_no';
			$title[$i] = ($second !== $first) ? $this->language->lang($sort[$second]) : '';
			$data[$i] = $this->language->lang('SHOUT_OPTION_' . ($active ? 'YES' : 'NO'), $this->language->lang('ACL_' . strtoupper($list[$i])), $class);
			// Keep this value in memory
			$first = $second;
		}

		return [
			'nb'		=> $i,
			'title'		=> $title,
			'data'		=> $data,
			'username'	=> $this->language->lang('SHOUT_OPTION_USER', $username),
		];
	}

	public function user_bbcode($val, $open, $close)
	{
		return $this->bbcodes->user_bbcode($val, $open, $close);
	}

	public function charge_bbcode($id)
	{
		return $this->bbcodes->charge_bbcode($id);
	}

	public function edit($val, $shout_id, $message)
	{
		if (!$this->shoutbox->check_edit($val, $shout_id))
		{
			return [
				'type'		=> 1,
				'mode'		=> $val['mode'],
				'shout_id'	=> $shout_id,
				'message'	=> $this->language->lang('NO_EDIT_PERM'),
			];
		}

		// Multi protections at this time...
		$message = $this->shoutbox->parse_shout_message($message, $val['priv'], $val['privat'], false, false);

		// will be modified by generate_text_for_storage
		$options = 0;
		$uid = $bitfield = '';
		generate_text_for_storage($message, $uid, $bitfield, $options, $this->auth->acl_get('u_shout_bbcode'), true, $this->auth->acl_get('u_shout_smilies'));

		$sql_ary = [
			'shout_text'			=> (string) $message,
			'shout_bbcode_uid'		=> $uid,
			'shout_bbcode_bitfield'	=> $bitfield,
			'shout_bbcode_flags'	=> $options,
		];

		$sql = 'UPDATE ' . $val['table'] . '
			SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE shout_id = ' . $shout_id;
		$this->work->shout_sql_query($sql);

		// For reload the message to everybody
		$this->shoutbox->update_messages($val['table']);
		$message = generate_text_for_display($message, $uid, $bitfield, $options);

		return [
			'type'		=> 2,
			'mode'		=> $val['mode'],
			'shout_id'	=> $shout_id,
			'message'	=> $this->language->lang('EDIT_DONE'),
			'texte'		=> $message,
		];
	}

	public function post($val, $message, $name, $cite)
	{
		if (!$this->auth->acl_get('u_shout_post'))
		{
			return [
				'type'		=> 2,
				'message'	=> $this->language->lang('NO_POST_PERM'),
			];
		}
		else if (!$this->shoutbox->verify_flood($val['on_priv'], $val['userid']))
		{
			return [
				'type'		=> 2,
				'message'	=> $this->language->lang('FLOOD_ERROR'),
			];
		}

		// Multi protections at this time...
		$message = $this->shoutbox->parse_shout_message($message, $val['priv'], $val['privat'], false, true);

		// will be modified by generate_text_for_storage
		$options = 0;
		$uid = $bitfield = '';
		generate_text_for_storage($message, $uid, $bitfield, $options, $this->auth->acl_get('u_shout_bbcode'), true, $this->auth->acl_get('u_shout_smilies'));

		// For guest, add a random number from ip after name
		if (!$this->user->data['is_registered'])
		{
			$name = $this->shoutbox->add_random_ip($name);
		}

		$sql_ary = [
			'shout_time'			=> (int) time(),
			'shout_user_id'			=> (int) $val['userid'],
			'shout_ip'				=> (string) $this->user->ip,
			'shout_text'			=> (string) $message,
			'shout_text2'			=> (string) $name,
			'shout_bbcode_uid'		=> (string) $uid,
			'shout_bbcode_bitfield'	=> (string) $bitfield,
			'shout_bbcode_flags'	=> (int) $options,
			'shout_robot_user'		=> (int) $cite,
			'shout_robot'			=> 0,
			'shout_forum'			=> 0,
			'shout_info'			=> ($cite > 1) ? 66 : 0,
		];

		$sql = 'INSERT INTO ' . $val['table'] . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
		$this->work->shout_sql_query($sql);
		$this->config->increment('shout_nr' . $val['priv'], 1, true);
		$this->shoutbox->delete_shout_posts($val);

		return [
			'type'		=> 1,
			'mode'		=> $val['mode'],
			'cite'		=> $cite,
			'message'	=> $this->language->lang('POSTED'),
		];
	}

	public function check($val, $on_bot)
	{
		$this->robot->shout_run_robot(true);
		$sql_where = $this->shoutbox->shout_sql_where($val['is_user'], $val['userid'], $on_bot);

		return ['last' => $this->get_time($val['table'], $sql_where)];
	}

	public function view($val, $on_bot, $start)
	{
		$data = [
			'messages'	=> [],
			'total'		=> 0,
		];

		$perm = $this->shoutbox->extract_permissions($val['auth']);
		$dateformat = $this->shoutbox->extract_dateformat($val['is_user']);
		$sql_where = $this->shoutbox->shout_sql_where($val['is_user'], $val['userid'], $on_bot);
		$is_mobile = $this->work->shout_is_mobile();

		$sql = $this->db->sql_build_query('SELECT', [
			'SELECT'	=> 's.*, u.user_id, u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height, u.user_type, v.user_id as v_user_id, v.username as v_username, v.user_colour as v_user_colour, v.user_avatar as v_user_avatar, v.user_avatar_type as v_user_avatar_type, v.user_avatar_width as v_user_avatar_width, v.user_avatar_height as v_user_avatar_height, v.user_type as v_user_type',
			'FROM'		=> [$val['table'] => 's'],
			'LEFT_JOIN'	=> [
				[
					'FROM'	=> [USERS_TABLE => 'u'],
					'ON'	=> 'u.user_id = s.shout_user_id',
				],
				[
					'FROM'	=> [USERS_TABLE => 'v'],
					'ON'	=> 'v.user_id = s.shout_robot_user',
				],
			],
			'WHERE'		=> $sql_where,
			'ORDER_BY'	=> 's.shout_id DESC',
		]);
		$result = $this->work->shout_sql_query($sql, true, (int) $this->config['shout_num' . $val['sort_on']], $start);
		while ($row = $this->db->sql_fetchrow($result))
		{
			// Get additional data
			$row = $this->shoutbox->get_additional_data($row, $perm, $val);

			// Construct the content of loop
			$data['messages'][$data['total']] = [
				'shoutId'		=> $row['shout_id'],
				'shoutTime'		=> $this->user->format_date($row['shout_time'], $dateformat),
				'username'		=> $this->work->construct_action_shout($row['user_id'], $row['username'], $row['user_colour']),
				'avatar'		=> $this->avatar->get_shout_avatar($row, $val['sort'], $is_mobile),
				'shoutText'		=> $this->shoutbox->shout_text_for_display($row, $val['sort'], false),
				'isUser'		=> $row['is_user'],
				'other'			=> $row['other'],
				'name'			=> $row['username'],
				'msgPlain'		=> $row['msg_plain'],
				'timeMsg'		=> $row['shout_time'],
				'colour'		=> $row['user_colour'],
				'deletemsg'		=> $row['delete'],
				'edit'			=> $row['edit'],
				'showIp'		=> $this->config['shout_see_button_ip'] && $row['show_ip'],
				'shoutIp'		=> $this->config['shout_see_button_ip'] ? $row['on_ip'] : '',
			];
			$data['total']++;
		}
		$this->db->sql_freeresult($result);

		$data = array_merge($data, [
			// Get the last message time
			'last'		=> $this->get_time($val['table'], $sql_where),
			// The number of total messages for pagination
			'number'	=> $this->get_pagination($sql_where, $val['table'], $val['priv']),
		]);

		return $data;
	}

	public function exclude($mode)
	{
		$exclude = ['smilies', 'smilies_popup', 'display_smilies', 'online', 'question', 'preview_rules', 'date_format', 'action_sound'];
		if (!in_array($mode, $exclude))
		{
			return true;
		}

		return false;
	}

	private function get_pagination($sql_where, $table, $priv)
	{
		$sql = 'SELECT COUNT(s.shout_id) as nr
			FROM ' . $table . ' s
				WHERE ' . $sql_where;
		$result = $this->work->shout_sql_query($sql);
		$nb = (int) $this->db->sql_fetchfield('nr');
		$this->db->sql_freeresult($result);

		// Limit the number of messages to display
		$max_number = (int) $this->config['shout_max_posts_on' . $priv];
		$nb = (($max_number > 0) && ($nb > $max_number)) ? $max_number : $nb;

		return $nb;
	}

	private function get_time($table, $sql_where)
	{
		$sql = 'SELECT s.shout_time
			FROM ' . $table . ' s
				WHERE ' . $sql_where . '
				ORDER BY s.shout_id DESC';
		$result = $this->work->shout_sql_query($sql, true, 1);
		// check just with the last 4 numbers
		$last_time = (string) substr($this->db->sql_fetchfield('shout_time'), 6, 4);
		$this->db->sql_freeresult($result);

		return $last_time;
	}
}
