<?php
/*
Copyright (c) 2007-2013 The web2Project Development Team <w2p-developers@web2project.net>
Copyright (c) 2003-2007 The dotProject Development Team <core-developers@dotproject.net>

This file is part of web2Project.

web2Project is free software; you can redistribute it and/or modify
it under the terms of the Clear BSD License as published by MetaCarta. The
full text of this license is included in LICENSE.

web2Project is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
Clear BSD License for more details.

*/

$loginFromPage = 'index.php';
require_once 'base.php';

clearstatcache();
if (is_file(W2P_BASE_DIR . '/includes/config.php') && filesize(W2P_BASE_DIR . '/includes/config.php') > 0) {
	require_once W2P_BASE_DIR . '/includes/config.php';
	if (isset($dPconfig)) {
		echo '<html><head><meta http-equiv="refresh" content="5; URL=' . W2P_BASE_URL . '/install/index.php"></head><body>';
		echo 'Fatal Error. It appears you\'re converting from dotProject.<br/><a href="./install/index.php">' . 'Click Here To Start the Conversion!</a> (forwarded in 5 sec.)</body></html>';
		exit();
	}
} else {
	echo '<html><head><meta http-equiv="refresh" content="5; URL=' . W2P_BASE_URL . '/install/index.php"></head><body>';
	echo 'Fatal Error. You haven\'t created a config file yet.<br/><a href="./install/index.php">' . 'Click Here To Start Installation and Create One!</a> (forwarded in 5 sec.)</body></html>';
	exit();
}

require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';
require_once W2P_BASE_DIR . '/includes/session.php';

$defaultTZ = w2PgetConfig('system_timezone', 'Europe/London');
date_default_timezone_set($defaultTZ);

// don't output anything. Usefull for fileviewer.php, gantt.php, etc.
$suppressHeaders = w2PgetParam($_GET, 'suppressHeaders', false);

// manage the session variable(s)
w2PsessionStart();

// Force POSIX locale (to prevent functions such as strtolower() from messing up UTF-8 strings)
setlocale(LC_CTYPE, 'C');

// check if session has previously been initialised
if (!isset($_SESSION['AppUI']) || isset($_GET['logout'])) {
	if (isset($_GET['logout']) && isset($_SESSION['AppUI']->user_id)) {
		$AppUI = &$_SESSION['AppUI'];
		$user_id = $AppUI->user_id;
		addHistory('login', $AppUI->user_id, 'logout', $AppUI->user_first_name . ' ' . $AppUI->user_last_name . ' ' . $AppUI->_('logged out'));
	}

	$_SESSION['AppUI'] = new w2p_Core_CAppUI();
}

$AppUI = &$_SESSION['AppUI'];
$last_insert_id = $AppUI->last_insert_id;

$AppUI->checkStyle();

// Function for update last action in user_access_log
$AppUI->updateLastAction($last_insert_id);

// load default preferences if not logged in
if ($AppUI->doLogin()) {
	$AppUI->loadPrefs(0);
}

// Function to register logout in user_acces_log
if (isset($user_id) && isset($_GET['logout'])) {
	$AppUI->registerLogout($user_id);
}

// set the default ui style
$uistyle = $AppUI->getPref('UISTYLE') ? $AppUI->getPref('UISTYLE') : w2PgetConfig('host_style');
$AppUI->PL->setUIStyle($uistyle);

// check is the user needs a new password; shortcut the rest if so
if (w2PgetParam($_POST, 'lostpass', 0)) {
	$AppUI->PL->Render();
	exit();
}

// check if the user is trying to log in
// Note the change to REQUEST instead of POST.  This is so that we can
// support alternative authentication methods such as the PostNuke
// and HTTP auth methods now supported.
if (isset($_POST['login'])) {
	$username = w2PgetCleanParam($_REQUEST, 'username', '');
	$password = w2PgetCleanParam($_REQUEST, 'password', '');
	$redirect = w2PgetCleanParam($_REQUEST, 'redirect', '');
	$AppUI->setUserLocale();
	@include_once (W2P_BASE_DIR . '/locales/' . $AppUI->user_locale . '/locales.php');
	include_once W2P_BASE_DIR . '/locales/core.php';
	$ok = $AppUI->login($username, $password);
	if (!$ok) {
		$AppUI->setMsg('Login Failed', UI_MSG_ERROR);
	} else {
		//Register login in user_acces_log
		$AppUI->registerLogin();
	}
	addHistory('login', $AppUI->user_id, 'login', $AppUI->user_first_name . ' ' . $AppUI->user_last_name . ' ' . $AppUI->_('logged in'));
	$AppUI->redirect('' . $redirect);
}

// clear out main url parameters
$m = '';
$a = '';
$u = '';

// check if we are logged in
if ($AppUI->doLogin()) {
	$AppUI->PL->Render();
	exit();
}

$AppUI->setUserLocale();

// bring in the rest of the support and localisation files
$perms = &$AppUI->acl();

$def_a = 'index';
if (!isset($_GET['m']) && !empty($w2Pconfig['default_view_m'])) {
	$active_modules = $AppUI->getActiveModules();
	$m = $w2Pconfig['default_view_m'];
	if ($perms->checkModule($m, 'view', $AppUI->user_id) && in_array($m, $active_modules)) {
		$m = $w2Pconfig['default_view_m'];
		$def_a = !empty($w2Pconfig['default_view_a']) ? $w2Pconfig['default_view_a'] : $def_a;
		$tab = $w2Pconfig['default_view_tab'];
	} else {
		$m = 'public';
		$def_a = 'welcome';
	}
} else {
	// set the module from the url
	$m = $AppUI->checkFileName(w2PgetCleanParam($_GET, 'm', getReadableModule()));
}

// set the action from the url
$a = $AppUI->checkFileName(w2PgetCleanParam($_GET, 'a', $def_a));
if ($m == 'projects' && $a == 'view' && $w2Pconfig['projectdesigner_view_project'] && !w2PgetParam($_GET, 'bypass') && !(isset($_GET['tab']))) {
	if ($AppUI->isActiveModule('projectdesigner')) {
		$m = 'projectdesigner';
		$a = 'index';
	}
}

/* This check for $u implies that a file located in a subdirectory of higher depth than 1
* in relation to the module base can't be executed. So it would'nt be possible to
* run for example the file module/directory1/directory2/file.php
* Also it won't be possible to run modules/module/abc.zyz.class.php for that dots are
* not allowed in the request parameters.
*/

$u = $AppUI->checkFileName(w2PgetCleanParam($_GET, 'u', ''));

// load module based locale settings
@include_once W2P_BASE_DIR . '/locales/' . $AppUI->user_locale . '/locales.php';
include_once W2P_BASE_DIR . '/locales/core.php';

setlocale(LC_TIME, $AppUI->user_lang);
$m_config = w2PgetConfig($m);

// include the module class file - we use file_exists instead of @ so
// that any parse errors in the file are reported, rather than errors
// further down the track.
$modclass = $AppUI->getModuleClass($m);
if (file_exists($modclass)) {
	include_once ($modclass);
}
if ($u && file_exists(W2P_BASE_DIR . '/modules/' . $m . '/' . $u . '/' . $u . '.class.php')) {
	include_once W2P_BASE_DIR . '/modules/' . $m . '/' . $u . '/' . $u . '.class.php';
}

// do some db work if dosql is set
// TODO - MUST MOVE THESE INTO THE MODULE DIRECTORY
if (isset($_POST['dosql'])) {
	require W2P_BASE_DIR . '/modules/' . $m . '/' . ($u ? ($u . '/') : '') . $AppUI->checkFileName($_POST['dosql']) . '.php';
}

// start output proper
include W2P_BASE_DIR . '/style/' . $uistyle . '/overrides.php';

$AppUI->PL->output_gz = !isset($_POST['dosql']) || $_POST['dosql'] != 'do_file_co';

$pageHandler = new w2p_Output_PageHandler();
$all_tabs   = $pageHandler->loadExtras($_SESSION, $AppUI, $m, 'tabs');
$all_crumbs = $pageHandler->loadExtras($_SESSION, $AppUI, $m, 'crumbs');

$module_file = W2P_BASE_DIR . '/modules/' . $m . '/' . ($u ? ($u . '/') : '') . $a . '.php';
if (file_exists($module_file)) {
	require $module_file;
} else {
	$AppUI->PL->ErrorBlock(UI_MSG_WARNING, 'Unknown module file');
	error_log($AppUI->_('Missing module file') . ': ' . $m);
}

$AppUI->PL->Render();
