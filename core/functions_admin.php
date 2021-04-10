<?php
/**
*
* @package Breizh Shoutbox Extension
* @copyright (c) 2018-2021 Sylver35  https://breizhcode.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace sylver35\breizhshoutbox\core;
use sylver35\breizhshoutbox\core\shoutbox;
use phpbb\json_response;
use phpbb\exception\http_exception;
use phpbb\cache\driver\driver_interface as cache;
use phpbb\config\config;
use phpbb\db\driver\driver_interface as db;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\extension\manager;

class functions_admin
{
	/* @var \sylver35\breizhshoutbox\core\shoutbox */
	protected $shoutbox;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log */
	protected $log;

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
	public function __construct(shoutbox $shoutbox, cache $cache, config $config, db $db, request $request, template $template, user $user, language $language, log $log, manager $ext_manager, $root_path, $shoutbox_table, $shoutbox_priv_table, $shoutbox_rules_table)
	{
		$this->shoutbox = $shoutbox;
		$this->cache = $cache;
		$this->config = $config;
		$this->db = $db;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->language = $language;
		$this->log = $log;
		$this->ext_manager = $ext_manager;
		$this->root_path = $root_path;
		$this->shoutbox_table = $shoutbox_table;
		$this->shoutbox_priv_table = $shoutbox_priv_table;
		$this->shoutbox_rules_table = $shoutbox_rules_table;
		$this->ext_path = $this->ext_manager->get_extension_path('sylver35/breizhshoutbox', true);
	}

	/*
	 * Build radio input with specific lang
	 */
	public function construct_radio($name, $sort = 1, $outline = false, $on1 = '', $on2 = '')
	{
		$title1 = $title2 = '';
		switch ($sort)
		{
			case 1:
				$title1 = 'YES';
				$title2 = 'NO';
			break;
			case 2:
				$title1 = 'ENABLE';
				$title2 = 'DISABLE';
			break;
			case 3:
				$title1 = $on1;
				$title2 = $on2;
			break;
		}
		$title1 = $this->language->lang($title1);
		$title2 = $this->language->lang($title2);

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

	public function build_adm_sound_select($sort)
	{
		$actual = $this->config['shout_sound_' . $sort];
		$soundlist = $this->shoutbox->filelist_all($this->ext_path, 'sounds/', 'mp3');
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

	public function build_select_horizontal($value)
	{
		$sel_center = ($this->config[$value] == 'center') ? ' selected="selected"' : '';
		$sel_right = ($this->config[$value] == 'right') ? ' selected="selected"' : '';

		$select = '<option title="' . $this->language->lang('SHOUT_DIV_CENTER') . '" value="center"' . $sel_center . '>' . $this->language->lang('SHOUT_DIV_CENTER') . '</option>';
		$select .= '<option title="' . $this->language->lang('SHOUT_DIV_RIGHT') . '" value="right"' . $sel_right . '>' . $this->language->lang('SHOUT_DIV_RIGHT') . '</option>';
		
		return $select;
	}

	public function build_select_vertical($value)
	{
		$sel_top = ($this->config[$value] == 'top') ? ' selected="selected"' : '';
		$sel_center = ($this->config[$value] == 'center') ? ' selected="selected"' : '';
		$sel_bottom = ($this->config[$value] == 'bottom') ? ' selected="selected"' : '';

		$select = '<option title="' . $this->language->lang('SHOUT_DIV_TOP') . '" value="top"' . $sel_top . '>' . $this->language->lang('SHOUT_DIV_TOP') . '</option>';
		$select .= '<option title="' . $this->language->lang('SHOUT_DIV_CENTER') . '" value="center"' . $sel_center . '>' . $this->language->lang('SHOUT_DIV_CENTER') . '</option>';
		$select .= '<option title="' . $this->language->lang('SHOUT_DIV_BOTTOM') . '" value="bottom"' . $sel_bottom . '>' . $this->language->lang('SHOUT_DIV_BOTTOM') . '</option>';
		
		return $select;
	}

	public function build_select_img($rootdir, $path, $config, $panel = false, $type = '')
	{
		$select = '';
		$imglist = $this->shoutbox->filelist_all($rootdir, $path, $type, true);
		foreach ($imglist as $key => $image)
		{
			natcasesort($image);
			foreach ($image as $img)
			{
				$on_img = $img;
				$img = substr($img, 0, strrpos($img, '.'));
				$value = $panel ? $on_img : $img;
				$selected = ($this->config[$config] == $value) ? ' selected="selected"' : '';
				$select .= '<option title="' . $on_img . '" value="' . $value . '"' . $selected . '>' . $img . "</option>\n";
			}
		}

		return $select;
	}

	public function build_select_background($rootdir, $path, $config)
	{
		$select = (!$this->config[$config]) ? ' selected="selected"' : '';
		$select = '<option title="" value=""' . $select . '>' . $this->language->lang('SHOUT_DIV_NONE') . "</option>\n";
		$imglist = $this->shoutbox->filelist_all($rootdir, $path, '', true);
		foreach ($imglist as $key => $image)
		{
			natcasesort($image);
			foreach ($image as $img)
			{
				$selected = ($this->config[$config] == $img) ? ' selected="selected"' : '';
				$select .= '<option title="' . $img . '" value="' . $img . '"' . $selected . '>' . $img . "</option>\n";
			}
		}

		return $select;
	}

	public function purge_all_shout_admin($priv)
	{
		if ($priv)
		{
			$val_priv = '_priv';
			$val_priv_on = '_PRIV';
			$shoutbox_table = $this->shoutbox_priv_table;
		}
		else
		{
			$val_priv = $val_priv_on = '';
			$shoutbox_table = $this->shoutbox_table;
		}

		$sql = 'SELECT COUNT(shout_id) as total
			FROM ' . $shoutbox_table;
		$result = $this->db->sql_query($sql);
		$deleted = $this->db->sql_fetchfield('total', $result);
		$this->db->sql_freeresult($result);

		$this->db->sql_query('TRUNCATE ' . $shoutbox_table);

		$this->config->increment('shout_del_purge' . $val_priv, $deleted, true);
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PURGE_SHOUTBOX' . $val_priv_on, time());
		$this->shoutbox->post_robot_shout(0, $this->user->ip, $priv, true, false);
	}

	public function purge_shout_admin($sort, $priv)
	{
		$shout_info = [];
		$sort = (int) str_replace('purge_', '', $sort);
		if ($priv)
		{
			$val_priv = '_priv';
			$val_priv_on = '_PRIV';
			$shoutbox_table = $this->shoutbox_priv_table;
		}
		else
		{
			$val_priv = $val_priv_on = '';
			$shoutbox_table = $this->shoutbox_table;
		}

		switch ($sort)
		{
			case 1:
				$shout_info = [1, 2, 3, 4];
			break;
			case 2:
				$shout_info = [4, 14, 15, 16, 60];
			break;
			case 3:
				$shout_info = [4, 17, 18, 19, 20, 21, 70, 71, 72, 73, 74, 75, 76, 77, 80];
			break;
			case 4:
				$shout_info = [4, 12];
			break;
			case 5:
				$shout_info = [4, 11];
			break;
			case 6:
				$shout_info = [4, 13];
			break;
		}

		$this->db->sql_query('DELETE FROM ' . $shoutbox_table . ' WHERE ' . $this->db->sql_in_set('shout_info', $shout_info, false, true));
		$deleted = $this->db->sql_affectedrows();

		if ($deleted)
		{
			$this->config->increment('shout_del_purge' . $val_priv, $deleted, true);
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, "LOG_PURGE_SHOUTBOX{$val_priv_on}_ROBOT", time(), [$deleted]);
			$this->shoutbox->post_robot_shout(0, $this->user->ip, $priv, true, true);
		}

		return $deleted;
	}

	public function get_group_options()
	{
		$group_options = '';
		$sql = $this->db->sql_build_query('SELECT', [
			'SELECT'	=> 'DISTINCT group_type, group_name, group_id, group_colour',
			'FROM'		=> [GROUPS_TABLE => ''],
			'WHERE'		=> $this->db->sql_in_set('group_name', ['GUESTS', 'BOTS'], true),
			'ORDER_BY'	=> 'group_type DESC, group_name ASC',
		]);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$selected = (in_array($row['group_id'], explode(', ', $this->config['shout_birthday_exclude']))) ? ' selected="selected"' : '';
			$group_options .= '<option' . (($row['group_type'] == GROUP_SPECIAL) ? ' class="sep"' : '') . ' value="' . $row['group_id'] . '" style="color:#' . $row['group_colour'] . ';font-weight:bold;"' . $selected . '>' . (($row['group_type'] == GROUP_SPECIAL) ? $this->language->lang('G_' . $row['group_name']) : $row['group_name']) . "</option>\n";
		}
		$this->db->sql_freeresult($result);

		return $group_options;
	}

	public function get_another_options()
	{
		for ($i = 1; $i < 8; $i++)
		{
			$this->template->assign_vars([
				'CHOICE_' . $i		=> preg_match('/' . $i . '/i', $this->config['shout_robot_choice']) ? ' checked="checked"' : '',
				'CHOICE_PRIV_' . $i	=> preg_match('/' . $i . '/i', $this->config['shout_robot_choice_priv']) ? ' checked="checked"' : '',
			]);
		}

		if ($this->shoutbox->breizhyoutube_exist())
		{
			$this->template->assign_vars([
				'SHOUT_ENABLE_YOUTUBE'	=> true,
				'SHOUT_VIDEO_NEW'		=> $this->construct_radio('shout_video_new', 2),
				'IMAGE_VIDEO'			=> $this->ext_path . 'images/panel/ecran.webp',
			]);
		}

		if ($this->shoutbox->relaxarcade_exist())
		{
			$this->template->assign_vars([
				'SHOUT_ENABLE_ROBOT_RA'	=> true,
				'SHOUT_NEW_SCORE'		=> $this->construct_radio('shout_arcade_new', 2),
				'SHOUT_NEW_RECORD'		=> $this->construct_radio('shout_arcade_record', 2),
				'SHOUT_NEW_URECORD'		=> $this->construct_radio('shout_arcade_urecord', 2),
			]);
		}
	}

	public function update_rules()
	{
		$sql = $this->db->sql_build_query('SELECT', [
			'SELECT'	=> 'l.lang_iso, r.rules_lang',
			'FROM'		=> [LANG_TABLE => 'l'],
			'LEFT_JOIN'	=> [
				[
					'FROM'	=> [$this->shoutbox_rules_table => 'r'],
					'ON'	=> 'r.rules_lang = l.lang_iso',
				],
			],
		]);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$iso = $row['lang_iso'];
			$rules_flags = $rules_flags_priv = 0;
			$rules_uid = $rules_bitfield = $rules_uid_priv = $rules_bitfield_priv = '';
			$rules_text = $this->request->variable('rules_text_' . $iso, '', true);
			$rules_text_priv = $this->request->variable('rules_text_priv_' . $iso, '', true);
			generate_text_for_storage($rules_text, $rules_uid, $rules_bitfield, $rules_flags, true, true, true);
			generate_text_for_storage($rules_text_priv, $rules_uid_priv, $rules_bitfield_priv, $rules_flags_priv, true, true, true);

			$data = [
				'rules_lang'			=> $iso,
				'rules_text'			=> $rules_text,
				'rules_bitfield'		=> $rules_bitfield,
				'rules_uid'				=> $rules_uid,
				'rules_flags'			=> $rules_flags,
				'rules_text_priv'		=> $rules_text_priv,
				'rules_bitfield_priv'	=> $rules_bitfield_priv,
				'rules_uid_priv'		=> $rules_uid_priv,
				'rules_flags_priv'		=> $rules_flags_priv,
			];

			if (isset($row['rules_lang']) && $row['rules_lang'])
			{
				$this->db->sql_query('UPDATE ' . $this->shoutbox_rules_table . ' SET ' .  $this->db->sql_build_array('UPDATE', $data) . " WHERE rules_lang = '$iso'");;
			}
			else
			{
				$this->db->sql_query('INSERT INTO ' . $this->shoutbox_rules_table . ' ' . $this->db->sql_build_array('INSERT', $data));
			}

			$this->update_config([
				"shout_rules_{$iso}"		=> ($data['rules_text'] !== '') ? 1 : 0,
				"shout_rules_priv_{$iso}"	=> ($data['rules_text_priv'] !== '') ? 1 : 0,
			]);
		}
		$this->db->sql_freeresult($result);
		$this->cache->destroy('_shout_rules');
	}

	public function get_shout_smilies()
	{
		$sql = $this->db->sql_build_query('SELECT', [
			'SELECT'	=> 'MIN(smiley_id) AS smiley_id, MIN(code) AS code, smiley_url,  MIN(smiley_order) AS min_smiley_order, MIN(smiley_width) AS smiley_width, MIN(smiley_height) AS smiley_height, MIN(emotion) AS emotion, MIN(display_on_shout) AS display_on_shout',
			'FROM'		=> [SMILIES_TABLE => ''],
			'WHERE'		=> 'display_on_shout = 1',
			'GROUP_BY'	=> 'smiley_url',
			'ORDER_BY'	=> 'min_smiley_order ASC',
		]);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('smilies', [
				'SRC'		=> $this->root_path . $this->config['smilies_path'] . '/' . $row['smiley_url'],
				'ID'		=> $row['smiley_id'],
				'CODE'		=> addslashes($row['code']),
				'EMOTION'	=> $row['emotion'],
				'WIDTH'		=> $row['smiley_width'],
				'HEIGHT'	=> $row['smiley_height'],
			]);
		}
		$this->db->sql_freeresult($result);
	}

	public function get_messages($start, $shout_number, $sort)
	{
		$i = 0;
		$shoutbox_table = ($sort) ? $this->shoutbox_table : $this->shoutbox_priv_table;
		$sql_nr = 'SELECT COUNT(DISTINCT shout_id) as total
			FROM ' . $shoutbox_table . '
			WHERE shout_inp = 0
				OR shout_inp = ' . $this->user->data['user_id'] . '
				OR shout_user_id = ' . $this->user->data['user_id'];
		$result_nr = $this->db->sql_query($sql_nr);
		$total_posts = $this->db->sql_fetchfield('total', $result_nr);
		$this->db->sql_freeresult($result_nr);

		$sql = $this->db->sql_build_query('SELECT', [
			'SELECT'	=> 's.*, u.user_id, u.username, u.user_colour, v.user_id as x_user_id, v.username as x_username, v.user_colour as x_user_colour',
			'FROM'		=> [$shoutbox_table => 's'],
			'LEFT_JOIN'	=> [
				[
					'FROM'	=> [USERS_TABLE => 'u'],
					'ON'	=> 's.shout_user_id = u.user_id',
				],
				[
					'FROM'	=> [USERS_TABLE => 'v'],
					'ON'	=> 'v.user_id = s.shout_robot_user',
				],
			],
			'WHERE'		=> 'shout_inp = 0 OR shout_inp = ' . $this->user->data['user_id'] . ' OR shout_user_id = ' . $this->user->data['user_id'],
			'ORDER_BY'	=> 's.shout_time DESC',
		]);
		$result = $this->db->sql_query_limit($sql, $shout_number, $start);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['shout_inp'] && ($row['shout_inp'] != $this->user->data['user_id']) && ($row['shout_user_id'] != $this->user->data['user_id']))
			{
				continue;
			}
			$row['username'] = ($row['shout_user_id'] == ANONYMOUS) ? $row['shout_text2'] : $row['username'];
			$row['shout_text'] = $this->shoutbox->shout_text_for_display($row, 3, true);

			$this->template->assign_block_vars('messages', [
				'TIME'				=> $this->user->format_date($row['shout_time']),
				'POSTER'			=> $this->shoutbox->construct_action_shout($row['shout_user_id'], $row['username'], $row['user_colour'], true),
				'ID'				=> $row['shout_id'],
				'MESSAGE'			=> $row['shout_text'],
				'ROW_NUMBER'		=> $i + ($start + 1),
			]);
			$i++;
		}
		$this->db->sql_freeresult($result);

		return [
			'i'				=> $i,
			'total_posts'	=> $total_posts,
		];
	}
	
	public function get_logs($sort)
	{
		$li = $start_log = 0;
		$log_array = (!$sort) ? ['LOG_SHOUT_SCRIPT', 'LOG_SHOUT_ACTIVEX', 'LOG_SHOUT_APPLET', 'LOG_SHOUT_OBJECTS', 'LOG_SHOUT_IFRAME'] : ['LOG_SHOUT_SCRIPT_PRIV', 'LOG_SHOUT_ACTIVEX_PRIV', 'LOG_SHOUT_APPLET_PRIV', 'LOG_SHOUT_OBJECTS_PRIV', 'LOG_SHOUT_IFRAME_PRIV'];

		$sql = $this->db->sql_build_query('SELECT', [
			'SELECT'	=> 'l.log_id, l.user_id, l.log_type, l.log_ip, l.log_time, l.log_operation, l.reportee_id, u.user_id, u.username, u.user_colour',
			'FROM'		=> [LOG_TABLE => 'l'],
			'LEFT_JOIN'	=> [
				[
					'FROM'	=> [USERS_TABLE => 'u'],
					'ON'	=> 'l.user_id = u.user_id',
				],
			],
			'WHERE'		=> $this->db->sql_in_set('log_operation', $log_array),
			'ORDER_BY'	=> 'log_time DESC',
		]);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['username'] = ($row['user_id'] == ANONYMOUS) ? $this->language->lang('GUEST') : $row['username'];
			$this->template->assign_block_vars('logs', [
				'TIME'				=> $this->user->format_date($row['log_time']),
				'REPORTEE'			=> $this->shoutbox->construct_action_shout($row['user_id'], $row['username'], $row['user_colour'], true),
				'OPERATION'			=> $this->language->lang($row['log_operation']),
				'ID'				=> $row['log_id'],
				'IP'				=> $row['log_ip'],
				'ROW_NUMBER'		=> $li + ($start_log + 1),
			]);
			$li++;
		}
		$this->db->sql_freeresult($result);

		return $li;
	}

	public function action_delete_mark($form_key, $deletemark, $sort, $u_action)
	{
		if (!check_form_key($form_key))
		{
			trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($u_action), E_USER_WARNING);
		}

		$marked = $this->request->variable('mark', [0]);
		$priv = $private = $where_sql = '';
		$shoutbox_table = $this->shoutbox_table;
		if ($sort)
		{
			$priv = '_priv';
			$private = '_PRIV';
			$shoutbox_table = $this->shoutbox_priv_table;
		}

		if ($deletemark && sizeof($marked))
		{
			$sql_in = [];
			foreach ($marked as $mark)
			{
				$sql_in[] = $mark;
			}
			$where_sql = ' WHERE ' . $this->db->sql_in_set('shout_id', $sql_in);
			unset($sql_in);
		}

		if ($where_sql)
		{
			$this->db->sql_query('DELETE FROM ' . $shoutbox_table . $where_sql);
			$deleted = $this->db->sql_affectedrows();
			// Reload the shoutbox for all
			$this->shoutbox->update_shout_messages($shoutbox_table);

			$message = $this->shoutbox->plural('LOG_SELECT', $deleted, '_SHOUTBOX' . $private);
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, $message, time(), [$deleted]);
			$this->config->increment('shout_del_acp' . $priv, $deleted, true);
			trigger_error($this->language->lang($message, $deleted) . adm_back_link($u_action));
		}
	}

	public function action_delete_marklog($form_key, $deletemarklog, $sort, $u_action)
	{
		if (!check_form_key($form_key))
		{
			trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($u_action), E_USER_WARNING);
		}

		$where_sql = '';
		$private = ($sort) ? '_PRIV' : '';
		$marked = $this->request->variable('mark', [0]);
		if ($deletemarklog && sizeof($marked))
		{
			$sql_in = [];
			foreach ($marked as $mark)
			{
				$sql_in[] = $mark;
			}
			$where_sql = ' WHERE ' . $this->db->sql_in_set('log_id', $sql_in);
			unset($sql_in);
		}
		if ($where_sql)
		{
			$this->db->sql_query('DELETE FROM ' . LOG_TABLE . $where_sql);
			$deleted = $this->db->sql_affectedrows();

			$message = $this->shoutbox->plural('LOG_LOG', $deleted, '_SHOUTBOX' . $private);
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, $message, time(), [$deleted]);
			trigger_error($this->language->lang($message, $deleted) . adm_back_link($u_action));
		}
	}

	public function action_purge_shoutbox($form_key, $action, $sort, $u_action)
	{
		if (!check_form_key($form_key))
		{
			trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($u_action), E_USER_WARNING);
		}

		$private = ($sort) ? '_PRIV' : '';
		if ($action == 'purge')
		{
			$this->purge_all_shout_admin($sort);
			trigger_error($this->language->lang('LOG_PURGE_SHOUTBOX' . $private) . adm_back_link($u_action));
		}
		else
		{
			$deleted = $this->purge_shout_admin($action, $sort);
			trigger_error($this->language->lang("LOG_PURGE_SHOUTBOX{$private}_ROBOT", $deleted) . adm_back_link($u_action));
		}
	}

	public function update_config($data)
	{
		foreach ($data as $key => $value)
		{
			$this->config->set($key, $value);
		}
	}
}
