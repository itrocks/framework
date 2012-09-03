<?php

interface Contained
{

	//--------------------------------------------------------------------------------------- dispose
	public function dispose();

	//------------------------------------------------------------------------------------- getParent
	/**
	 * @return object
	 */
	public function getParent();

	//------------------------------------------------------------------------------------- setParent
	/**
	 * @param object $object
	 */
	public function setParent($object);

}
