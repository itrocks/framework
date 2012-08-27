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
	 * @param  object
	 * @return Contained
	 */
	public function setParent($object);

}
