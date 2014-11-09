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

include_once(t3lib_extMgm::extPath('cwt_community').'res/class.tx_cwtcommunity_lib_constants.php');
include_once(t3lib_extMgm::extPath('cwt_community').'res/class.tx_cwtcommunity_lib_common.php');

/**
 * Misc function needed for integration of 3rd party extensions.
 * 
 * @author	Sebastian Faulhaber <sebastian.faulhaber@gmx.de>
 * @package TYPO3
 * @subpackage	tx_cwtcommunity
 */


class tx_cwtcommunity_lib_misc {

	/**
	 * 3rd Party Extension = seminars
	 * 
	 * Fetches all available seminars.
	 * 
	 * @return array	All seminars found in DB.
	 */
	public static function getSeminars() { 
		
		$res = tx_cwtcommunity_lib_common::dbQuery('SELECT uid,title, begin_date, end_date FROM `tx_seminars_seminars` WHERE NOT hidden = 1 AND NOT deleted = 1 ORDER BY begin_date ASC;');
		return $res;
	}

}
if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_community/res/class.tx_cwtcommunity_lib_misc.php"]) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_community/res/class.tx_cwtcommunity_lib_misc.php"]);
}
?>