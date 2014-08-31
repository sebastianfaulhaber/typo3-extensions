<?php

########################################################################
# Extension Manager/Repository config file for ext "cwt_community".
#
# Auto generated 02-07-2012 17:39
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'CWT Community',
	'description' => 'This extension provides a wide range of community features for frontend users. It mainly consists of the following parts:
Userlist, Profile, Profile Administration, Guestbook, Messages, Buddylist, Backend User Administration.',
	'category' => 'plugin',
	'author' => 'Sebastian Faulhaber',
	'author_email' => 'sebastian.faulhaber@gmx.de',
	'shy' => '',
	'version' => '2.1.1',
	'dependencies' => 'cwt_community_user,cwt_feedit,smarty',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'mod1',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 1,
	'createDirs' => 'uploads/tx_cwtcommunity,uploads/tx_cwtcommunity/icons,uploads/tx_cwtcommunity/gallery',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.5.0-6.0.4',
			'cwt_community_user' => 'cwt_community_user',
			'cwt_feedit' => 'cwt_feedit',
			'smarty' => 'smarty',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:112:{s:9:"ChangeLog";s:4:"d0f3";s:10:"README.txt";s:4:"ddfd";s:12:"ext_icon.gif";s:4:"d156";s:17:"ext_localconf.php";s:4:"e948";s:14:"ext_tables.php";s:4:"5c17";s:14:"ext_tables.sql";s:4:"a64b";s:28:"ext_typoscript_constants.txt";s:4:"1f2a";s:28:"ext_typoscript_editorcfg.txt";s:4:"60aa";s:24:"ext_typoscript_setup.txt";s:4:"1fa4";s:15:"flexform_ds.xml";s:4:"282d";s:34:"icon_tx_cwtcommunity_buddylist.gif";s:4:"e0f2";s:43:"icon_tx_cwtcommunity_buddylist_approval.gif";s:4:"e0f2";s:34:"icon_tx_cwtcommunity_guestbook.gif";s:4:"320d";s:39:"icon_tx_cwtcommunity_guestbook_data.gif";s:4:"320d";s:30:"icon_tx_cwtcommunity_icons.gif";s:4:"a765";s:32:"icon_tx_cwtcommunity_message.gif";s:4:"774d";s:37:"icon_tx_cwtcommunity_profileviews.gif";s:4:"a765";s:16:"locallang_db.xml";s:4:"ae44";s:7:"tca.php";s:4:"a073";s:14:"doc/manual.sxw";s:4:"6a8a";s:19:"doc/wizard_form.dat";s:4:"dd55";s:20:"doc/wizard_form.html";s:4:"b735";s:22:"doc/wizard_form.html.1";s:4:"1d8c";s:22:"mod1/action_delete.gif";s:4:"90c6";s:23:"mod1/action_disable.gif";s:4:"fba8";s:20:"mod1/action_edit.gif";s:4:"3248";s:22:"mod1/action_enable.gif";s:4:"fde9";s:20:"mod1/action_view.gif";s:4:"1b1d";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"8837";s:14:"mod1/index.php";s:4:"5991";s:18:"mod1/locallang.xml";s:4:"eda1";s:22:"mod1/locallang_mod.xml";s:4:"70f1";s:19:"mod1/moduleicon.gif";s:4:"d156";s:18:"pi1/arrow_down.gif";s:4:"61db";s:16:"pi1/arrow_up.gif";s:4:"a421";s:33:"pi1/class.tx_cwtcommunity_pi1.php";s:4:"1395";s:24:"pi1/guestbook_delete.gif";s:4:"4048";s:13:"pi1/icons.png";s:4:"c5c0";s:17:"pi1/locallang.xml";s:4:"ba57";s:23:"pi1/messages_answer.gif";s:4:"9a23";s:20:"pi1/messages_new.gif";s:4:"4ac7";s:21:"pi1/messages_read.gif";s:4:"fae4";s:23:"pi1/messages_unread.gif";s:4:"9081";s:21:"pi1/no_pic_female.png";s:4:"2da5";s:19:"pi1/no_pic_male.png";s:4:"2da5";s:34:"pi1/tx_cwtcommunity_pi1_abuse.html";s:4:"65ae";s:34:"pi1/tx_cwtcommunity_pi1_album.html";s:4:"84d9";s:40:"pi1/tx_cwtcommunity_pi1_album_admin.html";s:4:"d0fb";s:41:"pi1/tx_cwtcommunity_pi1_album_detail.html";s:4:"83e4";s:39:"pi1/tx_cwtcommunity_pi1_buddyadmin.html";s:4:"92eb";s:38:"pi1/tx_cwtcommunity_pi1_buddylist.html";s:4:"540c";s:38:"pi1/tx_cwtcommunity_pi1_guestbook.html";s:4:"d3e0";s:37:"pi1/tx_cwtcommunity_pi1_messages.html";s:4:"0538";s:40:"pi1/tx_cwtcommunity_pi1_photo_admin.html";s:4:"b780";s:41:"pi1/tx_cwtcommunity_pi1_photo_detail.html";s:4:"9fa7";s:36:"pi1/tx_cwtcommunity_pi1_profile.html";s:4:"4544";s:40:"pi1/tx_cwtcommunity_pi1_profile_edit.php";s:4:"1907";s:41:"pi1/tx_cwtcommunity_pi1_profile_mini.html";s:4:"55d0";s:35:"pi1/tx_cwtcommunity_pi1_search.html";s:4:"9908";s:37:"pi1/tx_cwtcommunity_pi1_userlist.html";s:4:"be20";s:38:"pi1/tx_cwtcommunity_pi1_userstats.html";s:4:"0894";s:36:"pi1/tx_cwtcommunity_pi1_welcome.html";s:4:"5b98";s:25:"pi1/userlist_addbuddy.gif";s:4:"6cf5";s:23:"pi1/userlist_female.gif";s:4:"a215";s:21:"pi1/userlist_male.gif";s:4:"b3b0";s:31:"pi1/userlist_status_offline.gif";s:4:"71e9";s:30:"pi1/userlist_status_online.gif";s:4:"381b";s:23:"pi1/welcome_newmail.png";s:4:"e40d";s:25:"pi1/welcome_nonewmail.png";s:4:"3961";s:39:"res/class.tx_cwtcommunity_lib_abuse.php";s:4:"5944";s:43:"res/class.tx_cwtcommunity_lib_buddylist.php";s:4:"0828";s:40:"res/class.tx_cwtcommunity_lib_common.php";s:4:"0ec6";s:43:"res/class.tx_cwtcommunity_lib_constants.php";s:4:"c20a";s:41:"res/class.tx_cwtcommunity_lib_gallery.php";s:4:"c26c";s:43:"res/class.tx_cwtcommunity_lib_guestbook.php";s:4:"0b0d";s:42:"res/class.tx_cwtcommunity_lib_messages.php";s:4:"db4d";s:41:"res/class.tx_cwtcommunity_lib_profile.php";s:4:"cd51";s:40:"res/class.tx_cwtcommunity_lib_search.php";s:4:"8df4";s:42:"res/class.tx_cwtcommunity_lib_userlist.php";s:4:"ecee";s:41:"res/class.tx_cwtcommunity_lib_welcome.php";s:4:"683f";s:20:"res/jquery/jquery.js";s:4:"f0c6";s:33:"res/jquery/theme/ui.accordion.css";s:4:"6e61";s:27:"res/jquery/theme/ui.all.css";s:4:"b7cd";s:34:"res/jquery/theme/ui.allplugins.css";s:4:"f5a3";s:28:"res/jquery/theme/ui.core.css";s:4:"6b03";s:34:"res/jquery/theme/ui.datepicker.css";s:4:"8da1";s:30:"res/jquery/theme/ui.dialog.css";s:4:"ab12";s:35:"res/jquery/theme/ui.progressbar.css";s:4:"2391";s:33:"res/jquery/theme/ui.resizable.css";s:4:"be08";s:30:"res/jquery/theme/ui.slider.css";s:4:"f254";s:28:"res/jquery/theme/ui.tabs.css";s:4:"c33c";s:29:"res/jquery/theme/ui.theme.css";s:4:"13d5";s:54:"res/jquery/theme/images/ui-bg_flat_0_aaaaaa_40x100.png";s:4:"2a44";s:55:"res/jquery/theme/images/ui-bg_flat_55_fbec88_40x100.png";s:4:"2b88";s:55:"res/jquery/theme/images/ui-bg_glass_75_d0e5f5_1x400.png";s:4:"f5d2";s:55:"res/jquery/theme/images/ui-bg_glass_85_dfeffc_1x400.png";s:4:"e471";s:55:"res/jquery/theme/images/ui-bg_glass_95_fef1ec_1x400.png";s:4:"5a3b";s:62:"res/jquery/theme/images/ui-bg_gloss-wave_55_5c9ccc_500x100.png";s:4:"527d";s:61:"res/jquery/theme/images/ui-bg_inset-hard_100_f5f8f9_1x100.png";s:4:"4ebb";s:61:"res/jquery/theme/images/ui-bg_inset-hard_100_fcfdfd_1x100.png";s:4:"2b6a";s:51:"res/jquery/theme/images/ui-icons_217bc0_256x240.png";s:4:"8bea";s:51:"res/jquery/theme/images/ui-icons_2e83ff_256x240.png";s:4:"a412";s:51:"res/jquery/theme/images/ui-icons_469bdd_256x240.png";s:4:"e0c0";s:51:"res/jquery/theme/images/ui-icons_6da8d5_256x240.png";s:4:"7cbd";s:51:"res/jquery/theme/images/ui-icons_cd0a0a_256x240.png";s:4:"85eb";s:51:"res/jquery/theme/images/ui-icons_d8e7f3_256x240.png";s:4:"b781";s:51:"res/jquery/theme/images/ui-icons_f9bd01_256x240.png";s:4:"bc97";s:24:"res/jquery/ui/ui.core.js";s:4:"637a";s:26:"res/jquery/ui/ui.dialog.js";s:4:"8720";s:29:"res/jquery/ui/ui.draggable.js";s:4:"05a0";s:29:"res/jquery/ui/ui.resizable.js";s:4:"7dd7";}',
	'suggests' => array(
	),
);

?>