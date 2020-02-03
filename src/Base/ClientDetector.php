<?php
/**
 * @package    framework
 * @copyright  Copyright 2005-2019 HUBzero Foundation, LLC.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Hubzero\Base;

use Hubzero\Http\Request;

/**
 * Client detector
 *
 * Inspired by Laravel's environment detector
 * http://laravel.com
 */
/**
 * Client detector
 *
 * Inspired by Laravel's environment detector
 * http://laravel.com
 */
class ClientDetector
{
	/**
	 * Request URI
	 */
	private $request = null;

	/**
	 * Create a new application instance.
	 *
	 * @return  void
	 */
	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	/**
	 * Detect the application's current client.
	 *
	 * @param   array  $environments
	 * @return  object
	 */
	public function detect($environments)
	{
		if ($this->detectConsoleEnvironment($environments))
		{
			return ClientManager::client('cli', true);
		}

		return $this->detectWebEnvironment($environments);
	}

	/**
	 * Determine client for a web request.
	 *
	 * @param   array   $environments
	 * @return  object
	 */
	protected function detectWebEnvironment($environments)
	{
		$default = ClientManager::client('site', true);

		// To determine the current client, we'll simply iterate through the possible
		// clients and look for the one that matches the path for the request we
		// are currently processing here, then return back that client.
		foreach ($environments as $environment => $url)
		{
			if ($client = ClientManager::client($environment, true))
			{
				if ($client->name == 'cli')
				{
					continue;
				}

				// Legacy check based on file path
				//    Ex: JPATH_API would be set from ROOT/api/index.php
				// @TODO: Remove need for this code
				$const = 'JPATH_' . strtoupper($environment);

				if (defined($const)
				 && defined('JPATH_BASE')
				 && JPATH_BASE == constant($const))
				{
					return $client;
				}

				// Check based on request path
				//    Ex: http://somehub.org/api
				if ($this->request->segment(1) == $url
				 || $this->request->segment(1) == $client->name
				 || $this->request->segment(1) == $client->url)
				{
					return $client;
				}
			}
		}

		return $default;
	}

	/**
	 * Determine if the client is command-line
	 *
	 * @param   array   $environments
	 * @return  bool
	 */
	protected function detectConsoleEnvironment($environments)
	{
		return (php_sapi_name() == 'cli');
	}
}
