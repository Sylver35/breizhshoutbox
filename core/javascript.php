<?php
/**
*
* @package phpBB Extension - Breizh Shoutbox
* @copyright (c) 2018-2025 Sylver35  https://breizhcode.com
* @license https://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace sylver35\breizhshoutbox\core;

use sylver35\breizhshoutbox\core\work;
use phpbb\exception\http_exception;
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\path_helper;
use phpbb\db\driver\driver_interface as db;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\auth\auth;
use phpbb\user;
use phpbb\language\language;
use phpbb\cache\driver\driver_interface as cache;
use phpbb\extension\manager;

class javascript
{
	/* @var \sylver35\breizhshoutbox\core\work */
	protected $work;

	/** @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\path_helper */
	protected $path_helper;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

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

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\extension\manager */
	protected $ext_manager;

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

	/**
	 * Constructor
	 */
	public function __construct(work $work, config $config, helper $helper, path_helper $path_helper, db $db, request $request, template $template, auth $auth, user $user, language $language, cache $cache, manager $ext_manager, $root_path, $php_ext)
	{
		$this->work = $work;
		$this->config = $config;
		$this->helper = $helper;
		$this->path_helper = $path_helper;
		$this->db = $db;
		$this->request = $request;
		$this->template = $template;
		$this->auth = $auth;
		$this->user = $user;
		$this->language = $language;
		$this->cache = $cache;
		$this->ext_manager = $ext_manager;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		$this->root_path_web = generate_board_url() . '/';
		$this->ext_path = $this->ext_manager->get_extension_path('sylver35/breizhshoutbox', true);
		$this->ext_path_web = $this->path_helper->update_web_root_path($this->ext_path);
	}

	public function active_config_shoutbox($user_id)
	{
		if (!$this->user->data['is_registered'] || $this->user->data['is_bot'] || !$this->auth->acl_get('u_shout_post') || ((int) $user_id !== (int) $this->user->data['user_id']) && !$this->auth->acl_gets(['a_shout_manage', 'm_shout_personal']))
		{
			throw new http_exception(403, 'NOT_AUTHORISED');
		}

		$other = ((int) $user_id !== (int) $this->user->data['user_id']);
		if ($this->request->is_set_post('submit'))
		{
			$this->config_submit($user_id, $other);
		}
		else if ($this->request->is_set_post('return'))
		{
			$this->config_return($user_id, $other);
		}
		else
		{
			$this->data_config_shoutbox($user_id, $other);
		}
	}

	private function config_submit($user_id, $other)
	{
		$user_shout = [
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
		];
		$user_shoutbox = [
			'bar'			=> $this->request->variable('shout_bar', 2),
			'bar_pop'		=> $this->request->variable('shout_bar_pop', 2),
			'bar_priv'		=> $this->request->variable('shout_bar_priv', 2),
			'defil'			=> $this->request->variable('shout_defil', 2),
			'defil_pop'		=> $this->request->variable('shout_defil_pop', 2),
			'defil_priv'	=> $this->request->variable('shout_defil_priv', 2),
			'panel'			=> $this->request->variable('shout_panel', 2),
			'panel_float'	=> $this->request->variable('shout_panel_float', 2),
			'dateformat'	=> $this->request->variable('dateformat', '', true),
		];

		$sql = 'UPDATE ' . USERS_TABLE . "
			SET user_shout = '" . $this->db->sql_escape(json_encode($user_shout)) . "', user_shoutbox = '" . $this->db->sql_escape(json_encode($user_shoutbox)) . "'
				WHERE user_id = " . (int) $user_id;
		$this->db->sql_query($sql);

		$errors = $this->work->update_session_file($user_id, $other);
		unset($errors);
		$redirect_url = $this->helper->route('sylver35_breizhshoutbox_configshout') . '?user_id=' . $user_id;
		meta_refresh(2, $redirect_url);
		trigger_error($this->language->lang('SHOUT_REDIRECT') . '<br><br>' . $this->language->lang('RETURN_PAGE', '<a href="' . $redirect_url . '">', '</a>'));
	}

	private function config_return($user_id, $other)
	{
		$user_shout = [
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
		];
		$user_shoutbox = [
			'bar'			=> 2,
			'bar_pop'		=> 2,
			'bar_priv'		=> 2,
			'defil'			=> 2,
			'defil_pop'		=> 2,
			'defil_priv'	=> 2,
			'panel'			=> 2,
			'panel_float'	=> 2,
			'dateformat'	=> '',
		];

		$sql = 'UPDATE ' . USERS_TABLE . "
			SET user_shout = '" . $this->db->sql_escape(json_encode($user_shout)) . "', user_shoutbox = '" . $this->db->sql_escape(json_encode($user_shoutbox)) . "'
				WHERE user_id = " . (int) $user_id;
		$this->db->sql_query($sql);

		$errors = $this->work->update_session_file($user_id, $other);
		unset($errors);
		$redirect_url = $this->helper->route('sylver35_breizhshoutbox_configshout') . '?user_id=' . $user_id;
		meta_refresh(2, $redirect_url);
		trigger_error($this->language->lang('SHOUT_REDIRECT') . '<br><br>' . $this->language->lang('RETURN_PAGE', '<a href="' . $redirect_url . '">', '</a>'));
	}

	private function data_config_shoutbox($user_id, $other)
	{
		$this->language->add_lang('ucp');
		$title_panel = $this->language->lang('SHOUT_PANEL_USER');

		if (!$other)
		{
			$user_shout = json_decode($this->user->data['user_shout']);
			$user_shoutbox = json_decode($this->user->data['user_shoutbox']);
		}
		else if ($other && $this->auth->acl_gets(['a_shout_manage', 'm_shout_personal']))
		{
			$sql = 'SELECT username, user_shout, user_shoutbox
				FROM ' . USERS_TABLE . '
					WHERE user_id = ' . (int) $user_id;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$title_panel = $this->language->lang('SHOUT_PANEL_TO_USER', $row['username']);
			$user_shout = json_decode($row['user_shout']);
			$user_shoutbox = json_decode($row['user_shoutbox']);
			$this->db->sql_freeresult($result);
		}
		else
		{
			throw new http_exception(403, 'NOT_AUTHORISED');
		}

		$user_shout->user = $this->work->set_user_option($user_shout->user, 'shout_sound_on', 4);
		$user_shout->new = $this->work->set_user_option($user_shout->new, 'shout_sound_new', 1);
		$user_shout->new_priv = $this->work->set_user_option($user_shout->new_priv, 'shout_sound_new_priv', 1);
		$user_shout->error = $this->work->set_user_option($user_shout->error, 'shout_sound_error', 1);
		$user_shout->del = $this->work->set_user_option($user_shout->del, 'shout_sound_del', 1);
		$user_shout->add = $this->work->set_user_option($user_shout->add, 'shout_sound_add', 1);
		$user_shout->edit = $this->work->set_user_option($user_shout->edit, 'shout_sound_edit', 1);
		$version = $this->work->get_version();

		$this->template->assign_vars([
			'IN_SHOUT_CONFIG'		=> true,
			'USER_ID'				=> $this->user->data['user_id'],
			'OTHER'					=> $other,
			'TITLE_PANEL'			=> $title_panel,
			'SOUND_NEW_DISP'		=> $user_shout->user && $user_shout->new !== '1',
			'SOUND_NEW_PRIV_DISP'	=> $user_shout->user && $user_shout->new_priv !== '1',
			'SOUND_DEL_DISP'		=> $user_shout->user && $user_shout->del !== '1',
			'SOUND_ADD_DISP'		=> $user_shout->user && $user_shout->add !== '1',
			'SOUND_EDIT_DISP'		=> $user_shout->user && $user_shout->edit !== '1',
			'SOUND_ERROR_DISP'		=> $user_shout->error !== '1',
			'NEW_SOUND'				=> $this->work->build_sound_select($user_shout->new, 'new'),
			'NEW_SOUND_PRIV'		=> $this->work->build_sound_select($user_shout->new_priv, 'new_priv'),
			'ERROR_SOUND'			=> $this->work->build_sound_select($user_shout->error, 'error'),
			'DEL_SOUND'				=> $this->work->build_sound_select($user_shout->del, 'del'),
			'ADD_SOUND'				=> $this->work->build_sound_select($user_shout->add, 'add'),
			'EDIT_SOUND'			=> $this->work->build_sound_select($user_shout->edit, 'edit'),
			'USER_SOUND_YES'		=> $user_shout->user,
			'USER_SOUND_INFO'		=> $user_shout->new,
			'USER_SOUND_INFO_PRIV'	=> $user_shout->new_priv,
			'USER_SOUND_INFO_E'		=> $user_shout->error,
			'USER_SOUND_INFO_D'		=> $user_shout->del,
			'USER_SOUND_INFO_A'		=> $user_shout->add,
			'USER_SOUND_INFO_ED'	=> $user_shout->edit,
			'SHOUT_BAR'				=> $this->work->set_user_option($user_shoutbox->bar, 'shout_bar_option', 3),
			'SHOUT_BAR_POP'			=> $this->work->set_user_option($user_shoutbox->bar_pop, 'shout_bar_option_pop', 3),
			'SHOUT_BAR_PRIV'		=> $this->work->set_user_option($user_shoutbox->bar_priv, 'shout_bar_option_priv', 3),
			'SHOUT_DEFIL'			=> $this->work->set_user_option($user_shoutbox->defil, 'shout_defil', 3),
			'SHOUT_DEFIL_POP'		=> $this->work->set_user_option($user_shoutbox->defil_pop, 'shout_defil_pop', 3),
			'SHOUT_DEFIL_PRIV'		=> $this->work->set_user_option($user_shoutbox->defil_priv, 'shout_defil_priv', 3),
			'SHOUT_PANEL'			=> $this->work->set_user_option($user_shoutbox->panel, 'shout_panel', 3),
			'SHOUT_PANEL_FLOAT'		=> $this->work->set_user_option($user_shoutbox->panel_float, 'shout_panel_float', 3),
			'SELECT_ON_INDEX'		=> $this->work->build_select_position($this->work->set_user_option($user_shout->index, 'shout_position_index', 2), true),
			'SELECT_ON_FORUM'		=> $this->work->build_select_position($this->work->set_user_option($user_shout->forum, 'shout_position_forum', 2)),
			'SELECT_ON_TOPIC'		=> $this->work->build_select_position($this->work->set_user_option($user_shout->topic, 'shout_position_topic', 2)),
			'DATE_FORMAT'			=> $this->work->set_user_option($user_shoutbox->dateformat, 'shout_dateformat', 5),
			'DATE_FORMAT_EX'		=> $this->user->format_date(time() - 60 * 61, $user_shoutbox->dateformat),
			'DATE_FORMAT_EX2'		=> $this->user->format_date(time() - 60 * 60 * 60, $user_shoutbox->dateformat),
			'S_DATEFORMAT_OPTIONS'	=> $this->work->build_dateformat_option($user_shoutbox->dateformat),
			'S_POP'					=> ($other) ? $this->auth->acl_get_list($user_id, 'u_shout_popup') : $this->auth->acl_get('u_shout_popup'),
			'S_PRIVATE'				=> ($other) ? $this->auth->acl_get_list($user_id, 'u_shout_priv') : $this->auth->acl_get('u_shout_priv'),
			'U_SHOUT_ACTION'		=> $this->helper->route('sylver35_breizhshoutbox_configshout') . '?user_id=' . $user_id,
			'U_DATE_URL' 			=> $this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'date_format']),
			'SHOUTBOX_VERSION'		=> $this->language->lang('SHOUTBOX_VERSION_ACP_COPY', $version['homepage'], $version['version']),
			'SHOUT_EXT_PATH'		=> $this->ext_path_web,
		]);
	}

	public function javascript_shout($sort)
	{
		// Get the user's $settings from cache
		$settings = $this->get_settings_from_cache($this->initialize_data($sort));

		$this->template->assign_vars([
			'ON_SHOUT_DISPLAY'			=> true,
			'LIST_SETTINGS_AUTH'		=> $settings[$sort]['settings_auth'],
			'LIST_SETTINGS_STRING'		=> $settings[$sort]['settings_string'],
			'LIST_SETTINGS_LANG'		=> $settings[$sort]['settings_lang'],
		]);
	}

	private function initialize_data($sort)
	{
		$version = $this->work->get_version();
		$data = [
			'sort'		=> '',
			'sort_perm'	=> '_manage',
			'sort_of'	=> (int) $sort,
			'private'	=> false,
			'popup'		=> false,
			'user_id'	=> (int) $this->user->data['user_id'],
			'creator'	=> (bool) $this->work->smiliecreator_exist(),
			'category'	=> (bool) $this->work->smiliescategory_exist(),
			'is_mobile'	=> (bool) $this->work->shout_is_mobile(),
			'is_user'	=> (bool) $this->user->data['is_registered'] && !$this->user->data['is_bot'],
			'is_bot'	=> (bool) $this->user->data['is_bot'],
			'guest'		=> (bool) !$this->user->data['is_registered'],
			'founder'	=> (bool) $this->user->data['user_type'] == USER_FOUNDER,
			'version'	=> (string) $version['version'],
			'homepage'	=> (string) $version['homepage'],
		];

		switch ($sort)
		{
			// Popup shoutbox
			case 1:
				$data['popup'] = true;
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

		return $data;
	}

	private function get_settings_from_cache($data)
	{
		$settings = [];
		if ($data['is_user'])
		{
			$file = '_shout_config_' . $this->user->data['user_id'] . '_' . $this->user->data['session_id'];
		}
		else
		{
			$sort_user = ($data['is_bot']) ? 'robots_' : 'anonymous_';
			$file = '_shout_config_' . $sort_user . $this->user->data['user_lang'];
		}

		if (($settings_user = $this->cache->get($file)) === false)
		{
			// Settings for popup shoutbox
			$settings[1] = $this->get_settings($this->initialize_data(1));
			$settings_user[1] = [
				'settings_auth'		=> $settings[1]['settings_auth'],
				'settings_string'	=> $settings[1]['settings_string'],
				'settings_lang'		=> $settings[1]['settings_lang'],
			];
			// Settings for normal shoutbox
			$settings[2] = $this->get_settings($this->initialize_data(2));
			$settings_user[2] = [
				'settings_auth'		=> $settings[2]['settings_auth'],
				'settings_string'	=> $settings[2]['settings_string'],
				'settings_lang'		=> $settings[2]['settings_lang'],
			];
			// Settings for private shoutbox protected at this time
			if ($data['is_user'] && $this->auth->acl_gets(['a_shout_manage', 'a_shout_priv', 'u_shout_priv']))
			{
				$settings[3] = $this->get_settings($this->initialize_data(3));
				$settings_user[3] = [
					'settings_auth'		=> $settings[3]['settings_auth'],
					'settings_string'	=> $settings[3]['settings_string'],
					'settings_lang'		=> $settings[3]['settings_lang'],
				];
			}

			// Destroy old user settings files
			if ($data['is_user'])
			{
				$this->work->destroy_old_user_settings($this->user->data['user_id'], $this->user->data['session_id']);
			}

			// cache for config sessions time
			$time = (int) $this->config['session_length'];
			if ($this->config['max_autologin_time'])
			{
				$time = (int) $this->config['max_autologin_time'] * 86400;
			}
			$this->cache->put($file, $settings_user, $time);
		}

		return $settings_user;
	}

	private function get_settings($data)
	{
		$i = $j = $k = 0;
		$data = $this->create_user_settings($data);
		$list_auth = $this->auth_to_javascript($data);
		$list_string = $this->settings_to_javascript($data);
		$list_lang = $this->lang_to_javascript($data);
		$name = $this->language->lang(['SHOUT_SORT', $data['sort_of']]);

		// Construct the javascript now
		$settings_auth = "// Settings for shoutbox $name\n	var shoutbox = {};\n	var config = {\n		";
		foreach ($list_auth as $key => $value)
		{
			$settings_auth .= $key . ':' . $value . ', ';
			if ($i > 18)
			{
				$settings_auth .= "\n		";
				$i = 0;
			}
			$i++;
		}

		$settings_string = "	";
		foreach ($list_string as $key => $value)
		{
			$settings_string .= $key . ":'" . $value . "', ";
			if ($j > 9)
			{
				$settings_string .= "\n		";
				$j = 0;
			}
			$j++;
		}
		$settings_string .= "\n	};";

		$settings_lang = "var bzhLang = {\n		";
		foreach ($list_lang as $key => $value)
		{
			$settings_lang .= "'" . $key . "':" . json_encode($value) . ', ';
			if ($k > 7)
			{
				$settings_lang .= "\n		";
				$k = 0;
			}
			$k++;
		}
		$settings_lang .= "\n	};";

		return [
			'settings_auth'		=> $settings_auth,
			'settings_string'	=> $settings_string,
			'settings_lang'		=> $settings_lang,
		];
	}

	private function create_user_settings($data)
	{
		if ($data['is_user'])
		{
			$user_shout = json_decode($this->user->data['user_shout']);
			$user_shoutbox = json_decode($this->user->data['user_shoutbox']);

			$data = array_merge($data, [
				'refresh'					=> $this->config['shout_temp_users'] * 1000,
				'inactiv'					=> ($this->auth->acl_get('u_shout_inactiv') || $data['private']) ? 0 : $this->config['shout_inactiv_member'],
				'dateformat'				=> $this->work->set_user_option($user_shoutbox->dateformat, 'shout_dateformat', 5),
				'shout_bar_option'			=> $this->work->set_user_option($user_shoutbox->bar, 'shout_bar_option', 3),
				'shout_bar_option_pop'		=> $this->work->set_user_option($user_shoutbox->bar_pop, 'shout_bar_option_pop', 3),
				'shout_bar_option_priv'		=> $this->work->set_user_option($user_shoutbox->bar_priv, 'shout_bar_option_priv', 3),
				'shout_defil'				=> $this->work->set_user_option($user_shoutbox->defil, 'shout_defil', 3),
				'shout_defil_pop'			=> $this->work->set_user_option($user_shoutbox->defil_pop, 'shout_defil_pop', 3),
				'shout_defil_priv'			=> $this->work->set_user_option($user_shoutbox->defil_priv, 'shout_defil_priv', 3),
				'active'					=> $this->work->set_user_option($user_shout->user, 'shout_sound_on', 4),
				'new_priv'					=> $this->work->set_user_option($user_shout->new_priv, 'shout_sound_new_priv', 1),
				'new'						=> $this->work->set_user_option($user_shout->new, 'shout_sound_new', 1),
				'error'						=> $this->work->set_user_option($user_shout->error, 'shout_sound_error', 1),
				'del'						=> $this->work->set_user_option($user_shout->del, 'shout_sound_del', 1),
				'add'						=> $this->work->set_user_option($user_shout->add, 'shout_sound_add', 1),
				'edit'						=> $this->work->set_user_option($user_shout->edit, 'shout_sound_edit', 1),
			]);
		}
		else
		{
			$data = array_merge($data, [
				'refresh'					=> ($data['is_bot']) ? 60 * 1000 : $this->config['shout_temp_anonymous'] * 1000,
				'inactiv'					=> $this->config['shout_inactiv_anony'],
				'dateformat'				=> $this->config['shout_dateformat'],
				'shout_bar_option'			=> $this->config['shout_bar_option'],
				'shout_bar_option_pop'		=> $this->config['shout_bar_option_pop'],
				'shout_bar_option_priv'		=> $this->config['shout_bar_option_priv'],
				'shout_defil'				=> $this->config['shout_defil'],
				'shout_defil_pop'			=> $this->config['shout_defil_pop'],
				'shout_defil_priv'			=> $this->config['shout_defil_priv'],
				'active'					=> ($data['is_bot']) ? false : $this->config['shout_sound_on'],
				'new'						=> $this->config['shout_sound_new'],
				'error'						=> $this->config['shout_sound_error'],
				'del'						=> $this->config['shout_sound_del'],
				'add'						=> $this->config['shout_sound_add'],
				'edit'						=> $this->config['shout_sound_edit'],
				'new_priv'					=> '',
			]);
		}

		$style = file_exists($this->ext_path . 'styles/' . rawurlencode($this->user->style['style_path']) . '/') ? rawurlencode($this->user->style['style_path']) : 'all';
		$data['style'] = 'styles/' . $style . '/theme/images/background/';
		$data['inactiv'] = (($data['inactiv'] > 0) && !$data['private']) ? round($data['inactiv'] * 60 / ($data['refresh'] / 1000)) : 0;

		return $data;
	}

	private function auth_to_javascript($data)
	{
		// Display the rules if wanted
		$rules = $rules_open = false;
		if ($this->work->check_shout_rules($data['sort']) !== '')
		{
			$rules = true;
			// Display the rules opened by default if wanted
			$rules_open = ($this->config['shout_rules_open' . $data['sort']] && $this->auth->acl_get('u_shout_post')) ? true : false;
		}

		$settings_auth = [
			'inactivity'		=> $data['inactiv'],
			'requestOn'			=> $data['refresh'],
			'sortShoutNb'		=> $data['sort_of'],
			'userId'			=> $data['user_id'],
			'perPage'			=> $this->config['shout_num' . $data['sort_p']],
			'maxPost'			=> $this->config['shout_max_post_chars'],
			'minName'			=> $this->config['min_name_chars'],
			'maxName'			=> $this->config['max_name_chars'],
			'isUser'			=> $this->work->return_js_bool($data['is_user']),
			'isFounder'			=> $this->work->return_js_bool($data['founder']),
			'isGuest'			=> $this->work->return_js_bool($data['guest']),
			'isRobot'			=> $this->work->return_js_bool($data['is_bot']),
			'isPriv'			=> $this->work->return_js_bool($data['private']),
			'isPopup'			=> $this->work->return_js_bool($data['popup']),
			'rulesOk'			=> $this->work->return_js_bool($rules),
			'rulesOpen'			=> $this->work->return_js_bool($rules_open),
			'isMobile'			=> $this->work->return_js_bool($data['is_mobile']),
			'refresh'			=> $this->work->return_js_bool(strpos($data['dateformat'], '|') !== false),
			'seeButtons'		=> $this->work->return_js_bool((bool) $this->config['shout_see_buttons']),
			'buttonsLeft'		=> $this->work->return_js_bool((bool) $this->config['shout_see_buttons_left']),
			'storeErrors'		=> $this->work->return_js_bool((bool) $this->config['shout_store_errors']),
			'topBar'			=> $this->work->return_js_bool($data['shout_bar_option' . $data['sort_p']]),
			'toBottom'			=> $this->work->return_js_bool($data['shout_defil' . $data['sort_p']]),
			'buttonIp'			=> $this->work->return_js_bool($this->config['shout_see_button_ip']),
			'buttonCite'		=> $this->work->return_js_bool($this->config['shout_see_cite']),
			'endClassBg'		=> $this->work->return_js_bool($this->config['shout_button_background' . $data['sort_p']]),
			'purgeOn'			=> $this->work->return_js_bool($this->auth->acl_get('a_shout' . $data['sort_perm'])),
			'onlineOk'			=> $this->work->return_js_bool($this->auth->acl_gets('u_viewprofile', 'a_user', 'a_useradd', 'a_userdel')),
			'postOk'			=> $this->work->return_js_bool($this->auth->acl_get('u_shout_post')),
			'limitPost'			=> $this->work->return_js_bool($this->auth->acl_get('u_shout_limit_post')),
			'smiliesOk'			=> $this->work->return_js_bool($this->auth->acl_get('u_shout_smilies')),
			'imageOk'			=> $this->work->return_js_bool($this->auth->acl_get('u_shout_image')),
			'colorOk'			=> $this->work->return_js_bool($this->auth->acl_get('u_shout_color')),
			'bbcodeOk'			=> $this->work->return_js_bool($this->auth->acl_get('u_shout_bbcode')),
			'charsOk'			=> $this->work->return_js_bool($this->auth->acl_get('u_shout_chars')),
			'popupOk'			=> $this->work->return_js_bool($this->auth->acl_get('u_shout_popup')),
			'formatOk'			=> $this->work->return_js_bool($this->auth->acl_get('u_shout_bbcode_change') && $data['is_user']),
			'privOk'			=> $this->work->return_js_bool($this->auth->acl_get('u_shout_priv') && $data['is_user']),
			'creator'			=> $this->work->return_js_bool($data['creator']),
			'category'			=> $this->work->return_js_bool($data['category']),
			'smiliesPerPage'	=> $this->config['shout_smilies_per_page'],
		];

		return $settings_auth;
	}

	private function settings_to_javascript($data)
	{
		$list_string = [
			'session'			=> $this->user->data['session_id'],
			'userLang'			=> $this->user->data['user_lang'],
			'userName'			=> $this->user->data['username'],
			'cookieName'		=> $this->config['cookie_name'] . '_',
			'cookieDomain'		=> '; domain=' . $this->config['cookie_domain'] . ($this->config['cookie_secure'] ? '; secure' : ''),
			'cookiePath'		=> '; path=' . $this->config['cookie_path'],
			'extensionUrl'		=> $this->work->shout_url($this->ext_path_web),
			'userTimezone'		=> phpbb_format_timezone_offset($this->user->create_datetime()->getOffset()),
			'dateDefault'		=> $this->config['shout_dateformat'],
			'dateFormat'		=> $data['dateformat'],
			'enableSound'		=> ($data['active']) ? '1' : '0',
			'newSound'			=> $data['new' . $data['sort']],
			'errorSound'		=> $data['error'],
			'delSound'			=> $data['del'],
			'addSound'			=> $data['add'],
			'editSound'			=> $data['edit'],
			'titleUrl'			=> $data['homepage'],
			'shoutImg'			=> ($data['is_mobile']) ? '' : $this->work->shout_url($this->ext_path_web) . $data['style'] . $this->config['shout_div_img' . $data['sort_p']],
			'shoutImgHori'		=> $this->config['shout_img_horizontal' . $data['sort_p']],
			'shoutImgVert'		=> $this->config['shout_img_vertical' . $data['sort_p']],
			'buttonBg'			=> ' button_background_' . $this->config['shout_color_background' . $data['sort_p']],
			'shoutHeight'		=> $this->config['shout_height' . $data['sort_p']],
			'widthPost'			=> $this->config['shout_width_post' . $data['sort_p']],
			'popupWidth'		=> $this->config['shout_popup_width'],
			'popupHeight'		=> $this->config['shout_popup_height'],
			'direction'			=> $this->language->lang('SHOUT_DIRECTION'),
			'base'				=> generate_board_url(),
			'popupUrl'			=> $this->work->remove_sid($this->helper->route('sylver35_breizhshoutbox_popup')),
			'configUrl'			=> $this->work->remove_sid($this->helper->route('sylver35_breizhshoutbox_configshout')) . '?user_id=' . $data['user_id'],
			'checkUrl'			=> $this->work->remove_sid($this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'check'])),
			'viewUrl'			=> $this->work->remove_sid($this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'view'])),
			'postUrl'			=> $this->work->remove_sid($this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'post'])),
			'smilUrl'			=> $this->work->remove_sid($this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'smilies'])),
			'smilPopUrl'		=> $this->work->remove_sid($this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'smilies_popup'])),
			'onlineUrl'			=> $this->work->remove_sid($this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'online'])),
			'soundUrl'			=> $this->work->remove_sid($this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'action_sound'])),
			'rulesUrl'			=> $this->work->remove_sid($this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'rules'])),
			'postingUrl'		=> $this->work->remove_sid($this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'posting'])),
			'questionUrl'		=> $this->work->remove_sid($this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'question'])),
			'creatorUrl'		=> ($data['creator']) ? $this->work->remove_sid($this->helper->route('sylver35_smilecreator_controller')) : '',
		];
		if ($data['is_user'])
		{
			$list_string = array_merge($list_string, [
				'privUrl'		=> $this->work->remove_sid($this->helper->route('sylver35_breizhshoutbox_private')),
				'purgeUrl'		=> $this->work->remove_sid($this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'purge'])),
				'purgeBotUrl'	=> $this->work->remove_sid($this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'purge_robot'])),
				'actUrl'		=> $this->work->remove_sid($this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'action_user'])),
				'actPostUrl'	=> $this->work->remove_sid($this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'action_post'])),
				'actDelUrl'		=> $this->work->remove_sid($this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'action_del'])),
				'actDelToUrl'	=> $this->work->remove_sid($this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'action_del_to'])),
				'actRemoveUrl'	=> $this->work->remove_sid($this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'action_remove'])),
				'citeUrl'		=> $this->work->remove_sid($this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'cite'])),
				'ubbcodeUrl'	=> $this->work->remove_sid($this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'user_bbcode'])),
				'persoUrl'		=> $this->work->remove_sid($this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'charge_bbcode'])),
				'deleteUrl'		=> $this->work->remove_sid($this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'delete'])),
				'editUrl'		=> $this->work->remove_sid($this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'edit'])),
				'dateUrl'		=> $this->work->remove_sid($this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'date_format'])),
				'authUrl'		=> $this->work->remove_sid($this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'auth'])),
			]);
		}

		return $list_string;
	}

	private function lang_to_javascript($data)
	{
		$this->config['shout_title'] = (!$this->config['shout_title']) ? $this->language->lang('SHOUT_START') : $this->config['shout_title'];
		$this->config['shout_title_priv'] = (!$this->config['shout_title_priv']) ? $this->language->lang('SHOUTBOX_SECRET') : $this->config['shout_title_priv'];

		// Initialise list_lang for all users
		$list_lang = [
			'DATETIME_0'	=> $this->language->lang(['datetime', 'AGO', 0]),
			'DATETIME_1'	=> $this->language->lang(['datetime', 'AGO', 1]),
			'DATETIME_2'	=> $this->language->lang(['datetime', 'AGO', 2]),
			'DATETIME_3'	=> $this->language->lang(['datetime', 'TODAY']),
			'PRINT_VER'		=> $this->language->lang('SHOUTBOX_VER', $data['version']),
			'TITLE'			=> $this->config['shout_title' . $data['sort']],
		];

		// Initialise lang_array for all users
		$lang_array = ['SHOUT_LOADING', 'SERVER_ERR', 'JS_ERR', 'ERROR', 'LINE', 'FILE', 'POST_DETAILS', 'SHOUT_MESSAGE', 'SHOUT_MESSAGES', 'COMMA_SEPARATOR', 'SHOUT_SEP', 'MSG_DEL_DONE', 'SHOUT_NO_MESSAGE', 'SHOUT_PAGE', 'NO_SHOUT_EDIT', 'CANCEL', 'NEXT', 'PREVIOUS', 'SHOUT_AUTO', 'SHOUT_DIV_BBCODE_CLOSE', 'SHOUT_ACTION_MSG', 'SHOUT_OUT_TIME', 'NO_SHOUT_DEL', 'NO_SHOW_IP_PERM', 'SHOUT_CLICK_SOUND_ON', 'SHOUT_CLICK_SOUND_OFF', 'MESSAGE_EMPTY', 'SHOUT_DIV_CLOSE', 'NO_POST_PERM', 'NO_SHOUT_POP', 'POST_MESSAGE', 'POST_MESSAGE_ALT', 'POSTED', 'SHOUT_POP', 'SHOUT_ONLINE', 'SHOUT_ONLINE_CLOSE', 'SHOUT_COLOR', 'NO_SHOUT_COLOR', 'SHOUT_COLOR_CLOSE', 'SMILIES', 'NO_SMILIES', 'SMILIES_CLOSE', 'SHOUT_CHARS', 'SHOUT_CHARS_CLOSE', 'NO_SHOUT_CHARS', 'SHOUT_RULES', 'SHOUT_RULES_PRIV', 'SHOUT_RULES_CLOSE', 'SHOUT_MORE_SMILIES', 'SHOUT_MORE_SMILIES_ALT', 'SHOUT_LESS_SMILIES', 'SHOUT_LESS_SMILIES_ALT', 'SHOUT_TOO_BIG', 'SHOUT_TOO_BIG2', 'SHOUT_ACTION_CITE_M', 'SHOUT_ACTION_CITE_ON', 'SHOUT_CLOSE', 'SHOUT_BBCODES', 'SHOUT_BBCODES_CLOSE', 'NO_SHOUT_BBCODE', 'SENDING', 'SHOUT_ROBOT_ON', 'SHOUT_ROBOT_OFF', 'SHOUT_COOKIES'];

		if ($data['guest'])
		{
			$lang_array = array_merge($lang_array, ['SHOUT_CLICK_HERE', 'SHOUT_CHOICE_NAME', 'SHOUT_CHOICE_YES', 'SHOUT_AFFICHE', 'SHOUT_CACHE', 'SHOUT_CHOICE_NAME_ERROR']);
			$list_lang['USERNAME_EXPLAIN'] = $this->language->lang($this->config['allow_name_chars'] . '_EXPLAIN', $this->language->lang('CHARACTERS', (int) $this->config['min_name_chars']), $this->language->lang('CHARACTERS', (int) $this->config['max_name_chars']));
		}
		else if ($data['is_user'])
		{
			$lang_array = array_merge($lang_array, ['SHOUT_PERSO', 'SENDING_EDIT', 'EDIT_DONE', 'EDIT_BUTTON', 'SHOUT_DEL', 'DEL_SHOUT', 'SHOUT_IP', 'SHOUT_POST_IP', 'ONLY_ONE_OPEN', 'SHOUT_EDIT', 'SHOUT_PRIV', 'SHOUT_CONFIG_OPEN', 'SHOUT_USER_IGNORE', 'SHOUT_PURGE_ROBOT_ALT', 'SHOUT_PURGE_ROBOT_BOX', 'SHOUT_PURGE_ALT', 'SHOUT_PURGE_BOX', 'PURGE_PROCESS']);
			$list_lang['MSG_ROBOT'] = $this->language->lang('SHOUT_ACTION_MSG_ROBOT', $this->work->construct_action_shout(0));
		}

		for ($i = 0, $nb = sizeof($lang_array); $i < $nb; $i++)
		{
			$list_lang[strtr($lang_array[$i], ['SHOUT_' => ''])] = $this->language->lang($lang_array[$i]);
		}

		if ($data['category'])
		{
			$list_lang = array_merge($list_lang, [
				'CATEGORY'				=> $this->language->lang('SC_CATEGORY'),
				'SMILIES_PAGE'			=> $this->language->lang('SC_SMILIES_PAGE'),
				'SMILIES_PAGE_TITLE'	=> $this->language->lang('SC_SMILIES_PAGE_TITLE'),
			]);
		}

		if ($data['creator'])
		{
			$this->language->add_lang('smilie_creator', 'sylver35/smilecreator');
			$list_lang['CREATOR'] = $this->language->lang('SMILIE_CREATOR');
		}

		return $list_lang;
	}
}
