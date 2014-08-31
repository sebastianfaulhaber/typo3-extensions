<?php
	//START CONFIG FOR FEEDIT
	$table = "fe_users";
	//Create Item array
	$items['username']['label'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_USERNAME_LABEL');
	$items['username']['helptext'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_USERNAME_HELPTEXT');
	$items['username']['error_msg'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_USERNAME_ERRMSG');
	$items['username']['type'] = "preview";
	$items['name']['label'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_NAME_LABEL');
	$items['name']['helptext'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_NAME_HELPTEXT');
	$items['name']['error_msg'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_NAME_ERRMSG');
	$items['password']['label'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_PWD_LABEL');
	$items['password']['label_again'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_PWD_LABELAGAIN');
	$items['password']['helptext'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_PWD_HELPTEXT');
	$items['password']['helptext_again'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_PWD_HELPTEXTAGAIN');
	$items['password']['error_msg'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_PWD_ERRMSG');
	$items["password"]["type"] = "password";
	$items["password"]["eval"] = "twice";
	$items['address']['label'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_ADDRESS_LABEL');
	$items['address']['helptext'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_ADDRESS_HELPTEXT');
	$items['address']['error_msg'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_ADDRESS_ERRMSG');
	$items['zip']['label'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_ZIP_LABEL');
	$items['zip']['helptext'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_ZIP_HELPTEXT');
	$items['zip']['error_msg'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_ZIP_ERRMSG');
	$items['city']['label'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_CITY_LABEL');
	$items['city']['helptext'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_CITY_HELPTEXT');
	$items['city']['error_msg'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_CITY_ERRMSG');
	$items['country']['label'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_COUNTRY_LABEL');
	$items['country']['helptext'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_COUNTRY_HELPTEXT');
	$items['country']['error_msg'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_COUNTRY_ERRMSG');
	$items['company']['label'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_COMPANY_LABEL');
	$items['company']['helptext'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_COMPANY_HELPTEXT');
	$items['company']['error_msg'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_COMPANY_ERRMSG');
	$items['telephone']['label'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_PHONE_LABEL');
	$items['telephone']['helptext'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_PHONE_HELPTEXT');
	$items['telephone']['error_msg'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_PHONE_ERRMSG');
	$items['fax']['label'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_FAX_LABEL');
	$items['fax']['helptext'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_FAX_HELPTEXT');
	$items['fax']['error_msg'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_FAX_ERRMSG');
	$items['email']['label'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_EMAIL_LABEL');
	$items['email']['helptext'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_EMAIL_HELPTEXT');
	$items['email']['error_msg'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_EMAIL_ERRMSG');
	$items["email"]["eval"] = "email";
	$items['www']['label'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_WWW_LABEL');
	$items['www']['helptext'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_WWW_HELPTEXT');
	$items['www']['error_msg'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_WWW_ERRMSG');
	$items['tx_cwtcommunityuser_image']['label'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_IMAGE_LABEL');
	$items['tx_cwtcommunityuser_image']['helptext'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_IMAGE_HELPTEXT');
	$items['tx_cwtcommunityuser_image']['error_msg'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_IMAGE_ERRMSG');
	$items['tx_cwtcommunityuser_sex']['label'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_SEX_LABEL');
	$items['tx_cwtcommunityuser_sex']['helptext'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_SEX_HELPTEXT');
	$items['tx_cwtcommunityuser_sex']['error_msg'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_SEX_ERRMSG');
	$items['tx_cwtcommunityuser_gallery_activated']['label'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_GALLERY_ACTIVATED_LABEL');
	$items['tx_cwtcommunityuser_gallery_activated']['helptext'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_GALLERY_ACTIVATED_HELPTEXT');
	$items['tx_cwtcommunityuser_gallery_activated']['error_msg'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_GALLERY_ACTIVATED_HELPTEXT');              
	$items['tx_cwtcommunityuser_profile_access']['label'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_PROFILE_ACCESS_LABEL');
	$items['tx_cwtcommunityuser_profile_access']['helptext'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_PROFILE_ACCESS_HELPTEXT');
	$items['tx_cwtcommunityuser_profile_access']['error_msg'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_PROFILE_ACCESS_ERRMSG');
	$items['tx_cwtcommunityuser_userlist_visibility']['label'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_USERLIST_VISIBILITY_LABEL');
	$items['tx_cwtcommunityuser_userlist_visibility']['helptext'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_USERLIST_VISIBILITY_HELPTEXT');
	$items['tx_cwtcommunityuser_userlist_visibility']['error_msg'] = tx_cwtcommunity_lib_common::getLL('CWT_PROFILE_EDIT_USERLIST_VISIBILITY_ERRMSG');
	
	$cruser_id = tx_cwtcommunity_lib_common::getLoggedInUserUID();
	   $record_uid = $cruser_id;
	
	   $isHashingEnabled = false;
	   if ($conf['profile.']['use_md5_hashed_passwords'] == '1') {
	   	$isHashingEnabled = true;
	   }
	   
	//Create form object
	$form = t3lib_div::makeInstance('tx_cwtfeedit_pi1');
	$form->init($table, $items, $record_uid, $cruser_id, $GLOBALS["TSFE"]->id, array(), $this, array("cwt_community_user"), false, $isHashingEnabled, 0, $this->conf, true);
	//Generate content
	$content.= $form->getFormHeader();
	$content.= $form->getElement("username");
	$content.= $form->getElement("name");
	$content.= $form->getElement("password");
	$content.= $form->getElement("address");
	$content.= $form->getElement("zip");
	$content.= $form->getElement("city");
	$content.= $form->getElement("country");
	$content.= $form->getElement("company");
	$content.= $form->getElement("telephone");
	$content.= $form->getElement("fax");
	$content.= $form->getElement("email");
	$content.= $form->getElement("www");
	$content.= $form->getElement("tx_cwtcommunityuser_image");
	$content.= $form->getElement("tx_cwtcommunityuser_sex");
	$content.= $form->getElement("tx_cwtcommunityuser_gallery_activated");
	$content.= $form->getElement("tx_cwtcommunityuser_profile_access");
	$content.= $form->getElement("tx_cwtcommunityuser_userlist_visibility");
	$content.= $form->getFormFooter();
?>