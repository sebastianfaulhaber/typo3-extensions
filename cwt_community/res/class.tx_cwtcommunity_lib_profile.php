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
include_once(t3lib_extMgm::extPath('cwt_community').'res/class.tx_cwtcommunity_lib_buddylist.php');

/**
 * Profile related functions used in the community using EXT:smarty.
 * 
 * @author	Sebastian Faulhaber <sebastian.faulhaber@gmx.de>
 * @package TYPO3
 * @subpackage	tx_cwtcommunity
 */
class tx_cwtcommunity_lib_profile {

	/**
	*  Displays the user profile page, it's gallery and buddylist for a user.
	*
	*  @param $user Associative array with user attributes.
	*  @return String The generated HTML source for this view.
	*/
    public static function getViewProfile($userRaw, $user) {
        // Get Smarty Instance
    	$smartyInstance = tx_cwtcommunity_lib_common::getSmartyInstance();
        $conf = tx_cwtcommunity_lib_common::getConfArray();
        $cObj = tx_cwtcommunity_lib_common::getCObj();
        $tplPath = tx_cwtcommunity_lib_common::getTemplatePath($conf['template_profile']);
        
        
        //get the content for the buddylist
        $buddylistContent .= tx_cwtcommunity_lib_buddylist::getViewBuddylist(tx_cwtcommunity_lib_buddylist::getBuddylist($user['uid']), true);
        
        //get the content for gallery
        $galleryContent .= tx_cwtcommunity_lib_gallery::getViewAlbumList($user['uid'], $cObj, $conf, true);
        
        //get gallery information
        $album_count = tx_cwtcommunity_lib_gallery::getAlbumCountForRole($user['uid']);
    	$album_count_forRole = tx_cwtcommunity_lib_gallery::getAlbumCountForRole($user['uid']);
        
        //check if feUser and profile's user are the same
        $isOwner = false;
		if (tx_cwtcommunity_lib_common::getLoggedInUserUID() == $user['uid']) {
			$isOwner = true;
		}
        
		// Provide smarty with the information for the template
        $smartyInstance->assign('user', $user);
        $smartyInstance->assign('userRaw', $userRaw);
        $smartyInstance->assign('buddylist',$buddylistContent);
        $smartyInstance->assign('gallery',$galleryContent);
        $smartyInstance->assign('album_count',$album_count);
        $smartyInstance->assign('album_count_forRole',$album_count_forRole);
        $smartyInstance->assign('isOwner',$isOwner);
        
        if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tx_cwtcommunity']['getViewProfile'] != null) {
        	foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tx_cwtcommunity']['getViewProfile'] as $_classRef) {
        		$_procObj = & t3lib_div::getUserObj($_classRef);
        		$_procObj->getViewProfile($smartyInstance, $user);
        	}	 
        }
        
        $content .= $smartyInstance->display($tplPath);
        
        return $content;
    }

	/**
	*  Displays the user profile generic view for a user.
	*
	*  @param $user Associative array with user attributes.
	*  @return String The generated HTML source for this view.
	*/
    public static function getViewProfileGeneric($user, $userRaw, $config) {
        // Get Smarty Instance
    	$smartyInstance = tx_cwtcommunity_lib_common::getSmartyInstance();
        $tplPath = t3lib_div::getFileAbsFileName($config['tpl']);
        
        // Provide smarty with the information for the template
        $smartyInstance->assign('userRaw', $userRaw);
        $smartyInstance->assign('user', $user);
        
        $content .= $smartyInstance->display($tplPath);
        
        return $content;
    }
    
	/**
	*  Displays the user profile page for a user.
	*
	*  @param $user Associative array with user attributes.
	*  @return String The generated HTML source for this view.
	*/
    public static function getViewProfileMini($userRaw, $user) {
        // Get Smarty Instance
    	$smartyInstance = tx_cwtcommunity_lib_common::getSmartyInstance();
        $conf = tx_cwtcommunity_lib_common::getConfArray();
        $tplPath = tx_cwtcommunity_lib_common::getTemplatePath($conf['template_profile_mini']);
        
        
		// Provide smarty with the information for the template
        $smartyInstance->assign('user', $user);
        $smartyInstance->assign('userRaw', $userRaw);
        
        $content .= $smartyInstance->display($tplPath);
        
        return $content;
    }
    
	/**
	*  Displays the user profile page for a user.
	*
	*  @param $user Associative array with user attributes.
	*  @return String The generated HTML source for this view.
	*/
    public static function getViewProfileUserstats($user) {
        // Get Smarty Instance
    	$smartyInstance = tx_cwtcommunity_lib_common::getSmartyInstance();
        $conf = tx_cwtcommunity_lib_common::getConfArray();
        $cObj = tx_cwtcommunity_lib_common::getCObj();
        $tplPath = tx_cwtcommunity_lib_common::getTemplatePath($conf['template_userstats']);
        
        
		// Provide smarty with the information for the template
        $smartyInstance->assign('user', $user);
        
        $content .= $smartyInstance->display($tplPath);
        
        return $content;
    }
    
    /**
     * Increases the profile view counter for the specified FE User.
     * 
     * @param	int		A FE User's UID.
     * @return	void
     */
    public static function increaseProfileViewCounter($feuser_uid) {
    	$conf = tx_cwtcommunity_lib_common::getConfArray();
    	$accessingFeuser_uid = tx_cwtcommunity_lib_common::getLoggedInUserUID();
    	
    	// Determine if profile access shall be counted
    	$res = tx_cwtcommunity_lib_common::dbQuery('SELECT * FROM tx_cwtcommunity_profileviews WHERE NOT deleted = 1 AND NOT hidden = 1 
    												AND cruser_id = '.intval($accessingFeuser_uid).' AND target_uid = '.intval($feuser_uid).';');
    	if (sizeof($res) > 0) {
    		// Check if profile may be counted due to TS configuration
    		$now = time();
    		$interval = 60 * intval($conf['userstats.']['countProfileViewAfterMinutes']);
    		if ((intval($res[0]['tstamp']) + $interval) < $now) {
    			tx_cwtcommunity_lib_common::dbUpdateQuery('UPDATE tx_cwtcommunity_profileviews SET tstamp = '.time().' WHERE cruser_id = '.intval($accessingFeuser_uid).' AND target_uid = '.intval($feuser_uid).';');
    		} else {
    			return;
    		}
    			
    	} else {
    		tx_cwtcommunity_lib_common::dbUpdateQuery('INSERT INTO tx_cwtcommunity_profileviews (tstamp, crdate, cruser_id, target_uid) VALUES ('.time().', '.time().', '.intval($accessingFeuser_uid).', '.intval($feuser_uid).');');
    	}
    	
    	// Actually increase the counter
    	tx_cwtcommunity_lib_common::dbUpdateQuery('UPDATE fe_users SET tx_cwtcommunityuser_stats_profileviews=tx_cwtcommunityuser_stats_profileviews+1 WHERE uid = '.intval($feuser_uid).';');
    }
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_community/res/class.tx_cwtcommunity_lib_profile.php"]) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_community/res/class.tx_cwtcommunity_lib_profile.php"]);
}
?>