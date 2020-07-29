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
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\db\driver\driver_interface as db;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\auth\auth;
use phpbb\user;
use phpbb\language\language;
use phpbb\extension\manager;
use phpbb\path_helper;
use phpbb\event\dispatcher_interface as phpbb_dispatcher;

class ajax
{
	/* @var \sylver35\breizhshoutbox\core\shoutbox */
	protected $shoutbox;

	/** @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\controller\helper */
	protected $helper;

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

	/** @var \phpbb\extension\manager */
	protected $ext_manager;

	/** @var \phpbb\path_helper */
	protected $path_helper;

	/** @var \phpbb\event\dispatcher_interface */
	protected $phpbb_dispatcher;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string ext path web */
	protected $ext_path_web;

	/** @var string phpEx */
	protected $php_ext;

	/** @var string Custom form action */
	protected $u_action;

	/**
	 * Constructor
	 */
	public function __construct(shoutbox $shoutbox, config $config, helper $helper, db $db, request $request, template $template, auth $auth, user $user, language $language, manager $ext_manager, path_helper $path_helper, phpbb_dispatcher $phpbb_dispatcher, $root_path, $php_ext)
	{
		$this->shoutbox = $shoutbox;
		$this->config = $config;
		$this->helper = $helper;
		$this->db = $db;
		$this->request = $request;
		$this->template = $template;
		$this->auth = $auth;
		$this->user = $user;
		$this->language = $language;
		$this->ext_manager = $ext_manager;
		$this->path_helper = $path_helper;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Function construct_ajax
	 *
	 * @param string $mode Mode to switch
	 * @return void
	 */
	public function construct_ajax($mode)
	{
		$val = $this->shoutbox->manage_ajax($mode, $this->request->variable('sort', 2), $this->request->variable('user', 0));
		$response = new \phpbb\json_response;

		// We have our own error handling
		$this->db->sql_return_on_error(true);

		switch ($mode)
		{
			case 'smilies':
				$content = $this->shoutbox->shout_ajax_smilies();

				$response->send($content, true);
			break;

			case 'smilies_popup':
				$content = $this->shoutbox->shout_ajax_smilies_popup($this->request->variable('cat', -1));

				$response->send($content, true);
			break;

			case 'display_smilies':
				$content = $this->shoutbox->shout_ajax_display_smilies($this->request->variable('smiley', 0), $this->request->variable('display', 3));

				$response->send($content, true);
			break;

			case 'user_bbcode':
				$data = $this->shoutbox->shout_ajax_user_bbcode($this->request->variable('open', ''), $this->request->variable('close', ''), $this->request->variable('other', 0));
				
				$response->send($data, true);
			break;

			case 'charge_bbcode':
				$data = $this->shoutbox->shout_ajax_charge_bbcode($val['id']);

				$response->send($data, true);
			break;

			case 'online':
				$data = $this->shoutbox->shout_ajax_online();

				$response->send($data, true);
			break;

			case 'rules':
				$data = $this->shoutbox->shout_ajax_rules($val['priv']);

				$response->send($data, true);
			break;

			case 'preview_rules':
				$data = $this->shoutbox->shout_ajax_preview_rules($this->request->variable('content', '', true));

				$response->send($data, true);
			break;

			case 'date_format':
				$data = $this->shoutbox->shout_ajax_date_format($this->request->variable('date', '', true));

				$response->send($data, true);
			break;

			case 'action_sound':
				$content = $this->shoutbox->shout_ajax_action_sound($this->request->variable('sound', 1));

				$response->send($content, true);
			break;

			case 'cite':
				$content = $this->shoutbox->shout_ajax_cite($val['id']);

				$response->send($content, true);
			break;

			case 'action_user':
				$content = $this->shoutbox->shout_ajax_action_user($val);

				$response->send($content, true);
			break;

			case 'action_post':
				if (!$val['id'])
				{
					$content = array(
						'type'	=> 0,
					);
				}
				else if ($this->auth->acl_get('u_shout_post_inp') || $this->auth->acl_get('m_shout_robot') || $this->auth->acl_get('a_') || $this->auth->acl_get('m_'))
				{
					$content = $this->shoutbox->shout_ajax_action_post($val, $this->request->variable('message', '', true));
				}
				else
				{
					$content = array(
						'type'		=> 0,
						'message'	=> $this->language->lang('NO_ACTION_PERM'),
					);
				}

				$response->send($content, true);
			break;

			case 'action_del':
				$content = $this->shoutbox->shout_ajax_action_del($val);

				$response->send($content, true);
			break;

			case 'action_del_to':
				$content = $this->shoutbox->shout_ajax_action_del_to($val);

				$response->send($content, true);
			break;

			case 'action_remove':
				$content = $this->shoutbox->shout_ajax_action_remove($val);

				$response->send($content, true);
			break;

			case 'delete':
				$post = $this->request->variable('post', 0);
				if (!$post)
				{
					$this->shoutbox->shout_error('NO_SHOUT_ID');
					break;
				}
				else if ($val['userid'] == ANONYMOUS)
				{
					$this->shoutbox->shout_error('NO_DELETE_PERM');
					break;
				}

				$content = $this->shoutbox->shout_ajax_delete($val, $post);

				if ($content)
				{
					$response->send($content, true);
				}
			break;

			case 'purge':
				$content = $this->shoutbox->shout_ajax_purge($val);

				if ($content)
				{
					$response->send($content, true);
				}
			break;

			case 'purge_robot':
				$content = $this->shoutbox->shout_ajax_purge_robot($val);

				if ($content)
				{
					$response->send($content, true);
				}
			break;

			case 'edit':
				$content = $this->shoutbox->shout_ajax_edit($val, $this->request->variable('shout_id', 0), $this->request->variable('chat_message', '', true));

				if ($content)
				{
					$response->send($content, true);
				}
			break;

			case 'post':
				if (!$this->auth->acl_get('u_shout_post'))
				{
					$this->shoutbox->shout_error('NO_POST_PERM');
					break;
				}

				$content = $this->shoutbox->shout_ajax_post($val, $this->request->variable('chat_message', '', true), $this->request->variable('name', '', true), $this->request->variable('cite', 0));

				if ($content)
				{
					$response->send($content, true);
				}
			break;

			case 'check':
			case 'check_pop':
			case 'check_priv':
				// Permissions verification
				if (!$this->auth->acl_get("u_shout{$val['perm']}"))
				{
					$this->shoutbox->shout_error("NO_VIEW{$val['privat']}_PERM");
					break;
				}

				$content = $this->shoutbox->shout_ajax_check($val, $this->request->variable('on_bot', 'on'));

				$response->send($content, true);
			break;

			case 'view':
			case 'view_pop':
			case 'view_priv':
				// Permissions verification
				if (!$this->auth->acl_get("u_shout{$val['perm']}"))
				{
					$this->shoutbox->shout_error("NO_VIEW{$val['privat']}_PERM");
					break;
				}

				$content = $this->shoutbox->shout_ajax_view($val, $this->request->variable('on_bot', 'on'), $this->request->variable('start', 0));

				$response->send($content, true);
			break;
		}
	}
}
