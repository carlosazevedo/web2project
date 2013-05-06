<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly');
}

global $AppUI;

$AppUI->PL->module = 'lostpassword';
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

$pass_dlg_elems = array(	$pl->Header($pl->HTML('<em>' . $w2Pconfig['company_name'] . '</em>'), null, null, 2),
				$pl->LabeledInput('Username', null, 'text', null, 'checkusername', 25, 255, 'text'),
				$pl->LabeledInput('EMail', null, 'email', null, 'checkemail', 25, 255, 'text'),
				array(	$pl->Column($pl->StaticImageView('./style/web2project/w2p_icon.ico', 32, 24, 'web2Project logo', 0, 'http://www.web2project.net/'), null, null, null, 'login_line_left'),
					$pl->Column($pl->Button('submit', 'sendpass', $AppUI->_('send password'), 'button'), null, null, null, 'login_line_right'))
		      );

$pass_dialog = $pl->DialogBlock($pass_dlg_elems, null, null, 'std login');

$pl->addObjects($pl->Form($pass_dialog, 'post', null, 'lostpassform', array('lostpass', 'redirect'), array('1', isset($redirect) ? $redirect : '')));

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

$pl->on_body_load = 'document.lostpassform.checkusername.focus();';
