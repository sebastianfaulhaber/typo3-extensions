<?php
/**
 * Copyright notice
 *
 *   (c) 2003-2014 Sebastian Faulhaber (sebastian.faulhaber@gmx.de)
 *   All rights reserved
 *
 *   This script is part of the Typo3 project. The Typo3 project is
 *   free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   The GNU General Public License can be found at
 *   http://www.gnu.org/copyleft/gpl.html.
 *
 *   This script is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   This copyright notice MUST APPEAR in all copies of the script!
 */

/**
 * Constants used in the community classes.
 * 
 * @author	Sebastian Faulhaber <sebastian.faulhaber@gmx.de>
 * @package TYPO3
 * @subpackage	tx_cwtcommunity
 */
class tx_cwtcommunity_lib_constants {
	
	/*
     * Please notice that the output is REMOTE_ADDR dependant. It will output 
     * content only if your IP address as client matches a certain list found 
     * in TYPO3_CONF_VARS[SYS][devIPmask]. 
     */
	public static $DEBUG_TS_CONFIGURATION = false;
	public static $DEBUG_SQL_QUERIES = false;
	public static $DEBUG_APPLICATION = false;
	
	const PATH_USER_PROFILE_IMAGES = 'uploads/tx_cwtcommunityuser/';
	const PATH_ICONS = 'uploads/tx_cwtcommunity/icons';
	const PATH_USER_GALLERY_IMAGES = 'uploads/tx_cwtcommunity/gallery/';
	
	const CONST_PREFIX_ID = 'tx_cwtcommunity_pi1';
	const CONST_ACTION = 'action';
	const CONST_CMD = 'cmd';
	const CONST_ALBUM_CRUSER_ID = 'album_user_id';
	const CONST_ALBUM_UID = 'album_uid';
	const CONST_PHOTO_UID = 'photo_uid';
	const CONST_COMMENT_UID = 'comment_uid';
	const CONST_USER_UID = 'feuser_uid';
	const CONST_APPROVAL_UID = 'approval_uid';
	const CONST_RECIPIENT_UID = 'recipient_uid';
	const CONST_ANSWER_UID = 'answer_uid';
	const CONST_RECIPIENTS = 'recipients';
	const CONST_RECIPIENTS_ALL_BUDDIES = 'recipients_all_buddies';
	const CONST_SUBJECT = 'subject';
	const CONST_BODY = 'body';
	const CONST_SORT_ASC = 'asc';
	const CONST_SORT_DESC = 'desc';
	const CONST_PROFILE_ACCESS_ROLE_OWNER = '0';
	const CONST_PROFILE_ACCESS_ROLE_BUDDY = '1';
	const CONST_PROFILE_ACCESS_ROLE_ALL = '2';
	const CONST_USERLIST_VISIBILITY_NOBODY = '0';
	const CONST_USERLIST_VISIBILITY_BUDDY = '1';
	const CONST_USERLIST_VISIBILITY_ALL = '2';	
	const CONST_USERLIST_SEARCHMODE_FULLTEXT = 'fulltext';
	const CONST_USERLIST_SEARCHMODE_ATSTART = 'atStart';
	const CONST_USERLIST_SEARCHMODE_ATEND = 'atEnd';
	const CONST_USERLIST_SEARCHMODE_AGE = 'age';
	const CONST_USERLIST_SEARCHMODE_EQUALS = 'equals';	
	const CONST_USERLIST_SEARCHMODE_SELECT2CHECKBOX = 'select2checkbox';
	const CONST_USERLIST_SEARCHMODE_IN = 'in';
	const CONST_USERLIST_SEARCHMODE_USERGROUP = 'usergroup';
	
	const ACTION_GALLERY_SHOW_ALBUM_LIST = 'show_album_list';
	const ACTION_GALLERY_SHOW_ALBUM_NEW = 'show_album_new';
	const ACTION_GALLERY_SHOW_ALBUM_EDIT = 'show_album_edit';
	const ACTION_GALLERY_SHOW_ALBUM_DELETE = 'show_album_delete';
	const ACTION_GALLERY_SHOW_ALBUM_DETAIL = 'show_album_detail';
	const ACTION_GALLERY_SHOW_PHOTO_DETAIL = 'show_photo_detail';
	const ACTION_GALLERY_SHOW_PHOTO_NEW = 'show_photo_new';
	const ACTION_GALLERY_SHOW_PHOTO_REPORT = 'show_photo_report';
	const ACTION_GALLERY_SHOW_PHOTO_EDIT = 'show_photo_edit';
	const ACTION_BUDDYLIST_SHOW_LIST = 'show_buddylist';
	const ACTION_MESSAGES_SHOW_ANSWER_MSG = 'show_messages_answer_msg';
	const ACTION_MESSAGES_SHOW_NEW_MSG = 'show_messages_new_msg';
	const ACTION_MESSAGES_SHOW_NEW_MSG_RESULT = 'show_messages_new_msg_result';
	const ACTION_SEARCH_SHOW_RESULT = 'show_search_results';
	const ACTION_SEARCH_SHOW_FORM = 'show_search_results_extended';
	const ACTION_COMMON_SORT_ORDER = 'sort_order';
	const ACTION_COMMON_SORT_COLUMN = 'sort_column';
	const ACTION_COMMON_PAGE = 'page';
	const ACTION_USERLIST_GENERIC_SHOWLIST = 'showList';
	const ACTION_USERLIST_GENERIC_SHOWTEASER = 'showTeaser';
	const ACTION_ABUSE_NEW_REQUEST = 'newAbuseRequest';
	
	const CMD_GALLERY_DELETE_ALBUM = 'delete_album';
	const CMD_GALLERY_DELETE_PHOTO = 'delete_photo';
	const CMD_GALLERY_DELETE_COMMENT = 'delete_comment';
	const CMD_GALLERY_SET_PREVIEW_PHOTO = 'set_preview_photo';
	const CMD_GALLERY_REPORT_PHOTO = 'report_photo';
	const CMD_BUDDYLIST_ACCEPT_APPROVAL = 'accept_approval';
	const CMD_BUDDYLIST_DENY_APPROVAL = 'deny_approval';
	const CMD_CANCEL = 'cancel';
	const CMD_SEND = 'send';
	const CMD_PHOTO_UPLOAD = 'photo_upload';

	const BE_ACTION_USER_ADMINISTRATION = 'getviewuseradministration';
	const BE_ACTION_USER_ADMINISTRATION_ENABLED = 'getviewuseradministrationenabled';
	const BE_ACTION_USER_ADMINISTRATION_DISABLED = 'getviewuseradministrationdisabled';
	const BE_ACTION_MESSAGE_PREVIEW = 'messagepreview';
	const BE_ACTION_MESSAGE_SEND = 'messagesend';
	const BE_ACTION_GALLERY = 'getviewgallery';
	const BE_ACTION_GALLERY_COMMENTS = 'getviewgallerycomments';
	
	const VIEW_SEARCH_EXTENDED = 'extended';
	const VIEW_SEARCH_SIMPLE = 'simple';
	const VIEW_SEARCH_RESULT = 'result';
	const VIEW_GUESTBOOK_LOGGED_IN = 'logged_in';
	const VIEW_GUESTBOOK_ADD = 'add';
	const VIEW_GUESTBOOK_ADD_RESULT = 'add_result';
	const VIEW_GUESTBOOK_DISABLED = 'disabled';
	
}
if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_community/res/class.tx_cwtcommunity_lib_constants.php"]) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_community/res/class.tx_cwtcommunity_lib_constants.php"]);
}
?>