<?php
namespace ITRocks\Framework\Email;

use ITRocks\Framework\Tools\Paths;
use ITRocks\Framework\View\Html\Template;

/**
 * HTML emails template engine
 */
class Html_Template extends Template
{

	//----------------------------------------------------------------------------------- replaceLink
	/**
	 * @param $uri string
	 * @return string
	 */
	protected function replaceLink(string $uri) : string
	{
		return str_contains($uri, '://')
			? $uri
			: (Paths::absoluteBase() . $uri);
	}

	//------------------------------------------------------------------------------------ replaceUri
	/**
	 * Replace URI with correct URI paths
	 * Global files (images) : change to full server-side path references for in-email inclusion
	 *
	 * @param $uri string
	 * @return string updated uri
	 */
	protected function replaceUri(string $uri) : string
	{
		if (str_contains($uri, '://') || !in_array(substr($uri, -4), ['.gif', '.jpg', '.png'])) {
			$final_uri = $uri;
		}
		else {
			$final_uri = $this->path . SL . $uri;
		}
		return $final_uri;
	}

}
