<?php
namespace SAF\Framework;

/**
 * The common interface to all view engines
 */
interface View_Engine
{

	//------------------------------------------------------------------------------------------ link
	/**
	 * Generate a link for class name and parameters
	 *
	 * @param $object     object|string object or class name
	 * @param $parameters string|string[] string or array : parameters list (feature and other parameters)
	 * @return string
	 */
	public function link($object, $parameters = null);

}
