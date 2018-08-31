<?php
namespace ITRocks\Framework\Http;

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
	public function getProtocol()
	{
		// REQUEST_SCHEME is not 100% proof then use HTTPS
		// HTTPS is set only when https is used
		return (!empty($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] !== 'off')
			? self::HTTPS
			: self::HTTP
		);
	}

}
