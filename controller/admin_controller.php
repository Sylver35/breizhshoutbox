<?php
/**
*
* @package Breizh Shoutbox Extension
* @copyright (c) 2018-2020 Sylver35  https://breizhcode.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace sylver35\breizhshoutbox\controller;
use sylver35\breizhshoutbox\core\shoutbox;
use sylver35\breizhshoutbox\core\functions_admin;
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\extension\manager;
use phpbb\db\driver\driver_interface as db;
use phpbb\pagination;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use phpbb\language\language;
use phpbb\log\log;

class admin_controller
{
	/* @var \sylver35\breizhshoutbox\core\shoutbox */
	protected $shoutbox;

	/* @var \sylver35\breizhshoutbox\core\functions_admin */
	protected $functions_admin;

	/** @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\extension\manager */
	protected $ext_manager;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\pagination */
	protected $pagination;

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

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpEx */
	protected $php_ext;

	/** @var string ext path */
	protected $ext_path;

	/** @var string Custom form action */
	protected $u_action;

	/**
	 * The database tables
	 *
	 * @var string */
	protected $shoutbox_rules_table;

	/**
	 * Constructor
	 */
	public function __construct(shoutbox $shoutbox, functions_admin $functions_admin, config $config, helper $helper, manager $ext_manager, db $db, pagination $pagination, request $request, template $template, user $user, language $language, log $log, $root_path, $php_ext, $shoutbox_rules_table)
	{
		$this->shoutbox = $shoutbox;
		$this->functions_admin = $functions_admin;
		$this->config = $config;
		$this->helper = $helper;
		$this->ext_manager = $ext_manager;
		$this->db = $db;
		$this->pagination = $pagination;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->language = $language;
		$this->log = $log;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		$this->shoutbox_rules_table = $shoutbox_rules_table;
		$this->ext_path = $this->ext_manager->get_extension_path('sylver35/breizhshoutbox', true);
	}

	public function acp_shoutbox_configs()
	{
		$this->language->add_lang('acp/board');
		$mode = $this->request->variable('mode', '');
		$form_key = 'sylver35/breizhshoutbox';
		add_form_key($form_key);
		if ($this->request->is_set_post('update'))
		{
			if (!check_form_key($form_key))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$this->functions_admin->update_config([
				'shout_temp_users'			=> $this->request->variable('shout_temp_users', 5),
				'shout_temp_anonymous'		=> $this->request->variable('shout_temp_anonymous', 10),
				'shout_inactiv_anony'		=> $this->request->variable('shout_inactiv_anony', 15),
				'shout_inactiv_member'		=> $this->request->variable('shout_inactiv_member', 30),
				'shout_dateformat'			=> $this->request->variable('shout_dateformat2', '', true),
				'shout_bbcode'				=> $this->request->variable('shout_bbcode', ''),
				'shout_bbcode_user'			=> $this->request->variable('shout_bbcode_user', ''),
				'shout_bbcode_size'			=> $this->request->variable('shout_bbcode_size', ''),
				'shout_see_buttons'			=> $this->request->variable('shout_see_buttons', 1),
				'shout_see_buttons_left'	=> $this->request->variable('shout_see_buttons_left', 1),
				'shout_see_button_ip'		=> $this->request->variable('shout_see_button_ip', 1),
				'shout_see_cite'			=> $this->request->variable('shout_see_cite', 1),
				'shout_avatar'				=> $this->request->variable('shout_avatar', 1),
				'shout_avatar_height'		=> $this->request->variable('shout_avatar_height', 20),
				'shout_avatar_robot'		=> $this->request->variable('shout_avatar_robot', 1),
				'shout_avatar_user'			=> $this->request->variable('shout_avatar_user', 1),
				'shout_avatar_img'			=> $this->request->variable('shout_avatar_img', 'no_avatar.webp'),
				'shout_avatar_img_robot'	=> $this->request->variable('shout_avatar_img_robot', 'avatar_robot.webp'),
				'shout_sound_on'			=> $this->request->variable('shout_sound_on', 1),
				'shout_sound_new'			=> $this->request->variable('shout_sound_new', ''),
				'shout_sound_error'			=> $this->request->variable('shout_sound_error', ''),
				'shout_sound_del'			=> $this->request->variable('shout_sound_del', ''),
				'shout_sound_add'			=> $this->request->variable('shout_sound_add', ''),
				'shout_sound_edit'			=> $this->request->variable('shout_sound_edit', ''),
				'shout_nr_acp'				=> $this->request->variable('shout_nr_acp', 20),
				'shout_max_post_chars'		=> $this->request->variable('shout_max_post_chars', 300),
				'shout_index'				=> $this->request->variable('shout_index', 1),
				'shout_position_index'		=> $this->request->variable('shout_position_index', 0),
				'shout_forum'				=> $this->request->variable('shout_forum', 1),
				'shout_position_forum'		=> $this->request->variable('shout_position_forum', 0),
				'shout_topic'				=> $this->request->variable('shout_topic', 1),
				'shout_position_topic'		=> $this->request->variable('shout_position_topic', 0),
			]);

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_SHOUT_' . strtoupper($mode));
			trigger_error($this->language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
		}
		else
		{
			$this->template->assign_vars([
				'SHOUT_TEMP_USERS'			=> (int) $this->config['shout_temp_users'],
				'SHOUT_TEMP_ANONYMOUS'		=> (int) $this->config['shout_temp_anonymous'],
				'SHOUT_INACTIV_ANONY'		=> (int) $this->config['shout_inactiv_anony'],
				'SHOUT_INACTIV_MEMBER'		=> (int) $this->config['shout_inactiv_member'],
				'SHOUT_BBCODE_SIZE'			=> (int) $this->config['shout_bbcode_size'],
				'SHOUT_BBCODE'				=> (string) $this->config['shout_bbcode'],
				'SHOUT_BBCODE_USER'			=> (string) $this->config['shout_bbcode_user'],
				'DATEFORMAT_VALUE'			=> (string) $this->config['shout_dateformat'],
				'SHOUT_DATEFORMAT'			=> $this->shoutbox->build_dateformat_option((string) $this->config['shout_dateformat'], true),
				'SHOUT_SEE_BUTTONS'			=> $this->functions_admin->construct_radio('shout_see_buttons', 1),
				'SHOUT_SEE_BUTTONS_LEFT'	=> $this->functions_admin->construct_radio('shout_see_buttons_left', 1),
				'SHOUT_SEE_BUTTON_IP'		=> $this->functions_admin->construct_radio('shout_see_button_ip', 1),
				'SHOUT_SEE_CITE'			=> $this->functions_admin->construct_radio('shout_see_cite', 1),
				'SHOUT_AVATAR'				=> $this->functions_admin->construct_radio('shout_avatar', 2),
				'SHOUT_AVATAR_HEIGHT'		=> (int) $this->config['shout_avatar_height'],
				'SHOUT_AVATAR_ROBOT'		=> $this->functions_admin->construct_radio('shout_avatar_robot', 2),
				'SHOUT_AVATAR_USER'			=> $this->functions_admin->construct_radio('shout_avatar_user', 2),
				'SHOUT_AVATAR_IMG'			=> (string) $this->config['shout_avatar_img'],
				'SHOUT_AVATAR_IMG_BOT'		=> (string) $this->config['shout_avatar_img_robot'],
				'SHOUT_AVATAR_IMG_SRC'		=> $this->ext_path . 'images/' . $this->config['shout_avatar_img'],
				'SHOUT_AVATAR_IMG_BOT_SRC'	=> $this->ext_path . 'images/' . $this->config['shout_avatar_img_robot'],
				'SHOUT_SOUND_ON'			=> $this->functions_admin->construct_radio('shout_sound_on', 2),
				'SHOUT_NR_ACP'				=> (int) $this->config['shout_nr_acp'],
				'SHOUT_MAX_POST_CHARS'		=> (int) $this->config['shout_max_post_chars'],
				'SHOUT_INDEX_ON'			=> $this->functions_admin->construct_radio('shout_index', 2),
				'POS_SHOUT_INDEX'			=> $this->shoutbox->build_select_position($this->config['shout_position_index'], true),
				'SHOUT_FORUM_ON'			=> $this->functions_admin->construct_radio('shout_forum', 2),
				'POS_SHOUT_FORUM'			=> $this->shoutbox->build_select_position($this->config['shout_position_forum'], false),
				'SHOUT_TOPIC_ON'			=> $this->functions_admin->construct_radio('shout_topic', 2),
				'POS_SHOUT_TOPIC'			=> $this->shoutbox->build_select_position($this->config['shout_position_topic'], false),
				'NEW_SOUND'					=> $this->functions_admin->build_adm_sound_select('new'),
				'ERROR_SOUND'				=> $this->functions_admin->build_adm_sound_select('error'),
				'DEL_SOUND'					=> $this->functions_admin->build_adm_sound_select('del'),
				'ADD_SOUND'					=> $this->functions_admin->build_adm_sound_select('add'),
				'EDIT_SOUND'				=> $this->functions_admin->build_adm_sound_select('edit'),
				'SHOUT_SOUNDS_PATH'			=> $this->ext_path . 'sounds/',
				'SHOUT_IMG_PATH'			=> $this->ext_path . 'images/',
				'U_DATE_FORMAT'				=> $this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'date_format']),
			]);
		}
		$this->template->assign_vars([
			'SHOUT_USER_ID'	=> $this->user->data['user_id'],
		]);
	}

	public function acp_shoutbox_config_gen()
	{
		$mode = $this->request->variable('mode', '');
		$form_key = 'sylver35/breizhshoutbox';
		add_form_key($form_key);
		if ($this->request->is_set_post('update'))
		{
			if (!check_form_key($form_key))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$this->functions_admin->update_config([
				'shout_title'				=> str_replace("'", $this->language->lang('SHOUT_PROTECT'), $this->request->variable('shout_title', 'shoutbox', true)),
				'shout_width_post'			=> $this->request->variable('shout_width_post', 325),
				'shout_prune'				=> $this->request->variable('shout_prune', 0),
				'shout_max_posts_on'		=> $this->request->variable('shout_max_posts_on', 100),
				'shout_max_posts'			=> $this->request->variable('shout_max_posts', 300),
				'shout_on_cron'				=> $this->request->variable('shout_on_cron', 1),
				'shout_log_cron'			=> $this->request->variable('shout_log_cron', 0),
				'shout_num'					=> $this->request->variable('shout_num', 25),
				'shout_height'				=> $this->request->variable('shout_height', 200),
				'shout_div_img'				=> $this->request->variable('shout_div_img', ''),
				'shout_img_horizontal'		=> $this->request->variable('shout_img_horizontal', 'right'),
				'shout_img_vertical'		=> $this->request->variable('shout_img_vertical', 'bottom'),
				'shout_color_background'	=> $this->request->variable('shout_color_background', 'blue'),
				'shout_button_background'	=> $this->request->variable('shout_button_background', 1),
				'shout_bar_option'			=> $this->request->variable('shout_bar_option', 1),
				'shout_defil'				=> $this->request->variable('shout_defil', 1),
			]);

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_SHOUT_' . strtoupper($mode), time());
			trigger_error($this->language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
		}
		else
		{
			$this->template->assign_vars([
				'SHOUT_TITLE'			=> (string) $this->config['shout_title'],
				'SHOUT_WIDTH_POST'		=> (int) $this->config['shout_width_post'],
				'SHOUT_PRUNE'			=> (int) $this->config['shout_prune'],
				'SHOUT_MAX_POSTS'		=> (int) $this->config['shout_max_posts'],
				'SHOUT_MAX_POSTS_ON'	=> (int) $this->config['shout_max_posts_on'],
				'SHOUT_NUM'				=> (int) $this->config['shout_num'],
				'SHOUT_HEIGHT'			=> (int) $this->config['shout_height'],
				'COLOR_IMAGE'			=> (string) $this->config['shout_color_background'] . '.webp',
				'SHOUT_DIV_IMG'			=> (string) $this->config['shout_div_img'],
				'SHOUT_BAR_OPTION'		=> (bool) $this->config['shout_bar_option'],
				'SHOUT_IMG_HORIZONTAL'	=> $this->functions_admin->build_select_horizontal('shout_img_horizontal'),
				'SHOUT_IMG_VERTICAL'	=> $this->functions_admin->build_select_vertical('shout_img_vertical'),
				'SHOUT_ON_CRON'			=> $this->functions_admin->construct_radio('shout_on_cron', 2),
				'SHOUT_LOG_CRON'		=> $this->functions_admin->construct_radio('shout_log_cron', 2),
				'SHOUT_BUTTON'			=> $this->functions_admin->construct_radio('shout_button_background', 1),
				'SHOUT_DEFIL'			=> $this->functions_admin->construct_radio('shout_defil', 3, true, 'SHOUT_DEFIL_TOP', 'SHOUT_DEFIL_BOTTOM'),
				'DIV_IMG_OPTION'		=> $this->functions_admin->build_select_background($this->ext_path, 'styles/all/theme/images/background/', 'shout_div_img'),
				'COLOR_OPTION'			=> $this->functions_admin->build_select_img($this->ext_path, 'styles/all/theme/images/fond/', 'shout_color_background', false, 'webp'),
				'COLOR_PATH'			=> $this->ext_path . 'styles/all/theme/images/fond/',
				'DIV_IMG_PATH'			=> $this->ext_path . 'styles/' . (file_exists($this->ext_path . 'styles/' . rawurlencode($this->user->style['style_path']) . '/theme/images/background/' . $this->config['shout_div_img']) ? rawurlencode($this->user->style['style_path']) : 'all') . '/theme/images/background/',
			]);
		}
	}

	public function acp_shoutbox_rules()
	{
		include($this->root_path . 'includes/functions_posting.' . $this->php_ext);
		include($this->root_path . 'includes/functions_display.' . $this->php_ext);
		$this->language->add_lang('posting');
		$form_key = 'sylver35/breizhshoutbox';
		add_form_key($form_key);

		if ($this->request->is_set_post('update'))
		{
			if (!check_form_key($form_key))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}
			
			$this->functions_admin->update_config([
				'shout_rules'			=> $this->request->variable('shout_rules', 1),
				'shout_rules_open'		=> $this->request->variable('shout_rules_open', 0),
				'shout_rules_open_priv'	=> $this->request->variable('shout_rules_open_priv', 0),
			]);
			$this->functions_admin->update_rules();

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_SHOUT_RULES');
			trigger_error($this->language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
		}
		else
		{
			$i = 1;
			$sql = $this->db->sql_build_query('SELECT', [
				'SELECT'	=> 'l.lang_iso, l.lang_local_name, r.*',
				'FROM'		=> [LANG_TABLE => 'l'],
				'LEFT_JOIN'	=> [
					[
						'FROM'	=> [$this->shoutbox_rules_table => 'r'],
						'ON'	=> 'r.rules_lang = l.lang_iso',
					],
				],
				'ORDER_BY'	=> 'l.lang_id',
			]);
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$decoded_text = censor_text($row['rules_text']);
				decode_message($decoded_text, $row['rules_uid']);
				$decoded_text_priv = censor_text($row['rules_text_priv']);
				decode_message($decoded_text_priv, $row['rules_uid_priv']);

				$this->template->assign_block_vars('rules', [
					'RULES_NR'					=> $i,
					'RULES_TEXT'				=> $decoded_text,
					'RULES_TEXT_PRIV'			=> $decoded_text_priv,
					'RULES_TEXT_DISPLAY'		=> generate_text_for_display($row['rules_text'], $row['rules_uid'], $row['rules_bitfield'], $row['rules_flags']),
					'RULES_TEXT_DISPLAY_PRIV'	=> generate_text_for_display($row['rules_text_priv'], $row['rules_uid_priv'], $row['rules_bitfield_priv'], $row['rules_flags_priv']),
					'RULES_LANG'				=> $row['lang_local_name'],
					'RULES_ISO'					=> $row['lang_iso'],
					'RULES_ON'					=> $this->language->lang('SHOUT_RULES_ON', $row['lang_iso'], $row['lang_local_name']),
					'RULES_ON_EXPLAIN'			=> $this->language->lang('SHOUT_RULES_ON_EXPLAIN', $row['lang_iso'], $row['lang_local_name']),
					'RULES_ON_PRIV_EXPLAIN'		=> $this->language->lang('SHOUT_RULES_ON_PRIV_EXPLAIN', $row['lang_iso'], $row['lang_local_name']),
					'COPY_TO'					=> $this->language->lang('SHOUT_COPY_RULE', $row['lang_iso'], $this->language->lang('SHOUT_NORMAL')),
					'COPY_TO_PRIV'				=> $this->language->lang('SHOUT_COPY_RULE', $row['lang_iso'], $this->language->lang('ACP_SHOUT_PRIVATE_CAT')),
				]);
				$i++;
			}
			$this->db->sql_freeresult($result);

			$this->functions_admin->get_shout_smilies();
			display_custom_bbcodes();

			$this->template->assign_vars([
				'SHOUT_RULES'			=> $this->functions_admin->construct_radio('shout_rules', 2),
				'SHOUT_RULES_OPEN'		=> $this->functions_admin->construct_radio('shout_rules_open', 1),
				'SHOUT_RULES_OPEN_PRIV'	=> $this->functions_admin->construct_radio('shout_rules_open_priv', 1),
				'U_SHOUT_SMILIES'		=> $this->helper->route('sylver35_breizhshoutbox_smilies_pop'),
				'U_PREVIEW_AJAX'		=> $this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'preview_rules']),
				'SHOUT_USER_ID'			=> $this->user->data['user_id'],
			]);
		}
	}

	public function acp_shoutbox_overview()
	{
		$id = $this->request->variable('i', '');
		$action = $this->request->variable('action', '');
		$start = $this->request->variable('start', 0);
		$marked = $this->request->variable('mark', [0]);
		$creation = $this->request->variable('creation_time', 0);
		$token = $this->request->variable('form_token', '');
		$deletemark = $this->request->is_set_post('delmarked') ? true : false;
		$deletemarklog = $this->request->is_set_post('delmarkedlog') ? true : false;
		$mode = $this->request->variable('mode', '');
		$form_key = 'sylver35/breizhshoutbox';
		add_form_key($form_key);

		if ($deletemark)
		{
			if (confirm_box(true))
			{
				$this->functions_admin->action_delete_mark($form_key, $deletemark, false, $this->u_action);
			}
			else
			{
				confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields([
					'start'			=> $start,
					'delmarked'		=> $deletemark,
					'mark'			=> $marked,
					'i'				=> $id,
					'mode'			=> $mode,
					'action'		=> $action,
					'creation_time'	=> $creation,
					'form_token'	=> $token,
				]));
			}
		}
		else if ($deletemarklog)
		{
			if (confirm_box(true))
			{
				$this->functions_admin->action_delete_marklog($form_key, $deletemarklog, false, $this->u_action);
			}
			else
			{
				confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields([
					'start'			=> $start,
					'delmarkedlog'	=> $deletemarklog,
					'mark'			=> $marked,
					'i'				=> $id,
					'mode'			=> $mode,
					'action'		=> $action,
					'creation_time' => $creation,
					'form_token'	=> $token,
				]));
			}
		}
		else if ($action)
		{
			if (confirm_box(true))
			{
				$this->functions_admin->action_purge_shoutbox($form_key, $action, false, $this->u_action);
			}
			else
			{
				confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields([
					'i'				=> $id,
					'mode'			=> $mode,
					'action'		=> $action,
					'creation_time'	=> $creation,
					'form_token'	=> $token,
				]));
			}
		}
		else
		{
			$start = $this->request->variable('start', 0);
			$shout_number = (int) $this->config['shout_nr_acp'];
			$return = $this->functions_admin->get_messages($start, $shout_number, true);
			$li = $this->functions_admin->get_logs(false);
			$total_del = $this->config['shout_del_acp'] + $this->config['shout_del_auto'] + $this->config['shout_del_purge'] + $this->config['shout_del_user'];

			$this->template->assign_vars([
				'S_DISPLAY_MESSAGES'		=> ($return['i'] > 0) ? true : false,
				'S_DISPLAY_LOGS'			=> ($li > 0) ? true : false,
				'S_ON_PAGE'					=> ($return['total_posts'] > $shout_number) ? true : false,
				'TOTAL_POSTS'				=> $return['total_posts'],
				'TOTAL_MESSAGES'			=> $this->language->lang($this->shoutbox->plural('NUMBER_MESSAGE', $return['total_posts']), $return['total_posts']),
				'MESSAGES_TOTAL_NR'			=> $this->language->lang('SHOUT_MESSAGES_TOTAL_NR', $this->config['shout_nr'], $this->user->format_date($this->config['shout_time'])),
				'PAGE_NUMBER' 				=> $this->pagination->validate_start($return['total_posts'], (int) $shout_number, $start),	
				'LAST_SHOUT_RUN'			=> ($this->config['shout_last_run'] == $this->config['shout_time']) ? $this->language->lang('SHOUT_NEVER') : $this->user->format_date($this->config['shout_last_run']),
				'LOGS_TOTAL_NR'				=> $this->language->lang($this->shoutbox->plural('NUMBER_LOG', $this->config['shout_nr_log'], '_TOTAL'), $this->config['shout_nr_log'], $this->user->format_date($this->config['shout_time'])),
				'MESSAGES_DEL_TOTAL'		=> $this->language->lang($this->shoutbox->plural('SHOUT_DEL_NR', $total_del), $total_del) . $this->language->lang('SHOUT_DEL_TOTAL'),
				'MESSAGES_DEL_ACP'			=> $this->language->lang($this->shoutbox->plural('SHOUT_DEL_NR', $this->config['shout_del_acp']), $this->config['shout_del_acp']),
				'MESSAGES_DEL_AUTO'			=> $this->language->lang($this->shoutbox->plural('SHOUT_DEL_NR', $this->config['shout_del_auto']), $this->config['shout_del_auto']),
				'MESSAGES_DEL_PURGE'		=> $this->language->lang($this->shoutbox->plural('SHOUT_DEL_NR', $this->config['shout_del_purge']), $this->config['shout_del_purge']),
				'MESSAGES_DEL_USER'			=> $this->language->lang($this->shoutbox->plural('SHOUT_DEL_NR', $this->config['shout_del_user']), $this->config['shout_del_user']),
			]);
			$this->pagination->generate_template_pagination($this->u_action, 'pagination', 'start', $return['total_posts'], $shout_number, $start);
		}
	}

	public function acp_shoutbox_private()
	{
		$id = $this->request->variable('i', '');
		$action = $this->request->variable('action', '');
		$start = $this->request->variable('start', 0);
		$marked = $this->request->variable('mark', [0]);
		$creation = $this->request->variable('creation_time', 0);
		$token = $this->request->variable('form_token', '');
		$deletemark = $this->request->is_set_post('delmarked') ? true : false;
		$deletemarklog = $this->request->is_set_post('delmarkedlog') ? true : false;
		$mode = $this->request->variable('mode', '');
		$form_key = 'sylver35/breizhshoutbox';
		add_form_key($form_key);

		if ($deletemark)
		{
			if (confirm_box(true))
			{
				$this->functions_admin->action_delete_mark($form_key, $deletemark, true, $this->u_action);
			}
			else
			{
				confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields([
					'start'			=> $start,
					'delmarked'		=> $deletemark,
					'mark'			=> $marked,
					'i'				=> $id,
					'mode'			=> $mode,
					'action'		=> $action,
					'creation_time'	=> $creation,
					'form_token'	=> $token,
				]));
			}
		}
		else if ($deletemarklog)
		{
			if (confirm_box(true))
			{
				$this->functions_admin->action_delete_marklog($form_key, $deletemarklog, true, $this->u_action);
			}
			else
			{
				confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields([
					'start'			=> $start,
					'delmarkedlog'	=> $deletemarklog,
					'mark'			=> $marked,
					'i'				=> $id,
					'creation_time'	=> $creation,
					'form_token'	=> $token,
				]));
			}
		}
		else if ($action)
		{
			if (confirm_box(true))
			{
				$this->functions_admin->action_purge_shoutbox($form_key, $action, true, $this->u_action);
			}
			else
			{
				confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields([
					'i'				=> $id,
					'action'		=> $action,
					'creation_time'	=> $creation,
					'form_token'	=> $token,
				]));
			}
		}
		else
		{
			$start = $this->request->variable('start', 0);
			$shout_number = (int) $this->config['shout_nr_acp'];
			$return = $this->functions_admin->get_messages($start, $shout_number, false);
			$li = $this->functions_admin->get_logs(true);
			$total_del = $this->config['shout_del_acp_priv'] + $this->config['shout_del_auto_priv'] + $this->config['shout_del_purge_priv'] + $this->config['shout_del_user_priv'];

			$this->template->assign_vars([
				'TOTAL_POSTS'				=> $return['total_posts'],
				'S_DISPLAY_MESSAGES'		=> ($return['i'] > 0) ? true : false,
				'S_DISPLAY_LOGS'			=> ($li > 0) ? true : false,
				'S_ON_PAGE'					=> ($return['total_posts'] > $shout_number) ? true : false,
				'TOTAL_MESSAGES'			=> $this->language->lang($this->shoutbox->plural('NUMBER_MESSAGE', $return['total_posts']), $return['total_posts']),
				'MESSAGES_TOTAL_NR'			=> $this->language->lang('SHOUT_MESSAGES_TOTAL_NR', $this->config['shout_nr_priv'], $this->user->format_date($this->config['shout_time_priv'])),
				'PAGE_NUMBER'				=> $this->pagination->validate_start($return['total_posts'], $shout_number, $start),	
				'LAST_SHOUT_RUN'			=> ($this->config['shout_last_run_priv'] == $this->config['shout_time_priv']) ? $this->language->lang('SHOUT_NEVER') : $this->user->format_date($this->config['shout_last_run_priv']),
				'LOGS_TOTAL_NR'				=> $this->language->lang($this->shoutbox->plural('NUMBER_LOG', $this->config['shout_nr_log_priv'], '_TOTAL'), $this->config['shout_nr_log_priv'], $this->user->format_date($this->config['shout_time_priv'])),
				'MESSAGES_DEL_TOTAL'		=> $this->language->lang($this->shoutbox->plural('SHOUT_DEL_NR', $total_del), $total_del) . $this->language->lang('SHOUT_DEL_TOTAL'),
				'MESSAGES_DEL_ACP'			=> $this->language->lang($this->shoutbox->plural('SHOUT_DEL_NR', $this->config['shout_del_acp_priv']), $this->config['shout_del_acp_priv']),
				'MESSAGES_DEL_AUTO'			=> $this->language->lang($this->shoutbox->plural('SHOUT_DEL_NR', $this->config['shout_del_auto_priv']), $this->config['shout_del_auto_priv']),
				'MESSAGES_DEL_PURGE'		=> $this->language->lang($this->shoutbox->plural('SHOUT_DEL_NR', $this->config['shout_del_purge_priv']), $this->config['shout_del_purge_priv']),
				'MESSAGES_DEL_USER'			=> $this->language->lang($this->shoutbox->plural('SHOUT_DEL_NR', $this->config['shout_del_user_priv']), $this->config['shout_del_user_priv']),
			]);
			$this->pagination->generate_template_pagination($this->u_action, 'pagination', 'start', $return['total_posts'], $shout_number, $start);
		}
	}

	public function acp_shoutbox_config_priv()
	{
		$mode = $this->request->variable('mode', '');
		$form_key = 'sylver35/breizhshoutbox';
		add_form_key($form_key);
		if ($this->request->is_set_post('update'))
		{
			if (!check_form_key($form_key))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$this->functions_admin->update_config([
				'shout_title_priv'				=> str_replace("'", $this->language->lang('SHOUT_PROTECT'), $this->request->variable('shout_title_priv', '', true)),
				'shout_width_post_priv'			=> $this->request->variable('shout_width_post_priv', 325),
				'shout_prune_priv'				=> $this->request->variable('shout_prune_priv', 0),
				'shout_on_cron_priv'			=> $this->request->variable('shout_on_cron_priv', 1),
				'shout_log_cron_priv'			=> $this->request->variable('shout_log_cron_priv', 0),
				'shout_button_background_priv'	=> $this->request->variable('shout_button_background_priv', 0),
				'shout_defil_priv'				=> $this->request->variable('shout_defil_priv', 1),
				'shout_max_posts_priv'			=> $this->request->variable('shout_max_posts_priv', 400),
				'shout_max_posts_on_priv'		=> $this->request->variable('shout_max_posts_on_priv', 300),
				'shout_height_priv'				=> $this->request->variable('shout_height_priv', 460),
				'shout_div_img_priv'			=> $this->request->variable('shout_div_img_priv', ''),
				'shout_img_horizontal_priv'		=> $this->request->variable('shout_img_horizontal_priv', 'right'),
				'shout_img_vertical_priv'		=> $this->request->variable('shout_img_vertical_priv', 'bottom'),
				'shout_num_priv'				=> $this->request->variable('shout_num_priv', 25),
				'shout_color_background_priv'	=> $this->request->variable('shout_color_background_priv', ''),
				'shout_on_cron_priv'			=> $this->request->variable('shout_on_cron_priv', 1),
				'shout_bar_option_priv'			=> $this->request->variable('shout_bar_option_priv', 1),
				'shout_sound_new_priv'			=> $this->request->variable('shout_sound_new_priv', ''),
			]);

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_SHOUT_' . strtoupper($mode), time());
			trigger_error($this->language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
		}
		else
		{
			$this->template->assign_vars([
				'SHOUT_TITLE_PRIV'			=> (string) $this->config['shout_title_priv'],
				'SHOUT_WIDTH_POST'			=> (int) $this->config['shout_width_post_priv'],
				'SHOUT_PRUNE_PRIV'			=> (int) $this->config['shout_prune_priv'],
				'SHOUT_IMG_HORIZONTAL_PRIV'	=> $this->functions_admin->build_select_horizontal('shout_img_horizontal_priv'),
				'SHOUT_IMG_VERTICAL_PRIV'	=> $this->functions_admin->build_select_vertical('shout_img_vertical_priv'),
				'SHOUT_ON_CRON_PRIV'		=> $this->functions_admin->construct_radio('shout_on_cron_priv', 2),
				'SHOUT_LOG_CRON_PRIV'		=> $this->functions_admin->construct_radio('shout_log_cron_priv', 2),
				'SHOUT_BUTTON'				=> $this->functions_admin->construct_radio('shout_button_background_priv', 1),
				'SHOUT_DEFIL'				=> $this->functions_admin->construct_radio('shout_defil_priv', 3, true, 'SHOUT_DEFIL_TOP', 'SHOUT_DEFIL_BOTTOM'),
				'NEW_SOUND_PRIV'			=> $this->functions_admin->build_adm_sound_select('new_priv'),
				'SHOUT_MAX_POSTS'			=> (int) $this->config['shout_max_posts_priv'],
				'SHOUT_MAX_POSTS_ON'		=> (int) $this->config['shout_max_posts_on_priv'],
				'SHOUT_HEIGHT_PRIV' 		=> (int) $this->config['shout_height_priv'],
				'SHOUT_NUM_PRIV'			=> (int) $this->config['shout_num_priv'],
				'COLOR_IMAGE'				=> (string) $this->config['shout_color_background_priv'] . '.webp',
				'SHOUT_DIV_IMG_PRIV'		=> (string) $this->config['shout_div_img_priv'],
				'SHOUT_BAR_TOP'				=> (bool) $this->config['shout_bar_option_priv'],
				'SHOUT_SOUNDS_PATH'			=> $this->ext_path . 'sounds/',
				'OPTION_IMAGE'				=> $this->functions_admin->build_select_img($this->ext_path, 'styles/all/theme/images/fond/', 'shout_color_background_priv', false, 'webp'),
				'COLOR_PATH'				=> $this->ext_path . 'styles/all/theme/images/fond/',
				'DIV_IMG_PATH'				=> $this->ext_path . 'styles/' . (file_exists($this->ext_path . 'styles/' . rawurlencode($this->user->style['style_path']) . '/theme/images/background/' . $this->config['shout_div_img_priv']) ? rawurlencode($this->user->style['style_path']) : 'all') . '/theme/images/background/',
				'DIV_IMG_OPTION'			=> $this->functions_admin->build_select_background($this->ext_path, 'styles/all/theme/images/background/', 'shout_div_img_priv'),
			]);
		}
	}

	public function acp_shoutbox_popup()
	{
		$mode = $this->request->variable('mode', '');
		$form_key = 'sylver35/breizhshoutbox';
		add_form_key($form_key);
		if ($this->request->is_set_post('update'))
		{
			if (!check_form_key($form_key))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$this->functions_admin->update_config([
				'shout_width_post_pop'			=> $this->request->variable('shout_width_post_pop', 325),
				'shout_height_pop'				=> $this->request->variable('shout_height_pop', 210),
				'shout_div_img_pop'				=> $this->request->variable('shout_div_img_pop', ''),
				'shout_img_horizontal_pop'		=> $this->request->variable('shout_img_horizontal_pop', 'right'),
				'shout_img_vertical_pop'		=> $this->request->variable('shout_img_vertical_pop', 'bottom'),
				'shout_num_pop'					=> $this->request->variable('shout_num_pop', 25),
				'shout_popup_height'			=> $this->request->variable('shout_popup_height', 580),
				'shout_popup_width'				=> $this->request->variable('shout_popup_width', 1100),
				'shout_color_background_pop'	=> $this->request->variable('shout_color_background_pop', 'blue'),
				'shout_button_background_pop'	=> $this->request->variable('shout_button_background_pop', 1),
				'shout_bar_option_pop'			=> $this->request->variable('shout_bar_option_pop', 1),
				'shout_defil_pop'				=> $this->request->variable('shout_defil_pop', 1),
			]);

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_SHOUT_' . strtoupper($mode), time());
			trigger_error($this->language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
		}
		else
		{
			$this->template->assign_vars([
				'SHOUT_WIDTH_POST'			=> (int) $this->config['shout_width_post_pop'],
				'SHOUT_HEIGHT_POP'			=> (int) $this->config['shout_height_pop'],
				'SHOUT_NUM_POP'				=> (int) $this->config['shout_num_pop'],
				'SHOUT_POPUP_HEIGHT'		=> (int) $this->config['shout_popup_height'],
				'SHOUT_WIDTH_POP'			=> (int) $this->config['shout_popup_width'],
				'SHOUT_BAR_OPTION'			=> (bool) $this->config['shout_bar_option_pop'],
				'COLOR_IMAGE'				=> (string) $this->config['shout_color_background_pop'] . '.webp',
				'SHOUT_DIV_IMG_POP'			=> (string) $this->config['shout_div_img_pop'],
				'SHOUT_IMG_HORIZONTAL_POP'	=> $this->functions_admin->build_select_horizontal('shout_img_horizontal_pop'),
				'SHOUT_IMG_VERTICAL_POP'	=> $this->functions_admin->build_select_vertical('shout_img_vertical_pop'),
				'SHOUT_BUTTON'				=> $this->functions_admin->construct_radio('shout_button_background_pop', 1),
				'SHOUT_DEFIL'				=> $this->functions_admin->construct_radio('shout_defil_pop', 3, true, 'SHOUT_DEFIL_TOP', 'SHOUT_DEFIL_BOTTOM'),
				'COLOR_SELECT'				=> $this->functions_admin->build_select_img($this->ext_path, 'styles/all/theme/images/fond/', 'shout_color_background_pop', false, 'webp'),
				'COLOR_PATH'				=> $this->ext_path . 'styles/all/theme/images/fond/',
				'DIV_IMG_PATH'				=> $this->ext_path . 'styles/' . (file_exists($this->ext_path . 'styles/' . rawurlencode($this->user->style['style_path']) . '/theme/images/background/' . $this->config['shout_div_img_pop']) ? rawurlencode($this->user->style['style_path']) : 'all') . '/theme/images/background/',
				'DIV_IMG_OPTION'			=> $this->functions_admin->build_select_background($this->ext_path, 'styles/all/theme/images/background/', 'shout_div_img_pop'),
			]);
		}
	}

	public function acp_shoutbox_panel()
	{
		$mode = $this->request->variable('mode', '');
		$form_key = 'sylver35/breizhshoutbox';
		add_form_key($form_key);
		if ($this->request->is_set_post('update'))
		{
			if (!check_form_key($form_key))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$this->functions_admin->update_config([
				'shout_panel'			=> $this->request->variable('shout_panel', 1),
				'shout_panel_all'		=> $this->request->variable('shout_panel_all', 0),
				'shout_panel_auto'		=> $this->request->variable('shout_panel_auto', 0),
				'shout_page_exclude'	=> str_replace(["\n", '&amp;'], ['||', '&'], $this->request->variable('shout_page_exclude', '')),
				'shout_panel_float'		=> $this->request->variable('shout_panel_float', ''),
				'shout_panel_img'		=> $this->request->variable('shout_panel_img', ''),
				'shout_panel_exit_img'	=> $this->request->variable('shout_panel_exit_img', ''),
				'shout_panel_width'		=> $this->request->variable('shout_panel_width', 800),
				'shout_panel_height'	=> $this->request->variable('shout_panel_height', 510),
			]);

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_SHOUT_' . strtoupper($mode), time());
			trigger_error($this->language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
		}
		else
		{
			$panel_path = 'images/panel/';
			$this->template->assign_vars([
				'PANEL_PATH'				=> $this->ext_path . $panel_path,
				'SHOUT_PANEL'				=> $this->functions_admin->construct_radio('shout_panel', 2),
				'SHOUT_PANEL_ALL'			=> $this->functions_admin->construct_radio('shout_panel_all', 2),
				'SHOUT_PANEL_AUTO'			=> $this->functions_admin->construct_radio('shout_panel_auto', 2),
				'SHOUT_PANEL_FLOAT'			=> $this->functions_admin->construct_radio('shout_panel_float', 3, false, 'SHOUT_PANEL_FLOAT_LEFT', 'SHOUT_PANEL_FLOAT_RIGHT'),
				'SHOUT_PANEL_WIDTH'			=> $this->config['shout_panel_width'],
				'SHOUT_PANEL_HEIGHT'		=> $this->config['shout_panel_height'],
				'PANEL_OPEN_IMAGE'			=> $this->config['shout_panel_img'],
				'PANEL_EXIT_IMAGE'			=> $this->config['shout_panel_exit_img'],
				'SHOUT_PAGE_EXCLUDE'		=> str_replace('||', "\n", $this->config['shout_page_exclude']),
				'OPTION_OPEN_TITLE'			=> substr($this->config['shout_panel_img'], 0, strrpos($this->config['shout_panel_img'], '.')),
				'OPTION_EXIT_TITLE'			=> substr($this->config['shout_panel_exit_img'], 0, strrpos($this->config['shout_panel_exit_img'], '.')),
				'PANEL_OPEN_OPTION'			=> $this->functions_admin->build_select_img($this->ext_path, $panel_path, 'shout_panel_img', true),
				'PANEL_EXIT_OPTION'			=> $this->functions_admin->build_select_img($this->ext_path, $panel_path, 'shout_panel_exit_img', true),
			]);
		}
	}

	public function acp_shoutbox_smilies()
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
				'SRC'		=> $row['smiley_url'],
				'ID'		=> $row['smiley_id'],
				'CODE'		=> $row['code'],
				'EMOTION'	=> $row['emotion'],
				'WIDTH'		=> $row['smiley_width'],
				'HEIGHT'	=> $row['smiley_height'],
			]);
		}
		$this->db->sql_freeresult($result);

		$sql = $this->db->sql_build_query('SELECT', [
			'SELECT'	=> 'MIN(smiley_id) AS smiley_id, MIN(code) AS code, smiley_url,  MIN(smiley_order) AS min_smiley_order, MIN(smiley_width) AS smiley_width, MIN(smiley_height) AS smiley_height, MIN(emotion) AS emotion, MIN(display_on_shout) AS display_on_shout',
			'FROM'		=> [SMILIES_TABLE => ''],
			'WHERE'		=> 'display_on_shout = 0',
			'GROUP_BY'	=> 'smiley_url',
			'ORDER_BY'	=> 'min_smiley_order ASC',
		]);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('smilies_popup', [
				'SRC'		=> $row['smiley_url'],
				'ID'		=> $row['smiley_id'],
				'CODE'		=> $row['code'],
				'EMOTION'	=> $row['emotion'],
				'WIDTH'		=> $row['smiley_width'],
				'HEIGHT'	=> $row['smiley_height'],
			]);
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_vars([
			'SHOUT_USER_ID'			=> $this->user->data['user_id'],
			'SMILIES_URL'			=> $this->root_path . $this->config['smilies_path'] . '/',
			'U_DISPLAY_AJAX'		=> $this->helper->route('sylver35_breizhshoutbox_ajax', ['mode' => 'display_smilies']),
			'SHOUT_IMG_PATH'		=> $this->ext_path . 'images/',
		]);
	}

	public function acp_shoutbox_robot()
	{
		$mode = $this->request->variable('mode', '');
		$form_key = 'sylver35/breizhshoutbox';
		add_form_key($form_key);

		if ($this->request->is_set_post('update'))
		{
			if (!check_form_key($form_key))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$this->functions_admin->update_config([
				'shout_enable_robot'		=> $this->request->variable('shout_enable_robot', 1),
				'shout_name_robot'			=> $this->request->variable('shout_name_robot', '', true),
				'shout_post_robot'			=> $this->request->variable('shout_post_robot', 1),
				'shout_rep_robot'			=> $this->request->variable('shout_rep_robot', 1),
				'shout_edit_robot'			=> $this->request->variable('shout_edit_robot', 1),
				'shout_post_robot_priv'		=> $this->request->variable('shout_post_robot_priv', 1),
				'shout_rep_robot_priv'		=> $this->request->variable('shout_rep_robot_priv', 1),
				'shout_edit_robot_priv'		=> $this->request->variable('shout_edit_robot_priv', 1),
				'shout_prez_form'			=> $this->request->variable('shout_prez_form', ''),
				'shout_color_robot'			=> $this->request->variable('shout_color_robot', ''),
				'shout_color_message'		=> $this->request->variable('shout_color_message', ''),
				'shout_delete_robot'		=> $this->request->variable('shout_delete_robot', 1),
				'shout_sessions'			=> $this->request->variable('shout_sessions', 1),
				'shout_sessions_priv'		=> $this->request->variable('shout_sessions_priv', 0),
				'shout_sessions_time'		=> $this->request->variable('shout_sessions_time', 15),
				'shout_sessions_bots'		=> $this->request->variable('shout_sessions_bots', 0),
				'shout_sessions_bots_priv'	=> $this->request->variable('shout_sessions_bots_priv', 0),
				'shout_hello'				=> $this->request->variable('shout_hello', 1),
				'shout_hello_priv'			=> $this->request->variable('shout_hello_priv', 1),
				'shout_newest'				=> $this->request->variable('shout_newest', 1),
				'shout_newest_priv'			=> $this->request->variable('shout_newest_priv', 1),
				'shout_birthday'			=> $this->request->variable('shout_birthday', 1),
				'shout_birthday_priv'		=> $this->request->variable('shout_birthday_priv', 1),
				'shout_cron_hour'			=> $this->request->variable('shout_cron_hour', '09'),
				'shout_exclude_forums'		=> implode(', ', $this->request->variable('shout_exclude_forums', [0])),
				'shout_birthday_exclude'	=> implode(', ', $this->request->variable('shout_birthday_exclude', [0])),
				'shout_robot_choice'		=> implode(', ', $this->request->variable('shout_robot_choice', [0])),
				'shout_robot_choice_priv'	=> implode(', ', $this->request->variable('shout_robot_choice_priv', [0])),
				'shout_video_new'			=> $this->request->variable('shout_video_new', 0),
				'shout_arcade_new'			=> $this->request->variable('shout_arcade_new', 0),
				'shout_arcade_record'		=> $this->request->variable('shout_arcade_record', 0),
				'shout_arcade_urecord'		=> $this->request->variable('shout_arcade_urecord', 0),
			]);

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_SHOUT_' . strtoupper($mode), time());
			trigger_error($this->language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
		}
		else
		{
			$this->functions_admin->get_another_options();
			$this->template->assign_vars([
				'SHOUT_NAME_ROBOT'			=> (string) $this->config['shout_name_robot'],
				'SHOUT_ENABLE_ROBOT'		=> $this->functions_admin->construct_radio('shout_enable_robot', 2, true),
				'SHOUT_POST_ROBOT'			=> $this->functions_admin->construct_radio('shout_post_robot', 2),
				'SHOUT_REP_ROBOT'			=> $this->functions_admin->construct_radio('shout_rep_robot', 2),
				'SHOUT_EDIT_ROBOT'			=> $this->functions_admin->construct_radio('shout_edit_robot', 2),
				'SHOUT_POST_ROBOT_PRIV'		=> $this->functions_admin->construct_radio('shout_post_robot_priv', 2),
				'SHOUT_REP_ROBOT_PRIV'		=> $this->functions_admin->construct_radio('shout_rep_robot_priv', 2),
				'SHOUT_EDIT_ROBOT_PRIV'		=> $this->functions_admin->construct_radio('shout_edit_robot_priv', 2),
				'SHOUT_DELETE_ROBOT'		=> $this->functions_admin->construct_radio('shout_delete_robot', 2),
				'SHOUT_SESSIONS'			=> $this->functions_admin->construct_radio('shout_sessions', 2),
				'SHOUT_SESSIONS_PRIV'		=> $this->functions_admin->construct_radio('shout_sessions_priv', 2),
				'SHOUT_SESSIONS_BOTS'		=> $this->functions_admin->construct_radio('shout_sessions_bots', 2),
				'SHOUT_SESSIONS_BOTS_PRIV'	=> $this->functions_admin->construct_radio('shout_sessions_bots_priv', 2),
				'SHOUT_HELLO'				=> $this->functions_admin->construct_radio('shout_hello', 2),
				'SHOUT_HELLO_PRIV'			=> $this->functions_admin->construct_radio('shout_hello_priv', 2),
				'SHOUT_NEWEST'				=> $this->functions_admin->construct_radio('shout_newest', 2),
				'SHOUT_NEWEST_PRIV'			=> $this->functions_admin->construct_radio('shout_newest_priv', 2),
				'SHOUT_BIRTHDAY'			=> $this->functions_admin->construct_radio('shout_birthday', 2),
				'SHOUT_BIRTHDAY_PRIV'		=> $this->functions_admin->construct_radio('shout_birthday_priv', 2),
				'SHOUT_COLOR_ROBOT'			=> (string) $this->config['shout_color_robot'],
				'SHOUT_COLOR_MESSAGE'		=> (string) $this->config['shout_color_message'],
				'SHOUT_SESSIONS_TIME'		=> (int) $this->config['shout_sessions_time'],
				'SHOUT_CRON_HOUR'			=> $this->functions_admin->hour_select((string) $this->config['shout_cron_hour'], 'shout_cron_hour'),
				'SHOUT_PREZ_FORM'			=> make_forum_select((int) $this->config['shout_prez_form'], false, true, true),
				'SHOUT_EXCLUDE_FORUMS'		=> make_forum_select(explode(', ', $this->config['shout_exclude_forums']), false, false, false, false),
				'GROUP_OPTIONS'				=> $this->functions_admin->get_group_options(),
				'SERVER_HOUR'				=> $this->language->lang($this->shoutbox->plural('SHOUT_SERVER_HOUR', date('H')), date('H'), date('i')),
			]);
		}
	}

	/**
	 * Set page url
	 *
	 * @param string $u_action Custom form action
	 * @return null
	 * @access public
	 */
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
