<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $m, $a, $project_id, $f, $min_view, $query_string, $durnTypes;
global $task_sort_item1, $task_sort_type1, $task_sort_order1;
global $task_sort_item2, $task_sort_type2, $task_sort_order2;
global $user_id, $w2Pconfig, $currentTabId, $currentTabName, $canEdit, $showEditCheckbox;
global $history_active;

/*
tasks.php

This file contains common task list rendering code used by
modules/tasks/index.php and modules/projects/vw_tasks.php

in

External used variables:
* $min_view: hide some elements when active (used in the vw_tasks.php)
* $project_id
* $f
* $query_string
*/

if (empty($query_string)) {
	$query_string = '?m=' . $m . '&amp;a=' . $a;
}
$mods = $AppUI->getActiveModules();
$history_active = !empty($mods['history']) && canView('history');

/*
* Let's figure out which tasks are selected
*/
$task_id = (int) w2PgetParam($_GET, 'task_id', 0);

$q = new w2p_Database_Query;
$pinned_only = (int) w2PgetParam($_GET, 'pinned', 0);
if (isset($_GET['pin'])) {
	$pin = (int) w2PgetParam($_GET, 'pin', 0);
	$msg = '';

	// load the record data
	if ($pin) {
		$msg = CTask::pinUserTask($AppUI->user_id, $task_id);
	} else {
		$msg = CTask::unpinUserTask($AppUI->user_id, $task_id);
	}

	if (!$msg) {
		$AppUI->setMsg($msg, UI_MSG_ERROR, true);
	}
	$AppUI->redirect('', -1);
}

$durnTypes = w2PgetSysVal('TaskDurationType');
$taskPriority = w2PgetSysVal('TaskPriority');

$task_project = (int) w2PgetParam($_GET, 'task_project', null);

$task_sort_item1 = w2PgetParam($_GET, 'task_sort_item1', 'task_start_date');
$task_sort_type1 = w2PgetParam($_GET, 'task_sort_type1', '');
$task_sort_item2 = w2PgetParam($_GET, 'task_sort_item2', 'task_end_date');
$task_sort_type2 = w2PgetParam($_GET, 'task_sort_type2', '');
$task_sort_order1 = (int) w2PgetParam($_GET, 'task_sort_order1', 0);
$task_sort_order2 = (int) w2PgetParam($_GET, 'task_sort_order2', 0);
if (isset($_POST['show_task_options'])) {
	$AppUI->setState('TaskListShowIncomplete', w2PgetParam($_POST, 'show_incomplete', 0));
}
$showIncomplete = $AppUI->getState('TaskListShowIncomplete', 0);

$project = new CProject;
$allowedProjects = $project->getAllowedSQL($AppUI->user_id, 'p.project_id');

$where_list = (count($allowedProjects)) ? implode(' AND ', $allowedProjects) : '';

$working_hours = ($w2Pconfig['daily_working_hours'] ? $w2Pconfig['daily_working_hours'] : 8);

$q = new w2p_Database_Query;
$q->addTable('projects', 'p');
$q->addQuery('company_name, p.project_id, project_color_identifier, project_name, project_percent_complete');
$q->addJoin('companies', 'com', 'company_id = project_company', 'inner');
$q->addJoin('tasks', 't1', 'p.project_id = t1.task_project', 'inner');
$q->leftJoin('project_departments', 'project_departments', 'p.project_id = project_departments.project_id OR project_departments.project_id IS NULL');
$q->leftJoin('departments', 'departments', 'departments.dept_id = project_departments.department_id OR dept_id IS NULL');
$q->addWhere($where_list . (($where_list) ? ' AND ' : '') . 't1.task_id = t1.task_parent');
$q->addGroup('p.project_id');
if (!$project_id && !$task_id) {
	$q->addOrder('project_name');
}
if ($project_id > 0) {
	$q->addWhere('p.project_id = '.$project_id);
}

$q2 = new w2p_Database_Query;
$q2->addTable('projects');
$q2->addQuery('project_id, COUNT(t1.task_id) AS total_tasks');
$q2->addJoin('tasks', 't1', 'projects.project_id = t1.task_project', 'inner');
if ($where_list) {
	$q2->addWhere($where_list);
}
if ($project_id > 0) {
	$q2->addWhere('project_id = '.$project_id);
}
$q2->addGroup('project_id');

$perms = &$AppUI->acl();
$projects = array();
$canViewTask = canView('tasks');;
if ($canViewTask) {

	$prc = $q->exec();
	echo db_error();
	while ($row = $q->fetchRow()) {
		$projects[$row['project_id']] = $row;
	}

	$prc2 = $q2->fetchRow();
	echo db_error();
	while ($row2 = $q2->fetchRow()) {
		if ($projects[$row2['project_id']]) {
			array_push($projects[$row2['project_id']], $row2);
		}
	}
}
$q->clear();
$q2->clear();

$q->addQuery('tasks.task_id, task_parent, task_name');
$q->addQuery('task_start_date, task_end_date, task_dynamic');
$q->addQuery('task_pinned, pin.user_id as pin_user');
$q->addQuery('ut.user_task_priority');
$q->addQuery('task_priority, task_percent_complete');
$q->addQuery('task_duration, task_duration_type');
$q->addQuery('task_project, task_represents_project');
$q->addQuery('task_description, task_owner, task_status');
$q->addQuery('usernames.user_username, usernames.user_id');
$q->addQuery('assignees.user_username as assignee_username');
$q->addQuery('count(distinct assignees.user_id) as assignee_count');
$q->addQuery('co.contact_first_name, co.contact_last_name');
$q->addQuery('contact_display_name AS contact_name');
$q->addQuery('contact_display_name AS owner');
$q->addQuery('task_milestone');
$q->addQuery('count(distinct f.file_task) as file_count');
$q->addQuery('tlog.task_log_problem');
$q->addQuery('task_access');

//subquery the parent state
$sq = new w2p_Database_Query;
$sq->addTable('tasks', 'stasks');
$sq->addQuery('COUNT(stasks.task_id)');
$sq->addWhere('stasks.task_id <> tasks.task_id AND stasks.task_parent = tasks.task_id');
$subquery = $sq->prepare();
$sq->clear();

$q->addQuery('(' . $subquery . ') AS task_nr_of_children');

$q->addTable('tasks');

if ($history_active) {
	$q->addQuery('MAX(history_date) as last_update');
	$q->leftJoin('history', 'h', 'history_item = tasks.task_id AND history_table=\'tasks\'');
}

$q->addJoin('projects', 'p', 'p.project_id = task_project', 'inner');
$q->leftJoin('users', 'usernames', 'task_owner = usernames.user_id');
$q->leftJoin('user_tasks', 'ut', 'ut.task_id = tasks.task_id');
$q->leftJoin('users', 'assignees', 'assignees.user_id = ut.user_id');
$q->leftJoin('contacts', 'co', 'co.contact_id = usernames.user_contact');
$q->leftJoin('task_log', 'tlog', 'tlog.task_log_task = tasks.task_id AND tlog.task_log_problem > 0');
$q->leftJoin('files', 'f', 'tasks.task_id = f.file_task');
$q->leftJoin('project_departments', 'project_departments', 'p.project_id = project_departments.project_id OR project_departments.project_id IS NULL');
$q->leftJoin('departments', 'departments', 'departments.dept_id = project_departments.department_id OR dept_id IS NULL');
$q->leftJoin('user_task_pin', 'pin', 'tasks.task_id = pin.task_id AND pin.user_id = ' . (int)$AppUI->user_id);

if ($project_id) {
	$q->addWhere('task_project = ' . (int)$project_id);
	//if we are on a project context make sure we show all tasks
	$f = 'all';
} else { 
	$q->addWhere('project_active = 1');
}

if ($task_id) {
	//if we are on a task context make sure we show ALL the children tasks
	$f = 'deepchildren';
}
if ($pinned_only) {
	$q->addWhere('task_pinned = 1');
}

$f = (($f) ? $f : '');
switch ($f) {
	case 'all':
		break;
	case 'myfinished7days':
		$q->addWhere('ut.user_id = ' . (int)$user_id);
	case 'allfinished7days': // patch 2.12.04 tasks finished in the last 7 days
		//$q->addTable('user_tasks');
		$q->addTable('user_tasks');
		$q->addWhere('user_tasks.user_id = ' . (int)$user_id);
		$q->addWhere('user_tasks.task_id = tasks.task_id');

		$q->addWhere('task_percent_complete = 100');
		//TODO: use date class to construct date.
		$q->addWhere('task_end_date >= \'' . date('Y-m-d 00:00:00', mktime(0, 0, 0, date('m'), date('d') - 7, date('Y'))) . '\'');
		break;
	case 'children':
		$q->addWhere('task_parent = ' . (int)$task_id);
		$q->addWhere('tasks.task_id <> ' . $task_id);
		break;
	case 'deepchildren':
		$taskobj = new CTask;
		$taskobj->load((int)$task_id);
		$deepchildren = $taskobj->getDeepChildren();
		$q->addWhere('tasks.task_id IN (' . implode(',', $deepchildren) . ')');
		$q->addWhere('tasks.task_id <> ' . $task_id);
		break;
	case 'myproj':
		$q->addWhere('project_owner = ' . (int)$user_id);
		break;
	case 'mycomp':
		if (!$AppUI->user_company) {
			$AppUI->user_company = 0;
		}
		$q->addWhere('project_company = ' . (int)$AppUI->user_company);
		break;
	case 'myunfinished':
		$q->addTable('user_tasks');
		$q->addWhere('user_tasks.user_id = ' . (int)$user_id);
		$q->addWhere('user_tasks.task_id = tasks.task_id');
		$q->addWhere('(task_percent_complete < 100 OR task_end_date = \'\')');
		break;
	case 'allunfinished':
		$q->addWhere('(task_percent_complete < 100 OR task_end_date = \'\')');
		break;
	case 'unassigned':
		$q->leftJoin('user_tasks', 'ut_empty', 'tasks.task_id = ut_empty.task_id');
		$q->addWhere('ut_empty.task_id IS NULL');
		break;
	case 'taskcreated':
		$q->addWhere('task_creator = ' . (int)$user_id);
		break;
	case 'taskowned':
		$q->addWhere('task_owner = ' . (int)$user_id);
		break;
	default:
		$q->addTable('user_tasks');
		$q->addWhere('user_tasks.user_id = ' . (int)$user_id);
		$q->addWhere('user_tasks.task_id = tasks.task_id');
		break;
}

if ($showIncomplete) {
	$q->addWhere('( task_percent_complete < 100 OR task_percent_complete IS NULL)');
}

//TODO: This whole structure is hard-coded based on the TaskStatus SelectList.
$task_status = 0;
if ($min_view && isset($_GET['task_status'])) {
	$task_status = (int) w2PgetParam($_GET, 'task_status', null);
} elseif ($currentTabId == 1 && $project_id) {
	$task_status = -1;
} elseif ($currentTabId > 1 && $project_id) {
	$task_status = $currentTabId-1;
} elseif (!$currentTabName) {
	// If we aren't tabbed we are in the tasks list.
	$task_status = (int) $AppUI->getState('inactive');
}

//When in task view context show all the tasks, active and inactive. (by not limiting the query by task status)
//When in a project view or in the tasks list, show the active or the inactive tasks depending on the selected tab or button.
if (!$task_id) {
	$q->addWhere('task_status = ' . (int)$task_status);
}
if (isset($task_type) && (int) $task_type > 0) {
	$q->addWhere('task_type = ' . (int)$task_type);
}
if (isset($task_owner) && (int) $task_owner > 0) {
	$q->addWhere('task_owner = ' . (int)$task_owner);
}

if (($project_id || !$task_id) && !$min_view) {
	if ($search_text = $AppUI->getState('searchtext')) {
		$q->addWhere('( task_name LIKE (\'%' . $search_text . '%\') OR task_description LIKE (\'%' . $search_text . '%\') )');
	}
}

// filter tasks considering task and project permissions
$projects_filter = '';
$tasks_filter = '';

// TODO: Enable tasks filtering
$allowedProjects = $project->getAllowedSQL($AppUI->user_id, 'task_project');
if (count($allowedProjects)) {
	$q->addWhere($allowedProjects);
}

$obj = new CTask;
$allowedTasks = $obj->getAllowedSQL($AppUI->user_id, 'tasks.task_id');
if (count($allowedTasks)) {
	$q->addWhere($allowedTasks);
}

// Filter by company
if (!$min_view && $f2 != 'allcompanies') {
	$q->addJoin('companies', 'c', 'c.company_id = p.project_company', 'inner');
	$q->addWhere('company_id = ' . (int) $f2);
}

$q->addGroup('tasks.task_id');
if (!$project_id && !$task_id) {
	$q->addOrder('p.project_id, task_start_date, task_end_date');
} else {
	$q->addOrder('task_start_date, task_end_date');
}
if ($canViewTask) {
	$tasks = $q->loadList();
}

// Recursive function for marking troubled parents, used below
function markParent($task_id, &$worried_parents, &$tasks_by_id) {
	$task = $tasks_by_id[$task_id];
	if ($task['task_dynamic'] == 1) {
		$worried_parents[$task_id] = true;
	}
	if ($task['task_parent'] != $task_id) {
		markParent($task['task_parent'], $worried_parents, $tasks_by_id);
	}
}


// POST PROCESSING TASKS
if (count($tasks) > 0) {
	// Activate the 'task_log_problem' field for dynamic parents with problematic children
	// The code will move up the tree to find all dynamic parents
	// (1) Create an array of rows, indexed by task_id
	$tasks_by_id = array();
	foreach ($tasks as $row) {
		$tasks_by_id[$row['task_id']] = $row;
	}
	// (2) Scan the indexed array, marking up the tree any dynamic tasks with troubled children at any sub-level.
	//     The result is an array of worried parents
	$worried_parents = array();
	foreach ($tasks_by_id as $row) {
		if ($row['task_log_problem'] > 0) {
			if ($row['task_parent'] != $row['task_id']) {
				markParent($row['task_parent'], $worried_parents, $tasks_by_id);
			}
		}
	}
	foreach ($tasks as $row) {
		//add information about assigned users into the page output
		$q->clear();
		$q->addQuery('ut.user_id,	u.user_username');
		$q->addQuery('ut.perc_assignment');
		$q->addQuery('contact_display_name AS assignee, contact_email');
		$q->addTable('user_tasks', 'ut');
		$q->addJoin('users', 'u', 'u.user_id = ut.user_id', 'inner');
		$q->addJoin('contacts', 'c', 'u.user_contact = c.contact_id', 'inner');
		$q->addWhere('ut.task_id = ' . (int)$row['task_id']);
		$q->addOrder('perc_assignment desc, contact_first_name, contact_last_name');

		$assigned_users = array();
		$row['task_assigned_users'] = $q->loadList();
	
		// (2) Now mark then as such. Needs to be done in two steps because the parents may come after the children
		if (array_key_exists($row['task_id'], $worried_parents)) {
			$row['task_log_problem'] = 1;
		}
		//pull the final task row into array
		$projects[$row['task_project']]['tasks'][] = $row;
	}
}

// If in minimal view the rows cannot be edited
$showEditCheckbox = ((isset($canEdit) && $canEdit && w2PgetConfig('direct_edit_assignment') && !$min_view) ? true : false);
?>

<script language="javascript" type="text/javascript">
function toggle_users(id){
  var element = document.getElementById(id);
  element.style.display = (element.style.display == '' || element.style.display == "none") ? "inline" : "none";
}

<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($showEditCheckbox) { ?>
	function checkAll(project_id) {
		var f = eval('document.assFrm' + project_id);
		var cFlag = f.master.checked ? false : true;
		
		for (var i=0, i_cmp=f.elements.length; i<i_cmp;i++) {
			var e = f.elements[i];
			// only if it's a checkbox.
			if(e.type == 'checkbox' && e.checked == cFlag && e.name != 'master') {
				e.checked = !e.checked;
			}
		}
	
	}
	
	function chAssignment(project_id, rmUser, del) {
		var f = eval('document.assFrm' + project_id);
	        var c = 0;
	        var a = 0;
	
	        f.task_user_assign.value = '';

	        // harvest all checked checkboxes (tasks to process)
	        for (var i=0, i_cmp=f.elements.length; i < i_cmp; i++) {
	                var el1 = f.elements[i];
	                // only if it's a checkbox.
	                if(el1.type == 'checkbox' && el1.checked == true && el1.name != 'master')
	                {
        	                c++;
				// now search for the corresponding 'select-multiple' with the name 'add_users[<project_id>]'
				var el2 = document.getElementsByName('add_users[' + project_id + ']')[0];
				var users = new Array();
			        // harvest all selected possible User Assignees
				for (var k=0, k_cmp=el2.options.length; k < k_cmp; k++) {
					if (el2.options[k].selected) {
						a++;
			                        users.push(el2.options[k].value);
					}
				}
				if (users.length > 0) {
					f.task_user_assign.value = f.task_user_assign.value + '|' + el1.value + ':' + users.join(',');
				}
			}
		}

	        if (del == true) {
	                if (c == 0) {
	                        alert ('<?php echo $AppUI->_('Please select at least one Task!', UI_OUTPUT_JS); ?>');
	                } else {
	                        if (confirm( '<?php echo $AppUI->_('Are you sure you want to unassign the User from Task(s)?', UI_OUTPUT_JS); ?>' )) {
					f.del.value = 1;
					f.rm.value = rmUser;
					f.project_id.value = project_id;
					f.submit();
				}
	                }
	        } else {
			if (c == 0) {
				alert ('<?php echo $AppUI->_('Please select at least one Task!', UI_OUTPUT_JS); ?>');
			} else if (a == 0) {
				alert ('<?php echo $AppUI->_('Please select at least one Assignee!', UI_OUTPUT_JS); ?>');
			} else {
				f.rm.value = rmUser;
				f.del.value = del;
				f.project_id.value = project_id;
				f.submit();
			}
	        }
	}
<?php } ?>
</script>

<?php 
global $expanded;
//if we are on a task view context then all subtasks are expanded by default, on other contexts config option stands.
$expanded = $task_id ? true : $AppUI->getPref('TASKSEXPANDED');
if ($project_id) {
$open_link = w2PtoolTip($m, 'click to expand/collapse all the tasks for this project.') . '<a href="javascript: void(0);"><img onclick="expand_collapse(\'project_' . $project_id . '_\', \'tblProjects\',\'collapse\',0,2);" id="project_' . $project_id . '__collapse" src="' . w2PfindImage('up22.png', $m) . '" border="0" width="22" height="22" align="center" ' . (!$expanded ? 'style="display:none"' : '') . ' alt="" /><img onclick="expand_collapse(\'project_' . $project_id . '_\', \'tblProjects\',\'expand\',0,2);" id="project_' . $project_id . '__expand" src="' . w2PfindImage('down22.png', $m) . '" border="0" width="22" height="22" align="center" ' . ($expanded ? 'style="display:none"' : '') . ' alt="" /></a>' . w2PendTip();
?>
<form name="task_list_options" method="post" action="<?php echo $query_string; ?>" accept-charset="utf-8">
    <input type='hidden' name='show_task_options' value='1' />
    <table width='100%' border='0' cellpadding='1' cellspacing='0'>
        <tr>
          <td align='left'>
                <?php echo $open_link; ?>
          </td>
          <td align='right'>
                <table>
                    <tr>
                      <td><?php echo $AppUI->_('Show'); ?>:</td>
                      <td>
                      <input type="checkbox" name="show_incomplete" id="show_incomplete" onclick="document.task_list_options.submit();"
                       <?php echo $showIncomplete ? 'checked="checked"' : ''; ?> />
                      </td>
                      <td><label for="show_incomplete"><?php echo $AppUI->_('Incomplete Tasks Only'); ?></label></td>
                    </tr>
                </table>
          </td>
        </tr>
    </table>
</form>
<?php }

$fieldList = array();
$fieldNames = array();

$module = new w2p_Core_Module();
$fields = $module->loadSettings('tasks', 'index_list');

if (count($fields) > 0) {
    $fieldList = array_keys($fields);
    $fieldNames = array_values($fields);
} else {
    // TODO: This is only in place to provide an pre-upgrade-safe
    //   state for versions earlier than v3.0
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('task_percent_complete', 'task_priority', 'user_task_priority',
        'task_name', 'user_username', '', 'task_start_date',
        'task_duration', 'task_end_date');
    $fieldNames = array('Work', 'P', 'U', 'Task Name', 'Task Owner',
        'Assigned Users', 'Start Date', 'Duration', 'Finish Date');

    $module->storeSettings('tasks', 'index_list', $fieldList, $fieldNames);
}
if ($history_active) {
    $fieldList[] = 'last_update';
    $fieldNames[] = 'Last Update';
}
if ($showEditCheckbox) {
    $fieldList[] = '';
    $fieldNames[] = '';
}
?>
<table id="tblProjects" class="tbl list">
    <tr>
        <?php
        echo '<th></th><th></th><th></th>';
        foreach ($fieldNames as $index => $name) {
            ?><th nowrap="nowrap">
<!--                <a href="?m=files&orderby=<?php echo $fieldList[$index]; ?>" class="hdr">-->
                    <?php echo $AppUI->_($fieldNames[$index]); ?>
<!--                </a>-->
            </th><?php
        }

        // Number of columns (used to calculate how many columns to span things through)
        $cols = count($fieldNames) + 3;

        ?>
    </tr>
	<?php
		reset($projects);
		
		if ($showEditCheckbox) {
			// get Users with all Allocation info (e.g. their freeCapacity)
			// but do it only when direct_edit_assignment is on and only once.
			$tempoTask = new CTask();
			$userAlloc = $tempoTask->getAllocation('user_id', null, true);
		}
		foreach ($projects as $k => $p) {
			$tnums = (isset($p['tasks'])) ? count($p['tasks']) : 0;
			if ($tnums > 0 || $project_id == $p['project_id']) {
				//echo '<pre>'; print_r($p); echo '</pre>';
				if (!$min_view) {
					// not minimal view
					$open_link = w2PtoolTip($m, 'Click to Expand/Collapse the Tasks for this Project.') . '<a href="javascript: void(0);"><img onclick="expand_collapse(\'project_' . $p['project_id'] . '_\', \'tblProjects\',\'collapse\',0,2);" id="project_' . $p['project_id'] . '__collapse" src="' . w2PfindImage('up22.png', $m) . '" border="0" width="22" height="22" align="center" ' . (!$expanded ? 'style="display:none"' : '') . ' alt="" /><img onclick="expand_collapse(\'project_' . $p['project_id'] . '_\', \'tblProjects\',\'expand\',0,2);" id="project_' . $p['project_id'] . '__expand" src="' . w2PfindImage('down22.png', $m) . '" border="0" width="22" height="22" align="center" ' . ($expanded ? 'style="display:none"' : '') . ' alt="" /></a>' . w2PendTip();
					?>
					<tr>
					  <td colspan="<?php echo $cols; ?>">
							<form name="assFrm<?php echo ($p['project_id']) ?>" action="index.php?m=<?php echo ($m); ?>&amp;=<?php echo ($a); ?>" method="post" accept-charset="utf-8">
							<input type="hidden" name="del" value="1" />
							<input type="hidden" name="rm" value="0" />
							<input type="hidden" name="store" value="0" />
							<input type="hidden" name="dosql" value="do_task_assign_aed" />
							<input type="hidden" name="project_id" value="<?php echo ($p['project_id']); ?>" />
							<input type="hidden" name="task_user_assign" />
						</td>
					</tr>
					<tr>
					  <td>
					   <?php echo $open_link; ?>
					  </td>
					  <td colspan="<?php echo ($showEditCheckbox) ? $cols - 3 : $cols; ?>">
						  <table width="100%" border="0">
							  <tr>
									<!-- patch 2.12.04 display company name next to project name -->
									<td nowrap="nowrap" style="border: outset #eeeeee 1px;background-color:#<?php echo $p['project_color_identifier']; ?>">
										<a href="./index.php?m=projects&amp;a=view&amp;project_id=<?php echo $k; ?>">
											<span style="color:<?php echo bestColor($p['project_color_identifier']); ?>;text-decoration:none;">
											<strong><?php echo $p['company_name'] . ' :: ' . $p['project_name']; ?></strong></span>
										</a>
									</td>
									<td width="<?php echo (101 - (int) $p['project_percent_complete']); ?>%">
										<?php echo (int) $p['project_percent_complete']; ?>%
									</td>
							  </tr>
						  </table>
					  </td>
						<?php
							if ($showEditCheckbox) {
								?>
							  <td colspan="3" align="right" valign="middle">
								  <table width="100%" border="0">
									  <tr>
											<td align="right">
												<select name="add_users[<?php echo $p['project_id'] ?>]" style="width:200px" size="3" multiple="multiple" class="text" ondblclick="javascript:chAssignment(<?php echo ($p['project_id']); ?>, 0, false)">
													<?php
															foreach ($userAlloc as $v => $u) {
																echo '<option value="' . $u['user_id'] . '">' . w2PformSafe($u['userFC']) . "</option>\n";
															}
													?>
												</select>
											</td>
											<td align="center">
												<?php
													echo ('<a href="javascript:chAssignment(' . $p['project_id'] . ', 0, 0);">' . w2PshowImage('add.png', 16, 16, 'Assign Users', 'Assign selected Users to selected Tasks', 'tasks') . "</a>\n");
													echo ('<a href="javascript:chAssignment(' . $p['project_id'] . ', 1, 1);">' . w2PshowImage('remove.png', 16, 16, 'Unassign Users', 'Unassign Users from Task', 'tasks') . "</a>\n");
												?>
												<br />
												<select class="text" name="percentage_assignment" title="<?php echo ($AppUI->_('Assign with Percentage')); ?>" >
													<?php
														for ($i = 0; $i <= 100; $i += 5) {
															echo ("\t" . '<option ' . (($i == 30) ? 'selected="true"' : '') . ' value="' . $i . '">' . $i . '%</option>');
														}
													?>
												</select>
											</td>
									  </tr>
								  </table>
							  </td>
								<?php
							}
						?>
					</tr>
					<?php
				}
		
				if ($task_sort_item1 != '') {
					if ($task_sort_item2 != '' && $task_sort_item1 != $task_sort_item2) {
						$p['tasks'] = array_csort($p['tasks'], $task_sort_item1, $task_sort_order1, $task_sort_type1, $task_sort_item2, $task_sort_order2, $task_sort_type2);
					} else {
						$p['tasks'] = array_csort($p['tasks'], $task_sort_item1, $task_sort_order1, $task_sort_type1);
					}
				}
		
				global $tasks_filtered, $children_of;
				//get list of task ids and set-up array of children
				if (isset($p['tasks']) && is_array($p['tasks'])) {
					foreach ($p['tasks'] as $i => $t) {
						$tasks_filtered[] = $t['task_id'];
						$children_of[$t['task_parent']] = (isset($t['task_parent']) && isset($children_of[$t['task_parent']]) && $children_of[$t['task_parent']]) ? $children_of[$t['task_parent']] : array();
						if ($t['task_parent'] != $t['task_id']) {
							array_push($children_of[$t['task_parent']], $t['task_id']);
						}
					}
		
					global $shown_tasks;
					$shown_tasks = array();
					$parent_tasks = array();
					reset($p);
					//1st pass) parent tasks and its children
					foreach ($p['tasks'] as $i => $t1) {
						if (($t1['task_parent'] == $t1['task_id']) && !$task_id) {
							//Here we are NOT on a task view context, like the tasks module list or the project view tasks list.
							
							//check for child
							$no_children = empty($children_of[$t1['task_id']]);
	
							echo showtask($t1, 0, true, false, $no_children);
							$shown_tasks[$t1['task_id']] = $t1['task_id'];
							findchild($p['tasks'], $t1['task_id']);
						} elseif ($t1['task_parent'] == $task_id && $task_id) {
							//Here we are on a task view context
		
							//check for child
							$no_children = empty($children_of[$t1['task_id']]);
	
							echo showtask($t1, 0, true, false, $no_children);
							$shown_tasks[$t1['task_id']] = $t1['task_id'];
							findchild($p['tasks'], $t1['task_id']);
						}
					}
					reset($p);
					//2nd pass parentless tasks
					foreach ($p['tasks'] as $i => $t1) {
						if (!isset($shown_tasks[$t1['task_id']])) {
							//Here we are on a parentless task context, this can happen because we are:
							//1) displaying filtered tasks that could be showing only child tasks and not its parents due to filtering.
							//2) in a situation where child tasks are active and parent tasks are inactive or vice-versa.
							//
							//The IF condition makes sure:
							//1) The parent task has been displayed and passed through the findchild first, so child tasks are not erroneously displayed as orphan (parentless) 
							//2) Only not displayed yet tasks are shown so we don't show duplicates due to findchild that may cause duplicate showtasks for level 1 (and higher) tasks.
							echo showtask($t1, -1, true, false, true);
							$shown_tasks[] = $t1['task_id'];
						}
					}
				}
		
				if ($tnums && $w2Pconfig['enable_gantt_charts'] && !$min_view) {
					?>
					<tr>
                        <td colspan="<?php echo $cols; ?>" align="right">
                            <input type="button" class="button" value="<?php echo $AppUI->_('Reports'); ?>" 
                                   onclick="javascript:window.location='index.php?m=reports&amp;project_id=<?php echo $k; ?>';" />
                            <input type="button" class="button" value="<?php echo $AppUI->_('Gantt Chart'); ?>" 
                                   onclick="javascript:window.location='index.php?m=tasks&amp;a=viewgantt&amp;project_id=<?php echo $k; ?>';" />
                        </td>
					</tr>
					</form>
					<?php
				}
			}
		}
		$AppUI->savePlace();
	?>
</table>
<table width="100%" class="std">
	<tr>
		<td nowrap="nowrap"><?php echo $AppUI->_('Key'); ?>:</td>
		<td>&nbsp;</td>
		<td style="border-style:solid;border-width:1px" bgcolor="#ffffff">&nbsp;&nbsp;</td>
		<td nowrap="nowrap">=<?php echo $AppUI->_('Future Task'); ?></td>
		<td>&nbsp;</td>
		<td style="border-style:solid;border-width:1px" bgcolor="#e6eedd">&nbsp;&nbsp;</td>
		<td nowrap="nowrap">=<?php echo $AppUI->_('Started and on time'); ?></td>
		<td>&nbsp;</td>
		<td style="border-style:solid;border-width:1px" bgcolor="#ffeebb">&nbsp;&nbsp;</td>
		<td nowrap="nowrap">=<?php echo $AppUI->_('Should have started'); ?></td>
		<td>&nbsp;</td>
		<td style="border-style:solid;border-width:1px" bgcolor="#CC6666">&nbsp;&nbsp;</td>
		<td nowrap="nowrap">=<?php echo $AppUI->_('Overdue'); ?></td>
		<td>&nbsp;</td>
		<td style="border-style:solid;border-width:1px" bgcolor="#aaddaa">&nbsp;&nbsp;</td>
		<td nowrap="nowrap">=<?php echo $AppUI->_('Done'); ?></td>
		<td width="40%">&nbsp;</td>
	</tr>
</table>