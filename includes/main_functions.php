<?php
##
## Global General Purpose Functions
##
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

define('SECONDS_PER_DAY', 86400);

require_once W2P_BASE_DIR . '/includes/backcompat_functions.php';
require_once W2P_BASE_DIR . '/includes/deprecated_functions.php';
require_once W2P_BASE_DIR . '/includes/cleanup_functions.php';
require_once W2P_BASE_DIR . '/lib/adodb/adodb.inc.php';

/**
 * @todo Personally, I'm already hating this autoloader... while it's great in
 * concept, we don't have anything that resembles a real class naming convention
 * so this ends up being nasty and getting nastier.  Hopefully, we can clean
 * these things up for v3.x
 */
spl_autoload_register('w2p_autoload');

function w2p_autoload($class_name)
{
    $name = $class_name;

    if (false !== strpos($name, 'w2p_')) {
        $name = str_replace('_', DIRECTORY_SEPARATOR, $name);
        $classpath = W2P_BASE_DIR . '/classes/' . $name . '.class.php';
        require_once $classpath;
        return;
    }

    $name = strtolower($class_name);
    switch ($name) {
        case 'bcode':                   // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'budgets':                 // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'cappui':                  // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'ccalendar':               // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'cdate':                   // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'cfilefolder':             // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'cforummessage':           // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'cinfotabbox':             // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'cmonthcalendar':          // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'cprojectdesigneroptions': // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'crole':                   // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'csyskey':                 // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'csysval':                 // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'ctabbox_core':            // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'ctasklog':                // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'ctitleblock':             // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'ctitleblock_core':        // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'customfields':            // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'cw2pobject':              // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'dbquery':                 // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'libmail':                 // Deprecated as of v2.3, TODO: remove this in v4.0
        case 'w2pacl':                  // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'w2pajaxresponse':         // Deprecated as of v3.0, TODO: remove this in v4.0
            require_once W2P_BASE_DIR . '/classes/deprecated.class.php';
            break;


        /*
         * The following are all wirings for module classes that don't follow
         * our naming conventions.
         */
        case 'cevent':
            // Deprecated as of v3.0, TODO: remove this in v4.0
            require_once W2P_BASE_DIR . '/modules/calendar/calendar.class.php';
            break;
        case 'cadmin_user':
        case 'cuser':
            // Deprecated as of v3.0, TODO: remove this in v4.0
            require_once W2P_BASE_DIR . '/modules/admin/users.class.php';
            break;

        /*
         * These are our library helper libraries. They're included here to simplify usage.
         */
        case 'date':
            require_once W2P_BASE_DIR . '/lib/PEAR/Date.php';
            break;
        case 'gacl':
            require_once W2P_BASE_DIR . '/lib/phpgacl/gacl.class.php';
            break;
        case 'gacl_api':
            require_once W2P_BASE_DIR . '/lib/phpgacl/gacl_api.class.php';
            break;
        case 'ganttgraph':
            require_once W2P_BASE_DIR . '/lib/jpgraph/src/jpgraph.php';
            require_once W2P_BASE_DIR . '/lib/jpgraph/src/jpgraph_gantt.php';
            break;
        case 'phpmailer':
            require_once W2P_BASE_DIR . '/lib/PHPMailer/class.phpmailer.php';
            break;
        case 'xajax':
            require_once W2P_BASE_DIR . '/lib/xajax/xajax_core/xajax.inc.php';
            break;
        case 'xajaxresponse':
            require_once W2P_BASE_DIR . '/lib/xajax/xajax_core/xajaxResponse.inc.php';
            break;

        default:
            if (file_exists(W2P_BASE_DIR . '/classes/' . $name . '.class.php')) {
                // Deprecated as of v3.0, TODO: remove this in v4.0
                trigger_error("The /classes/$name.class.php 'naming convention' has been deprecated in v3.0 and will be removed by v4.0.", E_USER_NOTICE);
                require_once W2P_BASE_DIR . '/classes/' . $name . '.class.php';
                return;
            }

            if ($name[0] == 'c') {
                $name = substr($name, 1);
            }
            $pieces = (strpos($name, '_') === false) ?
                    array($name, $name) : explode('_', $name);

            /*
             * I switched the order of the path resolution on the modules. The
             *   vast majority of module names/structures fall into this
             *   category, so we'll have marginally faster resolution.
             */
            $plural_pieces = array_map('w2p_pluralize', $pieces);
            if ('systems' == $plural_pieces[0]) {
                $plural_pieces[0] = 'system';
            }
            $path = implode('/', $plural_pieces);
            if (file_exists(W2P_BASE_DIR . '/modules/' . $path . '.class.php')) {
                require_once W2P_BASE_DIR . '/modules/' . $path . '.class.php';
                return;
            }

            $path = implode('/', $pieces);
            if (file_exists(W2P_BASE_DIR . '/modules/' . $path . '.class.php')) {
                require_once W2P_BASE_DIR . '/modules/' . $path . '.class.php';
                return;
            }

            break;
    }
}

/**
 * Merges arrays maintaining/overwriting shared numeric indicees
 *
 * @param type $a1
 * @param type $a2
 * @return type
 */
function arrayMerge($a1, $a2)
{
    if (is_array($a1) && !is_array($a2)) {
        return $a1;
    }
    if (is_array($a2) && !is_array($a1)) {
        return $a2;
    }
    foreach ($a2 as $k => $v) {
        $a1[$k] = $v;
    }
    return $a1;
}

/**
 * Retrieves a configuration setting.
 * @param $key string The name of a configuration setting
 * @param $default string The default value to return if the key not found.
 * @return The value of the setting, or the default value if not found.
 */
function w2PgetConfig($key, $default = null)
{
    global $w2Pconfig;

    if (isset($w2Pconfig[$key])) {
        return $w2Pconfig[$key];
    } else {
//TODO: This block had to be removed because if the w2pgetConfig was called before
//  we had a valid database object, creating the w2p_Core_Config object below would
//  call its parent - w2p_Core_BaseObject - which would try to get an w2p_Core_AppUI
//  which would in turn get back to here.. nasty loop.
//
//        if (!is_null($default)) {
//            $obj = new w2p_Core_Config();
//            $obj->overrideDatabase($dbConn);
//            $obj->config_name = $key;
//            $obj->config_value = $default;
//            $obj->store();
//        }
        return $default;
    }
}

/**
 * Utility function to return a value from a named array or a specified
 *  default, and avoid poisoning the URL by denying:
 * 1) the use of spaces (for SQL and XSS injection)
 * 2) the use of <, ", [, ; and { (for XSS injection)
 */
function w2PgetParam(&$arr, $name, $def = null)
{
    global $AppUI;

    if (isset($arr[$name])) {
        if ((is_array($arr[$name])) || (strpos($arr[$name], ' ') === false
                && strpos($arr[$name], '<') === false && strpos($arr[$name], '"') === false
                && strpos($arr[$name], '[') === false && strpos($arr[$name], ';') === false
                && strpos($arr[$name], '{') === false) || ($arr == $_POST)) {
            return isset($arr[$name]) ? $arr[$name] : $def;
        } else {
            //Hack attempt detected
            //return isset($arr[$name]) ? str_replace(' ','',$arr[$name]) : $def;
            $AppUI->setMsg('Poisoning attempt to the URL detected. Issue logged.', UI_MSG_ALERT);
            $AppUI->redirect(ACCESS_DENIED);
        }
    } else {
        return $def;
    }
}

/**
 * Alternative to protect from XSS attacks.
 */
function w2PgetCleanParam(&$arr, $name, $def = null)
{
    $val = isset($arr[$name]) ? $arr[$name] : $def;
    if (!is_null($val)) {
        return $val;
    }

    // Code from http://quickwired.com/kallahar/smallprojects/php_xss_filter_function.php
    // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
    // this prevents some character re-spacing such as <java\0script>
    // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
    $val = preg_replace('/([\x00-\x08][\x0b-\x0c][\x0e-\x20])/', '', $val);

    // straight replacements, the user should never need these since they're normal characters
    // this prevents like <IMG SRC=&#X40&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3A&#X61&#X6C&#X65&#X72&#X74&#X28&#X27&#X58&#X53&#X53&#X27&#X29>
    $search = 'abcdefghijklmnopqrstuvwxyz';
    $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $search .= '1234567890!@#$%^&*()';
    $search .= '~`";:?+/={}[]-_|\'\\';
    for ($i = 0, $i_cmp = strlen($search); $i < $i_cmp; $i++) {
        // ;? matches the ;, which is optional
        // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
        // &#x0040 @ search for the hex values
        $val = preg_replace('/(&#[x|X]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;
        // &#00064 @ 0{0,7} matches '0' zero to seven times
        $val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ;
    }

    // now the only remaining whitespace attacks are \t, \n, and \r
    $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
    $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout',
        'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
    $ra = array_merge($ra1, $ra2);

    $found = true; // keep replacing as long as the previous round replaced something
    while ($found == true) {
        $val_before = $val;
        for ($i = 0, $i_cmp = sizeof($ra); $i < $i_cmp; $i++) {
            $pattern = '/';
            for ($j = 0, $j_cmp = strlen($ra[$i]); $j < $j_cmp; $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[x|X]0{0,8}([9][a][b]);?)?';
                    $pattern .= '|(&#0{0,8}([9][10][13]);?)?';
                    $pattern .= ')?';
                }
                $pattern .= $ra[$i][$j];
            }
            $pattern .= '/i';
            $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2); // add in <> to nerf the tag
            $val = (in_array($arr[$name], $ra)) ? preg_replace($pattern, $replacement, $val) : $val; // filter out the hex tags
            if ($val_before == $val) {
                // no replacements were made, so exit the loop
                $found = false;
                break;
            }
        }
    }
    return $val;
}

function convert2days($durn, $units)
{
    switch ($units) {
        case 0:
        case 1:
            return $durn / w2PgetConfig('daily_working_hours');
            break;
        case 24:
            return $durn;
    }
}

function filterCurrency($number)
{

    if (substr($number, -3, 1) == ',') {
        // This is the European format, so convert it to the US decimal format.
        $number = str_replace('.', '', $number);
        $number = str_replace(',', '.', $number);
    } else {
        // This is the US format, so just make sure it's clean.
        $number = str_replace(',', '', $number);
    }

    return $number;
}

function w2pFindTaskComplete($start_date, $end_date, $percent, $nowdate = 0) {
    $start = strtotime($start_date);
    $end   = strtotime($end_date);
    if ($nowdate == 0) {
	    $now = time();
    } else {
	    $now = $nowdate;
    }

    if ($percent >= 100) { return 'done'; }
    if ($now > $end)     { return 'late'; }
    if ($now < $start)   { return ''; }
    if ($now > $start && $percent > 0) { return 'active'; }
    if ($now > $start && $percent == 0) { return 'notstarted'; }
}

/**
 * PHP doesn't come with a signum function
 */
function w2Psgn($x)
{
    return $x ? ($x > 0 ? 1 : -1) : 0;
}

function w2p_url($link, $text = '')
{
    $result = '';

    if ($link != '') {
        if (strpos($link, 'http') === false) {
            $link = 'http://' . $link;
        }
        $text = ('' != $text) ? $text : $link;
        $result = '<a href="' . $link . '" target="_new">' . $text . '</a>';
    }
    return $result;
}

function w2p_email($email, $name = '')
{
    $result = '';

    if ($email != '') {
        $name = ('' != $name) ? $name : $email;
        $result = '<a href="mailto:' . $email . '">' . $name . '</a>';
    }
    return $result;
}

function w2p_check_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function w2p_textarea($content)
{
    $result = '';

    if ($content != '') {
        $result = $content;
        $result = htmlentities($result, ENT_QUOTES, 'UTF-8');

        /*
         * Thanks to Alison Gianotto for two regular expressions to make our
         *   links all linky.  This code is based on her work here:
         *   http://www.snipe.net/2009/09/php-twitter-clickable-links
         */
        $result = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $result);
        $result = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r< ]*)#", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $result);
        $result = nl2br($result);
        //$result = html_entity_decode($result);
    }

    return $result;
}

function notifyNewExternalUser($emailAddress, $username, $logname, 
        $logpwd, $emailUtility = null) {

    global $AppUI;
	$mail = (!is_null($emailUtility)) ? $emailUtility : new w2p_Utilities_Mail();
	if ($mail->ValidEmail($emailAddress)) {
//TODO: why aren't we actually using this $email variable?
        if ($mail->ValidEmail($AppUI->user_email)) {
			$email = $AppUI->user_email;
		} else {
//TODO: this email should be set to something sane
            $email = 'web2project@web2project.net';
		}
		$mail->To($emailAddress);
        $emailManager = new w2p_Output_EmailManager($AppUI);
        $body = $emailManager->notifyNewExternalUser($logname, $logpwd);
		$mail->Subject('New Account Created');
        $mail->Body($body);
		$mail->Send();
	}
}

function notifyNewUser($emailAddress, $username, $emailUtility = null) {
	global $AppUI;
	$mail = (!is_null($emailUtility)) ? $emailUtility : new w2p_Utilities_Mail();
	if ($mail->ValidEmail($emailAddress)) {
//TODO: why aren't we actually using this $email variable?
        if ($mail->ValidEmail($AppUI->user_email)) {
			$email = $AppUI->user_email;
		} else {
//TODO: this email should be set to something sane
            return false;
		}

		$mail->To($emailAddress);
        $emailManager = new w2p_Output_EmailManager($AppUI);
        $body = $emailManager->getNotifyNewUser($username);
        $mail->Subject('New Account Created');
		$mail->Body($body);
		$mail->Send();
	}
}

/**
 * Function to collect delegations within a period
 *
 * @param Date the starting date of the period
 * @param Date the ending date of the period
 * @param array by-ref an array of links to append new items to
 * @param int the length to truncate entries by
 * @param int the company id to filter by
 */
function getDelegationLinks($startPeriod, $endPeriod, &$links, $strMaxLen, $company_id = 0, $minical = false, $user_id = null) {
	global $a, $AppUI;
	if (!isset($user_id)) {
		$user_id = $AppUI->user_id;
	}
	// List only delegations to the specified user
	$delegs = CDelegation::getDelegationsForPeriod($startPeriod, $endPeriod, $company_id, $user_id);
	$tf = $AppUI->getPref('TIMEFORMAT');
	//subtract one second so we don't have to compare the start dates for exact matches with the startPeriod which is 00:00 of a given day.
	$startPeriod->subtractSeconds(1);

	$link = array();
	
	// assemble the links for the tasks
	foreach ($delegs as $row) {
		// the link
		$link['delegation'] = true;

		if (!$minical) {
			$link['href'] = '?m=delegations&a=view&delegation_id=' . $row['delegation_id'];
			// the link text
			if (mb_strlen($row['delegation_name']) > $strMaxLen) {
				$row['short_name'] = mb_substr($row['delegation_name'], 0, $strMaxLen) . '...';
			} else {
				$row['short_name'] = $row['delegation_name'];
			}
	
			$link['td'] = 'background-color:#' . $row['project_color_identifier'] . '; ';
			$link['text'] = '<span style="color:' . bestColor($row['project_color_identifier']) . '">' . $row['short_name'] . '</span>';
	        }

		// determine which day(s) to display the task
		$start = new w2p_Utilities_Date($AppUI->formatTZAwareTime($row['delegation_start_date'], '%Y-%m-%d %T'));
		$end = $row['task_end_date'] ? new w2p_Utilities_Date($AppUI->formatTZAwareTime($row['task_end_date'], '%Y-%m-%d %T')) : null;

		// First we test if the Tasks Starts and Ends are on the same day, if so we don't need to go any further.
		if (($start->after($startPeriod)) && ($end && $end->after($startPeriod) && $end->before($endPeriod) && !($start->dateDiff($end)))) {
			$temp = $link;
			if (!$minical) {
				if ($a != 'day_view') {
						$tmp = $temp['text'];
						$temp['text'] = '<table class="calendar_cell" cellspacing="0"><tr><td class="calendar_cell_dates">' . w2PtoolTip($row['delegation_name'], getDelegationTooltip($row['delegation_id'], true, true, $delegs), true, '', 'inline-tooltip') . $start->format($tf) . w2PshowImage('deleg-start-16.png') . '<br>' . w2PshowImage('deleg-end-16.png') . $end->format($tf) . w2PendTip() . '</td>';
						$temp['text'] .= '<td style="' . $temp['td'] . '" class="calendar_cell_text">' . $tmp . '<a href="?m=delegations&amp;a=view&amp;delegation_id=' . $row['delegation_id'] . '"></a></td></tr></table>';
				}
			}
			$temp['timestamp'] = $start->format(FMT_DATETIME_MYSQL);
			$links[$end->format(FMT_TIMESTAMP_DATE)][] = $temp;
		} else {
			// If they aren't, we will now need to see if the Tasks Start date is between the requested period
			if ($start->after($startPeriod) && $start->before($endPeriod)) {
				$temp = $link;
				if (!$minical) {
					if ($a != 'day_view') {
						$tmp = $temp['text'];
						$temp['text'] = '<table class="calendar_cell" cellspacing="0"><tr><td class="calendar_cell_dates">' . w2PtoolTip($row['delegation_name'], getDelegationTooltip($row['delegation_id'], true, false, $delegs), true, '', 'inline-tooltip') . $start->format($tf) . w2PshowImage('deleg-start-16.png') . w2PendTip() . '</td>';
						$temp['text'] .= '<td style="' . $temp['td'] . '" class="calendar_cell_text">' . $tmp . '<a href="?m=delegations&amp;a=view&amp;delegation_id=' . $row['delegation_id'] . '"></a></td></tr></table>';
					}
				}
				$temp['timestamp'] = $start->format(FMT_DATETIME_MYSQL);
				$links[$start->format(FMT_TIMESTAMP_DATE)][] = $temp;
			}
			// And now the Tasks End date is checked if it is between the requested period too.
			if ($end && $end->after($startPeriod) && $end->before($endPeriod) && $start->before($end)) {
				$temp = $link;
				if (!$minical) {
					if ($a != 'day_view') {
						$tmp = $temp['text'];
						$temp['text'] = '<table class="calendar_cell" cellspacing="0"><tr><td class="calendar_cell_dates">' . w2PtoolTip($row['delegation_name'], getDelegationTooltip($row['delegation_id'], false, true, $delegs), true, '', 'inline-tooltip') . $end->format($tf) . w2PshowImage('deleg-end-16.png') . w2PendTip() . '</td>';
						$temp['text'] .= '<td style="' . $temp['td'] . '" class="calendar_cell_text">' . $tmp . '<a href="?m=delegations&amp;a=view&amp;delegation_id=' . $row['delegation_id'] . '"></a></td></tr></table>';
					}
				}
				$temp['timestamp'] = $end->format(FMT_DATETIME_MYSQL);
				$links[$end->format(FMT_TIMESTAMP_DATE)][] = $temp;
			}
		}
	}
}

function getDelegationTooltip($delegation_id, $starts = false, $ends = false ) {
	global $AppUI;

	if (!$delegation_id) {
		return '';
	}

	$df = $AppUI->getPref('SHDATEFORMAT');
	$tf = $AppUI->getPref('TIMEFORMAT');

	// load the record data
	$deleg = new CDelegation();
	$deleg->load($delegation_id);

	// get some info from the task
	$task = new CTask();
	$task->load($deleg->delegation_task);

	$start_date = (int)$deleg->delegation_start_date ? new w2p_Utilities_Date($AppUI->formatTZAwareTime($deleg->delegation_start_date, '%Y-%m-%d %T')) : null;
	$end_date = (int)$task->task_end_date ? new w2p_Utilities_Date($AppUI->formatTZAwareTime($task->task_end_date, '%Y-%m-%d %T')) : null;

	// load the record data
	$deleg_project = $task->project_name;
	$deleg_company = $task->company_name;

	$tt = '<table class="tool-tip">';
	$tt .= '<tr>';
	$tt .= '	<td valign="top" width="40%">';
	$tt .= '		<strong>' . $AppUI->_('Details') . '</strong>';
	$tt .= '		<table cellspacing="3" cellpadding="2" width="100%">';
	$tt .= '		<tr>';
	$tt .= '			<td class="tip-label">' . $AppUI->_('Company') . '</td>';
	$tt .= '			<td>' . $deleg_company . '</td>';
	$tt .= '		</tr>';
	$tt .= '		<tr>';
	$tt .= '			<td class="tip-label">' . $AppUI->_('Project') . '</td>';
	$tt .= '			<td>' . $deleg_project . '</td>';
	$tt .= '		</tr>';
	$tt .= '		<tr>';
	$tt .= '			<td class="tip-label">' . $AppUI->_('Delegated from') . '</td>';
	$tt .= '			<td>' . CContact::getContactByUserid($deleg->delegating_user_id) . '</td>';
	$tt .= '		</tr>	';
	$tt .= '		<tr>';
	$tt .= '			<td class="tip-label">' . $AppUI->_('Progress') . '</td>';
	$tt .= '			<td>' . sprintf("%.1f%%", $deleg->delegation_percent_complete) . '</td>';
	$tt .= '		</tr>	';
	$tt .= '		<tr>';
	$tt .= '			<td class="tip-label">' . $AppUI->_('Starts') . '</td>';
	$tt .= '			<td>' . ($start_date ? $start_date->format($df . ' ' . $tf) : '-') . '</td>';
	$tt .= '		</tr>';
	$tt .= '		<tr>';
	$tt .= '			<td class="tip-label">' . $AppUI->_('Ends') . '</td>';
	$tt .= '			<td>' . ($end_date ? $end_date->format($df . ' ' . $tf) : '-') . '</td>';
	$tt .= '		</tr>';
	$tt .= '		</table>';
	$tt .= '	</td>';
	$tt .= '	<td width="60%" valign="top">';
	$tt .= '		<strong>' . $AppUI->_('Description') . '</strong>';
	$tt .= '		<table cellspacing="0" cellpadding="2" border="0" width="100%">';
	$tt .= '		<tr>';
	$tt .= '			<td class="tip-label description">';
	$tt .= '				' . $deleg->delegation_description;
	$tt .= '			</td>';
	$tt .= '		</tr>';
	$tt .= '		</table>';
	$tt .= '	</td>';
	$tt .= '</tr>';
	$tt .= '</table>';
	return $tt;
}