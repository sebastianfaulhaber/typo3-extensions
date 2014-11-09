<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003-2014 Sebastian Faulhaber (http://www.faulhaber.it)
*  								  (sebastian.faulhaber@gmx.de)
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;

/**
 * Plugin 'CWT Frontend Edit' for the 'cwt_feedit' extension.
 * 
 * @author	Sebastian Faulhaber <sebastian.faulhaber@gmx.de>
 * @package TYPO3
 * @subpackage	tx_cwtfeedit
 */
class tx_cwtfeedit_pi1 {
	var $prefixId = "tx_cwtfeedit_pi1";		// Same as class name
	var $scriptRelPath = "pi1/class.tx_cwtfeedit_pi1.php";	// Path to this script relative to the extension dir.
	var $extKey = "cwt_feedit"; // The extension key.
	var $table = null; //Holds the table name.
	var $items = null; //Holds information about the columns to render
	var $record_uid = null; //Holds the uid of the record, that
	var $cruser_id = null; //Hold the fe user uid.
	var $back_pid = null; //Holds the pid of the page the user will be sent back to
	var $back_values = null; //Values, which will be appended to all back links
	var $parent_class = null; //A reference to the calling class, from which this class is instantiated.
	var $conf = null; //Typoscript configuration
	var $mode = "VIEW_EDITPROFILE"; //Decides how the form is rendered. Possible values are "VIEW_EDITPROFILE", "VIEW_EDITPROFILE_NEW", "VIEW_EDITPROFILE_PREVIEW" ,"VIEW_EDITPROFILE_RESULT", "VIEW_EDITPROFILE_ERROR"
	var $cObj = null; //
	var $debug = false; //If true, then debug messages appear in the frontend. For developing purposes only.
	var $postvars = null;
    var $fileFunc = null;
	var $lang = null;
	var $lasterror=""; // Store some Default Error Text
	var $createNew = false;
	var $hashPasswords = false;
	var $insert_pid = 0;
	var $skipPreview = false; //If true, the preview page will be skipped.
	var $CHECKBOX_ON = 'on';
	
	/**
	 * Item type: field will be displayed read-only. No input field will be rendered.
	 */
	const TYPE_PREVIEW = 'preview';

	/**
	 * Item type: field will not be displayed but rendered as a HTML hidden field.
	 */
	const TYPE_HIDDEN = 'hidden';
	
	/**
	 * Item type: field will be rendered as a password field.
	 */
	const TYPE_PASSWORD = 'password';

	/**
	 * Item eval function: the field will be displayed twice (e.g. for entering passwords.)
	 */
	const EVAL_TWICE = 'twice';
	
	/**
	 * Item eval function: field input will be validated against e-mail format.
	 */
	const EVAL_EMAIL = 'email';
		
	/**
	 * Item eval function: This checks if the user typed in at least X characters. The syntax for �atLeast� ia as follows: �atLeast[X]�.
	 */
	const EVAL_AT_LEAST = 'atLeast';
	
	/**
	 * Item eval function: This checks for a maximum of allowed characters. The syntax is as follows: �atMost[X]�.
	 */
	const EVAL_AT_MOST = 'atMost';
	
	/**
	 * Item eval function: field input must not be empty.
	 */	
	const EVAL_REQUIRED = 'required';
	
	/**
	 * Item eval function: field input will be validated aainst date format.
	 */	
	const EVAL_DATE = 'date';
	
	/*
	*  Constructor
	*
	*  @param	 $items	   Information about the fields to integrate. Here is an example:
	*					$items["username"]["label"] = "Username:";
	*					$items["username"]["helptext"] = "All characters and numbers are allowed.";
	*					$items["username"]["error_msg"] = "Please specify a valid username!";
	*					$items["username"]["eval"] = "required"; (more evaluation methods will be supported in future releases.)
	*					$items['username']['format']['date'] = The date format... e.g. 'd.m.Y'
	*					$items['username']['format']['empty'] = The value for this field is not fetched from DB. Currently only supported for password fields.
	*  @param	 $record_uid UID of the record that should be edited.
	*  @param	 $cruser_id UID of the feuser, who wants to edit the record
	*  @param	 $back_pid PID of the page, the user will be sent back to e.g. "45"
	*  @param	 $back_values Values, that are appended to the back link. e.g. array("action" => "showall", "myVar" => "myValue")
	*  @param	 $parent_class A reference to the calling class, from which this class is instantiated.
	*  @param	 $ext_keys Extension keys for ext's that extend the table we are working on. (must be an array!)
	*  @return null
	*/
	function init($table, $items, $record_uid, $cruser_id, $back_pid, $back_values, &$parent_class, $ext_keys, $createNew = false, $hashPasswords = false, $insert_pid = 0, $conf = array(), $skipPreview = false) {
        // Call Constructor!!. Needed for the display of the correct language values, according to typoscript settings!
		parent::__construct();
		//Load TCA for table
		GeneralUtility::loadTCA($table);
		//Merge TCA form $ext_keys
		$this->mergeExtendingTCAs($ext_keys);
		// Initialize new cObj
		$cObj = GeneralUtility::makeInstance("tslib_cObj");
        //Initialize fileFunc object
        $fileFunc = GeneralUtility::makeInstance("t3lib_basicFileFunctions");
		
        //Set language
		$this->lang = ($GLOBALS["TSFE"]->config["config"]["language"]) ? $GLOBALS["TSFE"]->config["config"]["language"] : "default";
		
		//Assign items to global.
		$this->postvars = GeneralUtility::_POST($this->prefixId);
		$this->table = $table;
		$this->items = $items;
		$this->record_uid = $record_uid;
		$this->cruser_id = $cruser_id;
		$this->back_pid = $back_pid;
		$this->back_values = $back_values;
		$this->parent_class = $parent_class;
		$this->conf = $conf;
		$this->cObj = $cObj;
        $this->fileFunc = $fileFunc;
        $this->createNew = $createNew;
        $this->hashPasswords = $hashPasswords;
        $this->insert_pid = $insert_pid;
        $this->skipPreview = $skipPreview;

		//Some debugging output
		if ($this->debug) {
			DebugUtility::printArray(GeneralUtility::_POST());			
			DebugUtility::printArray($_FILES);
            DebugUtility::printArray($this->items);
		}

		//Load Local Language values
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		/*
		* CONTROLLER
		*/
		//Decide, which form mode to use.
		$isSubmitted = false;
		$isSubmitted = $this->checkForSubmit();
		if ($isSubmitted) {
			//Get type of submit "PREVIEW", "DELETE" or "RESULT"
			$type = $this->getTypeOfSubmit();
			if ($type == "PREVIEW"){
				//Check all constraints
				//OKAY --> Preview mode
				if ($this->checkConstraintsAll($this->items)){
					$this->mode = "VIEW_EDITPROFILE_PREVIEW";
					if ($this->debug){ echo "Entering VIEW_EDITPROFILE_PREVIEW...<br>";}
				}
				//NOT OKAY --> Edit error mode
				else{
					$this->mode = "VIEW_EDITPROFILE_ERROR";
					if ($this->debug){ echo "Entering VIEW_EDITPROFILE_ERROR...<br>";}
				}
			}
			elseif($type == "DELETE"){
				//Set form mode
				$this->mode = "VIEW_EDITPROFILE";
				if ($this->debug){ echo "Entering VIEW_EDITPROFILE (DELETE)...<br>";}
			}
			elseif($type == "RESULT"){
				if ($this->skipPreview) {
					if ($this->checkConstraintsAll($this->items)){
						// Set form mode
						$this->mode = "VIEW_EDITPROFILE_RESULT";
						//Do the databasequery
						$res = $this->updateDatabaseRecord($this->items);
						if ($this->debug){ echo "Entering VIEW_EDITPROFILE_RESULT...<br>";}	
					} else{
						$this->mode = "VIEW_EDITPROFILE_ERROR";
						if ($this->debug){ echo "Entering VIEW_EDITPROFILE_ERROR...<br>";}
					}
					
				} else {
					// Set form mode
					$this->mode = "VIEW_EDITPROFILE_RESULT";
					//Do the databasequery
					$res = $this->updateDatabaseRecord($this->items);
					if ($this->debug){ echo "Entering VIEW_EDITPROFILE_RESULT...<br>";}					
				}

			}
		} elseif (!$createNew) {
			// Set form mode
			$this->mode = "VIEW_EDITPROFILE";
			if ($this->debug){ echo "Entering VIEW_EDITPROFILE...<br>";}
		} else {
			// Set form mode
			$this->mode = "VIEW_EDITPROFILE_NEW";
			if ($this->debug){ echo "Entering VIEW_EDITPROFILE_NEW ...<br>";}			
		}
		return null;
	}

	/**
	*  Gets the html Code for a specific form item.
	*
	*  @param	String	$item_key  Fetches HTML for this item. Must be in format: "column"
	*					e.g. "username"
	*  @return	String	The html code for the form item.
	*/
	function getElement($item_key="column") {
		global $TYPO3_LOADED_EXT;
		//Html, that will be returned
		$code = null;
		//Style information
		$cl_TH = $this->conf['tx_cwtfeedit_pi1.']['style.']['all.']['th'];
		$cl_TD = $this->conf['tx_cwtfeedit_pi1.']['style.']['all.']['td'];
		$cl_TR = $this->conf['tx_cwtfeedit_pi1.']['style.']['all.']['tr'];
		$cl_INPUT = $this->conf['tx_cwtfeedit_pi1.']['style.']['all.']['input'];
		$cl_INPUTIMAGE = $this->conf['tx_cwtfeedit_pi1.']['style.']['all.']['inputImage'];
		$cl_TEXTAREA = $this->conf['tx_cwtfeedit_pi1.']['style.']['all.']['textarea'];
		$cl_OPTION = $this->conf['tx_cwtfeedit_pi1.']['style.']['all.']['option'];
		$cl_CHECKBOX_TABLE = $this->conf['tx_cwtfeedit_pi1.']['style.']['checkbox.']['table'];
		$cl_CHECKBOX_TD = $this->conf['tx_cwtfeedit_pi1.']['style.']['checkbox.']['td'];
		$cl_CHECKBOX_TR = $this->conf['tx_cwtfeedit_pi1.']['style.']['checkbox.']['tr'];
		$cl_columns = $this->conf['tx_cwtfeedit_pi1.']['style.']['checkboxColumns'];
		
		//Get the item from global
		$item = $this->items[$item_key];
		
		//Check if field is a non-TCA field.
		if ($item['type'] == self::TYPE_HIDDEN) {
			$code .= $this->getElementNotInTca($item_key);
			return $code;
		}
		
		//Check form mode and decide what to do
		$mode = $this->mode;
		//Get field information from TCA
		$TCA = $GLOBALS["TCA"][$this->table]["columns"][$item_key];
		$type = $TCA["config"]["type"];
		$cols = $TCA['config']['cols'];
		$twice=false;

		// Test eval_functions
		$eval_functions = explode(",", $TCA["config"]["eval"]);
		// Add individual eval functions from the items Array
		if (isset($this->items[$item_key]["eval"])) {
			$eval_functions = array_merge($eval_functions, explode(",", $this->items[$item_key]["eval"]));
		}
		if (is_array($eval_functions))	{
			reset($eval_functions);

			while(list(,$cmd)=each($eval_functions))	{
				$cmdParts = preg_split('/\[|\]/',$cmd);	// Point is to enable parameters after each command enclosed in brackets [..]. These will be in position 1 in the array.
				$theCmd = trim($cmdParts[0]);
				switch($theCmd)	{
					case "twice":
						$twice=true;
					break;
				}
			}
		}
        // if set then no editmode for this field!!
        if ($item['type'] == self::TYPE_PREVIEW) {
            if ($mode == "VIEW_EDITPROFILE" || $mode == "VIEW_EDITPROFILE_NEW"){
                $mode = "VIEW_EDITPROFILE_PREVIEW";
            }
        }
		// **********************

		if ($mode == "VIEW_EDITPROFILE" || $mode == "VIEW_EDITPROFILE_NEW" || $mode == "VIEW_EDITPROFILE_ERROR"){
			// added by F.Rakow, password mode
			if ($type == "input" || $item["type"] == "password") {
				$format = $this->items[$item_key]['format'];
				
				if ($format['empty'] == 'empty') {
					$value = '';
					$value_again = '';
					
				} else {
					// Fetch value; special handling for eval = date
					$value = $this->fetchValueFromItemHTML($item_key);

					if (strstr($TCA['config']['eval'], self::EVAL_DATE) != false && $value != '' && is_numeric($value)) {
						$date_format = isset($this->conf['tx_cwtfeedit_pi1']['date_format']) ? $this->conf['tx_cwtfeedit_pi1']['date_format'] : 'd-m-Y';
						$value = date($date_format, intval($value));
					}

					if ($twice) {
						$value_again = $this->fetchValueFromItemHTML($item_key."_again");	
					}
					
				}

				$code .= '<tr class="'.$cl_TR.'">';
				$code .= '<td valign="top" align="left" class="'.$cl_TH.'">'.($this->checkIsRequired($item_key) == true ? '*' : '').'</td>';
				$code .= '<td valign="top" align="left" class="'.$cl_TH.'">'.$this->getLabelForItem($item_key).'</td>';
				$code .= '<td valign="top" class="'.$cl_TD.'"><input class="'.$cl_INPUT.'" name="'.$this->prefixId."[".$item_key."]".'" type="'.($item["type"]=="password"?"password":"text").'" size="'.$TCA["config"]["size"].'" maxlength="'.$TCA["config"]["max"].'" value="'.$value.'"><br><small>'.$item["helptext"].'</small></td>';
				$code .= '</tr>';

		        // Display 'password' fields twice
				if ($twice) {
					$code .= '<tr class="'.$cl_TR.'">';
					$code .= '<td valign="top" align="left" class="'.$cl_TH.'">'.($this->checkIsRequired($item_key) == true ? '*' : '').'</td>';
					$code .= '<td valign="top" align="left" class="'.$cl_TH.'">'.($item["label_again"] ? $item["label_again"] : $this->getLabelForItem($item_key).'</td>');
					$code .= '<td valign="top" class="'.$cl_TD.'"><input class="'.$cl_INPUT.'" name="'.$this->prefixId."[".$item_key."_again]".'" type="'.($item["type"]=="password"?"password":"text").'" size="'.$TCA["config"]["size"].'" maxlength="'.$TCA["config"]["max"].'" value="'.$value_again.'"><br><small>'.($item["helptext_again"]?$item["helptext_again"]:$item["helptext"]).'</small></td>';					
					$code .= '</tr>';
				}

				// In case of "VIEW_EDITPROFILE_ERROR" append error row
				if ($mode == "VIEW_EDITPROFILE_ERROR" && !$this->checkConstraints($item_key)){
					$code .= '<tr class="'.$cl_TR.'">';
					$code .= '<td class="'.$cl_TD.'"></td>';
					$code .= '<td colspan="2" class="'.$cl_TD.'"><small><font color="red">'.($this->lasterror?$this->lasterror:$this->items[$item_key]["error_msg"]).'</font></small></td>';
					$code .= '</tr>';
				}
				return $code;
			}
			elseif ($type == "check"){
				// Calculate checkbox value
				$checkbox_itemValue = $this->fetchValueFromItem($item_key);
				//debug($checkbox_itemValue, 'CHECKBOX PRE');
				if (is_array($checkbox_itemValue)) {
					$checkbox_itemValue = $this->getDecimalFromCheckboxArray($checkbox_itemValue);
				} elseif ($checkbox_itemValue == null) {
					$checkbox_itemValue = 0;
				}
				//debug($checkbox_itemValue, 'CHECKBOX POST');
				// Begin with html
				$code .= '<tr class="'.$cl_TR.'">';
				$code .= '<td valign="top" align="left" class="'.$cl_TH.'"></td>';
				$code .= '<td valign="top" align="left" class="'.$cl_TH.'">'.$this->getLabelForItem($item_key).'</td>';
				$code .= '<td valign="top" class="'.$cl_TD.'">';
				
				if ($cols != null) {
					$code .= '<table class="'.$cl_CHECKBOX_TABLE.'">';
					// Multiple checkboxes
					$cols++;
					// Convert decimal to binary
					$bin = decbin($checkbox_itemValue);
					// fill leading zeros
					$diff = $cols - strlen($bin);
					for ($k = 0; $k < $diff; $k++) {
						$bin = '0'.$bin;
					}
					$bin = strrev($bin);
					for ($j = 0; $j < $cols; $j++) {
						// Checkbox columns
						if ($cl_columns != null && $cl_columns != '') {
							if ($j % $cl_columns == 0){
								$code .= '<tr class="'.$cl_CHECKBOX_TR.'">';
							}
						} else {
							$code .= '<tr class="'.$cl_CHECKBOX_TR.'">';
						}
						if (substr($bin, $j, 1) == '1') {
							$code .= '<td class="'.$cl_CHECKBOX_TD.'"><input type="checkbox" checked="true" name="'.$this->prefixId.'['.$item_key.']['.$j.']" class="'.$cl_INPUT.'"/>&nbsp;'.tslib_cObj::stdWrap(self::getLLFromString($TCA['config']['items'][$j][0]), "").'</td>';
						} else {
							$code .= '<td class="'.$cl_CHECKBOX_TD.'"><input type="checkbox" name="'.$this->prefixId.'['.$item_key.']['.$j.']" class="'.$cl_INPUT.'"/>&nbsp;'.tslib_cObj::stdWrap(self::getLLFromString($TCA['config']['items'][$j][0]), "").'</td>';							
						}
						if ($cl_columns != null && $cl_columns != '') {
							if ($j % $cl_columns == $cl_columns -1){
								$code .= '</tr>';
							}
						} else {
							$code .= '</tr>';
						}
					}
					
					$code .= "</table>";
				} else {
					// Simple checkbox
					if ($checkbox_itemValue == '1') {
						$code .= '<input type="checkbox" checked="true" name="'.$this->prefixId.'['.$item_key.'][0]" class="'.$cl_INPUT.'"/>&nbsp;'.tslib_cObj::stdWrap(self::getLLFromString($TCA['label']), "");	
					} else {
						$code .= '<input type="checkbox" name="'.$this->prefixId.'['.$item_key.'][0]" class="'.$cl_INPUT.'"/>&nbsp;'.tslib_cObj::stdWrap(self::getLLFromString($TCA['label']), "");
					}
				}
			  $code .= "<br/><small>".$item["helptext"]."</small></td>";
			  $code .= '</tr>';

			  //In case of "VIEW_EDITPROFILE_ERROR" append error row
			  if ($mode == "VIEW_EDITPROFILE_ERROR" && !$this->checkConstraints($item_key)){
				 $code .= '<tr class="'.$cl_TR.'">';
				 $code .= '<td class="'.$cl_TD.'"></td>';
				 $code .= '<td class="'.$cl_TD.'" colspan="2"><small><font color="red">'.($this->lasterror?$this->lasterror:$this->items[$item_key]["error_msg"]).'</font></small></td>';
				 $code .= '</tr>';
			  }

			  //Return
			  return $code;
			}
			elseif ($type == "select"){
			  $code .= '<tr class="'.$cl_TR.'">';
			  $code .= '<td valign="top" align="left" class="'.$cl_TH.'"></td>';
			  $code .= '<td valign="top" align="left" class="'.$cl_TH.'">'.$this->getLabelForItem($item_key).'</td>';
			  $code .= '<td valign="top" class="'.$cl_TD.'"><SELECT class="'.$conf['tx_cwtfeedit_pi1.']['style.']['all.']['select'].'" name="'.$this->prefixId."[".$item_key."]".'">';
			  
			  // Generate Select items
			  if (!$TCA["config"]["foreign_table"]){
			  	// Handle 'itemsProcFunc' pre-processing function
			  	if ($TCA["config"]["itemsProcFunc"] != null) {
					$this->executeItemsProcFunc($TCA);
				}

				$isSelected = false;
				  for ($i=0; $i < sizeof($TCA["config"]["items"]); $i++) {

					// Check for selected
					if ($TCA["config"]["items"][$i][1] == $this->fetchValueFromItem($item_key)) {
						//Output selected
						$code .= '<option class="'.$cl_OPTION.'" value="'.$TCA["config"]["items"][$i][1].'" selected>'.$this->getLLFromString($TCA["config"]["items"][$i][0]).'</option>';
						$isSelected = true;
					} else {
						// Output not selected
						if (($TCA["config"]["items"][$i][1] == $TCA["config"]["default"]) && !$isSelected) {
							$code .= '<option class="'.$cl_OPTION.'" value="'.$TCA["config"]["items"][$i][1].'" selected>'.$this->getLLFromString($TCA["config"]["items"][$i][0]).'</option>';
						} else {
							$code .= '<option class="'.$cl_OPTION.'" value="'.$TCA["config"]["items"][$i][1].'">'.$this->getLLFromString($TCA["config"]["items"][$i][0]).'</option>';							
						}
					}
				  }
			  } else {
			  		// Select from FOREIGN TABLE
			  		$query = 'SELECT * FROM '.$TCA[config][foreign_table].' WHERE 1=1 '.$TCA[config][foreign_table_where];			  		
			  	  	$rows = $this->doDatabaseQuery($query);
			  	  	$foreignId = $this->fetchValueFromItem($item_key);
			  	  	foreach ($rows as $row){
			  	  		//Check for selected			  	  					  	  		
			  	  		if ($row[uid] == $foreignId){			  	  			
			  	  			//Output selected
							$code .= '<option class="'.$cl_OPTION.'" value="'.$row[uid].'" selected>'.$this->getDBLabel($row, $TCA).'</option>';
						}else{
							//Output not selected
							$code .= '<option class="'.$cl_OPTION.'" value="'.$row[uid].'">'.$this->getDBLabel($row, $TCA).'</option>';
						}
			  	  	}
			  }
			  $code .= "</SELECT><br><small>".$item["helptext"]."</small></td>";
			  $code .= '</tr>';

			  //In case of "VIEW_EDITPROFILE_ERROR" append error row
			  if ($mode == "VIEW_EDITPROFILE_ERROR" && !$this->checkConstraints($item_key)){
				 $code .= '<tr class="'.$cl_TR.'">';
				 $code .= '<td class="'.$cl_TD.'"></td>';
				 $code .= '<td class="'.$cl_TD.'" colspan="2"><small><font color="red">'.($this->lasterror?$this->lasterror:$this->items[$item_key]["error_msg"]).'</font></small></td>';
				 $code .= '</tr>';
			  }

			  //Return
			  return $code;
			}
		elseif ($type == "radio"){
			  $code .= '<tr class="'.$cl_TR.'">';
			  $code .= '<td valign="top" align="left" class="'.$cl_TH.'"></td>';
			  $code .= '<td valign="top" align="left" class="'.$cl_TH.'">'.$this->getLabelForItem($item_key).'</td>';
			  $code .= '<td valign="top" class="'.$cl_TD.'">';
			  
				for ($i=0; $i < sizeof($TCA["config"]["items"]); $i++) {
					// Check for selected
					if ($TCA["config"]["items"][$i][1] == $this->fetchValueFromItem($item_key)) {
						//Output selected
						$code .= '<input type="radio" class="'.$cl_OPTION.'" name="'.$this->prefixId."[".$item_key."]".'" value="'.$TCA["config"]["items"][$i][1].'" checked>&nbsp;'.$this->getLLFromString($TCA["config"]["items"][$i][0]).'</input>';
					} else {
						// Output not selected
						$code .= '<input type="radio" class="'.$cl_OPTION.'" name="'.$this->prefixId."[".$item_key."]".'" value="'.$TCA["config"]["items"][$i][1].'">&nbsp;'.$this->getLLFromString($TCA["config"]["items"][$i][0]).'</input>';
					}
				  }
			  $code .= "<br><small>".$item["helptext"]."</small></td>";
			  $code .= '</tr>';

			  // In case of "VIEW_EDITPROFILE_ERROR" append error row
			  if ($mode == "VIEW_EDITPROFILE_ERROR" && !$this->checkConstraints($item_key)){
				 $code .= '<tr class="'.$cl_TR.'">';
				 $code .= '<td class="'.$cl_TD.'"></td>';
				 $code .= '<td class="'.$cl_TD.'" colspan="2"><small><font color="red">'.($this->lasterror?$this->lasterror:$this->items[$item_key]["error_msg"]).'</font></small></td>';
				 $code .= '</tr>';
			  }

			  //Return
			  return $code;
			}
			elseif ($type == "text"){
			  $code .= '<tr class="'.$cl_TR.'">';
			  $code .= '<td valign="top" align="left" class="'.$cl_TH.'"></td>';
			  $code .= '<td valign="top" align="left" class="'.$cl_TH.'">'.$this->getLabelForItem($item_key).'</td>';
			  $code .= '<td valign="top" class="'.$cl_TD.'"><TEXTAREA class="'.$cl_TEXTAREA.'" name="'.$this->prefixId."[".$item_key."]".'" rows="'.$TCA["config"]["rows"].'" cols="'.$TCA["config"]["cols"].'">'.$this->fetchValueFromItemHTML($item_key).'</textarea><br><small>'.$this->items[$item_key]["helptext"].'</small></td>';			  

			  //In case of "VIEW_EDITPROFILE_ERROR" append error row
			  if ($mode == "VIEW_EDITPROFILE_ERROR" && !$this->checkConstraints($item_key)){
				 $code .= '<tr class="'.$cl_TR.'">';
				 $code .= '<td class="'.$cl_TD.'"></td>';
				 $code .= '<td class="'.$cl_TD.'" colspan="2"><small><font color="red">'.($this->lasterror?$this->lasterror:$this->items[$item_key]["error_msg"]).'</font></small></td>';
				 $code .= '</tr>';
			  }

			  //Return
			  return $code;
			}
			elseif ($type == "group" && $TCA["config"]["internal_type"] == "file"){
				//Fetch filenames from database
				$filenames = $this->fetchValuesForFiles($item_key);

				//Get number of input fields
				$size = $TCA["config"]["maxitems"];
				$number = $size - sizeof($filenames);

				//Get directory where the files reside
				$dir = GeneralUtility::getIndpEnv('TYPO3_SITE_URL').$TCA["config"]["uploadfolder"];

				//Start creating the code
				$code .= '<tr class="'.$cl_TR.'">';
				$code .= '<td valign="top" align="left" class="'.$cl_TH.'"></td>';
				$code .= '<td valign="top" align="left" class="'.$cl_TH.'">'.$this->getLabelForItem($item_key).'</td>';
				$code .= '<td valign="top" class="'.$cl_TD.'">';

				//Display filenames and create hidden fields for the value
					for($i = 0; $i < sizeof($filenames); $i++){
						$code .= $fileIcon;
						$file_split_points=explode('.',$filenames[$i]["name"]);
						$file_ending=strtolower(end($file_split_points));
						if ($this->conf['tx_cwtfeedit_pi1.']['style.']['upload.']['thumbnail'] == true && ($file_ending == 'jpg' || $file_ending == 'png')) {
							$imgTSConfig = array();
							$imgTSConfig['file'] =$TCA["config"]["uploadfolder"].'/'.$filenames[$i]["name"];
							$imgTSConfig['altText'] ='';
							$imgTSConfig['titleText'] ='';
							$imgTSConfig['file.']['maxW'] = $this->conf['tx_cwtfeedit_pi1.']['style.']['upload.']['thumbnail.']['maxWidth'];
							$imgTSConfig['file.']['maxH'] = $this->conf['tx_cwtfeedit_pi1.']['style.']['upload.']['thumbnail.']['maxHeight'];
							$code .= $this->cObj->IMAGE($imgTSConfig).'<br>';
						} else {
							$code .='&nbsp;&nbsp;'.$filenames[$i]["name"];
						}
						$code.='&nbsp;&nbsp;'.'<input class="'.$cl_INPUTIMAGE.'" type="image" src="'.$TYPO3_LOADED_EXT["cwt_feedit"]["siteRelPath"].'pi1/icon_delete.gif" name="'.$this->prefixId.'[submit_delete]['.$item_key.']['.$i.']" value="'.$this->pi_getLL("icon_delete").'">&nbsp;&nbsp;<small><a href="'.$dir."/".$filenames[$i]["name"].'" target="_blank">'.$this->pi_getLL("file_view").'</a></small><br>';
						$code .= '<input type="hidden" name="'.$this->prefixId."[".$item_key."][".$i."][name]".'" value="'.$filenames[$i]["name"].'">';			  
					}
			  
			  //Display input fields
			  for($i = sizeof($filenames); $i < $number + sizeof($filenames); $i++){
				  $code .= '<input class="'.$cl_INPUT.'" name="'.$this->prefixId."[".$item_key."][".$i."]".'" type="file"><br>';
			  }
			  $code .= "<small>".$this->items[$item_key]["helptext"]."</small></td>";

			  //In case of "VIEW_EDITPROFILE_ERROR" append error row
			  if ($mode == "VIEW_EDITPROFILE_ERROR" && !$this->checkConstraints($item_key)){
				 $code .= '<tr class="'.$cl_TR.'">';
				 $code .= '<td class="'.$cl_TD.'"></td>';
				 $code .= '<td class="'.$cl_TD.'" colspan="2"><small><font color="red">'.($this->lasterror?$this->lasterror:$this->items[$item_key]["error_msg"]).'</font></small></td>';
				 $code .= '</tr>';
			  }

			  //Return
			  return $code;			
			}			
		} elseif ($mode == "VIEW_EDITPROFILE_PREVIEW"){
			if ($type == "input"){
				//FIXME
				// Check for 'format' option
				$displayValue = $item["type"]=="password"?"*****":$this->fetchValueFromItemHTML($item_key);
				$format = $this->items[$item_key]['format'];
				if ($format != null) {
					if ($format['date'] != null) {
						$displayValue = date($format['date'], strtotime($displayValue));
					}
				}
				$code .= '<tr class="'.$cl_TR.'"><td class="'.$cl_TD.'"></td>';
				$code .= '<td valign="top" align="left" class="'.$cl_TH.'">'.$this->getLabelForItem($item_key).'</td>';
				$code .= '<td class="'.$cl_TD.'">'.$displayValue;
				$code .= '<input type="hidden" name="'.$this->prefixId."[".$item_key."]".'" value="'.$this->fetchValueFromItemHTML($item_key).'">'.'</td>';
				$code .= '</tr>';
			}
			elseif ($type == "select" || $type == "radio"){
				
				if (!$TCA["config"]["foreign_table"]){
					// Handle 'itemsProcFunc' pre-processing function
					if ($TCA["config"]["itemsProcFunc"] != null) {
						$this->executeItemsProcFunc($TCA);
					}
					
					// Determine SELECT Value
					for ($i=0; $i < sizeof($TCA["config"]["items"]); $i++) {
						//Check for selected
						if ($TCA["config"]["items"][$i][1] == $this->fetchValueFromItemHTML($item_key)) {
							//Output selected
							$value = $this->getLLFromString($TCA["config"]["items"][$i][0]);
						}
					}
					
					$code .= '<tr class="'.$cl_TR.'"><td class="'.$cl_TH.'"></td>';
					$code .= '<td valign="top" align="left" class="'.$cl_TH.'">'.$this->getLabelForItem($item_key).'</td>';
					$code .= '<td class="'.$cl_TD.'">'.$value;
					$code .= '<input type="hidden" name="'.$this->prefixId."[".$item_key."]".'" value="'.$this->fetchValueFromItemHTML($item_key).'">'.'</td>';
					$code .= '</tr>';
				} else {
					$query = 'SELECT * FROM '.$TCA[config][foreign_table].' WHERE uid = '.intval($this->fetchValueFromItem($item_key));
			  	  	$rows = $this->doDatabaseQuery($query);
			  	  	$code .= '<tr class="'.$cl_TR.'"><td class="'.$cl_TH.'"></td>';
					$code .= '<td valign="top" align="left" class="'.$cl_TH.'">'.$this->getLabelForItem($item_key).'</td>';
					$code .= '<td class="'.$cl_TD.'">'.$this->getDBLabel($rows[0], $TCA);
					$code .= '<input type="hidden" name="'.$this->prefixId."[".$item_key."]".'" value="'.$this->fetchValueFromItemHTML($item_key).'">'.'</td>';
					$code .= '</tr>';
				}
			} elseif ($type == "check") {
				// Calculate checkbox value
				$checkbox_itemValue = $this->fetchValueFromItem($item_key);
				//debug($checkbox_itemValue, 'CHECKBOX PRE');
				if (is_array($checkbox_itemValue)) {
					$checkbox_itemValue = $this->getDecimalFromCheckboxArray($checkbox_itemValue);
				} elseif ($checkbox_itemValue == null) {
					$checkbox_itemValue = 0;
				}
				//debug($checkbox_itemValue, 'CHECKBOX PRE');
				
				// Begin with html
				$code .= '<tr class="'.$cl_TR.'">';
				$code .= '<td valign="top" align="left" class="'.$cl_TH.'"></td>';
				$code .= '<td valign="top" align="left" class="'.$cl_TH.'">'.$this->getLabelForItem($item_key).'</td>';
				$code .= '<td valign="top" class="'.$cl_TD.'">';

				if ($cols != null) {
					$code .= '<table class="'.$cl_CHECKBOX_TABLE.'">';
					// Multiple checkboxes
					$cols++;
					// Convert decimal to binary
					$bin = decbin($checkbox_itemValue);
					// fill leading zeros
					$diff = $cols - strlen($bin);
					for ($k = 0; $k < $diff; $k++) {
						$bin = '0'.$bin;
					}
					$bin = strrev($bin);
					for ($j = 0; $j < $cols; $j++) {
						// Checkbox columns
						if ($cl_columns != null && $cl_columns != '') {
							if ($j % $cl_columns == 0){
								$code .= '<tr class="'.$cl_CHECKBOX_TR.'">';
							}
						} else {
							$code .= '<tr class="'.$cl_CHECKBOX_TR.'">';						
						}
						if (substr($bin, $j, 1) == '1') {
							$code .= '<td class="'.$cl_CHECKBOX_TD.'"><input type="checkbox" checked="true" disabled="true" class="'.$cl_INPUT.'"/>&nbsp;'.tslib_cObj::stdWrap(self::getLLFromString($TCA['config']['items'][$j][0]), "").'</td>';
							$code .= '<input type="hidden" name="'.$this->prefixId.'['.$item_key.']['.$j.']" value="'.$this->CHECKBOX_ON.'"/>'; 
						} else {
							$code .= '<td class="'.$cl_CHECKBOX_TD.'"><input type="checkbox" disabled="true" name="'.$this->prefixId.'['.$item_key.']['.$j.']" class="'.$cl_INPUT.'"/>&nbsp;'.tslib_cObj::stdWrap(self::getLLFromString($TCA['config']['items'][$j][0]), "").'</td>';							
						}
						if ($cl_columns != null && $cl_columns != '') {
							if ($j % $cl_columns == $cl_columns -1){
								$code .= '</tr>';
							}
						} else {
							$code .= '</tr>';						
						}
					}
					$code .= '</table>';
				} else {
					// Simple checkbox
					if ($checkbox_itemValue == '1') {
						$code .= '<input type="checkbox" checked="true" disabled="true" class="'.$cl_INPUT.'"/>&nbsp;'.tslib_cObj::stdWrap(self::getLLFromString($TCA['label']), "");	
						$code .= '<input type="hidden" name="'.$this->prefixId.'['.$item_key.'][0]" value="'.$this->CHECKBOX_ON.'"/>';
					} else {
						$code .= '<input type="checkbox" disabled="true" name="'.$this->prefixId.'['.$item_key.'][0]" class="'.$cl_INPUT.'"/>&nbsp;'.tslib_cObj::stdWrap(self::getLLFromString($TCA['label']), "");
					}
				}
				$code .= '</td></tr>';				
				
			}
			elseif ($type == "text"){
				$code .= '<tr class="'.$cl_TR.'"><td class="'.$cl_TD.'"></td>';
				$code .= '<td valign="top" align="left" class="'.$cl_TH.'">'.$this->getLabelForItem($item_key).'</td>';
				$code .= '<td class="'.$cl_TD.'">'.$this->fetchValueFromItemHTML($item_key);
				$code .= '<input type="hidden" name="'.$this->prefixId."[".$item_key."]".'" value="'.$this->fetchValueFromItemHTML($item_key).'">'.'</td>';
				$code .= '</tr>';
			}
			elseif ($type == "group" && $TCA["config"]["internal_type"] == "file"){
				//Fetch information on files
				$files = $this->fetchValuesForFiles($item_key);

				//Do the rest
				$code .= '<tr class="'.$cl_TR.'"><td class="'.$cl_TH.'"></td>';
				$code .= '<td valign="top" align="left" class="'.$cl_TH.'">'.$this->getLabelForItem($item_key).'</td>';
				$code .= '<td valign="top" class="'.$cl_TD.'">';
				for($i = 0; $i < sizeof($files); $i++){
					$file_split_points=explode('.',$files[$i]["name"]);
					$file_ending=strtolower(end($file_split_points));
					if ($this->conf['tx_cwtfeedit_pi1.']['style.']['upload.']['thumbnail'] == true && ($file_ending == 'jpg' || $file_ending == 'png')) {
						$imgTSConfig = array();
						$imgTSConfig['file'] =$TCA["config"]["uploadfolder"].'/'.$files[$i]["name"];
						$imgTSConfig['altText'] ='';
						$imgTSConfig['titleText'] ='';
						$imgTSConfig['file.']['maxW'] = $this->conf['tx_cwtfeedit_pi1.']['style.']['upload.']['thumbnail.']['maxWidth'];;
						$imgTSConfig['file.']['maxH'] = $this->conf['tx_cwtfeedit_pi1.']['style.']['upload.']['thumbnail.']['maxHeight'];;
						$code .= $this->cObj->IMAGE($imgTSConfig).'<br>';
					} else {
						$code .= $files[$i]["name"]."<br>";
					}
				}
			  
				//Generate Hidden fields
				for($i = 0; $i < sizeof($files); $i++){
					$code .= '<input type="hidden" name="'.$this->prefixId."[$item_key][$i][name]".'" value="'.$files[$i]["name"].'">';
					$code .= '<input type="hidden" name="'.$this->prefixId."[$item_key][$i][type]".'" value="'.$files[$i]["type"].'">';
					$code .= '<input type="hidden" name="'.$this->prefixId."[$item_key][$i][tmp_name]".'" value="'.$files[$i]["tmp_name"].'">';												
					$code .= '<input type="hidden" name="'.$this->prefixId."[$item_key][$i][size]".'" value="'.$files[$i]["size"].'">';				
				}
				$code .= '</td>';
			}
			return $code;
		}
		//Return
		return null;
	}
	
	/**
	 * Calculates the decimal value of an incoming checkbox group data array. The returned decimal 
	 * determines which checkboxes are checked and which aren't.
	 *
	 * @param	Array	The checkbox group data array taken from the POSTVARS.
	 * @return	int		The according decimal number for a checkbox group.
	 */
	function getDecimalFromCheckboxArray($checkboxArr) {
		// Construct binary string from input array
		$keys = array_keys($checkboxArr);
		//debug($keys, 'INPUT');
		if (sizeof($keys) > 0) {
			// Find greatest digit and create empty string;
			sort($keys, SORT_NUMERIC);
			$binary = str_pad('', $keys[sizeof($keys)-1]+1, '0');
			//debug($binary, 'BINARY');
			// Set all digits
			for ($i = 0; $i < sizeof($keys); $i++) {
				$binary[$keys[$i]] = '1';
				//debug(strrev($binary), 'BINARY');
			}
			
			// Convert the binary string to a decimal number and return
			$decimal = bindec(strrev($binary));
			//debug($decimal, 'DECIMAL');
			return $decimal;
		}
		return 0;	
	}
	
	
	private function getElementNotInTca($item_key) {
		$code .= '<input type="hidden" name="'.$this->prefixId.'['.$item_key.']" value="'.$this->items[$item_key]['value'].'"/>';
		return $code;
	}
	
	/* getDbLabel($row){	
	*
	*  Gets the lables of the chosen table from the global TCA.
	*
	*  @return String with the label and alt label.
	*/
	function getDbLabel($row, $TCA){	
		// Added by Mikael Conley, Wildside
		$GTCActrl = $GLOBALS["TCA"][$TCA[config][foreign_table]][ctrl];
		$label = $row[$GTCActrl["label"]];
		if ($GTCActrl["label_alt"]){
			$label .= ' '.$row[$GTCActrl["label_alt"]];
		}		
		return $label;		
	}

	/**
	*  Gets the html Code for the form header.
	*
	*  @return String The html code for the form item.
	*/
	function getFormHeader() {
		//HTML that will be returned
		$code = null;
		//Add code from typoscript
		$code .= $this->conf['tx_cwtfeedit_pi1.']['pre_html'];
		$code .= '<table class="'.$conf['tx_cwtfeedit_pi1.']['style.']['all.']['table'].'">';
		//Check form mode and decide what to do
		$mode = $this->mode;
		//Create form header
		if ($mode == "VIEW_EDITPROFILE" || $mode == "VIEW_EDITPROFILE_NEW" || $mode == "VIEW_EDITPROFILE_ERROR" || $mode == "VIEW_EDITPROFILE_PREVIEW" ){
			if ($this->createNew) {
				$tmp = 'true';
			} else {
				$tmp = 'false';
			}
			$code .= '<form action="" method="POST" enctype="multipart/form-data">';
			$code .= '<input type="hidden" name="'.$this->prefixId.'[createNew]" value="'.$tmp.'"/>';
		} elseif ($this->mode == "VIEW_EDITPROFILE_RESULT") {

		}
		return $code;
	}

	/**
	*  Gets the html Code for the form footer.
	*
	*  @return String The html code for the form item.
	*/
	function getFormFooter() {
		//HTML that will be returned
		$code = null;
		
		//Check form mode and decide what to do
		$mode = $this->mode;
		//Display Buttons
		if ($mode == "VIEW_EDITPROFILE" || $mode == "VIEW_EDITPROFILE_NEW" || $mode == "VIEW_EDITPROFILE_ERROR") {
			$code .= '<tr class="'.$cl_TR.'">';
			$code .= '<td colspan="3" class="'.$cl_TD.'">';
			$code .= '<input class="'.$cl_INPUT.'" name="'.$this->prefixId."[submit]".'" TYPE="SUBMIT" value="'.$this->pi_getLL("submit_value").'">';
			$code .= '<input class="'.$cl_INPUT.'" name="cancel" TYPE="BUTTON" value="'.$this->pi_getLL("back_value").'" onClick="location.replace(\''.$this->parent_class->pi_getPageLink($this->back_pid,"", $this->back_values).'\')">';
			$code .= '</td>';
			$code .= '</tr>';
			$code .= '</form>';
		}
		elseif ($mode == "VIEW_EDITPROFILE_PREVIEW"){
			$code .= '<tr class="'.$cl_TR.'">';
			$code .= '<td colspan="3" class="'.$cl_TD.'">';
			$code .= '<input class="'.$cl_INPUT.'" name="'.$this->prefixId."[submit_preview]".'" TYPE="SUBMIT" value="'.$this->pi_getLL("submit_preview_value").'">';
			$code .= '<input class="'.$cl_INPUT.'" name="cancel" TYPE="BUTTON" value="'.$this->pi_getLL("back_value").'" onClick="location.replace(\''.$this->parent_class->pi_getPageLink($this->back_pid,"", $this->back_values).'\')">';
			$code .= '</td>';
			$code .= '</tr>';
			$code .= '</form>';
		}
		elseif ($mode == "VIEW_EDITPROFILE_RESULT"){
			$code .= $this->pi_getLL("result_text")."<br>";
			$code .= '<a href="'.$this->parent_class->pi_getPageLink($this->back_pid,"", $this->back_values).'">'.$this->pi_getLL("result_back").'</a>';
		}
		
		$code .= '</table>';
		$code .= $this->conf['tx_cwtfeedit_pi1.']['post_html'];
		
		//Return
		return $code;
	}

	/* checkForSubmit()
	*
	*  Checks, if the user has submitted the form.
	*
	*  @return Boolean
	*/
	function checkForSubmit() {
		//Get the post vars from globals
		$submit = $this->postvars["submit"];
		$submit_preview = $this->postvars["submit_preview"];
		$submit_result = $this->postvars["submit_result"];		
		if ($submit != null || $submit_preview != null || $submit_result != null) {

			//Debug
			if ($this->debug){
				echo "Submit value: ".$submit.$submit_preview.$submit_result."<br>";
			}
			return true;
		}
		else{
			return false;
		}
	}

	/* getTypeOfSubmit()
	*
	*  Get type of submit: "PREVIEW", "DELETE" or "RESULT"
	*
	*  @return String Type of submit.
	*/
	function getTypeOfSubmit() {
		//Get the post vars
		$submit = $this->postvars["submit"];
		$submit_preview = $this->postvars["submit_preview"];
		$submit_delete = $this->postvars["submit_delete"];
		//Check the vars
		//User wants PREVIEW
		if ($submit != null){
			if ($this->skipPreview == true) {
				return "RESULT";
			} else {
				return "PREVIEW";
			}
		}
		//user wants RESULT
		elseif ($submit_preview != null){
		   return "RESULT";
		}
		elseif($submit_delete != null){
			return "DELETE";
		}
		//Return
		return null;
	}

	/* checkConstraints($item_key)
	*
	*  Checks the contraints for a given $this->items with the defined eval function of the
	*  item.
	*  @param $item_key The item key e.g. "username"
	*  @return Boolean true if constraints are okay, otherwise false.
	*/
	function checkConstraints($item_key) {
		$this->lasterror="";
		$twice=(substr($item_key,-6)=="_again");
		if ($twice) {
			return true;
		}
		//Get the value from post vars
		$item_value = $this->postvars[$item_key];
		//Get field information from TCA
		$TCA = $GLOBALS["TCA"][$this->table]["columns"][$item_key];
		//Get eval functions
		$eval_functions = explode(",", $TCA["config"]["eval"]);
		//Add individual eval functions from the items Array
		if (isset($this->items[$item_key]["eval"])) {
			$eval_functions = array_merge($eval_functions, explode(",", $this->items[$item_key]["eval"]));
		}
		//IN case we are dealing with files
		if ($TCA["config"]["type"] == "group" && $TCA["config"]["internal_type"] == "file" && $_FILES[$this->prefixId]["name"][$item_key] != null) {
			//check the constraints for all files of the group
			$keys = array_keys($_FILES[$this->prefixId]["name"][$item_key]);
			for($i = 0; $i < sizeof($_FILES[$this->prefixId]["name"][$item_key]); $i++){
				//Check the php error value
				if (!$this->evalFileError($_FILES[$this->prefixId]["error"][$item_key][$keys[$i]])) {
					return false;
				}
				//Check if the filesize is okay
				if (!$this->evalFileSize($_FILES[$this->prefixId]["size"][$item_key][$keys[$i]], $TCA["config"]["max_size"])) {
					return false;
				}
				//Check, if the file type is okay
				if (!$this->evalFileType($_FILES[$this->prefixId]["name"][$item_key][$keys[$i]], $TCA["config"]["allowed"])) {
					return false;
				}
			}

		}
		//Check constraints for item
		for ($i=0; $i < sizeof($eval_functions); $i++){
			//added some useful functions, based on the fe_adminLib.inc, F.Rakow
			//Call the different eval functions
			$cmdParts = preg_split('/\[|\]/',$eval_functions[$i]);	// Point is to enable parameters after each command enclosed in brackets [..]. These will be in position 1 in the array.
			$theCmd = trim($cmdParts[0]);
			switch($theCmd) {

				case "required":
					if (!$this->evalRequired($item_value)){
						  return false;
					}
				break;
			 	case "date":
			 		if (!$this->evalDate($item_value)){
						return false;
					}
				break;
				case "uniqueInPid":
				break;
				case "twice":
					if (strcmp($item_value, $this->postvars[$item_key."_again"]))	{
						$this->lasterror=$this->pi_getLL("error_twice","You must enter the same value twice.");
						return false;
					}
				break;
				case "email":
					if (!GeneralUtility::validEmail($item_value))	{
						$this->lasterror=$this->pi_getLL("error_email","You must enter a valid email address.");
						return false;
				}
				break;
				case "atLeast":
					$chars=intval($cmdParts[1]);
					if (strlen($item_value)<$chars)	{
						$this->lasterror=sprintf($this->pi_getLL("error_atLeast","You must enter at least %s characters!"), $chars);
						return false;
					}
				break;
				case "atMost":
					$chars=intval($cmdParts[1]);
					if (strlen($item_value)>$chars)	{
						$this->lasterror=sprintf($this->pi_getLL("error_atMost","You must enter at most %s characters!"), $chars);
						return false;
					}
				break;
				case "inBranch":
					$pars = explode(";",$cmdParts[1]);
					if (intval($pars[0]))	{
						$pid_list = $this->cObj->getTreeList(
							intval($pars[0]),
							intval($pars[1]) ? intval($pars[1]) : 999,
							intval($pars[2])
						);
						if (!$pid_list || !GeneralUtility::inList($pid_list,$item_value))	{
							$this->lasterror=sprintf($this->pi_getLL("error_inBranch","The value was not a valid value from this list: %s"), $pid_list);
							return false;
						}
					}
				break;
			}
		}
		//Return true / false
		return true;
	}

	/* checkConstraintsAll()
	*
	*  Checks if the constraints of all items from $this->items are okay and then results
	*  true or false.
	*  @return Boolean true if constraints are okay, otherwise false.
	*/
	function checkConstraintsAll($items) {
		//Check constraints for items
		for ($i=0; $i < sizeof($items); $i++){
			 //Get the item key
			 $item_key = array_keys($items);
			 $item_key = $item_key[$i];
			 if (!$this->checkConstraints($item_key)){
				  return false;
			 }
		}
		//Return true / false
		return true;
	}

	/* evalRequired($value)
	*
	*	Check if the required value is empty or not.
	*/
	function evalRequired($value) {
		//Check the constraint
		if ($value == null){
			return false;
		}
		else{
			 return true;
		}
	}

	/* evalDate($value)
	*
	*  Check if the value is a correct date.
	*/
	function evalDate($value) {
		//Check the constraint
		if (($timestamp = strtotime($value)) === -1 || $timestamp == false) {
			 //Debug
			 if ($this->debug){
				 echo "'".$value."' is no valid Date!";
			 }
			return false;
		}
		else{
			 return true;
		}
	}

	/* evalFileSize($value)
	*
	*	Check if the size of a file is okay.
	*/
	function evalFileSize($value, $allowed_size) {
		//Value is given in bytes so divide by 1000
		$value = $value / 1000;
		//Check the constraint
		if ($value <= $allowed_size){
			return true;
		}
		else{
			 if ($this->debug) {echo "File size exceeds maximum.<br>";}
			 return false;
		}
	}

	/* evalFileError($error_code)
	*
	*	Check the error value from the $_FILES array.
	*/
	function evalFileError($error_code) {
		if ($this->debug) {echo "File error: ".$error_code;}
		//Value is given in bytes so divide by 1000
		if ($error_code == "0") {
			if ($this->debug) {echo "File upload okay.";}
			return true;
		}
		elseif ($error_code == "1"){
			if ($this->debug) {echo "filesize exceeds upload_max_filesize in php.ini!";}
			return false;
		}
		elseif ($error_code == "3"){
			if ($this->debug) {echo "The file was uploaded partially!";}
			return false;
		}
		elseif ($error_code == "4"){
			if ($this->debug) {echo "No file was uploaded!";}
			return true;
		}
		else{
			return true;
		}
	}

	/* evalFileType($filename,$types)

	*
	*	Check if the filename is allowed
	*/
	function evalFileType($filename,$types) {
		//Validate input vars
		if ($filename == null || $filename == ""){
			//No file given, so return true
			return true;
		}
		
		// SECURITY CHECK
		if (preg_match(FILE_DENY_PATTERN_DEFAULT, $filename) > 0 || preg_match($TYPO3_CONF_VARS['BE']['fileDenyPattern'], $filename) > 0) {
			return false;
		}
		
		
		//Explode the filename
		$filename = explode(".", $filename);
		$type = $filename[sizeof($filename)-1];
		$type = strtolower($type);
		//Create the type array from tca value
		$types = explode(",", $types);
		//Check if in array
		if (in_array($type, $types)) {
			return true;
		}
		else{
			return false;
		}
	}
	
	/**
	 *	Checks if a field shall be considered as required. Two things will be checked:
	 *		1. Firstly, the TCA will be checked.
	 *		2. The ['eval'] part of the item array will be checked.
	 */
	function checkIsRequired($item_key="column"){
		//Load TCA for item
		$table = $this->table;
		$label = $item_key;
		GeneralUtility::loadTCA($table);
		$TCA = $GLOBALS["TCA"][$table]["columns"][$label]["config"]["eval"];
		$TCA = explode(",", $TCA);
		if (in_array("required", $TCA) || is_int(stripos($this->items[$item_key]['eval'], self::EVAL_REQUIRED))){
			return true;
		}
		else{
			 return false;
		}
	}

	/**
	*	Returns a string where any character not matching [a-zA-Z0-9_-] is substituted by "_"
	*
	*	@param	 String	String, that should be cleaned. e.g. 'My File.doc'
	*	@return	 String	String. e.g. 'My_File.doc'
	*/
	function cleanFilename($filename) {
        //The cleaning function comes from 'class.t3lib_basicfilefunc.php'
        $filename = $this->fileFunc->cleanFilename($filename);
		return $filename;
	}

	/**
	*  Makes the filename unique by appending a timestamp. (Considering, that one fe_user
    *  can only edit one field at the same time, the filename is really unique.)
    * 
	*  @param	 String	The filename without slashes and directory! e.g. 'MyFile.doc'
	*  @return	 String	The filename with an appended timestamp. e.g. 'MyFile_1038737318.doc'
	*/
	function makeFilenameUnique($filename) {
		//Get timestamp
		$hash = time();
		//Explode it by '.'
		$filename = explode(".",$filename);
		//
		$filename = $filename[0]."_".$hash.".".$filename[1];
		return $filename;
	}

	/**
	*  Fetches the local language value from a given string.
	* 
	*  @param	String	Must be in format: "LLL:EXT:cwt_community/locallang_db.php:tx_cwtcommunity_guestbook.status.I.0"
	*  @return	String	Local Language Value
	*/
	private function getLLFromString($lllString) {
		$string = explode(":", $lllString);
		$pathToFile = $string[1].":".$string[2];
		$ll_key = $string[3];
		//Read file
		if (strpos($pathToFile, 'EXT') === 0) {
			$LOCAL_LANG = tslib_fe::readLLfile($pathToFile, $this->lang);
			$resString = tslib_fe::getLLL($ll_key, $LOCAL_LANG);
			return $resString;
		}
  		return $lllString;
	}
		
	/**
	*  Returns fetchValueFromItemHTML($item_key) but ensures the output does
	*  not contain special html-characters.
	* 
	*  @see fetchValueFromItem()
	*/
	function fetchValueFromItemHTML($item_key){
		return htmlspecialchars($this->fetchValueFromItem($item_key));
	}     
     
	/**
	*  Fetches the value of an item_key from database, if in "VIEW_EDITPROFILE" mode.
	*  From post vars, if in "VIEW_EDITPROFILE_ERROR" or "VIEW_EDITPROFILE_PREVIEW" mode.
	*/
	function fetchValueFromItem($item_key){
		if ($this->mode == "VIEW_EDITPROFILE" && $this->postvars["submit_delete"] == null){
			// Check for eval_function twice
			$twice=(substr($item_key,-6)=="_again");
			if ($twice) {
				$item_key=substr($item_key,0,-6);
			}
			//Do the query
			if ($this->debug){ echo "Fetching values from DATABASE...<br>";}			
			$res = $this->doDatabaseQuery("SELECT ".$item_key." FROM ".$this->table." WHERE uid=".intval($this->record_uid));
			$res = $res[0][$item_key];
			
		} elseif($this->mode == "VIEW_EDITPROFILE" && $this->postvars["submit_delete"] != null){
			if ($this->debug){ echo "Fetching values from POST VARS...<br>";}
			 $res = $this->postvars[$item_key];
		} elseif ($this->mode == "VIEW_EDITPROFILE_ERROR" || $this->mode == "VIEW_EDITPROFILE_PREVIEW" || $this->mode == "VIEW_EDITPROFILE_RESULT"){
			if ($this->debug){ echo "Fetching values from POST VARS...<br>";}
			$res = $this->postvars[$item_key];
		} elseif ($this->mode == 'VIEW_EDITPROFILE_NEW') {
			if ($this->debug){ echo "Fetching values from POST VARS...<br>";}
			$res = $this->postvars[$item_key];			
		}
		//Return
		return $res;
	}
	
	/* fetchValuesForFiles($item_key)
	* 
	*  [Description]
	*
	*/
	function fetchValuesForFiles($item_key){
		if (($this->mode == "VIEW_EDITPROFILE" || $this->mode == "VIEW_EDITPROFILE_NEW") && $this->postvars["submit_delete"][$item_key] == null){			
			// Check for eval_function twice
			$twice=(substr($item_key,-6)=="_again");
			if ($twice) {
				$item_key=substr($item_key,0,-6);
			}
			//Do the query
			if ($this->debug){ echo "Fetching values from DATABASE...<br>";}			
			$res = $this->doDatabaseQuery("SELECT ".$item_key." FROM ".$this->table." WHERE uid=".intval($this->record_uid));
			$res = $res[0][$item_key];
			//Bring it to the right format
			$res = explode(",", $res);
			//In case theres nothing to explode, the next three lines fix the prob

			if ($res[0] == null || $res[0] == "") {
				  $res = array();
			} else {
				//If okay, then construct the array
				$temp = array();
				for($i = 0; $i < sizeof($res); $i++){
					$temp[$i]["name"] = $res[$i];
				}
				$res = $temp;
			}
		} elseif ($this->mode == "VIEW_EDITPROFILE_PREVIEW" || $this->mode == "VIEW_EDITPROFILE_RESULT" || $this->postvars["submit_delete"] != null){			
			if ($this->debug){ echo "Fetching FILE values from POST VARS...<br>";}
			
			//Okay...now recreate the $_FILES array
			if ($this->mode == "VIEW_EDITPROFILE_RESULT" || $this->mode == "VIEW_EDITPROFILE_PREVIEW") {
				$files = array();
				for($i = 0; $i < sizeof($this->postvars[$item_key]); $i++){
					//the [name] tells us, that the file does not come from $_FILES and must be added
					if (is_array($this->postvars[$item_key][$i])) {
						$temp = array();
						$temp["name"] = $this->postvars[$item_key][$i]["name"];
						//Add to files
						$files[] = $temp;
					}
				}
			
				//Move the files to upload folder
				//Get field information from TCA
				$TCA = $GLOBALS["TCA"][$this->table]["columns"][$item_key];
				$dir = PATH_site.$TCA["config"]["uploadfolder"];
				$this->moveTempFilesToDir($dir, $item_key);
			} else{
				//In case of DELETE determine, which file was deleted
				$delete_key = null;
				$delete_key = $this->postvars["submit_delete"][$item_key];
				$delete_key = array_keys($delete_key);
				$delete_key = $delete_key[0];

				$files = array();
				for($i = 0; $i < sizeof($this->postvars[$item_key]); $i++){
					//the [name] tells us, that the file does not come from $_FILES and must be added
					if (is_array($this->postvars[$item_key][$i]) && $i != $delete_key) {
						$temp = array();
						$temp["name"] = $this->postvars[$item_key][$i]["name"];
						//Add to files
						$files[] = $temp;
					}
				}
			}

			//get names and further information of files, that were uploaded.
			@$keys = array_keys($_FILES[$this->prefixId]["name"][$item_key]);
			for($i = 0; $i < sizeof($keys); $i++){
				//Only add files, that were uploaded
				if ($_FILES[$this->prefixId]["name"][$item_key][$keys[$i]] != null) {
					$temp = array();
					$temp["name"] = $_FILES[$this->prefixId]["name"][$item_key][$keys[$i]];
					$temp["type"] = $_FILES[$this->prefixId]["type"][$item_key][$keys[$i]];
					$temp["tmp_name"] = $_FILES[$this->prefixId]["tmp_name"][$item_key][$keys[$i]];
					$temp["size"] = $_FILES[$this->prefixId]["size"][$item_key][$keys[$i]];
					//Add to files
					$files[] = $temp;
				}
			}

			if ($this->mode != "VIEW_EDITPROFILE_RESULT") {
			//TODO: Do something here....
			}

			//Return the array
			return $files;
		} elseif ($this->mode == "VIEW_EDITPROFILE_ERROR") {
			//In case of error, display the files from post values
			$files = array();
			for($i = 0; $i < sizeof($this->postvars[$item_key]); $i++){				
				//the [name] tells us, that the file does not come from $_FILES and must be added
				if (is_array($this->postvars[$item_key][$i])) {
					$temp = array();
					$temp["name"] = $this->postvars[$item_key][$i]["name"];
					//Add to files
					$files[] = $temp;
				}
			}
			return $files;
		}
		//Return
		return $res;
	}

	/* moveTempFilesToDir($dir)
	* 
	*  Moves files from the path given in $_FILES to the specified directory for further handling.
	*  @param $dir String. Directory where the files should be moved to.
	*  @param $item_key String. The item key from $this->items. e.g. 'tx_cwttest_images'
	*/
	function moveTempFilesToDir($dir, $item_key){
		//Validate $_FILES
		if ($_FILES[$this->prefixId]["name"][$item_key] == null){
			//Do nothing
			return null;
		}
		//For all files in $_FILES
		$keys = array_keys($_FILES[$this->prefixId]["name"][$item_key]);
		//Move the files
		for($i = 0; $i < sizeof($keys); $i++){
			$filename = $_FILES[$this->prefixId]["name"][$item_key][$keys[$i]];
			GeneralUtility::upload_copy_move($_FILES[$this->prefixId]["tmp_name"][$item_key][$keys[$i]], $dir."/".$filename);
		}
	}

	/* updateDatabaseRecord($items)
	*
	*  This function updates the values in database for the record with the
	*  appropriate record uid.
	*/
	function updateDatabaseRecord($items){
	   //Get the array keys
	   $item_keys = array_keys($items);
	   //Preparing query
	   $temp = array();
	   $size = sizeof($item_keys);
	   for ($i=0; $i < $size; $i++){
	   		$part = array();
			//Get field information from TCA
			$TCA = $GLOBALS["TCA"][$this->table]["columns"][$item_keys[$i]];
			//Get type of field
			$type = $TCA["config"]["type"];

			//Special handling for file items
			if ($type == "group" && $TCA["config"]["internal_type"] == "file") {
			   $files = $this->fetchValuesForFiles($item_keys[$i]);
			   for($j = 0; $j < sizeof($files); $j++){
					//Get values from database
					$res = $this->doDatabaseQuery("SELECT ".$item_keys[$i]." FROM ".$this->table." WHERE uid = ".intval($this->record_uid));
					$res = explode(",", $res[0][$item_keys[$i]]);
					//Check if file was uploaded and filename has to be cleaned and has to be made unique
					if (!in_array($files[$j]["name"], $res)){
						//Clean filename
						$filename = null;
						$filename = $this->cleanFilename($files[$j]["name"]);
						//Make filename unique
						$filename = $this->makeFilenameUnique($filename);
						//Rename the file
						$dir = PATH_site.$TCA["config"]["uploadfolder"];
						rename($dir."/".$files[$j]["name"], $dir."/".$filename);
					}
					else{
						$filename = $files[$j]["name"];
					}
					//Add it
					$part[] = $filename;
			   }
			   $temp[] = $item_keys[$i]."='".implode(",", $part)."'";
		   }
		   // Special handling for password fields
		   elseif ($this->hashPasswords && $type == "input" && $items[$item_keys[$i]]["type"] == "password") {
		   		// Get PW from database
		   		$pw = $this->doDatabaseQuery("SELECT ".$item_keys[$i]." FROM ".$this->table." WHERE uid=".intval($this->record_uid));
				$pw = $pw[0][$item_keys[$i]];
				$newPw = $this->fetchValueFromItem($item_keys[$i]);
		   		// Only change if different from db value
		   		if ($newPw != $pw) {
					$temp[] = $item_keys[$i]."='".md5($GLOBALS['TYPO3_DB']->fullQuoteStr($newPw, $this->table))."'";		   			
		   		}
		   }
		   // Special handling for checkbox fields
		   elseif ($type == "check") {
				// Get the decimal value from input data array
				$checkbox_itemValue = $this->fetchValueFromItem($item_keys[$i]);
		   		if (is_array($checkbox_itemValue)) {
					$checkbox_itemValue = $this->getDecimalFromCheckboxArray($checkbox_itemValue);
				} elseif ($checkbox_itemValue == null) {
					$checkbox_itemValue = 0;
				}
		   		$temp[] = $item_keys[$i]."='".$GLOBALS['TYPO3_DB']->fullQuoteStr($checkbox_itemValue, $this->table)."'";
		   }
		   // Special handling for input fields with eval = 'date'
		   elseif ($type == 'input' && strstr($TCA['config']['eval'], self::EVAL_DATE) != false) {
		   		$timestamp = strtotime($this->fetchValueFromItem($item_keys[$i]));
		   		$temp[] = $item_keys[$i]."='".$GLOBALS['TYPO3_DB']->fullQuoteStr($timestamp, $this->table)."'";
		   }
		   // Normal items
		   else{
				$temp[] = $item_keys[$i]."='".$GLOBALS['TYPO3_DB']->fullQuoteStr($this->fetchValueFromItem($item_keys[$i]), $this->table)."'";
		   }
	   }
	   $temp = implode(", ", $temp);
	   //Add time stamp
	   $temp .= ", tstamp='".time()."'";

	   //Create the query depending on the mode we are in
		$createNew = GeneralUtility::_GP($this->prefixId);
		$createNew = $createNew['createNew']; 
	   if ($createNew != 'true') {
			// Normal RECORD EDIT MODE
	   		$query = "UPDATE ".$this->table." SET ".$temp." WHERE uid=".intval($this->record_uid);
	   } else {
	   		// CREATE A NEW RECORD!
			$query = "INSERT INTO ".$this->table." SET ".$temp.", pid = '".$this->insert_pid."', crdate = '".time()."', cruser_id = '".$this->cruser_id."'";
	   }

	   //Finally do the query!
	   $res = $this->doDatabaseUpdateQuery($query);
	   
	   // Execute Hook
	   $hookConfig = $this->conf['tx_cwtfeedit_pi1.']['hook.']['aftersave.'];
	   if ($hookConfig['class'] != null && $hookConfig['class'] != '') {
	   		// Try to include the hook class
	   			$class = GeneralUtility::getFileAbsFileName($hookConfig['class']);
				if (file_exists($class)) {
					require_once($class);
					$classObj = GeneralUtility::makeInstance($hookConfig['className']);
					
					if ($createNew != 'true') {
						$record = $this->doDatabaseQuery("SELECT * FROM ".$this->table." WHERE uid=".intval($this->record_uid));
					} else {
						$record = $this->doDatabaseQuery("SELECT * FROM ".$this->table." WHERE uid=".$GLOBALS['TYPO3_DB']->sql_insert_id());
					}
					
					$classObj->executeHook($this->parent_class, $record[0]);
				} else {
					GeneralUtility::sysLog('CWT_FEEDIT: The class file "'.$class.'" could not be found!', $this->extKey, GeneralUtility::SYSLOG_SEVERITY_ERROR);
				}
	   }
	}

	/**
	*  Merges the $tempColumns from extensions, that extend the table, we are currently
	*  working on. In case you wrote an extension, that extends the "fe_users", then
	*  the TCA information for the additional fields will be merged with the "fe_users" TCA.
	*
	*  @param	Array	Extension TCA's that should be merged.
	*/
	function mergeExtendingTCAs($ext_keys){
		global $TYPO3_LOADED_EXT;
		//Merge all ext_keys
		if (is_array($ext_keys)) {
			for($i = 0; $i < sizeof($ext_keys); $i++){
				//Include the ext_table
				$_EXTKEY=$ext_keys[$i]; // added by F.Rakow
				include($TYPO3_LOADED_EXT[$ext_keys[$i]]["ext_tables.php"]);
				//Add the tempColumns to target tables TCA
				//t3lib_extMgm::addTCAcolumns($this->table,$tempColumns,1);
			}
		}
	}

	private function executeItemsProcFunc(&$TCA) {
		// Generate items with pre-processing function
  		$itemsProcFunc = explode('->', $TCA["config"]["itemsProcFunc"], 2);
  		$itemsProcFunc = $itemsProcFunc[0];

  		// Call the itemsProcFunc class and generate items array
  		$procFuncClass = GeneralUtility::makeInstance($itemsProcFunc);
  		$pObj = array();
  		$procFuncClass->main($TCA["config"], $pObj);
	}
	
	private function getLabelForItem($item_key) {
		// Check if custom label is defined
		if ($this->items[$item_key]['label'] != null) {
			return $this->items[$item_key]['label'];
		}
		// Otherwise choose the default label from TCA
		$TCA = $GLOBALS["TCA"][$this->table]["columns"][$item_key];
		return $this->getLLFromString($TCA['label']);
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
	function doDatabaseQuery($query) {
		// Do the query
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		// Preparing result set
		$rows = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$rows[] = $row;
		}
		// Debugging
		if ($this -> debug) {
			echo "Query: $query<br>";
			print_r($rows);
			echo "<br>";
		}
		// Return the array
		return $rows;
	}

	/**
	*  [Description]
	*  @param	String	Database query, which will be executed. e.g. 'UPDATE myTable SET myAttribute=myValue WHERE...'
	*  @return	Object	null
	*/
	function doDatabaseUpdateQuery($query) {
		// Do the query
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		// Debugging
		if ($this -> debug) {
			echo "Query: $query<br>";
			echo "<br>";
		}
		// Return the array
		return null;
	}

}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_feedit/pi1/class.tx_cwtfeedit_pi1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_feedit/pi1/class.tx_cwtfeedit_pi1.php"]);
}

?>