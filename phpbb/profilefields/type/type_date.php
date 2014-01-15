<?php
/**
*
* @package phpBB
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\profilefields\type;

class type_date implements type_interface
{
	/**
	*
	*/
	public function __construct(\phpbb\profilefields\profilefields $profilefields, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->profilefields = $profilefields;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_options($default_lang_id, $field_data)
	{
		$profile_row = array(
			'var_name'				=> 'field_default_value',
			'lang_name'				=> $field_data['lang_name'],
			'lang_explain'			=> $field_data['lang_explain'],
			'lang_id'				=> $default_lang_id,
			'field_default_value'	=> $field_data['field_default_value'],
			'field_ident'			=> 'field_default_value',
			'field_type'			=> FIELD_DATE,
			'field_length'			=> $field_data['field_length'],
		);

		$always_now = request_var('always_now', -1);
		if ($always_now == -1)
		{
			$s_checked = ($field_data['field_default_value'] == 'now') ? true : false;
		}
		else
		{
			$s_checked = ($always_now) ? true : false;
		}

		$options = array(
			0 => array('TITLE' => $this->user->lang['DEFAULT_VALUE'],	'FIELD' => $this->profilefields->process_field_row('preview', $profile_row)),
			1 => array('TITLE' => $this->user->lang['ALWAYS_TODAY'],	'FIELD' => '<label><input type="radio" class="radio" name="always_now" value="1"' . (($s_checked) ? ' checked="checked"' : '') . ' onchange="document.getElementById(\'add_profile_field\').submit();" /> ' . $this->user->lang['YES'] . '</label><label><input type="radio" class="radio" name="always_now" value="0"' . ((!$s_checked) ? ' checked="checked"' : '') . ' onchange="document.getElementById(\'add_profile_field\').submit();" /> ' . $this->user->lang['NO'] . '</label>'),
		);

		return $options;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_default_option_values()
	{
		return array(
			'field_length'		=> 10,
			'field_minlen'		=> 10,
			'field_maxlen'		=> 10,
			'field_validation'	=> '',
			'field_novalue'		=> ' 0- 0-   0',
			'field_default_value'	=> ' 0- 0-   0',
		);
	}

	/**
	* {@inheritDoc}
	*/
	public function get_default_field_value($field_data)
	{
		if ($field_data['field_default_value'] == 'now')
		{
			$now = getdate();
			$field_data['field_default_value'] = sprintf('%2d-%2d-%4d', $now['mday'], $now['mon'], $now['year']);
		}

		return $field_data['field_default_value'];
	}

	/**
	* {@inheritDoc}
	*/
	public function get_profile_field($profile_row)
	{
		$var_name = 'pf_' . $profile_row['field_ident'];

		if (!$this->request->is_set($var_name . '_day'))
		{
			if ($profile_row['field_default_value'] == 'now')
			{
				$now = getdate();
				$profile_row['field_default_value'] = sprintf('%2d-%2d-%4d', $now['mday'], $now['mon'], $now['year']);
			}
			list($day, $month, $year) = explode('-', $profile_row['field_default_value']);
		}
		else
		{
			$day = $this->request->variable($var_name . '_day', 0);
			$month = $this->request->variable($var_name . '_month', 0);
			$year = $this->request->variable($var_name . '_year', 0);
		}

		return sprintf('%2d-%2d-%4d', $day, $month, $year);
	}

	/**
	* {@inheritDoc}
	*/
	public function validate_profile_field(&$field_value, $field_data)
	{
		$field_validate = explode('-', $field_value);

		$day = (isset($field_validate[0])) ? (int) $field_validate[0] : 0;
		$month = (isset($field_validate[1])) ? (int) $field_validate[1] : 0;
		$year = (isset($field_validate[2])) ? (int) $field_validate[2] : 0;

		if ((!$day || !$month || !$year) && !$field_data['field_required'])
		{
			return false;
		}

		if ((!$day || !$month || !$year) && $field_data['field_required'])
		{
			return $this->user->lang('FIELD_REQUIRED', $field_data['lang_name']);
		}

		if ($day < 0 || $day > 31 || $month < 0 || $month > 12 || ($year < 1901 && $year > 0) || $year > gmdate('Y', time()) + 50)
		{
			return $this->user->lang('FIELD_INVALID_DATE', $field_data['lang_name']);
		}

		if (checkdate($month, $day, $year) === false)
		{
			return $this->user->lang('FIELD_INVALID_DATE', $field_data['lang_name']);
		}

		return false;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_profile_value($field_value, $field_data)
	{
		$date = explode('-', $field_value);
		$day = (isset($date[0])) ? (int) $date[0] : 0;
		$month = (isset($date[1])) ? (int) $date[1] : 0;
		$year = (isset($date[2])) ? (int) $date[2] : 0;

		if (!$day && !$month && !$year && !$field_data['field_show_novalue'])
		{
			return null;
		}
		else if ($day && $month && $year)
		{
			// Date should display as the same date for every user regardless of timezone
			return $this->user->create_datetime()
				->setDate($year, $month, $day)
				->setTime(0, 0, 0)
				->format($user->lang['DATE_FORMAT'], true);
		}

		return $field_value;
	}

	/**
	* {@inheritDoc}
	*/
	public function generate_field($profile_row, $preview_options = false)
	{
		$profile_row['field_ident'] = (isset($profile_row['var_name'])) ? $profile_row['var_name'] : 'pf_' . $profile_row['field_ident'];

		$now = getdate();

		if (!$this->request->is_set($profile_row['field_ident'] . '_day'))
		{
			if ($profile_row['field_default_value'] == 'now')
			{
				$profile_row['field_default_value'] = sprintf('%2d-%2d-%4d', $now['mday'], $now['mon'], $now['year']);
			}
			list($day, $month, $year) = explode('-', ((!isset($this->user->profile_fields[$user_ident]) || $preview_options !== false) ? $profile_row['field_default_value'] : $this->user->profile_fields[$user_ident]));
		}
		else
		{
			if ($preview_options !== false && $profile_row['field_default_value'] == 'now')
			{
				$profile_row['field_default_value'] = sprintf('%2d-%2d-%4d', $now['mday'], $now['mon'], $now['year']);
				list($day, $month, $year) = explode('-', ((!isset($this->user->profile_fields[$user_ident]) || $preview_options !== false) ? $profile_row['field_default_value'] : $this->user->profile_fields[$user_ident]));
			}
			else
			{
				$day = $this->request->variable($profile_row['field_ident'] . '_day', 0);
				$month = $this->request->variable($profile_row['field_ident'] . '_month', 0);
				$year = $this->request->variable($profile_row['field_ident'] . '_year', 0);
			}
		}

		$profile_row['s_day_options'] = '<option value="0"' . ((!$day) ? ' selected="selected"' : '') . '>--</option>';
		for ($i = 1; $i < 32; $i++)
		{
			$profile_row['s_day_options'] .= '<option value="' . $i . '"' . (($i == $day) ? ' selected="selected"' : '') . ">$i</option>";
		}

		$profile_row['s_month_options'] = '<option value="0"' . ((!$month) ? ' selected="selected"' : '') . '>--</option>';
		for ($i = 1; $i < 13; $i++)
		{
			$profile_row['s_month_options'] .= '<option value="' . $i . '"' . (($i == $month) ? ' selected="selected"' : '') . ">$i</option>";
		}

		$profile_row['s_year_options'] = '<option value="0"' . ((!$year) ? ' selected="selected"' : '') . '>--</option>';
		for ($i = $now['year'] - 100; $i <= $now['year'] + 100; $i++)
		{
			$profile_row['s_year_options'] .= '<option value="' . $i . '"' . (($i == $year) ? ' selected="selected"' : '') . ">$i</option>";
		}

		$profile_row['field_value'] = 0;
		$this->template->assign_block_vars('date', array_change_key_case($profile_row, CASE_UPPER));
	}

	/**
	* {@inheritDoc}
	*/
	public function get_field_ident($field_data)
	{
		return '';
	}

	/**
	* {@inheritDoc}
	*/
	public function get_database_column_type()
	{
		return 'VCHAR:10';
	}

	/**
	* {@inheritDoc}
	*/
	public function get_language_options($field_data)
	{
		$options = array(
			'lang_name' => 'string',
		);

		if ($field_data['lang_explain'])
		{
			$options['lang_explain'] = 'text';
		}

		return $options;
	}
}
