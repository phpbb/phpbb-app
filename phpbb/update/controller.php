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

namespace phpbb\update;

use phpbb\filesystem\filesystem_interface;
use phpbb\language\language;

class controller
{
	/** @var filesystem_interface Filesystem manager */
	private filesystem_interface $filesystem;

	/** @var get_updates Updater class */
	private get_updates $updater;

	/** @var language Translation handler */
	private language $language;

	/** @var string phpBB root path */
	private string $phpbb_root_path;

	/**
	 * Constructor.
	 *
	 * @param filesystem_interface $filesystem
	 * @param get_updates $updater
	 * @param language $language
	 * @param string $phpbb_root_path
	 */
	public function __construct(
		filesystem_interface $filesystem,
		get_updates $updater,
		language $language,
		string $phpbb_root_path)
	{
		$this->filesystem = $filesystem;
		$this->language = $language;
		$this->updater = $updater;
		$this->phpbb_root_path = $phpbb_root_path;
	}

	/**
	 * Handle requests.
	 *
	 * @param string $download	The download URL.
	 *
	 * @return string[] Unencoded json response.
	 */
	public function handle(string $download): array
	{
		$update_path = $this->phpbb_root_path . 'store/update.zip';
		$status = ['status' => 'continue'];
		if (!file_exists($update_path))
		{
			$result = $this->updater->download($download, $update_path);
			if (!$result)
			{
				return [
					'status' => 'error',
					'error' => $this->language->lang('COULD_NOT_DOWNLOAD_UPDATE_PACKAGE')
				];
			}

			return $status;
		}

		if (!file_exists($update_path . '.sig'))
		{
			$result = $this->updater->download($download . '.sig', $update_path . '.sig');
			if (!$result)
			{
				return [
					'status' => 'error',
					'error' => $this->language->lang('COULD_NOT_DOWNLOAD_UPDATE_SIGNATURE')
				];
			}
			return $status;
		}

		if (!is_dir($this->phpbb_root_path . 'store/update'))
		{
			$result = $this->updater->validate($update_path, $update_path . '.sig');
			if (!$result)
			{
				return [
					'status' => 'error',
					'error' => $this->language->lang('UPDATE_SIGNATURE_INVALID')
				];
			}

			$result = $this->updater->extract($update_path, $this->phpbb_root_path . 'store/update');
			if (!$result)
			{
				return [
					'status' => 'error',
					'error' => $this->language->lang('COULD_NOT_EXTRACT_UPDATE')
				];
			}

			return $status;
		}

		if (!is_dir($this->phpbb_root_path . 'install'))
		{
			$result = $this->updater->copy($this->phpbb_root_path . 'store/update');
			if (!$result)
			{
				return [
					'status' => 'error',
					'error' => $this->language->lang('COULD_NOT_WRITE_UPDATE_FILES')
				];
			}

			return $status;
		}

		$this->filesystem->remove([
			$this->phpbb_root_path . 'store/update',
			$update_path,
			$update_path . '.sig'
		]);

		$status['status'] = 'done';
		return $status;
	}
}
