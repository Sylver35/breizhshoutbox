<?php
/**
*
* @package Breizh Shoutbox Extension
* @copyright (c) 2018-2020 Sylver35  https://breizhcode.com
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace sylver35\breizhshoutbox\controller;
use sylver35\breizhshoutbox\core\shoutbox;
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\db\driver\driver_interface as db;
use phpbb\template\template;
use phpbb\auth\auth;
use phpbb\user;
use phpbb\language\language;
use phpbb\request\request;
use phpbb\pagination;

class main
{
	/* @var \sylver35\breizhshoutbox\core\shoutbox */
	protected $shoutbox;

	/** @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpEx */
	protected $php_ext;

	/**
	 * Constructor
	 */
	public function __construct(shoutbox $shoutbox, config $config, helper $helper, db $db, template $template, auth $auth, user $user, language $language, request $request, pagination $pagination, $root_path, $php_ext)
	{
		$this->shoutbox = $shoutbox;
		$this->config = $config;
		$this->helper = $helper;
		$this->db = $db;
		$this->template = $template;
		$this->auth = $auth;
		$this->user = $user;
		$this->language = $language;
		$this->request = $request;
		$this->pagination = $pagination;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function handle_private()
	{
		if ($this->auth->acl_get('u_shout_priv'))
		{
			$this->shoutbox->shout_display(3);

			$this->template->assign_vars(array(
				'S_IN_PRIV'				=> true,
				'S_IN_SHOUT_POP'		=> false,
				'S_IN_SHOUT_TEMP'		=> true,
				'S_DISPLAY_ONLINE_LIST'	=> true,
			));
			// Add to navlinks
			$this->template->assign_block_vars_array('navlinks', array(
				array(
					'FORUM_NAME'	=> $this->language->lang('SHOUTBOX_SECRET'),
					'U_VIEW_FORUM'	=> $this->helper->route('sylver35_breizhshoutbox_private'),
				),
			));
			page_header($this->language->lang('SHOUTBOX_SECRET'), true);
			return $this->helper->render('shout_private.html', $this->user->lang['SHOUTBOX_SECRET']);
		}
		else
		{
			meta_refresh(3, append_sid("{$this->root_path}index.{$this->php_ext}"));
			trigger_error($this->language->lang('NO_VIEW_PRIV_PERM'));
		}
	}

	public function handle_popup()
	{
		if ($this->auth->acl_get('u_shout_popup'))
		{
			$this->shoutbox->shout_display(1);
			$this->template->assign_vars(array(
				'S_IN_SHOUT_POP'	=> true,
				'S_IN_PRIV'			=> false,
				'S_IN_SHOUT_TEMP'	=> true,
				'ABBC3_EXTENSION'	=> ($this->shoutbox->abbc3_exist()) ? true : false,
			));
			return $this->helper->render('shout_popup.html', $this->language->lang('SHOUTBOX_POPUP'));
		}
		else
		{
			meta_refresh(3, append_sid("{$this->root_path}index.{$this->php_ext}"));
			trigger_error($this->language->lang('NO_SHOUT_POP'));
		}
	}

	public function handle_lateral()
	{
		if ($this->auth->acl_get('u_shout_lateral'))
		{
			$display = $this->shoutbox->shout_panel();
			if ($display)
			{
				$this->shoutbox->shout_display(1);
				return $this->helper->render('shout_popup.html', $this->language->lang('SHOUT_LATERAL'));
			}
		}
		else
		{
			$this->template->assign_vars(array(
				'KILL_LATERAL'	=> true,
			));
			return false;
		}
	}

	public function handle_config_shout()
	{
		if ($this->auth->acl_get('u_shout_post'))
		{
			$this->shoutbox->active_config_shoutbox();
			return $this->helper->render('shout_config.html', $this->language->lang('SHOUT_PANEL_USER'));
		}
		else
		{
			meta_refresh(3, append_sid("{$this->root_path}index.{$this->php_ext}"));
			trigger_error($this->language->lang('NO_VIEW_PERM'));
		}
	}

	public function shoutbox_smilies_pop()
	{
		$start = $this->request->variable('start', 0);
		$url = $this->helper->route('sylver35_breizhshoutbox_smilies_pop');

		$sql = 'SELECT COUNT(DISTINCT smiley_url) AS smilies_count
			FROM ' . SMILIES_TABLE . '
				WHERE display_on_shout = 0';
		$result = $this->db->sql_query($sql);
		$count = (int) $this->db->sql_fetchfield('smilies_count');
		$this->db->sql_freeresult($result);

		$sql = $this->db->sql_build_query('SELECT', array(
			'SELECT'	=> 'smiley_url, MIN(smiley_id) AS smiley_id, MIN(code) AS code, MIN(smiley_order) AS min_smiley_order, MIN(smiley_width) AS smiley_width, MIN(smiley_height) AS smiley_height, MIN(emotion) AS emotion, MIN(display_on_shout) AS display_on_shout',
			'FROM'		=> array(SMILIES_TABLE => ''),
			'WHERE'		=> 'display_on_shout = 0',
			'GROUP_BY'	=> 'smiley_url',
			'ORDER_BY'	=> 'min_smiley_order ASC',
		));
		$result = $this->db->sql_query_limit($sql, (int) $this->config['smilies_per_page'], $start);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('smilies', array(
				'SMILEY_CODE'		=> $row['code'],
				'SMILEY_EMOTION'	=> $row['emotion'],
				'SMILEY_WIDTH'		=> $row['smiley_width'],
				'SMILEY_HEIGHT'		=> $row['smiley_height'],
				'SMILEY_SRC'		=> generate_board_url() . '/' . $this->config['smilies_path'] . '/' . $row['smiley_url'],
			));
		}
		$this->db->sql_freeresult($result);

		$start = $this->pagination->validate_start($start, (int) $this->config['smilies_per_page'], $count);
		$this->pagination->generate_template_pagination($url, 'pagination', 'start', $count, (int) $this->config['smilies_per_page'], $start);

		$data = $this->shoutbox->get_version();
		$this->template->assign_vars(array(
			'S_IN_SHOUT_ACP'	=> true,
			'SHOUTBOX_VERSION'	=> $this->language->lang('SHOUTBOX_VERSION_ACP_COPY', $data['homepage'], $data['version']),
		));

		page_header($this->language->lang('SMILIES'));

		$this->template->set_filenames(array(
			'body' => '@sylver35_breizhshoutbox/shout_template.html')
		);

		page_footer();
	}
}
