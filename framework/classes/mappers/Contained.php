<?php
namespace SAF\Framework;

interface Contained
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
	 * @param object $object
	 */
	public function setParent($object);

}
