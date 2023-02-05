<?php
/**
*
* @package Breizh Shoutbox Extension
* @copyright (c) 2019-2023 Sylver35  https://breizhcode.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace sylver35\breizhshoutbox\core;

use sylver35\breizhshoutbox\core\work;
use phpbb\config\config;
use phpbb\user;
use phpbb\language\language;
use phpbb\extension\manager;

class avatar
{
	/* @var \sylver35\breizhshoutbox\core\work */
	protected $work;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\extension\manager */
	protected $ext_manager;

	/** @var string ext path */
	protected $ext_path;

	/**
	 * Constructor
	 */
	public function __construct(work $work, config $config, user $user, language $language, manager $ext_manager)
	{
		$this->work = $work;
		$this->config = $config;
		$this->user = $user;
		$this->language = $language;
		$this->ext_manager = $ext_manager;
		$this->ext_path = $this->ext_manager->get_extension_path('sylver35/breizhshoutbox', true);
	}

	public function get_shout_avatar($row, $sort, $is_mobile)
	{
		if (!$this->config['shout_avatar'] || !$this->config['allow_avatar'] || $is_mobile)
		{
			return '';
		}
		else if (!$row['shout_user_id'] && $row['shout_robot_user'])
		{
			return $this->shout_user_avatar([
				'user_id'				=> $row['v_user_id'],
				'username'				=> $row['v_username'],
				'user_type'				=> $row['v_user_type'],
				'user_avatar'			=> $row['v_user_avatar'],
				'user_avatar_type'		=> $row['v_user_avatar_type'],
				'user_avatar_width'		=> $row['v_user_avatar_width'],
				'user_avatar_height'	=> $row['v_user_avatar_height'],
			], $this->config['shout_avatar_height'], false, ($sort === 1));
		}
		else
		{
			return $this->shout_user_avatar($row, $this->config['shout_avatar_height'], false, ($sort === 1));
		}
	}

	/*
	 * Display user avatar with resizing
	 * Add avatar type for robot, users with no avatar and anonymous
	 * Add title with username
	 * Return string
	 */
	public function shout_user_avatar($row, $height, $force, $popup = false)
	{
		if (!$force)
		{
			if (!$this->config['shout_avatar'] || !$this->config['allow_avatar'])
			{
				return '';
			}
		}

		if ($row['user_id'] && $row['user_avatar'] && $row['user_avatar_height'])
		{
			$avatar_height = ($row['user_avatar_height'] > $height) ? $height : $row['user_avatar_height'];
			$row['user_avatar_width'] = round($avatar_height / $row['user_avatar_height'] * $row['user_avatar_width']);
			$row['user_avatar_height'] = $avatar_height;
			$avatar = $this->work->shout_url(phpbb_get_user_avatar($row, $this->language->lang('SHOUT_AVATAR_TITLE', $row['username'])));
			$avatar = strtr($avatar, ['alt="' => 'title="' . $this->language->lang('SHOUT_AVATAR_TITLE', $row['username']) . '" alt="']);
			$avatar = ($popup) ? strtr($avatar, ['class="avatar' => 'class="avatar popup-avatar']) : $avatar;

			return $avatar;
		}
		else
		{
			$val = $this->build_additional_avatar($row);
		}

		$row = [
			'avatar'		=> $val['src'],
			'avatar_type'	=> 'avatar.driver.upload',
			'avatar_height'	=> $height,
			'avatar_width'	=> '',
		];
		$avatar = strtr(phpbb_get_user_avatar($row, $val['alt']), ['./download/file.php?avatar=' => '', 'alt="' => 'title="' . $val['alt'] . '" alt="']);
		$avatar = ($popup) ? strtr($avatar, ['class="avatar' => 'class="avatar popup-avatar']) : $avatar;

		return $this->work->shout_url($avatar);
	}

	private function build_additional_avatar($row)
	{
		$val = [
			'src'	=> $this->ext_path . 'images/burn.webp',
			'alt'	=> $this->language->lang('SHOUT_AVATAR_NONE', $row['username']),
		];

		if (!$row['user_id'] && $this->config['shout_avatar_robot'])
		{
			$val = [
				'src'	=> $this->ext_path . 'images/' . $this->config['shout_avatar_img_robot'],
				'alt'	=> $this->language->lang('SHOUT_AVATAR_TITLE', $this->config['shout_name_robot']),
			];
		}
		else if ($row['user_id'] == ANONYMOUS && $this->config['shout_avatar_user'])
		{
			$val = [
				'src'	=> $this->ext_path . 'images/anonym.webp',
				'alt'	=> $this->language->lang('SHOUT_AVATAR_TITLE', $this->language->lang('GUEST')),
			];
		}
		else if ($row['user_id'] && !$row['user_avatar'] && $this->config['shout_avatar_user'])
		{
			$val = [
				'src'	=> $this->ext_path . 'images/' . $this->config['shout_avatar_img'],
				'alt'	=> $this->language->lang('SHOUT_AVATAR_NONE', $row['username']),
			];
		}

		return $val;
	}
}
