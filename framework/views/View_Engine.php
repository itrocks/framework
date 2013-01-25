<?php
namespace SAF\Framework;

interface View_Engine
{

	//------------------------------------------------------------------------------------------ link
	/**
	 * Generate a link for class name and parameters
	 *
	 * @param $object string object or class name
	 * @param $parameters mixed  string or array : parameters list (feature and other parameters)
	 * @return string
	 */
	public function link($object, $parameters = null);

}
