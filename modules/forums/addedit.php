<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$forum_id = (int) w2PgetParam($_GET, 'forum_id', 0);

$forum = new CForum();
$forum->forum_id = $forum_id;

$obj = $AppUI->restoreObject();
if ($obj) {
    $forum = $obj;
    $forum_id = $forum->forum_id;
} else {
    $forum->load(null, $forum_id);
}
if (!$forum && $forum_id > 0) {
    $AppUI->setMsg('Forum');
    $AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
    $AppUI->redirect();
}

$canAddEdit = $forum->canAddEdit();
$canAuthor = $forum->canCreate();
$canEdit = $forum->canEdit();
if (!$canAddEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}

$status = isset($forum->forum_status) ? $forum->forum_status : -1;

$prj = new CProject();
if ($forum_id) {
    $projects = $prj->getAllowedProjects($AppUI->user_id, false);
} else {
    $projects = $prj->getAllowedProjects($AppUI->user_id, true);
}
foreach ($projects as $project_id => $project_info) {
	$projects[$project_id] = $project_info['project_name'];
}
$projects = arrayMerge(array(0 => $AppUI->_('All Projects')), $projects);

// check permissions for this record
$perms = &$AppUI->acl();
$users = $perms->getPermittedUsers('forums');

// setup the title block
$ttl = $forum_id > 0 ? 'Edit Forum' : 'Add Forum';
$titleBlock = new w2p_Theme_TitleBlock($ttl, 'support.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=forums', 'forums list');
if ($canDelete && ($forum_id > 0)) {
    $titleBlock->addCrumbDelete('delete forum', $canDelete);
}
$titleBlock->show();

// get the watcher list
$selected_watchers = $forum->getWatchers();

?>
<script language="javascript" type="text/javascript">
function submitIt(){
	var form = document.changeforum;
	if(form.forum_name.value.search(/^\s*$/) >= 0 ) {
		alert("<?php echo $AppUI->_('forumName', UI_OUTPUT_JS); ?>");
		form.forum_name.focus();
	} else if(form.forum_owner.value < 1) {
		alert("<?php echo $AppUI->_('forumSelectOwner', UI_OUTPUT_JS); ?>");
		form.forum_owner.focus();
	} else {
		form.submit();
	}
}
<?php
if ($canDelete && ($forum_id > 0)) {
?>
function delIt(){
	var form = document.changeforum;
	if (confirm( "<?php echo $AppUI->_('forumDeleteForum', UI_OUTPUT_JS); ?>" )) {
		form.del.value="<?php echo $forum_id; ?>";
		form.submit();
	}
}
<?php
}
?>
</script>

<script language="javascript" type="text/javascript">

function popWatchers() {
    var selected_watchers_id = document.getElementById('forum_watchers').value;
    var url = './index.php?m=public&a=watcher_selector&dialog=1&call_back=setWatchers&selected_watchers_id='+selected_watchers_id;
    window.open(url,'Watchers','height=600,width=400,resizable,scrollbars=yes');
}

function setWatchers(users_id_string){
    if(!users_id_string) {
	users_id_string = '';
    }
    document.changeforum.forum_watchers.value = users_id_string;
}

</script>

<form name="changeforum" action="?m=forums" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_forum_aed" />
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="forum_unique_update" value="<?php echo uniqid(''); ?>" />
	<input type="hidden" name="forum_id" id="forum_id" value="<?php echo $forum_id; ?>" />
	<input type="hidden" name="forum_watchers" id="forum_watchers" value="<?php echo implode(',', $selected_watchers); ?>" />
    <table cellspacing="0" cellpadding="4" border="0" width="100%" class="std addedit">
        <tr>
            <th valign="top" colspan="3">
                <strong><?php echo $AppUI->_($forum_id ? 'Edit' : 'Add') . ' ' . $AppUI->_('Forum'); ?></strong>
            </th>
        </tr>
        <tr>
            <td valign="top" width="50%">
                <strong><?php echo $AppUI->_('Details'); ?></strong>
                <table cellspacing="1" cellpadding="2" width="100%">
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Forum Name'); ?>:</td>
                    <td>
                        <input type="text" class="text" size="25" name="forum_name" id="forum_name" value="<?php echo $forum->forum_name; ?>" maxlength="50" style="width:200px;" />
                    </td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Related Project'); ?>:</td>
                    <td>
                        <?php echo arraySelect($projects, 'forum_project', 'size="1" class="text"', $forum->forum_project); ?>
                    </td>
                </tr>
                <tr>
                    <td align="right"><?php echo $AppUI->_('Owner'); ?>:</td>
                    <td>
                        <?php echo arraySelect($users, 'forum_owner', 'size="1" class="text"', $forum->forum_owner ? $forum->forum_owner : $AppUI->user_id); ?>
                    </td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Moderator'); ?>:</td>
                    <td>
                        <?php echo arraySelect($users, 'forum_moderated', 'size="1" class="text"', $forum->forum_moderated); ?>
                    </td>
                </tr>
                <?php if ($forum_id) { ?>
                <tr>
                    <td align="right"><?php echo $AppUI->_('Created On'); ?></td>
                    <td bgcolor="#ffffff">
                        <?php
                            echo $AppUI->formatTZAwareTime($forum->forum_create_date);
                        ?>
                    </td>
                </tr>
                <tr>
                    <td align="right"><?php echo $AppUI->_('Last Post'); ?>:</td>
                    <td bgcolor="#ffffff"><?php echo $forum->forum_last_date; ?></td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Message Count'); ?>:</td>
                    <td bgcolor="#ffffff"><?php echo $forum->forum_message_count; ?></td>
                </tr>
                <?php } ?>
                </table>
            </td>
            <td valign="top" width="50%">
                <strong><?php echo $AppUI->_('Description'); ?></strong><br />
                <textarea class="textarea" cols="50" rows="7" name="forum_description"><?php echo $forum->forum_description; ?></textarea>
		<?php
                    if ($AppUI->isActiveModule('projects') && canAdd('projects')) {
			echo '<br><br><input type="button" class="button" value="' . $AppUI->_('Select watchers...') . '" onclick="javascript:popWatchers();" />';
                    }
		?>
            </td>
        </tr>
        <tr>
            <td align="left">
                <input type="button" value="<?php echo $AppUI->_('back'); ?>" class="button" onclick="javascript:window.location='./index.php?m=forums';" />
            </td>
            <td align="right" colspan="2">
                <?php if ($forum->canAddEdit()) {
                    echo '<input type="button" value="' . $AppUI->_('submit') . '" class=button onclick="submitIt()" />';
                } ?>
            </td>
        </tr>
    </table>
</form>