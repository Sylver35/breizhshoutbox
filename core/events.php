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
use phpbb\config\config;
use phpbb\user;
use phpbb\auth\auth;
use phpbb\language\language;
use phpbb\db\driver\driver_interface as db;
use phpbb\cache\driver\driver_interface as cache;
use phpbb\event\dispatcher_interface as phpbb_dispatcher;

class events
{
	/* @var \sylver35\breizhshoutbox\core\shoutbox */
	protected $shoutbox;

	/* @var \sylver35\breizhshoutbox\core\work */
	protected $work;

	/* @var \sylver35\breizhshoutbox\core\robot */
	protected $robot;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\event\dispatcher_interface */
	protected $phpbb_dispatcher;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpEx */
	protected $php_ext;

	/**
	 * The database tables
	 *
	 * @var string */
	protected $shoutbox_table;
	protected $shoutbox_priv_table;

	/**
	 * Constructor
	 */
	public function __construct(shoutbox $shoutbox, work $work, robot $robot, config $config, user $user, auth $auth, language $language, db $db, cache $cache, phpbb_dispatcher $phpbb_dispatcher, $root_path, $php_ext, $shoutbox_table, $shoutbox_priv_table)
	{
		$this->shoutbox = $shoutbox;
		$this->work = $work;
		$this->robot = $robot;
		$this->config = $config;
		$this->user = $user;
		$this->auth = $auth;
		$this->language = $language;
		$this->db = $db;
		$this->cache = $cache;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		$this->shoutbox_table = $shoutbox_table;
		$this->shoutbox_priv_table = $shoutbox_priv_table;
	}

	/*
	 * Destroy settings file in cache for a user
	 */
	public function kill_session_shout($user_id, $session_id)
	{
		if ((int) $user_id === ANONYMOUS || $this->user->data['is_bot'])
		{
			return;
		}

		$errors = $this->work->update_session_file($user_id, 0, $session_id);
		unset($errors);
	}

	/*
	 * Destroy all old settings files in cache for a user
	 */
	public function old_user_settings($user_id, $session_id)
	{
		$this->work->destroy_old_user_settings($user_id, $session_id);
	}

	/*
	 * Destroy all settings files in cache for all users
	 */
	public function destroy_files()
	{
		$this->work->destroy_sessions_files();
	}

	/*
	 * Display infos Robot for users connections
	 */
	public function post_session_shout($data)
	{
		$go_post = $this->work->get_session_shout($this->shoutbox_table, 'shout_sessions', (int) $data['session_user_id']);
		$go_post_priv = $this->work->get_session_shout($this->shoutbox_priv_table, 'shout_sessions_priv', (int) $data['session_user_id']);

		$this->robot->insert_message_robot([
			'shout_time'				=> time(),
			'shout_user_id'				=> 0,
			'shout_ip'					=> (string) $this->user->ip,
			'shout_text'				=> 'view',
			'shout_bbcode_uid'			=> '',
			'shout_bbcode_bitfield'		=> '',
			'shout_bbcode_flags'		=> 0,
			'shout_robot'				=> 1,
			'shout_robot_user'			=> (int) $data['session_user_id'],
			'shout_forum'				=> 0,
			'shout_info'				=> 1,
		], $go_post, $go_post_priv, true);
	}

	/*
	 * Display infos Robot for bots connections
	 */
	public function post_session_bot($data)
	{
		$go_post = $this->work->get_session_shout($this->shoutbox_table, 'shout_sessions_bots', (int) $data['session_user_id']);
		$go_post_priv = $this->work->get_session_shout($this->shoutbox_priv_table, 'shout_sessions_bots_priv', (int) $data['session_user_id']);

		$this->robot->insert_message_robot([
			'shout_time'				=> time(),
			'shout_user_id'				=> 0,
			'shout_ip'					=> (string) $this->user->ip,
			'shout_text'				=> 'view',
			'shout_bbcode_uid'			=> '',
			'shout_bbcode_bitfield'		=> '',
			'shout_bbcode_flags'		=> 0,
			'shout_robot'				=> 1,
			'shout_robot_user'			=> (int) $data['session_user_id'],
			'shout_forum'				=> 0,
			'shout_info'				=> 2,
		], $go_post, $go_post_priv);
	}

	/*
	 * Display infos Robot for new posts, subjects, topics...
	 */
	public function advert_post_shoutbox($event, $forum_id)
	{
		$info = $this->sort_info($this->shoutbox->get_topic_data($event, $forum_id));
		$attribut = $this->shoutbox->qte_attr_display($event['data']['topic_id']);

		$this->robot->insert_message_robot([
			'shout_time'				=> (string) time(),
			'shout_user_id'				=> 0,
			'shout_ip'					=> (string) $this->user->ip,
			'shout_text'				=> (string) $attribut . $this->parse_web_adress($event['subject']),
			'shout_text2'				=> (string) $this->shoutbox->clean_url($event['url']),
			'shout_bbcode_uid'			=> '',
			'shout_bbcode_bitfield'		=> '',
			'shout_bbcode_flags'		=> 0,
			'shout_robot'				=> (int) $info['sort_info'],
			'shout_robot_user'			=> (int) $this->user->data['user_id'],
			'shout_forum'				=> (int) $forum_id,
			'shout_info_nb'				=> (int) $forum_id,
			'shout_info'				=> (int) $info['info'],
		], $info['ok_shout'], $info['ok_shout_priv']);
	}

	/**
	 * Update a username when it is changed
	 */
	public function shout_update_username($event)
	{
		$sql = 'SELECT user_id
			FROM ' . USERS_TABLE . "
				WHERE username = '" . $this->db->sql_escape($event['new_name']) . "'";
		$result = $this->db->sql_query($sql);
		$id = $this->db->sql_fetchfield('user_id');
		$this->db->sql_freeresult($result);

		$this->robot->insert_message_robot([
			'shout_time'				=> time(),
			'shout_user_id'				=> (int) $this->user->data['user_id'],
			'shout_ip'					=> (string) $this->user->ip,
			'shout_text'				=> (string) $event['old_name'],
			'shout_text2'				=> (string) $event['new_name'],
			'shout_bbcode_uid'			=> '',
			'shout_bbcode_bitfield'		=> '',
			'shout_bbcode_flags'		=> 0,
			'shout_robot'				=> 1,
			'shout_robot_user'			=> (int) $id,
			'shout_forum'				=> 0,
			'shout_info'				=> 22,
		], $this->config['shout_update_username'], $this->config['shout_update_username_priv']);
	}

	/*
	 * Display first connection for new users
	 */
	public function shout_add_newest_user($event)
	{
		$this->robot->insert_message_robot([
			'shout_time'				=> time(),
			'shout_user_id'				=> 0,
			'shout_ip'					=> (string) $this->user->ip,
			'shout_text'				=> (string) $event['user_row']['username'],
			'shout_bbcode_uid'			=> '',
			'shout_bbcode_bitfield'		=> '',
			'shout_bbcode_flags'		=> 0,
			'shout_robot'				=> 6,
			'shout_robot_user'			=> (int) $event['user_id'],
			'shout_forum'				=> 0,
			'shout_info'				=> 13,
		], $this->config['shout_newest'], $this->config['shout_newest_priv']);
	}

	public function remove_disallowed_bbcodes($sql_ary)
	{
		if ($this->config['shout_bbcode'])
		{
			$sql_ary['WHERE'] .= ' AND ' . $this->db->sql_in_set('b.bbcode_tag', explode(', ', $this->config['shout_bbcode']), true);
		}

		return $sql_ary;
	}

	public function modify_template_vars($event)
	{
		$hide_robot = true;
		$hide_allowed = false;
		if (!empty($this->config['shout_exclude_forums']))
		{
			$exclude = explode(',', $this->config['shout_exclude_forums']);
			if (in_array($event['forum_id'], $exclude))
			{
				$hide_robot = false;
			}
		}

		if ($this->auth->acl_get('u_shout_hide') && $hide_robot)
		{
			if ($event['mode'] == 'edit')
			{
				$hide_allowed = ($this->config['shout_edit_robot'] || $this->config['shout_edit_robot_priv']) ? true : false;
			}
			else
			{
				$hide_allowed = true;
			}
		}

		return $hide_allowed;
	}

	/**
	 * Delete all messages of a user
	 */
	public function delete_user_messages($user_id)
	{
		// Phase 1 delete messages in shoutbox table
		$this->delete_all_user_messages($user_id, $this->shoutbox_table, 'shout_del_auto');

		// Phase 2 delete messages in private shoutbox table
		$this->delete_all_user_messages($user_id, $this->shoutbox_priv_table, 'shout_del_auto_priv');
	}

	/**
	 * Delete all robot messages of a topic or of a post
	 */
	public function delete_topic_or_post($id, $sort)
	{
		$sort_id = ($sort) ? "'%&amp;t=$id%'" : "'%&amp;p=$id%'";
		// Phase 1 delete in shoutbox table
		$this->db->sql_query('DELETE FROM ' . $this->shoutbox_table . ' WHERE shout_forum <> 0 AND shout_text2 LIKE ' . $sort_id);
		if ($deleted = $this->db->sql_affectedrows())
		{
			$this->config->increment('shout_del_auto', $deleted, true);
			$this->shoutbox->update_messages($this->shoutbox_table);
		}

		// Phase 2 delete in private shoutbox table
		$this->db->sql_query('DELETE FROM ' . $this->shoutbox_priv_table . ' WHERE shout_forum <> 0 AND shout_text2 LIKE ' . $sort_id);
		if ($deleted_priv = $this->db->sql_affectedrows())
		{
			$this->config->increment('shout_del_auto_priv', $deleted_priv, true);
			$this->shoutbox->update_messages($this->shoutbox_priv_table);
		}
	}

	public function add_song_after($event)
	{
		$this->robot->insert_message_robot([
			'shout_time'				=> time(),
			'shout_user_id'				=> 0,
			'shout_ip'					=> (string) $this->user->ip,
			'shout_text'				=> (string) $event['data_video']['song_name'] . '||' . $event['data_video']['artist'],
			'shout_text2'				=> (string) $event['url'],
			'shout_bbcode_uid'			=> '',
			'shout_bbcode_bitfield'		=> '',
			'shout_bbcode_flags'		=> 0,
			'shout_robot'				=> 1,
			'shout_robot_user'			=> (int) $this->user->data['user_id'],
			'shout_info'				=> 30,
			'shout_info_nb'				=> (int) $event['id'],
		], $this->config['shout_breizhcharts_new'], false);
	}

	public function reset_all_notes($event)
	{
		$this->robot->insert_message_robot([
			'shout_time'				=> time(),
			'shout_user_id'				=> 0,
			'shout_ip'					=> '127.0.0.1',
			'shout_text'				=> (string) $event['winner']['song_name'],
			'shout_text2'				=> (string) $event['winner']['artist'] . '||' . $event['last_nb'],
			'shout_bbcode_uid'			=> '',
			'shout_bbcode_bitfield'		=> '',
			'shout_bbcode_flags'		=> 0,
			'shout_robot'				=> 1,
			'shout_robot_user'			=> 0,
			'shout_info_nb'				=> (int) $event['winner']['song_id'],
			'shout_info'				=> 31,
		], $this->config['shout_breizhcharts_reset'], false);
	}

	public function submit_new_video($event)
	{
		$this->robot->insert_message_robot([
			'shout_time'				=> time(),
			'shout_user_id'				=> (int) $this->user->data['user_id'],
			'shout_ip'					=> (string) $this->user->ip,
			'shout_text'				=> (string) $event['video_title'],
			'shout_text2'				=> (string) $event['cat_title'],
			'shout_bbcode_uid'			=> '',
			'shout_bbcode_bitfield'		=> '',
			'shout_bbcode_flags'		=> 0,
			'shout_robot'				=> (int) $event['video_cat_id'],
			'shout_robot_user'			=> (int) $this->user->data['user_id'],
			'shout_forum'				=> 0,
			'shout_info_nb'				=> (int) $event['video_id'],
			'shout_info'				=> 35,
		], $this->config['shout_video_new'], false);
	}

	public function submit_arcade_score($event, $muser)
	{
		$sup = ((int) $event['game_scoretype'] === 0) && ($event['gamescore'] > $event['mscore']);
		$inf = ((int) $event['game_scoretype'] === 1) && ($event['gamescore'] < $event['mscore']);

		if ($sup || $inf || is_null($event['mscore']) || $muser !== false)
		{
			$this->robot->insert_message_robot([
				'shout_time'				=> time(),
				'shout_user_id'				=> (int) $this->user->data['user_id'],
				'shout_ip'					=> (string) $this->user->ip,
				'shout_text'				=> (string) $event['row']['game_name'],
				'shout_text2'				=> (string) (isset($event['row']['ra_cat_title'])) ? $event['row']['ra_cat_title'] : '',
				'shout_bbcode_uid'			=> '',
				'shout_bbcode_bitfield'		=> '',
				'shout_bbcode_flags'		=> 0,
				'shout_robot'				=> (int) $event['gamescore'],
				'shout_robot_user'			=> (int) $event['row']['ra_cat_id'],
				'shout_forum'				=> 0,
				'shout_info_nb'				=> (int) $event['gid'],
				'shout_info'				=> 36,
			], $this->config['shout_arcade_new'], false);
		}
	}

	public function submit_arcade_record($event)
	{
		$this->robot->insert_message_robot([
			'shout_time'				=> time(),
			'shout_user_id'				=> (int) $this->user->data['user_id'],
			'shout_ip'					=> (string) $this->user->ip,
			'shout_text'				=> (string) $event['row']['game_name'],
			'shout_text2'				=> (string) (isset($event['row']['ra_cat_title'])) ? $event['row']['ra_cat_title'] : '',
			'shout_bbcode_uid'			=> '',
			'shout_bbcode_bitfield'		=> '',
			'shout_bbcode_flags'		=> 0,
			'shout_robot'				=> (int) $event['gamescore'],
			'shout_robot_user'			=> (int) $event['row']['ra_cat_id'],
			'shout_forum'				=> 0,
			'shout_info_nb'				=> (int) $event['gid'],
			'shout_info'				=> 38,
		], $this->config['shout_arcade_record'], false);
	}

	public function submit_arcade_urecord($event)
	{
		$this->robot->insert_message_robot([
			'shout_time'				=> time(),
			'shout_user_id'				=> (int) $this->user->data['user_id'],
			'shout_ip'					=> (string) $this->user->ip,
			'shout_text'				=> (string) $event['row']['game_name'],
			'shout_text2'				=> (string) (isset($event['row']['ra_cat_title'])) ? $event['row']['ra_cat_title'] : '',
			'shout_bbcode_uid'			=> '',
			'shout_bbcode_bitfield'		=> '',
			'shout_bbcode_flags'		=> 0,
			'shout_robot'				=> (int) $event['gamescore'],
			'shout_robot_user'			=> (int) $event['row']['ra_cat_id'],
			'shout_forum'				=> 0,
			'shout_info_nb'				=> (int) $event['gid'],
			'shout_info'				=> 37,
		], $this->config['shout_arcade_urecord'], false);
	}

	public function enable_mention_ext($event)
	{
		if ($this->work->mention_exist() && $this->auth->acl_get('u_can_mention'))
		{
			$event['parser']->enable_bbcode('mention');
			$event['parser']->enable_bbcode('smention');
		}
	}

	private function sort_info($data)
	{
		$info = 0;
		switch ($data['mode'])
		{
			case 'global':
				$info = 14;
			break;
			case 'annoucement':
				$info = 15;
			break;
			case 'post':
				$info = ($data['prez_form']) ? 60 : 16;
			break;
			case 'edit':
				$info = 17;
				if ($data['prez_form'])
				{
					$info = ($data['prez_poster']) ? 71 : 70;
				}
			break;
			case 'edit_topic':
			case 'edit_first_post':
				$info = 18;
				if ($data['prez_form'])
				{
					$info = ($data['prez_poster']) ? 73 : 72;
				}
			break;
			case 'edit_last_post':
				$info = 19;
				if ($data['prez_form'])
				{
					$info = ($data['prez_poster']) ? 75 : 74;
				}
			break;
			case 'quote':
				$info = ($data['prez_form']) ? 80 : 20;
			break;
			case 'reply':
				$info = 21;
				if ($data['prez_form'])
				{
					$info = ($data['prez_poster']) ? 77 : 76;
				}
			break;
		}

		return [
			'info'			=> $info,
			'sort_info'		=> ($info < 70) ? 2 : 3,
			'ok_shout'		=> $this->config['shout_' . $data['sort'] . '_robot'],
			'ok_shout_priv'	=> $this->config['shout_' . $data['sort'] . '_robot_priv'],
		];
	}

	private function parse_web_adress($adress)
	{
		// Parse web adress in subject to prevent bug
		return str_replace(['http://www.', 'http://', 'https://www.', 'https://', 'www.', 'Re: ', "'"], ['', '', '', '', '', '', $this->language->lang('SHOUT_PROTECT')], $adress);
	}

	private function delete_all_user_messages($user_id, $table, $sort_of)
	{
		$this->db->sql_query('DELETE FROM ' . $table . ' WHERE shout_user_id = ' . (int) $user_id);
		$deleted = $this->db->sql_affectedrows();

		$this->db->sql_query('DELETE FROM ' . $table . ' WHERE shout_inp = ' . (int) $user_id);
		$deleted += $this->db->sql_affectedrows();

		$this->db->sql_query('DELETE FROM ' . $table . ' WHERE shout_robot_user = ' . (int) $user_id);
		$deleted += $this->db->sql_affectedrows();

		if ($deleted)
		{
			$this->config->increment($sort_of, $deleted, true);
			$this->shoutbox->update_messages($table);
		}
	}
}
