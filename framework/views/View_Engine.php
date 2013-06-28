<?php
namespace SAF\Framework;

/**
 * The common interface to all view engines
 */
interface View_Engine
{

	//------------------------------------------------------------------------------------------ link
	/**
	 * Generates a link for to an object and feature, using parameters if needed
	 *
	 * @param $object     object|string linked object or class name
	 * @param $feature    string linked feature name
	 * @param $parameters string|string[] string or array : parameters list
	 * @return string
	 */
	public function link($object, $feature = null, $parameters = null);

}
