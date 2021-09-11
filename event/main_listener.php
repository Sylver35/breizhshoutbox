<?php
/**
*
* @package Breizh Shoutbox Extension
* @copyright (c) 2018-2021 Sylver35  https://breizhcode.com
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
	/* @var \sylver35\breizhshoutbox\core\shoutbox */
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

	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup'							=> 'load_language_on_setup',
			'core.page_header'							=> 'add_page_header',
			'core.session_create_after'					=> 'charge_post_session_shout',
			'core.index_modify_page_title'				=> 'shout_display',
			'core.viewforum_modify_page_title'			=> 'shout_display',
			'core.viewtopic_modify_page_title'			=> 'shout_display',
			'core.submit_post_end'						=> 'shout_advert_post',
			'core.user_add_after'						=> 'shout_add_newest_user',
			'core.display_custom_bbcodes_modify_sql'	=> 'remove_disallowed_bbcodes',
			'core.posting_modify_post_data'				=> 'shout_modify_post_data',
			'core.posting_modify_message_text'			=> 'shout_modify_post_before',
			'core.posting_modify_submit_post_before'	=> 'shout_modify_post_before_data',
			'core.posting_modify_submission_errors'		=> 'shout_submission_post_data',
			'core.posting_modify_template_vars'			=> 'shout_modify_template_vars',
			'core.permissions'							=> 'shout_add_permissions',
			'core.delete_user_after'					=> 'shout_delete_user',
			'core.delete_topics_after_query'			=> 'shout_delete_topics',
			'core.delete_posts_after'					=> 'shout_delete_posts',
			'breizhcharts.add_song_after'				=> 'add_song_after',
			'breizhcharts.reset_all_notes'				=> 'reset_all_notes',
			'video.submit_new_video'					=> 'submit_new_video',
			'arcade.submit_new_score'					=> 'submit_new_score',
			'arcade.submit_new_urecord'					=> 'submit_new_urecord',
			'arcade.submit_new_record'					=> 'submit_new_record',
			'arcade.page_arcade_games'					=> 'shout_display',
			'arcade.page_arcade_list'					=> 'shout_display',
			'portal.handle'								=> 'shout_display',
		];
	}

	/**
	 * @param array $event
	 */
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'sylver35/breizhshoutbox',
			'lang_set' => ['shout', 'acp/info_acp_shoutbox'],
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * Add urls for shoutbox
	 */
	public function add_page_header()
	{
		$this->shoutbox->shout_panel();
		$this->shoutbox->shout_page_header();
	}

	/**
	 * Load the shoutbox
	 */
	public function shout_display()
	{
		$this->shoutbox->shout_display(2);
	}

	/**
	 * @param array $event
	 */
	public function charge_post_session_shout($event)
	{
		if ($this->user->data['is_bot'])
		{
			if ($this->config['shout_sessions_bots'] || $this->config['shout_sessions_bots_priv'])
			{
				$this->shoutbox->post_session_bot($event['session_data']);
			}
		}
		else if ($this->user->data['is_registered'])
		{
			if ($this->config['shout_sessions'] || $this->config['shout_sessions_priv'])
			{
				$this->shoutbox->post_session_shout($event['session_data']);
			}
		}
	}

	/**
	 * @param array $event
	 */
	public function shout_advert_post($event)
	{
		$hide_robot = (isset($event['data']['hide_robot'])) ? $event['data']['hide_robot'] : false;
		$forum_id = (int) $event['data']['forum_id'];

		if (!empty($this->config['shout_exclude_forums']))
		{
			$exclude = explode(',', $this->config['shout_exclude_forums']);
			if (in_array($forum_id, $exclude))
			{
				return;
			}
		}

		if (!$hide_robot && $this->config['shout_enable_robot'])
		{
			$this->shoutbox->advert_post_shoutbox($event, $forum_id);
		}
	}

	/**
	 * @param array $event
	 */
	public function shout_add_newest_user($event)
	{
		$this->shoutbox->shout_add_newest_user($event);
	}

	/**
	 * @param array $event
	 */
	public function remove_disallowed_bbcodes($event)
	{
		if (!preg_match("#posting|adm#i", $this->user->page['page_name']) && !empty($this->config['shout_bbcode']))
		{
			$event->offsetSet('sql_ary', $this->shoutbox->remove_disallowed_bbcodes($event['sql_ary']));
		}
	}

	/**
	 * @param array $event
	 */
	public function shout_modify_post_data($event)
	{
		$event['post_data'] = array_merge($event['post_data'], [
			'hide_robot'	=> false,
		]);
	}

	/**
	 * @param array $event
	 */
	public function shout_modify_post_before_data($event)
	{
		$event['data'] = array_merge($event['data'], [
			'hide_robot'	=> $event['post_data']['hide_robot'],
		]);
	}

	/**
	 * @param array $event
	 */
	public function shout_modify_post_before($event)
	{
		$event['post_data'] = array_merge($event['post_data'], [
			'hide_robot'	=> $this->request->variable('hide_robot', false),
		]);
	}

	/**
	 * @param array $event
	 */
	public function shout_submission_post_data($event)
	{
		$event['post_data'] = array_merge($event['post_data'], [
			'hide_robot'	=> $this->request->variable('hide_robot', false),
		]);
	}

	/**
	 * @param array $event
	 */
	public function shout_modify_template_vars($event)
	{
		$hide_robot = true;
		$hide_allowed = false;
		if (!empty($this->config['shout_exclude_forums']))
		{
			$exclude = explode(',', $this->config['shout_exclude_forums']);
			if (in_array($event['forum_id'], $exclude))
			{
				$hide_robot = false;
			}
		}

		if ($this->auth->acl_get('u_shout_hide') && $hide_robot)
		{
			if ($event['mode'] == 'edit')
			{
				$hide_allowed = ($this->config['shout_edit_robot'] || $this->config['shout_edit_robot_priv']) ? true : false;
			}
			else
			{
				$hide_allowed = true;
			}
		}

		$event['page_data'] = array_merge($event['page_data'], [
			'S_SHOUT_HIDE_CHECKED'		=> ($event['post_data']['hide_robot']) ? ' checked="checked"' : '',
			'S_SHOUT_HIDE_ALLOWED'		=> $hide_allowed,
		]);
	}

	/**
	 * @param array $event
	 */
	public function shout_add_permissions($event)
	{
		$event['categories'] = array_merge($event['categories'], [
			'shoutbox' =>	'ACL_CAT_SHOUT',
		]);

		$event['permissions'] = array_merge($event['permissions'], [
			'a_shout_manage'	=> [
				'lang' => 'ACL_A_SHOUT_MANAGE',
				'cat' => 'shoutbox',
			],
			'a_shout_priv'			=> [
				'lang' => 'ACL_A_SHOUT_PRIV',
				'cat' => 'shoutbox',
			],
			'm_shout_delete'		=> [
				'lang' => 'ACL_M_SHOUT_DELETE',
				'cat' => 'shoutbox',
			],
			'm_shout_edit_mod'		=> [
				'lang' => 'ACL_M_SHOUT_EDIT_MOD',
				'cat' => 'shoutbox',
			],
			'm_shout_info'			=> [
				'lang' => 'ACL_M_SHOUT_INFO',
				'cat' => 'shoutbox',
			],
			'm_shout_personal'		=> [
				'lang' => 'ACL_M_SHOUT_PERSONAL',
				'cat' => 'shoutbox',
			],
			'm_shout_robot'			=> [
				'lang' => 'ACL_M_SHOUT_ROBOT',
				'cat' => 'shoutbox',
			],
			'u_shout_bbcode'		=> [
				'lang' => 'ACL_U_SHOUT_BBCODE',
				'cat' => 'shoutbox',
			],
			'u_shout_bbcode_custom'	=> [
				'lang' => 'ACL_U_SHOUT_BBCODE_CUSTOM',
				'cat' => 'shoutbox',
			],
			'u_shout_bbcode_change'	=> [
				'lang' => 'ACL_U_SHOUT_BBCODE_CHANGE',
				'cat' => 'shoutbox',
			],
			'u_shout_chars'			=> [
				'lang' => 'ACL_U_SHOUT_CHARS',
				'cat' => 'shoutbox',
			],
			'u_shout_color'			=> [
				'lang' => 'ACL_U_SHOUT_COLOR',
				'cat' => 'shoutbox',
			],
			'u_shout_delete_s'		=> [
				'lang' => 'ACL_U_SHOUT_DELETE_S',
				'cat' => 'shoutbox',
			],
			'u_shout_edit'			=> [
				'lang' => 'ACL_U_SHOUT_EDIT',
				'cat' => 'shoutbox',
			],
			'u_shout_hide'			=> [
				'lang' => 'ACL_U_SHOUT_HIDE',
				'cat' => 'shoutbox',
			],
			'u_shout_ignore_flood'	=> [
				'lang' => 'ACL_U_SHOUT_IGNORE_FLOOD',
				'cat' => 'shoutbox',
			],
			'u_shout_image'			=> [
				'lang' => 'ACL_U_SHOUT_IMAGE',
				'cat' => 'shoutbox',
			],
			'u_shout_inactiv'		=> [
				'lang' => 'ACL_U_SHOUT_INACTIV',
				'cat' => 'shoutbox',
			],
			'u_shout_info_s'		=> [
				'lang' => 'ACL_U_SHOUT_INFO_S',
				'cat' => 'shoutbox',
			],
			'u_shout_lateral'		=> [
				'lang' => 'ACL_U_SHOUT_LATERAL',
				'cat' => 'shoutbox',
			],
			'u_shout_limit_post'	=> [
				'lang' => 'ACL_U_SHOUT_LIMIT_POST',
				'cat' => 'shoutbox',
			],
			'u_shout_popup'			=> [
				'lang' => 'ACL_U_SHOUT_POPUP',
				'cat' => 'shoutbox',
			],
			'u_shout_post'			=> [
				'lang' => 'ACL_U_SHOUT_POST',
				'cat' => 'shoutbox',
			],
			'u_shout_post_inp'		=> [
				'lang' => 'ACL_U_SHOUT_POST_INP',
				'cat' => 'shoutbox',
			],
			'u_shout_priv'			=> [
				'lang' => 'ACL_U_SHOUT_PRIV',
				'cat' => 'shoutbox',
			],
			'u_shout_smilies'		=> [
				'lang' => 'ACL_U_SHOUT_SMILIES',
				'cat' => 'shoutbox',
			],
			'u_shout_view'			=> [
				'lang' => 'ACL_U_SHOUT_VIEW',
				'cat' => 'shoutbox',
			],
		]);
	}

	/**
	 * @param array $event
	 */
	public function shout_delete_user($event)
	{
		if ($event['mode'] == 'remove')
		{
			foreach ($event['user_ids'] as $user_id)
			{
				$this->shoutbox->delete_user_messages((int) $user_id);
			}
		}
	}

	/**
	 * @param array $event
	 */
	public function shout_delete_topics($event)
	{
		foreach ($event['topic_ids'] as $topic_id)
		{
			$this->shoutbox->shout_delete_topic((int) $topic_id);
		}
	}

	/**
	 * @param array $event
	 */
	public function shout_delete_posts($event)
	{
		foreach ($event['post_ids'] as $post_id)
		{
			$this->shoutbox->shout_delete_post((int) $post_id);
		}
	}

	/**
	 * @param array $event
	 */
	public function add_song_after($event)
	{
		$this->shoutbox->add_song_after($event);
	}

	/**
	 * @param array $event
	 */
	public function reset_all_notes($event)
	{
		$this->shoutbox->reset_all_notes($event);
	}

	/**
	 * @param array $event
	 */
	public function submit_new_video($event)
	{
		$this->shoutbox->submit_new_video($event);
	}

	/**
	 * @param array $event
	 */
	public function submit_new_score($event)
	{
		if ($this->config['shout_arcade_new'])
		{
			$muser = ((int) $event['muserid'] === 0) ? true : false;
			$this->shoutbox->submit_arcade_score($event, 36, $muser);
		}
	}

	/**
	 * @param array $event
	 */
	public function submit_new_urecord($event)
	{
		if ($this->config['shout_arcade_urecord'] && $event['gamescore'] > 0)
		{
			$muser = ((int) $event['muserid'] === 0) ? true : false;
			$this->shoutbox->submit_arcade_score($event, 37, $muser);
		}
	}

	/**
	 * @param array $event
	 */
	public function submit_new_record($event)
	{
		if ($this->config['shout_arcade_record'])
		{
			$this->shoutbox->submit_arcade_score($event, 38, false);
		}
	}
}
