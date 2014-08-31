<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE == 'BE') {
    t3lib_extMgm::addModulePath('web_txcwtcommunityM1', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');
        
    t3lib_extMgm::addModule('web', 'txcwtcommunityM1', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');
}

$TCA["tx_cwtcommunity_message"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_message',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cwtcommunity_message.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, fe_group, fe_users_uid, subject, body, status",
	)
);

$TCA["tx_cwtcommunity_buddylist"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_buddylist',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cwtcommunity_buddylist.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, fe_group, fe_users_uid, buddy_uid",
	)
);

$TCA["tx_cwtcommunity_buddylist_approval"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_buddylist_approval',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cwtcommunity_buddylist_approval.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, fe_group, requestor_uid, target_uid",
	)
);

$TCA["tx_cwtcommunity_guestbook"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_guestbook',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cwtcommunity_guestbook.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, fe_group, fe_users_uid, status",
	)
);

$TCA["tx_cwtcommunity_guestbook_data"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_guestbook_data',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cwtcommunity_guestbook_data.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, fe_group, guestbook_uid, text",
	)
);

$TCA["tx_cwtcommunity_icons"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_icons',		
		'label'     => 'string',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
        ),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cwtcommunity_icons.gif',
    ),
	"feInterface" => array (
        "fe_admin_fieldList" => "hidden, string, icon",
    )
);

$TCA["tx_cwtcommunity_albums"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_albums',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY cruser_id",
		'delete' => 'deleted',			
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cwtcommunity_albums.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, fe_group, title, description, preview_photo_uid, access_policy",
	)
);

$TCA["tx_cwtcommunity_photos"] = array (
    "ctrl" => array (
        'title'     => 'LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_photos',        
        'label'     => 'filename',    
        'tstamp'    => 'tstamp',
        'crdate'    => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => "ORDER BY crdate",    
        'delete' => 'deleted',    
        'enablecolumns' => array (        
            'disabled' => 'hidden',    
            'fe_group' => 'fe_group',
        ),
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
        'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cwtcommunity_photos.gif',
    ),
    "feInterface" => array (
        "fe_admin_fieldList" => "hidden, fe_group, title, description, filename, size, width, height, album_uid",
    )
);

$TCA["tx_cwtcommunity_photo_comments"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_photo_comments',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cwtcommunity_photo_comments.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, photo_uid, text",
	)
);

$TCA["tx_cwtcommunity_abuse"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_abuse',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cwtcommunity_abuse.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, email, reason, url",
	)
);

$TCA['tx_cwtcommunity_wall'] = array (
    'ctrl' => array (
        'title'     => 'LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_wall',        
        'label'     => 'uid',    
        'tstamp'    => 'tstamp',
        'crdate'    => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY crdate',    
        'delete' => 'deleted',    
        'enablecolumns' => array (        
            'disabled' => 'hidden',    
            'fe_group' => 'fe_group',
        ),
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
        'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cwtcommunity_wall.gif',
    ),
);

$TCA["tx_cwtcommunity_profileviews"] = array (
    "ctrl" => array (
        'title'     => 'LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_profileviews',        
        'label'     => 'uid',    
        'tstamp'    => 'tstamp',
        'crdate'    => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => "ORDER BY crdate",    
        'delete' => 'deleted',    
        'enablecolumns' => array (        
            'disabled' => 'hidden',
        ),
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
        'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cwtcommunity_profileviews.gif',
    ),
    "feInterface" => array (
        "fe_admin_fieldList" => "hidden, target_uid",
    )
);

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';

t3lib_extMgm::addPlugin(array('LLL:EXT:cwt_community/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:cwt_community/flexform_ds.xml');
?>
