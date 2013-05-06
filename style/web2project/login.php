<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly');
}

global $AppUI, $w2Pconfig, $loginFromPage;

$AppUI->PL->module = 'login';
$AppUI->PL->action = null;

$pl = &$AppUI->PL;

$pl->addObjects(
  $pl->Container(
    $pl->Row(array(
        $pl->Column(
	  $pl->StaticImageView('./style/' . $this->uistyle . '/images/w2p_logo.jpg', null, null, 'web2Project Home', 0, 'http://www.web2project.net'), 508
        ),
        $pl->Column(null, null, null, null, null, null, 'background:url(\'./style/' . $this->uistyle . '/images/logo_bkgd.jpg\')')
      )
    ), '100%'
  )
);

$pl->addObjects(
  $pl->Container(
    $pl->Row(
      $pl->Column(null, '100%', null, null, null, null, 'background: transparent url(\'./style/' . $this->uistyle . '/images/nav_shadow.jpg\') repeat-x scroll 0%')
    ), '100%'
  )
);

$pl->addObjects(array($pl->HTML('<br />'), $pl->HTML('<br />'), $pl->HTML('<br />'), $pl->HTML('<br />')));

$log_dlg_elems = array(	$pl->Header($pl->HTML('<em>' . $w2Pconfig['company_name'] . '</em>'), null, null, 2),
			$pl->LabeledInput('Username', null, 'text', null, 'username', 25, 255, 'text'),
			$pl->LabeledInput('Password', null, 'password', null, 'password', 25, 32, 'text'),
			array(	$pl->Column($pl->StaticImageView('./style/web2project/w2p_icon.ico', 32, 24, 'web2Project logo', 0, 'http://www.web2project.net/'), null, null, null, 'login_line_left'),
				$pl->Column($pl->Button('submit', 'login', $AppUI->_('login'), 'button'), null, null, null, 'login_line_right')),
			$pl->Column($pl->Anchor('forgotPassword', null, 'f=document.loginform;f.lostpass.value=1;f.submit();'), null, null, 2, 'login_line_center')
		      );
if (w2PgetConfig('activate_external_user_creation') == 'true') {
	$log_dlg_elems = array_merge(	$log_dlg_elems, 
					$pl->Column($pl->Anchor('newAccountSignup', null, 'javascript:window.location=\'./newuser.php\''), null, null, 2, 'login_line_center'));
}

$login_dialog = $pl->DialogBlock($log_dlg_elems, null, null, 'std login');

$pl->addObjects($pl->Form($login_dialog, 'post', $loginFromPage, 'loginform', array('login', 'lostpass', 'redirect'), array(time(), '0', $redirect)));

if ($AppUI->getVersion()) {
	$pl->addObjects($pl->HTML('<div align="center"><span style="font-size:7pt">' . $AppUI->_('Version') . ' ' . $AppUI->getVersion() . '</span></div>'));
}

$msg = '';
if (version_compare(PHP_VERSION, MIN_PHP_VERSION, '<')) {
	$msg .= '<br /><span class="warning">' . $AppUI->_('WARNING: web2Project is not NOT SUPPORTED for this PHP Version') . ' (' . PHP_VERSION . ')</span>';
}
if (!function_exists('mysql_pconnect')) {
	$msg .= '<br /><span class="warning">' . $AppUI->_('WARNING: PHP may not be compiled with MySQL support.  This will prevent proper operation of web2Project.  Please check you system setup.') . '</span>';
}
$pl->addObjects($pl->HTML('<div align="center">' . $msg . '</div>'));
$pl->addObjects($pl->HTML('<center><span style="font-size:7pt"><strong>' . $AppUI->_('You must have cookies enabled in your browser') . '</strong></span></center>'));

$pl->on_body_load = 'document.loginform.username.focus();';
