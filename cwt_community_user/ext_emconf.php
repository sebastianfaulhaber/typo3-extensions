<?php

########################################################################
# Extension Manager/Repository config file for ext: "cwt_community_user"
#
# Auto generated 17-07-2009 20:02
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'CWT Community User',
	'description' => 'This extension is needed for the CWT Community extension. It actually extends the fe_users table by some attributes.',
	'category' => 'misc',
	'author' => 'Sebastian Faulhaber',
	'author_email' => 'sebastian.faulhaber@gmx.de',
	'author_company' => '',
	'shy' => '',
	'dependencies' => 'cms',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => 'fe_users',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '3.0.1',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.2.0-6.2.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:9:{s:9:"ChangeLog";s:4:"4072";s:10:"README.txt";s:4:"dd4e";s:12:"ext_icon.gif";s:4:"d156";s:14:"ext_tables.php";s:4:"a2b9";s:14:"ext_tables.sql";s:4:"a781";s:16:"locallang_db.xml";s:4:"da20";s:19:"doc/wizard_form.dat";s:4:"26a0";s:20:"doc/wizard_form.html";s:4:"a3c8";s:22:"doc/wizard_form.html.1";s:4:"3924";}',
	'suggests' => array(
	),
);

?>