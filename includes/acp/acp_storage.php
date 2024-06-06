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

use phpbb\config\db as config;
use phpbb\config\db_text as config_text;
use phpbb\db\driver\driver_interface;
use phpbb\di\service_collection;
use phpbb\language\language;
use phpbb\log\log_interface;
use phpbb\path_helper;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class acp_storage
{
	/** @var config $config */
	protected $config;

	/** @var config_text $config_text */
	protected $config_text;

	/** @var driver_interface $db */
	protected $db;

	/** @var language $log */
	protected $lang;

	/** @var log_interface $log */
	protected $log;

	/** @var path_helper $path_helper */
	protected $path_helper;

	/** @var request */
	protected $request;

	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/** @var service_collection */
	protected $adapter_collection;

	/** @var service_collection */
	protected $provider_collection;

	/** @var service_collection */
	protected $storage_collection;

	/** @var \phpbb\filesystem\filesystem */
	protected $filesystem;

	/** @var string */
	public $page_title;

	/** @var string */
	public $phpbb_root_path;

	/** @var string */
	public $tpl_name;

	/** @var string */
	public $u_action;

	/** @var mixed */
	protected $state;

	/**
	 * @param string $id
	 * @param string $mode
	 */
	public function main(string $id, string $mode)
	{
		global $phpbb_container, $phpbb_dispatcher, $phpbb_root_path;

		$this->config = $phpbb_container->get('config');
		$this->config_text = $phpbb_container->get('config_text');
		$this->db = $phpbb_container->get('dbal.conn');
		$this->filesystem = $phpbb_container->get('filesystem');
		$this->lang = $phpbb_container->get('language');
		$this->log = $phpbb_container->get('log');
		$this->path_helper = $phpbb_container->get('path_helper');
		$this->request = $phpbb_container->get('request');
		$this->template = $phpbb_container->get('template');
		$this->user = $phpbb_container->get('user');
		$this->adapter_collection = $phpbb_container->get('storage.adapter_collection');
		$this->provider_collection = $phpbb_container->get('storage.provider_collection');
		$this->storage_collection = $phpbb_container->get('storage.storage_collection');
		$this->phpbb_root_path = $phpbb_root_path;

		// Add necesary language files
		$this->lang->add_lang(['acp/storage']);

		/**
		 * Add language strings
		 *
		 * @event core.acp_storage_load
		 * @since 3.3.0-a1
		 */
		$phpbb_dispatcher->trigger_event('core.acp_storage_load');

		@ini_set('memory_limit', '128M');

		switch ($mode)
		{
			case 'settings':
				$this->settings($id, $mode);
			break;
		}
	}

	/**
	 * @param string $id
	 * @param string $mode
	 */
	public function settings(string $id, string $mode)
	{
		$form_key = 'acp_storage';
		add_form_key($form_key);

		// Template from adm/style
		$this->tpl_name = 'acp_storage';

		// Set page title
		$this->page_title = 'STORAGE_TITLE';

		$action = $this->request->variable('action', '');
		$this->load_state();

		// If user cancelled to continue, remove state
		if ($this->request->is_set_post('cancel'))
		{
			if (!check_form_key($form_key) || !check_link_hash($this->request->variable('hash', ''), 'acp_storage'))
			{
				trigger_error($this->lang->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			if ($this->request->variable('cancel', false))
			{
				$action = '';
				$this->state = false;
				$this->save_state();
			}
		}

		if ($action)
		{
			if ($action == 'progress_bar')
			{
				$this->display_progress_bar();
			}
			else if ($action != 'update')
			{
				trigger_error('NO_ACTION', E_USER_ERROR);
			}

			if (!check_link_hash($this->request->variable('hash', ''), 'acp_storage'))
			{
				trigger_error($this->lang->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			// If update_type is copy or move, copy files from the old to the new storage
			if ($this->state['update_type'] >= 1)
			{
				$i = 0;
				foreach ($this->state['storages'] as $storage_name => $storage_options)
				{
					// Skip storages that have already moved files
					if ($this->state['storage_index'] > $i)
					{
						$i++;
						continue;
					}

					$current_adapter = $this->get_current_adapter($storage_name);
					$new_adapter = $this->get_new_adapter($storage_name);

					$sql = 'SELECT file_id, file_path
						FROM ' . STORAGE_TABLE . "
						WHERE  storage = '" . $this->db->sql_escape($storage_name) . "'
							AND file_id > " . (int) $this->state['file_index'];
					$result = $this->db->sql_query($sql);

					while ($row = $this->db->sql_fetchrow($result))
					{
						if (!still_on_time())
						{
							$this->save_state();
							meta_refresh(1, append_sid($this->u_action . '&amp;action=update&amp;hash=' . generate_link_hash('acp_storage')));
							trigger_error($this->lang->lang('STORAGE_UPDATE_REDIRECT', $this->lang->lang('STORAGE_' . strtoupper($storage_name) . '_TITLE'), $i + 1, count($this->state['storages'])));
						}

						$stream = $current_adapter->read_stream($row['file_path']);
						$new_adapter->write_stream($row['file_path'], $stream);

						if (is_resource($stream))
						{
							fclose($stream);
						}

						$this->state['file_index'] = $row['file_id']; // Set last uploaded file
					}

					// Copied all files of a storage, increase storage index and reset file index
					$this->state['storage_index']++;
					$this->state['file_index'] = 0;
				}

				// If update_type is move files, remove the old files
				if ($this->state['update_type'] == 2)
				{
					$i = 0;
					foreach ($this->state['storages'] as $storage_name => $storage_options)
					{
						// Skip storages that have already moved files
						if ($this->state['remove_storage_index'] > $i)
						{
							$i++;
							continue;
						}

						$current_adapter = $this->get_current_adapter($storage_name);

						$sql = 'SELECT file_id, file_path
							FROM ' . STORAGE_TABLE . "
							WHERE  storage = '" . $this->db->sql_escape($storage_name) . "'
								AND file_id > " . (int) $this->state['file_index'];
						$result = $this->db->sql_query($sql);

						while ($row = $this->db->sql_fetchrow($result))
						{
							if (!still_on_time())
							{
								$this->save_state();
								meta_refresh(1, append_sid($this->u_action . '&amp;action=update&amp;hash=' . generate_link_hash('acp_storage')));
								trigger_error($this->lang->lang('STORAGE_UPDATE_REMOVE_REDIRECT', $this->lang->lang('STORAGE_' . strtoupper($storage_name) . '_TITLE'), $i + 1, count($this->state['storages'])));
							}

							$current_adapter->delete($row['file_path']);

							$this->state['file_index'] = $row['file_id']; // Set last uploaded file
						}

						// Remove all files of a storage, increase storage index and reset file index
						$this->state['remove_storage_index']++;
						$this->state['file_index'] = 0;
					}
				}
			}

			// Here all files have been copied/moved, so save new configuration
			foreach (array_keys($this->state['storages']) as $storage_name)
			{
				$this->update_storage_config($storage_name);
			}

			$storages = array_keys($this->state['storages']);
			$this->state = false;
			$this->save_state();

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_STORAGE_UPDATE', false, $storages);
			trigger_error($this->lang->lang('STORAGE_UPDATE_SUCCESSFUL') . adm_back_link($this->u_action) . $this->close_popup_js());
		}

		// There is an updating in progress, show the form to continue or cancel
		if ($this->state != false)
		{
			$this->template->assign_vars(array(
				'UA_PROGRESS_BAR'		=> addslashes(append_sid($this->path_helper->get_phpbb_root_path() . $this->path_helper->get_adm_relative_path() . "index." . $this->path_helper->get_php_ext(), "i=$id&amp;mode=$mode&amp;action=progress_bar")),
				'S_CONTINUE_UPDATING'	=> true,
				'U_CONTINUE_UPDATING'	=> $this->u_action . '&amp;action=update&amp;hash=' . generate_link_hash('acp_storage'),
				'L_CONTINUE'			=> $this->lang->lang('CONTINUE_UPDATING'),
				'L_CONTINUE_EXPLAIN'	=> $this->lang->lang('CONTINUE_UPDATING_EXPLAIN'),
			));

			return;
		}

		// Process form and create a "state" for the update,
		// then show a confirm form
		$messages = [];

		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key($form_key) || !check_link_hash($this->request->variable('hash', ''), 'acp_storage'))
			{
				trigger_error($this->lang->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$modified_storages = [];

			foreach ($this->storage_collection as $storage)
			{
				$storage_name = $storage->get_name();

				$options = $this->get_provider_options($this->get_current_provider($storage_name));

				$this->validate_path($storage_name, $options, $messages);

				$modified = false;

				// Check if provider have been modified
				if ($this->request->variable([$storage_name, 'provider'], '') != $this->get_current_provider($storage_name))
				{
					$modified = true;
				}

				// Check if options have been modified
				if (!$modified)
				{
					foreach (array_keys($options) as $definition)
					{
						if ($this->request->variable([$storage_name, $definition], '') != $this->get_current_definition($storage_name, $definition))
						{
							$modified = true;
							break;
						}
					}
				}

				// If the storage have been modified, validate options
				if ($modified)
				{
					$modified_storages[] = $storage_name;
					$this->validate_data($storage_name, $messages);
				}
			}

			if (!empty($modified_storages))
			{
				if (empty($messages))
				{
					// Create state
					$this->state = [
						// Save the value of the checkbox, to remove all files from the
						// old storage once they have been successfully moved
						'update_type' => $this->request->variable('update_type', 0),
						'storage_index' => 0,
						'file_index' => 0,
						'remove_storage_index' => 0,
					];

					// Save in the state the selected storages and their configuration
					foreach ($modified_storages as $storage_name)
					{
						$this->state['storages'][$storage_name]['provider'] = $this->request->variable([$storage_name, 'provider'], '');

						$options = $this->get_provider_options($this->request->variable([$storage_name, 'provider'], ''));

						foreach (array_keys($options) as $definition)
						{
							$this->state['storages'][$storage_name]['config'][$definition] = $this->request->variable([$storage_name, $definition], '');
						}
					}

					$this->save_state(); // A storage update is going to be done here

					// Show the confirmation form to start the process
					$this->template->assign_vars(array(
						'UA_PROGRESS_BAR'		=> addslashes(append_sid($this->path_helper->get_phpbb_root_path() . $this->path_helper->get_adm_relative_path() . "index." . $this->path_helper->get_php_ext(), "i=$id&amp;mode=$mode&amp;action=progress_bar")), // same
						'S_CONTINUE_UPDATING'	=> true,
						'U_CONTINUE_UPDATING'	=> $this->u_action . '&amp;action=update&amp;hash=' . generate_link_hash('acp_storage'),
						'L_CONTINUE'			=> $this->lang->lang('START_UPDATING'),
						'L_CONTINUE_EXPLAIN'	=> $this->lang->lang('START_UPDATING_EXPLAIN'),
					));

					return;
				}
				else
				{
					trigger_error(implode('<br>', $messages) . adm_back_link($this->u_action), E_USER_WARNING);
				}
			}

			// If there is no changes
			trigger_error($this->lang->lang('STORAGE_NO_CHANGES') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Top table with stats of each storage
		$storage_stats = [];
		foreach ($this->storage_collection as $storage)
		{
			$storage_name = $storage->get_name();
			$options = $this->get_provider_options($this->get_current_provider($storage_name));

			$this->validate_path($storage_name, $options, $messages);

			try
			{
				$free_space = get_formatted_filesize($storage->free_space());
			}
			catch (\phpbb\storage\exception\exception $e)
			{
				$free_space = $this->lang->lang('STORAGE_UNKNOWN');
			}

			$storage_stats[] = [
				'name' => $this->lang->lang('STORAGE_' . strtoupper($storage->get_name()) . '_TITLE'),
				'files' => $storage->get_num_files(),
				'size' => get_formatted_filesize($storage->get_size()),
				'free_space' => $free_space,
			];
		}

		$this->template->assign_vars([
			'STORAGES'			=> $this->storage_collection,
			'STORAGE_STATS'		=> $storage_stats,
			'PROVIDERS' 		=> $this->provider_collection,

			'ERROR_MSG'			=> implode('<br>', $messages),
			'S_ERROR'			=> !empty($messages),

			'U_ACTION'			=> $this->u_action . '&amp;hash=' . generate_link_hash('acp_storage'),
		]);
	}

	/**
	 * Display progress bar
	 */
	protected function display_progress_bar() : void
	{
		adm_page_header($this->lang->lang('STORAGE_UPDATE_IN_PROGRESS'));
		$this->template->set_filenames(array(
			'body'	=> 'progress_bar.html')
		);
		$this->template->assign_vars(array(
			'L_PROGRESS'			=> $this->lang->lang('STORAGE_UPDATE_IN_PROGRESS'),
			'L_PROGRESS_EXPLAIN'	=> $this->lang->lang('STORAGE_UPDATE_IN_PROGRESS_EXPLAIN'))
		);
		adm_page_footer();
	}

	/**
	 * Get JS code for closing popup
	 *
	 * @return string Popup JS code
	 */
	function close_popup_js() : string
	{
		return "<script type=\"text/javascript\">\n" .
			"// <![CDATA[\n" .
			"	close_waitscreen = 1;\n" .
			"// ]]>\n" .
			"</script>\n";
	}

	/**
	 * Save state of storage update
	 */
	protected function save_state() : void
	{
		$state = $this->state;

		if ($state == false)
		{
			$state = [];
		}

		$this->config_text->set('storage_update_state', json_encode($state));
	}

	/**
	 * Load state of storage update
	 */
	protected function load_state() : void
	{
		$state = json_decode($this->config_text->get('storage_update_state'), true);

		if ($state == null || empty($state))
		{
			$state = false;
		}

		$this->state = $state;
	}

	/**
	 * Get the current provider from config
	 *
	 * @param string $storage_name Storage name
	 * @return string The current provider
	 */
	protected function get_current_provider(string $storage_name) : string
	{
		return $this->config['storage\\' . $storage_name . '\\provider'];
	}

	/**
	 * Get adapter definitions from a provider
	 *
	 * @param string $provider Provider class
	 * @return array Adapter definitions
	 */
	protected function get_provider_options(string $provider) : array
	{
		return $this->provider_collection->get_by_class($provider)->get_options();
	}

	/**
	 * Get the current value of the definition of a storage from config
	 *
	 * @param string $storage_name Storage name
	 * @param string $definition Definition
	 * @return string Definition value
	 */
	protected function get_current_definition(string $storage_name, string $definition) : string
	{
		return $this->config['storage\\' . $storage_name . '\\config\\' . $definition];
	}

	/**
	 * Validates data
	 *
	 * @param string $storage_name Storage name
	 * @param array $messages Reference to messages array
	 */
	protected function validate_data(string $storage_name, array &$messages)
	{
		$storage_title = $this->lang->lang('STORAGE_' . strtoupper($storage_name) . '_TITLE');

		// Check if provider exists
		try
		{
			$new_provider = $this->provider_collection->get_by_class($this->request->variable([$storage_name, 'provider'], ''));
		}
		catch (\Exception $e)
		{
			$messages[] = $this->lang->lang('STORAGE_PROVIDER_NOT_EXISTS', $storage_title);
			return;
		}

		// Check if provider is available
		if (!$new_provider->is_available())
		{
			$messages[] = $this->lang->lang('STORAGE_PROVIDER_NOT_AVAILABLE', $storage_title);
			return;
		}

		// Check options
		$new_options = $this->get_provider_options($this->request->variable([$storage_name, 'provider'], ''));

		foreach ($new_options as $definition_key => $definition_value)
		{
			$provider = $this->provider_collection->get_by_class($this->request->variable([$storage_name, 'provider'], ''));
			$definition_title = $this->lang->lang('STORAGE_ADAPTER_' . strtoupper($provider->get_name()) . '_OPTION_' . strtoupper($definition_key));

			$value = $this->request->variable([$storage_name, $definition_key], '');

			switch ($definition_value['type'])
			{
				case 'email':
					if (!filter_var($value, FILTER_VALIDATE_EMAIL))
					{
						$messages[] = $this->lang->lang('STORAGE_FORM_TYPE_EMAIL_INCORRECT_FORMAT', $definition_title, $storage_title);
					}
					// no break

				case 'text':
				case 'password':
					$maxlength = isset($definition_value['maxlength']) ? $definition_value['maxlength'] : 255;
					if (strlen($value) > $maxlength)
					{
						$messages[] = $this->lang->lang('STORAGE_FORM_TYPE_TEXT_TOO_LONG', $definition_title, $storage_title);
					}
				break;

				case 'radio':
				case 'select':
					if (!in_array($value, array_values($definition_value['options'])))
					{
						$messages[] = $this->lang->lang('STORAGE_FORM_TYPE_SELECT_NOT_AVAILABLE', $definition_title, $storage_title);
					}
				break;
			}
		}
	}

	/**
	 * Updates an storage with the info provided in the form
	 *
	 * @param string $storage_name Storage name
	 */
	protected function update_storage_config(string $storage_name) : void
	{
		$current_options = $this->get_provider_options($this->get_current_provider($storage_name));

		// Remove old storage config
		foreach (array_keys($current_options) as $definition)
		{
			$this->config->delete('storage\\' . $storage_name . '\\config\\' . $definition);
		}

		// Update provider
		$this->config->set('storage\\' . $storage_name . '\\provider', $this->state['storages'][$storage_name]['provider']);

		// Set new storage config
		$new_options = $this->get_provider_options($this->state['storages'][$storage_name]['provider']);

		foreach (array_keys($new_options) as $definition)
		{
			$this->config->set('storage\\' . $storage_name . '\\config\\' . $definition, $this->state['storages'][$storage_name]['config'][$definition]);
		}
	}

	/**
	 * Validates path
	 *
	 * @param string $storage_name Storage name
	 * @param array $options Storage provider configuration keys
	 * @param array $messages Error messages array
	 * @return void
	 */
	protected function validate_path(string $storage_name, array $options, array &$messages) : void
	{
		if ($this->provider_collection->get_by_class($this->get_current_provider($storage_name))->get_name() == 'local' && isset($options['path']))
		{
			$path = $this->request->is_set_post('submit') ? $this->request->variable([$storage_name, 'path'], '') : $this->get_current_definition($storage_name, 'path');

			if (empty($path))
			{
				$messages[] = $this->lang->lang('STORAGE_PATH_NOT_SET', $this->lang->lang('STORAGE_' . strtoupper($storage_name) . '_TITLE'));
			}
			else if (!$this->filesystem->is_writable($this->phpbb_root_path . $path) || !$this->filesystem->exists($this->phpbb_root_path . $path))
			{
				$messages[] = $this->lang->lang('STORAGE_PATH_NOT_EXISTS', $this->lang->lang('STORAGE_' . strtoupper($storage_name) . '_TITLE'));
			}
		}
	}

	/**
	 * Get current storage adapter
	 *
	 * @param string $storage_name Storage adapter name
	 *
	 * @return object Storage adapter instance
	 */
	protected function get_current_adapter(string $storage_name): object
	{
		static $adapters = [];

		if (!isset($adapters[$storage_name]))
		{
			$provider = $this->get_current_provider($storage_name);
			$provider_class = $this->provider_collection->get_by_class($provider);

			$adapter = $this->adapter_collection->get_by_class($provider_class->get_adapter_class());
			$definitions = $this->get_provider_options($provider);

			$options = [];
			foreach (array_keys($definitions) as $definition)
			{
				$options[$definition] = $this->get_current_definition($storage_name, $definition);
			}

			$adapter->configure($options);
			//$adapter->set_storage($storage_name);

			$adapters[$storage_name] = $adapter;
		}

		return $adapters[$storage_name];
	}

	/**
	 * Get new storage adapter
	 *
	 * @param string $storage_name
	 *
	 * @return object Storage adapter instance
	 */
	protected function get_new_adapter(string $storage_name) : object
	{
		static $adapters = [];

		if (!isset($adapters[$storage_name]))
		{
			$provider = $this->state['storages'][$storage_name]['provider'];
			$provider_class = $this->provider_collection->get_by_class($provider);

			$adapter = $this->adapter_collection->get_by_class($provider_class->get_adapter_class());
			$definitions = $this->get_provider_options($provider);

			$options = [];
			foreach (array_keys($definitions) as $definition)
			{
				$options[$definition] = $this->state['storages'][$storage_name]['config'][$definition];
			}

			$adapter->configure($options);
			//$adapter->set_storage($storage_name);

			$adapters[$storage_name] = $adapter;
		}

		return $adapters[$storage_name];
	}
}
