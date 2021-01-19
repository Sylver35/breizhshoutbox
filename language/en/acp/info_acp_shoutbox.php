<?php
/**
*
* Breizh Shoutbox Extension [English]
*
* @package language
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
// ’ » “ ” …
//

$lang = array_merge($lang, array(
// Main tab
	'ACP_SHOUTBOX'					=> 'Breizh Shoutbox',
// General category
	'ACP_SHOUT_GENERAL_CAT'			=> 'Overview',
	'ACP_SHOUT_CONFIGS'				=> 'General Setup',
	'ACP_SHOUT_CONFIGS_T'			=> 'General Setup Breizh Shoutbox',
	'ACP_SHOUT_CONFIGS_T_EXPLAIN'	=> 'On this page, you can adjust all the different settings of the shoutbox.',
	'ACP_SHOUT_RULES'				=> 'Usage Rules',
	'ACP_SHOUT_RULES_T'				=> 'Panel Rules for using the shoutbox',
	'ACP_SHOUT_RULES_T_EXPLAIN'		=> 'This page allows you to define rules for using the shoutbox.<br />You can put rules in different languages enabled in this forum.<br />This space allows you to create/edit rules. You can design them in the first frame and then export them as desired to the desired areas.',
// Category for main shoutbox
	'ACP_SHOUT_PRINCIPAL_CAT'		=> 'Main Shoutbox',
	'ACP_SHOUT_OVERVIEW'			=> 'Messages and Statistics',
	'ACP_SHOUT_OVERVIEW_T'			=> 'Messages and Statistics Breizh Shoutbox',
	'ACP_SHOUT_OVERVIEW_T_EXPLAIN'	=> 'On this page, you can see the statistics of the main shoutbox.
										<br />You can also delete messages or completely purge the shoutbox.',
	'ACP_SHOUT_CONFIG_GEN'			=> 'Settings Main shoutbox',
	'ACP_SHOUT_CONFIG_GEN_T'		=> 'Settings of shoutbox main forum',
	'ACP_SHOUT_CONFIG_GEN_T_EXPLAIN'=> 'On this page, you can set all settings of the Main shoutbox to your forum.',
// Category for private shoutbox
	'ACP_SHOUT_PRIVATE_CAT'			=> 'Private Shoutbox',
	'ACP_SHOUT_PRIVATE'				=> 'Messages and Statistics',
	'ACP_SHOUT_PRIVATE_T'			=> 'Panel Messages and statistics shoutbox private',
	'ACP_SHOUT_PRIVATE_T_EXPLAIN'	=> 'On this page you can see the statistics of the private shoutbox.
										<br />You can also delete messages or completely purge the shoutbox.',
	'ACP_SHOUT_CONFIG_PRIV'			=> 'Settings Private Shoutbox',
	'ACP_SHOUT_CONFIG_PRIV_T'		=> 'Panel of Private Shoutbox',
	'ACP_SHOUT_CONFIG_PRIV_T_EXPLAIN'=> 'On this page, you can set all the parameters of the Private shoutbox to your forum.
										<br />To set the permission to use this shoutbox private appointments in the permissions tab “Breizh Shoutbox”: “Can access the private shoutbox”',
// Category for popup shoutbox
	'ACP_SHOUT_POPUP_CAT'			=> 'Shoutbox in popup',
	'ACP_SHOUT_POPUP'				=> 'Settings popup',
	'ACP_SHOUT_POPUP_T'				=> 'Panel of the popup Breizh Shoutbox',
	'ACP_SHOUT_POPUP_T_EXPLAIN'		=> 'On this page, you can set all the parameters of the shoutbox in popup.<br />These settings also apply in the retractable lateral panel shoutbox.',
// Category for retractable lateral panel
	'ACP_SHOUT_PANEL'				=> 'Settings lateral panel',
	'ACP_SHOUT_PANEL_T'				=> 'Settings of the retractable lateral panel',
	'ACP_SHOUT_PANEL_T_EXPLAIN'		=> 'On this page, you can set all parameters of the retractable lateral panel.<br />Note that this panel contains the shoutbox in popup.',
// Category for smilies
	'ACP_SHOUT_SMILIES_CAT'			=> 'Smilies',
	'ACP_SHOUT_SMILIES'				=> 'Smilies settings',
	'ACP_SHOUT_SMILIES_T'			=> 'Panel of Smilies settings for the shoutbox',
	'ACP_SHOUT_SMILIES_T_EXPLAIN'	=> 'On this page, you can configure the smilies to be displayed in the shoutbox.<br />
										Smilies are displayed all present in the database, indifferently from those displayed on the page of posting messages.<br />
										To specify which smilies should appear or not, just click directly on the smiley images.',
	'ACP_SHOUT_SMILIES_POP'			=> 'Popup Smilies',
	'ACP_SHOUT_SMILIES_POP_T_EXPLAIN'=> 'Popup Smileys',
// Category for robot
	'ACP_SHOUT_ROBOT_CAT'			=> 'Robot shoutbox',
	'ACP_SHOUT_ROBOT'				=> 'Robot configuration',
	'ACP_SHOUT_ROBOT_T'				=> 'Configuration of the robot Breizh Shoutbox',
	'ACP_SHOUT_ROBOT_T_EXPLAIN'		=> 'On this page, you can adjust all the different points of the configuration of the robot shoutbox.
										<br />Some parameters are either the main shoutbox, or for the private shoutbox.',
// Language for Logs
	'LOG_SHOUT_CONFIGS'				=> '<strong>Update the general configuration of Breizh Shoutbox.</strong>',
	'LOG_SHOUT_CONFIG_GEN'			=> '<strong>Updated settings of main Shoutbox.</strong>',
	'LOG_SHOUT_CONFIG_PRIV'			=> '<strong>Update the configuration of private Shoutbox.</strong>',
	'LOG_SHOUT_RULES'				=> '<strong>Updated rules Breizh Shoutbox.</strong>',
	'LOG_SHOUT_POPUP'				=> '<strong>Update settings of popup Breizh Shoutbox.</strong>',
	'LOG_SHOUT_PANEL'				=> '<strong>Updated settings of lateral panel Breizh Shoutbox.</strong>',
	'LOG_SHOUT_ROBOT'				=> '<strong>Update settings of Breizh Shoutbox Robot.</strong>',
	'LOG_PURGE_SHOUTBOX'			=> '<strong>Purge all Messages of Shoutbox.</strong>',
	'LOG_PURGE_SHOUTBOX_PRIV'		=> '<strong>Purge all Messages of private Shoutbox.</strong>',
	'LOG_PURGE_SHOUTBOX_ROBOT'		=> '<strong>Purge %s Robots infos of Shoutbox.</strong>',
	'LOG_PURGE_SHOUTBOX_PRIV_ROBOT'	=> '<strong>Purge %s Robots infos of private Shoutbox.</strong>',
	'LOG_SELECT_SHOUTBOX'			=> '<strong>Remove %s Selected Message of the Shoutbox.</strong>',
	'LOG_SELECTS_SHOUTBOX'			=> '<strong>Remove %s Selected Messages of the private Shoutbox.</strong>',
	'LOG_SELECT_SHOUTBOX_PRIV'		=> '<strong>Remove %s Selected Message of the private Shoutbox.</strong>',
	'LOG_SELECTS_SHOUTBOX_PRIV'		=> '<strong>Remove %s Selected Messages of the Shoutbox.</strong>',
	'LOG_LOG_SHOUTBOX'				=> '<strong>Remove %s selected entry of users log (shoutbox).</strong>',
	'LOG_LOGS_SHOUTBOX'				=> '<strong>Remove %s selected entries of users log (shoutbox).</strong>',
	'LOG_LOG_SHOUTBOX_PRIV'			=> '<strong>Remove %s selected entry of users log (private shoutbox).</strong>',
	'LOG_LOGS_SHOUTBOX_PRIV'		=> '<strong>Remove %s selected entries of users log (private shoutbox).</strong>',
	'LOG_SHOUT_PRUNED'				=> '<strong>Pruned Breizh Shoutbox</strong>',
	'LOG_SHOUT_PRIV_PRUNED'			=> '<strong>Pruned private Breizh Shoutbox</strong>',
	'LOG_SHOUT_REMOVED'				=> '<strong>Automatic deletion of %s posts in the shoutbox.</strong>',
	'LOG_SHOUT_PRIV_REMOVED'		=> '<strong>Automatic deletion of %s posts in the private shoutbox.</strong>',
	'LOG_SHOUT_PURGED'				=> '<strong>Purge time automatic %s messages in the shoutbox.</strong>',
	'LOG_SHOUT_PRIV_PURGED'			=> '<strong>Purge time automatic %s messages in the private shoutbox.</strong>',
	'LOG_SHOUT_SCRIPT'				=> '<strong>Attempt to post script in the shoutbox.</strong>',
	'LOG_SHOUT_APPLET'				=> '<strong>Attempt to post applet in the shoutbox.</strong>',
	'LOG_SHOUT_ACTIVEX'				=> '<strong>Attempt to post active x object in the shoutbox.</strong>',
	'LOG_SHOUT_OBJECTS'				=> '<strong>Attempt to post chrome or about object in the shoutbox.</strong>',
	'LOG_SHOUT_IFRAME'				=> '<strong>Attempt to post iframe in the shoutbox.</strong>',
	'LOG_SHOUT_PRUNED_PRIV'			=> '<strong>Pruned private Breizh Shoutbox</strong>',
	'LOG_SHOUT_REMOVED_PRIV'		=> '<strong>Automatic deletion of %s posts in the private shoutbox.</strong>',
	'LOG_SHOUT_PURGED_PRIV'			=> '<strong>Purge time automatic %s messages in the private shoutbox.</strong>',
	'LOG_SHOUT_SCRIPT_PRIV'			=> '<strong>Attempt to post script in the private shoutbox.</strong>',
	'LOG_SHOUT_APPLET_PRIV'			=> '<strong>Attempt to post applet in the private shoutbox.</strong>',
	'LOG_SHOUT_ACTIVEX_PRIV'		=> '<strong>Attempt to post active x object in the private shoutbox.</strong>',
	'LOG_SHOUT_OBJECTS_PRIV'		=> '<strong>Attempt to post chrome or about object in the private shoutbox.</strong>',
	'LOG_SHOUT_IFRAME_PRIV'			=> '<strong>Attempt to post iframe in the private shoutbox.</strong>',
	'SHOUT_LOGS'					=> 'Post forbidden attempts',
	'SHOUT_LOGS_EXPLAIN'			=> 'Total number of forbidden item post attempts in the shoutbox',
	'NUMBER_LOG_TOTAL'				=> [
		1	=> '<strong>%d</strong> attempt since %s',
		2	=> '<strong>%d</strong> attempts since %s',
	],
	'NO_MESSAGE'					=> 'There is no message',
	'NO_SHOUT_LOG'					=> 'There is no entry',
	'NUMBER_MESSAGE'				=> [
		1	=> '<strong>%d</strong> message',
		2	=> '<strong>%d</strong> messages',
	],
	'NUMBER_LOG'					=> [
		1	=> '<strong>%d</strong> entry',
		2	=> '<strong>%d</strong> entries',
	],
	'ORDER'							=> 'order',
	'SHOUT_MESSAGES'				=> 'messages',

	'SHOUT_NORMAL'					=> 'main shoutbox',
	'SHOUT_PRIVATE'					=> 'private shoutbox',
	'DISPLAY_ON_SHOUTBOX'			=> 'Display Preferences in the shoutbox',

	'SHOUT_RULES_ACTIVE'			=> 'Rules of shoutbox',
	'SHOUT_RULES_ACTIVE_EXPLAIN'	=> 'Enable/Disable rules on the shoutbox.',
	'SHOUT_RULES_OPEN'				=> 'Rules always open',
	'SHOUT_RULES_OPEN_EXPLAIN'		=> 'Always lets show rules for everyone',
	'SHOUT_RULES_ON'				=> 'Rules in language “%s” “%s”',
	'SHOUT_RULES_ON_EXPLAIN'		=> 'Please enter the rules in the language “%s” “%s” for main shoutbox.<br />Bbcodes, Links and smilies are on.',
	'SHOUT_RULES_ON_PRIV_EXPLAIN'	=> 'Please enter the rules in the language “%s” “%s” for private shoutbox.<br />Bbcodes, Links and smilies are on.',
	'SHOUT_RULES_VIEW'				=> 'Visualizing Rules main shoutbox:',
	'SHOUT_RULES_VIEW_PRIV'			=> 'Visualizing Rules private shoutbox:',
	'SMILIES_EMOTION'				=> 'Emotion smiley',
	'SMILIES_OVERVIEW'				=> 'Smileys displayed by default',
	'SMILIES_POPUP'					=> 'Smilies displayed as secondary',
	'SMILIES_DISPLAYED'				=> 'Display by default',
	'SMILIES_NO_DISPLAYED'			=> 'Display in secondary ',
	'SMILIES_CLIC_NO'				=> 'Click to display this smiley in secondary',
	'SMILIES_CLIC_YES'				=> 'Click to display this smiley by default',

	'SHOUT_AVATAR'					=> 'Display Avatars',
	'SHOUT_AVATAR_EXPLAIN'			=> 'Indicate whether you want to enable the display of user avatars.',
	'SHOUT_AVATAR_HEIGHT'			=> 'Dimension avatars',
	'SHOUT_AVATAR_HEIGHT_EXPLAIN'	=> 'Enter here the height avatars in pixels, width is calculated automatically.',
	'SHOUT_AVATAR_IMG'				=> 'Avatar image default',
	'SHOUT_AVATAR_IMG_EXPLAIN'		=> 'Specify here the image chosen for the avatar by default for users who do not choose.<br />This image should be in the folder “ext/sylver35/breizhshoutbox/images/”',
	'SHOUT_AVATAR_IMG_BOT'			=> 'Avatar image of the robot',
	'SHOUT_AVATAR_IMG_BOT_EXPLAIN'	=> 'Specify here the image chosen for the avatar of the robot.<br />This image should be in the folder “ext/sylver35/breizhshoutbox/images/”',
	'SHOUT_AVATAR_ROBOT'			=> 'Robot Avatar',
	'SHOUT_AVATAR_ROBOT_EXPLAIN'	=> 'Enable/Disable robot avatar <em>If avatars are enabled</em>.',
	'SHOUT_AVATAR_USER'				=> 'User’s avatars',
	'SHOUT_AVATAR_USER_EXPLAIN'		=> 'Enable/disable default avatar for users without an avatar.',

	'SHOUT_BAR_TOP'					=> 'At the top of the shoutbox',
	'SHOUT_BAR_BOTTOM'				=> 'At the bottom of the shoutbox',
	'SHOUT_BACKGROUND_COLOR'		=> 'Background image of the shoutbox',
	'SHOUT_BACKGROUND_COLOR_EXPLAIN'=> 'Select the background image of the shoutbox',
	'SHOUT_BBCODE'					=> 'Prohibition bbcodes',
	'SHOUT_BBCODE_EXPLAIN'			=> 'Enter here the list of bbcodes that you do not want in the shoutbox.<br />Some bbcodes can cause bugs, your experience will allow you to list them here.<br />You must enter them without brackets, separated by a comma and a space.<br />Ex:&nbsp;&nbsp;<em>list, code, quote</em>',
	'SHOUT_BBCODE_USER_EXPLAIN'		=> 'Enter the list of bbcode that you do not want in the format of messages users.<br />The list of prohibited bbcodes above is already considered, this list is a complement. The videos are already prohibited.<br />You must enter them without brackets, separated by a comma and a space.<br />Eg:&nbsp;&nbsp;<em>list, code, quote</em>',
	'SHOUT_BBCODE_SIZE'				=> 'Font size',
	'SHOUT_BBCODE_SIZE_EXPLAIN'		=> 'Enter here the maximum font size allowed for the bbcode size= in the formatting user’s messages.<br />The number 100 corresponds to the overall size of the police, 150 corresponds to one and half times that size.',
	'SHOUT_BIRTHDAY_EXCLUDE'		=> 'Exclude groups',
	'SHOUT_BIRTHDAY_EXCLUDE_EXPLAIN'=> 'You can select one or more groups will be excluded from birthdays to wish.<br />Banned members are automatically excluded.<br /><br />Use ctrl+click to select more than one group.',
	'SHOUT_BUTTON_BACKGROUND'		=> 'Background image under the buttons',
	'SHOUT_BUTTON_BACKGROUND_EXPLAIN'=> 'Choose whether to display the background image under the left buttons',

	'SHOUT_CONFIG_TITLE'			=> 'Title of the shoutbox',
	'SHOUT_CONFIG_TITLE_EXPLAIN'	=> 'You can choose a title for your shoutbox',
	'SHOUT_COPY_RULE'				=> 'export to rules “%1$s” %2$s',
	'SHOUT_CORRECT'					=> 'Correction of minutes',
	'SHOUT_CORRECT_EXPLAIN'			=> 'Enabling this setting allows you to automatically correct the display of the minutes of the hour messages if the user uses a date format that contains "less than a minute" <em>(Auto refresh)</em>. This affects only messages less than an hour.',

	'SHOUT_DATE_LAST_RUN'			=> 'Date last automatic prune',

	'NUMBER_SHOUTS' 				=> 'Total Posts',
	'SHOUT_STATS'					=> 'Messages of Shoutbox',
	'SHOUT_STATISTICS'				=> 'Statistics',
	'SHOUT_VERSION'					=> 'Shoutbox version',

	'SHOUT_OPTIONS'					=> 'Purge the Shoutbox',
	'PURGE_SHOUT'					=> 'Delete all messages',
	'PURGE_SHOUT_MESSAGES'			=> 'Delete the messages',
	'PURGE_SHOUT_ROBOT'				=> 'Remove info Robot',
	'PURGE_SHOUT_ROBOT_EXPLAIN'		=> 'possibility to removes all the info in the shoutbox Robot',

	'SHOUT_DEFIL_TOP'				=> 'Last message on top',
	'SHOUT_DEFIL_BOTTOM'			=> 'Last message at the bottom',
	'SHOUT_DEFIL'					=> 'Scroll direction of messages',
	'SHOUT_DEFIL_EXPLAIN'			=> 'You can choose in which direction the messages scroll in the shoutbox.<br />- Be the last message at the top and then scroll down<br />- Be the last message at the bottom and scroll up.<br />Note that the focus will always be on the most recent message.',
	'SHOUT_DEFIL_MEMBERS'			=> 'Members can individually choose a different setting.',
	'SHOUT_DEL_MAIN'				=> 'Deleted messages',
	'SHOUT_DEL_ACP'					=> 'Number of deleted messages in the acp:',
	'SHOUT_DEL_AUTO'				=> 'Number of messages deleted automatically:',
	'SHOUT_DEL_PURGE'				=> 'Number of messages deleted during a purge:',
	'SHOUT_DEL_USER'				=> 'Number of messages deleted by users:',
	'SHOUT_DEL_NR'					=> [
		1	=> '<strong>%s</strong> Deleted message',
		2	=> '<strong>%s</strong> Deleted messages',
	],
	'SHOUT_DEL_TOTAL'				=> ' total',
	'SHOUT_EDIT_RULE'				=> 'Edit this text',

	'SHOUT_MAX_CHARS'				=> 'characters',

	'SHOUT_WIDTH_POST'				=> 'Size of the area post',
	'SHOUT_WIDTH_POST_PRO_EXPLAIN'	=> 'Please select the length of the entry area of the shoutbox messages (in pixels)',

	'SHOUT_PRUNE_TIME'				=> 'Prune time',
	'SHOUT_PRUNE_TIME_EXPLAIN'		=> 'The time when the messages are pruned automaticcly. When number of messages in the BDD setting is enabled, that will overide this setting. Set this setting to 0 to disable',
	'SHOUT_MAX_POSTS'				=> 'Maximum number of messages in the BDD',
	'SHOUT_MAX_POSTS_EXPLAIN'		=> 'Maximum number of messages in the BDD. Set <strong>0</strong> to disable. If this setting if enabled, it will <strong>overide</strong> the prune setting.',
	'SHOUT_MAX_POSTS_ON'			=> 'Maximum numbers of messages to display',
	'SHOUT_MAX_POSTS_ON_EXPLAIN'	=> 'This allows you to specify the maximum number of messages to be displayed in the shoutbox, regardless of the total.',

	'SHOUT_FLOOD_INTERVAL'			=> 'Flood interval',
	'SHOUT_FLOOD_INTERVAL_EXPLAIN'	=> 'The time minimum time between 2 posts for users. Set 0 to disable. A user permission is allowed to ignore',
	'SHOUT_NR_ACP'					=> 'Number of messages in acp',
	'SHOUT_NR_ACP_EXPLAIN'			=> 'Choose the number of messages per page in the acp, tab overview.',
	'SHOUT_MAX_POST_CHARS'			=> 'Maximum number of characters',
	'SHOUT_MAX_POST_CHARS_EXPLAIN'	=> 'Choose the maximum number of characters it is possible to post in a message.<br />Note that there is a permission to bypass this limit',
	'SHOUT_NUM'						=> 'Number of messages per page',

	'SHOUT_HEIGHT'					=> 'Height of the div messages',
	'SHOUT_HEIGHT_EXPLAIN'			=> 'Determine here the height of the message div in the shoutbox.',
	'SHOUT_DIV_IMG'					=> 'Background image of the messages div',
	'SHOUT_DIV_IMG_EXPLAIN'			=> 'You can add a background image in the messages div (having some level of transparency).<br />Image to put in “styles/all/theme/images/background/”<br />Possibility of having a different image (with an identical name) for each style added.<br />Also set the image position.',
	'SHOUT_DIV_HORIZONTAL'			=> 'horizontal position',
	'SHOUT_DIV_VERTICAL'			=> 'vertical position',
	'SHOUT_DIV_NONE'				=> 'any image',
	'SHOUT_DIV_TOP'					=> 'at the top',
	'SHOUT_DIV_CENTER'				=> 'in the center',
	'SHOUT_DIV_RIGHT'				=> 'to the right',
	'SHOUT_DIV_BOTTOM'				=> 'at the bottom',
	'SHOUT_POSITION_INDEX'			=> 'Shoutbox Position on the index',
	'SHOUT_POSITION_INDEX_EXPLAIN'	=> 'Decide what position you want to assign to the shoutbox on the index page of the forum.',
	'SHOUT_POSITION_FORUM'			=> 'Shoutbox Position on the viewforum',
	'SHOUT_POSITION_FORUM_EXPLAIN'	=> 'Decide what position you want to assign to the shoutbox on the pages of forums (viewforum).',
	'SHOUT_POSITION_TOPIC'			=> 'Shoutbox Position on the viewtopic',
	'SHOUT_POSITION_TOPIC_EXPLAIN'	=> 'Decide what position you want to assign to the shoutbox on the pages of messages (viewtopic).',

	'SHOUT_ON_CRON'					=> 'Enabling automatic deletions and prune',
	'SHOUT_ON_CRON_EXPLAIN'			=> 'Decide if you want to enable automatic deletions and automatic prune of messages.',
	'SHOUT_LOG_CRON'				=> 'Log of deletions and automatic prune',
	'SHOUT_LOG_CRON_EXPLAIN'		=> 'Decide if you want to enter in the admin log deletions and automatic prune messages.',
	'SHOUT_SEE_BUTTONS'				=> 'Display icons above',
	'SHOUT_SEE_BUTTONS_EXPLAIN'		=> 'Decide if you want to display icons above although the user does not have permission to use them (see the padlock on mouseover).',
	'SHOUT_SEE_BUTTONS_LEFT'		=> 'Display icons left',
	'SHOUT_SEE_BUTTONS_LEFT_EXPLAIN'=> 'Decide if you want to display icons to the left of the messages although the user does not have permission to use them (see the padlock on mouseover).',
	'SHOUT_SEE_BUTTON_IP'			=> 'Display ips',
	'SHOUT_SEE_BUTTON_IP_EXPLAIN'	=> 'Determine if you want to display the ips buttons, it cancels the permissions that allow it.',
	'SHOUT_SEE_CITE'				=> 'Display of quote icons',
	'SHOUT_SEE_CITE_EXPLAIN'		=> 'Determine if you want to display the quote icons to the left of the messages',
	'SHOUT_PANEL_PERMISSIONS'		=> 'To see this panel, must have permissions: “<em>A user can view the shoutbox in a lateral panel</em>” and “<em>A user can use the shoutbox in popup</em>” to yes.<br />Not enabled for mobile phones.',
	'SHOUT_PANEL_KILL'				=> 'pages excluded',
	'SHOUT_PANEL_KILL_EXPLAIN'		=> 'You can select or exclude pages displaying the retractable lateral panel.<br />Enter the name of the php page with the settings and the path if different from root.<br />One page per line. Ex: <em>ucp.php?mode=register&nbsp;&nbsp;gallery/index.php</em><br />Pages automatically excluded: errors, informations, redirections and connexion.',
	'SHOUT_PANEL_IMG'				=> 'Opening Image',
	'SHOUT_PANEL_IMG_EXPLAIN'		=> 'Choose the opening image for the retractable lateral panel.<br />Images of directory root/images/shoutbox/panel/',
	'SHOUT_PANEL_EXIT_IMG'			=> 'Closing Image',
	'SHOUT_PANEL_EXIT_IMG_EXPLAIN'	=> 'Choose the closing image for the retractable lateral panel.<br />Images of directory root/images/shoutbox/panel/',
	'SHOUT_PANEL_WIDTH'				=> 'Lateral panel width',
	'SHOUT_PANEL_WIDTH_EXPLAIN'		=> 'Specify the width of the retractable lateral panel.<br />Note that it must contain in the shoutbox in popup.',
	'SHOUT_PANEL_HEIGHT'			=> 'Lateral panel height',
	'SHOUT_PANEL_HEIGHT_EXPLAIN'	=> 'Specify the height of the retractable lateral panel.',
	'SHOUT_POP_HEIGHT'				=> 'Height of the popup',
	'SHOUT_POP_HEIGHT_EXPLAIN'		=> 'Determine the height of the shoutbox popup',
	'SHOUT_POP_WIDTH'				=> 'Width of the popup',
	'SHOUT_POP_WIDTH_EXPLAIN'		=> 'Determine the width of the shoutbox popup',
	'SHOUT_MESSAGES_TOTAL'			=> 'Number of total messages',
	'SHOUT_MESSAGES_TOTAL_EXPLAIN'	=> 'Number of posts in total since the installation of Breizh Shoutbox.',
	'SHOUT_MESSAGES_TOTAL_NR'		=> '<strong>%s</strong> messages since %s',
	'SHOUT_POSITION_TOP'			=> 'At the top of page',
	'SHOUT_POSITION_AFTER'			=> 'After the list of forums',
	'SHOUT_POSITION_END'			=> 'At the bottom of page',
	'SHOUT_POSITION_NONE'			=> 'Do not display',

	'SHOUTBOX_VERSION_ACP_COPY'		=> '<a href="%1$s" onclick="window.open(this.href);return false;">Breizh Shoutbox v%2$s</a> © 2018-2021 - Breizhcode - The Breizh Touch', // Don't translate this please ^-^
	'SHOUT_TOUCH_COPY'				=> '<span style="font-size: 11px">Breizh Shoutbox © 2010, 2012 <a href="http://breizh-portal.com/index.html">The Breizh touch</a></span>',
	'SHOUT_VERSION_UP_TO_DATE'		=> 'Your installation is up to date, no update is available for your version of Breizh Shoutbox: v%s. You do not need to update your installation.',
	'SHOUT_NO_VERSION'				=> '<span style="color: red">Failed to obtain latest version information...</span>',

	'SHOUT_PAGES'					=> 'pages',
	'SHOUT_SECONDES'				=> 'seconds',
	'SHOUT_APERCU'					=> 'overview: ',
	'SHOUT_DATE'					=> 'date',
	'SHOUT_USER'					=> 'user',
	'SHOUT_HOURS'					=> 'Hours',
	'SHOUT_PIXELS'					=> 'Pixels',
	'SHOUT_NEVER'					=> 'Never done',
	'SHOUT_LOG_ENTRIE'				=> 'Type attempt made',
	'SHOUT_NO_ADMIN'				=> 'You do not have administrative rights and can not access the resource',
	'SHOUT_SERVER_HOUR'				=> [
		1	=> 'The current server hour is: %d hour %s',
		2	=> 'The current server hour is: %d hours %s',
	],
	'SHOUT_BAR'						=> 'Position of the post box',
	'SHOUT_BAR_EXPLAIN'				=> 'Choose whether you want the post box at the top or bottom of the shoutbox.',

	'SHOUT_SOUND_NEW'				=> 'Sound of new posts',
	'SHOUT_SOUND_NEW_EXPLAIN'		=> 'Choose the sound to be activated by default for the arrival of new messages.',
	'SHOUT_SOUND_ERROR'				=> 'Sound of errors',
	'SHOUT_SOUND_ERROR_EXPLAIN'		=> 'Choose the sound to be activated by default for the errors.',
	'SHOUT_SOUND_DEL'				=> 'Sound deletions',
	'SHOUT_SOUND_DEL_EXPLAIN'		=> 'Select the sound to be activated by default for deletions of messages',
	'SHOUT_ALL_MESSAGES'			=> ' all messages of the shoutbox,',
	'SHOUT_PANEL'					=> 'Retractable Lateral Panel',
	'SHOUT_PANEL_EXPLAIN'			=> 'Activate the retractable lateral panel on every page of the forum does not include the shoutbox, except in the excluded pages.',
	'SHOUT_PANEL_ALL'				=> 'Retractable lateral panel anywhere',
	'SHOUT_PANEL_ALL_EXPLAIN'		=> 'Activate the retractable lateral panel over the pages that already has the shoutbox.',
	'SHOUT_PANEL_AUTO'				=> 'Automatic loading',
	'SHOUT_PANEL_AUTO_EXPLAIN'		=> 'Enabling this setting automatically loads the shoutbox into the panel when the page loads. The queries are then made and if the sound is activated, new messages received are notified.<br />However, by disabling this setting, the shoutbox is charged when the panel is opened and the number of requests from the shoutbox decreases very widely.',
	'SHOUT_PANEL_FLOAT'				=> 'Panel position',
	'SHOUT_PANEL_FLOAT_EXPLAIN'		=> 'Choose which side of the screen the panel should be displayed on',
	'SHOUT_PANEL_FLOAT_RIGHT'		=> 'to the right',
	'SHOUT_PANEL_FLOAT_LEFT'		=> 'to the left',
	'SHOUT_PANEL_CHOICE'			=> 'Choose if you want to display the retractable lateral panel',
	'SHOUT_TEMP'					=> 'Refresh time',
	'SHOUT_TEMP_TITLE'				=> 'setting the time of updating for shoutbox depending on the status connected/not connected.<br />Too short, there are risks that the server can not respond within the allotted time, too long, you lose responsiveness.<br />change the value until a satisfactory performance according to your server.',
	'SHOUT_TEMP_USERS'				=> 'Refresh time for members',
	'SHOUT_TEMP_USERS_EXPLAIN'		=> 'Choose here the time to refresh the shoutbox for members online.',
	'SHOUT_TEMP_ANONYMOUS'			=> 'Refresh time for guests',
	'SHOUT_TEMP_ANONYMOUS_EXPLAIN'	=> 'Choose here the time to refresh the shoutbox for guests.',
	'SHOUT_TEMP_BOT'				=> 'Refresh time for bots',
	'SHOUT_TEMP_BOT_EXPLAIN'		=> 'Time to refresh for bots is not adjustable, it is set by default to 120 seconds in order not to unnecessarily consume resources.',

// Robot
	'SHOUT_ROBOT_ACTIVATE'			=> 'Enable Robot',
	'SHOUT_ROBOT_ACTIVATE_EXPLAIN'	=> 'Make no completely disables all Robot functions in the shoutbox.<br /><em>Does not disable the enter information in the private shoutbox.</em>',
	'SHOUT_NAME_ROBOT'				=> 'Name of Robot',
	'SHOUT_NAME_ROBOT_EXPLAIN'		=> 'Enter the name you want to assign to the robot',
	'SHOUT_ROBOT_BIRTHDAY'			=> 'Robot’s birthdays',
	'SHOUT_ROBOT_BIRTHDAY_EXPLAIN'	=> 'Enable/disable Notifications of birthdays.',
	'SHOUT_ROBOT_BIRTHDAY_PRIV'		=> 'Robot’s birthdays private shoutbox',
	'SHOUT_ROBOT_BIRTHDAY_PRIV_EXPLAIN'=> 'Enable/disable Notifications of birthdays in private shoutbox.',
	'SHOUT_ROBOT_CHOICE'			=> 'Parameters of the purge Robot in front',
	'SHOUT_ROBOT_CHOICE_EXPLAIN'	=> 'Choose here all infos Robot you want to serve on the front.<br />Vou can add as many options as desired.<br />Note that infos for purge and load shedding will always be erased.',
	'SHOUT_ROBOT_CHOICE_PRIV'		=> 'Parameters of the purge Robot in front private shoutbox',
	'SHOUT_ROBOT_CHOICE_PRIV_EXPLAIN'=> 'Choose here all infos Robot you want to serve on the front.<br />You can add as many options as desired in the private shoutbox.<br />You can add as many choices as desired.<br />Note that infos for purge and load shedding will always be erased.',
	'SHOUT_ROBOT_COLOR'				=> 'Color of the Robot',
	'SHOUT_ROBOT_COLOR_INFO'		=> 'Color of the messages/infos:',
	'SHOUT_ROBOT_CRON_H'			=> 'Schedule informations date and anniversaries',
	'SHOUT_ROBOT_CRON_H_EXPLAIN'	=> 'Enter here the time at which you want information for the current date and anniversaries are disseminated. <em>(24-hour format)</em>',
	'SHOUT_ROBOT_DEL'				=> 'Shedding and automatic purge',
	'SHOUT_ROBOT_DEL_EXPLAIN'		=> 'Enable/disable messages of Shedding and automatic purge in the two shoutbox',
	'SHOUT_ROBOT_EDIT'				=> 'Editing Posts',
	'SHOUT_ROBOT_EDIT_EXPLAIN'		=> 'Enable editing info messages in the main shoutbox.',
	'SHOUT_ROBOT_EDIT_PRIV'			=> 'Editing Posts n private shoutbox',
	'SHOUT_ROBOT_EDIT_PRIV_EXPLAIN'	=> 'Enable editing info messages in the private shoutbox.',
	'SHOUT_ROBOT_EXCLU'				=> 'Excluded forums',
	'SHOUT_ROBOT_EXCLU_EXPLAIN'		=> 'Select the forums to which you do not want to publicize the release of new posts.<br />=> Note that the displays of information depends on the Rights of views Forums.',
	'SHOUT_ROBOT_HELLO'				=> 'Robot’s date of day',
	'SHOUT_ROBOT_HELLO_EXPLAIN'		=> 'Enable/disable notification of the current date.',
	'SHOUT_ROBOT_HELLO_PRIV'		=> 'Robot’s date of day private shoutbox',
	'SHOUT_ROBOT_HELLO_PRIV_EXPLAIN'=> 'Enable/disable notification of the current date in private shoutbox',
	'SHOUT_ROBOT_MESSAGE'			=> 'Robot topics',
	'SHOUT_ROBOT_MESSAGE_EXPLAIN'	=> 'Enable info new topics in the main shoutbox.',
	'SHOUT_ROBOT_MES_PRIV'			=> 'Robot topics in private shoutbox',
	'SHOUT_ROBOT_MES_PRIV_EXPLAIN'	=> 'Enable info new topics in the private shoutbox.',
	'SHOUT_ROBOT_NEWEST'			=> 'Robot of new registrations',
	'SHOUT_ROBOT_NEWEST_EXPLAIN'	=> 'Enable/disable Notifications of new registrations.',
	'SHOUT_ROBOT_NEWEST_PRIV'		=> 'Robot of new registrations private shoutbox',
	'SHOUT_ROBOT_NEWEST_PRIV_EXPLAIN'=> 'Enable/disable Notifications of new registrations in private shoutbox.',
	'SHOUT_ROBOT_PREZ'				=> 'Presentation forum',
	'SHOUT_ROBOT_PREZ_EXPLAIN'		=> 'Enter here the number of presentation forum members if you have one. The robot will broadcast different information.',
	'SHOUT_ROBOT_REP'				=> 'Replies to topics',
	'SHOUT_ROBOT_REP_EXPLAIN'		=> 'Enable Info replies to topics in the main shoutbox.',
	'SHOUT_ROBOT_REP_PRIV'			=> 'Replies to topics in private shoutbox',
	'SHOUT_ROBOT_REP_PRIV_EXPLAIN'	=> 'Enable Info replies to topics in the private shoutbox.',
	'SHOUT_ROBOT_SESSION'			=> 'Sessions Robot',
	'SHOUT_ROBOT_SESSION_EXPLAIN'	=> 'Enables/disables the welcome message to each user when they log on the forum.',
	'SHOUT_ROBOT_SESSION_PRIV'		=> 'Sessions Robot in private shoutbox',
	'SHOUT_ROBOT_SESSION_PRIV_EXPLAIN'=> 'Enables/disables the welcome message to each user when they log on the forum in the private shoutbox.',
	'SHOUT_ROBOT_SESSION_R'			=> 'Sessions Robot of bots',
	'SHOUT_ROBOT_SESSION_R_EXPLAIN'	=> 'Enable/disable notification of bots connection when they connect to the forum in main shoutbox.',
	'SHOUT_ROBOT_SESSION_R_PRIV'	=> 'Sessions Robot of bots in private shoutbox',
	'SHOUT_ROBOT_SESSION_R_PRIV_EXPLAIN'=> 'Enable/disable lnotification of bots connection when they connect to the forum in private shoutbox.',
	'SHOUT_ROBOT_TIME'				=> 'Time between two connections',
	'SHOUT_ROBOT_TIME_EXPLAIN'		=> 'This setting allows you to set the time interval between two user connections without the robot signaling it again.',

	'SHOUT_ON_CONNECT'				=> 'Connections Infos',
	'SHOUT_ON_SUBJET'				=> 'New forum topics',
	'SHOUT_ON_REPONSE'				=> 'Responses to topics and editions',
	'SHOUT_ON_BIRTHDAY'				=> 'Birthdays',
	'SHOUT_ON_DAY'					=> 'Dates of the Day',
	'SHOUT_ON_NEWS'					=> 'New registrations',
	'SHOUT_ON_PRIV'					=> 'Connections in private shout',
	'SHOUT_PURGE_ON'				=> 'Purge the ',
	'SHOUT_NO_MOD_ROBOT'			=> 'You do not have at this moment, any mod on your forum that is compatible with the robot Breizh Shoutbox...',
	'SHOUT_MOD_ROBOT'				=> 'Here you can set options for mods on your forum, compatible with the robot Breizh Shoutbox...',

// Installation
	'SHOUT_WELCOME'					=> 'This is your first post. Welcome in the Breizh Shoutbox... from Sylver35...',
	
	// Add new formats
	'dateformats'	=> array_merge($lang['dateformats'], array(
		'|d M| H:i'					=> 'Today, 13:37 / 01 janv. 13:37',
		'|M jS| g:i a'				=> 'Today, 1:37 pm / janv. 1er 1:37 pm',
		'H:i'						=> '13:37',
		'H:i a'						=> '1:37 pm'
	)),
	
	'SHOUT_USERS_CAN_CHANGE'		=> 'Note that users can enable/disable this setting individually',
	
	'SHOUT_SOUND_EMPTY'				=> 'No sound',
	'SHOUT_SOUND_ON'				=> 'Activate the sounds',
	'SHOUT_SOUND_ON_EXPLAIN'		=> 'Enable/disable all sounds in the shoutbox.',
	
	'SHOUT_PANEL_PERMISSIONS'		=> 'To see this panel, must have permissions: “<em>A user can view the shoutbox in a lateral panel</em>” and “<em>A user can use the shoutbox in popup</em>” to yes.<br />Not enabled for mobile phones.',
	'SHOUT_PANEL_KILL'				=> 'pages excluded',
	'SHOUT_PANEL_KILL_EXPLAIN'		=> 'You can select or exclude pages displaying the retractable lateral panel.<br />Enter the name of the php page with the settings and the path if different from root.<br />One page per line. Ex: <em>ucp.php?mode=register&nbsp;&nbsp;gallery/index.php</em><br />Pages automatically excluded: errors, informations, redirections and connexion.',
	'SHOUT_PANEL_IMG'				=> 'Opening Image',
	'SHOUT_PANEL_IMG_EXPLAIN'		=> 'Choose the opening image for the retractable lateral panel.<br />Images of directory root/images/shoutbox/panel/',
	'SHOUT_PANEL_EXIT_IMG'			=> 'Closing Image',
	'SHOUT_PANEL_EXIT_IMG_EXPLAIN'	=> 'Choose the closing image for the retractable lateral panel.<br />Images of directory root/images/shoutbox/panel/',
	'SHOUT_PANEL_WIDTH'				=> 'Lateral panel width',
	'SHOUT_PANEL_WIDTH_EXPLAIN'		=> 'Specify the width of the retractable lateral panel.<br />Note that it must contain in the shoutbox in popup.',
	'SHOUT_PANEL_HEIGHT'			=> 'Lateral panel height',
	'SHOUT_PANEL_HEIGHT_EXPLAIN'	=> 'Specify the height of the retractable lateral panel.',
	
	'SHOUT_INACTIV_A'				=> 'Inactivity time of guests',
	'SHOUT_INACTIV_A_EXPLAIN'		=> 'Here you determine the time of inactivity of the guests, after this period, the shoutbox will automatically standby and so will not do more requests.',
	'SHOUT_INACTIV_B'				=> 'Inactivity time of registered users',
	'SHOUT_INACTIV_B_EXPLAIN'		=> 'Here you determine the time of inactivity of the registered users, after this period, the shoutbox will automatically standby and so will not do more requests.<br />Note that there is a permission to skip this.',
));
