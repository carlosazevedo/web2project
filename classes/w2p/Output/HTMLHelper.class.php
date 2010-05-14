<?php /* $Id$ $URL$ */

/**
 *	@package web2project
 *	@subpackage output
 *	@version $Revision$
 */

class w2p_Output_HTMLHelper {

    public static function renderContactList(CAppUI $AppUI, $contactList) {

        $output  = '<table cellspacing="1" cellpadding="2" border="0" width="100%" class="tbl">';
        $output .= '<tr><th>'.$AppUI->_('Name').'</th><th>'.$AppUI->_('Email').'</th><th>'.$AppUI->_('Phone').'</th><th>'.$AppUI->_('Department').'</th></tr>';
        foreach ($contactList as $contact_id => $contact_data) {
            $output .= '<tr>';
            $output .= '<td class="hilite"><a href="index.php?m=contacts&a=addedit&contact_id=' . $contact_id . '">' . $contact_data['contact_order_by'] . '</a></td>';
            $output .= '<td class="hilite"><a href="mailto: ' . $contact_data['contact_email'] . '">' . $contact_data['contact_email'] . '</a></td>';
            $output .= '<td class="hilite">' . $contact_data['contact_phone'] . '</td>';
            $output .= '<td class="hilite">' . $contact_data['dept_name'] . '</td>';
            $output .= '</tr>';
        }
        $output .= '</table>';

        return $output;
    }
}