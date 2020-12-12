<?php
/**
*
* @package Breizh Shoutbox Extension
* @copyright (c) 2018-2020 Sylver35  https://breizhcode.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace sylver35\breizhshoutbox\core;
use sylver35\breizhshoutbox\core\shoutbox;
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
	public function __construct(shoutbox $shoutbox, config $config, db $db, auth $auth, user $user, language $language, phpbb_dispatcher $phpbb_dispatcher, $root_path, $shoutbox_table, $shoutbox_priv_table)
	{
		$this->shoutbox = $shoutbox;
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
	public function shout_manage($mode, $sort, $id, $other)
	{
		// First initialize somes variables, protect private
		// And select the good table for the type of shoutbox
		$val = [
			'is_user'		=> $this->user->data['is_registered'] && !$this->user->data['is_bot'],
			'userid'		=> (int) $this->user->data['user_id'],
			'id'			=> (int) $id,
			'other'			=> (int) $other,
			'sort'			=> (int) $sort,
			'mode'			=> (string) $mode,
			'on_priv'		=> false,
			'perm'			=> '_view',
			'auth'			=> '_manage',
			'priv'			=> '',
			'privat'		=> '',
			'board'			=> $this->root_path_web,
			'shout_table'	=> $this->shoutbox_table,
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
				$val['sort_on'] = '';
			break;
			// Private shoutbox
			case 3:
				$val['on_priv'] = true;
				$val['sort_on'] = $val['perm'] = $val['priv'] = $val['auth'] = '_priv';
				$val['privat'] = '_PRIV';
				$val['shout_table'] = $this->shoutbox_priv_table;
			break;
		}

		// Permissions and security verifications
		if (!$this->auth->acl_get('u_shout' . $val['perm']))
		{
			$this->shoutbox->shout_error('NO_VIEW' . $val['privat'] . '_PERM');
			return;
		}

		// We have our own error handling
		$this->db->sql_return_on_error(true);

		return $val;
	}

	/**
	 * Displays the rules with apropriate language
	 * @param $sort string sort of shoutbox 
	 * Return array
	 */
	public function shout_ajax_rules($sort)
	{
		$iso = $this->shoutbox->check_shout_rules($sort);
		if ($iso !== '')
		{
			$rules = $this->shoutbox->get_shout_rules();
			$text = $rules[$iso];
			if ($text['rules_text' . $sort])
			{
				$on_rules = generate_text_for_display($text['rules_text' . $sort], $text['rules_uid' . $sort], $text['rules_bitfield' . $sort], $text['rules_flags' . $sort]);
				return [
					'sort'	=> 1,
					'texte'	=> $on_rules,
				];
			}
		}

		return [
			'sort'	=> 0,
			'texte'	=> '',
		];
	}

	/**
	 * Displays list of users online
	 * Replace urls for users actions shout
	 * Return array
	 */
	public function shout_ajax_online()
	{
		$online = obtain_users_online();
		$online_strings = obtain_users_online_string($online);
		$list_online = $online_strings['online_userlist'];
		$start = $this->language->lang('REGISTERED_USERS') . ' ';

		$content = [
			'title'	=> $online_strings['l_online_users'] . '<br />(' . $this->language->lang('VIEW_ONLINE_TIMES', (int) $this->config['load_online_time']) . ')',
			'list'	=> '',
		];

		if ($list_online === $start . $this->language->lang('NO_ONLINE_USERS'))
		{
			$content['list'] = $list_online;
		}
		else
		{
			$r = $u = 0;
			$robots = $users = '';
			$userlist = explode(', ', str_replace($start, '', $list_online));
			foreach ($userlist as $user)
			{
				$id = $this->shoutbox->find_string($user, '&amp;u=', '" ');
				if (!$id)
				{
					$robots .= ($r > 0) ? ', ' : '';
					$robots .= $user;
					$r++;
				}
				else
				{
					$avatar = (strpos($user, 'class="useravatar"')) ? '<span class="useravatar">' . $this->shoutbox->find_string($user, 'class="useravatar">', '</span>') . '</span> ' : '';
					$user = str_replace($avatar, '', $user);
					$users .= ($u > 0) ? ', ' : '';
					$users .= ($avatar) ? $avatar : '';
					$users .= $this->shoutbox->construct_action_shout($id, $this->shoutbox->find_string($user, '">', '</a>'), $this->shoutbox->find_string($user, 'color: #', ';"'));
					$u++;
				}
			}
			$content['list'] .= $u . ' ' . $start;
			$content['list'] .= ($u > 0) ? $users : $this->language->lang('NO_ONLINE_USERS');
			$content['list'] .= '<hr />' . $r . ' ' . $this->language->lang('G_BOTS') . ' : ';
			$content['list'] .= ($r > 0) ? $robots : $this->language->lang('NO_ONLINE_BOTS');
		}

		return $this->shoutbox->replace_shout_url($content);
	}

	public function shout_ajax_auth($user_id, $username)
	{
		$this->language->add_lang('acp/common');
		$this->language->add_lang('permissions_shoutbox', 'sylver35/breizhshoutbox');
		$list = $this->shoutbox->list_auth_options();
		$first = '';
		$title = $data = [];
		$sort = [
			'a'	=> 'ACP_VIEW_ADMIN_PERMISSIONS',
			'm'	=> 'ACP_VIEW_GLOBAL_MOD_PERMISSIONS',
			'u'	=> 'ACP_VIEW_USER_PERMISSIONS',
		];

		for ($i = 0, $nb = sizeof($list); $i < $nb; $i++)
		{
			$second = substr($list[$i], 0, 1);
			$active = $this->auth->acl_get_list($user_id, $list[$i]);
			$title_auth = $this->language->lang($sort[$second]);
			$lang_auth = $this->language->lang('ACL_' . strtoupper($list[$i]));
			$class = ($active) ? 'auth_yes' : 'auth_no';
			$title[$i] = ($second !== $first) ? $title_auth : '';
			$data[$i] = $this->language->lang('SHOUT_OPTION_' . ($active ? 'YES' : 'NO'), $lang_auth, $class);
			$first = $second;
		}

		return [
			'nb'		=> $i,
			'title'		=> $title,
			'data'		=> $data,
			'username'	=> $this->language->lang('SHOUT_OPTION_USER', $username),
		];
	}

	public function shout_ajax_smilies()
	{
		$i = 0;
		$smilies = [];
		$sql = $this->db->sql_build_query('SELECT', [
			'SELECT'	=> 'smiley_url, MIN(smiley_id) AS smiley_id, MIN(code) AS code, MIN(smiley_order) AS min_smiley_order, MIN(smiley_width) AS smiley_width, MIN(smiley_height) AS smiley_height, MIN(emotion) AS emotion, MIN(display_on_shout) AS display_on_shout',
			'FROM'		=> [SMILIES_TABLE => ''],
			'WHERE'		=> 'display_on_shout = 1',
			'GROUP_BY'	=> 'smiley_url',
			'ORDER_BY'	=> 'min_smiley_order ASC',
		]);
		$result = $this->shoutbox->shout_sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$smilies[$i] = [
				'nb'		=> $i,
				'code'		=> (string) $row['code'],
				'emotion'	=> (string) $row['emotion'],
				'width'		=> (int) $row['smiley_width'],
				'height'	=> (int) $row['smiley_height'],
				'image'		=> (string) $row['smiley_url'],
			];
			$i++;
		}
		$this->db->sql_freeresult($result);

		$sql = 'SELECT COUNT(smiley_id) as total
			FROM ' . SMILIES_TABLE . '
				WHERE display_on_shout = 0';
		$result = $this->shoutbox->shout_sql_query($sql);
		$row_nb = $this->db->sql_fetchfield('total', $result);
		$this->db->sql_freeresult($result);

		$content = [
			'smilies'	=> $smilies,
			'total'		=> $i,
			'nb_pop'	=> (int) $row_nb,
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

	public function shout_ajax_smilies_popup($cat)
	{
		$i = 0;
		$smilies = [];

		$sql = $this->db->sql_build_query('SELECT', [
			'SELECT'	=> 'smiley_url, MIN(smiley_id) AS smiley_id, MIN(code) AS code, MIN(smiley_order) AS min_smiley_order, MIN(smiley_width) AS smiley_width, MIN(smiley_height) AS smiley_height, MIN(emotion) AS emotion, MIN(display_on_shout) AS display_on_shout',
			'FROM'		=> [SMILIES_TABLE => ''],
			'WHERE'		=> 'display_on_shout = 0',
			'GROUP_BY'	=> 'smiley_url',
			'ORDER_BY'	=> 'min_smiley_order ASC',
		]);
		$result = $this->shoutbox->shout_sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$smilies[$i] = [
				'nb'		=> $i,
				'code'		=> (string) $row['code'],
				'emotion'	=> (string) $row['emotion'],
				'width'		=> (int) $row['smiley_width'],
				'height'	=> (int) $row['smiley_height'],
				'image'		=> (string) $row['smiley_url'],
			];
			$i++;
		}
		$this->db->sql_freeresult($result);

		$content = [
			'smilies'	=> $smilies,
			'total'		=> $i,
			'nb_pop'	=> 0,
			'url'		=> $this->root_path_web . $this->config['smilies_path'] . '/',
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

	public function shout_ajax_display_smilies($smiley, $display)
	{
		$var_set = ($display === 1) ? 0 : 1;
		$sql = 'UPDATE ' . SMILIES_TABLE . " SET display_on_shout = $var_set WHERE smiley_id = $smiley";
		$this->db->sql_query($sql);
		$content = [
			'type'	=> ($display === 1) ? 1 : 2,
		];

		$i = $j = 0;
		$smilies = $smilies_pop = [];
		$sql = $this->db->sql_build_query('SELECT', [
			'SELECT'	=> 'smiley_url, MIN(smiley_id) AS smiley_id, MIN(code) AS code, MIN(smiley_order) AS min_smiley_order, MIN(smiley_width) AS smiley_width, MIN(smiley_height) AS smiley_height, MIN(emotion) AS emotion, MIN(display_on_shout) AS display_on_shout',
			'FROM'		=> [SMILIES_TABLE => ''],
			'WHERE'		=> 'display_on_shout = 1',
			'GROUP_BY'	=> 'smiley_url',
			'ORDER_BY'	=> 'min_smiley_order ASC',
		]);
		$result = $this->shoutbox->shout_sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$smilies[$i] = [
				'nb'		=> $i,
				'id'		=> (int) $row['smiley_id'],
				'code'		=> (string) $row['code'],
				'emotion'	=> (string) $row['emotion'],
				'width'		=> (int) $row['smiley_width'],
				'height'	=> (int) $row['smiley_height'],
				'image'		=> (string) $row['smiley_url'],
			];
			$i++;
		}
		$this->db->sql_freeresult($result);

		$sql = $this->db->sql_build_query('SELECT', [
			'SELECT'	=> 'smiley_url, MIN(smiley_id) AS smiley_id, MIN(code) AS code, MIN(smiley_order) AS min_smiley_order, MIN(smiley_width) AS smiley_width, MIN(smiley_height) AS smiley_height, MIN(emotion) AS emotion, MIN(display_on_shout) AS display_on_shout',
			'FROM'		=> [SMILIES_TABLE => ''],
			'WHERE'		=> 'display_on_shout = 0',
			'GROUP_BY'	=> 'smiley_url',
			'ORDER_BY'	=> 'min_smiley_order',
		]);
		$result_pop = $this->shoutbox->shout_sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result_pop))
		{
			$smilies_pop[$j] = [
				'nb'		=> $j,
				'id'		=> (int) $row['smiley_id'],
				'code'		=> (string) $row['code'],
				'emotion'	=> (string) $row['emotion'],
				'width'		=> (int) $row['smiley_width'],
				'height'	=> (int) $row['smiley_height'],
				'image'		=> (string) $row['smiley_url'],
			];
			$j++;
		}
		$this->db->sql_freeresult($result_pop);

		$content = array_merge($content, [
			'smilies'		=> $smilies,
			'smiliesPop'	=> $smilies_pop,
			'total'			=> $i,
			'totalPop'		=> $j,
			'url'			=> $this->root_path_web . $this->config['smilies_path'] . '/',
		]);

		return $content;
	}

	public function shout_ajax_question()
	{
		$guest_can_post = $this->auth->acl_get_list(ANONYMOUS, 'u_shout_post');
		return [
			'title'	=> $this->language->lang('SHOUT_COOKIES'),
			'info'	=> $this->language->lang('SHOUT_COOKIES_INFO', ($guest_can_post ? 3 : 2)),
			'robot'	=> $this->language->lang('SHOUT_COOKIES_ROBOT'),
			'sound'	=> $this->language->lang('SHOUT_COOKIES_SOUND'),
			'name'	=> ($guest_can_post) ? $this->language->lang('SHOUT_COOKIES_NAME') : '',
		];
	}

	public function shout_ajax_user_bbcode($val, $open, $close)
	{
		$text = $message = '';
		$on_user = ($val['other'] > 0) ? $val['other'] : $val['userid'];

		// Parse bbcodes
		$data = $this->shoutbox->parse_shout_bbcodes($open, $close, $on_user);
		switch ($data['sort'])
		{
			// Remove the bbcodes
			case 1:
				$sql = 'UPDATE ' . USERS_TABLE . " SET shout_bbcode = '' WHERE user_id = $on_user";
				$this->shoutbox->shout_sql_query($sql);
				$message = $this->language->lang('SHOUT_BBCODE_SUP');
				$text = $this->language->lang('SHOUT_EXEMPLE');
			break;
			// Retun error message
			case 2:
				$message = $data['message'];
			break;
			// Good ! Update the bbcodes
			case 3:
				$ok_bbcode = (string) ($open . '||' . $close);
				$options = 0;
				$uid = $bitfield = '';
				// Change it in the db
				$sql = 'UPDATE ' . USERS_TABLE . " SET shout_bbcode = '" . $this->db->sql_escape($ok_bbcode) . "' WHERE user_id = $on_user";
				$this->shoutbox->shout_sql_query($sql);
				$text = $open . $this->language->lang('SHOUT_EXEMPLE') . $close;
				generate_text_for_storage($text, $uid, $bitfield, $options, true, false, true);
				$text = generate_text_for_display($text, $uid, $bitfield, $options);
				$message = $this->language->lang('SHOUT_BBCODE_SUCCESS');
			break;
			// Return no change message
			case 4:
				$options = 0;
				$uid = $bitfield = '';
				if ($open != '1')
				{
					$text = $open . $this->language->lang('SHOUT_EXEMPLE') . $close;
					generate_text_for_storage($text, $uid, $bitfield, $options, true, false, true);
					$text = generate_text_for_display($text, $uid, $bitfield, $options);
				}
				else
				{
					$text = $this->language->lang('SHOUT_EXEMPLE');
				}
				$message = $data['message'];
			break;
			// Return error no permission
			case 5:
				$message = $data['message'];
			break;
		}

		return [
			'type'		=> $data['sort'],
			'before'	=> $open,
			'after'		=> $close,
			'on_user'	=> $on_user,
			'text'		=> $text,
			'message'	=> $message,
		];
	}

	public function shout_ajax_charge_bbcode($id)
	{
		$on_bbcode = [];
		$sql = $this->db->sql_build_query('SELECT', [
			'SELECT'	=> 'user_id, user_type, username, user_colour, shout_bbcode',
			'FROM'		=> [USERS_TABLE => ''],
			'WHERE'		=> 'user_id = ' . $id,
		]);
		$result = $this->shoutbox->shout_sql_query($sql, true, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		if ($row['shout_bbcode'])
		{
			$options = 0;
			$uid = $bitfield = '';
			$on_bbcode = explode('||', $row['shout_bbcode']);
			$message = $on_bbcode[0] . $this->language->lang('SHOUT_EXEMPLE') . $on_bbcode[1];
			generate_text_for_storage($message, $uid, $bitfield, $options, true, false, true);
			$message = generate_text_for_display($message, $uid, $bitfield, $options);
		}
		else
		{
			$on_bbcode[0] = '';
			$on_bbcode[1] = '';
			$message = $this->language->lang('SHOUT_EXEMPLE');
		}

		return [
			'id'		=> $id,
			'name'		=> $this->shoutbox->replace_shout_url(get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'])),
			'before'	=> $on_bbcode[0],
			'after'		=> $on_bbcode[1],
			'message'	=> $message,
		];
	}

	public function shout_ajax_preview_rules($rules)
	{
		$options = 0;
		$uid = $bitfield = '';
		generate_text_for_storage($rules, $uid, $bitfield, $options, true, false, true);
		$rules = $this->shoutbox->replace_shout_url(generate_text_for_display($rules, $uid, $bitfield, $options));

		return [
			'content'	=> $rules,
		];
	}

	public function shout_ajax_date_format($date)
	{
		$date = ($date == 'custom') ? $this->config['shout_dateformat'] : $date;

		return [
			'format'	=> $date,
			'date'		=> $this->user->format_date(time() - 60 * 61, $date),
			'date2'		=> $this->user->format_date(time() - 60 * 60 * 60, $date),
		];
	}

	public function shout_ajax_action_sound($on_sound)
	{
		$content = [];
		$user_shout = json_decode($this->user->data['user_shout']);
		$on_sound = ($user_shout->user == 2) ? $on_sound : $user_shout->user;
		switch ($on_sound)
		{
			// Turn on the sounds
			case 0:
				$content = [
					'type'		=> 1,
					'classOut'	=> 'button_shout_sound_off',
					'classIn'	=> 'button_shout_sound',
					'title'		=> $this->language->lang('SHOUT_CLICK_SOUND_OFF'),
				];
			break;
			// Turn off the sounds
			case 1:
				$content = [
					'type'		=> 0,
					'classOut'	=> 'button_shout_sound',
					'classIn'	=> 'button_shout_sound_off',
					'title'		=> $this->language->lang('SHOUT_CLICK_SOUND_ON'),
				];
			break;
		}

		$user_shout = [
			'user'		=> $content['type'],
			'new'		=> $user_shout->new,
			'new_priv'	=> $user_shout->new_priv,
			'error'		=> $user_shout->error,
			'del'		=> $user_shout->del,
			'add'		=> $user_shout->add,
			'edit'		=> $user_shout->edit,
			'index'		=> $user_shout->index,
			'forum'		=> $user_shout->forum,
			'topic'		=> $user_shout->topic,
		];

		$sql = 'UPDATE ' . USERS_TABLE . "
			SET user_shout = '" . $this->db->sql_escape(json_encode($user_shout)) . "'
				WHERE user_id = " . $this->user->data['user_id'];
		$this->db->sql_query($sql);

		return $content;
	}

	public function shout_ajax_cite($id)
	{
		$sql = 'SELECT user_id, user_type
			FROM ' . USERS_TABLE . '
				WHERE user_id = ' . $id;
		$result = $this->shoutbox->shout_sql_query($sql, true, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		if (!$row || $row['user_type'] == USER_IGNORE)
		{
			return [
				'type'		=> 0,
				'message'	=> $this->language->lang('NO_USER'),
			];
		}
		else
		{
			return [
				'type'		=> 1,
				'id'		=> $row['user_id'],
			];
		}
	}

	public function shout_ajax_action_user($val)
	{
		if (!$val['is_user'] || !$val['other'] || $val['other'] == ANONYMOUS)
		{
			return [
				'type'		=> 0,
				'message'	=> $this->language->lang('NO_ACTION_PERM'),
			];
		}
		else
		{
			$sql = $this->db->sql_build_query('SELECT', [
				'SELECT'	=> 'z.user_id, z.zebra_id, z.foe, u.user_id, u.user_type, u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height',
				'FROM'		=> [USERS_TABLE => 'u'],
				'LEFT_JOIN'	=> [
					[
						'FROM'	=> [ZEBRA_TABLE => 'z'],
						'ON'	=> 'u.user_id = z.zebra_id AND z.user_id = ' . $val['userid'],
					],
				],
				'WHERE'		=> 'u.user_id = ' . $val['other'],
			]);
			$result = $this->shoutbox->shout_sql_query($sql, true, 1);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);
			if (!$row)
			{
				return [
					'type'		=> 1,
				];
			}
			else if ($row['user_type'] == USER_IGNORE)
			{
				return [
					'type'		=> 2,
					'username'	=> $this->shoutbox->replace_shout_url(get_username_string('no_profile', $row['user_id'], $row['username'], $row['user_colour'])),
					'message'	=> $this->language->lang('SHOUT_USER_NONE'),
				];
			}
			else
			{
				return $this->shoutbox->action_user($row, $val['userid'], $val['sort']);
			}
		}
	}

	public function shout_ajax_action_post($val, $message)
	{
		if ($this->auth->acl_gets(['u_shout_post_inp', 'm_shout_robot', 'a_', 'm_']))
		{
			$info = 65;
			$robot = false;

			if (!$val['other'])
			{
				return [
					'type'	=> 0,
				];
			}
			else if ($val['other'] === 1)
			{
				// post a robot message
				if ($this->auth->acl_gets(['a_', 'm_shout_robot']))
				{
					$info = 0;
					$robot = true;
					$val['other'] = $val['userid'] = 0;
				}
				else
				{
					// no perm, out...
					return [
						'type'	=> 0,
					];
				}
			}
			else if ($val['other'] > 1)
			{
				// post a personal message
				$data = $this->shoutbox->shout_is_foe($val['userid'], $val['other']);
				if ($data['type'] > 0)
				{
					return [
						'type'		=> $data['type'],
						'message'	=> $data['message'],
					];
				}
			}

			$message = $this->shoutbox->parse_shout_message($message, $val['on_priv'], 'post', $robot);
			// Personalize message
			if ($val['other'] !== 0)
			{
				$message = $this->shoutbox->personalize_shout_message($message);
			}

			// will be modified by generate_text_for_storage
			$options = 0;
			$uid = $bitfield = '';
			generate_text_for_storage($message, $uid, $bitfield, $options, $this->auth->acl_get('u_shout_bbcode'), true, $this->auth->acl_get('u_shout_smilies'));

			$sql_ary = [
				'shout_text'				=> (string) $message,
				'shout_bbcode_uid'			=> $uid,
				'shout_bbcode_bitfield'		=> $bitfield,
				'shout_bbcode_flags'		=> $options,
				'shout_time'				=> (int) time(),
				'shout_user_id'				=> (int) $val['userid'],
				'shout_ip'					=> (string) $this->user->ip,
				'shout_robot_user'			=> (int) $val['other'],
				'shout_robot'				=> 0,
				'shout_forum'				=> 0,
				'shout_info'				=> (int) $info,
				'shout_inp'					=> (int) $val['other'],
			];
			$sql = 'INSERT INTO ' . $val['shout_table'] . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
			$this->db->sql_query($sql);
			$this->config->increment('shout_nr' . $val['priv'], 1, true);

			return [
				'type'		=> 1,
				'message'	=> $this->language->lang('POSTED'),
			];
		}
		else
		{
			return [
				'type'		=> 0,
				'message'	=> $this->language->lang('NO_ACTION_PERM'),
			];
		}
	}

	public function shout_ajax_action_del($val)
	{
		if ($val['other'] !== $val['userid'])
		{
			return [
				'type'		=> 0,
				'message'	=> $this->language->lang('NO_ACTION_PERM'),
			];
		}
		else
		{
			// Delete all personnal messages of this user
			$sql = 'DELETE FROM ' . $val['shout_table'] . '
				WHERE shout_user_id = ' . $val['other'] . '
					AND shout_inp <> 0';
			$this->shoutbox->shout_sql_query($sql);
			$deleted = $this->db->sql_affectedrows();
			if (!$deleted)
			{
				return [
					'type'		=> 1,
					'message'	=> $this->language->lang('SHOUT_ACTION_DEL_NO'),
				];
			}
			else
			{
				// For reload the message to everybody
				$this->shoutbox->update_shout_messages($val['shout_table']);
				$this->config->increment('shout_del_user' . $val['priv'], $deleted, true);
				return [
					'type'		=> 1,
					'message'	=> $this->language->lang('SHOUT_ACTION_DEL_REP') . ' ' . $this->language->lang($this->shoutbox->plural('NUMBER_MESSAGE', $deleted), $deleted),
				];
			}
		}
	}

	public function shout_ajax_action_del_to($val)
	{
		if ($val['other'] !== $val['userid'])
		{
			return [
				'type'		=> 0,
				'message'	=> $this->language->lang('NO_ACTION_PERM'),
			];
		}
		else
		{
			// Delete all personnal messages to this user
			$sql = 'DELETE FROM ' . $val['shout_table'] . '
				WHERE shout_inp = ' . $val['other'] . '
					AND shout_user_id <> ' . $val['other'];
			$this->shoutbox->shout_sql_query($sql);
			$deleted = $this->db->sql_affectedrows();
			if (!$deleted)
			{
				return [
					'type'		=> 1,
					'message'	=> $this->language->lang('SHOUT_ACTION_DEL_NO'),
				];
			}
			else
			{
				$this->shoutbox->update_shout_messages($val['shout_table']);
				$this->config->increment('shout_del_user' . $val['priv'], $deleted, true);
				return [
					'type'		=> 1,
					'message'	=> $this->language->lang('SHOUT_ACTION_DEL_REP') . ' ' . $this->language->lang($this->shoutbox->plural('NUMBER_MESSAGE', $deleted), $deleted),
				];
			}
		}
	}

	public function shout_ajax_action_remove($val)
	{
		if ($this->auth->acl_gets(['a_shout_manage', 'm_shout_delete']))
		{
			// Delete all messages of this user
			$sql = 'DELETE FROM ' . $val['shout_table'] . '
				WHERE shout_user_id = ' . $val['other'] . '
					OR shout_robot_user = ' . $val['other'] . '
					OR shout_inp = ' . $val['other'];
			$this->shoutbox->shout_sql_query($sql);
			$deleted = $this->db->sql_affectedrows();
			if ($deleted)
			{
				$this->shoutbox->update_shout_messages($val['shout_table']);
				$this->config->increment('shout_del_user' . $val['priv'], $deleted, true);
				return [
					'type'		=> 1,
					'message'	=> $this->language->lang('SHOUT_ACTION_REMOVE_REP') . ' ' . $this->language->lang($this->shoutbox->plural('NUMBER_MESSAGE', $deleted), $deleted),
				];
			}
			else
			{
				return [
					'type'		=> 0,
					'message'	=> $this->language->lang('SHOUT_ACTION_REMOVE_NO'),
				];
			}
		}
		else
		{
			return [
				'type'		=> 0,
				'message'	=> $this->language->lang('NO_SHOUT_DEL'),
			];
		}
	}

	public function shout_ajax_delete($val, $post)
	{
		if (!$post)
		{
			return [
				'type'		=> 3,
				'message'	=> $this->language->lang('NO_SHOUT_ID'),
			];
		}

		// If someone can delete all messages, he can delete it's messages :)
		$can_delete_all = ($this->auth->acl_gets(['m_shout_delete', 'a_shout' . $val['auth']])) ? true : false;
		$can_delete = $can_delete_all ? true : $this->auth->acl_get('u_shout_delete_s');

		$sql = 'SELECT shout_user_id
			FROM ' . $val['shout_table'] . "
				WHERE shout_id = $post";
		$result = $this->shoutbox->shout_sql_query($sql, true, 1);
		$on_id = $this->db->sql_fetchfield('shout_user_id');
		$this->db->sql_freeresult($result);

		$verify = $this->shoutbox->shout_verify_delete($val['userid'], $on_id, $can_delete_all, $can_delete);

		if (!$verify['result'])
		{
			return [
				'type'		=> 2,
				'message'	=> $this->language->lang($verify['message']),
			];
		}
		else
		{
			// Lets delete this post :D
			$sql = 'DELETE FROM ' . $val['shout_table'] . '
				WHERE shout_id = ' . $post;
			$this->db->sql_query($sql);

			$this->shoutbox->update_shout_messages($val['shout_table']);
			$this->config->increment('shout_del_user' . $val['priv'], 1, true);
			return [
				'type'	=> 1,
				'post'	=> $post,
				'sort'	=> $val['perm'],
			];
		}
	}

	public function shout_ajax_purge($val)
	{
		if (!$this->auth->acl_get('a_shout' . $val['auth']))
		{
			return [
				'type'		=> 2,
				'message'	=> $this->language->lang('NO_PURGE_PERM'),
			];
		}
		else
		{
			// First count total id
			$sql = 'SELECT COUNT(shout_id) as total
				FROM ' . $val['shout_table'];
			$result = $this->db->sql_query($sql);
			$deleted = $this->db->sql_fetchfield('total', $result);
			$this->db->sql_freeresult($result);

			// And now truncate the table, new id increment to 0
			$sql = 'TRUNCATE ' . $val['shout_table'];
			$this->db->sql_query($sql);

			$this->config->increment('shout_del_purge' . $val['priv'], $deleted, true);
			$this->shoutbox->post_robot_shout($val['userid'], $this->user->ip, $val['on_priv'], true, false, false, false);

			return [
				'type'	=> 1,
				'nr'	=> $deleted,
			];
		}
	}

	public function shout_ajax_purge_robot($val)
	{
		if (!$this->auth->acl_get('a_shout' . $val['auth']))
		{
			return [
				'type'		=> 2,
				'message'	=> $this->language->lang('NO_PURGE_ROBOT_PERM'),
			];
		}
		else
		{
			$sort_on = explode(', ', $this->config['shout_robot_choice' . $val['priv']] . ', 4');

			$sql = 'DELETE FROM ' . $val['shout_table'] . '
				WHERE ' . $this->db->sql_in_set('shout_info', $sort_on, false, true);
			$this->shoutbox->shout_sql_query($sql);
			$deleted = $this->db->sql_affectedrows();

			$this->config->increment('shout_del_purge' . $val['priv'], $deleted, true);
			$this->shoutbox->post_robot_shout($val['userid'], $this->user->ip, $val['on_priv'], true, true, false, false);

			return [
				'type'	=> 1,
				'nr'	=> $deleted,
			];
		}
	}

	public function shout_ajax_edit($val, $shout_id, $message)
	{
		if (!$this->shoutbox->shout_check_edit($val, $shout_id))
		{
			return [
				'type'		=> 1,
				'mode'		=> $val['mode'],
				'shout_id'	=> $shout_id,
				'message'	=> $this->language->lang('NO_EDIT_PERM'),
			];
		}

		// Multi protections at this time...
		$message = $this->shoutbox->parse_shout_message($message, $val['on_priv'], 'edit', false);

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

		$sql = 'UPDATE ' . $val['shout_table'] . '
			SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE shout_id = ' . $shout_id;
		$this->shoutbox->shout_sql_query($sql);

		// For reload the message to everybody
		$this->shoutbox->update_shout_messages($val['shout_table']);
		$message = generate_text_for_display($message, $uid, $bitfield, $options);

		return [
			'type'		=> 2,
			'mode'		=> $val['mode'],
			'shout_id'	=> $shout_id,
			'message'	=> $this->language->lang('EDIT_DONE'),
			'texte'		=> $message,
		];
	}

	public function shout_ajax_post($val, $message, $name, $cite)
	{
		if (!$this->auth->acl_get('u_shout_post'))
		{
			return [
				'type'		=> 2,
				'message'	=> $this->language->lang('NO_POST_PERM'),
			];
		}
		else if (!$this->shoutbox->shout_verify_flood($val['on_priv'], $val['userid']))
		{
			return [
				'type'		=> 2,
				'message'	=> $this->language->lang('FLOOD_ERROR'),
			];
		}

		// Multi protections at this time...
		$message = $this->shoutbox->parse_shout_message($message, $val['on_priv'], 'post', false);

		// Personalize message
		$message = $this->shoutbox->personalize_shout_message($message);

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

		$sql = 'INSERT INTO ' . $val['shout_table'] . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
		$this->shoutbox->shout_sql_query($sql);
		$this->config->increment('shout_nr' . $val['priv'], 1, true);
		$this->shoutbox->delete_shout_posts($val);

		return [
			'type'		=> 1,
			'mode'		=> $val['mode'],
			'cite'		=> $cite,
			'message'	=> $this->language->lang('POSTED'),
		];
	}

	public function shout_ajax_check($val, $on_bot)
	{
		$this->shoutbox->shout_run_robot(true);
		$sql_where = $this->shoutbox->shout_sql_where($val['is_user'], $val['userid'], $on_bot);
		$last_time = $this->shoutbox->get_shout_time($sql_where, $val['shout_table']);

		return [
			't'	=> $last_time,
		];
	}

	public function shout_ajax_view($val, $on_bot, $start)
	{
		$i = 0;
		$content = [
			'messages'	=> [],
		];

		$perm = $this->shoutbox->extract_permissions($val['auth']);
		$dateformat = $this->shoutbox->extract_dateformat($val['is_user']);
		$sql_where = $this->shoutbox->shout_sql_where($val['is_user'], $val['userid'], $on_bot);
		$is_mobile = $this->shoutbox->shout_is_mobile();

		$sql = $this->db->sql_build_query('SELECT', [
			'SELECT'	=> 's.*, u.user_id, u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height, u.user_type, v.user_id as x_user_id, v.username as x_username, v.user_colour as x_user_colour, v.user_avatar as x_user_avatar, v.user_avatar_type as x_user_avatar_type, v.user_avatar_width as x_user_avatar_width, v.user_avatar_height as x_user_avatar_height, v.user_type as x_user_type',
			'FROM'		=> [$val['shout_table'] => 's'],
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
		$result = $this->shoutbox->shout_sql_query($sql, true, (int) $this->config['shout_num' . $val['sort_on']], $start);
		while ($row = $this->db->sql_fetchrow($result))
		{
			// Initialize additional data
			$row = array_merge($row, [
				'is_user'		=> (($row['shout_user_id'] > 1) && ((int) $row['shout_user_id'] !== $val['userid'])),
				'name'			=> ($row['shout_user_id'] == ANONYMOUS) ? $row['shout_text2'] : $row['username'],
			]);

			// Checks permissions for delete, edit and show_ip
			$row = $this->shoutbox->get_permissions_row($row, $perm, $val);

			// Construct the content of loop
			$content['messages'][$i] = [
				'shoutId'		=> $row['shout_id'],
				'shoutTime'		=> $this->user->format_date($row['shout_time'], $dateformat),
				'username'		=> $this->shoutbox->construct_action_shout($row['user_id'], $row['name'], $row['user_colour']),
				'avatar'		=> $this->shoutbox->get_avatar_row($row, $val['sort'], $is_mobile),
				'shoutText'		=> $this->shoutbox->shout_text_for_display($row, $val['sort'], false),
				'timeMsg'		=> $row['shout_time'],
				'isUser'		=> $row['is_user'],
				'name'			=> $row['name'],
				'colour'		=> $row['user_colour'],
				'deletemsg'		=> $row['delete'],
				'edit'			=> $row['edit'],
				'showIp'		=> $row['show_ip'],
				'shoutIp'		=> $row['on_ip'],
				'msgPlain'		=> $row['msg_plain'],
			];
			$i++;
		}
		$this->db->sql_freeresult($result);

		$content = array_merge($content, [
			'total'		=> $i,
			// Get the last message time
			'last'		=> $this->shoutbox->get_shout_time($sql_where, $val['shout_table']),
			// The number of total messages for pagination
			'number'	=> $this->shoutbox->shout_pagination($sql_where, $val['shout_table'], $val['priv']),
		]);

		return $content;
	}
}
