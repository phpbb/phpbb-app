<?php
/***************************************************************************
 *                           admin_permissions.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

if (!empty($setmodules))
{
	$filename = basename(__FILE__);
	$module['FORUM']['PERMISSIONS'] = ($auth->acl_get('a_auth')) ? $filename . $SID . '&amp;mode=forum' : '';
	$module['FORUM']['MODERATORS'] = ($auth->acl_get('a_authmods')) ? $filename . $SID . '&amp;mode=mod' : '';
	$module['FORUM']['SUPER_MODERATORS'] = ($auth->acl_get('a_authmods')) ? $filename . $SID . '&amp;mode=supermod' : '';
	$module['FORUM']['ADMINISTRATORS'] = ($auth->acl_get('a_authadmins')) ? $filename . $SID . '&amp;mode=admin' : '';
	$module['USER']['PERMISSIONS'] = ($auth->acl_get('a_authusers')) ? $filename . $SID . '&amp;mode=user' : '';
	$module['GROUP']['PERMISSIONS'] = ($auth->acl_get('a_authgroups')) ? $filename . $SID . '&amp;mode=group' : '';

	return;
}

define('IN_PHPBB', 1);
// Include files
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);


// Grab and set some basic parameters
//
// 'mode' determines what we're altering; administrators, users, deps, etc.
// 'submit' is used to determine what we're doing ... special format
$mode	= (isset($_REQUEST['mode'])) ? htmlspecialchars($_REQUEST['mode']) : '';
$submode= (isset($_REQUEST['submode'])) ? htmlspecialchars($_REQUEST['submode']) : '';
$which_mode = (!empty($submode) && $submode != $mode) ? $submode : $mode;
$submit = array_values(preg_grep('#^submit_(.*)$#i', array_keys($_POST)));
$submit = (sizeof($submit)) ? substr($submit[0], strpos($submit[0], '_') + 1) : '';


// Submitted setting data
//
// 'auth_settings' contains the submitted option settings assigned to options, should be an 
//   associative array with integer values
$auth_settings		= (isset($_POST['settings'])) ? $_POST['settings'] : '';


// Forum, User or Group information
//
// 'ug_type' is either user or groups used mainly for forum/admin/mod permissions
// 'ug_data' contains the list of usernames, user_id's or group_ids for the 'ug_type'
// 'forum_id' contains the list of forums, 0 is used for "All forums", can be array or scalar
$ug_type = (isset($_REQUEST['ug_type'])) ? htmlspecialchars($_REQUEST['ug_type']) : '';
$ug_data = (isset($_POST['ug_data'])) ? $_POST['ug_data'] : '';

if (isset($_REQUEST['f']))
{
	$forum_id = (is_array($_REQUEST['f'])) ? $_REQUEST['f'] : intval($_REQUEST['f']);
}

if (!isset($forum_id[$which_mode]))
{
	$forum_id[$which_mode][] = 0;
}
$sql_forum_id = implode(', ', array_map('intval', $forum_id[$which_mode]));


// Instantiate a new auth admin object in readiness
$auth_admin = new auth_admin();


// What mode are we running? So we can output the correct title, explanation
// and set the sql_option_mode/acl check
switch ($mode)
{
	case 'forum':
		$l_title = $user->lang['PERMISSIONS'];
		$l_title_explain = $user->lang['PERMISSIONS_EXPLAIN'];
		$which_acl = 'a_auth';
		$sql_option_mode = 'f';
		break;

	case 'mod':
		$l_title = $user->lang['MODERATORS'];
		$l_title_explain = $user->lang['MODERATORS_EXPLAIN'];
		$which_acl = 'a_authmods';
		$sql_option_mode = 'm';
		break;

	case 'supermod':
		$l_title = $user->lang['SUPER_MODERATORS'];
		$l_title_explain = $user->lang['SUPER_MODERATORS_EXPLAIN'];
		$which_acl = 'a_authmods';
		$sql_option_mode = 'm';
		break;

	case 'admin':
		$l_title = $user->lang['ADMINISTRATORS'];
		$l_title_explain = $user->lang['ADMINISTRATORS_EXPLAIN'];
		$which_acl = 'a_authadmins';
		$sql_option_mode = 'a';
		break;

	case 'user':
		$l_title = $user->lang['USER_PERMISSIONS'];
		$l_title_explain = $user->lang['USER_PERMISSIONS_EXPLAIN'];
		$which_acl = 'a_authusers';
		$sql_option_mode = 'u';
		break;

	case 'group':
		$l_title = $user->lang['GROUP_PERMISSIONS'];
		$l_title_explain = $user->lang['GROUP_PERMISSIONS_EXPLAIN'];
		$which_acl = 'a_authgroups';
		$sql_option_mode = 'u';
		break;

	case 'deps':
		$l_title = $user->lang['DEPENDENCIES'];
		$l_title_explain = $user->lang['DEPENDENCIES_EXPLAIN'];
		$which_acl = 'a_authdeps';
		break;
}

// Permission check
if (!$auth->acl_get($which_acl))
{
	trigger_error($user->lang['NO_ADMIN']);
}

// Are we setting deps? If we are we need to re-run the mode match above for the
// relevant 'new' mode
if (!empty($submode))
{
	switch ($submode)
	{
		case 'forum':
			$l_title_explain = $user->lang['PERMISSIONS_EXPLAIN'];
			$which_acl = 'a_auth';
			$sql_option_mode = 'f';
			break;

		case 'mod':
			$l_title_explain = $user->lang['MODERATORS_EXPLAIN'];
			$which_acl = 'a_authmods';
			$sql_option_mode = 'm';
			break;

		case 'supermod':
			$l_title_explain = $user->lang['SUPER_MODERATORS_EXPLAIN'];
			$which_acl = 'a_authmods';
			$sql_option_mode = 'm';
			break;
	}

	// Permission check
	if (!$auth->acl_get($which_acl))
	{
		trigger_error($user->lang['NO_ADMIN']);
	}
}


// Does user want to update anything? Check here to find out 
// and act appropriately
switch ($submit)
{
	case 'update':

	print_r($auth_settings);

		// Here we decide which depedencies we are looking for ... if all the submitted
		// settings are the same we look for a dependency of "All options". This allows
		// for situations where for example changing permissions for all options to the 
		// same setting (e.g. 'unset' or 'no') could lead to forum view permissions being
		// changed to 'unset'. When all options do not have the same setting we lookup
		// dependencies for the given range of options and settings and those where 
		// "Any option" has been specified for the current range of settings

		// No dependencies exist or we've already shown 'em ... so now
		// we go ahead and update the permission sets
		echo "DONE";
		exit;

		// If we are submitting with dependencies first we set the original options
		if (isset($_POST['skipdeps']))
		{
			foreach ($ug_data as $id)
			{
				$auth_admin->acl_set($ug_type, $forum_id_deps, $id, $auth_settings_deps);
			}
		}
		unset($auth_settings_deps);
		unset($forum_id_deps);

		// Admin wants subforums to inherit permissions ... so handle this
		if (!empty($_POST['inherit']))
		{
			array_push($_POST['inherit'], $forum_id);
			$forum_id = $_POST['inherit'];
		}

		// This will be either the submitted dependencies or the "original" options
		// dependending on whether any dependencies existed and were submitted
		if (empty($_POST['skipdeps']))
		{
			foreach ($ug_data as $id)
			{
				$auth_admin->acl_set($ug_type, $forum_id, $id, $auth_settings);
			}
		}

		cache_moderators();

		trigger_error($user->lang['AUTH_UPDATED']);
		break;

	case 'delete':
		echo "HERE :: DELETE";
		exit;

/*
		$option_ids = false;
		if (!empty($settings)
		{
			$sql = "SELECT auth_option_id
				FROM " . ACL_OPTIONS_TABLE . "
				WHERE auth_option LIKE '" . $settings['option'] . "_%'";
			$result = $db->sql_query($sql);

			if ($row = $db->sql_fetchrow($result))
			{
				$option_ids = array();
				do
				{
					$option_ids[] = $row['auth_option_id'];
				}
				while($row = $db->sql_fetchrow($result));
			}
			$db->sql_freeresult($result);
		}

		foreach ($_POST['ug_id'] as $id)
		{
			$auth_admin->acl_delete($_POST['type'], $forum_id, $id, $option_ids);
		}

		cache_moderators();

		trigger_error($user->lang['AUTH_UPDATED']);
*/
		break;

	case 'presetsave':
		$holding_ary = array();
		foreach ($auth_settings as $option => $setting)
		{
			switch ($setting)
			{
				case ACL_YES:
					$holding_ary['yes'][] = $option;
					break;

				case ACL_NO:
					$holding_ary['no'][] = $option;
					break;

				case ACL_UNSET:
					$holding_ary['unset'][] = $option;
					break;
			}
		}
		unset($option);
		unset($setting);

		$sql = array(
			'preset_user_id'=> intval($user->data['user_id']),
			'preset_type'	=> $sql_option_mode,
			'preset_data'	=> $db->sql_escape(serialize($holding_ary))
		);

		if (!empty($_POST['presetname']))
		{
			$sql['preset_name'] = $db->sql_escape($_POST['presetname']);
		}
		
		if (!empty($_POST['presetname']) || $_POST['presetoption'] != -1)
		{
			$sql = ($_POST['presetoption'] == -1) ? 'INSERT INTO ' . ACL_PRESETS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql) : 'UPDATE ' . ACL_PRESETS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql) . ' WHERE preset_id =' . intval($_POST['presetoption']);
			$db->sql_query($sql);
		}
		break;

	case 'presetdel':
		if (!empty($_POST['presetoption']))
		{
			$sql = "DELETE FROM " . ACL_PRESETS_TABLE . " 
				WHERE preset_id = " . intval($_POST['presetoption']);
			$db->sql_query($sql);
		}
		break;
}
// End update


// Output page header
page_header($l_title);


// First potential form ... this is for selecting forums, users
// or groups. 
if (in_array($mode, array('user', 'group', 'forum', 'mod')) && empty($submit))
{

?>

<h1><?php echo $l_title; ?></h1>

<p><?php echo $l_title_explain ?></p>

<form method="post" action="<?php echo "admin_permissions.$phpEx$SID&amp;mode=$mode"; ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
<?php

	// Mode specific markup
	switch ($mode)
	{
		case 'forum':
		case 'mod':

?>
	<tr>
		<th align="center"><?php echo $user->lang['LOOK_UP_FORUM']; ?></th>
	</tr>
	<tr>
		<td class="row1" align="center">&nbsp;<select name="f"><?php echo 
	
			make_forum_select();
			
?></select> &nbsp;<input type="submit" name="submit_usergroups" value="<?php echo $user->lang['LOOK_UP_FORUM']; ?>" class="mainoption" /><input type="hidden" name="ug_type" value="forum" /><input type="hidden" name="action" value="usergroups" />&nbsp;</td>
	</tr>
<?php
		
			break;

		case 'user':

?>
	<tr>
		<th align="center"><?php echo $user->lang['LOOK_UP_USER']; ?></th>
	</tr>
	<tr>
		<td class="row1" align="center"><input type="text" class="post" name="ug_data" maxlength="30" size="20" /> <input type="submit" name="submit_options" value="<?php echo $user->lang['LOOK_UP_USER']; ?>" class="mainoption" /> <input type="submit" name="usersubmit" value="<?php echo $user->lang['FIND_USERNAME']; ?>" class="liteoption" onClick="window.open('<?php echo "../memberlist.$phpEx$SID&amp;mode=searchuser&amp;field=username"; ?>', '_phpbbsearch', 'HEIGHT=500,resizable=yes,scrollbars=yes,WIDTH=740');return false;" /><input type="hidden" name="ug_type" value="username" /></td>
	</tr>
<?php

			break;

		case 'group':
			// Generate list of groups
			$sql = "SELECT group_id, group_name    
				FROM " . GROUPS_TABLE . " 
				ORDER BY group_type DESC";
			$result = $db->sql_query($sql);

			$group_options = '';
			if ($row = $db->sql_fetchrow($result))
			{
				do
				{
					$group_options .= (($group_options != '') ? ', ' : '') . '<option value="' . $row['group_id'] . '">' . $row['group_name'] . '</option>';
				}
				while ($row = $db->sql_fetchrow($result));
			}
			$db->sql_freeresult($result);

?>
	<tr>
		<th align="center"><?php echo $user->lang['LOOK_UP_GROUP']; ?></th>
	</tr>
	<tr>
		<td class="row1" align="center">&nbsp;<select name="ug_data"><?php echo $group_options; ?></select> &nbsp;<input type="submit" name="submit_options" value="<?php echo $user->lang['LOOK_UP_GROUP']; ?>" class="mainoption" /><input type="hidden" name="ug_type" value="group" />&nbsp;</td>
	</tr>
<?php

		break;

	}

?>
</table></form>

<?php

}
// End user, group or forum selection


// Second possible form, this lists the currently enabled
// users/groups for the given mode
if ((in_array($submit, array('usergroups', 'delete', 'cancel'))) || ($submit != 'options' && empty($submode) && in_array($mode, array('admin', 'supermod'))))
{
	// Generate list of forum id's
	$s_forum_id = '';
	foreach ($forum_id as $forum_submode => $forum_submode_ids)
	{
		foreach ($forum_submode_ids as $submode_forum_id)
		{
			$s_forum_id .= '<input type="hidden" name="f[' . $forum_submode . '][]" value="' . $submode_forum_id . '" />';
		}
	}
	unset($forum_submode_ids);
	unset($forum_submode);
	unset($submode_forum_id);

?>

<p><?php echo $l_title_explain; ?></p>

<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td align="center"><h1><?php echo $user->lang['USERS']; ?></h1></td>
		<td align="center"><h1><?php echo $user->lang['GROUPS']; ?></h1></td>
	</tr>
	<tr>

		<td><form method="post" action="<?php echo "admin_permissions.$phpEx$SID&amp;mode=$mode"; ?>"><table width="90%" class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
<?php

	$sql = "SELECT DISTINCT u.user_id, u.username
		FROM " . USERS_TABLE . " u, " . ACL_USERS_TABLE . " a, " . ACL_OPTIONS_TABLE . " o
		WHERE o.auth_option LIKE '" . $sql_option_mode . "_%'
			AND a.auth_option_id = o.auth_option_id
			AND a.forum_id IN ($sql_forum_id)
			AND u.user_id = a.user_id
		ORDER BY u.username, u.user_regdate ASC";
	$result = $db->sql_query($sql);

	$users = '';
	while ($row = $db->sql_fetchrow($result))
	{
		$users .= '<option value="' . $row['user_id'] . '">' . $row['username'] . '</option>';
	}
	$db->sql_freeresult($result);

?>
			<tr>
				<th><?php echo $user->lang['MANAGE_USERS']; ?></th>
			</tr>
			<tr>
				<td class="row1" align="center"><select style="width:280px" name="ug_data[]" multiple="multiple" size="5"><?php echo $users; ?></select></td>
			</tr>
			<tr>
				<td class="cat" align="center"><input class="liteoption" type="submit" name="submit_delete" value="<?php echo $user->lang['DELETE']; ?>" /> &nbsp; <input class="liteoption" type="submit" name="submit_options" value="<?php echo $user->lang['SET_OPTIONS']; ?>" /><input type="hidden" name="ug_type" value="user" /><?php echo $s_forum_id; ?></td>
			</tr>
		</table></form></td>

		<td align="center"><form method="post" name="admingroups" action="<?php echo "admin_permissions.$phpEx$SID&amp;mode=$mode"; ?>"><table width="90%" class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
<?php

	$sql = "SELECT DISTINCT g.group_id, g.group_name
		FROM " . GROUPS_TABLE . " g, " . ACL_GROUPS_TABLE . " a, " . ACL_OPTIONS_TABLE . " o
		WHERE o.auth_option LIKE '" . $sql_option_mode . "_%'
			AND a.forum_id IN ($sql_forum_id)
			AND a.auth_option_id = o.auth_option_id
			AND g.group_id = a.group_id
		ORDER BY g.group_type DESC, g.group_name ASC";
	$result = $db->sql_query($sql);

	$groups = '';
	while ($row = $db->sql_fetchrow($result))
	{
		$groups .= '<option value="' . $row['group_id'] . '">' . ((!empty($user->lang['G_' . $row['group_name']])) ? '* ' . $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</option>';
	}
	$db->sql_freeresult($result);

	$sql = "SELECT group_id, group_name
		FROM " . GROUPS_TABLE . "
		ORDER BY group_type DESC, group_name";
	$result = $db->sql_query($sql);

	$group_list = '';
	while ($row = $db->sql_fetchrow($result))
	{
		$group_list .= '<option value="' . $row['group_id'] . '">' . ((!empty($user->lang['G_' . $row['group_name']])) ? '* ' . $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</option>';
	}
	$db->sql_freeresult($result);

?>
		<tr>
			<th><?php echo $user->lang['MANAGE_GROUPS']; ?></th>
		</tr>
		<tr>
			<td class="row1" align="center"><select style="width:280px" name="ug_data[]" multiple="multiple" size="5"><?php echo $groups; ?></select></td>
		</tr>
		<tr>
			<td class="cat" align="center"><input class="liteoption" type="submit" name="submit_delete" value="<?php echo $user->lang['DELETE']; ?>" /> &nbsp; <input class="liteoption" type="submit" name="submit_options" value="<?php echo $user->lang['SET_OPTIONS']; ?>" /><input type="hidden" name="ug_type" value="group" /><?php echo $s_forum_id; ?></td>
		</tr>
	</table></form></td>

	</tr>
	<tr>

		<td><form method="post" action="<?php echo "admin_permissions.$phpEx$SID&amp;mode=$mode"; ?>"><table class="bg" width="90%" cellspacing="1" cellpadding="4" border="0" align="center">
			<tr>
				<th><?php echo $user->lang['ADD_USERS']; ?></th>
			</tr>
			<tr>
				<td class="row1" align="center"><textarea cols="40" rows="4" name="ug_data[]"></textarea></td>
			</tr>
			<tr>
				<td class="cat" align="center"> <input type="submit" name="submit_options" value="<?php echo $user->lang['SUBMIT']; ?>" class="mainoption" />&nbsp; <input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="liteoption" />&nbsp; <input type="submit" name="usersubmit" value="<?php echo $user->lang['FIND_USERNAME']; ?>" class="liteoption" onclick="window.open('<?php echo "../memberlist.$phpEx$SID"; ?>&amp;mode=searchuser&amp;form=2&amp;field=entries', '_phpbbsearch', 'HEIGHT=500,resizable=yes,scrollbars=yes,WIDTH=740');return false;" /><input type="hidden" name="ug_type" value="user" /><?php echo $s_forum_id; ?></td>
			</tr>
		</table></form></td>

		<td><form method="post" action="<?php echo "admin_permissions.$phpEx$SID&amp;mode=$mode"; ?>"><table width="90%" class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
			<tr>
				<th><?php echo $user->lang['ADD_GROUPS']; ?></th>
			</tr>
			<tr>
				<td class="row1" align="center"><select name="ug_data[]" multiple="multiple" size="4"><?php echo $group_list; ?></select></td>
			</tr>
			<tr>
				<td class="cat" align="center"> <input type="submit" name="submit_options" value="<?php echo $user->lang['SUBMIT']; ?>" class="mainoption" />&nbsp; <input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="liteoption" /><input type="hidden" name="ug_type" value="group" /><?php echo $s_forum_id; ?></td>
			</tr>
		</table></form></td>
	</tr>
</table>

<?php

}
// End user and group acl selections






// Third possible form, this is the major section of this script. It
// handles the entry of permission options for all situations
if (in_array($submit, array('options', 'presetsave', 'presetdel', 'update')) || !empty($submode))
{

	if (!isset($forum_id) && empty($ug_data))
	{
		trigger_error($user->lang['NO_MODE']);
	}


	// Grab the forum details if non-zero forum_id
	if (!in_array(0, $forum_id[$which_mode]))
	{
		$forum_data = array();
		$sql = 'SELECT forum_id, forum_name, parent_id  
			FROM ' . FORUMS_TABLE . "
			WHERE forum_id IN ($sql_forum_id)";
		$result = $db->sql_query($sql);

		if (!($forum_data = $db->sql_fetchrow($result)))
		{
			trigger_error($user->lang['NO_FORUM']);
		}
		$db->sql_freeresult($result);
	}


	// Grab relevant user or group information
	$ug_ids = $ug_names = $ug_hidden = '';
	$l_no_error = '';
	switch ($ug_type)
	{
		case 'user':
			$l_no_error = $user->lang['NO_USER'];
			$sql = 'SELECT user_id AS id, username AS name 
				FROM ' . USERS_TABLE . '
				WHERE user_id';
			$sql .= (is_array($ug_data)) ? ' IN (' . implode(', ', $ug_data) . ')' : ' = ' . $ug_data;
			break;

		case 'username':
			$l_no_error = $user->lang['NO_USER'];
			$sql = 'SELECT user_id AS id, username AS name 
				FROM ' . USERS_TABLE . '
				WHERE username';
			$sql .= (is_array($ug_data)) ? ' IN (' . implode(', ', preg_replace('#^[\s]*?(.*?)[\s]*?$#', "'\\1'", $ug_data)) . ')' : ' = ' . "'" . trim($ug_data) . "'";
			break;

		case 'group':
			$l_no_error = $user->lang['NO_GROUP'];
			$sql = 'SELECT group_id AS id, group_name AS name 
				FROM ' . GROUPS_TABLE . '
				WHERE group_id';
			$sql .= (is_array($ug_data)) ? ' IN (' . implode(', ', $ug_data) . ')' : ' = ' . $ug_data;
			break;
	}
	$result = $db->sql_query($sql);

	if (!$row = $db->sql_fetchrow($result))
	{
		trigger_error($l_no_error);
	}
	unset($l_no_error);
	unset($ug_data);

	// Store the user_ids and names for later use
	do 
	{
		$ug_names .= (($ug_names != '') ? ', ' : '') . $row['name'];
		$ug_ids .= (($ug_ids != '') ? ', ' : '') . $row['id'];
		$ug_hidden .= '<input type="hidden" name="ug_data[]" value="' . $row['id'] . '" />';
	}
	while ($row = $db->sql_fetchrow($result));
	$db->sql_freeresult($result);


	// Grab the list of options ... if we're in deps mode we want all options, 
	// else we skip the master options
	$sql_founder = ($user->data['user_founder']) ? ' AND founder_only <> 1' : '';
	$sql_limit_option = ($mode == 'deps') ? '' : "AND auth_option <> '" . $sql_option_mode . "_'";
	$sql = "SELECT auth_option_id, auth_option
		FROM " . ACL_OPTIONS_TABLE . "
		WHERE auth_option LIKE '" . $sql_option_mode . "_%' 
			$sql_limit_option 
			$sql_founder";
	$result = $db->sql_query($sql);

	$auth_options = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$auth_options[] = $row;
	}
	$db->sql_freeresult($result);

	unset($sql_limit_option);


	// Now we'll build a list of preset options ...
	$preset_options = $preset_js = $preset_update_options = '';
	$holding = array();

	// Do we have a parent forum? If so offer option to inherit from that
	if ($forum_data['parent_id'] != 0)
	{
		switch ($ug_type)
		{
			case 'group':
				$sql = "SELECT o.auth_option, a.auth_setting FROM " . ACL_GROUPS_TABLE . " a, " . ACL_OPTIONS_TABLE . " o WHERE o.auth_option LIKE '" . $sql_option_mode . "_%' AND a.auth_option_id = o.auth_option_id AND a.forum_id = " . $forum_data['parent_id'] . " AND a.group_id IN ($ug_ids)";
				break;

			case 'user':
				$sql = "SELECT o.auth_option, a.auth_setting FROM " . ACL_USERS_TABLE . " a, " . ACL_OPTIONS_TABLE . " o WHERE o.auth_option LIKE '" . $sql_option_mode . "_%' AND a.auth_option_id = o.auth_option_id AND a.forum_id = " . $forum_data['parent_id'] . " AND a.user_id IN ($ug_ids)";
				break;
		}
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			do
			{
				switch ($row['auth_setting'])
				{
					case ACL_YES:
						$holding['allow'] .= $row['auth_option'] . ', ';
						break;

					case ACL_NO:
						$holding['deny'] .= $row['auth_option'] . ', ';
						break;

					case ACL_UNSET:
						$holding['inherit'] .= $row['auth_option'] . ', ';
						break;
				}
			}
			while ($row = $db->sql_fetchrow($result));

			$preset_options .= '<option value="preset_0">' . $user->lang['INHERIT_PARENT'] . '</option>';
			$preset_js .= "\tpresets['preset_0'] = new Array();" . "\n";
			$preset_js .= "\tpresets['preset_0'] = new preset_obj('" . $holding['allow'] . "', '" . $holding['deny'] . "', '" . $holding['inherit'] . "');\n";
		}
		$db->sql_freeresult($result);
	}

	// Look for custom presets
	$sql = "SELECT preset_id, preset_name, preset_data  
		FROM " . ACL_PRESETS_TABLE . " 
		WHERE preset_type = '" . (($mode == 'deps') ? 'f' : $sql_option_mode) . "' 
		ORDER BY preset_id ASC";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		do
		{
			$preset_update_options .= '<option value="' . $row['preset_id'] . '">' . $row['preset_name'] . '</option>';
			$preset_options .= '<option value="preset_' . $row['preset_id'] . '">' . $row['preset_name'] . '</option>';

			$preset_data = unserialize($row['preset_data']);
			
			foreach ($preset_data as $preset_type => $preset_type_ary)
			{
				$holding[$preset_type] = '';
				foreach ($preset_type_ary as $preset_option)
				{
					$holding[$preset_type] .= "$preset_option, ";
				}
			}

			$preset_js .= "\tpresets['preset_" . $row['preset_id'] . "'] = new Array();" . "\n";
			$preset_js .= "\tpresets['preset_" . $row['preset_id'] . "'] = new preset_obj('" . $holding['yes'] . "', '" . $holding['no'] . "', '" . $holding['unset'] . "');\n";
		}
		while ($row = $db->sql_fetchrow($result));
	}
	$db->sql_freeresult($result);

	unset($holding);


	// If we aren't looking @ deps then we try and grab existing sessions for
	// the given forum and user/group
	if (empty($auth_settings[$which_mode]))
	{
		if ($which_mode == $mode)
		{
			switch ($ug_type)
			{
				case 'group':
					$sql_table = ACL_GROUPS_TABLE . ' a ';
					$sql_join = 'a.group_id';
					break;

				case 'user':
					$sql_table = ACL_USERS_TABLE . ' a, ';
					$sql_join = 'a.user_id';;
					break;
			}
		
			$sql = "SELECT o.auth_option, MIN(a.auth_setting) AS min_auth_setting 
					FROM $sql_table, " . ACL_OPTIONS_TABLE . " o 
					WHERE o.auth_option LIKE '" . $sql_option_mode . "_%' 
						AND a.auth_option_id = o.auth_option_id 
						AND a.forum_id IN ($sql_forum_id) 
						AND $sql_join IN ($ug_ids)
					GROUP BY o.auth_option";
			$result = $db->sql_query($sql);

			$auth_settings[$which_mode] = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$auth_settings[$which_mode][$row['auth_option']] = $row['min_auth_setting'];
			}
			$db->sql_freeresult($result);
		}
		else
		{
			// We're looking at a view ... so we'll set all options to ignore
			// We could be a little more clever here but the "safe side" looks
			// better right now
			$auth_settings[$which_mode] = array();
			foreach ($auth_options as $option)
			{
				$auth_settings[$which_mode][$option['auth_option']] = -1;
			}
		}
	}

	// Should we display a dropdown for views?
	$view_options = '';
	if (in_array($mode, array('admin', 'supermod', 'mod')))
	{
		$view_options .= '<option value="-1">Select view</option>';
		$view_options .= (!empty($submode) && $mode != $submode) ? '<option value="' . $mode . '">' . $user->lang['ACL_VIEW_' . strtoupper($mode)] . '</option>' : '';
		$view_ary = array(
			'admin'		=> array('forum' => 'a_auth', 'mod' => 'a_modauth', 'supermod' => 'a_modauth'),
			'supermod'	=> array('forum' => 'a_auth'), 
			'mod'		=> array('forum' => 'a_auth')
		);

		foreach ($view_ary[$mode] as $which_submode => $which_acl)
		{
			if ($auth->acl_get($which_acl))
			{
				$view_options .= '<option value="' . $which_submode . '"' . (($which_submode == $submode) ? ' selected="selected"' : '') . '>' . $user->lang['ACL_VIEW_' . strtoupper($which_submode)] . '</option>';
			}

		}
		unset($view_ary);
	}

//	print_r($auth_settings);

?>

<script language="Javascript" type="text/javascript">
<!--

	var presets = new Array();
<?php

	echo $preset_js;

?>

	function preset_obj(yes, no, unset)
	{
		this.yes = yes;
		this.no = no;
		this.unset = unset;
	}

	function use_preset(option)
	{
		if (option)
		{
			document.acl.set.selectedIndex = 0;
			for (i = 0; i < document.acl.length; i++)
			{
				var elem = document.acl.elements[i];
				if (elem.name.indexOf('settings') == 0)
				{
					switch (option)
					{
						case 'all_yes':
							if (elem.value == <?php echo ACL_YES; ?>)
								elem.checked = true;
							break;

						case 'all_no':
							if (elem.value == <?php echo ACL_NO; ?>)
								elem.checked = true;
							break;

						case 'all_unset':
							if (elem.value == <?php echo ACL_UNSET; ?>)
								elem.checked = true;
							break;

						case 'all_ignore':
							if (elem.value == -1)
								elem.checked = true;
							break;

						default:
							option_start = elem.name.search(/\[(\w+?)\]$/);
							option_name = elem.name.substr(option_start + 1, elem.name.length - option_start - 2);

							if (presets[option].yes.indexOf(option_name + ',') != -1 && elem.value == <?php echo ACL_YES; ?>)
								elem.checked = true;
							else if (presets[option].no.indexOf(option_name + ',') != -1 && elem.value == <?php echo ACL_NO; ?>)
								elem.checked = true;
							else if (presets[option].unset.indexOf(option_name + ',') != -1 && elem.value == <?php echo ACL_UNSET; ?>)
								elem.checked = true;
							break;
					}
				}
			}
		}
	}

	function marklist(match, status)
	{
		for (i = 0; i < document.acl.length; i++)
		{
			if (document.acl.elements[i].name.indexOf(match) == 0)
				document.acl.elements[i].checked = status;
		}
	}

	function open_win(url, width, height)
	{
		aclwin = window.open(url, '_phpbbacl', 'HEIGHT=' + height + ',resizable=yes, scrollbars=yes,WIDTH=' + width);
		if (window.focus)
			aclwin.focus();
	}
//-->
</script>

<p><?php echo $user->lang['ACL_EXPLAIN']; ?></p>

<h1><?php echo $l_title; ?></h1>

<p><?php echo $l_title_explain; ?></p>

<form method="post" name="acl" action="<?php echo "admin_permissions.$phpEx$SID&amp;mode=$mode&amp;submode=$submode"; ?>"><table cellspacing="2" cellpadding="0" border="0" align="center">
<?php

	// This is the main listing of options

	// We output this for both deps and when update is requested where
	// deps exist
	if (($mode == 'admin' || $mode == 'supermod') && in_array($submode, array('forum', 'mod')))
	{

?>
	<tr>
		<td colspan="2" align="right"><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0">
			<tr>
				<th colspan="2">&nbsp;</th>
			</tr>
			<tr>
				<td class="row1" width="150">Will set options in: <br /><span class="gensmall"></span></td>
				<td class="row2"><select name="f[<?php echo $which_mode; ?>][]" multiple="4"><?php 
		
		echo make_forum_select($forum_id[$which_mode], false); 
		
?></select></td>
			</tr>
		</table><br /></td>
	</tr>
<?php

	}
	// End deps output

?>
	<tr>
		<td align="left"><?php
	
	$colspan = 4;
	if ($view_options != '')
	{
		$colspan = 5;
		
?><select name="submode" onchange="if (this.options[this.selectedIndex].value != -1) this.form.submit();"><?php echo $view_options; ?></select><?php
	
	}
	
?></td>
		<td align="right"><?php echo $user->lang['PRESETS']; ?>: <select name="set" onchange="use_preset(this.options[this.selectedIndex].value);"><option class="sep"><?php echo $user->lang['SELECT'] . ' -&gt;'; ?></option><option value="all_yes"><?php echo $user->lang['ALL_YES']; ?></option><option value="all_no"><?php echo $user->lang['ALL_NO']; ?></option><option value="all_unset"><?php echo $user->lang['ALL_UNSET']; ?></option><?php 

	if ($which_mode != $mode)
	{
		echo '<option value="all_ignore">All Ignore</option>';
	}

	echo ($preset_options) ? '<option class="sep">' . $user->lang['USER_PRESETS'] . ' -&gt;' . '</option>' . $preset_options : ''; 

?></select></td>
	</tr>
	<tr>
		<td colspan="2"><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
			<tr>
				<th>&nbsp;<?php echo $user->lang['OPTION']; ?>&nbsp;</th>
				<th width="50">&nbsp;<?php echo $user->lang['YES']; ?>&nbsp;</th>
				<th width="50">&nbsp;<?php echo $user->lang['UNSET']; ?>&nbsp;</th>
				<th width="50">&nbsp;<?php echo $user->lang['NO']; ?>&nbsp;</th><?php

	if ($which_mode != $mode)
	{

?>
				<th width="50">&nbsp;<?php echo 'Ignore';?>&nbsp;</th><?php

	}

?>
			</tr>
<?php

	for($i = 0; $i < sizeof($auth_options); $i++)
	{
		$row_class = ($row_class == 'row1') ? 'row2' : 'row1';

		// Try and output correct language strings, else output prettyfied auth_option
		$l_auth_option = (!empty($user->lang['acl_' . $auth_options[$i]['auth_option']])) ? $user->lang['acl_' . $auth_options[$i]['auth_option']] : ucfirst(preg_replace('#.*?_#', '', $auth_options[$i]['auth_option']));
		$s_auth_option = '[' . $which_mode . '][' . $auth_options[$i]['auth_option'] . ']';

		
		// Which option should we select?
		$selected_yes = (isset($auth_settings[$which_mode][$auth_options[$i]['auth_option']]) && $auth_settings[$which_mode][$auth_options[$i]['auth_option']] == ACL_YES) ? ' checked="checked"' : '';
		$selected_no = (isset($auth_settings[$which_mode][$auth_options[$i]['auth_option']]) && $auth_settings[$which_mode][$auth_options[$i]['auth_option']] == ACL_NO) ? ' checked="checked"' : '';
		$selected_unset = (!isset($auth_settings[$which_mode][$auth_options[$i]['auth_option']]) || $auth_settings[$which_mode][$auth_options[$i]['auth_option']] == ACL_UNSET) ? ' checked="checked"' : '';
		$selected_ignore = (isset($auth_settings[$which_mode][$auth_options[$i]['auth_option']]) && $auth_settings[$which_mode][$auth_options[$i]['auth_option']] == -1) ? ' checked="checked"' : '';

?>
			<tr>
				<td class="<?php echo $row_class; ?>" nowrap="nowrap"><?php echo $l_auth_option; ?>&nbsp;</td>

				<td class="<?php echo $row_class; ?>" align="center"><input type="radio" name="settings<?php echo $s_auth_option ;?>" value="<?php echo ACL_YES; ?>"<?php echo $selected_yes; ?> /></td>

				<td class="<?php echo $row_class; ?>" align="center"><input type="radio" name="settings<?php echo $s_auth_option ;?>" value="<?php echo ACL_UNSET; ?>"<?php echo $selected_unset; ?> /></td>

				<td class="<?php echo $row_class; ?>" align="center"><input type="radio" name="settings<?php echo $s_auth_option ;?>" value="<?php echo ACL_NO; ?>"<?php echo $selected_no; ?> /></td><?php
					
		if ($which_mode != $mode)
		{

?>
				<td class="<?php echo $row_class; ?>" align="center"><input type="radio" name="settings<?php echo $s_auth_option ;?>" value="-1"<?php echo $selected_ignore; ?> /></td><?php

		}

?>

			</tr>
<?php

	}

	// Subforum inheritance
	if (($sql_option_mode == 'f' || ($sql_option_mode == 'm' && $mode != 'supermod')) && empty($submode))
	{
		$children = get_forum_branch($forum_id[$which_mode], 'children', 'descending', false);

		if (!empty($children))
		{

?>
			<tr>
				<th colspan="4"><?php echo $user->lang['ACL_SUBFORUMS']; ?></th>
			</tr>
			<tr>
				<td class="row1" colspan="<?php echo $colspan; ?>"><table width="100%" cellspacing="1" cellpadding="0" border="0">
					<tr>
						<td class="gensmall" colspan="4" height="16" align="center"><?php echo $user->lang['ACL_SUBFORUMS_EXPLAIN']; ?></td>
					</tr>
<?php

			foreach ($children as $row)
			{

?>
					<tr>
						<td><input type="checkbox" name="inherit[]" value="<?php echo $row['forum_id']; ?>" /> <?php echo $row['forum_name']; ?></td>
					</tr>
<?php

			}

?>
					<tr>
						<td height="16" align="center"><a class="gensmall" href="javascript:marklist('inherit', true);"><?php echo $user->lang['MARK_ALL']; ?></a> :: <a href="javascript:marklist('inherit', false);" class="gensmall"><?php echo $user->lang['UNMARK_ALL']; ?></a></td>
					</tr>
				</table></td>
			</tr>
<?php

		}
	}

	// Display event/cron radio buttons
	if ($auth->acl_gets('a_events', 'a_cron') && $mode != 'deps' && $submit != 'update')
	{
		$row_class = ($row_class == 'row1') ? 'row2' : 'row1';

?>
			<!-- tr>
				<th colspan="4"><?php echo $user->lang['RUN_HOW']; ?></th>
			</tr>
			<tr>
				<td class="<?php echo $row_class; ?>" colspan="4" align="center"><input type="radio" name="runas" value="now" checked="checked" /> <?php echo $user->lang['RUN_AS_NOW']; ?><?php 
	
			if ($auth->acl_get('a_events'))
			{ 

?> &nbsp;<input type="radio" name="runas" value="evt" /> <?php 
	
				echo $user->lang['RUN_AS_EVT'];  
			}
			
			if ($auth->acl_get('a_cron'))
			{

?> &nbsp;<input type="radio" name="runas" value="crn" /> <?php 
	
				echo $user->lang['RUN_AS_CRN']; 
				
			}

?></td>
			</tr -->
<?php

	}

?>
			<tr>
				<td class="cat" colspan="<?php echo $colspan; ?>" align="center"><input class="mainoption" type="submit" name="submit_update" value="<?php echo $user->lang['UPDATE']; ?>" />&nbsp;&nbsp;<input class="liteoption" type="submit" name="submit_cancel" value="<?php echo $user->lang['CANCEL']; ?>" /><input type="hidden" name="ug_type" value="<?php echo $ug_type; ?>" /><?php echo $ug_hidden; ?><?php 

	// Output list of forums
	foreach ($forum_id as $forum_submode => $forum_submode_ids)
	{
		foreach ($forum_submode_ids as $submode_forum_id)
		{
			echo '<input type="hidden" name="f[' . $forum_submode . '][]" value="' . $submode_forum_id . '" />';
		}
	}
	unset($forum_submode_ids);
	unset($forum_submode);
	unset($submode_forum_id);

	// Output original settings ... needed when we jump views
	foreach ($auth_settings as $auth_submode => $auth_submode_settings)
	{
		if ($auth_submode != $which_mode)
		{
			foreach ($auth_submode_settings as $submode_option => $submode_setting)
			{
				echo '<input type="hidden" name="settings[' . $auth_submode . '][' . $submode_option . ']" value="' . $submode_setting . '" />';
			}
		}
	}
	unset($auth_submode);
	unset($auth_submode_settings);
	unset($auth_submode_option);
	unset($auth_submode_setting);
	
?></td>
			</tr>
		</table>

		<br clear="all" />

		<table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
			<tr>
				<th colspan="4"><?php echo $user->lang['PRESETS']; ?></th>
			</tr>
			<tr>
				<td class="row1" colspan="4"><table width="100%" cellspacing="1" cellpadding="0" border="0">
					<tr>
						<td colspan="2" height="16"><span class="gensmall"><?php echo $user->lang['PRESETS_EXPLAIN']; ?></span></td>
					</tr>
					<tr>
						<td nowrap="nowrap"><?php echo $user->lang['SELECT_PRESET']; ?>: </td>
						<td><select name="presetoption"><option class="sep" value="-1"><?php echo $user->lang['SELECT'] . ' -&gt;'; ?></option><?php 

	echo $preset_update_options;
			
		?></select></td>
					</tr>
					<tr>
						<td nowrap="nowrap"><?php echo $user->lang['PRESET_NAME']; ?>: </td>
						<td><input type="text" name="presetname" maxlength="25" /> </td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td class="cat" colspan="4" align="center"><input class="liteoption" type="submit" name="submit_presetsave" value="<?php echo $user->lang['SAVE']; ?>" /> &nbsp;<input class="liteoption" type="submit" name="submit_presetdel" value="<?php echo $user->lang['DELETE']; ?>" /></td>
			</tr>
		</table></td>
	</tr>
</table></form>

<?php

}

// Output page footer
page_footer();

?>