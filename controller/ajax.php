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
use phpbb\db\driver\driver_interface as db;
use phpbb\request\request;
use phpbb\auth\auth;
use phpbb\language\language;

class ajax
{
	/* @var \sylver35\breizhshoutbox\core\shoutbox */
	protected $shoutbox;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\language\language */
	protected $language;

	/**
	 * Constructor
	 */
	public function __construct(shoutbox $shoutbox, db $db, request $request, auth $auth, language $language)
	{
		$this->shoutbox = $shoutbox;
		$this->db = $db;
		$this->request = $request;
		$this->auth = $auth;
		$this->language = $language;
	}

	/**
	 * Function construct_ajax
	 *
	 * @param string $mode Mode to switch
	 * @return void
	 */
	public function construct_ajax($mode)
	{
		$val = $this->shoutbox->shout_manage_ajax($mode, $this->request->variable('sort', 2), $this->request->variable('user', 0));
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
				}
				else if ($val['userid'] == ANONYMOUS)
				{
					$this->shoutbox->shout_error('NO_DELETE_PERM');
				}
				else
				{
					$content = $this->shoutbox->shout_ajax_delete($val, $post);

					if ($content)
					{
						$response->send($content, true);
					}
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
				if ($this->auth->acl_get('u_shout_post'))
				{
					$content = $this->shoutbox->shout_ajax_post($val, $this->request->variable('chat_message', '', true), $this->request->variable('name', '', true), $this->request->variable('cite', 0));

					if ($content)
					{
						$response->send($content, true);
					}
				}
				else
				{
					$this->shoutbox->shout_error('NO_POST_PERM');
				}
			break;

			case 'check':
			case 'check_pop':
			case 'check_priv':
				// Permissions verification
				if ($this->auth->acl_get("u_shout{$val['perm']}"))
				{
					$response->send($this->shoutbox->shout_ajax_check($val, $this->request->variable('on_bot', true)), true);
				}
				else
				{
					$this->shoutbox->shout_error("NO_VIEW{$val['privat']}_PERM");
				}
			break;

			case 'view':
			case 'view_pop':
			case 'view_priv':
				// Permissions verification
				if ($this->auth->acl_get("u_shout{$val['perm']}"))
				{
					$response->send($this->shoutbox->shout_ajax_view($val, $this->request->variable('on_bot', true), $this->request->variable('start', 0)), true);
				}
				else
				{
					$this->shoutbox->shout_error("NO_VIEW{$val['privat']}_PERM");
				}
			break;
		}
	}
}
