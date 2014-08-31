<?php
/**
 * Copyright notice
 *
 *   (c) 2003-2009 Sebastian Faulhaber (sebastian.faulhaber@gmx.de)
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

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_t3lib.'class.t3lib_basicfilefunc.php');
include_once(t3lib_extMgm::extPath('cwt_community').'res/class.tx_cwtcommunity_lib_constants.php');

/**
 * Buddylist related functions used in the community.
 * 
 * @author	Sebastian Faulhaber <sebastian.faulhaber@gmx.de>
 * @package TYPO3
 * @subpackage	tx_cwtcommunity
 */
class tx_cwtcommunity_lib_buddylist {
	
	// Cache for fe_users
	private $fe_user_cache = array();
	
	/**
	 * Returns a cObj instance for internal use.
	 * 
	 * @return	cObj	A cObj for internal use only.
	 */
	private static function getCObj() {
		return tx_cwtcommunity_lib_common::getCObj();
	}

	/**
	 * Checks if a FE User is a buddy of the session FE User.
	 * 
	 * @param	int		A FE User's UID.
	 * @return	boolean	True if the target FE User is a buddy; false if not.
	 */
	public static function isBuddyOfLoggedInUser($feuser_uid) {
		$loggedInUid = tx_cwtcommunity_lib_common::getLoggedInUserUID();
		$res = tx_cwtcommunity_lib_common::dbQuery('SELECT * FROM tx_cwtcommunity_buddylist WHERE NOT deleted = 1 AND NOT hidden = 1 AND cruser_id = '.intval($loggedInUid).' AND buddy_uid = '.intval($feuser_uid).';');
		if (is_array($res[0])) {
			return true;
		}
		return false;
	}
	
	/**
	 * Checks if a FE User has requested buddylist approval of a target FE User.
	 *
	 * @param	int		The requestor FE User's UID.
	 * @param	int		The target FE User's UID.
	 * @return	boolean	True if buddylist approval request exists; false if not.
	 */
	public static function hasUserRequestedBuddyApproval($requestor_uid, $feuser_uid) {
		$res = tx_cwtcommunity_lib_common::dbQuery('SELECT * FROM tx_cwtcommunity_buddylist_approval WHERE NOT deleted = 1 AND NOT hidden = 1 AND requestor_uid = '.intval($requestor_uid).' AND target_uid = '.intval($feuser_uid).';');
		if (is_array($res[0])) {
			return true;
		}
		return false;		
	}

	/**
	 * Checks if the session FE User has requested buddylist approval of a target FE User.
	 * 
	 * @param	int		The target FE User's UID.
	 * @return	boolean	True if buddylist approval request exists; false if not.
	 */
	public static function hasLoggedInUserRequestedBuddyApproval($feuser_uid) {
		$loggedInUid = tx_cwtcommunity_lib_common::getLoggedInUserUID();
		return self::hasUserRequestedBuddyApproval($loggedInUid, $feuser_uid);
	}
	
    /**
     * Checks if the buddylist approval is enabled.
     *
     * @param	array	The $conf array supplied by the main() function.
     * @return	boolean	True if enabled.
     */
    public static function isBuddylistApprovalEnabled() {
    	$conf = tx_cwtcommunity_lib_common::getConfArray();
    	if ($conf['buddylist.']['approval'] == '1') {
    		return true;	
    	}
    	return false;
    }
    
    /**
     * Checks if the buddylist notification emails are enabled.
     *
     * @param	array	The $conf array supplied by the main() function.
     * @return	boolean	True if enabled.
     */
    public static function isBuddylistNotificationEnabled() {
    	$conf = tx_cwtcommunity_lib_common::getConfArray();
    	if ($conf['buddylist.']['approval_message'] == '1') {
    		return true;	
    	}
    	return false;    	
    }
    
	/**
    *  Displays the buddylist of a fe user.
    *
    *  @param	array		The buddy records to display.
    *  @param	boolean		If true then custom markers will be replaced.
    *  @return	string		The generated HTML content.
    */
    public static function getViewBuddylist($buddylist, $showReadOnly = false, $approvalsReceived = null, $approvalsSent = null){
    	// Get Smarty Instance
    	$smartyInstance = tx_cwtcommunity_lib_common::getSmartyInstance();
        $conf = tx_cwtcommunity_lib_common::getConfArray();
        $tplPath = tx_cwtcommunity_lib_common::getTemplatePath($conf['template_buddylist']);
        
        // Provide smarty with the information for the template
        $smartyInstance->assign('buddylist',$buddylist);
        $smartyInstance->assign('showReadOnly',$showReadOnly);
        if ($approvalsReceived != null) {
       		$smartyInstance->assign('approvalsRec', $approvalsReceived);
       		$smartyInstance->assign('approvalsRecCount', sizeof($approvalsReceived));
        }
        if ($approvalsSent != null) {
			$smartyInstance->assign('approvalsSent', $approvalsSent);
			$smartyInstance->assign('approvalsSentCount', sizeof($approvalsSent));        	
        }
                
        $content .= $smartyInstance->display($tplPath);
        return $content;
    }

	/**
    *  Displays the buddylist administrations view for a specific user.
    *
    *  @param	array		Buddy approvals that have been received.
    *  @param	array		Buddy approvals that have been sent.
    *  @return	string		The generated HTML content.
    */
    public static function getViewBuddyadmin($approvalsReceived, $approvalsSent){
    	// Get Smarty Instance
    	$smartyInstance = tx_cwtcommunity_lib_common::getSmartyInstance();
        $conf = tx_cwtcommunity_lib_common::getConfArray();
        $tplPath = tx_cwtcommunity_lib_common::getTemplatePath($conf['template_buddyadmin']);
        
        // Provide smarty with the information for the template
        $smartyInstance->assign('approvalsRec', $approvalsReceived);
        $smartyInstance->assign('approvalsSent', $approvalsSent);
        $content .= $smartyInstance->display($tplPath);
        return $content;
    }    
    
    /**
    *  Fetches buddylist information for a fe user and return it.
    *
    *  @param	int		A FE User's uid.
    *  @return	array	An Array of 'tx_cwtcommunity_buddylist' records.
    */
    public static function getBuddylist($uid) {
        //Init some vars
        $buddylist = null;
        //Get buddylist
        $buddylist = tx_cwtcommunity_lib_common::dbQuery("SELECT tx_cwtcommunity_buddylist.buddy_uid, fe_users.* FROM tx_cwtcommunity_buddylist, fe_users WHERE NOT tx_cwtcommunity_buddylist.hidden = 1 AND NOT tx_cwtcommunity_buddylist.deleted = 1 AND tx_cwtcommunity_buddylist.fe_users_uid = ".intval($uid)." AND fe_users.uid = tx_cwtcommunity_buddylist.buddy_uid AND NOT fe_users.deleted = 1 AND NOT fe_users.disable = 1 ORDER BY fe_users.username ASC");
        return $buddylist;
    }

    /**
     * Creates a new buddylist approval record, if one not already exists.
     *
     * @param	int		The UID of the requestor fe_user.
     * @param	int		The users that shall be added to the requestor's buddylist.
	 * @param	string	An optional message which is sent along with the request.
     * @return 	void
     */
    public static function createBuddylistApproval($requestor_uid, $target_uid, $message) {
    	$message == null ? $message = '': $message;
    	// Check if buddy request already exists
    	if (!self::hasUserRequestedBuddyApproval($requestor_uid, $target_uid)) {
    		tx_cwtcommunity_lib_common::dbUpdateQuery('INSERT INTO tx_cwtcommunity_buddylist_approval 
    				(pid, tstamp, crdate, cruser_id, requestor_uid, target_uid, message) 
    				VALUES ('.tx_cwtcommunity_lib_common::getGlobalStorageFolder().','.time().','.time().',
    					"'.intval($requestor_uid).'","'.intval($requestor_uid).'","'.intval($target_uid).'", 
    					'.$GLOBALS['TYPO3_DB']->fullQuoteStr($message, 'tx_cwtcommunity_buddylist_approval').');');
    	}
    }
    
    public static function acceptBuddylistApproval($approval_uid) {
    	// Add user to buddylist
    	$conf = tx_cwtcommunity_lib_common::getConfArray();
		$approval = self::getBuddylistApproval($approval_uid);
		self::addBuddy($approval['target_uid'], $approval['requestor_uid']);
		self::addBuddy($approval['requestor_uid'], $approval['target_uid']);

		// Delete approval
		tx_cwtcommunity_lib_common::dbUpdateQuery('UPDATE tx_cwtcommunity_buddylist_approval SET deleted = 1 WHERE uid = "'.intval($approval_uid).'";');

    	// Send notification to requestor
    	$user = tx_cwtcommunity_lib_common::getUser($approval['target_uid']);
		$varDbFields = explode(',', $conf['buddylist.']['notification.']['sender_db_field']);
		$vars = array();
		for ($i = 0; $i < sizeof($varDbFields); $i++) {
			$vars[] = $user[trim($varDbFields[$i])];
		}
		tx_cwtcommunity_lib_common::sendPrivateMessage($approval['target_uid'], $approval['requestor_uid'], tx_cwtcommunity_lib_common::getLL('WELCOME_VIEW_MESSAGE_BUDDY_ACCEPT'), tx_cwtcommunity_lib_common::getLL('WELCOME_VIEW_MESSAGE_BODY_ACCEPT', $vars));
    }
    
    public static function denyBuddylistApproval($approval_uid) {
    	// Delete approval
    	$conf = tx_cwtcommunity_lib_common::getConfArray();
		$approval = self::getBuddylistApproval($approval_uid);
		tx_cwtcommunity_lib_common::dbUpdateQuery('UPDATE tx_cwtcommunity_buddylist_approval SET deleted = 1 WHERE uid = "'.intval($approval_uid).'";');

    	// Send notification to requestor
		$user = tx_cwtcommunity_lib_common::getUser($approval['target_uid']);
    	$varDbFields = explode(',', $conf['buddylist.']['notification.']['sender_db_field']);
		$vars = array();
		for ($i = 0; $i < sizeof($varDbFields); $i++) {
			$vars[] = $user[trim($varDbFields[$i])];
		}
		tx_cwtcommunity_lib_common::sendPrivateMessage($approval['target_uid'], $approval['requestor_uid'], tx_cwtcommunity_lib_common::getLL('WELCOME_VIEW_MESSAGE_BUDDY_DENY'), tx_cwtcommunity_lib_common::getLL('WELCOME_VIEW_MESSAGE_BODY_DENY', $vars));    	
    }
    
    /**
     * Gets all open approvals for a specific user and the user information for the requestor.
     *
     * @param	int		Get the approvals for this fe_user UID.
     * @return  array	An Array of 'tx_cwtcommunity_buddylist_approval' records joined with the requestor's user information.
     */
    public static function getBuddylistApprovalsReceived($target_uid) {
    	$approvals = tx_cwtcommunity_lib_common::dbQuery('SELECT * FROM tx_cwtcommunity_buddylist_approval WHERE NOT deleted = 1 AND NOT hidden = 1 AND target_uid = "'.intval($target_uid).'";');
		// Get the user information for the buddylist approval requestor
        foreach ($approvals as &$approval){
        	$approval['user_info']= tx_cwtcommunity_lib_common::getUser($approval['requestor_uid']); 
        }
    	return $approvals;
    }    

    /**
     * Gets all approvals that have been sent by a specific user.
     *
     * @param	int		Get the approvals sent by this fe_user UID.
     * @return  array	An Array of 'tx_cwtcommunity_buddylist_approval' records joined with the requestor's user information.
     */
    public static function getBuddylistApprovalsSent($target_uid) {
    	$approvals = tx_cwtcommunity_lib_common::dbQuery('SELECT * FROM tx_cwtcommunity_buddylist_approval WHERE NOT deleted = 1 AND NOT hidden = 1 AND cruser_id = "'.intval($target_uid).'";');
    	// Get the user information for the buddylist approval requestor
        foreach ($approvals as &$approval){
        	$approval['user_info']= tx_cwtcommunity_lib_common::getUser($approval['target_uid']); 
        }
    	return $approvals;
    }    
    
    /**
     * Gets a specific approval.
     *
     * @param	int		The approval uid.
     * @return	array	The approval as an associative array.
     */
    public static function getBuddylistApproval($approval_uid) {
    	$res = tx_cwtcommunity_lib_common::dbQuery('SELECT * FROM tx_cwtcommunity_buddylist_approval WHERE NOT deleted = 1 AND NOT hidden = 1 AND uid = "'.intval($approval_uid).'";');
    	return $res[0];
    }    
    
    /**
     * Sends a notification mail to a specific user, if he has got a new buddylist approval request.
     * 
     * @param 	int		The UID of the FE User that shall be notified.
     * @param 	int		The requesting FE User's UID. 
     * @return 	void
     */
    public static function sendBuddylistNotification($target_uid, $requestor_uid) {
    	$conf = tx_cwtcommunity_lib_common::getConfArray();
    	// Get E-Mail of target user
		$user = tx_cwtcommunity_lib_common::getUser($target_uid);
		$requestor = tx_cwtcommunity_lib_common::getUser($requestor_uid);
    	//Send E-Mail
		if ($user != '' && t3lib_div::validEmail($user['email'])) {
			// Extract variables for LL substitution
			$varDbFields = explode(',', $conf['buddylist.']['notification.']['sender_db_field']);
			$vars = array();
			for ($i = 0; $i < sizeof($varDbFields); $i++) {
				$vars[] = $requestor[trim($varDbFields[$i])];
			}
			// Compose e-mail
			$mail_subject = tx_cwtcommunity_lib_common::getLL('WELCOME_VIEW_BUDDY_APPROVAL_MAIL_HEADER');
			$mail_body = tx_cwtcommunity_lib_common::getLL('WELCOME_VIEW_BUDDY_APPROVAL_MAIL_BODY', $vars);
			
			// Set mail sender
			$fromAddress = $conf['common.']['notification.']['mail.']['fromAddress'];
			$fromName = $conf['common.']['notification.']['mail.']['fromName'];
			$mail_headers = 'From: "'.$fromName.'" <'.$fromAddress.'>';			
			// Send mail
			@t3lib_div::plainMailEncoded($user['email'], $mail_subject, $mail_body, $mail_headers);
		}
    }
    
    /**
    *  Adds the user with $buddy_uid to $uid's buddylist. Furthermore it check for double
    *  additions, (in case a user wants to add a buddy, that exists on his/her list).
    *
    *  @param	int		A FE User's uid.
    *  @param	int		Buddy UID to add to list
    *  @return 	void
    */
    public static function addBuddy($uid, $buddy_uid) {
        // Check if the buddy already exists
        $buddy = tx_cwtcommunity_lib_common::dbQuery('SELECT uid FROM tx_cwtcommunity_buddylist WHERE NOT deleted = 1 AND NOT hidden = 1 AND buddy_uid = "'.intval($buddy_uid).'" AND fe_users_uid = "'.intval($uid).'"');
        if ($buddy[0]['uid'] != null){
            // Buddy exists, so do nothing
			return;
        } else {
            $res = tx_cwtcommunity_lib_common::dbUpdateQuery("INSERT INTO tx_cwtcommunity_buddylist 
            			(pid, crdate, cruser_id, fe_users_uid, buddy_uid) 
            			VALUES (".tx_cwtcommunity_lib_common::getGlobalStorageFolder().",".time().", 
            			".intval($uid).", ".intval($uid).", ".intval($buddy_uid).");");
        }
    }

    /**
    *  Deletes a buddy from a list.
    *
    *  @param	int		A FE User's uid.
    *  @param	int		Buddy UID to delete from list
    *  @return	void 
    */
    public static function deleteBuddy($uid, $buddy_uid) {
        //Check for vars, in case anybody wants to attack us.
        if ($uid == null || $buddy_uid == null){
            die("You are not allowed to do that!");
        }
        //Do the query
        $res = tx_cwtcommunity_lib_common::dbUpdateQuery('DELETE FROM tx_cwtcommunity_buddylist WHERE fe_users_uid = "'.intval($uid).'" AND buddy_uid = "'.intval($buddy_uid).'"');
    }    
}
if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_community/res/class.tx_cwtcommunity_lib_buddylist.php"]) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_community/res/class.tx_cwtcommunity_lib_buddylist.php"]);
}
?>