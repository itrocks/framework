<?php
namespace ITRocks\Framework\Controller;

/**
 * Target utils class
 */
abstract class Target
{

	//------------------------------------------------------------------------------ target constants
	const BLANK      = '#_blank';
	const MAIN       = '#main';
	const MENU       = '#menu';
	const MODAL      = '#modal';
	const NEW_WINDOW = '_blank';
	const NONE       = '#';
	const POPUP      = '#popup';
	const QUERY      = '#query';
	const RESPONSES  = '#responses';
	const TOP        = '';

	//-------------------------------------------------------------------------------------------- to
	/**
	 * Prepare an output to be targeted to the target name
	 *
	 * @param $target_name string @values static::const
	 * @param $content     string
	 * @return string
	 */
	public static function to(string $target_name, string $content) : string
	{
		return '<!--target ' . $target_name . '-->' . LF . $content . LF . '<!--end-->' . LF;
	}

}
