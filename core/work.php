<?php
/**
*
* @package phpBB Extension - Breizh Shoutbox
* @copyright (c) 2018-2025 Sylver35  https://breizhcode.com
* @license https://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace sylver35\breizhshoutbox\core;

use phpbb\config\config;
use phpbb\db\driver\driver_interface as db;
use phpbb\auth\auth;
use phpbb\user;
use phpbb\language\language;
use phpbb\cache\driver\driver_interface as cache;
use phpbb\extension\manager;
use phpbb\controller\helper;
use phpbb\path_helper;
use Symfony\Component\DependencyInjection\Container;
use phpbb\event\dispatcher_interface as phpbb_dispatcher;

class work
{
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

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\extension\manager */
	protected $ext_manager;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\path_helper */
	protected $path_helper;

	/** @var \Symfony\Component\DependencyInjection\Container */
	protected $phpbb_container;

	/** @var \phpbb\event\dispatcher_interface */
	protected $phpbb_dispatcher;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string root path web */
	protected $root_path_web;

	/** @var string phpEx */
	protected $php_ext;

	/** @var string ext path */
	protected $ext_path;

	/**
	 * The database tables
	 *
	 * @var string */
	protected $shoutbox_errors_table;
	protected $shoutbox_rules_table;

	/**
	 * Constructor
	 */
	public function __construct(config $config, db $db, auth $auth, user $user, language $language, cache $cache, manager $ext_manager, helper $helper, path_helper $path_helper, Container $phpbb_container, phpbb_dispatcher $phpbb_dispatcher, $root_path, $php_ext, $shoutbox_errors_table, $shoutbox_rules_table)
	{
		$this->config = $config;
		$this->db = $db;
		$this->auth = $auth;
		$this->user = $user;
		$this->language = $language;
		$this->cache = $cache;
		$this->ext_manager = $ext_manager;
		$this->helper = $helper;
		$this->path_helper = $path_helper;
		$this->phpbb_container = $phpbb_container;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->root_path = $root_path;
		$this->root_path_web = generate_board_url() . '/';
		$this->php_ext = $php_ext;
		$this->shoutbox_errors_table = $shoutbox_errors_table;
		$this->shoutbox_rules_table = $shoutbox_rules_table;
		$this->ext_path = $this->ext_manager->get_extension_path('sylver35/breizhshoutbox', true);
	}

	/**
	 * execute sql query or return error in the shoutbox
	 * @param string $sql
	 * @param bool $limit
	 * @param int $nb
	 * @param int $start
	 * @return string|bool
	 */
	public function shout_sql_query($sql, $limit = false, $nb = 0, $start = 0)
	{
		$result = '';
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
	 * Print sql error
	 * @param string $sql Sql query
	 * @param int $line Line number
	 * @param string $file Filename
	 * @return array
	 */
	private function shout_sql_error($sql, $line, $file)
	{
		$id = 1;
		$err = $this->db->sql_error();
		$error = str_replace(' />', '>', $err['message']);
		$error = preg_replace("#<b>(.*?)<br>#i", '', $error);
		$message = '#' . $err['code'] . ' : ' . $error;
		// Store error in the db if wanted
		if ($this->config['shout_store_errors'])
		{
			$id = $this->store_error('_sql', $message, $line, $file);
		}

		$response = new \phpbb\json_response;
		$response->send([
			'message'	=> $message,
			'line'		=> $line,
			'file'		=> $file,
			'content'	=> $sql,
			'stored'	=> $id,
			'error'		=> true,
			't'			=> 1,
		], true);
	}

	/**
	 * Return error.
	 * @param string $message Error
	 * @param string $on1 Error
	 * @param string $on2 Error
	 * @param string $on3 Error
	 * @return array
	 */
	public function shout_error($message, $on1 = false, $on2 = false, $on3 = false)
	{
		$id = 0;
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

		// Store error in the db if wanted
		if ($this->config['shout_store_errors'])
		{
			$id = $this->store_error('_php', $message);
		}
		$message = str_replace(' />', '>', $message);
		$message = preg_replace("#<b>(.*?)<br>#i", '', $message);

		$response = new \phpbb\json_response;
		$response->send([
			'type'		=> 10,
			'error'		=> true,
			'stored'	=> $id,
			'message'	=> $message,
		], true);
	}

	/**
	 * Store error in db
	 * @param string $mode type of error
	 * @param string $message error message
	 * @param string $sql Error
	 * @param int $line line of error
	 * @param string $file of error
	 * @return int
	 */
	private function store_error($mode, $message, $line = 0, $file = '')
	{
		$sql_ary = [
			'error_type'	=> (string) $mode,
			'error_time'	=> time(),
			'error_lang'	=> (string) $this->user->lang_name,
			'error_user'	=> (int) $this->user->data['user_id'],
			'error_ip'		=> (string) $this->user->ip,
			'error_sql'		=> (string) 'error',
			'error_line'	=> (int) $line,
			'error_file'	=> (string) $file,
			'error_message'	=> (string) $message,
		];

		$this->db->sql_query('INSERT INTO ' . $this->shoutbox_errors_table . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
		$id = (int) $this->db->sql_last_inserted_id();

		return $id;
	}

	/**
	 * Get the absolute adm path
	 * @return string
	 */
	public function adm_path()
	{
		return $this->root_path_web . $this->path_helper->get_adm_relative_path();
	}

	/**
	 * Remove sid from url
	 * @param string $url
	 * @return string
	 */
	public function remove_sid($url)
	{
		$url = (string) $url;
		$url = preg_replace(['#(?:&amp;)?sid=\w{0,128}#', '?&amp;'], ['', '?'], $url);

		if (substr($url, -1) == '&')
		{
			$url = substr_replace($url, '', -1);
		}
		if (substr($url, -1) == '?')
		{
			$url = substr_replace($url, '', -1);
		}
	
		return $url;
	}

	/**
	 * Return param bool for javascript options
	 * @param bool|int $option
	 * @return string
	 */
	public function return_js_bool($option)
	{
		return ($option) ? 'true' : 'false';
	}

	/**
	 * Test if the extension abbc3 is running
	 * @return bool
	 */
	public function abbc3_exist()
	{
		return $this->phpbb_container->has('vse.abbc3.bbcodes_config');
	}

	/**
	 * Test if the extension smiliecreator is running and auth is ok
	 * @return bool
	 */
	public function smiliecreator_exist()
	{
		// Verify auth since 1.6.0
		if ($this->auth->acl_get('u_creator_use'))
		{
			return $this->phpbb_container->has('sylver35.smilecreator.listener');
		}
		return false;
	}

	/**
	 * Test if the extension smiliescat is running
	 * @return bool
	 */
	public function smiliescategory_exist()
	{
		return $this->phpbb_container->has('sylver35.smiliescat.listener');
	}

	/**
	 * Test if the extension breizhcharts is running
	 * @return bool
	 */
	public function breizhcharts_exist()
	{
		return $this->phpbb_container->has('sylver35.breizhcharts.main.listener');
	}

	/**
	 * Test if the extension qte is running
	 * @return bool
	 */
	public function qte_exist()
	{
		return $this->phpbb_container->has('ernadoo.qte.main_listener');
	}

	/**
	 * Test if the extension mention is running
	 * @return bool
	 */
	public function mention_exist()
	{
		return $this->phpbb_container->has('paul999.mention.controller');
	}

	/**
	 * Test if the extension breizhyoutube is running
	 * @return bool
	 */
	public function breizhyoutube_exist()
	{
		return $this->phpbb_container->has('sylver35.breizhyoutube.listener');
	}

	/**
	 * Test if the extension relaxarcade is running
	 * @return bool
	 */
	public function relaxarcade_exist()
	{
		return $this->phpbb_container->has('teamrelax.relaxarcade.listener.main');
	}

	/**
	 * Get version of extension from cache
	 * @paran bool $version just the version or array
	 * @return string|array
	 */
	public function get_version($version = false)
	{
		if (($data = $this->cache->get('_shout_version')) === false)
		{
			$md_manager = $this->ext_manager->create_extension_metadata_manager('sylver35/breizhshoutbox');
			$meta = $md_manager->get_metadata();

			$data = [
				'version'	=> $meta['version'],
				'homepage'	=> $meta['homepage'],
			];
			// cache for 7 days
			$this->cache->put('_shout_version', $data, 604800);
		}

		return ($version) ? $data['version'] : $data;
	}

	public function build_sound_select($actual, $sort)
	{
		$soundlist = $this->filelist_all($this->ext_path, 'sounds/', 'mp3');
		$title = ($actual == 1) ? $this->language->lang('SHOUT_SOUND_EMPTY') : $actual;
		$select = ($actual == 1) ? ' selected="selected"' : '';
		$sound_select = '<select title="' . $title . '" id="shout_sound_' . $sort . '" name="shout_sound_' . $sort . '" onchange="configs.changeValue(this.value,\'sound_' . $sort . '\');">';
		$sound_select .= '<option value="1"' . $select . '>' . $this->language->lang('SHOUT_SOUND_EMPTY') . '</option>';
		foreach ($soundlist as $key => $sounds)
		{
			foreach ($sounds as $sound)
			{
				$sound = strtr($sound, ['.mp3' => '']);
				$selected = ($sound === $actual) ? ' selected="selected"' : '';
				$sound_select .= '<option title="' . $sound . '" value="' . $sound . '"' . $selected . '>' . $sound . '</option>';
			}
		}
		$sound_select .= '</select>';

		return $sound_select;
	}

	public function filelist_all($rootdir, $dir = '', $type = '', $sort_values = true)
	{
		if (!function_exists('filelist'))
		{
			include($this->root_path . 'includes/functions_admin.' . $this->php_ext);
		}

		$type = ($type) ? $type : 'gif|jpg|jpeg|png|webp|jp2|j2k|jpf|jpm|jpg2|j2c|jpc';
		$list = filelist($rootdir, $dir, $type);
		natcasesort($list);
		if ($sort_values)
		{
			$list = array_values($list);
		}

		return $list;
	}

	public function set_user_option($option, $conf, $sort)
	{
		$value = '';
		switch ($sort)
		{
			case 1:
				$value = (string) (($option === 'N') ? $this->config[$conf] : $option);
			break;

			case 2:
				$value = (int) (($option === 3) ? $this->config[$conf] : $option);
			break;

			case 3:
			case 4:
				$value = (bool) (($option === 2) ? $this->config[$conf] : $option);
			break;

			case 5:
				$value = (string) (($option === '') ? $this->config[$conf] : $option);
			break;
		}

		return $value;
	}

	public function build_select_position($value, $index = false)
	{
		// No selected_3 because it's the defaut value
		$selected_0 = $selected_1 = $selected_2 = $selected_4 = '';
		switch ($value)
		{
			case 0:
				$selected_0 = ' selected="selected"';
			break;
			case 1:
				$selected_1 = ' selected="selected"';
			break;
			case 2:
				$selected_2 = ' selected="selected"';
			break;
			case 4:
				$selected_4 = ' selected="selected"';
			break;
		}

		$option = '<option title="' . $this->language->lang('SHOUT_POSITION_NONE') . '" value="0"' . $selected_0 . '>' . $this->language->lang('SHOUT_POSITION_NONE') . '</option>';
		$option .= '<option title="' . $this->language->lang('SHOUT_POSITION_TOP') . '" value="1"' . $selected_1 . '>' . $this->language->lang('SHOUT_POSITION_TOP') . '</option>';
		if ($index)
		{
			$option .= '<option title="' . $this->language->lang('SHOUT_POSITION_AFTER') . '" value="4"' . $selected_4 . '>' . $this->language->lang('SHOUT_POSITION_AFTER') . '</option>';
		}
		$option .= '<option title="' . $this->language->lang('SHOUT_POSITION_END') . '" value="2"' . $selected_2 . '>' . $this->language->lang('SHOUT_POSITION_END') . '</option>';

		return $option;
	}

	public function build_dateformat_option($dateformat)
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
	 * Check if the rules with apropriate language exist
	 */
	public function check_shout_rules($sort)
	{
		if ($this->config['shout_rules'])
		{
			$iso = $this->user->lang_name;
			if ($this->config->offsetExists('shout_rules' . $sort . '_' . $iso))
			{
				if ($this->config['shout_rules' . $sort . '_' . $iso])
				{
					return $iso;
				}
			}
			else
			{
				if ($this->config->offsetExists('shout_rules' . $sort . '_en'))
				{
					if ($this->config['shout_rules' . $sort . '_en'])
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
	public function get_shout_rules()
	{
		if (($rules = $this->cache->get('_shout_rules')) === false)
		{
			$sql_ary = [
				'SELECT'	=> 'l.lang_iso, r.*',
				'FROM'		=> [LANG_TABLE => 'l'],
				'LEFT_JOIN'	=> [
					[
						'FROM'	=> [$this->shoutbox_rules_table => 'r'],
						'ON'	=> 'r.rules_lang = l.lang_iso',
					],
				],
			];
			$result = $this->shout_sql_query($this->db->sql_build_query('SELECT', $sql_ary));
			if (!$result)
			{
				return;
			}
			while ($row = $this->db->sql_fetchrow($result))
			{
				$rules[$row['lang_iso']] = [
					'rules_id'				=> $row['id'],
					'rules_text'			=> $row['rules_text'],
					'rules_uid'				=> $row['rules_uid'],
					'rules_bitfield'		=> $row['rules_bitfield'],
					'rules_flags'			=> $row['rules_flags'],
					'rules_text_priv'		=> $row['rules_text_priv'],
					'rules_uid_priv'		=> $row['rules_uid_priv'],
					'rules_bitfield_priv'	=> $row['rules_bitfield_priv'],
					'rules_flags_priv'		=> $row['rules_flags_priv'],
				];
			}
			$this->db->sql_freeresult($result);

			// cache for 7 days
			$this->cache->put('_shout_rules', $rules, 604800);
		}

		return $rules;
	}

	public function get_session_shout($table, $sessions, $user_id)
	{
		if (!$this->config[$sessions])
		{
			return false;
		}

		$interval = (int) $this->config['shout_sessions_time'] * 60;
		$sql = 'SELECT shout_time
			FROM ' . $table . '
				WHERE shout_robot = 1 AND shout_robot_user = ' . $user_id . ' AND shout_time BETWEEN ' . (time() - $interval) . ' AND ' . time();
		$result = $this->db->sql_query($sql);
		$go_post = $this->db->sql_fetchfield('shout_time') ? false : true;
		$this->db->sql_freeresult($result);

		return $go_post;
	}

	/*
	 * Replace relatives urls with complete urls
	 */
	public function shout_url($url)
	{
		return str_replace(['./../../../../', './../../../', './../../', './../', './'], $this->root_path_web, $url);
	}

	/*
	 * protect title value for robot messages
	 */
	public function shout_protect_title($value1, $value2)
	{
		$value = ($value2 !== '') ? $value2 : $value1;
		$value = str_replace('&amp;', '&', strip_tags($value));
		$value = preg_replace('/\&#([^>]+)\;/', '', $value);
		$value = str_replace(['&lt;', '&gt;', '&quot;'], '', $value);

		return htmlspecialchars($value, ENT_QUOTES);
	}

	public function plural($lang, $nr, $second, $content = '')
	{
		$text = $lang;
		$text .= ($nr > 1) ? 'S' : '';
		$text .= $second;
		if ($content !== '')
		{
			$text = $this->language->lang($text, $nr, $content);
		}

		return $text;
	}

	/**
	 * Extract information from a string
	 *
	 * @param $string	string where search in
	 * @param $start	string start of search
	 * @param $end		string end of search
	 * Return string or int
	 */
	public function find_string($string, $start, $end)
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

	/*
	 * Forms for robot messages and actions
	 */
	public function tpl($sort, $data1 = '', $data2 = '', $data3 = '')
	{
		$data4 = '';
		switch ($sort)
		{
			case 'cite':
				$data4 = $this->config['shout_color_message'];
			break;
			case 'url':
				$data3 = $this->shout_protect_title($data2, $data3);
			break;
			case 'italic':
			case 'colorbot':
				$data2 = $this->config['shout_color_message'];
			break;
			case 'personal':
				$data1 = $this->language->lang('SHOUT_ACTION_MSG');
			break;
			case 'citemsg':
				$data1 = $this->language->lang('SHOUT_ACTION_CITE_EXPLAIN');
				$data2 = $this->language->lang('SHOUT_ACTION_CITE');
			break;
			case 'citemulti':
				$data3 = $this->language->lang('SHOUT_ACTION_CITE_M_EXPLAIN');
				$data4 = $this->language->lang('SHOUT_ACTION_CITE_M');
			break;
			case 'perso':
				$data2 = $this->language->lang('SHOUT_ACTION_PERSO');
			break;
			case 'robot':
				$data2 = $this->language->lang('SHOUT_ACTION_MSG_ROBOT', $this->config['shout_name_robot']);
				$data3 = $this->language->lang('SHOUT_ACTION_MSG_ROBOT', $this->construct_action_shout(0));
			break;
			case 'auth':
				$data3 = $this->language->lang('SHOUT_ACTION_AUTH');
			break;
			case 'prefs':
				$data2 = $this->language->lang('SHOUT_CONFIG_OPEN_TO');
			break;
			case 'delreqto':
				$data2 = $this->language->lang('SHOUT_ACTION_DEL_TO_EXPLAIN');
				$data3 = $this->language->lang('SHOUT_ACTION_DEL_TO');
			break;
			case 'delreq':
				$data2 = $this->language->lang('SHOUT_ACTION_DELETE_EXPLAIN');
				$data3 = $this->language->lang('SHOUT_ACTION_DELETE');
			break;
			case 'remove':
				$data2 = $this->language->lang('SHOUT_ACTION_REMOVE_EXPLAIN');
				$data3 = $this->language->lang('SHOUT_ACTION_REMOVE');
			break;
			case 'profile':
				$data2 = $this->language->lang('SHOUT_ACTION_PROFIL', $data2);
			break;
			case 'admin':
				$data2 = $this->language->lang('SHOUT_ACTION_ADMIN');
			break;
			case 'modo':
				$data2 = $this->language->lang('SHOUT_ACTION_MCP');
			break;
			case 'ban':
				$data2 = $this->language->lang('SHOUT_ACTION_BAN');
			break;	
		}

		return sprintf($this->config['shout_tpl_' . $sort], $data1, $data2, $data3, $data4);
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
		else if ($id == ANONYMOUS || !$this->user->data['is_registered'] || $this->user->data['is_bot'])
		{
			$username_full = get_username_string('no_profile', $id, $username, $colour);
		}
		else if ($acp)
		{
			$username_full = get_username_string('full', $id, $username, $colour);
		}
		else
		{
			if ($this->auth->acl_gets(['u_shout_post_inp', 'a_', 'm_']))
			{
				$username_full = $this->tpl('action', $id, $this->language->lang('SHOUT_ACTION_TITLE_TO', $username), get_username_string('no_profile', $id, $username, $colour));
			}
			else
			{
				$username_full = get_username_string('full', $id, $username, $colour, '', append_sid("{$this->root_path_web}memberlist.{$this->php_ext}", "mode=viewprofile"));
			}
		}

		return $this->shout_url($username_full);
	}

	public function create_action_user($row, $founder, $self)
	{
		$auths = $this->get_auths($self);
		$urls = $this->get_urls($row['user_id']);

		return [
			'url_profile'	=> $this->tpl('profile', $urls[1], $row['username']),
			'url_auth'		=> $this->get_tpl_auth(1, $auths[7], '', $row),
			'url_admin'		=> $this->get_tpl_auth(3, $auths[2], $urls[2], $row),
			'url_robot'		=> $this->get_tpl_auth(8, $auths[8], $urls, $row),
			'url_prefs'		=> $this->get_tpl_auth(2, $auths[7], $urls[5], '', $founder, $self, $auths[9]),
			'url_modo'		=> $this->get_tpl_auth(4, $auths[3], $urls[3], $row, $founder, $self),
			'url_ban'		=> $this->get_tpl_auth(5, $auths[4], $urls[4], $row, $founder, $self),
			'url_remove'	=> $this->get_tpl_auth(6, $auths[1], '', $row, $founder, $self, $auths[5]),
			'url_perso'		=> $this->get_tpl_auth(7, $auths[7], '', $row, $founder, $self, $auths[9]),
		];
	}

	public function self_data_user()
	{
		$data = ['foe' => 0];
		$list = ['user_id', 'username', 'user_colour', 'user_avatar', 'user_avatar_type', 'user_avatar_width', 'user_avatar_height', 'user_type'];

		for ($i = 0, $nb = sizeof($list); $i < $nb; $i++)
		{
			$data[$list[$i]] = $this->user->data[$list[$i]];
		}

		return $data;
	}

	private function get_tpl_auth($sort, $auth, $url = '', $row = '', $founder = false, $self = false, $more = false)
	{
		$data = '';
		switch ($sort)
		{
			case 1:
				$data = $auth ? $this->tpl('auth', $row['user_id'], $row['username']) : '';
			break;
			case 2:
				$data = ($auth && (!$founder || $self) || ($more && $self)) ? $this->tpl('prefs', $url) : '';
			break;
			case 3:
				$data = $auth ? $this->tpl('admin', $url) : '';
			break;
			case 4:
				$data = ($auth && !$founder) ? $this->tpl('modo', $url) : '';
			break;
			case 5:
				$data = ($auth && (!$founder && !$self)) ? $this->tpl('ban', $url) : '';
			break;
			case 6:
				$data = (($auth || $more) && (!$founder || $self)) ? $this->tpl('remove', $row['user_id']) : '';
			break;
			case 7:
				$data = ($auth || $more) ? $this->tpl('perso', $row['user_id']) : '';
			break;
			case 8:
				$data = $auth ? $this->tpl('robot', $sort) : '';
			break;
		}

		return $data;
	}

	private function get_auths($self)
	{
		return [
			1 =>	$this->auth->acl_get('a_shout_manage'),
			2 =>	$this->auth->acl_get('a_user'),
			3 =>	$this->auth->acl_gets(['a_shout_manage', 'm_']),
			4 =>	$this->auth->acl_get('m_ban'),
			5 =>	$this->auth->acl_get('m_shout_delete'),
			6 =>	$this->auth->acl_get('m_shout_personal'),
			7 =>	$this->auth->acl_gets(['a_shout_manage', 'm_shout_personal']),
			8 =>	$this->auth->acl_gets(['a_shout_manage', 'm_shout_robot']),
			9 =>	$this->auth->acl_get('u_shout_bbcode_change') && $self,
		];
	}

	private function get_urls($user_id)
	{
		return [
			1 =>	append_sid("{$this->root_path_web}memberlist.{$this->php_ext}", ['mode' => 'viewprofile', 'u' => $user_id], false),
			2 =>	append_sid("{$this->adm_path()}index.{$this->php_ext}", ['i' => 'users', 'mode' => 'overview', 'u' => $user_id], true, $this->user->session_id),
			3 =>	append_sid("{$this->root_path_web}mcp.{$this->php_ext}", ['i' => 'notes', 'mode' => 'user_notes', 'u' => $user_id], true),
			4 =>	append_sid("{$this->root_path_web}mcp.{$this->php_ext}", ['i' => 'ban', 'mode' => 'user', 'u' => $user_id], true),
			5 =>	$this->helper->route('sylver35_breizhshoutbox_configshout') . '?user_id=' . $user_id,
		];
	}

	/*
	 * Destroy all settings files in cache for all users
	 */
	public function destroy_sessions_files()
	{
		$rootdir = $this->root_path . 'cache/' . PHPBB_ENVIRONMENT . '/';
		$list_files = $this->get_cache_files();

		foreach ($list_files as $file)
		{
			if ($error = unlink($rootdir . $file))
			{
				unset($error);
				continue;
			}
		}
	}

	/*
	 * Destroy actual settings file in cache for a user
	 */
	public function update_session_file($user_id, $other = 0, $session_id = '')
	{
		$errors = [];
		if ($other)
		{
			$sql = 'SELECT session_id, session_user_id
				FROM ' . SESSIONS_TABLE . '
					WHERE session_user_id = ' . (int) $user_id;
			$result = $this->db->sql_query($sql);
			$session_id = (string) $this->db->sql_fetchfield('session_id');
			$this->db->sql_freeresult($result);
		}
		else if ($session_id)
		{
			$session_id = $session_id;
		}
		else
		{
			$session_id = $this->user->data['session_id'];
		}

		$file_php = $this->root_path . 'cache/' . PHPBB_ENVIRONMENT . '/data_shout_config_' . $user_id . '_' . $session_id . '.' . $this->php_ext;
		if (file_exists($file_php))
		{
			if ($error = unlink($file_php))
			{
				$errors[] = $error;
			}
		}
		if (file_exists($file_php . '.lock'))
		{
			if ($error = unlink($file_php . '.lock'))
			{
				$errors[] = $error;
			}
		}
		unset($error);

		return $errors;
	}

	/*
	 * Destroy all old settings files in cache for a user
	 */
	public function destroy_old_user_settings($user_id, $session_id)
	{
		$files = $this->get_cache_files();
		$rootdir = $this->root_path . 'cache/' . PHPBB_ENVIRONMENT . '/';

		foreach ($files as $file)
		{
			// Don't destroy actual session file
			if (str_starts_with($file, 'data_shout_config_' . $user_id . '_' . $session_id))
			{
				continue;
			}
			else if (str_starts_with($file, 'data_shout_config_' . $user_id))
			{
				if ($error = unlink($rootdir . $file))
				{
					unset($error);
					continue;
				}
			}
		}
	}

	/*
	 * Get all data_shout_config files from cache
	 */
	public function get_cache_files()
	{
		$rootdir = $this->root_path . 'cache/';
		$dir = PHPBB_ENVIRONMENT . '/';
		$type = 'php|lock';
		$matches = [];

		if (!$dh = @opendir($rootdir . $dir))
		{
			return $matches;
		}

		while (($fname = readdir($dh)) !== false)
		{
			if (is_file("$rootdir$dir$fname"))
			{
				// Only the data_shout_config files
				if (preg_match('#\.' . $type . '$#i', $fname) && str_starts_with($fname, 'data_shout_config_'))
				{
					$matches[] = $fname;
				}
			}
		}
		closedir($dh);

		natcasesort($matches);
		$matches = array_values($matches);

		return $matches;
	}
}
