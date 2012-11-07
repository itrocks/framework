<?php
namespace SAF\Framework;

interface View_Engine
{

	//------------------------------------------------------------------------------------------ link
	/**
	 * Generate a link for class name and parameters
	 *
	 * @param string $object object or class name
	 * @param mixed  $parameters string or array : parameters list (feature and other parameters)
	 * @return string
	 */
	public function link($object, $parameters = null);

}
