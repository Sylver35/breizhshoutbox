<?php
/**
*
* @package Breizh Shoutbox Extension
* @copyright (c) 2019-2023 Sylver35  https://breizhcode.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace sylver35\breizhshoutbox\core;

use phpbb\exception\http_exception;
use sylver35\breizhshoutbox\core\work;
use sylver35\breizhshoutbox\core\robot;
use sylver35\breizhshoutbox\core\javascript;
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
	/* @var \sylver35\breizhshoutbox\core\work */
	protected $work;

	/* @var \sylver35\breizhshoutbox\core\robot */
	protected $robot;

	/* @var \sylver35\breizhshoutbox\core\javascript */
	protected $javascript;

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
	public function __construct(work $work, robot $robot, javascript $javascript, cache $cache, config $config, helper $helper, path_helper $path_helper, db $db, pagination $pagination, request $request, template $template, auth $auth, user $user, language $language, log $log, Container $phpbb_container, manager $ext_manager, phpbb_dispatcher $phpbb_dispatcher, $root_path, $php_ext, $shoutbox_table, $shoutbox_priv_table, $shoutbox_rules_table)
	{
		$this->work = $work;
		$this->robot = $robot;
		$this->javascript = $javascript;
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
	 * Get the adm path
	 * @return string
	 */
	public function adm_path()
	{
		return $this->root_path_web . $this->path_helper->get_adm_relative_path();
	}

	/**
	 * Display the shoutbox
	 */
	public function shout_display($sort_of)
	{
		/**
		 * You can use this event to display the shoutbox
		 *
		 * @event breizhshoutbox.shout_display_before
		 * @var	array	sort_of (1 = popup, 2 = normal, 3 = private)
		 * @since 1.8.1
		 */
		$vars = ['sort_of'];
		extract($this->phpbb_dispatcher->trigger_event('breizhshoutbox.shout_display_before', compact($vars)));

		$is_user = ($this->user->data['is_registered'] && !$this->user->data['is_bot']) ? true : false;
		$page = str_replace('.' . $this->php_ext, '', $this->user->page['page_name']);
		$is_mobile = $this->work->shout_is_mobile();
		$in_priv = ($sort_of === 3) ? true : false;
		$priv = ($in_priv) ? '_priv' : '';

		if (!$this->verify_display_shout($in_priv))
		{
			return;
		}

		// Define the username for anonymous here
		if (!$this->user->data['is_registered'])
		{
			$this->language->add_lang('ucp');
			$this->template->assign_var('SHOUT_USERNAME_EXPLAIN', $this->language->lang($this->config['allow_name_chars'] . '_EXPLAIN', $this->language->lang('CHARACTERS', (int) $this->config['min_name_chars']), $this->language->lang('CHARACTERS', (int) $this->config['max_name_chars'])));
			// Add form token for login box
			add_form_key('login', '_LOGIN');
		}
		else if ($is_user)
		{
			// Load the user's preferences
			$user_shout = json_decode($this->user->data['user_shout']);
			if ($user_shout->index != 3)
			{
				$this->config['shout_position_index'] = $this->work->set_user_option($user_shout->index, 'shout_index', 2);
				$this->config['shout_position_forum'] = $this->work->set_user_option($user_shout->forum, 'shout_forum', 2);
				$this->config['shout_position_topic'] = $this->work->set_user_option($user_shout->topic, 'shout_topic', 2);
			}
		}

		if (!$this->run_shout_display($page, $this->config['shout_position_index'], $this->config['shout_position_forum'], $this->config['shout_position_topic']))
		{
			return;
		}

		// Active lateral panel or not
		$panel = $this->get_panel($in_priv, $is_mobile);
		$this->config['shout_panel_auto'] = $panel['auto'];

		$this->template->assign_vars([
			'S_DISPLAY_SHOUTBOX'	=> true,
			'IN_SHOUT_POPUP'		=> $sort_of === 1,
			'PANEL_ALL'				=> $panel['active'],
			'S_IN_PRIV'				=> $in_priv,
			'ACTION_USERS_TOP'		=> ($this->auth->acl_gets(['u_shout_post_inp', 'a_', 'm_'])) ? true : false,
			'SHOUT_INDEX_POS'		=> $this->config['shout_position_index'],
			'SHOUT_FORUM_POS'		=> $this->config['shout_position_forum'],
			'SHOUT_TOPIC_POS'		=> $this->config['shout_position_topic'],
			'SHOUT_EXT_PATH'		=> $this->ext_path_web,
			'S_SHOUT_VERSION'		=> $this->work->get_version(true),
		]);

		// Active the posting form
		$this->shout_enable_posting($sort_of, $page, $is_mobile);
		// Create the script now
		$this->javascript->javascript_shout($sort_of);

		// Do the shoutbox Prune thang
		if ($this->config['shout_on_cron' . $priv] && ((int) $this->config['shout_max_posts' . $priv] === 0))
		{
			$this->execute_shout_cron($in_priv);
		}
		$this->robot->shout_run_robot();
	}

	private function verify_display_shout($in_priv)
	{
		$sort = ($in_priv) ? '_priv' : '_view';
		if (!$this->auth->acl_get('u_shout' . $sort))
		{
			$this->template->assign_vars([
				'S_DISPLAY_SHOUTBOX'	=> false,
			]);
			return false;
		}
		else if ($in_priv)
		{
			// Always post enter info in the private shoutbox -> toc toc toc, it's me ;)
			$this->robot->post_robot_shout($this->user->data['user_id'], $this->user->ip, true, false, false, false, false);
		}

		return true;
	}

	private function get_panel($in_priv, $is_mobile)
	{
		// Active lateral panel or not
		$panel = [
			'active'	=> false,
			'auto'		=> false,
		];
		if ($this->auth->acl_get('u_shout_lateral') && !$is_mobile)
		{
			// Activate it in private shoutbox
			if ($in_priv)
			{
				// Force autoload here
				$panel['auto'] = true;
				$panel['active'] = true;
			}
			else
			{
				// And verifie in another pages
				$panel['active'] = ($this->config['shout_panel'] && $this->config['shout_panel_all']) ? true : false;
			}
		}

		return $panel;
	}

	private function run_shout_display($page, $index, $forum, $topic)
	{
		$run = true;

		if ($page === 'index')
		{
			$run = ($index > 0) ? true : false;
		}
		else if ($page === 'viewforum')
		{
			$run = ($forum > 0) ? true : false;
		}
		else if ($page === 'viewtopic')
		{
			$run = ($topic > 0) ? true : false;
		}

		return $run;
	}

	private function shout_enable_posting($sort_of, $page, $is_mobile)
	{
		if ($this->auth->acl_gets(['u_shout_post', 'u_shout_bbcode']))
		{
			if ($page == 'viewtopic')
			{
				$forum_id = $this->request->variable('f', 0);
				if ($forum_id && $this->auth->acl_get('f_reply', $forum_id))
				{
					return;
				}
			}

			$this->language->add_lang('posting');
			$this->template->assign_vars([
				'SHOUT_POSTING'			=> true,
				'S_BBCODE_ALLOWED'		=> true,
				'S_BBCODE_IMG'			=> true,
				'S_LINKS_ALLOWED'		=> true,
				'S_BBCODE_QUOTE'		=> true,
				'S_SMILIES_ALLOWED'		=> $this->auth->acl_get('u_shout_smilies'),
				'TEXT_USER_TOP'			=> $this->auth->acl_get('u_shout_bbcode_change'),
			]);

			// Build custom bbcodes array if needed
			$mode = 'inline';
			$this->active_custom_bbcodes($sort_of, $is_mobile);

			/**
			 * You can use this event to add something in the posting form.
			 *
			 * @event breizhshoutbox.display_posting
			 * @var	array	mode
			 * @since 1.8.0
			 */
			$vars = ['mode', 'sort_of', 'is_mobile'];
			extract($this->phpbb_dispatcher->trigger_event('breizhshoutbox.display_posting', compact($vars)));
		}
	}

	private function active_custom_bbcodes($sort_of, $is_mobile)
	{
		if (!$this->auth->acl_get('u_shout_bbcode_custom') || $sort_of === 1 || ($sort_of === 2) && $is_mobile)
		{
			return;
		}

		if (!function_exists('display_custom_bbcodes'))
		{
			include($this->root_path . 'includes/functions_display.' . $this->php_ext);
		}
		display_custom_bbcodes();
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
			$priv = $private = '';
			$shoutbox_table = $this->shoutbox_table;
		}

		if ($this->config['shout_last_run' . $priv] == '')
		{
			$this->config->set('shout_last_run' . $priv, time() - 86400, true);
		}
		if (($this->config['shout_last_run' . $priv] + ($this->config['shout_prune' . $priv] * 3600)) < time())
		{
			if ((time() - 900) <= $this->config['shout_last_run' . $priv])
			{
				return;
			}
			else if ($this->config['shout_prune' . $priv] == '' || $this->config['shout_prune' . $priv] == 0 || $this->config['shout_max_posts' . $priv] > 0)
			{
				return;
			}
			else if (($this->config['shout_prune' . $priv] > 0) && ($this->config['shout_max_posts' . $priv] == 0))
			{
				$time = time() - ($this->config['shout_prune' . $priv] * 3600);

				$this->db->sql_query('DELETE FROM ' . $shoutbox_table . " WHERE shout_time < '$time'");
				$deleted = (int) $this->db->sql_affectedrows();
				if ($deleted > 0)
				{
					$this->config->increment("shout_del_auto{$priv}", $deleted, true);
					if ($this->config['shout_log_cron' . $priv])
					{
						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, "LOG_SHOUT{$private}_PURGED", time(), [$deleted]);
					}
					if ($this->config['shout_delete_robot'])
					{
						$this->robot->post_robot_shout(0, '0.0.0.0', $sort, true, false, true, false, $deleted);
					}
				}
				$this->config->set("shout_last_run{$priv}", time(), true);
			}
		}
	}

	/**
	 * Delete posts when the maximum reaches
	 */
	public function delete_shout_posts($val)
	{
		$nb_to_del = 9;
		if (!$this->config['shout_on_cron' . $val['priv']] || (int) $this->config['shout_max_posts' . $val['priv']] === 0)
		{
			return;
		}

		$sql = 'SELECT COUNT(shout_id) as total
			FROM ' . $val['table'];
		$result = $this->work->shout_sql_query($sql);
		if (!$result)
		{
			return;
		}
		$row_nb = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);
		
		if ($row_nb > ((int) $this->config['shout_max_posts' . $val['priv']] + $nb_to_del))
		{
			$delete = [];
			$sql = $this->db->sql_build_query('SELECT', [
				'SELECT'	=> 'shout_id',
				'FROM'		=> [$val['table'] => ''],
				'ORDER_BY'	=> 'shout_time DESC',
			]);
			$result = $this->work->shout_sql_query($sql, true, (int) $this->config['shout_max_posts' . $val['priv']]);
			if (!$result)
			{
				return;
			}
			while ($row = $this->db->sql_fetchrow($result))
			{
				$delete[] = $row['shout_id'];
			}
			$this->db->sql_freeresult($result);

			$this->db->sql_query('DELETE FROM ' . $val['table'] . ' WHERE ' . $this->db->sql_in_set('shout_id', $delete, true));
			$deleted = $this->db->sql_affectedrows();

			if ($this->config['shout_log_cron' . $val['priv']])
			{
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, "LOG_SHOUT{$val['privat']}_REMOVED", time(), [$deleted]);
			}
			$this->config->set("shout_del_auto{$val['priv']}", $deleted, true);
			if ($this->config['shout_delete_robot'])
			{
				$this->robot->post_robot_shout(0, '0.0.0.0', $val['on_priv'], true, false, true, true, $deleted);
			}
		}
	}

	/*
	 * Change time of the last message to one second +
	 * to update the shoutbox for all users
	 */
	public function update_shout_messages($table)
	{
		$sql = 'UPDATE ' . $table . '
			SET shout_time = shout_time + 1
				ORDER BY shout_id DESC';
		$this->db->sql_query_limit($sql, 1);
	}

	/**
	 * Display the general main variables
	 */
	public function shout_page_header()
	{
		$data = $this->work->get_version();
		$this->template->assign_vars([
			'SHOUT_POPUP_H'			=> $this->config['shout_popup_width'],
			'SHOUT_POPUP_W'			=> $this->config['shout_popup_height'],
			'U_SHOUT_PRIV_PAGE'		=> $this->auth->acl_get('u_shout_priv') ? $this->helper->route('sylver35_breizhshoutbox_private') : '',
			'U_SHOUT_POPUP'			=> $this->auth->acl_get('u_shout_popup') ? $this->helper->route('sylver35_breizhshoutbox_popup') : '',
			'U_SHOUT_CONFIG'		=> $this->auth->acl_get('u_shout_post') ? $this->helper->route('sylver35_breizhshoutbox_configshout', ['id' => $this->user->data['user_id']]) : '',
			'U_SHOUT_AJAX'			=> $this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'display_smilies']),
			'SHOUT_COPYRIGHT'		=> $this->language->lang('SHOUTBOX_VER', $data['version']),
		]);
	}

	/**
	 * Display the retractable lateral panel
	 */
	public function shout_panel()
	{
		if (!$this->auth->acl_get('u_shout_lateral') || $this->user->data['is_bot'] || $this->config['board_disable'] || $this->work->shout_is_mobile())
		{
			$this->template->assign_vars([
				'KILL_LATERAL'	=> true,
				'ACTIVE_PANEL'	=> false,
				'S_IS_BOT'		=> $this->user->data['is_bot'],
			]);
			return false;
		}
		// Display only if we are not in excluded page
		if (!$this->kill_lateral_on())
		{
			$this->template->assign_vars([
				'KILL_LATERAL'	=> true,
			]);
			return false;
		}
		else
		{
			if ($this->user->data['is_registered'])
			{
				$user_shoutbox = json_decode($this->user->data['user_shoutbox']);
				$this->config['shout_panel_float'] = $this->work->set_user_option((bool) $user_shoutbox->panel_float, 'shout_panel_float', 3);
			}
			$this->template->assign_vars([
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
				'S_SHOUT_VERSION'	=> $this->work->get_version(true),
			]);
			return true;
		}
	}

	/*
	 * Function for display or not the lateral panel
	 * based on page list in config
	 * Never display it for mobile phones (ipad ok)
	 * Return bool
	 */
	public function kill_lateral_on()
	{
		if (!$this->auth->acl_get('u_shout_lateral') || $this->user->data['is_bot'])
		{
			return false;
		}
		else if (!$this->user->data['is_registered'] && !$this->config['shout_panel'])
		{
			return false;
		}
		// Registred users can set this option
		else if ($this->user->data['is_registered'])
		{
			$set_option = false;
			$user_shoutbox = json_decode($this->user->data['user_shoutbox']);
			if ($user_shoutbox->panel != 3)
			{
				if ($user_shoutbox->panel == 0)
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

		// Exclude all pages in this list
		if (preg_match("#ucp|mcp#i", $this->user->page['page_name']) || preg_match("#adm#i", $this->user->page['page_dir']))
		{
			return false;
		}
		else if ($this->in_excluded_page())
		{
			return false;
		}

		// Ok, let's go to display it baby (^_^)
		return true;
	}

	private function in_excluded_page()
	{
		if ($this->config['shout_page_exclude'] !== '')
		{
			$is_param = $_page = $param = false;
			$on_page = ($this->user->page['page_dir'] ? $this->user->page['page_dir'] . '/' : '') . $this->user->page['page_name'] . ($this->user->page['query_string'] ? '?' . $this->user->page['query_string'] : '');
			$on_page1 = ($this->user->page['page_dir'] ? $this->user->page['page_dir'] . '/' : '') . $this->user->page['page_name'];
			$pages = explode('||', $this->config['shout_page_exclude']);
			foreach ($pages as $page)
			{
				$page = str_replace('app.php/', '', $page);
				$query_string = ($this->user->page['query_string']) ? explode('&', $this->user->page['query_string']) : '-';
				if (preg_match("#{$page}#i", $this->user->page['page_name']))
				{
					return true;
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
						return true;
					}
				}
				else
				{
					if (empty($this->user->page['query_string']))
					{
						if ($on_page == $page)
						{
							return true;
						}
					}
					else
					{
						if ($on_page1 == $_page && ($this->user->page['query_string'] == $param || $query_string[0] == $param))
						{
							return true;
						}
					}
				}
			}
		}

		return false;
	}

	/*
	 * Ignore info robot in forum messages
	 * Return bool
	 */
	public function shout_post_hide($mode, $s_hide_robot)
	{
		if ($this->auth->acl_get('u_shout_hide') && $s_hide_robot)
		{
			if ($mode === 'edit' && !$this->config['shout_edit_robot'] && !$this->config['shout_edit_robot_priv'])
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

	/*
	 * Personalize message before submit
	 * Return string
	 */
	public function personalize_message($message)
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

		return $message;
	}

	/*
	 * Parse bbcodes in personalisation
	 * before submit
	 * Return array
	 */
	public function parse_shout_bbcodes($open, $close, $other)
	{
		// Return error no permission for change personalisation of another
		if ($other > 0 && ($other !== (int) $this->user->data['user_id']))
		{
			if (!$this->auth->acl_gets(['a_', 'm_']))
			{
				return [
					'sort'		=> 5,
					'message'	=> $this->language->lang('NO_SHOUT_PERSO_PERM'),
				];
			}
		}

		// prepare the list
		$open = str_replace('][', '], [', $open);
		$close = str_replace('][', '], [', $close);
		// explode it
		$array_open = explode(', ', $open);
		$array_close = explode(', ', $close);
		// for this user or an another?
		$shout_bbcode = $this->get_shout_bbcode($other);

		$first = $this->first_parse($open, $close, $array_open, $array_close, $shout_bbcode);
		if ($first['sort'] !== 3)
		{
			return [
				'sort'		=> $first['sort'],
				'message'	=> $first['message'],
			];
		}

		$second = $this->second_parse($open, $close, $array_open, $array_close, $shout_bbcode);
		if ($second['sort'] !== 1)
		{
			return [
				'sort'		=> $second['sort'],
				'message'	=> $second['message'],
			];
		}

		// If all is ok, return 3
		return [
			'sort'	=> 3,
		];
	}

	private function first_parse($open, $close, $array_open, $array_close, $shout_bbcode)
	{
		// Any modification
		if ($open == 1 && $close == 1)
		{
			if ($shout_bbcode)
			{
				return [
					'sort'		=> 1,
				];
			}
			else
			{
				return [
					'sort'		=> 4,
					'message'	=> $this->language->lang('SHOUT_BBCODE_ERROR_SHAME'),
				];
			}
		}
		else if (($open == '' && $close != '') || ($open != '' && $close == ''))
		{
			// If one is empty
			return [
				'sort'		=> 2,
				'message'	=> $this->language->lang('SHOUT_BBCODE_ERROR'),
			];
		}
		else if (sizeof($array_open) != sizeof($array_close))
		{
			// If the number of bbcodes opening and closing is different
			return [
				'sort'		=> 2,
				'message'	=> $this->language->lang('SHOUT_BBCODE_ERROR_COUNT'),
			];
		}
		else if (!preg_match("#^\[|\[|\]|\]$#", $open) || !preg_match("#^\[|\[|\[/|\]|\]$#", $close))
		{
			// If a square bracket is absent
			return [
				'sort'		=> 2,
				'message'	=> $this->language->lang('SHOUT_BBCODE_ERROR_COUNT'),
			];
		}

		return [
			'sort'	=> 3,
		];
	}

	private function second_parse($open, $close, $array_open, $array_close, $shout_bbcode)
	{
		$verify = $this->verify_imbrication($open, $close, $array_open, $array_close, $shout_bbcode);
		if ($verify['sort'] !== 1)
		{
			return [
				'sort'		=> $verify['sort'],
				'message'	=> $verify['message'],
			];
		}

		$unautorised = $this->verify_unautorised_and_size($open, $close);
		if ($unautorised['sort'] !== 1)
		{
			return [
				'sort'		=> $unautorised['sort'],
				'message'	=> $unautorised['message'],
			];
		}

		$video = $this->verify_video_bbcode($open);
		if ($video['sort'] !== 1)
		{
			return [
				'sort'		=> $video['sort'],
				'message'	=> $video['message'],
			];
		}

		return [
			'sort'	=> 1,
		];
	}

	private function verify_imbrication($open, $close, $array_open, $array_close, $shout_bbcode)
	{
		// Initalise closing of bbcodes and correct imbrication
		$s = $n = 0;
		$slash = $sort = [];
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
			return [
				'sort'		=> 2,
				'message'	=> $this->work->plural('SHOUT_BBCODE_ERROR_SLASH', $s, '', $slash),
			];
		}
		// Check the correct imbrication of bbcodes
		if ($n)
		{
			$sort = implode(', ', $sort);
			return [
				'sort'		=> 2,
				'message'	=> $this->work->plural('SHOUT_BBCODE_ERROR_IMB', $n, '', $sort),
			];
		}

		// Check opening and closing of bbcodes
		if ($shout_bbcode)
		{
			$bbcode = explode('||', $shout_bbcode);
			if (str_replace('][', '], [', $bbcode[0]) == $open && str_replace('][', '], [', $bbcode[1]) == $close)
			{
				return [
					'sort'		=> 4,
					'message'	=> $this->language->lang('SHOUT_BBCODE_ERROR_SHAME'),
				];
			}
		}

		return [
			'sort'	=> 1,
		];
	}

	private function get_shout_bbcode($other)
	{
		if ($other > 0)
		{
			$sql = 'SELECT shout_bbcode
				FROM ' . USERS_TABLE . '
					WHERE user_id = ' . $other;
			$result = $this->db->sql_query($sql);
			$shout_bbcode = (string) $this->db->sql_fetchfield('shout_bbcode');
			$this->db->sql_freeresult($result);
		}
		else
		{
			$shout_bbcode = (string) $this->user->data['shout_bbcode'];
		}

		return $shout_bbcode;
	}

	private function verify_unautorised_and_size($open, $close)
	{
		// See for unautorised bbcodes
		$other_bbcode = ($this->config['shout_bbcode']) ? ', ' . $this->config['shout_bbcode'] : '';
		$bbcode_array = explode(', ', $this->config['shout_bbcode_user'] . $other_bbcode);
		foreach ($bbcode_array as $no)
		{
			if (strpos($close, "[/{$no}]") !== false)
			{
				return [
					'sort'		=> 2,
					'message'	=> $this->language->lang('SHOUT_NO_CODE', "[{$no}][/{$no}]"),
				];
			}
		}

		// Limit font size
		$shout_bbcode_size = $this->auth->acl_get('a_') ? 200 : (int) $this->config['shout_bbcode_size'];
		if (strpos($open, '[size=') !== false)
		{
			$this->language->add_lang('posting');
			$all = explode(', ', $open);
			foreach ($all as $is)
			{
				if (preg_match('/size=/i', $is))
				{
					$size = str_replace(['[', 'size=', ']'], '', $is);
					if ($size > $shout_bbcode_size)
					{
						return [
							'sort'		=> 2,
							'message'	=> $this->language->lang('MAX_FONT_SIZE_EXCEEDED', $shout_bbcode_size),
						];
					}
				}
				else
				{
					continue;
				}
			}
		}

		return [
			'sort'	=> 1,
		];
	}

	private function verify_video_bbcode($open)
	{
		// No video here !
		$video_array = ['flash', 'swf', 'mp4', 'mts', 'avi', '3gp', 'asf', 'flv', 'mpeg', 'video', 'embed', 'BBvideo', 'scrippet', 'quicktime', 'ram', 'gvideo', 'youtube', 'veoh', 'collegehumor', 'dm', 'gamespot', 'gametrailers', 'ignvideo', 'liveleak'];
		foreach ($video_array as $video)
		{
			if (strpos($open, '[' . $video) !== false || strpos($open, '<' . $video) !== false)
			{
				return [
					'sort'		=> 2,
					'message'	=> $this->language->lang('SHOUT_NO_VIDEO'),
				];
			}
			else
			{
				continue;
			}
		}

		return [
			'sort'	=> 1,
		];
	}

	/*
	 * Parse message before submit
	 */
	public function parse_shout_message($message, $priv, $on_priv, $robot, $personalize)
	{
		// Set the minimum of caracters to 1 in a message to parse all the time here...
		// This will not alter the minimum in the post form...
		$this->config['min_post_chars'] = 1;

		// Never post an empty message (with bbcode or not)
		if (empty(preg_replace("(\[.+?\])is", '', $message)))
		{
			$this->work->shout_error('MESSAGE_EMPTY');
			return;
		}

		// Don't parse img if unautorised and return img url only
		if ((strpos($message, '[/img]') !== false) && !$this->auth->acl_get('u_shout_image'))
		{
			$message = str_replace(['[img]', '[/img]'], '', $message);
		}

		// Correct a bug with somes empty bbcodes
		if ($message == '[img][/img]' || $message == '[b][/b]' || $message == '[i][/i]' || $message == '[u][/u]' || $message == '[url][/url]')
		{
			$this->work->shout_error('MESSAGE_EMPTY');
			return;
		}
		$message = str_replace(['/]', '&amp;amp;'], [']', '&'], $message);

		if (!$this->verify_message_length($message) || !$this->parse_bbcode_video_message($message) || !$this->parse_unautorized_content($message, $priv, $on_priv))
		{
			return;
		}

		$message = ($robot) ? $this->work->tpl('colorbot', $message) : $message;
		// Personalize message if needed
		$message = ($personalize) ? $this->personalize_message($message) : $message;

		return $this->url_free_sid($message);
	}

	private function verify_message_length($message)
	{
		// Verify message length...
		// Permission to ignore the limit of characters in a message
		if (!$this->auth->acl_get('u_shout_limit_post') && $this->config['shout_max_post_chars'])
		{
			$message_length = mb_strlen(preg_replace('#\[\/?[a-z\*\+\-]+(=[\S]+)?\]#ius', ' ', $message), 'utf-8');
			if ($message_length > $this->config['shout_max_post_chars'])
			{
				$this->language->add_lang('posting');
				$this->work->shout_error($this->language->lang('TOO_MANY_CHARS', (int) $this->config['shout_max_post_chars']) . ' (' . $message_length . ')');
				return false;
			}
		}

		return true;
	}

	private function parse_bbcode_video_message($message)
	{
		// See for unautorised bbcodes
		$bbcode_array = explode(', ', $this->config['shout_bbcode']);
		foreach ($bbcode_array as $no)
		{
			if (strpos($message, "[/{$no}]") !== false)
			{
				$this->work->shout_error('SHOUT_NO_CODE', "[{$no}][/{$no}]");
				return false;
			}
		}

		// No video!
		$video_array = ['flash', 'swf', 'mp4', 'mts', 'avi', '3gp', 'asf', 'flv', 'mpeg', 'video', 'embed', 'BBvideo', 'scrippet', 'quicktime', 'ram', 'gvideo', 'youtube', 'veoh', 'collegehumor', 'dm', 'gamespot', 'gametrailers', 'ignvideo', 'liveleak'];
		foreach ($video_array as $video)
		{
			if ((strpos($message, '[' . $video) !== false && strpos($message, '[/' . $video) !== false) || (strpos($message, '<' . $video) !== false && strpos($message, '</' . $video) !== false))
			{
				$this->work->shout_error('SHOUT_NO_VIDEO');
				return false;
			}
			else
			{
				continue;
			}
		}

		return true;
	}

	private function parse_unautorized_content($message, $priv, $on_priv)
	{
		$list = ['script', 'vbscript', 'applet', 'activex', 'object', 'chrome', 'about', 'iframe'];
		$log = ['LOG_SHOUT_SCRIPT', 'LOG_SHOUT_SCRIPT', 'LOG_SHOUT_APPLET', 'LOG_SHOUT_ACTIVEX', 'LOG_SHOUT_OBJECTS', 'LOG_SHOUT_OBJECTS', 'LOG_SHOUT_OBJECTS', 'LOG_SHOUT_IFRAME'];

		for ($i = 0, $nb = sizeof($list); $i < $nb; $i++)
		{
			if ((strpos($message, '&lt;' . $list[$i]) !== false && strpos($message, '&lt;/' . $list[$i]) !== false) || (strpos($message, '<' . $list[$i]) !== false && strpos($message, '</' . $list[$i]) !== false))
			{
				$this->log->add('user', $this->user->data['user_id'], $this->user->ip, $log[$i] . $on_priv, time(), ['reportee_id' => $this->user->data['user_id']]);
				$this->config->increment("shout_nr_log{$priv}", 1, true);
				$this->work->shout_error(str_replace('LOG_SHOUT', 'SHOUT_NO', $log[$i]));
				return false;
			}
		}

		return true;
	}

	/*
	 * Build a number with ip for differentiate guests
	 */
	public function add_random_ip($username)
	{
		$rand = 0;
		$ip = str_replace(['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'], ['1', '2', '3', '4', '5', '6', '7', '8', '9', '1', '2', '3', '4', '5', '6', '7', '8', '9', '1', '2', '3', '4', '5', '6', '7', '8'], strtolower($this->user->ip));
		$act = explode('.', $ip);
		for ($i = 0, $nb = sizeof($act); $i < $nb; $i++)
		{
			if ($act[$i] == 0)
			{
				continue;
			}
			$rand = $rand + (int) $act[$i];
		}
		$data = $username . ':' . round($rand / sizeof($act));

		return $data;
	}

	/* 
	 * Construct url whithout sid
	 * Because urls must be construct for all and use append_sid() after
	 */
	public function url_free_sid($content)
	{
		if (strpos($content, 'sid=') !== false)
		{
			$rep = explode('sid=', $content);
			// the sid number is on second part and 32 long
			if (strlen($rep[1]) > 32)
			{
				$sid_32 = substr($rep[1], 0, 32);
				$content = str_replace([$sid_32, '&amp;sid=', '&sid=', '?sid=', '-sid='], '', $content);
			}
			else
			{
				$content = $rep[0];
			}
			// Prevent somes bugs here
			$content = str_replace(['?&amp;', '?&', '&&amp;', '&amp&amp;', '&amp;&amp;'], ['?', '?', '&amp;', '&amp;', '&amp;'], $content);
		}

		return $content;
	}

	private function clean_tpl($tpl)
	{
		return str_replace(['\\', '&quot;', '""'], ['', '"', '"'], $this->config[$tpl]);
	}

	public function action_user($row, $id, $sort)
	{
		// Founders protection
		$go_founder = ($row['user_type'] != USER_FOUNDER || $this->user->data['user_type'] == USER_FOUNDER) ? true : false;
		$action = $this->work->create_action_user($row, $sort, $go_founder);

		return [
			'type'			=> 3,
			'id'			=> (int) $row['user_id'],
			'sort'			=> $sort,
			'foe'			=> ($row['foe']) ? true : false,
			'inp'			=> ($this->auth->acl_gets(['u_shout_post_inp', 'a_', 'm_'])) ? true : false,
			'retour'		=> ($this->auth->acl_get('a_user') || $this->auth->acl_get('m_') || ($this->auth->acl_get('m_ban') && $go_founder)) ? true : false,
			'username'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'], '', append_sid("{$this->root_path_web}memberlist.{$this->php_ext}", 'mode=viewprofile')),
			'avatar'		=> $this->shout_user_avatar($row, 60, true),
			'url_message'	=> $this->work->tpl('personal'),
			'url_del_to'	=> $this->work->tpl('delreqto', $id),
			'url_del'		=> $this->work->tpl('delreq', $id),
			'url_cite'		=> $this->work->tpl('citemsg'),
			'url_cite_m'	=> $this->work->tpl('citemulti', $row['username'], $row['user_colour']),
			'url_profile'	=> $action['url_profile'],
			'url_auth'		=> $action['url_auth'],
			'url_prefs'		=> $action['url_prefs'],
			'url_admin'		=> $action['url_admin'],
			'url_modo'		=> $action['url_modo'],
			'url_ban'		=> $action['url_ban'],
			'url_remove'	=> $action['url_remove'],
			'url_perso'		=> $action['url_perso'],
			'url_robot'		=> $action['url_robot'],
		];
	}

	public function shout_text_for_display($row, $sort, $acp)
	{
		if ($row['shout_info'])
		{
			$row['shout_text'] = $this->robot->display_infos_robot($row, (int) $row['shout_info'], $acp);
		}
		else
		{
			$row['shout_text'] = generate_text_for_display($row['shout_text'], $row['shout_bbcode_uid'], $row['shout_bbcode_bitfield'], $row['shout_bbcode_flags']);
			// Limit the max height for images
			$row['shout_text'] = str_replace('class="postimage"', 'class="postimage" style="max-height:200px;"', $row['shout_text']);
			// Transform video iframe in link
			if (preg_match('/<iframe/i', $row['shout_text']))
			{
				preg_match('/src="([^"]+)"/', $row['shout_text'], $match);
				$row['shout_text'] = '<a href="' . $match[1] . '" class="postlink">' . $match[1] . '</a>';
			}
		}

		// Active external links for all links in popup and private shoutbox
		if ($sort !== 2)
		{
			if (preg_match('/class=\"postlink\"/i', $row['shout_text']))
			{
				$row['shout_text'] = str_replace('class="postlink', 'onclick="window.open(this.href);return false;" class="postlink', $row['shout_text']);
			}
			else
			{
				$row['shout_text'] = str_replace(['a href="', 'class="action-user"'], ['a onclick="window.open(this.href);return false;" href="', 'class="action-user" onclick="window.open(this.href);return false;"'], $row['shout_text']);
			}
		}
		else
		{
			$row['shout_text'] = str_replace('class="postlink', 'onclick="window.open(this.href);return false;" class="postlink', $row['shout_text']);
		}

		return $this->work->shout_url($row['shout_text']);
	}

	public function get_topic_data($event, $forum_id)
	{
		$sort = 'post';
		$mode = (string) $event['mode'];
		$prez_poster = false;
		$prez_form = ((int) $this->config['shout_prez_form'] === $forum_id) ? true : false;
		$post_id = (isset($event['data']['post_id'])) ? (int) $event['data']['post_id'] : 0;

		if (strpos($mode, 'edit') !== false)
		{
			$sort = 'edit';
			$sql = $this->db->sql_build_query('SELECT', [
				'SELECT'	=> 'topic_poster, topic_first_post_id, topic_last_post_id',
				'FROM'		=> [TOPICS_TABLE => ''],
				'WHERE'		=> 'topic_id = ' . (int) $event['data']['topic_id'],
			]);
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);

			if ((int) $row['topic_first_post_id'] === $post_id)
			{
				$mode = 'edit_first_post';
			}
			else if ((int) $row['topic_last_post_id'] === $post_id)
			{
				$mode = 'edit_last_post';
			}
			$prez_poster = ($prez_form && ((int) $row['topic_poster'] === (int) $this->user->data['user_id'])) ? true : false;
		}
		else if ($mode === 'post' && $event['topic_type'] > 1)
		{
			$mode = ((int) $event['topic_type'] === 3) ? 'global' : 'annoucement';
		}

		$sort = (strpos($mode, 'quote') !== false || strpos($mode, 'reply') !== false) ? 'rep' : $sort;

		return [
			'prez_form'		=> $prez_form,
			'prez_poster'	=> $prez_poster,
			'mode'			=> $mode,
			'sort'			=> $sort,
		];
	}

	public function list_auth_options()
	{
		return [
			'a_shout_manage',
			'a_shout_priv',
			'm_shout_delete',
			'm_shout_edit_mod',
			'm_shout_info',
			'm_shout_personal',
			'm_shout_robot',
			'u_shout_bbcode',
			'u_shout_bbcode_custom',
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
		];
	}

	public function user_is_foe($userid, $id)
	{
		$sql = $this->db->sql_build_query('SELECT', [
			'SELECT'	=> 'u.user_id, u.user_type, z.friend, z.foe',
			'FROM'		=> [USERS_TABLE => 'u'],
			'LEFT_JOIN'	=> [
				[
					'FROM'	=> [ZEBRA_TABLE => 'z'],
					'ON'	=> 'z.zebra_id = u.user_id AND z.user_id = ' . $userid,
				],
			],
			'WHERE'		=> 'u.user_id = ' . $id,
		]);
		$result = $this->work->shout_sql_query($sql, true, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		if (!$row || $row['user_type'] == USER_IGNORE)
		{
			// user id don't exist or ignore
			return [
				'type'		=> 1,
				'message'	=> '',
			];
		}
		else if ($row['foe'])
		{
			// if user is foe
			return [
				'type'		=> 2,
				'message'	=> $this->language->lang('SHOUT_USER_IGNORE'),
			];
		}
		else
		{
			return [
				'type'		=> 0,
				'message'	=> '',
			];
		}
	}

	public function shout_verify_delete($userid, $on_id, $can_delete_all, $can_delete)
	{
		$result = false;
		if ($userid == ANONYMOUS)
		{
			$message = 'NO_DELETE_PERM';
		}
		else if (!$can_delete && ($userid == $on_id))
		{
			$message = 'NO_DELETE_PERM_S';
		}
		else if (!$can_delete_all && $can_delete && ($userid != $on_id))
		{
			$message = 'NO_DELETE_PERM_T';
		}
		else if (!$can_delete)
		{
			$message = 'NO_DELETE_PERM';
		}
		else if (($can_delete && ($userid == $on_id)) || $can_delete_all)
		{
			$message = '';
			$result = true;
		}
		else
		{
			$message = 'NO_DELETE_PERM';
		}

		return [
			'result'	=> $result,
			'message'	=> $message,
		];
	}

	public function shout_check_edit($val, $shout_id)
	{
		// If someone can edit all messages, he can edit it's messages :) (if errors in permissions set)
		if ($this->auth->acl_gets(['m_shout_edit_mod', 'a_shout' . $val['auth']]))
		{
			return true;
		}
		else if ($this->auth->acl_get('u_shout_edit'))
		{
			// We need to be sure its this users his shout.
			$sql = 'SELECT shout_user_id
				FROM ' . $val['table'] . '
					WHERE shout_id = ' . $shout_id;
			$result = $this->work->shout_sql_query($sql, true, 1);
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

	public function shout_verify_flood($on_priv, $userid)
	{
		// Flood control, not in private
		if (!$on_priv && !$this->auth->acl_get('u_shout_ignore_flood'))
		{
			$sql = $this->db->sql_build_query('SELECT', [
				'SELECT'	=> 'MAX(shout_time) AS last_post_time',
				'FROM'		=> [$this->shoutbox_table => ''],
				'WHERE'		=> (!$this->user->data['is_registered']) ? "shout_ip = '" . $this->db->sql_escape((string) $this->user->ip) . "'" : 'shout_user_id = ' . $userid,
			]);
			$result = $this->work->shout_sql_query($sql);
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

	public function get_shout_name($id, $username, $text)
	{
		if (!$id)
		{
			$name = $this->config['shout_name_robot'];
		}
		else if ($id == ANONYMOUS)
		{
			$name = $text;
		}
		else
		{
			$name = $username;
		}

		return $name;
	}

	public function get_shout_colour($id, $colour)
	{
		if (!$id)
		{
			return $this->config['shout_color_robot'];
		}
		else if ($id == ANONYMOUS)
		{
			return '6666FF';
		}
		else
		{
			return $colour;
		}
	}

	public function get_additional_data($row, $perm, $val)
	{
		// Initialize additional data
		$id = (int) $row['shout_user_id'];
		$row = array_merge($row, [
			'username'		=> $this->get_shout_name($id, $row['username'], $row['shout_text2']),
			'user_colour'	=> $this->get_shout_colour($id, $row['user_colour']),
			'is_user'		=> ($id > 1) && ($id === $val['userid']),
			'other'			=> ($id > 1) && ($id !== $val['userid']),
			'delete'		=> false,
			'edit'			=> false,
			'show_ip'		=> false,
			'on_ip'			=> '',
			'msg_plain'		=> '',
		]);

		if ($val['is_user'])
		{
			if ($perm['delete_all'] || ($id === $val['userid']) && $perm['delete'])
			{
				$row['delete'] = true;
			}
			if ($perm['edit_all'] || ($id === $val['userid']) && $perm['edit'])
			{
				$row['edit'] = true;
				$row['msg_plain'] = $row['shout_text'];
				decode_message($row['msg_plain'], $row['shout_bbcode_uid']);
			}
			if ($perm['info_all'] || ($id === $val['userid']) && $perm['info'])
			{
				$row['show_ip'] = true;
				$row['on_ip'] = $row['shout_ip'];
			}
		}

		return $row;
	}

	public function extract_dateformat($is_user)
	{
		$dateformat = $this->config['shout_dateformat'];
		if ($is_user)
		{
			$data = json_decode($this->user->data['user_shoutbox']);
			$dateformat = ($data->dateformat !== '') ? $data->dateformat : $dateformat;
		}

		return (string) $dateformat;
	}

	public function extract_permissions($auth)
	{
		// Prevents some errors for allocation of permissions
		// Initialise data
		$data = [
			'edit'		=> $this->auth->acl_get('u_shout_edit'),
			'delete'	=> $this->auth->acl_get('u_shout_delete_s'),
			'info'		=> $this->auth->acl_get('u_shout_info_s') && $this->config['shout_see_button_ip'],
			'edit_all'	=> false,
			'delete_all'=> false,
			'info_all'	=> false,
		];

		// If someone can edit all messages, he can edit its own messages :)
		if ($this->auth->acl_gets(['m_shout_edit_mod', 'a_shout' . $auth]))
		{
			$data['edit'] = $data['edit_all'] = true;
		}

		// If someone can delete all messages, he can delete its own messages :)
		if ($this->auth->acl_gets(['m_shout_delete', 'a_shout' . $auth]))
		{
			$data['delete'] = $data['delete_all'] = true;
		}

		// If someone can view all ip, he can view its own ip :)
		if ($this->auth->acl_gets(['m_shout_info', 'a_shout' . $auth]) && $this->config['shout_see_button_ip'])
		{
			$data['info'] = $data['info_all'] = true;
		}

		return $data;
	}

	public function shout_sql_where($is_user, $userid, $on_bot)
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
		$sql_where .= ($is_user) ? " AND (s.shout_inp = 0 OR (s.shout_inp = $userid OR s.shout_user_id = $userid))" : ' AND s.shout_inp = 0';

		return $sql_where;
	}

	public function get_shout_avatar($row, $sort, $is_mobile)
	{
		if (!$this->config['shout_avatar'] || !$this->config['allow_avatar'] || $is_mobile)
		{
			return '';
		}
		else if (!$row['shout_user_id'] && $row['shout_robot_user'])
		{
			return $this->shout_user_avatar([
				'user_id'				=> $row['v_user_id'],
				'username'				=> $row['v_username'],
				'user_type'				=> $row['v_user_type'],
				'user_avatar'			=> $row['v_user_avatar'],
				'user_avatar_type'		=> $row['v_user_avatar_type'],
				'user_avatar_width'		=> $row['v_user_avatar_width'],
				'user_avatar_height'	=> $row['v_user_avatar_height'],
			], $this->config['shout_avatar_height'], false, ($sort === 1));
		}
		else
		{
			return $this->shout_user_avatar($row, $this->config['shout_avatar_height'], false, ($sort === 1));
		}
	}

	/*
	 * Display user avatar with resizing
	 * Add avatar type for robot, users with no avatar and anonymous
	 * Add title with username
	 * Return string
	 */
	private function shout_user_avatar($row, $height, $force, $popup = false)
	{
		if (!$force)
		{
			if (!$this->config['shout_avatar'] || !$this->config['allow_avatar'])
			{
				return '';
			}
		}

		if ($row['user_id'] && $row['user_avatar'] && $row['user_avatar_height'])
		{
			$avatar_height = ($row['user_avatar_height'] > $height) ? $height : $row['user_avatar_height'];
			$row['user_avatar_width'] = round($avatar_height / $row['user_avatar_height'] * $row['user_avatar_width']);
			$row['user_avatar_height'] = $avatar_height;
			$avatar = $this->work->shout_url(phpbb_get_user_avatar($row, $this->language->lang('SHOUT_AVATAR_TITLE', $row['username'])));
			$avatar = strtr($avatar, ['alt="' => 'title="' . $this->language->lang('SHOUT_AVATAR_TITLE', $row['username']) . '" alt="']);
			$avatar = ($popup) ? strtr($avatar, ['class="avatar' => 'class="avatar popup-avatar']) : $avatar;

			return $avatar;
		}
		else
		{
			$val = $this->build_additional_avatar($row);
		}

		$row = [
			'avatar'		=> $val['src'],
			'avatar_type'	=> 'avatar.driver.upload',
			'avatar_height'	=> $height,
			'avatar_width'	=> '',
		];
		$avatar = strtr(phpbb_get_user_avatar($row, $val['alt']), ['./download/file.php?avatar=' => '', 'alt="' => 'title="' . $val['alt'] . '" alt="']);
		$avatar = ($popup) ? strtr($avatar, ['class="avatar' => 'class="avatar popup-avatar']) : $avatar;

		return $this->work->shout_url($avatar);
	}

	private function build_additional_avatar($row)
	{
		$val = [
			'src'	=> $this->ext_path . 'images/burn.webp',
			'alt'	=> $this->language->lang('SHOUT_AVATAR_NONE', $row['username']),
		];

		if (!$row['user_id'] && $this->config['shout_avatar_robot'])
		{
			$val = [
				'src'	=> $this->ext_path . 'images/' . $this->config['shout_avatar_img_robot'],
				'alt'	=> $this->language->lang('SHOUT_AVATAR_TITLE', $this->config['shout_name_robot']),
			];
		}
		else if ($row['user_id'] == ANONYMOUS && $this->config['shout_avatar_user'])
		{
			$val = [
				'src'	=> $this->ext_path . 'images/anonym.webp',
				'alt'	=> $this->language->lang('SHOUT_AVATAR_TITLE', $this->language->lang('GUEST')),
			];
		}
		else if ($row['user_id'] && !$row['user_avatar'] && $this->config['shout_avatar_user'])
		{
			$val = [
				'src'	=> $this->ext_path . 'images/' . $this->config['shout_avatar_img'],
				'alt'	=> $this->language->lang('SHOUT_AVATAR_NONE', $row['username']),
			];
		}

		return $val;
	}
}
