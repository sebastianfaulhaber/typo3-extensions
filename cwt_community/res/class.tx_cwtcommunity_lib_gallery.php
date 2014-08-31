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

include_once(t3lib_extMgm::extPath('cwt_community').'res/class.tx_cwtcommunity_lib_common.php');
include_once(t3lib_extMgm::extPath('cwt_community').'res/class.tx_cwtcommunity_lib_constants.php');

/**
 * Some functions for the gallery feature of the community.
 * 
 * @author	Sebastian Faulhaber <sebastian.faulhaber@gmx.de>
 * @package TYPO3
 * @subpackage	tx_cwtcommunity
 */
class tx_cwtcommunity_lib_gallery {

    /**
     * Generate the HTML content for the album list using EXT:smarty.
     *
     * @param	int		UID of the feuser to show the gallery for.
     * @return	string	The generated HTML content.
     */
	function getViewAlbumList($owner_uid) {
		// Get Smarty Instance
    	$smartyInstance = tx_cwtcommunity_lib_common::getSmartyInstance();
        $conf = tx_cwtcommunity_lib_common::getConfArray();
        $tplPath = tx_cwtcommunity_lib_common::getTemplatePath($conf['template_album']);
        
        $albumData = self::getAlbumsForRole($owner_uid);
        
        foreach ($albumData as &$album) {
        	$album['preview_pic'] = self::getPreviewPhotoURI($album['uid'], $conf);
        	$album['photo_count'] = self::getPhotoCount($album['uid'], $conf); 
        }
        
        // Provide smarty with the information for the template
        $smartyInstance->assign('albumData', $albumData);
        $smartyInstance->assign('owner_uid', $owner_uid);
        
        $content .= $smartyInstance->display($tplPath);
        return $content;
	}

    /**
     * Generate the HTML content for the album detail view using EXT:smarty.
     *
     * @param	int		UID of the album to show.
     * @return	string	The generated HTML content.
     */
	function getViewAlbumDetail($album_uid) {
		// Get Smarty Instance
    	$smartyInstance = tx_cwtcommunity_lib_common::getSmartyInstance();
        $conf = tx_cwtcommunity_lib_common::getConfArray();
        $tplPath = tx_cwtcommunity_lib_common::getTemplatePath($conf['template_album_detail']);
        
        $album = self::getAlbum($album_uid);
        $photos = self::getPhotos($album_uid);
        
		foreach ($photos as &$photo) {
        	$photo['pic'] = self::getPhotoURI($photo['uid'], $conf); 
        	$photo['comment_count'] = self::getCommentCount($photo['uid']);
        }
        // Provide smarty with the information for the template
        $smartyInstance->assign('album', $album);
        $smartyInstance->assign('photos', $photos);
        
        $content .= $smartyInstance->display($tplPath);
        return $content;
	}
	
    	
    /**
     * Generate the HTML content for the album delete view using EXT:smarty.
     *
     * @param	int		UID of the album to delete.
     * @return	string	The generated HTML content.
     */    
	function getViewAlbumDelete($album_uid){
		// Get Smarty Instance
    	$smartyInstance = tx_cwtcommunity_lib_common::getSmartyInstance();
        $conf = tx_cwtcommunity_lib_common::getConfArray();
        $tplPath = tx_cwtcommunity_lib_common::getTemplatePath($conf['template_album_admin']);
        
        $albumData = self::getAlbum($album_uid);
	    $form_action = tx_cwtcommunity_lib_common::getPageLink($conf['pid_gallery'],'', array(tx_cwtcommunity_lib_constants::CONST_ACTION => tx_cwtcommunity_lib_constants::ACTION_GALLERY_SHOW_ALBUM_DELETE, tx_cwtcommunity_lib_constants::CONST_ALBUM_UID => $albumData['uid']));
        
        $smartyInstance->assign('form_action', $form_action);
        $smartyInstance->assign('albumData', $albumData);
        
        $content .= $smartyInstance->display($tplPath);
        return $content;
	}
	
    /**
     * Generate the HTML content for the album new view using EXT:cwt_feedit.
     *
     * @param	mixed	The calling object.
	 * @param 	mixed	The typoscript configuration array.* 
     * @return	string	The generated HTML content.
     */
    function getViewAlbumNew($callerRef, $conf) {
		//START CONFIG FOR FEEDIT
		$table = "tx_cwtcommunity_albums";
        //Create Item array
        $items['title']['label'] = tx_cwtcommunity_lib_common::getLL('ALBUM_EDIT_TITLE_LABEL');
        $items['title']['helptext'] = tx_cwtcommunity_lib_common::getLL('ALBUM_EDIT_TITLE_HELPTEXT');
        $items['description']['label'] = tx_cwtcommunity_lib_common::getLL('ALBUM_EDIT_DESCRIPTION_LABEL');
        $items['description']['helptext'] = tx_cwtcommunity_lib_common::getLL('ALBUM_EDIT_DESCRIPTION_HELPTEXT');
        $items['access_policy']['label'] = tx_cwtcommunity_lib_common::getLL('ALBUM_EDIT_ACCESS_POLICY_LABEL');
        $items['access_policy']['helptext'] = tx_cwtcommunity_lib_common::getLL('ALBUM_EDIT_ACCESS_POLICY_HELPTEXT');    	                
		$cruser_id = tx_cwtcommunity_lib_common::getLoggedInUserUID();

        //Create form object
        $form = t3lib_div::makeInstance('tx_cwtfeedit_pi1');
        $form->init($table, $items, '0', $cruser_id, $GLOBALS["TSFE"]->id, 
        				array(tx_cwtcommunity_lib_constants::CONST_ACTION => tx_cwtcommunity_lib_constants::ACTION_GALLERY_SHOW_ALBUM_LIST
        					, tx_cwtcommunity_lib_constants::CONST_ALBUM_CRUSER_ID => tx_cwtcommunity_lib_common::getLoggedInUserUID())
        				, $callerRef, array(), true, false, tx_cwtcommunity_lib_common::getGlobalStorageFolder());
        				
        //Generate content
        $content.= $form->getFormHeader();
        $content.= $form->getElement("title");
        $content.= $form->getElement("description");
        $content.= $form->getElement("access_policy");
        $content.= $form->getFormFooter();
        return $content;
    }
    
    /**
     * Generate the HTML content for the album edit view using EXT:cwt_feeedit.
     *
     * @param	int		The UID of the album, which shall be edited.
     * @param	mixed	The calling object.
     * @param 	mixed	The typoscript configuration array.
     * @return	string	The generated HTML content.
     */    
    function getViewAlbumEdit($album_uid, $callerRef, $conf) {
        //START CONFIG FOR FEEDIT
		$table = "tx_cwtcommunity_albums";
        //Create Item array
        $items['title']['label'] = tx_cwtcommunity_lib_common::getLL('ALBUM_EDIT_TITLE_LABEL');
        $items['title']['helptext'] = tx_cwtcommunity_lib_common::getLL('ALBUM_EDIT_TITLE_HELPTEXT');
        $items['description']['label'] = tx_cwtcommunity_lib_common::getLL('ALBUM_EDIT_DESCRIPTION_LABEL');
        $items['description']['helptext'] = tx_cwtcommunity_lib_common::getLL('ALBUM_EDIT_DESCRIPTION_HELPTEXT');
        $items['access_policy']['label'] = tx_cwtcommunity_lib_common::getLL('ALBUM_EDIT_ACCESS_POLICY_LABEL');
        $items['access_policy']['helptext'] = tx_cwtcommunity_lib_common::getLL('ALBUM_EDIT_ACCESS_POLICY_HELPTEXT');        	                
		$cruser_id = tx_cwtcommunity_lib_common::getLoggedInUserUID();

        //Create form object
        $form = t3lib_div::makeInstance('tx_cwtfeedit_pi1');
        $form->init($table, $items, $album_uid, $cruser_id, $GLOBALS["TSFE"]->id
        						, array(tx_cwtcommunity_lib_constants::CONST_ACTION => tx_cwtcommunity_lib_constants::ACTION_GALLERY_SHOW_ALBUM_LIST
        						, tx_cwtcommunity_lib_constants::CONST_ALBUM_CRUSER_ID => tx_cwtcommunity_lib_common::getLoggedInUserUID())
        						, $callerRef, array(), false);
        //Generate content
        $content.= $form->getFormHeader();
        $content.= $form->getElement("title");
        $content.= $form->getElement("description");
        $content.= $form->getElement("access_policy");
        $content.= $form->getFormFooter();
        return $content;    	
    }	
	
    /**
     * Generate the HTML content for the new photo view using EXT:smarty.
     *
     * @param	int		UID of the album to add the photos to.
     * @return	string	The generated HTML content.
     */
    function getViewPhotoNew($album_uid){
    	// Get Smarty Instance
    	$smartyInstance = tx_cwtcommunity_lib_common::getSmartyInstance();
        $conf = tx_cwtcommunity_lib_common::getConfArray();
        $tplPath = tx_cwtcommunity_lib_common::getTemplatePath($conf['template_photo_admin']);
        
        $albumData = self::getAlbum($album_uid);
	    $form_action = tx_cwtcommunity_lib_common::getPageLink($conf['pid_gallery'],'', array(tx_cwtcommunity_lib_constants::CONST_ACTION => tx_cwtcommunity_lib_constants::ACTION_GALLERY_SHOW_PHOTO_NEW, tx_cwtcommunity_lib_constants::CONST_ALBUM_UID => $album_uid));
	    
        $smartyInstance->assign('form_action', $form_action);
        $smartyInstance->assign('albumData', $albumData);
        
        $content .= $smartyInstance->display($tplPath);
        return $content;
    }
    

    /**
     * Generate the HTML content for the photo detail view using EXT:smarty.
     *
     * @param	int		UID of the photo that is going to be reported.
     * @param	string	View "report" for report view or "" for detail view.
     * @return	string	The generated HTML content.
     */
	function getViewPhotoDetail($photo_uid, $view = '') {
		// Get Smarty Instance
    	$smartyInstance = tx_cwtcommunity_lib_common::getSmartyInstance();
        $conf = tx_cwtcommunity_lib_common::getConfArray();
        $tplPath = tx_cwtcommunity_lib_common::getTemplatePath($conf['template_photo_detail']);
        
        $form_action_report = tx_cwtcommunity_lib_common::getPageLink($conf['pid_gallery'],'', array(tx_cwtcommunity_lib_constants::CONST_ACTION => tx_cwtcommunity_lib_constants::ACTION_GALLERY_SHOW_PHOTO_REPORT, tx_cwtcommunity_lib_constants::CONST_PHOTO_UID => $photo_uid));
	    $form_action_detail = tx_cwtcommunity_lib_common::getPageLink($conf['pid_gallery'],'', array(tx_cwtcommunity_lib_constants::CONST_ACTION => tx_cwtcommunity_lib_constants::ACTION_GALLERY_SHOW_PHOTO_DETAIL, tx_cwtcommunity_lib_constants::CONST_PHOTO_UID => $photo_uid));
        
        $photo = self::getPhoto($photo_uid);
	    $photo['pic'] = self::getPhotoURI($photo['uid'], $conf);
		$album = self::getAlbum($photo['album_uid']);
		$comments = self::getComments($photo['uid']);
        
        $smartyInstance->assign('form_action_report', $form_action_report);
        $smartyInstance->assign('form_action_detail', $form_action_detail);
        $smartyInstance->assign('photo', $photo);
        $smartyInstance->assign('album', $album);
        $smartyInstance->assign('comments', $comments);
        $smartyInstance->assign('view', $view);
        
        $content .= $smartyInstance->display($tplPath);
        return $content;
	}  
	
    /**
     * Generate the HTML content for the photo edit view using EXT:cwt_feedit.
     *
     * @param	int		The UID of the photo, that shall be edited.
     * @param	mixed	The calling object.
     * @param 	mixed	The typoscript configuration array.
     * @return	string	The generated HTML content.
     */    
    function getViewPhotoEdit($photo_uid, $callerRef, $conf) {
    	$photo = self::getPhoto($photo_uid);
        //START CONFIG FOR FEEDIT
		$table = "tx_cwtcommunity_photos";
        //Create Item array
        $items['title']['label'] = tx_cwtcommunity_lib_common::getLL('PHOTO_EDIT_TITLE_LABEL');
        $items['title']['helptext'] = tx_cwtcommunity_lib_common::getLL('PHOTO_EDIT_TITLE_HELPTEXT');
        $items['description']['label'] = tx_cwtcommunity_lib_common::getLL('PHOTO_EDIT_DESCRIPTION_LABEL');
        $items['description']['helptext'] = tx_cwtcommunity_lib_common::getLL('PHOTO_EDIT_DESCRIPTION_HELPTEXT');        	                
		$cruser_id = tx_cwtcommunity_lib_common::getLoggedInUserUID();

        //Create form object
        $form = t3lib_div::makeInstance('tx_cwtfeedit_pi1');
        $form->init($table, $items, $photo_uid, $cruser_id, $GLOBALS["TSFE"]->id
        						, array(tx_cwtcommunity_lib_constants::CONST_ACTION => tx_cwtcommunity_lib_constants::ACTION_GALLERY_SHOW_ALBUM_DETAIL
        						, tx_cwtcommunity_lib_constants::CONST_ALBUM_UID => $photo['album_uid'])
        						, $callerRef, array(), false);
        //Generate content
        $content.= $form->getFormHeader();
        $content.= $form->getElement('title');
        $content.= $form->getElement('description');
        $content.= $form->getFormFooter();
        return $content;    	
    }    
    
	/**
	 * Deletes an album, contained photos and photo comments.
	 * 
	 * @param	int		The UID of the album, that shall be deleted.
	 */	
	public function deleteAlbumCascading($album_uid) {
		// Delete album
		tx_cwtcommunity_lib_common::dbUpdateQuery('DELETE FROM tx_cwtcommunity_albums WHERE uid = "'.intval($album_uid).'";');
		// Delete comments for photos 
		tx_cwtcommunity_lib_common::dbUpdateQuery('DELETE FROM tx_cwtcommunity_photo_comments WHERE photo_uid IN (SELECT uid FROM tx_cwtcommunity_photos WHERE album_uid = "'.intval($album_uid).'");');		
		// Delete photos
		tx_cwtcommunity_lib_common::dbUpdateQuery('DELETE FROM tx_cwtcommunity_photos WHERE album_uid = "'.intval($album_uid).'";');
	}
	
	/**
	 * Determines if a user has activated his gallery. This is the main prerequisite for displaying
	 *  a gallery!!
	 *
	 * @param	int		The feuser_uid to fetch the album count for.
	 * @return	boolean	True if user has activated his gallery, otherwise False.
	 */
	public function isGalleryActivated($feuser_uid) {
		$res = tx_cwtcommunity_lib_common::dbQuery('SELECT tx_cwtcommunityuser_gallery_activated FROM fe_users WHERE uid = "'.intval($feuser_uid).'";');
		if ($res[0]['tx_cwtcommunityuser_gallery_activated'] == 1) {
			return true;
		}
		return false;
	}
	
	/**
	 * Gets the number of albums for a specific feuser.
	 *
	 * @param	int	The feuser_uid to fetch the album count for.
	 * @return	int	The number of albums.
	 */
	public function getAlbumCount($feuser_uid) {
		$count = tx_cwtcommunity_lib_common::dbQuery('SELECT COUNT(uid) AS COUNT FROM tx_cwtcommunity_albums WHERE cruser_id = "'.intval($feuser_uid).'" AND NOT deleted = "1" AND NOT hidden = 1;');
		return $count[0]['COUNT'];
	}

	/**
	 * Gets the number of albums for a specific feuser according the currently logged in user's role. The function narrows
	 * the result set automaticaly...so sit back and relax.
	 *
	 * @param	int	The feuser_uid to fetch the album count for.
	 * @return	int	The number of albums.
	 */
	public function getAlbumCountForRole($feuser_uid) {
		$loggedIn = tx_cwtcommunity_lib_common::getLoggedInUserUID();
		// Check if we have an owner or not
		if ($feuser_uid == $loggedIn) {
			// OWNER
			$count = tx_cwtcommunity_lib_common::dbQuery('SELECT COUNT(uid) AS COUNT from tx_cwtcommunity_albums WHERE cruser_id = "'.intval($feuser_uid).'" AND NOT deleted = "1" AND NOT hidden = 1;');
			return $count[0]['COUNT'];
		} else {
			if (tx_cwtcommunity_lib_common::isBuddy($feuser_uid, $loggedIn)){
				// FRIEND
				$count = tx_cwtcommunity_lib_common::dbQuery('SELECT COUNT(uid) AS COUNT from tx_cwtcommunity_albums WHERE cruser_id = "'.intval($feuser_uid).'" AND access_policy IN (1,2)AND NOT deleted = "1" AND NOT hidden = 1;');
				return $count[0]['COUNT'];	
			} else {
				// NORMAL USER
				$count = tx_cwtcommunity_lib_common::dbQuery('SELECT COUNT(uid) AS COUNT from tx_cwtcommunity_albums WHERE cruser_id = "'.intval($feuser_uid).'" AND access_policy IN (2)AND NOT deleted = "1" AND NOT hidden = 1;');
				return $count[0]['COUNT'];	
			}
		}		
	}
	
	/**
	 * This check is a user may access another user's album. This actually depends on the access policy setting
	 * of an album.
	 * 
	 * @param	int		The album's UID to check.
	 * @param 	int		The feuser's UID to check access for.
	 * @return 	boolean	True if user may access album, otherwise false.
	 */
	public function mayUserAccessAlbum($album_uid, $feuser_uid) {
		$album = self::getAlbum($album_uid);
		// OWNER
		if ($feuser_uid == $album['cruser_id']) {
			return true;
		} else {
			// ACCESS POLICY - 0
			if ($album['access_policy'] == '0') {
				return false;
			} elseif ($album['access_policy'] == '1')  {
				 if (tx_cwtcommunity_lib_common::isBuddy($album['cruser_id'], $feuser_uid)){
				 	return true;
				 }
				 return false;
			} elseif ($album['access_policy'] == '2')  {
				return true;
			}
		}	
	}
	
	/**
	 * Gets a specific album.
	 *
	 * @param	int		The album_uid.
	 * @return	array	An associative array containing the db result set.
	 */
	public function getAlbum($album_uid) {
		$res = tx_cwtcommunity_lib_common::dbQuery('SELECT * FROM tx_cwtcommunity_albums WHERE uid = "'.intval($album_uid).'" AND NOT deleted = "1" AND NOT hidden = 1;');
		return $res[0];
	}
	
	/**
	 * Gets all albums for a specific feuser.
	 *
	 * @param	int		The feuser_uid to fetch the albums for.
	 * @return	array	An associative array containing the db result set.
	 */
	public function getAlbums($feuser_uid) {
		$res = tx_cwtcommunity_lib_common::dbQuery('SELECT * FROM tx_cwtcommunity_albums WHERE cruser_id = "'.intval($feuser_uid).'" AND NOT deleted = "1" AND NOT hidden = 1;');
		return $res;
	}
	
	/**
	 * This fetches the albums for a specific user dependant on the role, that the currently logged in user has. Possible 
	 * roles are 'OWNER', 'BUDDY' and 'NORMAL USER'. The function narrows the result set automatically...so relax.
	 * 
	 * @param	int		The feuser_uid to fetch the albums for.
	 * @return	array	An associative array containing the db result set.
	 */
	public function getAlbumsForRole($feuser_uid) {
		$loggedIn = tx_cwtcommunity_lib_common::getLoggedInUserUID();
		// Check if we have an owner or not
		if ($feuser_uid == $loggedIn) {
			// OWNER
			return tx_cwtcommunity_lib_common::dbQuery('SELECT * FROM tx_cwtcommunity_albums WHERE cruser_id = "'.intval($feuser_uid).'" AND NOT deleted = "1" AND NOT hidden = 1;');
		} else {
			if (tx_cwtcommunity_lib_common::isBuddy($feuser_uid, $loggedIn)){
				// FRIEND
				return tx_cwtcommunity_lib_common::dbQuery('SELECT * FROM tx_cwtcommunity_albums WHERE cruser_id = "'.intval($feuser_uid).'" AND access_policy IN (1,2) AND NOT deleted = "1" AND NOT hidden = 1;');	
			} else {
				// NORMAL USER
				return tx_cwtcommunity_lib_common::dbQuery('SELECT * FROM tx_cwtcommunity_albums WHERE cruser_id = "'.intval($feuser_uid).'" AND access_policy IN (2) AND NOT deleted = "1" AND NOT hidden = 1;');	
			}
		}
		return $res;
	}
	
	/**
	 * Fetches the number of photos contained by a specific album.
	 *
	 * @param	int		The album's UID.
	 * @return	int		The number of photos contained by the specified album.
	 */
	public function getPhotoCount($album_uid) {
		$count = tx_cwtcommunity_lib_common::dbQuery('SELECT COUNT(uid) AS COUNT FROM tx_cwtcommunity_photos WHERE album_uid = "'.intval($album_uid).'" AND NOT deleted = "1" AND NOT hidden = 1;');
		return $count[0]['COUNT'];
	}
	
	
	/**
	 * Determines the URI of a specific photo. The URI (e.g. 'uploads/tx_cwtcommunity/gallery/myPhoto.jpg') can 
	 * then be used to generate a resized version via Typo3 cObj functions.
	 *
	 * @param	int		The photo's UID.
	 * @param	int		The callers $conf array (this is usually passed in the 'main()' function of a plugin.).
	 * @return	string	The photos URI. e.g. 'uploads/tx_cwtcommunity/gallery/myPhoto.jpg'
	 */
	public function getPhotoURI($photo_uid, $conf) {
		// Assemble URI
		$photo = tx_cwtcommunity_lib_common::dbQuery('SELECT filename FROM tx_cwtcommunity_photos WHERE uid = "'.intval($photo_uid).'" AND NOT deleted = "1" AND NOT hidden = 1;');
		$uri = $conf['album.']['photo.']['storageFolder'].$photo[0]['filename'];
		return $uri;
	}

	/**
	 * Fetches a specific photo.
	 *
	 * @param		int 	The photo's UID.
	 * @return		array	An associative array containing the photo's attributes.
	 */
	public function getPhoto($photo_uid) {
		$photo = tx_cwtcommunity_lib_common::dbQuery('SELECT * FROM tx_cwtcommunity_photos p WHERE uid = "'.intval($photo_uid).'" AND NOT deleted = "1" AND NOT hidden = 1;');
		return $photo[0];
	}	
	
	/**
	 * Fetches all photos for a specific album.
	 *
	 * @param		int 	The album's UID to fetch the photos for.
	 * @return		array	An associative array containing all the photos contained by the album.
	 */
	public function getPhotos($album_uid) {
		$photos = tx_cwtcommunity_lib_common::dbQuery('SELECT * FROM tx_cwtcommunity_photos p WHERE album_uid = "'.intval($album_uid).'" AND NOT deleted = "1" AND NOT hidden = 1 ORDER BY crdate ASC;');
		return $photos;
	}
	
	/**
	 * Fetches the URI for an album's preview photo. If the album has not been assigned a preview photo yet,
	 * this function will use the first photo contained by the album. If there are no pics in the album 
	 * it will return null.
	 *
	 * @param	int		The album's UID.
	 * @param	int		The callers $conf array (this is usually passed in the 'main()' function of a plugin.).
	 * @return	string	The photos URI. e.g. 'uploads/tx_cwtcommunity/gallery/myPhoto.jpg' in case a photo is found. Otherwise NULL.
	 */
	public function getPreviewPhotoURI($album_uid, $conf) {
		$album = tx_cwtcommunity_lib_common::dbQuery('SELECT * FROM tx_cwtcommunity_albums a WHERE a.uid = "'.intval($album_uid).'" AND NOT a.deleted = "1" AND NOT hidden = 1;');
		if ($album[0]['preview_photo_uid'] != "") {
			// Preview photo assigned
			return self::getPhotoURI($album[0]['preview_photo_uid'], $conf);
		} else {
			// No preview photo assigned yet
			if (self::getPhotoCount($album[0]['uid']) > 0) {
				$photo = tx_cwtcommunity_lib_common::dbQuery('SELECT uid FROM tx_cwtcommunity_photos WHERE album_uid = "'.intval($album[0]['uid']).'" AND NOT deleted = "1" AND NOT hidden = 1 ORDER BY uid ASC LIMIT 0,1;');
				return self::getPhotoURI($photo[0]['uid'], $conf); 
			} else {
				return null;
			}			
		}
	}
	
	/**
	 * This handles uploads of new photos: All files are checked against a number of constraints (filetype,
	 * filesize, etc.). If all checks are successfull the files are moved to the configured storage folder.
	 * 
	 * @param		int		The album's UID to add the photos to.
	 * @param		int		The callers $conf array (this is usually passed in the 'main()' function of a plugin.).
	 * @return		string	The error HTML. If no errors have occured null is returned.
	 */
	public function processUploadedFiles($album_uid, $conf) {
		$errors = null;
		$arrKeys = array_keys($_FILES);
		for ($i = 0; $i < sizeof($_FILES); $i++) {
			$file = $_FILES[$arrKeys[$i]];
			if ($file['name'] != '' && $file['type'] != '' && $file['tmp_name'] != '' && !self::isPhotosPerAlbumLimitExceeded($album_uid, $conf)) {
				$hasFileErrors = false;
				// Check if file is OK
				if (!$file['error'] == '0' && $file['name'] != '') {
					$errors .= tx_cwtcommunity_lib_common::generateErrorMessage("An unknown error has occured. Please contact your system administrator.");
					$hasFileErrors = true;
				}
				// Check if file type is allowed
				$type = explode('.', $file['name']);
				$type = strtolower($type[sizeof($type) - 1]);
				$allowedTypes = explode(',', $conf['album.']['photo.']['new.']['formatsAllowed']);
				if (array_search($type, $allowedTypes) == '') {
					$errors .= tx_cwtcommunity_lib_common::generateErrorMessage('"'.$file['name'].'": '.tx_cwtcommunity_lib_common::getLL('PHOTO_NEW_ERROR_WRONGTYPE').'<br/>');
					$hasFileErrors = true;
				}
				// Check if file size is below the allowed limit
				$allowedSize = $conf['album.']['photo.']['new.']['maxSize'] * 1000;
				if ($file['size'] > $allowedSize) {
					$errors .= tx_cwtcommunity_lib_common::generateErrorMessage('"'.$file['name'].'": '.tx_cwtcommunity_lib_common::getLL('PHOTO_NEW_ERROR_WRONGSIZE').'<br/>');
					$hasFileErrors = true;
				}
				
				if (!file_exists($conf['album.']['photo.']['storageFolder'])) {
					$errors .= tx_cwtcommunity_lib_common::generateErrorMessage('"'.$conf['album.']['photo.']['storageFolder'].'": '.tx_cwtcommunity_lib_common::getLL('PHOTO_NEW_ERROR_WRONGFOLDER').'<br>');
					$hasFileErrors = true;
				}
				// Move file and create DB record, if not errors have occured
				if (!$hasFileErrors) {
					// Make filename unique
					$uniqueFilename = tx_cwtcommunity_lib_common::makeFilenameUnique(tx_cwtcommunity_lib_common::cleanFilename($file['name']));
										 
					// Move file to configured storage directory
					$storageFolder = $conf['album.']['photo.']['storageFolder'];
					t3lib_div::upload_copy_move($file['tmp_name'], $storageFolder.$uniqueFilename);
					$dimensions = getimagesize($storageFolder.$uniqueFilename);

					// Create database record
					self::createNewPhoto($album_uid, $uniqueFilename, $file['size'], $dimensions[0], $dimensions[1]);
				}
			}
		}
		return $errors;
	}
	
	/**
	 * This function checks if the photo per album limit is exceeded or not.
	 *
	 * @param		int		The album's UID.
	 * @param		int		The callers $conf array (this is usually passed in the 'main()' function of a plugin.).
	 * @return		boolean	True if limit is reached, false if not.
	 */
	public function isPhotosPerAlbumLimitExceeded($album_uid, $conf) {
		$limit = $conf['album.']['photo.']['new.']['maximumPerAlbum'];
		$photoCount = self::getPhotoCount($album_uid);
		if ($photoCount < $limit) {
			return false;
		}
		return true;
	}
	/**
	 * Create a new photo record in the database.
	 *
	 * @param		int		The album's UID to add the photo to.
	 * @param		string	The filename of the new photo.
	 * @param		int		The filesize in bytes.
	 * @param		int		The width.
	 * @param		int		The height.
	 */	
	public function createNewPhoto($album_uid, $filename, $size, $width, $height) {
		tx_cwtcommunity_lib_common::dbUpdateQuery('INSERT INTO tx_cwtcommunity_photos (pid, album_uid, filename, size, width, height, cruser_id, crdate) VALUES ('.tx_cwtcommunity_lib_common::getGlobalStorageFolder().', '.$album_uid.', "'.$filename.'", '.$size.', '.$width.', '.$height.', '.tx_cwtcommunity_lib_common::getLoggedInUserUID().', '.time().');');
	}

	/**
	 * Gets the number of comments for a specific photo.
	 *
	 * @param	int	The photo UID to fetch the comment count for.
	 * @return	int	The number of comments.
	 */
	public function getCommentCount($photo_uid) {
		$count = tx_cwtcommunity_lib_common::dbQuery('SELECT COUNT(uid) AS COUNT FROM tx_cwtcommunity_photo_comments WHERE photo_uid = "'.intval($photo_uid).'" AND NOT deleted = "1" AND NOT hidden = 1;');
		return $count[0]['COUNT'];
	}
	
	/**
	 * This  will fetch all comments for a specific photo.
	 *
	 * @param		int		The photo to fetch the comments for.
	 * @return		array	An associative array containing all the comments.
	 */
	public function getComments($photo_uid) {
		$comments = tx_cwtcommunity_lib_common::dbQuery('SELECT c.uid, c.crdate, c.cruser_id, c.text, u.username, u.tx_cwtcommunityuser_image AS image FROM tx_cwtcommunity_photo_comments c, fe_users u WHERE c.cruser_id = u.uid AND photo_uid = "'.intval($photo_uid).'" AND NOT c.deleted = "1" AND NOT c.hidden = 1 ORDER BY c.crdate DESC;');
		return $comments;
	}
	
	/**
	 * Deletes a photo and its comments.
	 *
	 * @param		int		The UID of the photo that will be deleted.
	 * @param		int		The callers $conf array (this is usually passed in the 'main()' function of a plugin.).
	 */
	public function deletePhoto($photo_uid, $conf) {
		$photo = tx_cwtcommunity_lib_common::dbQuery('SELECT album_uid, filename FROM tx_cwtcommunity_photos WHERE uid = "'.intval($photo_uid).'";');
		$album = tx_cwtcommunity_lib_common::dbQuery('SELECT preview_photo_uid FROM tx_cwtcommunity_albums WHERE uid = "'.intval($photo[0]['album_uid']).'";');
		tx_cwtcommunity_lib_common::dbUpdateQuery('DELETE FROM tx_cwtcommunity_photos WHERE uid = "'.intval($photo_uid).'";');
		tx_cwtcommunity_lib_common::dbUpdateQuery('DELETE FROM tx_cwtcommunity_photo_comments WHERE photo_uid = "'.intval($photo_uid).'";');
		if ($album[0]['preview_photo_uid'] == $photo_uid) {
			// Update preview photo
			tx_cwtcommunity_lib_common::dbUpdateQuery('UPDATE tx_cwtcommunity_albums SET preview_photo_uid = "" WHERE uid = "'.intval($photo[0]['album_uid']).'";');
		}
		
		// Delete photo from filesystem
		$storageFolder = $conf['album.']['photo.']['storageFolder'];
		$file = $storageFolder.$photo[0]['filename'];
		if (@file_exists($file)) {
			unlink($file);
		}
	}
	
	/**
	 * Sets a photo as the preview picture for an album.
	 *
	 * @param		int		The photo's UID.
	 * @param		int		The album's UID to set the preview picture for.
	 */
	public function setPhotoAsPreview($photo_uid, $album_uid) {
		tx_cwtcommunity_lib_common::dbUpdateQuery('UPDATE tx_cwtcommunity_albums SET preview_photo_uid = "'.$photo_uid.'" WHERE uid = "'.$album_uid.'";');
	}

	/**
	 * Adds a comment to a specific photo.
	 *
	 * @param	int		The comment is added to the photo with this UID.
	 * @param	string	The comment itself.
	 */
	public function addComment($photo_uid, $comment) {
		tx_cwtcommunity_lib_common::dbUpdateQuery('INSERT INTO tx_cwtcommunity_photo_comments (pid, crdate, cruser_id, photo_uid, text) VALUES ('.tx_cwtcommunity_lib_common::getGlobalStorageFolder().',"'.time().'", "'.tx_cwtcommunity_lib_common::getLoggedInUserUID().'", "'.$photo_uid.'", "'.$comment.'");');
	}
	
	/**
	 * This delete a single comment
	 *
	 * @param		int		The comment's UID.
	 */
	public function deleteComment($comment_uid) {
		tx_cwtcommunity_lib_common::dbUpdateQuery('DELETE FROM tx_cwtcommunity_photo_comments WHERE uid = "'.intval($comment_uid).'";');
	}
	
	/**
	 * This will send an e-mail to this site's admin concerning a photo that a user has reported.
	 *
	 * @param		int		The photo's UID that has been reported.
	 * @param		int		The feuser UID of the user who reported this photo.
	 * @param		string	The reason for reporting this photo.
	 * @param		int		The callers $conf array (this is usually passed in the 'main()' function of a plugin.).
	 */
	public function reportPhotoToAdmin($photo_uid, $requestor_uid, $reason, $conf) {
		// Construct the mail parts
		$photo = self::getPhoto($photo_uid);
		$mail_subject = tx_cwtcommunity_lib_common::getLL('PHOTO_DETAIL_REPORT_MAIL_SUBJECT');
		$mail_body = tx_cwtcommunity_lib_common::getLL('PHOTO_DETAIL_REPORT_MAIL_BODY');
		$photo_keys = array_keys($photo);
		$mail_body .= '
'.tx_cwtcommunity_lib_common::getLL('PHOTO_DETAIL_REPORT_REASON').': "'.$reason.'"';
		for ($i = 0; $i < sizeof($photo); $i++) {
			$mail_body .= '
'.$photo_keys[$i].': "'.$photo[$photo_keys[$i]].'"';	
		}
		$recipient = $conf['photo.']['detail.']['report.']['recipient']; 
		
		// Set mail sender
		$conf = tx_cwtcommunity_lib_common::getConfArray();
		$fromAddress = $conf['common.']['notification.']['mail.']['fromAddress'];
		$fromName = $conf['common.']['notification.']['mail.']['fromName'];
		$mail_headers = 'From: '.$fromName.' <'.$fromAddress.'>';
		
		// Send mail
		@t3lib_div::plainMailEncoded($recipient, $mail_subject, $mail_body, $mail_headers);
	}
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_community/res/class.tx_cwtcommunity_lib_gallery.php"]) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cwt_community/res/class.tx_cwtcommunity_lib_gallery.php"]);
}
?>