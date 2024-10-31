<?php
	global $platformwrapper, $cpserverurl, $cppluginurl, $siteurl, $homeurl, $cppluginurl, $pluginversion, $jstagname, $existingpluginversion, $sz_widgets_dynamic, $sz_table_prefix;

	require_once(ABSPATH.'wp-content/plugins/sezwho/platformwrapper.php');
	$platformwrapper = platformwrapper::getInstance();
	$cpserverurl = "http://sezwho.com";
	$cpjsserverurl = "http://js.sezwho.com";
	$cpcssserverurl = "http://css.sezwho.com";
	$siteurl = $platformwrapper->yk_get_settings('siteurl');
	$homeurl = $platformwrapper->yk_get_settings('home');
	$cppluginurl = $siteurl."/wp-content/plugins/sezwho";
	$pluginversion = "WP2.2";
	$jstagname = "2.2";
	$pluginversionminor = "1";
	$pluginrevision = "2736";
	$existingpluginversion;
	$sz_plugin_installed = $platformwrapper->yk_get_option("sz_plugin_installed");
	if(false !== $sz_plugin_installed)  //SezWho hasn't been installed
		$existingpluginversion = $platformwrapper->yk_get_plugin_version();
	
	$sz_widgets_dynamic = $platformwrapper->yk_get_widget_dynamic();
	if(-1 == $sz_widgets_dynamic)
	{
		$sz_widgets_dynamic = 0;
		$platformwrapper->yk_set_widget_dynamic($sz_widgets_dynamic);
	}

	$szuserimagerepo = "http://s3.amazonaws.com/sz_users_images/";
	$szuserlinkrepo = $cpserverurl."/mypublicprofile.php?commenter_email=";
	$szuserimagerepo_post = "_t";
	$szuserlinkrepo_use_en = 0;
	$szuserimage_title = "SezWho Profile: ";
	$sz_show_commenter_pic = 0; //For default handling this is set to 0.
	$sz_table_prefix = $platformwrapper->yk_get_table_prefix();
?>
