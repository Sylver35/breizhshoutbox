<?php
/**
*
* Breizh Shoutbox Extension [French]
*
* @package language
* @version $Id: permissions_shoutbox.php 100
* @copyright (c) 2018-2021 Sylver35  https://breizhcode.com
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
// ’ « » “ ” …
//

$lang = array_merge($lang, array(
	'ACL_CAT_SHOUT'					=> 'Breizh Shoutbox',

	'ACL_A_SHOUT_MANAGE'			=> 'Peut administrer et modifier les paramètres de la shoutbox',
	'ACL_A_SHOUT_PRIV'				=> 'Peut administrer et modifier les paramètres de la <strong>shoutbox privée</strong>',

	'ACL_M_SHOUT_DELETE'			=> 'Peut supprimer les messages de tous les utilisateurs',
	'ACL_M_SHOUT_EDIT_MOD'			=> 'Peut éditer les messages de tous les utilisateurs',
	'ACL_M_SHOUT_INFO'				=> 'Peut voir les IPs de tous les utilisateurs',
	'ACL_M_SHOUT_PERSONAL'			=> 'Peut modifier la personnalisation des messages des utilisateurs',
	'ACL_M_SHOUT_ROBOT'				=> 'Peut créer des messages robot',

	'ACL_U_SHOUT_BBCODE'			=> 'Peut utiliser des bbcodes dans ses messages',
	'ACL_U_SHOUT_BBCODE_CUSTOM'		=> 'Peut utiliser tous les bbcodes custom dans ses messages',
	'ACL_U_SHOUT_BBCODE_CHANGE'		=> 'Peut utiliser la mise en forme des messages',
	'ACL_U_SHOUT_CHARS'				=> 'Peut utiliser les caractères spéciaux', 
	'ACL_U_SHOUT_COLOR'				=> 'Peut utiliser les couleurs dans ses messages',
	'ACL_U_SHOUT_DELETE_S'			=> 'Peut supprimer ses propres messages',
	'ACL_U_SHOUT_EDIT'				=> 'Peut éditer ses propres messages',
	'ACL_U_SHOUT_HIDE'				=> 'Peut annuler les infos postées par le robot',
	'ACL_U_SHOUT_IGNORE_FLOOD'		=> 'Peut ignorer la limite de flood',
	'ACL_U_SHOUT_IMAGE'				=> 'Peut poster des images dans ses messages',
	'ACL_U_SHOUT_INACTIV'			=> 'Peut ignorer l’inactivité et la mise en veille de la shoutbox',
	'ACL_U_SHOUT_INFO_S'			=> 'Peut voir les IPs de ses propres messages',
	'ACL_U_SHOUT_LATERAL'			=> 'Peut afficher la shoutbox dans le panneau latéral',
	'ACL_U_SHOUT_LIMIT_POST'		=> 'Peut ignorer la limite de caractères dans un message',
	'ACL_U_SHOUT_POPUP'				=> 'Peut utiliser la shoutbox en popup',
	'ACL_U_SHOUT_POST'				=> 'Peut poster des <strong>messages</strong> dans la shoutbox',
	'ACL_U_SHOUT_POST_INP'			=> 'Peut poster des <strong>messages personnels</strong> dans la shoutbox',
	'ACL_U_SHOUT_PRIV'				=> 'Peut accéder à la <strong>shoutbox privée</strong>',
	'ACL_U_SHOUT_SMILIES'			=> 'Peut poster des smileys dans ses messages',
	'ACL_U_SHOUT_VIEW'				=> 'Peut <strong>voir</strong> la shoutbox',
));
