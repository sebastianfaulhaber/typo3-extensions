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
 * Message center related functions used in the community using EXT:smarty.
 * 
 * @author	Sebastian Faulhaber <sebastian.faulhaber@gmx.de>
 * @package TYPO3
 * @subpackage	tx_cwtcommunity
 */
class tx_cwtcommunity_lib_messages {
	
	private $cObj = null;
	// Cache for fe_users
	private $fe_user_cache = array();
	
	/**
	 * Returns a cObj instance for internal use.
	 * 
	 * @return	cObj	A cObj for internal use only.
	 */
	private function getCObj() {
		// Lazy load cobj
		if ($this->cObj == null) {
	        $this->cObj = t3lib_div::makeInstance('tslib_cObj');			
		}
		return $this->cObj;
	}

	/* getViewMessages($messages)
    *
    *  Displays the message view of a user.
    *
    *  @param $messages Array of messages.
    *  @param $uid Session user's uid
    *  @param $recipient_uid fe user uid
    *  @param $message One message
    *  @param $view 'single', 'new_result' or '' to decide which part of the template is used
    *  @return String The generated HTML source for this view.
    */
    function getViewMessages($messages, $message=null, $uid=null, $recipient_uid=null, $view=''){
		// Get Smarty Instance
		$smartyInstance = tx_cwtcommunity_lib_common::getSmartyInstance();
		$conf = tx_cwtcommunity_lib_common::getConfArray();
		$cObj = tx_cwtcommunity_lib_common::getCObj();
		$tplPath = tx_cwtcommunity_lib_common::getTemplatePath($conf['template_messages']);
		
		// Provide smarty with the information for the template
		$smartyInstance->assign('messages', $messages);
		$smartyInstance->assign('message', $message);
		$smartyInstance->assign('uid', $uid);
		$smartyInstance->assign('recipient_uid', $recipient_uid);
		$smartyInstance->assign('view', $view);
		$smartyInstance->assign('mode', $mode);
		
		$content .= $smartyInstance->display($tplPath);
        
		return $content;
		
    	
    }
	
	
    /**
    *  Displays a form for sending messages.
    *
    *  @param	int		The sender's fe_user UID.
    *  @param	int		If we deal with a single recipient, a fe_user UID.
    *  @param	string	The message's subject.
    *  @param	string	The message's body content.
    *  @param	string	The display mode: answer or new.
    *  @param 	string	The view for the template
    *  @return	string	The generated HTML source for this view.
    */
    function getViewMessagesNew($uid, $recipient_uid, $subject, $body, $mode, $view, $recipients, $recipients_all_buddies, $action) {
		// Get Smarty Instance
		$smartyInstance = tx_cwtcommunity_lib_common::getSmartyInstance();
		$conf = tx_cwtcommunity_lib_common::getConfArray();
		$cObj = tx_cwtcommunity_lib_common::getCObj();
		$tplPath = tx_cwtcommunity_lib_common::getTemplatePath($conf['template_messages']);
		
    	
		$form_action = tx_cwtcommunity_lib_common::getPageLink($GLOBALS['TSFE']->id, "_self", array(tx_cwtcommunity_lib_constants::CONST_ACTION => $action, "uid" => $uid, tx_cwtcommunity_lib_constants::CONST_RECIPIENT_UID => $recipient_uid));
		
		//get recipient user's information for "answer" mode
		$recipient = tx_cwtcommunity_lib_common::getUser($recipient_uid);
		
		// Provide smarty with the information for the template
		$smartyInstance->assign('recipients', $recipients);
		$smartyInstance->assign('recipient', $recipient);
		$smartyInstance->assign('recipients_all_buddies', $recipients_all_buddies);
		$smartyInstance->assign('view', $view);
		$smartyInstance->assign('mode', $mode);
		$smartyInstance->assign('subject', htmlspecialchars($subject));
		$smartyInstance->assign('body', htmlspecialchars($body));
		$smartyInstance->assign('form_action',$form_action);
		
		
		$content .= $smartyInstance->display($tplPath);
        
		return $content;
		    
    }    

    /**
     * Sends a notification mail to a specific user, if he has got a new private message.
     * 
     * @param 	int		The UID of the user that shall be notified.
     * @param	int		The UID of the message that has arrived.
     */
    function sendPrivateMessageNotification($target_uid, $msg_uid) {
    	// Get E-Mail of target user
		$user = tx_cwtcommunity_lib_common::getUser($target_uid);
		$conf = tx_cwtcommunity_lib_common::getConfArray();

    	//Send E-Mail
		if ($user != '' && t3lib_div::validEmail($user['email'])) {
			// Extract variables for LL substitution
			$varDbFields = explode(',', $conf['messages.']['notification.']['sender_db_field']);
			$vars = array();
			for ($i = 0; $i < sizeof($varDbFields); $i++) {
				$vars[] = $user[trim($varDbFields[$i])];
			}
			$vars[] = $conf['messages.']['typo3SiteURL'];
			
			$mail_subject = tx_cwtcommunity_lib_common::getLL('MESSAGES_NOTIFICATION_MAIL_SUBJECT', array());
			$mail_body = tx_cwtcommunity_lib_common::getLL('MESSAGES_NOTIFICATION_MAIL_BODY', $vars);
			
			// Set mail sender
			$conf = tx_cwtcommunity_lib_common::getConfArray();
			$fromAddress = $conf['common.']['notification.']['mail.']['fromAddress'];
			$fromName = $conf['common.']['notification.']['mail.']['fromName'];
			$mail_headers = 'From: "'.$fromName.'" <'.$fromAddress.'>';			
			// Send mail
			@t3lib_div::plainMailEncoded($user['email'], $mail_subject, $mail_body, $mail_headers);
		}
    }
    
    /**
     * Checks if the private message notification emails are enabled.
     *
     * @return	boolean	True if enabled.
     */
    function isPrivateMessageNotificationEnabled() {
    	$conf = tx_cwtcommunity_lib_common::getConfArray();
    	if ($conf['messages.']['notification.']['enabled'] == '1') {
    		return true;	
    	}
    	return false;    	
    }    
    
    /**
    *  Gets all unread messages for a user.
    *
	*  @param	int		A FE User's uid.
	*  @return	array	An Array of 'tx_cwtcommunity_message' records.
    */
    public static function getNewMessages($uid) {
        $messages = tx_cwtcommunity_lib_common::dbQuery("SELECT tx_cwtcommunity_message.cruser_id, tx_cwtcommunity_message.crdate, tx_cwtcommunity_message.subject, tx_cwtcommunity_message.uid, tx_cwtcommunity_message.status, fe_users.username FROM tx_cwtcommunity_message, fe_users WHERE NOT tx_cwtcommunity_message.deleted = 1 AND NOT tx_cwtcommunity_message.hidden = 1 AND tx_cwtcommunity_message.cruser_id = fe_users.uid AND tx_cwtcommunity_message.fe_users_uid = ".intval($uid)." AND tx_cwtcommunity_message.status = 0 ORDER BY crdate DESC");
        return $messages;
    }
    
    /**
    *  Gets all the messages for a user.
    *
	*  @param	int		A FE User's uid.
	*  @return	array	An Array of 'tx_cwtcommunity_message' records.
    */
    function getMessages($uid) {
        $messages = tx_cwtcommunity_lib_common::dbQuery("SELECT tx_cwtcommunity_message.cruser_id, tx_cwtcommunity_message.crdate, tx_cwtcommunity_message.subject, tx_cwtcommunity_message.uid, tx_cwtcommunity_message.status, fe_users.username FROM tx_cwtcommunity_message, fe_users WHERE NOT tx_cwtcommunity_message.deleted = 1 AND NOT tx_cwtcommunity_message.hidden = 1 AND tx_cwtcommunity_message.cruser_id = fe_users.uid AND tx_cwtcommunity_message.fe_users_uid = ".intval($uid)." ORDER BY crdate DESC");
        for ($i = 0; $i < sizeof($messages); $i++) {
        	$messages[$i]['cruser'] = tx_cwtcommunity_lib_common::getUser($messages[$i]['cruser_id']);
        }
        return $messages;
    }
    
    /**
     * Get a single message.
     * @param int		UID of the message.
     * @return array	An array of the 'tx_cwtcommunity_message' record.
     */
    
	function getMessage($uid) {
		$message = tx_cwtcommunity_lib_common::dbQuery("SELECT * FROM tx_cwtcommunity_message WHERE NOT deleted = 1 AND NOT hidden = 1 AND uid ='".intval($uid)."';");
		$message = $message[0];
		return $message;    	
	}
	
	/**
    *  Delete a single message.
    *
	*  @param	int		The message's uid.
	*  @return	void
    */
    function deleteMessage($msg_uid) {
        $conf = tx_cwtcommunity_lib_common::getConfArray();
			if ($conf['messages.']['softDeletion.']['enabled'] == 1) {
				//soft delete
				$res = tx_cwtcommunity_lib_common::dbUpdateQuery("UPDATE tx_cwtcommunity_message SET deleted = 1 WHERE uid = ".intval($msg_uid));
			} else {
				$res = tx_cwtcommunity_lib_common::dbUpdateQuery("DELETE FROM tx_cwtcommunity_message WHERE uid = ".intval($msg_uid));
			}
        return null;
    }
	

    /**
    *  Gets one message for a user and sets the status to 'read' = 1
    *
	*  @param	int		A FE User's uid.
    *  @param	int		The message's uid.
    *  @return	array	The resulting 'tx_cwtcommunity_message' record.
    */
    function getMessagesSingle($uid, $msg_uid) {
        $message = tx_cwtcommunity_lib_common::dbQuery("SELECT tx_cwtcommunity_message.cruser_id, tx_cwtcommunity_message.crdate, tx_cwtcommunity_message.subject, tx_cwtcommunity_message.body, tx_cwtcommunity_message.status, tx_cwtcommunity_message.uid, fe_users.username FROM tx_cwtcommunity_message, fe_users WHERE NOT tx_cwtcommunity_message.deleted = 1 AND NOT tx_cwtcommunity_message.hidden = 1 AND tx_cwtcommunity_message.uid = ".intval($msg_uid)." AND tx_cwtcommunity_message.fe_users_uid = ".intval($uid)." AND tx_cwtcommunity_message.cruser_id = fe_users.uid ORDER BY crdate DESC");
        $message = $message[0];
        
        // Fetch Sender information
        $message['cruser'] = tx_cwtcommunity_lib_common::getUser($message['cruser_id']);
        
        // Update the status
        $res = tx_cwtcommunity_lib_common::dbUpdateQuery("UPDATE tx_cwtcommunity_message SET status = 1 WHERE uid = ".intval($msg_uid));
        return $message;
    }
    
    
    public static function sendNotificationEmail($feuser_uids = array(), $mail_subject, $mail_body) {
    	if (is_array($feuser_uids)) {
    		for ($i = 0; $i < sizeof($feuser_uids); $i++) {
    			// Get users e-mail
    			$user = tx_cwtcommunity_lib_common::getUser($feuser_uids[$i]);
    			tx_cwtcommunity_lib_common::sendHtmlMailSwift($user['email'], $mail_subject, $mail_body, array(), array(), array());	
    		}
    	}
    }
    
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_community/res/class.tx_cwtcommunity_lib_abuse.php"]) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_community/res/class.tx_cwtcommunity_lib_abuse.php"]);
}
?>