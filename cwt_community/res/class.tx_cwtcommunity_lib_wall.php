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

/**
 * Wall related functions used in the community using EXT:smarty.
 * 
 * @author	Sebastian Faulhaber <sebastian.faulhaber@gmx.de>
 * @package TYPO3
 * @subpackage	tx_cwtcommunity
 */
class tx_cwtcommunity_lib_wall {

	
	public static function getWallEntriesForUser($owner_uid) {
		$conf = tx_cwtcommunity_lib_common::getConfArray();
		$res = tx_cwtcommunity_lib_common::dbQuery('SELECT * FROM tx_cwtcommunity_wall WHERE wall_owner_uid = '.intval($owner_uid).' AND wall_entry_uid IS NULL AND deleted != 1 AND hidden != 1 ORDER BY uid DESC;');
		
		// Generate path to user pic
		for ($i = 0; $i < sizeof($res); $i++) {
			$res[$i]['cruser_pic'] = tx_cwtcommunity_lib_common::getUserPicGeneric($res[$i]['cruser_id'], $conf['wall.']['user_pic.']);
			$res[$i]['cruser'] = tx_cwtcommunity_lib_common::getUserForDisplay($res[$i]['cruser_id']);
			$res[$i]['cruser_linkToUsersProfile'] = tx_cwtcommunity_lib_common::getLinkToUsersProfile($res[$i]['cruser_id']);
			$res[$i]['crdate_date'] = date(tx_cwtcommunity_lib_common::getLL('CWT_DATE_FORMAT'), $res[$i]['crdate']);
			$res[$i]['crdate_time'] = date(tx_cwtcommunity_lib_common::getLL('CWT_TIME_FORMAT'), $res[$i]['crdate']);
		}
		return $res;
	}
	
	public static function getWallEntry($entry_uid) {
		$res = tx_cwtcommunity_lib_common::dbQuery('SELECT * FROM tx_cwtcommunity_wall WHERE uid = '.intval($entry_uid).' AND wall_entry_uid IS NULL AND deleted != 1 AND hidden != 1 ORDER BY uid DESC;');
		return $res[0];
	}
	
	public static function addWallEntryText($owner_uid, $content_text) {		
		// Prepare Query
		$now = time();
		$content_text = strip_tags($content_text);
		$content_text = $GLOBALS['TYPO3_DB']->fullQuoteStr($content_text, 'tx_cwtcommunity_wall');
		$res = tx_cwtcommunity_lib_common::dbUpdateQuery('INSERT INTO tx_cwtcommunity_wall SET pid = '.tx_cwtcommunity_lib_common::getGlobalStorageFolder()
															.', tstamp = '.$now
															.', crdate = '.$now
															.', cruser_id = '.tx_cwtcommunity_lib_common::getLoggedInUserUID()
															.', wall_owner_uid = '.intval($owner_uid)
															.', content_text = '.$content_text);
		return $res;
	}
	 
	public static function addWallEntryComment($owner_uid, $content_text) {
		   //dhjsh
	}

	public static function addWallEntryImage($owner_uid, $content_text, $content_image) {
		// ACCESS CHECK
		if (tx_cwtcommunity_lib_common::getLoggedInUserUID() != $owner_uid) {
			exit("FATAL ERROR - ACCESS DENIED!");
		}
		
		return true;	
	}
	
	public static function deleteWallEntry($entry_uid) {
		$entry = self::getWallEntry($entry_uid);
		
		// ACCESS CHECK
		if (tx_cwtcommunity_lib_common::getLoggedInUserUID() == $entry['wall_owner_uid']
			 || tx_cwtcommunity_lib_common::getLoggedInUserUID() == $entry['cruser_id']) {
			$res = tx_cwtcommunity_lib_common::dbUpdateQuery('UPDATE tx_cwtcommunity_wall SET deleted = 1 WHERE uid = '.intval($entry_uid).' OR wall_entry_uid = '.intval($entry_uid));
			return $res;	
		} else {
			exit("FATAL ERROR - ACCESS DENIED!");
		}
	}
	
	/**
    *
    *  Displays the wall page for a user.
    *
    *  @param $owner_uid All the information that shall be displayed.
    *  @return String The generated HTML source for this view.
    */
    public static function getViewWall($owner_uid) {
        // Get Smarty Instance
    	$smartyInstance = tx_cwtcommunity_lib_common::getSmartyInstance();
        $conf = tx_cwtcommunity_lib_common::getConfArray();
        $cObj = tx_cwtcommunity_lib_common::getCObj();
        $tplPath = tx_cwtcommunity_lib_common::getTemplatePath($conf['template_wall']);
        $userRaw = tx_cwtcommunity_lib_common::getUser($owner_uid);
		$user = tx_cwtcommunity_lib_common::getUserForDisplay($owner_uid);

		// Provide smarty with the information for the template
        $smartyInstance->assign('user', $user);
        $smartyInstance->assign('userRaw', $userRaw);
        
        $content .= $smartyInstance->display($tplPath);
        
        return $content;
    }

}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_community/res/class.tx_cwtcommunity_lib_wall.php"]) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_community/res/class.tx_cwtcommunity_lib_wall.php"]);
}
?>