<?php
namespace SAF\Framework\Email;

use SAF\Framework\View\Html\Template;

/**
 * HTML emails template engine
 */
class Html_Template extends Template
{

	//------------------------------------------------------------------------------------ replaceUri
	/**
	 * Replace URI with correct URI paths
	 * Global files (images) : change to full server-side path references for in-email inclusion
	 *
	 * @param $uri string
	 * @return string updated uri
	 */
	protected function replaceUri($uri)
	{
		if (strpos($uri, '://') || !in_array(substr($uri, -4), ['.gif', '.jpg', '.png'])) {
			$final_uri = $uri;
		}
		else {
			$final_uri = $this->path . SL . $uri;
		}
		return $final_uri;
	}

}
