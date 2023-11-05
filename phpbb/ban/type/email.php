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

namespace phpbb\ban\type;

use phpbb\ban\exception\no_valid_emails_exception;
use phpbb\exception\runtime_exception;

class email extends base
{
	/**
	 * {@inheritDoc}
	 */
	public function get_type()
	{
		return 'email';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_user_column()
	{
		return 'user_email';
	}

	/**
	 * {@inheritDoc}
	 */
	public function after_ban(array $data)
	{
		$this->logout_affected_users($data['items']);

		return parent::after_ban($data);
	}

	/**
	 * {@inheritDoc}
	 */
	public function prepare_for_storage(array $items)
	{
		if (!$this->get_excluded())
		{
			throw new runtime_exception(); // TODO
		}
		$regex = '#^.*?@.*|(([a-z0-9\-]+\.)+([a-z]{2,3}))$#i'; // TODO

		$ban_items = [];
		foreach ($items as $item)
		{
			$item = trim($item);
			if (strlen($item) > 100 || !preg_match($regex, $item) || in_array($item, $this->excluded))
			{
				continue;
			}
			$ban_items[] = $item;
		}

		if (empty($ban_items))
		{
			throw new no_valid_emails_exception(); // TODO
		}

		return $ban_items;
	}
}
