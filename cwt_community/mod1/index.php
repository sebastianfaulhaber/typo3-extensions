<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Sebastian Faulhaber <sebastian.faulhaber@gmx.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */


$LANG->includeLLFile('EXT:cwt_community/mod1/locallang.xml');
require_once(PATH_t3lib . 'class.t3lib_scbase.php');
include_once(t3lib_extMgm::extPath('cwt_community').'res/class.tx_cwtcommunity_lib_constants.php');
include_once(t3lib_extMgm::extPath('cwt_community').'res/class.tx_cwtcommunity_lib_common.php');
include_once(t3lib_extMgm::extPath('cwt_community').'res/class.tx_cwtcommunity_lib_gallery.php');
include_once(t3lib_extMgm::extPath('cwt_community').'res/class.tx_cwtcommunity_lib_messages.php');
include_once(t3lib_extMgm::extPath('cwt_community').'res/class.tx_cwtcommunity_lib_abuse.php');
$BE_USER->modAccess($MCONF,1);    // This checks permissions and exits if the users has no permission for entry.
// DEFAULT initialization of a module [END]



/**
 * Module 'Community Admin' for the 'cwt_community' extension.
 *
 * @author    Sebastian Faulhaber <sebastian.faulhaber@gmx.de>
 * @package    TYPO3
 * @subpackage    tx_cwtcommunity
 */
class  tx_cwtcommunity_module1 extends t3lib_SCbase {
	var $pageinfo;

	/**
	 * Initializes the Module
	 * @return    void
	 */
	function init()    {
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		parent::init();

		/*
		 if (t3lib_div::_GP('clear_all_cache'))    {
		 $this->include_once[] = PATH_t3lib.'class.t3lib_tcemain.php';
		 }
		 */
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return    void
	 */
	function menuConfig()    {
		global $LANG;
		$this->MOD_MENU = Array (
                        'function' => Array (
                            '1' => $LANG->getLL('function1'),
                            '2' => $LANG->getLL('function2'),
                            '3' => $LANG->getLL('function3'),
		)
		);
		parent::menuConfig();
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return    [type]        ...
	 */
	function main()    {
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))    {

			// Draw the header.
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form='<form action="" method="post" enctype="multipart/form-data">';

			// JavaScript
			$this->doc->JScode = '
                            <script language="javascript" type="text/javascript">
                                script_ended = 0;
                                function jumpToUrl(URL)    {
                                    document.location = URL;
                                }
                            </script>
                        ';
			$this->doc->postCode='
                            <script language="javascript" type="text/javascript">
                                script_ended = 1;
                                if (top.fsMod) top.fsMod.recentIds["web"] = 0;
                            </script>
                        ';

			$headerSection = $this->doc->getHeader('pages', $this->pageinfo, $this->pageinfo['_thePath']) . '<br />'
			. $LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path') . ': ' . t3lib_div::fixed_lgd_cs($this->pageinfo['_thePath'], -50);

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
			$this->content.=$this->doc->divider(5);


			// Render content:
			$this->moduleContent();


			// ShortCut
			if ($BE_USER->mayMakeShortcut())    {
				$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
			}

			$this->content.=$this->doc->spacer(10);
		} else {
			// If no access or if ID == zero

			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
		}

	}

	/**
	 * Prints out the module HTML
	 *
	 * @return    void
	 */
	function printContent()    {

		$this->content.=$this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content
	 *
	 * @return    void
	 */
	function moduleContent()    {
		global $LANG;
		$content.= $GLOBALS['BACK_PATH'];
		switch((string)$this->MOD_SETTINGS['function'])    {
			/***********************************************
			 * FUNCTION: User administration
			 ************************************************/
			case 1:
				//Get action value
				$action = null;
				$action = t3lib_div::_GET(tx_cwtcommunity_lib_constants::CONST_ACTION);

				//Decide what to do
				if ($action == null || $action == tx_cwtcommunity_lib_constants::BE_ACTION_USER_ADMINISTRATION) {
					//Get letter
					$letter = t3lib_div::_GET('letter');
					//Get the model
					$users = tx_cwtcommunity_lib_common::getUserlist($letter);
					//Generate the view
					$content.= $this->getViewUserAdministration($users);
				} elseif ($action == tx_cwtcommunity_lib_constants::BE_ACTION_USER_ADMINISTRATION_ENABLED) {
					//Get uid
					$uid = t3lib_div::_GET('uid');
					//Do the action
					$res = tx_cwtcommunity_lib_common::enableUser($uid);
					//Generate the view
					$content.= $this->getViewUserAdministrationEnabled();
				} elseif ($action == tx_cwtcommunity_lib_constants::BE_ACTION_USER_ADMINISTRATION_DISABLED) {
					//Get uid
					$uid = t3lib_div::_GET('uid');
					//Do the action
					$res = tx_cwtcommunity_lib_common::disableUser($uid);
					//Generate the view
					$content.= $this->getViewUserAdministrationDisabled();
				} elseif ($action == tx_cwtcommunity_lib_constants::BE_ACTION_GALLERY) {
					$feuser_uid = t3lib_div::_GET(tx_cwtcommunity_lib_constants::CONST_USER_UID);
					//Generate the view
					$content.= $this->getViewUserAdministrationGallery($feuser_uid);
				} elseif ($action == tx_cwtcommunity_lib_constants::BE_ACTION_GALLERY_COMMENTS) {
					$photo_uid = t3lib_div::_GET(tx_cwtcommunity_lib_constants::CONST_PHOTO_UID);
					//Generate the view
					$content.= $this->getViewUserAdministrationGalleryComments($photo_uid);
				}
				$this->content.=$this->doc->section($LANG->getLL('viewUserAdministration_title'),$content,1,1);
				break;
			/***********************************************
			* FUNCTION: Mailing
			************************************************/
			case 2:
				//Get action value
				$action = null;
				$action = t3lib_div::_POST(tx_cwtcommunity_lib_constants::CONST_ACTION);

				//Decide what to do
				if ($action == null || $action == '') {
					//Get fe_groups
					$fe_groups = tx_cwtcommunity_lib_common::getGroups();
					$fe_users = tx_cwtcommunity_lib_common::getUsers();
					//Generate content
					$content.= $this->getViewMailing($fe_groups, $fe_users);
					$this->content.=$this->doc->section($LANG->getLL('function2'),$content,1,1);
				}
				else if ($action == tx_cwtcommunity_lib_constants::BE_ACTION_MESSAGE_PREVIEW){
					//Get fe_groups
					$fe_groups = tx_cwtcommunity_lib_common::getGroups();
					$fe_users = tx_cwtcommunity_lib_common::getUsers();
					//Generate content
					$content.= $this->getViewMailingPreview($fe_groups,$fe_users);
					$this->content.=$this->doc->section($LANG->getLL('function2'),$content,1,1);
				}
				else if ($action == tx_cwtcommunity_lib_constants::BE_ACTION_MESSAGE_SEND){
					//Get button value
					$submit = t3lib_div::_POST('submit');
					if ($submit == $LANG->getLL('viewMailing_submitmessage')){
						//Get fe_group_uid
						$fe_group_uid = t3lib_div::_POST('fe_group');
						//Generate content
						$content.= $this->getViewMailingResult($fe_group_uid);
						$this->content.=$this->doc->section($LANG->getLL('function2'),$content,1,1);
					}
					else if ($submit == $LANG->getLL('viewMailing_modifymessage')){
						//Get fe_groups
						$fe_groups = tx_cwtcommunity_lib_common::getGroups();
						$fe_users = tx_cwtcommunity_lib_common::getUsers();
						//Generate content
						$content.= $this->getViewMailing($fe_groups, $fe_users);
						$this->content.=$this->doc->section($LANG->getLL('function2'),$content,1,1);
					}
					else if ($submit == $LANG->getLL('viewMailing_cancelmessage')){
						//Clear post vars
						$GLOBALS['HTTP_POST_VARS'] = null;
						//Get fe_groups
						$fe_groups = tx_cwtcommunity_lib_common::getGroups();
						$fe_users = tx_cwtcommunity_lib_common::getUsers();
						//Generate content
						$content.= $this->getViewMailing($fe_groups, $fe_users);
						$this->content.=$this->doc->section($LANG->getLL('function2'),$content,1,1);
					}
				}
				break;
			/***********************************************
			* FUNCTION: Abuse report administration
			************************************************/
			case 3:
				// Get the model
				$abuse_reports = tx_cwtcommunity_lib_abuse::getAbuseData();

				// Generate the view
				$content.= $this->getViewAbuseReport($abuse_reports);
				$this->content .= $this->doc->section($LANG->getLL('ABUSE_VIEW_TITLE'), $content, 1, 1);
				break;
		}
	}


	/**
	 *  Display the mailing view. Administrators can send messages to all users
	 *   and / or groups of users.
	 *
	 *  @param $fe_users Fe groups array from tx_cwtcommunity_lib_common::getGroups()
	 *  @return $content The generated content.
	 */
	function getViewMailing($fe_groups, $fe_users){
		global $LANG;
		//Init some vars
		$content = null;
		$doc = get_object_vars($this->doc);

		//Output description
		$content.= $LANG->getLL('viewMailing_description').'<br><br>';

		//Starting formular
		$content.='<form action="" method="POST">';
		$content.='<input name="'.tx_cwtcommunity_lib_constants::CONST_ACTION.'" type="hidden" value="'.tx_cwtcommunity_lib_constants::BE_ACTION_MESSAGE_PREVIEW.'">';
		$content.='<table width="100%">';
		$content.='<tr bgcolor="'.$doc['bgColor5'].'">';
		$content.='<td colspan="2"><b>'.$LANG->getLL('viewMailing_newmessage_1').'</td>';
		$content.='</tr>';
		$content.='<tr>';
		$content.='<td valign="top">'.$LANG->getLL('viewMailing_cruser_id').'</td>';
		$content.='<td><select name="cruser_id">';
		//Generate options from db result
		$cruser_id = t3lib_div::_POST('cruser_id');
		for ($i=0;$i < sizeof($fe_users) ; $i++){
			if ($cruser_id == $fe_users[$i]['uid']){
				$content.= '<option value="'.$fe_users[$i]['uid'].'" selected="selected">'.$fe_users[$i]['username'].'</option>';
			}
			else{
				$content.= '<option value="'.$fe_users[$i]['uid'].'">'.$fe_users[$i]['username'].'</option>';
			}
		}
		$content.='</select></td>';
		$content.='</tr>';
		$content.='<tr>';
		$content.='<td valign="top">'.$LANG->getLL('viewMailing_title').'</td>';
		$content.='<td><input name="title" type="text" size="51" value="'.t3lib_div::_POST('title').'"></td>';
		$content.='</tr>';
		$content.='<tr>';
		$content.='<td valign="top">'.$LANG->getLL('viewMailing_text').'</td>';
		$content.='<td><textarea name="text" cols="50" rows="10">'.t3lib_div::_POST('text').'</textarea></td>';
		$content.='</tr>';
		$content.='<tr>';
		$content.='<td valign="top">'.$LANG->getLL('viewMailing_group').'</td>';
		$content.='<td><select name="fe_group">';
		$content.='<option value="" ></option>';
		//Generate options from db result
		$fe_group = t3lib_div::_POST('fe_group');
		for ($i=0;$i < sizeof($fe_groups) ; $i++){
			if ($fe_group == $fe_groups[$i]['uid']){
				$content.= '<option value="'.$fe_groups[$i]['uid'].'" selected="selected">'.$fe_groups[$i]['title'].'</option>';
			}
			else{
				$content.= '<option value="'.$fe_groups[$i]['uid'].'">'.$fe_groups[$i]['title'].'</option>';
			}
		}
		$content.='</select></td>';
		$content.='</tr>';
		$content.='<tr>';
		$content.='<td align="right" colspan="2"><input type="submit" value="'.$LANG->getLL('viewMailing_previewmessage').'"></td>';
		$content.='</tr>';

		$content.='</table>';
		$content.='</form>';

		//Legend
		$content.= $this->doc->divider(5);

		//Return
		return $content;
	}

	/* getViewMailingPreview($fe_groups)
	 *
	 *  Display the mailing preview view.
	 *
	 *  @param $fe_users Fe groups array from tx_cwtcommunity_lib_common::getGroups()
	 *  @return $content The generated content.
	 */
	function getViewMailingPreview($fe_groups, $fe_users){
		global $LANG;
		//Init some vars
		$content = null;
		$doc = get_object_vars($this->doc);
		$username = tx_cwtcommunity_lib_common::dbQuery('SELECT username FROM fe_users WHERE uid = '.intval(t3lib_div::_POST('cruser_id')));
		$username = $username[0]['username'];
		if (t3lib_div::_POST('fe_group') != null && t3lib_div::_POST('fe_group') != ''){
			$fe_group = tx_cwtcommunity_lib_common::dbQuery('SELECT title FROM fe_groups WHERE uid = '.intval(t3lib_div::_POST('fe_group')));
			$fe_group = $fe_group[0]['title'];
		}

		//Output description
		$content.= $LANG->getLL('viewMailing_description').'<br><br>';

		//Starting formular
		$content.='<form action="" method="POST">';
		//Hidden fields
		$content.='<input name="'.tx_cwtcommunity_lib_constants::CONST_ACTION.'" type="hidden" value="'.tx_cwtcommunity_lib_constants::BE_ACTION_MESSAGE_SEND.'">';
		$content.='<input name="cruser_id" type="hidden" value="'.t3lib_div::_POST('cruser_id').'">';
		$content.='<input name="title" type="hidden" value="'.t3lib_div::_POST('title').'">';
		$content.='<input name="text" type="hidden" value="'.t3lib_div::_POST('text').'">';
		$content.='<input name="fe_group" type="hidden" value="'.t3lib_div::_POST('fe_group').'">';
		$content.='<table width="100%">';
		$content.='<tr bgcolor="'.$doc['bgColor5'].'">';
		$content.='<td colspan="2"><b>'.$LANG->getLL('viewMailing_newmessage_2').'</td>';
		$content.='</tr>';
		$content.='<tr>';
		$content.='<td valign="top" width="200">'.$LANG->getLL('viewMailing_cruser_id').'</td>';
		$content.='<td>'.$username.'</td>';
		$content.='</tr>';
		$content.='<tr>';
		$content.='<td valign="top" width="200">'.$LANG->getLL('viewMailing_title').'</td>';
		$content.='<td>'.t3lib_div::_POST('title').'</td>';
		$content.='</tr>';
		$content.='<tr>';
		$content.='<td valign="top">'.$LANG->getLL('viewMailing_text').'</td>';
		$content.='<td>'.t3lib_div::_POST('text').'</td>';
		$content.='</tr>';
		$content.='<tr>';
		$content.='<td valign="top">'.$LANG->getLL('viewMailing_group').'</td>';
		$content.='<td>'.$fe_group.'</td>';
		$content.='</tr>';
		$content.='<tr>';
		$content.='<td align="right" colspan="2">';
		$content.='<input type="submit" name="submit" value="'.$LANG->getLL('viewMailing_submitmessage').'">';
		$content.='<input type="submit" name="submit" value="'.$LANG->getLL('viewMailing_modifymessage').'">';
		$content.='<input type="submit" name="submit" value="'.$LANG->getLL('viewMailing_cancelmessage').'">';
		$content.='</td>';
		$content.='</tr>';

		$content.='</table>';
		$content.='</form>';
		//Legend
		$content.= $this->doc->divider(5);

		//Return
		return $content;
	}

	/* getViewMailingResult($fe_group_uid)
	 *
	 *  Display the mailing result view.
	 *
	 *  @return $content The generated content.
	 */
	function getViewMailingResult($fe_group_uid){
		global $LANG;
		//Init some vars
		$content = null;
		$doc = get_object_vars($this->doc);

		//Output description
		//$content.= $LANG->getLL('viewMailing_description').'<br><br>';
		$cruser_id = t3lib_div::_POST('cruser_id');
		$subject = t3lib_div::_POST('title');
		$text = t3lib_div::_POST('text');

		//Send messages
		tx_cwtcommunity_lib_common::sendMessages($fe_group_uid, $subject, $text, $cruser_id);

		//Starting content
		$content .= $LANG->getLL('viewMailing_result');
		$content .= '<br>';
		$content .= '<a style="'.$this->linkStyle.'" href="?id='.$this->id.'&M=web_txcwtcommunityM1'.'">'.$LANG->getLL('viewMailing_backtomailing').'</a>';
		$content .= '<br>';
		//Legend
		$content.= $this->doc->divider(5);

		//Return
		return $content;
	}

	/**
	 *  Display the main user dministration view. BE users can disable and enable users here.
	 *
	 *  @param	array	Users array from tx_cwtcommunity_lib_common::getUserlist($letter)
	 *  @return	string	The generated content.
	 */
	function getViewUserAdministration($users) {
		global $LANG;
		//Init some vars
		$content = null;
		$switch = true;
		$doc = get_object_vars($this->doc);

		//Output description
		$content.= $LANG->getLL('viewUserAdministration_description').'<br><br>';

		//Create the row
		$content .= '<div style="'.$doc['defStyle'].'">';
		$letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y');
		$content .= '<a style="'.$this->linkStyle.'" href="?id='.$this->id.'&M=web_txcwtcommunityM1'.'&'.tx_cwtcommunity_lib_constants::CONST_ACTION.'='.tx_cwtcommunity_lib_constants::BE_ACTION_USER_ADMINISTRATION.'">'.$LANG->getLL('all').'</a>|';
		for ($y=0; $y < sizeof($letters); $y++) {
			$content .= '<a style="'.$this->linkStyle.'" href="?id='.$this->id.'&M=web_txcwtcommunityM1'.'&'.tx_cwtcommunity_lib_constants::CONST_ACTION.'='.tx_cwtcommunity_lib_constants::BE_ACTION_USER_ADMINISTRATION.'&letter='.strtolower($letters[$y]).'">'.$letters[$y].'</a>|';
		}
		$content .= '<a style="'.$this->linkStyle.'" href="?id='.$this->id.'&M=web_txcwtcommunityM1'.'&'.tx_cwtcommunity_lib_constants::CONST_ACTION.'='.tx_cwtcommunity_lib_constants::BE_ACTION_USER_ADMINISTRATION.'&letter=z">Z</a>';
		$content .= '<br><br></div>';

		//Display the user records
		$content .= '<table>';
		$content .= '<tr bgcolor="'.$doc['bgColor5'].'">';
		$content .= '<td><b>'.$LANG->getLL('username').'</small></b></td>';
		$content .= '<td><b>'.$LANG->getLL('name').'</b></td>';
		$content .= '<td><b>'.$LANG->getLL('crdate').'</b></td>';
		$content .= '<td><b>'.$LANG->getLL('lastlogin').'</b></td>';
		$content .= '<td><b>'.$LANG->getLL('GALLERY_GALLERY_ACTIVATED').'</b></td>';
		$content .= '<td><b>'.''.'</b></td>';
		$content .= '<td><b></b></td>';
		$content .= '<td><b></b></td>';
		$content .= '<td><b></b></td>';
		$content .= '<td><b></b></td>';
		$content .= '</tr>';

		for($i = 0; $i < sizeof($users); $i++) {
			// Alternating row colors
			if ($switch == true){
				$switch = false;
				$content .= '<tr bgcolor="'.$doc['bgColor4'].'">';
			} elseif($switch == false) {
				$switch = true;
				$content .= '<tr>';
			}
			// Gallery activated?
			if ($users[$i]['tx_cwtcommunityuser_gallery_activated'] == '0') {
				$galleryActivated = '';
			} else {
				$galleryActivated = 'X';
			}
			// Beginning row content
			$content .= '<td>'.$users[$i]['username'].'</td>';
			$content .= '<td>'.$users[$i]['name'].'</td>';
			$content .= '<td>'.t3lib_BEfunc::datetime($users[$i]['crdate']).'</td>';
			$content .= '<td>'.t3lib_BEfunc::datetime($users[$i]['lastlogin']).'</td>';
			$content .= '<td align="center">'.$galleryActivated.'</td>';
			$content .= '<td><a style="'.$this->linkStyle.'" href="?id='.$this->id.'&M=web_txcwtcommunityM1'.'&'.tx_cwtcommunity_lib_constants::CONST_ACTION.'='.tx_cwtcommunity_lib_constants::BE_ACTION_GALLERY.'&'.tx_cwtcommunity_lib_constants::CONST_USER_UID.'='.$users[$i]['uid'].'">'.$users[$i]['album_count'].' '.$LANG->getLL('GALLERY_ALBUMS').'</a></td>';
			// User enabled
			if ($users[$i]['disable'] == 0) {
				$content .= '<td><a style="'.$this->linkStyle.'" href="?id='.$this->id.'&M=web_txcwtcommunityM1'.'&'.tx_cwtcommunity_lib_constants::CONST_ACTION.'='.tx_cwtcommunity_lib_constants::BE_ACTION_USER_ADMINISTRATION_DISABLED.'&uid='.$users[$i]['uid'].'"><img src="'.TYPO3_MOD_PATH.'action_disable.gif" alt="'.$LANG->getLL('viewUserAdministration_disable').'" border="0"></a></td>';
			} elseif ($users[$i]['disable'] == 1) {
				// User disabled
				$content .= '<td><a style="'.$this->linkStyle.'" href="?id='.$this->id.'&M=web_txcwtcommunityM1'.'&'.tx_cwtcommunity_lib_constants::CONST_ACTION.'='.tx_cwtcommunity_lib_constants::BE_ACTION_USER_ADMINISTRATION_ENABLED.'&uid='.$users[$i]['uid'].'"><img src="'.TYPO3_MOD_PATH.'action_enable.gif" alt="'.$LANG->getLL('viewUserAdministration_enable').'" border="0"></a></td>';
			}
			$content .= '<td><a href="#" onclick="'.htmlspecialchars(tx_cwtcommunity_lib_common::beGenerateViewLink('fe_users', $users[$i]['uid'])).'"><img src="'.TYPO3_MOD_PATH.'action_view.gif" alt="'.$LANG->getLL('COMMON_VIEW').'" border="0"></a></td>';
			$content .= '<td><a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick('&id='.$this->id.'&edit[fe_users]['.$users[$i]['uid'].']=edit',$GLOBALS['BACK_PATH'],t3lib_div::getIndpEnv('REQUEST_URI'))).'"><img src="'.TYPO3_MOD_PATH.'action_edit.gif" alt="'.$LANG->getLL('COMMON_EDIT').'" border="0"></a></td>';
			$content .= '<td><a href="'.htmlspecialchars($GLOBALS['SOBE']->doc->issueCommand('&id='.$this->id.'&cmd[fe_users]['.$users[$i]['uid'].'][delete]=1')).'" onclick="'.htmlspecialchars("return confirm('Want to delete?');").'"><img src="'.TYPO3_MOD_PATH.'action_delete.gif" alt="'.$LANG->getLL('COMMON_DELETE').'" border="0"></a></td>';
			$content .= '</tr>';
		}
		$content .= '</table>';

		// Legend
		$content.= $this->doc->divider(10);
		$content.= '<b>'.$LANG->getLL('legend').'</b><br><br>';
		$content.= '<img src="'.TYPO3_MOD_PATH.'action_enable.gif" border="0" alt="'.$LANG->getLL('ViewCheckForBrokenLinks_enable').'">&nbsp;'.$LANG->getLL('viewUserAdministration_enable');
		$content.= '<br><img src="'.TYPO3_MOD_PATH.'action_disable.gif" border="0" alt="'.$LANG->getLL('ViewLinksToApprove_delete').'">&nbsp;'.$LANG->getLL('viewUserAdministration_disable').'<br>';

		//return
		return $content;
	}

	/**
	 *  Display the main abuse dministration view.
	 *
	 *  @param	array	A list of abuse records from table 'tx_cwtcommunity_abuse'.
	 *  @return	string	The generated content.
	 */
	function getViewAbuseReport($abuse_reports) {
		global $LANG;
		//Init some vars
		$content = null;
		$switch = true;
		$doc = get_object_vars($this->doc);

		// Output description
		$content.= $LANG->getLL('ABUSE_VIEW_DESC').'<br><br>';

		//Display the records
		$content .= '<table>';
		$content .= '<tr bgcolor="'.$doc['bgColor5'].'">';
		$content .= '<td><b>'.$LANG->getLL('crdate').'</small></b></td>';
		$content .= '<td><b>'.$LANG->getLL('ABUSE_EMAIL').'</small></b></td>';
		$content .= '<td><b>'.$LANG->getLL('ABUSE_REASON').'</b></td>';
		$content .= '<td><b>'.$LANG->getLL('ABUSE_URL').'</b></td>';
		$content .= '<td><b></b></td>';
		$content .= '<td><b></b></td>';
		$content .= '</tr>';

		for($i = 0; $i < sizeof($abuse_reports); $i++) {
			// Alternating row colors
			if ($switch == true){
				$switch = false;
				$content .= '<tr bgcolor="'.$doc['bgColor4'].'">';
			} elseif($switch == false) {
				$switch = true;
				$content .= '<tr>';
			}
			// Beginning row content
			$content .= '<td>'.t3lib_BEfunc::datetime($abuse_reports[$i]['crdate']).'</td>';
			$content .= '<td>'.$abuse_reports[$i]['email'].'</td>';
			$content .= '<td>'.substr($abuse_reports[$i]['reason'], 0, 40).'...</td>';
			$content .= '<td><a href="'.$abuse_reports[$i]['url'].'" target="_blank" style="'.$this->linkStyle.'">'.substr($abuse_reports[$i]['url'], 0, 40).'...</a></td>';
			$content .= '<td><a href="#" onclick="'.htmlspecialchars(tx_cwtcommunity_lib_common::beGenerateViewLink('tx_cwtcommunity_abuse', $abuse_reports[$i]['uid'])).'"><img src="'.TYPO3_MOD_PATH.'action_view.gif" alt="'.$LANG->getLL('COMMON_VIEW').'" border="0"></a></td>';
			$content .= '<td><a href="'.htmlspecialchars($GLOBALS['SOBE']->doc->issueCommand('&id='.$this->id.'&cmd[tx_cwtcommunity_abuse]['.$abuse_reports[$i]['uid'].'][delete]=1')).'" onclick="'.htmlspecialchars("return confirm('Want to delete?');").'"><img src="'.TYPO3_MOD_PATH.'action_delete.gif" alt="'.$LANG->getLL('COMMON_DELETE').'" border="0"></a></td>';
			$content .= '</tr>';
		}
		$content .= '</table>';

		// Legend
		$content.= $this->doc->divider(10);
		return $content;
	}

	/**
	 *  Displays the main gallery administration view.
	 *
	 *  @param	int		The fe_user to administrate.
	 *  @return	string	The generated content.
	 */
	function getViewUserAdministrationGallery($feuser_uid) {
		global $LANG;
		//Init some vars
		$content = null;
		$switch = true;
		$doc = get_object_vars($this->doc);
		$albums = tx_cwtcommunity_lib_gallery::getAlbums($feuser_uid);
		$user = tx_cwtcommunity_lib_common::getUser($feuser_uid);

		//Output description
		//$content.= $LANG->getLL('viewUserAdministration_description').'<br><br>';
		$content .= ''.$LANG->getLL('GALLERY_VIEWING').' "'.$user['username'].'"'.'<br/><br/>';
		$content .= '<a style="'.$this->linkStyle.'" href="?id='.$this->id.'&M=web_txcwtcommunityM1'.'">'.$LANG->getLL('GALLERY_BACK_USERLIST').'</a><br/><br/>';

		//Display the records
		$content .= '<table>';
		$content .= '<tr bgcolor="'.$doc['bgColor5'].'">';
		$content .= '<td><b>'.$LANG->getLL('GALLERY_ALBUM_TITLE').'</b></td>';
		$content .= '<td><b>'.$LANG->getLL('GALLERY_PHOTO_COUNT').'</b></td>';
		$content .= '<td><b>'.$LANG->getLL('GALLERY_PHOTO_UID').'</b></td>';
		$content .= '<td><b>'.$LANG->getLL('GALLERY_PHOTO_TITLE').'</b></td>';
		$content .= '<td><b>'.$LANG->getLL('GALLERY_PHOTO_FILENAME').'</b></td>';
		$content .= '<td><b>'.$LANG->getLL('GALLERY_COMMENT_COUNT').'</b></td>';
		$content .= '<td><b></b></td>';
		$content .= '<td><b></b></td>';
		$content .= '<td><b></b></td>';
		$content .= '</tr>';

		for($i = 0; $i < sizeof($albums); $i++) {
			// Get photos for album
			$album = $albums[$i];
			$photo_count = tx_cwtcommunity_lib_gallery::getPhotoCount($album['uid']);
			$photos = tx_cwtcommunity_lib_gallery::getPhotos($album['uid']);
				
			$content .= '<tr bgcolor="'.$doc['bgColor4'].'">';

			// Beginning row content
			$content .= '<td>'.$album['title'].'</td>';
			$content .= '<td align="right">'.$photo_count.'</td>';
			$content .= '<td>&nbsp;</td>';
			$content .= '<td>&nbsp;</td>';
			$content .= '<td>&nbsp;</td>';
			$content .= '<td>&nbsp;</td>';
			$content .= '<td><a href="#" onclick="'.htmlspecialchars(tx_cwtcommunity_lib_common::beGenerateViewLink('tx_cwtcommunity_albums', $album['uid'])).'"><img src="'.TYPO3_MOD_PATH.'action_view.gif" alt="'.$LANG->getLL('COMMON_VIEW').'" border="0"></a></td>';
			$content .= '<td><a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick('&id='.$this->id.'&edit[tx_cwtcommunity_albums]['.$album['uid'].']=edit',$GLOBALS['BACK_PATH'],t3lib_div::getIndpEnv('REQUEST_URI'))).'"><img src="'.TYPO3_MOD_PATH.'action_edit.gif" alt="'.$LANG->getLL('COMMON_EDIT').'" border="0"></a></td>';
			$content .= '<td><a href="'.htmlspecialchars($GLOBALS['SOBE']->doc->issueCommand('&id='.$this->id.'&cmd[tx_cwtcommunity_albums]['.$album['uid'].'][delete]=1')).'" onclick="'.htmlspecialchars("return confirm('Want to delete?');").'"><img src="'.TYPO3_MOD_PATH.'action_delete.gif" alt="'.$LANG->getLL('COMMON_DELETE').'" border="0"></a></td>';
			$content .= '</tr>';

			for ($j=0; $j < sizeof($photos); $j++) {
				$photo = $photos[$j];
				$comment_count = tx_cwtcommunity_lib_gallery::getCommentCount($photo['uid']);
				$photo_path = $this->getGalleryStoragePath().$photo['filename'];

				// Alternating row colors
				if ($switch == true){
					$switch = false;
					$content .= '<tr bgcolor="'.$doc['bgColor5'].'">';
				} elseif($switch == false) {
					$switch = true;
					$content .= '<tr>';
				}
				// Beginning row content
				$content .= '<td>&nbsp;</td>';
				$content .= '<td>&nbsp;</td>';
				$content .= '<td>'.$photo['uid'].'</td>';
				$content .= '<td>'.$photo['title'].'</td>';
				$content .= '<td><a style="'.$this->linkStyle.'" target="_blank" href="'.$photo_path.'">'.$photo['filename'].'</a></td>';
				$content .= '<td><a style="'.$this->linkStyle.'" href="?id='.$this->id.'&M=web_txcwtcommunityM1'.'&'.tx_cwtcommunity_lib_constants::CONST_ACTION.'='.tx_cwtcommunity_lib_constants::BE_ACTION_GALLERY_COMMENTS.'&'.tx_cwtcommunity_lib_constants::CONST_USER_UID.'='.$photo['cruser_id'].'&'.tx_cwtcommunity_lib_constants::CONST_PHOTO_UID.'='.$photo['uid'].'">'.$comment_count.' '.$LANG->getLL('GALLERY_COMMENTS').'</a></td>';
				$content .= '<td><a href="#" onclick="'.htmlspecialchars(tx_cwtcommunity_lib_common::beGenerateViewLink('tx_cwtcommunity_photos', $photo['uid'])).'"><img src="'.TYPO3_MOD_PATH.'action_view.gif" alt="'.$LANG->getLL('COMMON_VIEW').'" border="0"></a></td>';
				$content .= '<td><a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick('&id='.$this->id.'&edit[tx_cwtcommunity_photos]['.$photo['uid'].']=edit',$GLOBALS['BACK_PATH'],t3lib_div::getIndpEnv('REQUEST_URI'))).'"><img src="'.TYPO3_MOD_PATH.'action_edit.gif" alt="'.$LANG->getLL('COMMON_EDIT').'" border="0"></a></td>';
				$content .= '<td><a href="'.htmlspecialchars($GLOBALS['SOBE']->doc->issueCommand('&id='.$this->id.'&cmd[tx_cwtcommunity_photos]['.$photo['uid'].'][delete]=1')).'" onclick="'.htmlspecialchars("return confirm('Want to delete?');").'"><img src="'.TYPO3_MOD_PATH.'action_delete.gif" alt="'.$LANG->getLL('COMMON_DELETE').'" border="0"></a></td>';
				$content .= '</tr>';
			}
		}
		$content .= '</table>';
		return $content;
	}

	/**
	 *  Displays the gallery comment moderation view.
	 *
	 *  @param	int		The photo to administrate.
	 *  @return	string	The generated content.
	 */
	function getViewUserAdministrationGalleryComments($photo_uid) {
		global $LANG;
		//Init some vars
		$content = null;
		$switch = true;
		$doc = get_object_vars($this->doc);
		$photo = tx_cwtcommunity_lib_gallery::getPhoto($photo_uid);
		$user = tx_cwtcommunity_lib_common::getUser($photo['cruser_id']);
		$comments = tx_cwtcommunity_lib_gallery::getComments($photo_uid);

		//Output description
		//$content.= $LANG->getLL('viewUserAdministration_description').'<br><br>';
		$content .= ''.$LANG->getLL('GALLERY_VIEWING').' "'.$user['username'].'"'.'<br/><br/>';
		$content .= '<a style="'.$this->linkStyle.'" href="?id='.$this->id.'&M=web_txcwtcommunityM1'.'&'.tx_cwtcommunity_lib_constants::CONST_ACTION.'='.tx_cwtcommunity_lib_constants::BE_ACTION_GALLERY.'&'.tx_cwtcommunity_lib_constants::CONST_USER_UID.'='.$photo['cruser_id'].'">'.$LANG->getLL('GALLERY_BACK_ALBUMLIST').'</a><br/><br/>';

		// Display photo information
		$content .= '<b>'.$LANG->getLL('GALLERY_PHOTO_UID').'</b>: '.$photo['uid'].'<br/>';
		$content .= '<b>'.$LANG->getLL('GALLERY_PHOTO_TITLE').'</b>: '.$photo['title'].'<br/>';
		$content .= '<b>'.$LANG->getLL('GALLERY_PHOTO_DESC').'</b>: '.$photo['description'].'<br/>';
		$content .= '<br/><br/>';

		// Display the records
		$content .= '<table>';
		$content .= '<tr bgcolor="'.$doc['bgColor4'].'">';
		$content .= '<td><b>'.$LANG->getLL('GALLERY_COMMENT').'</b></td>';
		$content .= '<td><b>'.$LANG->getLL('GALLERY_USERNAME').'</b></td>';
		$content .= '<td><b></b></td>';
		$content .= '<td><b></b></td>';
		$content .= '<td><b></b></td>';
		$content .= '</tr>';

		for($i = 0; $i < sizeof($comments); $i++) {
			// Get photos for album
			$comment = $comments[$i];
			$creator = tx_cwtcommunity_lib_common::getUser($comment['cruser_id']);
				
			// Alternating row colors
			if ($switch == true){
				$switch = false;
				$content .= '<tr bgcolor="'.$doc['bgColor5'].'">';
			} elseif($switch == false) {
				$switch = true;
				$content .= '<tr>';
			}
				
			// Beginning row content
			$content .= '<td>'.$comment['text'].'</td>';
			$content .= '<td><a style="'.$this->linkStyle.'" href="#" onclick="'.htmlspecialchars(tx_cwtcommunity_lib_common::beGenerateViewLink('fe_users', $creator['uid'])).'">'.$creator['username'].'</a></td>';
			$content .= '<td><a href="#" onclick="'.htmlspecialchars(tx_cwtcommunity_lib_common::beGenerateViewLink('tx_cwtcommunity_photo_comments', $comment['uid'])).'"><img src="'.TYPO3_MOD_PATH.'action_view.gif" alt="'.$LANG->getLL('COMMON_VIEW').'" border="0"></a></td>';
			$content .= '<td><a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick('&id='.$this->id.'&edit[tx_cwtcommunity_photo_comments]['.$comment['uid'].']=edit',$GLOBALS['BACK_PATH'],t3lib_div::getIndpEnv('REQUEST_URI'))).'"><img src="'.TYPO3_MOD_PATH.'action_edit.gif" alt="'.$LANG->getLL('COMMON_EDIT').'" border="0"></a></td>';
			$content .= '<td><a href="'.htmlspecialchars($GLOBALS['SOBE']->doc->issueCommand('&id='.$this->id.'&cmd[tx_cwtcommunity_photo_comments]['.$comment['uid'].'][delete]=1')).'" onclick="'.htmlspecialchars("return confirm('Want to delete?');").'"><img src="'.TYPO3_MOD_PATH.'action_delete.gif" alt="'.$LANG->getLL('COMMON_DELETE').'" border="0"></a></td>';
			$content .= '</tr>';

		}
		$content .= '</table>';
		return $content;
	}

	/* getViewUserAdministrationEnabled()
	 *
	 *  Display the result view, when a user was enabled.
	 *
	 */
	function getViewUserAdministrationEnabled(){
		global $LANG;
		//Output header and description
		$content.= '<div align="left"><strong>'.$LANG->getLL('viewUserAdministrationenabled_title').'</strong></div><BR>';
		$content.= $LANG->getLL('viewUserAdministrationenabled_description').'<br>';
		$content.= '<a style="'.$this->linkStyle.'" href="?id='.$this->id.'&M=web_txcwtcommunityM1'.'">'.$LANG->getLL('back').'...</a>';

		//Return
		return $content;
	}

	/* getViewUserAdministrationDisabled()
	 *
	 *  Display the result view, when a user was disabled.
	 *
	 */
	function getViewUserAdministrationDisabled(){
		global $LANG;
		//Output header and description
		$content.= '<div align="left"><strong>'.$LANG->getLL('viewUserAdministrationdisabled_title').'</strong></div><BR>';
		$content.= $LANG->getLL('viewUserAdministrationdisabled_description').'<br>';
		$content.= '<a style="'.$this->linkStyle.'" href="?id='.$this->id.'&M=web_txcwtcommunityM1'.'">'.$LANG->getLL('back').'...</a>';

		//Return
		return $content;
	}

	/**
	 * Returns the path to the gallery photo storage.
	 *
	 * @return	string		The path e.g. '../../../../uploads/tx_cwtcommunity/gallery/'
	 */
	function getGalleryStoragePath() {
		$defaultPath = '../../../../uploads/tx_cwtcommunity/gallery/';
		// Check if user has defined a custom path
		$constants = tx_cwtcommunity_lib_common::dbQuery('SELECT constants FROM sys_template ORDER BY UID ASC LIMIT 0,1;');
		$res = preg_match('/album\.photo\.storageFolder\s=\s.*\s/', $constants[0]['constants'],$matches);
		if ($res == '1') {
			// Custom path found
			$path = explode(' = ', $matches[0]);
			return '../../../../'.$path[1];
		}
		// Return default path
		return $defaultPath;
	}




}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cwt_community/mod1/index.php'])    {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cwt_community/mod1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_cwtcommunity_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)    include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>