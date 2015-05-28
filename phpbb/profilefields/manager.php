<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\profilefields;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
* Custom Profile Fields
*/
class manager
{
	/**
	* Container interface
	* @var ContainerInterface
	*/
	protected $container;

	/**
	* Auth object
	* @var \phpbb\auth\auth
	*/
	protected $auth;

	/**
	* Database object
	* @var \phpbb\db\driver\driver_interface
	*/
	protected $db;

	/**
	* Event dispatcher object
	* @var \phpbb\event\dispatcher_interface
	*/
	protected $dispatcher;

	/**
	* Request object
	* @var \phpbb\request\request
	*/
	protected $request;

	/**
	* Template object
	* @var \phpbb\template\template
	*/
	protected $template;

	/**
	* Service Collection object
	* @var \phpbb\di\service_collection
	*/
	protected $type_collection;

	/**
	* User object
	* @var \phpbb\user
	*/
	protected $user;

	protected $fields_table;

	protected $fields_language_table;

	protected $fields_data_table;

	protected $profile_cache = array();

	protected $config_text;

	protected $db_tools;

	/**
	* Construct
	*
	* @param	ContainerInterface $container A container
	* @param	\phpbb\auth\auth			$auth		Auth object
	* @param	\phpbb\db\driver\driver_interface	$db			Database object
	* @param	\phpbb\event\dispatcher_interface		$dispatcher	Event dispatcher object
	* @param	\phpbb\request\request		$request	Request object
	* @param	\phpbb\template\template	$template	Template object
	* @param	\phpbb\di\service_collection $type_collection
	* @param	\phpbb\user					$user		User object
	* @param	string				$fields_table
	* @param	string				$fields_language_table
	* @param	string				$fields_data_table
	*/
	public function __construct(ContainerInterface $container, \phpbb\auth\auth $auth, \phpbb\db\driver\driver_interface $db, \phpbb\event\dispatcher_interface $dispatcher, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\di\service_collection $type_collection, \phpbb\user $user, $fields_table, $fields_language_table, $fields_data_table)
	{
		$this->container = $container;
		$this->auth = $auth;
		$this->db = $db;
		$this->dispatcher = $dispatcher;
		$this->request = $request;
		$this->template = $template;
		$this->type_collection = $type_collection;
		$this->user = $user;

		$this->fields_table = $fields_table;
		$this->fields_language_table = $fields_language_table;
		$this->fields_data_table = $fields_data_table;

		$this->config_text = $this->container->get('config_text');
		$this->db_tools = $this->container->get('dbal.tools');
	}

	/**
	* Assign editable fields to template, mode can be profile (for profile change) or register (for registration)
	* Called by ucp_profile and ucp_register
	*/
	public function generate_profile_fields($mode, $lang_id)
	{
		$sql_where = '';
		switch ($mode)
		{
			case 'register':
				// If the field is required we show it on the registration page
				$sql_where .= ' AND f.field_show_on_reg = 1';
			break;

			case 'profile':
				// Show hidden fields to moderators/admins
				if (!$this->auth->acl_gets('a_', 'm_') && !$this->auth->acl_getf_global('m_'))
				{
					$sql_where .= ' AND f.field_show_profile = 1';
				}
			break;

			default:
				trigger_error('Wrong profile mode specified', E_USER_ERROR);
			break;
		}

		$sql = 'SELECT l.*, f.*
			FROM ' . $this->fields_language_table . ' l, ' . $this->fields_table . " f
			WHERE f.field_active = 1
				$sql_where
				AND l.lang_id = " . (int) $lang_id . '
				AND l.field_id = f.field_id
			ORDER BY f.field_order';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			// Return templated field
			$profile_field = $this->type_collection[$row['field_type']];
			$tpl_snippet = $profile_field->process_field_row('change', $row);

			$this->template->assign_block_vars('profile_fields', array(
				'LANG_NAME'		=> $this->user->lang($row['lang_name']),
				'LANG_EXPLAIN'	=> $this->user->lang($row['lang_explain']),
				'FIELD'			=> $tpl_snippet,
				'FIELD_ID'		=> $profile_field->get_field_ident($row),
				'S_REQUIRED'	=> ($row['field_required']) ? true : false,
			));
		}
		$this->db->sql_freeresult($result);
	}

	/**
	* Build profile cache, used for display
	*/
	protected function build_cache()
	{
		$this->profile_cache = array();

		// Display hidden/no_view fields for admin/moderator
		$sql = 'SELECT l.*, f.*
			FROM ' . $this->fields_language_table . ' l, ' . $this->fields_table . ' f
			WHERE l.lang_id = ' . $this->user->get_iso_lang_id() . '
				AND f.field_active = 1 ' .
				((!$this->auth->acl_gets('a_', 'm_') && !$this->auth->acl_getf_global('m_')) ? '	AND f.field_hide = 0 ' : '') . '
				AND f.field_no_view = 0
				AND l.field_id = f.field_id
			ORDER BY f.field_order';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->profile_cache[$row['field_ident']] = $row;
		}
		$this->db->sql_freeresult($result);
	}

	/**
	* Submit profile field for validation
	*/
	public function submit_cp_field($mode, $lang_id, &$cp_data, &$cp_error)
	{
		$sql_where = '';
		switch ($mode)
		{
			case 'register':
				// If the field is required we show it on the registration page
				$sql_where .= ' AND f.field_show_on_reg = 1';
			break;

			case 'profile':
				// Show hidden fields to moderators/admins
				if (!$this->auth->acl_gets('a_', 'm_') && !$this->auth->acl_getf_global('m_'))
				{
					$sql_where .= ' AND f.field_show_profile = 1';
				}
			break;

			default:
				trigger_error('Wrong profile mode specified', E_USER_ERROR);
			break;
		}

		$sql = 'SELECT l.*, f.*
			FROM ' . $this->fields_language_table . ' l, ' . $this->fields_table . ' f
			WHERE l.lang_id = ' . (int) $lang_id . "
				AND f.field_active = 1
				$sql_where
				AND l.field_id = f.field_id
			ORDER BY f.field_order";
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$profile_field = $this->type_collection[$row['field_type']];
			$cp_data['pf_' . $row['field_ident']] = $profile_field->get_profile_field($row);
			$check_value = $cp_data['pf_' . $row['field_ident']];

			if (($cp_result = $profile_field->validate_profile_field($check_value, $row)) !== false)
			{
				// If the result is not false, it's an error message
				$cp_error[] = $cp_result;
			}
		}
		$this->db->sql_freeresult($result);
	}

	/**
	* Update profile field data directly
	*/
	public function update_profile_field_data($user_id, $cp_data)
	{
		if (!count($cp_data))
		{
			return;
		}

		$sql = 'UPDATE ' . $this->fields_data_table . '
			SET ' . $this->db->sql_build_array('UPDATE', $cp_data) . '
			WHERE user_id = ' . (int) $user_id;
		$this->db->sql_query($sql);

		if (!$this->db->sql_affectedrows())
		{
			$cp_data = $this->build_insert_sql_array($cp_data);
			$cp_data['user_id'] = (int) $user_id;

			$sql = 'INSERT INTO ' . $this->fields_data_table . ' ' . $this->db->sql_build_array('INSERT', $cp_data);
			$this->db->sql_query($sql);
		}
	}

	/**
	* Generate the template arrays in order to display the column names
	*
	* @param string	$restrict_option	Restrict the published fields to a certain profile field option
	* @return array		Returns an array with the template variables type, name and explain for the fields to display
	*/
	public function generate_profile_fields_template_headlines($restrict_option = '')
	{
		if (!count($this->profile_cache))
		{
			$this->build_cache();
		}

		$tpl_fields = array();

		// Go through the fields in correct order
		foreach ($this->profile_cache as $field_ident => $field_data)
		{
			if ($restrict_option && !$field_data[$restrict_option])
			{
				continue;
			}

			$profile_field = $this->type_collection[$field_data['field_type']];

			$tpl_fields[] = array(
				'PROFILE_FIELD_IDENT'	=> $field_ident,
				'PROFILE_FIELD_TYPE'	=> $field_data['field_type'],
				'PROFILE_FIELD_NAME'	=> $profile_field->get_field_name($field_data['lang_name']),
				'PROFILE_FIELD_EXPLAIN'	=> $this->user->lang($field_data['lang_explain']),
			);
		}

		$profile_cache = $this->profile_cache;

		/**
		* Event to modify template headlines of the generated profile fields
		*
		* @event core.generate_profile_fields_template_headlines
		* @var	string	restrict_option	Restrict the published fields to a certain profile field option
		* @var	array	tpl_fields		Array with template data fields
		* @var	array	profile_cache	A copy of the profile cache to make additional checks
		* @since 3.1.6-RC1
		*/
		$vars = array(
			'restrict_option',
			'tpl_fields',
			'profile_cache',
		);
		extract($this->dispatcher->trigger_event('core.generate_profile_fields_template_headlines', compact($vars)));
		unset($profile_cache);

		return $tpl_fields;
	}

	/**
	* Grab the user specific profile fields data
	*
	* @param	int|array	$user_ids	Single user id or an array of ids
	* @return array		Users profile fields data
	*/
	public function grab_profile_fields_data($user_ids = 0)
	{
		if (!is_array($user_ids))
		{
			$user_ids = array($user_ids);
		}

		if (!count($this->profile_cache))
		{
			$this->build_cache();
		}

		if (!count($user_ids))
		{
			return array();
		}

		$sql = 'SELECT *
			FROM ' . $this->fields_data_table . '
			WHERE ' . $this->db->sql_in_set('user_id', array_map('intval', $user_ids));
		$result = $this->db->sql_query($sql);

		$field_data = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$field_data[$row['user_id']] = $row;
		}
		$this->db->sql_freeresult($result);

		/**
		* Event to modify profile fields data retrieved from the database
		*
		* @event core.grab_profile_fields_data
		* @var	array	user_ids		Single user id or an array of ids
		* @var	array	field_data		Array with profile fields data
		* @since 3.1.0-b3
		*/
		$vars = array('user_ids', 'field_data');
		extract($this->dispatcher->trigger_event('core.grab_profile_fields_data', compact($vars)));

		$user_fields = array();

		// Go through the fields in correct order
		foreach (array_keys($this->profile_cache) as $used_ident)
		{
			foreach ($field_data as $user_id => $row)
			{
				$user_fields[$user_id][$used_ident]['value'] = $row['pf_' . $used_ident];
				$user_fields[$user_id][$used_ident]['data'] = $this->profile_cache[$used_ident];
			}

			foreach ($user_ids as $user_id)
			{
				if (!isset($user_fields[$user_id][$used_ident]))
				{
					$user_fields[$user_id][$used_ident]['value'] = '';
					$user_fields[$user_id][$used_ident]['data'] = $this->profile_cache[$used_ident];
				}
			}
		}

		return $user_fields;
	}

	/**
	* Assign the user's profile fields data to the template
	*
	* @param array	$profile_row		Array with users profile field data
	* @param bool	$use_contact_fields	Should we display contact fields as such?
	*			This requires special treatments (links should not be parsed in the values, and more)
	* @return array
	*/
	public function generate_profile_fields_template_data($profile_row, $use_contact_fields = true)
	{
		// $profile_row == $user_fields[$row['user_id']];
		$tpl_fields = array();
		$tpl_fields['row'] = $tpl_fields['blockrow'] = array();

		/**
		* Event to modify data of the generated profile fields, before the template assignment loop
		*
		* @event core.generate_profile_fields_template_data_before
		* @var	array	profile_row		Array with users profile field data
		* @var	array	tpl_fields		Array with template data fields
		* @var	bool	use_contact_fields	Should we display contact fields as such?
		* @since 3.1.0-b3
		*/
		$vars = array('profile_row', 'tpl_fields', 'use_contact_fields');
		extract($this->dispatcher->trigger_event('core.generate_profile_fields_template_data_before', compact($vars)));

		foreach ($profile_row as $ident => $ident_ary)
		{
			$profile_field = $this->type_collection[$ident_ary['data']['field_type']];
			$value = $profile_field->get_profile_value($ident_ary['value'], $ident_ary['data']);
			$value_raw = $profile_field->get_profile_value_raw($ident_ary['value'], $ident_ary['data']);

			if ($value === null)
			{
				continue;
			}

			$field_desc = $contact_url = '';
			if ($use_contact_fields && $ident_ary['data']['field_is_contact'])
			{
				$value = $profile_field->get_profile_contact_value($ident_ary['value'], $ident_ary['data']);
				$field_desc = $this->user->lang($ident_ary['data']['field_contact_desc']);
				if (strpos($field_desc, '%s') !== false)
				{
					$field_desc = sprintf($field_desc, $value);
				}
				$contact_url = '';
				if (strpos($ident_ary['data']['field_contact_url'], '%s') !== false)
				{
					$contact_url = sprintf($ident_ary['data']['field_contact_url'], $value);
				}
			}

			$tpl_fields['row'] += array(
				'PROFILE_' . strtoupper($ident) . '_IDENT'		=> $ident,
				'PROFILE_' . strtoupper($ident) . '_VALUE'		=> $value,
				'PROFILE_' . strtoupper($ident) . '_VALUE_RAW'	=> $value_raw,
				'PROFILE_' . strtoupper($ident) . '_CONTACT'	=> $contact_url,
				'PROFILE_' . strtoupper($ident) . '_DESC'		=> $field_desc,
				'PROFILE_' . strtoupper($ident) . '_TYPE'		=> $ident_ary['data']['field_type'],
				'PROFILE_' . strtoupper($ident) . '_NAME'		=> $this->user->lang($ident_ary['data']['lang_name']),
				'PROFILE_' . strtoupper($ident) . '_EXPLAIN'	=> $this->user->lang($ident_ary['data']['lang_explain']),

				'S_PROFILE_' . strtoupper($ident) . '_CONTACT'	=> $ident_ary['data']['field_is_contact'],
				'S_PROFILE_' . strtoupper($ident)			=> true,
			);

			$tpl_fields['blockrow'][] = array(
				'PROFILE_FIELD_IDENT'		=> $ident,
				'PROFILE_FIELD_VALUE'		=> $value,
				'PROFILE_FIELD_VALUE_RAW'	=> $value_raw,
				'PROFILE_FIELD_CONTACT'		=> $contact_url,
				'PROFILE_FIELD_DESC'		=> $field_desc,
				'PROFILE_FIELD_TYPE'		=> $ident_ary['data']['field_type'],
				'PROFILE_FIELD_NAME'		=> $this->user->lang($ident_ary['data']['lang_name']),
				'PROFILE_FIELD_EXPLAIN'		=> $this->user->lang($ident_ary['data']['lang_explain']),

				'S_PROFILE_CONTACT'						=> $ident_ary['data']['field_is_contact'],
				'S_PROFILE_' . strtoupper($ident)		=> true,
			);
		}

		/**
		* Event to modify template data of the generated profile fields
		*
		* @event core.generate_profile_fields_template_data
		* @var	array	profile_row		Array with users profile field data
		* @var	array	tpl_fields		Array with template data fields
		* @var	bool	use_contact_fields	Should we display contact fields as such?
		* @since 3.1.0-b3
		*/
		$vars = array('profile_row', 'tpl_fields', 'use_contact_fields');
		extract($this->dispatcher->trigger_event('core.generate_profile_fields_template_data', compact($vars)));

		return $tpl_fields;
	}

	/**
	* Build Array for user insertion into custom profile fields table
	*/
	public function build_insert_sql_array($cp_data)
	{
		$sql_not_in = array();
		foreach ($cp_data as $key => $null)
		{
			$sql_not_in[] = (strncmp($key, 'pf_', 3) === 0) ? substr($key, 3) : $key;
		}

		$sql = 'SELECT f.field_type, f.field_ident, f.field_default_value, l.lang_default_value
			FROM ' . $this->fields_language_table . ' l, ' . $this->fields_table . ' f
			WHERE l.lang_id = ' . $this->user->get_iso_lang_id() . '
				' . ((count($sql_not_in)) ? ' AND ' . $this->db->sql_in_set('f.field_ident', $sql_not_in, true) : '') . '
				AND l.field_id = f.field_id';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$profile_field = $this->type_collection[$row['field_type']];
			$cp_data['pf_' . $row['field_ident']] = $profile_field->get_default_field_value($row);
		}
		$this->db->sql_freeresult($result);

		return $cp_data;
	}

	/**
	* Disable all profile fields of a certain type
	*
	* This should be called when an extension which has profile field types
	* is disabled so that all those profile fields are hidden and do not
	* cause errors
	*
	* @param string $profilefield_type_name Type identifier of the profile fields
	*/
	public function disable_profilefields($profilefield_type_name)
	{
		// Get list of profile fields affected by this operation, if any
		$pfs = array();
		$sql = 'SELECT field_id, field_ident
			FROM ' . PROFILE_FIELDS_TABLE . "
			WHERE field_active = 1
				AND field_type = '" . $this->db->sql_escape($profilefield_type_name) . "'";
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$pfs[(int) $row['field_id']] = $row['field_ident'];
		}
		$this->db->sql_freeresult($result);

		// If no profile fields affected, then nothing to do
		if (!sizeof($pfs))
		{
			return;
		}

		// Update the affected profile fields to "inactive"
		$sql = 'UPDATE ' . PROFILE_FIELDS_TABLE . "
			SET field_active = 0
			WHERE field_active = 1
				AND field_type = '" . $this->db->sql_escape($profilefield_type_name) . "'";
		$this->db->sql_query($sql);

		// Save modified information into a config_text field to recover on enable
		$this->config_text->set($profilefield_type_name . '.saved', base64_encode(serialize($pfs)));

		// Log activity
		foreach ($pfs as $field_ident)
		{
			add_log('admin', 'LOG_PROFILE_FIELD_DEACTIVATE', $field_ident);
		}
	}

	/**
	* Purge all profile fields of a certain type
	*
	* This should be called when an extension which has profile field types
	* is purged so that all those profile fields are removed
	*
	* @param string $profilefield_type_name Type identifier of the profile fields
	*/
	public function purge_profilefields($profilefield_type_name)
	{
		// Remove the information saved on disable in a config_text field, not needed any longer
		$this->config_text->delete($profilefield_type_name . '.saved');

		// Get list of profile fields affected by this operation, if any
		$pfs = array();
		$sql = 'SELECT field_id, field_ident
			FROM ' . PROFILE_FIELDS_TABLE . "
			WHERE field_type = '" . $this->db->sql_escape($profilefield_type_name) . "'";
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$pfs[(int) $row['field_id']] = $row['field_ident'];
		}
		$this->db->sql_freeresult($result);

		// If no profile fields exist, then nothing to do
		if (!sizeof($pfs))
		{
			return;
		}

		$this->db->sql_transaction('begin');

		// Delete entries from all profile field definition tables
		$where = $this->db->sql_in_set('field_id', array_keys($pfs));
		$this->db->sql_query('DELETE FROM ' . PROFILE_FIELDS_TABLE . ' WHERE ' . $where);
		$this->db->sql_query('DELETE FROM ' . PROFILE_FIELDS_LANG_TABLE . ' WHERE ' . $where);
		$this->db->sql_query('DELETE FROM ' . PROFILE_LANG_TABLE . ' WHERE ' . $where);

		// Drop columns from the Profile Fields data table
		foreach ($pfs as $field_ident)
		{
			$this->db_tools->sql_column_remove(PROFILE_FIELDS_DATA_TABLE, 'pf_' . $field_ident);
		}

		// Reset the order of the remaining fields
		$order = 0;

		$sql = 'SELECT *
			FROM ' . PROFILE_FIELDS_TABLE . '
			ORDER BY field_order';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$order++;
			if ($row['field_order'] != $order)
			{
				$sql = 'UPDATE ' . PROFILE_FIELDS_TABLE . "
					SET field_order = $order
					WHERE field_id = {$row['field_id']}";
				$this->db->sql_query($sql);
			}
		}
		$this->db->sql_freeresult($result);

		$this->db->sql_transaction('commit');

		// Log activity
		foreach ($pfs as $field_ident)
		{
			add_log('admin', 'LOG_PROFILE_FIELD_REMOVED', $field_ident);
		}
	}

	/**
	* Enable the profile fields of a certain type
	*
	* This should be called when an extension which has profile field types
	* that was disabled is re-enabled so that all those profile fields that
	* were disabled are enabled again
	*
	* @param string $profilefield_type_name Type identifier of the profile fields
	*/
	public function enable_profilefields($profilefield_type_name)
	{
		// Read the modified information saved on disable from a config_text field to recover values, then remove it
		$pfs = $this->config_text->get($profilefield_type_name . '.saved');
		$this->config_text->delete($profilefield_type_name . '.saved');

		// If nothing saved, then nothing to do
		if ($pfs == '')
		{
			return;
		}

		$pfs = unserialize(base64_decode($pfs));

		// Restore the affected profile fields to "active"
		$sql = 'UPDATE ' . PROFILE_FIELDS_TABLE . "
			SET field_active = 1
			WHERE field_active = 0
				AND " . $this->db->sql_in_set('field_id', array_keys($pfs));
		$this->db->sql_query($sql);
		foreach ($pfs as $field_ident)
		{
			add_log('admin', 'LOG_PROFILE_FIELD_ACTIVATE', $field_ident);
		}
	}
}
