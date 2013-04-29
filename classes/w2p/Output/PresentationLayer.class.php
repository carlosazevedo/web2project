class w2p_Presentation_Layer {

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

	public $mobile_version = false;

	private $render_elements = null;

	public function __construct($app_ui = null; $width = null, $user_tz = null, $style = null) 
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

	private funcion outputHead()
	{
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">';
		echo '<head>';
		echo '<meta name="Description" content="' . $this->head_description . '" />';
		echo '<meta name="Version" content="' . $AppUI->getVersion() . '" />';
		echo '<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />';
		echo '<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />';
		echo '<title>' . (is_null($this->dialog_title) ? @w2PgetConfig('page_title') : $this->dialog_title) . ' :: ' . $AppUI->_($this->module . $this->action) . '</title>';
		if ($mobile_version && file_exists('./style/common_mobile.css')) {
			echo '<link rel="stylesheet" type="text/css" href="./style/common_mobile.css" media="all" charset="utf-8"/>';
		} else {
			echo '<link rel="stylesheet" type="text/css" href="./style/common.css" media="all" charset="utf-8"/>';
		}
		if ($mobile_version && file_exists('./style/' . $uistyle . '/common_mobile.css')) {
			echo '<link rel="stylesheet" type="text/css" href="./style/' . $uistyle . '/common_mobile.css" media="all" charset="utf-8"/>';
		} else {
			echo '<link rel="stylesheet" type="text/css" href="./style/' . $uistyle . '/common.css" media="all" charset="utf-8"/>';
		}
		if ($mobile_version && file_exists('./modules/' . $module . '/style_mobile.css')) {
			echo '<link rel="stylesheet" type="text/css" href="./modules/' . $module . '/style_mobile.css" media="all" charset="utf-8"/>';
		} else {
			echo '<link rel="stylesheet" type="text/css" href="./modules/' . $module . '/style.css" media="all" charset="utf-8"/>';
		}
		if ($mobile_version && file_exists('./style/' . $uistyle . '/' . $module . '/style_mobile.css')) {
			echo '<link rel="stylesheet" type="text/css" href="./style/' . $uistyle . '/' . $module . '/style_mobile.css" media="all" charset="utf-8"/>';
		} else {
			echo '<link rel="stylesheet" type="text/css" href="./style/' . $uistyle . '/' . $module . '/style.css" media="all" charset="utf-8"/>';
		}
		echo '<link rel="stylesheet" type="text/css" href="./js/common.js" media="all" charset="utf-8"/>';
		echo '<link rel="stylesheet" type="text/css" href="./modules/' . $module . '/module.js" media="all" charset="utf-8"/>';
		echo '<link rel="shortcut icon" href="./style/' . $uistyle . '/favicon.ico" type="image/ico" />';
		echo '</head>';
	}

	protected function outputSiteID()
	{
		echo '<div class="siteid">';
		echo '<a href="http://www.web2project.net/" target="_new" title="web2Project v. ' . $AppUI->getVersion() . $AppUI->_('click to visit web2Project site') . '">';
		echo '<img src="style/' . $uistyle . '/images/title.jpg" border="0" class="banner" align="left" alt="' . $AppUI->_('click to visit web2Project site') . '" />';
		echo '</a>';
		echo '</div>';
	}

	protected function outputMenu()
	{
		echo '<div class="menu current-' . $module . '">';
		$nav = $AppUI->getMenuModules();
		echo '<ul id="headerNav">';
		foreach ($nav as $mod) {
			if (canAccess($mod['mod_directory'])) {
				echo '<li class="' . $mod['mod_directory'] . '">';
				echo '<a href="?m=' . $mod['mod_directory'] . '" class="' . (($module == $mod['mod_directory']) ? 'module' : '') . '">' . $AppUI->_($mod['mod_ui_name']) . '</a>';
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
		if ($AppUI->user_id > 0) {
			echo $AppUI->_('Welcome') . ' ' . $AppUI->user_display_name;
                        echo '<br />';
			echo $AppUI->_('Server time is') . ' ' . $AppUI->getTZAwareTime();
		}
		echo '</div>';
		if ($AppUI->user_id > 0) {
			echo '<div class="quick_buttons">';
			$help_page = $AppUI->help_pages[$module];
			echo '<div class="help">';
			echo $this->AnchorButton('Help', $help_page, 'help');
			echo '</div>';
			$buttons = $AppUI->quick_buttons;
			foreach ($buttons as $button)	{
				echo $this->AnchorButton($button['text'], $button['href'], $button['class']);
			}
			echo '<div class="quick_actions">';
			$actions = $AppUI->quick_actions;
			echo $this->AnchorDropDown($actions['text'], $actions['href'], $module, 'quick_action');
			echo '</div>';
			echo '<div class="logout">';
			echo $this->AnchorButton('Logout', './index.php?logout=-1', 'logout');
			echo '</div>';
			echo '</div>';
		}
	}

	protected function outputErrorMessage()
	{
		echo '<div id="error-message" title="' . $AppUI->_('Error Message') . '">';
		$err_msg = $AppUI->getMsg();
		if ($err_msg != '') {
			echo $err_msg;
		}
		echo '</div>';
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

	public function LineEdit()
	{
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

	public function Button()
	{
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

	public function ImageView()
	{
	}

	public function LabelView()
	{
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

	public function LabeledBlock()
	{
	}

	public function Block()
	{
	}

	public function outputBody()
	{
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
		outputHead();
		echo '<body>';
		echo '<div style="width: ' . $total_width . '; margin: 0 auto; overflow: hidden;">';
		outputSiteID();
		if (!$is_dialog) {
			outputMenu();
			outputQuickButtons();
			outputErrorMessage();
		}
		outputBody();
		echo '</div>';
		echo '</body>';
	}

}
