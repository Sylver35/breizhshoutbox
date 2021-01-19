<?php
/**
*
* Breizh Shoutbox Extension [French]
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
// ’ « » “ ” …
//

$lang = array_merge($lang, array(
// Main tab
	'ACP_SHOUTBOX'					=> 'Breizh Shoutbox',
// General category
	'ACP_SHOUT_GENERAL_CAT'			=> 'Généralités',
	'ACP_SHOUT_CONFIGS'				=> 'Configuration générale',
	'ACP_SHOUT_CONFIGS_T'			=> 'Configuration générale de Breizh Shoutbox',
	'ACP_SHOUT_CONFIGS_T_EXPLAIN'	=> 'Sur cette page, vous pouvez régler tous les différents paramètres généraux de la shoutbox.',
	'ACP_SHOUT_RULES'				=> 'Règles d’utilisation',
	'ACP_SHOUT_RULES_T'				=> 'Panneau des Règles d’utilisation de la Shoutbox',
	'ACP_SHOUT_RULES_T_EXPLAIN'		=> 'Cette page vous permet de définir des règles d’utilisation de la shoutbox.<br />Vous pouvez mettre des règles dans les différents langages activés sur ce forum.<br />Cet espace vous permet de créer/éditer des règles. Vous pouvez les concevoir dans le premier cadre puis les exporter à volonté vers les zones voulues.',
// Category for main shoutbox
	'ACP_SHOUT_PRINCIPAL_CAT'		=>	'Shoutbox principale',
	'ACP_SHOUT_OVERVIEW'			=> 'Messages et statistiques',
	'ACP_SHOUT_OVERVIEW_T'			=> 'Messages et statistiques de Breizh Shoutbox',
	'ACP_SHOUT_OVERVIEW_T_EXPLAIN'	=> 'Sur cette page, vous pouvez voir les statistiques de la shoutbox principale.
										<br />Vous pouvez également supprimer des messages ou purger totalement la shoutbox.',
	'ACP_SHOUT_CONFIG_GEN'			=> 'Paramètres shoutbox principale',
	'ACP_SHOUT_CONFIG_GEN_T'		=> 'Paramètres de la shoutbox principale du forum',
	'ACP_SHOUT_CONFIG_GEN_T_EXPLAIN'=> 'Sur cette page, vous pouvez régler tous les différents paramètres de la shoutbox Principale de votre forum.',
// Category for private shoutbox
	'ACP_SHOUT_PRIVATE_CAT'			=> 'Shoutbox Privée',
	'ACP_SHOUT_PRIVATE'				=> 'Messages et statistiques',
	'ACP_SHOUT_PRIVATE_T'			=> 'Panneau des Messages et statistiques de la shoutbox privée',
	'ACP_SHOUT_PRIVATE_T_EXPLAIN'	=> 'Sur cette page, vous pouvez voir les statistiques de la shoutbox privée.
										<br />Vous pouvez également supprimer des messages ou purger totalement la shoutbox.',
	'ACP_SHOUT_CONFIG_PRIV'			=> 'Paramètres Shoutbox Privée',
	'ACP_SHOUT_CONFIG_PRIV_T'		=> 'Panneau de configuration de la Shoutbox Privée',
	'ACP_SHOUT_CONFIG_PRIV_T_EXPLAIN'=> 'Sur cette page, vous pouvez régler tous les différents paramètres de la shoutbox Privée de votre forum.
										<br />Pour bien régler la permission d’utilisation de cette shoutbox privée, rendez-vous dans les permissions, onglet "Breizh Shoutbox": "Peut accéder à la shoutbox privée"',
// Category for popup shoutbox
	'ACP_SHOUT_POPUP_CAT'			=> 'Shoutbox en popup',
	'ACP_SHOUT_POPUP'				=> 'Paramètres de la popup',
	'ACP_SHOUT_POPUP_T'				=> 'Paramètres de la popup Breizh Shoutbox',
	'ACP_SHOUT_POPUP_T_EXPLAIN'		=> 'Sur cette page, vous pouvez régler tous les différents paramètres de la shoutbox en popup.<br />Ces réglages concernent aussi la shoutbox en panneau latéral rétractable.',
// Category for retractable lateral panel
	'ACP_SHOUT_PANEL'				=> 'Paramètres du panneau latéral',
	'ACP_SHOUT_PANEL_T'				=> 'Paramètres du panneau latéral rétractable',
	'ACP_SHOUT_PANEL_T_EXPLAIN'		=> 'Sur cette page, vous pouvez régler tous les différents paramètres du panneau latéral rétractable.<br />Notez que ce panneau latéral contient la shoutbox en popup.',
// Category for smilies
	'ACP_SHOUT_SMILIES_CAT'			=> 'Smileys',
	'ACP_SHOUT_SMILIES'				=> 'Paramètres des Smileys',
	'ACP_SHOUT_SMILIES_T'			=> 'Paramètres des smileys pour la shoutbox',
	'ACP_SHOUT_SMILIES_T_EXPLAIN'	=> 'Sur cette page, vous pouvez configurer les smileys devant être affichés dans la shoutbox.<br />
										Sont affichés tous les smileys présents dans la base de données, indifférement de ceux affichés sur la page de rédaction des messages.<br />
										Pour spécifier quels smileys doivent apparaitre ou non, il vous suffit de cliquer directement sur les images des smileys.',
	'ACP_SHOUT_SMILIES_POP'			=> 'Popup Smileys',
	'ACP_SHOUT_SMILIES_POP_T_EXPLAIN'=> 'Popup Smileys',
// Category for robot
	'ACP_SHOUT_ROBOT_CAT'			=> 'Robot de la shoutbox',
	'ACP_SHOUT_ROBOT'				=> 'Configuration du Robot',
	'ACP_SHOUT_ROBOT_T'				=> 'Configuration du Robot de Breizh Shoutbox',
	'ACP_SHOUT_ROBOT_T_EXPLAIN'		=> 'Sur cette page, vous pouvez régler tous les différents points de configuration du Robot de la shoutbox.<br />Certains paramètres sont soit pour la shoutbox principale, soit pour la shoutbox privée.',

// Language for Logs
	'LOG_SHOUT_CONFIGS'				=> '<strong>Mise à jour de la configuration générale de Breizh Shoutbox.</strong>',
	'LOG_SHOUT_CONFIG_GEN'			=> '<strong>Mise à jour des paramètres de la Shoutbox générale.</strong>',
	'LOG_SHOUT_CONFIG_PRIV'			=> '<strong>Mise à jour des paramètres de la Shoutbox privée.</strong>',
	'LOG_SHOUT_RULES'				=> '<strong>Mise à jour des règles de Breizh Shoutbox.</strong>',
	'LOG_SHOUT_POPUP'				=> '<strong>Mise à jour des paramètres de la popup Breizh Shoutbox.</strong>',
	'LOG_SHOUT_PANEL'				=> '<strong>Mise à jour des paramètres du panneau latéral Breizh Shoutbox.</strong>',
	'LOG_SHOUT_ROBOT'				=> '<strong>Mise à jour des paramètres du Robot Breizh Shoutbox.</strong>',
	'LOG_PURGE_SHOUTBOX'			=> '<strong>Purge de tous les Messages de la Shoutbox.</strong>',
	'LOG_PURGE_SHOUTBOX_PRIV'		=> '<strong>Purge de tous les Messages de la Shoutbox privée.</strong>',
	'LOG_PURGE_SHOUTBOX_ROBOT'		=> '<strong>Purge de %s infos de Robot de la Shoutbox.</strong>',
	'LOG_PURGE_SHOUTBOX_PRIV_ROBOT'	=> '<strong>Purge de %s infos de Robot de la Shoutbox privée.</strong>',
	'LOG_SELECT_SHOUTBOX'			=> '<strong>Suppression de %s Message sélectionné de la Shoutbox.</strong>',
	'LOG_SELECTS_SHOUTBOX'			=> '<strong>Suppression de %s Messages sélectionnés de la Shoutbox.</strong>',
	'LOG_SELECT_SHOUTBOX_PRIV'		=> '<strong>Suppression de %s Message sélectionné de la Shoutbox privée.</strong>',
	'LOG_SELECTS_SHOUTBOX_PRIV'		=> '<strong>Suppression de %s Messages sélectionnés de la Shoutbox privée.</strong>',
	'LOG_LOG_SHOUTBOX'				=> '<strong>Suppression de %s entrée sélectionnée du log utilisateurs (shoutbox).</strong>',
	'LOG_LOGS_SHOUTBOX'				=> '<strong>Suppression de %s entrées sélectionnées du log utilisateurs (shoutbox).</strong>',
	'LOG_LOG_SHOUTBOX_PRIV'			=> '<strong>Suppression de %s entrée sélectionnée du log utilisateurs (shoutbox privée).</strong>',
	'LOG_LOGS_SHOUTBOX_PRIV'		=> '<strong>Suppression de %s entrées sélectionnées du log utilisateurs (shoutbox privée).</strong>',
	'LOG_SHOUT_PRUNED'				=> '<strong>Breizh Shoutbox délestée</strong>',
	'LOG_SHOUT_PRIV_PRUNED'			=> '<strong>Breizh Shoutbox privée délestée</strong>',
	'LOG_SHOUT_REMOVED'				=> '<strong>Délestage automatique de %1$s messages de la shoutbox.</strong>',
	'LOG_SHOUT_PRIV_REMOVED'		=> '<strong>Délestage automatique de %1$s messages de la shoutbox privée.</strong>',
	'LOG_SHOUT_PURGED'				=> '<strong>Purge temporelle automatique de %1$s messages de la shoutbox.</strong>',
	'LOG_SHOUT_PRIV_PURGED'			=> '<strong>Purge temporelle automatique de %1$s messages de la shoutbox privée.</strong>',
	'LOG_SHOUT_SCRIPT'				=> '<strong>Tentative de post de script dans la shoutbox.</strong>',
	'LOG_SHOUT_APPLET'				=> '<strong>Tentative de post d’applet dans la shoutbox.</strong>',
	'LOG_SHOUT_ACTIVEX'				=> '<strong>Tentative de post d’objet active x dans la shoutbox.</strong>',
	'LOG_SHOUT_OBJECTS'				=> '<strong>Tentative de post d’objet chrome ou about dans la shoutbox.</strong>',
	'LOG_SHOUT_IFRAME'				=> '<strong>Tentative de post d’iframe dans la shoutbox.</strong>',
	'LOG_SHOUT_PRUNED_PRIV'			=> '<strong>Breizh Shoutbox privée délestée</strong>',
	'LOG_SHOUT_REMOVED_PRIV'		=> '<strong>Suppression automatique de %1$s messages de la shoutbox privée.</strong>',
	'LOG_SHOUT_PURGED_PRIV'			=> '<strong>Purge temporelle automatique de %1$s messages de la shoutbox privée.</strong>',
	'LOG_SHOUT_SCRIPT_PRIV'			=> '<strong>Tentative de post de script dans la shoutbox privée.</strong>',
	'LOG_SHOUT_APPLET_PRIV'			=> '<strong>Tentative de post d’applet dans la shoutbox privée.</strong>',
	'LOG_SHOUT_ACTIVEX_PRIV'		=> '<strong>Tentative de post d’objet active x dans la shoutbox privée.</strong>',
	'LOG_SHOUT_OBJECTS_PRIV'		=> '<strong>Tentative de post d’objet chrome ou about dans la shoutbox privée.</strong>',
	'LOG_SHOUT_IFRAME_PRIV'			=> '<strong>Tentative de post d’iframe dans la shoutbox privée.</strong>',
	'SHOUT_LOGS'					=> 'Tentatives de post interdites',
	'SHOUT_LOGS_EXPLAIN'			=> 'Nombre total de tentatives de post d’éléments interdits dans la shoutbox',
	'NUMBER_LOG_TOTAL'				=> [
		1	=> '<strong>%d</strong> tentative depuis le %s',
		2	=> '<strong>%d</strong> tentatives depuis le %s',
	],
	'NO_MESSAGE'					=> 'Il n’y a aucun message',
	'NO_SHOUT_LOG'					=> 'Il n’y a aucune entrée',
	'NUMBER_MESSAGE'				=> [
		1	=> '<strong>%d</strong> message',
		2	=> '<strong>%d</strong> messages',
	],
	'NUMBER_LOG'					=> [
		1	=> '<strong>%d</strong> entrée',
		2	=> '<strong>%d</strong> entrées',
	],
	'ORDER'							=> 'ordre',
	'SHOUT_MESSAGES'				=> 'Messages',

	'SHOUT_NORMAL'					=> 'shoutbox générale',
	'SHOUT_PRIVATE'					=> 'shoutbox privée',
	'DISPLAY_ON_SHOUTBOX'			=> 'Préférences d’affichage dans la shoutbox',

	'SHOUT_RULES_ACTIVE'			=> 'Règles de la shoutbox',
	'SHOUT_RULES_ACTIVE_EXPLAIN'	=> 'Activer/Désactiver les règles de la shoutbox.',
	'SHOUT_RULES_OPEN'				=> 'Règles toujours ouvertes',
	'SHOUT_RULES_OPEN_EXPLAIN'		=> 'Permet de toujours faire afficher les règles pour tous',
	'SHOUT_RULES_ON'				=> 'Règles en langage “%s” “%s”',
	'SHOUT_RULES_ON_EXPLAIN'		=> 'Entrez ici les Règles dans la langue “%s” “%s” pour la shoutbox générale.<br />Les bbcodes, les liens et les smileys sont activés.',
	'SHOUT_RULES_ON_PRIV_EXPLAIN'	=> 'Entrez ici les Règles dans la langue “%s” “%s” pour la shoutbox privée.<br />Les bbcodes, les liens et les smileys sont activés.',
	'SHOUT_RULES_VIEW'				=> 'Visualisation des Règles shoutbox générale:',
	'SHOUT_RULES_VIEW_PRIV'			=> 'Visualisation des Règles shoutbox privée:',
	'SMILIES_EMOTION'				=> 'Émotion du smiley',
	'SMILIES_OVERVIEW'				=> 'Smileys affichés par défaut',
	'SMILIES_POPUP'					=> 'Smileys affichés en secondaire',
	'SMILIES_DISPLAYED'				=> 'Affichage par défaut',
	'SMILIES_NO_DISPLAYED'			=> 'Affichage en secondaire',
	'SMILIES_CLIC_NO'				=> 'Cliquez pour afficher ce smiley en secondaire',
	'SMILIES_CLIC_YES'				=> 'Cliquez pour afficher ce smiley par défaut',

	'SHOUT_AVATAR'					=> 'Affichage des avatars',
	'SHOUT_AVATAR_EXPLAIN'			=> 'Indiquez si vous souhaitez activer l’affichage des avatars des utilisateurs',
	'SHOUT_AVATAR_HEIGHT'			=> 'Dimension des avatars',
	'SHOUT_AVATAR_HEIGHT_EXPLAIN'	=> 'Indiquez ici la hauteur des avatars en pixels, la largeur est calculée automatiquement',
	'SHOUT_AVATAR_IMG'				=> 'Image de l’avatar par defaut',
	'SHOUT_AVATAR_IMG_EXPLAIN'		=> 'Spécifiez ici l’image choisie pour l’avatar par défaut pour les utilisateurs n’en ayant pas choisi.<br />Cette image doit se trouver dans le dossier “ext/sylver35/breizhshoutbox/images/”',
	'SHOUT_AVATAR_IMG_BOT'			=> 'Image de l’avatar du robot',
	'SHOUT_AVATAR_IMG_BOT_EXPLAIN'	=> 'Spécifiez ici l’image choisie pour l’avatar du robot.<br />Cette image doit se trouver dans le dossier “ext/sylver35/breizhshoutbox/images/”',
	'SHOUT_AVATAR_ROBOT'			=> 'Avatar du robot',
	'SHOUT_AVATAR_ROBOT_EXPLAIN'	=> 'Activer/Désactiver l’avatar du robot <em>si les avatars sont activés</em>',
	'SHOUT_AVATAR_USER'				=> 'Avatars des utilisateurs',
	'SHOUT_AVATAR_USER_EXPLAIN'		=> 'Activer/désactiver l’avatar par défaut pour les utilisateurs n’ayant pas d’avatar',

	'SHOUT_BAR_TOP'					=> 'En haut de la shoutbox',
	'SHOUT_BAR_BOTTOM'				=> 'En bas de la shoutbox',
	'SHOUT_BACKGROUND_COLOR'		=> 'Image de fond de la shoutbox',
	'SHOUT_BACKGROUND_COLOR_EXPLAIN'=> 'Choisissez l’image de fond de la shoutbox',
	'SHOUT_BBCODE'					=> 'Interdiction de bbcodes',
	'SHOUT_BBCODE_EXPLAIN'			=> 'Entrez ici la liste des bbcodes que vous souhaitez interdire dans la shoutbox.<br />Certains bbcodes risquent de provoquer des bugs, votre expérience vous permettra de les lister ici.<br />Vous devez les saisir sans les crochets, séparés par une virgule et un espace.<br />Ex:&nbsp;&nbsp;<em>list, code, quote</em>',
	'SHOUT_BBCODE_USER_EXPLAIN'		=> 'Entrez ici la liste des bbcodes que vous souhaitez interdire dans la mise en forme des messages des utilisateurs.<br />La liste des bbcodes interdits au dessus est déjà prise en compte, cette liste est donc un complément. Les vidéos sont déjà interdites.<br />Vous devez les saisir sans les crochets, séparés par une virgule et un espace.<br />Ex:&nbsp;&nbsp;<em>list, code, quote</em>',
	'SHOUT_BBCODE_SIZE'				=> 'Taille de la police',
	'SHOUT_BBCODE_SIZE_EXPLAIN'		=> 'Indiquez ici la taille maximale de la police autorisée pour le bbcode size= dans la mise en forme des messages des utilisateurs.<br />Le chiffre 100 correspond à la taille générale de la police, 150 correspond à une fois et demie cette taille.',
	'SHOUT_BIRTHDAY_EXCLUDE'		=> 'Exclure des groupes',
	'SHOUT_BIRTHDAY_EXCLUDE_EXPLAIN'=> 'Vous pouvez sélectionner un ou des groupes qui seront exclus des anniversaires à souhaiter.<br />Les membres bannis sont exclus d’office.<br /><br />Utilisez ctrl+clic pour sélectionner plus d’un groupe.',
	'SHOUT_BUTTON_BACKGROUND'		=> 'Image de fond sous les boutons',
	'SHOUT_BUTTON_BACKGROUND_EXPLAIN'=> 'Choisissez d’afficher ou non l’image de fond sous les boutons de gauche',

	'SHOUT_CONFIG_TITLE'			=> 'Titre de la shoutbox',
	'SHOUT_CONFIG_TITLE_EXPLAIN'	=> 'Vous pouvez choisir un titre pour votre shoutbox, il se trouve à gauche, notez qu’il sera mis automatiquement en majuscules',
	'SHOUT_COPY_RULE'				=> 'Exporter vers règles “%1$s” %2$s',
	'SHOUT_CORRECT'					=> 'Correction des minutes',
	'SHOUT_CORRECT_EXPLAIN'			=> 'Activer ce paramètre permet de faire corriger automatiquement l’affichage des minutes de l’heure des messages si l’utilisateur utilise un format de date contenant "il y a moins d’une minute" <em>(Auto refresh)</em>. Ceci ne touche que les messages de moins d’une heure.',

	'SHOUT_DATE_LAST_RUN'			=> 'Date du dernier délestage automatique',

	'NUMBER_SHOUTS' 				=> 'Nombre total de messages',
	'SHOUT_STATS'					=> 'Messages de la Shoutbox',
	'SHOUT_STATISTICS'				=> 'Statistiques',
	'SHOUT_VERSION'					=> 'Version de la shoutbox',

	'SHOUT_OPTIONS'					=> 'Purge de la Shoutbox',
	'PURGE_SHOUT'					=> 'Supprimer tous les messages',
	'PURGE_SHOUT_MESSAGES'			=> 'Supprimer les messages',
	'PURGE_SHOUT_ROBOT'				=> 'Supprimer les infos du Robot',
	'PURGE_SHOUT_ROBOT_EXPLAIN'		=> 'Vous pouvez supprimer les infos du Robot en fonction du type d’info...',

	'SHOUT_DEFIL_TOP'				=> 'Dernier message en haut',
	'SHOUT_DEFIL_BOTTOM'			=> 'Dernier message en bas',
	'SHOUT_DEFIL'					=> 'Sens de défilement des messages',
	'SHOUT_DEFIL_EXPLAIN'			=> 'Vous pouvez choisir dans quel sens les messages défileront dans la shoutbox.<br />- Soit le dernier message en haut puis défilant vers le bas<br />- Soit le dernier message en bas puis défilant vers le haut.<br />Notez que le focus sera toujours sur le message le plus récent.',
	'SHOUT_DEFIL_MEMBERS'			=> 'les membres peuvent choisir individuellement un réglage différent.',
	'SHOUT_DEL_MAIN'				=> 'Messages supprimés',
	'SHOUT_DEL_ACP'					=> 'Nombre de messages supprimés dans l’acp:',
	'SHOUT_DEL_AUTO'				=> 'Nombre de messages supprimés automatiquement:',
	'SHOUT_DEL_PURGE'				=> 'Nombre de messages supprimés lors d’une purge:',
	'SHOUT_DEL_USER'				=> 'Nombre de messages supprimés par les utilisateurs:',
	'SHOUT_DEL_NR'					=> [
		1	=> '<strong>%s</strong> message supprimé',
		2	=> '<strong>%s</strong> messages supprimés',
	],
	'SHOUT_DEL_TOTAL'				=> ' au total',
	'SHOUT_EDIT_RULE'				=> 'Éditer ce texte',

	'SHOUT_MAX_CHARS'				=> 'caractères',

	'SHOUT_WIDTH_POST'				=> 'Dimension de la zone de post',
	'SHOUT_WIDTH_POST_PRO_EXPLAIN'	=> 'Choisissez ici la longueur de la zone de saisie des messages de la shoutbox (en pixels)',

	'SHOUT_PRUNE_TIME'				=> 'Temps de délestage',
	'SHOUT_PRUNE_TIME_EXPLAIN'		=> 'Le temps où les messages sont automatiquement délestés. Si ce paramètre est à 0 ou si le paramètre de nombre maximum de messages dans la BDD est activé, ce paramètre est annulé.',
	'SHOUT_MAX_POSTS'				=> 'Nombre maximum de messages dans la BDD',
	'SHOUT_MAX_POSTS_EXPLAIN'		=> 'Entrez 0 pour désactiver ce paramètre. Si activé, le paramètre de temps de délestage sera <strong>annulé</strong> automatiquement!<br />La différence entre ce paramètre et le nombre maxi à afficher fait office d’archives.',
	'SHOUT_MAX_POSTS_ON'			=> 'Nombre maxi de messages à afficher',
	'SHOUT_MAX_POSTS_ON_EXPLAIN'	=> 'Ceci vous permet de spécifier le nombre maximum de messages devant être affichés dans la shoutbox, indépendamment du nombre maximum.',

	'SHOUT_INACTIV_A'				=> 'Temps d’inactivité des invités',
	'SHOUT_INACTIV_A_EXPLAIN'		=> 'Déterminez ici le temps d’inactivité des invités, passé ce délai, la shoutbox se mettra automatiquement en veille et ainsi ne fera plus de requêtes.',
	'SHOUT_INACTIV_B'				=> 'Temps d’inactivité des utilisateurs enregistrés',
	'SHOUT_INACTIV_B_EXPLAIN'		=> 'Déterminez ici le temps d’inactivité des utilisateurs enregistrés, passé ce délai, la shoutbox se mettra automatiquement en veille et ainsi ne fera plus de requêtes.<br />Notez qu’il existe une permission pour ignorer ceci.',
	'SHOUT_FLOOD_INTERVAL'			=> 'Intervale de Flood',
	'SHOUT_FLOOD_INTERVAL_EXPLAIN'	=> 'Temps minimum entre 2 messages pour les utilisateurs. Régler 0 pour le désactiver. Une permission utilisateur existe pour l’ignorer',
	'SHOUT_NR_ACP'					=> 'Nombre de messages dans l’acp',
	'SHOUT_NR_ACP_EXPLAIN'			=> 'Choisissez le nombre de messages par page dans l’acp, onglets "Messages et Statistiques".',
	'SHOUT_MAX_POST_CHARS'			=> 'Nombre maximum de caractères',
	'SHOUT_MAX_POST_CHARS_EXPLAIN'	=> 'Choisissez le nombre maximum de caractères qu’il est possible de poster dans un message.<br />Notez qu’il existe une permission pour ignorer cette limite',
	'SHOUT_NUM'						=> 'Nombre de messages par page',

	'SHOUT_HEIGHT'					=> 'Hauteur de la div des messages',
	'SHOUT_HEIGHT_EXPLAIN'			=> 'Déterminez ici la hauteur de la div des messages dans la shoutbox.',
	'SHOUT_DIV_IMG'					=> 'Image de fond de la div des messages',
	'SHOUT_DIV_IMG_EXPLAIN'			=> 'Vous pouvez ajouter une image de fond dans la div des messages (ayant un certain niveau de transparence).<br />Image à mettre dans “styles/all/theme/images/background/”<br />Possibilité d’avoir une image différente (portant un nom identique) pour chaque style ajouté.<br />Définissez aussi la position de l’image',
	'SHOUT_DIV_HORIZONTAL'			=> 'position horizontale',
	'SHOUT_DIV_VERTICAL'			=> 'position verticale',
	'SHOUT_DIV_NONE'				=> 'aucune image',
	'SHOUT_DIV_TOP'					=> 'en haut',
	'SHOUT_DIV_CENTER'				=> 'au centre',
	'SHOUT_DIV_RIGHT'				=> 'à droite',
	'SHOUT_DIV_BOTTOM'				=> 'en bas',
	'SHOUT_POSITION_INDEX'			=> 'Position de la shoutbox sur l’index',
	'SHOUT_POSITION_INDEX_EXPLAIN'	=> 'Déterminez quelle position vous souhaitez attribuer à la shoutbox sur la page d’index du forum.',
	'SHOUT_POSITION_FORUM'			=> 'Position de la shoutbox dans les forums',
	'SHOUT_POSITION_FORUM_EXPLAIN'	=> 'Déterminez quelle position vous souhaitez attribuer à la shoutbox sur les pages de vue des forums (viewforum).',
	'SHOUT_POSITION_TOPIC'			=> 'Position de la shoutbox dans les sujets',
	'SHOUT_POSITION_TOPIC_EXPLAIN'	=> 'Déterminez quelle position vous souhaitez attribuer à la shoutbox sur les pages de vue des sujets (viewtopic).',

	'SHOUT_ON_CRON'					=> 'Activation des suppressions et délestages automatiques',
	'SHOUT_ON_CRON_EXPLAIN'			=> 'Déterminez si vous souhaitez activer les suppressions et délestages automatiques des messages.',
	'SHOUT_LOG_CRON'				=> 'Log des suppressions et délestages automatiques',
	'SHOUT_LOG_CRON_EXPLAIN'		=> 'Déterminez si vous souhaitez faire inscrire dans le log admin les suppressions et délestages automatiques des messages.',
	'SHOUT_SEE_BUTTONS'				=> 'Affichage des icônes supérieurs',
	'SHOUT_SEE_BUTTONS_EXPLAIN'		=> 'Déterminez si vous souhaitez faire afficher les icônes supérieurs malgré que l’utilisateur ne possède pas les permissions de les utiliser (vue du cadenas au passage de la souris).',
	'SHOUT_SEE_BUTTONS_LEFT'		=> 'Affichage des icônes gauche',
	'SHOUT_SEE_BUTTONS_LEFT_EXPLAIN'=> 'Déterminez si vous souhaitez faire afficher les icônes situés à gauche des messages malgré que l’utilisateur ne possède pas les permissions de les utiliser (vue du cadenas au passage de la souris).',
	'SHOUT_SEE_BUTTON_IP'			=> 'Affichage des ips',
	'SHOUT_SEE_BUTTON_IP_EXPLAIN'	=> 'Déterminez si vous souhaitez faire afficher les boutons des ips, cela annule les permissions qui le permettent.',
	'SHOUT_SEE_CITE'				=> 'Affichage des icônes citer',
	'SHOUT_SEE_CITE_EXPLAIN'		=> 'Déterminez si vous souhaitez faire afficher les icônes citer à gauche des messages',
	'SHOUT_PANEL_PERMISSIONS'		=> 'Pour voir ce panneau, il faut avoir les permissions: “<em>Peut afficher la shoutbox dans le panneau latéral</em>” et “<em>Peut utiliser la shoutbox en popup</em>” à oui.<br />Non activé pour les mobiles.',
	'SHOUT_PANEL_KILL'				=> 'Pages exclues',
	'SHOUT_PANEL_KILL_EXPLAIN'		=> 'Vous pouvez choisir les pages ou exclure l’affichage du Panneau latéral rétractable.<br />Entrez le nom de la page php avec les paramètres ainsi que son chemin si différent de root.<br />Exclure une page php sans les paramètres excluera cette page avec des paramètres.<br />Vous pouvez aussi exclure tout un répertoire ex: <em>app/gallery/</em><br />Une page par ligne. ex: <em>ucp.php?mode=register&nbsp;&nbsp;app/gallery</em><br />Pages exclues d’office: erreurs, informations, redirections et connexion.',
	'SHOUT_PANEL_IMG'				=> 'Image d’ouverture',
	'SHOUT_PANEL_IMG_EXPLAIN'		=> 'Choisissez l’image d’ouverture du Panneau latéral rétractable.<br />Images du dossier images/shoutbox/panel/',
	'SHOUT_PANEL_EXIT_IMG'			=> 'Image de fermeture',
	'SHOUT_PANEL_EXIT_IMG_EXPLAIN'	=> 'Choisissez l’image de fermeture du Panneau latéral rétractable.<br />Images du dossier images/shoutbox/panel/',
	'SHOUT_PANEL_WIDTH'				=> 'Largeur du panneau',
	'SHOUT_PANEL_WIDTH_EXPLAIN'		=> 'Indiquez la largeur du Panneau latéral rétractable.<br />Notez qu’il doit contenir la shoutbox en popup à l’intérieur.',
	'SHOUT_PANEL_HEIGHT'			=> 'Hauteur du panneau',
	'SHOUT_PANEL_HEIGHT_EXPLAIN'	=> 'Indiquez la hauteur du Panneau latéral rétractable.',
	'SHOUT_POP_HEIGHT'				=> 'Hauteur de la popup',
	'SHOUT_POP_HEIGHT_EXPLAIN'		=> 'Déterminez la Hauteur de la popup de la shoutbox',
	'SHOUT_POP_WIDTH'				=> 'Largeur de la popup',
	'SHOUT_POP_WIDTH_EXPLAIN'		=> 'Déterminez la largeur de la popup de la shoutbox',
	'SHOUT_MESSAGES_TOTAL'			=> 'Nombre de messages au total',
	'SHOUT_MESSAGES_TOTAL_EXPLAIN'	=> 'Nombre de messages postés au total depuis l’installation de la Breizh Shoutbox.',
	'SHOUT_MESSAGES_TOTAL_NR'		=> '<strong>%s</strong> messages postés depuis le %s',
	'SHOUT_POSITION_TOP'			=> 'En haut de la page',
	'SHOUT_POSITION_AFTER'			=> 'Après la liste des forums',
	'SHOUT_POSITION_END'			=> 'En bas de la page',
	'SHOUT_POSITION_NONE'			=> 'Ne pas afficher',

	'SHOUTBOX_VERSION_ACP_COPY'		=> '<a href="%1$s" onclick="window.open(this.href);return false;">Breizh Shoutbox v%2$s</a> © 2018-2021 - Breizhcode - The Breizh Touch',
	'SHOUT_PAGES'					=> 'pages',
	'SHOUT_SECONDES'				=> 'secondes',
	'SHOUT_APERCU'					=> 'aperçu: ',
	'SHOUT_DATE'					=> 'date',
	'SHOUT_USER'					=> 'utilisateur',
	'SHOUT_USERS_CAN_CHANGE'		=> 'Notez que les utilisateurs peuvent activer/désactiver ce paramètre individuellement',
	'SHOUT_HOURS'					=> 'Heures',
	'SHOUT_PIXELS'					=> 'Pixels',
	'SHOUT_NEVER'					=> 'Jamais effectué',
	'SHOUT_LOG_ENTRIE'				=> 'Type de tentative effectuée',
	'SHOUT_NO_ADMIN'				=> 'Vous ne disposez pas des droits d’administration et ne pouvez pas accéder à ces ressouces...',
	'SHOUT_SERVER_HOUR'				=> [
		1	=> 'L’heure actuelle du serveur est: %d heure %s',
		2	=> 'L’heure actuelle du serveur est: %d heures %s',
	],
	'SHOUT_BAR'						=> 'Position de la barre de post',
	'SHOUT_BAR_EXPLAIN'				=> 'Choisissez si vous souhaitez afficher la barre de post en haut ou en bas de la shoutbox.',

	'SHOUT_SOUND_NEW'				=> 'Son des nouveaux messages',
	'SHOUT_SOUND_NEW_EXPLAIN'		=> 'Choisissez le son qui sera activé par defaut pour l’arrivée des nouveaux messages.',
	'SHOUT_SOUND_ERROR'				=> 'Son des erreurs',
	'SHOUT_SOUND_ERROR_EXPLAIN'		=> 'Choisissez le son qui sera activé par defaut pour les erreurs.',
	'SHOUT_SOUND_DEL'				=> 'Son des suppressions',
	'SHOUT_SOUND_DEL_EXPLAIN'		=> 'Choisissez le son qui sera activé par defaut pour les suppressions de messages',
	'SHOUT_SOUND_ADD'				=> 'Son des envois',
	'SHOUT_SOUND_ADD_EXPLAIN'		=> 'Choisissez le son qui sera activé par defaut pour les envois de messages',
	'SHOUT_SOUND_EDIT'				=> 'Son des éditions',
	'SHOUT_SOUND_EDIT_EXPLAIN'		=> 'Choisissez le son qui sera activé par defaut pour les éditions de messages',
	'SHOUT_SOUND_EMPTY'				=> 'Aucun son',
	'SHOUT_SOUND_ON'				=> 'Activer les sons',
	'SHOUT_SOUND_ON_EXPLAIN'		=> 'Activer/désactiver tous les sons dans la shoutbox.',
	'SHOUT_ALL_MESSAGES'			=> ' tous les messages des shoutbox,',
	'SHOUT_PANEL'					=> 'Panneau latéral rétractable',
	'SHOUT_PANEL_EXPLAIN'			=> 'Activer le panneau latéral rétractable dans toutes les pages du forum ne comportant pas la shoutbox, sauf dans les pages exclues.',
	'SHOUT_PANEL_ALL'				=> 'Panneau latéral rétractable partout',
	'SHOUT_PANEL_ALL_EXPLAIN'		=> 'Activer le panneau latéral rétractable en plus dans les pages comportant déjà la shoutbox.',
	'SHOUT_PANEL_AUTO'				=> 'Chargement automatique',
	'SHOUT_PANEL_AUTO_EXPLAIN'		=> 'Activer ce paramètre permet de charger automatiquement la shoutbox dans le panneau au chargement de la page. Les requêtes sont alors faites et si le son est activé, les nouveaux messages reçus sont notifiés.<br />Par contre, en désactivant ce paramètre, la shoutbox se charge à l’ouverture du panneau et le nombre de requêtes de la shoutbox diminue très largement.',
	'SHOUT_PANEL_FLOAT'				=> 'Position du panneau',
	'SHOUT_PANEL_FLOAT_EXPLAIN'		=> 'Choisissez de quel coté de l’écran le panneau doit être affiché',
	'SHOUT_PANEL_FLOAT_RIGHT'		=> 'à droite',
	'SHOUT_PANEL_FLOAT_LEFT'		=> 'à gauche',
	'SHOUT_PANEL_CHOICE'			=> 'Choisissez si vous souhaitez afficher le panneau latéral rétractable',
	'SHOUT_TEMP'					=> 'Temps de réactualisation',
	'SHOUT_TEMP_TITLE'				=> 'réglages du délai de réactualisation de la shoutbox en fonction du statut connecté/non connecté.<br />Trop court, il y a des risques que le serveur ne puisse répondre dans le temps impartit, trop long, vous perdez de la réactivité.<br />modifiez la valeur jusqu’à obtention d’un comportement satisfaisant selon votre serveur.',
	'SHOUT_TEMP_USERS'				=> 'Temps de réactualisation pour les membres',
	'SHOUT_TEMP_USERS_EXPLAIN'		=> 'Choisissez ici le temps de réactualisation de la shoutbox pour les membres connectés.',
	'SHOUT_TEMP_ANONYMOUS'			=> 'Temps de réactualisation pour les invités',
	'SHOUT_TEMP_ANONYMOUS_EXPLAIN'	=> 'Choisissez ici le temps de réactualisation de la shoutbox pour les invités.',
	'SHOUT_TEMP_BOT'				=> 'Temps de réactualisation pour les robots',
	'SHOUT_TEMP_BOT_EXPLAIN'		=> 'Le temps de réactualisation pour les robots n’est pas réglable, il est mis par défaut sur 120 secondes afin de ne pas consommer des ressources inutilement.',

// Robot
	'SHOUT_ROBOT_ACTIVATE'			=> 'Activer le Robot',
	'SHOUT_ROBOT_ACTIVATE_EXPLAIN'	=> 'Mettre à non désactive totalement l’ensemble des fonctions Robot dans les shoutbox.<br /><em>Ne désactive pas les infos d’entrées dans la shoutbox privée.</em>',
	'SHOUT_NAME_ROBOT'				=> 'Nom du Robot',
	'SHOUT_NAME_ROBOT_EXPLAIN'		=> 'Indiquez le nom que vous souhaitez attribuer au robot',
	'SHOUT_ROBOT_BIRTHDAY'			=> 'Robot des anniversaires',
	'SHOUT_ROBOT_BIRTHDAY_EXPLAIN'	=> 'Active/désactive les notifications des anniversaires.',
	'SHOUT_ROBOT_BIRTHDAY_PRIV'		=> 'Robot des anniversaires shoutbox privée',
	'SHOUT_ROBOT_BIRTHDAY_PRIV_EXPLAIN'	=> 'Active/désactive la notification des anniversaires dans la shoutbox privée soient diffusées.',
	'SHOUT_ROBOT_CHOICE'			=> 'Paramètres de la purge Robot en façade',
	'SHOUT_ROBOT_CHOICE_EXPLAIN'	=> 'Choisissez ici toutes les infos Robot que vous souhaitez pouvoir purger en façade.<br />Vous pouvez ajouter autant de choix que désiré.<br />Notez que les infos de purge et délestage seront toujours effacées.',
	'SHOUT_ROBOT_CHOICE_PRIV'		=> 'Paramètres de la purge Robot en façade shoutbox privée',
	'SHOUT_ROBOT_CHOICE_PRIV_EXPLAIN'=> 'Choisissez ici toutes les infos Robot que vous souhaitez pouvoir purger en façade dans la shoutbox privée.<br />Vous pouvez ajouter autant de choix que désiré.<br />Notez que les infos de purge et délestage seront toujours effacées.',
	'SHOUT_ROBOT_COLOR'				=> 'Couleur du Robot',
	'SHOUT_ROBOT_COLOR_INFO'		=> 'Couleur des messages/infos:',
	'SHOUT_ROBOT_CRON_H'			=> 'Horaire des infos de date et anniversaires',
	'SHOUT_ROBOT_CRON_H_EXPLAIN'	=> 'Indiquez ici l’heure à laquelle vous souhaitez que les infos de la date du jour et des anniversaires soient diffusées. <em>(format 24 heures)</em>',
	'SHOUT_ROBOT_DEL'				=> 'Délestages et purges automatiques',
	'SHOUT_ROBOT_DEL_EXPLAIN'		=> 'Active/désactive les messages de délestages et purges automatiques dans les shoutbox.',
	'SHOUT_ROBOT_EDIT'				=> 'Édition des messages',
	'SHOUT_ROBOT_EDIT_EXPLAIN'		=> 'Activer les Infos d’édition des messages dans la shoutbox principale.',
	'SHOUT_ROBOT_EDIT_PRIV'			=> 'Édition des messages shout privée',
	'SHOUT_ROBOT_EDIT_PRIV_EXPLAIN'	=> 'Activer les Infos d’édition des messages dans la shoutbox privée.',
	'SHOUT_ROBOT_EXCLU'				=> 'Forums exclus',
	'SHOUT_ROBOT_EXCLU_EXPLAIN'		=> 'sélectionnez les forums pour lesquels vous ne souhaitez pas rendre publique la parution des nouveaux sujets et messages.<br />=> Notez que les affichages des infos dépendent des droits de vues des forums.',
	'SHOUT_ROBOT_HELLO'				=> 'Robot de la date du jour',
	'SHOUT_ROBOT_HELLO_EXPLAIN'		=> 'Active/désactive la notification de la date du jour.',
	'SHOUT_ROBOT_HELLO_PRIV'		=> 'Robot de la date du jour shoutbox privée',
	'SHOUT_ROBOT_HELLO_PRIV_EXPLAIN'=> 'Active/désactive la notification de la date du jour dans la shoutbox privée',
	'SHOUT_ROBOT_MESSAGE'			=> 'Robot des sujets',
	'SHOUT_ROBOT_MESSAGE_EXPLAIN'	=> 'Activer les Infos de nouveaux sujets dans la shoutbox principale.',
	'SHOUT_ROBOT_MES_PRIV'			=> 'Robot des sujets shout privée',
	'SHOUT_ROBOT_MES_PRIV_EXPLAIN'	=> 'Activer les Infos de nouveaux sujets dans la shoutbox privée.',
	'SHOUT_ROBOT_NEWEST'			=> 'Robot des nouvelles inscriptions',
	'SHOUT_ROBOT_NEWEST_EXPLAIN'	=> 'Active/désactive la notification des nouvelles inscriptions d’utilisateurs sur ce forum, dans la shoutbox générale.',
	'SHOUT_ROBOT_NEWEST_PRIV'		=> 'Robot des nouvelles inscriptions shoutbox privée',
	'SHOUT_ROBOT_NEWEST_PRIV_EXPLAIN'=> 'Active/désactive la notification des nouvelles inscriptions d’utilisateurs sur ce forum, dans la shoutbox privée.',
	'SHOUT_ROBOT_PREZ'				=> 'Forum de présentation',
	'SHOUT_ROBOT_PREZ_EXPLAIN'		=> 'Choisissez le forum de présentation des membres si vous en avez un.<br />Le Robot diffusera des infos adaptées.',
	'SHOUT_ROBOT_REP'				=> 'Réponses aux sujets',
	'SHOUT_ROBOT_REP_EXPLAIN'		=> 'Activer les Infos de réponses aux sujets dans la shoutbox principale.',
	'SHOUT_ROBOT_REP_PRIV'			=> 'Réponses aux sujets shout privée',
	'SHOUT_ROBOT_REP_PRIV_EXPLAIN'	=> 'Activer les Infos de réponses aux sujets dans la shoutbox privée.',
	'SHOUT_ROBOT_SESSION'			=> 'Robot des sessions',
	'SHOUT_ROBOT_SESSION_EXPLAIN'	=> 'Active/désactive le message de bienvenue à chaque utilisateurs quand ils se connectent sur le forum.',
	'SHOUT_ROBOT_SESSION_PRIV'		=> 'Robot des sessions dans la shoutbox privée',
	'SHOUT_ROBOT_SESSION_PRIV_EXPLAIN'=> 'Active/désactive le message de bienvenue à chaque utilisateurs quand ils se connectent sur le forum dans la shoutbox privée.',
	'SHOUT_ROBOT_SESSION_R'			=> 'Robot des sessions des robots',
	'SHOUT_ROBOT_SESSION_R_EXPLAIN'	=> 'Active/désactive la notification de connection des robots quand ils se connectent au forum.',
	'SHOUT_ROBOT_SESSION_R_PRIV'	=> 'Robot des sessions des robots shoutbox privée',
	'SHOUT_ROBOT_SESSION_R_PRIV_EXPLAIN'=> 'Active/désactive les notifications dans la shoutbox privée, de connexions des robots quand ils se connectent au forum.',
	'SHOUT_ROBOT_TIME'				=> 'Temps entre deux connexions',
	'SHOUT_ROBOT_TIME_EXPLAIN'		=> 'Ce réglage vous permet de définir l’intervalle de temps entre deux connexions des utilisateurs sans que le robot ne le signale à nouveau.',
	'SHOUT_ROBOT_NEW_VIDEO'			=> 'Nouvelle vidéo',
	'SHOUT_ROBOT_NEW_VIDEO_EXPLAIN'	=> 'Diffuse un message si une nouvelle vidéo est publiée',

	'SHOUT_ON_CONNECT'				=> 'Infos de connexions',
	'SHOUT_ON_SUBJET'				=> 'Nouveaux sujets',
	'SHOUT_ON_REPONSE'				=> 'Réponses aux sujets et éditions',
	'SHOUT_ON_BIRTHDAY'				=> 'Anniversaires',
	'SHOUT_ON_DAY'					=> 'Dates du jour',
	'SHOUT_ON_NEWS'					=> 'Nouvelles inscriptions',
	'SHOUT_ON_PRIV'					=> 'Connexions dans la shout privée',
	'SHOUT_PURGE_ON'				=> 'Purger les ',

// Installation
	'SHOUT_WELCOME'					=> 'Ceci est votre premier message. Bienvenue dans la Breizh Shoutbox… de la part de Sylver35… ',

// Add new formats
	'dateformats'	=> array_merge($lang['dateformats'], array(
		'|d M| H:i'					=> '[Jours relatifs], 13:37 / 01 janv. 13:37',
		'|M jS| g:i a'				=> '[Jours relatifs], 1:37 pm / janv. 1er 1:37 pm',
		'H:i'						=> '13:37',
		'H:i a'						=> '1:37 pm'
	)),
));
