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
 * Welcome related functions used in the community using EXT:smarty.
 * 
 * @author	Sebastian Faulhaber <sebastian.faulhaber@gmx.de>
 * @package TYPO3
 * @subpackage	tx_cwtcommunity
 */
class tx_cwtcommunity_lib_welcome {

	/**
    *
    *  Displays the welcome page for a user.
    *
    *  @param $user_info All the information that shall be displayed.
    *  @param $count The number of new messages
    *  @return String The generated HTML source for this view.
    */
    public static function getViewWelcome($user_info) {
        // Get Smarty Instance
    	$smartyInstance = tx_cwtcommunity_lib_common::getSmartyInstance();
        $conf = tx_cwtcommunity_lib_common::getConfArray();
        $cObj = tx_cwtcommunity_lib_common::getCObj();
        $tplPath = tx_cwtcommunity_lib_common::getTemplatePath($conf['template_welcome']);
        
        // Get the users new messages
        $messages = tx_cwtcommunity_lib_messages::getNewMessages($user_info['uid']);
        $approvals = tx_cwtcommunity_lib_buddylist::getBuddylistApprovalsReceived($user_info['uid']);

		// Provide smarty with the information for the template
        $smartyInstance->assign('user_info', $user_info);
        $smartyInstance->assign('message_count',sizeof($messages));
        $smartyInstance->assign('approval_count',sizeof($approvals));
        $smartyInstance->assign('approvals',$approvals);
        
        $content .= $smartyInstance->display($tplPath);
        
        return $content;
    }
    
	/**
    *  Fetches information about an fe user with a specified uid (for the welcome page).
    *
    *  @param $fe_users_uid Valid uid from 'fe_users'
    *  @return Array
    */
	function getUserInfo($fe_users_uid = null) {
		// Fetch user
		$user_info = tx_cwtcommunity_lib_common::dbQuery("SELECT * FROM fe_users WHERE uid = ".intval($fe_users_uid));
		$temp = array();
		$keys = array_keys($user_info[0]);
		// Create return array
		for ($i = 0;$i < sizeof($user_info[0]);$i++) {
			$temp[$keys[$i]] = $user_info[0][$keys[$i]];
		}
		return $temp;
    } 

}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_community/res/class.tx_cwtcommunity_lib_welcome.php"]) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_community/res/class.tx_cwtcommunity_lib_welcome.php"]);
}
?>