<?php
/**
*
* @package auth
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Http\Uri\Uri;

/**
* OAuth authentication provider for phpBB3
*
* @package auth
*/
class phpbb_auth_provider_oauth extends phpbb_auth_provider_base
{
	/**
	* Database driver
	*
	* @var phpbb_db_driver
	*/
	protected $db;

	/**
	* phpBB config
	*
	* @var phpbb_config
	*/
	protected $config;

	/**
	* phpBB request object
	*
	* @var phpbb_request
	*/
	protected $request;

	/**
	* phpBB user
	*
	* @var phpbb_user
	*/
	protected $user;

	/**
	* Cache driver.
	*
	* @var phpbb_cache_driver_interface
	*/
	protected $driver;

	/**
	* Cached service once it has been created
	*
	* @var \OAuth\Common\Service\ServiceInterface|null
	*/
	protected $service;

	/**
	* Cached current uri object
	*
	* @var \OAuth\Common\Http\Uri\UriInterface|null
	*/
	protected $current_uri;

	/**
	* OAuth Authentication Constructor
	*
	* @param 	phpbb_db_driver 				$db
	* @param 	phpbb_config 					$config
	* @param 	phpbb_request 					$request
	* @param 	phpbb_user 						$user
	* @param	phpbb_cache_driver_interface	$driver
	*/
	public function __construct(phpbb_db_driver $db, phpbb_config $config, phpbb_request $request, phpbb_user $user, phpbb_cache_driver_interface $driver)
	{
		$this->db = $db;
		$this->config = $config;
		$this->request = $request;
		$this->user = $user;
		$this->driver = $driver;
	}

	/**
	* {@inheritdoc}
	*/
	public function login($username, $password)
	{
		// Requst the name of the OAuth service
		$service_name = $this->request->variable('oauth_service', '', false, phpbb_request_interface::POST);
		if ($service_name === '')
		{
			return array(
				'status'		=> LOGIN_ERROR_EXTERNAL_AUTH,
				'error_msg'		=> 'LOGIN_ERROR_EXTERNAL_AUTH_APACHE',
				'user_row'		=> array('user_id' => ANONYMOUS),
			);
		}

		if ($this->request->is_set('code', phpbb_request_interface::GET))
		{
			// Second pass: request access token, authenticate with phpBB
		} else {
			// First pass: get authorization uri, redirect to service
		}
	}

	/**
	*
	*/
	protected function get_service_credentials($service_name)
	{
		return array(
			'key'		=> $this->config['auth_oauth_' . $service_name . '_key'],
			'secret'	=> $this->config['auth_oauth_' . $service_name . '_secret'],
		);
	}

	protected function get_current_uri()
	{
		if ($this->current_uri)
		{
			return $this->current_uri;
		}

		$uri_factory = new \OAuth\Common\Http\Uri\UriFactory();
		$current_uri = $uri_factory->createFromSuperGlobalArray($this->request->get_super_global(phpbb_request_interface::SERVER));
		$current_uri->setQuery('');

		$this->current_uri = $current_uri;
		return $current_uri;
	}

	/**
	* Returns the cached service object or creates a new one
	*
	* @param	string	$service_name	The name of the service
	* @param	array	$scope			The scope of the request against the api.
	* @return	\OAuth\Common\Service\ServiceInterface
	*/
	protected function get_service($service_name, array $scopes = array())
	{
		if ($this->service)
		{
			return $this->service;
		}

		// Get the service credentials for the given service
		$service_credentials = $this->get_credentials($service_name);

		// Check that the service has settings
		if ($service_credentials['key'] == false || $service_credentials['secret'] == false)
		{
			return array(
				'status'		=> LOGIN_ERROR_EXTERNAL_AUTH,
				'error_msg'		=> 'LOGIN_ERROR_EXTERNAL_AUTH_APACHE',
				'user_row'		=> array('user_id' => ANONYMOUS),
			);
		}

		$storage = new phpbb_auth_oauth_token_storage($this->driver);

		$current_uri = $this->get_current_uri();

		// Setup the credentials for the requests
		$credentials = new Credentials(
			$service_credentials['key'],
			$service_credentials['secret'],
			$current_uri->getAbsoluteUri()
		);

		$service_factory = new \OAuth\ServiceFactory();
		$this->service = $service_factory->createService($service_name, $credentials, $storage, $scopes);

		return $this->service;
	}
}
