<?php
/** 
*
* Breizh Shoutbox Extension [French]
*
* @package language
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
	'SHOUT_DIRECTION'		=> 'left', // lang direction left (ltr) or right (rtl)
	'SHOUT_START'			=> 'Shoutbox',
	'SHOUT_LOADING'			=> 'Chargement…',
	'SHOUT_MESSAGE'			=> 'Message',
	'SHOUT_MESSAGES'		=> 'Messages',
	'SHOUT_AUTO'			=> 'Entrez un message…',
	'POST_MESSAGE'			=> 'Poster',
	'POST_MESSAGE_ALT'		=> 'Poster un message',
	'SENDING' 				=> 'Envoi du message…',
	'MESSAGE_EMPTY'			=> 'Le message est vide.',
	'SHOUT_TOO_BIG'			=> 'Votre message est trop long, nombre de caractères : ',
	'SHOUT_TOO_BIG2'		=> 'Le maximum autorisé est de : ',
	'SHOUT_OUT_TIME'		=> 'temps d’inactivité dépassé, mise en veille automatique…',
	'SHOUT_NO_MESSAGE'		=> 'Il n’y a aucun message.',
	'NO_AJAX_USER'			=> 'Vous devez activer Javascript pour pouvoir visionner la Shoutbox.',
	'NO_SHOUT_ID'			=> 'Pas d’id de message.',
	'JS_ERR'				=> 'Il y a eu une erreur JavaScript. Erreur : ',
	'LINE'					=> 'Ligne',
	'FILE'					=> 'Fichier',
	'FLOOD_ERROR'			=> 'Erreur de flood !',
	'POSTED'				=> 'Message posté…',
	'SHOUT_NO_CODE'			=> 'Le bbcode suivant : %s n’est pas accepté.',
	'SHOUT_NO_VIDEO'		=> 'Il n’est pas permis de poster des vidéos dans la shoutbox',
	'SHOUT_NO_SCRIPT'		=> 'Les scripts ne sont pas tolérés dans cette shoutbox !  Veuillez noter que cette tentative a été enregistrée.',
	'SHOUT_NO_APPLET'		=> 'Les applets ne sont pas tolérés dans cette shoutbox !  Veuillez noter que cette tentative a été enregistrée.',
	'SHOUT_NO_ACTIVEX'		=> 'Les objets active x ne sont pas tolérés dans cette shoutbox !  Veuillez noter que cette tentative a été enregistrée.',
	'SHOUT_NO_OBJECTS'		=> 'Les objets chrome et about ne sont pas tolérés dans cette shoutbox !  Veuillez noter que cette tentative a été enregistrée.',
	'SHOUT_NO_IFRAME'		=> 'Les iframes ne sont pas tolérés dans cette shoutbox !  Veuillez noter que cette tentative a été enregistrée.',
	'SHOUT_DEL'				=> 'Supprimer le message',
	'DEL_SHOUT'				=> 'Êtes vous sûr de vouloir supprimer ce message ?',
	'MSG_DEL_DONE'			=> 'Message en cours de suppression…',
	'NO_SHOUT_ID'			=> 'Pas de numéro id de message.',
	'SHOUT_PAGE'			=> 'Page N° ',
	'CODE'					=> 'code',
	'EDIT'					=> 'Éditer',
	'CANCEL'				=> 'Abandonner',
	'COLORS'				=> 'Couleurs',
	'SHOUT_IP'				=> 'Voir l’ip du posteur',
	'SHOUT_EDIT'			=> 'Éditer le message',
	'SENDING_EDIT'			=> 'Publication après édition…',
	'EDIT_DONE'				=> 'Le message a été édité',
	'ONLY_ONE_OPEN'			=> 'Vous ne pouvez avoir qu’une seule boite d’édition ouverte',
	'SHOUT_AVATAR_SHORT'	=> 'Avatar',
	'SHOUT_AVATAR_TITLE'	=> 'Avatar de %s',
	'SHOUT_AVATAR_NONE'		=> '%s n’a pas d’avatar',
	'SHOUT_COLOR'			=> 'Coloriser le texte',
	'SHOUT_COLOR_CLOSE'		=> 'Refermer la colorisation du texte',
	'SHOUT_CHARS'			=> 'Ajouter des caractères spéciaux',
	'SHOUT_CHARS_CLOSE'		=> 'Refermer le panneau des caractères spéciaux',
	'SHOUT_BBCODES'			=> 'Ouvrir le panneau des bbcodes',
	'SHOUT_BBCODES_CLOSE'	=> 'Refermer le panneau des bbcodes',
	'SMILIES'				=> 'Inclure des Smileys',
	'SMILIES_CLOSE'			=> 'Refermer le panneau des Smileys',
	'SHOUT_MORE_SMILIES'	=> 'Plus de smileys',
	'SHOUT_MORE_SMILIES_ALT' => 'Cliquez ici pour voir plus de smileys',
	'SHOUT_LESS_SMILIES'	=> 'Moins de smileys',
	'SHOUT_LESS_SMILIES_ALT' => 'Cliquez ici pour voir moins de smileys',
	'SHOUT_POST_IP'			=> 'IP de l’utilisateur :',
	'SHOUTBOX'				=> '<a href="%1$s">%2$s</a>', // Don't traduct this
	'SHOUTBOX_VER'			=> 'Breizh Shoutbox v%s', // Don't traduct this
	'SHOUTBOX_VER_ALT'		=> 'Breizh Shoutbox v%s © 2018-2020', // Don't traduct this
	'SHOUT_TOUCH'			=> 'The Breizh Touch', // Don't traduct this
	'SHOUTBOX_POPUP'		=> 'Popup Breizh Shoutbox',
	'SHOUT_POP'				=> 'Ouvrir la shoutbox dans une popup',
	'SHOUT_POPUP'			=> 'Popup Shoutbox',
	'SHOUT_RULES'			=> 'Règles d’utilisation de la shoutbox',
	'SHOUT_RULES_PRIV'		=> 'Règles d’utilisation de la shoutbox Privée',
	'SHOUT_RULES_CLOSE'		=> 'Refermer le panneau des règles d’utilisation',
	'SHOUTBOX_SECRET'		=> 'Shoutbox Privée',
	'SHOUT_PRIV'			=> 'Entrer dans la shoutbox privée',
	'SHOUT_PURGE'			=> 'Purger la shout',
	'SHOUT_PURGE_ALT'		=> 'Purger totalement la shoutbox',
	'SHOUT_PURGE_BOX'		=> ' Souhaitez vous réellement purger totalement la shoutbox ?  Attention, cette action est irréversible…',
	'PURGE_PROCESS'			=> 'Purge de la shoutbox en cours…',
	'SHOUT_PURGE_ROBOT_ALT'	=> 'Purger la shoutbox des infos robot',
	'SHOUT_PURGE_ROBOT_BOX'	=> ' Souhaitez vous réellement purger la shoutbox des infos robot ?  Attention, cette action est irréversible…',
	'SERVER_ERR'			=> 'Quelque chose s’est mal déroulé après avoir envoyé une requête au serveur, veuillez rafraichir la page…',
	'SHOUT_ERROR'			=> 'Erreur : ',
	'SHOUT_IMG_POST_ERROR'	=> 'Erreur : pour insérer une image, vous devez cliquer sur l’icône image…',
	'SHOUT_IMG_DIM_ERROR'	=> 'Erreur : l’image envoyée est corrompue ou n’est pas une image…',
	'SHOUT_IMG_FOPEN_ERROR'	=> 'Erreur : impossible de contacter le serveur hébergeant l’image…',
	'SHOUT_PROCESS_IMG'		=> 'Vérifications de l’image en cours…',

// User panel
	'SHOUT_CONFIG_URL'		=> 'Config Shoutbox',
	'SHOUT_PRINCIPAL'		=> 'shoutbox principale',
	'SHOUT_PRIVATE'			=> 'shoutbox privée',
	'DISPLAY_SOUND_CHOICE'	=> 'Vous pouvez choisir d’activer ou désactiver le son lors de l’arrivée de nouveaux messages',
	'SOUND_OR_NOT'			=> 'Choisissez le réglage qui vous convient',
	'CHOOSE_NEW_SOUND'		=> 'Choisissez le son qui sera diffusé lors des nouveaux messages',
	'CHOOSE_ERROR_SOUND'	=> 'Choisissez le son qui sera diffusé lors des erreurs',
	'CHOOSE_DEL_SOUND'		=> 'Choisissez le son qui sera diffusé lors des suppressions de messages',
	'CHOOSE_ADD_SOUND'		=> 'Choisissez le son qui sera diffusé lors des ajouts de messages',
	'CHOOSE_EDIT_SOUND'		=> 'Choisissez le son qui sera diffusé lors des éditions de messages',
	'CHOOSE_NEW_YES'		=> 'Le son choisi sera diffusé lors des nouveaux messages',
	'CHOOSE_ERROR_YES'		=> 'Le son choisi sera diffusé lors des erreurs',
	'CHOOSE_DELETE_YES'		=> 'Le son choisi sera diffusé lors des suppressions de messages',
	'CHOOSE_ADD_YES'		=> 'Le son choisi sera diffusé lors des ajouts de messages',
	'CHOOSE_EDIT_YES'		=> 'Le son choisi sera diffusé lors des éditions de messages',
	'CHOOSE_POSITIONS'		=> 'Positions de la shoutbox',
	'CHOOSE_NEW_NO'			=> 'Aucun son ne sera diffusé lors des nouveaux messages',
	'SHOUT_SOUND_YES'		=> 'Activation du son',
	'SHOUT_SOUND_NO'		=> 'Désactivation du son',
	'SHOUT_SOUND_ECOUTE'	=> 'Écouter le son',
	'SHOUT_CONFIG_OPEN'		=> 'Ouvrir le panneau des préférences de la shoutbox',
	'SHOUT_CONFIG_OPEN_TO'	=> 'Modifier les préférences de la shoutbox',
	'SHOUT_PANEL_USER'		=> 'Panneau des réglages utilisateur',
	'SHOUT_PANEL_TO_USER'	=> 'Panneau des réglages pour l’utilisateur %1$s',
	'SHOUT_PREF_UPDATED'	=> 'Vos préférences pour la shoutbox sont sauvegardées',
	'RETURN_SHOUT_PREF'		=> '%s« Retourner au panneau des préférences%s',
	'SHOUT_DEF_VAL'			=> 'Valeurs par défaut',
	'SHOUT_DEF_VAL_EXPLAIN'	=> 'Revenir aux valeurs par défaut du forum',
	'SHOUT_ANY'				=> 'Aucun son',
	'CHOOSE_ERROR_NO'		=> 'Aucun son ne sera diffusé lors des erreurs',
	'CHOOSE_DELETE_NO'		=> 'Aucun son ne sera diffusé lors des suppressions de messages',
	'CHOOSE_ADD_NO'			=> 'Aucun son ne sera diffusé lors des ajouts de messages',
	'CHOOSE_EDIT_NO'		=> 'Aucun son ne sera diffusé lors des éditions de messages',
	'SHOUT_FLOAT_RIGHT'		=> 'à droite',
	'SHOUT_FLOAT_LEFT'		=> 'à gauche',

// No permission errors
	'NO_POST_GUEST'			=> 'Les invités peuvent poster.',
	'NO_ACTION_PERM'		=> 'Vous n’êtes pas autorisé à effectuer cette action',
	'NO_ADMIN_PERM'			=> 'Pas de permission administrateur trouvée…',
	'NO_EDIT_PERM'			=> 'Vous ne pouvez pas éditer ce message…',
	'NO_DELETE_PERM'		=> 'Vous n’êtes pas autorisé à supprimer des messages…',
	'NO_DELETE_PERM_S'		=> 'Vous n’êtes pas autorisé à supprimer vos propres messages…',
	'NO_DELETE_PERM_T'		=> 'Vous n’êtes pas autorisé à supprimer les messages des autres utilisateurs…',
	'NO_POST_PERM'			=> 'Vous n’êtes pas autorisé à poster des messages…',
	'NO_PURGE_PERM'			=> 'Vous n’êtes pas autorisé à purger la shoutbox…',
	'NO_PURGE_ROBOT_PERM'	=> 'Vous n’êtes pas autorisé à purger les infos de la shoutbox…',
	'NO_SHOUT_BBCODE'		=> 'Vous n’êtes pas autorisé à utiliser les BBcodes…',
	'NO_SHOUT_CHARS'		=> 'Vous n’êtes pas autorisé à utiliser les caractères spéciaux…',
	'NO_SHOUT_COLOR'		=> 'Vous n’êtes pas autorisé à utiliser la colorisation du texte…',
	'NO_SHOUT_DEL'			=> 'Vous n’êtes pas autorisé à supprimer le message…',
	'NO_SHOUT_EDIT'			=> 'Vous n’êtes pas autorisé à éditer le message…',
	'NO_SHOUT_IMG'			=> 'Vous n’êtes pas autorisé à poster des images…',
	'NO_SHOUT_POP'			=> 'Vous n’êtes pas autorisé à utiliser la shoutbox dans une popup…',
	'NO_SHOW_IP_PERM'		=> 'Vous n’êtes pas autorisé à voir les ip des posteurs…',
	'NO_SMILIES'			=> 'Vous n’êtes pas autorisé à utiliser les Smileys…',
	'NO_SMILIE_PERM'		=> 'Vous n’êtes pas autorisé à poster des smileys…',
	'NO_VIEW_PERM'			=> 'Vous n’êtes pas autorisé à visionner la shoutbox…',
	'NO_VIEW_PRIV_PERM'		=> 'Vous n’êtes pas autorisé à visionner la shoutbox privée…',
	'NO_SHOUT_PERSO_PERM'	=> 'Vous n’êtes pas autorisé à modifier la mise en forme des messages des utilisateurs',

// Various panels
	'SHOUT_CLOSE'				=> 'Refermer',
	'SHOUT_DIV_CLOSE'			=> 'Refermer le panneau',
	'SHOUT_CLICK_SOUND_ON'		=> 'Activer les sons',
	'SHOUT_CLICK_SOUND_OFF'		=> 'Désactiver les sons',
	'SHOUT_CHOICE_NAME'			=> 'Choisir un nom d’utilisateur',
	'SHOUT_CHOICE_YES'			=> 'Nom d’utilisateur mis à jour',
	'SHOUT_CHOICE_NAME_ERROR'	=> 'Vous devez au préalable choisir un nom d’utilisateur.',
	'SHOUT_CLICK_HERE'			=> 'Cliquez ici pour vous connecter',
	'SHOUT_LOG_ME_IN'			=> 'Connexion auto',
	'SHOUT_HIDE_ME'				=> 'Session invisible',
	'PICK_COLOR'				=> 'Choisir une couleur en cliquant dans la zone',
	'PICK_BUTON'				=> 'Coloriser le texte',
	'SHOUT_CHOICE_COLOR'		=> 'Changer de palette',
	'SHOUT_PICOLOR'				=> 'Palette picolor',
	'SHOUT_PHPBBCOLOR'			=> 'Palette phpbb',
	'SHOUT_PHPBB2COLOR'			=> 'Palette phpbb élargie',
	'SHOUT_LATERAL'				=> 'Shoutbox en panneau latéral',
	'SHOUT_LATERAL_OPEN'		=> 'Ouvrir la Shoutbox en panneau latéral',
	'SHOUT_LATERAL_CLOSE'		=> 'Refermer le panneau latéral',
	'SHOUT_AFFICHE'				=> 'afficher le mot de passe',
	'SHOUT_CACHE'				=> 'cacher le mot de passe',

// Formatting messages panel
	'SHOUT_EXEMPLE'				=> 'Voici un exemple de texte mis en forme',
	'SHOUT_PERSO'				=> 'Personnaliser la mise en forme des messages',
	'SHOUT_PERSO_GO'			=> 'Mettre en forme',
	'SHOUT_BBCODE_OPEN'			=> 'BBcodes ouverture',
	'SHOUT_BBCODE_CLOSE'		=> 'BBcodes fermeture',
	'SHOUT_BBCODE_SUCCESS'		=> 'Modifications effectuées',
	'SHOUT_BBCODE_SUP'			=> 'Mise en forme supprimée',
	'SHOUT_BBCODE_ERROR'		=> 'Vous devez renseigner les deux champs',
	'SHOUT_BBCODE_ERROR_COUNT'	=> 'Vous devez avoir autant de bbcodes ouvrants que de bbcodes fermants',
	'SHOUT_BBCODE_ERROR_SHAME'	=> 'Aucune modification effectuée',
	'SHOUT_BBCODE_ERROR_SLASH'	=> 'Erreur, le bbcode de fermeture “%s” ne comporte pas de slash de fermeture “/”',
	'SHOUT_BBCODE_ERROR_SLASHS'	=> 'Erreur, les %1$s bbcodes de fermeture “%2$s” ne comportent pas de slash de fermeture “/”',
	'SHOUT_BBCODE_ERROR_IMB'	=> 'Erreur, le bbcode de fermeture “%2$s” est mal imbriqué',
	'SHOUT_BBCODE_ERROR_IMBS'	=> 'Erreur, les %1$s bbcodes de fermeture “%2$s” sont mal imbriqués',
	'SHOUT_DIV_BBCODE_CLOSE'	=> 'Refermer le panneau de mise en forme des messages',
	'SHOUT_DIV_BBCODE_EXPLAIN'	=> 'Vous pouvez personnaliser la mise en forme de vos messages dans la shoutbox.<br />Entrez des bbcodes simples, les ouvertures dans la première zone, les fermetures dans la seconde.<br />Attention : respectez bien l’imbrication des bbcodes et n’oubliez pas de bien tous les fermer.<br />Exemple : <em>[b][i] et [/i][/b]</em>',

// User actions panel
	'SHOUT_ACTION_TITLE'			=> 'Actions pour l’utilisateur',
	'SHOUT_ACTION_TITLE_TO'			=> 'Actions pour l’utilisateur %s',
	'SHOUT_ACTION_PROFIL'			=> 'Voir le profil de %s',
	'SHOUT_ACTION_CITE'				=> 'Citer l’utilisateur',
	'SHOUT_ACTION_CITE_M'			=> 'Multi citer l’utilisateur',
	'SHOUT_ACTION_CITE_ON'			=> 'Pour ',
	'SHOUT_ACTION_CITE_EXPLAIN'		=> 'Citer l’utilisateur dans un message de la shoutbox',
	'SHOUT_ACTION_CITE_M_EXPLAIN'	=> 'Multi citer l’utilisateur dans un message de la shoutbox',
	'SHOUT_ACTION_MSG'				=> 'Envoyer un message personnel dans la shoutbox',
	'SHOUT_ACTION_MSG_ROBOT'		=> 'Envoyer un message en tant que %s',
	'SHOUT_ACTION_DELETE'			=> 'Supprimer mes messages personnels',
	'SHOUT_ACTION_DELETE_EXPLAIN'	=> 'Êtes vous sûr de vouloir supprimer tous vos messages personnels ?',
	'SHOUT_ACTION_DEL_TO'			=> 'Supprimer les messages personnels qui me sont destinés',
	'SHOUT_ACTION_DEL_TO_EXPLAIN'	=> 'Êtes vous sûr de vouloir supprimer tous les messages personnels qui vous sont destinés ?',
	'SHOUT_ACTION_DEL_REP'			=> 'Tous vos messages personnels ont bien été supprimés :',
	'SHOUT_ACTION_DEL_NO'			=> 'Aucun message personnel supprimé',
	'SHOUT_ACTION_MCP'				=> 'Fiche de suivi',
	'SHOUT_ACTION_BAN'				=> 'Bannir du forum',
	'SHOUT_ACTION_AUTH'				=> 'Permissions shoutbox',
	'SHOUT_ACTION_REMOVE'			=> 'Supprimer tous les messages shoutbox de l’utilisateur',
	'SHOUT_ACTION_REMOVE_EXPLAIN'	=> 'Êtes vous sûr de vouloir supprimer tous les messages shoutbox et infos Robot de cet utilisateur ?',
	'SHOUT_ACTION_REMOVE_REP'		=> 'Tous les messages shoutbox de cet utilisateur ont bien été supprimés :',
	'SHOUT_ACTION_REMOVE_NO'		=> 'Aucun message supprimé',
	'SHOUT_ACTION_ADMIN'			=> 'Administrer l’utilisateur',
	'SHOUT_ACTION_PERSO'			=> 'Modifier la mise en forme des messages de l’utilisateur',
	'SHOUT_USER_POST'				=> '@MP_', // Before a private message
	'SHOUT_USER_IGNORE'				=> 'Vous avez défini ce membre en ignoré',
	'SHOUT_USER_NONE'				=> 'Aucune action possible pour ce membre',

// Infos cookies
	'SHOUT_COOKIES'					=> 'Informations concernant les cookies de la shoutbox',
	'SHOUT_COOKIES_INFO'			=> 'Cette shoutbox utilise %s cookies pour pouvoir bien fonctionner',
	'SHOUT_COOKIES_ROBOT'			=> 'Permet de faire afficher ou non les infos robot',
	'SHOUT_COOKIES_NAME'			=> 'Permet de conserver le nom d’utilisateur en invité',
	'SHOUT_COOKIES_SOUND'			=> 'Permet de choisir l’activation ou la désactivation du son en invité',

// Permissions panel
	'SHOUT_OPTION_YES'				=> 'Active : <span class="%2$s">“%1$s”</span>',
	'SHOUT_OPTION_NO'				=> 'Inactive : <span class="%2$s">“%1$s”</span>',
	'SHOUT_OPTION_USER'				=> 'Traçage des permissions shoutbox de %1$s',

// Members connected panel
	'SHOUT_ONLINE_TITLE'		=> 'Membres connectés en temps réel',
	'SHOUT_ONLINE'				=> 'Ouvrir le panneau des membres connectés',
	'SHOUT_ONLINE_CLOSE'		=> 'Fermer le panneau des membres connectés',

// Post infos Robot
	'SHOUT_ROBOT_ON'			=> 'Désactiver les infos Robot',
	'SHOUT_ROBOT_OFF'			=> 'Afficher les infos Robot',
	'SHOUT_SELECT_ROBOT'		=> 'Désactiver la publication par le robot de la shoutbox',
	'SHOUT_ROBOT_START'			=> 'Info :', // Au début des infos robot
	'SHOUT_ROBOT_DATE'			=> 'l j F Y', // Forme de la date du jour

// Robot info messages
	'SHOUT_GLOBAL_ROBOT'		=> '%1$s %2$s vient de publier une annonce générale : %3$s',
	'SHOUT_ANNOU_ROBOT'			=> '%1$s %2$s vient de publier une annonce : %3$s',
	'SHOUT_POST_ROBOT'			=> '%1$s %2$s vient de publier un nouveau sujet : %3$s',
	'SHOUT_REPLY_ROBOT'			=> '%1$s %2$s vient de répondre à un sujet : %3$s',
	'SHOUT_EDIT_ROBOT'			=> '%1$s %2$s vient d’éditer un message : %3$s',
	'SHOUT_TOPIC_ROBOT'			=> '%1$s %2$s vient d’éditer un sujet : %3$s',
	'SHOUT_LAST_ROBOT'			=> '%1$s %2$s vient d’éditer le dernier message du sujet : %3$s',
	'SHOUT_QUOTE_ROBOT'			=> '%1$s %2$s vient de répondre à un sujet en citant : %3$s',
	'SHOUT_PREZ_ROBOT'			=> '%1$s %2$s vient de publier sa présentation : %3$s',
	'SHOUT_PREZ_F_ROBOT'		=> '%1$s %2$s vient d’éditer une présentation : %3$s',
	'SHOUT_PREZ_FS_ROBOT'		=> '%1$s %2$s vient d’éditer sa présentation : %3$s',
	'SHOUT_PREZ_E_ROBOT'		=> '%1$s %2$s vient d’éditer un message dans une présentation : %3$s',
	'SHOUT_PREZ_ES_ROBOT'		=> '%1$s %2$s vient d’éditer un message dans sa présentation : %3$s',
	'SHOUT_PREZ_L_ROBOT'		=> '%1$s %2$s vient d’éditer le dernier message dans une présentation : %3$s',
	'SHOUT_PREZ_LS_ROBOT'		=> '%1$s %2$s vient d’éditer le dernier message dans sa présentation : %3$s',
	'SHOUT_PREZ_Q_ROBOT'		=> '%1$s %2$s vient de répondre dans une présentation en citant : %3$s',
	'SHOUT_PREZ_R_ROBOT'		=> '%1$s %2$s vient de répondre dans une présentation : %3$s',
	'SHOUT_PREZ_RS_ROBOT'		=> '%1$s %2$s vient de répondre dans sa présentation : %3$s',
	'SHOUT_ENTER_PRIV'			=> '%1$s %2$s vient d’entrer dans la shoutbox privée',
	'SHOUT_PURGE_SHOUT'			=> '%s Purge de la shoutbox effectuée…',
	'SHOUT_PURGE_PRIV'			=> '%s Purge de la shoutbox privée effectuée…',
	'SHOUT_PURGE_ROBOT'			=> '%s Purge des infos Robot effectuée…',
	'SHOUT_PURGE_AUTO'			=> '%s Purge automatique de %s messages de la shoutbox effectuée…',
	'SHOUT_PURGE_PRIV_AUTO'		=> '%s Purge automatique de %s messages de la shoutbox privée effectuée…',
	'SHOUT_DELETE_AUTO'			=> '%s Délestage automatique de %s messages de la shoutbox effectué…',
	'SHOUT_DELETE_PRIV_AUTO'	=> '%s Délestage automatique de %s messages de la shoutbox privée effectué…',
	'SHOUT_BIRTHDAY_ROBOT'		=> 'Toute l’équipe de %1$s souhaite un Joyeux anniversaire à %2$s',
	'SHOUT_BIRTHDAY_ROBOT_FULL'	=> 'Toute l’équipe de %1$s souhaite un Joyeux anniversaire à %2$s pour ses %3$s %4$s ans !',
	'SHOUT_HELLO_ROBOT'			=> 'Bonjour, nous sommes le %1$s %2$s',
	'SHOUT_NEWEST_ROBOT'		=> 'Un nouveau membre vient de s’enregistrer : %1$s, toute l’équipe de %2$s lui souhaite la bienvenue…',
	'SHOUT_SESSION_ROBOT'		=> 'Bonjour %s et bienvenue sur le forum…',
	'SHOUT_SESSION_ROBOT_BOT'	=> '%1$s %2$s vient de se connecter sur le forum…',

	'SHOUT_VIDEO'					=> 'Galerie vidéos',
	'SHOUT_NEW_VIDEO'				=> 'Nouvelle vidéo : %1$s dans : %2$s',

	'RELAXARCADE'					=> 'Relax-Arcade',
	'SHOUT_NEW_SCORE_RA'			=> 'Nouveau score',
	'SHOUT_NEW_SCORE_RA_TXT'		=> 'je viens d’établir le tout premier score de %s points à %s',
	'SHOUT_NEW_RECORD_RA'			=> 'Nouveau record',
	'SHOUT_NEW_RECORD_RA_TXT'		=> 'je viens d’établir un nouveau record de %s points à %s',
	'SHOUT_NEW_URECORD_RA'			=> 'Nouveau record ultime',
	'SHOUT_NEW_URECORD_RA_TXT'		=> 'je viens d’établir un nouveau record ultime de %s points à %s',
	'SHOUT_NEW_SCORE_RA_EXPLAIN'	=> 'Diffuse un message si un tout premier score est établi.',
	'SHOUT_NEW_RECORD_RA_EXPLAIN'	=> 'Diffuse un message si un nouveau record est établi.',
	'SHOUT_NEW_URECORD_RA_EXPLAIN'	=> 'Diffuse un message si un nouveau record ultime est établi.',
));
