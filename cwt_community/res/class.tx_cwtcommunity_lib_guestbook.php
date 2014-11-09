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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Guestbook related functions used in the community using EXT:smarty.
 * 
 * @author	Sebastian Faulhaber <sebastian.faulhaber@gmx.de>
 * @package TYPO3
 * @subpackage	tx_cwtcommunity
 */
class tx_cwtcommunity_lib_guestbook {

	/* getViewGuestbook
	*
	*  Displays the guestbook view for a logged in fe users' guestbook. In this view
	*  the fe user is able to enable/ disable his/her guestbook. Furthermore this view
	*  provides the feature to delete specific guestbook items.
	*
	*  @param fe user uid, currently logged in.
	*  @param $isLocked Boolean. True if Guestbook is locked/ False, if guestbook is open.
	*  @param $guestbook guestbook items from function
	*  @param $view The type of view to be displayed 'logged_in', 'add', 'add_result', 'disabled' or none.
	*  @return String The generated HTML source for this view.
	*/
	public static function getViewGuestbook($uid, $isLocked = false, $guestbook = null, $view = ''){
		// Get Smarty Instance
		$smartyInstance = tx_cwtcommunity_lib_common::getSmartyInstance();
		$conf = tx_cwtcommunity_lib_common::getConfArray();
		$cObj = tx_cwtcommunity_lib_common::getCObj();
		$tplPath = tx_cwtcommunity_lib_common::getTemplatePath($conf['template_guestbook']);
        
		$form_action_enable_disable_guestbook = tx_cwtcommunity_lib_common::getPageLink($GLOBALS['TSFE']->id, "_self", array(tx_cwtcommunity_lib_constants::CONST_ACTION => "getviewguestbookloggedin", "uid" => $uid));
		$form_action_add_guestbook_entry = tx_cwtcommunity_lib_common::getPageLink($GLOBALS['TSFE']->id, "_self", array(tx_cwtcommunity_lib_constants::CONST_ACTION => "getviewguestbookadd", "uid" => $uid));
        
        
		// Provide smarty with the information for the template
		$smartyInstance->assign('guestbook', $guestbook);
		$smartyInstance->assign('isLocked',$isLocked);
		$smartyInstance->assign('view',$view);
		$smartyInstance->assign('uid',$uid);
		$smartyInstance->assign('enable_disable_guestbook',$form_action_enable_disable_guestbook);
		$smartyInstance->assign('add_guestbook_entry',$form_action_add_guestbook_entry);
        
		$content .= $smartyInstance->display($tplPath);
        
		return $content;
    }
    
    /**
	*  Activates a user's guestbook
	*  
	*  @param	int		A FE User's uid.
	*  @return	void
	*/
	public static function openGuestbook($uid) { 
		//Check first if an entry in tx_cwtcommunity_guestbook for the user uid exists
		$res = tx_cwtcommunity_lib_common::dbQuery("SELECT tx_cwtcommunity_guestbook.status FROM tx_cwtcommunity_guestbook, fe_users WHERE fe_users.uid = ".intval($uid)." AND fe_users.uid = tx_cwtcommunity_guestbook.fe_users_uid");
		//NO ENTRY --> create one
		if ($res[0]['status'] == null) {
			// Execute the query
			$res1 = tx_cwtcommunity_lib_common::dbUpdateQuery("INSERT INTO tx_cwtcommunity_guestbook (pid, crdate, fe_users_uid, status) VALUES (".tx_cwtcommunity_lib_common::getGlobalStorageFolder().", ".time().", ".intval($uid).", 0)");		    
		}
		// ENTRY EXISTS --> only doing an update
		elseif ($res[0]['status'] == "1"){
			// Execute the query
			$res1 = tx_cwtcommunity_lib_common::dbUpdateQuery("UPDATE tx_cwtcommunity_guestbook SET status = 0 WHERE fe_users_uid = ".intval($uid));
		}
	return null;
    }
    
    /**
	*  Deactivates a user's guestbook
	*  
	*  @param	int		A FE User's uid.
	*  @return	void
	*/
	public static function lockGuestbook($uid) { 
		// Check first if an entry in tx_cwtcommunity_guestbook for the user uid exists
		$res = tx_cwtcommunity_lib_common::dbQuery("SELECT tx_cwtcommunity_guestbook.status FROM tx_cwtcommunity_guestbook, fe_users WHERE fe_users.uid = ".intval($uid)." AND fe_users.uid = tx_cwtcommunity_guestbook.fe_users_uid");
		// NO ENTRY --> create one
		if ($res[0]['status'] == null) {
			//Execute the query
			$res1 = tx_cwtcommunity_lib_common::dbUpdateQuery("INSERT INTO tx_cwtcommunity_guestbook (pid, crdate, fe_users_uid, status) VALUES (".tx_cwtcommunity_lib_common::getGlobalStorageFolder().", ".time().", ".intval($uid).", 1)");		    
		}
		// ENTRY EXISTS --> only doing an update
		elseif ($res[0]['status'] == "0"){
			//Execute the query
			$res1 = tx_cwtcommunity_lib_common::dbUpdateQuery("UPDATE tx_cwtcommunity_guestbook SET status = 1 WHERE fe_users_uid = ".intval($uid));
		}
		return null;
    }
    
	/** 
	*  Gets the status of a users Guestbook.
	*  
	*  @param	int		fe user uid
	*  @return	int		1 -> Closed, 0 -> Open
	*/
	public static function getGuestbookStatus($uid) { 
		// Check if an entry for a guestbook exists
		$res = tx_cwtcommunity_lib_common::dbQuery("SELECT gb.status FROM tx_cwtcommunity_guestbook AS gb WHERE gb.fe_users_uid = ".intval($uid));
		// Guestbook OPEN
		if ($res[0]['status'] == "0") {
			return "0";		    
		}
		// Guestbook CLOSED
		elseif ($res[0]['status'] == "1"){
			return "1";
		}
		// Guestbook CLOSED		
		return "1";
    }

    /**
	*  Deletes an item from a users' guestbook.
	*  
	*  @param	int		The uid of a 'tx_cwtcommunity_guestbook_data_item'.
	*  @return	void
	*/
	public static function deleteGuestbookItem($item_uid) { 
		$res = tx_cwtcommunity_lib_common::dbUpdateQuery("DELETE FROM tx_cwtcommunity_guestbook_data WHERE uid = ".intval($item_uid));
		return null;
    }
    
    /**
	*  Deletes ALL items from a user's guestbook.
	*
	*  @param	int		A FE User's uid.
	*  @return	void
	*/
	public static function deleteGuestbook($uid) {
		// Get the users guestbook uid
		$gb_uid = tx_cwtcommunity_lib_common::dbQuery("SELECT uid FROM tx_cwtcommunity_guestbook WHERE fe_users_uid = ".intval($uid));
		$gb_uid = $gb_uid[0]["uid"];
		$res = tx_cwtcommunity_lib_common::dbUpdateQuery("DELETE FROM tx_cwtcommunity_guestbook_data WHERE guestbook_uid = ".intval($gb_uid));
		return null;
    }
    
    /**
	*  Fetches the guestbook of a user
	*  
	*  @param $fe_users_uid Valid uid from 'fe_users'
	*  @return Array
	*/
	public static function getGuestbook($fe_users_uid = null) {
		// Fetch user
		$records = tx_cwtcommunity_lib_common::dbQuery("SELECT tx_cwtcommunity_guestbook_data.cruser_id, tx_cwtcommunity_guestbook_data.crdate, tx_cwtcommunity_guestbook_data.text, tx_cwtcommunity_guestbook_data.uid, fe_users.username FROM tx_cwtcommunity_guestbook, tx_cwtcommunity_guestbook_data, fe_users WHERE tx_cwtcommunity_guestbook.fe_users_uid = ".intval($fe_users_uid)." AND tx_cwtcommunity_guestbook_data.guestbook_uid = tx_cwtcommunity_guestbook.uid AND tx_cwtcommunity_guestbook_data.cruser_id = fe_users.uid ORDER BY tx_cwtcommunity_guestbook_data.crdate DESC");
		return $records;
    }
    
    /**
     * Insert a new guestbook entry for a specified FE User.
     *
     * @param	int		The creating user's uid.	
     * @param	string	The guestbook entrie's text.
     * @param	int		The guestbook owner's uid.
     * @return	void
     */
	public static function insertGuestbookData($cruser_id, $text, $uid) { 
		// Get guestbook uid for uid
		$guestbookUID = tx_cwtcommunity_lib_common::dbQuery("SELECT tx_cwtcommunity_guestbook.uid FROM tx_cwtcommunity_guestbook, fe_users WHERE fe_users.uid = ".intval($uid)." AND fe_users.uid = tx_cwtcommunity_guestbook.fe_users_uid");
		$guestbookUID = $guestbookUID[0]['uid']; 
		// Get timestamp
		$crdate = time();
		$text = $GLOBALS['TYPO3_DB']->fullQuoteStr($text, 'tx_cwtcommunity_guestbook_data'); 
		// Insert entry into db
		$res = tx_cwtcommunity_lib_common::dbUpdateQuery("INSERT INTO tx_cwtcommunity_guestbook_data (pid, guestbook_uid, text,cruser_id, crdate) VALUES (" . tx_cwtcommunity_lib_common::getGlobalStorageFolder() . ", ".intval($guestbookUID).", ".$text.", ".intval($cruser_id).", $crdate)");
		return null;
    }
    
     /**
     * Checks if the private message notification emails are enabled.
     *
     * @return	boolean	True if enabled.
     */
    public static function isGuestbookNotificationEnabled() {
    	$conf = tx_cwtcommunity_lib_common::getConfArray();
    	if ($conf['guestbook.']['notification.']['enabled'] == '1') {
    		return true;	
    	}
    	return false;    	
    } 
    
    /**
     * Sends a notification mail to a specific user, if a new guestbook entry is created.
     * 
     * @param 	int		The UID of the user that shall be notified.
     * @param	int		The UID of the message that has arrived.
     */
    public static function sendGuestbookNotification($target_uid) {
    	// Get E-Mail of target user
		$user = tx_cwtcommunity_lib_common::getUser($target_uid);
		$conf = tx_cwtcommunity_lib_common::getConfArray();

    	//Send E-Mail
		if ($user != '' && GeneralUtility::validEmail($user['email'])) {
			// Extract variables for LL substitution
			$varDbFields = explode(',', $conf['guestbook.']['notification.']['sender_db_field']);
			$vars = array();
			for ($i = 0; $i < sizeof($varDbFields); $i++) {
				$vars[] = $user[trim($varDbFields[$i])];
			}
			$vars[] = $conf['guestbook.']['typo3SiteURL'];
			
			$mail_subject = tx_cwtcommunity_lib_common::getLL('GUESTBOOK_NOTIFICATION_MAIL_SUBJECT', array());
			$mail_body = tx_cwtcommunity_lib_common::getLL('GUESTBOOK_NOTIFICATION_MAIL_BODY', $vars);
			
			// Set mail sender
			$conf = tx_cwtcommunity_lib_common::getConfArray();
			$fromAddress = $conf['common.']['notification.']['mail.']['fromAddress'];
			$fromName = $conf['common.']['notification.']['mail.']['fromName'];
			$mail_headers = 'From: "'.$fromName.'" <'.$fromAddress.'>';			
			// Send mail
			@GeneralUtility::plainMailEncoded($user['email'], $mail_subject, $mail_body, $mail_headers);
		}
    }    
    
}
if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_community/res/class.tx_cwtcommunity_lib_guestbook.php"]) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_community/res/class.tx_cwtcommunity_lib_guestbook.php"]);
}
?>