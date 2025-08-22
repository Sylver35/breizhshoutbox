<?php
/**
*
* @package phpBB Extension - Breizh Shoutbox
* @copyright (c) 2018-2025 Sylver35  https://breizhcode.com
* @license https://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace sylver35\breizhshoutbox\core;

use sylver35\breizhshoutbox\core\work;
use phpbb\config\config;
use phpbb\db\driver\driver_interface as db;
use phpbb\auth\auth;
use phpbb\user;
use phpbb\language\language;
use phpbb\template\template;
use phpbb\controller\helper;
use Symfony\Component\DependencyInjection\Container;

class bbcodes
{
	/* @var \sylver35\breizhshoutbox\core\work */
	protected $work;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/** @var \Symfony\Component\DependencyInjection\Container */
	protected $phpbb_container;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpEx */
	protected $php_ext;

	/**
	 * Constructor
	 */
	public function __construct(work $work, config $config, db $db, auth $auth, user $user, language $language, template $template, helper $helper, Container $phpbb_container, $root_path, $php_ext)
	{
		$this->work = $work;
		$this->config = $config;
		$this->db = $db;
		$this->auth = $auth;
		$this->user = $user;
		$this->language = $language;
		$this->template = $template;
		$this->helper = $helper;
		$this->phpbb_container = $phpbb_container;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/*
	 * Personalize message before submit
	 * Return string
	 */
	public function personalize_message($message)
	{
		if ($this->user->data['shout_bbcode'] && $this->auth->acl_get('u_shout_bbcode_change'))
		{
			list($open, $close) = explode('||', $this->user->data['shout_bbcode']);
			// Don't personalize if somes bbcodes are presents
			if (strpos($message, '[spoil') !== false || strpos($message, '[hidden') !== false || strpos($message, '[offtopic') !== false || strpos($message, '[mod=') !== false || strpos($message, '[quote') !== false || strpos($message, '[code') !== false || strpos($message, '[list') !== false)
			{
				return $message;
			}
			return $open . $message . $close;
		}

		return $message;
	}

	public function user_bbcode($val, $open, $close)
	{
		$text = $message = '';
		$on_user = ($val['other'] > 0) ? $val['other'] : $val['userid'];

		// Parse bbcodes
		$data = $this->parse_shout_bbcodes($open, $close, $on_user);
		switch ($data['sort'])
		{
			// Remove the bbcodes
			case 1:
				$this->work->shout_sql_query('UPDATE ' . USERS_TABLE . " SET shout_bbcode = '' WHERE user_id = $on_user");
				$message = $this->language->lang('SHOUT_BBCODE_SUP');
				$text = $this->language->lang('SHOUT_EXEMPLE');
			break;
			// Retun error message
			case 2:
				$message = $data['message'];
			break;
			// Good ! Update the bbcodes
			case 3:
				$ok_bbcode = (string) ($open . '||' . $close);
				$options = 0;
				$uid = $bitfield = '';
				// Change it in the db
				$this->work->shout_sql_query('UPDATE ' . USERS_TABLE . " SET shout_bbcode = '" . $this->db->sql_escape($ok_bbcode) . "' WHERE user_id = $on_user");
				$text = $open . $this->language->lang('SHOUT_EXEMPLE') . $close;
				generate_text_for_storage($text, $uid, $bitfield, $options, true, false, true);
				$text = generate_text_for_display($text, $uid, $bitfield, $options);
				$message = $this->language->lang('SHOUT_BBCODE_SUCCESS');
			break;
			// Return no change message
			case 4:
				$options = 0;
				$uid = $bitfield = '';
				if ($open != '1')
				{
					$text = $open . $this->language->lang('SHOUT_EXEMPLE') . $close;
					generate_text_for_storage($text, $uid, $bitfield, $options, true, false, true);
					$text = generate_text_for_display($text, $uid, $bitfield, $options);
				}
				else
				{
					$text = $this->language->lang('SHOUT_EXEMPLE');
				}
				$message = $data['message'];
			break;
			// Return error no permission
			case 5:
				$message = $data['message'];
			break;
		}

		return [
			'type'		=> $data['sort'],
			'before'	=> $open,
			'after'		=> $close,
			'on_user'	=> $on_user,
			'text'		=> $text,
			'message'	=> $message,
		];
	}

	public function charge_bbcode($id)
	{
		$on_bbcode = [
			0	=> '',
			1	=> '',
		];
		$message = $this->language->lang('SHOUT_EXEMPLE');

		$sql = 'SELECT user_id, user_type, username, user_colour, shout_bbcode
			FROM ' . USERS_TABLE . '
				WHERE user_id = ' . $id;
		$result = $this->work->shout_sql_query($sql, true, 1);
		$row = $this->db->sql_fetchrow($result);
		if ($row['shout_bbcode'])
		{
			$options = 0;
			$uid = $bitfield = '';
			$on_bbcode = explode('||', $row['shout_bbcode']);
			$message = $on_bbcode[0] . $message . $on_bbcode[1];
			generate_text_for_storage($message, $uid, $bitfield, $options, true, false, true);
			$message = generate_text_for_display($message, $uid, $bitfield, $options);
		}
		$this->db->sql_freeresult($result);

		return [
			'id'		=> $id,
			'name'		=> $this->work->shout_url(get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'])),
			'before'	=> $on_bbcode[0],
			'after'		=> $on_bbcode[1],
			'message'	=> $message,
		];
	}

	/*
	 * Parse bbcodes in personalisation
	 * before submit
	 * Return array
	 */
	public function parse_shout_bbcodes($open, $close, $other)
	{
		// Return error no permission for change personalisation of another
		if ($other > 0 && ($other !== (int) $this->user->data['user_id']))
		{
			if (!$this->auth->acl_gets(['a_', 'm_']))
			{
				return [
					'sort'		=> 5,
					'message'	=> $this->language->lang('NO_SHOUT_PERSO_PERM'),
				];
			}
		}

		// prepare the list
		$open = str_replace('][', '], [', $open);
		$close = str_replace('][', '], [', $close);
		// explode it
		$array_open = explode(', ', $open);
		$array_close = explode(', ', $close);
		// for this user or an another?
		$shout_bbcode = $this->get_shout_bbcode($other);

		$first = $this->first_parse($open, $close, $array_open, $array_close, $shout_bbcode);
		if ($first['sort'] !== 3)
		{
			return [
				'sort'		=> $first['sort'],
				'message'	=> $first['message'],
			];
		}

		$second = $this->second_parse($open, $close, $array_open, $array_close, $shout_bbcode);
		if ($second['sort'] !== 1)
		{
			return [
				'sort'		=> $second['sort'],
				'message'	=> $second['message'],
			];
		}

		// If all is ok, return 3
		return ['sort' => 3];
	}

	private function first_parse($open, $close, $array_open, $array_close, $shout_bbcode)
	{
		// Any modification
		if ($open == 1 && $close == 1)
		{
			if ($shout_bbcode)
			{
				return ['sort' => 1];
			}
			else
			{
				return [
					'sort'		=> 4,
					'message'	=> $this->language->lang('SHOUT_BBCODE_ERROR_SHAME'),
				];
			}
		}
		else if (($open == '' && $close != '') || ($open != '' && $close == ''))
		{
			// If one is empty
			return [
				'sort'		=> 2,
				'message'	=> $this->language->lang('SHOUT_BBCODE_ERROR'),
			];
		}
		else if (sizeof($array_open) != sizeof($array_close))
		{
			// If the number of bbcodes opening and closing is different
			return [
				'sort'		=> 2,
				'message'	=> $this->language->lang('SHOUT_BBCODE_ERROR_COUNT'),
			];
		}
		else if (!preg_match("#^\[|\[|\]|\]$#", $open) || !preg_match("#^\[|\[|\[/|\]|\]$#", $close))
		{
			// If a square bracket is absent
			return [
				'sort'		=> 2,
				'message'	=> $this->language->lang('SHOUT_BBCODE_ERROR_COUNT'),
			];
		}

		return ['sort' => 3];
	}

	private function second_parse($open, $close, $array_open, $array_close, $shout_bbcode)
	{
		$verify = $this->verify_imbrication($open, $close, $array_open, $array_close, $shout_bbcode);
		if ($verify['sort'] !== 1)
		{
			return [
				'sort'		=> $verify['sort'],
				'message'	=> $verify['message'],
			];
		}

		$unautorised = $this->verify_unautorised_and_size($open, $close);
		if ($unautorised['sort'] !== 1)
		{
			return [
				'sort'		=> $unautorised['sort'],
				'message'	=> $unautorised['message'],
			];
		}

		$video = $this->verify_video_bbcode($open);
		if ($video['sort'] !== 1)
		{
			return [
				'sort'		=> $video['sort'],
				'message'	=> $video['message'],
			];
		}

		return ['sort' => 1];
	}

	private function verify_imbrication($open, $close, $array_open, $array_close, $shout_bbcode)
	{
		// Initalise closing of bbcodes and correct imbrication
		$s = $n = 0;
		$slash = $sort = [];
		$reverse_open = array_reverse($array_open);
		for ($i = 0, $nb = sizeof($reverse_open); $i < $nb; $i++)
		{
			$first = substr($reverse_open[$i], 0, strlen($array_close[$i]) - 2) . ']';
			if (strpos($array_close[$i], '[/') === false)
			{
				$slash[] = $array_close[$i];
				$s++;
			}
			else if ($first != str_replace('/', '', $array_close[$i]))
			{
				$sort[] = $array_close[$i];
				$n++;
			}
			else
			{
				continue;
			}
		}
		// Check closing of bbcodes
		if ($s)
		{
			$slash = implode(', ', $slash);
			return [
				'sort'		=> 2,
				'message'	=> $this->work->plural('SHOUT_BBCODE_ERROR_SLASH', $s, '', $slash),
			];
		}
		// Check the correct imbrication of bbcodes
		if ($n)
		{
			$sort = implode(', ', $sort);
			return [
				'sort'		=> 2,
				'message'	=> $this->work->plural('SHOUT_BBCODE_ERROR_IMB', $n, '', $sort),
			];
		}

		// Check opening and closing of bbcodes
		if ($shout_bbcode)
		{
			$bbcode = explode('||', $shout_bbcode);
			if (str_replace('][', '], [', $bbcode[0]) == $open && str_replace('][', '], [', $bbcode[1]) == $close)
			{
				return [
					'sort'		=> 4,
					'message'	=> $this->language->lang('SHOUT_BBCODE_ERROR_SHAME'),
				];
			}
		}

		return ['sort' => 1];
	}

	private function get_shout_bbcode($other)
	{
		if ($other > 0)
		{
			$sql = 'SELECT shout_bbcode
				FROM ' . USERS_TABLE . '
					WHERE user_id = ' . $other;
			$result = $this->db->sql_query($sql);
			$shout_bbcode = (string) $this->db->sql_fetchfield('shout_bbcode');
			$this->db->sql_freeresult($result);
		}
		else
		{
			$shout_bbcode = (string) $this->user->data['shout_bbcode'];
		}

		return $shout_bbcode;
	}

	private function verify_unautorised_and_size($open, $close)
	{
		// See for unautorised bbcodes
		$other_bbcode = ($this->config['shout_bbcode']) ? ', ' . $this->config['shout_bbcode'] : '';
		$bbcode_array = explode(', ', $this->config['shout_bbcode_user'] . $other_bbcode);
		foreach ($bbcode_array as $no)
		{
			if (strpos($close, "[/{$no}]") !== false)
			{
				return [
					'sort'		=> 2,
					'message'	=> $this->language->lang('SHOUT_NO_CODE', "[{$no}][/{$no}]"),
				];
			}
		}

		// Limit font size
		$shout_bbcode_size = $this->auth->acl_get('a_') ? 200 : (int) $this->config['shout_bbcode_size'];
		if (strpos($open, '[size=') !== false)
		{
			$this->language->add_lang('posting');
			$all = explode(', ', $open);
			foreach ($all as $is)
			{
				if (preg_match('/size=/i', $is))
				{
					$size = str_replace(['[', 'size=', ']'], '', $is);
					if ($size > $shout_bbcode_size)
					{
						return [
							'sort'		=> 2,
							'message'	=> $this->language->lang('MAX_FONT_SIZE_EXCEEDED', $shout_bbcode_size),
						];
					}
				}
				else
				{
					continue;
				}
			}
		}

		return ['sort' => 1];
	}

	private function verify_video_bbcode($open)
	{
		// No video here !
		$video_array = ['flash', 'swf', 'mp4', 'mts', 'avi', '3gp', 'asf', 'flv', 'mpeg', 'video', 'embed', 'BBvideo', 'scrippet', 'quicktime', 'ram', 'gvideo', 'youtube', 'veoh', 'collegehumor', 'dm', 'gamespot', 'gametrailers', 'ignvideo', 'liveleak'];
		foreach ($video_array as $video)
		{
			if (strpos($open, '[' . $video) !== false || strpos($open, '<' . $video) !== false)
			{
				return [
					'sort'		=> 2,
					'message'	=> $this->language->lang('SHOUT_NO_VIDEO'),
				];
			}
			else
			{
				continue;
			}
		}

		return ['sort' => 1];
	}

	public function parse_bbcode_video_message($message)
	{
		// See for unautorised bbcodes
		$bbcode_array = explode(', ', $this->config['shout_bbcode']);
		foreach ($bbcode_array as $no)
		{
			if (strpos($message, "[/{$no}]") !== false)
			{
				$this->work->shout_error('SHOUT_NO_CODE', "[{$no}][/{$no}]");
				return false;
			}
		}

		// No video!
		$video_array = ['flash', 'swf', 'mp4', 'mts', 'avi', '3gp', 'asf', 'flv', 'mpeg', 'video', 'embed', 'BBvideo', 'scrippet', 'quicktime', 'ram', 'gvideo', 'youtube', 'veoh', 'collegehumor', 'dm', 'gamespot', 'gametrailers', 'ignvideo', 'liveleak'];
		foreach ($video_array as $video)
		{
			if ((strpos($message, '[' . $video) !== false && strpos($message, '[/' . $video) !== false) || (strpos($message, '<' . $video) !== false && strpos($message, '</' . $video) !== false))
			{
				$this->work->shout_error('SHOUT_NO_VIDEO');
				return false;
			}
			else
			{
				continue;
			}
		}

		return true;
	}

	public function active_custom_bbcodes($sort_of, $is_mobile)
	{
		if (!$this->auth->acl_get('u_shout_bbcode_custom') || $sort_of === 1 || ($sort_of === 2) && $is_mobile)
		{
			return;
		}

		if (!function_exists('display_custom_bbcodes'))
		{
			include($this->root_path . 'includes/functions_display.' . $this->php_ext);
		}
		display_custom_bbcodes();
	}

	public function parse_img_bbcodes($message)
	{
		// Don't parse img if unautorised and return img url only
		if ((strpos($message, '[/img]') !== false) && !$this->auth->acl_get('u_shout_image'))
		{
			$message = str_replace(['[img]', '[/img]'], '', $message);
		}

		// Correct a bug with somes empty bbcodes
		if ($message == '[img][/img]' || $message == '[b][/b]' || $message == '[i][/i]' || $message == '[u][/u]' || $message == '[url][/url]')
		{
			$this->work->shout_error('MESSAGE_EMPTY');
			return;
		}

		return str_replace(['/]', '&amp;amp;'], [']', '&'], $message);
	}

	public function add_mention_ext()
	{
		if ($this->work->mention_exist() && $this->auth->acl_get('u_can_mention'))
		{
			$this->template->assign_vars([
				'ACTIV_SHOUT_MENTION'	=> true,
			   'U_AJAX_MENTION_URL'		=> $this->helper->route('paul999_mention_controller'),
			   'MIN_MENTION_LENGTH'		=> $this->config['simple_mention_minlength'],
			]);
		}
	}
}
