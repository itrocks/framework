<?php
namespace SAF\Framework;

interface Component
{

	//--------------------------------------------------------------------------------------- dispose
	/**
	 * TODO documentation
	 */
	public function dispose();

	//------------------------------------------------------------------------------------- getParent
	/**
	 * Get parent object
	 *
	 * @return object
	 */
	public function getParent();

	//------------------------------------------------------------------------------------- setParent
	/**
	 * Set parents object
	 *
	 * @param $object object
	 * @return Component
	 */
	public function setParent($object);

}
