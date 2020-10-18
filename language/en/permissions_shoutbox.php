<?php
/**
*
* Breizh Shoutbox Extension [English]
*
* @package language
* @version $Id: permissions_shoutbox.php 100
* @copyright (c) 2018-2020 Sylver35  https://breizhcode.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'ACL_CAT_SHOUT'					=> 'Breizh Shoutbox',

	'ACL_A_SHOUT_MANAGE'			=> 'Can manage and modify the settings of the shoutbox',
	'ACL_A_SHOUT_PRIV'				=> 'Can manage and modify the settings of the <strong>private shoutbox</strong>',

	'ACL_M_SHOUT_DELETE'			=> 'Can delete messages of all users',
	'ACL_M_SHOUT_EDIT_MOD'			=> 'Can edit messages of all users',
	'ACL_M_SHOUT_INFO'				=> 'Can see the IPs of all users',
	'ACL_M_SHOUT_PERSONAL'			=> 'Can change the personalization messages of user',
	'ACL_M_SHOUT_PURGE'				=> 'Can purge the shoutbox in front',
	'ACL_M_SHOUT_ROBOT'				=> 'Can create robot messages',

	'ACL_U_SHOUT_BBCODE'			=> 'Can use bbcode in messages',
	'ACL_U_SHOUT_BBCODE_CHANGE'		=> 'Can use the personalization messages',
	'ACL_U_SHOUT_CHARS'				=> 'Can use special characters',
	'ACL_U_SHOUT_COLOR'				=> 'Can use colors in messages',
	'ACL_U_SHOUT_DELETE_S'			=> 'Can delete his own messages',
	'ACL_U_SHOUT_EDIT'				=> 'Can edit his own posts',
	'ACL_U_SHOUT_HIDE'				=> 'Can cancel the information posted by the robot',
	'ACL_U_SHOUT_IGNORE_FLOOD'		=> 'Can ignore the flood limit',
	'ACL_U_SHOUT_IMAGE'				=> 'Can post images in messages',
	'ACL_U_SHOUT_INACTIV'			=> 'Can ignore inactivity and sleep down in the shoutbox',
	'ACL_U_SHOUT_INFO_S'			=> 'Can view IPs from himself',
	'ACL_U_SHOUT_LATERAL'			=> 'Can view the shoutbox in the lateral panel',
	'ACL_U_SHOUT_LIMIT_POST'		=> 'Can ignore the character limit in a message',
	'ACL_U_SHOUT_POPUP'				=> 'Can use the shoutbox in popup',
	'ACL_U_SHOUT_POST'				=> 'Can <strong>post</strong> messages in the shoutbox',
	'ACL_U_SHOUT_POST_INP'			=> 'Can post <strong>private messages</strong> in the shoutbox',
	'ACL_U_SHOUT_PRIV'				=> 'Can access to the <strong>private shoutbox</strong>',
	'ACL_U_SHOUT_SMILIES'			=> 'Can post smilies in messages',
	'ACL_U_SHOUT_VIEW'				=> 'Can <strong>view</strong> the shoutbox',
));
