<?php

/**
 * @package     web2project\modules\misc
 */

if (!isset($AppUI)) {
    $AppUI = new w2p_Core_CAppUI();
}
require_once ($AppUI->getLibraryClass('PEAR/BBCodeParser'));
$bbparser = new HTML_BBCodeParser();

if (isset($a) && $a == 'viewer') {
    $filters = array('All Topics', 'My Watched', 'Last 30 days');
} else {
    $filters = array('All Forums', 'My Forums', 'My Watched', 'My Projects', 'My Company', 'Inactive Projects');
}

class CForum extends w2p_Core_BaseObject
{

    public $forum_id = null;
    public $forum_project = null;
    public $forum_status = null;
    public $forum_owner = null;
    public $forum_name = null;
    public $forum_create_date = null;
    public $forum_last_date = null;
    public $forum_last_id = null;
    public $forum_message_count = null;
    public $forum_description = null;
    public $forum_moderated = null;

    public function __construct()
    {
        parent::__construct('forums', 'forum_id');
	$this->_tbl_project_id = 'forum_project';
    }

    public function isValid()
    {
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

        if ('' == trim($this->forum_name)) {
            $this->_error['forum_name'] = $baseErrorMsg . 'forum name is not set';
        }
        if (0 == (int) $this->forum_owner) {
            $this->_error['forum_owner'] = $baseErrorMsg . 'forum owner is not set';
        }

        return (count($this->_error)) ? false : true;
    }

    public function getMessages($notUsed = null, $forum_id = 0, $message_id = 0, $sortDir = 'asc')
    {
        $q = $this->_getQuery();
        $q->addTable('forums');
        $q->addTable('forum_messages');
        $q->addQuery('forum_messages.*,	contact_first_name, contact_last_name, contact_email,
            contact_display_name, contact_display_name as contact_name, user_username, forum_moderated, visit_user');
        $q->addJoin('forum_visits', 'v', 'visit_user = ' . (int) $this->_AppUI->user_id . ' AND visit_forum = ' . (int) $forum_id . ' AND visit_message = forum_messages.message_id');
        $q->addJoin('users', 'u', 'message_author = u.user_id', 'inner');
        $q->addJoin('contacts', 'con', 'contact_id = user_contact', 'inner');
        $q->addWhere('forum_id = message_forum AND (message_id = ' . (int) $message_id . ' OR message_parent = ' . (int) $message_id . ')');
        $q->addOrder('message_date ' . $sortDir);

        return $q->loadList();
    }

    public function load($notUsed = null, $forum_id)
    {
        $q = $this->_getQuery();
        $q->addQuery('*');
        $q->addTable('forums');
        $q->addWhere('forum_id = ' . (int) $forum_id);
        $q->loadObject($this, true, false);
    }

    public function loadFull($notUsed = null, $forum_id)
    {
        $q = $this->_getQuery();
        $q->addTable('forums');
        $q->addTable('users', 'u');
        $q->addQuery('forum_id, forum_project,	forum_description, forum_owner, forum_name,
            forum_create_date, forum_last_date, forum_message_count, forum_moderated,
            user_username, contact_first_name, contact_last_name, contact_display_name,
            project_name, project_color_identifier');
        $q->addJoin('contacts', 'con', 'contact_id = user_contact', 'inner');
        $q->addJoin('projects', 'p', 'p.project_id = forum_project', 'left');
        $q->addWhere('user_id = forum_owner');
        $q->addWhere('forum_id = ' . (int) $forum_id);

        $this->project_name = '';
        $this->project_color_identifier = '';
        $this->contact_first_name = '';
        $this->contact_last_name = '';
        $this->contact_display_name = '';
        $q->loadObject($this);
    }

    public function getAllowedForums($user_id, $company_id, $filter = -1, $orderby = 'forum_name', $orderdir = 'asc', $max_msg_length = 30)
    {
        $project = new CProject();
        $project->overrideDatabase($this->_query);

        $q = $this->_getQuery();
        $q->addTable('forums');

        $q->addQuery('forum_id, forum_project, forum_description, forum_owner, forum_name');
        $q->addQuery('forum_moderated, forum_create_date, forum_last_date');
        $q->addQuery('sum(if(c.message_parent=-1,1,0)) as forum_topics, sum(if(c.message_parent>0,1,0)) as forum_replies');
        $q->addQuery('user_username, project_name, project_color_identifier, contact_display_name as owner_name');
        $q->addQuery('SUBSTRING(l.message_body,1,' . $max_msg_length . ') message_body');
        $q->addQuery('LENGTH(l.message_body) message_length, watch_user, l.message_parent, l.message_id');
        $q->addQuery('count(distinct v.visit_message) as visit_count, count(distinct c.message_id) as message_count');
        $q->addQuery('w.notify_by_email');

        $q->addJoin('users', 'u', 'u.user_id = forum_owner');
        $q->addJoin('projects', 'pr', 'pr.project_id = forum_project');
        $q->addJoin('forum_messages', 'l', 'l.message_id = forum_last_id');
        $q->addJoin('forum_messages', 'c', 'c.message_forum = forum_id');
        $q->addJoin('forum_watch', 'w', 'watch_user = ' . $user_id . ' AND watch_forum = forum_id');
        $q->addJoin('forum_visits', 'v', 'visit_user = ' . $user_id . ' AND visit_forum = forum_id and visit_message = c.message_id');
        $q->addJoin('contacts', 'cts', 'contact_id = u.user_contact');

        $project->setAllowedSQL($user_id, $q, null, 'pr');
        $this->setAllowedSQL($user_id, $q);

        switch ($filter) {
            case 1:
                $q->addWhere('(project_active = 1 OR forum_project = 0) AND forum_owner = ' . $user_id);
                break;
            case 2:
                $q->addWhere('(project_active = 1 OR forum_project = 0) AND watch_user IS NOT NULL');
                break;
            case 3:
                $q->addWhere('(project_active = 1 OR forum_project = 0) AND project_owner = ' . $user_id);
                break;
            case 4:
                $q->addWhere('(project_active = 1 OR forum_project = 0) AND project_company = ' . $company_id);
                break;
            case 5:
                $q->addWhere('(project_active = 0 OR forum_project = 0)');
                break;
            default:
                $q->addWhere('(project_active = 1 OR forum_project = 0)');
                break;
        }

        $q->addGroup('forum_id');
        $q->addOrder($orderby . ' ' . $orderdir);

        return $q->loadList();
    }

    protected function hook_preCreate() {
        $this->forum_create_date = $this->_AppUI->convertToSystemTZ($this->forum_create_date);

        parent::hook_preCreate();
    }

    public function delete()
    {
        $result = false;

        if ($this->canDelete()) {
            $q = $this->_getQuery();
            $q->setDelete('forum_messages');
            $q->addWhere('message_forum = ' . (int) $this->forum_id);
            if (!$q->exec()) {
                $this->_error['delete-messages'] = db_error();
                return false;
            }

            $result = parent::delete();
        }
        return $result;
    }

    protected function hook_preDelete()
    {
        $q = $this->_getQuery();
        $q->setDelete('forum_visits');
        $q->addWhere('visit_forum = ' . (int) $this->forum_id);
        $q->exec();
    }

    public function getAllowedRecords($uid, $fields = '*', $orderby = '', $index = null, $extra = null)
    {
        $oPrj = new CProject();
        $oPrj->overrideDatabase($this->_query);

        $aPrjs = $oPrj->getAllowedRecords($uid, 'projects.project_id, project_name', '', null, null, 'projects');
        if (count($aPrjs)) {
            $buffer = '(forum_project IN (' . implode(',', array_keys($aPrjs)) . ') OR forum_project IS NULL OR forum_project = \'\' OR forum_project = 0)';

            if ($extra['where'] != '') {
                $extra['where'] = $extra['where'] . ' AND ' . $buffer;
            } else {
                $extra['where'] = $buffer;
            }
        } else {
            // There are no allowed projects, so only allow forums with no project associated.
            if ($extra['where'] != '') {
                $extra['where'] = $extra['where'] . ' AND (forum_project IS NULL OR forum_project = \'\' OR forum_project = 0) ';
            } else {
                $extra['where'] = '(forum_project IS NULL OR forum_project = \'\' OR forum_project = 0)';
            }
        }
        return parent::getAllowedRecords($uid, $fields, $orderby, $index, $extra);
    }

    public function hook_search()
    {
        $search['table'] = 'forums';
        $search['table_alias'] = 'f';
        $search['table_module'] = 'forums';
        $search['table_key'] = 'f.forum_id';
        $search['table_link'] = 'index.php?m=forums&a=viewer&forum_id='; // first part of link
        $search['table_key2'] = 'fm.message_id';
        $search['table_link2'] = '&message_id='; // second part of link

        $search['table_title'] = 'Forums';
        $search['table_orderby'] = 'forum_name';
        $search['search_fields'] = array(
            'forum_name', 'forum_description',
            'message_title', 'message_body'
        );
        $search['display_fields'] = $search['search_fields'];
        $search['table_joins'] = array(
            array(
                'table' => 'forum_messages',
                'alias' => 'fm',
                'join' => 'f.forum_id = fm.message_forum'
            )
        );

        return $search;
    }

    public function canEdit() 
    {
	return parent::canEdit() || ($this->_AppUI->user_id == $this->forum_owner);
    }

    public function canDelete() 
    {
	return parent::canDelete() || ($this->_AppUI->user_id == $this->forum_owner);
    }

    public static function getWatchedMessages($start_date, $end_date, $user_id, $company_id = 0)
    {
        $db_start = $start_date->format(FMT_DATETIME_MYSQL);
        $db_end = $end_date->format(FMT_DATETIME_MYSQL);

	$obj = new CForum();
	$forums = $obj->getAllowedRecords($user_id, 'forums.forum_id, forum_name', '', null, null);
	if (count($forums)) {
		$prj_filter = ' AND (forum_id IN (' . implode(',', array_keys($forums)) . '))';
	} else {
		$prj_filter = ' AND False';
	}

	// Filter also by company, if needed
	if ($company_id) {
		$prj_filter .= ' AND (project_company = "' . (string)$company_id . '")';
	}

        $q = new w2p_Database_Query;
        $q->addTable('forum_messages', 'fm');
	$q->leftJoin('forum_visits', 'fv', 'fm.message_id = fv.visit_message AND fm.message_forum = fv.visit_forum AND fv.visit_user = ' . $user_id);
        $q->leftJoin('forums', 'f', 'f.forum_id = fm.message_forum');
        $q->leftJoin('users', 'u', 'u.user_id = fm.message_author');
        $q->leftJoin('contacts', 'cts', 'cts.contact_id = u.user_contact');
        $q->leftJoin('projects', 'pr', 'pr.project_id = f.forum_project');
	if ($company_id) {
		$q->leftJoin('companies', 'cmp', 'cmp.company_id = pr.project_company');
	}
	$q->innerJoin('forum_watch','fw','fm.message_parent = fw.watch_topic OR fm.message_id = fw.watch_topic OR fm.message_forum = fw.watch_forum');
	$q->addGroup('fm.message_id');
        $q->addQuery('fm.message_id, fm.message_forum, fm.message_parent, fm.message_title, fm.message_date, fm.message_author, f.forum_project, cts.contact_display_name, pr.project_name, f.forum_name');
	$q->addWhere('fw.watch_user = ' . $user_id . ' AND fm.message_published = true AND fm.message_date <= "' . $db_end .'" AND (fv.visit_date > "' . $db_end .'" OR fv.visit_date IS NULL) ' . $prj_filter);

        return $q->loadList();
    }

    public static function getHRef($forum_id)
    {
    	   return 'm=forums&a=viewer&forum_id=' . (string)$forum_id;
    }

    protected function generateHistoryDescription($event) {
        global $AppUI;

	$event = mb_strtolower($event);
	if ($event == 'create') {
		return $AppUI->_('Forum') . ' \'' . $this->forum_name . '\' ' . $AppUI->_('was created with ID') . ' ' . $this->forum_id;
	} elseif ($event == 'update') {
		return $AppUI->_('Forum') . ' \'' . $this->forum_name . '\', ' . $AppUI->_('with ID') . ' ' . $this->forum_id . ', ' . $AppUI->_('was edited');
	} elseif ($event == 'delete') {
		return $AppUI->_('Forum') . ' \'' . $this->forum_name . '\', ' . $AppUI->_('with ID') . ' ' . $this->forum_id . ', ' . $AppUI->_('was deleted');
	} else {
		return parent::generateHistoryDescription($event);
	}
    }

    public function getWatchers() {
        $q = $this->_getQuery();
        $q->addTable('forum_watch');
        $q->addQuery('watch_user');
	$q->addWhere('watch_forum = ' . (int)$this->forum_id);
        return $q->loadColumn();
    }

    public function setWatchers() {
	// This operation will be done in three steps to preserve the
	// email notification settings of watchers already present.
	$selected_watchers = w2PgetParam($_POST, 'forum_watchers', '');
	// Delete any watcher not present on the new list
        $q = $this->_getQuery();
        $q->setDelete('forum_watch');
	if (strlen($selected_watchers) > 0) {
		$q->addWhere('watch_forum = ' . $this->forum_id . ' AND watch_user NOT IN (' . $selected_watchers . ')');
	} else {
		$q->addWhere('watch_forum = ' . $this->forum_id);
	}
	$q->exec();
	$q->clear();
	// Get the remaining watchers
	$already = $this->getWatchers();
	// Compute the difference so that we're left only with watchers not already set.
	$watchers = explode(',', $selected_watchers);
	$watchers = array_diff($watchers, $already);
	// Insert the new watchers
	foreach ($watchers as $watch) {
		if ((int)$watch) {
			$q->addTable('forum_watch');
			$q->addInsert('watch_user', $watch);
			$q->addInsert('watch_forum', $this->forum_id);
			$q->addInsert('notify_by_email', false);
	                $q->exec();
	                $q->clear();
		}
	}
    }

    protected function hook_postStore() {
	$this->setWatchers();
        parent::hook_postStore();
    }
}