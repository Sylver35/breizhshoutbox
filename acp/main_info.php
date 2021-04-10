<?php
/**
*
* @package Breizh Shoutbox Extension
* 
* @copyright (c) 2018-2021 Sylver35  https://breizhcode.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace sylver35\breizhshoutbox\acp;

class main_info
{
	public function module()
	{
		return array(
			'filename'	=> '\sylver35\breizhshoutbox\acp\main_module',
			'title'		=> 'ACP_SHOUTBOX',
			'modes'		=> array(
				// General settings
				'configs'		=> array(
					'title'	=> 'ACP_SHOUT_CONFIGS',
					'auth'	=> 'ext_sylver35/breizhshoutbox && acl_a_shout_manage',
					'cat'	=> array('ACP_SHOUT_GENERAL_CAT')
				),
				// Rules settings
				'rules'			=> array(
					'title'	=> 'ACP_SHOUT_RULES',
					'auth'	=> 'ext_sylver35/breizhshoutbox && acl_a_shout_manage',
					'cat'	=> array('ACP_SHOUT_GENERAL_CAT')
				),
				// Main shoutbox
				'overview'		=> array(
					'title'	=> 'ACP_SHOUT_OVERVIEW',
					'auth'	=> 'ext_sylver35/breizhshoutbox && acl_a_shout_manage',
					'cat'	=> array('ACP_SHOUT_PRINCIPAL_CAT')
				),
				'config_gen'	=> array(
					'title'	=> 'ACP_SHOUT_CONFIG_GEN',
					'auth'	=> 'ext_sylver35/breizhshoutbox && acl_a_shout_manage',
					'cat'	=> array('ACP_SHOUT_PRINCIPAL_CAT')
				),
				// Private shoutbox
				'private'		=> array(
					'title'	=> 'ACP_SHOUT_PRIVATE',
					'auth'	=> 'ext_sylver35/breizhshoutbox && acl_a_shout_priv',
					'cat'	=> array('ACP_SHOUT_PRIVATE_CAT')
				),
				'config_priv'	=> array(
					'title'	=> 'ACP_SHOUT_CONFIG_PRIV',
					'auth'	=> 'ext_sylver35/breizhshoutbox && acl_a_shout_priv',
					'cat'	=> array('ACP_SHOUT_PRIVATE_CAT')
				),
				// Popup shoutbox
				'popup'			=> array(
					'title'	=> 'ACP_SHOUT_POPUP',
					'auth'	=> 'ext_sylver35/breizhshoutbox && acl_a_shout_manage',
					'cat'	=> array('ACP_SHOUT_POPUP_CAT')
				),
				// Retractable lateral panel
				'panel'			=> array(
					'title'	=> 'ACP_SHOUT_PANEL',
					'auth'	=> 'ext_sylver35/breizhshoutbox && acl_a_shout_manage',
					'cat'	=> array('ACP_SHOUT_POPUP_CAT')
				),
				// Smilies
				'smilies'		=> array(
					'title'	=> 'ACP_SHOUT_SMILIES',
					'auth'	=> 'ext_sylver35/breizhshoutbox && acl_a_shout_manage',
					'cat'	=> array('ACP_SHOUT_SMILIES_CAT')
				),
				// Popup smilies
				'smilies_pop'	=> array(
					'title'	=> 'ACP_SHOUT_SMILIES_POP',
					'auth'	=> 'ext_sylver35/breizhshoutbox && acl_a_shout_manage',
					'cat'	=> array('ACP_SHOUT_SMILIES_CAT')
				),
				// Robot
				'robot'			=> array(
					'title'	=> 'ACP_SHOUT_ROBOT',
					'auth'	=> 'ext_sylver35/breizhshoutbox && acl_a_shout_manage',
					'cat'	=> array('ACP_SHOUT_ROBOT_CAT')
				),
			),
		);
	}
}
