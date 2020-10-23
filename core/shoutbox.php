<?php
/**
*
* @package Breizh Shoutbox Extension
* @copyright (c) 2018-2020 Sylver35  https://breizhcode.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace sylver35\breizhshoutbox\core;
use phpbb\json_response;
use phpbb\exception\http_exception;
use phpbb\cache\driver\driver_interface as cache;
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\path_helper;
use phpbb\db\driver\driver_interface as db;
use phpbb\pagination;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\auth\auth;
use phpbb\user;
use phpbb\language\language;
use phpbb\log\log;
use Symfony\Component\DependencyInjection\Container;
use phpbb\extension\manager;
use phpbb\event\dispatcher_interface as phpbb_dispatcher;

class shoutbox
{
	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\path_helper */
	protected $path_helper;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \Symfony\Component\DependencyInjection\Container */
	protected $phpbb_container;

	/** @var \phpbb\extension\manager */
	protected $ext_manager;

	/** @var \phpbb\event\dispatcher_interface */
	protected $phpbb_dispatcher;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpEx */
	protected $php_ext;

	/** @var string root path web */
	protected $root_path_web;

	/** @var string ext path */
	protected $ext_path;

	/** @var string ext path web */
	protected $ext_path_web;

	/** @var string Custom form action */
	protected $u_action;

	/**
	 * The database tables
	 *
	 * @var string */
	protected $shoutbox_table;
	protected $shoutbox_priv_table;
	protected $shoutbox_rules_table;

	/**
	 * Constructor
	 */
	public function __construct(cache $cache, config $config, helper $helper, path_helper $path_helper, db $db, pagination $pagination, request $request, template $template, auth $auth, user $user, language $language, log $log, Container $phpbb_container, manager $ext_manager, phpbb_dispatcher $phpbb_dispatcher, $root_path, $php_ext, $shoutbox_table, $shoutbox_priv_table, $shoutbox_rules_table)
	{
		$this->cache = $cache;
		$this->config = $config;
		$this->helper = $helper;
		$this->path_helper = $path_helper;
		$this->db = $db;
		$this->pagination = $pagination;
		$this->request = $request;
		$this->template = $template;
		$this->auth = $auth;
		$this->user = $user;
		$this->language = $language;
		$this->log = $log;
		$this->phpbb_container = $phpbb_container;
		$this->ext_manager = $ext_manager;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		$this->shoutbox_table = $shoutbox_table;
		$this->shoutbox_priv_table = $shoutbox_priv_table;
		$this->shoutbox_rules_table = $shoutbox_rules_table;
		$this->root_path_web = generate_board_url() . '/';
		$this->ext_path = $this->ext_manager->get_extension_path('sylver35/breizhshoutbox', true);
		$this->ext_path_web = $this->path_helper->update_web_root_path($this->ext_path);
	}

	/**
	 * Return error.
	 * @param string $message Error
	 * @return void
	 */
	public function shout_error($message, $on1 = false, $on2 = false, $on3 = false)
	{
		$response = new json_response();
		if ($this->language->is_set($message))
		{
			$message = $this->language->lang($message);
		}
		else
		{
			if ($on1 && !$on2 && !$on3)
			{
				$message = $this->language->lang($message, $on1);
			}
			else if ($on1 && $on2 && !$on3)
			{
				$message = $this->language->lang($message, $on1, $on2);
			}
			else if ($on1 && $on2 && $on3)
			{
				$message = $this->language->lang($message, $on1, $on2, $on3);
			}
		}

		$response->send(array(
			'type'		=> 10,
			'error'		=> true,
			'message'	=> $message,
		), true);
	}

	/**
	 * execute sql query or return error
	 * @param string $sql
	 * @param bool $limit
	 * @param int $nb
	 * @param int $start
	 * @return string|bool
	 */
	private function shout_sql_query($sql, $limit = false, $nb = 0, $start = 0)
	{
		if ($limit && $nb && $start)
		{
			$result = $this->db->sql_query_limit($sql, (int) $nb, (int) $start);
		}
		else if ($limit && $nb)
		{
			$result = $this->db->sql_query_limit($sql, (int) $nb);
		}
		else if ($nb)
		{
			$result = $this->db->sql_query($sql, (int) $nb);
		}
		else
		{
			$result = $this->db->sql_query($sql);
		}

		if ($result)
		{
			return $result;
		}
		else
		{
			$this->shout_sql_error($sql, __LINE__, __FILE__);
			return false;
		}
	}

	/**
	 * Prints a sql error.
	 * @param string $sql Sql query
	 * @param int $line Line number
	 * @param string $file Filename
	 * @return void
	 */
	private function shout_sql_error($sql, $line, $file)
	{
		$response = new json_response();
		$err = $this->db->sql_error();

		$response->send(array(
			'message'	=> $err['message'],
			'line'		=> $line,
			'file'		=> $file,
			'content'	=> $sql,
			'error'		=> true,
			't'			=> 1,
		), true);
	}

	/**
	 * Initialize somes variables
	 * @param string $mode mode to switch
	 * @param int $sort sort of shoutbox
	 * @param int $id id of javascript user
	 * @param int $other id of other user
	 * @return array
	 */
	public function shout_manage_ajax($mode, $sort, $id, $other)
	{
		// First initialize somes variables, protect private
		// And select the good table for the type of shoutbox
		$val = array(
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
			'board'			=> generate_board_url() . '/',
			'shout_table'	=> $this->shoutbox_table,
			'viewonline'	=> $this->auth->acl_get('u_viewonline'),
		);

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
		if (!$this->auth->acl_get("u_shout{$val['perm']}"))
		{
			$this->shout_error("NO_VIEW{$val['privat']}_PERM");
			return;
		}
		if ($val['userid'] !== $val['id'])
		{
			$this->shout_error('SERVER_ERR');
			return;
		}

		// We have our own error handling
		$this->db->sql_return_on_error(true);

		return $val;
	}

	/**
	 * Get the adm path
	 * @return string
	 */
	public function adm_path()
	{
		return $this->root_path_web . $this->path_helper->get_adm_relative_path();
	}

	/**
	 * test if the extension abbc3 is running
	 * @return bool
	 */
	public function abbc3_exist()
	{
		if ($this->phpbb_container->has('vse.abbc3.bbcodes_config'))
		{
			return true;
		}
		return false;
	}

	/**
	 * test if the extension smiliecreator is running
	 * @return bool
	 */
	public function smiliecreator_exist()
	{
		if ($this->phpbb_container->has('sylver35.smilecreator.listener'))
		{
			return true;
		}
		return false;
	}

	/**
	 * test if the extension smiliescat is running
	 * @return bool
	 */
	public function smiliescategory_exist()
	{
		if ($this->phpbb_container->has('sylver35.smiliescat.listener'))
		{
			return true;
		}
		return false;
	}

	/**
	 * test if the extension breizhyoutube is running
	 * @return bool
	 */
	public function breizhyoutube_exist()
	{
		if ($this->phpbb_container->has('sylver35.breizhyoutube.listener'))
		{
			return true;
		}
		return false;
	}

	/**
	 * test if the extension relaxarcade is running
	 * @return bool
	 */
	public function relaxarcade_exist()
	{
		if ($this->phpbb_container->has('teamrelax.relaxarcade.listener.main'))
		{
			return true;
		}
		return false;
	}

	/**
	 * Runs the cron functions if time is up
	 * Work with normal and private shoutbox
	 */
	private function execute_shout_cron($sort)
	{
		if ($sort)
		{
			$priv = '_priv';
			$private = '_PRIV';
			$shoutbox_table = $this->shoutbox_priv_table;
		}
		else
		{
			$priv = '';
			$private = '';
			$shoutbox_table = $this->shoutbox_table;
		}

		if ($this->config["shout_last_run{$priv}"] == '')
		{
			$this->config->set("shout_last_run{$priv}", time() - 86400, true);
		}
		if (($this->config["shout_last_run{$priv}"] + ($this->config["shout_prune{$priv}"] * 3600)) < time())
		{

			if ((time() - 900) <= $this->config["shout_last_run{$priv}"])
			{
				return;
			}

			if ($this->config["shout_prune{$priv}"] == '' || $this->config["shout_prune{$priv}"] == 0 || $this->config["shout_max_posts{$priv}"] > 0)
			{
				return;
			}
			else if (($this->config["shout_prune{$priv}"] > 0) && ($this->config["shout_max_posts{$priv}"] == 0))
			{
				$time = time() - ($this->config["shout_prune{$priv}"] * 3600);

				$sql = 'DELETE FROM ' . $shoutbox_table . " WHERE shout_time < '$time'";
				$this->db->sql_query($sql);
				$deleted = (int) $this->db->sql_affectedrows();
				if ($deleted > 0)
				{
					$this->config->increment("shout_del_auto{$priv}", $deleted, true);
					if ($this->config["shout_log_cron{$priv}"])
					{
						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_SHOUT' . $private . '_PURGED', time(), array($deleted));
					}
					if ($this->config['shout_delete_robot'])
					{
						$this->post_robot_shout(0, '0.0.0.0', $sort, true, false, true, false, $deleted);
					}
				}
				$this->config->set("shout_last_run{$priv}", time(), true);
			}
		}
	}

	/**
	 * Delete posts when the maximum reaches
	 */
	private function delete_shout_posts($val)
	{
		$nb_to_del = 9;
		if (!$this->config["shout_on_cron{$val['priv']}"] || $this->config["shout_max_posts{$val['priv']}"] == 0)
		{
			return;
		}

		$sql = 'SELECT COUNT(shout_id) as total
			FROM ' . $val['shout_table'];
		$result = $this->shout_sql_query($sql);
		if (!$result)
		{
			return;
		}
		$row_nb = $this->db->sql_fetchfield('total', $result);
		$this->db->sql_freeresult($result);
		
		if ($row_nb > ((int) $this->config["shout_max_posts{$val['priv']}"] + $nb_to_del))
		{
			$delete = array();
			$sql = $this->db->sql_build_query('SELECT', array(
				'SELECT'	=> 'shout_id',
				'FROM'		=> array($val['shout_table'] => ''),
				'ORDER_BY'	=> 'shout_time DESC',
			));
			$result = $this->shout_sql_query($sql, true, (int) $this->config["shout_max_posts{$val['priv']}"]);
			if (!$result)
			{
				return;
			}
			while ($row = $this->db->sql_fetchrow($result))
			{
				$delete[] = $row['shout_id'];
			}
			$this->db->sql_freeresult($result);
			$sql = 'DELETE FROM ' . $val['shout_table'] . '
				WHERE ' . $this->db->sql_in_set('shout_id', $delete, true);
			$this->db->sql_query($sql);
			$deleted = $this->db->sql_affectedrows();

			if ($this->config["shout_log_cron{$val['priv']}"])
			{
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_SHOUT' . $val['priv'] . '_REMOVED', time(), array($deleted));
			}
			$this->config->set("shout_del_auto{$val['priv']}", $deleted, true);
			if ($this->config['shout_delete_robot'])
			{
				$this->post_robot_shout(0, '0.0.0.0', $val['on_priv'], true, false, true, true, $deleted);
			}
		}
	}

	/*
	 * Change time of the last message to one second +
	 * to update the shoutbox for all users
	 */
	public function update_shout_messages($shoutbox_table)
	{
		$sql = 'UPDATE ' . $shoutbox_table . '
			SET shout_time = shout_time + 1
			ORDER BY shout_id DESC';
		$this->db->sql_query_limit($sql, 1);
	}

	public function get_version($version = false)
	{
		if (($data = $this->cache->get('_shout_version')) === false)
		{
			$md_manager = $this->ext_manager->create_extension_metadata_manager('sylver35/breizhshoutbox');
			$meta = $md_manager->get_metadata();

			$data = array(
				'version'	=> $meta['version'],
				'homepage'	=> $meta['homepage'],
			);
			// cache for 7 days
			$this->cache->put('_shout_version', $data, 604800);
		}

		if ($version)
		{
			return $data['version'];
		}
		else
		{
			return $data;
		}
	}

	/**
	 * Check if the rules with apropriate language exist
	 */
	private function check_shout_rules($sort)
	{
		if ($this->config['shout_rules'])
		{
			$iso = $this->user->lang_name;
			if ($this->config->offsetExists("shout_rules{$sort}_{$iso}"))
			{
				if ($this->config["shout_rules{$sort}_{$iso}"])
				{
					return $iso;
				}
			}
			else
			{
				if ($this->config->offsetExists("shout_rules{$sort}_en"))
				{
					if ($this->config["shout_rules{$sort}_en"])
					{
						return 'en';
					}
				}
			}
		}
		return '';
	}

	/**
	 * Get the rules from the cache
	 */
	private function get_shout_rules()
	{
		if (($rules = $this->cache->get('_shout_rules')) === false)
		{
			$sql_ary = array(
				'SELECT'	=> 'l.lang_iso, r.*',
				'FROM'		=> array(LANG_TABLE => 'l'),
				'LEFT_JOIN'	=> array(
					array(
						'FROM'	=> array($this->shoutbox_rules_table => 'r'),
						'ON'	=> 'r.rules_lang = l.lang_iso',
					),
				),
			);
			$result = $this->shout_sql_query($this->db->sql_build_query('SELECT', $sql_ary));
			if (!$result)
			{
				return;
			}
			while ($row = $this->db->sql_fetchrow($result))
			{
				$rules[$row['lang_iso']] = array(
					'rules_id'				=> $row['id'],
					'rules_text'			=> $row['rules_text'],
					'rules_uid'				=> $row['rules_uid'],
					'rules_bitfield'		=> $row['rules_bitfield'],
					'rules_flags'			=> $row['rules_flags'],
					'rules_text_priv'		=> $row['rules_text_priv'],
					'rules_uid_priv'		=> $row['rules_uid_priv'],
					'rules_bitfield_priv'	=> $row['rules_bitfield_priv'],
					'rules_flags_priv'		=> $row['rules_flags_priv'],
				);
			}
			$this->db->sql_freeresult($result);

			// cache for 7 days
			$this->cache->put('_shout_rules', $rules, 604800);
		}

		return $rules;
	}

	/**
	 * Displays the rules with apropriate language
	 * @param $sort string sort of shoutbox 
	 * Return array
	 */
	public function shout_ajax_rules($sort)
	{
		$content = array(
			'sort'	=> 0,
			'texte'	=> '',
		);

		$iso = $this->check_shout_rules($sort);
		if ($iso !== '')
		{
			$rules = $this->get_shout_rules();
			$text = $rules[$iso];
			if ($text["rules_text{$sort}"])
			{
				if (!function_exists('generate_text_for_display'))
				{
					include($this->root_path . 'includes/functions_content.' . $this->php_ext);
				}
				$on_rules = generate_text_for_display($text["rules_text{$sort}"], $text["rules_uid{$sort}"], $text["rules_bitfield{$sort}"], $text["rules_flags{$sort}"]);
				$content = array(
					'sort'	=> 1,
					'texte'	=> $on_rules,
				);
			}
		}

		return $content;
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

		$content = array(
			'title'	=> $online_strings['l_online_users'] . '<br />(' . $this->language->lang('VIEW_ONLINE_TIMES', (int) $this->config['load_online_time']) . ')',
		);

		if ($list_online == $this->language->lang('NO_ONLINE_USERS'))
		{
			$content['liste'] = $list_online;
		}
		else if (strpos($list_online, 'avatar') !== false)
		{
			$content['liste'] = $list_online;
		}
		else
		{
			$i = 0;
			$content['liste'] = $this->language->lang('REGISTERED_USERS') . ' ';
			$userlist = explode(', ', str_replace($content['liste'], '', $list_online));
			foreach ($userlist as $user)
			{
				$content['liste'] .= ($i > 0) ? ', ' : '';
				$id = $this->find_string($user, '&amp;u=', '" ');
				if (!$id || $id == $this->user->data['user_id'])
				{
					$content['liste'] .= $this->replace_shout_url($user);
				}
				else
				{
					$username = $this->find_string($user, '">', '</a>');
					$colour = $this->find_string($user, 'color: #', ';"');
					$content['liste'] .= $this->construct_action_shout($id, $username, $colour);
				}
				$i++;
			}
		}

		return $content;
	}

	/**
	 * Extract information from a string
	 *
	 * @param $string	string where search in
	 * @param $start	string start of search
	 * @param $end		string end of search
	 * Return string or int
	 */
	private function find_string($string, $start, $end)
	{
		$ini = strpos($string, $start);
		if ($ini == 0)
		{
			return $ini;
		}
		$ini += strlen($start);
		$len = strpos($string, $end, $ini) - $ini;
		$value = substr($string, $ini, $len);

		return $value;
	}

	/**
	 * Delete all messages of a user
	 */
	public function delete_user_messages($user_id)
	{
		$user_id = (int) $user_id;
		// Phase 1 delete messages in shoutbox table
		$sql = 'DELETE FROM ' . $this->shoutbox_table . "
			WHERE shout_user_id = $user_id
				OR shout_robot_user = $user_id
				OR shout_inp = $user_id";
		$this->db->sql_query($sql);
		$deleted = $this->db->sql_affectedrows();
		if ($deleted)
		{
			$this->config->increment('shout_del_auto', $deleted, true);
			$this->update_shout_messages($this->shoutbox_table);
		}

		// Phase 2 delete messages in private shoutbox table
		$sql = 'DELETE FROM ' . $this->shoutbox_priv_table . "
			WHERE shout_user_id = $user_id
				OR shout_robot_user = $user_id
				OR shout_inp = $user_id";
		$this->db->sql_query($sql);
		$deleted = $this->db->sql_affectedrows();
		if ($deleted)
		{
			$this->config->increment('shout_del_auto_priv', $deleted, true);
			$this->update_shout_messages($this->shoutbox_priv_table);
		}
	}

	/**
	 * Delete all robot messages of a topic
	 */
	public function shout_delete_topic($topic_id)
	{
		$topic_id = (int) $topic_id;
		// Phase 1 delete messages in shoutbox table
		$sql = 'DELETE FROM ' . $this->shoutbox_table . "
			WHERE shout_forum <> 0
				AND shout_text2 LIKE '%&amp;t=$topic_id%'";
		$this->db->sql_query($sql);
		$deleted = $this->db->sql_affectedrows();
		if ($deleted)
		{
			$this->config->increment('shout_del_auto', $deleted, true);
			$this->update_shout_messages($this->shoutbox_table);
		}

		// Phase 2 delete messages in private shoutbox table
		$sql = 'DELETE FROM ' . $this->shoutbox_priv_table . "
			WHERE shout_forum <> 0
				AND shout_text2 LIKE '%&amp;t=$topic_id%'";
		$this->db->sql_query($sql);
		$deleted = $this->db->sql_affectedrows();
		if ($deleted)
		{
			$this->config->increment('shout_del_auto_priv', $deleted, true);
			$this->update_shout_messages($this->shoutbox_priv_table);
		}
	}

	/**
	 * Delete all robot messages of a post
	 */
	public function shout_delete_post($post_id)
	{
		$post_id = (int) $post_id;
		// Phase 1 delete messages in shoutbox table
		$sql = 'DELETE FROM ' . $this->shoutbox_table . "
			WHERE shout_forum <> 0
				AND shout_text2 LIKE '%&amp;p=$post_id%'";
		$this->db->sql_query($sql);
		$deleted = $this->db->sql_affectedrows();
		if ($deleted)
		{
			$this->config->increment('shout_del_auto', $deleted, true);
			$this->update_shout_messages($this->shoutbox_table);
		}

		// Phase 2 delete messages in private shoutbox table
		$sql = 'DELETE FROM ' . $this->shoutbox_priv_table . "
			WHERE shout_forum <> 0
				AND shout_text2 LIKE '%&amp;p=$post_id%'";
		$this->db->sql_query($sql);
		$deleted = $this->db->sql_affectedrows();
		if ($deleted)
		{
			$this->config->increment('shout_del_auto_priv', $deleted, true);
			$this->update_shout_messages($this->shoutbox_priv_table);
		}
	}

	/**
	 * Display the shoutbox
	 */
	public function shout_display($sort_of)
	{
		// Protection for private and define sort of shoutbox
		$is_user = ($this->user->data['is_registered'] && !$this->user->data['is_bot']) ? true : false;
		$in_priv = ($sort_of === 3) ? true : false;
		$priv = ($in_priv) ? '_priv' : '';
		$private = ($in_priv) ? '_priv' : '_view';

		if (!$this->auth->acl_get("u_shout{$private}"))
		{
			$this->template->assign_vars(array(
				'S_DISPLAY_SHOUTBOX'	=> false,
			));
			return;
		}
		else if ($in_priv)
		{
			// Always post enter info in the private shoutbox -> toc toc toc, it's me ;)
			$this->post_robot_shout($this->user->data['user_id'], $this->user->ip, true, false, false, false, false);
		}

		// Define the username for anonymous here
		if (!$this->user->data['is_registered'])
		{
			$this->language->add_lang('ucp');
			$this->template->assign_vars(array(
				'SHOUT_USERNAME_EXPLAIN'	=> $this->language->lang($this->config['allow_name_chars'] . '_EXPLAIN', $this->language->lang('CHARACTERS', (int) $this->config['min_name_chars']), $this->language->lang('CHARACTERS', (int) $this->config['max_name_chars'])),
			));
			// Add form token for login box
			add_form_key('login', '_LOGIN');
		}
		else if ($is_user)
		{
			// Load the user's preferences
			$user_shout = json_decode($this->user->data['user_shout']);
			if ($user_shout->index != 3)
			{
				$this->config['shout_index'] = ($user_shout->index != '0') ? true : false;
				$this->config['shout_forum'] = ($user_shout->forum != '0') ? true : false;
				$this->config['shout_topic'] = ($user_shout->topic != '0') ? true : false;
				$this->config['shout_position_index'] = (int) $user_shout->index;
				$this->config['shout_position_forum'] = (int) $user_shout->forum;
				$this->config['shout_position_topic'] = (int) $user_shout->topic;
			}
		}

		if (!$this->stop_shout_display($this->config['shout_index'], $this->config['shout_forum'], $this->config['shout_topic']))
		{
			return;
		}

		// Active lateral panel or not
		$panel = false;
		if ($this->auth->acl_get('u_shout_lateral'))
		{
			// Activate it in private shoutbox
			if ($in_priv)
			{
				// Force autoload here
				$this->config['shout_panel_auto'] = true;
				$panel = true;
			}
			else
			{
				// And verifie in another pages
				$panel = ($this->config['shout_panel'] && $this->config['shout_panel_all']) ? true : false;
			}
		}

		$this->template->assign_vars(array(
			'S_DISPLAY_SHOUTBOX'	=> true,
			'COLOR_PANEL'			=> 3,
			'IN_SHOUT_POPUP'		=> ($sort_of === 1) ? true : false,
			'PANEL_ALL'				=> $panel,
			'S_IN_PRIV'				=> $in_priv,
			'ACTION_USERS_TOP'		=> ($this->auth->acl_get('u_shout_post_inp') || $this->auth->acl_get('a_') || $this->auth->acl_get('m_')) ? true : false,
			'INDEX_SHOUT'			=> $this->config['shout_index'],
			'FORUM_SHOUT'			=> $this->config['shout_forum'],
			'TOPIC_SHOUT'			=> $this->config['shout_topic'],
			'INDEX_SHOUT_TOP'		=> ((int) $this->config['shout_position_index'] === 1) ? true : false,
			'INDEX_SHOUT_AFTER'		=> ((int) $this->config['shout_position_index'] === 4) ? true : false,
			'INDEX_SHOUT_END'		=> ((int) $this->config['shout_position_index'] === 2) ? true : false,
			'POS_SHOUT_FORUM_TOP'	=> ((int) $this->config['shout_position_forum'] === 1) ? true : false,
			'POS_SHOUT_FORUM_END'	=> ((int) $this->config['shout_position_forum'] === 2) ? true : false,
			'POS_SHOUT_TOPIC_TOP'	=> ((int) $this->config['shout_position_topic'] === 1) ? true : false,
			'POS_SHOUT_TOPIC_END'	=> ((int) $this->config['shout_position_topic'] === 2) ? true : false,
			'SHOUT_EXT_PATH'		=> $this->ext_path_web,
			'S_SHOUT_VERSION'		=> $this->get_version(true),
		));

		$this->shout_charge_posting();
		$this->javascript_shout($sort_of);

		// Do the shoutbox Prune thang
		if ($this->config["shout_on_cron{$priv}"] && ($this->config["shout_max_posts{$priv}"] == 0))
		{
			$this->execute_shout_cron($in_priv);
		}
		$this->shout_run_robot(true);
	}

	private function stop_shout_display($index, $forum, $topic)
	{
		$stop = true;
		$page = str_replace('.' . $this->php_ext, '', $this->user->page['page_name']);

		if ($page === 'index')
		{
			$stop = $index;
		}
		else if ($page === 'viewforum')
		{
			$stop = $forum;
		}
		else if ($page === 'viewtopic')
		{
			$stop = $topic;
		}

		return $stop;
	}

	private function shout_charge_posting()
	{
		if ($this->auth->acl_get('u_shout_post') && $this->auth->acl_get('u_shout_bbcode'))
		{
			$this->language->add_lang('posting');
			$this->template->assign_vars(array(
				'SHOUT_POSTING'			=> true,
				'S_BBCODE_ALLOWED'		=> true,
				'S_BBCODE_IMG'			=> true,
				'S_LINKS_ALLOWED'		=> true,
				'S_BBCODE_QUOTE'		=> true,
				'S_BBCODE_FLASH'		=> false,
				'TEXT_USER_TOP'			=> ($this->auth->acl_get('u_shout_bbcode_change')) ? true : false,
			));

			if (!function_exists('display_custom_bbcodes'))
			{
				include($this->root_path . 'includes/functions_display.' . $this->php_ext);
			}
			// Build custom bbcodes array
			display_custom_bbcodes();
		}
	}

	public function remove_disallowed_bbcodes($sql_ary)
	{
		$disallowed_bbcodes = explode(', ', $this->config['shout_bbcode']);
		if (!empty($disallowed_bbcodes))
		{
			$sql_ary['WHERE'] .= ' AND ' . $this->db->sql_in_set('b.bbcode_tag', $disallowed_bbcodes, true);
		}

		return $sql_ary;
	}

	/**
	 * Search compatibles browsers
	 * To display correctly the shout
	 * Return bool
	 */
	public function shout_is_mobile()
	{
		$browser = strtolower($this->user->browser);

		if (!empty($browser))
		{
			if (preg_match("#ipad|tablet#i", $browser))
			{
				return false;
			}
			else if (preg_match("#mobile|android|iphone|mobi|ipod|fennec|webos|j2me|midp|cdc|cdlc|bada#i", $browser))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Displays the retractable lateral panel
	 */
	public function shout_panel()
	{
		if (!$this->auth->acl_get('u_shout_lateral') || $this->user->data['is_bot'] || $this->config['board_disable'])
		{
			$this->template->assign_vars(array(
				'KILL_LATERAL'	=> true,
				'ACTIVE_PANEL'	=> false,
				'S_IS_BOT'		=> $this->user->data['is_bot'],
			));
			return false;
		}
		// Display only if we are not in excluded page
		if (!$this->kill_lateral_on())
		{
			$this->template->assign_vars(array(
				'KILL_LATERAL'	=> true,
			));
			return false;
		}
		else
		{
			if ($this->user->data['is_registered'])
			{
				$user_shoutbox = json_decode($this->user->data['user_shoutbox']);
				$this->config['shout_panel_float'] = $this->set_user_option((bool) $user_shoutbox->panel_float, 'shout_panel_float', 3);
			}
			$this->template->assign_vars(array(
				'S_IN_SHOUT_POP'	=> true,
				'S_IN_PRIV'			=> false,
				'ACTIVE_PANEL'		=> true,
				'S_IS_BOT'			=> false,
				'AUTO_PANEL'		=> $this->config['shout_panel_auto'] ? true : false,
				'PANEL_FLOAT'		=> $this->config['shout_panel_float'] ? 'left' : 'right',
				'PANEL_OPEN'		=> $this->ext_path_web . 'images/panel/' . $this->config['shout_panel_img'],
				'PANEL_CLOSE'		=> $this->ext_path_web . 'images/panel/' . $this->config['shout_panel_exit_img'],
				'PANEL_WIDTH'		=> $this->config['shout_panel_width'] . 'px',
				'PANEL_HEIGHT'		=> $this->config['shout_panel_height'] . 'px',
				'U_SHOUT_LATERAL'	=> $this->helper->route('sylver35_breizhshoutbox_lateral'),
				'S_SHOUT_VERSION'	=> $this->get_version(true),
			));
			return true;
		}
	}

	/*
	 * Function for display or not the lateral panel
	 * based on page list in config
	 * Never display it for mobile phones (ipad ok)
	 * Return true or false
	 */
	public function kill_lateral_on()
	{
		if (!$this->auth->acl_get('u_shout_lateral') || $this->user->data['is_bot'])
		{
			// No permission
			return false;
		}
		else if (!$this->user->data['is_registered'] && !$this->config['shout_panel'])
		{
			return false;
		}
		else if ($this->shout_is_mobile())
		{
			// Not for mobile browsers (not ipad)
			return false;
		}
		// Registred users can set this option
		else if ($this->user->data['is_registered'])
		{
			$set_option = false;
			$user_shoutbox = json_decode($this->user->data['user_shoutbox']);
			if ($user_shoutbox->panel != 3)
			{
				if ((int) $user_shoutbox->panel === 0)
				{
					return false;
				}
				else
				{
					$set_option = true;
				}
			}
			if (!$this->config['shout_panel'] && !$set_option)
			{
				return false;
			}
		}

		$is_param = $_page = $param = false;
		// Exclude all pages in this list
		if (preg_match("#ucp|mcp|search#i", $this->user->page['page_name']) || preg_match("#adm#i", $this->user->page['page_dir']))
		{
			return false;
		}
		$exclude_list = str_replace('&amp;', '&', $this->config['shout_page_exclude']);
		if ($exclude_list != '')
		{
			$on_page = ($this->user->page['page_dir'] ? $this->user->page['page_dir'] . '/' : '') . $this->user->page['page_name'] . ($this->user->page['query_string'] ? '?' . $this->user->page['query_string'] : '');
			$on_page1 = ($this->user->page['page_dir'] ? $this->user->page['page_dir'] . '/' : '') . $this->user->page['page_name'];
			$pages = explode('||', $exclude_list);
			foreach ($pages as $page)
			{
				$page = str_replace('app.php/', '', $page);
				$query_string = ($this->user->page['query_string']) ? explode('&', $this->user->page['query_string']) : '-';
				if (preg_match("#{$page}#i", $this->user->page['page_name']))
				{
					return false;
				}
				else if (strpos($page, '?') !== false)
				{
					$is_param = true;
					list($_page, $param) = explode('?', $page);
				}

				if (!$is_param)
				{
					// exclude all pages with or without parameters
					if ($on_page1 == $_page)
					{
						return false;
					}
				}
				else
				{
					if (empty($this->user->page['query_string']))
					{
						if ($on_page == $page)
						{
							return false;
						}
					}
					else
					{
						if ($on_page1 == $_page && ($this->user->page['query_string'] == $param || $query_string[0] == $param))
						{
							return false;
						}
					}
				}
			}
		}

		// Ok, let's go to display it baby (^_^)
		return true;
	}

	/*
	 * Ignore info robot in forum messages
	 * Return bool
	 */
	public function shout_post_hide($mode, $s_hide_robot)
	{
		if ($this->auth->acl_get('u_shout_hide') && $s_hide_robot)
		{
			if ($mode == 'edit' && !$this->config['shout_edit_robot'] && !$this->config['shout_edit_robot_priv'])
			{
				return false;
			}
			else
			{
				return true;
			}
		}
		else
		{
			return false;
		}
	}

	/*
	 * Personalize message before submit
	 * Return string
	 */
	public function personalize_shout_message($message)
	{
		if ($this->user->data['shout_bbcode'] && $this->auth->acl_get('u_shout_bbcode_change'))
		{
			list($open, $close) = explode('||', $this->user->data['shout_bbcode']);
			// Don't personalize if somes bbcodes are presents
			if (strpos($message, '[spoil') !== false || strpos($message, '[hidden') !== false || strpos($message, '[offtopic') !== false || strpos($message, '[mod=') !== false || strpos($message, '[quote') !== false || strpos($message, '[code') !== false || strpos($message, '[list') !== false)
			{
				return $message;
			}
			return $open . $message . $close;
		}
		else
		{
			return $message;
		}
	}

	/*
	 * Parse bbcodes in personalisation
	 * before submit
	 * Return array
	 */
	private function parse_shout_bbcodes($open, $close, $other)
	{
		// Return error no permission for change personalisation of another
		if ($other > 0 && ($other != $this->user->data['user_id']))
		{
			if (!$this->auth->acl_get('a_') && !$this->auth->acl_get('m_'))
			{
				return array(
					'sort'		=> 5,
					'message'	=> $this->language->lang('NO_SHOUT_PERSO_PERM'),
				);
			}
		}

		// prepare the list
		$open = str_replace('][', '], [', $open);
		$close = str_replace('][', '], [', $close);
		// explode it
		$array_open = explode(', ', $open);
		$array_close = explode(', ', $close);
		// for this user or an another?
		if ($other > 0)
		{
			$sql = 'SELECT shout_bbcode
				FROM ' . USERS_TABLE . '
					WHERE user_id = ' . (int) $other;
			$result = $this->db->sql_query($sql);
			$shout_bbcode = $this->db->sql_fetchfield('shout_bbcode');
			$this->db->sql_freeresult($result);
		}
		else
		{
			$shout_bbcode = $this->user->data['shout_bbcode'];
		}

		// Any modification
		if ($open == 1 && $close == 1)
		{
			if ($shout_bbcode)
			{
				return array(
					'sort'		=> 1,
				);
			}
			else
			{
				return array(
					'sort'		=> 4,
					'message'	=> $this->language->lang('SHOUT_BBCODE_ERROR_SHAME'),
				);
			}
		}
		else if (($open == '' && $close != '') || ($open != '' && $close == ''))
		{
			// If one is empty
			return array(
				'sort'		=> 2,
				'message'	=> $this->language->lang('SHOUT_BBCODE_ERROR'),
			);
		}
		else if (sizeof($array_open) != sizeof($array_close))
		{
			// If the number of bbcodes opening and closing is different
			return array(
				'sort'		=> 2,
				'message'	=> $this->language->lang('SHOUT_BBCODE_ERROR_COUNT'),
			);
		}
		else if (!preg_match("#^\[|\[|\]|\]$#", $open) || !preg_match("#^\[|\[|\[/|\]|\]$#", $close))
		{
			// If a square bracket is absent
			return array(
				'sort'		=> 2,
				'message'	=> $this->language->lang('SHOUT_BBCODE_ERROR_COUNT'),
			);
		}
		else
		{
			// Initalise closing of bbcodes and correct imbrication
			$s = $n = 0;
			$slash = $sort = array();
			$reverse_open = array_reverse($array_open);
			for ($i = 0, $nb = sizeof($reverse_open); $i < $nb; $i++)
			{
				$first = substr($reverse_open[$i], 0, strlen($array_close[$i]) - 2) . ']';
				if (strpos($array_close[$i], '[/') === false)
				{
					$slash[] = $array_close[$i];
					$s++;
				}
				else if ($first != str_replace('/', '', $array_close[$i]))
				{
					$sort[] = $array_close[$i];
					$n++;
				}
				else
				{
					continue;
				}
			}
			// Check closing of bbcodes
			if ($s)
			{
				$slash = implode(', ', $slash);
				return array(
					'sort'		=> 2,
					'message'	=> $this->language->lang($this->plural('SHOUT_BBCODE_ERROR_SLASH', $s), $s, $slash),
				);
			}
			// Check the correct imbrication of bbcodes
			if ($n)
			{
				$sort = implode(', ', $sort);
				return array(
					'sort'		=> 2,
					'message'	=> $this->language->lang($this->plural('SHOUT_BBCODE_ERROR_IMB', $n), $n, $sort),
				);
			}
			// Check opening and closing of bbcodes
			if ($shout_bbcode)
			{
				$bbcode = explode('||', $shout_bbcode);
				if (str_replace('][', '], [', $bbcode[0]) == $open && str_replace('][', '], [', $bbcode[1]) == $close)
				{
					return array(
						'sort'		=> 4,
						'message'	=> $this->language->lang('SHOUT_BBCODE_ERROR_SHAME'),
					);
				}
			}
			// See for unautorised bbcodes
			$other_bbcode = ($this->config['shout_bbcode']) ? ', ' . $this->config['shout_bbcode'] : '';
			$bbcode_array = explode(', ', $this->config['shout_bbcode_user'] . $other_bbcode);
			foreach ($bbcode_array as $no)
			{
				if (strpos($close, "[/{$no}]") !== false)
				{
					return array(
						'sort'		=> 2,
						'message'	=> $this->language->lang('SHOUT_NO_CODE', "[{$no}][/{$no}]"),
					);
				}
			}
			// Limit font size for no admin
			if (strpos($open, '[size=') !== false && !$this->auth->acl_get('a_'))
			{
				$all = explode(', ', $open);
				foreach ($all as $is)
				{
					if (preg_match('/size=/i', $is))
					{
						$size = str_replace(array('[', 'size=', ']'), '', $is);
						if ($size > $this->config['shout_bbcode_size'])
						{
							return array(
								'sort'		=> 2,
								'message'	=> $this->language->lang('MAX_FONT_SIZE_EXCEEDED', $this->config['shout_bbcode_size']),
							);
						}
					}
					else
					{
						continue;
					}
				}
			}
			// No video here !
			$video_array = array('flash', 'swf', 'mp4', 'mts', 'avi', '3gp', 'asf', 'flv', 'mpeg', 'video', 'embed', 'BBvideo', 'scrippet', 'quicktime', 'ram', 'gvideo', 'youtube', 'veoh', 'collegehumor', 'dm', 'gamespot', 'gametrailers', 'ignvideo', 'liveleak');
			foreach ($video_array as $video)
			{
				if (strpos($open, '[' . $video) !== false || strpos($open, '<' . $video) !== false)
				{
					return array(
						'sort'		=> 2,
						'message'	=> $this->language->lang('SHOUT_NO_VIDEO'),
					);
				}
				else
				{
					continue;
				}
			}

			// If all is ok, return 3
			return array(
				'sort'	=> 3,
			);
		}
	}

	/*
	 * Parse message before submit
	 * Prevent some hacking too...
	 */
	private function parse_shout_message($message, $sort_shout = false, $mode = 'post', $robot = false)
	{
		$priv = (!$sort_shout) ? '' : '_priv';
		$on_priv = (!$sort_shout) ? '' : '_PRIV';
		// Set the minimum of caracters to 1 in a message to parse all the time here...
		// This will not alter the minimum in the post form...
		$this->config['min_post_chars'] = 1;

		// Delete enter message before if present...
		if (strpos($message, $this->language->lang('SHOUT_AUTO')) !== false)
		{
			$message = str_replace($this->language->lang('SHOUT_AUTO'), '', $message);
		}
		// Never post an empty message (with bbcode or not)
		if (empty($message) || empty(preg_replace("(\[.+?\])is", '', $message)))
		{
			$this->shout_error('MESSAGE_EMPTY');
			return;
		}
		// Don't parse img if unautorised and return img url only
		if ((strpos($message, '[/img]') !== false) && !$this->auth->acl_get('u_shout_image'))
		{
			$message = str_replace(array('[img]', '[/img]'), '', $message);
		}
		// Correct a bug with somes empty bbcodes
		if ($message == '[img][/img]' || $message == '[b][/b]' || $message == '[i][/i]' || $message == '[u][/u]' || $message == '[url][/url]')
		{
			$this->shout_error('MESSAGE_EMPTY');
			return;
		}
		$message = str_replace(array('/]', '&amp;amp;'), array(']', '&'), $message);

		// Verify message length...
		// Permission to ignore the limit of characters in a message
		if (!$this->auth->acl_get('u_shout_limit_post') && $this->config['shout_max_post_chars'])
		{
			$message_length = ($mode == 'post') ? mb_strlen($message, 'utf-8') : mb_strlen(preg_replace('#\[\/?[a-z\*\+\-]+(=[\S]+)?\]#ius', ' ', $message), 'utf-8');
			if ($message_length > $this->config['shout_max_post_chars'])
			{
				$this->shout_error('TOO_MANY_CHARS_POST', $message_length, $this->config['shout_max_post_chars']);
				return;
			}
		}
		// See for unautorised bbcodes
		$bbcode_array = explode(', ', $this->config['shout_bbcode']);
		foreach ($bbcode_array as $no)
		{
			if (strpos($message, "[/{$no}]") !== false)
			{
				$this->shout_error('SHOUT_NO_CODE', "[{$no}][/{$no}]");
				return;
			}
		}
		// No video!
		$video_array = array('flash', 'swf', 'mp4', 'mts', 'avi', '3gp', 'asf', 'flv', 'mpeg', 'video', 'embed', 'BBvideo', 'scrippet', 'quicktime', 'ram', 'gvideo', 'youtube', 'veoh', 'collegehumor', 'dm', 'gamespot', 'gametrailers', 'ignvideo', 'liveleak');
		foreach ($video_array as $video)
		{
			if ((strpos($message, '[' . $video) !== false && strpos($message, '[/' . $video) !== false) || (strpos($message, '<' . $video) !== false && strpos($message, '</' . $video) !== false))
			{
				$this->shout_error('SHOUT_NO_VIDEO');
				return;
			}
			else
			{
				continue;
			}
		}
		// Die script and vbscript for all the time... and log it
		if ((strpos($message, '&lt;script') !== false && strpos($message, '&lt;/script') !== false) || (strpos($message, '<script') !== false && strpos($message, '</script') !== false) ||
			 (strpos($message, '&lt;vbscript') !== false && strpos($message, '&lt;/vbscript') !== false) || (strpos($message, '<vbscript') !== false && strpos($message, '</vbscript') !== false))
		{
			$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_SHOUT_SCRIPT' . $on_priv, time(), array('reportee_id' => $this->user->data['user_id']));
			$this->config->increment("shout_nr_log{$priv}", 1, true);
			$this->shout_error('SHOUT_NO_SCRIPT');
			return;
		}
		// Die applet for all the time...  and log it
		else if ((strpos($message, '&lt;applet') !== false && strpos($message, '&lt;/applet') !== false) || (strpos($message, '<applet') !== false && strpos($message, '</applet') !== false))
		{
			$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_SHOUT_APPLET' . $on_priv, time(), array('reportee_id' => $this->user->data['user_id']));
			$this->config->increment("shout_nr_log{$priv}", 1, true);
			$this->shout_error('SHOUT_NO_APPLET');
			return;
		}
		// Die activex for all the time...  and log it
		else if ((strpos($message, '&lt;activex') !== false && strpos($message, '&lt;/activex') !== false) || (strpos($message, '<activex') !== false && strpos($message, '</activex') !== false))
		{
			$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_SHOUT_ACTIVEX' . $on_priv, time(), array('reportee_id' => $this->user->data['user_id']));
			$this->config->increment("shout_nr_log{$priv}", 1, true);
			$this->shout_error('SHOUT_NO_ACTIVEX');
			return;
		}
		// Die about and chrome objects for all the time...  and log it
		else if ((strpos($message, '&lt;object') !== false && strpos($message, '&lt;/object') !== false) || (strpos($message, '<object') !== false && strpos($message, '</object') !== false) ||
				 (strpos($message, '&lt;about') !== false && strpos($message, '&lt;/about') !== false) || (strpos($message, '<about') !== false && strpos($message, '</about') !== false) ||
				 (strpos($message, '&lt;chrome') !== false && strpos($message, '&lt;/chrome') !== false) || (strpos($message, '<chrome') !== false && strpos($message, '</chrome') !== false))
		{
			$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_SHOUT_OBJECTS' . $on_priv, time(), array('reportee_id' => $this->user->data['user_id']));
			$this->config->increment("shout_nr_log{$priv}", 1, true);
			$this->shout_error('SHOUT_NO_OBJECTS');
			return;
		}
		// Die iframe for all the time...  and log it
		else if ((strpos($message, '&lt;iframe') !== false && strpos($message, '&lt;/iframe') !== false) || (strpos($message, '<iframe') !== false && strpos($message, '</iframe') !== false) || (strpos($message, '[iframe') !== false && strpos($message, '[/iframe') !== false))
		{
			$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_SHOUT_IFRAME' . $on_priv, time(), array('reportee_id' => $this->user->data['user_id']));
			$this->config->increment("shout_nr_log{$priv}", 1, true);
			$this->shout_error('SHOUT_NO_IFRAME');
			return;
		}
		if ($robot)
		{
			$message = $this->tpl('colorbot', $message);
		}

		return $this->shout_url_free_sid($message);
	}

	/*
	 * Build a number with ip for differentiate guests
	 */
	private function add_random_ip($username)
	{
		$rand = 0;
		$in = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
		$out = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '1', '2', '3', '4', '5', '6', '7', '8', '9', '1', '2', '3', '4', '5', '6', '7', '8');
		$ip = str_replace($in, $out, strtolower($this->user->ip));
		$act = explode('.', $ip);
		for ($i = 0, $nb = sizeof($act); $i < $nb; $i++)
		{
			if ($act[$i] == 0)
			{
				continue;
			}
			$rand = $rand + $act[$i];
		}
		$data = $username . ':' . round($rand / sizeof($act));

		return $data;
	}

	/* 
	 * Construct/change profile url
	 * to add actions in jQuery
	 * Only if user have right permissions
	 * But never in acp
	 * Return string
	 */
	public function construct_action_shout($id, $username = '', $colour = '', $acp = false)
	{
		if (!$id)
		{
			$username_full = get_username_string('no_profile', $id, $this->config['shout_name_robot'], $this->config['shout_color_robot']);
		}
		else if ($id == ANONYMOUS)
		{
			$username_full = get_username_string('no_profile', $id, $username, '6666FF');
		}
		else if (!$this->user->data['is_registered'] || $this->user->data['is_bot'])
		{
			$username_full = get_username_string('no_profile', $id, $username, $colour);
		}
		else if ($id === $this->user->data['user_id'] || $acp)
		{
			$username_full = get_username_string('full', $id, $username, $colour, '', $this->replace_shout_url(append_sid("{$this->root_path_web}memberlist.{$this->php_ext}", "mode=viewprofile")));
		}
		else
		{
			if ($this->auth->acl_get('u_shout_post_inp') || $this->auth->acl_get('a_') || $this->auth->acl_get('m_'))
			{
				$username_full = $this->tpl('action', $id, $this->language->lang('SHOUT_ACTION_TITLE_TO', $username), get_username_string('no_profile', $id, $username, $colour));
			}
			else
			{
				$username_full = get_username_string('full', $id, $username, $colour, '', $this->replace_shout_url(append_sid("{$this->root_path_web}memberlist.{$this->php_ext}", "mode=viewprofile")));
			}
		}

		return $username_full;
	}

	/* 
	 * Construct url whithout sid
	 * Because urls must be construct for all and use append_sid after
	 */
	private function shout_url_free_sid($content)
	{
		if (strpos($content, 'sid=') !== false)
		{
			$rep = explode('sid=', $content);
			// the sid number is on second part and 32 long
			if (strlen($rep[1]) > 32)
			{
				$sid_32 = substr($rep[1], 0, 32);
				$content = str_replace($sid_32, '', $content);
				$content = str_replace(array('&amp;sid=', '&sid=', '?sid=', '-sid='), '', $content);
			}
			else
			{
				$content = $rep[0];
			}
			$content = str_replace(array('?&amp;', '?&', '&&amp;', '&amp&amp;', '&amp;&amp;'), array('?', '?', '&amp;', '&amp;', '&amp;'), $content);
		}

		return $content;
	}

	/*
	 * Replace relatives urls with complete urls
	 */
	private function replace_shout_url($url)
	{
		return str_replace(array('./../../../', './../../', './../', './'), generate_board_url() . '/', $url);
	}

	/*
	 * protect title value for robot messages
	 */
	private function shout_chars($value)
	{
		return htmlspecialchars(str_replace(array('<t>', '</t>', '&lt;t&gt;', '&lt;/t&gt;', '&quot;'), '', $value), ENT_QUOTES);
	}

	/*
	 * Forms for robot messages and actions
	 */
	private function tpl($sort, $content1 = '', $content2 = '', $content3 = '')
	{
		$tpl = array(
			'action'	=> '<a onclick="shoutbox.actionUser(' . $content1 . ');" title="' . $content2 . '" class="username-coloured action-user">' . $content3 . '</a>',
			'cite'		=> '<span style="color:#' . $this->config['shout_color_message'] . ';font-weight:bold;">' . $content1 . ' </span> ' . $content2 . ' :: ' . $content3,
			'url'		=> '<a class="action-user" href="' . $content1 . '" title="' . $this->shout_chars(($content3 !== '') ? $content3 : $content2) . '">' . $content2 . '</a>',
			'italic'	=> '<span class="shout-italic" style="color:#' . $this->config['shout_color_message'] . '">' . $content1 . '</span>',
			'bold'		=> '<span class="shout-bold">',
			'close'		=> '</span>',
			'colorbot'	=> '[color=#' . $this->config['shout_color_message'] . '][i]' . $content1 . '[/i][/color]',
			'personal'	=> 'onclick="shoutbox.personalMsg();" title="' . $this->language->lang('SHOUT_ACTION_MSG') . '"><span title="">' . $this->language->lang('SHOUT_ACTION_MSG'),
			'citemsg'	=> 'onclick="shoutbox.citeMsg();" title="' . $this->language->lang('SHOUT_ACTION_CITE_EXPLAIN') . '"><span title="">' . $this->language->lang('SHOUT_ACTION_CITE'),
			'citemulti'	=> 'onclick="shoutbox.citeMultiMsg(\'' . $content1 . '\', \'' . $content2 . '\', true);" title="' . $this->language->lang('SHOUT_ACTION_CITE_M_EXPLAIN') . '"><span title="">' . $this->language->lang('SHOUT_ACTION_CITE_M'),
			'perso'		=> 'onclick="shoutbox.changePerso(' . $content1 . ');" title="' . $this->language->lang('SHOUT_ACTION_PERSO') . '"><span title="">' . $this->language->lang('SHOUT_ACTION_PERSO'),
			'robot'		=> 'onclick="shoutbox.robotMsg(' . $content1 . ');" title="' . $this->language->lang('SHOUT_ACTION_MSG_ROBOT', $this->config['shout_name_robot']) . '"><span title="">' . $this->language->lang('SHOUT_ACTION_MSG_ROBOT', $this->construct_action_shout(0)),
			'auth'		=> 'onclick="shoutbox.runAuth(' . $content1 . ', \'' . $content2 . '\');" title="' . $this->language->lang('SHOUT_ACTION_AUTH') . '"><span title="">' . $this->language->lang('SHOUT_ACTION_AUTH'),
			'prefs'		=> 'onclick="shoutbox.shoutPopup(\'' . $content1 . '\', \'850\', \'500\', \'_popup\');" title="' . $this->language->lang('SHOUT_CONFIG_OPEN_TO') . '"><span title="">' . $this->language->lang('SHOUT_CONFIG_OPEN_TO'),
			'delreqto'	=> 'onclick="if(confirm(\'' . $this->language->lang('SHOUT_ACTION_DEL_TO_EXPLAIN') . '\'))shoutbox.delReqTo(' . $content1 . ');" title="' . $this->language->lang('SHOUT_ACTION_DEL_TO') . '"><span title="">' . $this->language->lang('SHOUT_ACTION_DEL_TO'),
			'delreq'	=> 'onclick="if(confirm(\'' . $this->language->lang('SHOUT_ACTION_DELETE_EXPLAIN') . '\'))shoutbox.delReq(' . $content1 . ');" title="' . $this->language->lang('SHOUT_ACTION_DELETE') . '"><span title="">' . $this->language->lang('SHOUT_ACTION_DELETE'),
			'remove'	=> 'onclick="if(confirm(\'' . $this->language->lang('SHOUT_ACTION_REMOVE_EXPLAIN') . '\'))shoutbox.removeMsg(' . $content1 . ');" title="' . $this->language->lang('SHOUT_ACTION_REMOVE') . '"><span title="">' . $this->language->lang('SHOUT_ACTION_REMOVE'),
			'profile'	=> $content1 . '" title="' . $this->language->lang('SHOUT_ACTION_PROFIL', $content2) . '"><span title="">' . $this->language->lang('SHOUT_ACTION_PROFIL', $content2),
			'admin'		=> $content1 . '" title="' . $this->language->lang('SHOUT_ACTION_ADMIN') . '"><span title="">' . $this->language->lang('SHOUT_ACTION_ADMIN'),
			'modo'		=> $content1 . '" title="' . $this->language->lang('SHOUT_ACTION_MCP') . '"><span title="">' . $this->language->lang('SHOUT_ACTION_MCP'),
			'ban'		=> $content1 . '" title="' . $this->language->lang('SHOUT_ACTION_BAN') . '"><span title="">' . $this->language->lang('SHOUT_ACTION_BAN'),
		);

		return $tpl[$sort];
	}

	private function action_user($row, $userid, $sort)
	{
		// Founders protection
		$go_founder = ($row['user_type'] != USER_FOUNDER || $this->user->data['user_type'] == USER_FOUNDER) ? true : false;

		return array(
			'type'			=> 3,
			'id'			=> $row['user_id'],
			'sort'			=> $sort,
			'foe'			=> ($row['foe']) ? true : false,
			'inp'			=> ($this->auth->acl_get('u_shout_post_inp') || $this->auth->acl_get('a_') || $this->auth->acl_get('m_')) ? true : false,
			'username'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'], '', append_sid("{$this->root_path_web}memberlist.{$this->php_ext}", "mode=viewprofile")),
			'avatar'		=> $this->shout_user_avatar($row, 60, true),
			'url_profile'	=> $this->tpl('profile', append_sid("{$this->root_path_web}memberlist.{$this->php_ext}", "mode=viewprofile&amp;u={$row['user_id']}", false), $row['username']),
			'url_message'	=> $this->tpl('personal'),
			'url_del_to'	=> $this->tpl('delreqto', $userid),
			'url_del'		=> $this->tpl('delreq', $userid),
			'url_cite'		=> $this->tpl('citemsg'),
			'url_cite_m'	=> $this->tpl('citemulti', $row['username'], $row['user_colour']),
			'url_auth'		=> ($this->auth->acl_get('a_') || $this->auth->acl_get('m_shout_personal')) ? $this->tpl('auth', $row['user_id'], $row['username']) : '',
			'url_prefs'		=> ($this->auth->acl_get('a_') || $this->auth->acl_get('m_shout_personal')) ? $this->tpl('prefs', $this->helper->route('sylver35_breizhshoutbox_configshout', array('id' => $row['user_id']))) : '',
			'retour'		=> ($this->auth->acl_get('a_user') || $this->auth->acl_get('m_') || ($this->auth->acl_get('m_ban') && $go_founder)) ? true : false,
			'url_admin'		=> ($this->auth->acl_get('a_user')) ? $this->tpl('admin', append_sid("{$this->adm_path()}index.{$this->php_ext}", "i=users&amp;mode=overview&amp;u={$row['user_id']}", true, $this->user->session_id)) : '',
			'url_modo'		=> ($this->auth->acl_get('m_')) ? $this->tpl('modo', append_sid("{$this->root_path_web}mcp.{$this->php_ext}", "i=notes&amp;mode=user_notes&amp;u={$row['user_id']}", true, $this->user->session_id)) : '',
			'url_ban'		=> ($this->auth->acl_get('m_ban') && $go_founder) ? $this->tpl('ban', append_sid("{$this->root_path_web}mcp.{$this->php_ext}", "i=ban&amp;mode=user&amp;u={$row['user_id']}", true, $this->user->session_id)) : '',
			'url_remove'	=> (($this->auth->acl_get('a_') || $this->auth->acl_get('m_shout_delete')) && $go_founder) ? $this->tpl('remove', $row['user_id']) : '',
			'url_perso'		=> (($this->auth->acl_get('a_') || $this->auth->acl_get('m_shout_personal')) && $go_founder) ? $this->tpl('perso', $row['user_id']) : '',
			'url_robot'		=> ($this->auth->acl_get('a_') || $this->auth->acl_get('m_shout_robot')) ? $this->tpl('robot', $sort) : '',
		);
	}

	public function shout_text_for_display($row, $sort, $acp)
	{
		if ($row['shout_info'])
		{
			$row['shout_text'] = $this->display_infos_robot($row, $acp);
		}
		else
		{
			$row['shout_text'] = generate_text_for_display($row['shout_text'], $row['shout_bbcode_uid'], $row['shout_bbcode_bitfield'], $row['shout_bbcode_flags']);
		}

		// Limit the max height for images
		$row['shout_text'] = str_replace('class="postimage"', 'class="postimage" style="max-height:200px;"', $row['shout_text']);

		// Active external links for all links in popup and private shoutbox
		if ($sort !== 2)
		{
			if (preg_match('/class=\"postlink\"/i', $row['shout_text']))
			{
				$row['shout_text'] = str_replace('class="postlink', 'onclick="window.open(this.href);return false;" class="postlink', $row['shout_text']);
			}
			else
			{
				$row['shout_text'] = str_replace(array('a href="', 'class="action-user"'), array('a onclick="window.open(this.href);return false;" href="', 'class="action-user" onclick="window.open(this.href);return false;"'), $row['shout_text']);
			}
		}
		else
		{
			$row['shout_text'] = str_replace('class="postlink', 'onclick="window.open(this.href);return false;" class="postlink', $row['shout_text']);
		}

		return $this->replace_shout_url($row['shout_text']);
	}

	/*
	 * Traduct and display infos robot
	 * for all infos robot functions
	 */
	private function display_infos_robot($row, $acp)
	{
		$message = '';
		$italic = true;
		$start = $this->language->lang('SHOUT_ROBOT_START');

		switch ($row['shout_info'])
		{
			case 1:
				$username = $this->construct_action_shout($row['x_user_id'], $row['x_username'], $row['x_user_colour'], $acp);
				$message = $this->language->lang('SHOUT_SESSION_ROBOT', $username);
			break;
			case 2:
				$username = get_username_string('no_profile', $row['x_user_id'], $row['x_username'], $row['x_user_colour']);
				$message = $this->language->lang('SHOUT_SESSION_ROBOT_BOT', $start, $username);
			break;
			case 3:
				$username = $this->construct_action_shout($row['x_user_id'], $row['x_username'], $row['x_user_colour'], $acp);
				$message = $this->language->lang('SHOUT_ENTER_PRIV', $start, $username);
			break;
			case 4:
				$message = $this->language->lang('SHOUT_PURGE_ROBOT', $start);
			break;
			case 5:
				$message = $this->language->lang('SHOUT_PURGE_PRIV', $start);
			break;
			case 6:
				$message = $this->language->lang('SHOUT_PURGE_SHOUT', $start);
			break;
			case 7:
				$message = $this->language->lang('SHOUT_PURGE_AUTO', $start, $row['shout_text']);
			break;
			case 8:
				$message = $this->language->lang('SHOUT_PURGE_PRIV_AUTO', $start, $row['shout_text']);
			break;
			case 9:
				$message = $this->language->lang('SHOUT_DELETE_AUTO', $start, $row['shout_text']);
			break;
			case 10:
				$message = $this->language->lang('SHOUT_DELETE_PRIV_AUTO', $start, $row['shout_text']);
			break;
			case 11:
				$username = $this->construct_action_shout($row['x_user_id'], $row['x_username'], $row['x_user_colour'], $acp);
				if ($row['shout_info_nb'] > 0)
				{
					// With age to display in birthdays
					$message = $this->language->lang('SHOUT_BIRTHDAY_ROBOT_FULL', $this->config['sitename'], $username, $this->tpl('close'), $this->tpl('bold') . $row['shout_info_nb']);
				}
				else
				{
					// No age to display in birthdays
					$message = $this->language->lang('SHOUT_BIRTHDAY_ROBOT', $this->config['sitename'], $username);
				}
			break;
			case 12:
				$message = $this->language->lang('SHOUT_HELLO_ROBOT', $this->tpl('close'), $this->tpl('bold') . $this->user->format_date($row['shout_time'], $this->language->lang('SHOUT_ROBOT_DATE'), true));
			break;
			case 13:
				$username = $this->construct_action_shout($row['x_user_id'], $row['x_username'], $row['x_user_colour'], $acp);
				$message = $this->language->lang('SHOUT_NEWEST_ROBOT', $username, $this->config['sitename']);
			break;
			case 14:
				$username = $this->construct_action_shout($row['x_user_id'], $row['x_username'], $row['x_user_colour'], $acp);
				$url = $this->tpl('url', append_sid($this->replace_shout_url($row['shout_text2']), false), $row['shout_text']);
				$message = $this->language->lang('SHOUT_GLOBAL_ROBOT', $start, $username, $url);
			break;
			case 15:
				$username = $this->construct_action_shout($row['x_user_id'], $row['x_username'], $row['x_user_colour'], $acp);
				$url = $this->tpl('url', append_sid($this->replace_shout_url($row['shout_text2']), false), $row['shout_text']);
				$message = $this->language->lang('SHOUT_ANNOU_ROBOT', $start, $username, $url);
			break;
			case 16:
				$username = $this->construct_action_shout($row['x_user_id'], $row['x_username'], $row['x_user_colour'], $acp);
				$url = $this->tpl('url', append_sid($this->replace_shout_url($row['shout_text2']), false), $row['shout_text']);
				$message = $this->language->lang('SHOUT_POST_ROBOT', $start, $username, $url);
			break;
			case 17:
				$username = $this->construct_action_shout($row['x_user_id'], $row['x_username'], $row['x_user_colour'], $acp);
				$url = $this->tpl('url', append_sid($this->replace_shout_url($row['shout_text2']), false), $row['shout_text']);
				$message = $this->language->lang('SHOUT_EDIT_ROBOT', $start, $username, $url);
			break;
			case 18:
				$username = $this->construct_action_shout($row['x_user_id'], $row['x_username'], $row['x_user_colour'], $acp);
				$url = $this->tpl('url', append_sid($this->replace_shout_url($row['shout_text2']), false), $row['shout_text']);
				$message = $this->language->lang('SHOUT_TOPIC_ROBOT', $start, $username, $url);
			break;
			case 19:
				$username = $this->construct_action_shout($row['x_user_id'], $row['x_username'], $row['x_user_colour'], $acp);
				$url = $this->tpl('url', append_sid($this->replace_shout_url($row['shout_text2']), false), $row['shout_text']);
				$message = $this->language->lang('SHOUT_LAST_ROBOT', $start, $username, $url);
			break;
			case 20:
				$username = $this->construct_action_shout($row['x_user_id'], $row['x_username'], $row['x_user_colour'], $acp);
				$url = $this->tpl('url', append_sid($this->replace_shout_url($row['shout_text2']), false), $row['shout_text']);
				$message = $this->language->lang('SHOUT_QUOTE_ROBOT', $start, $username, $url);
			break;
			case 21:
				$username = $this->construct_action_shout($row['x_user_id'], $row['x_username'], $row['x_user_colour'], $acp);
				$url = $this->tpl('url', append_sid($this->replace_shout_url($row['shout_text2']), false), $row['shout_text']);
				$message = $this->language->lang('SHOUT_REPLY_ROBOT', $start, $username, $url);
			break;
			case 35:
				$title = (strlen($row['shout_text']) > 45) ? substr($row['shout_text'], 0, 42) . '...' : $row['shout_text'];
				$url = $this->tpl('url', $this->helper->route('sylver35_breizhyoutube_controller', array('mode' => 'view', 'id' => $row['shout_info_nb'])), $title, $row['shout_text']);
				$cat_url = $this->tpl('url', $this->helper->route('sylver35_breizhyoutube_controller', array('mode' => 'cat', 'id' => $row['shout_robot'])), $row['shout_text2']);
				$message = $this->language->lang('SHOUT_NEW_VIDEO', $url, $cat_url);
			break;
			case 36:
				$url = $this->tpl('url', $this->helper->route('teamrelax_relaxarcade_page_games', array('gid' => $row['shout_info_nb'])), $row['shout_text']);
				$cat_url = ($row['shout_robot_user'] && $row['shout_text2']) ? $this->tpl('url', $this->helper->route('teamrelax_relaxarcade_page_list', array('cid' => $row['shout_robot_user'])), $row['shout_text2']) : false;
				$message = $this->language->lang('SHOUT_NEW_SCORE_RA_TXT', $row['shout_robot'], $url);
				$message .= ($cat_url) ? ' ' . $this->language->lang('IN') . ' ' . $cat_url : '';
			break;
			case 37:
				$url = $this->tpl('url', $this->helper->route('teamrelax_relaxarcade_page_games', array('gid' => $row['shout_info_nb'])), $row['shout_text']);
				$cat_url = ($row['shout_robot_user'] && $row['shout_text2']) ? $this->tpl('url', $this->helper->route('teamrelax_relaxarcade_page_list', array('cid' => $row['shout_robot_user'])), $row['shout_text2']) : false;
				$message = $this->language->lang('SHOUT_NEW_URECORD_RA_TXT', $row['shout_robot'], $url);
				$message .= ($cat_url) ? ' ' . $this->language->lang('IN') . ' ' . $cat_url : '';
			break;
			case 38:
				$url = $this->tpl('url', $this->helper->route('teamrelax_relaxarcade_page_games', array('gid' => $row['shout_info_nb'])), $row['shout_text']);
				$cat_url = ($row['shout_robot_user'] && $row['shout_text2']) ? $this->tpl('url', $this->helper->route('teamrelax_relaxarcade_page_list', array('cid' => $row['shout_robot_user'])), $row['shout_text2']) : false;
				$message = $this->language->lang('SHOUT_NEW_RECORD_RA_TXT', $row['shout_robot'], $url);
				$message .= ($cat_url) ? ' ' . $this->language->lang('IN') . ' ' . $cat_url : '';
			break;
			case 60:
				$username = $this->construct_action_shout($row['x_user_id'], $row['x_username'], $row['x_user_colour'], $acp);
				$url = $this->tpl('url', append_sid($this->replace_shout_url($row['shout_text2']), false), $row['shout_text']);
				$message = $this->language->lang('SHOUT_PREZ_ROBOT', $start, $username, $url);
			break;
			case 65:
				$username = $this->construct_action_shout($row['x_user_id'], $row['x_username'], $row['x_user_colour'], $acp);
				$message = generate_text_for_display($row['shout_text'], $row['shout_bbcode_uid'], $row['shout_bbcode_bitfield'], $row['shout_bbcode_flags']);
				$message = $this->tpl('cite', $this->language->lang('SHOUT_USER_POST'), $username, $message);
				$italic = false;
			break;
			case 66:
				$username = $this->construct_action_shout($row['x_user_id'], $row['x_username'], $row['x_user_colour'], $acp);
				$message = generate_text_for_display($row['shout_text'], $row['shout_bbcode_uid'], $row['shout_bbcode_bitfield'], $row['shout_bbcode_flags']);
				$message = $this->tpl('cite', $this->language->lang('SHOUT_ACTION_CITE_ON'), $username, $message);
				$italic = false;
			break;
			case 70:
				$username = $this->construct_action_shout($row['x_user_id'], $row['x_username'], $row['x_user_colour'], $acp);
				$url = $this->tpl('url', append_sid($this->replace_shout_url($row['shout_text2']), false), $row['shout_text']);
				$message = $this->language->lang('SHOUT_PREZ_E_ROBOT', $start, $username, $url);
			break;
			case 71:
				$username = $this->construct_action_shout($row['x_user_id'], $row['x_username'], $row['x_user_colour'], $acp);
				$url = $this->tpl('url', append_sid($this->replace_shout_url($row['shout_text2']), false), $row['shout_text']);
				$message = $this->language->lang('SHOUT_PREZ_ES_ROBOT', $start, $username, $url);
			break;
			case 72:
				$username = $this->construct_action_shout($row['x_user_id'], $row['x_username'], $row['x_user_colour'], $acp);
				$url = $this->tpl('url', append_sid($this->replace_shout_url($row['shout_text2']), false), $row['shout_text']);
				$message = $this->language->lang('SHOUT_PREZ_F_ROBOT', $start, $username, $url);
			break;
			case 73:
				$username = $this->construct_action_shout($row['x_user_id'], $row['x_username'], $row['x_user_colour'], $acp);
				$url = $this->tpl('url', append_sid($this->replace_shout_url($row['shout_text2']), false), $row['shout_text']);
				$message = $this->language->lang('SHOUT_PREZ_FS_ROBOT', $start, $username, $url);
			break;
			case 74:
				$username = $this->construct_action_shout($row['x_user_id'], $row['x_username'], $row['x_user_colour'], $acp);
				$url = $this->tpl('url', append_sid($this->replace_shout_url($row['shout_text2']), false), $row['shout_text']);
				$message = $this->language->lang('SHOUT_PREZ_L_ROBOT', $start, $username, $url);
			break;
			case 75:
				$username = $this->construct_action_shout($row['x_user_id'], $row['x_username'], $row['x_user_colour'], $acp);
				$url = $this->tpl('url', append_sid($this->replace_shout_url($row['shout_text2']), false), $row['shout_text']);
				$message = $this->language->lang('SHOUT_PREZ_LS_ROBOT', $start, $username, $url);
			break;
			case 76:
				$username = $this->construct_action_shout($row['x_user_id'], $row['x_username'], $row['x_user_colour'], $acp);
				$url = $this->tpl('url', append_sid($this->replace_shout_url($row['shout_text2']), false), $row['shout_text']);
				$message = $this->language->lang('SHOUT_PREZ_R_ROBOT', $start, $username, $url);
			break;
			case 77:
				$username = $this->construct_action_shout($row['x_user_id'], $row['x_username'], $row['x_user_colour'], $acp);
				$url = $this->tpl('url', append_sid($this->replace_shout_url($row['shout_text2']), false), $row['shout_text']);
				$message = $this->language->lang('SHOUT_PREZ_RS_ROBOT', $start, $username, $url);
			break;
			case 80:
				$username = $this->construct_action_shout($row['x_user_id'], $row['x_username'], $row['x_user_colour'], $acp);
				$url = $this->tpl('url', append_sid($this->replace_shout_url($row['shout_text2']), false), $row['shout_text']);
				$message = $this->language->lang('SHOUT_PREZ_Q_ROBOT', $start, $username, $url);
			break;
			case 99:
				$message = $this->language->lang('SHOUT_WELCOME');
			break;
		}

		return ($italic) ? $this->tpl('italic', $message) : $message;
	}

	/*
	 * Display infos Robot for purge, delete messages
	 * and enter in the private shoutbox
	 */
	public function post_robot_shout($user_id, $ip, $priv = false, $purge = false, $robot = false, $auto = false, $delete = false, $deleted = '')
	{
		$info = 0;
		$sort_info = 1;
		$message = '-';
		$userid = (int) $user_id;
		$_priv = ($priv) ? '_priv' : '';
		$enter_priv = ($priv && !$purge && !$robot && !$auto && !$delete) ? true : false;
		$shoutbox_table = ($priv) ? $this->shoutbox_priv_table : $this->shoutbox_table;

		if (!$this->config['shout_enable_robot'] && !$enter_priv)
		{
			return;
		}

		if ($priv && $purge && !$robot && !$auto && !$delete)
		{
			$info = 5;
		}
		else if (!$priv && $purge && !$robot && !$auto && !$delete)
		{
			$info = 6;
		}
		else if (!$priv && $purge && !$robot && $auto && !$delete)
		{
			$message = $deleted;
			$info = 7;
		}
		else if ($priv && $purge && !$robot && $auto && !$delete)
		{
			$message = $deleted;
			$info = 8;
		}
		else if (!$priv && $purge && !$robot && $auto && $delete)
		{
			$message = $deleted;
			$info = 9;
		}
		else if ($priv && $purge && !$robot && $auto && $delete)
		{
			$message = $deleted;
			$info = 10;
		}
		else if ($robot && !$auto && !$delete)
		{
			$info = 4;
		}
		else if ($enter_priv)
		{
			$sql = $this->db->sql_build_query('SELECT', array(
				'SELECT'	=> 'shout_time',
				'FROM'		=> array($shoutbox_table => ''),
				'WHERE'		=> "shout_robot = 8 AND shout_robot_user = $userid AND shout_time BETWEEN " . (time() - 60 * 30) . " AND " . time(),
			));
			$result = $this->db->sql_query($sql);
			$is_posted = $this->db->sql_fetchfield('shout_time');
			$this->db->sql_freeresult($result);
			if ($is_posted)
			{
				return;
			}
			$message = $this->user->data['username'];
			$sort_info = 8;
			$info = 3;
		}

		$sql_data = array(
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
		);

		$sql = 'INSERT INTO ' . $shoutbox_table . ' ' . $this->db->sql_build_array('INSERT', $sql_data);
		$this->db->sql_query($sql);
		$this->config->increment("shout_nr{$_priv}", 1, true);
	}

	/*
	 * Display infos Robot for connections
	 */
	public function post_session_shout($event)
	{
		if ($event['session_user_id'] == ANONYMOUS || !$this->config['shout_enable_robot'])
		{
			return;
		}
		else if (!$this->config['shout_sessions'] && !$this->config['shout_sessions_priv'])
		{
			return;
		}
		else if (!$event['session_viewonline'])
		{
			return;
		}
		
		$bot = $this->user->data['is_bot'] ? true : false;
		if ($bot)
		{
			if (!$this->config['shout_sessions_bots'] && !$this->config['shout_sessions_bots_priv'])
			{
				return;
			}
		}

		$userid = (int) $event['session_user_id'];
		$interval = (int) $this->config['shout_sessions_time'] * 60;
		$is_posted = $is_posted_priv = false;

		if (($bot && $this->config['shout_sessions_bots']) || (!$bot && $this->config['shout_sessions']))
		{
			$sql = $this->db->sql_build_query('SELECT', array(
				'SELECT'	=> 'shout_time',
				'FROM'		=> array($this->shoutbox_table => ''),
				'WHERE'		=> "shout_robot = 1 AND shout_robot_user = $userid AND shout_time BETWEEN " . (time() - $interval) . " AND " . time(),
			));
			$result = $this->db->sql_query($sql);
			$is_posted = $this->db->sql_fetchfield('shout_time');
			$this->db->sql_freeresult($result);
		}
		if (($bot && $this->config['shout_sessions_bots_priv']) || (!$bot && $this->config['shout_sessions_priv']))
		{
			$sql = $this->db->sql_build_query('SELECT', array(
				'SELECT'	=> 'shout_time',
				'FROM'		=> array($this->shoutbox_priv_table => ''),
				'WHERE'		=> "shout_robot = 1 AND shout_robot_user = $userid AND shout_time BETWEEN " . (time() - $interval) . " AND " . time(),
			));
			$result = $this->db->sql_query($sql);
			$is_posted_priv = $this->db->sql_fetchfield('shout_time');
			$this->db->sql_freeresult($result);
		}

		$shout_info = ($bot) ? 2 : 1;
		$message = ($event['session_viewonline']) ? 'view' : 'hide';

		$sql_data = array(
			'shout_time'				=> time(),
			'shout_user_id'				=> 0,
			'shout_ip'					=> (string) $this->user->ip,
			'shout_text'				=> (string) $message,
			'shout_bbcode_uid'			=> '',
			'shout_bbcode_bitfield'		=> '',
			'shout_bbcode_flags'		=> 0,
			'shout_robot'				=> 1,
			'shout_robot_user'			=> $userid,
			'shout_forum'				=> 0,
			'shout_info'				=> $shout_info,
		);

		if (!$is_posted)
		{
			if (($bot && $this->config['shout_sessions_bots']) || (!$bot && $this->config['shout_sessions']))
			{
				$sql = 'INSERT INTO ' . $this->shoutbox_table . ' ' . $this->db->sql_build_array('INSERT', $sql_data);
				$this->db->sql_query($sql);
				$this->config->increment('shout_nr', 1, true);
			}
		}
		if (!$is_posted_priv)
		{
			if (($bot && $this->config['shout_sessions_bots_priv']) || (!$bot && $this->config['shout_sessions_priv']))
			{
				$sql = 'INSERT INTO ' . $this->shoutbox_priv_table . ' ' . $this->db->sql_build_array('INSERT', $sql_data);
				$this->db->sql_query($sql);
				$this->config->increment('shout_nr_priv', 1, true);
			}
		}
	}

	private function sort_info($post_mode, $is_prez_form, $prez_poster)
	{
		$ok_shout = $this->config['shout_post_robot'] ? true : false;
		$ok_shout_priv = $this->config['shout_post_robot_priv'] ? true : false;
		$sort_info = $info = 0;

		switch ($post_mode)
		{
			case 'global':
				$sort_info = 2;
				$info = 14;
			break;
			case 'annoucement':
				$sort_info = 2;
				$info = 15;
			break;
			case 'post':
				$sort_info = 2;
				$info = (!$is_prez_form) ? 16 : 60;
			break;
			case 'edit':
				$sort_info = 3;
				$info = 17;
				if ($is_prez_form)
				{
					$info = (!$prez_poster) ? 70 : 71;
				}
				$ok_shout = ($this->config['shout_edit_robot']) ? true : false;
				$ok_shout_priv = ($this->config['shout_edit_robot_priv']) ? true : false;
			break;
			case 'edit_topic':
			case 'edit_first_post':
				$sort_info = 3;
				$info = 18;
				if ($is_prez_form)
				{
					$info = (!$prez_poster) ? 72 : 73;
				}
				$ok_shout = ($this->config['shout_edit_robot']) ? true : false;
				$ok_shout_priv = ($this->config['shout_edit_robot_priv']) ? true : false;
			break;
			case 'edit_last_post':
				$sort_info = 3;
				$info = 19;
				if ($is_prez_form)
				{
					$info = (!$prez_poster) ? 74 : 75;
				}
				$ok_shout = ($this->config['shout_edit_robot']) ? true : false;
				$ok_shout_priv = ($this->config['shout_edit_robot_priv']) ? true : false;
			break;
			case 'quote':
				$sort_info = 3;
				$ok_shout = ($this->config['shout_rep_robot']) ? true : false;
				$ok_shout_priv = ($this->config['shout_rep_robot_priv']) ? true : false;
				$info = (!$is_prez_form) ? 20 : 80;
			break;
			case 'reply':
				$sort_info = 3;
				$info = 21;
				if ($is_prez_form)
				{
					$info = (!$prez_poster) ? 76 : 77;
				}
				$ok_shout = ($this->config['shout_rep_robot']) ? true : false;
				$ok_shout_priv = ($this->config['shout_rep_robot_priv']) ? true : false;
			break;
		}

		return array(
			'info'			=> (int) $info,
			'sort_info'		=> (int) $sort_info,
			'ok_shout'		=> (bool) $ok_shout,
			'ok_shout_priv'	=> (bool) $ok_shout_priv,
		);
	}

	/*
	 * Display infos Robot for new posts, subjects, topics...
	 */
	public function advert_post_shoutbox($event)
	{
		$ok_shout = $this->config['shout_post_robot'] ? true : false;
		$ok_shout_priv = $this->config['shout_post_robot_priv'] ? true : false;
		$hide_robot = (isset($event['data']['hide_robot'])) ? $event['data']['hide_robot'] : false;
		if ((!$ok_shout && !$ok_shout_priv) || $hide_robot != false || !$this->config['shout_enable_robot'])
		{
			return;
		}

		$userid = (int) $this->user->data['user_id'];
		$topic_id = (int) $event['data']['topic_id'];
		$forum_id = (int) $event['data']['forum_id'];
		$is_prez_form = ($forum_id === (int) $this->config['shout_prez_form']) ? true : false;

		if ($this->config['shout_exclude_forums'])
		{
			$exclude_forums = explode(',', $this->config['shout_exclude_forums']);
			if (in_array($forum_id, $exclude_forums))
			{
				return;
			}
		}

		// Parse web adress in subject to prevent bug
		$subject = str_replace(array('http://www.', 'http://', 'https://www.', 'https://', 'www.', 'Re: ', "'"), array('', '', '', '', '', '', $this->language->lang('SHOUT_PROTECT')), (string) $event['subject']);

		$prez_poster = false;
		if ($is_prez_form)
		{
			$sql = 'SELECT topic_poster
				FROM ' . TOPICS_TABLE . '
				WHERE topic_id = ' . $topic_id;
			$result = $this->db->sql_query_limit($sql, 1);
			$topic_poster = (int) $this->db->sql_fetchfield('topic_poster');
			$this->db->sql_freeresult($result);
			$prez_poster = ($topic_poster === $userid) ? true : false;
		}

		if ($event['topic_type'] == 3 && $event['mode'] == 'post')
		{
			$event['mode'] = 'global';
		}
		else if ($event['topic_type'] == 2 && $event['mode'] == 'post')
		{
			$event['mode'] = 'annoucement';
		}
		$on_info = $this->sort_info($event['mode'], $is_prez_form, $prez_poster);

		$sql_data = array(
			'shout_time'				=> (string) time(),
			'shout_user_id'				=> 0,
			'shout_ip'					=> (string) $this->user->ip,
			'shout_text'				=> (string) $subject,
			'shout_text2'				=> (string) $this->shout_url_free_sid($event['url']),
			'shout_bbcode_uid'			=> '',
			'shout_bbcode_bitfield'		=> '',
			'shout_bbcode_flags'		=> 0,
			'shout_robot'				=> (int) $on_info['sort_info'],
			'shout_robot_user'			=> (int) $userid,
			'shout_forum'				=> (int) $forum_id,
			'shout_info_nb'				=> (int) $forum_id,
			'shout_info'				=> (int) $on_info['info'],
		);

		if ($on_info['ok_shout'])
		{
			$sql = 'INSERT INTO ' . $this->shoutbox_table . ' ' . $this->db->sql_build_array('INSERT', $sql_data);
			$this->db->sql_query($sql);
			$this->config->increment('shout_nr', 1, true);
		}
		if ($on_info['ok_shout_priv'])
		{
			$sql = 'INSERT INTO ' . $this->shoutbox_priv_table . ' ' . $this->db->sql_build_array('INSERT', $sql_data);
			$this->db->sql_query($sql);
			$this->config->increment('shout_nr_priv', 1, true);
		}
	}

	/*
	 * Display info of birthdays
	 */
	public function robot_birthday_shout($sleep)
	{
		if (!$this->config['shout_birthday'] && !$this->config['shout_birthday_priv'])
		{
			return;
		}
		if ($this->config['shout_last_run_birthday'] == date('d-m-Y'))
		{
			return;
		}

		$is_posted = false;
		if ($this->config['shout_birthday'])
		{
			$sql = $this->db->sql_build_query('SELECT', array(
				'SELECT'	=> 'shout_id',
				'FROM'		=> array($this->shoutbox_table => ''),
				'WHERE'		=> 'shout_robot = 5 AND shout_info = 11 AND shout_time BETWEEN ' . (time() - 60 * 60) . ' AND ' . time(),
			));
			$result = $this->db->sql_query($sql);
			$is_posted = $this->db->sql_fetchfield('shout_id') ? true : false;
			$this->db->sql_freeresult($result);
		}
		else if ($this->config['shout_birthday_priv'])
		{
			$sql = $this->db->sql_build_query('SELECT', array(
				'SELECT'	=> 'shout_id',
				'FROM'		=> array($this->shoutbox_priv_table => ''),
				'WHERE'		=> 'shout_robot = 5 AND shout_info = 11 AND shout_time BETWEEN ' . (time() - 60 * 60) . ' AND ' . time(),
			));
			$result = $this->db->sql_query($sql);
			$is_posted = $this->db->sql_fetchfield('shout_id') ? true : false;
			$this->db->sql_freeresult($result);
		}

		if (!$is_posted)
		{
			$time = $this->user->create_datetime();
			$now = phpbb_gmgetdate($time->getTimestamp() + $time->getOffset());

			// Display birthdays of 29th february on 28th february in non-leap-years
			$leap_year_birthdays = '';
			if ($now['mday'] == 28 && $now['mon'] == 2 && !$time->format('L'))
			{
				$leap_year_birthdays = " OR u.user_birthday LIKE '" . $this->db->sql_escape(sprintf('%2d-%2d-', 29, 2)) . "%'";
			}

			$sql_ary = array(
				'SELECT' => 'u.user_id, u.user_birthday, u.group_id',
				'FROM' => array(
					USERS_TABLE => 'u',
				),
				'LEFT_JOIN' => array(
					array(
						'FROM' => array(BANLIST_TABLE => 'b'),
						'ON' => 'u.user_id = b.ban_userid',
					),
				),
				'WHERE' => "(b.ban_id IS NULL OR b.ban_exclude = 1)
					AND (u.user_birthday LIKE '" . $this->db->sql_escape(sprintf('%2d-%2d-', $now['mday'], $now['mon'])) . "%' $leap_year_birthdays)
					AND u.user_type IN (" . USER_NORMAL . ', ' . USER_FOUNDER . ')',
			);

			$sql = $this->db->sql_build_query('SELECT', $sql_ary);
			$result = $this->db->sql_query($sql);
			$rows = $this->db->sql_fetchrowset($result);
			$this->db->sql_freeresult($result);

			if (!empty($rows))
			{
				if ($sleep)
				{
					usleep(500000);
				}
				foreach ($rows as $row)
				{
					$exclude_group = explode(', ', $this->config['shout_birthday_exclude']);
					if (in_array($row['group_id'], $exclude_group))
					{
						continue;
					}

					$birthday_year = (int) substr($row['user_birthday'], -4);
					$birthday_age = ($birthday_year) ? max(0, $now['year'] - $birthday_year) : 0;
					$message = 'SHOUT_BIRTHDAY_ROBOT';

					$sql_data = array(
						'shout_time'			=> time(),
						'shout_user_id'			=> 0,
						'shout_ip'				=> (string) $this->user->ip,
						'shout_text'			=> (string) $message,
						'shout_bbcode_uid'		=> '',
						'shout_bbcode_bitfield'	=> '',
						'shout_bbcode_flags'	=> 0,
						'shout_robot'			=> 5,
						'shout_robot_user'		=> (int) $row['user_id'],
						'shout_forum'			=> 0,
						'shout_info_nb'			=> (int) $birthday_age,
						'shout_info'			=> 11,
					);

					if ($this->config['shout_birthday'])
					{
						$sql = 'INSERT INTO ' . $this->shoutbox_table . ' ' . $this->db->sql_build_array('INSERT', $sql_data);
						$this->db->sql_query($sql);
						$this->config->increment('shout_nr', 1, true);
					}
					if ($this->config['shout_birthday_priv'])
					{
						$sql = 'INSERT INTO ' . $this->shoutbox_priv_table . ' ' . $this->db->sql_build_array('INSERT', $sql_data);
						$this->db->sql_query($sql);
						$this->config->increment('shout_nr_priv', 1, true);
					}
				}
			}
			$this->config->set('shout_last_run_birthday', date('d-m-Y'), true);
		}
	}

	/*
	 * Display the date info Robot
	 */
	private function hello_robot_shout($sleep)
	{
		if (!$this->config['shout_hello'] && !$this->config['shout_hello_priv'])
		{
			return;
		}
		if ($this->config['shout_cron_run'] == date('d-m-Y'))
		{
			return;
		}

		$is_posted = false;
		if ($this->config['shout_hello'])
		{
			$sql = $this->db->sql_build_query('SELECT', array(
				'SELECT'	=> 'shout_id',
				'FROM'		=> array($this->shoutbox_table => ''),
				'WHERE'		=> 'shout_robot = 4 AND shout_info = 12 AND shout_time BETWEEN ' . (time() - 60 * 60) . ' AND ' . time(),
			));
			$result = $this->db->sql_query($sql);
			$is_posted = $this->db->sql_fetchfield('shout_id') ? true : false;
			$this->db->sql_freeresult($result);
		}
		else if ($this->config['shout_hello_priv'])
		{
			$sql = $this->db->sql_build_query('SELECT', array(
				'SELECT'	=> 'shout_id',
				'FROM'		=> array($this->shoutbox_priv_table => ''),
				'WHERE'		=> 'shout_robot = 4 AND shout_info = 12 AND shout_time BETWEEN ' . (time() - 60 * 60) . ' AND ' . time(),
			));
			$result = $this->db->sql_query($sql);
			$is_posted = $this->db->sql_fetchfield('shout_id');
			$this->db->sql_freeresult($result);
		}
		if (!$is_posted)
		{
			$sql_data = array(
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
			);

			if ($sleep)
			{
				usleep(500000);
			}
			if ($this->config['shout_hello'])
			{
				$sql = 'INSERT INTO ' . $this->shoutbox_table . ' ' . $this->db->sql_build_array('INSERT', $sql_data);
				$this->db->sql_query($sql);
				$this->config->increment('shout_nr', 1, true);
			}
			if ($this->config['shout_hello_priv'])
			{
				$sql = 'INSERT INTO ' . $this->shoutbox_priv_table . ' ' . $this->db->sql_build_array('INSERT', $sql_data);
				$this->db->sql_query($sql);
				$this->config->increment('shout_nr_priv', 1, true);
			}
			$this->config->set('shout_cron_run', date('d-m-Y'), true);
		}
	}

	private function shout_run_robot($sleep = false)
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
	 * Display first connection for new users
	 */
	public function shout_add_newest_user($event)
	{
		if (!$this->config['shout_enable_robot'] || !$this->config['shout_newest'] && !$this->config['shout_newest_priv'])
		{
			return;
		}

		$sql_data = array(
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
		);

		if ($this->config['shout_newest'])
		{
			$sql = 'INSERT INTO ' . $this->shoutbox_table . ' ' . $this->db->sql_build_array('INSERT', $sql_data);
			$this->db->sql_query($sql);
			$this->config->increment('shout_nr', 1, true);
		}
		if ($this->config['shout_newest_priv'])
		{
			$sql = 'INSERT INTO ' . $this->shoutbox_priv_table . ' ' . $this->db->sql_build_array('INSERT', $sql_data);
			$this->db->sql_query($sql);
			$this->config->increment('shout_nr_priv', 1, true);
		}
	}

	public function submit_new_video($event)
	{
		if (!$this->config['shout_enable_robot'] || !$this->config['shout_video_new'])
		{
			return;
		}

		$sql_data = array(
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
		);

		$sql = 'INSERT INTO ' . $this->shoutbox_table . ' ' . $this->db->sql_build_array('INSERT', $sql_data);
		$this->db->sql_query($sql);
		$this->config->increment('shout_nr', 1, true);
	}

	public function submit_arcade_score($event, $type)
	{
		if (!$this->config['shout_enable_robot'])
		{
			return;
		}

		$title = (isset($event['row']['ra_cat_title'])) ? $event['row']['ra_cat_title'] : '';
		$sql_data = array(
			'shout_time'				=> time(),
			'shout_user_id'				=> (int) $this->user->data['user_id'],
			'shout_ip'					=> (string) $this->user->ip,
			'shout_text'				=> (string) $event['row']['game_name'],
			'shout_text2'				=> (string) $title,
			'shout_bbcode_uid'			=> '',
			'shout_bbcode_bitfield'		=> '',
			'shout_bbcode_flags'		=> 0,
			'shout_robot'				=> (int) $event['gamescore'],
			'shout_robot_user'			=> (int) $event['row']['ra_cat_id'],
			'shout_forum'				=> 0,
			'shout_info_nb'				=> (int) $event['gid'],
			'shout_info'				=> (int) $type,
		);

		$sql = 'INSERT INTO ' . $this->shoutbox_table . ' ' . $this->db->sql_build_array('INSERT', $sql_data);
		$this->db->sql_query($sql);
		$this->config->increment('shout_nr', 1, true);
	}

	/*
	 * Build radio input with specific lang
	 */
	public function construct_radio($name, $sort = 1, $outline = false, $on1 = '', $on2 = '')
	{
		$title1 = $this->language->lang('YES');
		$title2 = $this->language->lang('NO');
		switch ($sort)
		{
			case 1:
				$title1 = $this->language->lang('YES');
				$title2 = $this->language->lang('NO');
			break;
			case 2:
				$title1 = $this->language->lang('ENABLE');
				$title2 = $this->language->lang('DISABLE');
			break;
			case 3:
				$title1 = $this->language->lang($on1);
				$title2 = $this->language->lang($on2);
			break;
		}

		$check1 = ($this->config->offsetGet($name)) ? ' checked="checked" id="' . $name . '"' : '';
		$check2 = (!$this->config->offsetGet($name)) ? ' checked="checked" id="' . $name . '"' : '';

		$data = '<label title="' . $title1 . '"><input type="radio" class="radio" name="' . $name . '" value="1"' . $check1 . ' /> ' . $title1 . '</label>';
		$data .= ($outline) ? '<br /><br />' : '';
		$data .= '<label title="' . $title2 . '"><input type="radio" class="radio" name="' . $name . '" value="0"' . $check2 . ' /> ' . $title2 . '</label>';

		return $data;
	}

	/*
	 * Build select for infos hour
	 * 24 hours format only
	 */
	public function hour_select($value, $select_name)
	{
		$select = '<select id="' . $select_name . '" name="' . $select_name . '">';
		for ($i = 0; $i < 24; $i++)
		{
			$i = ($i < 10) ? '0' . $i : $i;
			$selected = ($i == $value) ? ' selected="selected"' : '';
			$select .= '<option value="' . $i . '"' . $selected . '>' . $i . "</option>\n";
		}
		$select .= '</select>';

		return $select;
	}

	private function list_auth_options()
	{
		return array(
			'a_shout_manage',
			'a_shout_priv',
			'm_shout_delete',
			'm_shout_edit_mod',
			'm_shout_info',
			'm_shout_personal',
			'm_shout_robot',
			'u_shout_bbcode',
			'u_shout_bbcode_change',
			'u_shout_chars',
			'u_shout_color',
			'u_shout_delete_s',
			'u_shout_edit',
			'u_shout_hide',
			'u_shout_ignore_flood',
			'u_shout_image',
			'u_shout_inactiv',
			'u_shout_info_s',
			'u_shout_lateral',
			'u_shout_limit_post',
			'u_shout_popup',
			'u_shout_post',
			'u_shout_post_inp',
			'u_shout_priv',
			'u_shout_smilies',
			'u_shout_view',
		);
	}

	public function shout_ajax_auth($user_id, $username)
	{
		$this->language->add_lang('acp/common');
		$this->language->add_lang('permissions_shoutbox', 'sylver35/breizhshoutbox');
		$list = $this->list_auth_options();
		$first = '';
		$title = $data = array();
		$sort = array(
			'a'	=> 'ACP_VIEW_ADMIN_PERMISSIONS',
			'm'	=> 'ACP_VIEW_GLOBAL_MOD_PERMISSIONS',
			'u'	=> 'ACP_VIEW_USER_PERMISSIONS',
		);

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

		return array(
			'nb'		=> $i,
			'title'		=> $title,
			'data'		=> $data,
			'username'	=> $this->language->lang('SHOUT_OPTION_USER', $username),
		);
	}

	public function shout_ajax_smilies()
	{
		$i = 0;
		$smilies = [];
		$sql = $this->db->sql_build_query('SELECT', array(
			'SELECT'	=> 'smiley_url, MIN(smiley_id) AS smiley_id, MIN(code) AS code, MIN(smiley_order) AS min_smiley_order, MIN(smiley_width) AS smiley_width, MIN(smiley_height) AS smiley_height, MIN(emotion) AS emotion, MIN(display_on_shout) AS display_on_shout',
			'FROM'		=> array(SMILIES_TABLE => ''),
			'WHERE'		=> 'display_on_shout = 1',
			'GROUP_BY'	=> 'smiley_url',
			'ORDER_BY'	=> 'min_smiley_order ASC',
		));
		$result = $this->shout_sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$smilies[$i] = array(
				'nb'		=> $i,
				'code'		=> (string) $row['code'],
				'emotion'	=> (string) $row['emotion'],
				'width'		=> (int) $row['smiley_width'],
				'height'	=> (int) $row['smiley_height'],
				'image'		=> (string) $row['smiley_url'],
			);
			$i++;
		}
		$this->db->sql_freeresult($result);

		$sql = 'SELECT COUNT(smiley_id) as total
			FROM ' . SMILIES_TABLE . '
				WHERE display_on_shout = 0';
		$result = $this->shout_sql_query($sql);
		$row_nb = $this->db->sql_fetchfield('total', $result);
		$this->db->sql_freeresult($result);

		$content = array(
			'smilies'	=> $smilies,
			'total'		=> $i,
			'nb_pop'	=> (int) $row_nb,
			'url'		=> $this->root_path_web . $this->config['smilies_path'] . '/',
		);

		/**
		 * You can use this event to modify the content array.
		 *
		 * @event breizhshoutbox.smilies
		 * @var	array	content		The content array to be displayed in the smilies form
		 * @since 1.7.0
		 */
		$vars = array('content');
		extract($this->phpbb_dispatcher->trigger_event('breizhshoutbox.smilies', compact($vars)));

		return $content;
	}

	public function shout_ajax_smilies_popup($cat)
	{
		$i = 0;
		$smilies = [];

		$sql = $this->db->sql_build_query('SELECT', array(
			'SELECT'	=> 'smiley_url, MIN(smiley_id) AS smiley_id, MIN(code) AS code, MIN(smiley_order) AS min_smiley_order, MIN(smiley_width) AS smiley_width, MIN(smiley_height) AS smiley_height, MIN(emotion) AS emotion, MIN(display_on_shout) AS display_on_shout',
			'FROM'		=> array(SMILIES_TABLE => ''),
			'WHERE'		=> 'display_on_shout = 0',
			'GROUP_BY'	=> 'smiley_url',
			'ORDER_BY'	=> 'min_smiley_order ASC',
		));
		$result = $this->shout_sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$smilies[$i] = array(
				'nb'		=> $i,
				'code'		=> (string) $row['code'],
				'emotion'	=> (string) $row['emotion'],
				'width'		=> (int) $row['smiley_width'],
				'height'	=> (int) $row['smiley_height'],
				'image'		=> (string) $row['smiley_url'],
			);
			$i++;
		}
		$this->db->sql_freeresult($result);

		$content = array(
			'smilies'	=> $smilies,
			'total'		=> $i,
			'nb_pop'	=> 0,
			'url'		=> $this->root_path_web . $this->config['smilies_path'] . '/',
		);

		/**
		 * You can use this event to modify the content array.
		 *
		 * @event breizhshoutbox.smilies_popup
		 * @var	array	content			The content array to be displayed in the smilies form
		 * @var	int		cat				The id of smilies category if needed
		 * @since 1.7.0
		 */
		$vars = array('content', 'cat');
		extract($this->phpbb_dispatcher->trigger_event('breizhshoutbox.smilies_popup', compact($vars)));

		return $content;
	}

	public function shout_ajax_display_smilies($smiley, $display)
	{
		$var_set = ($display === 1) ? 0 : 1;
		$sql = 'UPDATE ' . SMILIES_TABLE . " SET display_on_shout = $var_set WHERE smiley_id = $smiley";
		$this->db->sql_query($sql);
		$content = array(
			'type'	=> ($display === 1) ? 1 : 2,
		);

		$i = $j = 0;
		$smilies = $smilies_pop = [];
		$sql = $this->db->sql_build_query('SELECT', array(
			'SELECT'	=> 'smiley_url, MIN(smiley_id) AS smiley_id, MIN(code) AS code, MIN(smiley_order) AS min_smiley_order, MIN(smiley_width) AS smiley_width, MIN(smiley_height) AS smiley_height, MIN(emotion) AS emotion, MIN(display_on_shout) AS display_on_shout',
			'FROM'		=> array(SMILIES_TABLE => ''),
			'WHERE'		=> 'display_on_shout = 1',
			'GROUP_BY'	=> 'smiley_url',
			'ORDER_BY'	=> 'min_smiley_order ASC',
		));
		$result = $this->shout_sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$smilies[$i] = array(
				'nb'		=> $i,
				'id'		=> (int) $row['smiley_id'],
				'code'		=> (string) $row['code'],
				'emotion'	=> (string) $row['emotion'],
				'width'		=> (int) $row['smiley_width'],
				'height'	=> (int) $row['smiley_height'],
				'image'		=> (string) $row['smiley_url'],
			);
			$i++;
		}
		$this->db->sql_freeresult($result);

		$sql = $this->db->sql_build_query('SELECT', array(
			'SELECT'	=> 'smiley_url, MIN(smiley_id) AS smiley_id, MIN(code) AS code, MIN(smiley_order) AS min_smiley_order, MIN(smiley_width) AS smiley_width, MIN(smiley_height) AS smiley_height, MIN(emotion) AS emotion, MIN(display_on_shout) AS display_on_shout',
			'FROM'		=> array(SMILIES_TABLE => ''),
			'WHERE'		=> 'display_on_shout = 0',
			'GROUP_BY'	=> 'smiley_url',
			'ORDER_BY'	=> 'min_smiley_order',
		));
		$result_pop = $this->shout_sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result_pop))
		{
			$smilies_pop[$j] = array(
				'nb'		=> $j,
				'id'		=> (int) $row['smiley_id'],
				'code'		=> (string) $row['code'],
				'emotion'	=> (string) $row['emotion'],
				'width'		=> (int) $row['smiley_width'],
				'height'	=> (int) $row['smiley_height'],
				'image'		=> (string) $row['smiley_url'],
			);
			$j++;
		}
		$this->db->sql_freeresult($result_pop);

		$content = array_merge($content, array(
			'smilies'		=> $smilies,
			'smiliesPop'	=> $smilies_pop,
			'total'			=> $i,
			'totalPop'		=> $j,
			'url'			=> $this->root_path_web . $this->config['smilies_path'] . '/',
		));

		return $content;
	}

	public function shout_ajax_question()
	{
		$guest_can_post = $this->auth->acl_get_list(ANONYMOUS, 'u_shout_post');
		return array(
			'title'	=> $this->language->lang('SHOUT_COOKIES'),
			'info'	=> $this->language->lang('SHOUT_COOKIES_INFO', ($guest_can_post ? 3 : 2)),
			'robot'	=> $this->language->lang('SHOUT_COOKIES_ROBOT'),
			'sound'	=> $this->language->lang('SHOUT_COOKIES_SOUND'),
			'name'	=> ($guest_can_post) ? $this->language->lang('SHOUT_COOKIES_NAME') : '',
		);
	}

	public function shout_ajax_user_bbcode($val, $open, $close)
	{
		$text = $message = '';
		$on_user = ($val['other'] > 0) ? $val['other'] : $val['userid'];

		// Parse bbcodes
		$data = $this->parse_shout_bbcodes($open, $close, $on_user);
		switch ($data['sort'])
		{
			// Remove the bbcodes
			case 1:
				$sql = 'UPDATE ' . USERS_TABLE . " SET shout_bbcode = '' WHERE user_id = $on_user";
				$this->shout_sql_query($sql);
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
				$this->shout_sql_query($sql);
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

		return array(
			'type'		=> $data['sort'],
			'before'	=> $open,
			'after'		=> $close,
			'on_user'	=> $on_user,
			'text'		=> $text,
			'message'	=> $message,
		);
	}

	public function shout_ajax_charge_bbcode($id)
	{
		$on_bbcode = [];
		$sql = $this->db->sql_build_query('SELECT', array(
			'SELECT'	=> 'user_id, user_type, username, user_colour, shout_bbcode',
			'FROM'		=> array(USERS_TABLE => ''),
			'WHERE'		=> 'user_id = ' . $id,
		));
		$result = $this->shout_sql_query($sql, true, 1);
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

		return array(
			'id'		=> $id,
			'name'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
			'before'	=> $on_bbcode[0],
			'after'		=> $on_bbcode[1],
			'message'	=> $message,
		);
	}

	public function shout_ajax_preview_rules($rules)
	{
		$options = 0;
		$uid = $bitfield = '';
		generate_text_for_storage($rules, $uid, $bitfield, $options, true, false, true);
		$rules = $this->replace_shout_url(generate_text_for_display($rules, $uid, $bitfield, $options));

		return array(
			'content'	=> $rules,
		);
	}

	public function shout_ajax_date_format($date)
	{
		$date = ($date == 'custom') ? $this->config['shout_dateformat'] : $date;

		return array(
			'format'	=> $date,
			'date'		=> $this->user->format_date(time() - 60 * 61, $date),
			'date2'		=> $this->user->format_date(time() - 60 * 60 * 60, $date),
		);
	}

	public function shout_ajax_action_sound($on_sound)
	{
		$content = array();
		$user_shout = json_decode($this->user->data['user_shout']);
		$on_sound = ($user_shout->user == 2) ? $on_sound : $user_shout->user;
		switch ($on_sound)
		{
			// Turn on the sounds
			case 0:
				$content = array(
					'type'		=> 1,
					'classOut'	=> 'button_shout_sound_off',
					'classIn'	=> 'button_shout_sound',
					'title'		=> $this->language->lang('SHOUT_CLICK_SOUND_OFF'),
				);
			break;
			// Turn off the sounds
			case 1:
				$content = array(
					'type'		=> 0,
					'classOut'	=> 'button_shout_sound',
					'classIn'	=> 'button_shout_sound_off',
					'title'		=> $this->language->lang('SHOUT_CLICK_SOUND_ON'),
				);
			break;
		}

		$user_shout = json_encode(array(
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
		));

		$sql = 'UPDATE ' . USERS_TABLE . "
			SET user_shout = '" . $this->db->sql_escape($user_shout) . "'
				WHERE user_id = " . $this->user->data['user_id'];
		$this->db->sql_query($sql);

		return $content;
	}

	public function shout_ajax_cite($id)
	{
		$sql = 'SELECT user_id, user_type
			FROM ' . USERS_TABLE . '
				WHERE user_id = ' . $id;
		$result = $this->shout_sql_query($sql, true, 1);
		$row = $this->db->sql_fetchrow($result);
		if (!$row || $row['user_type'] == USER_IGNORE)
		{
			$content = array(
				'type'		=> 0,
				'message'	=> $this->language->lang('NO_USER'),
			);
		}
		else
		{
			$content = array(
				'type'		=> 1,
				'id'		=> $row['user_id'],
			);
		}
		$this->db->sql_freeresult($result);

		return $content;
	}

	public function shout_ajax_action_user($val)
	{
		if (!$val['is_user'] || !$val['other'] || $val['other'] == ANONYMOUS)
		{
			$content = array(
				'type'		=> 0,
				'message'	=> $this->language->lang('NO_ACTION_PERM'),
			);
		}
		else
		{
			$sql = $this->db->sql_build_query('SELECT', array(
				'SELECT'	=> 'z.user_id, z.zebra_id, z.foe, u.user_id, u.user_type, u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height',
				'FROM'		=> array(USERS_TABLE => 'u'),
				'LEFT_JOIN'	=> array(
					array(
						'FROM'	=> array(ZEBRA_TABLE => 'z'),
						'ON'	=> 'u.user_id = z.zebra_id AND z.user_id = ' . $val['userid'],
					),
				),
				'WHERE'		=> 'u.user_id = ' . $val['other'],
			));
			$result = $this->shout_sql_query($sql, true, 1);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);
			if (!$row)
			{
				$content = array(
					'type'		=> 1,
				);
			}
			else if ($row['user_type'] == USER_IGNORE)
			{
				$content = array(
					'type'		=> 2,
					'username'	=> get_username_string('no_profile', $row['user_id'], $row['username'], $row['user_colour']),
					'message'	=> $this->language->lang('SHOUT_USER_NONE'),
				);
			}
			else
			{
				$content = $this->action_user($row, $val['userid'], $val['sort']);
			}
		}

		return $content;
	}

	public function shout_ajax_action_post($val, $message)
	{
		if ($this->auth->acl_get('u_shout_post_inp') || $this->auth->acl_get('m_shout_robot') || $this->auth->acl_get('a_') || $this->auth->acl_get('m_'))
		{
			$info = 65;
			$robot = false;

			if (!$val['other'])
			{
				return array(
					'type'	=> 0,
				);
			}
			else if ($val['other'] === 1)
			{
				// post a robot message
				if ($this->auth->acl_get('a_') || $this->auth->acl_get('m_shout_robot'))
				{
					$info = 0;
					$robot = true;
					$val['other'] = $val['userid'] = 0;
				}
				else
				{
					// no perm, out...
					return array(
						'type'	=> 0,
					);
				}
			}
			else if ($val['other'] > 1)
			{
				// post a personal message
				$data = $this->shout_is_foe($val['userid'], $val['other']);
				if ($data['type'] > 0)
				{
					return array(
						'type'		=> $data['type'],
						'message'	=> $data['message'],
					);
				}
			}

			$message = $this->parse_shout_message($message, $val['on_priv'], 'post', $robot);
			// Personalize message
			if ($val['other'] !== 0)
			{
				$message = $this->personalize_shout_message($message);
			}

			// will be modified by generate_text_for_storage
			$options = 0;
			$uid = $bitfield = '';
			generate_text_for_storage($message, $uid, $bitfield, $options, $this->auth->acl_get('u_shout_bbcode'), true, $this->auth->acl_get('u_shout_smilies'));

			$sql_ary = array(
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
			);
			$sql = 'INSERT INTO ' . $val['shout_table'] . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
			$this->db->sql_query($sql);
			$this->config->increment("shout_nr{$val['priv']}", 1, true);

			return array(
				'type'		=> 1,
				'message'	=> $this->language->lang('POSTED'),
			);
		}
		else
		{
			return array(
				'type'		=> 0,
				'message'	=> $this->language->lang('NO_ACTION_PERM'),
			);
		}
	}

	private function shout_is_foe($userid, $id)
	{
		$content = array(
			'type'		=> 0,
			'message'	=> '',
		);

		$sql = $this->db->sql_build_query('SELECT', array(
			'SELECT'	=> 'u.user_id, u.user_type, z.friend, z.foe',
			'FROM'		=> array(USERS_TABLE => 'u'),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(ZEBRA_TABLE => 'z'),
					'ON'	=> 'z.zebra_id = u.user_id AND z.user_id = ' . $userid,
				),
			),
			'WHERE'		=> 'u.user_id = ' . $id,
		));
		$result = $this->shout_sql_query($sql, true, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		if (!$row || $row['user_type'] == USER_IGNORE)
		{
			// user id don't exist or ignore
			$content = array(
				'type'		=> 1,
			);
		}
		else if ($row['foe'])
		{
			// if user is foe
			$content = array(
				'type'		=> 2,
				'message'	=> $this->language->lang('SHOUT_USER_IGNORE'),
			);
		}
		else
		{
			$content = array(
				'type'		=> 0,
			);
		}

		return $content;
	}

	public function shout_ajax_action_del($val)
	{
		if ($val['other'] !== $val['userid'])
		{
			$content = array(
				'type'		=> 0,
				'message'	=> $this->language->lang('NO_ACTION_PERM'),
			);
		}
		else
		{
			// Delete all personnal messages of this user
			$sql = 'DELETE FROM ' . $val['shout_table'] . '
				WHERE shout_user_id = ' . $val['other'] . '
					AND shout_inp <> 0';
			$this->shout_sql_query($sql);
			$deleted = $this->db->sql_affectedrows();
			if (!$deleted)
			{
				$content = array(
					'type'		=> 1,
					'message'	=> $this->language->lang('SHOUT_ACTION_DEL_NO'),
				);
			}
			else
			{
				// For reload the message to everybody
				$this->update_shout_messages($val['shout_table']);
				$this->config->increment("shout_del_user{$val['priv']}", $deleted, true);
				$content = array(
					'type'		=> 1,
					'message'	=> $this->language->lang('SHOUT_ACTION_DEL_REP') . ' ' . $this->language->lang($this->plural('NUMBER_MESSAGE', $deleted), $deleted),
				);
			}
		}

		return $content;
	}

	public function shout_ajax_action_del_to($val)
	{
		if ($val['other'] !== $val['userid'])
		{
			$content = array(
				'type'		=> 0,
				'message'	=> $this->language->lang('NO_ACTION_PERM'),
			);
		}
		else
		{
			// Delete all personnal messages to this user
			$sql = 'DELETE FROM ' . $val['shout_table'] . '
				WHERE shout_inp = ' . $val['other'] . '
					AND shout_user_id <> ' . $val['other'];
			$this->shout_sql_query($sql);
			$deleted = $this->db->sql_affectedrows();
			if (!$deleted)
			{
				$content = array(
					'type'		=> 1,
					'message'	=> $this->language->lang('SHOUT_ACTION_DEL_NO'),
				);
			}
			else
			{
				$this->update_shout_messages($val['shout_table']);
				$this->config->increment("shout_del_user{$val['priv']}", $deleted, true);
				$content = array(
					'type'		=> 1,
					'message'	=> $this->language->lang('SHOUT_ACTION_DEL_REP') . ' ' . $this->language->lang($this->plural('NUMBER_MESSAGE', $deleted), $deleted),
				);
			}
		}

		return $content;
	}

	public function shout_ajax_action_remove($val)
	{
		if ($this->auth->acl_get('a_shout_manage') || $this->auth->acl_get('m_shout_delete'))
		{
			// Delete all messages of this user
			$sql = 'DELETE FROM ' . $val['shout_table'] . '
				WHERE shout_user_id = ' . $val['other'] . '
					OR shout_robot_user = ' . $val['other'] . '
					OR shout_inp = ' . $val['other'];
			$this->shout_sql_query($sql);
			$deleted = $this->db->sql_affectedrows();
			if ($deleted)
			{
				$this->update_shout_messages($val['shout_table']);
				$this->config->increment("shout_del_user{$val['priv']}", $deleted, true);
				$content = array(
					'type'		=> 1,
					'message'	=> $this->language->lang('SHOUT_ACTION_REMOVE_REP') . ' ' . $this->language->lang($this->plural('NUMBER_MESSAGE', $deleted), $deleted),
				);
			}
			else
			{
				$content = array(
					'type'		=> 0,
					'message'	=> $this->language->lang('SHOUT_ACTION_REMOVE_NO'),
				);
			}
		}
		else
		{
			$content = array(
				'type'		=> 0,
				'message'	=> $this->language->lang('NO_SHOUT_DEL'),
			);
		}

		return $content;
	}

	public function shout_ajax_delete($val, $post)
	{
		if (!$post)
		{
			return array(
				'type'		=> 3,
				'message'	=> $this->language->lang('NO_SHOUT_ID'),
			);
		}

		// If someone can delete all messages, he can delete it's messages :)
		$can_delete_all = ($this->auth->acl_get('m_shout_delete') || $this->auth->acl_get("a_shout{$val['auth']}")) ? true : false;
		$can_delete = $can_delete_all ? true : $this->auth->acl_get('u_shout_delete_s');

		$sql = 'SELECT shout_user_id
			FROM ' . $val['shout_table'] . "
				WHERE shout_id = $post";
		$result = $this->shout_sql_query($sql, true, 1);
		$on_id = $this->db->sql_fetchfield('shout_user_id');
		$this->db->sql_freeresult($result);

		$verify = $this->shout_verify_delete($val['userid'], $on_id, $can_delete_all, $can_delete);

		if (!$verify['result'])
		{
			return array(
				'type'		=> 2,
				'message'	=> $this->language->lang($verify['message']),
			);
		}
		else
		{
			// Lets delete this post :D
			$sql = 'DELETE FROM ' . $val['shout_table'] . "
				WHERE shout_id = $post";
			$this->db->sql_query($sql);

			$this->update_shout_messages($val['shout_table']);
			$this->config->increment("shout_del_user{$val['priv']}", 1, true);
			return array(
				'type'	=> 1,
				'post'	=> $post,
				'sort'	=> $val['perm'],
			);
		}
	}

	private function shout_verify_delete($userid, $on_id, $can_delete_all, $can_delete)
	{
		if ($userid == ANONYMOUS)
		{
			$message = 'NO_DELETE_PERM';
			$result = false;
		}
		else if (!$can_delete && ($userid == $on_id))
		{
			$message = 'NO_DELETE_PERM_S';
			$result = false;
		}
		else if (!$can_delete_all && $can_delete && ($userid != $on_id))
		{
			$message = 'NO_DELETE_PERM_T';
			$result = false;
		}
		else if (!$can_delete)
		{
			$message = 'NO_DELETE_PERM';
			$result = false;
		}
		else if (($can_delete && ($userid == $on_id)) || $can_delete_all)
		{
			$message = '';
			$result = true;
		}
		else
		{
			$message = 'NO_DELETE_PERM';
			$result = false;
		}

		return array(
			'message'	=> $message,
			'result'	=> $result,
		);
	}

	public function shout_ajax_purge($val)
	{
		if (!$this->auth->acl_get("a_shout_manage{$val['auth']}"))
		{
			return array(
				'type'		=> 2,
				'message'	=> $this->language->lang('NO_PURGE_PERM'),
			);
		}
		else
		{
			$sql = 'DELETE FROM ' . $val['shout_table'];
			$this->shout_sql_query($sql);
			$deleted = $this->db->sql_affectedrows();

			$this->config->increment("shout_del_purge{$val['priv']}", $deleted, true);
			$this->post_robot_shout($val['userid'], $this->user->ip, $val['on_priv'], true, false, false, false);

			return array(
				'type'	=> 1,
				'nr'	=> $deleted,
			);
		}
	}

	public function shout_ajax_purge_robot($val)
	{
		if (!$this->auth->acl_get("a_shout{$val['auth']}"))
		{
			return array(
				'type'		=> 2,
				'message'	=> $this->language->lang('NO_PURGE_ROBOT_PERM'),
			);
		}
		else
		{
			$sort_on = explode(', ', $this->config["shout_robot_choice{$val['priv']}"] . ', 4');

			$sql = 'DELETE FROM ' . $val['shout_table'] . '
				WHERE ' . $this->db->sql_in_set('shout_info', $sort_on, false, true);
			$this->shout_sql_query($sql);
			$deleted = $this->db->sql_affectedrows();

			$this->config->increment("shout_del_purge{$val['priv']}", $deleted, true);
			$this->post_robot_shout($val['userid'], $this->user->ip, $val['on_priv'], true, true, false, false);

			return array(
				'type'	=> 1,
				'nr'	=> $deleted,
			);
		}
	}

	public function shout_ajax_edit($val, $shout_id, $message)
	{
		if (!$this->shout_check_edit($val, $shout_id))
		{
			return array(
				'type'		=> 1,
				'mode'		=> $val['mode'],
				'shout_id'	=> $shout_id,
				'message'	=> $this->language->lang('NO_EDIT_PERM'),
			);
		}

		// Multi protections at this time...
		$message = $this->parse_shout_message($message, $val['on_priv'], 'edit', false);

		// will be modified by generate_text_for_storage
		$options = 0;
		$uid = $bitfield = '';
		generate_text_for_storage($message, $uid, $bitfield, $options, $this->auth->acl_get('u_shout_bbcode'), true, $this->auth->acl_get('u_shout_smilies'));

		$sql_ary = array(
			'shout_text'			=> (string) $message,
			'shout_bbcode_uid'		=> $uid,
			'shout_bbcode_bitfield'	=> $bitfield,
			'shout_bbcode_flags'	=> $options,
		);

		$sql = 'UPDATE ' . $val['shout_table'] . '
			SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE shout_id = ' . $shout_id;
		$this->shout_sql_query($sql);

		// For reload the message to everybody
		$this->update_shout_messages($val['shout_table']);
		$message = generate_text_for_display($message, $uid, $bitfield, $options);

		return array(
			'type'		=> 2,
			'mode'		=> $val['mode'],
			'shout_id'	=> $shout_id,
			'message'	=> $this->language->lang('EDIT_DONE'),
			'texte'		=> $message,
		);
	}

	private function shout_check_edit($val, $shout_id)
	{
		// If someone can edit all messages, he can edit it's messages :) (if errors in permissions set)
		if ($this->auth->acl_get('m_shout_edit_mod') || $this->auth->acl_get("a_shout{$val['auth']}"))
		{
			return true;
		}
		else if ($this->auth->acl_get('u_shout_edit'))
		{
			// We need to be sure its this users his shout.
			$sql = 'SELECT shout_user_id
				FROM ' . $val['shout_table'] . '
					WHERE shout_id = ' . $shout_id;
			$result = $this->shout_sql_query($sql, true, 1);
			$on_id = (int) $this->db->sql_fetchfield('shout_user_id');
			$this->db->sql_freeresult($result);
			// Not his shout, display error
			if (!$on_id || $on_id !== $val['userid'])
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return false;
	}

	public function shout_ajax_post($val, $message, $name, $cite)
	{
		if (!$this->auth->acl_get('u_shout_post'))
		{
			return array(
				'type'		=> 2,
				'message'	=> $this->language->lang('NO_POST_PERM'),
			);
		}
		else if (!$this->shout_verify_flood($val['on_priv'], $val['userid']))
		{
			return array(
				'type'		=> 2,
				'message'	=> $this->language->lang('FLOOD_ERROR'),
			);
		}

		// Multi protections at this time...
		$message = $this->parse_shout_message($message, $val['on_priv'], 'post', false);

		// Personalize message
		$message = $this->personalize_shout_message($message);

		// will be modified by generate_text_for_storage
		$options = 0;
		$uid = $bitfield = '';
		generate_text_for_storage($message, $uid, $bitfield, $options, $this->auth->acl_get('u_shout_bbcode'), true, $this->auth->acl_get('u_shout_smilies'));

		// For guest, add a random number from ip after name
		if (!$this->user->data['is_registered'])
		{
			$name = $this->add_random_ip($name);
		}

		$sql_ary = array(
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
		);

		$sql = 'INSERT INTO ' . $val['shout_table'] . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
		$this->shout_sql_query($sql);
		$this->config->increment("shout_nr{$val['priv']}", 1, true);
		$this->delete_shout_posts($val);

		return array(
			'type'		=> 1,
			'mode'		=> $val['mode'],
			'cite'		=> $cite,
			'message'	=> $this->language->lang('POSTED'),
		);
	}

	private function shout_verify_flood($on_priv, $userid)
	{
		// Flood control, not in private
		if (!$on_priv && !$this->auth->acl_get('u_shout_ignore_flood'))
		{
			$sql = $this->db->sql_build_query('SELECT', array(
				'SELECT'	=> 'MAX(shout_time) AS last_post_time',
				'FROM'		=> array($this->shoutbox_table => ''),
				'WHERE'		=> (!$this->user->data['is_registered']) ? "shout_ip = '" . $this->db->sql_escape((string) $this->user->ip) . "'" : 'shout_user_id = ' . $userid,
			));
			$result = $this->shout_sql_query($sql);
			if ($row = $this->db->sql_fetchrow($result))
			{
				if ($row['last_post_time'] > 0 && (time() - $row['last_post_time']) < $this->config['shout_flood_interval'])
				{
					$this->db->sql_freeresult($result);
					return false;
				}
			}
			$this->db->sql_freeresult($result);
		}

		return true;
	}

	public function shout_ajax_check($val, $on_bot)
	{
		$this->shout_run_robot();

		$sql = $this->db->sql_build_query('SELECT', array(
			'SELECT'	=> 's.shout_time',
			'FROM'		=> array($val['shout_table'] => 's'),
			'WHERE'		=> $this->shout_sql_where($val['is_user'], $val['userid'], $on_bot),
			'ORDER_BY'	=> 's.shout_time DESC',
		));
		$result = $this->shout_sql_query($sql, true, 1);
		$time = $this->db->sql_fetchfield('shout_time');
		$this->db->sql_freeresult($result);

		// check just with the last 4 numbers
		return array(
			't'		=> substr($time, 6, 4),
		);
	}

	public function shout_ajax_view($val, $on_bot, $start)
	{
		$i = 0;
		$content = array(
			'messages'	=> array(),
		);

		$perm = $this->shout_extract_permissions($val['auth']);
		$dateformat = $this->shout_extract_dateformat($val['is_user']);
		$sql_where = $this->shout_sql_where($val['is_user'], $val['userid'], $on_bot);
		$is_mobile = $this->shout_is_mobile();

		$sql = $this->db->sql_build_query('SELECT', array(
			'SELECT'	=> 's.*, u.user_id, u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height, u.user_type, v.user_id as x_user_id, v.username as x_username, v.user_colour as x_user_colour, v.user_avatar as x_user_avatar, v.user_avatar_type as x_user_avatar_type, v.user_avatar_width as x_user_avatar_width, v.user_avatar_height as x_user_avatar_height, v.user_type as x_user_type',
			'FROM'		=> array($val['shout_table'] => 's'),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'	=> 'u.user_id = s.shout_user_id',
				),
				array(
					'FROM'	=> array(USERS_TABLE => 'v'),
					'ON'	=> 'v.user_id = s.shout_robot_user',
				),
			),
			'WHERE'		=> $sql_where,
			'ORDER_BY'	=> 's.shout_id DESC',
		));
		$result = $this->shout_sql_query($sql, true, (int) $this->config["shout_num{$val['sort_on']}"], $start);
		while ($row = $this->db->sql_fetchrow($result))
		{
			// Initialize additional data
			$row = array_merge($row, array(
				'delete'		=> false,
				'edit'			=> false,
				'show_ip'		=> false,
				'on_ip'			=> false,
				'avatar_img'	=> '',
				'msg_plain'		=> '',
				'is_user'		=> (($row['shout_user_id'] > 1) && ((int) $row['shout_user_id'] !== $val['userid'])) ? true : false,
				'real_user'		=> (($row['shout_user_id'] > 1) || ((int) $row['shout_robot_user'] !== 0)) ? true : false,
				'name'			=> $row['username'],
			));

			if (!$is_mobile)
			{
				$row['avatar_img'] = $this->get_avatar_row($row, $val['sort']);
			}

			// Message made by anonymous
			$row['username'] = ($row['shout_user_id'] == ANONYMOUS) ? $row['shout_text2'] : $row['username'];
			$row['username'] = $this->construct_action_shout($row['user_id'], $row['username'], $row['user_colour']);
			$row['on_time'] = $this->user->format_date($row['shout_time'], $dateformat);

			// Checks permissions for delete, edit and show_ip
			$row = $this->get_permissions_row($row, $perm, $val);

			// The message now
			$row['shout_text'] = $this->shout_text_for_display($row, $val['sort'], false);

			// Construct the content of loop
			$content['messages'][$i] = array(
				'shoutId'		=> $row['shout_id'],
				'shoutTime'		=> $row['on_time'],
				'timeMsg'		=> $row['shout_time'],
				'shoutText'		=> $row['shout_text'],
				'username'		=> $row['username'],
				'isUser'		=> $row['is_user'],
				'name'			=> $row['name'],
				'colour'		=> $row['user_colour'],
				'avatar'		=> $row['avatar_img'],
				'deletemsg'		=> $row['delete'],
				'edit'			=> $row['edit'],
				'showIp'		=> $row['show_ip'],
				'msgPlain'		=> $row['msg_plain'],
				'shoutIp'		=> $row['on_ip'],
			);
			$i++;
		}
		$this->db->sql_freeresult($result);

		// Get the last message time
		$last_time = $this->get_shout_time($sql_where, $val['shout_table']);
		// The number of total messages for pagination
		$number = $this->shout_pagination($sql_where, $val['shout_table'], $val['priv']);

		$content = array_merge($content, array(
			'total'		=> $i,
			'last'		=> $last_time,
			'number'	=> $number,
		));

		return $content;
	}

	private function get_avatar_row($row, $sort)
	{
		if (!$this->config['shout_avatar'] || !$this->config['allow_avatar'])
		{
			return false;
		}
		$avatar = '';
		$popup = ($sort === 1) ? true : false;
		if (!$row['shout_user_id'] && $row['shout_robot_user'])
		{
			$row_avatar = array(
				'user_id'				=> $row['x_user_id'],
				'username'				=> $row['x_username'],
				'user_type'				=> $row['x_user_type'],
				'user_avatar'			=> $row['x_user_avatar'],
				'user_avatar_type'		=> $row['x_user_avatar_type'],
				'user_avatar_width'		=> $row['x_user_avatar_width'],
				'user_avatar_height'	=> $row['x_user_avatar_height'],
			);
			$avatar = $this->shout_user_avatar($row_avatar, $this->config['shout_avatar_height'], false, $popup);
		}
		else
		{
			$avatar = $this->shout_user_avatar($row, $this->config['shout_avatar_height'], false, $popup);
		}

		return $avatar;
	}

	private function get_permissions_row($row, $perm, $val)
	{
		if ($val['is_user'])
		{
			if ($perm['delete_all'] || ((int) $row['shout_user_id'] === $val['userid']) && $perm['delete'])
			{
				$row['delete'] = true;
			}
			if ($perm['edit_all'] || ((int) $row['shout_user_id'] === $val['userid']) && $perm['edit'])
			{
				$row['edit'] = true;
				$row['msg_plain'] = $row['shout_text'];
				decode_message($row['msg_plain'], $row['shout_bbcode_uid']);
			}
			if ($perm['info_all'] || ((int) $row['shout_user_id'] === $val['userid']) && $perm['info'])
			{
				$row['show_ip'] = true;
				$row['on_ip'] = $row['shout_ip'];
			}
		}

		return $row;
	}

	private function get_shout_time($sql_where, $table)
	{
		$sql = $this->db->sql_build_query('SELECT', array(
			'SELECT'	=> 's.shout_time',
			'FROM'		=> array($table => 's'),
			'WHERE'		=> $sql_where,
			'ORDER_BY'	=> 's.shout_id DESC',
		));
		$result_time = $this->shout_sql_query($sql, true, 1);
		$last_time = $this->db->sql_fetchfield('shout_time');
		$this->db->sql_freeresult($result_time);

		// check just with the last 4 numbers
		return substr($last_time, 6, 4);
	}

	private function shout_extract_dateformat($is_user)
	{
		$dateformat = $this->config['shout_dateformat'];
		if ($is_user)
		{
			$data = json_decode($this->user->data['user_shoutbox']);
			$dateformat = ($data->dateformat !== '') ? $data->dateformat : $dateformat;
		}

		return (string) $dateformat;
	}

	private function shout_extract_permissions($auth)
	{
		// Prevents some errors for the allocation of permissions
		$data = array(
			'edit'		=> $this->auth->acl_get('u_shout_edit'),
			'delete'	=> $this->auth->acl_get('u_shout_delete_s'),
			'info'		=> $this->auth->acl_get('u_shout_info_s'),
			'edit_all'	=> false,
			'delete_all'=> false,
			'info_all'	=> false,
		);

		// If someone can edit all messages, he can edit its own messages :)
		if ($this->auth->acl_get('m_shout_edit_mod') || $this->auth->acl_get("a_shout{$auth}"))
		{
			$data['edit'] = $data['edit_all'] = true;
		}

		// If someone can delete all messages, he can delete its own messages :)
		if ($this->auth->acl_get('m_shout_delete') || $this->auth->acl_get("a_shout{$auth}"))
		{
			$data['delete'] = $data['delete_all'] = true;
		}

		// If someone can view all ip, he can view its own ip :)
		if ($this->auth->acl_get('m_shout_info') || $this->auth->acl_get("a_shout{$auth}"))
		{
			$data['info'] = $data['info_all'] = true;
		}

		return $data;
	}

	private function shout_pagination($sql_where, $table, $priv)
	{
		$sql = $this->db->sql_build_query('SELECT', array(
			'SELECT'	=> 'COUNT(s.shout_id) as nr',
			'FROM'		=> array($table => 's'),
			'WHERE'		=> $sql_where,
		));
		$result = $this->db->sql_query($sql);
		$nb = (int) $this->db->sql_fetchfield('nr');
		$this->db->sql_freeresult($result);

		// Limit the number of messages to display
		$max_number = (int) $this->config["shout_max_posts_on{$priv}"];
		if ($max_number > 0)
		{
			$nb = ($nb > $max_number) ? $max_number : $nb;
		}

		return $nb;
	}

	private function shout_sql_where($is_user, $userid, $on_bot)
	{
		if (!$on_bot)
		{
			$sql_where = 's.shout_robot = 0';
		}
		else
		{
			// Read the forums permissions
			$sql_where = $this->auth->acl_getf_global('f_read') ? $this->db->sql_in_set('s.shout_forum', array_keys($this->auth->acl_getf('f_read', true)), false, true) . ' OR s.shout_forum = 0' : 's.shout_forum = 0';
		}

		// Add personal messages if needed
		if (!$is_user)
		{
			$sql_where .= ' AND s.shout_inp = 0';
		}
		else
		{
			$sql_where .= " AND (s.shout_inp = 0 OR (s.shout_inp = $userid OR s.shout_user_id = $userid))";
		}

		return $sql_where;
	}

	/*
	 * Display user avatar with resizing
	 * Add avatar type for robot, users with no avatar and anonymous
	 * Add title with username
	 * Return string or false
	 */
	public function shout_user_avatar($row, $height, $force = false, $popup = false)
	{
		if (!$force)
		{
			if (!$this->config['shout_avatar'] || !$this->config['allow_avatar'])
			{
				return false;
			}
		}
		if (!$row['user_id'] && $this->config['shout_avatar_robot'])
		{
			$val_src = $this->ext_path . 'images/' . $this->config['shout_avatar_img_robot'];
			$val_alt = $this->language->lang('SHOUT_AVATAR_TITLE', $this->config['shout_name_robot']);
		}
		else if ($row['user_id'] == ANONYMOUS && $this->config['shout_avatar_user'])
		{
			$val_src = $this->ext_path . 'images/anonym.webp';
			$val_alt = $this->language->lang('SHOUT_AVATAR_TITLE', $this->language->lang('GUEST'));
		}
		else if ($row['user_id'] && !$row['user_avatar'] && $this->config['shout_avatar_user'])
		{
			$val_src = $this->ext_path . 'images/' . $this->config['shout_avatar_img'];
			$val_alt = $this->language->lang('SHOUT_AVATAR_NONE', $row['username']);
		}
		else if ($row['user_id'] && $row['user_avatar'] && $row['user_avatar_height'])
		{
			$avatar_height = ($row['user_avatar_height'] > $height) ? $height : $row['user_avatar_height'];
			$row['user_avatar_width'] = round($avatar_height / $row['user_avatar_height'] * $row['user_avatar_width']);
			$row['user_avatar_height'] = $avatar_height;
			$avatar = $this->replace_shout_url(phpbb_get_user_avatar($row, $this->language->lang('SHOUT_AVATAR_TITLE', $row['username'])));
			$avatar = str_replace('alt="', 'title="' . $this->language->lang('SHOUT_AVATAR_TITLE', $row['username']) . '" alt="', $avatar);
			if ($popup)
			{
				$avatar = str_replace('class="avatar', 'class="avatar popup-avatar', $avatar);
			}

			return $avatar;
		}
		else
		{
			return false;
		}

		$row = array(
			'avatar'		=> $val_src,
			'avatar_type'	=> 'avatar.driver.upload',
			'avatar_height'	=> $height,
			'avatar_width'	=> '',
		);
		$avatar = phpbb_get_user_avatar($row, $val_alt);
		$avatar = str_replace(array('./download/file.php?avatar=', 'alt="'), array('', 'title="' . $val_alt . '" alt="'), $avatar);
		if ($popup)
		{
			$avatar = str_replace('class="avatar', 'class="avatar popup-avatar', $avatar);
		}

		return $this->replace_shout_url($avatar);
	}

	public function build_adm_sound_select($sort)
	{
		$actual = $this->config["shout_sound_{$sort}"];
		$soundlist = $this->filelist_all($this->ext_path, 'sounds/', 'mp3');
		if (sizeof($soundlist))
		{
			$select = (!$actual) ? ' selected="selected"' : '';
			$sound_select = '<option value="0"' . $select . '>' . $this->language->lang('SHOUT_SOUND_EMPTY') . '</option>';
			foreach ($soundlist as $key => $sounds)
			{
				$sounds = str_replace('.mp3', '', $sounds);
				natcasesort($sounds);
				foreach ($sounds as $sound)
				{
					$selected = ($sound === $actual) ? ' selected="selected"' : '';
					$sound_select .= '<option title="' . $sound . '" value="' . $sound . '"' . $selected . '>' . $sound . "</option>\n";
				}
			}
			return $sound_select;
		}

		return false;
	}

	private function build_sound_select($actual, $sort)
	{
		$soundlist = $this->filelist_all($this->ext_path, 'sounds/', 'mp3');

		$title = ($actual == 1) ? $this->language->lang('SHOUT_SOUND_EMPTY') : $actual;
		$select = ($actual == 1) ? ' selected="selected"' : '';
		$sound_select = '<select title="' . $title . '" id="shout_sound_' . $sort . '" name="shout_sound_' . $sort . '" onchange="configs.changeValue(this.value,\'sound_' . $sort . '\');">';
		$sound_select .= '<option value="1"' . $select . '>' . $this->language->lang('SHOUT_SOUND_EMPTY') . '</option>';
		foreach ($soundlist as $key => $sounds)
		{
			$sounds = str_replace('.mp3', '', $sounds);
			natcasesort($sounds);
			foreach ($sounds as $sound)
			{
				$selected = ($sound == $actual) ? ' selected="selected"' : '';
				$sound_select .= '<option title="' . $sound . '" value="' . $sound . '"' . $selected . '>' . $sound . '</option>';
			}
		}
		$sound_select .= '</select>';

		return $sound_select;
	}

	public function plural($lang, $nr, $second = '')
	{
		$text = $lang;
		$text .= ($nr > 1) ? 'S' : '';
		$text .= ($second) ? $second : '';

		return $text;
	}

	public function purge_shout_admin($sort, $priv)
	{
		$sort = str_replace('purge_', '', $sort);
		$shoutbox_table = $this->shoutbox_table;
		$val_priv = $val_priv_on = '';
		if ($priv)
		{
			$shoutbox_table = $this->shoutbox_priv_table;
			$val_priv = '_priv';
			$val_priv_on = '_PRIV';
		}

		$sql = 'DELETE FROM ' . $shoutbox_table . " WHERE shout_robot = $sort";
		$this->db->sql_query($sql);
		$deleted = $this->db->sql_affectedrows();

		if ($deleted)
		{
			$this->config->increment("shout_del_purge{$val_priv}", $deleted, true);
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, "LOG_PURGE_SHOUTBOX{$val_priv_on}_ROBOT", time(), array($deleted));
			$this->post_robot_shout(0, $this->user->ip, $priv, true, true);
			return false;
		}
		else
		{
			return true;
		}
	}

	public function filelist_all($rootdir, $dir = '', $type = '', $sort_values = true)
	{
		if (!function_exists('filelist'))
		{
			include($this->root_path . 'includes/functions_admin.' . $this->php_ext);
		}

		$type = ($type === '') ? 'gif|jpg|jpeg|png|webp|jp2|j2k|jpf|jpm|jpg2|j2c|jpc' : $type;
		$list = filelist($rootdir, $dir, $type);
		if ($sort_values)
		{
			$list = array_values($list);
		}

		return $list;
	}

	public function build_select_img($rootdir, $path, $sort, $panel = false, $type = '')
	{
		$select = '';
		$imglist = $this->filelist_all($rootdir, $path, $type, true);
		foreach ($imglist as $key => $image)
		{
			natcasesort($image);
			foreach ($image as $img)
			{
				$on_img = $img;
				$img = substr($img, 0, strrpos($img, '.'));
				$value = ($panel) ? $on_img : $img;
				$selected = ($this->config[$sort] == $value) ? ' selected="selected"' : '';
				$select .= '<option title="' . $on_img . '" value="' . $value . '"' . $selected . '>' . $img . "</option>\n";
			}
		}

		return $select;
	}

	public function build_select_position($value, $sort = false, $acp = false)
	{
		$option = [
			'data'	=> '',
			'title'	=> '',
		];
		// / No selected_3 because it's the defaut
		$selected_0 = $selected_1 = $selected_2 = $selected_4 = '';
		switch ($value)
		{
			case 0:
				$selected_0 = ' selected="selected"';
				$option['title'] = $this->language->lang('SHOUT_POSITION_NONE');
			break;
			case 1:
				$selected_1 = ' selected="selected"';
				$option['title'] = $this->language->lang('SHOUT_POSITION_TOP');
			break;
			case 2:
				$selected_2 = ' selected="selected"';
				$option['title'] = $this->language->lang('SHOUT_POSITION_END');
			break;
			case 4:
				$selected_4 = ' selected="selected"';
				$option['title'] = $this->language->lang('SHOUT_POSITION_AFTER');
			break;
		}

		if (!$acp)
		{
			$option['data'] = '<option title="' . $this->language->lang('SHOUT_POSITION_NONE') . '" value="0"' . $selected_0 . '>' . $this->language->lang('SHOUT_POSITION_NONE') . '</option>';
		}
		$option['data'] .= '<option title="' . $this->language->lang('SHOUT_POSITION_TOP') . '" value="1"' . $selected_1 . '>' . $this->language->lang('SHOUT_POSITION_TOP') . '</option>';
		if ($sort)
		{
			$option['data'] .= '<option title="' . $this->language->lang('SHOUT_POSITION_AFTER') . '" value="4"' . $selected_4 . '>' . $this->language->lang('SHOUT_POSITION_AFTER') . '</option>';
		}
		$option['data'] .= '<option title="' . $this->language->lang('SHOUT_POSITION_END') . '" value="2"' . $selected_2 . '>' . $this->language->lang('SHOUT_POSITION_END') . '</option>';

		return $option;
	}

	public function build_dateformat_option($dateformat, $adm = false)
	{
		$options = '';
		$on_select = false;
		foreach ($this->language->lang_raw('dateformats') as $format => $null)
		{
			$selected = ($format === $dateformat) ? ' selected="selected"' : '';
			$on_select = ($format === $dateformat) ? true : $on_select;
			$options .= '<option value="' . $format . '"' . $selected . '>';
			$options .= $this->user->format_date(time(), $format, false) . ((strpos($format, '|') !== false) ? $this->language->lang('VARIANT_DATE_SEPARATOR') . $this->user->format_date(time(), $format, true) : '');
			$options .= '</option>';
		}
		$select = (!$on_select) ? ' selected="selected"' : '';
		$options .= '<option value="custom"' . $select . '>' . $this->language->lang('CUSTOM_DATEFORMAT') . '</option>';

		return $options;
	}

	private function return_bool($option)
	{
		return ($option) ? 'true' : 'false';
	}

	public function active_config_shoutbox($user_id)
	{
		if (!$this->user->data['is_registered'] || $this->user->data['is_bot'] || !$this->auth->acl_get('u_shout_post'))
		{
			throw new http_exception(403, 'NOT_AUTHORISED');
		}

		if ($this->request->is_set_post('submit'))
		{
			$user_shout = json_encode(array(
				'user'			=> $this->request->variable('user_sound', 2),
				'new'			=> $this->request->variable('shout_sound_new', 'N', true),
				'new_priv'		=> $this->request->variable('shout_sound_new_priv', 'N', true),
				'error'			=> $this->request->variable('shout_sound_error', 'N', true),
				'del'			=> $this->request->variable('shout_sound_del', 'N', true),
				'add'			=> $this->request->variable('shout_sound_add', 'N', true),
				'edit'			=> $this->request->variable('shout_sound_edit', 'N', true),
				'index'			=> $this->request->variable('position_index', 3),
				'forum'			=> $this->request->variable('position_forum', 3),
				'topic'			=> $this->request->variable('position_topic', 3),
			));
			$user_shoutbox = json_encode(array(
				'bar'			=> $this->request->variable('shout_bar', 2),
				'bar_pop'		=> $this->request->variable('shout_bar_pop', 2),
				'bar_priv'		=> $this->request->variable('shout_bar_priv', 2),
				'defil'			=> $this->request->variable('shout_defil', 2),
				'defil_pop'		=> $this->request->variable('shout_defil_pop', 2),
				'defil_priv'	=> $this->request->variable('shout_defil_priv', 2),
				'panel'			=> $this->request->variable('shout_panel', 2),
				'panel_float'	=> $this->request->variable('shout_panel_float', 2),
				'dateformat'	=> $this->request->variable('dateformat', '', true),
			));

			$sql = 'UPDATE ' . USERS_TABLE . "
				SET user_shout = '" . $this->db->sql_escape($user_shout) . "', user_shoutbox = '" . $this->db->sql_escape($user_shoutbox) . "'
					WHERE user_id = $user_id";
			$this->db->sql_query($sql);

			redirect($this->helper->route('sylver35_breizhshoutbox_configshout', array('id' => $user_id)));
		}
		else if ($this->request->is_set_post('retour'))
		{
			$user_shout = json_encode(array(
				'user'			=> 2,
				'new'			=> 'N',
				'new_priv'		=> 'N',
				'error'			=> 'N',
				'del'			=> 'N',
				'add'			=> 'N',
				'edit'			=> 'N',
				'index'			=> 3,
				'forum'			=> 3,
				'topic'			=> 3,
			));
			$user_shoutbox = json_encode(array(
				'bar'			=> 2,
				'bar_pop'		=> 2,
				'bar_priv'		=> 2,
				'defil'			=> 2,
				'defil_pop'		=> 2,
				'defil_priv'	=> 2,
				'panel'			=> 2,
				'panel_float'	=> 2,
				'dateformat'	=> '',
			));

			$sql = 'UPDATE ' . USERS_TABLE . "
				SET user_shout = '" . $this->db->sql_escape($user_shout) . "', user_shoutbox = '" . $this->db->sql_escape($user_shoutbox) . "'
					WHERE user_id = $user_id";
			$this->db->sql_query($sql);

			redirect($this->helper->route('sylver35_breizhshoutbox_configshout', array('id' => $user_id)));
		}
		else
		{
			$this->data_config_shoutbox($user_id);
		}
	}

	private function set_user_option($option, $config, $sort)
	{
		$value = '';
		switch ($sort)
		{
			case 1:
				if ($option === 'N')
				{
					$value = (string) $this->config[$config];
				}
				else
				{
					$value = (string) $option;
				}
			break;
			case 2:
				if ($option === 3)
				{
					$value = (int) $this->config[$config];
				}
				else
				{
					$value = (int) $option;
				}
			break;
			case 3:
				if ($option === 2)
				{
					$value = (bool) $this->config[$config];
				}
				else
				{
					$value = (bool) $option;
				}
			break;
			case 4:
				if ($option === 2)
				{
					$value = (int) $this->config[$config];
				}
				else
				{
					$value = (int) $option;
				}
			break;
			case 5:
				if ($option === '')
				{
					$value = (string) $this->config[$config];
				}
				else
				{
					$value = (string) $option;
				}
			break;
		}
		return $value;
	}

	private function data_config_shoutbox($user_id)
	{
		$this->language->add_lang('ucp');
		$other = false;
		$username = '';
		if ($user_id === $this->user->data['user_id'])
		{
			$user_shout = json_decode($this->user->data['user_shout']);
			$user_shoutbox = json_decode($this->user->data['user_shoutbox']);
			$auth_pop = $this->auth->acl_get('u_shout_popup');
			$auth_priv = $this->auth->acl_get('u_shout_priv');
		}
		else
		{
			$other = true;
			$sql = 'SELECT username, user_shout, user_shoutbox
				FROM ' . USERS_TABLE . '
					WHERE user_id = ' . $user_id;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);
			$username = $row['username'];
			$user_shout = json_decode($row['user_shout']);
			$user_shoutbox = json_decode($row['user_shoutbox']);
			$auth_pop = $this->auth->acl_get_list($user_id, 'u_shout_popup');
			$auth_priv = $this->auth->acl_get_list($user_id, 'u_shout_priv');
		}

		$user_shout->user = $this->set_user_option($user_shout->user, 'shout_sound_on', 4);
		$user_shout->new = $this->set_user_option($user_shout->new, 'shout_sound_new', 1);
		$user_shout->new_priv = $this->set_user_option($user_shout->new_priv, 'shout_sound_new_priv', 1);
		$user_shout->error = $this->set_user_option($user_shout->error, 'shout_sound_error', 1);
		$user_shout->del = $this->set_user_option($user_shout->del, 'shout_sound_del', 1);
		$user_shout->add = $this->set_user_option($user_shout->add, 'shout_sound_add', 1);
		$user_shout->edit = $this->set_user_option($user_shout->edit, 'shout_sound_edit', 1);
		$user_shoutbox->bar = $this->set_user_option($user_shoutbox->bar, 'shout_bar_option', 3);
		$user_shoutbox->bar_pop = $this->set_user_option($user_shoutbox->bar_pop, 'shout_bar_option_pop', 3);
		$user_shoutbox->bar_priv = $this->set_user_option($user_shoutbox->bar_priv, 'shout_bar_option_priv', 3);
		$user_shoutbox->defil = $this->set_user_option($user_shoutbox->defil, 'shout_defil', 3);
		$user_shoutbox->defil_pop = $this->set_user_option($user_shoutbox->defil_pop, 'shout_defil_pop', 3);
		$user_shoutbox->defil_priv = $this->set_user_option($user_shoutbox->defil_priv, 'shout_defil_priv', 3);
		$user_shoutbox->panel = $this->set_user_option($user_shoutbox->panel, 'shout_panel', 3);
		$user_shoutbox->panel_float = $this->set_user_option($user_shoutbox->panel_float, 'shout_panel_float', 3);
		$user_shoutbox->dateformat = $this->set_user_option($user_shoutbox->dateformat, 'shout_dateformat', 5);
		$select_index = $this->build_select_position($this->set_user_option($user_shout->index, 'shout_position_index', 2), true);
		$select_forum = $this->build_select_position($this->set_user_option($user_shout->forum, 'shout_position_forum', 2));
		$select_topic = $this->build_select_position($this->set_user_option($user_shout->topic, 'shout_position_topic', 2));
		$version = $this->get_version();

		$this->template->assign_vars(array(
			'IN_SHOUT_CONFIG'		=> true,
			'USER_ID'				=> $this->user->data['user_id'],
			'USERNAME'				=> $other,
			'TITLE_PANEL'			=> ($other) ? $this->language->lang('SHOUT_PANEL_TO_USER', $username) : $this->language->lang('SHOUT_PANEL_USER'),
			'S_POP'					=> $auth_pop,
			'S_PRIVATE'				=> $auth_priv,
			'SOUND_NEW_DISP'		=> $user_shout->user && $user_shout->new !== '1',
			'SOUND_NEW_PRIV_DISP'	=> $user_shout->user && $user_shout->new_priv !== '1',
			'SOUND_DEL_DISP'		=> $user_shout->user && $user_shout->del !== '1',
			'SOUND_ADD_DISP'		=> $user_shout->user && $user_shout->add !== '1',
			'SOUND_EDIT_DISP'		=> $user_shout->user && $user_shout->edit !== '1',
			'SOUND_ERROR_DISP'		=> $user_shout->error !== '1',
			'NEW_SOUND'				=> $this->build_sound_select($user_shout->new, 'new'),
			'NEW_SOUND_PRIV'		=> $this->build_sound_select($user_shout->new_priv, 'new_priv'),
			'ERROR_SOUND'			=> $this->build_sound_select($user_shout->error, 'error'),
			'DEL_SOUND'				=> $this->build_sound_select($user_shout->del, 'del'),
			'ADD_SOUND'				=> $this->build_sound_select($user_shout->add, 'add'),
			'EDIT_SOUND'			=> $this->build_sound_select($user_shout->edit, 'edit'),
			'USER_SOUND_YES'		=> $user_shout->user,
			'USER_SOUND_INFO'		=> $user_shout->new,
			'USER_SOUND_INFO_PRIV'	=> $user_shout->new_priv,
			'USER_SOUND_INFO_E'		=> $user_shout->error,
			'USER_SOUND_INFO_D'		=> $user_shout->del,
			'USER_SOUND_INFO_A'		=> $user_shout->add,
			'USER_SOUND_INFO_ED'	=> $user_shout->edit,
			'SHOUT_BAR'				=> $user_shoutbox->bar,
			'SHOUT_BAR_POP'			=> $user_shoutbox->bar_pop,
			'SHOUT_BAR_PRIV'		=> $user_shoutbox->bar_priv,
			'SHOUT_DEFIL'			=> $user_shoutbox->defil,
			'SHOUT_DEFIL_POP'		=> $user_shoutbox->defil_pop,
			'SHOUT_DEFIL_PRIV'		=> $user_shoutbox->defil_priv,
			'SHOUT_PANEL'			=> $user_shoutbox->panel,
			'SHOUT_PANEL_FLOAT'		=> $user_shoutbox->panel_float,
			'SELECT_ON_INDEX'		=> $select_index['data'],
			'SELECT_ON_FORUM'		=> $select_forum['data'],
			'SELECT_ON_TOPIC'		=> $select_topic['data'],
			'TITLE_ON_INDEX'		=> $select_index['title'],
			'TITLE_ON_FORUM'		=> $select_forum['title'],
			'TITLE_ON_TOPIC'		=> $select_topic['title'],
			'SHOUT_EXT_PATH'		=> $this->ext_path_web,
			'DATE_FORMAT'			=> $user_shoutbox->dateformat,
			'DATE_FORMAT_EX'		=> $this->user->format_date(time() - 60 * 61, $user_shoutbox->dateformat),
			'DATE_FORMAT_EX2'		=> $this->user->format_date(time() - 60 * 60 * 60, $user_shoutbox->dateformat),
			'S_DATEFORMAT_OPTIONS'	=> $this->build_dateformat_option($user_shoutbox->dateformat),
			'U_SHOUT_ACTION'		=> $this->helper->route('sylver35_breizhshoutbox_configshout', array('id' => $user_id)),
			'U_DATE_URL' 			=> $this->helper->route('sylver35_breizhshoutbox_ajax', array('mode' => 'date_format')),
			'SHOUTBOX_VERSION'		=> $this->language->lang('SHOUTBOX_VERSION_ACP_COPY', $version['homepage'], $version['version']),
		));
	}

	public function javascript_shout($sort_of)
	{
		$version = $this->get_version();
		$data = array(
			'sort'		=> '',
			'sort_perm'	=> '_manage',
			'sort_of'	=> $sort_of,
			'private'	=> false,
			'creator'	=> $this->smiliecreator_exist(),
			'category'	=> $this->smiliescategory_exist(),
			'is_mobile'	=> $this->shout_is_mobile(),
			'user_id'	=> (int) $this->user->data['user_id'],
			'is_user'	=> (bool) $this->user->data['is_registered'] && !$this->user->data['is_bot'],
			'version'	=> (string) $version['version'],
			'homepage'	=> (string) $version['homepage'],
		);

		switch ($sort_of)
		{
			// Popup shoutbox
			case 1:
				$data['sort_p'] = '_pop';
			break;
			// Normal shoutbox
			case 2:
				$data['sort_p'] = '';
			break;
			// Private shoutbox
			case 3:
				$data['private'] = true;
				$data['sort'] = $data['sort_p'] = $data['sort_perm'] = '_priv';
			break;
		}

		// Construct the user's preferences
		$result = $this->create_user_preferences($data, $sort_of);

		$this->template->assign_vars(array(
			'LIST_SETTINGS_AUTH'		=> $this->settings_auth_to_javascript($result['data']),
			'LIST_SETTINGS_STRING'		=> $this->settings_to_javascript($result['data'], $result['sound']),
			'LIST_SETTINGS_LANG'		=> $this->lang_to_javascript($result['data']),
			'ON_SHOUT_DISPLAY'			=> true,
		));
	}

	private function create_user_preferences($data, $sort_of)
	{
		if ($data['is_user'])
		{
			$user_shout = json_decode($this->user->data['user_shout']);
			$user_shoutbox = json_decode($this->user->data['user_shoutbox']);
			$user_shout->user = $this->set_user_option($user_shout->user, 'shout_sound_on', 4);

			$sound = array(
				'new'		=> $this->set_user_option($user_shout->new, 'shout_sound_new', 1),
				'new_priv'	=> $this->set_user_option($user_shout->new_priv, 'shout_sound_new_priv', 1),
				'error'		=> $this->set_user_option($user_shout->error, 'shout_sound_error', 1),
				'del'		=> $this->set_user_option($user_shout->del, 'shout_sound_del', 1),
				'add'		=> $this->set_user_option($user_shout->add, 'shout_sound_add', 1),
				'edit'		=> $this->set_user_option($user_shout->edit, 'shout_sound_edit', 1),
				'active'	=> $user_shout->user === 1,
			);

			$data = array_merge($data, array(
				'refresh'					=> $this->config['shout_temp_users'] * 1000,
				'inactiv'					=> ($this->auth->acl_get('u_shout_inactiv') || $data['private']) ? 0 : $this->config['shout_inactiv_member'],
				'dateformat'				=> $this->set_user_option($user_shoutbox->dateformat, 'shout_dateformat', 5),
				'shout_bar_option'			=> $this->set_user_option($user_shoutbox->bar, 'shout_bar_option', 3),
				'shout_bar_option_pop'		=> $this->set_user_option($user_shoutbox->bar_pop, 'shout_bar_option_pop', 3),
				'shout_bar_option_priv'		=> $this->set_user_option($user_shoutbox->bar_priv, 'shout_bar_option_priv', 3),
				'shout_defil'				=> $this->set_user_option($user_shoutbox->defil, 'shout_defil', 3),
				'shout_defil_pop'			=> $this->set_user_option($user_shoutbox->defil_pop, 'shout_defil_pop', 3),
				'shout_defil_priv'			=> $this->set_user_option($user_shoutbox->defil_priv, 'shout_defil_priv', 3),
			));
		}
		else
		{
			$data = array_merge($data, array(
				'refresh'					=> $this->config['shout_temp_anonymous'] * 1000,
				'inactiv'					=> $this->config['shout_inactiv_anony'],
				'dateformat'				=> $this->config['shout_dateformat'],
				'shout_bar_option'			=> $this->config['shout_bar_option'],
				'shout_bar_option_pop'		=> $this->config['shout_bar_option_pop'],
				'shout_bar_option_priv'		=> $this->config['shout_bar_option_priv'],
				'shout_defil'				=> $this->config['shout_defil'],
				'shout_defil_pop'			=> $this->config['shout_defil_pop'],
				'shout_defil_priv'			=> $this->config['shout_defil_priv'],
			));
			if ($this->user->data['is_bot'])
			{
				$data['refresh'] = 60 * 1000;
				// No sounds for bots, they have no ears [:-)
				$sound = array(
					'new_priv'	=> '',
					'new'		=> '',
					'error'		=> '',
					'del'		=> '',
					'add'		=> '',
					'edit'		=> '',
					'active'	=> false,
				);
			}
			else
			{
				$sound = array(
					'new_priv'	=> '',
					'new'		=> $this->config['shout_sound_new'],
					'error'		=> $this->config['shout_sound_error'],
					'del'		=> $this->config['shout_sound_del'],
					'add'		=> $this->config['shout_sound_add'],
					'edit'		=> $this->config['shout_sound_edit'],
					'active'	=> $this->config['shout_sound_on'] ? true : false,
				);
			}
		}
		$data['inactiv'] = (($data['inactiv'] > 0) && !$data['private']) ? round($data['inactiv'] * 60 / ($data['refresh'] / 1000)) : 0;

		return array(
			'data'	=> $data,
			'sound'	=> $sound,
		);
	}

	private function settings_auth_to_javascript($data)
	{
		// Display the rules if wanted
		$rules = $rules_open = false;
		if ($this->check_shout_rules($data['sort']) !== '')
		{
			$rules = true;
			// Display the rules opened by default if wanted
			$rules_open = ($this->config["shout_rules_open{$data['sort']}"] && $this->auth->acl_get('u_shout_post')) ? true : false;
		}

		$settings_auth = array(
			'inactivity'		=> $data['inactiv'],
			'requestOn'			=> $data['refresh'],
			'sortShoutNb'		=> $data['sort_of'],
			'userId'			=> $data['user_id'],
			'perPage'			=> $this->config["shout_num{$data['sort_p']}"],
			'maxPost'			=> $this->config['shout_max_post_chars'],
			'minName'			=> $this->config['min_name_chars'],
			'maxName'			=> $this->config['max_name_chars'],
			'isUser'			=> $this->return_bool($data['is_user']),
			'isGuest'			=> $this->return_bool($data['user_id'] === ANONYMOUS),
			'isRobot'			=> $this->return_bool($this->user->data['is_bot']),
			'isPriv'			=> $this->return_bool($data['private']),
			'rulesOk'			=> $this->return_bool($rules),
			'rulesOpen'			=> $this->return_bool($rules_open),
			'isMobile'			=> $this->return_bool($data['is_mobile']),
			'refresh'			=> $this->return_bool(strpos($data['dateformat'], '|') !== false),
			'seeButtons'		=> $this->return_bool($this->config['shout_see_buttons']),
			'buttonsLeft'		=> $this->return_bool($this->config['shout_see_buttons_left']),
			'barHaute'			=> $this->return_bool($data["shout_bar_option{$data['sort_p']}"]),
			'toBottom'			=> $this->return_bool($data["shout_defil{$data['sort_p']}"]),
			'buttonIp'			=> $this->return_bool($this->config['shout_see_button_ip']),
			'buttonCite'		=> $this->return_bool($this->config['shout_see_cite']),
			'endClassBg'		=> $this->return_bool($this->config["shout_button_background{$data['sort_p']}"]),
			'purgeOn'			=> $this->return_bool($this->auth->acl_get("a_shout{$data['sort_perm']}")),
			'onlineOk'			=> $this->return_bool($this->auth->acl_gets('u_viewprofile', 'a_user', 'a_useradd', 'a_userdel')),
			'postOk'			=> $this->return_bool($this->auth->acl_get('u_shout_post')),
			'limitPost'			=> $this->return_bool($this->auth->acl_get('u_shout_limit_post')),
			'smiliesOk'			=> $this->return_bool($this->auth->acl_get('u_shout_smilies')),
			'imageOk'			=> $this->return_bool($this->auth->acl_get('u_shout_image')),
			'colorOk'			=> $this->return_bool($this->auth->acl_get('u_shout_color')),
			'bbcodeOk'			=> $this->return_bool($this->auth->acl_get('u_shout_bbcode')),
			'charsOk'			=> $this->return_bool($this->auth->acl_get('u_shout_chars')),
			'popupOk'			=> $this->return_bool($this->auth->acl_get('u_shout_popup')),
			'formatOk'			=> $this->return_bool($this->auth->acl_get('u_shout_bbcode_change') && $data['is_user']),
			'privOk'			=> $this->return_bool($this->auth->acl_get('u_shout_priv') && $data['is_user']),
			'creator'			=> $this->return_bool($data['creator']),
			'category'			=> $this->return_bool($data['category']),
		);

		$i = 0;
		$settings = "var config = {\n		";
		foreach ($settings_auth as $key => $value)
		{
			if ($i > 18)
			{
				$settings .= "\n		";
				$i = 0;
			}
			$settings .= $key . ':' . $value . ', ';
			$i++;
		}

		return $settings;
	}

	private function settings_to_javascript($data, $sound)
	{
		$settings_string = array(
			'cookieName'		=> $this->config['cookie_name'] . '_',
			'cookieDomain'		=> '; domain=' . $this->config['cookie_domain'] . ($this->config['cookie_secure'] ? '; secure' : ''),
			'cookiePath'		=> '; path=' . $this->config['cookie_path'],
			'enableSound'		=> ($sound['active']) ? '1' : '0',
			'extensionUrl'		=> $this->replace_shout_url($this->ext_path_web),
			'userTimezone'		=> phpbb_format_timezone_offset($this->user->create_datetime()->getOffset()),
			'dateDefault'		=> $this->config['shout_dateformat'],
			'dateFormat'		=> $data['dateformat'],
			'newSound'			=> $sound["new{$data['sort']}"],
			'errorSound'		=> $sound['error'],
			'delSound'			=> $sound['del'],
			'addSound'			=> $sound['add'],
			'editSound'			=> $sound['edit'],
			'titleUrl'			=> $data['homepage'],
			'buttonBg'			=> ' button_background_' . $this->config["shout_color_background{$data['sort_p']}"],
			'shoutHeight'		=> $this->config["shout_height{$data['sort_p']}"],
			'widthPost'			=> $this->config["shout_width_post{$data['sort_p']}"],
			'popupWidth'		=> $this->config['shout_popup_width'],
			'popupHeight'		=> $this->config['shout_popup_height'],
			'direction'			=> $this->language->lang('SHOUT_DIRECTION'),
			'popupUrl'			=> $this->helper->route('sylver35_breizhshoutbox_popup'),
			'configUrl'			=> $this->helper->route('sylver35_breizhshoutbox_configshout', array('id' => $data['user_id'])),
			'checkUrl'			=> $this->helper->route('sylver35_breizhshoutbox_ajax', array('mode' => "check{$data['sort_p']}")),
			'viewUrl'			=> $this->helper->route('sylver35_breizhshoutbox_ajax', array('mode' => "view{$data['sort_p']}")),
			'postUrl'			=> $this->helper->route('sylver35_breizhshoutbox_ajax', array('mode' => 'post')),
			'smilUrl'			=> $this->helper->route('sylver35_breizhshoutbox_ajax', array('mode' => 'smilies')),
			'smilPopUrl'		=> $this->helper->route('sylver35_breizhshoutbox_ajax', array('mode' => 'smilies_popup')),
			'onlineUrl'			=> $this->helper->route('sylver35_breizhshoutbox_ajax', array('mode' => 'online')),
			'soundUrl'			=> $this->helper->route('sylver35_breizhshoutbox_ajax', array('mode' => 'action_sound')),
			'rulesUrl'			=> $this->helper->route('sylver35_breizhshoutbox_ajax', array('mode' => 'rules')),
			'postingUrl'		=> $this->helper->route('sylver35_breizhshoutbox_ajax', array('mode' => 'posting')),
			'questionUrl'		=> $this->helper->route('sylver35_breizhshoutbox_ajax', array('mode' => 'question')),
			'creatorUrl'		=> ($data['creator']) ? $this->helper->route('sylver35_smilecreator_controller') : '',
		);
		if ($data['is_user'])
		{
			$settings_string = array_merge($settings_string, array(
				'privUrl'		=> $this->helper->route('sylver35_breizhshoutbox_private'),
				'purgeUrl'		=> $this->helper->route('sylver35_breizhshoutbox_ajax', array('mode' => 'purge')),
				'purgeBotUrl'	=> $this->helper->route('sylver35_breizhshoutbox_ajax', array('mode' => 'purge_robot')),
				'actUrl'		=> $this->helper->route('sylver35_breizhshoutbox_ajax', array('mode' => 'action_user')),
				'actPostUrl'	=> $this->helper->route('sylver35_breizhshoutbox_ajax', array('mode' => 'action_post')),
				'actDelUrl'		=> $this->helper->route('sylver35_breizhshoutbox_ajax', array('mode' => 'action_del')),
				'actDelToUrl'	=> $this->helper->route('sylver35_breizhshoutbox_ajax', array('mode' => 'action_del_to')),
				'actRemoveUrl'	=> $this->helper->route('sylver35_breizhshoutbox_ajax', array('mode' => 'action_remove')),
				'citeUrl'		=> $this->helper->route('sylver35_breizhshoutbox_ajax', array('mode' => 'cite')),
				'ubbcodeUrl'	=> $this->helper->route('sylver35_breizhshoutbox_ajax', array('mode' => 'user_bbcode')),
				'persoUrl'		=> $this->helper->route('sylver35_breizhshoutbox_ajax', array('mode' => 'charge_bbcode')),
				'deleteUrl'		=> $this->helper->route('sylver35_breizhshoutbox_ajax', array('mode' => 'delete')),
				'editUrl'		=> $this->helper->route('sylver35_breizhshoutbox_ajax', array('mode' => 'edit')),
				'dateUrl'		=> $this->helper->route('sylver35_breizhshoutbox_ajax', array('mode' => 'date_format')),
				'authUrl'		=> $this->helper->route('sylver35_breizhshoutbox_ajax', array('mode' => 'auth')),
			));
		}

		$i = 0;
		$settings = "	";
		foreach ($settings_string as $key => $value)
		{
			$settings .= $key . ":'" . $value . "', ";
			if ($i > 9)
			{
				$settings .= "\n		";
				$i = 0;
			}
			$i++;
		}
		$settings .= "\n	};";

		return $settings;
	}

	private function lang_to_javascript($data)
	{
		if ($data['creator'])
		{
			$this->language->add_lang('smilie_creator', 'sylver35/smilecreator');
		}
		$this->config['shout_title'] = (!$this->config['shout_title']) ? $this->language->lang('SHOUT_START') : $this->config['shout_title'];
		$this->config['shout_title_priv'] = (!$this->config['shout_title_priv']) ? $this->language->lang('SHOUTBOX_SECRET') : $this->config['shout_title_priv'];

		$lang_shout = array(
			'LOADING'				=> $this->language->lang('SHOUT_LOADING'),
			'TITLE'					=> $this->config["shout_title{$data['sort']}"],
			'SERVER_ERR'			=> $this->language->lang('SERVER_ERR'),
			'JS_ERR'				=> $this->language->lang('JS_ERR'),
			'ERROR'					=> $this->language->lang('ERROR'),
			'LINE'					=> $this->language->lang('LINE'),
			'FILE'					=> $this->language->lang('FILE'),
			'DETAILS'				=> $this->language->lang('POST_DETAILS'),
			'PRINT_VER'				=> $this->language->lang('SHOUTBOX_VER', $data['version']),
			'MESSAGE'				=> $this->language->lang('SHOUT_MESSAGE'),
			'MESSAGES'				=> $this->language->lang('SHOUT_MESSAGES'),
			'SEPARATOR'				=> $this->language->lang('COMMA_SEPARATOR'),
			'SHOUT_SEP'				=> $this->language->lang('SHOUT_SEP'),
			'MSG_DEL_DONE'			=> $this->language->lang('MSG_DEL_DONE'),
			'NO_MESSAGE'			=> $this->language->lang('SHOUT_NO_MESSAGE'),
			'PAGE'					=> $this->language->lang('SHOUT_PAGE'),
			'NO_EDIT'				=> $this->language->lang('NO_SHOUT_EDIT'),
			'CANCEL'				=> $this->language->lang('CANCEL'),
			'NEXT'					=> $this->language->lang('NEXT'),
			'PREVIOUS'				=> $this->language->lang('PREVIOUS'),
			'AUTO'					=> $this->language->lang('SHOUT_AUTO'),
			'BBCODE_CLOSE'			=> $this->language->lang('SHOUT_DIV_BBCODE_CLOSE'),
			'ACTION_MSG'			=> $this->language->lang('SHOUT_ACTION_MSG'),
			'OUT_TIME'				=> $this->language->lang('SHOUT_OUT_TIME'),
			'NO_SHOUT_DEL'			=> $this->language->lang('NO_SHOUT_DEL'),
			'NO_IP_PERM'			=> $this->language->lang('NO_SHOW_IP_PERM'),
			'SOUND'					=> $this->language->lang('SHOUT_CLICK_SOUND_ON'),
			'SOUND_OFF'				=> $this->language->lang('SHOUT_CLICK_SOUND_OFF'),
			'MESSAGE_EMPTY'			=> $this->language->lang('MESSAGE_EMPTY'),
			'DIV_CLOSE'				=> $this->language->lang('SHOUT_DIV_CLOSE'),
			'NO_POST_PERM'			=> $this->language->lang('NO_POST_PERM'),
			'NO_POP'				=> $this->language->lang('NO_SHOUT_POP'),
			'POST_MESSAGE'			=> $this->language->lang('POST_MESSAGE'),
			'POST_MESSAGE_ALT'		=> $this->language->lang('POST_MESSAGE_ALT'),
			'POSTED'				=> $this->language->lang('POSTED'),
			'POP'					=> $this->language->lang('SHOUT_POP'),
			'ONLINE'				=> $this->language->lang('SHOUT_ONLINE'),
			'ONLINE_CLOSE'			=> $this->language->lang('SHOUT_ONLINE_CLOSE'),
			'COLOR'					=> $this->language->lang('SHOUT_COLOR'),
			'NO_COLOR'				=> $this->language->lang('NO_SHOUT_COLOR'),
			'COLOR_CLOSE'			=> $this->language->lang('SHOUT_COLOR_CLOSE'),
			'SMILIES'				=> $this->language->lang('SMILIES'),
			'NO_SMILIES'			=> $this->language->lang('NO_SMILIES'),
			'SMILIES_CLOSE'			=> $this->language->lang('SMILIES_CLOSE'),
			'CHARS'					=> $this->language->lang('SHOUT_CHARS'),
			'CHARS_CLOSE'			=> $this->language->lang('SHOUT_CHARS_CLOSE'),
			'NO_CHARS'				=> $this->language->lang('NO_SHOUT_CHARS'),
			'RULES'					=> $this->language->lang('SHOUT_RULES'),
			'RULES_PRIV'			=> $this->language->lang('SHOUT_RULES_PRIV'),
			'RULES_CLOSE'			=> $this->language->lang('SHOUT_RULES_CLOSE'),
			'MORE_SMILIES'			=> $this->language->lang('SHOUT_MORE_SMILIES'),
			'MORE_SMILIES_ALT'		=> $this->language->lang('SHOUT_MORE_SMILIES_ALT'),
			'LESS_SMILIES'			=> $this->language->lang('SHOUT_LESS_SMILIES'),
			'LESS_SMILIES_ALT'		=> $this->language->lang('SHOUT_LESS_SMILIES_ALT'),
			'TOO_BIG'				=> $this->language->lang('SHOUT_TOO_BIG'),
			'TOO_BIG2'				=> $this->language->lang('SHOUT_TOO_BIG2'),
			'ACTION_CITE'			=> $this->language->lang('SHOUT_ACTION_CITE_M'),
			'CITE_ON'				=> $this->language->lang('SHOUT_ACTION_CITE_ON'),
			'SHOUT_CLOSE'			=> $this->language->lang('SHOUT_CLOSE'),
			'BBCODES'				=> $this->language->lang('SHOUT_BBCODES'),
			'BBCODES_CLOSE'			=> $this->language->lang('SHOUT_BBCODES_CLOSE'),
			'NO_BBCODE'				=> $this->language->lang('NO_SHOUT_BBCODE'),
			'SENDING'				=> $this->language->lang('SENDING'),
			'DATETIME_0'			=> $this->language->lang(['datetime', 'AGO', 0]),
			'DATETIME_1'			=> $this->language->lang(['datetime', 'AGO', 1]),
			'DATETIME_2'			=> $this->language->lang(['datetime', 'AGO', 2]),
			'DATETIME_3'			=> $this->language->lang(['datetime', 'TODAY']),
			'ROBOT_ON'				=> $this->language->lang('SHOUT_ROBOT_ON'),
			'ROBOT_OFF'				=> $this->language->lang('SHOUT_ROBOT_OFF'),
			'SHOUT_COOKIES'			=> $this->language->lang('SHOUT_COOKIES'),
			'CREATOR'				=> ($data['creator']) ? $this->language->lang('SMILIE_CREATOR') : '',
		);
		if (!$this->user->data['is_registered'])
		{
			$lang_shout = array_merge($lang_shout, array(
				'CLICK_HERE'			=> $this->language->lang('SHOUT_CLICK_HERE'),
				'CHOICE_NAME'			=> $this->language->lang('SHOUT_CHOICE_NAME'),
				'CHOICE_YES'			=> $this->language->lang('SHOUT_CHOICE_YES'),
				'AFFICHE'				=> $this->language->lang('SHOUT_AFFICHE'),
				'CACHE'					=> $this->language->lang('SHOUT_CACHE'),
				'CHOICE_NAME_ERROR'		=> $this->language->lang('SHOUT_CHOICE_NAME_ERROR'),
				'USERNAME_EXPLAIN'		=> $this->language->lang($this->config['allow_name_chars'] . '_EXPLAIN', $this->language->lang('CHARACTERS', (int) $this->config['min_name_chars']), $this->language->lang('CHARACTERS', (int) $this->config['max_name_chars'])),
			));
		}
		else if (!$this->user->data['is_bot'])
		{
			$lang_shout = array_merge($lang_shout, array(
				'MSG_ROBOT'				=> $this->language->lang('SHOUT_ACTION_MSG_ROBOT', $this->construct_action_shout(0)),
				'PERSO'					=> $this->language->lang('SHOUT_PERSO'),
				'SENDING_EDIT'			=> $this->language->lang('SENDING_EDIT'),
				'EDIT_DONE'				=> $this->language->lang('EDIT_DONE'),
				'SHOUT_DEL'				=> $this->language->lang('SHOUT_DEL'),
				'DEL_SHOUT'				=> $this->language->lang('DEL_SHOUT'),
				'IP'					=> $this->language->lang('SHOUT_IP'),
				'POST_IP'				=> $this->language->lang('SHOUT_POST_IP'),
				'ONE_OPEN'				=> $this->language->lang('ONLY_ONE_OPEN'),
				'EDIT'					=> $this->language->lang('EDIT'),
				'SHOUT_EDIT'			=> $this->language->lang('SHOUT_EDIT'),
				'PRIV'					=> $this->language->lang('SHOUT_PRIV'),
				'CONFIG_OPEN'			=> $this->language->lang('SHOUT_CONFIG_OPEN'),
				'USER_IGNORE'			=> $this->language->lang('SHOUT_USER_IGNORE'),
				'PURGE_ROBOT_ALT'		=> $this->language->lang('SHOUT_PURGE_ROBOT_ALT'),
				'PURGE_ROBOT_BOX'		=> $this->language->lang('SHOUT_PURGE_ROBOT_BOX'),
				'PURGE_ALT'				=> $this->language->lang('SHOUT_PURGE_ALT'),
				'PURGE_BOX'				=> $this->language->lang('SHOUT_PURGE_BOX'),
				'PURGE_PROCESS'			=> $this->language->lang('PURGE_PROCESS'),
			));
		}

		$i = 0;
		$settings = "var bzhLang = {\n		";
		foreach ($lang_shout as $key => $value)
		{
			$settings .= "'" . $key . "':" . json_encode($value) . ', ';
			if ($i > 7)
			{
				$settings .= "\n		";
				$i = 0;
			}
			$i++;
		}
		$settings .= "\n	};";

		return $settings;
	}
}
