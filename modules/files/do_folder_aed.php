<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$delete = (int) w2PgetParam($_POST, 'del', 0);
$fail = $delete ? 'm=files' : 'm=files&a=addedit_folder';

$controller = new w2p_Controllers_Base(new CFile_Folder(), $delete, 'File Folder', 'm=files', $fail);

$AppUI = $controller->process($AppUI, $_POST);
$AppUI->redirect($controller->resultPath);
