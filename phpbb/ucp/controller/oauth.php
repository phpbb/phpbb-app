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

namespace phpbb\ucp\controller;

use phpbb\auth\provider_collection;
use phpbb\exception\http_exception;
use phpbb\request\request_interface;
use Symfony\Component\HttpFoundation\Response;

class oauth
{
	/** @var provider_collection Auth provider collection */
	protected $auth_collection;

	/** @var request_interface Request class instance */
	protected $request;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	/** @var string PHP extension */
	protected $php_ext;

	/**
	 * Constructor for oauth controller
	 *
	 * @param provider_collection $auth_collection
	 * @param request_interface $request
	 * @param string $phpbb_root_path
	 * @param string $php_ext
	 */
	public function __construct(provider_collection $auth_collection, request_interface $request,
						string $phpbb_root_path, string $php_ext)
	{
		$this->auth_collection = $auth_collection;
		$this->request = $request;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Handle linking of accounts via oauth
	 *
	 * @return void
	 */
	public function link()
	{
		$auth_provider = $this->auth_collection->get_provider();

		// Check if auth provider supports linking
		$provider_data = $auth_provider->get_auth_link_data();
		if ($provider_data === null)
		{
			throw new http_exception(Response::HTTP_UNAUTHORIZED, 'UCP_AUTH_LINK_NOT_SUPPORTED');
		}

		// Link account if link is signalled, otherwise redirect to index
		if ($this->request->variable('link', false))
		{
			// In this case the link data should only be populated with the
			// link_method as the provider dictates how data is returned to it.
			$link_data = ['link_method' => 'auth_link'];

			$error = $auth_provider->link_account($link_data);

			if ($error)
			{
				throw new http_exception(Response::HTTP_BAD_REQUEST, $error);
			}

			// Redirect to UCP page if there was no error linking the account
			redirect(append_sid("{$this->phpbb_root_path}ucp.$this->php_ext?i=ucp_auth_link&mode=auth_link"));
		}
		else
		{
			redirect(append_sid("{$this->phpbb_root_path}index.$this->php_ext"));
		}
	}
}
