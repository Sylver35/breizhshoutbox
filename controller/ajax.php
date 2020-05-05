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
	 * The database tables
	 *
	 * @var string */
	protected $shoutbox_table;
	protected $shoutbox_priv_table;

	/**
	 * Constructor
	 */
	public function __construct(shoutbox $shoutbox, config $config, helper $helper, db $db, request $request, template $template, auth $auth, user $user, language $language, manager $ext_manager, path_helper $path_helper, phpbb_dispatcher $phpbb_dispatcher, $root_path, $php_ext, $shoutbox_table, $shoutbox_priv_table)
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
		$this->shoutbox_table = $shoutbox_table;
		$this->shoutbox_priv_table = $shoutbox_priv_table;
	}

	/**
	 * Function construct_ajax
	 *
	 * @param string $mode Mode to switch
	 * @return void
	 */
	public function construct_ajax($mode)
	{
		$val_id = $this->request->variable('user', 0);
		$val_sort = $this->request->variable('sort', 2);
		$userid = $this->user->data['user_id'];
		$is_user = ($this->user->data['is_registered'] && !$this->user->data['is_bot']) ? true : false;
		$adm_path = $this->shoutbox->adm_relative_path();
		$board_url = generate_board_url() . '/';
		$response = new \phpbb\json_response;

		// First initialize somes variables, protect private
		// And select the good table for the type of shoutbox
		$val_on_priv = false;
		$val_perm = '_view';
		$val_auth = 'manage';
		$val_priv = $val_privat = '';
		$val_sort_on = '';
		$shoutbox_table = $this->shoutbox_table;
		switch ($val_sort)
		{
			// Popup shoutbox
			case 1:
				$val_sort_on = '_pop';
			break;
			// Normal shoutbox
			case 2:
				$val_sort_on = '';
			break;
			// Private shoutbox
			case 3:
				$val_on_priv = true;
				$val_sort_on = $val_perm = $val_priv = '_priv';
				$val_auth = 'priv';
				$val_privat = '_PRIV';
				$shoutbox_table = $this->shoutbox_priv_table;
			break;
		}

		// We have our own error handling
		$this->db->sql_return_on_error(true);

		switch ($mode)
		{
			case 'smilies':
				$i = 0;
				$smilies = [];
				$sql_ary = array(
					'SELECT'	=> 'smiley_url, MIN(smiley_id) AS smiley_id, MIN(code) AS code, MIN(smiley_order) AS min_smiley_order, MIN(smiley_width) AS smiley_width, MIN(smiley_height) AS smiley_height, MIN(emotion) AS emotion, MIN(display_on_shout) AS display_on_shout',
					'FROM'		=> array(SMILIES_TABLE => ''),
					'WHERE'		=> 'display_on_shout = 1',
					'GROUP_BY'	=> 'smiley_url',
					'ORDER_BY'	=> 'min_smiley_order ASC',
				);
				$result = $this->shoutbox->shout_sql_query($this->db->sql_build_query('SELECT', $sql_ary), true, 3600);
				if (!$result)
				{
					break;
				}
				while ($row = $this->db->sql_fetchrow($result))
				{
					$smilies[$i] = array(
						'nb'		=> $i,
						'code'		=> $row['code'],
						'emotion'	=> $row['emotion'],
						'width'		=> $row['smiley_width'],
						'height'	=> $row['smiley_height'],
						'image'		=> $row['smiley_url'],
					);
					$i++;
				}
				$this->db->sql_freeresult($result);

				$sql = 'SELECT COUNT(smiley_id) as total
					FROM ' . SMILIES_TABLE . '
						WHERE display_on_shout = 0';
				$result = $this->shoutbox->shout_sql_query($sql);
				$row_nb = $this->db->sql_fetchfield('total', $result);
				$this->db->sql_freeresult($result);

				$content = array(
					'smilies'	=> $smilies,
					'total'		=> $i,
					'nb_pop'	=> $row_nb,
					'url'		=> $board_url . $this->config['smilies_path'] . '/',
				);

				/**
				 * You can use this event to modify the content array.
				 *
				 * @event breizhshoutbox.smilies
				 * @var	array	content		The content array to be displayed in the smilies form
				 * @since 1.7.0
				 */
				$vars = array('content');
				extract($this->phpbb_dispatcher->trigger_event('breizhshoutbox.smilies', compact($vars)));

				$response->send($content, true);
			break;

			case 'smilies_popup':
				$cat = $this->request->variable('cat', -1);
				$i = 0;
				$smilies = [];

				$sql_ary = array(
					'SELECT'	=> 'smiley_url, MIN(smiley_id) AS smiley_id, MIN(code) AS code, MIN(smiley_order) AS min_smiley_order, MIN(smiley_width) AS smiley_width, MIN(smiley_height) AS smiley_height, MIN(emotion) AS emotion, MIN(display_on_shout) AS display_on_shout',
					'FROM'		=> array(SMILIES_TABLE => ''),
					'WHERE'		=> 'display_on_shout = 0',
					'GROUP_BY'	=> 'smiley_url',
					'ORDER_BY'	=> 'min_smiley_order ASC',
				);
				$result = $this->shoutbox->shout_sql_query($this->db->sql_build_query('SELECT', $sql_ary), true, 3600);
				if (!$result)
				{
					break;
				}
				while ($row = $this->db->sql_fetchrow($result))
				{
					$smilies[$i] = array(
						'nb'		=> $i,
						'code'		=> $row['code'],
						'emotion'	=> $row['emotion'],
						'width'		=> $row['smiley_width'],
						'height'	=> $row['smiley_height'],
						'image'		=> $row['smiley_url'],
					);
					$i++;
				}
				$this->db->sql_freeresult($result);

				$content = array(
					'smilies'	=> $smilies,
					'total'		=> $i,
					'nb_pop'	=> 0,
					'url'		=> $board_url . $this->config['smilies_path'] . '/',
				);

				/**
				 * You can use this event to modify the content array.
				 *
				 * @event breizhshoutbox.smilies
				 * @var	array	content			The content array to be displayed in the smilies form
				 * @var	int		cat				The id of smilies category if needed
				 * @since 1.7.0
				 */
				$vars = array(
					'content',
					'cat',
				);
				extract($this->phpbb_dispatcher->trigger_event('breizhshoutbox.smilies_popup', compact($vars)));

				$response->send($content, true);
			break;

			case 'display_smilies':
				$smiley = $this->request->variable('smiley', 0);
				$display = $this->request->variable('display', 3);

				if ($smiley && $display !== 3)
				{
					$var_set = ($display === 1) ? 0 : 1;
					$sql = 'UPDATE ' . SMILIES_TABLE . " SET display_on_shout = $var_set WHERE smiley_id = $smiley";
					$this->db->sql_query($sql);
					$content = array('type'	=> ($display === 1) ? 1 : 2);

					$i = $j = 0;
					$smilies = $smilies_pop = [];
					$sql_ary = array(
						'SELECT'	=> 'smiley_url, MIN(smiley_id) AS smiley_id, MIN(code) AS code, MIN(smiley_order) AS min_smiley_order, MIN(smiley_width) AS smiley_width, MIN(smiley_height) AS smiley_height, MIN(emotion) AS emotion, MIN(display_on_shout) AS display_on_shout',
						'FROM'		=> array(SMILIES_TABLE => ''),
						'WHERE'		=> 'display_on_shout = 1',
						'GROUP_BY'	=> 'smiley_url',
						'ORDER_BY'	=> 'min_smiley_order ASC',
					);
					$result = $this->shoutbox->shout_sql_query($this->db->sql_build_query('SELECT', $sql_ary));
					if (!$result)
					{
						break;
					}
					while ($row = $this->db->sql_fetchrow($result))
					{
						$smilies[$i] = array(
							'nb'		=> $i,
							'id'		=> $row['smiley_id'],
							'code'		=> $row['code'],
							'emotion'	=> $row['emotion'],
							'width'		=> $row['smiley_width'],
							'height'	=> $row['smiley_height'],
							'image'		=> $row['smiley_url'],
						);
						$i++;
					}
					$this->db->sql_freeresult($result);

					$sql_ary = array(
						'SELECT'	=> 'smiley_url, MIN(smiley_id) AS smiley_id, MIN(code) AS code, MIN(smiley_order) AS min_smiley_order, MIN(smiley_width) AS smiley_width, MIN(smiley_height) AS smiley_height, MIN(emotion) AS emotion, MIN(display_on_shout) AS display_on_shout',
						'FROM'		=> array(SMILIES_TABLE => ''),
						'WHERE'		=> 'display_on_shout = 0',
						'GROUP_BY'	=> 'smiley_url',
						'ORDER_BY'	=> 'min_smiley_order',
					);
					$result_pop = $this->shoutbox->shout_sql_query($this->db->sql_build_query('SELECT', $sql_ary));
					if (!$result_pop)
					{
						break;
					}
					while ($row = $this->db->sql_fetchrow($result_pop))
					{
						$smilies_pop[$j] = array(
							'nb'		=> $j,
							'id'		=> $row['smiley_id'],
							'code'		=> $row['code'],
							'emotion'	=> $row['emotion'],
							'width'		=> $row['smiley_width'],
							'height'	=> $row['smiley_height'],
							'image'		=> $row['smiley_url'],
						);
						$j++;
					}
					$this->db->sql_freeresult($result_pop);

					$content = array_merge($content, array(
						'smilies'		=> $smilies,
						'smiliesPop'	=> $smilies_pop,
						'total'			=> $i,
						'totalPop'		=> $j,
						'url'			=> $board_url . $this->config['smilies_path'] . '/',
					));
				}
				else
				{
					$content = array('type' => 3);
				}

				$response->send($content, true);
			break;

			case 'user_bbcode':
				$open = $this->request->variable('open', '');
				$close = $this->request->variable('close', '');
				$other = $this->request->variable('other', 0);
				$on_user = $other ? $other : $userid;
				$text = $message = '';

				// Parse bbcodes
				$data = $this->shoutbox->parse_shout_bbcodes($open, $close, $other);
				// the result of parse
				switch ($data['sort'])
				{
					// Remove the bbcodes
					case 1:
						$sql = 'UPDATE ' . USERS_TABLE . " SET shout_bbcode = '' WHERE user_id = $on_user";
						$result = $this->shoutbox->shout_sql_query($sql);
						if (!$result)
						{
							break;
						}
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
						$sql = 'UPDATE ' . USERS_TABLE . " SET shout_bbcode = '" . $this->db->sql_escape($ok_bbcode) . "' WHERE user_id = $on_user";
						$result = $this->shoutbox->shout_sql_query($sql);
						if (!$result)
						{
							break;
						}
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

				$response->send(array(
					'type'		=> $data['sort'],
					'before'	=> $open,
					'after'		=> $close,
					'on_user'	=> $on_user,
					'text'		=> $text,
					'message'	=> $message,
				), true);
			break;

			case 'charge_bbcode':
				$sql = $this->db->sql_build_query('SELECT', array(
					'SELECT'	=> 'user_id, user_type, username, user_colour, shout_bbcode',
					'FROM'		=> array(USERS_TABLE => ''),
					'WHERE'		=> 'user_id = ' . $val_id,
				));
				$result = $this->shoutbox->shout_sql_query($sql, true, 1);
				if (!$result)
				{
					break;
				}
				$row = $this->db->sql_fetchrow($result);
				$on_bbcode = [];
				if ($row['shout_bbcode'])
				{
					$on_bbcode = explode('||', $row['shout_bbcode']);
					$options = 0;
					$uid = $bitfield = '';
					$message = $on_bbcode[0] . $this->language->lang('SHOUT_EXEMPLE') . $on_bbcode[1];
					generate_text_for_storage($message, $uid, $bitfield, $options, true, false, true);
					$message = generate_text_for_display($message, $uid, $bitfield, $options);
				}
				else
				{
					$on_bbcode[0] = '';
					$on_bbcode[1] = '';
					$message = $this->language->lang('SHOUT_EXEMPLE');
				}

				$response->send(array(
					'id'		=> $val_id,
					'name'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
					'before'	=> $on_bbcode[0],
					'after'		=> $on_bbcode[1],
					'message'	=> $message,
				), true);
				$this->db->sql_freeresult($result);
			break;

			case 'online':
				$data = $this->shoutbox->shout_online();

				$response->send(array(
					'title'		=> $data['l_online'],
					'liste'		=> $data['userlist'],
				), true);
			break;

			case 'rules':
				$data = $this->shoutbox->shout_rules($val_priv);

				$response->send(array(
					'sort'		=> $data['sort'],
					'texte'		=> $data['text'],
				), true);
			break;

			case 'preview_rules':
				$rules = $this->request->variable('content', '', true);
				$options = 0;
				$uid = $bitfield = '';
				generate_text_for_storage($rules, $uid, $bitfield, $options, true, false, true);
				$rules = $this->shoutbox->replace_shout_url(generate_text_for_display($rules, $uid, $bitfield, $options));

				$response->send(array(
					'content'	=> $rules,
				), true);
			break;

			case 'date_format':
				$date = $this->request->variable('date', '', true);
				$date = ($date == 'custom') ? $this->config['shout_dateformat'] : $date;

				$response->send(array(
					'format'	=> $date,
					'date'		=> $this->user->format_date(time() - 60 * 61, $date),
					'date2'		=> $this->user->format_date(time() - 60 * 60 * 60, $date),
				), true);
			break;

			case 'action_sound':
				$on_sound = $this->request->variable('sound', 1);
				$shout = json_decode($this->user->data['user_shout']);
				$on_sound = ($shout->user == 2) ? $on_sound : $shout->user;
				$content = array();
				switch ($on_sound)
				{
					// Turn off the sounds
					case 1:
						$content = array(
							'type'		=> 0,
							'classOut'	=> 'button_shout_sound',
							'classIn'	=> 'button_shout_sound_off',
							'title'		=> $this->language->lang('SHOUT_CLICK_SOUND_ON'),
						);
					break;
					// Turn on the sounds
					case 0:
						$content = array(
							'type'		=> 1,
							'classOut'	=> 'button_shout_sound_off',
							'classIn'	=> 'button_shout_sound',
							'title'		=> $this->language->lang('SHOUT_CLICK_SOUND_OFF'),
						);
					break;
				}
				$user_shout = array(
					'user'		=> $content['type'],
					'new'		=> $shout->new,
					'new_priv'	=> $shout->new_priv,
					'error'		=> $shout->error,
					'del'		=> $shout->del,
					'add'		=> $shout->add,
					'edit'		=> $shout->edit,
					'index'		=> $shout->index,
					'forum'		=> $shout->forum,
					'topic'		=> $shout->topic,
				);

				$sql = 'UPDATE ' . USERS_TABLE . "
					SET user_shout = '" . $this->db->sql_escape(json_encode($user_shout)) . "'
						WHERE user_id = $userid";
				$this->db->sql_query($sql);

				$response->send($content, true);
			break;

			case 'cite':
				$sql = $this->db->sql_build_query('SELECT', array(
					'SELECT'	=> 'user_id, user_type',
					'FROM'		=> array(USERS_TABLE => ''),
					'WHERE'		=> 'user_id = ' . $val_id,
				));
				$result = $this->shoutbox->shout_sql_query($sql, true, 1);
				if (!$result)
				{
					break;
				}
				$row = $this->db->sql_fetchrow($result);
				if (!$row || $row['user_type'] == USER_IGNORE)
				{
					$content = array(
						'type'		=> 0,
						'message'	=> $this->language->lang('NO_USER'),
					);
				}
				else
				{
					$content = array(
						'type'		=> 1,
						'id'		=> $row['user_id'],
					);
				}
				$this->db->sql_freeresult($result);

				$response->send($content, true);
			break;

			case 'action_user':
				if (!$is_user || $val_id == ANONYMOUS || !$val_id)
				{
					$response->send(array(
						'type'		=> 0,
						'message'	=> $this->language->lang('NO_ACTION_PERM'),
					), true);
				}
				else
				{
					$sql_ary = array(
						'SELECT'	=> 'z.*, u.user_id, u.user_type, u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height',
						'FROM'		=> array(USERS_TABLE => 'u'),
						'LEFT_JOIN'	=> array(
							array(
								'FROM'	=> array(ZEBRA_TABLE => 'z'),
								'ON'	=> "u.user_id = z.zebra_id AND z.user_id = $userid",
							),
						),
						'WHERE'		=> "u.user_id = $val_id",
					);
					$result = $this->shoutbox->shout_sql_query($this->db->sql_build_query('SELECT', $sql_ary), true, 1);
					if (!$result)
					{
						break;
					}
					$row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);
					if (!$row)
					{
						$content = array(
							'type'		=> 1,
						);
					}
					else if ($row['user_type'] == USER_IGNORE)
					{
						$content = array(
							'type'		=> 2,
							'username'	=> get_username_string('no_profile', $row['user_id'], $row['username'], $row['user_colour']),
							'message'	=> $this->language->lang('SHOUT_USER_NONE'),
						);
					}
					else
					{
						// Construct urls to be displayed via javascript
						$url_message = $url_del_to = $url_del = false;
						// administrators & moderators can always use this part
						$inp = ($this->auth->acl_get('u_shout_post_inp') || $this->auth->acl_get('a_') || $this->auth->acl_get('m_')) ? true : false;
						// Founders protections
						$go_founder = ($row['user_type'] != USER_FOUNDER || $this->user->data['user_type'] == USER_FOUNDER) ? true : false;
						$robot = $this->shoutbox->construct_action_shout(0);
						$tpl_span = '<span title="">';

						if ($inp)
						{
							$url_message = 'onclick="shoutbox.personalMsg();return false;" title="' . $this->language->lang('SHOUT_ACTION_MSG') . '">' . $tpl_span . $this->language->lang('SHOUT_ACTION_MSG');
							$url_del_to = 'onclick="if(confirm(\'' . $this->language->lang('SHOUT_ACTION_DEL_TO_EXPLAIN') . '\'))shoutbox.delReqTo(' . $userid . ');return false;" title="' . $this->language->lang('SHOUT_ACTION_DEL_TO') . '">' . $tpl_span . $this->language->lang('SHOUT_ACTION_DEL_TO');
							$url_del = 'onclick="if(confirm(\'' . $this->language->lang('SHOUT_ACTION_DELETE_EXPLAIN') . '\'))shoutbox.delReq(' . $userid . ');return false;" title="' . $this->language->lang('SHOUT_ACTION_DELETE') . '">' . $tpl_span . $this->language->lang('SHOUT_ACTION_DELETE');
						}
						$url_profile = 'href="' . append_sid("{$board_url}memberlist.{$this->php_ext}", "mode=viewprofile&amp;u={$val_id}", false) . '" onclick="window.open(this.href);return false" title="' . $this->language->lang('SHOUT_ACTION_PROFIL') . ' ' . $this->language->lang('FROM') . ' ' . $row['username'] . '">' . $tpl_span . $this->language->lang('SHOUT_ACTION_PROFIL');
						$url_cite = 'onclick="shoutbox.citeMsg();return false;" title="' . $this->language->lang('SHOUT_ACTION_CITE_EXPLAIN') . '">' . $tpl_span . $this->language->lang('SHOUT_ACTION_CITE');
						$url_cite_m = 'onclick="shoutbox.citeMultiMsg(\'' . $row['username'] . '\', \'' . $row['user_colour'] . '\');return false;" title="' . $this->language->lang('SHOUT_ACTION_CITE_M_EXPLAIN') . '">' . $tpl_span . $this->language->lang('SHOUT_ACTION_CITE_M');
						$url_admin = 'href="' . append_sid("{$board_url}{$adm_path}index.{$this->php_ext}", "i=users&amp;mode=overview&amp;u=$val_id", true, $this->user->session_id) . '" onclick="window.open(this.href);return false" title="' . $this->language->lang('SHOUT_USER_ADMIN') . '">' . $tpl_span . $this->language->lang('SHOUT_USER_ADMIN');
						$url_modo = 'href="' . append_sid("{$board_url}mcp.{$this->php_ext}", "i=notes&amp;mode=user_notes&amp;u={$val_id}", true, $this->user->session_id) . '" onclick="window.open(this.href);return false" title="' . $this->language->lang('SHOUT_ACTION_MCP') . '">' . $tpl_span . $this->language->lang('SHOUT_ACTION_MCP');
						$url_ban = 'href="' . append_sid("{$board_url}mcp.{$this->php_ext}", "i=ban&amp;mode=user&amp;u={$val_id}", true, $this->user->session_id) . '" onclick="window.open(this.href);return false" title="' . $this->language->lang('SHOUT_ACTION_BAN') . '">' . $tpl_span . $this->language->lang('SHOUT_ACTION_BAN');
						$url_remove = 'onclick="if(confirm(\'' . $this->language->lang('SHOUT_ACTION_REMOVE_EXPLAIN') . '\'))shoutbox.removeMsg(' . $val_id . ');return false;" title="' . $this->language->lang('SHOUT_ACTION_REMOVE') . '">' . $tpl_span . $this->language->lang('SHOUT_ACTION_REMOVE');
						$url_perso = 'onclick="shoutbox.changePerso(' . $val_id . ');return false;" title="' . $this->language->lang('SHOUT_ACTION_PERSO') . '">' . $tpl_span . $this->language->lang('SHOUT_ACTION_PERSO');
						$url_robot = 'onclick="shoutbox.iH(\'shout_avatar\',\'\');shoutbox.robotMsg(' . $val_sort . ');return false;" title="' . $this->language->lang('SHOUT_ACTION_MSG_ROBOT', $this->config['shout_name_robot']) . '">' . $tpl_span . $this->language->lang('SHOUT_ACTION_MSG_ROBOT', $robot);

						$content = array(
							'type'			=> 3,
							'id'			=> $val_id,
							'sort'			=> $val_sort,
							'foe'			=> $row['foe'] ? true : false,
							'username'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'], '', append_sid("{$board_url}memberlist.{$this->php_ext}", "mode=viewprofile")),
							'avatar'		=> $this->shoutbox->shout_user_avatar($row, 60, true),
							'url_message'	=> $row['foe'] ? $this->language->lang('SHOUT_USER_IGNORE') : $url_message,
							'url_profile'	=> $url_profile,
							'url_cite'		=> $url_cite,
							'url_cite_m'	=> $url_cite_m,
							'retour'		=> ($this->auth->acl_get('a_user') || $this->auth->acl_get('m_') || ($this->auth->acl_get('m_ban') && $go_founder)) ? true : false,
							'url_admin'		=> $this->auth->acl_get('a_user') ? $url_admin : false,
							'url_modo'		=> $this->auth->acl_get('m_') ? $url_modo : false,
							'url_ban'		=> ($this->auth->acl_get('m_ban') && $go_founder) ? $url_ban : false,
							'url_remove'	=> (($this->auth->acl_get('a_') || $this->auth->acl_get('m_shout_delete')) && $go_founder) ? $url_remove : false,
							'url_perso'		=> (($this->auth->acl_get('a_') || $this->auth->acl_get('m_shout_personal')) && $go_founder) ? $url_perso : false,
							'url_del_to'	=> $url_del_to,
							'url_del'		=> $url_del,
							'url_robot'		=> ($this->auth->acl_get('a_') || $this->auth->acl_get('m_shout_robot')) ? $url_robot : '',
						);
					}

					$response->send($content, true);
				}
			break;

			case 'action_post':
				// Important! initialize
				$go = $robot = $friend = false;
				$shout_info = 0;
				// Administrators and moderators can always post personnal messages
				if ($this->auth->acl_get('u_shout_post_inp') || $this->auth->acl_get('m_shout_robot') || $this->auth->acl_get('a_') || $this->auth->acl_get('m_'))
				{
					$message = $this->request->variable('message', '', true);
					// any user id
					if (!$val_id)
					{
						$response->send(array(
							'type'		=> 0,
						), true);
					}
					// post a robot message
					else if ($val_id == 1)
					{
						if ($this->auth->acl_get('a_') || $this->auth->acl_get('m_shout_robot'))
						{
							$robot = true;
							$go = true;
							$val_id = $userid = 0;
						}
						// no perm, out...
						else
						{
							$response->send(array(
								'type'		=> 0,
							), true);
						}
					}
					// post a personal message
					else if ($val_id > 1)
					{
						$sql = $this->db->sql_build_query('SELECT', array(
							'SELECT'	=> 'u.user_id, u.user_type, z.friend, z.foe',
							'FROM'		=> array(USERS_TABLE => 'u'),
							'LEFT_JOIN'	=> array(
								array(
									'FROM'	=> array(ZEBRA_TABLE => 'z'),
									'ON'	=> "z.zebra_id = u.user_id AND z.user_id = $userid",
								),
							),
							'WHERE'		=> "u.user_id = $val_id",
						));
						$result = $this->shoutbox->shout_sql_query($sql, true, 1);
						if (!$result)
						{
							break;
						}
						$row = $this->db->sql_fetchrow($result);
						// user id don't exist or ignore
						if (!$row || $row['user_type'] == USER_IGNORE)
						{
							$response->send(array(
								'type'		=> 0,
							), true);
						}
						// user is foe
						else if ($row['foe'])
						{
							$response->send(array(
								'type'		=> 2,
								'message'	=> $this->language->lang('SHOUT_USER_IGNORE'),
							), true);
						}
						// let's go
						else
						{
							$go = true;
							$friend = ($row['friend']) ? true : false;
							$shout_info = 65;
						}
						$this->db->sql_freeresult($result);
					}
					if ($go)
					{
						$message = $this->shoutbox->parse_shout_message($message, $val_on_priv, 'post', $robot);
						// Personalize message
						if ($this->user->data['shout_bbcode'] && $this->auth->acl_get('u_shout_bbcode_change') && ($val_id != 0))
						{
							$message = $this->shoutbox->personalize_shout_message($message);
						}

						// will be modified by generate_text_for_storage
						$options = 0;
						$uid = $bitfield = '';
						$allow_bbcode = ($this->auth->acl_get('u_shout_bbcode')) ? true : false;
						$allow_smilies = ($this->auth->acl_get('u_shout_smilies')) ? true : false;
						generate_text_for_storage($message, $uid, $bitfield, $options, $allow_bbcode, true, $allow_smilies);

						$sql_ary = array(
							'shout_text'				=> (string) $message,
							'shout_bbcode_uid'			=> $uid,
							'shout_bbcode_bitfield'		=> $bitfield,
							'shout_bbcode_flags'		=> $options,
							'shout_time'				=> (int) time(),
							'shout_user_id'				=> $userid,
							'shout_ip'					=> (string) $this->user->ip,
							'shout_robot_user'			=> (int) $val_id,
							'shout_robot'				=> 0,
							'shout_forum'				=> 0,
							'shout_info'				=> (int) $shout_info,
							'shout_inp'					=> (int) $val_id,
						);
						$sql = 'INSERT INTO ' . $shoutbox_table . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
						$this->db->sql_query($sql);
						$this->config->increment("shout_nr{$val_priv}", 1, true);
						$response->send(array(
							'type'		=> 1,
							'friend'	=> $friend,
							'message'	=> $this->language->lang('POSTED'),
						), true);
					}
				}
				else
				{
					$response->send(array(
						'type'		=> 0,
						'message'	=> $this->language->lang('NO_ACTION_PERM'),
					), true);
				}
			break;

			case 'action_del':
				if ($val_id != $userid)
				{
					$content = array(
						'type'		=> 0,
						'message'	=> $this->language->lang('NO_ACTION_PERM'),
					);
				}
				else
				{
					// Delete all personnal messages of this user
					$sql = 'DELETE FROM ' . $shoutbox_table . '
						WHERE shout_user_id = ' . $userid . '
							AND shout_inp <> 0';
					$result = $this->shoutbox->shout_sql_query($sql);
					if (!$result)
					{
						break;
					}
					$deleted = $this->db->sql_affectedrows();
					if (!$deleted)
					{
						$content = array(
							'type'		=> 1,
							'message'	=> $this->language->lang('SHOUT_ACTION_DEL_NO'),
						);
					}
					else
					{
						// For reload the message to everybody
						$this->shoutbox->update_shout_messages($shoutbox_table, 1);
						$this->config->increment("shout_del_user{$val_priv}", $deleted, true);
						$content = array(
							'type'		=> 1,
							'message'	=> $this->language->lang('SHOUT_ACTION_DEL_REP') . ' ' . $this->language->lang($this->shoutbox->plural('NUMBER_MESSAGE', $deleted), $deleted),
						);
					}
				}

				$response->send($content, true);
			break;

			case 'action_del_to':
				if ($val_id != $userid)
				{
					$content = array(
						'type'		=> 0,
						'message'	=> $this->language->lang('NO_ACTION_PERM'),
					);
				}
				else
				{
					// Delete all personnal messages to this user
					$sql = 'DELETE FROM ' . $shoutbox_table . "
						WHERE shout_inp = $userid
							AND shout_user_id <> $userid";
					$result = $this->shoutbox->shout_sql_query($sql);
					if (!$result)
					{
						break;
					}
					$deleted = $this->db->sql_affectedrows();
					if (!$deleted)
					{
						$content = array(
							'type'		=> 1,
							'message'	=> $this->language->lang('SHOUT_ACTION_DEL_NO'),
						);
					}
					else
					{
						$this->shoutbox->update_shout_messages($shoutbox_table, 1);
						$this->config->increment("shout_del_user{$val_priv}", $deleted, true);
						$content = array(
							'type'		=> 1,
							'message'	=> $this->language->lang('SHOUT_ACTION_DEL_REP') . ' ' . $this->language->lang($this->shoutbox->plural('NUMBER_MESSAGE', $deleted), $deleted),
						);
					}
				}

				$response->send($content, true);
			break;

			case 'action_remove':
				if ($this->auth->acl_get('a_shout_manage') || $this->auth->acl_get('m_shout_delete'))
				{
					// Delete all messages of this user
					$sql = 'DELETE FROM ' . $shoutbox_table . "
						WHERE shout_user_id = $val_id
							OR shout_robot_user = $val_id
							OR shout_inp = $val_id";
					$result = $this->shoutbox->shout_sql_query($sql);
					if (!$result)
					{
						break;
					}
					$deleted = $this->db->sql_affectedrows();
					if (!$deleted)
					{
						$content = array(
							'type'		=> 0,
							'message'	=> $this->language->lang('SHOUT_ACTION_REMOVE_NO'),
						);
					}
					else
					{
						$this->shoutbox->update_shout_messages($shoutbox_table, 1);
						$this->config->increment("shout_del_user{$val_priv}", $deleted, true);
						$content = array(
							'type'		=> 1,
							'message'	=> $this->language->lang('SHOUT_ACTION_REMOVE_REP') . ' ' . $this->language->lang($this->shoutbox->plural('NUMBER_MESSAGE', $deleted), $deleted),
						);
					}
				}
				else
				{
					$content = array(
						'type'		=> 0,
						'message'	=> $this->language->lang('NO_SHOUT_DEL'),
					);
				}

				$response->send($content, true);
			break;

			case 'delete':
				$post = $this->request->variable('post', 0);
				if (!$post)
				{
					$this->shoutbox->shout_error('NO_SHOUT_ID');
					break;
				}
				else if ($userid == ANONYMOUS)
				{
					$this->shoutbox->shout_error('NO_DELETE_PERM');
					break;
				}
				else
				{
					// If someone can delete all messages, he can delete it's messages :)
					$can_delete_all = ($this->auth->acl_get('m_shout_delete') || $this->auth->acl_get("a_shout_{$val_auth}")) ? true : false;
					$can_delete = $can_delete_all ? true : $this->auth->acl_get('u_shout_delete_s');
					
					$sql = 'SELECT shout_user_id
						FROM ' . $shoutbox_table . '
							WHERE shout_id = ' . $post;
					$result = $this->shoutbox->shout_sql_query($sql, true, 1);
					if (!$result)
					{
						break;
					}
					$on_id = $this->db->sql_fetchfield('shout_user_id');
					$this->db->sql_freeresult($result);
					
					if (!$can_delete && ($userid == $on_id))
					{
						$this->shoutbox->shout_error('NO_DELETE_PERM_S');
						break;
					}
					else if (!$can_delete_all && $can_delete && ($userid != $on_id))
					{
						$this->shoutbox->shout_error('NO_DELETE_PERM_T');
						break;
					}
					else if (!$can_delete)
					{
						$this->shoutbox->shout_error('NO_DELETE_PERM');
						break;
					}
					else if (($can_delete && ($userid == $on_id)) || $can_delete_all)
					{
						// Lets delete this post :D
						$sql = 'DELETE FROM ' . $shoutbox_table . '
							WHERE shout_id = ' . (int) $post;
						$this->db->sql_query($sql);

						$this->shoutbox->update_shout_messages($shoutbox_table, $post);
						$this->config->increment("shout_del_user{$val_priv}", 1, true);
						$response->send(array(
							'type'	=> 1,
							'post'	=> $post,
							'sort'	=> $val_perm,
						), true);
					}
					else
					{
						$this->shoutbox->shout_error('NO_DELETE_PERM');
						break;
					}
				}
			break;

			case 'purge':
				if (!$this->auth->acl_get('m_shout_purge') && !$this->auth->acl_get('a_shout_manage'))
				{
					$this->shoutbox->shout_error('NO_PURGE_PERM');
					break;
				}
				else
				{
					$sql = 'DELETE FROM ' . $shoutbox_table;
					$result = $this->shoutbox->shout_sql_query($sql);
					if (!$result)
					{
						break;
					}
					$deleted = $this->db->sql_affectedrows();

					$this->config->increment("shout_del_purge{$val_priv}", $deleted, true);
					$this->shoutbox->post_robot_shout($userid, $this->user->ip, $val_on_priv, true, false, false, false);
					$content = array(
						'type'	=> 1,
						'nr'	=> $deleted,
					);
				}

				$response->send($content, true);
			break;

			case 'purge_robot':
				if (!$this->auth->acl_get('m_shout_purge') && !$this->auth->acl_get('a_shout_manage'))
				{
					$this->shoutbox->shout_error('NO_PURGE_ROBOT_PERM');
					break;
				}
				else
				{
					$sort_on = explode(', ', $this->config["shout_robot_choice{$val_priv}"] . ', 4');
					
					$sql = 'DELETE FROM ' . $shoutbox_table . '
						WHERE ' . $this->db->sql_in_set('shout_info', $sort_on, false, true);
					$result = $this->shoutbox->shout_sql_query($sql);
					if (!$result)
					{
						break;
					}
					$deleted = $this->db->sql_affectedrows();
					
					$this->config->increment("shout_del_purge{$val_priv}", $deleted, true);
					$this->shoutbox->post_robot_shout($userid, $this->user->ip, $val_on_priv, true, true, false, false);
					$content = array(
						'type'	=> 1,
						'nr'	=> $deleted,
					);
				}

				$response->send($content, true);
			break;

			case 'edit':
				$shout_id = $this->request->variable('shout_id', 0);
				$message = $this->request->variable('chat_message', '', true);

				// will be modified by generate_text_for_storage
				$options = 0;
				$uid = $bitfield = '';
				$allow_urls = true;

				// Protect by checking permissions
				$allow_bbcode = $this->auth->acl_get('u_shout_bbcode') ? true : false;
				$allow_smilies = $this->auth->acl_get('u_shout_smilies') ? true : false;
				// If someone can edit all messages, he can edit it's messages :) (if errors in permissions set)
				$can_edit_all = ($this->auth->acl_get('m_shout_edit_mod') || $this->auth->acl_get("a_shout_{$val_auth}")) ? true : false;
				$can_edit = $can_edit_all ? true : $this->auth->acl_get('u_shout_edit');
				$ok_edit = false;

				// We need to be sure its this users his shout.
				$sql = 'SELECT shout_user_id
					FROM ' . $shoutbox_table . '
						WHERE shout_id = ' . $shout_id;
				$result = $this->shoutbox->shout_sql_query($sql, true, 1);
				if (!$result)
				{
					break;
				}
				$on_id = (int) $this->db->sql_fetchfield('shout_user_id');
				$this->db->sql_freeresult($result);
				// If not able to edit all messages
				if (!$can_edit_all)
				{
					// Not his shout, display error
					if (!$on_id || $on_id != $userid)
					{
						$this->shoutbox->shout_error('NO_EDIT_PERM');
						break;
					}
					else
					{
						$ok_edit = true;
					}
				}
				else if ($can_edit)
				{
					$ok_edit = true;
				}

				if (!$ok_edit)
				{
					$this->shoutbox->shout_error('NO_EDIT_PERM');
					break;
				}

				// First verification of empty message
				if ($message == '')
				{
					$this->shoutbox->shout_error('MESSAGE_EMPTY');
					break;
				}

				// Don't parse img if unautorised and return img url only
				if ((strpos($message, '[img]') !== false) && (strpos($message, '[/img]') !== false) && !$this->auth->acl_get('u_shout_image'))
				{
					$message = str_replace(array('[img]', '[/img]'), '', $message);
				}
				// Multi protections at this time...
				$message = $this->shoutbox->parse_shout_message($message, $val_on_priv, 'edit', false);

				generate_text_for_storage($message, $uid, $bitfield, $options, $allow_bbcode, $allow_urls, $allow_smilies);

				$sql_ary = array(
					'shout_text'				=> (string) $message,
					'shout_bbcode_uid'			=> $uid,
					'shout_bbcode_bitfield'		=> $bitfield,
					'shout_bbcode_flags'		=> $options,
				);

				$sql = 'UPDATE ' . $shoutbox_table . '
					SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
						WHERE shout_id = ' . $shout_id;
				$result = $this->shoutbox->shout_sql_query($sql);
				if (!$result)
				{
					break;
				}

				// For reload the message to everybody
				$this->shoutbox->update_shout_messages($shoutbox_table, 1);
				$message = generate_text_for_display($message, $uid, $bitfield, $options);
				$content = array(
					'type'		=> 2,
					'mode'		=> $mode,
					'shout_id'	=> $shout_id,
					'message'	=> $this->language->lang('EDIT_DONE'),
					'texte'		=> $message,
				);

				$response->send($content, true);
			break;

			case 'post':
				$message = $this->request->variable('chat_message', '', true);
				$name = $this->request->variable('name', '', true);
				$cite = $this->request->variable('cite', 0);
				$shout_info = ($cite > 1) ? 66 : 0;

				// will be modified by generate_text_for_storage
				$options = 0;
				$uid = $bitfield = '';

				// Checking permissions
				$allow_bbcode = $this->auth->acl_get('u_shout_bbcode') ? true : false;
				$allow_smilies = $this->auth->acl_get('u_shout_smilies') ? true : false;

				if (!$this->auth->acl_get('u_shout_post'))
				{
					$this->shoutbox->shout_error('NO_POST_PERM');
					break;
				}

				// Flood control, not in private
				if (!$this->auth->acl_get('u_shout_ignore_flood') && !$val_on_priv)
				{
					$current_time = time();
					$sql = $this->db->sql_build_query('SELECT', array(
						'SELECT'	=> 'MAX(shout_time) AS last_post_time',
						'FROM'		=> array($shoutbox_table => ''),
						'WHERE'		=> (!$this->user->data['is_registered']) ? "shout_ip = '" . $this->db->sql_escape((string) $this->user->ip) . "'" : 'shout_user_id = ' . $userid,
					));
					$result = $this->shoutbox->shout_sql_query($sql);
					if (!$result)
					{
						break;
					}
					if ($row = $this->db->sql_fetchrow($result))
					{
						if ($row['last_post_time'] > 0 && ($current_time - $row['last_post_time']) < $this->config['shout_flood_interval'])
						{
							$this->db->sql_freeresult($result);
							$this->shoutbox->shout_error('FLOOD_ERROR');
							break;
						}
					}
					$this->db->sql_freeresult($result);
				}

				// Don't parse img if unautorised and return img url only
				if ((strpos($message, '[/img]') !== false) && !$this->auth->acl_get('u_shout_image'))
				{
					$message = str_replace(array('[img]', '[/img]'), '', $message);
				}
				// Multi protections at this time...
				$message = $this->shoutbox->parse_shout_message($message, $val_on_priv, 'post', false);

				// Personalize message
				if ($this->user->data['is_registered'] && $this->user->data['shout_bbcode'] && $this->auth->acl_get('u_shout_bbcode_change'))
				{
					$message = $this->shoutbox->personalize_shout_message($message);
				}

				generate_text_for_storage($message, $uid, $bitfield, $options, $allow_bbcode, true, $allow_smilies);

				// For guest, add a random number from ip after name
				if (!$this->user->data['is_registered'])
				{
					$name = $this->shoutbox->add_random_ip($name);
				}

				$sql_ary = array(
					'shout_time'			=> time(),
					'shout_user_id'			=> (int) $userid,
					'shout_ip'				=> (string) $this->user->ip,
					'shout_text'			=> (string) $message,
					'shout_text2'			=> (string) $name,
					'shout_bbcode_uid'		=> $uid,
					'shout_bbcode_bitfield'	=> $bitfield,
					'shout_bbcode_flags'	=> $options,
					'shout_robot_user'		=> $cite,
					'shout_robot'			=> 0,
					'shout_forum'			=> 0,
					'shout_info'			=> $shout_info,
				);

				$sql = 'INSERT INTO ' . $shoutbox_table . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
				$result = $this->shoutbox->shout_sql_query($sql);
				if (!$result)
				{
					break;
				}
				$this->config->increment("shout_nr{$val_priv}", 1, true);
				
				if ($this->config["shout_on_cron{$val_priv}"])
				{
					if ($this->config["shout_max_posts{$val_priv}"] > 0)
					{
						$this->shoutbox->delete_shout_posts($val_on_priv);
					}
				}

				$response->send(array(
					'mode'		=> $mode,
					'cite'		=> $cite,
					'type'		=> 1,
					'message'	=> $this->language->lang('POSTED'),
				), true);
			break;

			case 'check':
			case 'check_pop':
			case 'check_priv':
				// !Important, always read the good permission before
				if (!$this->auth->acl_get("u_shout{$val_perm}"))
				{
					$this->shoutbox->shout_error("NO_VIEW{$val_privat}_PERM");
					break;
				}

				if ($this->config['shout_enable_robot'] && $this->config['shout_cron_hour'] == date('H'))
				{
					// Say hello Mr Robot :-)
					$this->shoutbox->hello_robot_shout();
					// Wish birthdays Mr Robot :-)
					$this->shoutbox->robot_birthday_shout();
				}
				$cookie_bot = $this->request->variable($this->config['cookie_name'] . '_set-robot', 'on', false, /** @scrutinizer ignore-type */ \phpbb\request\request_interface::COOKIE);
				$bot = ($cookie_bot == 'off') ? false : true;

				// Read the forums permissions
				if (!$bot)
				{
					$sql_where = 'shout_robot = 0';
				}
				else
				{
					if ($this->auth->acl_gets('a_', 'm_'))
					{
						$sql_where = 'shout_forum = 0 OR (shout_forum <> 0)';
					}
					else
					{
						$sql_where = ($this->auth->acl_getf_global('f_read')) ? '(' . $this->db->sql_in_set('shout_forum', array_keys($this->auth->acl_getf('f_read', true)), false, true) . ' OR shout_forum = 0)' : 'shout_forum = 0';
					}
				}

				// count and add personal messages if needed
				if (!$is_user)
				{
					$sql_and = ' AND shout_inp = 0';
				}
				else
				{
					$sql_and = " AND (shout_inp = 0 OR (shout_inp = $userid OR shout_user_id = $userid))";
				}

				$sql = $this->db->sql_build_query('SELECT', array(
					'SELECT'	=> 'shout_time',
					'FROM'		=> array($shoutbox_table => ''),
					'WHERE'		=> $sql_where . $sql_and,
					'ORDER_BY'	=> 'shout_time DESC',
				));
				$result = $this->shoutbox->shout_sql_query($sql, true, 1);
				if (!$result)
				{
					break;
				}
				$time = $this->db->sql_fetchfield('shout_time');
				$this->db->sql_freeresult($result);
				// check just with the last 4 numbers
				$on_time = substr($time, 6, 4);

				$response->send(array(
					't'	=> $on_time,
				), true);
			break;

			case 'view':
			case 'view_pop':
			case 'view_priv':
				// Permissions verification
				if (!$this->auth->acl_get("u_shout{$val_perm}"))
				{
					$this->shoutbox->shout_error("NO_VIEW{$val_privat}_PERM");
					break;
				}

				$start = $this->request->variable('start', 0);
				$i = 0;
				$content = array(
					'messages'	=> array(),
				);

				$dateformat = $this->config['shout_dateformat'];
				if ($is_user)
				{
					$shout2 = json_decode($this->user->data['user_shoutbox']);
					$dateformat = ($shout2->dateformat != '') ? $shout2->dateformat : $dateformat;
				}
				$cookie_bot = $this->request->variable($this->config['cookie_name'] . '_set-robot', 'on', false, /** @scrutinizer ignore-type */ \phpbb\request\request_interface::COOKIE);
				$bot = ($cookie_bot == 'off') ? false : true;

				// Display avatars ?
				$see_avatar = ($this->shoutbox->compatibles_browsers() === 2) ? false : true;

				// Prevents some errors for the allocation of permissions
				// If someone can edit all messages, he can edit its own messages :)
				$can_edit_all = ($this->auth->acl_get('m_shout_edit_mod') || $this->auth->acl_get("a_shout_{$val_auth}")) ? true : false;
				$can_edit = $can_edit_all ? true : $this->auth->acl_get('u_shout_edit');

				// If someone can delete all messages, he can delete its own messages :)
				$can_delete_all = ($this->auth->acl_get('m_shout_delete') || $this->auth->acl_get("a_shout_{$val_auth}")) ? true : false;
				$can_delete = $can_delete_all ? true : $this->auth->acl_get('u_shout_delete_s');

				// If someone can view all ip, he can view its own ip :)
				$can_info_all = ($this->auth->acl_get('m_shout_info') || $this->auth->acl_get("a_shout_{$val_auth}")) ? true : false;
				$can_info = $can_info_all ? true : $this->auth->acl_get('u_shout_info_s');

				// Read the forums permissions
				if (!$bot)
				{
					$sql_where = 's.shout_robot = 0';
				}
				else
				{
					if ($this->auth->acl_gets('a_', 'm_'))
					{
						$sql_where = 's.shout_forum = 0 OR (s.shout_forum <> 0)';
					}
					else
					{
						$sql_where = $this->auth->acl_getf_global('f_read') ? $this->db->sql_in_set('s.shout_forum', array_keys($this->auth->acl_getf('f_read', true)), false, true) . ' OR s.shout_forum = 0' : 's.shout_forum = 0';
					}
				}

				// Add personal messages if needed
				if ($is_user)
				{
					$sql_and = ' AND (s.shout_inp = 0 OR (s.shout_inp = ' . $userid . ' OR s.shout_user_id = ' . $userid . '))';
				}
				else
				{
					$sql_and = ' AND s.shout_inp = 0';
				}

				$sql = $this->db->sql_build_query('SELECT', array(
					'SELECT'	=> 's.*, u.user_id, u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height, u.user_type, v.user_id as x_user_id, v.username as x_username, v.user_colour as x_user_colour, v.user_avatar as x_user_avatar, v.user_avatar_type as x_user_avatar_type, v.user_avatar_width as x_user_avatar_width, v.user_avatar_height as x_user_avatar_height, v.user_type as x_user_type',
					'FROM'		=> array($shoutbox_table => 's'),
					'LEFT_JOIN'	=> array(
						array(
							'FROM'	=> array(USERS_TABLE => 'u'),
							'ON'	=> 'u.user_id = s.shout_user_id',
						),
						array(
							'FROM'	=> array(USERS_TABLE => 'v'),
							'ON'	=> 'v.user_id = s.shout_robot_user',
						),
					),
					'WHERE'		=> $sql_where . $sql_and,
					'ORDER_BY'	=> 's.shout_id DESC',
				));
				$result = $this->shoutbox->shout_sql_query($sql, true, (int) $this->config["shout_non_ie_nr{$val_sort_on}"], $start);
				if (!$result)
				{
					break;
				}
				while ($row = $this->db->sql_fetchrow($result))
				{
					// Initialize additional data
					$row = array_merge($row, array(
						'delete'		=> false,
						'edit'			=> false,
						'show_ip'		=> false,
						'avatar_img'	=> false,
						'msg_plain'		=> false,
						'on_ip'			=> false,
						'is_user'		=> (($row['shout_user_id'] > 1) && ($row['shout_user_id'] != $userid)) ? true : false,
						'name'			=> $row['username'],
					));

					// Double protect private messages to both users concerned
					if ($row['shout_inp'])
					{
						if (!$is_user || ($row['shout_inp'] != $userid) && ($row['shout_user_id'] != $userid))
						{
							// No permission to see it, continue...
							continue;
						}
					}

					if ($see_avatar)
					{
						if (!$row['shout_user_id'] && $row['shout_robot_user'])
						{
							$row_avatar = array(
								'user_id'				=> $row['x_user_id'],
								'username'				=> $row['x_username'],
								'user_type'				=> $row['x_user_type'],
								'user_avatar'			=> $row['x_user_avatar'],
								'user_avatar_type'		=> $row['x_user_avatar_type'],
								'user_avatar_width'		=> $row['x_user_avatar_width'],
								'user_avatar_height'	=> $row['x_user_avatar_height'],
							);
							$row['avatar_img'] = $this->shoutbox->shout_user_avatar($row_avatar, $this->config['shout_avatar_height']);
						}
						else
						{
							$row['avatar_img'] = $this->shoutbox->shout_user_avatar($row, $this->config['shout_avatar_height']);
						}
					}

					// Message made by anonymous
					$row['username'] = ($row['shout_user_id'] == ANONYMOUS) ? $row['shout_text2'] : $row['username'];
					$row['username'] = $this->shoutbox->construct_action_shout($row['user_id'], $row['username'], $row['user_colour']);
					$row['on_time'] = $this->user->format_date($row['shout_time'], $dateformat);

					// Checks permissions for delete, edit and show_ip
					if ($is_user)
					{
						if ($can_delete_all || ($row['shout_user_id'] == $userid) && $can_delete)
						{
							$row['delete'] = true;
						}
						if ($can_edit_all || ($row['shout_user_id'] == $userid) && $can_edit)
						{
							$row['edit'] = true;
							$row['msg_plain'] = $row['shout_text'];
							decode_message($row['msg_plain'], $row['shout_bbcode_uid']);
						}
						if ($can_info_all || ($row['shout_user_id'] == $userid) && $can_info)
						{
							$row['show_ip'] = true;
							$row['on_ip'] = $row['shout_ip'];
						}
					}

					$row['shout_text'] = $this->shoutbox->shout_text_for_display($row, $val_sort, false);

					$content['messages'][$i] = array(
						'shoutId'		=> $row['shout_id'],
						'shoutTime'		=> $row['on_time'],
						'timeMsg'		=> $row['shout_time'],
						'shoutText'		=> $row['shout_text'],
						'username'		=> $row['username'],
						'isUser'		=> $row['is_user'],
						'name'			=> $row['name'],
						'colour'		=> $row['user_colour'],
						'avatar'		=> $row['avatar_img'],
						'deletemsg'		=> $row['delete'],
						'edit'			=> $row['edit'],
						'showIp'		=> $row['show_ip'],
						'msgPlain'		=> $row['msg_plain'],
						'shoutIp'		=> $row['on_ip'],
					);
					$i++;
				}
				$this->db->sql_freeresult($result);

				$sql = $this->db->sql_build_query('SELECT', array(
					'SELECT'	=> 's.shout_time',
					'FROM'		=> array($shoutbox_table => 's'),
					'WHERE'		=> $sql_where . $sql_and,
					'ORDER_BY'	=> 's.shout_id DESC',
				));
				$result_time = $this->shoutbox->shout_sql_query($sql, true, 1);
				if (!$result_time)
				{
					break;
				}
				$last_time = $this->db->sql_fetchfield('shout_time');
				$this->db->sql_freeresult($result_time);
				// check just with the last 4 numbers
				$last_time = substr($last_time, 6, 4);
				// The number of total messages for pagination
				$number = $this->shoutbox->shout_pagination($shoutbox_table, $val_priv, $bot);

				$content = array_merge($content, array(
					'total'		=> $i,
					'last'		=> $last_time,
					'number'	=> $number,
				));

				$response->send($content, true);
			break;
		}
	}
}
