<?php

interface View
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param array $uri
	 * @param array $get
	 * @param array $post
	 * @param array $files
	 */
	public function run($uri, $get, $post, $files);

}
