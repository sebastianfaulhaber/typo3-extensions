<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$tempColumns = Array (
	"tx_cwtcommunityuser_image" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:cwt_community_user/locallang_db.xml:fe_users.tx_cwtcommunityuser_image",		
		"config" => Array (
			"type" => "group",
			"internal_type" => "file",
			"allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],	
			"max_size" => 500,	
			"uploadfolder" => "uploads/tx_cwtcommunityuser",
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
	"tx_cwtcommunityuser_sex" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:cwt_community_user/locallang_db.xml:fe_users.tx_cwtcommunityuser_sex",		
		"config" => Array (
			"type" => "select",
			"items" => Array (
				Array("LLL:EXT:cwt_community_user/locallang_db.xml:fe_users.tx_cwtcommunityuser_sex.I.0", "0"),
				Array("LLL:EXT:cwt_community_user/locallang_db.xml:fe_users.tx_cwtcommunityuser_sex.I.1", "1")
			),
			"size" => 1,	
			"maxitems" => 1,
		)
	),
    "tx_cwtcommunityuser_gallery_activated" => Array (        
        "exclude" => 1,        
        "label" => "LLL:EXT:cwt_community_user/locallang_db.xml:fe_users.tx_cwtcommunityuser_gallery_activated",        
        "config" => Array (
            "type" => "radio",
            "items" => Array (
                Array("LLL:EXT:cwt_community_user/locallang_db.xml:fe_users.tx_cwtcommunityuser_gallery_activated.I.0", "0"),
                Array("LLL:EXT:cwt_community_user/locallang_db.xml:fe_users.tx_cwtcommunityuser_gallery_activated.I.1", "1"),
            ),
        )
    ),
    "tx_cwtcommunityuser_profile_access" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:cwt_community_user/locallang_db.xml:fe_users.tx_cwtcommunityuser_profile_access",		
		"config" => Array (
			"type" => "select",
			"items" => Array (
				Array("LLL:EXT:cwt_community_user/locallang_db.xml:fe_users.tx_cwtcommunityuser_profile_access.I.0", "0"),
				Array("LLL:EXT:cwt_community_user/locallang_db.xml:fe_users.tx_cwtcommunityuser_profile_access.I.1", "1"),
				Array("LLL:EXT:cwt_community_user/locallang_db.xml:fe_users.tx_cwtcommunityuser_profile_access.I.2", "2"),
			),
			"size" => 1,	
			"maxitems" => 1,
		)
	),
    "tx_cwtcommunityuser_userlist_visibility" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:cwt_community_user/locallang_db.xml:fe_users.tx_cwtcommunityuser_userlist_visibility",		
		"config" => Array (
			"type" => "select",
			"items" => Array (
				Array("LLL:EXT:cwt_community_user/locallang_db.xml:fe_users.tx_cwtcommunityuser_userlist_visibility.I.0", "0"),
				Array("LLL:EXT:cwt_community_user/locallang_db.xml:fe_users.tx_cwtcommunityuser_userlist_visibility.I.1", "1"),
				Array("LLL:EXT:cwt_community_user/locallang_db.xml:fe_users.tx_cwtcommunityuser_userlist_visibility.I.2", "2"),
			),
			"size" => 1,	
			"maxitems" => 1,
		)
	),
	"tx_cwtcommunityuser_notification_newbuddy" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:cwt_community_user/locallang_db.xml:fe_users.tx_cwtcommunityuser_notification_newbuddy",		
		"config" => Array (
			"type" => "radio",
			"items" => Array (
				Array("LLL:EXT:cwt_community_user/locallang_db.xml:fe_users.tx_cwtcommunityuser_notification_newbuddy.I.0", "0"),
				Array("LLL:EXT:cwt_community_user/locallang_db.xml:fe_users.tx_cwtcommunityuser_notification_newbuddy.I.1", "1"),
			),
		)
	),
	"tx_cwtcommunityuser_notification_newmsg" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:cwt_community_user/locallang_db.xml:fe_users.tx_cwtcommunityuser_notification_newmsg",		
		"config" => Array (
			"type" => "radio",
			"items" => Array (
				Array("LLL:EXT:cwt_community_user/locallang_db.xml:fe_users.tx_cwtcommunityuser_notification_newmsg.I.0", "0"),
				Array("LLL:EXT:cwt_community_user/locallang_db.xml:fe_users.tx_cwtcommunityuser_notification_newmsg.I.1", "1"),
			),
		)
	),	
    "tx_cwtcommunityuser_stats_profileviews" => Array (        
        "exclude" => 1,        
        "label" => "LLL:EXT:cwt_community_user/locallang_db.xml:fe_users.tx_cwtcommunityuser_stats_profileviews",        
        "config" => Array (
            "type" => "input",    
            "size" => "30",    
            "eval" => "int",
        )
    ),
);


\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA("fe_users");
t3lib_extMgm::addTCAcolumns("fe_users",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("fe_users","tx_cwtcommunityuser_image;;;;1-1-1, tx_cwtcommunityuser_sex, tx_cwtcommunityuser_gallery_activated, tx_cwtcommunityuser_profile_access, tx_cwtcommunityuser_userlist_visibility, tx_cwtcommunityuser_notification_newbuddy, tx_cwtcommunityuser_notification_newmsg, tx_cwtcommunityuser_stats_profileviews");
?>