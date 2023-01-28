<?php
/**
*
* @package Breizh Shoutbox Extension
* @copyright (c) 2019-2023 Sylver35  https://breizhcode.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
	protected $shoutbox_rules_table;

	/**
	 * Constructor
	 */
	public function __construct(config $config, db $db, auth $auth, user $user, language $language, cache $cache, manager $ext_manager, Container $phpbb_container, phpbb_dispatcher $phpbb_dispatcher, $root_path, $php_ext, $shoutbox_rules_table)
	{
		$this->config = $config;
		$this->db = $db;
		$this->auth = $auth;
		$this->user = $user;
		$this->language = $language;
		$this->cache = $cache;
		$this->ext_manager = $ext_manager;
		$this->phpbb_container = $phpbb_container;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->root_path = $root_path;
		$this->root_path_web = generate_board_url() . '/';
		$this->php_ext = $php_ext;
		$this->shoutbox_rules_table = $shoutbox_rules_table;
		$this->ext_path = $this->ext_manager->get_extension_path('sylver35/breizhshoutbox', true);
	}

	/**
	 * Return error.
	 * @param string $message Error
	 * @return void
	 */
	public function shout_error($message, $on1 = false, $on2 = false, $on3 = false)
	{
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
			$message = str_replace(' />', '/>', $message);
			$message = preg_replace("#<b>(.*?)<br/>#i", '', $message);
		}

		$response = new \phpbb\json_response;
		$response->send([
			'type'		=> 10,
			'error'		=> true,
			'message'	=> $message,
		], true);
	}

	/**
	 * execute sql query or return error
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
	 * Prints a sql error.
	 * @param string $sql Sql query
	 * @param int $line Line number
	 * @param string $file Filename
	 * @return void
	 */
	private function shout_sql_error($sql, $line, $file)
	{
		$err = str_replace(' />', '/>', $this->db->sql_error());
		$err = preg_replace("#<b>(.*?)<br/>#i", '', $err);
		$response = new \phpbb\json_response;

		$response->send([
			'message'	=> $err['message'],
			'line'		=> $line,
			'file'		=> $file,
			'content'	=> $sql,
			'error'		=> true,
			't'			=> 1,
		], true);
	}

	public function return_bool($option)
	{
		return ($option) ? 'true' : 'false';
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
	 * test if the extension breizhcharts is running
	 * @return bool
	 */
	public function breizhcharts_exist()
	{
		if ($this->phpbb_container->has('sylver35.breizhcharts.main.listener'))
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

		$type = ($type !== '') ? $type : 'gif|jpg|jpeg|png|webp|jp2|j2k|jpf|jpm|jpg2|j2c|jpc';
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
		// No selected_3 because it's the defaut
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

	/*
	 * Replace relatives urls with complete urls
	 */
	public function shout_url($url)
	{
		return str_replace(['./../../../../', './../../../', './../../', './../', './'], $this->root_path_web, $url);
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
			$username_full = get_username_string('no_profile', $id, $username, (($id == ANONYMOUS) ? '6666FF' : $colour));
		}
		else if ($id === $this->user->data['user_id'] || $acp)
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
		//return sprintf($this->clean_tpl('shout_tpl_' . $sort), $data1, $data2, $data3, $data4);
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
}
