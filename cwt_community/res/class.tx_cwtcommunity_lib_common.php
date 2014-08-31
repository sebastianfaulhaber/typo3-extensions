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
 * Misc functions used in the community.
 *
 * @author	Sebastian Faulhaber <sebastian.faulhaber@gmx.de>
 * @package TYPO3
 * @subpackage	tx_cwtcommunity
 */
class tx_cwtcommunity_lib_common {

	private static $cObj = null;
	private static $fe_user_cache = array();
	private static $local_lang_cache = array();
	private static $smartyInstance = null;

	/**
	 * The 'Starting Point' configuration for the calling FE plugin. The list
	 * of PID's is mainly used to set the appropriate scope for search queries.
	 */
	private static $sysfolderList = null;

	/**
	 * The calling class' $conf array, which contains the according Typoscript
	 * configuration.
	 */
	private static $conf = array();

	/**
	 * A reference to the calling class. The caller must extend 'tslib_pibase'
	 * in order to make some features available.
	 */
	private static $callerRef = null;

	/**
	 * A constructor-like method, that initializes a lot of stuff used within this class.
	 *
	 * @param	array	The calling class' Typoscript configuration
	 * @param 	string	The 'Starting Point' configuration for the FE plugin.
	 * @param	class	A reference to the calling class itself.
	 * @return	void
	 */
	public static function init($conf, $callerRef, $smartyInstance) {
		self::$conf = $conf;
		self::$callerRef = $callerRef;
		self::$smartyInstance = $smartyInstance;
		self::initSmarty();

		// Get the 'Starting Point' PIDs
		if (self::$callerRef->cObj->data['pages'] != null) {
			self::$sysfolderList = self::$callerRef->cObj->data["pages"];
		} else {
			// If no starting point is given, then take the pid of the plugin page
			self::$sysfolderList = $GLOBALS['TSFE']->id;
		}
		
		//Include additional classes
		self::includeAdditionalClasses($conf['includeAdditionalClasses']);
	}

	
	/**
	 * Used to accumulate additional HTML-code for the header-section, <head>...</head>.
	 * 
	 * @param	string		An identifier for the script to include: e.g. 'jQuery'
	 * @param	string		The HTML header data to include. e.g.: <script src="typo3conf/ext/vitalisreisen/res/jquery/jquery.js" type="text/javascript"></script>
	 * @return	void
	 */
	public static function addAdditionalHeaderData($id, $headerData) {
		$extKey = self::getCallerRef()->extKey;
		$GLOBALS['TSFE']->additionalHeaderData[$extKey.'_'.$id] = $headerData;
	}
	
	/**
	 * Used for initialization of the common Smarty instance:
	 * -  Assignment of variables that shall be available to all templates.
	 *
	 * @return		void
	 */
	private static function initSmarty() {
		$conf = self::getConfArray();
		
		// Assign the TS configuration
		self::getSmartyInstance()->assign('CONF', self::getConfArrayCleaned());

		// Assign the currently logged in FE User
		self::getSmartyInstance()->assign('CUR_USER', self::getUserForDisplay(self::getLoggedInUserUID()));

		// Assign this class as object
		$self = new tx_cwtcommunity_lib_common();
		self::getSmartyInstance()->assign_by_ref('LIB_COMMON', $self);
		self::getSmartyInstance()->assign_by_ref('LIB_BUDDY', t3lib_div::makeInstance('tx_cwtcommunity_lib_buddylist'));
		
		// Assign foreign classes aka. "GENERICS - Global"
		$genConfigItems = $conf['generics.']['global.'];
		is_array($genConfigItems) ? $genConfigItems : $genConfigItems = array();
		foreach ($genConfigItems as $genConfigItem) {
				$class = t3lib_div::getFileAbsFileName($genConfigItem['class']);
				if (file_exists($class)) {
					require_once($class);
					$classObj = t3lib_div::makeInstance($genConfigItem['className']);
					self::getSmartyInstance()->assign_by_ref('LIB_'.strtoupper($genConfigItem['className']), $classObj);
					
				} else {
					t3lib_div::sysLog('GENERICS_GLOBAL: The class file "'.$class.'" could not be found!', self::getCallerRef()->extKey, t3lib_div::SYSLOG_SEVERITY_ERROR);
				}
		}
		
		
		//Assign the current page id 
		self::getSmartyInstance()->assign('PID_SELF', $GLOBALS['TSFE']->id);
		
		//Assign the upload folder for the gallery
		self::getSmartyInstance()->assign('IMG_PATH_GALLERY', tx_cwtcommunity_lib_constants::PATH_USER_GALLERY_IMAGES);
		
		// Assign the extension key
		self::getSmartyInstance()->assign('EXT_KEY', self::getCallerRef()->extKey);
		
	}

	/**
	 * Returns the fully qualified path to a template file. The resulting path
	 * may then be used to render the template with Smarty.
	 *
	 * @param	string		The template path; e.g. 'typo3conf/ext/cwt_community/pi1/tx_cwtcommunity_pi1_search.html'
	 * @return	string		The fully qualified path; e.g. '/var/www/typo3conf/ext/cwt_community/pi1/userlist.html'
	 */
	public static function getTemplatePath($path) {
		$tplPath = PATH_site.$path;
		if (!file_exists($tplPath)) {
			echo tx_cwtcommunity_lib_common::generateErrorMessage('The specified template "'.$tplPath.'" could not be found!');
		}
		return $tplPath;
	}

	/**
	 * Returns a cObj instance.
	 *
	 * @return	cObj	A cObj instance.
	 */
	public static function getCObj() {
		// Lazy load cobj
		if (self::$cObj == null) {
			self::$cObj = t3lib_div::makeInstance('tslib_cObj');
		}
		return self::$cObj;
	}

	/**
	 * Gets the caller ref. This can be used to fetch object variables from a  
	 * static context.
	 * 
	 * @return		object		The caller reference: this should be an instance of a FE Plugin class.
	 */
	public static function getCallerRef() {
		return self::$callerRef;
	}	
	
	/**
	 * This function substitutes all markers of type '###CUSTOM_<fe_users_db_field>###' with the corresponding value from the
	 * T3 fe_users table. Therefore administrators may include custom markers in their templates without changing the
	 * code of cwt_community. Example: If you include a marker like '###CUSTOM_CR_USER_ID###' in your template, the value of the fe_users database
	 * column 'cr_user_id' will be displayed in the frontend.
	 *
	 * @param	String	HTML content.
	 * @param	Integer	$fe_user_uid
	 * @return	String	The substituted content.
	 */
	public static function substituteCustomMarkersForUser($content, $fe_user_uid) {
		return self::substituteCustomMarkers("__CUSTOM__", $content, $fe_user_uid);
	}

	/**
	 * Does the same as 'substituteCustomMarkersForUser($content, $fe_user_uid)' but the currently logged in user.
	 *
	 * @param String	HTML content.
	 * @return String	The substituted content.
	 */
	public static function substituteCustomMarkersForLoggedInUser($content) {
		return self::substituteCustomMarkers("__CUSTOM_LOGGEDIN__", $content, self::getLoggedInUserUID());
	}

	/**
	 * This function substitutes all markers of type '###__SORT_LINK_ASC__<fe_users_db_field>###' and
	 *  '###__SORT_LINK_DESC__<fe_users_db_field>###' with the corresponding sort link.
	 *
	 * @param String	HTML content.
	 * @return String	The substituted content.
	 */
	public static function substituteCustomMarkersForUserlistSort($content) {
		$markerArray = array();
		// Create ASCENDING
		preg_match_all('/###__SORT_LINK_ASC__[\w|\.]*###/', $content, $foundMarkers);
		for ($i=0; $i < sizeof($foundMarkers[0]); $i++) {
			$foundMarkers[0][$i] = str_ireplace('###', '', $foundMarkers[0][$i]);
			$tmp = explode('__SORT_LINK_ASC__', $foundMarkers[0][$i]);
			$markerArray['###'.$foundMarkers[0][$i].'###'] = self::getLinkToUserlist(tx_cwtcommunity_lib_constants::CONST_SORT_ASC, $tmp[1]);
		}
		 
		// Create DESCENDING
		preg_match_all('/###__SORT_LINK_DESC__[\w|\.]*###/', $content, $foundMarkers);
		for ($i=0; $i < sizeof($foundMarkers[0]); $i++) {
			$foundMarkers[0][$i] = str_ireplace('###', '', $foundMarkers[0][$i]);
			$tmp = explode('__SORT_LINK_DESC__', $foundMarkers[0][$i]);
			$markerArray['###'.$foundMarkers[0][$i].'###'] = self::getLinkToUserlist(tx_cwtcommunity_lib_constants::CONST_SORT_DESC, $tmp[1]);
		}

		// Substitute the gathered markers
		$content = self::getCObj()->substituteMarkerArray($content, $markerArray,'',1);
		return $content;
	}

	public static function substituteCustomMarkers($prefix, $content, $fe_user_uid) {
		// Get currently logged in user
		$feRecord = self::getUser($fe_user_uid);
		$feRecordKeys = array_keys($feRecord);
		$markerArray = array();
		$wrap = self::$conf['common.']['customMarker.']['types.']['check.']['wrap'];
		$prefixDisabled = self::$conf['common.']['customMarker.']['types.']['check.']['prefixDisabled'];
		$prefixEnabled = self::$conf['common.']['customMarker.']['types.']['check.']['prefixEnabled'];
		$showDisabled = self::$conf['common.']['customMarker.']['types.']['check.']['showDisabled'];

		//Create marker array
		//Load TCA for table
		t3lib_div::loadTCA('fe_users');
		//Merge TCA form $ext_keys
		$mergers = array('cwt_community_user');
		$foreignMergers = self::$conf['mergeTCAFromExtension'];
		$foreignMergers = str_replace(' ', '', $foreignMergers);
		$foreignMergers = explode(',', $foreignMergers);
		$mergers = array_merge($mergers, $foreignMergers);
		self::mergeExtendingTCAs($mergers);
		 
		for ($i = 0; $i < sizeof($feRecord); $i++) {
			// Check for T3 type of field
			//Get field information from TCA
			$TCA = $GLOBALS['TCA']['fe_users']['columns'][$feRecordKeys[$i]];
			$type = $TCA['config']['type'];
			$cols = $TCA['config']['cols'];
				
			// See 'cwt_feedit' handling for select fields
			if ($type == 'select' || $type == 'radio') {
				if (!$TCA['config']['foreign_table']) {
					$markerArray["###".$prefix.strtoupper($feRecordKeys[$i])."###"] = self::getCObj()->stdWrap(self::getLLFromString($TCA['config']['items'][$feRecord[$feRecordKeys[$i]]][0]), "");
				} else {
					$query = 'SELECT * FROM '.$TCA['config']['foreign_table'].' WHERE uid = '.intval($feRecord[$feRecordKeys[$i]]);
					$rows = self::dbQuery($query);
					$markerArray["###".$prefix.strtoupper($feRecordKeys[$i])."###"] = self::getCObj()->stdWrap(self::getDBLabel($rows[0], $TCA), "");
				}

			} elseif ($type == 'check') {
				// Check for 'cols'
				if ($cols != null) {
					// Multiple checkboxes
					$cols++;
					// Convert decimal to binary
					$bin = decbin($feRecord[$feRecordKeys[$i]]);
					// fill leading zeros
					$diff = $cols - strlen($bin);
					for ($k = 0; $k < $diff; $k++) {
						$bin = '0'.$bin;
					}
					$bin = strrev($bin);
					for ($j = 0; $j < $cols; $j++) {
						if (substr($bin, $j, 1) == '1') {
							$tmp = $prefixEnabled.self::getCObj()->stdWrap(self::getLLFromString($TCA['config']['items'][$j][0]), "");
							$assembledString .= str_replace('|', $tmp, $wrap);
						} elseif ($showDisabled == '1') {
							$tmp = $prefixDisabled.self::getCObj()->stdWrap(self::getLLFromString($TCA['config']['items'][$j][0]), "");
							$assembledString .= str_replace('|', $tmp, $wrap);
						}
					}
					$markerArray["###".$prefix.strtoupper($feRecordKeys[$i])."###"] = $assembledString;
				} else {
					// Simple checkbox
					if ($feRecord[$feRecordKeys[$i]] == '1') {
						$markerArray["###".$prefix.strtoupper($feRecordKeys[$i])."###"] = $prefixEnabled.self::getCObj()->stdWrap(self::getLLFromString($TCA['label']), "");
					} elseif ($showDisabled == '1') {
						$markerArray["###".$prefix.strtoupper($feRecordKeys[$i])."###"] = $prefixDisabled.self::getCObj()->stdWrap(self::getLLFromString($TCA['label']), "");
					}

				}
			} else {
				$markerArray["###".$prefix.strtoupper($feRecordKeys[$i])."###"] = self::getCObj()->stdWrap($feRecord[$feRecordKeys[$i]], "");
			}
		}
		 
		$markerArray['###'.$prefix.'AGE###'] = self::getCObj()->stdWrap(tx_cwtcommunity_lib_common::calculateAgeFromDateOfBirth($feRecord['date_of_birth']), "");
		$markerArray['###'.$prefix.'DATE_OF_BIRTH###'] = self::getCObj()->stdWrap(date(tx_cwtcommunity_lib_common::getLL('CWT_DATE_FORMAT'),$feRecord['date_of_birth']), "");
		$markerArray['###__EXT__BASE_PATH###'] = t3lib_extMgm::extRelPath('cwt_community');
		$markerArray['###__EXT__ABUSE_LINK###'] = self::getCObj()->stdWrap(self::getLinkToAbuseReport(),"");
		 
		// Substitute
		$content = self::getCObj()->substituteMarkerArray($content, $markerArray,'',1);
		return $content;
	}

	
	/**
	 * Retrieves the actual values of SELECTBOX, RADIOBUTTON, CHECKBOX types for a specific record.
	 * This is especially useful when displaying records in the frontend.
	 * 
	 * @param	array	An associative array of a table record. e.g. from 'fe_users'.
	 * @param	string	The table to which the record belongs to. e.g. 'fe_users'.
	 * @param	array	Array of extension keys, which we need to resolve all values.
	 * @return	Array	An associative array of a table record.
	 */
	public static function getRecordForDisplay($feRecord, $table, $mergeTCAFromExtension = array()) {
		$feRecordKeys = array_keys($feRecord);

		//Remove fields from result
		unset($feRecord['password']);
		
		
		//Load TCA for table
		t3lib_div::loadTCA($table);
		//Merge TCA form $ext_keys
		self::mergeExtendingTCAs($mergeTCAFromExtension);
		 
		for ($i = 0; $i < sizeof($feRecord); $i++) {
			// Check for T3 type of field
			// Get field information from TCA
			$TCA = $GLOBALS['TCA'][$table]['columns'][$feRecordKeys[$i]];
			$type = $TCA['config']['type'];
			$cols = $TCA['config']['cols'];
			// See 'cwt_feedit' handling for select fields
			if ($type == 'select' || $type == 'radio') {
				// Handle 'itemsProcFunc' pre-processing function
			  	if ($TCA["config"]["itemsProcFunc"] != null) {
					self::executeItemsProcFunc($TCA);
				}
				
				if (!$TCA['config']['foreign_table']) {	
					// Determine SELECT Value
					for ($j=0; $j < sizeof($TCA["config"]["items"]); $j++) {
						//Check for selected
						if ($TCA["config"]["items"][$j][1] == $feRecord[$feRecordKeys[$i]]) {
							//Output selected
							$feRecord[$feRecordKeys[$i]] = self::getLLFromString($TCA['config']['items'][$j][0]);
							break;
						}
					}
				} else {
					$query = 'SELECT * FROM '.$TCA['config']['foreign_table'].' WHERE uid = '.intval($feRecord[$feRecordKeys[$i]]);
					$rows = self::dbQuery($query);
					$feRecord[$feRecordKeys[$i]] = self::getDBLabel($rows[0], $TCA);
				}
			} elseif ($type == 'check') {
				// Check for 'cols'
				if ($cols != null) {
					// Multiple checkboxes
					$cols++;
					// Convert decimal to binary
					$bin = decbin($feRecord[$feRecordKeys[$i]]);
					// fill leading zeros
					$diff = $cols - strlen($bin);
					for ($k = 0; $k < $diff; $k++) {
						$bin = '0'.$bin;
					}
					$bin = strrev($bin);
					$cboxArray = array();
					for ($j = 0; $j < $cols; $j++) {
						if (substr($bin, $j, 1) == '1') {
							$cboxArray[] = array(self::getLLFromString($TCA['config']['items'][$j][0]), '1');
						} else {
							$cboxArray[] = array(self::getLLFromString($TCA['config']['items'][$j][0]), '0');
						}
					}
					$feRecord[$feRecordKeys[$i]] = $cboxArray;
				} else {
					// Simple checkbox
					$cboxArray = array(); 
					if ($feRecord[$feRecordKeys[$i]] == '1') {
						$cboxArray[] = array(self::getLLFromString($TCA['label']), '1');
					} else {
						$cboxArray[] = array(self::getLLFromString($TCA['label']), '0');
					}
					$feRecord[$feRecordKeys[$i]] = $cboxArray;
				}
			}
		}

		// Add special fields
		$feRecord['linkToProfile'] = self::getLinkToUsersProfile($feRecord['uid']);
		
		return $feRecord;		
	}
	
	
	/**
	 * Returns the number of checked checkboxes for a T3 field of type "check".
	 * 
	 * @param	$rawDecimalValue		The decimal value stored in the T3 database.
	 * @return	int						The number of checked checkboxes.
	 */
	public static function getCheckboxCheckedCount($rawDecimalValue) {
		// Convert decimal to binary
		$bin = decbin($rawDecimalValue);
		// Count binary "1"
		$count = substr_count($bin, '1');
		return $count;
	}
	
	/**
	 *  Fetches information about an fe user with a specified uid.
	 *
	 *  @param		int		Valid UID from 'fe_users' table.
	 *  @return		array	An associative array containing the user with substituted LL values for select fields.
	 */
	public static function getUserForDisplay($feusers_uid) {
		// Get currently logged in user
		$feRecord = self::getUser($feusers_uid);
		
		//Merge TCA form $ext_keys
		$mergers = array('cwt_community_user');
		$foreignMergers = self::$conf['mergeTCAFromExtension'];
		$foreignMergers = str_replace(' ', '', $foreignMergers);
		$foreignMergers = explode(',', $foreignMergers);
		$mergers = array_merge($mergers, $foreignMergers);
		return self::getRecordForDisplay($feRecord, 'fe_users', $mergers);
	}
	
	public static function getUsersForDisplay($users) {
		$usersForDisplay = array();
		for ($i=0; $i < sizeof($users); $i++) {
			$usersForDisplay[$i] = self::getUserForDisplay($users[$i]['uid']);
		}
		return $usersForDisplay;
	}

	private static function executeItemsProcFunc(&$TCA) {
		// Generate items with pre-processing function
  		$itemsProcFunc = explode('->', $TCA["config"]["itemsProcFunc"]);
  		$itemsProcFunc = $itemsProcFunc[0];
  		// Call the itemsProcFunc class and generate items array
  		$procFuncClass = t3lib_div::makeInstance($itemsProcFunc);
  		$pObj = array();
  		if (method_exists($procFuncClass, 'main')) {
  			$procFuncClass->main($TCA["config"], $pObj);	
  		}
	}	
	
	/**
	 * This function substitutes markers of the format '###__LL__<marker_name>###' with the Locallang value '<marker_name>'.
	 * For example: if you use the marker '###__LL__TITLE###' this function will replace it with the value of 'TITLE', which
	 * should be present in your locallang.xml of course.
	 *
	 * @param		String	HTML content.
	 * @return		String	The substituted content.
	 */
	public static function substituteLLMarkers($content) {
		$markerArray = array();
		preg_match_all('/###__LL__[\w|\.]*###/', $content, $foundLLMarkers);
		for ($i=0; $i < sizeof($foundLLMarkers[0]); $i++) {
			$foundLLMarkers[0][$i] = str_ireplace('###', '', $foundLLMarkers[0][$i]);
			$tmp = explode('__LL__', $foundLLMarkers[0][$i]);
			$markerArray['###'.$foundLLMarkers[0][$i].'###'] = self::getCObj()->stdWrap(self::getLL($tmp[1]), '');
		}
		$content = self::getCObj()->substituteMarkerArray($content, $markerArray,'',1);
		return $content;
	}

	/**
	 *  Gets the lables of the chosen table from the global TCA.
	 *
	 *  @return String with the label and alt label.
	 */
	private static function getDbLabel($row, $TCA) {
		$GTCActrl = $GLOBALS["TCA"][$TCA[config][foreign_table]][ctrl];
		$label = $row[$GTCActrl["label"]];
		if ($GTCActrl["label_alt"]){
			$label .= ', '.$row[$GTCActrl["label_alt"]];
		}
		return $label;
	}

	/**
	 *  Merges the $tempColumns from extensions, that extend the table, we are currently
	 *  working on. In case you wrote an extension, that extends the "fe_users", then
	 *  the TCA information for the additional fields will be merged with the "fe_users" TCA.
	 *
	 *  @param	Array	Extension TCA's that should be merged.
	 */
	private static function mergeExtendingTCAs($ext_keys) {
		global $TYPO3_LOADED_EXT;
		//Merge all ext_keys
		if (is_array($ext_keys)) {
			for($i = 0; $i < sizeof($ext_keys); $i++){
				// Check if extension is loaded
				if (t3lib_extMgm::isLoaded($ext_keys[$i], false)) {
					// Include the ext_table
					$_EXTKEY=$ext_keys[$i];
					include_once($TYPO3_LOADED_EXT[$ext_keys[$i]]["ext_tables.php"]);
				}
			}
		}
	}

	private static function includeAdditionalClasses($additionalClasses) {
		if ($additionalClasses != null) {
			$addClassesArr = explode(",", $additionalClasses);
			for ($i=0; $i < sizeof($addClassesArr); $i++) {
				$absFileName = t3lib_div::getFileAbsFileName(trim($addClassesArr[$i]));
				if (file_exists($absFileName)) {
					include_once($absFileName);	
				} else {
					debug('Could not include file: "'.$absFileName.'"');
				}
			}
			
		}
	}
	
	/**
	 * Returns a locallang value from a specified various extension.
	 * 
	 * @param $ext_key		The extension key.
	 * @param $llKey		The locallang key.
	 * @param $llKeyFileUri	[OPTIONAL] A URI to the locallang file within the target extension. By default '/pi1/locallang.xml' will be used.
	 * @return				The according locallang value.
	 */
	public static function getLLFromExtension($ext_key, $llKey, $llKeyFileUri = '/pi1/locallang.xml') {
		$llValue = self::getLLFromString('LLL:EXT:'.$ext_key.$llKeyFileUri.':'.$llKey);
		if ($llValue == null || $llValue == '') {
			debug('The locallang key "'.$llKey.'" could not be found!');
		}
		return $llValue;
	}
	
	/**
	 *  Fetches the local language value from a given string.
	 *
	 *  @param	String	Must be in format: "LLL:EXT:cwt_community/locallang_db.php:tx_cwtcommunity_guestbook.status.I.0"
	 *  @return	String	Local Language Value
	 */
	private static function getLLFromString($lllString) {
		$string = explode(":", $lllString);
		$pathToFile = $string[1].":".$string[2];
		$ll_key = $string[3];

		//Read file
		$lang = ($GLOBALS["TSFE"]->config["config"]["language"]) ? $GLOBALS["TSFE"]->config["config"]["language"] : "default";
		
		// Check if LLL File is given.
		if (strpos($pathToFile, 'EXT') === 0) {
			if (strtolower(substr($pathToFile, -3)) == 'xml') {
				// Lookup in cache
				if (self::$local_lang_cache[$pathToFile] != null) {
					$LOCAL_LANG = self::$local_lang_cache[$pathToFile];
				} else {
					//T3_V6: Removed this because of incompatibility.
					$LOCAL_LANG = t3lib_div::readLLXMLfile(t3lib_div::getFileAbsFileName($pathToFile), $lang);
					
					//T3_V6: This is for Typo3 V6
					//$LOCAL_LANG = \TYPO3\CMS\Core\Utility\GeneralUtility::readLLfile(t3lib_div::getFileAbsFileName($pathToFile), $lang);
					
					self::$local_lang_cache[$pathToFile] = $LOCAL_LANG;
				}
					
			} else {
				// Lookup in cache
				if (self::$local_lang_cache[$pathToFile] != null) {
					$LOCAL_LANG = self::$local_lang_cache[$pathToFile];
				} else {
					$LOCAL_LANG = $GLOBALS['TSFE']->readLLfile($pathToFile, $lang);
					self::$local_lang_cache[$pathToFile] = $LOCAL_LANG;
				}
			}
			$ret =  $GLOBALS['TSFE']->getLLL($ll_key, $LOCAL_LANG);
			return $ret;		
		}
		return $lllString;
	}

	/**
	 * Calculates the age based on the date of birth.
	 *
	 * @param 	string	The date of birth as a UNIX timestamp.
	 * @return	string	The age.
	 */
	public static function calculateAgeFromDateOfBirth($date) {
		$secondsSince = time() - $date;
		$secondsInAYear = 31556926;
		$yearsSince = floor($secondsSince / $secondsInAYear);
		return $yearsSince;
	}

	/**
	 * This function post-processes values from locallang.xml by substituting special markers of type '{1..n}' with
	 * dynamic values taken from the wildcards array. Let's assume that your locallang value look like
	 *  'Hello from {0}.'; then the '{0}' will get substituted by the first elements of the wildcards array.
	 *
	 * @param		string	The locallang key.
	 * @param 		array	An array with values to substitute. The first element will substitute {0} and so on.
	 * @return		string	The post processed locallang value.
	 */
	public static function getLL($llkey, $wildcards = array()) {
		$llValue = self::$callerRef->pi_getLL($llkey);
		if (sizeof($wildcards) > 0) {
			// Substitute wildcards in locallang value
			for ($i = 0; $i < sizeof($wildcards); $i++) {
				$llValue = str_replace('{'.$i.'}', $wildcards[$i], $llValue);
			}
		}
		return htmlspecialchars($llValue);
	}

		/**
	 * This function post-processes values from locallang.xml by substituting special markers of type '{1..n}' with
	 * dynamic values taken from the wildcards array. Let's assume that your locallang value look like
	 *  'Hello from {0}.'; then the '{0}' will get substituted by the first elements of the wildcards array.
	 *
	 * @param		string	The locallang key.
	 * @param 		array	An string with semicolon separated values to substitute. The first element will substitute {0} and so on.
	 * @return		string	The post processed locallang value.
	 */
	public static function getLLWithWildcardsAsString($llkey, $wildcards) {
		$llValue = self::$callerRef->pi_getLL($llkey);
		if ($wildcards != null) {
			$wildcards_array = explode(';', $wildcards);
			// Substitute wildcards in locallang value
			for ($i = 0; $i < sizeof($wildcards_array); $i++) {
				$llValue = str_replace('{'.$i.'}', $wildcards_array[$i], $llValue);
			}
		}
		return htmlspecialchars($llValue);
	}
	
	
	/**
	 *  Fetches information about an fe user with a specified uid.
	 *
	 *  @param		int		Valid UID from 'fe_users' table.
	 *  @return		array	An associative array containing the user.
	 */
	public static function getUser($fe_users_uid)	{
		// Fetch user
		$temp = array();
		if (!empty($fe_users_uid)) {
			$cache = self::$fe_user_cache;
			// Check if user is in cache
			if ($cache[$fe_users_uid] != null) {
				// Get user from cache
				$user = $cache[$fe_users_uid];
			} else {
				// Put fe_user into cache
				$user = self::dbQuery("SELECT * FROM fe_users WHERE uid = ".intval($fe_users_uid));
				// Check if user not null
				if ($user[0]['uid'] == '') {
					return array();
				}
				$cache[$fe_users_uid] = $user;
			}
				
			$keys = array_keys($user[0]);
			// Create return array
			for ($i = 0;$i < sizeof($user[0]);$i++) {
				$temp[$keys[$i]] = $user[0][$keys[$i]];
			}
		}
		//Remove fields from result
		unset($temp['password']);
		
		// Return
		return $temp;
	}

	/**
	 *  Fetches information about an fe user with a specified uid including 
	 *  additional user statistics.
	 *
	 *  @param		int		Valid UID from 'fe_users' table.
	 *  @return		array	An associative array containing the user.
	 */	
	public static function getUserWithStats($feuser_uid) {
		// Get the user itself
		$user = self::getUser($feuser_uid);
		// Add user statistics
		$stats = self::dbQuery('SELECT u.tx_cwtcommunityuser_stats_profileviews AS profileviews, 
								(SELECT COUNT(*) FROM tx_cwtcommunity_buddylist WHERE NOT deleted = 1 AND NOT hidden = 1 AND cruser_id = '.intval($feuser_uid).') AS buddies,
								(SELECT COUNT(*) FROM tx_cwtcommunity_buddylist_approval WHERE NOT hidden = 1 AND target_uid = '.intval($feuser_uid).') AS buddy_requests,
								(SELECT COUNT(*) FROM tx_cwtcommunity_albums WHERE NOT deleted = 1 AND NOT hidden = 1 AND cruser_id = '.intval($feuser_uid).') AS albums,
								(SELECT COUNT(*) FROM tx_cwtcommunity_photos WHERE NOT deleted = 1 AND NOT hidden = 1 AND cruser_id = '.intval($feuser_uid).') AS photos  
								FROM fe_users AS u WHERE NOT u.deleted = 1 AND NOT u.disable = 1 AND u.uid = '.intval($feuser_uid).';');
		$stats = $stats[0];
		
		// Get buddies online
		$buddies = tx_cwtcommunity_lib_buddylist::getBuddylist($feuser_uid);
		$buddyOnlineCount = 0;
		foreach ($buddies as $buddy) {
			if (self::isUserOnline($buddy)) {
				$buddyOnlineCount++;
			}
		}
		$stats['buddies_online'] = $buddyOnlineCount;
		
		$user['stats'] = $stats;
		return $user;
	}


	/**
	 * Fetches a fe user by name. If the user cannot be found NULL is returned.
	 *
	 * @param	string		The name of the user.
	 * @return 	array		The fe user array, if the user can be found. Otherwise NULL!
	 */
	public static function getUserByName($name) {
		// remove whitespaces
		$name = trim($name);
		$user = self::dbQuery('SELECT * FROM fe_users WHERE '.self::$conf['messages.']['new.']['recipient_db_field'].' = UPPER('.strtoupper($GLOBALS['TYPO3_DB']->fullQuoteStr($name, 'fe_users')).') AND NOT deleted = 1 AND NOT disable = 1;');
		
		//Remove fields from result
		unset($user[0]['password']);
		
		return $user[0];
	}

	/**
	 *  Gets the uid of the user who is logged in.
	 *
	 *  @return	String	UID of the currently logged in user. If no user is logged in the function returns NULL.
	 */
	public static function getLoggedInUserUID() {
		if ($GLOBALS['TSFE']->loginUser != 1) {
			return null;
		}
		$temp = get_object_vars($GLOBALS["TSFE"]->fe_user);
		$temp = $temp['user']['uid'];
		return $temp;
	}

	/**
	 *  This function runs queries on the typo3 database and returns the
	 *  result set in an associative array e.g. $return[0]['myAttribute'].
	 *  Please notice, that this function is only suitable for 'SELECT'
	 *  queries.
	 *
	 *  @param	String	Database query, which will be executed. e.g. 'SELECT * FROM myTable'
	 *  @return	Array	Associative array with query results
	 */
	public static function dbQuery($query) {
		// Do the query
		$rows = array();
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		$error = $GLOBALS['TYPO3_DB']->sql_error();

		// Check for SQL error
		if ($error != null) {
			debug($error, 'A SQL error has occured while executing the following query: "'.$query.'".');
			return $rows;
		}
		 
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$rows[] = $row;
		}

		// Debugging
		if (tx_cwtcommunity_lib_constants::$DEBUG_SQL_QUERIES) {
			debug($rows, 'DEBUG_SQL_QUERIES: '.$query);
			debug(mysql_error(), $query);
		}
		// Return the array
		return $rows;
	}

	/**
	 *  Executes an update query against the T3 DB.
	 *
	 *  @param	string	Database query, which will be executed. e.g. 'UPDATE myTable SET myAttribute=myValue WHERE...'
	 */
	public static function dbUpdateQuery($query) {
		// Do the query
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		$error = $GLOBALS['TYPO3_DB']->sql_error();

		// Debugging
		if (tx_cwtcommunity_lib_constants::$DEBUG_SQL_QUERIES) {
			debug($query, 'DEBUG_SQL_QUERIES: The SQL query.');
			debug(mysql_error(), $query);
		}
		
		// Check for SQL error
		if ($error != null) {
			debug($error, 'A SQL error has occured while executing the following query: "'.$query.'".');
		}
		return $res;
	}

	/**
	 * This function kills duplicate session entries in 'fe_sessions' for the logged in fe_user.
	 */
	public static function killDuplicateUserSessions(){
		//Get user
		$uid = self::getLoggedInUserUID();
		$ses_id = $GLOBALS["TSFE"]->fe_user->id;
		if (!empty($uid)) {
			//Look for duplicate sessions
			$ses = self::dbQuery("SELECT ses_id FROM fe_sessions WHERE ses_userid = ".intval($uid));
			if (sizeof($ses) > 1){
				//Keep the most actual session, delete the rest
				self::dbUpdateQuery("DELETE FROM fe_sessions WHERE ses_userid = ".intval($uid)." AND ses_id != '".intval($ses_id)."'");
			}
		}
		return;
	}

	/**
	 * Gets the gender of a fe_user.
	 *
	 * @param	int 		Fe_users uid.
	 * @return	boolean		TRUE in case of male, FALSE in case of female.
	 */
	public static function isMale($user_uid){
		$conf = self::getConfArray();
		$user = self::getUser($user_uid);
		
		$custom_gender = $conf['profile.']['custom_gender_db_field'];
		if ($custom_gender != null && $custom_gender != '') {
			$users_gender = $user[$custom_gender];
		} else {
			$users_gender = $user['tx_cwtcommunityuser_sex'];
		}
		
		if ($users_gender == "0") {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Checks if a user is active (not deleted and not disabled).
	 *
	 * @param	int 		Fe_users uid.
	 * @return	boolean		TRUE in case of active.
	 */
	public static function isUserActive($user_uid) {
		$res = self::dbQuery("SELECT deleted, disable FROM fe_users WHERE uid='".intval($user_uid)."';");
		if ($res[0]['disable'] == '1' || $res[0]['deleted'] == '1') {
			return false;
		}
		return true;
	}

	/**
	 * This determines if a person is a buddy of another.
	 *
	 * @param	int		This feuser's buddylist is checked.
	 * @param	int		The buddylist is checked for a buddy with this uid.
	 * @return	boolean	True if buddy is found, otherwise false.
	 */
	public static function isBuddy($feuser_uid, $buddy_uid) {
		$res = self::dbQuery('SELECT uid FROM tx_cwtcommunity_buddylist WHERE fe_users_uid = "'.intval($feuser_uid).'" AND buddy_uid = "'.intval($buddy_uid).'";');
		if ($res[0]['uid'] != null) {
			return true;
		}
		return false;
	}

	/**
	 * Generates the HTML for an error message, that is intended to be shown in the frontend.
	 *
	 * @param	string		The error message to wrap.
	 * @param	array		The $conf array supplied by the main() function.
	 * @return	string		The wrapped error message.
	 */
	public static function generateErrorMessage($message) {
		$conf = self::getConfArray();
		$wrap = $conf['common.']['messages.']['error.']['wrap'];
		return str_replace('|', $message, $wrap);
	}

	/**
	 * Generates the HTML for an info message, that is intended to be shown in the frontend.
	 *
	 * @param	string		The info message to wrap.
	 * @param	array		The $conf array supplied by the main() function.
	 * @return	string		The wrapped info message.
	 */
	public static function generateInfoMessage($message) {
		$conf = self::getConfArray();
		$wrap = $conf['common.']['messages.']['info.']['wrap'];
		return str_replace('|', $message, $wrap);
	}

	/**
	 * Generates the pic for a specific user.
	 *
	 * @param 	int			A feuser's UID.
	 * @param	array		The $conf array supplied by the main() function.
	 * @return	string		The generated image as HTML.
	 */
	public static function getUserPic($feuser_uid) {
		$conf = self::getConfArray();
		return self::getUserPicGeneric($feuser_uid, $conf['common.']['user_pic.'], $conf);
	}

	/**
	 * Generates the pic for a specific user.
	 *
	 * @param 	int			A feuser's UID.
	 * @param 	array		The image configuration from T3 setup. e.g. '$conf['common.']['user_pic.']'
	 * @return	string		The generated image as HTML.
	 */
	public static function getUserPicGeneric($feuser_uid, $imgConfig) {
		$conf = self::getConfArray();
		$cObj = self::getCObj();
		$uri = self::getPathToUserProfileImage($feuser_uid, $conf);
		return $cObj->cImage($uri, $imgConfig);
	}

	/**
	 *	Returns a string where any character not matching [a-zA-Z0-9_-] is substituted by "_"
	 *
	 *	@param	 String	String, that should be cleaned. e.g. 'My File.doc'
	 *	@return	 String	String. e.g. 'My_File.doc'
	 */
	public static function cleanFilename($filename) {
		//Initialize fileFunc object
        $fileFunc = t3lib_div::makeInstance("t3lib_basicFileFunctions");
		
		return $fileFunc->cleanFilename($filename);
	}

	/**
	 *  Makes the filename unique by appending a timestamp. (Considering, that one fe_user
	 *  can only edit one field at the same time, the filename is really unique.)
	 *
	 *  @param	 String	The filename without slashes and directory! e.g. 'MyFile.doc'
	 *  @return	 String	The filename with an appended timestamp. e.g. 'MyFile_1038737318.doc'
	 */
	public static function makeFilenameUnique($filename) {
		//Get timestamp
		$tstamp = time();
		// Explode it by '.'
		$filename = explode(".",$filename);
		// Make it unique
		$uniqueName = md5($filename[0]."_".$tstamp).".".$filename[1];
		return $uniqueName;
	}

	/**
	 * This constructs the relative path to a user's profile image. This can be used to show an image via
	 * Typo3's cImage function.
	 *
	 * @param		int		The fe user's UID.
	 * @param		array	The $conf array supplied by the main() function.
	 * @return		string	The relative path to the user's image.
	 */
	public static function getPathToUserProfileImage($feuser_uid) {
		$conf = self::getConfArray();
		$user = self::getUser($feuser_uid);
		// Check if a custom db field for image shall be used
		$custom_pic = $conf['profile.']['custom_pic_db_field'];
		$custom_path = $conf['profile.']['custom_pic_path'];
		if ($custom_pic != '' && $custom_path != '') {
			// Custom
			$filename = $user[$custom_pic];
			$uri = $custom_path;
				
		} else {
			// Default
			$filename = $user['tx_cwtcommunityuser_image'];
			$uri = self::getPathToProfileImages();
		}

		// Check if default pic shall be shown
		$custom_gender = $conf['profile.']['custom_gender_db_field'];
		if ($filename == '' || $filename == null || !file_exists($uri.'/'.$filename)) {
			if ($custom_gender != null && $custom_gender != '') {
				$users_gender = $user[$custom_gender];
			} else {
				$users_gender = $user['tx_cwtcommunityuser_sex'];
			}
			
			if ($users_gender == '0') {
				return $conf['icon_profilepic_male'];
			} else {
				return $conf['icon_profilepic_female'];
			}
		}
		return $uri.$filename;
	}

	/**
	 * Returns the path to the upload folder for profile images. This may then be used 
	 * to create an appropriate URI for displaying profile images.
	 * 
	 * @return	Part of an URI. e.g. 'uploads/tx_cwtcommunityuser/'
	 */
	public static function getPathToProfileImages() {
		return tx_cwtcommunity_lib_constants::PATH_USER_PROFILE_IMAGES;
	}
	
	/**
	 * Returns the PID of the global record storage folder.
	 *
	 * @return	int		The storage folder PID.
	 */
	public static function getGlobalStorageFolder() {
		return self::$conf['pid_storage_folder'];
	}

	/**
	 * Get URL to some page.
	 * Returns the URL to page $id with $target and an array of additional url-parameters, $urlParameters
	 *
	 * The function basically calls $this->cObj->getTypoLink_URL()
	 *
	 * @param	integer		Page id
	 * @param	string		Target value to use. Affects the &type-value of the URL, defaults to current.
	 * @param	array		Additional URL parameters to set (key/value pairs)
	 * @return	string		The resulting URL
	 * @see tslib_pibase::pi_getPageLink()
	 */
	public static function getPageLink($id, $target = '', $urlParameters = array()) {
		return self::$callerRef->pi_getPageLink($id, $target, $urlParameters);
	}
	
	/**
	 * Link a string to some page.
	 * Like pi_getPageLink() but takes a string as first parameter which will in turn be
	 * wrapped with the URL including target attribute.
	 *
	 * @param	string		The content string to wrap in <a> tags
	 * @param	integer		Page id
	 * @param	string		Target value to use. Affects the &type-value of the URL, defaults to current.
	 * @param	array		Additional URL parameters to set (key/value pairs)
	 * @return	string		The input string wrapped in <a> tags with the URL and target set.
	 * @see tslib_pibase::pi_getPageLink()
	 */
	public static function linkToPage($str, $id, $target = '', $urlParameters = array()) {
		return self::$callerRef->pi_linkToPage($str, $id, $urlParameters, $target);
	}

	/**
	 * This constructs the link to a user's profile page.
	 */
	public static function getLinkToUsersProfile($feuser_uid) {
		$conf = self::getConfArray();
		return self::getPageLink($conf['pid_profile'], "", array(tx_cwtcommunity_lib_constants::CONST_ACTION => "getviewprofile", "uid" => $feuser_uid));
	}

	/**
	 * Generates a link to the userlist page. Optionally sorting parameters may be supplied.
	 *
	 * @param	string	The sort order: asc or desc.
	 * @param	string	The column to sort by.
	 * @param	array	The typoscript configuration array.
	 * @return 	The generated link.
	 */
	public static function getLinkToUserlist($sortOrder = '', $sortColumn = '') {
		$conf = self::getConfArray();
		return self::getPageLink($conf['pid_userlist'], "", array(tx_cwtcommunity_lib_constants::ACTION_COMMON_SORT_ORDER => $sortOrder, tx_cwtcommunity_lib_constants::ACTION_COMMON_SORT_COLUMN => $sortColumn));
	}

	/**
	 *  Enables a fe_user.
	 *
	 *  @param		int		Valid UID from 'fe_users' table.
	 *  @return		void
	 */
	public static function enableUser($uid) {
		//Do the query
		$res = self::dbUpdateQuery('UPDATE fe_users SET disable = 0 WHERE uid = "'.intval($uid).'";');
		return null;
	}

	/**
	 *  Disables a fe_user.
	 *
	 *  @param		int		Valid UID from 'fe_users' table.
	 *  @return		void
	 */
	public static function disableUser($uid) {
		//Do the query
		$res = self::dbUpdateQuery('UPDATE fe_users SET disable = 1 WHERE uid = "'.intval($uid).'";');
		return null;
	}

	/**
	 *  Fetches a list of fe users according to the userlist privacy setting of each user
	 *  (Profile field: 'tx_cwtcommunityuser_userlist_visibility').
	 *
	 *  @param	string	Only displays usernames with this as first letter.
	 *  @param	string	The sort order: asc or desc.
	 *  @param	string	The fe_user column to sort the list by.
	 *  @param	string	The page to display.
	 *  @return	array	Array of fe user records.
	 */
	public static function getUsersWithFilter($letter, $sortOrder = '', $sortColumn = '', $page = null) {
		$conf = tx_cwtcommunity_lib_common::getConfArray();
	
		$sortOrderQuery = tx_cwtcommunity_lib_userlist::getUserlistSortOrder($sortOrder);
		$sortColumnQuery = tx_cwtcommunity_lib_userlist::getUserlistSortColumn($sortColumn);
		$letter = tx_cwtcommunity_lib_userlist::getUserlistLetter($letter);	
		
		$userlistCommonWhereClause = tx_cwtcommunity_lib_userlist::getUserlistCommonWhereClause();
		$userlistOrderBy = tx_cwtcommunity_lib_userlist::getUserlistOrderBy($sortOrder, $sortColumn);
		$userlistLimit = tx_cwtcommunity_lib_userlist::getUserlistLimit($page);
		
		// fetch all by default
		if ($letter == null) {
			// Fetch users
			$users = tx_cwtcommunity_lib_common::dbQuery('SELECT * FROM fe_users AS u WHERE '.$userlistCommonWhereClause.' '.$userlistOrderBy.' '.$userlistLimit);
		} else {
			// Fetch users
			$users = tx_cwtcommunity_lib_common::dbQuery('SELECT * FROM fe_users AS u WHERE '.$userlistCommonWhereClause.' AND ('.$sortColumnQuery.' LIKE '.$GLOBALS['TYPO3_DB']->fullQuoteStr($letter.'%', 'fe_users').' OR '.$sortColumnQuery.' LIKE '.$GLOBALS['TYPO3_DB']->fullQuoteStr(strtoupper($letter).'%', 'fe_users').') '.$userlistOrderBy.' '.$userlistLimit);
		}
		
		return $users;
	}
	
	/**
	 *  Gets the number of FE_USERS.
	 *
	 *  @return		int	The number of FE_USERS.
	 */
	public static function getUserCount() {
		$userlistCommonWhereClause = tx_cwtcommunity_lib_userlist::getUserlistCommonWhereClause();
		
		$res = self::dbQuery('SELECT COUNT(*) FROM fe_users AS u WHERE '.$userlistCommonWhereClause.' ');
		return $res[0]['COUNT(*)'];
	}
	
	/**
	 *  Gets the number of FE_USERS.
	 *
	 *  @return		int	The number of FE_USERS.
	 */
	public static function getUserCountWithFilter($letter) {
		$conf = tx_cwtcommunity_lib_common::getConfArray();

		// Determine the default filter column
		$sortColumn = $conf['userlist.']['default.']['sortColumn'];
		$sortColumn = $GLOBALS['TYPO3_DB']->fullQuoteStr('u.'.$sortColumn, 'fe_users');
		$sortColumn = str_replace('\'', '', $sortColumn);
		
		$userlistCommonWhereClause = tx_cwtcommunity_lib_userlist::getUserlistCommonWhereClause();
		
		$res = self::dbQuery('SELECT COUNT(*) FROM fe_users AS u WHERE '.$userlistCommonWhereClause.' AND '.$sortColumn.' LIKE '.$GLOBALS['TYPO3_DB']->fullQuoteStr($letter.'%', 'fe_users').' ');
		return $res[0]['COUNT(*)'];
	}	

	/**
	 * This method determines the pagecount for a given list size and items per page.
	 * 
	 * @param $size				The total size of the list. e.g. 10
	 * @param $itemsPerPage		The number of items per page. e.g. 5 
	 * @return int				The number of pages needed to display the list. e.g. 2
	 */
	public static function getPageCount($size, $itemsPerPage) {
		if ($size % $itemsPerPage == 0) {
			if ($size < $itemsPerPage) {
				$pageCount = 1;
			} else {
				$pageCount = ($size / $itemsPerPage);				
			}
		} else {
			$pageCount = floor($size / $itemsPerPage) + 1; 
		}
		
		if (tx_cwtcommunity_lib_constants::$DEBUG_APPLICATION) {
			debug($size, 'SIZE');
			debug($itemsPerPage, 'ITEMSPERPAGE');
			debug($pageCount, 'PAGECOUNT');	
		}
		
		return $pageCount;
	}
	
    /**
     * Checks if a fe user is in a fe group	
     * @param 	$user_uid		The User's uid
     * @param 	$fe_group_uid	The group's uid
     * @return 	boolean			True if the user is in the group
     */
    public static function isUserInGroup($user_uid, $fe_group_uid) {
    	if ($fe_group_uid != null) {
				//Explode usergroup
				$usergroups = explode(',', self::getGroupsForUser($user_uid));
				if ($usergroups != false) {
					if (in_array($fe_group_uid, $usergroups)) {
						return true;
					}	
				}
			
		}
		return false;
    }	
	
	/**
	 * Get the fe groups of a user
	 * @param 	$uid	The user's uid
	 * @return 	array	A list of fe groups
	 */
	public static function getGroupsForUser($uid){
    	$users = self::dbQuery('SELECT uid, usergroup FROM fe_users WHERE NOT deleted = 1 and uid= '.intval($uid));
		return $users[0]['usergroup'];
    }
	
	/**
	 *  Parses for strings in textToParse and replaces them with icons. The mapping between
	 *  string and icon can be made with icon records in backend.
	 *
	 *  @param	string		Input text that shall be parsed for icon replacements.
	 *  @return	string		The parsed text.
	 */
	public static function parseIcons($textToParse) {
		$conf = tx_cwtcommunity_lib_common::getConfArray();
		if ($conf["iconReplacement"]){
			$pid_icons = $conf["pid_icons"];
			//Get strings to parse from db
			$res = tx_cwtcommunity_lib_common::dbQuery("SELECT * FROM tx_cwtcommunity_icons WHERE pid =".$pid_icons." AND NOT deleted=1 AND NOT hidden=1");
			//Parse text
			for ($i=0; $i < sizeof($res); $i++){
				$textToParse = str_replace($res[$i]["string"], self::getCObj()->cImage(tx_cwtcommunity_lib_constants::PATH_ICONS.$res[$i]["icon"],array("altText" => $res[$i]["string"])), $textToParse);
			}
		}
		return $textToParse;
	}

	/**
	 *  BE FUNCTION: Gets information about frontend users.
	 *
	 *  @param	string		Only displays usernames with this as first letter.
	 *  @return	array		Array of tx_cwtcommunity_pi1_user records.
	 */
	public static function getUserlist($letter) {
		// fetch all by default
		if ($letter == null) {
			// Fetch users
			//$users = self::dbQuery('SELECT DISTINCT u.uid, u.username, u.name, u.crdate, u.lastlogin, u.disable, u.tx_cwtcommunityuser_gallery_activated, (SELECT COUNT(uid) AS COUNT FROM tx_cwtcommunity_albums WHERE cruser_id = u.uid) AS album_count FROM fe_users AS u, tx_cwtcommunity_albums AS a WHERE NOT u.deleted=1 ORDER BY u.username ASC;');
			//FIXME: Unfortunately the above query makes problems with MySQL Version 4.1.22. therefore the following workaround is used until further notice.
			$users = self::dbQuery('SELECT uid, username, name, crdate, lastlogin, disable, tx_cwtcommunityuser_gallery_activated FROM fe_users WHERE NOT deleted=1 ORDER BY username ASC;');
			for ($i = 0; $i < sizeof($users); $i++) {
				$album_count = self::dbQuery('SELECT COUNT(uid) AS album_count FROM tx_cwtcommunity_albums WHERE cruser_id = '.intval($users[$i]['uid']).';');
				$users[$i]['album_count'] = $album_count[0]['album_count'];
			}

		} else{
			// Fetch users
			//$users = self::dbQuery('SELECT DISTINCT u.uid, u.username, u.name, u.crdate, u.lastlogin, u.disable, u.tx_cwtcommunityuser_gallery_activated, (SELECT COUNT(uid) AS COUNT FROM tx_cwtcommunity_albums WHERE cruser_id = u.uid) AS album_count FROM fe_users AS u, tx_cwtcommunity_albums AS a WHERE u.username LIKE "'.$letter.'%" OR u.username LIKE "'.strtoupper($letter).'%" AND NOT u.deleted=1 ORDER BY u.username ASC;');
			//FIXME: Unfortunately the above query makes problems with MySQL Version 4.1.22. therefore the following workaround is used until further notice.
			$users = self::dbQuery(
			'SELECT uid, username, name, crdate, lastlogin, disable, tx_cwtcommunityuser_gallery_activated FROM fe_users 
			WHERE (username LIKE '. $GLOBALS['TYPO3_DB']->fullQuoteStr($letter.'%', 'fe_users')
			.' OR username LIKE '. $GLOBALS['TYPO3_DB']->fullQuoteStr(strtoupper($letter).'%', 'fe_users') .') 
			AND NOT deleted=1 ORDER BY username ASC;');
			for ($i = 0; $i < sizeof($users); $i++) {
				$album_count = self::dbQuery('SELECT COUNT(uid) AS album_count FROM tx_cwtcommunity_albums WHERE cruser_id = '.intval($users[$i]['uid']).';');
				$users[$i]['album_count'] = $album_count[0]['album_count'];
			}
		}
		return $users;
	}

	/**
	 *  BE FUNCTION: Gets information about user groups.
	 *
	 *  @return	array	Array of fe_groups records.
	 */
	public static function getGroups(){
		$res = null;
		$res = self::dbQuery('SELECT * FROM fe_groups WHERE NOT deleted = 1 AND NOT hidden = 1');
		return $res;
	}

	/**
	 *  BE FUNCTION: Gets information about fe users.
	 *
	 *  @return		array	Array of fe_user records.
	 */
	public static function getUsers() {
		$res = null;
		$res = self::dbQuery('SELECT * FROM fe_users WHERE NOT deleted = 1 AND NOT disable = 1');
		return $res;
	}
	
	/**
	 *  BE FUNCTION: Sends messages either to all frontend users (if fe_group_uid is NULL) or to a specific
	 *  usergroup.
	 *
	 *  @param	int		Uid of an usergoup from 'fe_groups'
	 *  @param	string	The subject of the mail to be sent.
	 *  @param	string	The message body.
	 *  @param	int		The fe_users uid of the user, who sends the mail.
	 */
	public static function sendMessages($fe_group_uid=null, $subject, $text, $cruser_id) {
		//Fetch users from db
		$users = self::dbQuery('SELECT uid, usergroup FROM fe_users WHERE NOT deleted = 1');
		$tstamp = time();
		$text = $GLOBALS['TYPO3_DB']->fullQuoteStr($text, 'tx_cwtcommunity_message');
		$subject = $GLOBALS['TYPO3_DB']->fullQuoteStr($subject, 'tx_cwtcommunity_message');
		$isNotificationEnabled = true;

		// send to group
		if ($fe_group_uid != null) {
			for ($i=0; $i < sizeof($users); $i++){
				//Explode usergroup
				$usergroups = explode(',', $users[$i]['usergroup']);
				if ($usergroups != false) {
					if (in_array($fe_group_uid, $usergroups)) {
						self::dbUpdateQuery('INSERT INTO tx_cwtcommunity_message (pid, fe_users_uid, subject, body, status, cruser_id, tstamp, crdate) VALUES ("'.tx_cwtcommunity_lib_common::getGlobalStorageFolder().'","'.intval($users[$i]['uid']).'",'.$subject.','.$text.',0,'.intval($cruser_id).','.$tstamp.','.$tstamp.');');
						//FIXME: The notification function does not work for messages sent from BE, because we would need the FE plugin context. :-/
//						if ($isNotificationEnabled) {
//							tx_cwtcommunity_lib_messages::sendPrivateMessageNotification($users[$i]['uid'], null);
//						}
					}
				}
			}
		} else {
			// send to all
			for ($i=0; $i < sizeof($users); $i++) {
				self::dbUpdateQuery('INSERT INTO tx_cwtcommunity_message (pid, fe_users_uid, subject, body, status, cruser_id, tstamp, crdate) VALUES ("'.tx_cwtcommunity_lib_common::getGlobalStorageFolder().'","'.intval($users[$i]['uid']).'",'.$subject.','.$text.',0,'.intval($cruser_id).','.$tstamp.','.$tstamp.');');
				//FIXME: The notification function does not work for messages sent from BE, because we would need the FE plugin context. :-/
//				if ($isNotificationEnabled) {
//					tx_cwtcommunity_lib_messages::sendPrivateMessageNotification($users[$i]['uid'], null);
//				}
			}
		}
	}

	/**
	 * BE FUNCTION: Generates java script "onClick" code, which can be used to display record in the backend.
	 *
	 * @param	string		The table name. e.g. 'fe_users'
	 * @param 	int			The record's UID.
	 */
	public static function beGenerateViewLink($table, $uid) {
		return 'top.launchView(\''.$table.'\', \''.$uid.'\'); return false;';
	}

	/**
	 * Gets the sex icon for a specific FE User.
	 *
	 *  @param		int		Valid UID from 'fe_users' table.
	 *  @return		string	The resulting HTML code.
	 */
	public static function getSexIcon($user_uid) {
		$conf = self::getConfArray();
		// Get icon according to user's sex
		$cObj = self::getCObj();
		if (tx_cwtcommunity_lib_common::isMale($user_uid)) {
			return $cObj->cImage($conf["icon_userlist_male"], "", array());
		} else {
			return $cObj->cImage($conf["icon_userlist_female"], "", array());
		}
	}

	/**
	 * Gets the isOnline icon for a specific FE User.
	 *
	 *  @param		int		Valid UID from 'fe_users' table.
	 *  @return		string	The resulting HTML code.
	 */
	public static function getIsOnlineIcon($user_uid) {
		$conf = self::getConfArray();
		// Determine if user is online
		$cObj = self::getCObj();
		if (self::isUserOnline($user_uid)){
			return $cObj->cImage($conf["icon_userlist_status_online"], array("altText" => self::getLL("icon_userlist_status_online")));
		} else {
			return $cObj->cImage($conf["icon_userlist_status_offline"], array("altText" => self::getLL("icon_userlist_status_offline")));
		}
	}

	/**
	 *  Sends a single message.
	 *
	 *  @param	int		Session user id
	 *  @param	int		Recipient user id
	 *  @param	string	The subject of the mail
	 *  @param	string	The mail body.
	 */
	public static function sendPrivateMessage($uid, $recipient_uid, $subject, $body) {
		//Do the query
		$subject = $GLOBALS['TYPO3_DB']->fullQuoteStr($subject, 'tx_cwtcommunity_message');
		$body = $GLOBALS['TYPO3_DB']->fullQuoteStr($body, 'tx_cwtcommunity_message');
		$res = tx_cwtcommunity_lib_common::dbUpdateQuery('INSERT INTO tx_cwtcommunity_message (pid, crdate, fe_users_uid, cruser_id, subject, body, status) VALUES ('.tx_cwtcommunity_lib_common::getGlobalStorageFolder().', '.time().', "'.intval($recipient_uid).'", "'.intval($uid).'", '.$subject.', '.$body.', 0);');
	}

	/**
	 * Determines if a specific FE User is online or not.
	 *
	 *  @param		int		Valid UID from 'fe_users' table.
	 *  @return		boolean	True if online; false if not.
	 */
	public static function isUserOnline($feuser_uid){
		$conf = self::getConfArray();
		// Get online status
		$last_action = self::dbQuery("SELECT ses.ses_tstamp FROM fe_users AS usr, fe_sessions AS ses WHERE usr.uid = ".intval($feuser_uid)." AND usr.uid = ses.ses_userid");
		$last_action= $last_action[0]['ses_tstamp'];
		$max_idle_time = $conf['maxIdleTime'];
		$time = time();

		$diff = $time - intval($last_action);
		if ($diff < 0){
			return true;
		}
		if (($diff / 60) < $max_idle_time){
			return true;
		}
		else{
			return false;
		}
	}

	/**
	 * Determines if the currently logged in user is allowed to access the profile of the specified fe user.
	 *
	 * @param	int		The profile's feuser_uid to check the access to.
	 * @return	boolean	True if profile access is allowed, false if not.
	 */
	public static function isProfileAccessAllowedForLoggedInUser($feuser_uid) {
		$loggedIn = self::getLoggedInUserUID();

		// Check if we have an owner or not
		if ($feuser_uid == $loggedIn) {
			// OWNER
			return true;
		} else {
			// Get access policy of target profile
			$accessPolicy = self::dbQuery('SELECT tx_cwtcommunityuser_profile_access FROM fe_users WHERE uid = "'.intval($feuser_uid).'";');
			$accessPolicy = $accessPolicy[0]['tx_cwtcommunityuser_profile_access'];

			if ($accessPolicy == tx_cwtcommunity_lib_constants::CONST_PROFILE_ACCESS_ROLE_OWNER) {
				return false;
			} elseif ($accessPolicy == tx_cwtcommunity_lib_constants::CONST_PROFILE_ACCESS_ROLE_BUDDY) {
				if (self::isBuddy($feuser_uid, $loggedIn)) {
					return true;
				}
				return false;
			} else {
				return true;
			}
		}
	}

	/**
	 * Get the PID's of the starting points that are defined for the current FE plugin.
	 *
	 * @return	string		A list of PIDs that can be used for SQL queries.
	 */
	public static function getSysfolderPIDs() {
		$pages = self::$callerRef->pi_getPidList(self::$sysfolderList, self::getCObj()->data['recursive']);
		return $pages;
	}

	/**
	 * Returns the extension's configuration array formerly known as '$conf'.
	 *
	 * @return	Array	The extension's configuration array.
	 */
	public static function getConfArray() {
		return self::$conf;
	}

	/**
	 * Returns the extension's configuration array formerly known as '$conf' without dots in array keys.
	 *
	 * @return	Array	The extension's configuration array.
	 */
	public static function getConfArrayCleaned() {
		return self::removeDotFromArray(self::$conf);
	}
	
	/**
	 * Removes the dots in the array keys in a nested array
	 * (used to clean Typoscript array)
	 *
	 * @param array $array
	 * @return array $array
	 */
	private static function removeDotFromArray($array){
		$keys = array_keys($array);
		foreach($keys as $key){
			if (is_array($array[$key])){
				$keyNew = str_replace('.', '', $key);
				$array[$keyNew] = self::removeDotFromArray($array[$key]);
				if (array_key_exists($key, $array)) {
					unset($array[$key]);
				}
			}
		}
		return $array;
	}
	
	/**
	 * Returns an instance of EXT:smarty
	 *
	 * @return smartyInstance
	 */
	public static function getSmartyInstance() {
		return self::$smartyInstance;
	}

	/**
	 * Returns a link to the abuse report page.
	 *
	 * @return
	 */
	public static function getLinkToAbuseReport() {
		$conf = self::getConfArray();
		return self::getLinkToAbuseReportForPage($conf['pid_abuse']);
	}

	/**
	 * Returns a link to the abuse report page.
	 *
	 * @return
	 */
	public static function getLinkToAbuseReportForPage($pid_abuse) {
		$url = (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] : "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		return self::getPageLink($pid_abuse, "", array("url" => $url));
	}
	
    /**
     * Sends a html mail to a specific user using the specified parameters.
     * 
     * @param 	string	The e-mail address or a comma seperated list of adresses.
     * @param	string	The subject of the mail
     * @param	string	The body of the mail
     * @param	string 	The Copy adresses comma seperated
     * @param	array	The absolute (!!) filenames of the attachments
     * @param	array 	BCC adresses. E.g. array('person1@example.org','person2@otherdomain.org' => 'Person 2 Name')
     */
	public static function sendHtmlMailSwift($email, $mail_subject, $mail_body, $cc = "", $attachments = null, $bcc = array()) {
		$conf = self::getConfArray();
    	$validMail = true;
    	$fromAddress = $conf['common.']['notification.']['mail.']['fromAddress'];
		$fromName = $conf['common.']['notification.']['mail.']['fromName'];
    	
		//Send E-Mail
		if(!is_array($email)) {
			$emailArray = t3lib_div::trimExplode(',', $email, 1);
		}
    	for ($i = 0; $i < sizeof($emailArray); $i++) {
			if (!t3lib_div::validEmail($emailArray[$i])) {
				if ( substr( $emailArray[$i], strlen( $emailArray[$i] ) - strlen( '.t3vm' ) ) !== '.t3vm' ){
					$validMail = false;
				}
			}
		}
    	if ($validMail) {
    		
			$replyToEmail = $fromAddress;
			$replyToName = $fromName;
			
			$html_start='<html><head><title>HTML-Mail</title></head><body>';
			$html_end='</body></html>';	
			// Send mail
	  	    // new TYPO3 swiftmailer code
	        $htmlMail = t3lib_div::makeInstance('t3lib_mail_Message');
	        $htmlMail->setTo($emailArray);
	        $htmlMail->setBcc($bcc);
			$htmlMail->setFrom(array($fromAddress => $fromName));
			$htmlMail->setSubject($mail_subject);
			$htmlMail->setReturnPath($fromAddress);
			$htmlMail->setCharset($GLOBALS['TSFE']->metaCharset);
			$htmlMail->setReplyTo(array($replyToEmail => $replyToName));				
			
    		// Add attachments
			if(!empty($attachments)) {
				foreach ($attachments as $attachment) {
					if(@is_readable($attachment)) {
						if($attachment) $htmlMail->attach(Swift_Attachment::fromPath($attachment));
					}
				}
			}
			$htmlMail->setBody($html_start.$mail_body.$html_end, 'text/html');
			$htmlMail->send();
		}
    }    	
	
}
if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_community/res/class.tx_cwtcommunity_lib_common.php"]) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_community/res/class.tx_cwtcommunity_lib_common.php"]);
}
?>