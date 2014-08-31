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
include_once(t3lib_extMgm::extPath('cwt_feedit').'pi1/class.tx_cwtfeedit_pi1.php');
include_once(t3lib_extMgm::extPath('cwt_community').'res/class.tx_cwtcommunity_lib_constants.php');
include_once(t3lib_extMgm::extPath('cwt_community').'res/class.tx_cwtcommunity_lib_common.php');
include_once(t3lib_extMgm::extPath('cwt_community').'res/class.tx_cwtcommunity_lib_gallery.php');
include_once(t3lib_extMgm::extPath('cwt_community').'res/class.tx_cwtcommunity_lib_buddylist.php');
include_once(t3lib_extMgm::extPath('cwt_community').'res/class.tx_cwtcommunity_lib_messages.php');
include_once(t3lib_extMgm::extPath('cwt_community').'res/class.tx_cwtcommunity_lib_search.php');
include_once(t3lib_extMgm::extPath('cwt_community').'res/class.tx_cwtcommunity_lib_userlist.php');
include_once(t3lib_extMgm::extPath('cwt_community').'res/class.tx_cwtcommunity_lib_abuse.php');
include_once(t3lib_extMgm::extPath('cwt_community').'res/class.tx_cwtcommunity_lib_profile.php');
include_once(t3lib_extMgm::extPath('cwt_community').'res/class.tx_cwtcommunity_lib_guestbook.php');
include_once(t3lib_extMgm::extPath('cwt_community').'res/class.tx_cwtcommunity_lib_welcome.php');
include_once(t3lib_extMgm::extPath('cwt_community').'res/class.tx_cwtcommunity_lib_wall.php');
include_once(t3lib_extMgm::extPath('cwt_community').'res/class.tx_cwtcommunity_lib_misc.php');

/**
 * Plugin 'CWT Community' for the 'cwt_community' extension.
 * 
 * @author	Sebastian Faulhaber <sebastian.faulhaber@gmx.de>
 * @package TYPO3
 * @subpackage	tx_cwtcommunity
 */
class tx_cwtcommunity_pi1 extends tslib_pibase {
	public $prefixId = tx_cwtcommunity_lib_constants::CONST_PREFIX_ID;
	// Path to this script relative to the extension dir.
	public $scriptRelPath = "pi1/class.tx_cwtcommunity_pi1.php";
	// The extension key.
	public $extKey = "cwt_community";
	// Reference to the calling cObj
	public $cObj;
	// Holds template code.
	private $orig_templateCode = null;
	// Contains the flexform configuration for the plugin.
	private $flexform = null; 
	// Switch for customer marker substitution
	private $enableCustomMarkerSubstitution = true;
	
	/**
	 * The main method of the Plugin
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
    function main($content, $conf) {
    	$this->conf = $conf;
        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();
        
       	/*
		 * INIT FLEXFORM
		 * Init config flexform, the flexform config can now be overwritten by extensions calling a cwt_community object.
		 */
		if (!$this->conf['tsFlex']) {
    		$this->pi_initPIflexForm();
    		$this->flexform = $this->cObj->data['pi_flexform'];       
		} else {
    		$this->flexform = $this->conf['tsFlex'];
    	}
    	$CODE = $this->pi_getFFvalue($this->flexform, 'field_code', 'sDEF');
		$action = t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_ACTION);
		
    	/**
    	 * INIT DEBUGGING OPTIONS
    	 */
    	tx_cwtcommunity_lib_constants::$DEBUG_SQL_QUERIES = $this->pi_getFFvalue($this->flexform, 'show_sql_queries', 'debug');
    	tx_cwtcommunity_lib_constants::$DEBUG_TS_CONFIGURATION = $this->pi_getFFvalue($this->flexform, 'show_ts_configuration', 'debug');

		/*
		 * INIT SMARTY INSTANCE
		 */
		$smartyInstance = tx_smarty::smarty();        
		tx_cwtcommunity_lib_common::init($conf, $this, $smartyInstance);
        
        /**
    	 * INIT XHR API
    	 */		
		if ($CODE == 'XHR_API') {
			// Send special header
			header('Content-Type: application/json; charset=utf-8', true);
			header('Cache-Control: no-cache', true); 
			header('Pragma: no-cache', true);
			header('Expires: -1', true); 
			
			// Extract data from POST data
			$header = t3lib_div::_GET('header');
			$data = t3lib_div::_GET('data');
			$module = $header['module'];
			$func = $header['func'];

			// Call method
			if ($module == 'tx_cwtcommunity_lib_wall') {
				
				if ($func == 'getWallEntriesForUser') {
					$res = tx_cwtcommunity_lib_wall::getWallEntriesForUser($data['owner_uid']);
					
				} elseif ($func == 'addWallEntryText') {
					$res = tx_cwtcommunity_lib_wall::addWallEntryText($data['owner_uid'], $data['content_text']);
					
				} elseif ($func == 'deleteWallEntry') {
					$res = tx_cwtcommunity_lib_wall::deleteWallEntry($data['entry_uid']);
					
				}
				
			} else if ($module == 'tx_cwtcommunity_lib_search') {
				
				if ($func == 'executeCustomExtendedSearch') {
					$searcharray = $data['searchArray'];
					$sortOrder = $data['sortOrder'];
					$sortColumn = $data['sortColumn'];
					$page = $data['page'];
					$limitFeUserFields = $data['limitFeUserFields'];
					
					$res['users'] = tx_cwtcommunity_lib_search::executeCustomExtendedSearch($searcharray, $sortOrder, $sortColumn, $page, $limitFeUserFields);
					$res['usersForDisplay'] = tx_cwtcommunity_lib_common::getUsersForDisplay($res['users']);
					
				} else if ($func == 'executeCustomSimpleSearch') {
					$searchstring = $data['searchString'];
					$sortOrder = $data['sortOrder'];
					$sortColumn = $data['sortColumn'];
					$page = $data['page'];
					$limitFeUserFields = $data['limitFeUserFields'];
					
					$res['users'] = tx_cwtcommunity_lib_search::executeCustomSimpleSearch($searchstring, $sortOrder, $sortColumn, $page, $limitFeUserFields);
					$res['usersForDisplay'] = tx_cwtcommunity_lib_common::getUsersForDisplay($res['users']);					
				}
				
			} else if ($module == 'tx_cwtcommunity_lib_messages') {
				
				if ($func == 'sendNotificationEmail') {
					$feuser_uids = $data['feuser_uids'];
					$mail_subject = $data['mail_subject'];
					$mail_body = $data['mail_body'];
					
					$res = tx_cwtcommunity_lib_messages::sendNotificationEmail($feuser_uids, $mail_subject, $mail_body);
				} 
				
			} else if ($module == 'tx_cwtcommunity_lib_misc') {
				
				if ($func == 'getSeminars') {
					$res = tx_cwtcommunity_lib_misc::getSeminars();
				} 
				
			} else {
				exit('FATAL ERROR: COULD NOT FIND METHOD.'); 
			}
 
			// Encode response
			print json_encode($res);
			
			exit();
		}
		
		
		/*
		 * INIT JQUERY
		 */
		if ($conf['jquery.']['enabled'] == 'true') {
			$path = $conf['jquery.']['path'];
			$themePath = $conf['jquery.']['themePath'];
			tx_cwtcommunity_lib_common::addAdditionalHeaderData('jquery.js', '<script src="'.$path.'/jquery.js" type="text/javascript"></script>');
			tx_cwtcommunity_lib_common::addAdditionalHeaderData('jquery01.js', '<script src="'.$path.'/ui/ui.core.js" type="text/javascript"></script>');
			tx_cwtcommunity_lib_common::addAdditionalHeaderData('jquery02.js', '<script src="'.$path.'/ui/ui.dialog.js" type="text/javascript"></script>');
			tx_cwtcommunity_lib_common::addAdditionalHeaderData('jquery03.js', '<script src="'.$path.'/ui/ui.draggable.js" type="text/javascript"></script>');
			tx_cwtcommunity_lib_common::addAdditionalHeaderData('jquery04.js', '<script src="'.$path.'/ui/ui.resizable.js" type="text/javascript"></script>');
			tx_cwtcommunity_lib_common::addAdditionalHeaderData('theme', '<link rel="stylesheet" href="'.$themePath.'/ui.all.css" type="text/css">');			
		}
		
        // Kill duplicate user sessions
		if ($conf['common.']['killDuplicateUserSessions'] == '1'){
			tx_cwtcommunity_lib_common::killDuplicateUserSessions();
		}		
		
        if (tx_cwtcommunity_lib_constants::$DEBUG_TS_CONFIGURATION) {
			debug($this->flexform, 'FLEXFORM');
			debug($conf, 'CONF');
        }
                
        // Initialize new cObj 
        $cObj = t3lib_div::makeInstance('tslib_cObj');
        $this->cObj = $cObj;

        
        /*
		* CONTROLLER
		*/
		if ($CODE == null) {
			// SO what....here should go some help text, which describes what CODE values to use.
			$content .= tx_cwtcommunity_lib_common::getLL('CWT_CODE_ERR_MSG');
			
		} elseif ($CODE == 'WALL') {
                // TODO: Fix GET/POST var access.
                $uid = t3lib_div::_GP("uid");
                if ($uid == null) {
                    $uid = tx_cwtcommunity_lib_common::getLoggedInUserUID();
                }
                
				// Generate the view
                $content .= tx_cwtcommunity_lib_wall::getViewWall($uid);
			
		} elseif ($CODE == 'ABUSE_REPORT') {
			// Get action
			$action = t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_ACTION);
			$submitPressed = t3lib_div::_GP(tx_cwtcommunity_lib_constants::CMD_SEND);
			$reason = t3lib_div::_GP('reason');
			$email = t3lib_div::_GP('email');
			$url = t3lib_div::_GP('url');
                
			if ($reason != null && $submitPressed != null) {
				// UID of fe user logged in
				$user_uid = tx_cwtcommunity_lib_common::getLoggedInUserUID();
				// Insert into db
				$res = tx_cwtcommunity_lib_abuse::insertAbuseData($user_uid, $reason, $email, $url);
				//Send mail notification if enabled
				if ($conf['abuse.']['notification.']['isEnabled'] == '1') {
					tx_cwtcommunity_lib_abuse::sendAbuseNotification($reason, $email, $url);
				}
				
				$content .= tx_cwtcommunity_lib_abuse::getViewAbuseReport($reason, $email, $url, true);
			} elseif ($submitPressed != null) {
				// DISPLAY INPUT FORM
				$content .= tx_cwtcommunity_lib_common::generateErrorMessage(tx_cwtcommunity_lib_common::getLL('ABUSE_TEXT_ERRMSG'));
				$content .= tx_cwtcommunity_lib_abuse::getViewAbuseReport($reason, $email, $url, false);
			} else {
				// DISPLAY INPUT FORM
				$content .= tx_cwtcommunity_lib_abuse::getViewAbuseReport($reason, $email, $url, false);
			}
			
		} elseif ($CODE == "WELCOME")	{
			// Get the user id
			$session_user_uid = tx_cwtcommunity_lib_common::getLoggedInUserUID();
			// Get the user information
			$user_info = tx_cwtcommunity_lib_welcome::getUserInfo($session_user_uid);
			// Generate the view
			$content .= tx_cwtcommunity_lib_welcome::getViewWelcome($user_info);
		
		} elseif ($CODE == "MESSAGES") {
			//Now check, if the user views his own messages
			$session_user_uid = tx_cwtcommunity_lib_common::getLoggedInUserUID();
			// Read the file
			$this->orig_templateCode = $this->cObj->fileResource($conf["template_messages"]);
			//Get action
			$action = t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_ACTION);
			//Check for post vars
			$submitPressed = $this->piVars["submit_button"];
			$cancelPressed = $this->piVars["cancel_button"];

			//Decide what to do
			if ($action == null || $action == "getviewmessages" || $cancelPressed != null){
				//Get the model
				$messages = tx_cwtcommunity_lib_messages::getMessages($session_user_uid);
				//Generate the view
				$content .= tx_cwtcommunity_lib_messages::getViewMessages($messages);
			} elseif ($action == "getviewmessagesdelete"){
				//Get the msg uid
				$msg_uid = t3lib_div::_GP('msg_uid');
				$msg = tx_cwtcommunity_lib_messages::getMessage($msg_uid);
				//Sanity check
				if ($msg['fe_users_uid'] != tx_cwtcommunity_lib_common::getLoggedInUserUID()) {
					return tx_cwtcommunity_lib_common::generateErrorMessage('An unknown error has occured. Please contact your system administrator.');
				} else {
					//Delete the message						
					$res = tx_cwtcommunity_lib_messages::deleteMessage($msg_uid);
					//Get the model
					$messages = tx_cwtcommunity_lib_messages::getMessages($session_user_uid);
					//Generate the view
					$content .= tx_cwtcommunity_lib_messages::getViewMessages($messages);						                    
				}

			} elseif (	$action == tx_cwtcommunity_lib_constants::ACTION_MESSAGES_SHOW_ANSWER_MSG || 
						$action == tx_cwtcommunity_lib_constants::ACTION_MESSAGES_SHOW_NEW_MSG){
				//Get the recipient uid
				$recipient_uid = t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_RECIPIENT_UID);
				$answer_uid = t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_ANSWER_UID);
				$subject = t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_SUBJECT);
				$body = t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_BODY);
				$recipients = t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_RECIPIENTS);
				$recipients_all_buddies = t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_RECIPIENTS_ALL_BUDDIES);
				$action == tx_cwtcommunity_lib_constants::ACTION_MESSAGES_SHOW_NEW_MSG ? $mode = tx_cwtcommunity_lib_constants::ACTION_MESSAGES_SHOW_NEW_MSG : $mode = tx_cwtcommunity_lib_constants::ACTION_MESSAGES_SHOW_ANSWER_MSG;
                        
				// user wants to submit
				if ($submitPressed != null){
					$allUsersValid = true;
					// Explode recipients if necessary
					if ($mode == tx_cwtcommunity_lib_constants::ACTION_MESSAGES_SHOW_NEW_MSG && $recipients_all_buddies == null) {
						$users = array();
						strpos($recipients, $this->conf['messages.']['new.']['recipient_delimiter']) === false ? $users[] = $recipients : $users = explode($this->conf['messages.']['new.']['recipient_delimiter'], $recipients);
						// Check if all users can be found
						for ($i = 0; $i < sizeof($users); $i++) {
							$user = tx_cwtcommunity_lib_common::getUserByName($users[$i]);
							if ($user == null) {
								$allUsersValid = false;
								$content .= tx_cwtcommunity_lib_common::generateErrorMessage(tx_cwtcommunity_lib_common::getLL('COMMON_USER_NOT_FOUND', array($users[$i])));
							} else {
								$users[$i] = $user; 
							}
							// Maximum number of recipients reached
							if ($i+1 > intval($this->conf['messages.']['new.']['max_recipients'])) {
								$content .= tx_cwtcommunity_lib_common::generateErrorMessage(tx_cwtcommunity_lib_common::getLL('MESSAGES_MAXIMUM_RECIPIENTS', array($this->conf['messages.']['new.']['max_recipients'])));
								$allUsersValid = false;											 
							}						    		
						}
					} else if ($mode == tx_cwtcommunity_lib_constants::ACTION_MESSAGES_SHOW_NEW_MSG && $recipients_all_buddies != null) {
						// Get buddies
						$users = array();
						$buddies = tx_cwtcommunity_lib_buddylist::getBuddylist(tx_cwtcommunity_lib_common::getLoggedInUserUID());
						for ($i=0; $i < sizeof($buddies); $i++) {
							$users[] = tx_cwtcommunity_lib_common::getUser($buddies[$i]['buddy_uid']);
						}
					}           
					// Sanity Checks
					if ( ($subject != null && $body != null) && ( ($mode == tx_cwtcommunity_lib_constants::ACTION_MESSAGES_SHOW_NEW_MSG && $allUsersValid == true) || ($mode == tx_cwtcommunity_lib_constants::ACTION_MESSAGES_SHOW_ANSWER_MSG) ) ) {
						// Is notification email enabled
						$isNotificationEnabled = tx_cwtcommunity_lib_messages::isPrivateMessageNotificationEnabled();
						if ($mode == tx_cwtcommunity_lib_constants::ACTION_MESSAGES_SHOW_NEW_MSG) {
							for ($i = 0; $i < sizeof($users); $i++) {
								tx_cwtcommunity_lib_common::sendPrivateMessage($session_user_uid, $users[$i]['uid'], $subject, $body);
								if ($isNotificationEnabled) {
									tx_cwtcommunity_lib_messages::sendPrivateMessageNotification($users[$i]['uid'], null);
								}
							}
						} else {
							// Send msg
							tx_cwtcommunity_lib_common::sendPrivateMessage($session_user_uid, $recipient_uid, $subject, $body);
							if ($isNotificationEnabled) {
								tx_cwtcommunity_lib_messages::sendPrivateMessageNotification($recipient_uid, null);
							}	                                                         			
						}
						// Display result view
						$content .= tx_cwtcommunity_lib_messages::getViewMessages(null, null, $session_user_uid, $recipient_uid, "new_result");
					} else {
						// Not okay...display new view
						if ($subject == null || $body == null || ($mode == tx_cwtcommunity_lib_constants::ACTION_MESSAGES_SHOW_NEW_MSG && $recipients == null)) {
							$content .= tx_cwtcommunity_lib_common::generateErrorMessage(tx_cwtcommunity_lib_common::getLL('COMMON_FILL_OUT_ALL_REQUIRED_FIELDS'));
						}
						$content .= tx_cwtcommunity_lib_messages::getViewMessagesNew($session_user_uid, $recipient_uid, $subject, $body, $mode, "new", $recipients, $recipients_all_buddies, $action);
					}
				} else {
					if ($answer_uid != null){
						//Get the message from database
						$query = tx_cwtcommunity_lib_messages::getMessagesSingle($session_user_uid, $answer_uid);
						//Get the subject, of mail to answer
						$subject = tx_cwtcommunity_lib_common::getLL('CWT_REPLY_ABBREV').$query["subject"];
						//Get the bodytext
						$body = $this->conf['messages.']['new.']['reply.']['body_abbreviation'].$query["body"];
					}
					//Generate the view
					$content .= tx_cwtcommunity_lib_messages::getViewMessagesNew($session_user_uid, $recipient_uid, $subject, $body, $mode, "new", $recipients, $recipients_all_buddies, $action);
				}
			} elseif ($action == "getviewmessagessingle"){
				//Get the msg uid
				$msg_uid = t3lib_div::_GP("msg_uid");
				//Get the model
				$message = tx_cwtcommunity_lib_messages::getMessagesSingle($session_user_uid, $msg_uid);
				//Generate the view
				$content .= tx_cwtcommunity_lib_messages::getViewMessages(null, $message, $session_user_uid, null, "single");
			}
                
		} elseif ($CODE == "GUESTBOOK") {
			// Check, if the user views his own guestbook
			$session_user_uid = tx_cwtcommunity_lib_common::getLoggedInUserUID();
			// Is notification email enabled
			$isNotificationEnabled = tx_cwtcommunity_lib_guestbook::isGuestbookNotificationEnabled();
			
            // if no uid is given, then take session user uid
            $uid = t3lib_div::_GP("uid");
            if ($uid == null){
            	$uid = tx_cwtcommunity_lib_common::getLoggedInUserUID();
            }

			// FOR THE GUESTBOOK OWNER
			if ($session_user_uid == $uid) {
				// At first, check for POST vars
				// User wants to activate guestbook
				if ($this->piVars["open_guestbook"] != null) {
					// so...open it ;-)
				    $res = tx_cwtcommunity_lib_guestbook::openGuestbook($session_user_uid);
				} elseif($this->piVars["lock_guestbook"] != null){
					// User wants to close guestbook, so...lock it!
					$res = tx_cwtcommunity_lib_guestbook::lockGuestbook($session_user_uid);
				}

				// Now check, if the user has enabled his gb
				$status = tx_cwtcommunity_lib_guestbook::getGuestbookStatus($session_user_uid);
				// Then ...Check, if guestbook is locked or open
				if ($status == "0") {
					$isLocked = false;
				} elseif ($status == "1"){
					$isLocked = true;
				}

				// Then check for action, in case the user wants to delete something.
                // Get action
                $action = t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_ACTION);
				$item = t3lib_div::_GP("item");
				if ($action == "getviewguestbookdeleteitem") {
					// Delete guestbook item
					$res = tx_cwtcommunity_lib_guestbook::deleteGuestbookItem($item);
				} elseif ($action == "getviewguestbookdeleteall"){
					// Delete the whole guestbook
					$res = tx_cwtcommunity_lib_guestbook::deleteGuestbook($session_user_uid);
				}

        	    // Get the model->guestbook
  		        $guestbook = tx_cwtcommunity_lib_guestbook::getGuestbook($session_user_uid);
				//Generate the view
			    $content .= tx_cwtcommunity_lib_guestbook::getViewGuestbook($session_user_uid, $isLocked, $guestbook,'logged_in');
			} else{
				// FOR OTHER USERS
				// Check, if the user has enabled his gb
				$status = tx_cwtcommunity_lib_guestbook::getGuestbookStatus(t3lib_div::_GP("uid"));

				// ENABLED !! Everything is fine ;-)
				if ($status == "0") {
	                // Get action
                	$action = t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_ACTION);
                	// Decide what to do
            	    if ($action == "getviewguestbookadd") {
        	            // Get fe user uid
    	                $uid = t3lib_div::_GP("uid");
                    	$text = htmlspecialchars($this->piVars["text"]);
						$submitPressed = $this->piVars["submit_button"];
						$cancelPressed = $this->piVars["cancel_button"];
                	    // Check for add record
            	        // ADD RECORD
        	            if ($text != null && $submitPressed != null) {
    	                    // UID of fe user logged in
	                        $user_uid = tx_cwtcommunity_lib_common::getLoggedInUserUID();
                        	// Insert into db
                        	$res = tx_cwtcommunity_lib_guestbook::insertGuestbookData($user_uid, $text, $uid);
                        	if ($isNotificationEnabled) {
                        		tx_cwtcommunity_lib_guestbook::sendGuestbookNotification($uid);
                        	}
                    	    // Generate the view
                    	    $content .= tx_cwtcommunity_lib_guestbook::getViewGuestbook($uid,false,null,'add_result');
            	        } elseif ($submitPressed != null && $text == null){
            	        	// Generate Error message
            	        	$content .= tx_cwtcommunity_lib_common::generateErrorMessage(tx_cwtcommunity_lib_common::getLL('CWT_GUESTBOOK_TEXT_ERRMSG'));
							// Generate the view
                    	    $content .= tx_cwtcommunity_lib_guestbook::getViewGuestbook($uid,false,$guestbook,'add');
            	    	} elseif ($cancelPressed != null){
            	        	// CANCEL
							// Display normal guestbook view
		                    // Get fe user uid
                    		$uid = t3lib_div::_GP("uid");
                		    // Get the model->guestbook
            		        $guestbook = tx_cwtcommunity_lib_guestbook::getGuestbook($uid);
            		        // Generate the view
        	        	    $content .= tx_cwtcommunity_lib_guestbook::getViewGuestbook($uid,false,$guestbook,'');

						} else {
							// DISPLAY INPUT FORM
                        	// Generate the view
                    	    $content .= tx_cwtcommunity_lib_guestbook::getViewGuestbook($uid,false,$guestbook,'add');
                	    }
            	    } else {
		                    // Get fe user uid
                    		$uid = t3lib_div::_GP("uid");
                		    // Get the model->user
            		        $guestbook = tx_cwtcommunity_lib_guestbook::getGuestbook($uid);
        		            // Generate the view
    		                $content .= tx_cwtcommunity_lib_guestbook::getViewGuestbook($uid,false,$guestbook,'');
		                }
				} else{
					//Generate view
					$content .= tx_cwtcommunity_lib_guestbook::getViewGuestbook(null,false,null,'disabled');
				}
			}
			
            } elseif ($CODE == "PROFILE") {
                // If no FE User uid is given, then take session user uid
                // TODO: Fix GET/POST var access.
                $uid = t3lib_div::_GP("uid");
                if ($uid == null) {
                    $uid = tx_cwtcommunity_lib_common::getLoggedInUserUID();
                }
                $userRaw = tx_cwtcommunity_lib_common::getUser($uid);
				$user = tx_cwtcommunity_lib_common::getUserForDisplay($uid);
                
                // Sanity checks
				if (!tx_cwtcommunity_lib_common::isUserActive($uid)) {	
					$content = tx_cwtcommunity_lib_common::generateErrorMessage(tx_cwtcommunity_lib_common::getLL('COMMON_USER_DELETED'));
				} else {
					// Increase profile view counter
					if ($uid != tx_cwtcommunity_lib_common::getLoggedInUserUID()) {
						tx_cwtcommunity_lib_profile::increaseProfileViewCounter($uid);
					}
					// Generate the view
	                $content .= tx_cwtcommunity_lib_profile::getViewProfile($userRaw, $user);
				}
			
            } elseif ($CODE == "PROFILE_MINI") {
                $uid = t3lib_div::_GP("uid");
                if ($uid == null) {
                    $uid = tx_cwtcommunity_lib_common::getLoggedInUserUID();
                }
                $userRaw = tx_cwtcommunity_lib_common::getUser($uid);
				$user = tx_cwtcommunity_lib_common::getUserForDisplay($uid);
                // Sanity checks
			if (!tx_cwtcommunity_lib_common::isUserActive($uid)) {
				$content = tx_cwtcommunity_lib_common::generateErrorMessage(tx_cwtcommunity_lib_common::getLL('COMMON_USER_DELETED'), array());
			} else {
				// Generate the view
                $content .= tx_cwtcommunity_lib_profile::getViewProfileMini($userRaw, $user);
			}
			
		} elseif ($CODE == "GENERICS_PROFILE") {
				// Get FF configuration
				$tsConfigRef = $this->pi_getFFvalue($this->flexform, 'ts_configuration', 'generic_views_profile');
				$config = $conf['generics.']['profile.'][$tsConfigRef.'.'];
				
				$uid = t3lib_div::_GP("uid");
                if ($uid == null) {
                    $uid = tx_cwtcommunity_lib_common::getLoggedInUserUID();
                }
                $userRaw = tx_cwtcommunity_lib_common::getUser($uid);
                $user = tx_cwtcommunity_lib_common::getUserForDisplay($uid);
				
				$content .= tx_cwtcommunity_lib_profile::getViewProfileGeneric($user, $userRaw, $config);

			
            } elseif ($CODE == "PROFILE_USERSTATS") {
		    // If no FE User uid is given, then take session user uid
            // TODO: Fix GET/POST var access.
            $uid = t3lib_div::_GP("uid");
            if ($uid == null) {
            	$uid = tx_cwtcommunity_lib_common::getLoggedInUserUID();
            }
            $user = tx_cwtcommunity_lib_common::getUserWithStats($uid);

            // Sanity checks
			if (!tx_cwtcommunity_lib_common::isUserActive($uid)) {	
				$content = tx_cwtcommunity_lib_common::generateErrorMessage(tx_cwtcommunity_lib_common::getLL('COMMON_USER_DELETED'));
			} else {
				// Generate the view
                $content .= tx_cwtcommunity_lib_profile::getViewProfileUserstats($user);
			}
			
            } elseif ($CODE == "SEARCH") {
			// Display search box
			$content .= tx_cwtcommunity_lib_search::getViewSearch('simple');	
			
            }  elseif ($CODE == "SEARCH_EXTENDED") {
              // Get search result
			$searcharray = t3lib_div::_GP('searchtext');
			
			if ($searcharray != null) {
				// excecute search
				$users = tx_cwtcommunity_lib_search::executeExtendedSearch($searcharray);
				$usersForDisplay = tx_cwtcommunity_lib_common::getUsersForDisplay($users);
				$pageCount = tx_cwtcommunity_lib_common::getPageCount(sizeof($users), $conf['userlist.']['paging.']['usersPerPage']); 
				
				// generate view for search result
				$search_result_content = tx_cwtcommunity_lib_userlist::getViewUserlist($users, $usersForDisplay, true, 1, $pageCount);
				// Generate the view
				$content .= tx_cwtcommunity_lib_search::getViewSearch('extended', '', sizeof($users), $searcharray, true, $search_result_content);										
			} else {
				// Display search box
				$content .= tx_cwtcommunity_lib_search::getViewSearch('extended', '', 0, $searcharray);
			}
                
            } elseif ($CODE == 'BUDDYLIST' || $CODE == 'BUDDYADMIN') {
                //Get the uid of logged in user
                $uid = tx_cwtcommunity_lib_common::getLoggedInUserUID();
                //Get action
                $action = t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_ACTION);
                $cmd = t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_CMD);
                $approval_uid = t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_APPROVAL_UID);
                $approvalsReceived = tx_cwtcommunity_lib_buddylist::getBuddylistApprovalsReceived($uid);
				$approvalsSent = tx_cwtcommunity_lib_buddylist::getBuddylistApprovalsSent($uid);
					
                //Decide what to do
                if ($action == null || $action == tx_cwtcommunity_lib_constants::ACTION_BUDDYLIST_SHOW_LIST) {
                // Check for commands
					if ($cmd != null && $cmd != '') {
						// Sanity check
						$approval = tx_cwtcommunity_lib_buddylist::getBuddylistApproval($approval_uid);
						if ($approval['target_uid'] != tx_cwtcommunity_lib_common::getLoggedInUserUID()) {
							return tx_cwtcommunity_lib_common::generateErrorMessage('An unknown error has occured. Please contact your system administrator.');
						}
						if ($cmd == tx_cwtcommunity_lib_constants::CMD_BUDDYLIST_ACCEPT_APPROVAL) {
							// Accept approval
							tx_cwtcommunity_lib_buddylist::acceptBuddylistApproval($approval_uid);
							
						} elseif ($cmd == tx_cwtcommunity_lib_constants::CMD_BUDDYLIST_DENY_APPROVAL) {
							// Deny approval
							tx_cwtcommunity_lib_buddylist::denyBuddylistApproval($approval_uid);
						}
					}
					
					// Determine the view to show
					switch ($CODE) {
						case 'BUDDYLIST':
		                    $buddylist = tx_cwtcommunity_lib_buddylist::getBuddylist($uid);
		                    $content .= tx_cwtcommunity_lib_buddylist::getViewBuddylist($buddylist, false, $approvalsReceived, $approvalsSent);
							break;
						case 'BUDDYADMIN':
							$content .= tx_cwtcommunity_lib_buddylist::getViewBuddyadmin($approvalsReceived, $approvalsSent);
							break;
					}

                } elseif ($action == "getviewbuddylistadd") {
                    //get buddy uid, which should be added
                    // TODO: Fix GET/POST var access.
                    $buddy_uid = t3lib_div::_GP("buddy_uid");
                    $message = t3lib_div::_GP("message");
                    $requestor_uid = tx_cwtcommunity_lib_common::getLoggedInUserUID();
                    
                    // Check if buddylist approval is enabled
					$isEnabled = tx_cwtcommunity_lib_buddylist::isBuddylistApprovalEnabled();
					if ($isEnabled) {
						// Create buddylist approval
						tx_cwtcommunity_lib_buddylist::createBuddylistApproval(tx_cwtcommunity_lib_common::getLoggedInUserUID(), $buddy_uid, $message);
						$content .= tx_cwtcommunity_lib_common::generateInfoMessage(tx_cwtcommunity_lib_common::getLL('WELCOME_VIEW_BUDDY_APPROVAL_NOTICE', array()));
						
						// Is notification email enabled
						$isNotificationEnabled = tx_cwtcommunity_lib_buddylist::isBuddylistNotificationEnabled();
						if ($isNotificationEnabled) {
							tx_cwtcommunity_lib_buddylist::sendBuddylistNotification($buddy_uid, $requestor_uid);
						}
					} else {
	                    // Add it to list
	                    $res = tx_cwtcommunity_lib_buddylist::addBuddy($uid, $buddy_uid);		
					}

					if ($conf['buddylist.']['approval'] == '0') {
                    	$buddylist = tx_cwtcommunity_lib_buddylist::getBuddylist($uid);
						$content .= tx_cwtcommunity_lib_buddylist::getViewBuddylist($buddylist, false, $approvalsReceived, $approvalsSent);
					} else {
						$content .= tx_cwtcommunity_lib_buddylist::getViewBuddyadmin($approvalsReceived, $approvalsSent);	
					}
					
					
                } elseif($action == "getviewbuddylistdelete"){
                    //get buddy uid, which should be added
                    $buddy_uid = t3lib_div::_GP("buddy_uid");
                    //Add it to list
                    $res = tx_cwtcommunity_lib_buddylist::deleteBuddy($uid, $buddy_uid);
                    //Get the model
                    $buddylist = tx_cwtcommunity_lib_buddylist::getBuddylist($uid);
                    //Generate the view
                    $content .= tx_cwtcommunity_lib_buddylist::getViewBuddylist($buddylist, false, $approvalsReceived, $approvalsSent);
                }
            } elseif ($CODE == "USERLIST") {

                // Decide what to do
                if ($action == null || $action == "getviewuserlist") {
					$searchstring = t3lib_div::_GP('searchtext');
					$searcharray = t3lib_div::_GP('searcharray');
                	$letter = t3lib_div::_GP("letter");
					$sortOrder = t3lib_div::_GP(tx_cwtcommunity_lib_constants::ACTION_COMMON_SORT_ORDER);
					$sortColumn = t3lib_div::_GP(tx_cwtcommunity_lib_constants::ACTION_COMMON_SORT_COLUMN);
					$page = t3lib_div::_GP(tx_cwtcommunity_lib_constants::ACTION_COMMON_PAGE);
					$isSearchResult = false;
					
                    // Get the model->user
					if ($searchstring != null && $searchstring != '') {
						// Handle CustomSimpleSearch
						$users = tx_cwtcommunity_lib_search::executeCustomSimpleSearch($searchstring, $sortOrder, $sortColumn, $page);
						$userCount = tx_cwtcommunity_lib_search::getUserCountCustomSimpleSearch($searchstring);
						$isSearchResult = true;
						$pageCount = tx_cwtcommunity_lib_common::getPageCount($userCount, $conf['userlist.']['paging.']['usersPerPage']);
						
					} elseif ($searcharray != null && is_array($searcharray) && sizeof($searcharray) > 0) {
						// Handle CustomExtendedSearch
						$users = tx_cwtcommunity_lib_search::executeCustomExtendedSearch($searcharray, $sortOrder, $sortColumn, $page);
						$userCount = tx_cwtcommunity_lib_search::getUserCountCustomExtendedSearch($searcharray);
						$isSearchResult = true;
						$pageCount = tx_cwtcommunity_lib_common::getPageCount($userCount, $conf['userlist.']['paging.']['usersPerPage']);
						
					} else {
						$users = tx_cwtcommunity_lib_common::getUsersWithFilter($letter, $sortOrder, $sortColumn, $page);
						if ($letter != null && $letter != '') {
							$userCount = tx_cwtcommunity_lib_common::getUserCountWithFilter($letter);
							$pageCount = tx_cwtcommunity_lib_common::getPageCount(tx_cwtcommunity_lib_common::getUserCountWithFilter($letter), $conf['userlist.']['paging.']['usersPerPage']);
						} else {
							$userCount = tx_cwtcommunity_lib_common::getUserCount();
							$pageCount = tx_cwtcommunity_lib_common::getPageCount(tx_cwtcommunity_lib_common::getUserCount(), $conf['userlist.']['paging.']['usersPerPage']);							
						}
					}
					
                    $usersForDisplay = tx_cwtcommunity_lib_common::getUsersForDisplay($users);
                    
                    // Generate the view
                    $content .= tx_cwtcommunity_lib_userlist::getViewUserlist($users, $usersForDisplay, $isSearchResult, $page, $pageCount, $userCount);
                    
                } elseif ($action == tx_cwtcommunity_lib_constants::ACTION_SEARCH_SHOW_RESULT) {
	                // Get search result
					$searchstring = t3lib_div::_GP('searchtext');
                    // Get the model->user
                    $users = tx_cwtcommunity_lib_search::executeSimpleSearch($searchstring);
                    $usersForDisplay = tx_cwtcommunity_lib_common::getUsersForDisplay($users);
					$pageCount = tx_cwtcommunity_lib_common::getPageCount(sizeof($users), $conf['userlist.']['paging.']['usersPerPage']);
                    $userCount = sizeof($users);
					
                    // Generate the view
					$content .= tx_cwtcommunity_lib_search::getViewSearch('result', $searchstring, sizeof($users));
                    $content .= tx_cwtcommunity_lib_userlist::getViewUserlist($users, $usersForDisplay, true, 1, $pageCount, $userCount);
                }
            } elseif ($CODE == "GENERICS_USERLIST") {
				// Get FF configuration
				$tsConfigRef = $this->pi_getFFvalue($this->flexform, 'ts_configuration', 'generic_views_userlist');
				$config = $conf['generics.']['userlist.'][$tsConfigRef.'.'];
				$page = t3lib_div::_GP(tx_cwtcommunity_lib_constants::ACTION_COMMON_PAGE);
								
				// Check for action
				if ($action == tx_cwtcommunity_lib_constants::ACTION_USERLIST_GENERIC_SHOWLIST) {
					$config['action'] = tx_cwtcommunity_lib_constants::ACTION_USERLIST_GENERIC_SHOWLIST;
				} else {
					$config['action'] = tx_cwtcommunity_lib_constants::ACTION_USERLIST_GENERIC_SHOWTEASER;
				}
				
				// Include external class and make an instance
				$class = t3lib_div::getFileAbsFileName($config['class']);
				if (file_exists($class)) {
					require_once($class);
					$classObj = t3lib_div::makeInstance($config['className']);
					$users = $classObj->getUsers($config);
					$usersForDisplay = tx_cwtcommunity_lib_common::getUsersForDisplay($users);
					$pageCount = tx_cwtcommunity_lib_common::getPageCount(tx_cwtcommunity_lib_common::getUserCount(), $conf['userlist.']['paging.']['usersPerPage']);
					//FIXME: The Usercount method should be provided by generics implementation, since we don't know which users are displayed at this point.
					$userCount = tx_cwtcommunity_lib_common::getUserCount();
					$content .= tx_cwtcommunity_lib_userlist::getViewUserlistGeneric($users, $usersForDisplay, $config, $page, $pageCount, $userCount);
					
				} else {
					t3lib_div::sysLog('GENERICS_USERLIST: The class file "'.$class.'" could not be found!', $this->extKey, t3lib_div::SYSLOG_SEVERITY_ERROR);
				}
			
            } elseif ($CODE == "GALLERY") {
                // Decide what to do
            if ($action == null || $action == tx_cwtcommunity_lib_constants::ACTION_GALLERY_SHOW_ALBUM_LIST) {
            	// Sanity checks
				$owner_uid = t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_ALBUM_CRUSER_ID);	
            	if (!tx_cwtcommunity_lib_gallery::isGalleryActivated($owner_uid)) {
					return tx_cwtcommunity_lib_common::generateErrorMessage('An unknown error has occured. Please contact your system administrator.');
				}
				// Generate content
            	$content .= tx_cwtcommunity_lib_gallery::getViewAlbumList($owner_uid);
            		
            } elseif ($action == tx_cwtcommunity_lib_constants::ACTION_GALLERY_SHOW_ALBUM_EDIT) {
            	// Sanity check
				$album = tx_cwtcommunity_lib_gallery::getAlbum(t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_ALBUM_UID));
				if ($album['cruser_id'] != tx_cwtcommunity_lib_common::getLoggedInUserUID()) {
					return tx_cwtcommunity_lib_common::generateErrorMessage('An unknown error has occured. Please contact your system administrator.');
				}
				// Generate content
				$content .= tx_cwtcommunity_lib_gallery::getViewAlbumEdit(t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_ALBUM_UID), $this, $conf);
            	            		
            } elseif ($action == tx_cwtcommunity_lib_constants::ACTION_GALLERY_SHOW_ALBUM_DETAIL) {
         		// Sanity checks
				$album = tx_cwtcommunity_lib_gallery::getAlbum(t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_ALBUM_UID));
				// Is gallery activated?
            	if (!tx_cwtcommunity_lib_gallery::isGalleryActivated($album['cruser_id'])) {
					return tx_cwtcommunity_lib_common::generateErrorMessage('An unknown error has occured. Please contact your system administrator.');
				}
				// Does the user have sufficient access rights?
				if (!tx_cwtcommunity_lib_gallery::mayUserAccessAlbum($album['uid'], tx_cwtcommunity_lib_common::getLoggedInUserUID())) {
					return tx_cwtcommunity_lib_common::generateErrorMessage('An unknown error has occured. Please contact your system administrator.');					
				}
				
				// Handle commands
				if ($album['cruser_id'] == tx_cwtcommunity_lib_common::getLoggedInUserUID()) {
					$cmd = t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_CMD);
					// Delete command
					if ($cmd == tx_cwtcommunity_lib_constants::CMD_GALLERY_DELETE_PHOTO) {
						tx_cwtcommunity_lib_gallery::deletePhoto(t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_PHOTO_UID), $this->conf);
					}
					// Set preview command
					if ($cmd == tx_cwtcommunity_lib_constants::CMD_GALLERY_SET_PREVIEW_PHOTO) {
						tx_cwtcommunity_lib_gallery::setPhotoAsPreview(t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_PHOTO_UID), $album['uid']);
					}
				}
				
				// Generate content
            	$content .= tx_cwtcommunity_lib_gallery::getViewAlbumDetail($album['uid']);
            	            		
            } elseif ($action == tx_cwtcommunity_lib_constants::ACTION_GALLERY_SHOW_ALBUM_DELETE) {
                // Sanity check
				$album = tx_cwtcommunity_lib_gallery::getAlbum(t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_ALBUM_UID));
				if ($album['cruser_id'] != tx_cwtcommunity_lib_common::getLoggedInUserUID()) {
					return tx_cwtcommunity_lib_common::generateErrorMessage('An unknown error has occured. Please contact your system administrator.');
				}            		
				// Check if album has photos
				$deleteFlag = t3lib_div::_GP(tx_cwtcommunity_lib_constants::CMD_GALLERY_DELETE_ALBUM);
				$cancelFlag = t3lib_div::_GP(tx_cwtcommunity_lib_constants::CMD_CANCEL);
				if (tx_cwtcommunity_lib_gallery::getPhotoCount($album['uid']) > 0 
					&& ($deleteFlag == null || $deleteFlag == '') && ($cancelFlag == '' || $cancelFlag == null)) {
					// Show confirmation page
					$content .= tx_cwtcommunity_lib_gallery::getViewAlbumDelete($album['uid']);						
				} elseif ($cancelFlag != '' || $cancelFlag != null) {
					$content .= tx_cwtcommunity_lib_gallery::getViewAlbumList($album['cruser_id']);
				} else {
					// Delete album and show album list
					tx_cwtcommunity_lib_gallery::deleteAlbumCascading($album['uid']);
					$content .= tx_cwtcommunity_lib_gallery::getViewAlbumList($album['cruser_id']);
				}
				
            } elseif ($action == tx_cwtcommunity_lib_constants::ACTION_GALLERY_SHOW_ALBUM_NEW) {
                // Sanity check
				$feuser_uid = t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_ALBUM_CRUSER_ID);
				if ($feuser_uid != tx_cwtcommunity_lib_common::getLoggedInUserUID()) {
					return tx_cwtcommunity_lib_common::generateErrorMessage('An unknown error has occured. Please contact your system administrator.');
				}		
				// Check if threshold will exceed
				if (tx_cwtcommunity_lib_gallery::getAlbumCount($feuser_uid) >= $this->conf['album.']['new.']['maximumPerUser']) {
					$content = tx_cwtcommunity_lib_common::generateErrorMessage(tx_cwtcommunity_lib_common::getLL('ALBUM_NEW_LIMIT_EXCEEDED'));
					$content .= tx_cwtcommunity_lib_gallery::getViewAlbumList($feuser_uid);
					return $content;			
				}
				// Generate the content
				$content .= tx_cwtcommunity_lib_gallery::getViewAlbumNew($this, $conf);

            } elseif ($action == tx_cwtcommunity_lib_constants::ACTION_GALLERY_SHOW_PHOTO_DETAIL) {
         		// Sanity checks
				$photo = tx_cwtcommunity_lib_gallery::getPhoto(t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_PHOTO_UID));
				// Is gallery activated?
            	if (!tx_cwtcommunity_lib_gallery::isGalleryActivated($photo['cruser_id'])) {
					return tx_cwtcommunity_lib_common::generateErrorMessage('An unknown error has occured. Please contact your system administrator.');
				}
				// Does the user have sufficient access rights?
				if (!tx_cwtcommunity_lib_gallery::mayUserAccessAlbum($photo['album_uid'], tx_cwtcommunity_lib_common::getLoggedInUserUID())) {
					return tx_cwtcommunity_lib_common::generateErrorMessage('An unknown error has occured. Please contact your system administrator.');					
				}
				
				// Handle commands
            	$cmd = t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_CMD);
				// Add comment            		
				if ($cmd == tx_cwtcommunity_lib_common::getLL('FORM_COMMENT_ADD')) {
					// Sanity check
					$comment = t3lib_div::_GP('TEXT');
					if ($comment != null && $comment != '') {
						tx_cwtcommunity_lib_gallery::addComment($photo['uid'], htmlspecialchars($comment));
					}
				}
				// 	Delete comment					
				if ($photo['cruser_id'] == tx_cwtcommunity_lib_common::getLoggedInUserUID()) {
					if ($cmd == tx_cwtcommunity_lib_constants::CMD_GALLERY_DELETE_COMMENT) {		
						tx_cwtcommunity_lib_gallery::deleteComment(t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_COMMENT_UID));
					}						
				}

				// Generate content
            	$content .= tx_cwtcommunity_lib_gallery::getViewPhotoDetail($photo['uid']);
            	
            } elseif ($action == tx_cwtcommunity_lib_constants::ACTION_GALLERY_SHOW_PHOTO_NEW) {
            	// Sanity check
				$album = tx_cwtcommunity_lib_gallery::getAlbum(t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_ALBUM_UID));
				// Check if owner
				if ($album['cruser_id'] != tx_cwtcommunity_lib_common::getLoggedInUserUID()) {
					return tx_cwtcommunity_lib_common::generateErrorMessage('An unknown error has occured. Please contact your system administrator.');
				}
				// Generate content
				$cmd = t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_CMD);
				if ($cmd == tx_cwtcommunity_lib_common::getLL('FORM_CANCEL_BUTTON')) {
					$content .= tx_cwtcommunity_lib_gallery::getViewAlbumDetail($album['uid']);
					
				} elseif ($cmd == tx_cwtcommunity_lib_common::getLL('FORM_BUTTON_UPLOAD')) {
					// Process the uploaded files
					$errors = tx_cwtcommunity_lib_gallery::processUploadedFiles($album['uid'], $this->conf);
					// If there are any errors go back to upload form
					if ($errors != null) {
						$content .= $errors;
						$content .= tx_cwtcommunity_lib_gallery::getViewPhotoNew(t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_ALBUM_UID)); 
					} else {
						// Go back to album detail
						$content .= tx_cwtcommunity_lib_gallery::getViewAlbumDetail($album['uid']);							
					}
				} else {
					$content .= tx_cwtcommunity_lib_gallery::getViewPhotoNew(t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_ALBUM_UID));
				}

            } elseif ($action == tx_cwtcommunity_lib_constants::ACTION_GALLERY_SHOW_PHOTO_EDIT) {
            	// Sanity check
				$photo = tx_cwtcommunity_lib_gallery::getPhoto(t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_PHOTO_UID));
				// Check if owner
				if ($photo['cruser_id'] != tx_cwtcommunity_lib_common::getLoggedInUserUID()) {
					return tx_cwtcommunity_lib_common::generateErrorMessage('An unknown error has occured. Please contact your system administrator.');
				}
				
				// Generate content with cwt_feedit
				$content .= tx_cwtcommunity_lib_gallery::getViewPhotoEdit($photo['uid'], $this, $conf);

            } elseif ($action == tx_cwtcommunity_lib_constants::ACTION_GALLERY_SHOW_PHOTO_REPORT) {
         		// Sanity checks
				$photo = tx_cwtcommunity_lib_gallery::getPhoto(t3lib_div::_GP(tx_cwtcommunity_lib_constants::CONST_PHOTO_UID));
				// Is gallery activated?
            	if (!tx_cwtcommunity_lib_gallery::isGalleryActivated($photo['cruser_id'])) {
					return tx_cwtcommunity_lib_common::generateErrorMessage('An unknown error has occured. Please contact your system administrator.');
				}
				// Does the user have sufficient access rights?
				if (!tx_cwtcommunity_lib_gallery::mayUserAccessAlbum($photo['album_uid'], tx_cwtcommunity_lib_common::getLoggedInUserUID())) {
					return tx_cwtcommunity_lib_common::generateErrorMessage('An unknown error has occured. Please contact your system administrator.');					
				}
				
				// Handle commands
            	$report = t3lib_div::_GP(tx_cwtcommunity_lib_constants::CMD_GALLERY_REPORT_PHOTO);
            	$cancel = t3lib_div::_GP(tx_cwtcommunity_lib_constants::CMD_CANCEL);           		
				if ($report != '' || $cancel != '') {
					if ($report != null && $report != '') {
						// Report photo
						$reason = t3lib_div::_GP('REASON');
						if ($reason != null && $reason != '') {
							tx_cwtcommunity_lib_gallery::reportPhotoToAdmin($photo['uid'], tx_cwtcommunity_lib_common::getLoggedInUserUID(), $reason, $this->conf);
						}							
					}

					// Generate content
            		$content .= tx_cwtcommunity_lib_gallery::getViewPhotoDetail($photo['uid']);						
				} else {
					// Generate content
            		$content .= tx_cwtcommunity_lib_gallery::getViewPhotoDetail($photo['uid'], "report");
				}
            }
        } elseif ($CODE == "PROFILE_EDIT") {
			// Include the CWT_FEEDIT configuration for the profile edit page.
			$path = t3lib_div::getFileAbsFileName($conf['template_profile_edit']);
			if (file_exists($path)) {
				include_once($path);	
			} else {
				tx_cwtcommunity_lib_common::generateErrorMessage('Configuration file "'.$path.'" could not be found!');
			}
        }

        // Return the generated content!
        return $this->pi_wrapInBaseClass($content);
	}
}
if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_community/pi1/class.tx_cwtcommunity_pi1.php"]) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_community/pi1/class.tx_cwtcommunity_pi1.php"]);
} 

?>
