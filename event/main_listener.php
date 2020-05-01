<?php
/**
*
* @package Breizh Shoutbox Extension
* @copyright (c) 2018-2020 Sylver35  https://breizhcode.com
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace sylver35\breizhshoutbox\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use sylver35\breizhshoutbox\core\shoutbox;
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\auth\auth;
use phpbb\user;
use phpbb\language\language;

class main_listener implements EventSubscriberInterface
{
	/* @var \sylver35\breizhshoutbox\core\breizhshoutbox */
	protected $shoutbox;

	/** @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\controller\helper */
	protected $helper;

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

	/**
	 * Constructor
	 *
	 */
	public function __construct(shoutbox $shoutbox, config $config, helper $helper, request $request, template $template, auth $auth, user $user, language $language)
	{
		$this->shoutbox = $shoutbox;
		$this->config = $config;
		$this->helper = $helper;
		$this->request = $request;
		$this->template = $template;
		$this->auth = $auth;
		$this->user = $user;
		$this->language = $language;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'							=> 'load_language_on_setup',
			'core.page_header'							=> 'add_page_header',
			'core.session_create_after'					=> 'charge_post_session_shout',
			'core.index_modify_page_title'				=> 'charge_shout_display',
			'core.viewforum_modify_page_title'			=> 'charge_shout_display',
			'core.viewtopic_modify_page_title'			=> 'charge_shout_display',
			'core.submit_post_end'						=> 'charge_advert_post',
			'core.user_add_after'						=> 'charge_add_newest_user',
			'core.display_custom_bbcodes_modify_sql'	=> 'remove_disallowed_bbcodes',
			'core.posting_modify_post_data'				=> 'shout_modify_post_data',
			'core.posting_modify_message_text'			=> 'shout_modify_post_before',
			'core.posting_modify_submit_post_before'	=> 'shout_modify_post_before_data',
			'core.posting_modify_submission_errors'		=> 'shout_submission_post_data',
			'core.posting_modify_template_vars'			=> 'shout_modify_template_vars',
			'core.permissions'							=> 'add_permissions',
			'video.submit_new_video'					=> 'submit_new_video',
			'arcade.submit_new_score'					=> 'submit_new_score',
			'arcade.submit_new_urecord'					=> 'submit_new_urecord',
			'arcade.submit_new_record'					=> 'submit_new_record',
			'arcade.page_arcade_games'					=> 'charge_shout_display',
			'arcade.page_arcade_list'					=> 'charge_shout_display',
		);
	}

	/**
	 * @param \phpbb\event\data $event
	 */
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'sylver35/breizhshoutbox',
			'lang_set' => array('shout', 'acp/info_acp_shoutbox'),
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * Add urls for shoutbox
	 */
	public function add_page_header()
	{
		$data = $this->shoutbox->get_version();
		$this->template->assign_vars(array(
			'SHOUT_POPUP_H'			=> $this->config['shout_popup_width'],
			'SHOUT_POPUP_W'			=> $this->config['shout_popup_height'],
			'U_SHOUT_PRIV_PAGE'		=> $this->auth->acl_get('u_shout_priv') ? $this->helper->route('sylver35_breizhshoutbox_private') : '',
			'U_SHOUT_POPUP'			=> $this->auth->acl_get('u_shout_popup') ? $this->helper->route('sylver35_breizhshoutbox_popup') : '',
			'U_SHOUT_CONFIG'		=> $this->auth->acl_get('u_shout_post') ? $this->helper->route('sylver35_breizhshoutbox_configshout') : '',
			'U_SHOUT_AJAX'			=> $this->helper->route('sylver35_breizhshoutbox_ajax', array('mode' => 'display_smilies')),
			'SHOUT_COPYRIGHT'		=> $this->language->lang('SHOUTBOX_VER', $data['version']),
		));
		$this->shoutbox->shout_panel();
	}

	public function charge_shout_display()
	{
		$this->shoutbox->shout_display(2);
	}

	/**
	 * @param \phpbb\event\data $event
	 */
	public function charge_post_session_shout($event)
	{
		$this->shoutbox->post_session_shout($event['session_data']);
	}

	/**
	 * @param \phpbb\event\data $event
	 */
	public function charge_advert_post($event)
	{
		$this->shoutbox->advert_post_shoutbox($event);
	}

	/**
	 * @param \phpbb\event\data $event
	 */
	public function charge_add_newest_user($event)
	{
		$this->shoutbox->shout_add_newest_user($event);
	}

	/**
	 * @param \phpbb\event\data $event
	 */
	public function remove_disallowed_bbcodes($event)
	{
		if (!preg_match("#posting|ucp|mcp|adm#i", $this->user->page['page_name']) && !empty($this->config['shout_bbcode']))
		{
			$event->offsetSet('sql_ary', $this->shoutbox->remove_disallowed_bbcodes($event['sql_ary']));
		}
	}

	/**
	 * @param \phpbb\event\data $event
	 */
	public function shout_modify_post_data($event)
	{
		$event['post_data'] = array_merge($event['post_data'], array(
			'hide_robot'	=> false,
		));
	}

	/**
	 * @param \phpbb\event\data $event
	 */
	public function shout_modify_post_before_data($event)
	{
		$event['data'] = array_merge($event['data'], array(
			'hide_robot'	=> $event['post_data']['hide_robot'],
		));
	}

	/**
	 * @param \phpbb\event\data $event
	 */
	public function shout_modify_post_before($event)
	{
		$event['post_data'] = array_merge($event['post_data'], array(
			'hide_robot'	=> $this->request->variable('hide_robot', false),
		));
	}

	/**
	 * @param \phpbb\event\data $event
	 */
	public function shout_submission_post_data($event)
	{
		$event['post_data'] = array_merge($event['post_data'], array(
			'hide_robot'	=> $this->request->variable('hide_robot', false),
		));
	}

	/**
	 * @param \phpbb\event\data $event
	 */
	public function shout_modify_template_vars($event)
	{
		$s_hide_robot = true;
		$shout_hide_allowed = false;
		$exclude_forums = array();
		if (!empty($this->config['shout_exclude_forums']))
		{
			$exclude_forums = explode(',', $this->config['shout_exclude_forums']);
			if (in_array($event['forum_id'], $exclude_forums))
			{
				$s_hide_robot = false;
			}
		}
		if ($this->auth->acl_get('u_shout_hide') && $s_hide_robot)
		{
			if ($event['mode'] == 'edit')
			{
				$shout_hide_allowed = ($this->config['shout_edit_robot'] || $this->config['shout_edit_robot_priv']) ? true : false;
			}
			else
			{
				$shout_hide_allowed = true;
			}
		}

		$event['page_data'] = array_merge($event['page_data'], array(
			'S_SHOUT_HIDE_CHECKED'		=> ($event['post_data']['hide_robot']) ? ' checked="checked"' : '',
			'S_SHOUT_HIDE_ALLOWED'		=> $shout_hide_allowed,
		));
	}

	/**
	 * @param \phpbb\event\data $event
	 */
	public function add_permissions($event)
	{
		$event['categories'] = array_merge($event['categories'], array(
			'shoutbox' =>	'ACL_CAT_SHOUT',
		));

		$event['permissions'] = array_merge($event['permissions'], array(
			'a_shout_manage'		=> array('lang' => 'ACL_A_SHOUT_MANAGE',		'cat' => 'misc'),
			'a_shout_priv'			=> array('lang' => 'ACL_A_SHOUT_PRIV',			'cat' => 'misc'),

			'm_shout_delete'		=> array('lang' => 'ACL_M_SHOUT_DELETE',		'cat' => 'shoutbox'),
			'm_shout_edit_mod'		=> array('lang' => 'ACL_M_SHOUT_EDIT_MOD',		'cat' => 'shoutbox'),
			'm_shout_info'			=> array('lang' => 'ACL_M_SHOUT_INFO',			'cat' => 'shoutbox'),
			'm_shout_personal'		=> array('lang' => 'ACL_M_SHOUT_PERSONAL',		'cat' => 'shoutbox'),
			'm_shout_purge'			=> array('lang' => 'ACL_M_SHOUT_PURGE',			'cat' => 'shoutbox'),
			'm_shout_robot'			=> array('lang' => 'ACL_M_SHOUT_ROBOT',			'cat' => 'shoutbox'),

			'u_shout_bbcode'		=> array('lang' => 'ACL_U_SHOUT_BBCODE',		'cat' => 'shoutbox'),
			'u_shout_bbcode_change'	=> array('lang' => 'ACL_U_SHOUT_BBCODE_CHANGE',	'cat' => 'shoutbox'),
			'u_shout_chars'			=> array('lang' => 'ACL_U_SHOUT_CHARS',			'cat' => 'shoutbox'),
			'u_shout_color'			=> array('lang' => 'ACL_U_SHOUT_COLOR',			'cat' => 'shoutbox'),
			'u_shout_delete_s'		=> array('lang' => 'ACL_U_SHOUT_DELETE_S',		'cat' => 'shoutbox'),
			'u_shout_edit'			=> array('lang' => 'ACL_U_SHOUT_EDIT',			'cat' => 'shoutbox'),
			'u_shout_hide'			=> array('lang' => 'ACL_U_SHOUT_HIDE',			'cat' => 'shoutbox'),
			'u_shout_ignore_flood'	=> array('lang' => 'ACL_U_SHOUT_IGNORE_FLOOD',	'cat' => 'shoutbox'),
			'u_shout_image'			=> array('lang' => 'ACL_U_SHOUT_IMAGE',			'cat' => 'shoutbox'),
			'u_shout_inactiv'		=> array('lang' => 'ACL_U_SHOUT_INACTIV',		'cat' => 'shoutbox'),
			'u_shout_info_s'		=> array('lang' => 'ACL_U_SHOUT_INFO_S',		'cat' => 'shoutbox'),
			'u_shout_lateral'		=> array('lang' => 'ACL_U_SHOUT_LATERAL',		'cat' => 'shoutbox'),
			'u_shout_limit_post'	=> array('lang' => 'ACL_U_SHOUT_LIMIT_POST',	'cat' => 'shoutbox'),
			'u_shout_popup'			=> array('lang' => 'ACL_U_SHOUT_POPUP',			'cat' => 'shoutbox'),
			'u_shout_post'			=> array('lang' => 'ACL_U_SHOUT_POST',			'cat' => 'shoutbox'),
			'u_shout_post_inp'		=> array('lang' => 'ACL_U_SHOUT_POST_INP',		'cat' => 'shoutbox'),
			'u_shout_priv'			=> array('lang' => 'ACL_U_SHOUT_PRIV',			'cat' => 'shoutbox'),
			'u_shout_smilies'		=> array('lang' => 'ACL_U_SHOUT_SMILIES',		'cat' => 'shoutbox'),
			'u_shout_view'			=> array('lang' => 'ACL_U_SHOUT_VIEW',			'cat' => 'shoutbox'),
		));
	}

	/**
	 * @param \phpbb\event\data $event
	 */
	public function submit_new_video($event)
	{
		$this->shoutbox->submit_new_video($event);
	}

	/**
	 * @param \phpbb\event\data $event
	 */
	public function submit_new_score($event)
	{
		if ($this->config['shout_arcade_new'])
		{
			$this->shoutbox->submit_arcade_score($event, 2);
		}
	}

	/**
	 * @param \phpbb\event\data $event
	 */
	public function submit_new_urecord($event)
	{
		if ($this->config['shout_arcade_urecord'] && $event['gamescore'] > 0)
		{
			$this->shoutbox->submit_arcade_score($event, 3);
		}
	}

	/**
	 * @param \phpbb\event\data $event
	 */
	public function submit_new_record($event)
	{
		if ($this->config['shout_arcade_record'])
		{
			$this->shoutbox->submit_arcade_score($event, 4);
		}
	}
}
