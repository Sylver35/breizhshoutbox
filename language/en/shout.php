<?php
/** 
*
* Breizh Shoutbox Extension [English]
*
* @package language
* @version $Id: shout.php 100
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
// ’ « » “ ” …
//

$lang = array_merge($lang, array(
	'SHOUT_SEP'				=> ' ¦ ',
	'SHOUT_PROTECT'			=> '’', // Don't change this
	'SHOUT_START'			=> 'Shoutbox',
	'SHOUT_LOADING'			=> 'Loading…',
	'SHOUT_MESSAGE'			=> 'Message',
	'SHOUT_MESSAGES'		=> 'Messages',
	'SHOUT_AUTO'			=> 'Enter a message…',
	'POST_MESSAGE'			=> 'Post',
	'POST_MESSAGE_ALT'		=> 'Post a message',
	'SENDING'				=> 'Sending message…',
	'MESSAGE_EMPTY'			=> 'Message is empty.',
	'SHOUT_TOO_BIG'			=> 'Your message is too long, number of characters: ',
	'SHOUT_TOO_BIG2'		=> 'The maximum allowed is: ',
	'SHOUT_OUT_TIME'		=> 'inactivity time exceeded, automatic standby…',
	'SHOUT_NO_MESSAGE'		=> 'There is no message.',
	'NO_AJAX_USER'			=> 'You must enable Javascript to view the Shoutbox.',
	'NO_SHOUT_ID'			=> 'No message id.',
	'JS_ERR'				=> 'There has been a JavaScript error. Error:',
	'LINE'					=> 'Line',
	'FILE'					=> 'File',
	'FLOOD_ERROR'			=> 'Flood error',
	'POSTED' 				=> 'Message posted…',
	'SHOUT_NO_CODE'			=> 'The following bbcode: %s is not accepted.',
	'SHOUT_NO_VIDEO'		=> 'It is not possible to post videos in the shoutbox',
	'SHOUT_NO_SCRIPT'		=> 'The scripts are not tolerated in this shoutbox !  Please note that this attempt has been registered.',
	'SHOUT_NO_APPLET'		=> 'The applets are not tolerated in this shoutbox !  Please note that this attempt has been registered.',
	'SHOUT_NO_ACTIVEX'		=> 'The active x objets are not tolerated in this shoutbox !  Please note that this attempt has been registered.',
	'SHOUT_NO_OBJECTS'		=> 'The chrome & about objets are not tolerated in this shoutbox !  Please note that this attempt has been registered.',
	'SHOUT_NO_IFRAME'		=> 'The iframes are not tolerated in this shoutbox !  Please note that this attempt has been registered.',
	'SHOUT_DEL'				=> 'delete the message',
	'DEL_SHOUT'				=> 'Are you sure you want to delete this message?',
	'MSG_DEL_DONE'			=> 'Message being deleted…',
	'NO_SHOUT_ID'	 		=> 'No message id.',
	'SHOUT_PAGE'			=> 'Page N° ',
	'CODE'					=> 'code',
	'EDIT'					=> 'Edit',
	'CANCEL'				=> 'discontinue',
	'COLORS'				=> 'Colors',
	'SHOUT_IP'				=> 'See ip poster',
	'SHOUT_EDIT'			=> 'Edit message',
	'SENDING_EDIT'			=> 'Publication after edition…',
	'EDIT_DONE'				=> 'The message has been edited',
	'ONLY_ONE_OPEN'			=> 'You can only have one edit box open',
	'SHOUT_AVATAR_SHORT'	=> 'Avatar',
	'SHOUT_AVATAR_TITLE'	=> 'Avatar of %s',
	'SHOUT_AVATAR_NONE'		=> '%s has no avatar',
	'SHOUT_COLOR'			=> 'Colorize text',
	'SHOUT_COLOR_CLOSE'		=> 'Collapse colorizing text',
	'SHOUT_CHARS'			=> 'Add special characters',
	'SHOUT_CHARS_CLOSE'		=> 'Close the panel of special characters',
	'SHOUT_BBCODES'			=> 'Open the panel of bbcodes',
	'SHOUT_BBCODES_CLOSE'	=> 'Close the panel of bbcodes',
	'SMILIES'				=> 'Include Smilies', 
	'SMILIES_CLOSE'			=> 'Collapse the Smilies panel',
	'SHOUT_MORE_SMILIES'	=> 'More smilies',
	'SHOUT_MORE_SMILIES_ALT'=> 'Click here to see more smilies',
	'SHOUT_LESS_SMILIES'	=> 'Less smilies',
	'SHOUT_LESS_SMILIES_ALT' => 'Click here to see less smilies',
	'SHOUT_POST_IP'			=> 'IP of the user:',
	'SHOUTBOX'				=> '<a href="%1$s">%2$s</a>', // Don't traduct this
	'SHOUTBOX_VER'			=> 'Breizh Shoutbox v%s', // Don't traduct this
	'SHOUTBOX_VER_ALT'		=> 'Breizh Shoutbox v%s © 2018-2020', // Don't traduct this
	'SHOUT_TOUCH'			=> 'The Breizh Touch', // Don't traduct this
	'SHOUTBOX_POPUP'		=> 'Breizh Shoutbox Popup',
	'SHOUT_POP'				=> 'Open shoutbox in a popup',
	'SHOUT_POPUP'			=> 'Shoutbox Popup',
	'SHOUT_RULES'			=> 'Rules for using the Shoutbox',
	'SHOUT_RULES_PRIV'		=> 'Rules for using the Private Shoutbox',
	'SHOUT_RULES_CLOSE'		=> 'Close the panel usage rules',
	'SHOUTBOX_SECRET'		=> 'Private Shoutbox',
	'SHOUT_PRIV'			=> 'Enter in the private shoutbox',
	'SHOUT_PURGE'			=> 'Purge the shoutbox',
	'SHOUT_PURGE_ALT'		=> 'Clic here to fully purge the shoutbox',
	'SHOUT_PURGE_BOX'		=> 'Would you really completely purge the shoutbox ?  Please note, this action is irreversible…',
	'PURGE_PROCESS'			=> 'Purging the shoutbox in progress…',
	'SHOUT_PURGE_ROBOT_ALT'	=> 'Click here to purge the shoutbox infos robot',
	'SHOUT_PURGE_ROBOT_BOX'	=> ' Would you really purge the shoutbox infos robot ?  Please note, this action is irreversible…',
	'SERVER_ERR'			=> 'There was something wrong while doing a request to the server, please refresh the page…',
	'SHOUT_ERROR' 			=> 'Error: ',
	'SHOUT_IMG_POST_ERROR'	=> 'Error: to insert an image, you must click on the icon image…',
	'SHOUT_IMG_DIM_ERROR'	=> 'Error: image sent is corrupt or is not an image…',
	'SHOUT_IMG_FOPEN_ERROR'	=> 'Error: unable to contact the server hosting the image…',
	'SHOUT_PROCESS_IMG'		=> 'Checks image in progress…',

	// User panel
	'SHOUT_CONFIG_URL'		=> 'Config Shoutbox',
	'DISPLAY_SOUND_CHOICE'	=> 'You can choose to enable or disable the sound when new messages arrives',
	'SOUND_OR_NOT'			=> 'Choose the settings for you',
	'CHOOSE_NEW_SOUND'		=> 'Select the sound to be played at the new messages',
	'CHOOSE_ERROR_SOUND'	=> 'Select the sound to be played at errors',
	'CHOOSE_DEL_SOUND'		=> 'Select the sound to be played when deleting messages',
	'CHOOSE_NEW_YES'		=> 'The selected sound will be played at the new messages',
	'CHOOSE_ERROR_YES'		=> 'The selected sound will be played at errors',
	'CHOOSE_DELETE_YES'		=> 'The selected sound will be played when deleting messages',
	'CHOOSE_POSITIONS'		=> 'Positions of the shoutbox',
	'CHOOSE_NEW_NO'			=> 'No sound will be played at the new messages',
	'SHOUT_SOUND_YES'		=> 'Activation of the sound',
	'SHOUT_SOUND_NO'		=> 'Mute the sound',
	'SHOUT_SOUND_ECOUTE'	=> 'Play sound',
	'SHOUT_CONFIG_OPEN'		=> 'Open the shoutbox preferences panel',
	'SHOUT_PANEL_USER'		=> 'User Settings panel',
	'SHOUT_PANEL_TO_USER'	=> 'Settings panel for user %1$s',
	'SHOUT_PREF_UPDATED'	=> 'Your shoutbox preferences are saved',
	'RETURN_SHOUT_PREF'		=> '%s« Back to the preferences panel%s',
	'SHOUT_DEF_VAL'			=> 'Defaut values',
	'SHOUT_DEF_VAL_EXPLAIN'	=> 'Return to the default forum values',
	'SHOUT_ANY'				=> 'No sound',
	'CHOOSE_ERROR_NO'		=> 'No sound will be played at errors',
	'CHOOSE_DELETE_NO'		=> 'No sound will be played when deleting messages',
	'SHOUT_FLOAT_RIGHT'		=> 'On right',
	'SHOUT_FLOAT_LEFT'		=> 'On left',

	// No permission errors
	'NO_POST_GUEST'			=> 'Guests can post.',
	'NO_ACTION_PERM'		=> 'You are not allowed to perform this action',
	'NO_ADMIN_PERM'			=> 'No admin permission found…',
	'NO_EDIT_PERM'			=> 'You are not allowed to edit this message…',
	'NO_DELETE_PERM'		=> 'You are not allowed to delete messages…',
	'NO_DELETE_PERM_S'		=> 'You are not allowed to delete your own messages…',
	'NO_DELETE_PERM_T'		=> 'You are not allowed to delete messages from other users…',
	'NO_POST_PERM'			=> 'You are not allowed to post messages…',
	'NO_PURGE_PERM'			=> 'You are not allowed to purge the shoutbox…',
	'NO_PURGE_ROBOT_PERM'	=> 'You are not allowed to purge the shoutbox infos…',
	'NO_SHOUT_BBCODE'		=> 'You are not allowed to use BBCode…',
	'NO_SHOUT_CHARS'		=> 'You are not allowed to use special characters…',
	'NO_SHOUT_COLOR'		=> 'You are not allowed to use the colorization of text…',
	'NO_SHOUT_DEL'			=> 'You are not allowed to delete the message…',
	'NO_SHOUT_EDIT'			=> 'You are not allowed to edit the message…',
	'NO_SHOUT_IMG'			=> 'You are not allowed to post images…',
	'NO_SHOUT_POP'			=> 'You are not allowed to use the shoutbox in a popup…',
	'NO_SHOW_IP_PERM'		=> 'You are not allowed to see ip posters, but we see your…',
	'NO_SMILIES' 			=> 'You are not allowed to use Smilies…',
	'NO_SMILIE_PERM'		=> 'You are not allowed to post smilies…',
	'NO_VIEW_PERM'			=> 'You are not allowed to view the shoutbox…',
	'NO_VIEW_PRIV_PERM'		=> 'You are not allowed to view the private shoutbox…',
	'NO_SHOUT_PERSO_PERM'	=> 'You are not allowed to change the formatting of user messages',

	// Various panels
	'SHOUT_CLOSE'				=> 'Collapse',
	'SHOUT_DIV_CLOSE'			=> 'Collapse the panel',
	'SHOUT_CLICK_SOUND_ON'		=> 'Turn on sounds',
	'SHOUT_CLICK_SOUND_OFF'		=> 'Turn off sounds',
	'SHOUT_CHOICE_NAME'			=> 'Choose a username',
	'SHOUT_CHOICE_YES'			=> 'Username updated',
	'SHOUT_CHOICE_NAME_ERROR'	=> 'You must first choose a username.',
	'SHOUT_CLICK_HERE'			=> 'Click here to login',
	'SHOUT_LOG_ME_IN'			=> 'auto login',
	'SHOUT_HIDE_ME'				=> 'Hide me',
	'PICK_COLOR'				=> 'Choose a color by clicking in the area',
	'PICK_BUTON'				=> 'Color text',
	'SHOUT_CHOICE_COLOR'		=> 'Change pallet',
	'SHOUT_PICOLOR'				=> 'Picolor pallet',
	'SHOUT_PHPBBCOLOR'			=> 'Phpbb pallet',
	'SHOUT_PHPBB2COLOR'			=> 'Phpbb extended pallet',
	'SHOUT_LATERAL'				=> 'Shoutbox in lateral panel',
	'SHOUT_LATERAL_OPEN'		=> 'Open the Shoutbox in lateral panel',
	'SHOUT_LATERAL_CLOSE'		=> 'Close the latéral panel',
	'SHOUT_AFFICHE'				=> 'show password',
	'SHOUT_CACHE'				=> 'hide password',

	// Formatting messages panel
	'SHOUT_EXEMPLE'				=> 'Here an example of formatted text',
	'SHOUT_PERSO'				=> 'Customize the formatting of messages',
	'SHOUT_PERSO_GO'			=> 'Format',
	'SHOUT_BBCODE_OPEN'			=> 'Opening BBcodes',
	'SHOUT_BBCODE_CLOSE'		=> 'Closing BBcodes',
	'SHOUT_BBCODE_SUCCESS'		=> 'changes made',
	'SHOUT_BBCODE_SUP'			=> 'Formatting removed',
	'SHOUT_BBCODE_ERROR'		=> 'You must fill in the two fields',
	'SHOUT_BBCODE_ERROR_COUNT'	=> 'You need to have as many openings bbcodes as closing bbcodes',
	'SHOUT_BBCODE_ERROR_SHAME'	=> 'No changes made',
	'SHOUT_BBCODE_ERROR_SLASH'	=> 'Error, the closing bbcode “%s” does not contain a closing slash “/”',
	'SHOUT_BBCODE_ERROR_SLASHS'	=> 'Error, the %1$s closing bbcodes “%2$s” do not have a closing slash “/”',
	'SHOUT_BBCODE_ERROR_IMB'	=> 'Error, the closing bbcode “%2$s” is poorly nested',
	'SHOUT_BBCODE_ERROR_IMBS'	=> 'Error, the %1$s closing bbcodes “%2$s” are poorly nested',
	'SHOUT_DIV_BBCODE_CLOSE'	=> 'Close the panel formatting messages',
	'SHOUT_DIV_BBCODE_EXPLAIN'	=> 'You can customize the formatting of your messages in the shoutbox.<br />Enter bbcodes simple, openings in the first zone, closings in the second.<br />Caution: Observe the nesting bbcodes well and remember to close all.<br />Exemple: <em>[b][i] and [/i][/b]</em>',

	// User actions panel
	'SHOUT_ACTION_TITLE'			=> 'Actions for the user',
	'SHOUT_ACTION_TITLE_TO'			=> 'Actions for the user %s',
	'SHOUT_ACTION_PROFIL'			=> 'See profile of %s',
	'SHOUT_ACTION_CITE'				=> 'Quote the user',
	'SHOUT_ACTION_CITE_M'			=> 'Multi quote the user',
	'SHOUT_ACTION_CITE_ON'			=> 'For ',
	'SHOUT_ACTION_CITE_EXPLAIN'		=> 'Quote the user in a message of shoutbox',
	'SHOUT_ACTION_CITE_M_EXPLAIN'	=> 'Multi quote the user in a message of shoutbox',
	'SHOUT_ACTION_MSG'				=> 'Send a personal message in the shoutbox',
	'SHOUT_ACTION_MSG_ROBOT'		=> 'Send a message as %s',
	'SHOUT_ACTION_DELETE'			=> 'Remove my personal messages',
	'SHOUT_ACTION_DELETE_EXPLAIN'	=> 'Are you sure you want to delete all your personal messages?',
	'SHOUT_ACTION_DEL_TO'			=> 'Delete personal messages sent to me',
	'SHOUT_ACTION_DEL_TO_EXPLAIN'	=> 'Are you sure you want to delete all personal messages intended for you?',
	'SHOUT_ACTION_DEL_REP'			=> 'All your personal messages were deleted',
	'SHOUT_ACTION_DEL_NO'			=> 'Any personal message deleted',
	'SHOUT_ACTION_MCP'				=> 'User notes',
	'SHOUT_ACTION_BAN'				=> 'Ban of the Forum',
	'SHOUT_ACTION_REMOVE'			=> 'Delete all shoutbox messages of the user',
	'SHOUT_ACTION_REMOVE_EXPLAIN'	=> 'Are you sure you want to delete all shoutbox messages and infos Robot of this user?',
	'SHOUT_ACTION_REMOVE_REP'		=> 'All shoutbox messages of this user have been deleted:',
	'SHOUT_ACTION_REMOVE_NO'		=> 'Any deleted message',
	'SHOUT_ACTION_ADMIN'			=> 'Administer the user',
	'SHOUT_ACTION_PERSO'			=> 'Change the formatting of user’s messages',
	'SHOUT_USER_POST'				=> '@PM_', // Before a personnal message
	'SHOUT_USER_IGNORE'				=> 'You have set this member to foe',
	'SHOUT_USER_NONE'				=> 'No action possible for this member',

// Infos cookies
	'SHOUT_COOKIES'					=> 'Information about shoutbox cookies',
	'SHOUT_COOKIES_INFO'			=> 'This shoutbox uses 3 cookies to be able to function properly',
	'SHOUT_COOKIES_ROBOT'			=> 'Allows to display or not the robot info',
	'SHOUT_COOKIES_NAME'			=> 'Allows you to keep the username as a guest',
	'SHOUT_COOKIES_SOUND'			=> 'Allows you to choose the activation or deactivation of sound as a guest',

// Permissions panel
	'SHOUT_OPTION_YES'				=> 'Active : <span class="%2$s">“%1$s”</span>',
	'SHOUT_OPTION_NO'				=> 'Inactive : <span class="%2$s">“%1$s”</span>',
	'SHOUT_OPTION_USER'				=> 'Tracing shoutbox permissions for %1$s',

// Members connected panel
	'SHOUT_ONLINE_TITLE'		=> 'Members connected in real time',
	'SHOUT_ONLINE'				=> 'Open the panel of connected members',
	'SHOUT_ONLINE_CLOSE'		=> 'Close the panel of connected members',
	
// Post infos Robot
	'SHOUT_ROBOT_ON'			=> 'Disable Robot infos',
	'SHOUT_ROBOT_OFF'			=> 'Show Robot infos',
	'SHOUT_SELECT_ROBOT'		=> 'Disable the publication by the shoutbox’s robot',
	'SHOUT_ROBOT_START'			=> 'Info:', // At the beginning of infos robot
	'SHOUT_ROBOT_DATE'			=> 'l F j, Y', // Form of the info date
	
// Robot info messages
	'SHOUT_GLOBAL_ROBOT'		=> '%1$s %2$s just create a global announcement: %3$s',
	'SHOUT_ANNOU_ROBOT'			=> '%1$s %2$s just create an announcement: %3$s',
	'SHOUT_POST_ROBOT'			=> '%1$s %2$s just create a new topic: %3$s',
	'SHOUT_REPLY_ROBOT'			=> '%1$s %2$s just replying to a topic: %3$s',
	'SHOUT_EDIT_ROBOT'			=> '%1$s %2$s just edit a message: %3$s',
	'SHOUT_TOPIC_ROBOT'			=> '%1$s %2$s just edit a topic: %3$s',
	'SHOUT_LAST_ROBOT'			=> '%1$s %2$s just edit the last post in the topic: %3$s',
	'SHOUT_QUOTE_ROBOT'			=> '%1$s %2$s just replying a topic quoting: %3$s',
	'SHOUT_PREZ_ROBOT'			=> '%1$s %2$s just create its presentation: %3$s',
	'SHOUT_PREZ_F_ROBOT'		=> '%1$s %2$s just edit a presentation: %3$s',
	'SHOUT_PREZ_FS_ROBOT'		=> '%1$s %2$s just edit its presentation: %3$s',
	'SHOUT_PREZ_E_ROBOT'		=> '%1$s %2$s just edit a message in a presentation: %3$s',
	'SHOUT_PREZ_ES_ROBOT'		=> '%1$s %2$s just edit a message in its presentation: %3$s',
	'SHOUT_PREZ_L_ROBOT'		=> '%1$s %2$s just edit the last message in a presentation: %3$s',
	'SHOUT_PREZ_LS_ROBOT'		=> '%1$s %2$s just edit the last message in its presentation: %3$s',
	'SHOUT_PREZ_Q_ROBOT'		=> '%1$s %2$s just replying a presentation quoting: %3$s',
	'SHOUT_PREZ_R_ROBOT'		=> '%1$s %2$s just replying to a presentation: %3$s',
	'SHOUT_PREZ_RS_ROBOT'		=> '%1$s %2$s just replying to its presentation: %3$s',
	'SHOUT_ENTER_PRIV'			=> '%1$s %2$s just enter in the private shoutbox',
	'SHOUT_PURGE_SHOUT'			=> '%s Purge the shoutbox done…',
	'SHOUT_PURGE_PRIV'			=> '%s Purge the private shoutbox done…',
	'SHOUT_PURGE_ROBOT'			=> '%s Purge of Robot infos done…',
	'SHOUT_PURGE_AUTO'			=> '%s Automatic purge of %s messages in the shoutbox done…',
	'SHOUT_PURGE_PRIV_AUTO'		=> '%s Automatic purge of %s messages in the private shoutbox done…',
	'SHOUT_DELETE_AUTO'			=> '%s Automatic load shedding of %s messages in the shoutbox done…',
	'SHOUT_DELETE_PRIV_AUTO'	=> '%s Automatic load shedding of %s messages in the private shoutbox done…',
	'SHOUT_BIRTHDAY_ROBOT'		=> 'All the team of %1$s wish a Happy Birthday to %2$s',
	'SHOUT_BIRTHDAY_ROBOT_FULL'	=> 'All the team of %1$s wish a Happy Birthday to %2$s for %3$s %4$s years!',
	'SHOUT_HELLO_ROBOT'			=> 'Hello, we are %1$s %2$s',
	'SHOUT_NEWEST_ROBOT'		=> 'A new member just register: %1$s, All the team of %2$s welcome him…',
	'SHOUT_SESSION_ROBOT'		=> 'Hello %s and welcome to the forum…',
	'SHOUT_SESSION_ROBOT_BOT'	=> '%1$s %2$s just connect to the forum…',

	'SHOUT_VIDEO'					=> 'Videos galery',
	'SHOUT_NEW_VIDEO'				=> 'New video: %1$s in: %2$s',

	'RELAXARCADE'					=> 'Relax-Arcade',
	'SHOUT_NEW_SCORE_RA'			=> 'New score ',
	'SHOUT_NEW_SCORE_RA_TXT'		=> 'I just set the first score of %s points to %s',
	'SHOUT_NEW_RECORD_RA'			=> 'New record ',
	'SHOUT_NEW_RECORD_RA_TXT'		=> 'I just set a new record of %s points to %s',
	'SHOUT_NEW_URECORD_RA'			=> 'New ultimate record ',
	'SHOUT_NEW_URECORD_RA_TXT'		=> 'I have just set a new ultimate record of %s points to %s',
	'SHOUT_NEW_SCORE_RA_EXPLAIN'	=> 'Display a message if a very first score is set.',
	'SHOUT_NEW_RECORD_RA_EXPLAIN'	=> 'Display a message if a new record is set.',
	'SHOUT_NEW_URECORD_RA_EXPLAIN'	=> 'Display a message if a new ultimate record is set.',
));
