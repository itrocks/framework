<?php

class Html_View implements View
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param array $uri
	 * @param array $get
	 * @param array $post
	 * @param array $files
	 */
	public function run($uri, $get, $post, $files)
	{
		echo "<pre>"
		. "uri = " . print_r($uri, true)
		. "get = " . print_r($get, true)
		. "post = " . print_r($post, true)
		. "files = " . print_r($files, true);
	}

}
