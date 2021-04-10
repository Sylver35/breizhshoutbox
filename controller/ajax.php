<?php
/**
*
* @package Breizh Shoutbox Extension
* @copyright (c) 2018-2021 Sylver35  https://breizhcode.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace sylver35\breizhshoutbox\controller;

use sylver35\breizhshoutbox\core\functions_ajax;
use phpbb\request\request;

class ajax
{
	/* @var \sylver35\breizhshoutbox\core\functions_ajax */
	protected $functions_ajax;

	/** @var \phpbb\request\request */
	protected $request;

	/**
	 * Constructor
	 */
	public function __construct(functions_ajax $functions_ajax, request $request)
	{
		$this->functions_ajax = $functions_ajax;
		$this->request = $request;
	}

	/**
	 * Function construct_ajax
	 *
	 * @param string $mode Mode to switch
	 * @return void
	 * @access public
	 */
	public function construct_ajax($mode)
	{
		$data = [];
		$val = $this->functions_ajax->shout_initialize($mode, (int) $this->request->variable('sort', 2), (int) $this->request->variable('user', 0), (int) $this->request->variable('other', 0));

		switch ($mode)
		{
			case 'smilies':
				$data = $this->functions_ajax->shout_ajax_smilies();
			break;

			case 'smilies_popup':
				$data = $this->functions_ajax->shout_ajax_smilies_popup((int) $this->request->variable('cat', -1));
			break;

			case 'display_smilies':
				$data = $this->functions_ajax->shout_ajax_display_smilies((int) $this->request->variable('smiley', 0), (int) $this->request->variable('display', 3));
			break;

			case 'user_bbcode':
				$data = $this->functions_ajax->shout_ajax_user_bbcode($val, (string) $this->request->variable('open', ''), (string) $this->request->variable('close', ''));
			break;

			case 'charge_bbcode':
				$data = $this->functions_ajax->shout_ajax_charge_bbcode($val['other']);
			break;

			case 'online':
				$data = $this->functions_ajax->shout_ajax_online();
			break;

			case 'question':
				$data = $this->functions_ajax->shout_ajax_question();
			break;

			case 'auth':
				$data = $this->functions_ajax->shout_ajax_auth($val['other'], (string) $this->request->variable('name', '', true));
			break;

			case 'rules':
				$data = $this->functions_ajax->shout_ajax_rules($val['priv']);
			break;

			case 'preview_rules':
				$data = $this->functions_ajax->shout_ajax_preview_rules((string) $this->request->variable('content', '', true));
			break;

			case 'date_format':
				$data = $this->functions_ajax->shout_ajax_date_format((string) $this->request->variable('date', '', true));
			break;

			case 'action_sound':
				$data = $this->functions_ajax->shout_ajax_action_sound((int) $this->request->variable('sound', 1));
			break;

			case 'cite':
				$data = $this->functions_ajax->shout_ajax_cite($val['other']);
			break;

			case 'action_user':
				$data = $this->functions_ajax->shout_ajax_action_user($val);
			break;

			case 'action_post':
				$data = $this->functions_ajax->shout_ajax_action_post($val, (string) $this->request->variable('message', '', true));
			break;

			case 'action_del':
				$data = $this->functions_ajax->shout_ajax_action_del($val);
			break;

			case 'action_del_to':
				$data = $this->functions_ajax->shout_ajax_action_del_to($val);
			break;

			case 'action_remove':
				$data = $this->functions_ajax->shout_ajax_action_remove($val);
			break;

			case 'delete':
				$data = $this->functions_ajax->shout_ajax_delete($val, (int) $this->request->variable('post', 0));
			break;

			case 'purge':
				$data = $this->functions_ajax->shout_ajax_purge($val);
			break;

			case 'purge_robot':
				$data = $this->functions_ajax->shout_ajax_purge_robot($val);
			break;

			case 'edit':
				$data = $this->functions_ajax->shout_ajax_edit($val, (int) $this->request->variable('shout_id', 0), (string) $this->request->variable('message', '', true));
			break;

			case 'post':
				$data = $this->functions_ajax->shout_ajax_post($val, (string) $this->request->variable('message', '', true), (string) $this->request->variable('name', '', true), (int) $this->request->variable('cite', 0));
			break;

			case 'check':
			case 'check_pop':
			case 'check_priv':
				$data = $this->functions_ajax->shout_ajax_check($val, (bool) $this->request->variable('on_bot', true));
			break;

			case 'view':
			case 'view_pop':
			case 'view_priv':
				$data = $this->functions_ajax->shout_ajax_view($val, (bool) $this->request->variable('on_bot', true), (int) $this->request->variable('start', 0));
			break;
		}

		// Send the response to the browser now
		$json_response = new \phpbb\json_response;
		$json_response->send($data);
	}
}
