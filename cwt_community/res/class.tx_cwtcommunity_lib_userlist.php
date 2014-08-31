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
include_once(t3lib_extMgm::extPath('cwt_community').'res/class.tx_cwtcommunity_lib_common.php');

/**
 * Userlist related functions used in the community using EXT:smarty.
 * 
 * @author	Sebastian Faulhaber <sebastian.faulhaber@gmx.de>
 * @package TYPO3
 * @subpackage	tx_cwtcommunity
 */
class tx_cwtcommunity_lib_userlist {

	/**
	*  Displays an alphabetically list of frontend users.
	*
	*  @param	array	The array of FE Users to display.
	*  @param	array	The array of FE Users (for display) to display.
	*  @param	boolean	True if the special search result view shall be displayed.
	*  @param	array	The page to display.
	*  @param	array	The number of pages.
	*  @return	string	The generated HTML source for this view.
	*/
    public static function getViewUserlist($users, $usersForDisplay, $isSearchResult = false, $page, $pageCount, $userCount) {
        // Get Smarty Instance
    	$smartyInstance = tx_cwtcommunity_lib_common::getSmartyInstance();
        $conf = tx_cwtcommunity_lib_common::getConfArray();
        $tplPath = tx_cwtcommunity_lib_common::getTemplatePath($conf['template_userlist']);
        
        // Provide smarty with the information for the template
        $smartyInstance->assign('users', $users);
        $smartyInstance->assign('usersForDisplay', $usersForDisplay);
        $smartyInstance->assign('isSearchResult', $isSearchResult);
        $smartyInstance->assign('page', $page);
        $smartyInstance->assign('pageCount', $pageCount);
        $smartyInstance->assign('userCount', array($userCount));
        $content .= $smartyInstance->display($tplPath);
        
        return $content;
    } 

    /**
	*  Displays a configurable list of frontend users.
	*
	*  @param	array	The array of FE Users to display.
	*  @param	array	The array of FE Users (for display) to display.
	*  @param	array	Configuration array for this view, which contains the HTML template for example.
  	*  @param	array	The page to display.
  	*  @param	array	The number of pages.
	*  @return	string	The generated HTML source for this view.
	*/
    public static function getViewUserlistGeneric($users, $usersForDisplay, $config, $page, $pageCount, $userCount) {
        // Get Smarty Instance
    	$smartyInstance = tx_cwtcommunity_lib_common::getSmartyInstance();
        $conf = tx_cwtcommunity_lib_common::getConfArray();
        $tplPath = t3lib_div::getFileAbsFileName($config['tpl']);
        
        // Provide smarty with the information for the template
        $smartyInstance->assign('users', $users);
        $smartyInstance->assign('usersForDisplay', $usersForDisplay);
        $smartyInstance->assign('genericsConfig', $config);
        $smartyInstance->assign('page', $page);
        $smartyInstance->assign('pageCount', $pageCount);
        $smartyInstance->assign('userCount', $userCount);        
        $content .= $smartyInstance->display($tplPath);
        
        return $content;    	
    }
    
    public static function getUserlistCommonWhereClause() {
    	$conf = tx_cwtcommunity_lib_common::getConfArray();
    	
        //Determine additionalWhereClause
		$additionalSQLWhereClause = $conf['userlist.']['additionalSQLWhereClause'];
		if ($additionalSQLWhereClause == null) {
			$additionalSQLWhereClause = '';
		}
		
    	//Determine userlistVisibilityWhereClause
		if (tx_cwtcommunity_lib_common::getLoggedInUserUID() != null) {
			$userlistVisibilityWhereClause = " AND ((u.tx_cwtcommunityuser_userlist_visibility = ".tx_cwtcommunity_lib_constants::CONST_USERLIST_VISIBILITY_ALL.") OR (u.tx_cwtcommunityuser_userlist_visibility = ".tx_cwtcommunity_lib_constants::CONST_USERLIST_VISIBILITY_BUDDY." AND u.uid IN (SELECT fe_users_uid FROM tx_cwtcommunity_buddylist AS b WHERE b.buddy_uid = ".tx_cwtcommunity_lib_common::getLoggedInUserUID()."))) ";
		} else {
			$userlistVisibilityWhereClause = " AND ((u.tx_cwtcommunityuser_userlist_visibility = ".tx_cwtcommunity_lib_constants::CONST_USERLIST_VISIBILITY_ALL.")) ";
		}
		
		// Limit pages
		$pages = tx_cwtcommunity_lib_common::getSysfolderPIDs();
		$returnStr = ' u.pid IN ('.$pages.') '.$userlistVisibilityWhereClause.' '.$additionalSQLWhereClause.' AND NOT u.deleted = "1" AND NOT u.disable = "1" ';
		if (tx_cwtcommunity_lib_constants::$DEBUG_APPLICATION) {
			debug($returnStr, 'getUserlistCommonWhereClause() return'); 
		}
		return $returnStr;
		
    }
    
 	public static function getUserlistOrderBy($sortOrder, $sortColumn) {
    	$conf = tx_cwtcommunity_lib_common::getConfArray();
    	
    	$sortColumn = self::getUserlistSortColumn($sortColumn);
    	$sortOrder = self::getUserlistSortOrder($sortOrder);
		
		$returnStr = ' ORDER BY '.$sortColumn.' '.$sortOrder.' ';
		if (tx_cwtcommunity_lib_constants::$DEBUG_APPLICATION) {
			debug($returnStr, 'getUserlistOrderBy($sortOrder, $sortColumn) return'); 
		}
		return $returnStr;
    }
    
    public static function getUserlistSortOrder($sortOrder) {
    	$conf = tx_cwtcommunity_lib_common::getConfArray();
    	
        // Default sorting if not specified correctly
		if ($sortOrder == null || $sortOrder == '') {
			$sortOrder = $conf['userlist.']['default.']['sortOrder'];
		}
		
		//Sanity Check
		($sortOrder != 'asc' && $sortOrder != 'desc') ? $sortOrder = 'asc' : $sortOrder = $sortOrder;
		
    	if (tx_cwtcommunity_lib_constants::$DEBUG_APPLICATION) {
			debug($sortOrder, 'getUserlistSortOrder($sortOrder) return'); 
		}
		return $sortOrder;
    }
    
    public static function getUserlistSortColumn($sortColumn) {
    	$conf = tx_cwtcommunity_lib_common::getConfArray();
    	
    	if ($sortColumn == null || $sortColumn == '') {
			$sortColumn = $conf['userlist.']['default.']['sortColumn'];
		}
		
		//Sanity Check
    	$sortColumn = $GLOBALS['TYPO3_DB']->fullQuoteStr('u.'.$sortColumn, 'fe_users');
		$sortColumn = str_replace('\'', '', $sortColumn);
		
       	if (tx_cwtcommunity_lib_constants::$DEBUG_APPLICATION) {
			debug($sortColumn, 'getUserlistSortColumn($sortColumn)'); 
		}
		return $sortColumn;
    }
    
    public static function getUserlistLetter($letter) {
    	$conf = tx_cwtcommunity_lib_common::getConfArray();
    	
    	//If a default letter is configured, it is selected here
		if ($letter == null && $conf['defaultUserListLetter']) {
			$letter = $conf['defaultUserListLetter'];
		}
		
		if (tx_cwtcommunity_lib_constants::$DEBUG_APPLICATION) {
			debug($letter, 'getUserlistLetter($letter)'); 
		}
		return $letter;
    }
    
 	public static function getUserlistLimit($page) {
    	$conf = tx_cwtcommunity_lib_common::getConfArray();
    	
		// Calculate pages
		$usersPerPage = $conf['userlist.']['paging.']['usersPerPage'];
		$page == null ? $page = 1 : $page = $page;
		$startIndex = ($page-1) * $usersPerPage;
		
		$returnStr = ' LIMIT '.$startIndex.','.$usersPerPage;
		if (tx_cwtcommunity_lib_constants::$DEBUG_APPLICATION) {
			debug($returnStr, 'getUserlistLimit($page) return'); 
		}
		return $returnStr;
    }
    
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_community/res/class.tx_cwtcommunity_lib_userlist.php"]) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_community/res/class.tx_cwtcommunity_lib_userlist.php"]);
}
?>