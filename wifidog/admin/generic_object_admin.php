<?php


/********************************************************************\
 * This program is free software; you can redistribute it and/or    *
 * modify it under the terms of the GNU General Public License as   *
 * published by the Free Software Foundation; either version 2 of   *
 * the License, or (at your option) any later version.              *
 *                                                                  *
 * This program is distributed in the hope that it will be useful,  *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of   *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the    *
 * GNU General Public License for more details.                     *
 *                                                                  *
 * You should have received a copy of the GNU General Public License*
 * along with this program; if not, contact:                        *
 *                                                                  *
 * Free Software Foundation           Voice:  +1-617-542-5942       *
 * 59 Temple Place - Suite 330        Fax:    +1-617-542-2652       *
 * Boston, MA  02111-1307,  USA       gnu@gnu.org                   *
 *                                                                  *
 \********************************************************************/
/**@file generic_object_admin.php
 * A simple interface to edit any object that implements the GenericObject interface.  The php file takes the following params:
 * $_REQUEST['action']: new, edit, delete, preview (also save, but not meant for calling from outside this file)
 * $_REQUEST['object_id']:  The id of the object ot be edited
 * $_REQUEST['object_class']:  The class name of the object ot be edited
 * $_REQUEST['node_id']: In preview mode, the current node to simulate display
 * $_REQUEST['debug']:  If present and non empty, the $_REQUEST variables will be displayed
 * @author Copyright (C) 2005 Benoit Grégoire <bock@step.polymtl.ca>,
 * Technologies Coeus inc.
 */
define('BASEPATH', '../');
require_once 'admin_common.php';
require_once BASEPATH.'classes/GenericObject.php';

if (!empty ($_REQUEST['debug'])) {
	echo "<pre>";
	print_r($_REQUEST);
	echo "</pre>";
}
$html = '';
$html = '<h3>'._("Generic object editor").'</h3>';
if (empty ($_REQUEST['object_class'])) {
	echo "<div class='errormsg'>"._("Sorry, the 'object_class' parameter must be specified")."</div>\n";
	exit;
} else {
	$class = $_REQUEST['object_class'];
}

if ($_REQUEST['action'] == 'new') {
	$object = call_user_func(array ($class, 'createNewObject'));
	$_REQUEST['action'] = 'edit';
} else {
	if (empty ($_REQUEST['object_id'])) {
		echo "<div class='errormsg'>"._("Sorry, the 'object_id' parameter must be specified")."</div>\n";
		exit;
	}
	$object = call_user_func(array ($class, 'getObject'), $_REQUEST['object_id']);
}

if ($_REQUEST['action'] == 'save') {
	$html .= $object->processAdminUI();
	$object = call_user_func(array ($class, 'getObject'), $_REQUEST['object_id']);
	$_REQUEST['action'] = 'edit';
}

if ($_REQUEST['action'] == 'delete') {
	$errmsg = '';

	if ($object->delete($errmsg)) {
		$html .= "<div class='successmsg'>"._("Content successfully deleted")."</div>\n";
	} else {
		$html .= "<div class='errormsg'>"._("Deletion failed, error was: ")."$errmsg</div>\n";
		$_REQUEST['action'] = 'edit';
	}
}

if ($_REQUEST['action'] == 'edit') {
	$common_input = '';
	if (!empty ($_REQUEST['debug'])) {
		$common_input .= "<input type=submit name='debug' value='true'>\n";
	}
	$common_input .= "<input type='hidden' name='object_id' value='".$object->GetId()."'>\n";
	$common_input .= "<input type='hidden' name='object_class' value='".get_class($object)."'>\n";

	$html .= "<form enctype='multipart/form-data' action='".GENERIC_OBJECT_ADMIN_ABS_HREF."' method='post'>";
	$html .= $common_input;
	$html .= $object->getAdminUI();
	$html .= "<input type='hidden' name='action' value='save'>\n";
	$html .= "<input type=submit name='save_submit' value='"._("Save")." ".get_class($object)."'>\n";
	$html .= '</form>';

	$html .= "<form action='".GENERIC_OBJECT_ADMIN_ABS_HREF."' method='post'>";
	$html .= $common_input;
	$html .= "<input type='hidden' name='action' value='preview'>\n";
	$html .= "<input type=submit name='preview_submit' value='"._("Preview")." ".get_class($object)."'>\n";
	$html .= '</form>';

	$html .= "<form action='".GENERIC_OBJECT_ADMIN_ABS_HREF."' method='post'>";
	$html .= $common_input;
	$html .= "<input type='hidden' name='action' value='delete'>\n";
	$html .= "<input type=submit name='delete_submit' value='"._("Delete")." ".get_class($object)."'>\n";
	$html .= '</form>';
}

if ($_REQUEST['action'] == 'preview') {
	if (empty ($_REQUEST['node_id'])) {
		$node_id = null;
		$node = null;
	} else {
		$node_id = $_REQUEST['node_id'];
		$node = Node :: getObject($node_id);
		Node :: setCurrentNode($node);
	}
	$common_input = '';
	if (!empty ($_REQUEST['debug'])) {
		$common_input .= "<input type=submit name='debug' value='true'>\n";
	}
	$common_input .= "<input type='hidden' name='object_id' value='".$object->GetId()."'>\n";
	$common_input .= "<input type='hidden' name='object_class' value='".get_class($object)."'>\n";
	$common_input .= "<input type='hidden' name='node_id' value='".$node_id."'>\n";

	$html .= "<form action='".GENERIC_OBJECT_ADMIN_ABS_HREF."' method='post'>";
	$html .= $common_input;

	$name = "node_id";
	$html .= Node :: getSelectNodeUI($name);

	$html .= $object->getUserUI();
	$html .= "<input type='hidden' name='action' value='preview'>\n";
	$html .= "<input type=submit name='preview_submit' value='"._("Preview")." ".get_class($object)."'>\n";
	$html .= '</form>';

	$html .= "<form action='".GENERIC_OBJECT_ADMIN_ABS_HREF."' method='post'>";
	$html .= $common_input;
	$html .= "<input type='hidden' name='action' value='edit'>\n";
	$html .= "<input type=submit name='edit_submit' value='"._("Edit")." ".get_class($object)."'>\n";
	$html .= '</form>';
}

require_once BASEPATH.'classes/MainUI.php';
$ui=new MainUI();
$ui->setToolSection('ADMIN');
$ui->setTitle(_("Generic object editor"));
$ui->setMainContent($html);
$ui->display();
?>

