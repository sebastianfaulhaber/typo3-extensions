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
class tx_cwtcommunity_lib_search {
	
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
	   
	/**
	 * @deprecated
	 */
    function executeSimpleSearch($searchstring) {
		// Limit pages
		$pages = tx_cwtcommunity_lib_common::getSysfolderPIDs();
		$users = tx_cwtcommunity_lib_common::dbQuery('SELECT * FROM fe_users f WHERE f.pid IN ('.$pages.') AND (UPPER(username) '
			.'LIKE UPPER('.$GLOBALS['TYPO3_DB']->fullQuoteStr('%'.$searchstring.'%', 'fe_users').')'
			.' OR UPPER(name) LIKE UPPER('.$GLOBALS['TYPO3_DB']->fullQuoteStr('%'.$searchstring.'%', 'fe_users').')) AND NOT deleted = "1" AND NOT disable = "1";');						
		return $users;
    }

    /**
     * Executes a search for fe users.
     *
     * @param	string	The search string.
     * @return	array	The users that were found.
     */
    function executeCustomSimpleSearch($searchstring, $sortOrder, $sortColumn, $page, $limitFeUserFields = array() ) {
    	$conf = tx_cwtcommunity_lib_common::getConfArray();
    	$searchFields = explode(',', $conf['userlist.']['search.']['simpleCustomSearchFields']);

    	//Where clause
    	for ($i=0; $i < sizeof($searchFields); $i++) {
    		$searchFieldsWhereClause.= ' '.'u.'.trim($searchFields[$i]).' LIKE UPPER('.$GLOBALS['TYPO3_DB']->fullQuoteStr('%'.$searchstring.'%', 'fe_users').')';
    		if ((sizeof($searchFields) > 1) && ($i != sizeof($searchFields)-1)) {
    			$searchFieldsWhereClause.= ' OR ';
    		}
    	}
    		
	    $userlistCommonWhereClause = tx_cwtcommunity_lib_userlist::getUserlistCommonWhereClause();
		$userlistOrderBy = tx_cwtcommunity_lib_userlist::getUserlistOrderBy($sortOrder, $sortColumn);
		$userlistLimit = tx_cwtcommunity_lib_userlist::getUserlistLimit($page);
		
    	// Check if result fields shall be limited
		if (sizeof($limitFeUserFields) < 1) {
			$feUserFields = 'u.*'; 
		} else {
			$feUserFields = implode(',u.', $limitFeUserFields);
			$feUserFields = 'u.'.$feUserFields;
		}
		
		$users = tx_cwtcommunity_lib_common::dbQuery('SELECT '.$feUserFields.' FROM fe_users AS u WHERE '.$userlistCommonWhereClause.' AND ('.$searchFieldsWhereClause.') '.$userlistOrderBy.' '.$userlistLimit);
		return $users;
    }
    
	/**
	 *  Gets the number of FE_USERS.
	 *
	 *  @return		int	The number of FE_USERS.
	 */
	public static function getUserCountCustomSimpleSearch($searchstring) {
		$conf = tx_cwtcommunity_lib_common::getConfArray();
		$searchFields = explode(',', $conf['userlist.']['search.']['simpleCustomSearchFields']);
		
	   	//Where clause
    	for ($i=0; $i < sizeof($searchFields); $i++) {
    		$searchFieldsWhereClause.= ' '.'u.'.trim($searchFields[$i]).' LIKE UPPER('.$GLOBALS['TYPO3_DB']->fullQuoteStr('%'.$searchstring.'%', 'fe_users').')';
    		if ((sizeof($searchFields) > 1) && ($i != sizeof($searchFields)-1)) {
    			$searchFieldsWhereClause.= ' OR ';
    		}
    	}
		
	    $userlistCommonWhereClause = tx_cwtcommunity_lib_userlist::getUserlistCommonWhereClause();
		
		$res = tx_cwtcommunity_lib_common::dbQuery('SELECT COUNT(*) FROM fe_users AS u WHERE '.$userlistCommonWhereClause.' AND ('.$searchFieldsWhereClause.')');
		return $res[0]['COUNT(*)'];
	}    
    
	    /**
     * Executes a search for fe users.
     *
     * @param	string	The search string.
     * @return	array	The users that were found.
     */
    function executeCustomExtendedSearch($searcharray, $sortOrder, $sortColumn, $page, $limitFeUserFields = array() ) {
    	$conf = tx_cwtcommunity_lib_common::getConfArray();

    	//Where clause
    	if (is_array($searcharray)) {
    		$keys = array_keys($searcharray);
        	for ($i=0; $i < sizeof($keys); $i++) {
        		$key = $keys[$i];
        
        		// Determine column name
				if ($searcharray[$key]['column'] != null && $searcharray[$key]['column'] != '') {
					// Override default column name
					$column = $GLOBALS['TYPO3_DB']->fullQuoteStr(trim($searcharray[$key]['column']), trim($searcharray[$key]['columnTable']));
					
				} else {
					$column = $GLOBALS['TYPO3_DB']->fullQuoteStr('u.'.trim($key), 'fe_users');
				}
				$column = str_replace('\'', '', $column);

        		$searchmode = $searcharray[$key]['mode'];
        		$searchtext = $searcharray[$key]['searchtext'];
        		
        		// Additional Config can be used to join data from other tables (than fe_users)
        		$additionalConfigTables = '';
        		$additionalConfigJoin = '';
   				if ($searcharray[$key]['additionalConfig'] != null
   					 && $searcharray[$key]['additionalConfig'] != ''
   					 && $searchtext != null && $searchtext != '' && $searchtext != '99999') {
	        		$additionalConfig = $conf['userlist.']['search.']['additionalConfig.'][$searcharray[$key]['additionalConfig'].'.'];
	        		$additionalConfigTables = $additionalConfig['tables'];
	        		$additionalConfigJoin = $additionalConfig['join']; 	        		
   				}

   				// Start the MAGIC
        		if ($searchtext != null && $searchtext != '' && $searchtext != '99999') {
        			
        			if ($searchmode != tx_cwtcommunity_lib_constants::CONST_USERLIST_SEARCHMODE_SELECT2CHECKBOX) {
        				$searchFieldsWhereClause.= ' AND ';	
        			}
        			
        			
       	    		// Set searchtext depending on mode
	        		if ($searchmode == tx_cwtcommunity_lib_constants::CONST_USERLIST_SEARCHMODE_ATEND) {
	        			$searchtext = $GLOBALS['TYPO3_DB']->fullQuoteStr('%'.$searchtext, 'fe_users');
	        			$searchFieldsWhereClause.= ' '.$column.' LIKE UPPER('.$searchtext.')';
	        			
	        		} elseif ($searchmode == tx_cwtcommunity_lib_constants::CONST_USERLIST_SEARCHMODE_ATSTART) {
	        			$searchtext = $GLOBALS['TYPO3_DB']->fullQuoteStr($searchtext.'%', 'fe_users');
	        			$searchFieldsWhereClause.= ' '.$column.' LIKE UPPER('.$searchtext.')';
	        			 
	        		} elseif ($searchmode == tx_cwtcommunity_lib_constants::CONST_USERLIST_SEARCHMODE_AGE) {
        				$searchtext = $GLOBALS['TYPO3_DB']->fullQuoteStr($searchtext, 'fe_users');
        				$searchFieldsWhereClause.= ' '.'(YEAR(CURDATE())-YEAR(FROM_UNIXTIME(date_of_birth))) - (RIGHT(CURDATE(),5)<RIGHT(FROM_UNIXTIME(date_of_birth,\'%Y-%m-%d\'),5))'.' = '.$searchtext.''; 
        			        			
        			} elseif ($searchmode == tx_cwtcommunity_lib_constants::CONST_USERLIST_SEARCHMODE_EQUALS) {
	        			$searchtext = $GLOBALS['TYPO3_DB']->fullQuoteStr($searchtext, 'fe_users');
	        			$searchFieldsWhereClause.= ' '.$column.' = '.$searchtext.'';
	        			 
	        		} elseif ($searchmode == tx_cwtcommunity_lib_constants::CONST_USERLIST_SEARCHMODE_IN) {
	        			// Implode searchtext into string
	        			if (is_array($searchtext)) {
		        			$searchtextImploded = '';
		        			for ($j = 0; $j < sizeof($searchtext); $j++) {
		        				$searchtextImploded .= $GLOBALS['TYPO3_DB']->fullQuoteStr($searchtext[$j], 'fe_users');
		        				$searchtextImploded .= ',';
		        			}
		        			
		        			//Remove last comma
		        			$searchtextImploded = substr($searchtextImploded, 0, strlen($searchtextImploded)-1);
		        			
		        			$searchFieldsWhereClause.= ' '.$column.' IN ('.$searchtextImploded.')'; 	        				
	        			} 
	        		} elseif (tx_cwtcommunity_lib_constants::CONST_USERLIST_SEARCHMODE_USERGROUP) {
	        			if (is_array($searchtext)) {
	        				for ($j = 0; $j < sizeof($searchtext); $j++) {
	        					if ($j > 0) {
	        						$searchFieldsWhereClause .= ' AND ';	
	        					}
		        				$searchFieldsWhereClause.= ' '.' ('.$column.' = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($searchtext[$j], 'fe_users').' OR '.$column.' LIKE '.$GLOBALS['TYPO3_DB']->fullQuoteStr('%,'.$searchtext[$j], 'fe_users').' OR '.$column.' LIKE '.$GLOBALS['TYPO3_DB']->fullQuoteStr($searchtext[$j].',%', 'fe_users').' OR '.$column.' LIKE '.$GLOBALS['TYPO3_DB']->fullQuoteStr('%,'.$searchtext[$j].',%', 'fe_users').')';
	        				}
	        				
	        			} else {
		        			$searchFieldsWhereClause.= ' '.' ('.$column.' = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($searchtext, 'fe_users').' OR '.$column.' LIKE '.$GLOBALS['TYPO3_DB']->fullQuoteStr('%,'.$searchtext, 'fe_users').' OR '.$column.' LIKE '.$GLOBALS['TYPO3_DB']->fullQuoteStr($searchtext.',%', 'fe_users').' OR '.$column.' LIKE '.$GLOBALS['TYPO3_DB']->fullQuoteStr('%,'.$searchtext.',%', 'fe_users').')';
	        			}
	        				        			
	        		} elseif ($searchmode != tx_cwtcommunity_lib_constants::CONST_USERLIST_SEARCHMODE_SELECT2CHECKBOX) {
	        			// Use fulltext as fallback
	        			$searchtext = $GLOBALS['TYPO3_DB']->fullQuoteStr('%'.$searchtext.'%', 'fe_users');
	        			$searchFieldsWhereClause.= ' '.$column.' LIKE UPPER('.$searchtext.')';
	        			 
	        		}
        		}
	    	}			    		
    	}
    	
    	
    	$userlistCommonWhereClause = tx_cwtcommunity_lib_userlist::getUserlistCommonWhereClause();
		$userlistOrderBy = tx_cwtcommunity_lib_userlist::getUserlistOrderBy($sortOrder, $sortColumn);
		$userlistLimit = tx_cwtcommunity_lib_userlist::getUserlistLimit($page);

		// Check if result fields shall be limited
		if (sizeof($limitFeUserFields) < 1) {
			$feUserFields = 'u.*'; 
		} else {
			$feUserFields = implode(',u.', $limitFeUserFields);
			$feUserFields = 'u.'.$feUserFields;
		}

		$users = tx_cwtcommunity_lib_common::dbQuery('SELECT '.$feUserFields.' FROM fe_users AS u '.$additionalConfigTables.' WHERE '.$userlistCommonWhereClause.' '.$searchFieldsWhereClause.' '.$additionalConfigJoin.' '.$userlistOrderBy.' '.$userlistLimit);
		 
		//echo('SELECT * FROM fe_users AS u '.$additionalConfigTables.' WHERE '.$userlistCommonWhereClause.' '.$searchFieldsWhereClause.' '.$additionalConfigJoin.' '.$userlistOrderBy.' '.$userlistLimit);
		
		// Workaround for SELECT2CHECKBOX
		if (is_array($searcharray)) {
    		$keys = array_keys($searcharray);
        	for ($i=0; $i < sizeof($keys); $i++) {
        		$usersFound = array();
        		$key = $keys[$i];
        		$searchmode = $searcharray[$key]['mode'];
        		$searchtext = $searcharray[$key]['searchtext'];
        		if ($searchtext != null && $searchtext != '' && $searchtext != '99999') {
        		    
        			if ($searchmode == tx_cwtcommunity_lib_constants::CONST_USERLIST_SEARCHMODE_SELECT2CHECKBOX) {
 
						for ($j=0; $j < sizeof($users); $j++) {
							$bin = decbin($users[$j][$key]);
							$bin = strrev($bin);
							if (substr($bin, $searchtext-1, '1') == '1') {
								$usersFound[] = $users[$j];
							}
						}
						$users = $usersFound;
						
				    }
				    			
        		}
        	}
        }
		
		//Remove fields from result
		for ($k = 0; $k < sizeof($users); $k++) {
			unset($users[$k]['password']);	
		}
		
		return $users;
    }
    
	/**
	 *  Gets the number of FE_USERS.
	 *
	 *  @return		int	The number of FE_USERS.
	 */
	public static function getUserCountCustomExtendedSearch($searcharray) {
		$conf = tx_cwtcommunity_lib_common::getConfArray();
		
	    //Where clause
    	if (is_array($searcharray)) {
    		$keys = array_keys($searcharray);
        	for ($i=0; $i < sizeof($keys); $i++) {
        		$key = $keys[$i];
        		$column = $GLOBALS['TYPO3_DB']->fullQuoteStr('u.'.trim($key), 'fe_users');
        		$column = str_replace('\'', '', $column);
        		$searchmode = $searcharray[$key]['mode'];
        		$searchtext = $searcharray[$key]['searchtext'];
        		
        		if ($searchtext != null && $searchtext != '' && $searchtext != '99999') {
        		
		    		$searchFieldsWhereClause.= ' AND ';
        			
	        		// Set searchtext depending on mode
	        		if ($searchmode == tx_cwtcommunity_lib_constants::CONST_USERLIST_SEARCHMODE_ATEND) {
	        			$searchtext = $GLOBALS['TYPO3_DB']->fullQuoteStr('%'.$searchtext, 'fe_users');
	        			$searchFieldsWhereClause.= ' '.$column.' LIKE UPPER('.$searchtext.')';
	        			
	        		} elseif ($searchmode == tx_cwtcommunity_lib_constants::CONST_USERLIST_SEARCHMODE_ATSTART) {
	        			$searchtext = $GLOBALS['TYPO3_DB']->fullQuoteStr($searchtext.'%', 'fe_users');
	        			$searchFieldsWhereClause.= ' '.$column.' LIKE UPPER('.$searchtext.')';
	        			 
	        		} elseif ($searchmode == tx_cwtcommunity_lib_constants::CONST_USERLIST_SEARCHMODE_AGE) {
	        			$searchtext = $GLOBALS['TYPO3_DB']->fullQuoteStr($searchtext, 'fe_users');
	        			$searchFieldsWhereClause.= ' '.'(YEAR(CURDATE())-YEAR(FROM_UNIXTIME(date_of_birth))) - (RIGHT(CURDATE(),5)<RIGHT(FROM_UNIXTIME(date_of_birth,\'%Y-%m-%d\'),5))'.' = '.$searchtext.''; 
	        			        			
	        		} elseif ($searchmode == tx_cwtcommunity_lib_constants::CONST_USERLIST_SEARCHMODE_EQUALS) {
	        			$searchtext = $GLOBALS['TYPO3_DB']->fullQuoteStr($searchtext, 'fe_users');
	        			$searchFieldsWhereClause.= ' '.$column.' = '.$searchtext.'';
	        			 
	        		} else {
	        			// Use fulltext as fallback
	        			$searchtext = $GLOBALS['TYPO3_DB']->fullQuoteStr('%'.$searchtext.'%', 'fe_users');
						$searchFieldsWhereClause.= ' '.$column.' LIKE UPPER('.$searchtext.')';
	        		}
	        		
        		}
	    	}			    		
    	}
		
    	$userlistCommonWhereClause = tx_cwtcommunity_lib_userlist::getUserlistCommonWhereClause();
    	$res = tx_cwtcommunity_lib_common::dbQuery('SELECT COUNT(*) FROM fe_users AS u WHERE '.$userlistCommonWhereClause.' '.$searchFieldsWhereClause.'');
    	return $res[0]['COUNT(*)'];
	}   
	
	
	/**
	 * @deprecated
	 */
    function executeExtendedSearch($searcharray) {
    	$conf = $conf = tx_cwtcommunity_lib_common::getConfArray();
    	// extend your search with typoscript config
		$params = $this->conf['search.']['additional_parameters.'];
		$keys = array_keys($params);
    	for ($i = 0; $i < sizeof($params); $i++) {
    		if ($params[$keys[$i]] != '') {
    			$searcharray[$keys[$i]] = $params[$keys[$i]];    			
    		}
    	}
    	$keys = array_keys($searcharray);
    	for ($i = 0; $i < sizeof($searcharray); $i++) {
    		if ($searcharray[$keys[$i]] != '') {
    			$search .= 'UPPER('.str_replace('__CUSTOM_SEARCH__', '', $keys[$i]).') LIKE UPPER('.$GLOBALS['TYPO3_DB']->fullQuoteStr('%'.$searcharray[$keys[$i]].'%', 'fe_users').') AND ';    			
    		}
    	}
    	$users = tx_cwtcommunity_lib_common::dbQuery('SELECT * FROM fe_users f WHERE '.$search.' NOT deleted = "1" AND NOT disable = "1";');
		return $users;    	
    }

    /**
    *  Displays the search form
    *
    * @param	view			The type of view to be displayed 'simple', 'extended' or 'result'.
    * @param	searchtext		The searchtext in case of a simple search.
    * @param	numberSearchResults	The number of results returned by the search.
    * @param 	searcharray 	The search criteria in case of the extended search
    * @param 	isSubmitted		If true the extended search form has been submitted
    * @param 	searchResultContent	The resulting userlist content for the extended search
    * @return	string			The generated HTML content.
    */
    function getViewSearch($view, $searchtext = '', $numberSearchResults = 0, $searcharray = null, $isSubmitted = false, $searchResultContent = ''){
		// Get Smarty Instance
		$smartyInstance = tx_cwtcommunity_lib_common::getSmartyInstance();
        $conf = tx_cwtcommunity_lib_common::getConfArray();
        $cObj = tx_cwtcommunity_lib_common::getCObj();
        $tplPath = tx_cwtcommunity_lib_common::getTemplatePath($conf['template_search']);
        
		
		if ($view == tx_cwtcommunity_lib_constants::VIEW_SEARCH_EXTENDED) {
			$countries = tx_cwtcommunity_lib_common::dbQuery('SELECT uid,cn_short_en FROM static_countries s;');
			$languages = tx_cwtcommunity_lib_common::dbQuery('SELECT uid,lg_name_en FROM static_languages s;');
        	$zones = tx_cwtcommunity_lib_common::dbQuery('SELECT uid,zn_name_local FROM static_country_zones s ORDER BY zn_name_local ASC;');
        
			$form_action_search_extended = tx_cwtcommunity_lib_common::getPageLink($conf['pid_search'],'', array(tx_cwtcommunity_lib_constants::CONST_ACTION => tx_cwtcommunity_lib_constants::ACTION_SEARCH_SHOW_FORM));
			
			// provide smarty with the information for the template
			$smartyInstance->assign('search', $form_action_search_extended);
			$smartyInstance->assign('search_result', $searchResultContent);
			$smartyInstance->assign('isSubmitted', $isSubmitted);
			$smartyInstance->assign('searcharray', $searcharray);
			$smartyInstance->assign('countries', $countries);
			// TODO: array should be created in the template
			$smartyInstance->assign('numberResults', array($numberSearchResults));
		} elseif ($view == tx_cwtcommunity_lib_constants::VIEW_SEARCH_SIMPLE) {
			
			$form_action_search = tx_cwtcommunity_lib_common::getPageLink($conf['pid_userlist'], '', array(tx_cwtcommunity_lib_constants::CONST_ACTION => tx_cwtcommunity_lib_constants::ACTION_SEARCH_SHOW_RESULT));
			
			// provide smarty with the information for the template
			$smartyInstance->assign('search', $form_action_search);
			
		} elseif ($view == tx_cwtcommunity_lib_constants::VIEW_SEARCH_RESULT) {
			// provide smarty with the information for the template
			// TODO: array should be created in the template 
			$smartyInstance->assign('searchtext_numberResults',  array($searchtext, $numberSearchResults));
		}
		
		// Provide smarty with the information for the template     
		$smartyInstance->assign('view', $view);
        
         
        $content .= $smartyInstance->display($tplPath);
        
        
        
		return $content;
    }       
}
if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_community/res/class.tx_cwtcommunity_lib_search.php"]) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_community/res/class.tx_cwtcommunity_lib_search.php"]);
}
?>