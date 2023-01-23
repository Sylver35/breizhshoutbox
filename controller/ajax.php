<?php
/**
*
* @package Breizh Shoutbox Extension
* @copyright (c) 2019-2023 Sylver35  https://breizhcode.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace sylver35\breizhshoutbox\controller;

use sylver35\breizhshoutbox\core\functions_ajax;
use sylver35\breizhshoutbox\core\smilies;
use sylver35\breizhshoutbox\core\actions;

class ajax
{
	/* @var \sylver35\breizhshoutbox\core\functions_ajax */
	protected $functions_ajax;

	/* @var \sylver35\breizhshoutbox\core\smilies */
	protected $smilies;

	/* @var \sylver35\breizhshoutbox\core\actions */
	protected $actions;

	/**
	 * Constructor
	 */
	public function __construct(functions_ajax $functions_ajax, smilies $smilies, actions $actions)
	{
		$this->functions_ajax = $functions_ajax;
		$this->smilies = $smilies;
		$this->actions = $actions;
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
		$data = $val = [];
		if ($this->functions_ajax->exclude($mode))
		{
			$val = $this->functions_ajax->shout_initialize($mode, $this->value('sort', 2), $this->value('user', 0), $this->value('other', 0));
		}

		switch ($mode)
		{
			case 'smilies':
				$data = $this->smilies->smilies();
			break;

			case 'smilies_popup':
				$data = $this->smilies->smilies_popup($this->value('cat', -1));
			break;

			case 'display_smilies':
				$data = $this->smilies->display_smilies($this->value('smiley', 0), $this->value('display', 3));
			break;

			case 'user_bbcode':
				$data = $this->functions_ajax->user_bbcode($val, $this->value('open', ''), $this->value('close', ''));
			break;

			case 'charge_bbcode':
				$data = $this->functions_ajax->charge_bbcode($val['other']);
			break;

			case 'online':
				$data = $this->functions_ajax->online();
			break;

			case 'auth':
				$data = $this->functions_ajax->auth($val['other'], $this->value('name', ''));
			break;

			case 'question':
				$data = $this->actions->question();
			break;

			case 'rules':
				$data = $this->actions->rules($val['priv']);
			break;

			case 'preview_rules':
				$data = $this->actions->preview_rules($this->value('content', ''));
			break;

			case 'date_format':
				$data = $this->actions->date_format($this->value('date', ''));
			break;

			case 'action_sound':
				$data = $this->actions->action_sound($this->value('sound', 1));
			break;

			case 'cite':
				$data = $this->actions->cite($val['other']);
			break;

			case 'action_user':
				$data = $this->actions->action_user($val);
			break;

			case 'action_post':
				$data = $this->actions->action_post($val, $this->value('message', ''));
			break;

			case 'action_del':
				$data = $this->actions->action_del($val);
			break;

			case 'action_del_to':
				$data = $this->actions->action_del_to($val);
			break;

			case 'action_remove':
				$data = $this->actions->action_remove($val);
			break;

			case 'delete':
				$data = $this->actions->delete($val, $this->value('post', 0));
			break;

			case 'purge':
				$data = $this->actions->purge($val);
			break;

			case 'purge_robot':
				$data = $this->actions->purge_robot($val);
			break;

			case 'edit':
				$data = $this->functions_ajax->edit($val, $this->value('shout_id', 0), $this->value('message', ''));
			break;

			case 'post':
				$data = $this->functions_ajax->post($val, $this->value('message', ''), $this->value('name', ''), $this->value('cite', 0));
			break;

			case 'check':
				$data = $this->functions_ajax->check($val, $this->value('on_bot', true));
			break;

			case 'view':
				$data = $this->functions_ajax->view($val, $this->value('on_bot', true), $this->value('start', 0));
			break;
		}

		// Send the response to the browser now
		$json_response = new \phpbb\json_response;
		$json_response->send($data, true);
	}

	private function value($value, $default)
	{
		return $this->functions_ajax->get_var($value, $default);
	}
}
