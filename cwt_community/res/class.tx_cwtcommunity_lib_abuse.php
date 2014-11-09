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
use TYPO3\CMS\Core\Utility\GeneralUtility;

include_once(t3lib_extMgm::extPath('cwt_community').'res/class.tx_cwtcommunity_lib_constants.php');
include_once(t3lib_extMgm::extPath('cwt_community').'res/class.tx_cwtcommunity_lib_common.php');

/**
 * Abuse report related functions used in the community.
 * 
 * @author	Sebastian Faulhaber <sebastian.faulhaber@gmx.de>
 * @package TYPO3
 * @subpackage	tx_cwtcommunity
 */


class tx_cwtcommunity_lib_abuse {
	
	/**
	*  Displays an alphabetically list of frontend users.
	*
	*  @param	Array	The array of fe_users to display.
	*  @param	boolean	True if the special search result view shall be displayed.
	*  @return	String	The generated HTML source for this view.
	*/
    public static function getViewAbuseReport($reason, $email, $url, $isSubmitted = false) {
        // Get Smarty Instance
    	$smartyInstance = tx_cwtcommunity_lib_common::getSmartyInstance();
        $conf = tx_cwtcommunity_lib_common::getConfArray();
        $tplPath = tx_cwtcommunity_lib_common::getTemplatePath($conf['template_abuse_report']);
        
        // Provide smarty with the information for the template
        $smartyInstance->assign('reason', $reason);
        $smartyInstance->assign('email', $email);
        $smartyInstance->assign('url', $url);
        $smartyInstance->assign('isSubmitted', $isSubmitted);
        $smartyInstance->assign('isError', $isError);
        $content .= $smartyInstance->display($tplPath);
        
        return $content;
    }
    
    /**
     * Insert a new abuse record entry for a specified FE User.
     *
     * @param	int		The creating user's uid.	
     * @param	string	The abuse record's reason.
     * @param	string  The email of the creator.
     * @param	string  The url of the page.
     * @return	void
     */
    public static function insertAbuseData($cruser_id, $reason, $email ,$url) { 
        // Get timestamp
        $crdate = time();
        $reason = $GLOBALS['TYPO3_DB']->fullQuoteStr($reason, 'tx_cwtcommunity_abuse'); 
        $email = $GLOBALS['TYPO3_DB']->fullQuoteStr($email, 'tx_cwtcommunity_abuse'); 
        $url = $GLOBALS['TYPO3_DB']->fullQuoteStr($url, 'tx_cwtcommunity_abuse'); 
        
        // Insert entry into db
        if (is_nan($cruser_id)){
			$cruser_uid = null;
		}
					
        $res = tx_cwtcommunity_lib_common::dbUpdateQuery("INSERT INTO tx_cwtcommunity_abuse (pid, email, reason, url ,cruser_id, crdate) VALUES (" . tx_cwtcommunity_lib_common::getGlobalStorageFolder() . ", ".$email.", ".$reason.", ".$url.", ".intval($cruser_id).", $crdate)");
        return null;
    }
    
    public static function sendAbuseNotification($reason, $email ,$url) {
    	$conf = tx_cwtcommunity_lib_common::getConfArray();
    	
    	$mail_subject = tx_cwtcommunity_lib_common::getLL('ABUSE_NOTIFICATION_MAIL_SUBJECT');
		$mail_body = tx_cwtcommunity_lib_common::getLL('ABUSE_NOTIFICATION_MAIL_BODY', array($reason, $email, $url));
		
		// Set mail sender
		$fromAddress = $conf['common.']['notification.']['mail.']['fromAddress'];
		$fromName = $conf['common.']['notification.']['mail.']['fromName'];
		$mail_headers = 'From: '.$fromName.' <'.$fromAddress.'>';			
		// Send mail
		@GeneralUtility::plainMailEncoded($conf['abuse.']['notification.']['recipient'], $mail_subject, $mail_body, $mail_headers);
    	
    }
    
    /**
     * Fetches all abuse records.
     * 
     * @return		array		A list of abuse records from table 'tx_cwtcommunity_abuse'.
     */
    public static function getAbuseData() { 
        $res = tx_cwtcommunity_lib_common::dbQuery("SELECT * FROM tx_cwtcommunity_abuse WHERE NOT hidden = 1 AND NOT deleted = 1 ORDER BY crdate DESC;");
        return $res;
    }
}
if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_community/res/class.tx_cwtcommunity_lib_abuse.php"]) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_community/res/class.tx_cwtcommunity_lib_abuse.php"]);
}
?>