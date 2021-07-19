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

class ajax
{
	/* @var \sylver35\breizhshoutbox\core\functions_ajax */
	protected $functions_ajax;

	/**
	 * Constructor
	 */
	public function __construct(functions_ajax $functions_ajax)
	{
		$this->functions_ajax = $functions_ajax;
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
		$val = $this->functions_ajax->shout_initialize($mode, $this->functions_ajax->get_var('sort', 2), $this->functions_ajax->get_var('user', 0), $this->functions_ajax->get_var('other', 0));

		switch ($mode)
		{
			case 'smilies':
				$data = $this->functions_ajax->shout_ajax_smilies();
			break;

			case 'smilies_popup':
				$data = $this->functions_ajax->shout_ajax_smilies_popup($this->functions_ajax->get_var('cat', -1));
			break;

			case 'display_smilies':
				$data = $this->functions_ajax->shout_ajax_display_smilies($this->functions_ajax->get_var('smiley', 0), $this->functions_ajax->get_var('display', 3));
			break;

			case 'user_bbcode':
				$data = $this->functions_ajax->shout_ajax_user_bbcode($val, $this->functions_ajax->get_var('open', ''), $this->functions_ajax->get_var('close', ''));
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
				$data = $this->functions_ajax->shout_ajax_auth($val['other'], $this->functions_ajax->get_var('name', ''));
			break;

			case 'rules':
				$data = $this->functions_ajax->shout_ajax_rules($val['priv']);
			break;

			case 'preview_rules':
				$data = $this->functions_ajax->shout_ajax_preview_rules($this->functions_ajax->get_var('content', ''));
			break;

			case 'date_format':
				$data = $this->functions_ajax->shout_ajax_date_format($this->functions_ajax->get_var('date', ''));
			break;

			case 'action_sound':
				$data = $this->functions_ajax->shout_ajax_action_sound($this->functions_ajax->get_var('sound', 1));
			break;

			case 'cite':
				$data = $this->functions_ajax->shout_ajax_cite($val['other']);
			break;

			case 'action_user':
				$data = $this->functions_ajax->shout_ajax_action_user($val);
			break;

			case 'action_post':
				$data = $this->functions_ajax->shout_ajax_action_post($val, $this->functions_ajax->get_var('message', ''));
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
				$data = $this->functions_ajax->shout_ajax_delete($val, $this->functions_ajax->get_var('post', 0));
			break;

			case 'purge':
				$data = $this->functions_ajax->shout_ajax_purge($val);
			break;

			case 'purge_robot':
				$data = $this->functions_ajax->shout_ajax_purge_robot($val);
			break;

			case 'edit':
				$data = $this->functions_ajax->shout_ajax_edit($val, $this->functions_ajax->get_var('shout_id', 0), $this->functions_ajax->get_var('message', ''));
			break;

			case 'post':
				$data = $this->functions_ajax->shout_ajax_post($val, $this->functions_ajax->get_var('message', ''), $this->functions_ajax->get_var('name', ''), $this->functions_ajax->get_var('cite', 0));
			break;

			case 'check':
			case 'check_pop':
			case 'check_priv':
				$data = $this->functions_ajax->shout_ajax_check($val, $this->functions_ajax->get_var('on_bot', true));
			break;

			case 'view':
			case 'view_pop':
			case 'view_priv':
				$data = $this->functions_ajax->shout_ajax_view($val, $this->functions_ajax->get_var('on_bot', true), $this->functions_ajax->get_var('start', 0));
			break;
		}

		// Send the response to the browser now
		$json_response = new \phpbb\json_response;
		$json_response->send($data, true);
	}
}
