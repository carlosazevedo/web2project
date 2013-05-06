<?php
/**
 * The Application Presentation Layer Class.
 *
 * @package     web2project\output
 * @author      Carlos Azevedo
 */

class PL_Object {

	public $html = null;
	public $class = null;
	public $id = null;
	public $style = null;
	public $extra = null;

	protected $contains = null;

	public function __construct($html)
	{
		$this->html = $html;
	}

	public function Render()
	{
		return $this->html;
	}

	public function addObjects($to_contain)
	{
		if (is_array($to_contain)) {
			foreach ($to_contain as $child) {
				if (!is_null($child)) {
					$this->contains[] = $child;
				}
			}
		} else if (!is_null($to_contain)) {
			$this->contains[] = $to_contain;
		}
	}

	public function appendValue($value, $name)
	{
		if (!is_null($value)) {
			return $name . '="' . $value . '" ';
		}
		return '';
	}

}

class PL_Container extends PL_Object {

	public $width = null;
	public $height = null;
	public $cellspacing = 0;
	public $cellpadding = 0;
	public $border = 0;
	

	public function __construct($wdt, $hgt, $cspacing, $cpadding, $brd, $cls, $oid, $stl, $ext)
	{
		$this->width = $wdt;
		$this->height = $hgt;
		$this->cellspacing = $cspacing;
		$this->cellpadding = $cpadding;
		$this->border = $brd;
		$this->class = $cls;
		$this->id = $oid;
		$this->style = $stl;
		$this->extra = $ext;
		$this->contains = array();
	}

	public function Render()
	{
		$html = '<table ' . $this->appendValue($this->width, 'width') . $this->appendValue($this->cellspacing, 'cellspacing') . 
			 	    $this->appendValue($this->cellpadding, 'cellpadding') . $this->appendValue($this->border, 'border') .
				    $this->appendValue($this->class, 'class') . $this->appendValue($this->id, 'id') .
				    $this->appendValue($this->style, 'style') . ' ' . $this->extra . '>';
		if (is_array($this->contains)) {
			foreach ($this->contains as &$obj) {
				$html .= $obj->Render();
			}
		}
		$html .= '</table>';
		return $html;
	}

}

class PL_Row extends PL_Object {

	public $width = null;
	public $height = null;
	public $rowspan = null;
	

	public function __construct($wdt, $hgt, $rs, $cls, $oid, $stl, $ext)
	{
		$this->width = $wdt;
		$this->height = $hgt;
		$this->rowspan = $rs;
		$this->class = $cls;
		$this->id = $oid;
		$this->style = $stl;
		$this->extra = $ext;
		$this->contains = array();
	}

	public function Render()
	{
		$html = '<tr ' . $this->appendValue($this->rowspan, 'rowspan') . $this->appendValue($this->class, 'class') . 
				 $this->appendValue($this->id, 'id') . $this->appendValue($this->style, 'style') . 
				 ' ' . $this->extra . '>';
		if (is_array($this->contains)) {
			foreach ($this->contains as &$obj) {
				$html .= $obj->Render();
			}
		}
		$html .= '</tr>';
		return $html;
	}

}

class PL_Column extends PL_Object {

	public $width = null;
	public $height = null;
	public $colspan = null;
	

	public function __construct($wdt, $hgt, $cs, $cls, $oid, $stl, $ext)
	{
		$this->width = $wdt;
		$this->height = $hgt;
		$this->colspan = $cs;
		$this->class = $cls;
		$this->id = $oid;
		$this->style = $stl;
		$this->extra = $ext;
		$this->contains = array();
	}

	public function Render()
	{
		$html = '<td ' . $this->appendValue($this->width, 'width') . $this->appendValue($this->colspan, 'colspan') . 
				 $this->appendValue($this->class, 'class') . $this->appendValue($this->id, 'id') . 
				 $this->appendValue($this->style, 'style') . ' ' . $this->extra . '>';
		if (is_array($this->contains)) {
			foreach ($this->contains as &$obj) {
				$html .= $obj->Render();
			}
		} else {
			$html .= '&nbsp;';
		}
		$html .= '</td>';
		return $html;
	}

}

class PL_Header extends PL_Object {

	public $width = null;
	public $height = null;
	public $colspan = null;
	

	public function __construct($wdt, $hgt, $cs, $cls, $oid, $stl, $ext)
	{
		$this->width = $wdt;
		$this->height = $hgt;
		$this->colspan = $cs;
		$this->class = $cls;
		$this->id = $oid;
		$this->style = $stl;
		$this->extra = $ext;
		$this->contains = array();
	}

	public function Render()
	{
		$html = '<th ' . $this->appendValue($this->width, 'width') . $this->appendValue($this->colspan, 'colspan') . 
				 $this->appendValue($this->class, 'class') . $this->appendValue($this->id, 'id') . 
				 $this->appendValue($this->style, 'style') . ' ' . $this->extra . '>';
		if (is_array($this->contains)) {
			foreach ($this->contains as &$obj) {
				$html .= $obj->Render();
			}
		} else {
			$html .= '&nbsp;';
		}
		$html .= '</th>';
		return $html;
	}

}

class PL_StaticImage extends PL_Object {

	public $source = null;
	public $width = null;
	public $height = null;
	public $alt_text = null;
	public $border = null;
	public $href = null;


	public function __construct($src, $wdt, $hgt, $alt, $brd, $href, $cls, $oid, $stl, $ext)
	{
		$this->source = $src;
		$this->width = $wdt;
		$this->height = $hgt;
		$this->alt_text = $alt;
		$this->border = $brd;
		$this->href = $href;
		$this->class = $cls;
		$this->id = $oid;
		$this->style = $stl;
		$this->extra = $ext;
	}

	public function Render()
	{
		$html = '';
		if (!is_null($this->href)) {
			$html .= '<a href="' . $this->href . '">';
		}
		$html .= '<img ' . $this->appendValue($this->border, 'border') . $this->appendValue($this->alt_text, 'alt') . 
			 	   $this->appendValue($this->source, 'src') . $this->appendValue($this->class, 'class') . 
				   $this->appendValue($this->id, 'id') . $this->appendValue($this->style, 'style') . 
				   ' ' . $this->extra . '>';
		if (!is_null($this->href)) {
			$html .= '</a>';
		}
		return $html;
	}

}

class PL_Label extends PL_Object {

	public $text = null;
	public $relates_to = null;

	public function __construct($txt, $r_t, $cls, $oid, $stl, $ext)
	{
		$this->text = $txt;
		$this->relates_to = $r_t;
		$this->class = $cls;
		$this->id = $oid;
		$this->style = $stl;
		$this->extra = $ext;
	}

	public function Render()
	{
		if (isset($this->relates_to)) {
			$html = '<label for="' . $this->relates_to . '" '. $this->appendValue($this->class, 'class') . 
				 $this->appendValue($this->id, 'id') . $this->appendValue($this->style, 'style') . 
				 ' ' . $this->extra . '>';
		} else {
			$html = '<span '. $this->appendValue($this->class, 'class') . $this->appendValue($this->id, 'id') . 
				 $this->appendValue($this->style, 'style') . ' ' . $this->extra . '>';
		}
		$html .= ($this->text . ':');
		if (isset($this->relates_to)) {
			$html .= '</label>';
		} else {
			$html .= '</span>';
		}
		return $html;
	}

}

class PL_LineEdit extends PL_Object {

	public $type = 'text';
	public $name = null;
	public $size = null;
	public $maxlength = null;
	public $readonly = null;

	public function __construct($ty, $nm, $sz, $ml, $ro, $cls, $oid, $stl, $ext)
	{
		$this->type = $ty;
		$this->name = $nm;
		$this->size = $sz;
		$this->maxlength = $ml;
		$this->readonly = $ro;
		$this->class = $cls;
		$this->id = $oid;
		$this->style = $stl;
		$this->extra = $ext;
	}

	public function Render()
	{
		$html = '<input type="' . $this->type . '" name="' . $this->name . '" ' . $this->appendValue($this->size, 'size') .
			 $this->appendValue($this->maxlength, 'maxlength') . ($this->readonly ? 'readonly="readonly" ' : '') .
			 $this->appendValue($this->class, 'class') . $this->appendValue($this->id, 'id') .
			 $this->appendValue($this->style, 'style') . ' ' . $this->extra . '>';
		return $html;
	}

}

class PL_Button extends PL_Object {

	public $type = 'submit';
	public $name = null;
	public $value = null;

	public function __construct($ty, $nm, $vl, $cls, $oid, $stl, $ext)
	{
		$this->type = $ty;
		$this->name = $nm;
		$this->value = $vl;
		$this->class = $cls;
		$this->id = $oid;
		$this->style = $stl;
		$this->extra = $ext;
	}

	public function Render()
	{
		$html = '<input type="' . $this->type . '" name="' . $this->name . '" ' . $this->appendValue($this->value, 'value') .
			 $this->appendValue($this->class, 'class') . $this->appendValue($this->id, 'id') .
			 $this->appendValue($this->style, 'style') . ' ' . $this->extra . '>';
		return $html;
	}

}

class PL_Anchor extends PL_Object {

	public $text = null;
	public $href = null;
	public $onclick = null;

	public function __construct($txt, $hr, $oc, $cls, $oid, $stl, $ext)
	{
		$this->text = $txt;
		$this->href = $hr;
		$this->onclick = $oc;
		$this->class = $cls;
		$this->id = $oid;
		$this->style = $stl;
		$this->extra = $ext;
	}

	public function Render()
	{
		$html = '<a href="' . (isset($this->href) ? $this->href : 'javascript: void(0);') .'" ' . 
			 $this->appendValue($this->onclick, 'onclick') . $this->appendValue($this->class, 'class') . 
			 $this->appendValue($this->id, 'id') . $this->appendValue($this->style, 'style') . ' ' . $this->extra . '>' .
			 $this->text . '</a>';
		return $html;
	}

}

class PL_Form extends PL_Object {

	public $method = null;
	public $action = null;
	public $name = null;
	public $hidden_fields_names = null;
	public $hidden_fields_values = null;


	public function __construct($meth, $act, $nm, $hfn, $hfv)
	{
		$this->method = $meth;
		$this->action = $act;
		$this->name = $nm;
		$this->hidden_fields_names = $hfn;
		$this->hidden_fields_values = $hfv;
	}

	public function Render()
	{
		$html = '<form method="' . $this->method . '" action="' . $this->action . '" name="' . $this->name . '" accept-charset="utf-8">';
		if (is_array($this->hidden_fields_names)) {
			foreach ($this->hidden_fields_names as $i => $name) {
				$html .= '<input type="hidden" name="' . $name . '" value="' . $this->hidden_fields_values[$i] . '" />';
			}
		}
		if (is_array($this->contains)) {
			foreach ($this->contains as &$obj) {
				$html .= $obj->Render();
			}
		}
		$html .= '</form>';
		return $html;
	}

}



class w2p_Output_PresentationLayer {

	public $user_timezone = null;
	public $system_timezone = null;

	public $AppUI = null;

	public $total_width = null;

	public $suppress_headers = false;
	public $head_description = 'web2Project Default Style';

	public $module = null;
	public $action = null;
	public $uistyle = 'web2project';
	public $dialog_title = null;
	public $is_dialog = false;
	public $on_body_load = null;

	public $mobile_version = false;
	public $output_gz = true;

	private $render_elements = null;

	public function __construct(&$app_ui = null, $width = null, $user_tz = null, $style = null) 
	{
		$system_timezone = w2PgetConfig('system_timezone', 'Europe/London');

		if (is_null($user_tz)) {
			$this->user_timezone = $system_timezone;
		} else {
			$this->user_timezone = $user_tz;
		}

		if (is_null($width)) {
			$this->total_width = "100 %";
		} else {
			$this->total_width = $width;
		}

		if (is_null($app_ui)) {
			throw new Exception('Missing AppUI instance');
		} else {
			$this->AppUI = $app_ui;
		}

		if (!is_null($style)) {
			$this->uistyle = $style;
		}

		$render_elements = array();
	}

	public function addObjects($to_contain)
	{
		if (is_array($to_contain)) {
			foreach ($to_contain as $child) {
				if (!is_null($child)) {
					$this->render_elements[] = $child;
				}
			}
		} else if (!is_null($to_contain)) {
			$this->render_elements[] = $to_contain;
		}
	}

	public function setUIStyle($new_style)
	{
		// Get the style options array
		$styles = $this->AppUI->readDirs('style');
		
		// Check if the requested style exists
		if (in_array($new_style, $styles)) {
			$this->uistyle = $new_style;
		}
	}

	private function outputHead()
	{
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">';
		echo '<head>';
		echo '<meta name="Description" content="' . $this->head_description . '" />';
		echo '<meta name="Version" content="' . $this->AppUI->getVersion() . '" />';
		echo '<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />';
		echo '<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />';
		echo '<title>' . (is_null($this->dialog_title) ? @w2PgetConfig('page_title') : $this->dialog_title) . ' :: ' . $this->AppUI->_($this->module . $this->action) . '</title>';
		if ($mobile_version && file_exists('./style/common_mobile.css')) {
			echo '<link rel="stylesheet" type="text/css" href="./style/common_mobile.css" media="all" charset="utf-8"/>';
		} else if (file_exists('./style/common.css')) {
			echo '<link rel="stylesheet" type="text/css" href="./style/common.css" media="all" charset="utf-8"/>';
		}
		if ($mobile_version && file_exists('./style/' . $this->uistyle . '/style_common_mobile.css')) {
			echo '<link rel="stylesheet" type="text/css" href="./style/' . $this->uistyle . '/style_common_mobile.css" media="all" charset="utf-8"/>';
		} else if (file_exists('./style/' . $this->uistyle . '/style_common.css')) {
			echo '<link rel="stylesheet" type="text/css" href="./style/' . $this->uistyle . '/style_common.css" media="all" charset="utf-8"/>';
		}
		if ($mobile_version && file_exists('./modules/' . $module . '/module_mobile.css')) {
			echo '<link rel="stylesheet" type="text/css" href="./modules/' . $module . '/module_mobile.css" media="all" charset="utf-8"/>';
		} else if (file_exists('./modules/' . $module . '/module.css')) {
			echo '<link rel="stylesheet" type="text/css" href="./modules/' . $module . '/module.css" media="all" charset="utf-8"/>';
		}
		if ($mobile_version && file_exists('./style/' . $this->uistyle . '/' . $module . '/style_module_mobile.css')) {
			echo '<link rel="stylesheet" type="text/css" href="./style/' . $this->uistyle . '/' . $module . '/style_module_mobile.css" media="all" charset="utf-8"/>';
		} else if (file_exists('./style/' . $this->uistyle . '/' . $module . '/style_module.css')) {
			echo '<link rel="stylesheet" type="text/css" href="./style/' . $this->uistyle . '/' . $module . '/style_module.css" media="all" charset="utf-8"/>';
		}
		if (file_exists('./js/common.js')) {
			echo '<script type="text/javascript" src="./js/common.js" />';
		}
		if (file_exists('./modules/' . $module . '/module.js')) {
			echo '<script type="text/javascript" src="./modules/' . $module . '/module.js" />';
		}
		if (file_exists('./style/' . $this->uistyle . '/favicon.ico')) {
			echo '<link rel="shortcut icon" href="./style/' . $this->uistyle . '/favicon.ico" type="image/ico" />';
		}
		echo '</head>';
	}

	protected function outputBody()
	{
		echo '<body' . (isset($this->on_body_load) ? ' onload="' . $this->on_body_load . '"' : '') . '>';
		unset($this->on_body_load);
	}

	protected function outputIncludes()
	{
		if ($mobile_version && file_exists('./style/' . $this->uistyle . '/override_mobile.php')) {
			include_once ('./style/' . $this->uistyle . '/override_mobile.php');
		} else if (file_exists('./style/' . $this->uistyle . '/override.php')) {
			include_once ('./style/' . $this->uistyle . '/override.php');
		}
	}

	protected function outputNewPasswordForm()
	{
		$this->AppUI->setUserLocale();
		@include_once W2P_BASE_DIR . '/locales/' . $this->AppUI->user_locale . '/locales.php';
		include_once W2P_BASE_DIR . '/locales/core.php';
		setlocale(LC_TIME, $this->AppUI->user_lang);
		if (w2PgetParam($_POST, 'sendpass', 0)) {
			sendNewPass();
		} else {
			require W2P_BASE_DIR . '/style/' . $this->uistyle . '/lostpass.php';
		}
	}

	protected function outputLoginForm()
	{
		// Load basic locale settings
		$this->AppUI->setUserLocale();
		@include_once ('./locales/' . $this->AppUI->user_locale . '/locales.php');
		include_once ('./locales/core.php');
		setlocale(LC_TIME, $this->AppUI->user_lang);
		$redirect = $_SERVER['QUERY_STRING'] ? strip_tags($_SERVER['QUERY_STRING']) : '';
		if (strpos($redirect, 'logout') !== false) {
			$redirect = '';
		}

		require W2P_BASE_DIR . '/style/' . $this->uistyle . '/login.php';
		// Destroy the current session and output login page
		session_unset();
		session_destroy();
	}

	protected function outputErrorMessage()
	{
		echo '<div id="error-message" title="' . $this->AppUI->_('Error Message') . '">';
		$err_msg = $this->AppUI->getMsg();
		if ($err_msg != '') {
			echo $err_msg;
		}
		echo '</div>';
	}

	protected function outputSiteID()
	{
		echo '<div class="siteid">';
		echo '<a href="http://www.web2project.net/" target="_new" title="web2Project v. ' . $this->AppUI->getVersion() . $this->AppUI->_('click to visit web2Project site') . '">';
		echo '<img src="style/' . $this->uistyle . '/images/title.jpg" border="0" class="banner" align="left" alt="' . $this->AppUI->_('click to visit web2Project site') . '" />';
		echo '</a>';
		echo '</div>';
	}

	protected function outputMenu()
	{
		echo '<div class="menu current-' . $module . '">';
		$nav = $this->AppUI->getMenuModules();
		echo '<ul id="headerNav">';
		foreach ($nav as $mod) {
			if (canAccess($mod['mod_directory'])) {
				echo '<li class="' . $mod['mod_directory'] . '">';
				echo '<a href="?m=' . $mod['mod_directory'] . '" class="' . (($module == $mod['mod_directory']) ? 'module' : '') . '">' . $this->AppUI->_($mod['mod_ui_name']) . '</a>';
				echo '</li>';
			}
		}
		echo '</ul>';
		echo '</div>';
	}

	protected function outputQuickButtons()
	{
		echo '<div class="quick_buttons_block">';
		echo '<div class="userid">';
		if ($this->AppUI->user_id > 0) {
			echo $this->AppUI->_('Welcome') . ' ' . $this->AppUI->user_display_name;
                        echo '<br />';
			echo $this->AppUI->_('Server time is') . ' ' . $this->AppUI->getTZAwareTime();
		}
		echo '</div>';
		if ($this->AppUI->user_id > 0) {
			echo '<div class="quick_buttons">';
			$help_page = $this->AppUI->helpPages($module);
			echo '<div class="help">';
			echo $this->AnchorButton('Help', $help_page, 'help');
			echo '</div>';
			$buttons = $this->AppUI->quick_buttons;
			foreach ($buttons as $button)	{
				echo $this->AnchorButton($button['text'], $button['href'], $button['class']);
			}
			echo '<div class="quick_actions">';
			$actions = $this->AppUI->quick_actions;
			echo $this->AnchorDropDown($actions['text'], $actions['href'], $module, 'quick_action');
			echo '</div>';
			echo '<div class="logout">';
			echo $this->AnchorButton('Logout', './index.php?logout=-1', 'logout');
			echo '</div>';
			echo '</div>';
		}
	}

	public function outputHTML()
	{
		if (count($this->render_elements)) {
			foreach ($this->render_elements as &$obj) {
				echo $obj->Render();
			}
			unset($this->render_elements);
			$this->render_elements = array();
		}
	}

	public function HTML($html)
	{
		$obj = new PL_Object($html);
		return $obj;
	}

	public function Label($text, $relates_to = null, $translate = true, $class = null, $id = null, $style = null, $extra = null)
	{
		$obj = new PL_Label($translate ? $this->AppUI->_($text) : $text, $relates_to, $class, $id, $style, $extra);
		return $obj;
	}

	public function Anchor($text, $href = null, $onclick = null, $class = null, $id = null, $style = null, $extra = null)
	{
		$obj = new PL_Anchor($this->AppUI->_($text), $href, $onclick, $class, $id, $style, $extra);
		return $obj;
	}

	public function AnchorButton($text, $href, $class)
	{
	}

	public function AnchorDropDown($texts, $hrefs, $selected, $class)
	{
	}

	public function NumberEdit()
	{
	}

	public function LineEdit($type, $name, $size = null, $maxlength = null, $readonly = false, $class = null, $id = null, $style = null, $extra = null)
	{
		$text = strtolower($text);
		if (in_array($type, array('email', 'password', 'text', 'url'))) {
			$obj = new PL_LineEdit($type, $name, $size, $maxlength, $readonly, $class, $id, $style, $extra);
			return $obj;
		}
	}

	public function TextEdit()
	{
	}

	public function MoneyEdit()
	{
	}

	public function DateEdit()
	{
	}

	public function DateTimeEdit()
	{
	}

	public function NumberArrowEdit()
	{
	}

	public function Button($type, $name, $value, $class = null, $id = null, $style = null, $extra = null)
	{
		$text = strtolower($text);
		if (in_array($type, array('button', 'file', 'reset', 'submit'))) {
			$obj = new PL_Button($type, $name, $value, $class, $id, $style, $extra);
			return $obj;
		}
	}

	public function CheckButton()
	{
	}

	public function RadioButton()
	{
	}

	public function DropDownSelect()
	{
	}

	public function ListSelect()
	{
	}

	public function List2ListSelect()
	{
	}

	public function StaticImageView($src, $width = null, $height = null, $alt = null, $border = 0, $href = null, $class = null, $id = null, $style = null, $extra = null)
	{
		if (isset($alt)) {
			$alt = $this->AppUI->_($alt);
		}
		$obj = new PL_StaticImage($src, $width, $heigth, $alt, $border, $href, $class, $id, $style, $extra);
		return $obj;
	}

	public function NumberView()
	{
	}

	public function LineView()
	{
	}

	public function TextView()
	{
	}

	public function MoneyView()
	{
	}

	public function DateView()
	{
	}

	public function DateTimeView()
	{
	}

	public function Table()
	{
	}

	public function Tree()
	{
	}

	public function LabeledInput($label, $labelspan, $type, $editspan, $name, $size, $maxlength, $class = null, $id = null, $style = null, $extra = null)
	{
		$left = $this->Column($this->Label($label, $name, true, $class), null, null, $labelspan, 'labeled_input_label');
		$right = $this->Column($this->LineEdit($type, $name, $size, $maxlength, false, $class, $id, $style, $extra), null, null, $editspan, 'labeled_input_edit');
		return array($left, $right);
	}

	public function LabeledBlock()
	{
	}

	public function TabbedBlock()
	{
	}

	public function DialogBlock($to_contain, $width = null, $heigth = null, $class = null, $id = null, $style = null, $extra = null)
	{
		$cont = new PL_Container($width, $heigth, 0, 0, 0, $class, $id, $style, $extra);
		$top_side = $this->Row($this->Column($this->Container($this->Row(array(
								$this->Column($this->StaticImageView('./style/' . $this->uistyle . '/images/box_top_left.jpg', 19, 17), null, null, null, 'dialog_top', null, 'background:url(./style/' . $this->uistyle . '/images/box_top_left.jpg) no-repeat;'),
								$this->Column($this->StaticImageView('./style/' . $this->uistyle . '/images/box_top.jpg', 19, 17), '100%', null, null, 'dialog_top', null, 'background:url(./style/' . $this->uistyle . '/images/box_top.jpg);'),
								$this->Column($this->StaticImageView('./style/' . $this->uistyle . '/images/box_top_right.jpg', 19, 17), null, null, null, 'dialog_top', null, 'background:url(./style/' . $this->uistyle . '/images/box_top_right.jpg) no-repeat;'),
							     )), '100%'), null, null, 2));
		$cont->addObjects($top_side);
		if (is_array($to_contain)) {
			foreach ($to_contain as &$obj) {
				$cont->addObjects($this->Row($obj));
			}
		}
		$bottom_side = $this->Row($this->Column($this->Container($this->Row(array(
								$this->Column($this->StaticImageView('./style/' . $this->uistyle . '/images/box_bottom_left.jpg', 19, 35), null, null, null, 'dialog_bottom', null, 'background:url(./style/' . $this->uistyle . '/images/box_bottom_left.jpg) no-repeat;'),
								$this->Column($this->StaticImageView('./style/' . $this->uistyle . '/images/box_bottom.jpg', 19, 35), '100%', null, null, 'dialog_bottom', null, 'background:url(./style/' . $this->uistyle . '/images/box_bottom.jpg);'),
								$this->Column($this->StaticImageView('./style/' . $this->uistyle . '/images/box_bottom_right.jpg', 19, 35), null, null, null, 'dialog_bottom', null, 'background:url(./style/' . $this->uistyle . '/images/box_bottom_right.jpg) no-repeat;'),
							     )), '100%'), null, null, 2));
		$cont->addObjects($bottom_side);
		return $cont;
	}

	public function Container($to_contain, $width = null, $height = null, $cellspacing = 0, $cellpadding = 0, $border = null, $class = null, $id = null, $style = null, $extra = null)
	{
		$obj = new PL_Container($width, $heigth, $cellspacing, $cellpadding, $border, $class, $id, $style, $extra);
		$obj->addObjects($to_contain);
		return $obj;
	}

	public function Column($to_contain, $width = null, $height = null, $colspan = null, $class = null, $id = null, $style = null, $extra = null)
	{
		$obj = new PL_Column($width, $heigth, $colspan, $class, $id, $style, $extra);
		$obj->addObjects($to_contain);
		return $obj;
	}

	public function Header($to_contain, $width = null, $height = null, $colspan = null, $class = null, $id = null, $style = null, $extra = null)
	{
		$obj = new PL_Header($width, $heigth, $colspan, $class, $id, $style, $extra);
		$obj->addObjects($to_contain);
		return $obj;
	}

	public function Row($to_contain, $width = null, $height = null, $rowspan = null, $class = null, $id = null, $style = null, $extra = null)
	{
		$obj = new PL_Row($width, $heigth, $rowspan, $class, $id, $style, $extra);
		$obj->addObjects($to_contain);
		return $obj;
	}

	public function TitleBlock($title, $icon)
	{
	}

	public function ErrorBlock($type, $message)
	{
	}

	public function Form($to_contain, $method, $action, $name, $hidden_fields_names = null, $hidden_fields_values = null)
	{
		$obj = new PL_Form($method, $action, $name, $hidden_fields_names, $hidden_fields_values);
		$obj->addObjects($to_contain);
		return $obj;
	}

	public function Render()
	{
		if (!suppress_headers) {
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');						// Date in the past
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');					// always modified
			header('Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0');	// HTTP/1.1
			header('Pragma: no-cache');									// HTTP/1.0
			header("Content-type: text/html; charset=UTF-8");
		}
		// Check is the user needs a new password
		if (w2PgetParam($_POST, 'lostpass', 0)) {
			$this->outputNewPasswordForm();
			$this->outputHead();
			$this->outputBody();
			$this->outputIncludes();
			echo '<div style="width: ' . $this->total_width . '; margin: 0 auto; overflow: hidden;">';
			$this->outputHTML();
			echo '</div>';
			echo '</body>';
			echo '</html>';
		// Check if we are logged in
		} else if ($this->AppUI->doLogin()) {
			$this->outputLoginForm();
			$this->outputHead();
			$this->outputBody();
			$this->outputIncludes();
			echo '<div style="width: ' . $this->total_width . '; margin: 0 auto; overflow: hidden;">';
			$this->outputHTML();
			echo '</div>';
			echo '</body>';
			echo '</html>';
		} else {
			if (!$this->output_gz) {
				ob_start();
			} else {
				if(!ob_start('ob_gzhandler')) {
					ob_start();
				}
			}
			$this->outputHead();
			$this->outputBody();
			$this->outputIncludes();
			echo '<div style="width: ' . $this->total_width . '; margin: 0 auto; overflow: hidden;">';
			$this->outputSiteID();
			if (!$this->is_dialog) {
				$this->outputMenu();
				$this->outputQuickButtons();
				$this->outputErrorMessage();
			}
			$this->outputHTML();
			echo '</div>';
			echo '</body>';
			echo '</html>';
			ob_end_flush();
		}
	}

}
