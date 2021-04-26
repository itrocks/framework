<?php
namespace ITRocks\Framework\Http;

abstract class Uri
{

	//---------------------------------------------------------------------------- startsWithProtocol
	/**
	 * @param $uri string
	 * @return boolean
	 */
	public static function startsWithProtocol(string $uri) : bool
	{
		return str_contains($uri, ':')
			&& ((strpos($uri, ':') < strpos($uri, SL)) || !str_contains($uri, SL));
	}

}
