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
		return array('\phpbb\db\migration\data\v32x\v328');
	}

	public function update_data()
	{
		return array(
			// Version of extension
			array('config.add', array('shout_version', '1.0.0')),

			// Config
			array('config.add', array('shout_avatar', 1)),
			array('config.add', array('shout_avatar_height', 20)),
			array('config.add', array('shout_avatar_robot', 1)),
			array('config.add', array('shout_avatar_user', 1)),
			array('config.add', array('shout_avatar_img', 'no_avatar.gif')),
			array('config.add', array('shout_avatar_img_robot', 'avatar_robot.png')),
			array('config.add', array('shout_bar_option', 1)),
			array('config.add', array('shout_bar_option_pop', 1)),
			array('config.add', array('shout_bar_option_priv', 1)),
			array('config.add', array('shout_bbcode', '')),
			array('config.add', array('shout_bbcode_user', 'list, code, quote')),
			array('config.add', array('shout_bbcode_size', 200)),
			array('config.add', array('shout_birthday', 1)),
			array('config.add', array('shout_birthday_exclude', '')),
			array('config.add', array('shout_birthday_priv', 1)),
			array('config.add', array('shout_button_background', 0)),
			array('config.add', array('shout_button_background_pop', 0)),
			array('config.add', array('shout_button_background_priv', 0)),
			array('config.add', array('shout_color_robot', 'FF8040')),
			array('config.add', array('shout_color_message', '004080')),
			array('config.add', array('shout_color_background', 'orange')),
			array('config.add', array('shout_color_background_sub', 'transparent')),
			array('config.add', array('shout_color_background_pop', 'blue')),
			array('config.add', array('shout_color_background_priv', 'subsilver3')),
			array('config.add', array('shout_cron_hour', '09')),
			array('config.add', array('shout_cron_run', '', true)),
			array('config.add', array('shout_dateformat', '|d M| H:i')),
			array('config.add', array('shout_defil', 1)),
			array('config.add', array('shout_del_acp', 0)),
			array('config.add', array('shout_del_auto', 0)),
			array('config.add', array('shout_del_purge', 0)),
			array('config.add', array('shout_del_user', 0)),
			array('config.add', array('shout_del_acp_priv', 0)),
			array('config.add', array('shout_del_auto_priv', 0)),
			array('config.add', array('shout_del_purge_priv', 0)),
			array('config.add', array('shout_del_user_priv', 0)),
			array('config.add', array('shout_delete_robot', 1)),
			array('config.add', array('shout_edit_robot', 1)),
			array('config.add', array('shout_edit_robot_priv', 1)),
			array('config.add', array('shout_enable_robot', 1)),
			array('config.add', array('shout_exclude_forums', '')),
			array('config.add', array('shout_forum', 1)),
			array('config.add', array('shout_height', 200)),
			array('config.add', array('shout_hello', 1)),
			array('config.add', array('shout_hello_priv', 1)),
			array('config.add', array('shout_ie_nr', 6)),
			array('config.add', array('shout_ie_nr_pop', 7)),
			array('config.add', array('shout_ie_nr_priv', 12)),
			array('config.add', array('shout_inactiv_anony', 15)),
			array('config.add', array('shout_inactiv_member', 45)),
			array('config.add', array('shout_index', 1)),
			array('config.add', array('shout_last_run', time(), true)),
			array('config.add', array('shout_last_run_priv', time(), true)),
			array('config.add', array('shout_log_cron', 1)),
			array('config.add', array('shout_log_cron_priv', 1)),
			array('config.add', array('shout_max_post_chars', 250)),
			array('config.add', array('shout_max_posts_on', 150)),
			array('config.add', array('shout_max_posts', 0)),
			array('config.add', array('shout_max_posts_priv', 0)),
			array('config.add', array('shout_max_posts_on_priv', 150)),
			array('config.add', array('shout_newest', 1)),
			array('config.add', array('shout_newest_priv', 1)),
			array('config.add', array('shout_non_ie_nr', 25)),
			array('config.add', array('shout_non_ie_height_priv', 410)),
			array('config.add', array('shout_non_ie_nr_priv', 25)),
			array('config.add', array('shout_non_ie_height_pop', 220)),
			array('config.add', array('shout_non_ie_nr_pop', 25)),
			array('config.add', array('shout_nr', 0)),
			array('config.add', array('shout_nr_acp', 20)),
			array('config.add', array('shout_nr_priv', 0)),
			array('config.add', array('shout_nr_log_priv', 0)),
			array('config.add', array('shout_on_cron', 0)),
			array('config.add', array('shout_on_cron_priv', 0)),
			array('config.add', array('shout_page_exclude', 'gallery||video')),
			array('config.add', array('shout_pagin_option', 0)),
			array('config.add', array('shout_pagin_option_pop', 0)),
			array('config.add', array('shout_pagin_option_priv', 0)),
			array('config.add', array('shout_panel', 1)),
			array('config.add', array('shout_panel_all', 1)),
			array('config.add', array('shout_panel_auto', 0)),
			array('config.add', array('shout_panel_img', 'media_start.png')),
			array('config.add', array('shout_panel_exit_img', 'left.png')),
			array('config.add', array('shout_panel_height', 300)),
			array('config.add', array('shout_panel_width', 720)), 
			array('config.add', array('shout_popup_height', 310)),
			array('config.add', array('shout_popup_width', 720)),
			array('config.add', array('shout_position_forum', 1)),
			array('config.add', array('shout_position_index', 1)),
			array('config.add', array('shout_position_topic', 1)),
			array('config.add', array('shout_post_robot', 1)),
			array('config.add', array('shout_post_robot_priv', 1)),
			array('config.add', array('shout_prez_form', 0)),
			array('config.add', array('shout_prune', 0)),
			array('config.add', array('shout_prune_priv', 0)),
			array('config.add', array('shout_rep_robot', 1)),
			array('config.add', array('shout_rep_robot_priv', 1)),
			array('config.add', array('shout_robot_choice', '5')),
			array('config.add', array('shout_robot_choice_priv', '1, 5')),
			array('config.add', array('shout_rules', 1)),
			array('config.add', array('shout_see_buttons', 1)),
			array('config.add', array('shout_see_buttons_left', 1)),
			array('config.add', array('shout_sessions', 1)),
			array('config.add', array('shout_sessions_priv', 0)),
			array('config.add', array('shout_sessions_time', 120)),
			array('config.add', array('shout_sessions_bots', 0)),
			array('config.add', array('shout_sessions_bots_priv', 0)),
			array('config.add', array('shout_sound_on', 1)),
			array('config.add', array('shout_sound_add', 'crapaud.mp3')),
			array('config.add', array('shout_sound_del', 'effect37.mp3')),
			array('config.add', array('shout_sound_edit', 'cat.mp3')),
			array('config.add', array('shout_sound_error', 'chimpanze.mp3')),
			array('config.add', array('shout_sound_new', 'discretion.mp3')),
			array('config.add', array('shout_sound_new_priv', 'bell1.mp3')),
			array('config.add', array('shout_temp_users', 5)),
			array('config.add', array('shout_temp_anonymous', 15)),
			array('config.add', array('shout_time', time())),
			array('config.add', array('shout_time_priv', time())),
			array('config.add', array('shout_title', 'Breizh Shoutbox')),
			array('config.add', array('shout_title_priv', 'Private Shoutbox')),
			array('config.add', array('shout_topic', 1)),
			array('config.add', array('shout_width_post', 350)),
			array('config.add', array('shout_width_post_pop', 300)),
			array('config.add', array('shout_width_post_priv', 350)),
			array('config.add', array('shout_width_post_sub', 100)),

			// Add admin permissions
			array('permission.add', array('a_shout_manage', true)),
			array('permission.add', array('a_shout_priv', true)),

			// Add moderator permissions
			array('permission.add', array('m_shout_delete', true)),
			array('permission.add', array('m_shout_edit_mod', true)),
			array('permission.add', array('m_shout_info', true)),
			array('permission.add', array('m_shout_personal', true)),
			array('permission.add', array('m_shout_purge', true)),
			array('permission.add', array('m_shout_robot', true)),

			// Add user permissions
			array('permission.add', array('u_shout_bbcode', true)),
			array('permission.add', array('u_shout_bbcode_change', true)),
			array('permission.add', array('u_shout_chars', true)),
			array('permission.add', array('u_shout_color', true)),
			array('permission.add', array('u_shout_delete_s', true)),
			array('permission.add', array('u_shout_edit', true)),
			array('permission.add', array('u_shout_hide', true)),
			array('permission.add', array('u_shout_ignore_flood', true)),
			array('permission.add', array('u_shout_image', true)),
			array('permission.add', array('u_shout_inactiv', true)),
			array('permission.add', array('u_shout_info_s', true)),
			array('permission.add', array('u_shout_lateral', true)),
			array('permission.add', array('u_shout_limit_post', true)),
			array('permission.add', array('u_shout_popup', true)),
			array('permission.add', array('u_shout_post', true)),
			array('permission.add', array('u_shout_post_inp', true)),
			array('permission.add', array('u_shout_priv', true)),
			array('permission.add', array('u_shout_smilies', true)),
			array('permission.add', array('u_shout_view', true)),

			// Set permissions administration
			array('permission.permission_set', array('ADMINISTRATORS', 'a_shout_manage', 'group')),
			array('permission.permission_set', array('ADMINISTRATORS', 'a_shout_priv', 'group')),

			// Set permissions moderation
			array('permission.permission_set', array('ADMINISTRATORS', 'm_shout_delete', 'group')),
			array('permission.permission_set', array('ADMINISTRATORS', 'm_shout_edit_mod', 'group')),
			array('permission.permission_set', array('ADMINISTRATORS', 'm_shout_info', 'group')),
			array('permission.permission_set', array('ADMINISTRATORS', 'm_shout_personal', 'group')),
			array('permission.permission_set', array('ADMINISTRATORS', 'm_shout_purge', 'group')),
			array('permission.permission_set', array('ADMINISTRATORS', 'm_shout_robot', 'group')),
			array('permission.permission_set', array('GLOBAL_MODERATORS', 'm_shout_delete', 'group')),
			array('permission.permission_set', array('GLOBAL_MODERATORS', 'm_shout_edit_mod', 'group')),
			array('permission.permission_set', array('GLOBAL_MODERATORS', 'm_shout_info', 'group')),
			array('permission.permission_set', array('GLOBAL_MODERATORS', 'm_shout_personal', 'group')),
			array('permission.permission_set', array('GLOBAL_MODERATORS', 'm_shout_purge', 'group')),
			array('permission.permission_set', array('GLOBAL_MODERATORS', 'm_shout_robot', 'group')),

			// Set permissions users
			array('permission.permission_set', array('REGISTERED', 'u_shout_bbcode', 'group')),
			array('permission.permission_set', array('REGISTERED', 'u_shout_bbcode_change', 'group')),
			array('permission.permission_set', array('REGISTERED', 'u_shout_chars', 'group')),
			array('permission.permission_set', array('REGISTERED', 'u_shout_color', 'group')),
			array('permission.permission_set', array('REGISTERED', 'u_shout_edit', 'group')),
			array('permission.permission_set', array('REGISTERED', 'u_shout_hide', 'group')),
			array('permission.permission_set', array('REGISTERED', 'u_shout_image', 'group')),
			array('permission.permission_set', array('REGISTERED', 'u_shout_info_s', 'group')),
			array('permission.permission_set', array('REGISTERED', 'u_shout_lateral', 'group')),
			array('permission.permission_set', array('REGISTERED', 'u_shout_popup', 'group')),
			array('permission.permission_set', array('REGISTERED', 'u_shout_post', 'group')),
			array('permission.permission_set', array('REGISTERED', 'u_shout_post_inp', 'group')),
			array('permission.permission_set', array('REGISTERED', 'u_shout_smilies', 'group')),
			array('permission.permission_set', array('REGISTERED', 'u_shout_view', 'group')),

			array('permission.permission_set', array('ADMINISTRATORS', 'u_shout_delete_s', 'group')),
			array('permission.permission_set', array('ADMINISTRATORS', 'u_shout_inactiv', 'group')),
			array('permission.permission_set', array('ADMINISTRATORS', 'u_shout_limit_post', 'group')),
			array('permission.permission_set', array('ADMINISTRATORS', 'u_shout_ignore_flood', 'group')),
			array('permission.permission_set', array('ADMINISTRATORS', 'u_shout_bbcode', 'group')),
			array('permission.permission_set', array('ADMINISTRATORS', 'u_shout_bbcode_change', 'group')),
			array('permission.permission_set', array('ADMINISTRATORS', 'u_shout_chars', 'group')),
			array('permission.permission_set', array('ADMINISTRATORS', 'u_shout_color', 'group')),
			array('permission.permission_set', array('ADMINISTRATORS', 'u_shout_edit', 'group')),
			array('permission.permission_set', array('ADMINISTRATORS', 'u_shout_hide', 'group')),
			array('permission.permission_set', array('ADMINISTRATORS', 'u_shout_image', 'group')),
			array('permission.permission_set', array('ADMINISTRATORS', 'u_shout_info_s', 'group')),
			array('permission.permission_set', array('ADMINISTRATORS', 'u_shout_lateral', 'group')),
			array('permission.permission_set', array('ADMINISTRATORS', 'u_shout_popup', 'group')),
			array('permission.permission_set', array('ADMINISTRATORS', 'u_shout_post', 'group')),
			array('permission.permission_set', array('ADMINISTRATORS', 'u_shout_post_inp', 'group')),
			array('permission.permission_set', array('ADMINISTRATORS', 'u_shout_smilies', 'group')),
			array('permission.permission_set', array('ADMINISTRATORS', 'u_shout_view', 'group')),
			array('permission.permission_set', array('ADMINISTRATORS', 'u_shout_priv', 'group')),

			array('permission.permission_set', array('GLOBAL_MODERATORS', 'u_shout_delete_s', 'group')),
			array('permission.permission_set', array('GLOBAL_MODERATORS', 'u_shout_inactiv', 'group')),
			array('permission.permission_set', array('GLOBAL_MODERATORS', 'u_shout_limit_post', 'group')),
			array('permission.permission_set', array('GLOBAL_MODERATORS', 'u_shout_ignore_flood', 'group')),
			array('permission.permission_set', array('GLOBAL_MODERATORS', 'u_shout_bbcode', 'group')),
			array('permission.permission_set', array('GLOBAL_MODERATORS', 'u_shout_bbcode_change', 'group')),
			array('permission.permission_set', array('GLOBAL_MODERATORS', 'u_shout_chars', 'group')),
			array('permission.permission_set', array('GLOBAL_MODERATORS', 'u_shout_color', 'group')),
			array('permission.permission_set', array('GLOBAL_MODERATORS', 'u_shout_edit', 'group')),
			array('permission.permission_set', array('GLOBAL_MODERATORS', 'u_shout_hide', 'group')),
			array('permission.permission_set', array('GLOBAL_MODERATORS', 'u_shout_image', 'group')),
			array('permission.permission_set', array('GLOBAL_MODERATORS', 'u_shout_info_s', 'group')),
			array('permission.permission_set', array('GLOBAL_MODERATORS', 'u_shout_lateral', 'group')),
			array('permission.permission_set', array('GLOBAL_MODERATORS', 'u_shout_popup', 'group')),
			array('permission.permission_set', array('GLOBAL_MODERATORS', 'u_shout_post', 'group')),
			array('permission.permission_set', array('GLOBAL_MODERATORS', 'u_shout_post_inp', 'group')),
			array('permission.permission_set', array('GLOBAL_MODERATORS', 'u_shout_smilies', 'group')),
			array('permission.permission_set', array('GLOBAL_MODERATORS', 'u_shout_view', 'group')),

			array('permission.permission_set', array('NEWLY_REGISTERED', 'u_shout_view', 'group')),
			array('permission.permission_set', array('NEWLY_REGISTERED', 'u_shout_lateral', 'group')),
			array('permission.permission_set', array('NEWLY_REGISTERED', 'u_shout_post', 'group')),
			array('permission.permission_set', array('NEWLY_REGISTERED', 'u_shout_popup', 'group')),
			array('permission.permission_set', array('NEWLY_REGISTERED', 'u_shout_smilies', 'group')),
			array('permission.permission_set', array('NEWLY_REGISTERED', 'u_shout_bbcode', 'group')),
			array('permission.permission_set', array('GUESTS', 'u_shout_view', 'group')),
			array('permission.permission_set', array('GUESTS', 'u_shout_lateral', 'group')),
			array('permission.permission_set', array('BOTS', 'u_shout_view', 'group')),

			// Add ACP extension category
			array('module.add', array('acp', '', array(
				'module_langname'	=> 'ACP_SHOUTBOX',
			))),
			array('module.add', array('acp', 'ACP_SHOUTBOX', array(
				'module_langname'	=> 'ACP_SHOUT_GENERAL_CAT',
			))),
			array('module.add', array('acp', 'ACP_SHOUTBOX', array(
				'module_langname'	=> 'ACP_SHOUT_PRIVATE_CAT',
			))),
			array('module.add', array('acp', 'ACP_SHOUTBOX', array(
				'module_langname'	=> 'ACP_SHOUT_POPUP_CAT',
			))),
			array('module.add', array('acp', 'ACP_SHOUTBOX', array(
				'module_langname'	=> 'ACP_SHOUT_SMILIES_CAT',
			))),
			array('module.add', array('acp', 'ACP_SHOUTBOX', array(
				'module_langname'	=> 'ACP_SHOUT_ROBOT_CAT'
			))),
			array('module.add', array('acp', 'ACP_SHOUT_GENERAL_CAT', array(
				'module_basename'	=> '\sylver35\breizhshoutbox\acp\main_module',
				'module_langname'	=> 'ACP_SHOUT_CONFIGS',
				'module_mode'		=> 'configs',
				'module_auth'		=> 'ext_sylver35/breizhshoutbox && acl_a_shout_manage',
			))),
			array('module.add', array('acp', 'ACP_SHOUT_GENERAL_CAT', array(
				'module_basename'	=> '\sylver35\breizhshoutbox\acp\main_module',
				'module_langname'	=> 'ACP_SHOUT_CONFIG_GEN',
				'module_mode'		=> 'config_gen',
				'module_auth'		=> 'ext_sylver35/breizhshoutbox && acl_a_shout_manage',
			))),
			array('module.add', array('acp', 'ACP_SHOUT_GENERAL_CAT', array(
				'module_basename'	=> '\sylver35\breizhshoutbox\acp\main_module',
				'module_langname'	=> 'ACP_SHOUT_OVERVIEW',
				'module_mode'		=> 'overview',
				'module_auth'		=> 'ext_sylver35/breizhshoutbox && acl_a_shout_manage',
			))),
			array('module.add', array('acp', 'ACP_SHOUT_GENERAL_CAT', array(
				'module_basename'	=> '\sylver35\breizhshoutbox\acp\main_module',
				'module_langname'	=> 'ACP_SHOUT_RULES',
				'module_mode'		=> 'rules',
				'module_auth'		=> 'ext_sylver35/breizhshoutbox && acl_a_shout_manage',
			))),
			array('module.add', array('acp', 'ACP_SHOUT_PRIVATE_CAT', array(
				'module_basename'	=> '\sylver35\breizhshoutbox\acp\main_module',
				'module_langname'	=> 'ACP_SHOUT_CONFIG_PRIV',
				'module_mode'		=> 'config_priv',
				'module_auth'		=> 'ext_sylver35/breizhshoutbox && acl_a_shout_priv',
			))),
			array('module.add', array('acp', 'ACP_SHOUT_PRIVATE_CAT', array(
				'module_basename'	=> '\sylver35\breizhshoutbox\acp\main_module',
				'module_langname'	=> 'ACP_SHOUT_PRIVATE',
				'module_mode'		=> 'private',
				'module_auth'		=> 'ext_sylver35/breizhshoutbox && acl_a_shout_priv',
			))),
			array('module.add', array('acp', 'ACP_SHOUT_POPUP_CAT', array(
				'module_basename'	=> '\sylver35\breizhshoutbox\acp\main_module',
				'module_langname'	=> 'ACP_SHOUT_POPUP',
				'module_mode'		=> 'popup',
				'module_auth'		=> 'ext_sylver35/breizhshoutbox && acl_a_shout_manage',
			))),
			array('module.add', array('acp', 'ACP_SHOUT_POPUP_CAT', array(
				'module_basename'	=> '\sylver35\breizhshoutbox\acp\main_module',
				'module_langname'	=> 'ACP_SHOUT_PANEL',
				'module_mode'		=> 'panel',
				'module_auth'		=> 'ext_sylver35/breizhshoutbox && acl_a_shout_manage',
			))),
			array('module.add', array('acp', 'ACP_SHOUT_SMILIES_CAT', array(
				'module_basename'	=> '\sylver35\breizhshoutbox\acp\main_module',
				'module_langname'	=> 'ACP_SHOUT_SMILIES',
				'module_mode'		=> 'smilies',
				'module_auth'		=> 'ext_sylver35/breizhshoutbox && acl_a_shout_manage',
			))),
			array('module.add', array('acp', 'ACP_SHOUT_ROBOT_CAT', array(
				'module_basename'	=> '\sylver35\breizhshoutbox\acp\main_module',
				'module_langname'	=> 'ACP_SHOUT_ROBOT',
				'module_mode'		=> 'robot',
				'module_auth'		=> 'ext_sylver35/breizhshoutbox && acl_a_shout_manage',
			))),

			// Custon function for first message
			array('custom', array(
				array(&$this, 'add_first_messages')
			)),
		);
	}

	public function update_schema()
	{
		return array(
			'add_tables' => array(
				$this->table_prefix . 'shoutbox' => array(
					'COLUMNS'	=> array(
						'shout_id'					=> array('UINT', null, 'auto_increment'),
						'shout_time'				=> array('UINT:11', 0),
						'shout_user_id'				=> array('UINT', 0),
						'shout_ip'					=> array('VCHAR:40', ''),
						'shout_text'				=> array('MTEXT_UNI', ''),
						'shout_text2'				=> array('MTEXT_UNI', null),
						'shout_bbcode_uid'			=> array('VCHAR:8', ''),
						'shout_bbcode_bitfield'		=> array('VCHAR:255', ''),
						'shout_bbcode_flags'		=> array('VCHAR:8', ''),
						'shout_robot'				=> array('UINT:10', 0),
						'shout_robot_user'			=> array('UINT:10', 0),
						'shout_forum'				=> array('UINT:6', 0),
						'shout_info'				=> array('UINT:4', 0),
						'shout_info_nb'				=> array('UINT:8', 0),
						'shout_inp'					=> array('UINT:8', 0),
					),
					'PRIMARY_KEY'	=> 'shout_id',
				),

				$this->table_prefix . 'shoutbox_priv' => array(
					'COLUMNS'	=> array(
						'shout_id'					=> array('UINT', null, 'auto_increment'),
						'shout_time'				=> array('UINT:11', 0),
						'shout_user_id'				=> array('UINT', 0),
						'shout_ip'					=> array('VCHAR:40', ''),
						'shout_text'				=> array('MTEXT_UNI', ''),
						'shout_text2'				=> array('MTEXT_UNI', null),
						'shout_bbcode_uid'			=> array('VCHAR:8', ''),
						'shout_bbcode_bitfield'		=> array('VCHAR:255', ''),
						'shout_bbcode_flags'		=> array('VCHAR:8', ''),
						'shout_robot'				=> array('UINT:10', 0),
						'shout_robot_user'			=> array('UINT:10', 0),
						'shout_forum'				=> array('UINT:6', 0),
						'shout_info'				=> array('UINT:4', 0),
						'shout_info_nb'				=> array('UINT:8', 0),
						'shout_inp'					=> array('UINT:8', 0),
					),
					'PRIMARY_KEY'	=> 'shout_id',
				),

				$this->table_prefix . 'shoutbox_rules' => array(
					'COLUMNS'	=> array(
						'id'						=> array('UINT', null, 'auto_increment'),
						'rules_lang'				=> array('VCHAR:30', ''),
						'rules_text'				=> array('MTEXT_UNI', ''),
						'rules_uid'					=> array('VCHAR:8', ''),
						'rules_bitfield'			=> array('VCHAR:255', ''),
						'rules_flags'				=> array('VCHAR:8', ''),
						'rules_text_priv'			=> array('MTEXT_UNI', ''),
						'rules_uid_priv'			=> array('VCHAR:8', ''),
						'rules_bitfield_priv'		=> array('VCHAR:255', ''),
						'rules_flags_priv'			=> array('VCHAR:8', ''),
					),
					'PRIMARY_KEY'	=> 'id',
				),
			),

			'add_columns' => array(
				$this->table_prefix . 'smilies' => array(
					'display_on_shout'	=> array('TINT:1', 1),
				),
				$this->table_prefix . 'users' => array(
					'shout_bbcode'		=> array('VCHAR:255', ''),
					'user_shout'		=> array('VCHAR:255', '{"user":2,"new":0,"new_priv":0,"error":0,"del":0,"add":0,"edit":0,"index":3,"forum":3,"topic":3}'),
					'user_shoutbox'		=> array('VCHAR:255', '{"bar":"N","pagin":"N","bar_pop":"N","pagin_pop":"N","bar_priv":"N","pagin_priv":"N","defil":"N","panel":"N","dateformat":""}'),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables' => array(
				$this->table_prefix . 'shoutbox',
				$this->table_prefix . 'shoutbox_priv',
				$this->table_prefix . 'shoutbox_rules',
			),
			'drop_columns' => array(
				$this->table_prefix . 'users' => array(
					'shout_bbcode',
					'user_shout',
					'user_shoutbox',
				),
				$this->table_prefix . 'smilies' => array(
					'display_on_shout',
				),
			),
		);
	}

	public function add_first_messages()
	{
		if ($this->db_tools->sql_table_exists($this->table_prefix . 'shoutbox') && $this->db_tools->sql_table_exists($this->table_prefix . 'shoutbox_priv'))
		{
			$sql_data = array(
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
			);

			$sql = 'INSERT INTO ' . $this->table_prefix . 'shoutbox ' . $this->db->sql_build_array('INSERT', $sql_data);
			$this->db->sql_query($sql);

			$sql = 'INSERT INTO ' . $this->table_prefix . 'shoutbox_priv ' . $this->db->sql_build_array('INSERT', $sql_data);
			$this->db->sql_query($sql);
		}
	}
}
