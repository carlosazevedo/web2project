<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// Add / Edit forum
$message_parent = (int) w2PgetParam($_GET, 'message_parent', -2);
$message_id = (int) w2PgetParam($_GET, 'message_id', 0);
$forum_id = (int) w2PgetParam($_REQUEST, 'forum_id', 0);

$myForum = new CForum();
$myForum->forum_id = $forum_id;

//Pull forum information
$myForum->load(null, $forum_id);
if (!$myForum) {
	$AppUI->setMsg('Forum');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect('m=forums');
} else {
	$AppUI->savePlace();
}

// Build a back-url for when the back button is pressed
$back_url_params = array();
foreach ($_GET as $k => $v) {
	if ($k != 'post_message') {
		$back_url_params[] = "$k=$v";
	}
}
$back_url = implode('&', $back_url_params);

//pull message information
$message = new CForum_Message();
if ($message_id) {
	$message->load($message_id);
}

$canAddEdit = $message->canAddEdit();
if (!$canAddEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}

$new_topic = false;
if ($message_parent < 0) {
	$message_parent = -1;

	// get the task list if this is a new topic
	if ($myForum->forum_project > 0) {
		$new_topic = true;
		$noTask = new CTask();
		$task_list = $noTask->getAllowedTaskList(null, $myForum->forum_project);

		$level = 0;
		$task_options = array();
		$last_parent = 0;
		foreach ($task_list as $task) {
		        if ($task['task_parent'] != $task['task_id']) {
		      		if ($last_parent != $task['task_parent']) {
		      			$last_parent = $task['task_parent'];
      					$level++;
		      		}
		      	} else {
		      		$last_parent = 0;
      				$level = 0;
		      	}
		      	$task_options[$task['task_id']] = ($level ? str_repeat('&nbsp;&nbsp;', $level) : '') . $task['task_name'];
		}
	}

	// get the watcher list
	$selected_watchers = $message->getWatchers();
}

//pull message information from parent (topic)
if ($message_parent != -1) {
    $last_message = new CForum_Message();
    $last_message->load($message_parent);
    $last_message->message_body = mb_str_replace("\n", "\n> ", $last_message->message_body);
}

?>
<script language="javascript" type="text/javascript">
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canAddEdit) {
?>
function submitIt(){
	var form = document.changeforum;
	if (form.message_title.value.search(/^\s*$/) >= 0 ) {
		alert("<?php echo $AppUI->_('forumSubject', UI_OUTPUT_JS); ?>");
		form.message_title.focus();
	} else if (form.message_body.value.search(/^\s*$/) >= 0) {
		alert("<?php echo $AppUI->_('forumTypeMessage', UI_OUTPUT_JS); ?>");
		form.message_body.focus();
	} else {
		form.submit();
	}
}

function delIt(){
	var form = document.changeforum;
	if (confirm( "<?php echo $AppUI->_('forumDeletePost', UI_OUTPUT_JS); ?>" )) {
		form.del.value="<?php echo $message_id; ?>";
		form.submit();
	}
}
<?php } ?>
function orderByName(x){
	var form = document.changeforum;
	if (x == 'name') {
		form.forum_order_by.value = form.forum_last_name.value + ', ' + form.forum_name.value;
	} else {
		form.forum_order_by.value = form.forum_project.value;
	}
}
</script>
<br />
<?php
if (function_exists('styleRenderBoxTop')) {
	echo styleRenderBoxTop();
}
?>

<script language="javascript" type="text/javascript">

function popWatchers() {
    var selected_watchers_id = document.getElementById('topic_watchers').value;
    var url = './index.php?m=public&a=watcher_selector&dialog=1&call_back=setWatchers&selected_watchers_id='+selected_watchers_id;
    window.open(url,'Watchers','height=600,width=400,resizable,scrollbars=yes');
}

function setWatchers(users_id_string){
    if(!users_id_string) {
	users_id_string = '';
    }
    document.changeforum.topic_watchers.value = users_id_string;
}

</script>

<form name="changeforum" action="?m=forums&forum_id=<?php echo $forum_id; ?>" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_post_aed" />
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="message_forum" value="<?php echo $forum_id; ?>" />
	<input type="hidden" name="message_parent" value="<?php echo $message_parent; ?>" />
	<input type="hidden" name="message_published" value="<?php echo $myForum->forum_moderated ? '1' : '0'; ?>" />
	<input type="hidden" name="message_author" value="<?php echo (isset($message->message_author) && ($message_id || $message_parent < 0)) ? $message->message_author : $AppUI->user_id; ?>" />
	<input type="hidden" name="message_editor" value="<?php echo (isset($message->message_author) && ($message_id || $message_parent < 0)) ? $AppUI->user_id : '0'; ?>" />
	<input type="hidden" name="message_id" value="<?php echo $message_id; ?>" />
	<input type="hidden" name="topic_watchers" id="topic_watchers" value="<?php echo implode(',', $selected_watchers); ?>" />
    <table cellspacing="0" cellpadding="3" border="0" width="100%" class="std addedit">
        <tr><td colspan="3">
            <table cellspacing="1" cellpadding="2" border="0" width="100%">
            <tr>
                <td align="left" nowrap="nowrap">
		    <?php
                    $titleBlock = new w2p_Theme_TitleBlock('', '', $m, "$m.$a");
                    $titleBlock->addCrumb('?m=forums', 'forums list');
                    $titleBlock->addCrumb('?m=forums&a=viewer&forum_id=' . $forum_id, 'topics for this forum');
		    if ($message_parent > -1) {
	                $titleBlock->addCrumb('?m=forums&a=viewer&forum_id=' . $forum_id . '&message_id=' . $message_parent, 'this topic');
		    }
                    $titleBlock->show();
		    ?>
                </td>
            </tr>
            </table>
        </td></tr>
        <tr>
            <th valign="top" colspan="3"><strong><?php
        echo $AppUI->_($message_id ? 'Edit Message' : 'Add Message');
        ?></strong></th>
        </tr>
        <?php
        if ($message_parent != -1) { //check if this is a reply-post; if so, printout the original message
            $messageAuthor = isset($last_message->message_author) ? $last_message->message_author : $AppUI->user_id;
            $date = (int)($last_message->message_date) ? new w2p_Utilities_Date($last_message->message_date) : new w2p_Utilities_Date();
            ?>
            <tr>
                <td align="right"><?php echo $AppUI->_('Author') ?>:</td>
                <td align="left"><?php echo CContact::getContactByUserid($messageAuthor); ?> (<?php echo $AppUI->formatTZAwareTime($date->format('%Y-%m-%d %H:%M:%S'), $df . ' ' . $tf); ?>)</td><td width="100%">&nbsp;</td>
            </tr>
            <tr><td align="right"><?php echo $AppUI->_('Subject') ?>:</td><td align="left"><?php echo $last_message->message_title ?></td><td width="100%">&nbsp;</td></tr>
            <tr><td align="right" valign="top"><?php echo $AppUI->_('Message') ?>:</td><td align="left">
            <?php
                $messageBody = $bbparser->qparse($last_message->message_body);
                $messageBody = nl2br($messageBody);
                echo $messageBody;
            ?></td><td width="100%">&nbsp;</td></tr>
            <tr><td colspan="3" align="left"><hr /></td></tr>
            <?php
        } //end of if-condition

        ?>
	<?php if (($new_topic || $message_parent == -1) && $myForum->forum_project) { ?>
	    <tr>
       		<td align="right"><?php echo $AppUI->_('Related task'); ?>:</td>
       		<td><?php echo arraySelect($task_options, 'message_task', 'size="1" class="text"', $message->message_task); ?></td>
	        <td width="100%">&nbsp;</td>
	    </tr>
	<?php } ?>

        <tr>
            <td align="right"><?php echo $AppUI->_('Subject'); ?>:</td>
            <td><input type="text" class="text" name="message_title" value="<?php echo ($message_id || $message_parent == -1 ?  $message->message_title : 'Re: ' . $last_message->message_title); ?>" size="120" maxlength="250" /></td>
	    <td width="100%">
	    <?php
                    if ($AppUI->isActiveModule('projects') && canAdd('projects')) {
			echo '<input type="button" class="button" value="' . $AppUI->_('Select watchers...') . '" onclick="javascript:popWatchers();" />';
                    } else {
		        echo '&nbsp;';
		    }
	    ?>
	    </td>
        </tr>
        <tr>
            <td align="right" valign="top"><?php echo $AppUI->_('Message'); ?>:</td>
            <td align="left" valign="top">
               <textarea cols="101" name="message_body" style="height:200px"><?php echo (($message_id == 0) && ($message_parent != -1)) ? "\n>" . $last_message->message_body . "\n\n" : $message->message_body; ?></textarea>
            </td><td width="100%">&nbsp;</td>
        </tr>
        <tr>
            <td>
            </td>
            <td align="left">
                <small><b><?php echo $AppUI->_('BBCode Ready');?>!</b></small>
                <?php echo w2PshowImage('log-info.gif','','','BBCode Tags Accepted','
                [b][/b] Bold. Example: [b]<b>This text will be bold</b>[/b]<br />
                [i][/i] Italic. Example: [i]<i>This text will be in italic</i>[/i]<br />
                [u][/u] Underlined. Example: [u]<u>This text will be underlined</u>[/u]<br />
                [s][/s] Scratched. Example: [s]<del>This text will be scratched</del>[/s]<br />
                [sub][/sub] Subscript. Example: [sub]<sub>This text will be subscript</sub>[/sub]<br />
                [sup][/sup] Superscript. Example: [sup]<sup>This text will be superscript</sup>[/sup]<br />
                [email][/email] Email Address. Example: [email]my@mail.net[/email]<br />
                [color=color_name][/color] Colorized Text. Example: [color=blue]I am Blue[/color]<br />
                [size=size_value][/size], [font=font_name][/font] and [align=left|center|right][align] Format Text. Example: [align=right]I am on the Right[/align]<br />
                [url=url_address][/url] Link. Example: [url=http://web2project.net]web2Project[/url]<br />
                [list][/list],[ulist][/ulist] and [li][/li] Lists.<br />
                [quote][/quote] Quoted Text. Example: [quote]<q>This text will be superscript</q>[/quote]<br />
                [code][/code] Text in code format. Example: [code]//This is a code comment;[/code]<br />
                '); ?>
            </td><td width="100%">&nbsp;</td>
        </tr>
        <tr>
            <td>
                <input type="button" value="<?php echo $AppUI->_('back'); ?>" class="button" onclick="javascript:window.location='./index.php?<?php echo $back_url; ?>';" />
            </td><td width="100%">&nbsp;</td>
            <td align="right"><?php
            echo '<input type="button" value="' . $AppUI->_('submit') . '" class=button onclick="submitIt()">';
        ?></td>
        </tr>
    </table>
</form>