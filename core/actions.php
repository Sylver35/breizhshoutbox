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
use sylver35\breizhshoutbox\core\work;
use phpbb\config\config;
use phpbb\db\driver\driver_interface as db;
use phpbb\auth\auth;
use phpbb\user;
use phpbb\language\language;
use phpbb\event\dispatcher_interface as phpbb_dispatcher;

class actions
{
	/* @var \sylver35\breizhshoutbox\core\shoutbox */
	protected $shoutbox;

	/* @var \sylver35\breizhshoutbox\core\work */
	protected $work;

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

	/**
	 * Constructor
	 */
	public function __construct(shoutbox $shoutbox, work $work, config $config, db $db, auth $auth, user $user, language $language, phpbb_dispatcher $phpbb_dispatcher, $root_path)
	{
		$this->shoutbox = $shoutbox;
		$this->work = $work;
		$this->config = $config;
		$this->db = $db;
		$this->auth = $auth;
		$this->user = $user;
		$this->language = $language;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->root_path = $root_path;
		$this->root_path_web = generate_board_url() . '/';
	}

	/**
	 * Displays the rules with apropriate language
	 * @param $sort string sort of shoutbox 
	 * Return array
	 */
	public function rules($sort)
	{
		$iso = $this->work->check_shout_rules($sort);
		if ($iso !== '')
		{
			$rules = $this->shoutbox->get_shout_rules();
			$text = $rules[$iso];
			if ($text['rules_text' . $sort])
			{
				return [
					'sort'	=> 1,
					'texte'	=> generate_text_for_display($text['rules_text' . $sort], $text['rules_uid' . $sort], $text['rules_bitfield' . $sort], $text['rules_flags' . $sort]),
				];
			}
		}

		return [
			'sort'	=> 0,
			'texte'	=> '',
		];
	}

	public function preview_rules($rules)
	{
		$options = 0;
		$uid = $bitfield = '';
		generate_text_for_storage($rules, $uid, $bitfield, $options, true, false, true);
		$rules = $this->shoutbox->shout_url(generate_text_for_display($rules, $uid, $bitfield, $options));

		return [
			'content'	=> $rules,
		];
	}

	public function question()
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

	public function date_format($date)
	{
		$date = ($date == 'custom') ? $this->config['shout_dateformat'] : $date;

		return [
			'format'	=> $date,
			'date'		=> $this->user->format_date(time() - 60 * 61, $date),
			'date2'		=> $this->user->format_date(time() - 60 * 60 * 60, $date),
		];
	}

	public function action_sound($on_sound)
	{
		$data = [];
		$user_shout = json_decode($this->user->data['user_shout']);
		$on_sound = ($user_shout->user == 2) ? $on_sound : $user_shout->user;
		switch ($on_sound)
		{
			// Turn on the sounds
			case 0:
				$data = [
					'type'		=> 1,
					'classOut'	=> 'button_shout_sound_off',
					'classIn'	=> 'button_shout_sound',
					'title'		=> $this->language->lang('SHOUT_CLICK_SOUND_OFF'),
				];
			break;
			// Turn off the sounds
			case 1:
				$data = [
					'type'		=> 0,
					'classOut'	=> 'button_shout_sound',
					'classIn'	=> 'button_shout_sound_off',
					'title'		=> $this->language->lang('SHOUT_CLICK_SOUND_ON'),
				];
			break;
		}

		$user_shout = [
			'user'		=> $data['type'],
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

		return $data;
	}

	public function cite($id)
	{
		$sql = 'SELECT user_id, user_type
			FROM ' . USERS_TABLE . '
				WHERE user_id = ' . $id;
		$result = $this->work->shout_sql_query($sql, true, 1);
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

	public function action_user($val)
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
			$result = $this->work->shout_sql_query($sql, true, 1);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);
			if (!$row)
			{
				return ['type' => 1];
			}
			else if ($row['user_type'] == USER_IGNORE)
			{
				return [
					'type'		=> 2,
					'username'	=> $this->work->shout_url(get_username_string('no_profile', $row['user_id'], $row['username'], $row['user_colour'])),
					'message'	=> $this->language->lang('SHOUT_USER_NONE'),
				];
			}
			else
			{
				return $this->shoutbox->action_user($row, $val['userid'], $val['sort']);
			}
		}
	}

	public function action_post($val, $message)
	{
		if ($this->auth->acl_gets(['u_shout_post_inp', 'm_shout_robot', 'a_', 'm_']))
		{
			$info = 65;
			$robot = false;

			if (!$val['other'])
			{
				return ['type' => 0];
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
					return ['type' => 0];
				}
			}
			else if ($val['other'] > 1)
			{
				// post a personal message
				$data = $this->shoutbox->user_is_foe($val['userid'], $val['other']);
				if ($data['type'] > 0)
				{
					return [
						'type'		=> $data['type'],
						'message'	=> $data['message'],
					];
				}
			}

			// Multi protections at this time...
			$message = $this->shoutbox->parse_shout_message($message, $val['priv'], $val['privat'], $robot, ($val['other'] !== 0));

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
			$sql = 'INSERT INTO ' . $val['table'] . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
			$this->db->sql_query($sql);
			$this->config->increment('shout_nr' . $val['priv'], 1, true);

			return [
				'type'		=> 1,
				'message'	=> $this->language->lang('POSTED'),
			];
		}
		else
		{
			// no perm, out...
			return [
				'type'		=> 0,
				'message'	=> $this->language->lang('NO_ACTION_PERM'),
			];
		}
	}

	public function action_del($val)
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
			$sql = 'DELETE FROM ' . $val['table'] . '
				WHERE shout_user_id = ' . $val['other'] . '
					AND shout_inp <> 0';
			$this->work->shout_sql_query($sql);
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
				$this->shoutbox->update_shout_messages($val['table']);
				$this->config->increment('shout_del_user' . $val['priv'], $deleted, true);
				return [
					'type'		=> 1,
					'message'	=> $this->language->lang('SHOUT_ACTION_DEL_REP') . ' ' . $this->language->lang('NUMBER_MESSAGE', $deleted),
				];
			}
		}
	}

	public function action_del_to($val)
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
			$sql = 'DELETE FROM ' . $val['table'] . '
				WHERE shout_inp = ' . $val['other'] . '
					AND shout_user_id <> ' . $val['other'];
			$this->work->shout_sql_query($sql);
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
				$this->shoutbox->update_shout_messages($val['table']);
				$this->config->increment('shout_del_user' . $val['priv'], $deleted, true);
				return [
					'type'		=> 1,
					'message'	=> $this->language->lang('SHOUT_ACTION_DEL_REP') . ' ' . $this->language->lang('NUMBER_MESSAGE', $deleted),
				];
			}
		}
	}

	public function action_remove($val)
	{
		if ($this->auth->acl_gets(['a_shout_manage', 'm_shout_delete']))
		{
			// Delete all messages of this user
			$sql = 'DELETE FROM ' . $val['table'] . '
				WHERE shout_user_id = ' . $val['other'] . '
					OR shout_robot_user = ' . $val['other'] . '
					OR shout_inp = ' . $val['other'];
			$this->work->shout_sql_query($sql);
			$deleted = $this->db->sql_affectedrows();
			if ($deleted)
			{
				$this->shoutbox->update_shout_messages($val['table']);
				$this->config->increment('shout_del_user' . $val['priv'], $deleted, true);
				return [
					'type'		=> 1,
					'message'	=> $this->language->lang('SHOUT_ACTION_REMOVE_REP') . ' ' . $this->language->lang('NUMBER_MESSAGE', $deleted),
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

	public function delete($val, $post)
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
			FROM ' . $val['table'] . "
				WHERE shout_id = $post";
		$result = $this->work->shout_sql_query($sql, true, 1);
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
			$sql = 'DELETE FROM ' . $val['table'] . '
				WHERE shout_id = ' . $post;
			$this->db->sql_query($sql);

			$this->shoutbox->update_shout_messages($val['table']);
			$this->config->increment('shout_del_user' . $val['priv'], 1, true);
			return [
				'type'	=> 1,
				'post'	=> $post,
				'sort'	=> $val['perm'],
			];
		}
	}

	public function purge($val)
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
				FROM ' . $val['table'];
			$result = $this->db->sql_query($sql);
			$deleted = (int) $this->db->sql_fetchfield('total');
			$this->db->sql_freeresult($result);

			// And now truncate the table, new id increment to 0
			$sql = 'TRUNCATE ' . $val['table'];
			$this->db->sql_query($sql);

			$this->config->increment('shout_del_purge' . $val['priv'], $deleted, true);
			$this->shoutbox->post_robot_shout($val['userid'], $this->user->ip, $val['on_priv'], true, false, false, false);

			return [
				'type'	=> 1,
				'nr'	=> $deleted,
			];
		}
	}

	public function purge_robot($val)
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

			$sql = 'DELETE FROM ' . $val['table'] . '
				WHERE ' . $this->db->sql_in_set('shout_info', $sort_on, false, true);
			$this->work->shout_sql_query($sql);
			$deleted = $this->db->sql_affectedrows();

			$this->config->increment('shout_del_purge' . $val['priv'], $deleted, true);
			$this->shoutbox->post_robot_shout($val['userid'], $this->user->ip, $val['on_priv'], true, true, false, false);

			return [
				'type'	=> 1,
				'nr'	=> $deleted,
			];
		}
	}
}
