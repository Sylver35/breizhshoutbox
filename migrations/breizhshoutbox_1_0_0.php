<?php
/**
 *
 * @package Breizh Shoutbox Extension
 * @copyright (c) 2018-2020 Sylver35  https://breizhcode.com
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace sylver35\breizhshoutbox\migrations;

use phpbb\db\migration\migration;

class breizhshoutbox_1_0_0 extends migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'users', 'user_shout') && $this->db_tools->sql_table_exists($this->table_prefix . 'shoutbox');
	}

	static public function depends_on()
	{
		return ['\phpbb\db\migration\data\v32x\v328'];
	}

	public function update_data()
	{
		return [
			// Version of extension
			['config.add', ['shout_version', '1.0.0']],

			// Config
			['config.add', ['shout_avatar', 1]],
			['config.add', ['shout_avatar_height', 20]],
			['config.add', ['shout_avatar_robot', 1]],
			['config.add', ['shout_avatar_user', 1]],
			['config.add', ['shout_avatar_img', 'no_avatar.webp']],
			['config.add', ['shout_avatar_img_robot', 'avatar_robot.webp']],
			['config.add', ['shout_bar_option', 1]],
			['config.add', ['shout_bar_option_pop', 1]],
			['config.add', ['shout_bar_option_priv', 1]],
			['config.add', ['shout_bbcode', '']],
			['config.add', ['shout_bbcode_user', 'list, code, quote']],
			['config.add', ['shout_bbcode_size', 200]],
			['config.add', ['shout_birthday', 1]],
			['config.add', ['shout_birthday_exclude', '']],
			['config.add', ['shout_birthday_priv', 1]],
			['config.add', ['shout_button_background', 0]],
			['config.add', ['shout_button_background_pop', 0]],
			['config.add', ['shout_button_background_priv', 0]],
			['config.add', ['shout_color_robot', 'FF8040']],
			['config.add', ['shout_color_message', '004080']],
			['config.add', ['shout_color_background', 'orange']],
			['config.add', ['shout_color_background_sub', 'transparent']],
			['config.add', ['shout_color_background_pop', 'blue']],
			['config.add', ['shout_color_background_priv', 'subsilver3']],
			['config.add', ['shout_cron_hour', '09']],
			['config.add', ['shout_cron_run', '', true]],
			['config.add', ['shout_dateformat', '|d M| H:i']],
			['config.add', ['shout_defil', 1]],
			['config.add', ['shout_del_acp', 0]],
			['config.add', ['shout_del_auto', 0]],
			['config.add', ['shout_del_purge', 0]],
			['config.add', ['shout_del_user', 0]],
			['config.add', ['shout_del_acp_priv', 0]],
			['config.add', ['shout_del_auto_priv', 0]],
			['config.add', ['shout_del_purge_priv', 0]],
			['config.add', ['shout_del_user_priv', 0]],
			['config.add', ['shout_delete_robot', 1]],
			['config.add', ['shout_edit_robot', 1]],
			['config.add', ['shout_edit_robot_priv', 1]],
			['config.add', ['shout_enable_robot', 1]],
			['config.add', ['shout_exclude_forums', '']],
			['config.add', ['shout_forum', 1]],
			['config.add', ['shout_height', 200]],
			['config.add', ['shout_hello', 1]],
			['config.add', ['shout_hello_priv', 1]],
			['config.add', ['shout_ie_nr', 6]],
			['config.add', ['shout_ie_nr_pop', 7]],
			['config.add', ['shout_ie_nr_priv', 12]],
			['config.add', ['shout_inactiv_anony', 15]],
			['config.add', ['shout_inactiv_member', 45]],
			['config.add', ['shout_index', 1]],
			['config.add', ['shout_last_run', time(), true]],
			['config.add', ['shout_last_run_priv', time(), true]],
			['config.add', ['shout_log_cron', 1]],
			['config.add', ['shout_log_cron_priv', 1]],
			['config.add', ['shout_max_post_chars', 250]],
			['config.add', ['shout_max_posts_on', 250]],
			['config.add', ['shout_max_posts', 0]],
			['config.add', ['shout_max_posts_priv', 0]],
			['config.add', ['shout_max_posts_on_priv', 250]],
			['config.add', ['shout_newest', 1]],
			['config.add', ['shout_newest_priv', 1]],
			['config.add', ['shout_non_ie_nr', 25]],
			['config.add', ['shout_non_ie_height_priv', 410]],
			['config.add', ['shout_non_ie_nr_priv', 25]],
			['config.add', ['shout_non_ie_height_pop', 220]],
			['config.add', ['shout_non_ie_nr_pop', 25]],
			['config.add', ['shout_nr', 0]],
			['config.add', ['shout_nr_acp', 20]],
			['config.add', ['shout_nr_priv', 0]],
			['config.add', ['shout_nr_log_priv', 0]],
			['config.add', ['shout_on_cron', 0]],
			['config.add', ['shout_on_cron_priv', 0]],
			['config.add', ['shout_page_exclude', 'gallery||video']],
			['config.add', ['shout_pagin_option', 0]],
			['config.add', ['shout_pagin_option_pop', 0]],
			['config.add', ['shout_pagin_option_priv', 0]],
			['config.add', ['shout_panel', 1]],
			['config.add', ['shout_panel_all', 1]],
			['config.add', ['shout_panel_auto', 0]],
			['config.add', ['shout_panel_img', 'forward.webp']],
			['config.add', ['shout_panel_exit_img', 'back.webp']],
			['config.add', ['shout_panel_height', 300]],
			['config.add', ['shout_panel_width', 720]], 
			['config.add', ['shout_popup_height', 310]],
			['config.add', ['shout_popup_width', 720]],
			['config.add', ['shout_position_forum', 1]],
			['config.add', ['shout_position_index', 1]],
			['config.add', ['shout_position_topic', 1]],
			['config.add', ['shout_post_robot', 1]],
			['config.add', ['shout_post_robot_priv', 1]],
			['config.add', ['shout_prez_form', 0]],
			['config.add', ['shout_prune', 0]],
			['config.add', ['shout_prune_priv', 0]],
			['config.add', ['shout_rep_robot', 1]],
			['config.add', ['shout_rep_robot_priv', 1]],
			['config.add', ['shout_robot_choice', '5']],
			['config.add', ['shout_robot_choice_priv', '1, 5']],
			['config.add', ['shout_rules', 1]],
			['config.add', ['shout_see_buttons', 1]],
			['config.add', ['shout_see_buttons_left', 1]],
			['config.add', ['shout_sessions', 1]],
			['config.add', ['shout_sessions_priv', 0]],
			['config.add', ['shout_sessions_time', 120]],
			['config.add', ['shout_sessions_bots', 0]],
			['config.add', ['shout_sessions_bots_priv', 0]],
			['config.add', ['shout_sound_on', 1]],
			['config.add', ['shout_sound_add', 'crapaud.mp3']],
			['config.add', ['shout_sound_del', 'effect37.mp3']],
			['config.add', ['shout_sound_edit', 'cat.mp3']],
			['config.add', ['shout_sound_error', 'chimpanze.mp3']],
			['config.add', ['shout_sound_new', 'discretion.mp3']],
			['config.add', ['shout_sound_new_priv', 'bell1.mp3']],
			['config.add', ['shout_temp_users', 5]],
			['config.add', ['shout_temp_anonymous', 15]],
			['config.add', ['shout_time', time()]],
			['config.add', ['shout_time_priv', time()]],
			['config.add', ['shout_title', 'Breizh Shoutbox']],
			['config.add', ['shout_title_priv', 'Private Shoutbox']],
			['config.add', ['shout_topic', 1]],
			['config.add', ['shout_width_post', 350]],
			['config.add', ['shout_width_post_pop', 300]],
			['config.add', ['shout_width_post_priv', 350]],
			['config.add', ['shout_width_post_sub', 100]],

			// Add admin permissions
			['permission.add', ['a_shout_manage', true]],
			['permission.add', ['a_shout_priv', true]],

			// Add moderator permissions
			['permission.add', ['m_shout_delete', true]],
			['permission.add', ['m_shout_edit_mod', true]],
			['permission.add', ['m_shout_info', true]],
			['permission.add', ['m_shout_personal', true]],
			['permission.add', ['m_shout_purge', true]],
			['permission.add', ['m_shout_robot', true]],

			// Add user permissions
			['permission.add', ['u_shout_bbcode', true]],
			['permission.add', ['u_shout_bbcode_change', true]],
			['permission.add', ['u_shout_chars', true]],
			['permission.add', ['u_shout_color', true]],
			['permission.add', ['u_shout_delete_s', true]],
			['permission.add', ['u_shout_edit', true]],
			['permission.add', ['u_shout_hide', true]],
			['permission.add', ['u_shout_ignore_flood', true]],
			['permission.add', ['u_shout_image', true]],
			['permission.add', ['u_shout_inactiv', true]],
			['permission.add', ['u_shout_info_s', true]],
			['permission.add', ['u_shout_lateral', true]],
			['permission.add', ['u_shout_limit_post', true]],
			['permission.add', ['u_shout_popup', true]],
			['permission.add', ['u_shout_post', true]],
			['permission.add', ['u_shout_post_inp', true]],
			['permission.add', ['u_shout_priv', true]],
			['permission.add', ['u_shout_smilies', true]],
			['permission.add', ['u_shout_view', true]],

			// Set permissions administration
			['permission.permission_set', ['ADMINISTRATORS', 'a_shout_manage', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'a_shout_priv', 'group']],

			// Set permissions moderation
			['permission.permission_set', ['ADMINISTRATORS', 'm_shout_delete', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'm_shout_edit_mod', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'm_shout_info', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'm_shout_personal', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'm_shout_purge', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'm_shout_robot', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'm_shout_delete', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'm_shout_edit_mod', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'm_shout_info', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'm_shout_personal', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'm_shout_purge', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'm_shout_robot', 'group']],

			// Set permissions users
			['permission.permission_set', ['REGISTERED', 'u_shout_bbcode', 'group']],
			['permission.permission_set', ['REGISTERED', 'u_shout_bbcode_change', 'group']],
			['permission.permission_set', ['REGISTERED', 'u_shout_chars', 'group']],
			['permission.permission_set', ['REGISTERED', 'u_shout_color', 'group']],
			['permission.permission_set', ['REGISTERED', 'u_shout_edit', 'group']],
			['permission.permission_set', ['REGISTERED', 'u_shout_hide', 'group']],
			['permission.permission_set', ['REGISTERED', 'u_shout_image', 'group']],
			['permission.permission_set', ['REGISTERED', 'u_shout_info_s', 'group']],
			['permission.permission_set', ['REGISTERED', 'u_shout_lateral', 'group']],
			['permission.permission_set', ['REGISTERED', 'u_shout_popup', 'group']],
			['permission.permission_set', ['REGISTERED', 'u_shout_post', 'group']],
			['permission.permission_set', ['REGISTERED', 'u_shout_post_inp', 'group']],
			['permission.permission_set', ['REGISTERED', 'u_shout_smilies', 'group']],
			['permission.permission_set', ['REGISTERED', 'u_shout_view', 'group']],

			['permission.permission_set', ['ADMINISTRATORS', 'u_shout_delete_s', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'u_shout_inactiv', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'u_shout_limit_post', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'u_shout_ignore_flood', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'u_shout_bbcode', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'u_shout_bbcode_change', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'u_shout_chars', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'u_shout_color', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'u_shout_edit', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'u_shout_hide', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'u_shout_image', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'u_shout_info_s', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'u_shout_lateral', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'u_shout_popup', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'u_shout_post', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'u_shout_post_inp', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'u_shout_smilies', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'u_shout_view', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'u_shout_priv', 'group']],

			['permission.permission_set', ['GLOBAL_MODERATORS', 'u_shout_delete_s', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'u_shout_inactiv', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'u_shout_limit_post', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'u_shout_ignore_flood', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'u_shout_bbcode', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'u_shout_bbcode_change', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'u_shout_chars', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'u_shout_color', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'u_shout_edit', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'u_shout_hide', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'u_shout_image', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'u_shout_info_s', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'u_shout_lateral', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'u_shout_popup', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'u_shout_post', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'u_shout_post_inp', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'u_shout_smilies', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'u_shout_view', 'group']],

			['permission.permission_set', ['NEWLY_REGISTERED', 'u_shout_view', 'group']],
			['permission.permission_set', ['NEWLY_REGISTERED', 'u_shout_lateral', 'group']],
			['permission.permission_set', ['NEWLY_REGISTERED', 'u_shout_post', 'group']],
			['permission.permission_set', ['NEWLY_REGISTERED', 'u_shout_popup', 'group']],
			['permission.permission_set', ['NEWLY_REGISTERED', 'u_shout_smilies', 'group']],
			['permission.permission_set', ['NEWLY_REGISTERED', 'u_shout_bbcode', 'group']],
			['permission.permission_set', ['GUESTS', 'u_shout_view', 'group']],
			['permission.permission_set', ['GUESTS', 'u_shout_lateral', 'group']],
			['permission.permission_set', ['BOTS', 'u_shout_view', 'group']],

			// Add ACP extension category
			['module.add', ['acp', '', [
				'module_langname'	=> 'ACP_SHOUTBOX',
			]]],
			['module.add', ['acp', 'ACP_SHOUTBOX', [
				'module_langname'	=> 'ACP_SHOUT_GENERAL_CAT',
			]]],
			['module.add', ['acp', 'ACP_SHOUTBOX', [
				'module_langname'	=> 'ACP_SHOUT_PRIVATE_CAT',
			]]],
			['module.add', ['acp', 'ACP_SHOUTBOX', [
				'module_langname'	=> 'ACP_SHOUT_POPUP_CAT',
			]]],
			['module.add', ['acp', 'ACP_SHOUTBOX', [
				'module_langname'	=> 'ACP_SHOUT_SMILIES_CAT',
			]]],
			['module.add', ['acp', 'ACP_SHOUTBOX', [
				'module_langname'	=> 'ACP_SHOUT_ROBOT_CAT'
			]]],
			['module.add', ['acp', 'ACP_SHOUT_GENERAL_CAT', [
				'module_basename'	=> '\sylver35\breizhshoutbox\acp\main_module',
				'module_langname'	=> 'ACP_SHOUT_CONFIGS',
				'module_mode'		=> 'configs',
				'module_auth'		=> 'ext_sylver35/breizhshoutbox && acl_a_shout_manage',
			]]],
			['module.add', ['acp', 'ACP_SHOUT_GENERAL_CAT', [
				'module_basename'	=> '\sylver35\breizhshoutbox\acp\main_module',
				'module_langname'	=> 'ACP_SHOUT_CONFIG_GEN',
				'module_mode'		=> 'config_gen',
				'module_auth'		=> 'ext_sylver35/breizhshoutbox && acl_a_shout_manage',
			]]],
			['module.add', ['acp', 'ACP_SHOUT_GENERAL_CAT', [
				'module_basename'	=> '\sylver35\breizhshoutbox\acp\main_module',
				'module_langname'	=> 'ACP_SHOUT_OVERVIEW',
				'module_mode'		=> 'overview',
				'module_auth'		=> 'ext_sylver35/breizhshoutbox && acl_a_shout_manage',
			]]],
			['module.add', ['acp', 'ACP_SHOUT_GENERAL_CAT', [
				'module_basename'	=> '\sylver35\breizhshoutbox\acp\main_module',
				'module_langname'	=> 'ACP_SHOUT_RULES',
				'module_mode'		=> 'rules',
				'module_auth'		=> 'ext_sylver35/breizhshoutbox && acl_a_shout_manage',
			]]],
			['module.add', ['acp', 'ACP_SHOUT_PRIVATE_CAT', [
				'module_basename'	=> '\sylver35\breizhshoutbox\acp\main_module',
				'module_langname'	=> 'ACP_SHOUT_CONFIG_PRIV',
				'module_mode'		=> 'config_priv',
				'module_auth'		=> 'ext_sylver35/breizhshoutbox && acl_a_shout_priv',
			]]],
			['module.add', ['acp', 'ACP_SHOUT_PRIVATE_CAT', [
				'module_basename'	=> '\sylver35\breizhshoutbox\acp\main_module',
				'module_langname'	=> 'ACP_SHOUT_PRIVATE',
				'module_mode'		=> 'private',
				'module_auth'		=> 'ext_sylver35/breizhshoutbox && acl_a_shout_priv',
			]]],
			['module.add', ['acp', 'ACP_SHOUT_POPUP_CAT', [
				'module_basename'	=> '\sylver35\breizhshoutbox\acp\main_module',
				'module_langname'	=> 'ACP_SHOUT_POPUP',
				'module_mode'		=> 'popup',
				'module_auth'		=> 'ext_sylver35/breizhshoutbox && acl_a_shout_manage',
			]]],
			['module.add', ['acp', 'ACP_SHOUT_POPUP_CAT', [
				'module_basename'	=> '\sylver35\breizhshoutbox\acp\main_module',
				'module_langname'	=> 'ACP_SHOUT_PANEL',
				'module_mode'		=> 'panel',
				'module_auth'		=> 'ext_sylver35/breizhshoutbox && acl_a_shout_manage',
			]]],
			['module.add', ['acp', 'ACP_SHOUT_SMILIES_CAT', [
				'module_basename'	=> '\sylver35\breizhshoutbox\acp\main_module',
				'module_langname'	=> 'ACP_SHOUT_SMILIES',
				'module_mode'		=> 'smilies',
				'module_auth'		=> 'ext_sylver35/breizhshoutbox && acl_a_shout_manage',
			]]],
			['module.add', ['acp', 'ACP_SHOUT_ROBOT_CAT', [
				'module_basename'	=> '\sylver35\breizhshoutbox\acp\main_module',
				'module_langname'	=> 'ACP_SHOUT_ROBOT',
				'module_mode'		=> 'robot',
				'module_auth'		=> 'ext_sylver35/breizhshoutbox && acl_a_shout_manage',
			]]],

			// Custon function for first message
			['custom', [
				[&$this, 'add_first_messages')
			]]],
		];
	}

	public function update_schema()
	{
		return [
			'add_tables' => [
				$this->table_prefix . 'shoutbox' => [
					'COLUMNS'	=> [
						'shout_id'					=> ['UINT', null, 'auto_increment'],
						'shout_time'				=> ['UINT:11', 0],
						'shout_user_id'				=> ['UINT', 0],
						'shout_ip'					=> ['VCHAR:40', ''],
						'shout_text'				=> ['MTEXT_UNI', ''],
						'shout_text2'				=> ['MTEXT_UNI', null],
						'shout_bbcode_uid'			=> ['VCHAR:8', ''],
						'shout_bbcode_bitfield'		=> ['VCHAR:255', ''],
						'shout_bbcode_flags'		=> ['VCHAR:8', ''],
						'shout_robot'				=> ['UINT:10', 0],
						'shout_robot_user'			=> ['UINT:10', 0],
						'shout_forum'				=> ['UINT:6', 0],
						'shout_info'				=> ['UINT:4', 0],
						'shout_info_nb'				=> ['UINT:8', 0],
						'shout_inp'					=> ['UINT:8', 0],
					],
					'PRIMARY_KEY'	=> 'shout_id',
				],

				$this->table_prefix . 'shoutbox_priv' => [
					'COLUMNS'	=> [
						'shout_id'					=> ['UINT', null, 'auto_increment'],
						'shout_time'				=> ['UINT:11', 0],
						'shout_user_id'				=> ['UINT', 0],
						'shout_ip'					=> ['VCHAR:40', ''],
						'shout_text'				=> ['MTEXT_UNI', ''],
						'shout_text2'				=> ['MTEXT_UNI', null],
						'shout_bbcode_uid'			=> ['VCHAR:8', ''],
						'shout_bbcode_bitfield'		=> ['VCHAR:255', ''],
						'shout_bbcode_flags'		=> ['VCHAR:8', ''],
						'shout_robot'				=> ['UINT:10', 0],
						'shout_robot_user'			=> ['UINT:10', 0],
						'shout_forum'				=> ['UINT:6', 0],
						'shout_info'				=> ['UINT:4', 0],
						'shout_info_nb'				=> ['UINT:8', 0],
						'shout_inp'					=> ['UINT:8', 0],
					],
					'PRIMARY_KEY'	=> 'shout_id',
				],

				$this->table_prefix . 'shoutbox_rules' => [
					'COLUMNS'	=> [
						'id'						=> ['UINT', null, 'auto_increment'],
						'rules_lang'				=> ['VCHAR:30', ''],
						'rules_text'				=> ['MTEXT_UNI', ''],
						'rules_uid'					=> ['VCHAR:8', ''],
						'rules_bitfield'			=> ['VCHAR:255', ''],
						'rules_flags'				=> ['VCHAR:8', ''],
						'rules_text_priv'			=> ['MTEXT_UNI', ''],
						'rules_uid_priv'			=> ['VCHAR:8', ''],
						'rules_bitfield_priv'		=> ['VCHAR:255', ''],
						'rules_flags_priv'			=> ['VCHAR:8', ''],
					],
					'PRIMARY_KEY'	=> 'id',
				],
			],

			'add_columns' => [
				$this->table_prefix . 'smilies' => [
					'display_on_shout'	=> ['TINT:1', 1],
				],
				$this->table_prefix . 'users' => [
					'shout_bbcode'		=> ['VCHAR:255', ''],
					'user_shout'		=> ['VCHAR:255', '{"user":2,"new":0,"new_priv":0,"error":0,"del":0,"add":0,"edit":0,"index":3,"forum":3,"topic":3}'],
					'user_shoutbox'		=> ['VCHAR:255', '{"bar":"N","pagin":"N","bar_pop":"N","pagin_pop":"N","bar_priv":"N","pagin_priv":"N","defil":"N","panel":"N","dateformat":""}'],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'shoutbox',
				$this->table_prefix . 'shoutbox_priv',
				$this->table_prefix . 'shoutbox_rules',
			],
			'drop_columns' => [
				$this->table_prefix . 'users' => [
					'shout_bbcode',
					'user_shout',
					'user_shoutbox',
				],
				$this->table_prefix . 'smilies' => [
					'display_on_shout',
				],
			],
		];
	}

	public function add_first_messages()
	{
		if ($this->db_tools->sql_table_exists($this->table_prefix . 'shoutbox') && $this->db_tools->sql_table_exists($this->table_prefix . 'shoutbox_priv'))
		{
			$sql_data = [
				'shout_time'				=> time(),
				'shout_user_id'				=> 0,
				'shout_ip'					=> '0.0.0.0',
				'shout_text'				=> 'SHOUT_WELCOME',
				'shout_bbcode_uid'			=> '',
				'shout_bbcode_bitfield'		=> '',
				'shout_bbcode_flags'		=> 0,
				'shout_robot'				=> 1,
				'shout_robot_user'			=> 0,
				'shout_forum'				=> 0,
				'shout_info'				=> 99,
			];

			$sql = 'INSERT INTO ' . $this->table_prefix . 'shoutbox ' . $this->db->sql_build_array('INSERT', $sql_data);
			$this->db->sql_query($sql);

			$sql = 'INSERT INTO ' . $this->table_prefix . 'shoutbox_priv ' . $this->db->sql_build_array('INSERT', $sql_data);
			$this->db->sql_query($sql);
		}
	}
}
