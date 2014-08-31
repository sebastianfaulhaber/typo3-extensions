<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_cwtcommunity_message"] = array (
	"ctrl" => $TCA["tx_cwtcommunity_message"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,fe_group,fe_users_uid,subject,body,status"
	),
	"feInterface" => $TCA["tx_cwtcommunity_message"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'fe_group' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		"fe_users_uid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_message.fe_users_uid",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"subject" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_message.subject",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "30",
			)
		),
		"body" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_message.body",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"status" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_message.status",		
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_message.status.I.0", "0"),
					Array("LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_message.status.I.1", "1"),
				),
				"size" => 1,	
				"maxitems" => 1,
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, fe_users_uid, subject, body, status")
	),
	"palettes" => array (
		"1" => array("showitem" => "fe_group")
	)
);



$TCA["tx_cwtcommunity_buddylist"] = array (
	"ctrl" => $TCA["tx_cwtcommunity_buddylist"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,fe_group,fe_users_uid,buddy_uid"
	),
	"feInterface" => $TCA["tx_cwtcommunity_buddylist"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'fe_group' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		"fe_users_uid" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_buddylist.fe_users_uid",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"buddy_uid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_buddylist.buddy_uid",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, fe_users_uid, buddy_uid")
	),
	"palettes" => array (
		"1" => array("showitem" => "fe_group")
	)
);



$TCA["tx_cwtcommunity_buddylist_approval"] = array (
	"ctrl" => $TCA["tx_cwtcommunity_buddylist_approval"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,fe_group,requestor_uid,target_uid,message"
	),
	"feInterface" => $TCA["tx_cwtcommunity_buddylist_approval"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'fe_group' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		"requestor_uid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_buddylist_approval.requestor_uid",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"target_uid" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_buddylist_approval.target_uid",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
        "message" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_buddylist_approval.message",        
            "config" => Array (
                "type" => "text",
                "cols" => "30",    
                "rows" => "2",
            )
        ),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, requestor_uid, target_uid")
	),
	"palettes" => array (
		"1" => array("showitem" => "fe_group")
	)
);



$TCA["tx_cwtcommunity_guestbook"] = array (
	"ctrl" => $TCA["tx_cwtcommunity_guestbook"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,fe_group,fe_users_uid,status"
	),
	"feInterface" => $TCA["tx_cwtcommunity_guestbook"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'fe_group' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		"fe_users_uid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_guestbook.fe_users_uid",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"status" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_guestbook.status",		
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_guestbook.status.I.0", "0"),
					Array("LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_guestbook.status.I.1", "1"),
				),
				"size" => 1,	
				"maxitems" => 1,
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, fe_users_uid, status")
	),
	"palettes" => array (
		"1" => array("showitem" => "fe_group")
	)
);



$TCA["tx_cwtcommunity_guestbook_data"] = array (
	"ctrl" => $TCA["tx_cwtcommunity_guestbook_data"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,fe_group,guestbook_uid,text"
	),
	"feInterface" => $TCA["tx_cwtcommunity_guestbook_data"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'fe_group' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		"guestbook_uid" => Array (
			"exclude" => 1,		
			"label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_guestbook_data.guestbook_uid",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_cwtcommunity_guestbook",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"text" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_guestbook_data.text",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, guestbook_uid, text")
	),
	"palettes" => array (
		"1" => array("showitem" => "fe_group")
	)
);

		   

$TCA["tx_cwtcommunity_icons"] = array (
    "ctrl" => $TCA["tx_cwtcommunity_icons"]["ctrl"],
	"interface" => array (
        "showRecordFieldList" => "hidden,string,icon"
    ),
    "feInterface" => $TCA["tx_cwtcommunity_icons"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
            )
        ),
        "string" => Array (
            "exclude" => 1,
			"label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_icons.string",		
            "config" => Array (
                "type" => "input",
                "size" => "30",
                "eval" => "required",
            )
        ),
        "icon" => Array (
            "exclude" => 1,
			"label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_icons.icon",		
            "config" => Array (
                "type" => "group",
                "internal_type" => "file",
                "allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],
                "max_size" => 500,
				"uploadfolder" => "uploads/tx_cwtcommunity",
                "show_thumbs" => 1,
                "size" => 1,
                "minitems" => 0,
                "maxitems" => 1,
            )
        ),
    ),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, string, icon")
    ),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_cwtcommunity_albums"] = array (
	"ctrl" => $TCA["tx_cwtcommunity_albums"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,fe_group,title,description,preview_photo_uid,access_policy"
	),
	"feInterface" => $TCA["tx_cwtcommunity_albums"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'fe_group' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		"title" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_albums.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "100",	
				"eval" => "required",
			)
		),
		"description" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_albums.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"preview_photo_uid" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_albums.preview_photo_uid",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_cwtcommunity_photos",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"access_policy" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_albums.access_policy",		
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_albums.access_policy.I.0", "0"),
					Array("LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_albums.access_policy.I.1", "1"),
					Array("LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_albums.access_policy.I.2", "2"),
				),
				"size" => 1,	
				"maxitems" => 1,
				"default" => "2",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2, description;;;;3-3-3, preview_photo_uid, access_policy")
	),
	"palettes" => array (
		"1" => array("showitem" => "fe_group")
	)
);



$TCA["tx_cwtcommunity_photos"] = array (
    "ctrl" => $TCA["tx_cwtcommunity_photos"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "hidden,fe_group,title,description,filename,size,width,height,album_uid"
    ),
    "feInterface" => $TCA["tx_cwtcommunity_photos"]["feInterface"],
    "columns" => array (
        'hidden' => array (        
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
            'config'  => array (
                'type'    => 'check',
                'default' => '0'
            )
        ),
        'fe_group' => array (        
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
            'config'  => array (
                'type'  => 'select',
                'items' => array (
                    array('', 0),
                    array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
                    array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
                    array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
                ),
                'foreign_table' => 'fe_groups'
            )
        ),
        "title" => Array (        
            "exclude" => 0,        
            "label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_photos.title",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",    
                "max" => "100",
            )
        ),
        "description" => Array (        
            "exclude" => 0,        
            "label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_photos.description",        
            "config" => Array (
                "type" => "text",
                "cols" => "30",    
                "rows" => "5",
            )
        ),
        "filename" => Array (        
            "exclude" => 0,        
            "label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_photos.filename",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",    
                "eval" => "required",
            )
        ),
        "size" => Array (        
            "exclude" => 0,        
            "label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_photos.size",        
            "config" => Array (
                "type"     => "input",
                "size"     => "4",
                "max"      => "4",
                "eval"     => "int",
                "checkbox" => "0",
                "range"    => Array (
                    "upper" => "1000",
                    "lower" => "10"
                ),
                "default" => 0
            )
        ),
        "width" => Array (        
            "exclude" => 0,        
            "label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_photos.width",        
            "config" => Array (
                "type"     => "input",
                "size"     => "4",
                "max"      => "4",
                "eval"     => "int",
                "checkbox" => "0",
                "range"    => Array (
                    "upper" => "1000",
                    "lower" => "10"
                ),
                "default" => 0
            )
        ),
        "height" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_photos.height",        
            "config" => Array (
                "type"     => "input",
                "size"     => "4",
                "max"      => "4",
                "eval"     => "int",
                "checkbox" => "0",
                "range"    => Array (
                    "upper" => "1000",
                    "lower" => "10"
                ),
                "default" => 0
            )
        ),
        "album_uid" => Array (        
            "exclude" => 0,        
            "label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_photos.album_uid",        
            "config" => Array (
                "type" => "group",    
                "internal_type" => "db",    
                "allowed" => "tx_cwtcommunity_albums",    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 1,
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2, description;;;;3-3-3, filename, size, width, height, album_uid")
    ),
    "palettes" => array (
        "1" => array("showitem" => "fe_group")
    )
);


$TCA["tx_cwtcommunity_photo_comments"] = array (
	"ctrl" => $TCA["tx_cwtcommunity_photo_comments"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,photo_uid,text"
	),
	"feInterface" => $TCA["tx_cwtcommunity_photo_comments"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"photo_uid" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_photo_comments.photo_uid",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_cwtcommunity_photos",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"text" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_photo_comments.text",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, photo_uid, text")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
    )
);

$TCA["tx_cwtcommunity_abuse"] = array (
	"ctrl" => $TCA["tx_cwtcommunity_abuse"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,email,reason,url"
	),
	"feInterface" => $TCA["tx_cwtcommunity_abuse"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"email" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_abuse.email",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"reason" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_abuse.reason",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"url" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_abuse.url",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, email, reason, url")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);

$TCA["tx_cwtcommunity_profileviews"] = array (
    "ctrl" => $TCA["tx_cwtcommunity_profileviews"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "hidden,target_uid"
    ),
    "feInterface" => $TCA["tx_cwtcommunity_profileviews"]["feInterface"],
    "columns" => array (
        'hidden' => array (        
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
            'config'  => array (
                'type'    => 'check',
                'default' => '0'
            )
        ),
        "target_uid" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_profileviews.target_uid",        
            "config" => Array (
                "type" => "select",    
                "foreign_table" => "fe_users",    
                "foreign_table_where" => "ORDER BY fe_users.uid",    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 1,
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "hidden;;1;;1-1-1, target_uid")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);

$TCA['tx_cwtcommunity_wall'] = array (
    'ctrl' => $TCA['tx_cwtcommunity_wall']['ctrl'],
    'interface' => array (
        'showRecordFieldList' => 'hidden,fe_group,wall_owner_uid,wall_entry_uid,content_text,content_image'
    ),
    'feInterface' => $TCA['tx_cwtcommunity_wall']['feInterface'],
    'columns' => array (
        'hidden' => array (        
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
            'config'  => array (
                'type'    => 'check',
                'default' => '0'
            )
        ),
        'fe_group' => array (        
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
            'config'  => array (
                'type'  => 'select',
                'items' => array (
                    array('', 0),
                    array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
                    array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
                    array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
                ),
                'foreign_table' => 'fe_groups'
            )
        ),
        'wall_owner_uid' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_wall.wall_owner_uid',        
            'config' => array (
                'type'     => 'input',
                'size'     => '4',
                'max'      => '4',
                'eval'     => 'int',
                'checkbox' => '0',
                'range'    => array (
                    'upper' => '1000000',
                    'lower' => '0'
                ),
                'default' => 0
            )
        ),
        'wall_entry_uid' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_wall.wall_entry_uid',        
            'config' => array (
                'type' => 'select',    
                'foreign_table' => 'tx_cwtcommunity_wall',    
                'foreign_table_where' => 'ORDER BY tx_cwtcommunity_wall.uid',    
                'size' => 1,    
                'minitems' => 0,
                'maxitems' => 1,
            )
        ),
        'content_text' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_wall.content_text',        
            'config' => array (
                'type' => 'input',    
                'size' => '3000',
            )
        ),
        'content_image' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:cwt_community/locallang_db.xml:tx_cwtcommunity_wall.content_image',        
            'config' => array (
                'type' => 'group',
                'internal_type' => 'file',
                'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],    
                'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],    
                'uploadfolder' => 'uploads/tx_cwtcommunity',
                'size' => 1,    
                'minitems' => 0,
                'maxitems' => 1,
            )
        ),
    ),
    'types' => array (
        '0' => array('showitem' => 'hidden;;1;;1-1-1, wall_owner_uid, wall_entry_uid, content_text, content_image')
    ),
    'palettes' => array (
        '1' => array('showitem' => 'fe_group')
    )
);

?>
