#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
    tx_cwtcommunityuser_image blob NOT NULL,
    tx_cwtcommunityuser_sex int(11) DEFAULT '0' NOT NULL,
    tx_cwtcommunityuser_gallery_activated tinyint(3) DEFAULT '1' NOT NULL,
	tx_cwtcommunityuser_profile_access tinyint(3) DEFAULT '2' NOT NULL,
	tx_cwtcommunityuser_userlist_visibility tinyint(3) DEFAULT '2' NOT NULL,
	tx_cwtcommunityuser_notification_newbuddy int(11) DEFAULT '1' NOT NULL,
	tx_cwtcommunityuser_notification_newmsg int(11) DEFAULT '1' NOT NULL,
	tx_cwtcommunityuser_stats_profileviews int(11) DEFAULT '0' NOT NULL
);