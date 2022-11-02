<?php
namespace ITRocks\Framework\Http;

use ITRocks\Framework\Tools\Paths;

/**
 * Http toolbox class
 */
class Http
{

	//------------------------------------------------------------------------------------ GET / POST
	const GET  = 'GET';
	const POST = 'POST';

	//---------------------------------------------------------------------------------- HTTP / HTTPS
	const HTTP  = 'http';
	const HTTPS = 'https';

	//----------------------------------------------------------------------------------- getProtocol
	/**
	 * Return the protocol http which is used by the server without ://
	 *
	 * @return string @values http, https
	 */
	public function getProtocol() : string
	{
		// REQUEST_SCHEME is not 100% proof then use HTTPS.
		// HTTPS is set only when https is used.
		return (!empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) !== 'off'))
			? self::HTTPS
			: self::HTTP;
	}

	//------------------------------------------------------------------------------------------ post
	/**
	 * @param $uri  string
	 * @param $data array
	 * @return string
	 */
	public function post(string $uri, array $data) : string
	{
		if (!str_starts_with($uri, 'http')) {
			$uri = Paths::absoluteBase() . ltrim($uri, SL);
		}
		$proxy = new Proxy(static::POST);
		$proxy->retry_delay = 1;
		$proxy->request($uri, $data, static::POST, 3);
		return $proxy->getResponse();
	}

}
