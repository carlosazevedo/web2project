<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly');
}

global $AppUI;

$AppUI->PL->module = 'login';
$AppUI->PL->action = null;

$pl = &$AppUI->PL;

$pl->addObjects(
  $pl->Container(
    $pl->Row(array(
        $pl->Column(
	  $pl->StaticImageView('./style/' . $this->uistyle . '/images/w2p_logo.jpg', null, null, 'web2Project Home', 0, 'http://www.web2project.net'), 508
        ),
        $pl->Column(null, null, null, null, null, 'background:url(\'./style/' . $this->uistyle . '/images/logo_bkgd.jpg\')')
      )
    ), '100%'
  )
);

$pl->addObjects(
  $pl->Container(
    $pl->Row(
      $pl->Column(null, '100%', null, null, null, 'background: transparent url(\'./style/' . $this->uistyle . '/images/nav_shadow.jpg\') repeat-x scroll 0%')
    ), '100%'
  )
);

$pl->addObjects(array($pl->HTML('<br />'), $pl->HTML('<br />'), $pl->HTML('<br />'), $pl->HTML('<br />')));

/*


    <body bgcolor="#f0f0f0" onload="document.loginform.username.focus();">
        <?php include ('overrides.php'); ?>
        <!--please leave action argument empty -->
        <form method="post" action="<?php echo $loginFromPage; ?>" name="loginform" accept-charset="utf-8">
            <table style="border-style:none;" cellspacing="0" class="std login">
                <input type="hidden" name="login" value="<?php echo time(); ?>" />
                <input type="hidden" name="lostpass" value="0" />
                <input type="hidden" name="redirect" value="<?php echo $redirect; ?>" />
                <tr>
                    <td colspan="2">
                        <?php echo styleRenderBoxTop(); ?>
                    </td>
                </tr>
                <tr>
                    <th colspan="2"><em><?php echo $w2Pconfig['company_name']; ?></em></th>
                </tr>
                <tr>
                    <td style="padding:6px" align="right"><?php echo $AppUI->_('Username'); ?>:</td>
                    <td style="padding:6px" align="right"><input type="text" size="25" maxlength="255" name="username" class="text" /></td>
                </tr>
                <tr>
                    <td style="padding:6px" align="right"><?php echo $AppUI->_('Password'); ?>:</td>
                    <td style="padding:6px" align="right"><input type="password" size="25" maxlength="32" name="password" class="text" /></td>
                </tr>
                <tr>
                    <td style="padding:6px" align="left"><a href="http://www.web2project.net/"><img src="./style/web2project/w2p_icon.ico" width="32" height="24" border="0" alt="web2Project logo" /></a></td>
                    <td style="padding:6px" align="right" valign="bottom"><input type="submit" name="login" value="<?php echo $AppUI->_('login'); ?>" class="button" /></td>
                </tr>
                <tr>
                    <td style="padding:6px" colspan="2"><a href="javascript: void(0);" onclick="f=document.loginform;f.lostpass.value=1;f.submit();"><?php echo $AppUI->_('forgotPassword'); ?></a></td>
                </tr>
                <?php if (w2PgetConfig('activate_external_user_creation') == 'true') { ?>
                    <tr>
                         <td style="padding:6px" colspan="2"><a href="javascript: void(0);" onclick="javascript:window.location='./newuser.php'"><?php echo $AppUI->_('newAccountSignup'); ?></a></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td colspan="2">
                        <?php echo styleRenderBoxBottom(); ?>
                    </td>
                </tr>
            </table>
            <?php if ($AppUI->getVersion()) { ?>
                <div align="center">
                    <span style="font-size:7pt">Version <?php echo $AppUI->getVersion(); ?></span>
                </div>
            <?php } ?>
        </form>
        <div align="center">
            <?php
                echo '<span class="error">' . $AppUI->getMsg() . '</span>';

                $msg = '';
                $msg .= (version_compare(PHP_VERSION, MIN_PHP_VERSION, '<')) ? '<br /><span class="warning">WARNING: web2project is NOT SUPPORT for this PHP Version (' . PHP_VERSION . ')</span>' : '';
                $msg .= function_exists('mysql_pconnect') ? '' : '<br /><span class="warning">WARNING: PHP may not be compiled with MySQL support.  This will prevent proper operation of web2Project.  Please check you system setup.</span>';
                echo $msg;
            ?>
        </div>
        <center><span style="font-size:7pt"><strong><?php echo $AppUI->_('You must have cookies enabled in your browser'); ?></strong></span></center>
    </body>
</html>

*/